<?php
require(dirname(__FILE__) . "/../../../../../_includes/classes/fpdf/fpdf.php");	
require(dirname(__FILE__) . "/../../../../../_includes/classes/fpdf/fpdi.php");

$id_course = $_GET['id_course'];
$id_customer = $_GET['id_customer'];

if($id_course){
	$connection=Yii::app()->db;
	// Get the data of the customer in this course
	$params=array(':id_customer'=>$id_customer, ':id_course'=>$id_course);	
	$sql = 'SELECT 
	customer_courses_scorm.data	
	FROM		
	customer_courses_scorm
	WHERE 
	customer_courses_scorm.id_customer = :id_customer
	AND
	customer_courses_scorm.id_course = :id_course';
			
	$command=$connection->createCommand($sql);
	
	$row = $command->queryRow(true, $params);
	$data = !empty($row['data']) ? unserialize(base64_decode($row['data'])):array();
	

	$sql = 'SELECT 
	scorm_certificate_product.id,
	scorm_certificate.id AS id_certificate,
	scorm_certificate.file_name,
	scorm_certificate_product.id AS id_scorm_certificate_product
	FROM 
	scorm_certificate_product 
	INNER JOIN 
	scorm_certificate
	ON
	scorm_certificate_product.id_scorm_certificate = scorm_certificate.id
	WHERE scorm_certificate_product.id_product_downloadable_files = "'.$id_course.'"';
			
	$command_scorm_certificate_product=$connection->createCommand($sql);

	if($rows_scorm_certificate_product = $command_scorm_certificate_product->queryAll(true)){
		
		// Verify id_customer from api.php
		if($id_customer){
			
			$sql = 'SELECT 
			customer.id,
			customer.firstname,
			customer.lastname,
			customer.email,
			customer_custom_fields_value.value AS no_license,
			customer.language_code
			FROM 
			customer 
			
			LEFT JOIN
			customer_custom_fields_value
			ON
			customer.id = customer_custom_fields_value.id_customer AND customer_custom_fields_value.id_custom_fields = 4
			
			WHERE customer.id = "'.$id_customer.'"';
					
			$command_customer=$connection->createCommand($sql);

			if($row_customer = $command_customer->queryRow(true)){

				
				// Client Custom Field

					
					
				$sql = 'SELECT 
				*
				FROM 
				customer_custom_fields_value 
				WHERE id_customer = "'.$row_customer['id'].'"';
						
				$command_customer_custom_fields_value=$connection->createCommand($sql);

				if($rows_customer_custom_fields_value = $command_customer_custom_fields_value->queryAll(true)){
					$arr_customer_custom_field = array();
					foreach($rows_customer_custom_fields_value as $row_custom_field){
						$arr_customer_custom_field[$row_custom_field['id_custom_fields']][$row_custom_field['id_custom_fields_option']] = $row_custom_field['value'];
					}
					//echo '<pre>'.print_r($arr_customer_custom_field,1).'</pre>';
				}
				
				// Verify the condition to respect with the customer field and the scorm_certificate_product
				foreach($rows_scorm_certificate_product as $row_scorm_certificate_product){
					$respect_condition = true;
					$sql = 'SELECT 
					*
					FROM 
					scorm_certificate_condition 
					WHERE id_scorm_certificate_product = "'.$row_scorm_certificate_product['id'].'"';
							
					$command_condition=$connection->createCommand($sql);

					if($rows_condition = $command_condition->queryAll(true)){
						foreach($rows_condition as $row_condition){
							// Verify to put the good value to compare with custom_fields table if its a single check box
							switch($row_condition['id_custom_fields']){
								case '6':// Single Check Box
								switch($row_condition['id_custom_fields_option']){
									case '-1':// Single Check Box
										$row_condition['id_custom_fields_option'] = $row_condition['id_custom_fields'];
									break;
									case '-2':// Single Check Box
										$row_condition['id_custom_fields_option'] = 0;
									break;	
								}
								break;
							}
							
							if($row_condition['id_custom_fields']==-1){
								if(!(($data['cmi.core.score.raw'] >= $row_condition['score_from']) && ($data['cmi.core.score.raw'] <= $row_condition['score_to']))){
									$respect_condition = false;
									break;
								}
							}else{
								if(!isset($arr_customer_custom_field[$row_condition['id_custom_fields']][$row_condition['id_custom_fields_option']])){
									$respect_condition = false;
									break;
								}
							}
						}
					}
					// If True then end loop
					if($respect_condition == true){
						$certificate_id = $row_scorm_certificate_product['id_certificate'];
						$certificate_file_name = $row_scorm_certificate_product['file_name'];
						$customer_name = $row_customer['firstname'] . " " .$row_customer['lastname'];
						$customer_id = $row_customer['id'];
						$customer_email = $row_customer['email'];
						$current_date = Html::df_date(date("Y-m-d"),1,-1,$row_customer['language_code'],Yii::app()->params[$row->company_country_code]);
						$no_license = $row_customer['no_license']; 
						break;
					}
				}
			}
			
			

			// We send the certificate if $respect_condition is true
			if($respect_condition == true){
			
				
				
				// Verify witch certificate to send
				switch($certificate_id){
					case 1://Certificat OPQ
						$pdf =& new FPDI('P','in',array(10,7.5));	

						//Set font
						$pdf->SetFont('Times','B',36);
						
						$pagecount = $pdf->setSourceFile(Yii::getPathOfAlias('webroot').'/protected/scorm_certificate/'.$certificate_file_name);
							
						
						$tplidx = $pdf->importPage(1);
						
						$pdf->addPage();
						$pdf->useTemplate($tplidx,0,0);											
						$pdf->SetAutoPageBreak(0);	
						
						//$pdf->SetTextColor(255,0,0);
						
						$pdf->SetY(2.7);
						$pdf->SetX(0.4);
						$pdf->MultiCell(0, 0.5, utf8_decode($customer_name), 0, 'C', false);
						
						//Set font
						$pdf->SetFont('Times','B',18);
						
						$pdf->SetY(3.2);
						$pdf->SetX(0.4);
						$pdf->MultiCell(0, 0.5, utf8_decode('Numéro de permis: '.$no_license), 0, 'C', false);
						
						//Set font
						$pdf->SetFont('Times','B',15);
						$pdf->SetTextColor(148,148,148);
						
						$pdf->SetY(5.2);
						$pdf->SetX(0.4);
						$pdf->MultiCell(0, 0.5, utf8_decode($current_date), 0, 'C', false);
						
						$pdf->SetTextColor(0,0,0);
						
						// Addionnal Field
						$sql = 'SELECT 
						*
						FROM 
						scorm_certificate_additional_field_value 
						WHERE id_scorm_certificate_product = "'.$row_scorm_certificate_product['id'].'"';
								
						$command_additional_field=$connection->createCommand($sql);
		
						if($rows_additional_field = $command_additional_field->queryAll(true)){
							foreach($rows_additional_field as $row_additional_field){
								switch($row_additional_field['id_scorm_cetificate_additional_field']){
									case 1:
										//Set font
										$pdf->SetFont('Times','B',9);
										
										$pdf->SetY(5.95);
										$pdf->SetX(8.14);
										$pdf->Write(0.5,utf8_decode($row_additional_field['value']));
									break;
									case 2:
										//Set font
										$pdf->SetFont('Times','B',16);
										
										$pdf->SetY(4.7);
										$pdf->SetX(0.4);
										$pdf->MultiCell(0, 0.5, utf8_decode("Numéro de dossier OPQ: ".$row_additional_field['value']), 0, 'C', false);
									break;
									case 3:
										//Set font
										$pdf->SetFont('Times','BI',22);
										
										$pdf->SetY(4.2);
										$pdf->SetX(0.4);
										$pdf->MultiCell(0, 0.5, utf8_decode($row_additional_field['value']), 0, 'C', false);
									break;
								}
							}
						}

						$pdfdoc = $pdf->Output('Certificat.pdf', 'D');
						
						
					
					break;	
					case 2://Certificat SOFEDUC
						$pdf =& new FPDI('P','in',array(10,7.5));	

						//Set font
						$pdf->SetFont('Times','B',36);
						
						$pagecount = $pdf->setSourceFile(Yii::getPathOfAlias('webroot').'/protected/scorm_certificate/'.$certificate_file_name);

						$tplidx = $pdf->importPage(1);
						
						$pdf->addPage();
						$pdf->useTemplate($tplidx,0,0);											
						$pdf->SetAutoPageBreak(0);	
						
						//$pdf->SetTextColor(255,0,0);
						
						$pdf->SetY(3.14);
						$pdf->SetX(0.4);
						$pdf->MultiCell(0, 0.5, utf8_decode($customer_name), 0, 'C', false);
	
						
						//Set font
						$pdf->SetFont('Times','B',15);
						$pdf->SetTextColor(148,148,148);
						
						$pdf->SetY(4.75);
						$pdf->SetX(0.4);
						$pdf->MultiCell(0, 0.5, utf8_decode($current_date), 0, 'C', false);
						
						$pdf->SetTextColor(0,0,0);
						
						// Addionnal Field
						$sql = 'SELECT 
						*
						FROM 
						scorm_certificate_additional_field_value 
						WHERE id_scorm_certificate_product = "'.$row_scorm_certificate_product['id'].'"';
								
						$command_additional_field=$connection->createCommand($sql);
		
						if($rows_additional_field = $command_additional_field->queryAll(true)){
							foreach($rows_additional_field as $row_additional_field){
								switch($row_additional_field['id_scorm_cetificate_additional_field']){
									case 1:
										//Set font
										$pdf->SetFont('Times','B',11);
										
										$pdf->SetY(6.01);
										$pdf->SetX(5.96);
										$pdf->Write(0.5,utf8_decode($row_additional_field['value']));
									break;
									case 3:
										//Set font
										$pdf->SetFont('Times','BI',22);
										
										$pdf->SetY(4.2);
										$pdf->SetX(0.4);
										$pdf->MultiCell(0, 0.5, utf8_decode($row_additional_field['value']), 0, 'C', false);
									break;
									
								}
							}
						}

						$pdfdoc = $pdf->Output('Certificat.pdf', 'D');
	
					break;
				}

			}
		}	
	}
}
?>