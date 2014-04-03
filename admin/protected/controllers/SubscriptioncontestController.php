<?php

class SubscriptioncontestController extends Controller
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
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
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
			$where[] = 'subscription_contest.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}	
		
		// start date start
		if (isset($filters['start_date_start']) && !empty($filters['start_date_start'])) {
			$where[] = 'subscription_contest.start_date >= :start_date_start';
			$params[':start_date_start']=$filters['start_date_start'];
		}	
		
		// start date end
		if (isset($filters['start_date_end']) && !empty($filters['start_date_end'])) {
			$where[] = 'subscription_contest.start_date <= :start_date_end';
			$params[':start_date_end']=$filters['start_date_end'];
		}						
		
		// end date start
		if (isset($filters['end_date_start']) && !empty($filters['end_date_start'])) {
			$where[] = 'subscription_contest.end_date >= :end_date_start';
			$params[':end_date_start']=$filters['end_date_start'];
		}	
		
		// end date end
		if (isset($filters['end_date_end']) && !empty($filters['end_date_end'])) {
			$where[] = 'subscription_contest.end_date <= :end_date_end';
			$params[':end_date_end']=$filters['end_date_end'];
		}		
		
		// Type
		if (isset($filters['type'])) {
			switch ($filters['type']) {
				case 0:
				case 1:					
					$where[] = 'subscription_contest.contest = :type';				
					$params[':type']=$filters['type'];
					break;
			}
		}						
		
				
				
		// status
		if (isset($filters['active'])) {
			switch ($filters['active']) {
				case 0:
				case 1:					
					$where[] = 'subscription_contest.active = :active';				
					$params[':active']=$filters['active'];
					break;
			}
		}
		
										
		
		$sql = "SELECT 
		COUNT(subscription_contest.id) AS total 
		FROM 
		subscription_contest 		
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
		subscription_contest.id,
		subscription_contest.name,
		subscription_contest.contest,
		subscription_contest.start_date,
		subscription_contest.end_date,
		subscription_contest.active,
		(SELECT COUNT(id) FROM subscription_contest_person WHERE id_subscription_contest = subscription_contest.id) AS total_person
		FROM 
		subscription_contest 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY subscription_contest.contest ".$direct;
		// code
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {	
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY subscription_contest.name ".$direct;
		} else if (isset($sort_col[4]) && !empty($sort_col[4])) {
			$direct = $sort_col[4] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY subscription_contest.start_date ".$direct;	
		// end date
		} else if (isset($sort_col[5]) && !empty($sort_col[5])) {
			$direct = $sort_col[5] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY subscription_contest.end_date ".$direct;	
		// active
		} else if (isset($sort_col[8]) && !empty($sort_col[8])) {
			$direct = $sort_col[8] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY subscription_contest.active ".$direct;												
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(subscription_contest.name LIKE CONCAT(:name,'%'),0,1) ASC, subscription_contest.name ASC";
			} else {
				$sql.=" ORDER BY subscription_contest.id ASC";
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
			
			echo '<row id="'.$row['id'].'" '.($row['active']?'':'class="innactive"').'>
			<cell type="ch" />
			<cell type="ro"><![CDATA['.($row['contest']?Yii::t('global','LABEL_CONTEST'):Yii::t('global','LABEL_SUBSCRIPTIONS')).']]></cell>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.(($row['start_date'] != '0000-00-00 00:00:00') ? $row['start_date']:'').']]></cell>
			<cell type="ro"><![CDATA['.(($row['end_date'] != '0000-00-00 00:00:00') ? $row['end_date']:'').']]></cell>	
			<cell type="ro"><![CDATA['.$row['total_person'].']]></cell>	
			<cell type="ch">'.$row['active'].'</cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/**
	 * This is the action to edit or create a rebate
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new SubscriptionContestForm;	
		
		$id = (int)$id;
		
		if ($id) {
			if ($subscription_contest = Tbl_SubscriptionContest::model()->findByPk($id)) {
				$model->id = $subscription_contest->id;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));	
	}	
	
	/**
	 * This is the action to edit each section
	 */ 
	public function actionEdit_section($container, $containerJS)	
	{						
		$id = trim($_POST['id']);
		$id_subscription_contest = (int)$_POST['id_subscription_contest'];
						
		$this->renderPartial($id,array('id'=>$id_subscription_contest,'container'=>$container,'containerJS'=>$containerJS));	
	}
	
	public function actionEdit_info_options($container, $containerLayout, $id=0)
	{
		$model = new SubscriptionContestForm;
		
		$id = (int)$id;		
		
		if ($id) {
			if ($subscription_contest = Tbl_SubscriptionContest::model()->findByPk($id)) {
				$model->id = $subscription_contest->id;
				$model->contest = $subscription_contest->contest;
				
				$model->customer_only = $subscription_contest->customer_only;
				$model->include_form_address = $subscription_contest->include_form_address;
				$model->include_form_telephone = $subscription_contest->include_form_telephone;
				$model->id_rebate_coupon = $subscription_contest->id_rebate_coupon;
				
				$model->name = $subscription_contest->name;
				$model->start_date = ($subscription_contest->start_date != '0000-00-00 00:00:00') ? $subscription_contest->start_date:'';
				$model->end_date = ($subscription_contest->end_date != '0000-00-00 00:00:00') ? $subscription_contest->end_date:'';
				$model->active = $subscription_contest->active;	

				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_info_options',array('model'=>$model, 'container'=>$container, 'containerLayout'=>$containerLayout));		
	}	
	
	public function actionXml_subscription_contest_description($container, $id=0)
	{
		$model = new SubscriptionContestForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($subscription_contest = Tbl_SubscriptionContest::model()->findByPk($id)) {							
				// grab description information 
				foreach ($subscription_contest->tbl_subscription_contest_description as $row) {
					$model->subscription_contest_description[$row->language_code]['description'] = $row->description;
					$model->subscription_contest_description[$row->language_code]['name'] = $row->name;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
			
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:hidden;">	
					<div style="padding:10px;">
					<div class="row">
						<strong>'.Yii::t('controllers/CmspagesController','LABEL_TITLE').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_subscription_contest_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->subscription_contest_description[$value->code]['name'])).'</span>)</em>':'').'
						<div>'.
						CHtml::activeTextField($model,'subscription_contest_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'id'=>$container.'_subscription_contest_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_subscription_contest_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					<div class="row">
						<div>'.
						CHtml::activeTextArea($model,'subscription_contest_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'class' => 'editor', 'rows' => 6, 'id'=>$container.'_subscription_contest_description['.$value->code.'][description]')).'
						<br /><span id="'.$container.'_subscription_contest_description['.$value->code.'][description]_errorMsg" class="error"></span>
						</div>
					</div>   
					</div>
					</div>
				]]>
			</content>
		</tab>';
		
			++$i;
		}
		
		echo '</row></tabbar>';
	}					
	
	/**
	 * This is the action to save 
	 */
	public function actionSave_info()
	{
		$model = new SubscriptionContestForm;

		// collect user input data
		if(isset($_POST['SubscriptionContestForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['SubscriptionContestForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$output = array('id'=>$model->id);
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}	
	
			
	
	/**
	 * This is the action to delete a product
	 */
	public function actionDelete_person($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id=:id'; 
				$criteria->params=array(':id'=>$id); 					
				
				// delete all
				Tbl_SubscriptionContestPerson::model()->deleteAll($criteria);					
			}
		}
	}	
	
	/**
	 * This is the action to delete a product
	 */
	public function actionWinner_person()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id_subscription_contest = $_POST['id'];
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_subscription_contest=:id_subscription_contest'; 
		$criteria->params=array(':id_subscription_contest'=>$id_subscription_contest); 	
		Tbl_SubscriptionContestPerson::model()->updateAll(array('contest_winner'=>0),$criteria);
		
		$sql = 'SELECT 
		id
		FROM
		subscription_contest_person
		WHERE id_subscription_contest = ' . $id_subscription_contest . '
		ORDER BY RAND() LIMIT 1';	
		
		$command=$connection->createCommand($sql);
		$row = $command->queryRow(true);
		$id_winner = $row['id'];
			
		Tbl_SubscriptionContestPerson::model()->updateByPk($id_winner,array('contest_winner'=>1));		
	}	

	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_person($posStart=0, $count=100, array $filters=array(), array $sort_col=array(), $id=0)
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		$search_by=(int)$_GET['search_by'];		
		
		$where=array('subscription_contest_person.id_subscription_contest = :id_subscription_contest');
		$params=array(':id_subscription_contest'=>$id,':language_code'=>Yii::app()->language);

	
	
		$sql = 'SELECT 
		COUNT(subscription_contest_person.id) AS total
		FROM
		subscription_contest_person
		LEFT JOIN
		country_description 
		ON 
		(subscription_contest_person.country_code = country_description.country_code AND country_description.language_code = :language_code)	
		LEFT JOIN
		state_description
		ON 
		(subscription_contest_person.state_code = state_description.state_code AND state_description.language_code = :language_code)
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
		subscription_contest_person.*,
		subscription_contest.contest, 
		IF(subscription_contest_person.id_customer>0,CONCAT_WS(' ',customer.firstname,customer.lastname),CONCAT_WS(' ',subscription_contest_person.firstname,subscription_contest_person.lastname)) AS name,
		IF(subscription_contest_person.id_customer>0,customer.email,subscription_contest_person.email) AS email,
		IF(subscription_contest_person.id_customer>0,customer_address.zip,subscription_contest_person.zip) AS zip,
		IF(subscription_contest_person.id_customer>0,customer_address.telephone,subscription_contest_person.telephone) AS telephone,
		IF(subscription_contest_person.id_customer>0,customer_address.address,subscription_contest_person.address) AS address_name,
		IF(subscription_contest_person.id_customer>0,cdc.name,country_description.name) AS country_name,
		IF(subscription_contest_person.id_customer>0,sdc.name,state_description.name) AS state_name
		FROM
		subscription_contest_person
		LEFT JOIN
		subscription_contest 
		ON 
		(subscription_contest_person.id_subscription_contest = subscription_contest.id)
		LEFT JOIN
		country_description 
		ON 
		(subscription_contest_person.country_code = country_description.country_code AND country_description.language_code = :language_code)	
		LEFT JOIN
		state_description
		ON 
		(subscription_contest_person.state_code = state_description.state_code AND state_description.language_code = :language_code)	
		
		LEFT JOIN
		customer 
		ON 
		(subscription_contest_person.id_customer = customer.id)
		LEFT JOIN
		customer_address 
		ON 
		(customer.id = customer_address.id_customer AND customer_address.default_billing = 1)	
		LEFT JOIN
		country_description AS cdc
		ON 
		(customer_address.country_code = cdc.country_code AND cdc.language_code = :language_code)	
		LEFT JOIN
		state_description AS sdc
		ON 
		(customer_address.state_code = sdc.state_code AND sdc.language_code = :language_code)	
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		//echo $sql;
		
		// sorting

		$sql.=" ORDER BY subscription_contest_person.contest_winner DESC, subscription_contest_person.date_created ".$direct;																					
				
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;			

		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {
			
			if($row['contest_winner'] && $row['contest']==1){
				$font_style = 'color:#090;font-weight:bold;';
			}else{
				$font_style = '';	
			}
				
			echo '<row id="'.$row['id'].':'.$row['id_product_variant'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['name'].'</span>]]></cell>
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['email'].'</span>]]></cell>			
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['telephone'].'</span>]]></cell>
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['address_name'].'</span>]]></cell>
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['country_name'].'</span>]]></cell>
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['state_name'].'</span>]]></cell>
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.$row['zip'].'</span>]]></cell>
			<cell type="ro"><![CDATA[<span style="'.$font_style.'">'.date("Y-m-d",strtotime($row['date_created'])).'</span>]]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	
	
	/**
	 * This is the action to delete
	 */
	public function actionDelete()
	{
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			// rebate
			$command=$connection->createCommand('DELETE FROM 
			subscription_contest,
			subscription_contest_person,
			subscription_contest_description 
			USING 
			subscription_contest 
			LEFT JOIN 
			subscription_contest_person 
			ON 
			(subscription_contest.id = subscription_contest_person.id_subscription_contest)
			LEFT JOIN 
			subscription_contest_description 
			ON
			(subscription_contest.id = subscription_contest_description.id_subscription_contest) 
			WHERE 
			subscription_contest.id=:id');					
			
			foreach ($ids as $id) {									
				$command->execute(array(':id'=>$id));						
			}
		}
	}
	
	/**
	 * This is the action to toggle product active status
	 */
	public function actionToggle_active()
	{
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		
		if ($p = Tbl_SubscriptionContest::model()->findByPk($id)) {
			$p->active = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	/**
	 * This action is to redirect to the right url for the preview
	 */
	public function actionPreview($id,$language)
	{
		$id=(int)$id;	
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		require(dirname(__FILE__).'/../../../_includes/function.php');
		
		$sql='SELECT
		subscription_contest.id
		FROM
		subscription_contest
		INNER JOIN
		subscription_contest_description
		ON
		(subscription_contest.id = subscription_contest_description.id_subscription_contest AND subscription_contest_description.language_code = "'.$language.'")
		WHERE
		subscription_contest.id = :id
		LIMIT 1';
		$command=$connection->createCommand($sql);
		if ($row = $command->queryRow(true,array(':id'=>$id))) {
			$this->redirect('/'.$language.'/registration?id='.$id);
		} else echo Yii::t('global','ERROR_INVALID_PAGE');
		exit;
	}			
	
	
	/**
	 * This is the action to get an XML list of the rebate menu
	 */
	public function actionXml_list_section($id=0)
	{
		$id = (int)$id;
		
		$disabled = '';
		
		if (!$id) { 
			$disabled = '<disabled><![CDATA[1]]></disabled>';
		}
			
		//set content type and xml tag
		header("Content-type:text/xml");		
		
		echo '<data>
			<item id="edit_info">
				<Title><![CDATA['.Yii::t('controllers/SubscriptionContestController','LABEL_INFORMATION').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/SubscriptionContestController','LABEL_INFORMATION_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_persons">
				<Title><![CDATA['.Yii::t('controllers/SubscriptionContestController','LABEL_PERSON_LIST').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/SubscriptionContestController','LABEL_PERSON_LIST_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>
		</data>';
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