<?php

class ExportController extends Controller
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
	 * This is the action to get an XML list of export tpl
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
			$where[] = 'export_tpl.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// sku
		if (isset($filters['type']) && !empty($filters['type'])) {
			$where[] = 'export_tpl.type = :type';
			$params[':type']=$filters['type'];
		}								
		
		$sql = "SELECT 
		COUNT(export_tpl.id) AS total 
		FROM 
		export_tpl 
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
		export_tpl.id,
		export_tpl.type,
		export_tpl.name
		FROM 
		export_tpl
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY export_tpl.name ".$direct;
		// type
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY export_tpl.type ".$direct;
		} else {
			$sql.=" ORDER BY export_tpl.id ASC";
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
			switch ($row['type']) {
				case 0:
					$type = Yii::t('views/export/edit_options','LABEL_TYPE_0');
					break;
				case 1:
					$type = Yii::t('views/export/edit_options','LABEL_TYPE_1');
					break;
				case 2:
					$type = Yii::t('views/export/edit_options','LABEL_TYPE_2');
					break;
				case 3:
					$type = Yii::t('views/export/edit_options','LABEL_TYPE_3');
					break;
			}			
			
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['name'].']]></cell>
			<cell type="ro"><![CDATA['.$type.']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}	
	
	/**
	 * This is the action to edit or create an export tpl
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new ExportForm;	
		
		$id = (int)$id;
		
		if ($id) { 
			if (!$p = Tbl_ExportTpl::model()->findByPk($id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		

			$model->id = $p->id;				
		}			
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));	
	}	
	
	public function actionEdit_options($id=0, $container)
	{
		$model = new ExportForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($p = Tbl_ExportTpl::model()->findByPk($id)) {
				$model->id = $p->id;
				$model->name = $p->name;
				$model->type = $p->type;
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_options',array('model'=>$model, 'container'=>$container));	
	}	
	
	/**
	 * This is the action to save
	 */
	public function actionSave()
	{
		$model = new ExportForm;
		
		// collect user input data
		if(isset($_POST['ExportForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ExportForm'] as $name=>$value)
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
			} else if ($model->id) {
				// check type
				switch ($model->type){
					// products
					case 0:
						// check for mandatory columns
						
						/*
						// sku is mandatory
						$criteria=new CDbCriteria; 
						$criteria->condition='id_export_tpl=:id_export_tpl AND id_export_columns=:id_export_columns'; 
						$criteria->params=array(':id_export_tpl'=>$model->id,':id_export_columns'=>7); 														
						
						if (!Tbl_ExportTplColumns::model()->find($criteria)) {
							$p = new Tbl_ExportTplColumns;
							$p->id_export_tpl = $model->id;
							$p->id_export_columns = 7;
							$p->save();
						}*/						
						break;										
				}
			}
								
			echo CJSON::encode($output);	
		}
	}		
	
	/**
	 * This is the action to delete a template
	 */
	public function actionDelete()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$ids = $_POST['ids'];
		$file_path = dirname(Yii::app()->getBasePath()).'/protected/export_files/';
		
		if (is_array($ids) && sizeof($ids)) {			
			// Verify if product is in a combo
			foreach ($ids as $id) {			
				// delete files
				foreach (Tbl_ExportTplFiles::model()->findAll('id_export_tpl=:id_export_tpl',array(':id_export_tpl'=>$id)) as $p) {
					if (is_file($file_path.$p->source)) @unlink($file_path.$p->source);
					
					$p->delete();	
				}
				
				// delete columns
				Tbl_ExportTplColumns::model()->deleteAll('id_export_tpl=:id_export_tpl',array(':id_export_tpl'=>$id));				
				Tbl_ExportTpl::model()->deleteByPk($id);
			}
		}
	}		
	
	public function actionEdit_column_options($container, $id=0)
	{
		$model = new ExportColumnForm;
		
		$id = (int)$id;
		
		if (!$p = Tbl_ExportTpl::model()->findByPk($id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));
		
		$model->id_export_tpl = $p->id;
		$model->type_export_tpl = $p->type;
		
		$this->renderPartial('edit_column_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to save
	 */
	public function actionSave_column()
	{
		$model = new ExportColumnForm;
		
		// collect user input data
		if(isset($_POST['ExportColumnForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ExportColumnForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$output = array();
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}		
	
	public function actionGet_column_languages($id,$id_export_columns,$container)
	{
		$id = (int)$id;
		$id_export_columns = (int)$id_export_columns;
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$model = new ExportColumnForm;
		$model_name = get_class($model);
		
		$sql = 'SELECT
		language.code,
		language.name
		FROM
		language
		LEFT JOIN
		export_tpl_columns
		ON
		(language.code = export_tpl_columns.extra AND export_tpl_columns.id_export_tpl = :id_export_tpl AND export_tpl_columns.id_export_columns = :id_export_columns)
		WHERE
		language.active = 1 
		AND
		export_tpl_columns.id_export_columns IS NULL
		ORDER BY 
		language.default_language DESC,
		language.name ASC';
		$command=$connection->createCommand($sql);		
		
		foreach ($command->queryAll(true, array(':id_export_tpl'=>$id,':id_export_columns'=>$id_export_columns)) as $row) {
			 echo '<div style="margin-top:5px;">'.CHtml::checkBox($model_name.'[languages]['.$row['code'].']',0,array('id'=>$container.'_select_languages_'.$row['code'],'value'=>$row['code'])).'&nbsp;<label for="'.$container.'_select_languages_'.$row['code'].'" style="display:inline;">'.$row['name'].'</label></div>';
		}	
	}
	
	/**
	 * This is the action to get an XML list of export tpl columns
	 */
	public function actionXml_list_columns($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		
		$id=(int)$_GET['id'];

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('export_tpl_columns.id_export_tpl = :id_export_tpl');
		$params=array(':id_export_tpl'=>$id);
		
		// filters
						
		
		$sql = "SELECT 
		COUNT(export_tpl_columns.id_export_tpl) AS total 
		FROM 
		export_tpl_columns
		INNER JOIN
		export_tpl
		ON
		(export_tpl_columns.id_export_tpl = export_tpl.id) 
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
		export_tpl_columns.id,
		export_tpl_columns.id_export_columns,
		export_tpl_columns.extra,
		export_tpl.type
		FROM 
		export_tpl_columns
		INNER JOIN
		export_tpl
		ON
		(export_tpl_columns.id_export_tpl = export_tpl.id) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY export_tpl_columns.sort_order ASC";
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';
		
		$no_delete = array();
		
		switch ($row['type']) {
			case 0:
				$label = 'PRODUCT';
				
				$no_delete = array(
				);
				break;				
		}
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {			
			echo '<row id="'.$row['id'].'">
			'.(!in_array($row['id_export_columns'],$no_delete) ? '<cell type="ch" />':'<cell type="ro" />').'
			<cell type="ro"><![CDATA['.Yii::t('views/export/edit_options','LABEL_COLUMN_'.$label.'_'.$row['id_export_columns']).($row['extra'] ? ' ('.$row['extra'].')':'').']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}		
	
	/**
	 * This is the action to save column order
	 */
	public function actionSave_column_sort_order($id)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$i=0;
			foreach ($ids as $id_export_tpl_columns) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_export_tpl=:id AND id=:id_export_tpl_columns'; 
				$criteria->params=array(':id'=>$id,':id_export_tpl_columns'=>$id_export_tpl_columns); 					
								
				if ($p = Tbl_ExportTplColumns::model()->find($criteria)) {
					$p->sort_order = $i;
					$p->save();
					
					++$i;
				}
			}
		}
	}	
	
	/**
	 * This is the action to delete a column
	 */
	public function actionDelete_column()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {			
			// Verify if product is in a combo
			$to_delete = array();
			
			foreach ($ids as $id) {
				$model = Tbl_ExportTplColumns::model()->findByPk($id);
				$id_export_tpl = $model->id_export_tpl;
				$id_export_columns = $model->id_export_columns;
								
				Tbl_ExportTplColumns::model()->deleteByPk($id);
			}
			
			if (sizeof($to_delete)) {
				foreach ($to_delete as $row) {
					Tbl_ExportTplColumns::model()->deleteAll('id_export_columns=:id_export_columns AND id_export_tpl=:id_export_tpl',array(':id_export_columns'=>$row[':id_export_columns'],':id_export_tpl'=>$row[':id_export_tpl']));
				}
			}
		}
	}		
	
	/**
	 * This is the action to get an XML list of export tpl files
	 */
	public function actionXml_list_exported_files($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		
		$id=(int)$_GET['id'];

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('export_tpl_files.id_export_tpl = :id_export_tpl');
		$params=array(':id_export_tpl'=>$id);
		
		// filters
						
		
		$sql = "SELECT 
		COUNT(export_tpl_files.id_export_tpl) AS total 
		FROM 
		export_tpl_files
		INNER JOIN
		export_tpl
		ON
		(export_tpl_files.id_export_tpl = export_tpl.id) 
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
		export_tpl_files.id,
		export_tpl_files.filename,
		export_tpl_files.date_created
		FROM 
		export_tpl_files
		INNER JOIN
		export_tpl
		ON
		(export_tpl_files.id_export_tpl = export_tpl.id) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY export_tpl_files.date_created DESC";
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';		
			
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {					
			$target_path = dirname(Yii::app()->getBasePath()).'/protected/export_files/';
		
			echo '<row id="'.$row['id'].'">
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['filename'].']]></cell>
			<cell type="ro"><![CDATA['.round(filesize($target_path.$row['filename'])/1048576,2).' MB]]></cell>
			<cell type="ro"><![CDATA['.$row['date_created'].']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}			
	
	public function actionEdit_export_files_options($container, $id)
	{	
		$model = new ExportFileForm;
	
		$id = (int)$id;	

		if (!$p = Tbl_ExportTpl::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		

		$model->id_export_tpl = $p->id;		
		$model->type = $p->type;
		$model->filters = $p_file ? unserialize(base64_decode($p_file->filters)):$model->filters;
	
		$this->renderPartial('edit_export_files_options',array('container'=>$container, 'model'=>$model));			
	}
		
	public function actionExport()
	{
		$file_path = dirname(Yii::app()->getBasePath()).'/protected/export_files/';
	
		$model = new ExportFileForm;
		
		// collect user input data
		if(isset($_GET['ExportFileForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_GET['ExportFileForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			/*
			$output = array();
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	*/
		}				
	}
	
	public function actionDownload()
	{
		$file_path = dirname(Yii::app()->getBasePath()).'/protected/export_files/';
		
		$ids = $_GET['ids'];
		
		if (is_array($ids)) {
			// more than one zip
			if (sizeof($ids) > 1) {
				foreach ($ids as $id) {
					if (!$p = Tbl_ExportTplFiles::model()->findByPk($id)) {
						throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
					}	
				}			
			// return file	
			} else {
				$id = array_pop($ids);
				
				if (!$p = Tbl_ExportTplFiles::model()->findByPk($id)) {
					throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
				}	
				
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
				header("Pragma: public");  
				header("Content-Type: application/octet-stream; charset=utf-8");  
				
				header('Content-Disposition: attachment; filename="'.$p->filename.'"');
				
				//setlocale(LC_ALL, 'en_US.UTF8');
				
				// create a file pointer connected to the output stream
				$output = fopen('php://output', 'wb');
				
				if ($handle = @fopen($file_path.$p->filename, "rb")) {					
					while (($buffer = fgets($handle)) !== false) {
						fwrite($output,$buffer);
					}
					fclose($handle);
				}
				
				fclose($output);
			}
		}
	}
	
	/**
	 * This is the action to delete a file
	 */
	public function actionDelete_file()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$ids = $_POST['ids'];
		$file_path = dirname(Yii::app()->getBasePath()).'/protected/export_files/';
		
		if (is_array($ids) && sizeof($ids)) {			
			// Verify if product is in a combo
			foreach ($ids as $id) {
				if ($p = Tbl_ExportTplFiles::model()->findByPk($id)) {
					if (is_file($file_path.$p->source)) @unlink($file_path.$p->source);
					
					$p->delete();	
				}
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