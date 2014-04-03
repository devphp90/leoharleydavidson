<?php

class CustomertypesController extends Controller
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
		$model = new CustomerTypeForm;
		
		$id = (int)$id;
		$model->id = $id;
			
		// collect user input data
		if(isset($_POST['CustomerTypeForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['CustomerTypeForm'] as $name=>$value)
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
		} else {						
			if ($id) {
				if ($customer_type = Tbl_CustomerType::model()->findByPk($id)) {
					$model->name = $customer_type->name;
					$model->percent_discount = $customer_type->percent_discount;
					$model->taxable = $customer_type->taxable;	
					$model->apply_on_rebate = $customer_type->apply_on_rebate;						
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
				Tbl_CustomerType::model()->deleteByPk($id);
				Tbl_Customer::model()->updateAll(array('id_customer_type'=>0),'id_customer_type=:id_customer_type',array(':id_customer_type'=>$id));
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
		
		// type
		if (isset($filters['type']) && !empty($filters['type'])) {
			$where[] = 'customer_type.name LIKE CONCAT(:type,"%")';
			$params[':type']=$filters['type'];
		}
		
		// percent discount
		if (isset($filters['percent_discount']) && !empty($filters['percent_discount'])) {
			// <=N
			if (preg_match('/^<=([0-9\.]+)$/', $filters['percent_discount'])) {
				$where[] = 'customer_type.percent_discount <= :percent_discount';
				$params[':percent_discount']=ltrim($filters['percent_discount'],'<=');
			// >=N
			} else if (preg_match('/^>=([0-9\.]+)$/', $filters['percent_discount'])) {
				$where[] = 'customer_type.percent_discount >= :percent_discount';
				$params[':percent_discount']=ltrim($filters['percent_discount'],'>=');
			// <N
			} else if (preg_match('/^<([0-9\.]+)$/', $filters['percent_discount'])) {		
				$where[] = 'customer_type.percent_discount < :percent_discount';
				$params[':percent_discount']=ltrim($filters['percent_discount'],'<');
			// >N
			} else if (preg_match('/^>([0-9\.]+)$/', $filters['percent_discount'])) {	
				$where[] = 'customer_type.percent_discount > :percent_discount';
				$params[':percent_discount']=ltrim($filters['percent_discount'],'>');
			// =N
			} else if (preg_match('/^=([0-9\.]+)$/', $filters['percent_discount'])) {		
				$where[] = 'customer_type.percent_discount = :percent_discount';
				$params[':percent_discount']=ltrim($filters['percent_discount'],'=');
			// N1..N2
			} else if (preg_match('/^([0-9\.]+)\.\.([0-9\.]+)$/', $filters['percent_discount'])) {
				$search = explode('..',$filters['percent_discount']);
				$where[] = 'customer_type.percent_discount BETWEEN :percent_discount_start AND :percent_discount_end';
				$params[':percent_discount_start']=$search[0];
				$params[':percent_discount_end']=$search[1];
			// N				
			} else {
				$where[] = 'customer_type.percent_discount = :percent_discount';
				$params[':percent_discount']=$filters['percent_discount'];
			}
		}		
		
		// taxable
		if (isset($filters['taxable'])) {
			switch ($filters['taxable']) {
				case 0:
				case 1:					
					$where[] = 'customer_type.taxable = :taxable';				
					$params[':taxable']=$filters['taxable'];
					break;
			}
		}
		
		// applicable on rebate
		if (isset($filters['apply_on_rebate'])) {
			switch ($filters['apply_on_rebate']) {
				case 0:
				case 1:					
					$where[] = 'customer_type.apply_on_rebate = :apply_on_rebate';				
					$params[':apply_on_rebate']=$filters['apply_on_rebate'];
					break;
			}
		}						
		
		$sql = "SELECT COUNT(customer_type.id) AS total FROM customer_type ".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');	
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = "SELECT 
				customer_type.id,
				customer_type.percent_discount,
				customer_type.taxable,
				customer_type.apply_on_rebate,
				customer_type.name 
				FROM customer_type  ".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sort_col = isset($_GET['sort_col']) ? $_GET['sort_col']:array();
		
		// type
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY customer_type.name ".$direct;
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY customer_type.percent_discount ".$direct;
		} else {
			$sql.=" ORDER BY customer_type.id ASC";
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
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['percent_discount'].']]></cell>
			<cell type="ch"><![CDATA['.$row['apply_on_rebate'].']]></cell>
			<cell type="ch"><![CDATA['.$row['taxable'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}		
	
	/**
	 * This is the action to toggle active status
	 */
	public function actionToggle_taxable()
	{
		//throw new CException(print_r($_POST,1));	
		
		// current 
		$id = trim($_POST['id']);
		$taxable = ($_POST['taxable']=='true'?1:0);
		
		if ($customertype = Tbl_CustomerType::model()->findByPk($id)) {
			$customertype->taxable = $taxable;
			$customertype->save();
		} 
	}
	
	/**
	 * This is the action to toggle apply on rebate
	 */
	public function actionToggle_apply_on_rebate()
	{
		//throw new CException(print_r($_POST,1));	
		
		// current 
		$id = trim($_POST['id']);
		$apply_on_rebate = ($_POST['apply_on_rebate']=='true'?1:0);
		
		if ($customertype = Tbl_CustomerType::model()->findByPk($id)) {
			$customertype->apply_on_rebate = $apply_on_rebate;
			$customertype->save();
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