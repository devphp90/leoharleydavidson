<?php

class NewslettersubscriptionController extends Controller
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
	 * This is the action to delete
	 */
	public function actionDelete()
	{
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			foreach ($ids as $id) {			
				Tbl_NewsletterSubscription::model()->deleteByPk($id);					
			}
		}
	}			 

	/**
	 * This is the action to get an XML list
	 */
	public function actionXml_list($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		// filters
		
		// email
		if (isset($filters['email']) && !empty($filters['email'])) {
			$where[] = 'newsletter_subscription.email LIKE CONCAT("%",:email,"%")';
			$params[':email']=$filters['email'];
		}
		//language
		if (isset($filters['language_code'])) {
			$where[] = 'newsletter_subscription.language_code = :language_code';
			$params[':language_code']=$filters['language_code'];
		}
															
		
		$sql = "SELECT 
		COUNT(newsletter_subscription.id) AS total 
		FROM 
		newsletter_subscription
		INNER JOIN language ON newsletter_subscription.language_code = language.code
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
		newsletter_subscription.id,
		newsletter_subscription.email,
		newsletter_subscription.language_code,
		newsletter_subscription.date_created,
		language.name
		FROM 
		newsletter_subscription 
		INNER JOIN language ON newsletter_subscription.language_code = language.code
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		$sql.=" ORDER BY newsletter_subscription.date_created ASC";		
		
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
			<cell type="ro"><![CDATA['.$row['email'].']]></cell>
			<cell type="ro"><![CDATA['.$row['date_created'].']]></cell>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
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