<?php
include(dirname(__FILE__) . "/../../_includes/config.php");
include(dirname(__FILE__) . "/../../_includes/validate_session.php");
require(dirname(__FILE__) . "/../../_includes/classes/fpdf/fpdf.php");	
require(dirname(__FILE__) . "/../../_includes/classes/fpdf/fpdi.php");



// Verify files id from api.php

/* CES VALEURS SONT UTILISE POUR EFFECTUER DES TESTS */
$SCOInstanceID = 1;
$id_customer = 36;
$data['cmi.core.score.raw'] = 90;



if($SCOInstanceID){
	
	if (!$result = $mysqli->query('SELECT 
	scorm_certificate_product.id,
	scorm_certificate.id AS id_certificate,
	scorm_certificate.file_name,
	scorm_certificate_product.id AS id_scorm_certificate_product,
	product_downloadable_files_description.name AS course_name
	FROM 
	scorm_certificate_product 
	INNER JOIN 
	scorm_certificate
	ON
	scorm_certificate_product.id_scorm_certificate = scorm_certificate.id
	
	INNER JOIN
	(product_downloadable_files CROSS JOIN product_downloadable_files_description)
	ON
	(scorm_certificate_product.id_product_downloadable_files = product_downloadable_files.id AND product_downloadable_files_description.id_product_downloadable_files = product_downloadable_files.id AND product_downloadable_files_description.language_code = "'.$mysqli->escape_string($_SESSION['customer']['language']).'")
	
	
	WHERE scorm_certificate_product.id_product_downloadable_files = "'.$SCOInstanceID.'"')) throw new Exception('An error occured while trying to lookup scorm_certificate_product table.');
	if($result->num_rows){
		// Verify id_customer from api.php
		if($id_customer){
			if (!$result_customer = $mysqli->query('SELECT 
			customer.id,
			customer.firstname,
			customer.lastname,
			customer.email,
			customer_custom_fields_value.value AS no_license
			FROM 
			customer 
			
			LEFT JOIN
			customer_custom_fields_value
			ON
			customer.id = customer_custom_fields_value.id_customer AND customer_custom_fields_value.id_custom_fields = 4
			
			WHERE customer.id = "'.$id_customer.'" AND active = 1')) throw new Exception('An error occured while trying to lookup customer table.');
			if($result_customer->num_rows){
				$row_customer = $result_customer->fetch_assoc();
				
				// Client Custom Field
				if (!$result_custom_field = $mysqli->query('SELECT 
				*
				FROM 
				customer_custom_fields_value 
				WHERE id_customer = "'.$row_customer['id'].'"')) throw new Exception('An error occured while trying to lookup customer_custom_fields_value table.');
				
				if($result_custom_field->num_rows){
					$arr_customer_custom_field = array();
					while ($row_custom_field = $result_custom_field->fetch_assoc()) {
						$arr_customer_custom_field[$row_custom_field['id_custom_fields']][$row_custom_field['id_custom_fields_option']] = $row_custom_field['value'];
					}
					//echo '<pre>'.print_r($arr_customer_custom_field,1).'</pre>';
				}
				
				// Verify the condition to respect with the customer field and the scorm_certificate_product
				while ($row = $result->fetch_assoc()) {
					$respect_condition = true;
					if (!$result_condition = $mysqli->query('SELECT 
					*
					FROM 
					scorm_certificate_condition 
					WHERE id_scorm_certificate_product = "'.$row['id'].'"')) throw new Exception('An error occured while trying to lookup scorm_certificate_condition table.');
					if($result->num_rows){
						while ($row_condition = $result_condition->fetch_assoc()) {
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
							
							// Verify if it's the score condition else continue normally
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
						$certificate_id = $row['id_certificate'];
						$certificate_file_name = $row['file_name'];
						$course_name = $row['course_name'];
						$customer_name = ucfirst($row_customer['firstname']) . " " .ucfirst($row_customer['lastname']);
						$customer_email = $row_customer['email'];
						$current_date = df_date(date("Y-m-d"),1,-1);
						$no_license = $row_customer['no_license']; 
						break;
					}
				}
			}
			
			

			// We send the certificate if $respect_condition is true
			if($respect_condition == true){
				
				// Verify witch certificate to send
				switch($certificate_id){
					
					case 1://Attestation de formation
						$pdf =& new FPDI('P','in',array(11,8.5));	
						$pagecount = $pdf->setSourceFile('../../admin/protected/scorm_certificate/'.$certificate_file_name);
						$tplidx = $pdf->importPage(1);
						
						$pdf->addPage();
						$pdf->useTemplate($tplidx,0,0);											
						$pdf->SetAutoPageBreak(0);	
						
						//Set font
						$pdf->SetFont('Times','B',33);
						$pdf->SetTextColor(225,62,65);
						
						$pdf->SetY(2.33);
						$pdf->SetX(3.65);
						$pdf->MultiCell(0, 0.5, utf8_decode($customer_name), 0, 'C', false);

						//Set font
						$pdf->SetFont('Times','B',15);
						
						$pdf->SetY(7.00);
						$pdf->SetX(7.00);
						$pdf->MultiCell(0, 0.5, utf8_decode($current_date), 0, 'C', false);
						
						//Set font
						$pdf->SetFont('Times','B',24);

						$pdf->SetY(4.0);
						$pdf->SetX(3.65);
						$pdf->MultiCell(0, 0.5, utf8_decode($course_name), 0, 'C', false);

						// Addionnal Field
						if (!$result_additional_field = $mysqli->query('SELECT 
						*
						FROM 
						scorm_certificate_additional_field_value 
						WHERE id_scorm_certificate_product = "'.$row['id'].'"')) throw new Exception('An error occured while trying to lookup scorm_certificate_product table.');
						if($result_additional_field->num_rows){
							while ($row_additional_field = $result_additional_field->fetch_assoc()) {
								switch($row_additional_field['id_scorm_cetificate_additional_field']){
									case 1:
										
									break;
									case 2:
										
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

						$pdfdoc = $pdf->Output('', 'S');
	
					break;	
					
					
					/*case 1://Certificat OPQ
						$pdf =& new FPDI('P','in',array(10,7.5));	

						//Set font
						$pdf->SetFont('Times','B',36);
						
						$pagecount = $pdf->setSourceFile('../../admin/protected/scorm_certificate/'.$certificate_file_name);
							
						
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
						if (!$result_additional_field = $mysqli->query('SELECT 
						*
						FROM 
						scorm_certificate_additional_field_value 
						WHERE id_scorm_certificate_product = "'.$row['id'].'"')) throw new Exception('An error occured while trying to lookup scorm_certificate_product table.');
						if($result_additional_field->num_rows){
							while ($row_additional_field = $result_additional_field->fetch_assoc()) {
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
										$course_name = $row_additional_field['value'];
									break;
								}
							}
						}

						$pdfdoc = $pdf->Output('', 'S');
						
						
					
					break;	
					case 2://Certificat SOFEDUC
						$pdf =& new FPDI('P','in',array(10,7.5));	

						//Set font
						$pdf->SetFont('Times','B',36);
						
						$pagecount = $pdf->setSourceFile('../../admin/protected/scorm_certificate/'.$certificate_file_name);
							
						
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
						if (!$result_additional_field = $mysqli->query('SELECT 
						*
						FROM 
						scorm_certificate_additional_field_value 
						WHERE id_scorm_certificate_product = "'.$row['id'].'"')) throw new Exception('An error occured while trying to lookup scorm_certificate_product table.');
						if($result_additional_field->num_rows){
							while ($row_additional_field = $result_additional_field->fetch_assoc()) {
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
										$course_name = $row_additional_field['value'];
									break;
									
								}
							}
						}

						$pdfdoc = $pdf->Output('', 'S');
	
					break;*/
				}
				
				
				$message_email = '<p>Bonjour <strong>'.$customer_name.'</strong>,</p>
<p> L\'Académie de l\'Apothicaire est heureuse de vous faire parvenir votre attestation de réussite pour le cours <strong>'.$course_name.'</strong>.<br />
  <br />
  Pour plus de détails sur les modalités d\'accréditation, consultez la page <a href="http://www.apothicaire.ca/fr/page/evaluations-et-accreditations" target="_blank">Évaluations et accréditations</a>.<br />
  <br />
  Nous sommes heureux de vous compter parmi nos clients. Si vous avez des commentaires sur nos formations, n\'hésitez pas à nous les faire parvenir à commentaires@apothicaire.ca.<br />
  <br />
  N\'oubliez pas de visitez régulièrement notre <a href="http://www.apothicaire.ca/fr/catalog/professionnels-de-la-sante/formation-continue" target="_blank">catalogue</a>, nous y ajoutons périodiquement de nouvelles formations.<br />
  <br />
  Merci de nous accorder votre confiance,<br />
  <br />
  L\'équipe de l\'Académie de l\'Apothicaire</p>';
				
				include_mailer();
									
				// send email to customer with attached certificate
				$mail = new PHPMailer(); // defaults to using php "mail()"
				$mail->CharSet = 'UTF-8';
				
				// text only
				//$mail->IsHTML(false);
				
				$mail->SetFrom($config_site['company_email'], $config_site['company_company']);
				
				$mail->AddAddress($customer_email);
				
				$mail->Subject = 'Attestation de réussite - ' . $course_name;
  
				
				$mail->MsgHTML($message_email);
				
				$mail->AddStringAttachment($pdfdoc,'Attestation.pdf');
				
				$sendmail_failed = $mail->Send() ? 0:1;

			}/*else{
				/echo 'non';	
			}*/
		}	
	}
}
?>