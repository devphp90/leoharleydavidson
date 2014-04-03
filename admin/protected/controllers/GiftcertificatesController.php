<?php

class GiftcertificatesController extends Controller
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
		$this->render('index',array('model'=>$model));	
	}
	
	/**
	 * This is the action to edit or create a customer type
	 */
	public function actionEdit($container, $id=0)
	{
		$app = Yii::app();
		
		$model = new GiftCertificateForm;
		
		$id = (int)$id;
		$model->id = $id;
		$current_datetime = date('Y-m-d H:i:s');
		
			
		// collect user input data
		if(isset($_POST['GiftCertificateForm']))
		{	
		
			//Verify if Save and Send by Email have been clicked
			$send_email = $_POST['email'];
					
			// loop through each attribute and set it in our model
			foreach($_POST['GiftCertificateForm'] as $name=>$value)
			{
				$model->$name=$value;
			}

			// validate 
			if($model->validate()) {
				
				$sendmail_failed = 0;
				
				if($send_email=='true'){
					$model->shipping_method = 0;
					$model->sent = 1;
					$model->date_sent = $current_datetime;
					
					// Get Customer Info
					$customer = Tbl_Customer::model()->findByPk($model->id_customer);
					$customer_name = ucfirst(strtolower($customer->firstname)) . ' ' . ucfirst(strtolower($customer->lastname));
					
					$mail = new PHPMailer(); // defaults to using php "mail()"
					$mail->CharSet = 'UTF-8';
					$mail->SetLanguage('fr');
					
					// text only
					//$mail->IsHTML(false);
				
					$mail->SetFrom($app->params['no_reply_email'], $app->params['site_name']);
			
					$mail->AddAddress($model->person_email, $model->person_name);
					
					$mail->Subject = Yii::t('emails','GIFT_CERTIFICATE_SUBJECT',array('{customer_name}'=>$customer_name),NULL,$model->language_code);
					
					$number_formatter = new CNumberFormatter($model->language_code);
					$amount = $number_formatter->formatCurrency($model->price, Yii::app()->params['currency']);

					$person_message ='';
					if(!empty($model->person_message)){
						$person_message = ($model->person_message ? "\r\n\r\n".$model->person_message:'');
						$person_message_html = ($model->person_message ? '<br /><br />'.nl2br($model->person_message):'');
					}
					
					$mail->AltBody = Yii::t('emails','GIFT_CERTIFICATE_PLAIN', array('{person_name}'=>$model->person_name,
					'{person_message}'=>$person_message,
					'{customer_name}'=>$customer_name,
					'{amount}'=>$amount,
					'{code}'=>$model->code,
					'{signature}'=>Html::get_company_signature(0,$model->language_code),
					),NULL,$model->language_code);
					
					$mail->MsgHTML(Yii::t('emails','GIFT_CERTIFICATE_HTML', array('{person_name}'=>$model->person_name,
					'{person_message}'=>$person_message_html,
					'{customer_name}'=>$customer_name,
					'{amount}'=>$amount,
					'{code}'=>$model->code,
					'{signature}'=>Html::get_company_signature(1,$model->language_code),
					),NULL,$model->language_code));
					$sendmail_failed = $mail->Send() ? 0:1;

				}
				
				if (!$sendmail_failed) { 
					$model->save();
				}
			}
			if (!$sendmail_failed) {
				$output = array('id'=>$model->id);
				$errors = $model->getErrors();
				
				if (sizeof($errors)) { 
					$output['errors'] = $errors;
				}
				echo CJSON::encode($output);
			}
			
			
								
							
		} else {						
			if ($id) {
				if ($gift_certificate = Tbl_GiftCertificate::model()->findByPk($id)) {
					$model->code = $gift_certificate->code;
					$model->price = $gift_certificate->price;
					$model->active = $gift_certificate->active;
					$model->id_customer = $gift_certificate->id_customer;
					
					$connection=Yii::app()->db;
					$sql = "SELECT 
					customer.id,
					customer.lastname,
					customer.firstname,
					customer.email,
					customer_address.telephone
					FROM 
					customer
					LEFT JOIN 
					customer_address 
					ON 
					customer.id = customer_address.id_customer AND customer_address.default_billing = 1
					WHERE
					customer.id = " . $model->id_customer;	
					
					/* Select queries return a resultset */
					$command=$connection->createCommand($sql);
					if ($row = $command->queryRow(true)) {
						$model->customer_name = '<div><strong>'.$row['firstname'] . " " . $row['lastname'].'</strong></div>
						<div>'.Yii::t('global','LABEL_CONTACT_US_E').'&nbsp;<a href="mailto:'.$row['email'].'">'.$row['email'].'</a></div>
						<div>'.Yii::t('global','LABEL_CONTACT_US_T').'&nbsp;'.$row['telephone'].'</div>';
					}

					$model->comments = $gift_certificate->comments;
					$model->person_name = $gift_certificate->person_name;
					$model->person_address = $gift_certificate->person_address;
					$model->person_email = $gift_certificate->person_email;
					$model->person_message = $gift_certificate->person_message;
					$model->shipping_method = $gift_certificate->shipping_method;
					$model->language_code = $gift_certificate->language_code;	
					$model->sent = $gift_certificate->sent;		
					$model->date_sent = $gift_certificate->date_sent;					
				} else {
					throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
				}
			}							
			
			$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));	
		}				
	}
	
	/**
	 * This is the action to delete 
	 */
	public function actionDelete()
	{		
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {
				Tbl_GiftCertificate::model()->deleteByPk($id);
			}
		}
	}	
	
	/**
	 * This is the action to get an XML list of the customer types
	 */
	public function actionXml_list()
	{		
		//throw new CException(print_r($_GET,1));	
	
		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		if (isset($_GET['type']) && $_GET['type']){
			$type=trim($_GET['type']);	
		} else { $type=''; }
		
		//define variables from incoming values
		if(isset($_GET["posStart"]))
			$posStart = $_GET['posStart'];
		else
			$posStart = 0;
		if(isset($_GET["count"]))
			$count = $_GET['count'];
		else
			$count = 34;
			
		//filtering
		$filters = isset($_GET['filters']) ? $_GET['filters']:array();
		
		$where=array();
		$params=array();
		
		// code
		if (isset($filters['code']) && !empty($filters['code'])) {
			$where[] = 'gift_certificate.code LIKE CONCAT("%",:code,"%")';
			$params[':code']=$filters['code'];
		}
		// customer_name
		if (isset($filters['customer_name']) && !empty($filters['customer_name'])) {
			$where[] = 'CONCAT(customer.firstname," ",customer.lastname) LIKE CONCAT("%",:customer_name,"%")';
			$params[':customer_name']=$filters['customer_name'];
		}
		
		/*// person_name
		if (isset($filters['person_name']) && !empty($filters['person_name'])) {
			$where[] = 'gift_certificate.person_name LIKE CONCAT("%",:person_name,"%")';
			$params[':person_name']=$filters['person_name'];
		}*/
		
		// price
		if (isset($filters['price']) && !empty($filters['price'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['price'])) {
				$where[] = 'gift_certificate.price <= :price';
				$params[':price']=ltrim($filters['price'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['price'])) {
				$where[] = 'gift_certificate.price >= :price';
				$params[':price']=ltrim($filters['price'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'gift_certificate.price < :price';
				$params[':price']=ltrim($filters['price'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'gift_certificate.price > :price';
				$params[':price']=ltrim($filters['price'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['price'])) {		
				$where[] = 'gift_certificate.price = :price';
				$params[':price']=ltrim($filters['price'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['price'])) {
				$search = explode('..',$filters['price']);
				$where[] = 'gift_certificate.price BETWEEN :price_start AND :price_end';
				$params[':price_start']=$search[0];
				$params[':price_end']=$search[1];
			// N				
			} else {
				$where[] = 'gift_certificate.price = :price';
				$params[':price']=$filters['price'];
			}
		}
		
		// sent date start
		if (isset($filters['sent_date_start']) && !empty($filters['sent_date_start'])) {
			$where[] = 'gift_certificate.date_sent >= :sent_date_start';
			$params[':sent_date_start']=$filters['sent_date_start'];
		}	
		
		// sent date end
		if (isset($filters['sent_date_end']) && !empty($filters['sent_date_end'])) {
			$where[] = 'gift_certificate.date_sent <= :sent_date_end';
			$params[':sent_date_end']=$filters['sent_date_end'];
		}	
		
		// shipping_method
		if (isset($filters['shipping_method'])) {
			$where[] = 'gift_certificate.shipping_method = :shipping_method';
			$params[':shipping_method']=$filters['shipping_method'];
		}
		/*// language_code
		if (isset($filters['language_code'])) {
			$where[] = 'gift_certificate.language_code = :language_code';
			$params[':language_code']=$filters['language_code'];
		}*/
		// shipping_method
		if (isset($filters['sent'])) {
			$where[] = 'gift_certificate.sent = :sent';
			$params[':sent']=$filters['sent'];
		}
		// status
		if (isset($filters['active'])) {
			switch ($filters['active']) {
				case 0:
				case 1:					
					$where[] = 'gift_certificate.active = :active';				
					$params[':active']=$filters['active'];
					break;
			}
		}		
		
		/*// taxable
		if (isset($filters['shipping'])) {
			switch ($filters['shipping']) {
				case 0:
					$where[] = 'customer_type.taxable = :taxable';				
					$params[':taxable']=$filters['taxable'];
					break;
				case 1:					
					$where[] = 'customer_type.taxable = :taxable';				
					$params[':taxable']=$filters['taxable'];
					break;
			}
		}*/					
		
		$sql = "SELECT 
				COUNT(*) AS total 
				FROM gift_certificate ".(sizeof($where) ? ' 
				INNER JOIN 
				language
				ON
				gift_certificate.language_code = language.code 
				LEFT JOIN 
				customer
				ON
				gift_certificate.id_customer = customer.id 
				WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
					gift_certificate.id,
					gift_certificate.code,
					gift_certificate.price,
					gift_certificate.active,
					gift_certificate.id_customer,
					gift_certificate.person_name,
					gift_certificate.shipping_method,
					gift_certificate.language_code,
					gift_certificate.sent,
					gift_certificate.date_sent,
					language.name,
					CONCAT(customer.firstname,' ',customer.lastname) AS customer_name
					FROM gift_certificate
					INNER JOIN 
					language
					ON
					gift_certificate.language_code = language.code 
					LEFT JOIN 
					customer
					ON
					gift_certificate.id_customer = customer.id 
					".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
					//echo $sql;		
		
		// sorting
		$sort_col = isset($_GET['sort_col']) ? $_GET['sort_col']:array();
		
		// type
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY gift_certificate.code ".$direct;
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY gift_certificate.price ".$direct;
		} else if (isset($sort_col[3]) && !empty($sort_col[3])) {
			$direct = $sort_col[3] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY CONCAT(customer.firstname,' ',customer.lastname) ".$direct;
		} else if (isset($sort_col[6]) && !empty($sort_col[6])) {
			$direct = $sort_col[6] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY gift_certificate.date_sent ".$direct;			
		} else {
			if (isset($filters['customer_name']) && !empty($filters['customer_name'])) { 
				$sql.=" ORDER BY IF(CONCAT(customer.firstname,' ',customer.lastname) LIKE CONCAT(:customer_name,'%'),0,1) ASC, CONCAT(customer.firstname,'  ',customer.lastname) ASC";
			} else if (isset($filters['code']) && !empty($filters['code'])) { 
				$sql.=" ORDER BY IF(gift_certificate.code LIKE CONCAT(:code,'%'),0,1) ASC, gift_certificate.code ASC";
			} else {
				$sql.=" ORDER BY gift_certificate.id ASC";
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
			switch($row['shipping_method']){
				case 0:
					$shipping_method = Yii::t('views/giftcertificates/edit','LABEL_SHIPPING_METHOD_0');
				break;
				case 1:
					$shipping_method = Yii::t('views/giftcertificates/edit','LABEL_SHIPPING_METHOD_1');
				break;	
			}
			
			
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['code'].']]></cell>
			<cell type="ro">'.Html::nf($row['price']).'</cell>
			<cell type="ro"><![CDATA['.$row['customer_name'].']]></cell>
			<cell type="ro"><![CDATA['.$shipping_method.']]></cell>
			<cell type="ch"><![CDATA['.$row['sent'].']]></cell>
			<cell type="ro">'.(($row['date_sent']!="0000-00-00 00:00:00")?$row['date_sent']:'-').'</cell>
			<cell type="ch">'.$row['active'].'</cell>
			<cell type="ro"><![CDATA['.$row['shipping_method'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}

	public function actionXml_list_customer_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			// filters
			
			// customer_name
			if (isset($filters['customer_name']) && !empty($filters['customer_name'])) {
				$where[] = '(customer.firstname LIKE CONCAT("%",:customer_name,"%") OR customer.lastname LIKE CONCAT("%",:customer_name,"%"))';
				$params[':customer_name']=$filters['customer_name'];
			}
			
			$where[]='customer.active=:active';
			$params[':active']="1";		
			
			
			$sql = "SELECT 
			COUNT(*) AS total  
			FROM 
			customer 
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
			customer.id,
			customer.lastname,
			customer.firstname,
			customer.email,
			customer_address.telephone
			FROM 
			customer
			LEFT JOIN 
			customer_address 
			ON 
			customer.id = customer_address.id_customer AND customer_address.default_billing = 1
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
				
			// sorting

			$sql.=" ORDER BY customer.lastname ASC";	
			
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
				<cell type="ro"><![CDATA['.$row['firstname']." ".$row['lastname'].']]></cell>
				<cell type="ro"><![CDATA['.$row['email'].']]></cell>
				<cell type="ro"><![CDATA['.$row['telephone'].']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
	
	}		
	
	public function actionAdd_customer()
	{
		
		// current product
		$id = $_POST['id'];
				
		$connection=Yii::app()->db;
		$sql = "SELECT 
		customer.id,
		customer.lastname,
		customer.firstname,
		customer.email,
		customer_address.telephone
		FROM 
		customer
		LEFT JOIN 
		customer_address 
		ON 
		customer.id = customer_address.id_customer AND customer_address.default_billing = 1
		WHERE
		customer.id = " . $id;	
		
		/* Select queries return a resultset */
		$command=$connection->createCommand($sql);
		if ($row = $command->queryRow(true)) {
			$info = '<div><strong>'.$row['firstname'] . " " . $row['lastname'].'</strong></div>
			<div>'.Yii::t('global','LABEL_CONTACT_US_E').'&nbsp;<a href="mailto:'.$row['email'].'">'.$row['email'].'</a></div>
			<div>'.Yii::t('global','LABEL_CONTACT_US_T').'&nbsp;'.$row['telephone'].'</div>';
			$output = array('id'=>$row['id'],'info'=>$info);
		}else{
			$output = array('error'=>1);	
		}

		echo CJSON::encode($output);
			
	}
	
			
	
	public function actionToggle_active()
	{
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		
		if ($p = Tbl_GiftCertificate::model()->findByPk($id)) {
			$p->active = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	public function actionToggle_sent()
	{
		$app = Yii::app();
		
		$id = (int)$_POST['id'];
		$active = ($_POST['sent']=='true'?1:0);
		
		$send_email = $_POST['send_email'];
		
		$current_datetime = date('Y-m-d H:i:s');
		
		if ($model = Tbl_GiftCertificate::model()->findByPk($id)) {
			$model->sent = $active;
			$model->date_sent = ($active?$current_datetime:'0000-00-00 00:00:00');
			if (!$model->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
		if($send_email){ 
			
			// Get the current language tu use later
			$current_language = Yii::app()->language;
			// Set the language in the customer language
			Yii::app()->setLanguage($model->language_code);
			
			
			// Get Customer Info
			$customer = Tbl_Customer::model()->findByPk($model->id_customer);
			$customer_name = ucfirst(strtolower($customer->firstname)) . ' ' . ucfirst(strtolower($customer->lastname));
			
			$mail = new PHPMailer(); // defaults to using php "mail()"
			$mail->CharSet = 'UTF-8';
			$mail->SetLanguage('fr');
			
			// text only
			//$mail->IsHTML(false);
		
			$mail->SetFrom($app->params['no_reply_email'], $app->params['site_name']);
	
			$mail->AddAddress($model->person_email, $model->person_name);
			
			$mail->Subject = Yii::t('controllers/GiftcertificatesController','TEXT_EMAIL_TITLE') . $customer_name;
			
			$number_formatter = new CNumberFormatter($model->language_code);
			$amount = $number_formatter->formatCurrency($model->price, Yii::app()->params['currency']);
			
			
			//Find the country and state of the company
			$country_name = '';
			$state_name = ''; 
			if($app->params['company_country_code']){
				$criteria=new CDbCriteria; 
				$criteria->condition='country_code=:country_code AND language_code=:language_code'; 
				$criteria->params=array(':country_code'=>$app->params['company_country_code'],':language_code'=>$model->language_code); 

				if ($country = Tbl_CountryDescription::model()->find($criteria)) {
					$country_name = $country->name;
				}
			}
			if($app->params['company_state_code']){
				$criteria=new CDbCriteria; 
				$criteria->condition='state_code=:state_code AND language_code=:language_code'; 
				$criteria->params=array(':state_code'=>$app->params['company_state_code'],':language_code'=>$model->language_code); 

				if ($state = Tbl_StateDescription::model()->find($criteria)) {
					$state_name = $state->name;
				}
			}
			
			$person_message ='';
			if(!empty($model->person_message)){
				$person_message = '
				
				' . $model->person_message;
				$person_message_html = '<br /><br />'.nl2br($model->person_message);
			}

			$mail->AltBody = Yii::t('controllers/GiftcertificatesController','TEXT_EMAIL_PLAIN', array('{person_name}'=>$model->person_name,'{person_message}'=>$person_message,'{customer_name}'=>$customer_name,'{amount}'=>$amount,'{code}'=>$model->code,'{company_company}'=>$app->params['company_company'],'{company_address}'=>$app->params['company_address'],'{company_city}'=>$app->params['company_city'],'{state_name}'=>$state_name,'{country_name}'=>$country_name,'{company_zip}'=>$app->params['company_zip'],'{company_telephone}'=>$app->params['company_telephone'],'{company_fax}'=>$app->params['company_fax'],'{company_email}'=>$app->params['company_email']));
			$mail->MsgHTML(Yii::t('controllers/GiftcertificatesController','TEXT_EMAIL_HTML', array('{person_name}'=>$model->person_name,'{person_message}'=>$person_message_html,'{customer_name}'=>$customer_name,'{amount}'=>$amount,'{code}'=>$model->code,'{company_company}'=>$app->params['company_company'],'{company_address}'=>$app->params['company_address'],'{company_city}'=>$app->params['company_city'],'{state_name}'=>$state_name,'{country_name}'=>$country_name,'{company_zip}'=>$app->params['company_zip'],'{company_telephone}'=>$app->params['company_telephone'],'{company_fax}'=>$app->params['company_fax'],'{company_email}'=>$app->params['company_email'])));
			$sendmail_failed = $mail->Send() ? 0:1;
			
			// Set the language back to what is supposed to be
			Yii::app()->setLanguage($current_language);
			
			if($sendmail_failed) {
				throw new CException(Yii::t('global','LABEL_ALERT_NO_DATA_RETURN'));	
			}
			
		}
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