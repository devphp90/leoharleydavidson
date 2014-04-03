<?php

class ReportsController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{					
		// display the form
		$this->render('index');	
	}
	
	public function actionIndex_options()
	{	
		$this->renderPartial('index_options');	
	}			
	
	
	/************************************************************
	*															*
	*															*
	*						SCORM REPORTS						*
	*															*
	*															*
	************************************************************/
	
	public function actionScorm_report()
	{	
		$this->render('scorm_report');	
	}		
	
	public function actionScorm_report_options()
	{	
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$sql = 'SELECT 
		product_downloadable_files.id,
		product_downloadable_files.name
		FROM
		product_downloadable_files
		INNER JOIN 
		product_downloadable_files_description	
		ON
		(product_downloadable_files.id = product_downloadable_files_description.id_product_downloadable_files AND product_downloadable_files_description.language_code = :language_code)
		WHERE
		product_downloadable_files.type = "ADL SCORM 1.2"
		ORDER BY 
		product_downloadable_files.name ASC';
		$command=$connection->createCommand($sql);				
		
		$courses = $command->queryAll(true, array(':language_code'=>Yii::app()->language));
		
	
		$this->renderPartial('scorm_report_options',array('courses'=>$courses));	
	}

	public function actionXml_list_courses_select($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			$where = array();
			$params = array();	
			
			// filters
			
			// customer_name
			if (isset($filters['course_name']) && !empty($filters['course_name'])) {
				$where[] = '(product_downloadable_files.name LIKE CONCAT("%",:course_name,"%"))';
				$params[':course_name']=$filters['course_name'];
			}
			
			$params[':language_code'] = Yii::app()->language;
			
			$sql = "SELECT 
			COUNT(*) AS total  
			FROM
			product_downloadable_files
			INNER JOIN 
			product_downloadable_files_description	
			ON
			(product_downloadable_files.id = product_downloadable_files_description.id_product_downloadable_files AND product_downloadable_files_description.language_code = :language_code)
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
			
				
			//if this is the first query - get total number of records in the query result
			if($posStart==0){
				/* Select queries return a resultset */
				$command=$connection->createCommand($sql);
				$row = $command->queryRow(true, $params);
				$totalCount = $row['total'];		
			}
			
			
			//create query 
			
			$sql = "SELECT 
			product_downloadable_files.id,
			product_downloadable_files.name
			FROM
			product_downloadable_files
			INNER JOIN 
			product_downloadable_files_description	
			ON
			(product_downloadable_files.id = product_downloadable_files_description.id_product_downloadable_files AND product_downloadable_files_description.language_code = :language_code)
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
				
			// sorting

			$sql.="ORDER BY product_downloadable_files.name ASC";	
			
			//add limits to query to get only rows necessary for the output
			$sql.= " LIMIT ".$posStart.",".$count;
			
			$command=$connection->createCommand($sql);
			
			
			//set content type and xml tag
			header("Content-type:text/xml");
			
			//output data in XML format   
			echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
			
			// Cycle through results
			foreach ($command->queryAll(true, $params) as $row) {
				echo '<row id="'.$row['id'].'">
				<cell type="ro"><![CDATA['.$row['name'].']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
	
	}		
	
	public function actionSelect_course()
	{
		
		// current product
		$id = $_POST['id'];
		
		$params[':language_code'] = Yii::app()->language;
				
		$connection=Yii::app()->db;
		$sql = "SELECT 
		product_downloadable_files.id,
		product_downloadable_files.name
		FROM
		product_downloadable_files
		INNER JOIN 
		product_downloadable_files_description	
		ON
		(product_downloadable_files.id = product_downloadable_files_description.id_product_downloadable_files AND product_downloadable_files_description.language_code = :language_code)
		WHERE
		product_downloadable_files.id = " . $id;	
		
		/* Select queries return a resultset */
		$command=$connection->createCommand($sql);
		if ($row = $command->queryRow(true, $params)) {
			$output = array('id'=>$row['id'],'name'=>$row['name']);
		}else{
			$output = array('error'=>1);	
		}

		echo CJSON::encode($output);
			
	}
	
	/**
	 * This is the action to get an XML list 
	 */
	public function actionXml_list_scorm_report($id)
	{		

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		
		$params=array(':id_course' => $id);
		
		$sql = 'SELECT 
		IF(customer.id IS NOT NULL,customer.id,c.id) AS id,
		IF(customer.id IS NOT NULL,customer.firstname,c.firstname) AS firstname,
		IF(customer.id IS NOT NULL,customer.lastname,c.lastname) AS lastname,
		IF(customer.id IS NOT NULL,customer.email,c.email) AS email
		FROM		
		orders_item_product_downloadable_files AS oipdf

		LEFT JOIN
		(orders_item_product CROSS JOIN orders_item CROSS JOIN orders CROSS JOIN customer) 
		ON
		(oipdf.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id AND orders.id_customer = customer.id)
		
		LEFT JOIN
		(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o CROSS JOIN customer AS c) 
		ON
		(oipdf.id_orders_item_product = oip.id AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id AND o.id_customer = c.id)
		
		WHERE 
		oipdf.id_product_downloadable_files = :id_course
		AND
		(
			(orders.id IS NOT NULL AND orders.status > 0)
			OR
			(o.id IS NOT NULL AND o.status > 0)
		)
		ORDER BY 
		firstname ASC,
		lastname ASC';
				
		$command=$connection->createCommand($sql);
		
		$rows = $command->queryAll(true, $params);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.sizeof($rows).'">';
		
		// Cycle through results
		foreach ($rows as $row) {
			// get count
			$criteria=new CDbCriteria; 
			$criteria->condition='id_course=:id_course AND id_customer=:id_customer'; 
			$criteria->params=array(':id_course'=>$id,':id_customer'=>$row['id']);	
			
			$count = Tbl_CustomerCoursesScorm::model()->count($criteria);
			
			// get last result
			$criteria->order='score DESC';
			$criteria->limit = 1;		
			
			$result = Tbl_CustomerCoursesScorm::model()->find($criteria);
			
			switch ($result->lesson_status) {
				//passed: Necessary number of objectives in the SCO were mastered, or the necessary score was achieved. Student is considered to have completed the SCO and passed.
				case 'passed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_PASSED');
					break;
				//completed: The SCO may or may note be passed, but all the elements in the SCO were experienced by the student. The student is considered to have completed the SCO. For instance, passing may depend on a certain score known to the LMS system. The SCO knows the raw score, but not whether that raw score was high enough to pass. 
				case 'completed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_COMPLETED');
					break;				
				//failed: The SCO was not passed. All the SCO elements may or may not have been completed by the student. The student is considered to have completed the SCO and failed. 
				case 'failed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_FAILED');
					break;				
				//incomplete: The SCO was begun but not finished. 
				case 'incomplete':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_INCOMPLETE');
					break;				
				//browsed: The student launched the SCO with a LMS mode of "browse" on the initial attempt. 
				case 'browsed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_BROWSED');
					break;				
				//not attempted: Incomplete implies that the student made an attempt to perform the SCO, but for some reason was unable to finish it. Not attempted means that the student did not even begin the SCO. Maybe they just read the table of contents, or SCO abstract and decided they were not ready. Any algorithm within the SCO may be used to determine when the SCO moves from "not attempted" to "incomplete". 	
				default:
				case 'not attempted':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_NOT_ATTEMPTED');
					break;				
			}
			
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['firstname'].' '.$row['lastname'].']]></cell>
			<cell type="ro"><![CDATA['.$row['email'].']]></cell>
			<cell type="ro"><![CDATA['.$count.']]></cell>
			<cell type="ro"><![CDATA['.$lesson_status.']]></cell>
			<cell type="ro"><![CDATA['.$result->score.']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to get an XML list 
	 */
	public function actionXml_list_customer_course_attempt($id,$id_course)
	{		

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		$id_course=(int)$id_course;
		
		$params=array(':id'=>$id,':id_course' => $id_course);
		
		$sql = 'SELECT 
		customer_courses_scorm.id,
		customer_courses_scorm.score,
		customer_courses_scorm.lesson_status,
		customer_courses_scorm.date_start		
		FROM		
		customer_courses_scorm
		
		WHERE 
		customer_courses_scorm.id_customer = :id
		AND
		customer_courses_scorm.id_course = :id_course
		ORDER BY 
		customer_courses_scorm.date_start DESC';
				
		$command=$connection->createCommand($sql);
		
		$rows = $command->queryAll(true, $params);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.sizeof($rows).'">';
		
		// Cycle through results
		foreach ($rows as $row) {
			switch ($row['lesson_status']) {
				//passed: Necessary number of objectives in the SCO were mastered, or the necessary score was achieved. Student is considered to have completed the SCO and passed.
				case 'passed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_PASSED');
					break;
				//completed: The SCO may or may note be passed, but all the elements in the SCO were experienced by the student. The student is considered to have completed the SCO. For instance, passing may depend on a certain score known to the LMS system. The SCO knows the raw score, but not whether that raw score was high enough to pass. 
				case 'completed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_COMPLETED');
					break;				
				//failed: The SCO was not passed. All the SCO elements may or may not have been completed by the student. The student is considered to have completed the SCO and failed. 
				case 'failed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_FAILED');
					break;				
				//incomplete: The SCO was begun but not finished. 
				case 'incomplete':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_INCOMPLETE');
					break;				
				//browsed: The student launched the SCO with a LMS mode of "browse" on the initial attempt. 
				case 'browsed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_BROWSED');
					break;				
				//not attempted: Incomplete implies that the student made an attempt to perform the SCO, but for some reason was unable to finish it. Not attempted means that the student did not even begin the SCO. Maybe they just read the table of contents, or SCO abstract and decided they were not ready. Any algorithm within the SCO may be used to determine when the SCO moves from "not attempted" to "incomplete". 	
				default:
				case 'not attempted':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_NOT_ATTEMPTED');
					break;				
			}
			
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['date_start'].']]></cell>
			<cell type="ro"><![CDATA['.$lesson_status.']]></cell>
			<cell type="ro"><![CDATA['.$row['score'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	public function actionScorm_participant_report()
	{	
		$this->render('scorm_participant_report');	
	}		
	
	public function actionScorm_participant_report_options()
	{	
		$this->renderPartial('scorm_participant_report_options');	
	}	
	
	public function actionDownload_scorm_certificate()
	{	
		$this->renderPartial('download_scorm_certificate');	
	}		
	
	/**
	 * This is the action to get an XML list 
	 */
	public function actionXml_list_scorm_participant_report($id)
	{		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$id;
		
		$params=array(':id'=>$id);
		
		$sql = 'SELECT 
		customer_courses_scorm.id,
		customer_courses_scorm.id_course,
		customer_courses_scorm.score,
		customer_courses_scorm.lesson_status,
		customer_courses_scorm.date_start,
		customer_courses_scorm.date_end,
		customer_courses_scorm.data,
		product_downloadable_files.name,
		customer.language_code AS customer_language,
		customer.id AS id_customer		
		FROM		
		customer_courses_scorm
		
		INNER JOIN 
		product_downloadable_files
		ON
		(customer_courses_scorm.id_course = product_downloadable_files.id)
		
		INNER JOIN 
		customer
		ON
		(customer_courses_scorm.id_customer = customer.id)
		
		WHERE 
		customer_courses_scorm.id_customer = :id
		ORDER BY 
		customer_courses_scorm.date_start DESC';
				
		$command=$connection->createCommand($sql);
		
		$rows = $command->queryAll(true, $params);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.sizeof($rows).'">';
		
		// Cycle through results
		foreach ($rows as $row) {
			switch ($row['lesson_status']) {
				//passed: Necessary number of objectives in the SCO were mastered, or the necessary score was achieved. Student is considered to have completed the SCO and passed.
				case 'passed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_PASSED');
					break;
				//completed: The SCO may or may note be passed, but all the elements in the SCO were experienced by the student. The student is considered to have completed the SCO. For instance, passing may depend on a certain score known to the LMS system. The SCO knows the raw score, but not whether that raw score was high enough to pass. 
				case 'completed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_COMPLETED');
					break;				
				//failed: The SCO was not passed. All the SCO elements may or may not have been completed by the student. The student is considered to have completed the SCO and failed. 
				case 'failed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_FAILED');
					break;				
				//incomplete: The SCO was begun but not finished. 
				case 'incomplete':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_INCOMPLETE');
					break;				
				//browsed: The student launched the SCO with a LMS mode of "browse" on the initial attempt. 
				case 'browsed':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_BROWSED');
					break;				
				//not attempted: Incomplete implies that the student made an attempt to perform the SCO, but for some reason was unable to finish it. Not attempted means that the student did not even begin the SCO. Maybe they just read the table of contents, or SCO abstract and decided they were not ready. Any algorithm within the SCO may be used to determine when the SCO moves from "not attempted" to "incomplete". 	
				default:
				case 'not attempted':
					$lesson_status = Yii::t('views/reports/scorm_report','LABEL_LESSON_STATUS_NOT_ATTEMPTED');
					break;				
			}

			// each client must have his own certificate configuration (reports_scorm_certificate.php)
			if(Yii::app()->params['scorm_certificate']){
				$link_certificate = 0;

				if($row['id_course']){
					
					$data = !empty($row['data']) ? unserialize(base64_decode($row['data'])):array();
					
					$sql = 'SELECT 
					scorm_certificate_product.id
					FROM 
					scorm_certificate_product 
					INNER JOIN 
					scorm_certificate
					ON
					scorm_certificate_product.id_scorm_certificate = scorm_certificate.id
					WHERE scorm_certificate_product.id_product_downloadable_files = "'.$row['id_course'].'"';
							
					$command_scorm_certificate_product=$connection->createCommand($sql);
				
					if($rows_scorm_certificate_product = $command_scorm_certificate_product->queryAll(true)){
						
						// Verify id_customer
						if($row['id_customer']){

							$sql = 'SELECT 
							customer.id
							FROM 
							customer 
							WHERE customer.id = "'.$row['id_customer'].'"';
									
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
											
											// Verify if it's the score condition else continue normally
											if($row_condition['id_custom_fields']==-1){
												if(!(($data['cmi.core.score.raw'] >= $row_condition['score_from']) && ($data['cmi.core.score.raw'] <= $row_condition['score_to']))){
													$respect_condition = false;
													break;
												}
											}else if(!isset($arr_customer_custom_field[$row_condition['id_custom_fields']][$row_condition['id_custom_fields_option']])){
													$respect_condition = false;
													
													break;
												
											}
										}
									}
									
									// If True then end loop
									if($respect_condition == true){
										break;
									}
								}
							}

							// We show the link if $respect_condition is true
							if($respect_condition == true){
								$link_certificate = 1;
							}
						}	
					}
				}
			}

			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['name'].(($link_certificate && Yii::app()->params['scorm_certificate'] && $row['date_end']!="0000-00-00 00:00:00")?'<br /><a href="'.CController::createUrl('download_scorm_certificate').'?id_course='.$row['id_course'].'&id_customer='.$row['id_customer'].'" style="text-decoration:none">'.Yii::t('controllers/ReportsController','LABEL_SEE_CERTIFICATE').'</a>':'').']]></cell>			
			<cell type="ro"><![CDATA['.$row['date_start'].']]></cell>
			<cell type="ro"><![CDATA['.$lesson_status.']]></cell>
			<cell type="ro"><![CDATA['.$row['score'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
		
	public function actionCustomer_report()
	{	
		
		// Verify if custom field exist to give the possibilities to filter by custom fields
		$count = Tbl_CustomFields::model()->count();
		
		$this->render('customer_report',array('count'=>$count));	
	}		
	
	public function actionCustomer_report_options()
	{	
		$this->renderPartial('customer_report_options');	
	}	
	
	public function actionCustomer_report_add_custom_fields()
	{	
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$sql = 'SELECT
		custom_fields.id,
		custom_fields_description.name,
		custom_fields.type
		FROM
		custom_fields
		INNER JOIN 
		custom_fields_description
		ON
		(custom_fields.id = custom_fields_description.id_custom_fields AND custom_fields_description.language_code = :language_code) 
		WHERE
		custom_fields.form = 0 
		AND
		custom_fields.type IN (0, 1, 2, 5)
		ORDER BY 
		custom_fields.sort_order ASC';	
		
		$command=$connection->createCommand($sql);
		
		// Cycle through results
		$custom_fields= $command->queryAll(true, array(':language_code'=>Yii::app()->language));		
	
		$this->renderPartial('customer_report_add_custom_fields',array('custom_fields'=>$custom_fields));	
	}		
	
	public function actionGet_custom_field_options($id,$type)
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection

		$id=(int)$id;
		$type=(int)$type;
		
		$output='<select id="select_custom_field_option">';		
		
		switch ($type) {
			// single checkbox
			case 0:
				$output .= '<option value="'.$id.'">'.Yii::t('global','LABEL_YES').'</option><option value="0">'.Yii::t('global','LABEL_NO').'</option>';
			
				break;	
			default:
		
				$sql = 'SELECT 
				custom_fields_option.id,
				custom_fields_option_description.name
				FROM
				custom_fields_option
				INNER JOIN
				custom_fields_option_description
				ON
				(custom_fields_option.id = custom_fields_option_description.id_custom_fields_option AND custom_fields_option_description.language_code = :language_code) 
				
				INNER JOIN
				custom_fields
				ON
				(custom_fields_option.id_custom_fields = custom_fields.id)
				WHERE
				custom_fields_option.id_custom_fields = :id
				ORDER BY
				custom_fields_option.sort_order ASC';	
				
				$command=$connection->createCommand($sql);		
					
					
				foreach ($command->queryAll(true, array(':language_code'=>Yii::app()->language,':id'=>$id)) as $row) {
					$output .= '<option value="'.$row['id'].'">'.$row['name'].'</option>';
				}						
				break;
		}		
	
		$output.='</select>';
		
		echo $output;
	}
		
	/**
	 * This is the action to get an XML list 
	 */
	public function actionXml_list_customer_report()
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language);
		$custom_fields=$_GET['custom_fields'];
		$custom_fields = is_array($custom_fields) ? $custom_fields:array();
		
		// filters

		//create query 
		$sql = "SELECT 
		customer.*,
		CONCAT_WS(' ',customer.firstname,customer.lastname) AS name,
		customer_address.telephone,
		customer_address.zip,
		country_description.name AS country_name,
		state_description.name AS state_name,
		customer_type.name AS customer_type
		FROM 
		customer 
		LEFT JOIN 
		customer_type
		ON
		(customer.id_customer_type = customer_type.id)
		LEFT JOIN 
		customer_address
		ON
		(customer.id = customer_address.id_customer AND customer_address.default_billing=1)
		LEFT JOIN
		country_description 
		ON 
		(customer_address.country_code = country_description.country_code AND country_description.language_code = :language_code)	
		LEFT JOIN
		state_description
		ON 
		(customer_address.state_code = state_description.state_code AND state_description.language_code = :language_code)";
		
		$i=1;
		foreach ($custom_fields as $id_custom_fields => $values) {
			
			foreach ($values as $id_custom_fields_option) {
				$sql .= "LEFT JOIN 
				customer_custom_fields_value AS cfv".$i."
				ON
				(customer.id = cfv".$i.".id_customer AND cfv".$i.".id_custom_fields = '".$id_custom_fields."' AND cfv".$i.".id_custom_fields_option = '".($id_custom_fields_option ? $id_custom_fields_option:$id_custom_fields)."')";
				
				++$i;
			}
		}	
		
		$i=1;
		$sql .= "WHERE 1=1 ";
		foreach ($custom_fields as $id_custom_fields => $values) {
			
			
			$where = array();
			foreach ($values as $id_custom_fields_option) {
				if (!$id_custom_fields_option) $where[] = "cfv".$i.".id_customer IS NULL";
				else $where[] = "cfv".$i.".id_customer IS NOT NULL";
				
				++$i;
			}
			
			$sql .= " AND (".implode(' OR ',$where).") ";
		}			
		
		$sql.=" ORDER BY customer.id ASC";
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {			
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['email'].']]></cell>
			<cell type="ro"><![CDATA['.$row['customer_type'].']]></cell>			
			<cell type="ro"><![CDATA['.$row['telephone'].']]></cell>
			<cell type="ro"><![CDATA['.$row['country_name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['state_name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['zip'].']]></cell>
			<cell type="ro"><![CDATA['.$row['date_created'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}				
	
	/************************************************************
	*															*
	*															*
	*						COUPON REPORTS						*
	*															*
	*															*
	************************************************************/
	
	public function actionCoupon_report()
	{	
		$this->render('coupon_report');	
	}		
	
	public function actionCoupon_report_options()
	{	
		$this->renderPartial('coupon_report_options',array());	
	}			
	
	/**
	 * This is the action to get an XML list 
	 */
	public function actionXml_list_coupon_report($coupon_code)
	{		

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$coupon_code=trim($coupon_code);
		
		$params=array(':coupon_code' => $coupon_code);
		
		$sql = 'SELECT 
		customer.id,
		customer.firstname,
		customer.lastname,
		customer.email,
		orders.id AS id_orders,
		orders.date_order,
		orders.total,
		(SELECT IFNULL((SELECT SUM(orders_discount_item_product.amount) FROM orders_discount_item_product WHERE orders_discount_item_product.id_orders_discount = orders_discount.id),0)+IFNULL((SELECT SUM(orders_discount_item_option.amount) FROM orders_discount_item_option WHERE orders_discount_item_option.id_orders_discount = orders_discount.id),0)) AS total_discount
		FROM		
		orders_discount
		
		INNER JOIN 
		orders
		ON
		(orders_discount.id_orders = orders.id)
		
		INNER JOIN
		customer 
		ON
		(orders.id_customer = customer.id)
		
		WHERE 
		orders_discount.id_rebate_coupon = :coupon_code
		AND
		orders.status > 0
		ORDER BY 
		id_orders ASC';
				
		$command=$connection->createCommand($sql);
		
		$rows = $command->queryAll(true, $params);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.sizeof($rows).'">';
		
		// Cycle through results
		foreach ($rows as $row) {
			echo '<row id="'.$row['id_orders'].'">
			<cell type="ro"><![CDATA['.$row['id_orders'].']]></cell>
			<cell type="ro"><![CDATA['.$row['date_order'].']]></cell>			
			<cell type="ro"><![CDATA['.$row['firstname'].' '.$row['lastname'].']]></cell>
			<cell type="ro"><![CDATA['.$row['email'].']]></cell>
			<cell type="ro"><![CDATA['.$row['total'].']]></cell>
			<cell type="ro"><![CDATA['.$row['total_discount'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/**
	 * This is the action to get an XML list of rebate/coupons
	 */
	public function actionXml_list_rebate_coupon_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		// filters
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'rebate_coupon.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// code
		if (isset($filters['coupon_code']) && !empty($filters['coupon_code'])) {
			$where[] = 'rebate_coupon.coupon_code LIKE CONCAT("%",:coupon_code,"%")';
			$params[':coupon_code']=$filters['coupon_code'];
		}		
			
		// discount type
		if (isset($filters['discount_type'])) {
			$where[] = 'rebate_coupon.type = :discount_type';				
			$params[':discount_type']=$filters['discount_type'];
		}						
		
		// Type
		if (isset($filters['type'])) {
			switch ($filters['type']) {
				case 0:
				case 1:					
					$where[] = 'rebate_coupon.coupon = :type';				
					$params[':type']=$filters['type'];
					break;
			}
		}								
		
		$sql = "SELECT 
		COUNT(rebate_coupon.id) AS total 
		FROM 
		rebate_coupon 		
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		rebate_coupon.id,
		rebate_coupon.name,
		rebate_coupon.coupon_code,
		rebate_coupon.coupon,
		rebate_coupon.start_date,
		rebate_coupon.end_date,
		rebate_coupon.discount_type,
		rebate_coupon.discount,
		rebate_coupon.active,
		rebate_coupon.type
		FROM 
		rebate_coupon 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// type
		if (isset($sort_col[0]) && !empty($sort_col[0])) {	
			$direct = $sort_col[0] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.coupon ".$direct;
		// discount type
		} else if (isset($sort_col[1]) && !empty($sort_col[1])) {
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.type ".$direct;						
			
		// nom
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {	
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.name ".$direct;
			
		// code
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY rebate_coupon.coupon_code ".$direct;
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(rebate_coupon.name LIKE CONCAT(:name,'%'),0,1) ASC, rebate_coupon.name ASC";
			} else if (isset($filters['coupon_code']) && !empty($filters['coupon_code'])) {
				$sql.=" ORDER BY IF(rebate_coupon.coupon_code LIKE CONCAT(:coupon_code,'%'),0,1) ASC, rebate_coupon.name ASC";
			} else {
				$sql.=" ORDER BY rebate_coupon.id ASC";
			}
			
		}		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';

		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			switch($row['type']){
				case 0:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_0');
				break;
				case 1:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_1');
				break;
				case 2:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_2');
				break;
				case 3:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_3');
				break;
				case 4:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_4');
				break;
				case 5:
					$type_name = Yii::t('controllers/RebatecouponController','LABEL_REBATE_5');
				break;
				
			}
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ro"><![CDATA['.($row['coupon']?Yii::t('global','LABEL_COUPON'):Yii::t('global','LABEL_REBATE')).']]></cell>
			<cell type="ro"><![CDATA['.$type_name.']]></cell>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['coupon_code'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/************************************************************
	*															*
	*															*
	*						SALES REPORTS						*
	*															*
	*															*
	************************************************************/
	
	public function actionSales_report()
	{	
		$this->render('sales_report');	
	}		
	
	/**
	 * This is the action to get an XML list of orders
	 */
	public function actionXml_list_sales_report($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('orders.status = 7');
		$params=array();
		
		// filters
		
		// order_no
		if (isset($filters['invoice_no']) && !empty($filters['invoice_no'])) {
			$where[] = 'orders.id LIKE CONCAT("%",:invoice_no,"%")';
			$params[':invoice_no']=$filters['invoice_no'];
		}
		
		// date_order start
		if (isset($filters['date_order_start']) && !empty($filters['date_order_start'])) {
			$where[] = 'orders.date_order >= :date_order_start';
			$params[':date_order_start']=$filters['date_order_start'];
		}	
		
		// date_order end
		if (isset($filters['date_order_end']) && !empty($filters['date_order_end'])) {
			$where[] = 'orders.date_order <= :date_order_end';
			$params[':date_order_end']=$filters['date_order_end'];
		}	
		
		// date_order_payment start
		if (isset($filters['date_order_payment_start']) && !empty($filters['date_order_payment_start'])) {
			$where[] = 'orders.date_payment >= :date_order_payment_start';
			$params[':date_order_payment_start']=$filters['date_order_payment_start'];
		}	
		
		// date_order_payment end
		if (isset($filters['date_order_payment_end']) && !empty($filters['date_order_payment_end'])) {
			$where[] = 'orders.date_payment <= :date_order_payment_end';
			$params[':date_order_payment_end']=$filters['date_order_payment_end'];
		}	
		
	
		
		// subtotal
		if (isset($filters['subtotal']) && !empty($filters['subtotal'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['subtotal'])) {
				$where[] = 'orders.subtotal <= :subtotal';
				$params[':subtotal']=ltrim($filters['subtotal'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['subtotal'])) {
				$where[] = 'orders.subtotal >= :subtotal';
				$params[':subtotal']=ltrim($filters['subtotal'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['subtotal'])) {		
				$where[] = 'orders.subtotal < :subtotal';
				$params[':subtotal']=ltrim($filters['subtotal'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['subtotal'])) {		
				$where[] = 'orders.subtotal > :subtotal';
				$params[':subtotal']=ltrim($filters['subtotal'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['subtotal'])) {		
				$where[] = 'orders.subtotal = :subtotal';
				$params[':subtotal']=ltrim($filters['subtotal'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['subtotal'])) {
				$search = explode('..',$filters['total']);
				$where[] = 'orders.subtotal BETWEEN :total_start AND :total_end';
				$params[':total_start']=$search[0];
				$params[':total_end']=$search[1];
			// N				
			} else {
				$where[] = 'orders.subtotal = :subtotal';
				$params[':subtotal']=$filters['subtotal'];
			}
		}					
		
		// shipping
		if (isset($filters['shipping']) && !empty($filters['shipping'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['shipping'])) {
				$where[] = 'orders.shipping <= :shipping';
				$params[':shipping']=ltrim($filters['shipping'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['shipping'])) {
				$where[] = 'orders.shipping >= :shipping';
				$params[':shipping']=ltrim($filters['shipping'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['shipping'])) {		
				$where[] = 'orders.shipping < :shipping';
				$params[':shipping']=ltrim($filters['shipping'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['shipping'])) {		
				$where[] = 'orders.shipping > :shipping';
				$params[':shipping']=ltrim($filters['shipping'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['shipping'])) {		
				$where[] = 'orders.shipping = :shipping';
				$params[':shipping']=ltrim($filters['shipping'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['shipping'])) {
				$search = explode('..',$filters['total']);
				$where[] = 'orders.shipping BETWEEN :total_start AND :total_end';
				$params[':total_start']=$search[0];
				$params[':total_end']=$search[1];
			// N				
			} else {
				$where[] = 'orders.shipping = :shipping';
				$params[':shipping']=$filters['shipping'];
			}
		}
		
		// cost
		if (isset($filters['cost']) && !empty($filters['cost'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['cost'])) {
				$where[] = '(SELECT SUM(oip.qty*oip.cost_price) FROM orders_item_product AS oip INNER JOIN orders_item AS oi ON (oip.id_orders_item = oi.id) WHERE oi.id_orders = orders.id) <= :subtotal';
				$params[':cost']=ltrim($filters['cost'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['cost'])) {
				$where[] = '(SELECT SUM(oip.qty*oip.cost_price) FROM orders_item_product AS oip INNER JOIN orders_item AS oi ON (oip.id_orders_item = oi.id) WHERE oi.id_orders = orders.id) >= :cost';
				$params[':cost']=ltrim($filters['cost'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['cost'])) {		
				$where[] = '(SELECT SUM(oip.qty*oip.cost_price) FROM orders_item_product AS oip INNER JOIN orders_item AS oi ON (oip.id_orders_item = oi.id) WHERE oi.id_orders = orders.id) < :cost';
				$params[':cost']=ltrim($filters['cost'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['cost'])) {		
				$where[] = '(SELECT SUM(oip.qty*oip.cost_price) FROM orders_item_product AS oip INNER JOIN orders_item AS oi ON (oip.id_orders_item = oi.id) WHERE oi.id_orders = orders.id) > :cost';
				$params[':cost']=ltrim($filters['cost'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['cost'])) {		
				$where[] = '(SELECT SUM(oip.qty*oip.cost_price) FROM orders_item_product AS oip INNER JOIN orders_item AS oi ON (oip.id_orders_item = oi.id) WHERE oi.id_orders = orders.id) = :cost';
				$params[':cost']=ltrim($filters['cost'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['cost'])) {
				$search = explode('..',$filters['total']);
				$where[] = '(SELECT SUM(oip.qty*oip.cost_price) FROM orders_item_product AS oip INNER JOIN orders_item AS oi ON (oip.id_orders_item = oi.id) WHERE oi.id_orders = orders.id) BETWEEN :total_start AND :total_end';
				$params[':total_start']=$search[0];
				$params[':total_end']=$search[1];
			// N				
			} else {
				$where[] = '(SELECT SUM(oip.qty*oip.cost_price) FROM orders_item_product AS oip INNER JOIN orders_item AS oi ON (oip.id_orders_item = oi.id) WHERE oi.id_orders = orders.id) = :cost';
				$params[':cost']=$filters['cost'];
			}
		}		
		
		// taxes
		if (isset($filters['taxes']) && !empty($filters['taxes'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['taxes'])) {
				$where[] = 'orders.taxes <= :taxes';
				$params[':taxes']=ltrim($filters['taxes'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['taxes'])) {
				$where[] = 'orders.taxes >= :taxes';
				$params[':taxes']=ltrim($filters['taxes'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['taxes'])) {		
				$where[] = 'orders.taxes < :taxes';
				$params[':taxes']=ltrim($filters['taxes'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['taxes'])) {		
				$where[] = 'orders.taxes > :taxes';
				$params[':taxes']=ltrim($filters['taxes'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['taxes'])) {		
				$where[] = 'orders.taxes = :taxes';
				$params[':taxes']=ltrim($filters['taxes'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['taxes'])) {
				$search = explode('..',$filters['total']);
				$where[] = 'orders.taxes BETWEEN :total_start AND :total_end';
				$params[':total_start']=$search[0];
				$params[':total_end']=$search[1];
			// N				
			} else {
				$where[] = 'orders.taxes = :taxes';
				$params[':taxes']=$filters['taxes'];
			}
		}		
		
		// discounts
		if (isset($filters['discounts']) && !empty($filters['discounts'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['discounts'])) {
				$where[] = '(IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_product AS oip CROSS JOIN orders_discount_item_product AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_product) WHERE oi.id_orders = orders.id),0)+IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_option AS oip CROSS JOIN orders_discount_item_option AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_option) WHERE oi.id_orders = orders.id),0)) <= :discounts';
				$params[':discounts']=ltrim($filters['discounts'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['discounts'])) {
				$where[] = '(IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_product AS oip CROSS JOIN orders_discount_item_product AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_product) WHERE oi.id_orders = orders.id),0)+IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_option AS oip CROSS JOIN orders_discount_item_option AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_option) WHERE oi.id_orders = orders.id),0)) >= :discounts';
				$params[':discounts']=ltrim($filters['discounts'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['discounts'])) {		
				$where[] = '(IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_product AS oip CROSS JOIN orders_discount_item_product AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_product) WHERE oi.id_orders = orders.id),0)+IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_option AS oip CROSS JOIN orders_discount_item_option AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_option) WHERE oi.id_orders = orders.id),0)) < :discounts';
				$params[':discounts']=ltrim($filters['discounts'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['discounts'])) {		
				$where[] = '(IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_product AS oip CROSS JOIN orders_discount_item_product AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_product) WHERE oi.id_orders = orders.id),0)+IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_option AS oip CROSS JOIN orders_discount_item_option AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_option) WHERE oi.id_orders = orders.id),0)) > :discounts';
				$params[':discounts']=ltrim($filters['discounts'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['discounts'])) {		
				$where[] = '(IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_product AS oip CROSS JOIN orders_discount_item_product AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_product) WHERE oi.id_orders = orders.id),0)+IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_option AS oip CROSS JOIN orders_discount_item_option AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_option) WHERE oi.id_orders = orders.id),0)) = :discounts';
				$params[':discounts']=ltrim($filters['discounts'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['discounts'])) {
				$search = explode('..',$filters['total']);
				$where[] = '(IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_product AS oip CROSS JOIN orders_discount_item_product AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_product) WHERE oi.id_orders = orders.id),0)+IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_option AS oip CROSS JOIN orders_discount_item_option AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_option) WHERE oi.id_orders = orders.id),0)) BETWEEN :total_start AND :total_end';
				$params[':total_start']=$search[0];
				$params[':total_end']=$search[1];
			// N				
			} else {
				$where[] = '(IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_product AS oip CROSS JOIN orders_discount_item_product AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_product) WHERE oi.id_orders = orders.id),0)+IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_option AS oip CROSS JOIN orders_discount_item_option AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_option) WHERE oi.id_orders = orders.id),0)) = :discounts';
				$params[':discounts']=$filters['discounts'];
			}
		}	
		
		// payment_method
		if (isset($filters['payment_method'])) {
			if (is_numeric($filters['payment_method'])) {
				$where[] = 'orders.payment_method = :payment_method';				
				$params[':payment_method']=$filters['payment_method'];
			}
		}		
		
		$sql = "SELECT 
		COUNT(orders.id) AS total 
		FROM 
		orders
							
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		orders.*,
		(SELECT SUM(oip.qty*oip.cost_price) FROM orders_item_product AS oip INNER JOIN orders_item AS oi ON (oip.id_orders_item = oi.id) WHERE oi.id_orders = orders.id) AS cost_price,
		(IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_product AS oip CROSS JOIN orders_discount_item_product AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_product) WHERE oi.id_orders = orders.id),0)+IFNULL((SELECT SUM(d.amount) FROM orders_item AS oi INNER JOIN (orders_item_option AS oip CROSS JOIN orders_discount_item_option AS d) ON (oi.id = oip.id_orders_item AND oip.id = d.id_orders_item_option) WHERE oi.id_orders = orders.id),0)) AS discounts
		
		
		FROM 
		orders
		
		
		
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// date_order
		if (isset($sort_col[0]) && !empty($sort_col[0])) {	
			$direct = $sort_col[0] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY date_order ".$direct;
		// date_payment
		} else if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY date_payment ".$direct;
		// order_no
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY id ".$direct;	
		// subtotal
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY subtotal ".$direct;				
		// shipping
		} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY shipping ".$direct;																			
		// cost_price
		} else if (isset($sort_col[5]) && !empty($sort_col[5])) {
			$direct = $sort_col[5] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY cost_price ".$direct;																							
		// taxes
		} else if (isset($sort_col[6]) && !empty($sort_col[6])) {
			$direct = $sort_col[6] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY taxes ".$direct;	
		// discounts
		} else if (isset($sort_col[7]) && !empty($sort_col[7])) {
			$direct = $sort_col[7] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY discounts ".$direct;									
		} else {
			$sql.=" ORDER BY orders.id DESC";
		}		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['date_order'].']]></cell>	
			<cell type="ro"><![CDATA['.$row['date_payment'].']]></cell>		
			<cell type="ro"><![CDATA['.$row['id'].']]></cell>		
			<cell type="ro"><![CDATA['.$row['subtotal'].']]></cell>		
			<cell type="ro"><![CDATA['.$row['shipping'].']]></cell>		
			<cell type="ro"><![CDATA['.$row['cost_price'].']]></cell>		
			<cell type="ro"><![CDATA['.$row['taxes'].']]></cell>		
			<cell type="ro"><![CDATA['.$row['discounts'].']]></cell>
			
			
			<cell type="ro"><![CDATA[';
			
			
			switch ($row['payment_method']) {
				//cc
				case 0:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CREDIT_CARD');
					break;
				// interact
				case 1:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_INTERACT');
					break;
				// cheque
				case 2:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CHEQUE');
					break;
				// paypal
				case 4:
					echo 'PayPal';
					break;
				// cash
				case 5:
					echo Yii::t('views/orders/edit_info_options','LABEL_PAYMENT_METHOD_CASH');
					break;					
			}
			
			
			echo ']]></cell>
					
			</row>';
		}
		
		echo '</rows>';
	}		
	
	/************************************************************
	*															*
	*															*
	*					PRODUCT SALES REPORTS					*
	*															*
	*															*
	************************************************************/
	
	public function actionProduct_sales_report()
	{	
		$this->render('product_sales_report');	
	}		
	
	public function actionProduct_sales_report_options()
	{	
		$this->renderPartial('product_sales_report_options',array());	
	}			
		
	
	/**
	 * This is the action to get an XML list of orders
	 */
	public function actionXml_list_product_sales_report($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('orders.status = 7');
		$params=array(':language_code'=>Yii::app()->language);
		$having='';
		
		// filters
		if ($start_date = $_GET['start_date']) {
			$where[] = 'orders.date_order >= "'.$start_date.'"';	
		}
		
		if ($end_date = $_GET['end_date']) {
			$where[] = 'orders.date_order <= "'.$end_date.'"';	
		}		
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'orders_item_product_description.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// variant_name
		if (isset($filters['variant_name']) && !empty($filters['variant_name'])) {
			$where[] = 'orders_item_product_description.variant_name LIKE CONCAT("%",:variant_name,"%")';
			$params[':variant_name']=$filters['variant_name'];
		}	
		
		// sku
		if (isset($filters['sku']) && !empty($filters['sku'])) {
			$where[] = 'IF(orders_item_product.id_product_variant != 0,orders_item_product.variant_sku,orders_item_product.sku) LIKE CONCAT("%",:sku,"%")';
			$params[':sku']=$filters['sku'];
		}			
		
		// qty
		if (isset($filters['qty']) && !empty($filters['qty'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['qty'])) {
				$having = 'qty <= :qty';
				$params[':qty']=ltrim($filters['qty'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['qty'])) {
				$having = 'qty >= :qty';
				$params[':qty']=ltrim($filters['qty'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['qty'])) {		
				$having = 'qty < :qty';
				$params[':qty']=ltrim($filters['qty'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['qty'])) {		
				$having = 'qty > :qty';
				$params[':qty']=ltrim($filters['qty'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['qty'])) {		
				$having = 'qty = :qty';
				$params[':qty']=ltrim($filters['qty'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['qty'])) {
				$search = explode('..',$filters['total']);
				$having = 'qty BETWEEN :total_start AND :total_end';
				$params[':total_start']=$search[0];
				$params[':total_end']=$search[1];
			// N				
			} else {
				$having = 'qty = :qty';
				$params[':qty']=$filters['qty'];
			}
		}		
		
		$sql = "SELECT 
		COUNT(*)
		FROM (SELECT 
		orders_item_product.id,
		SUM(orders_item_product.qty) AS qty
		FROM 
		orders
		INNER JOIN
		orders_item
		ON
		(orders.id = orders_item.id_orders)
		INNER JOIN
		(orders_item_product CROSS JOIN orders_item_product_description)
		ON
		(orders_item.id = orders_item_product.id_orders_item AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code)
							
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		$sql .= ' GROUP BY 
		orders_item_product.id_product,
		orders_item_product.id_product_variant';
		
		if ($having) $sql .= ' HAVING '.$having;
		
		$sql .= ') AS t';
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT
		*
		FROM (SELECT 
		orders_item_product.id_product AS id,
		orders_item_product.id_product_variant,
		IF(orders_item_product.id_product_variant != 0,orders_item_product.variant_sku,orders_item_product.sku) AS sku,
		orders_item_product_description.name,
		orders_item_product_description.variant_name,
		SUM(orders_item_product.qty) AS qty
		
		FROM 
		orders
		INNER JOIN
		orders_item
		ON
		(orders.id = orders_item.id_orders)
		INNER JOIN
		(orders_item_product CROSS JOIN orders_item_product_description)
		ON
		(orders_item.id = orders_item_product.id_orders_item AND orders_item_product.id = orders_item_product_description.id_orders_item_product AND orders_item_product_description.language_code = :language_code)
							
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		$sql .= ' GROUP BY 
		orders_item_product.id_product,
		orders_item_product.id_product_variant';
		
		if ($having) $sql .= ' HAVING '.$having;
		
		$sql .= ') AS t ';
		
		// sorting

		// name
		if (isset($sort_col[0]) && !empty($sort_col[0])) {	
			$direct = $sort_col[0] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY name ".$direct;
		// variant_name
		} else if (isset($sort_col[1]) && !empty($sort_col[1])) {
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY variant_name ".$direct;	
		// sku
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY sku ".$direct;				
		// qty
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY qty ".$direct;																										
		} else {
			$sql.=" ORDER BY name, variant_name";
		}		
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].':'.$row['id_product_variant'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['variant_name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>	
			<cell type="ro"><![CDATA['.$row['qty'].']]></cell>				
			</row>';
		}
		
		echo '</rows>';
	}			
	
	/************************************************************
	*															*
	*															*
	*						TAX REPORTS							*
	*															*
	*															*
	************************************************************/
	
	public function actionTax_report()
	{	
		$this->render('tax_report');	
	}		
	
	public function actionTax_report_options()
	{	
		$this->renderPartial('tax_report_options',array());	
	}			
		
	
	/**
	 * This is the action to get an XML list of orders
	 */
	public function actionXml_list_tax_report($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array(':language_code'=>Yii::app()->language);
		$having='';
		
		// filters
		if ($start_date = $_GET['start_date']) {
			$where[] = 'o.date_order >= "'.$start_date.'"';	
		}
		
		if ($end_date = $_GET['end_date']) {
			$where[] = 'o.date_order <= "'.$end_date.'"';	
		}		
		
		$sql = "SELECT 
		COUNT(tax.id)
		FROM 
		tax
		INNER JOIN
		tax_description
		ON
		(tax.id = tax_description.id_tax AND tax_description.language_code = :language_code) ";	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT
		tax.id,
		tax.tax_number,
		tax_description.name,
		ROUND(IFNULL((SELECT
			SUM(oipt.amount)
			FROM
			orders_tax AS ot
			INNER JOIN
			(orders_item_product_tax AS oipt CROSS JOIN orders_item_product AS oip CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
			ON
			(ot.id = oipt.id_orders_tax AND oipt.id_orders_item_product = oip.id AND oip.id_orders_item = oi.id AND oi.id_orders = o.id)
			WHERE
			ot.id_tax = tax.id
			AND
			o.status NOT IN (-1) ".(sizeof($where) ? ' AND '.implode(' AND ',$where):'')."),0)+IFNULL((SELECT
			SUM(oipt.amount)
			FROM
			orders_tax AS ot
			INNER JOIN
			(orders_item_option_tax AS oipt CROSS JOIN orders_item_option AS oip CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
			ON
			(ot.id = oipt.id_orders_tax AND oipt.id_orders_item_option = oip.id AND oip.id_orders_item = oi.id AND oi.id_orders = o.id)
			WHERE
			ot.id_tax = tax.id
			AND
			o.status NOT IN (-1) ".(sizeof($where) ? ' AND '.implode(' AND ',$where):'')."),0),2) AS total
			
			
		FROM 
		tax
		INNER JOIN
		tax_description
		ON
		(tax.id = tax_description.id_tax AND tax_description.language_code = :language_code) ";	
		
		// sorting
		$sql.=" ORDER BY name";
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			echo '<row id="'.$row['id'].'">
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['tax_number'].']]></cell>
			<cell type="ro"><![CDATA['.$row['total'].']]></cell>	
			</row>';
		}
		
		echo '</rows>';
	}			
	
	/**
	 * Filters
	 */
		
    public function filters()
    {
        return array(
            'accessControl',
        );
    }
	
	
	/**
	 * Access Rules
	 */
	
	/*
    public function accessRules()
    {
        return array(	
        );
    }*/
	
}