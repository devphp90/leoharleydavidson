<?php

class ImportController extends Controller
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
	 * This is the action to get an XML list of import tpl
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
			$where[] = 'import_tpl.name LIKE CONCAT("%",:name,"%")';
			$params[':name']=$filters['name'];
		}
		
		// sku
		if (isset($filters['type']) && !empty($filters['type'])) {
			$where[] = 'import_tpl.type = :type';
			$params[':type']=$filters['type'];
		}								
		
		$sql = "SELECT 
		COUNT(import_tpl.id) AS total 
		FROM 
		import_tpl 
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
		import_tpl.id,
		import_tpl.type,
		import_tpl.name
		FROM 
		import_tpl
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// name
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY import_tpl.name ".$direct;
		// type
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY import_tpl.type ".$direct;
		} else {
			$sql.=" ORDER BY import_tpl.id ASC";
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
					$type = Yii::t('views/import/edit_options','LABEL_TYPE_0');
					break;
				case 1:
					$type = Yii::t('views/import/edit_options','LABEL_TYPE_1');
					break;
				case 2:
					$type = Yii::t('views/import/edit_options','LABEL_TYPE_2');
					break;
				case 3:
					$type = Yii::t('views/import/edit_options','LABEL_TYPE_3');
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
	 * This is the action to edit or create an import tpl
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new ImportForm;	
		
		$id = (int)$id;
		
		if ($id) { 
			if (!$p = Tbl_ImportTpl::model()->findByPk($id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}		

			$model->id = $p->id;				
		}			
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));	
	}	
	
	public function actionEdit_options($id=0, $container)
	{
		$model = new ImportForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($p = Tbl_ImportTpl::model()->findByPk($id)) {
				$model->id = $p->id;
				$model->name = $p->name;
				$model->type = $p->type;
				$model->subtract_qty = $p->subtract_qty;
				
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
		$model = new ImportForm;
		
		// collect user input data
		if(isset($_POST['ImportForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ImportForm'] as $name=>$value)
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
					// add products
					case 0:
						// check for mandatory columns
						
						// sku is mandatory
						$criteria=new CDbCriteria; 
						$criteria->condition='id_import_tpl=:id_import_tpl AND id_import_columns=:id_import_columns'; 
						$criteria->params=array(':id_import_tpl'=>$model->id,':id_import_columns'=>7); 														
						
						if (!Tbl_ImportTplColumns::model()->find($criteria)) {
							$p = new Tbl_ImportTplColumns;
							$p->id_import_tpl = $model->id;
							$p->id_import_columns = 7;
							$p->save();
						}						
						break;
					// add / update products
					case 1:
						// check for mandatory columns
						
						// sku is mandatory
						$criteria=new CDbCriteria; 
						$criteria->condition='id_import_tpl=:id_import_tpl AND id_import_columns=:id_import_columns'; 
						$criteria->params=array(':id_import_tpl'=>$model->id,':id_import_columns'=>7); 														
						
						if (!Tbl_ImportTplColumns::model()->find($criteria)) {
							$p = new Tbl_ImportTplColumns;
							$p->id_import_tpl = $model->id;
							$p->id_import_columns = 7;
							$p->save();
						}	
						break;	
					// update products					
					case 2:
						// check for mandatory columns
						
						// sku is mandatory
						$criteria=new CDbCriteria; 
						$criteria->condition='id_import_tpl=:id_import_tpl AND id_import_columns=:id_import_columns'; 
						$criteria->params=array(':id_import_tpl'=>$model->id,':id_import_columns'=>7); 														
						
						if (!Tbl_ImportTplColumns::model()->find($criteria)) {
							$p = new Tbl_ImportTplColumns;
							$p->id_import_tpl = $model->id;
							$p->id_import_columns = 7;
							$p->save();
						}	
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
		$file_path = dirname(Yii::app()->getBasePath()).'/protected/import_files/';
		
		if (is_array($ids) && sizeof($ids)) {			
			// Verify if product is in a combo
			foreach ($ids as $id) {			
				// delete files
				foreach (Tbl_ImportTplFiles::model()->findAll('id_import_tpl=:id_import_tpl',array(':id_import_tpl'=>$id)) as $p) {
					if (is_file($file_path.$p->source)) @unlink($file_path.$p->source);
					
					$p->delete();	
				}
				
				// delete columns
				Tbl_ImportTplColumns::model()->deleteAll('id_import_tpl=:id_import_tpl',array(':id_import_tpl'=>$id));				
				Tbl_ImportTpl::model()->deleteByPk($id);
			}
		}
	}		
	
	public function actionEdit_column_options($container, $id=0)
	{
		$model = new ImportColumnForm;
		
		$id = (int)$id;
		
		if (!$p = Tbl_ImportTpl::model()->findByPk($id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));
		
		$model->id_import_tpl = $p->id;
		$model->type_import_tpl = $p->type;
		
		$this->renderPartial('edit_column_options',array('model'=>$model, 'container'=>$container));	
	}		
	
	/**
	 * This is the action to save
	 */
	public function actionSave_column()
	{
		$model = new ImportColumnForm;
		
		// collect user input data
		if(isset($_POST['ImportColumnForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ImportColumnForm'] as $name=>$value)
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
	
	public function actionGet_column_languages($id,$id_import_columns,$container)
	{
		$id = (int)$id;
		$id_import_columns = (int)$id_import_columns;
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$model = new ImportColumnForm;
		$model_name = get_class($model);
		
		$sql = 'SELECT
		language.code,
		language.name
		FROM
		language
		LEFT JOIN
		import_tpl_columns
		ON
		(language.code = import_tpl_columns.extra AND import_tpl_columns.id_import_tpl = :id_import_tpl AND import_tpl_columns.id_import_columns = :id_import_columns)
		WHERE
		language.active = 1 
		AND
		import_tpl_columns.id_import_columns IS NULL
		ORDER BY 
		language.default_language DESC,
		language.name ASC';
		$command=$connection->createCommand($sql);		
		
		foreach ($command->queryAll(true, array(':id_import_tpl'=>$id,':id_import_columns'=>$id_import_columns)) as $row) {
			 echo '<div style="margin-top:5px;">'.CHtml::checkBox($model_name.'[languages]['.$row['code'].']',0,array('id'=>$container.'_select_languages_'.$row['code'],'value'=>$row['code'])).'&nbsp;<label for="'.$container.'_select_languages_'.$row['code'].'" style="display:inline;">'.$row['name'].'</label></div>';
		}	
	}
	
	/**
	 * This is the action to get an XML list of import tpl columns
	 */
	public function actionXml_list_columns($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		
		$id=(int)$_GET['id'];

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('import_tpl_columns.id_import_tpl = :id_import_tpl');
		$params=array(':id_import_tpl'=>$id);
		
		// filters
						
		
		$sql = "SELECT 
		COUNT(import_tpl_columns.id_import_tpl) AS total 
		FROM 
		import_tpl_columns
		INNER JOIN
		import_tpl
		ON
		(import_tpl_columns.id_import_tpl = import_tpl.id) 
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
		import_tpl_columns.id,
		import_tpl_columns.id_import_columns,
		import_tpl_columns.extra,
		import_tpl.type
		FROM 
		import_tpl_columns
		INNER JOIN
		import_tpl
		ON
		(import_tpl_columns.id_import_tpl = import_tpl.id) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY import_tpl_columns.sort_order ASC";
		
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
			case 1:
			case 2:
				$label = 'PRODUCT';
				
				$no_delete = array(
					7,
				);
				break;				
			case 3:
				$label = 'CATEGORY';
				
				$no_delete = array(
				);				
				break;
		}
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {			
			echo '<row id="'.$row['id'].'">
			'.(!in_array($row['id_import_columns'],$no_delete) ? '<cell type="ch" />':'<cell type="ro" />').'
			<cell type="ro"><![CDATA['.Yii::t('views/import/edit_options','LABEL_COLUMN_'.$label.'_'.$row['id_import_columns']).($row['extra'] ? ' ('.$row['extra'].')':'').']]></cell>
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
			foreach ($ids as $id_import_tpl_columns) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_import_tpl=:id AND id=:id_import_tpl_columns'; 
				$criteria->params=array(':id'=>$id,':id_import_tpl_columns'=>$id_import_tpl_columns); 					
								
				if ($p = Tbl_ImportTplColumns::model()->find($criteria)) {
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
				$model = Tbl_ImportTplColumns::model()->findByPk($id);
				$id_import_tpl = $model->id_import_tpl;
				$id_import_columns = $model->id_import_columns;
				
				switch ($id_import_columns) {
					//category
					case 27:
					if(Tbl_ImportTplColumns::model()->count('id_import_tpl=:id_import_tpl and id_import_columns = 27',array(':id_import_tpl'=>$id_import_tpl))==1){$to_delete[] = array(':id_import_columns'=>28,':id_import_tpl'=>$id_import_tpl);};
					// special price
					case 12:
					// special price from date
					case 13:
					// special price to date
					case 14:
						if ($id_import_columns != 12) $to_delete[] = array(':id_import_columns'=>12,':id_import_tpl'=>$id_import_tpl);
						if ($id_import_columns != 13) $to_delete[] = array(':id_import_columns'=>13,':id_import_tpl'=>$id_import_tpl);
						if ($id_import_columns != 14) $to_delete[] = array(':id_import_columns'=>14,':id_import_tpl'=>$id_import_tpl);						
						break;
				}
				
				Tbl_ImportTplColumns::model()->deleteByPk($id);
			}
			
			if (sizeof($to_delete)) {
				foreach ($to_delete as $row) {
					Tbl_ImportTplColumns::model()->deleteAll('id_import_columns=:id_import_columns AND id_import_tpl=:id_import_tpl',array(':id_import_columns'=>$row[':id_import_columns'],':id_import_tpl'=>$row[':id_import_tpl']));
				}
			}
		}
	}		
	
	/**
	 * This is the action to get an XML list of import tpl files
	 */
	public function actionXml_list_imported_files($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		
		$id=(int)$_GET['id'];

		// get list of product groups
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array('import_tpl_files.id_import_tpl = :id_import_tpl');
		$params=array(':id_import_tpl'=>$id);
		
		// filters
						
		
		$sql = "SELECT 
		COUNT(import_tpl_files.id_import_tpl) AS total 
		FROM 
		import_tpl_files
		INNER JOIN
		import_tpl
		ON
		(import_tpl_files.id_import_tpl = import_tpl.id) 
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
		import_tpl_files.id,
		import_tpl_files.pid,
		import_tpl_files.type,
		import_tpl_files.filename,
		import_tpl_files.source,
		import_tpl_files.errors,
		import_tpl_files.status,
		import_tpl_files.date_created,
		import_tpl_files.date_imported
		FROM 
		import_tpl_files
		INNER JOIN
		import_tpl
		ON
		(import_tpl_files.id_import_tpl = import_tpl.id) 
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting
		$sql.=" ORDER BY import_tpl_files.date_created DESC";
		
		//add limits to query to get only rows necessary for the output
		$sql.= " LIMIT ".$posStart.",".$count;
		
		$command=$connection->createCommand($sql);
		
		//set content type and xml tag
		header("Content-type:text/xml");
		
		//output data in XML format   
		echo '<rows total_count="'.$totalCount.'" pos="'.$posStart.'">';		
		
		// check if import is running				
		$pid = $this->getImportPID();
		
		// Cycle through results
		foreach ($command->queryAll(true, $params) as $row) {			
			$errors = 0;
			
			if (!$row['pid'] && $row['status'] != 0 && $row['status'] != 3) $errors = 1;
			
			switch ($row['type']) {
				case 0:
					$type = Yii::t('views/import/edit_options','LABEL_TYPE_0');
					break;
				case 1:
					$type = Yii::t('views/import/edit_options','LABEL_TYPE_1');
					break;
				case 2:
					$type = Yii::t('views/import/edit_options','LABEL_TYPE_2');
					break;
				case 3:
					$type = Yii::t('views/import/edit_options','LABEL_TYPE_3');
					break;
			}	
			
			$target_path = dirname(Yii::app()->getBasePath()).'/protected/import_files/';
		
			echo '<row id="'.$row['id'].'" '.($errors ? 'style="color:#F00;"':'').'>
			<cell type="ch" />
			<cell type="ro"><![CDATA['.$row['filename'].($pid && $pid == $row['pid'] ? ' ('.Yii::t('controllers/ImportController','LABEL_RUNNING').')':'').']]></cell>
			<cell type="ro"><![CDATA['.$type.']]></cell>
			<cell type="ro"><![CDATA['.round(filesize($target_path.$row['source'])/1048576,2).' MB]]></cell>
			<cell type="ro"><![CDATA['.$row['date_created'].']]></cell>
			<cell type="ro"><![CDATA['.($row['date_imported'] != '0000-00-00 00:00:00' ? $row['date_imported']:'').']]></cell>
			</row>';
		}
		
		echo '</rows>';
	}			
	
	public function actionEdit_upload_files_options($container, $id)
	{	
		$id = (int)$id;	
	
		$this->renderPartial('edit_upload_files_options',array('container'=>$container, 'id'=>$id));	
	}					
	
	/**
	 * This is the action to upload files
	 */
	public function actionUpload_file($id)
	{					
		$app = Yii::app();
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = $app->user->getId();	
		$id = (int)$id;		
		
		if (!$p = Tbl_ImportTpl::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		// if id is not set
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$target_path = dirname(Yii::app()->getBasePath()).'/protected/import_files/';
			$targetFile = $_FILES['Filedata']['name'];
			$ext = strtolower(trim(pathinfo($targetFile, PATHINFO_EXTENSION)));
			$force_crop = 0;
			$allowed_ext = array(
				'csv',
			);									
			
			if (empty($ext) || !in_array($ext, $allowed_ext)) {
				echo Yii::t('controllers/ImportController','ERROR_ALLOWED_FILES');				
				exit;
			} else {	
				$new_filename = md5(time().$targetFile).'.'.$ext;
				
				if (($handle = @fopen($tempFile, "r")) && ($nf_handle = @fopen($target_path.$new_filename,"w"))) {					
					while (($buffer = fgets($handle)) !== false) {
						fwrite($nf_handle,mb_convert_encoding($buffer, "UTF-8", "auto,ISO-8859-1"));
					}
					fclose($handle);
					fclose($nf_handle);								
			
			//	if (move_uploaded_file($tempFile,$target_path . $new_filename)) {			
					// insert new file
					$upload_file = new Tbl_ImportTplFiles;
					$upload_file->id_import_tpl = $id;
					$upload_file->filename = $targetFile;
					$upload_file->source = $new_filename;
					$upload_file->id_user_created = $current_id_user;
					$upload_file->date_created = $current_datetime;
					$upload_file->type = $p->type;
					
					if (!$upload_file->save()) {
						echo Yii::t('global','ERROR_SAVING');
						exit;
					}
	
					echo 'id_import_tpl_files:'.$upload_file->id;
					exit;
				} else {
					echo Yii::t('global','ERROR_SAVING');
					exit;
				}
				break;
			}
			
			echo Yii::t('controllers/ImportController','ERROR_UPLOAD_FILE_FAILED');
		}			
	}	
	
	public function actionEdit_import_files_options($container, $id)
	{	
		$model=new ImportFileForm;	
	
		$id = (int)$id;	

		if (!$p = Tbl_ImportTplFiles::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		

		$model->id = $p->id;	
		$model->id_import_tpl = $p->id_import_tpl;
		$model->id_import_tpl_type = Tbl_ImportTpl::model()->findByPk($p->id_import_tpl)->type;
		$model->filename = $p->filename;
		$model->source = $p->source;
		$model->status = $p->status;
		$model->columns_separated_with = $p->columns_separated_with ? $p->columns_separated_with:$model->columns_separated_with;
		$model->columns_enclosed_with = $p->columns_enclosed_with ? $p->columns_enclosed_with:$model->columns_enclosed_with;
		$model->columns_escaped_with = $p->columns_escaped_with ? $p->columns_escaped_with:$model->columns_escaped_with;
		$model->skip_first_row = $p->skip_first_row;
		$model->set_active = $p->set_active;
		$model->pid = $p->pid;
		$model->errors = $p->errors;
		$model->date_imported = $p->date_imported;		
		
		// check if import is running
		$pid = $this->getImportPID();
		$count_error = sizeof(unserialize(base64_decode($p->errors)));
		if (!$p->errors && $p->date_imported == '0000-00-00 00:00:00') $output = array('incomplete'=>1,'output'=>$this->renderPartial('edit_import_files_options',array('container'=>$container, 'model'=>$model, 'pid'=>$pid),1));	
		else if ($p->errors) $output = '<div style="width:100%; height:100%; overflow:auto;"><div style="padding:10px;"><div><strong>'.($count_error>100?Yii::t('controllers/ImportController','LABEL_IMPORT_ERROR_MORE', array("{error}" => ($count_error-1))):Yii::t('controllers/ImportController','LABEL_IMPORT_ERROR', array("{error}" => $count_error))).'</strong></div><pre>'.implode("\r\n\r\n",unserialize(base64_decode($p->errors))).'</pre></div></div>';
		else $output = '<div style="width:100%; height:100%; overflow:auto;"><div style="padding:10px;"><strong>'.Yii::t('controllers/ImportController','LABEL_IMPORT_SUCCESS',array('{date}'=>$p->date_imported)).'</strong></div></div>';
		
		echo json_encode($output);
	}
	
	/**
	 * This is the action to get an XML list of preview import
	 */
	public function actionXml_list_preview_column()
	{	

		$model = new ImportFileForm;
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection		
		
		//set content type and xml tag
		header("Content-type:text/xml, charset=UTF-8; encoding=UTF-8");
		
		//output data in XML format   
		echo '<rows>';
				
				
		// collect user input data
		if(isset($_GET['ImportFileForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_GET['ImportFileForm'] as $name=>$value)
			{
				$model->$name=$value;
			}		
			
			if (!$p = Tbl_ImportTplFiles::model()->findByPk($model->id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
			
			$file_path = dirname(Yii::app()->getBasePath()).'/protected/import_files/';
				
			$row = 1;
			$x=1;
			$rows = array();
			
			setlocale(LC_ALL, 'en_US.UTF8');
			
			if (($handle = fopen($file_path.$p->source, "r")) !== FALSE) {
				while (($data = fgetcsv($handle,0,$model->columns_separated_with,$model->columns_enclosed_with,$model->columns_escaped_with))) {					
					if ($row == 4) break;
					
					if (!$model->skip_first_row || $model->skip_first_row && $x > 1) {						
						$i=1;
						foreach ($data as $value) {						
							$rows[$row][$i] = $value;
							++$i;
						}
						++$row;			
					}
																						
					++$x;
				}
				fclose($handle);
			} 
			
			//create query 
			$sql = "SELECT 
			import_tpl_columns.id,
			import_tpl_columns.id_import_columns,
			import_tpl_columns.extra,
			import_tpl.type
			FROM 
			import_tpl_columns
			INNER JOIN
			import_tpl
			ON
			(import_tpl_columns.id_import_tpl = import_tpl.id) 
			WHERE
			import_tpl.id = :id 
			ORDER BY import_tpl_columns.sort_order ASC";			
			
			$command=$connection->createCommand($sql);
			
			switch ($row['type']) {
				case 0:
				case 1:
				case 2:
					$label = 'PRODUCT';
					break;				
				case 3:
					$label = 'CATEGORY';
					break;
			}			
			
			// Cycle through results
			$i=1;
			foreach ($command->queryAll(true, array(':id'=>$p->id_import_tpl)) as $row) {			
				echo '<row id="'.$i.'">
				<cell type="ro"><![CDATA['.Yii::t('views/import/edit_options','LABEL_COLUMN_'.$label.'_'.$row['id_import_columns']).($row['extra'] ? ' ('.$row['extra'].')':'').']]></cell>	
				<cell type="ro"><![CDATA['.(isset($rows[1][$i]) ? $rows[1][$i]:'').']]></cell>
				<cell type="ro"><![CDATA['.(isset($rows[2][$i]) ? $rows[2][$i]:'').']]></cell>
				<cell type="ro"><![CDATA['.(isset($rows[3][$i]) ? $rows[3][$i]:'').']]></cell>
				</row>';
				
				++$i;
			}
		}
			
		echo '</rows>';
	}		
	
	public function actionExport_template($title)
	{
		$title = trim($title);
		$columns = $_GET['columns'];
		
		// output headers so that the file is downloaded rather than displayed
//		header('Content-Type: text/csv; charset=utf-8');
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
		header("Pragma: public");  
		header ("Content-Type: application/octet-stream; charset=utf-8");  
		
		header('Content-Disposition: attachment; filename="'.$title.'.csv"');
		
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');
		
		if (sizeof($columns)) {
			// output the column headings
			fputcsv($output, $columns);
		}
		
		fclose($output);
		exit;		
	}
	
	public function actionImport_file()
	{
		$model = new ImportFileForm;
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection						
		$app = Yii::app();
				
		// collect user input data
		if(isset($_POST['ImportFileForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['ImportFileForm'] as $name=>$value)
			{
				$model->$name=$value;
			}		
			
			if (!$p = Tbl_ImportTplFiles::model()->findByPk($model->id)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
															
			// check if import is running				
			if($this->getImportPID()) { 
				echo json_encode(array('errors' => Yii::t('controllers/ImportController','ERROR_IMPORT_IN_PROGRESS')));
				exit;
			} 
			
			$descriptorspec = array(
			   array('pipe', 'r'),               // stdin 
			   array('pipe', 'w'),				 // stdout 
			   array('pipe', 'w'),               // stderr 			
			);
			$pipes = array();			
			$cwd = '/tmp';
			$env = array(
				'id' => $model->id,
				'columns_separated_with' => $model->columns_separated_with,
				'columns_enclosed_with' => $model->columns_enclosed_with,
				'columns_escaped_with' => $model->columns_escaped_with,
				'skip_first_row' => $model->skip_first_row,
				'set_active' => $model->set_active,
				'id_user' => Yii::app()->user->getId(),
			);			
			
			// start process 
			//proc_open('php '.$this->getImportScriptPath().' &', $descriptorspec, $pipes, $cwd, $env);
			proc_open('php '.$this->getImportScriptPath().' &> '.dirname($this->getImportScriptPath()).'/import.log &', $descriptorspec, $pipes, $cwd, $env);
			
			$p->pid = $this->getImportPID();
			$p->save();	
			
			echo 'true';
		} else {
			echo 'false';	
		}
	}
	
	public function actionDownload_file($id)
	{
		$id=(int)$id;
		$file_path = dirname(Yii::app()->getBasePath()).'/protected/import_files/';
		
		if (!$p = Tbl_ImportTplFiles::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}

		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");  
		header("Pragma: public");  
		header("Content-Type: application/octet-stream; charset=utf-8");  
		
		header('Content-Disposition: attachment; filename="'.$p->filename.'"');
		
		//setlocale(LC_ALL, 'en_US.UTF8');
		
		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'wb');
		
		if ($handle = @fopen($file_path.$p->source, "rb")) {					
			while (($buffer = fgets($handle)) !== false) {
				fwrite($output,$buffer);
			}
			fclose($handle);
		}
		
		fclose($output);
		exit;	
	}
	
	/**
	 * This is the action to delete a file
	 */
	public function actionDelete_file()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		$ids = $_POST['ids'];
		$file_path = dirname(Yii::app()->getBasePath()).'/protected/import_files/';
		
		if (is_array($ids) && sizeof($ids)) {			
			// Verify if product is in a combo
			foreach ($ids as $id) {
				if ($p = Tbl_ImportTplFiles::model()->findByPk($id)) {
					if (is_file($file_path.$p->source)) @unlink($file_path.$p->source);
					
					$p->delete();	
				}
			}
		}
	}	
	
	/**
	 * This is a function to get import script file path
	 */	
	public function getImportScriptPath()
	{
		return realpath(dirname(__FILE__).'/../components/').'/import-script.php';
	}
	
	/**
	 * This is a function to get pid of current import if any
	 */
	public function getImportPID()
	{
		$file_path = $this->getImportScriptPath();
		
		exec('pgrep -u '.get_current_user().' -f "'.$file_path.'"', $pids); 		
		$pid = is_array($pids) && sizeof($pids) ? $pids[0]:0;
		
		return $pid;
	}
	 
	/** 
	 * This is the action to check if we have an import running
	 */
	public function actionCheck_import_status()
	{
		$pid = $this->getImportPID();
		
		$update = Tbl_ImportTplFiles::model()->updateAll(array('pid'=>0),$pid ? 'pid != :pid':'',$pid ? array(':pid'=>$pid):array());
		
		echo $update ? 'true':'false';
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