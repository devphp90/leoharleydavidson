<?php

class TaxgroupsController extends Controller
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
			$where[] = 'tax_group.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}							
		
		$sql = "SELECT 
		COUNT(tax_group.id) AS total 
		FROM 
		tax_group 
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
		tax_group.id,
		tax_group.name
		FROM 
		tax_group
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY tax_group.name ".$direct;
		} else {
			if (isset($filters['name']) && !empty($filters['name'])) { 
				$sql.=" ORDER BY IF(tax_group.name LIKE CONCAT(:name,'%'),0,1) ASC, tax_group.name ASC";
			} else {
				$sql.=" ORDER BY tax_group.id ASC";
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
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	

	/**
	 * This is the action to edit or create
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new TaxGroupsForm;	
		
		$id = (int)$id;

		if ($id) { 
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_TaxGroup::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		
			
			$model->id = $id;	
		}		
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container,'id'=>$id));	
	}
	
	
	public function actionEdit_options($container, $id=0)
	{
		$model = new TaxGroupsForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($taxgroup = Tbl_TaxGroup::model()->findByPk($id)) {
				$model->id = $taxgroup->id;
				$model->name = $taxgroup->name;
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave()
	{
		$model = new TaxGroupsForm;
		
		// collect user input data
		if(isset($_POST['TaxGroupsForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['TaxGroupsForm'] as $name=>$value)
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
	public function actionDelete()
	{
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			foreach ($ids as $id) {

				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax_group=:id_tax_group'; 
				$criteria->params=array(':id_tax_group'=>$id); 					
				
				// delete all
				Tbl_TaxGroup::model()->deleteByPk($id);
								
				Tbl_Product::model()->updateAll(array('id_tax_group'=>0),$criteria);						
			}
		}
	}		 
	
	public function actionXml_list_product_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_tax_group'])){
			$id_tax_group = (int)$_GET['id_tax_group'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			// filters
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
				$params[':name']=$filters['name'];
			}
			
			// sku
			if (isset($filters['sku']) && !empty($filters['sku'])) {
				$where[] = 'product.sku LIKE CONCAT("%",:sku,"%")';
				$params[':sku']=$filters['sku'];
			}
			
			// tax group
			if (isset($filters['tax_group']) && !empty($filters['tax_group'])) {
				$where[] = 'tax_group.name LIKE CONCAT("%",:tax_group,"%")';
				$params[':tax_group']=$filters['tax_group'];
			}
			
			$where[]='product.active=:active';
			$params[':active']="1";		
			
			
			$sql = "SELECT 
			COUNT(product.id) AS total  
			FROM 
			product 
			INNER JOIN 
			product_description 
			ON 
			(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			tax_group 
			ON 
			product.id_tax_group=tax_group.id
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
			product.id AS id_product,
			product_description.name,
			product.sku,
			tax_group.name AS tax_group_name,
			tax_group.id AS id_tax_group
			FROM 
			product 
			INNER JOIN 
			product_description 
			ON 
			(product.id = product_description.id_product AND product_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			tax_group 
			ON 
			product.id_tax_group=tax_group.id
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
				
			// sorting
			if (isset($sort_col[1]) && !empty($sort_col[1])) {	
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';	
				
				$sql .= " ORDER BY 
				product_description.name ".$direct;
			}else{
				if (isset($filters['name']) && !empty($filters['name'])) { 
					$sql.=" ORDER BY IF(product_description.name LIKE CONCAT(:name,'%'),0,1) ASC, product_description.name ASC";
				} else if (isset($filters['sku']) && !empty($filters['sku'])) { 
					$sql.=" ORDER BY IF(product.sku LIKE CONCAT(:sku,'%'),0,1) ASC, product.sku ASC";
				} else if (isset($filters['tax_group']) && !empty($filters['tax_group'])) { 
					$sql.=" ORDER BY IF(tax_group.name LIKE CONCAT(:tax_group,'%'),0,1) ASC, tax_group.name ASC";
				} else {
					$sql.=" ORDER BY product_description.name ASC";
				}				
			}			
			
			//add limits to query to get only rows necessary for the output
			//$sql.= " LIMIT ".$posStart.",".$count;
			
			$command=$connection->createCommand($sql);
			
			
			//set content type and xml tag
			header("Content-type:text/xml");
			
			//output data in XML format   
			echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
			
			// Cycle through results
			foreach ($command->queryAll(true, $params) as $row) {
				echo '<row id="'.$row['id_product'].'">' . 
				($row['tax_group_name'] ? '<cell type="ro"></cell>':'<cell type="ch" />').'
				<cell type="ro"><![CDATA['.$row['name'].']]></cell>
				<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
				<cell type="ro"><![CDATA['.$row['tax_group_name'].']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
		}
	}
	
	public function actionAdd_product()
	{
		$id_tax_group = $_POST['id_tax_group'];
		$ids = $_POST['ids'];

		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id_tax_group); 		
		
		if (!Tbl_TaxGroup::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product) {	
				if ($p = Tbl_Product::model()->findByPk($id_product)) {			
					$p->id_tax_group = $id_tax_group;
					if (!$p->save()){
						echo 'false';
						exit;
					}
				}
			}
		}
		echo $id_tax_group;
		
	}	
	
	public function actionXml_list_options_add($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		if(isset($_GET['id_tax_group'])){
			$id_tax_group = (int)$_GET['id_tax_group'];
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
			$posStart=(int)$posStart;
			$count=(int)$count;
			
			// filters
			
			// options group
			if (isset($filters['options_group']) && !empty($filters['options_group'])) {
				$where[] = 'options_group_description.name LIKE CONCAT("%",:options_group,"%")';
				$params[':options_group']=$filters['options_group'];
			}
			
			// name
			if (isset($filters['name']) && !empty($filters['name'])) {
				$where[] = 'options_description.name LIKE CONCAT("%",:name,"%")';
				$params[':name']=$filters['name'];
			}
			
			// sku
			if (isset($filters['sku']) && !empty($filters['sku'])) {
				$where[] = 'options.sku LIKE CONCAT("%",:sku,"%")';
				$params[':sku']=$filters['sku'];
			}
			
			// tax group
			if (isset($filters['tax_group']) && !empty($filters['tax_group'])) {
				$where[] = 'tax_group.name LIKE CONCAT("%",:tax_group,"%")';
				$params[':tax_group']=$filters['tax_group'];
			}
			
			$where[]='options.active=:active';
			$params[':active']="1";		
			
			
			$sql = "SELECT 
			COUNT(options.id) AS total  
			FROM 
			options 
			INNER JOIN 
			options_description 
			ON 
			(options.id = options_description.id_options AND options_description.language_code = '".Yii::app()->language."') 
			INNER JOIN 
			options_group 
			ON 
			(options.id_options_group = options_group.id) 
			INNER JOIN 
			options_group_description 
			ON 
			(options_group.id = options_group_description.id_options_group AND options_group_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			tax_group 
			ON 
			options.id_tax_group=tax_group.id
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
			options.id AS id_options,
			options_description.name,
			options.sku,
			options_group_description.name AS option_group_name,
			tax_group.name AS tax_group_name,
			tax_group.id AS id_tax_group
			FROM 
			options 
			INNER JOIN 
			options_description 
			ON 
			(options.id = options_description.id_options AND options_description.language_code = '".Yii::app()->language."') 
			INNER JOIN 
			options_group 
			ON 
			(options.id_options_group = options_group.id) 
			INNER JOIN 
			options_group_description 
			ON 
			(options_group.id = options_group_description.id_options_group AND options_group_description.language_code = '".Yii::app()->language."') 
			LEFT JOIN
			tax_group 
			ON 
			options.id_tax_group=tax_group.id
			".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
				
			// sorting
			if (isset($sort_col[1]) && !empty($sort_col[1])) {	
				$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';	
				
				$sql .= " ORDER BY 
				options_description.name ".$direct;
			}else{
				if (isset($filters['options_group']) && !empty($filters['options_group'])) {
					$sql.=" ORDER BY IF(options_group_description.name LIKE CONCAT(:options_group,'%'),0,1) ASC, options_group_description.name ASC";
				} else if (isset($filters['name']) && !empty($filters['name'])) { 
					$sql.=" ORDER BY IF(options_description.name LIKE CONCAT(:name,'%'),0,1) ASC, options_description.name ASC";
				} else if (isset($filters['sku']) && !empty($filters['sku'])) { 
					$sql.=" ORDER BY IF(options.sku LIKE CONCAT(:sku,'%'),0,1) ASC, options.sku ASC";
				} else if (isset($filters['tax_group']) && !empty($filters['tax_group'])) { 
					$sql.=" ORDER BY IF(tax_group.name LIKE CONCAT(:tax_group,'%'),0,1) ASC, tax_group.name ASC";
				} else {
					$sql.=" ORDER BY options_description.name ASC";
				}				
			}	
			
			//add limits to query to get only rows necessary for the output
			//$sql.= " LIMIT ".$posStart.",".$count;
			
			$command=$connection->createCommand($sql);
			
			
			//set content type and xml tag
			header("Content-type:text/xml");
			
			//output data in XML format   
			echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
			
			// Cycle through results
			foreach ($command->queryAll(true, $params) as $row) {
				echo '<row id="'.$row['id_options'].'">' . 
				($row['tax_group_name'] ? '<cell type="ro"></cell>':'<cell type="ch" />').'
				<cell type="ro"><![CDATA['.$row['option_group_name'].']]></cell>
				<cell type="ro"><![CDATA['.$row['name'].']]></cell>
				<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
				<cell type="ro"><![CDATA['.$row['tax_group_name'].']]></cell>
				</row>';
			}
			
			echo '</rows>';
			
		}
	}		
	
	public function actionAdd_options()
	{
		$id_tax_group = $_POST['id_tax_group'];
		$ids = $_POST['ids'];

		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id_tax_group); 		
		
		if (!Tbl_TaxGroup::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product) {	
				if ($p = Tbl_Options::model()->findByPk($id_product)) {			
					$p->id_tax_group = $id_tax_group;
					if (!$p->save()){
						echo 'false';
						exit;
					}
				}
			}
		}
		echo $id_tax_group;
		
	}
	
	/**
	 * This is the action to delete a product from the list
	 */
	public function actionDelete_product()
	{
		$id = (int)$_GET['id_tax_group'];
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax_group=:id_tax_group AND id=:id'; 
				$criteria->params=array(':id_tax_group'=>$id,':id'=>$id_product); 					
				
				// delete all
				Tbl_Product::model()->updateAll(array('id_tax_group'=>0),$criteria);					
			}
		}
	}
	
	/**
	 * This is the action to delete an option from the list
	 */
	public function actionDelete_option()
	{
		$id = (int)$_GET['id_tax_group'];
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id_product) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_tax_group=:id_tax_group AND id=:id'; 
				$criteria->params=array(':id_tax_group'=>$id,':id'=>$id_product); 					
				
				// delete all
				Tbl_Options::model()->updateAll(array('id_tax_group'=>0),$criteria);					
			}
		}
	}		
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_products($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$_GET['id_tax_group'];
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('product.id_tax_group=:id_tax_group AND product.id_tax_group!=0');
		$params=array(':id_tax_group'=>$id,':language_code'=>Yii::app()->language);
		
		// filters
			
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'product_description.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// sku
		if (isset($filters['sku']) && !empty($filters['sku'])) {
			$where[] = 'product.sku LIKE CONCAT(:sku,"%")';
			$params[':sku']=$filters['sku'];
		}
		
		// filters
		
		$sql = 'SELECT 
		COUNT(product.id) AS total
		FROM 
		product
		INNER JOIN
		product_description
		ON
		(product.id = product_description.id_product AND product_description.language_code = :language_code)
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = 'SELECT 
		product.id,
		product.sku,
		product_description.name
		FROM 
		product
		INNER JOIN
		product_description
		ON
		(product.id = product_description.id_product AND product_description.language_code = :language_code)		
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
		
		// sorting

		if (isset($filters['name']) && !empty($filters['name'])) {
			$sql.=" ORDER BY 
			IF(product_description.name LIKE CONCAT(:name,'%'),1,0) DESC, product_description.name ASC";
		}else{
			$sql.=" ORDER BY product_description.name ASC";
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
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}
	
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list_options($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$id=(int)$_GET['id_tax_group'];
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('options.id_tax_group=:id_tax_group AND options.id_tax_group!=0');
		$params=array(':id_tax_group'=>$id,':language_code'=>Yii::app()->language);
		
		// filters
			
		// options group
		if (isset($filters['options_group']) && !empty($filters['options_group'])) {
			$where[] = 'options_group_description.name LIKE CONCAT("%",:options_group,"%")';
			$params[':options_group']=$filters['options_group'];
		}
		
		// name
		if (isset($filters['name']) && !empty($filters['name'])) {
			$where[] = 'options_description.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// sku
		if (isset($filters['sku']) && !empty($filters['sku'])) {
			$where[] = 'options.sku LIKE CONCAT(:sku,"%")';
			$params[':sku']=$filters['sku'];
		}
		
		// filters
		
		$sql = 'SELECT 
		COUNT(options.id) AS total
		FROM 
		options
		INNER JOIN
		options_description
		ON
		(options.id = options_description.id_options AND options_description.language_code = :language_code)
		INNER JOIN 
		options_group 
		ON 
		(options.id_options_group = options_group.id) 
		INNER JOIN 
		options_group_description 
		ON 
		(options_group.id = options_group_description.id_options_group AND options_group_description.language_code = "'.Yii::app()->language.'") 
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
		
		//if this is the first query - get total number of records in the query result
		if($posStart==0){
			/* Select queries return a resultset */
			$command=$connection->createCommand($sql);
			$row = $command->queryRow(true, $params);
			$totalCount = $row['total'];		
		}
		
		//create query 
		$sql = 'SELECT 
		options.id,
		options.sku,
		options_description.name,
		options_group_description.name AS option_group_name
		FROM 
		options
		INNER JOIN
		options_description
		ON
		(options.id = options_description.id_options AND options_description.language_code = :language_code)
		INNER JOIN 
		options_group 
		ON 
		(options.id_options_group = options_group.id) 
		INNER JOIN 
		options_group_description 
		ON 
		(options_group.id = options_group_description.id_options_group AND options_group_description.language_code = "'.Yii::app()->language.'") 		
		'.(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');
		
		// sorting

		if (isset($filters['name']) && !empty($filters['name'])) {
			$sql.=" ORDER BY 
			IF(options_description.name LIKE CONCAT(:name,'%'),1,0) DESC, options_description.name ASC";
		}else{
			$sql.=" ORDER BY options_description.name ASC";
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
			<cell type="ro"><![CDATA['.$row['option_group_name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$row['sku'].']]></cell>
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