<?php
class UsersController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array();
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
	 * This is the action to edit or create a user
	 */ 
	public function actionEdit($container, $id=0)	
	{
		
		$model=new UsersForm;	
		
		$id = (int)$id;
		
		$model->id = $id;
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container));
	}
	
	/**
	 * This is the action to edit each section
	 */ 
	public function actionEdit_section($container, $containerJS)	
	{						
		$id = trim($_POST['id']);
		$id_user = (int)$_POST['id_user'];
		
		if ($id_user) { 			
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id_user); 		
			
			if (!Tbl_User::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}			
		}
						
		$this->renderPartial($id,array('id'=>$id_user,'container'=>$container,'containerJS'=>$containerJS));	
	}		

	public function actionEdit_info_options($container, $id=0)
	{
		$model = new UsersForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($user = Tbl_User::model()->findByPk($id)) {
				$model->id = $user->id;
				$model->gender = $user->gender;
				$model->lastname = $user->lastname;
				$model->firstname = $user->firstname;
				$model->address = $user->address;
				$model->city = $user->city;
				$model->zip = $user->zip;
				$model->state = $user->state;	
				$model->country = $user->country;
				$model->phone_home = $user->phone_home;	
				$model->phone_cell = $user->phone_cell;	
				$model->default_language_code = $user->default_language_code;	
				$model->email = $user->email;	
				
				$connection=Yii::app()->db;
				
				foreach (Tbl_LinkedStore::model()->findAll() as $store) {
					if (!empty($store->database)) {
						// check if current user exists
						if ($connection = Html::connect_other_db($store->database)) {						
							$sql = 'SELECT 
							id
							FROM 
							user
							WHERE
							email = :email
							LIMIT 1';
							$command = $connection->createCommand($sql);
							
							if ($id_user = $command->queryScalar(array(':email'=>$user->email))) {
								$model->linked_store[$store->id] = 1;	
							}						
						}
					}
				}
				
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_info_options',array('model'=>$model, 'container'=>$container));	
	}
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave_info()
	{
		
		$model = new UsersForm;
			
		// collect user input data
		if(isset($_POST['UsersForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['UsersForm'] as $name=>$value){
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
	 * This is the action to get the list of province	
	 */
	public function actionGet_province_list()
	{
		$model=new UsersForm;
		
		$id_prefix = get_class($model);
		
		$country = trim($_POST['country']);
		
		echo Html::generateStateList($id_prefix.'[state]', $country, '', '', array('style'=>'min-width:80px;'));
	}

	public function actionEdit_login_info_options($container, $id=0)
	{
		$model = new UsersLoginForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($user = Tbl_User::model()->findByPk($id)) {
				$model->id = $user->id;
				$model->username = $user->username;
				$model->active = $user->active;	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_login_info_options',array('model'=>$model, 'container'=>$container));	
	}
	
	/**
	 * This is the action to save the information section	 
	 */
	public function actionSave_login_info()
	{
		
		$model = new UsersLoginForm;
			
		// collect user input data
		if(isset($_POST['UsersLoginForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['UsersLoginForm'] as $name=>$value){
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
	 * This is the action to get an XML list of permissions
	 */
	public function actionXml_list_permission($id=0)
	{		
		$id = (int)$id;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id=:id'; 
		$criteria->params=array(':id'=>$id); 		
		
		if (!Tbl_User::model()->count($criteria)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}			
	
		// xml header
		header("content-type: text/xml");	
		
		$eol = "\r\n";
		
		echo '<?xml version="1.0" encoding="utf-8"?>'.$eol;
		echo '<tree id="0">'.$eol;		
		
		$this->get_permissions($id);		
		
		echo '</tree>'.$eol;
	}
	
	/**
	 * This is a function to get a list of the permissions and sub permissions recursively
	 */
	public function get_permissions($id_user=0,$parent='')
	{
		$id_user = (int)$id_user;
		
		$connection=Yii::app()->db;
		$sql = "SELECT 
				a.name,
				ad.name_permission 
				FROM (AuthItem AS a)
				LEFT JOIN 
				(AuthItem_description AS ad) 
				ON 
				(a.name = ad.name_AuthItem AND language_code = '".Yii::app()->language."')
				LEFT JOIN
				(AuthItemChild AS ac)
				ON
				(a.name = ac.child)
				WHERE
				ac.parent " . (!empty($parent) ? "= '" . $parent . "'" : "IS NULL").'
				ORDER BY a.order ASC';
		$command = $connection->createCommand($sql);

		$eol = "\r\n";
			
		foreach ($command->queryAll(true) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='parent=:parent'; 
			$criteria2->params=array(':parent'=>$row["name"]); 						

			$child = Tbl_AuthItemChild::model()->count($criteria2) ? 1:0;	

			$criteria2->condition='userid=:userid AND itemname=:itemname'; 
			$criteria2->params=array(':userid'=>$id_user,':itemname'=>$row["name"]); 	
			
			$checked = Tbl_AuthAssignment::model()->count($criteria2) ? 1:'';
		
			echo '<item text="'.$row["name_permission"].'" id="'.$row["name"].'" child="'.$child.'" checked="'.$checked.'" call="true" open="1">'.$eol;
			
			if ($child) { $this->get_permissions($id_user, $row["name"]); }
			
			echo '</item>'.$eol;
		}

	}	
	/**
	 * This is the action to save permissions
	 */
	public function actionSave_permissions($id=0)
	{
		$id = (int)$id;
		$ids = $_POST['ids'];			
		
		if (!$user = Tbl_User::model()->findByPk($id)) {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}		
		
		$criteria=new CDbCriteria; 
		$criteria->condition='userid=:userid'; 
		$criteria->params=array(':userid'=>$id); 
		
		Tbl_AuthAssignment::model()->deleteAll($criteria);
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $itemname) {
				$model = new Tbl_AuthAssignment;
				$model->userid = $id;
				$model->itemname = $itemname;
				if (!$model->save()){
					throw new CException(Yii::t('global','ERROR_SAVING'));	
				}
			}
			
			// check for linked stores
			foreach (Tbl_LinkedStore::model()->findAll() as $store) {
				// check store is checked
				if (!empty($store->database)) {
					if ($connection = Html::connect_other_db($store->database)) {		
						// get id
						$sql = 'SELECT
						id
						FROM
						user
						WHERE
						email = :email
						LIMIT 1';
						$command_get_user_id = $connection->createCommand($sql);
						
						// add assignments
						$sql = 'INSERT INTO
						AuthAssignment
						SET 
						userid = :id_user,
						itemname = :itemname';
						$command_add_assignment = $connection->createCommand($sql);						
						
						if ($id_user = $command_get_user_id->queryScalar(array(':email'=>$user->email))) {					
							// delete all previous assignments
							$sql = 'DELETE FROM 
							AuthAssignment
							WHERE
							userid = :id_user';
							$command_delete_auth = $connection->createCommand($sql);
							$command_delete_auth->execute(array(':id_user'=>$id_user));
							
							// add assignments
							foreach ($ids as $itemname) {
								$command_add_assignment->execute(array(':id_user'=>$id_user,':itemname'=>$itemname));
							}							
						}
					}
				}
			}
		}
	}
	
	/**
	 * This is the action to delete 
	 */
	public function actionDelete()
	{		
		// selected user
		$ids = $_POST['ids'];
		$delete_linked = (int)$_POST['delete_linked'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;
			foreach ($ids as $id) {
				$id_user_exist = 0;
				
				if (!$user = Tbl_User::model()->findByPk($id)) {
					throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
				}						
			
				//Have to check if id_user_created or id_user_modified is not use by the current id
				$table_name = Yii::app()->db->schema->getTables();//Get all table from the current DB and put it in an array

				foreach($table_name as $key => $value){
					$sql = "SHOW COLUMNS FROM `" . $key . "` LIKE 'id_user_created'";
					$command_user_created = $connection->createCommand($sql);
					if(sizeof($command_user_created->queryAll(false))){
						$sql = "SELECT id_user_created FROM " . $key . " WHERE id_user_created = '" . $id . "'";
						$command_user_created_result = $connection->createCommand($sql);
						if(sizeof($command_user_created_result->queryAll(false))){
							$id_user_exist = 1;
							break;
						}
						
					}
					if(!$id_user_exist){
						$sql = "SHOW COLUMNS FROM `" . $key . "` LIKE 'id_user_modified'";
						$command_user_modified = $connection->createCommand($sql);
						if(sizeof($command_user_modified->queryAll(false))){
							$sql = "SELECT id_user_modified FROM " . $key . " WHERE id_user_modified = '" . $id . "'";
							$command_user_modified_result = $connection->createCommand($sql);
							if(sizeof($command_user_modified_result->queryAll(false))){
								$id_user_exist = 1;
								break;
							}
							
						}
					}
				}
				
				//If id_user_exist, deleted field is set to true (1) to keep infos of the users for report else delete the user
				if($id_user_exist==1){
					$criteria=new CDbCriteria; 
					$criteria->params=array(':id'=>$id); 	
					$criteria->condition='id=:id'; 
					Tbl_User::model()->updateAll(array('deleted'=>1,'active'=>0),$criteria);
				}else{
					Tbl_User::model()->deleteByPk($id);
				}
				
				// delete linked
				if ($delete_linked) {					
					// check for linked stores
					foreach (Tbl_LinkedStore::model()->findAll() as $store) {
						// check store is checked
						if (!empty($store->database)) {
							if ($connection = Html::connect_other_db($store->database)) {									
								if($id_user_exist==1){
									$sql = 'UPDATE
									user
									SET
									deleted = 1
									WHERE
									email = :email
									LIMIT 1';									
								} else {
									$sql = 'DELETE FROM
									user
									WHERE
									email = :email
									LIMIT 1';
								}
								
								$command_del_user = $connection->createCommand($sql);
								$command_del_user->execute(array(':email'=>$user->email));								
							}
						}
					}
				}				
			}
		}
	}	
	
	/**
	 * This is the action to toggle user active status
	 */
	public function actionToggle_active()
	{
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		
		if ($p = Tbl_User::model()->findByPk($id)) {
			$p->active = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}	
	
	
	/**
	 * This is the action to get an XML list of users
	 */
	public function actionXml_list($posStart=0, $count=100, array $filters=array(), array $sort_col=array())
	{		

		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		$posStart=(int)$posStart;
		$count=(int)$count;
		
		$where=array();
		$params=array();
		
		// filters
		// by default
		$where[] = 'deleted = 0 AND permission <> 999';
		
		
		// name
		if (isset($filters['lastname']) && !empty($filters['lastname'])) {
			$where[] = 'CONCAT(firstname," ",lastname) LIKE CONCAT("%",:lastname,"%")';
			$params[':lastname']=$filters['lastname'];
		}

		// status
		if (isset($filters['active'])) {
			switch ($filters['active']) {
				case 0:
				case 1:					
					$where[] = 'active = :active';				
					$params[':active']=$filters['active'];
					break;
			}
		}								
		
		$sql = "SELECT 
		COUNT(id) AS total 
		FROM 
		user 
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
		id,
		lastname,
		firstname,
		email,
		active, 
		address, 
		city, 
		phone_home,
		phone_cell,
		cd.name AS country_name,
		sd.name AS state_name,
		zip
		FROM 
		user
		LEFT JOIN (country_description AS cd) ON user.country = cd.country_code AND cd.language_code = '" . Yii::app()->language . "'
		LEFT JOIN (state_description AS sd) ON user.state = sd.state_code AND sd.language_code = '" . Yii::app()->language . "'
		".(sizeof($where) ? ' WHERE '.implode(' AND ',$where):'');		
		
		// sorting

		// lastname
		if (isset($sort_col[1]) && !empty($sort_col[1])) {	
			$direct = $sort_col[1] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY CONCAT(firstname,' ',lastname) ".$direct;
		// email
		} else if (isset($sort_col[2]) && !empty($sort_col[2])) {
			$direct = $sort_col[2] == 'des' ? 'DESC':'ASC';
			
			$sql.=" ORDER BY email ".$direct;			
		} else {
			if (isset($filters['lastname']) && !empty($filters['lastname'])) { 
				$sql.=" ORDER BY IF(CONCAT(firstname,' ',lastname) LIKE CONCAT(:lastname,'%'),0,1) ASC, CONCAT(firstname,' ',lastname) ASC";
			} else {
				$sql.=" ORDER BY id ASC";
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
			<cell type="ro"><![CDATA['.$row['firstname'].' '.$row['lastname'].']]></cell>
			<cell type="ro"><![CDATA['.
			($row['phone_home']?'<strong>'.Yii::t('controllers/UsersController','LABEL_HOME').'</strong> '.$row['phone_home']:'').
			($row['phone_cell']?"\r\n".'<br/><strong>'.Yii::t('controllers/UsersController','LABEL_CELL').'</strong> '.$row['phone_cell']:'').']]></cell>
			<cell type="ro"><![CDATA['.
			($row['address']?$row['address']."\r\n".'<br/>':'').
			($row['city']?$row['city']:'').
			($row['state_name']?' '.$row['state_name']:'').
			($row['country_name']?' '.$row['country_name']:'').
			($row['zip']?"\r\n".'<br />'.$row['zip']:'').']]></cell>
			<cell type="ro"><![CDATA['.$row['email'].']]></cell>
			<cell type="ch">'.$row['active'].'</cell>
			</row>';
		}
		echo '</rows>';
	}
	
	public function actionIs_user_linked_store()
	{
		$linked=0;
		
		// selected user
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			foreach ($ids as $id) {				
				if (!$user = Tbl_User::model()->findByPk($id)) throw new CException(Yii::t('global','ERROR_INVALID_ID'));		

				// check for linked stores
				foreach (Tbl_LinkedStore::model()->findAll() as $store) {
					// check store is checked
					if (!empty($store->database)) {
						if ($connection = Html::connect_other_db($store->database)) {	
							// check linked
							$sql = 'SELECT
							COUNT(id)
							FROM
							user
							WHERE
							email = :email
							LIMIT 1';
							$command = $connection->createCommand($sql);
							
							if ($command->queryScalar(array(':email'=>$user->email))) $linked=1;
						}
					}
				}
			}
		}
		
		echo $linked ? 'true':'false';
		exit;
	}
	
	/**
	 * This is the action to get an XML list of the product menu
	 */
	public function actionXml_list_user_section($id=0)
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
				<Title><![CDATA['.Yii::t('controllers/UsersController','LABEL_PERSONNAL_INFOS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/UsersController','LABEL_PERSONNAL_INFOS_DESCRIPTION').']]></Description>
			</item>
			<item id="edit_login_info">
				<Title><![CDATA['.Yii::t('controllers/UsersController','LABEL_USER_INFOS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/UsersController','LABEL_USER_INFOS_DESCRIPTION').']]></Description>
				'.$disabled.'
			</item>
			<item id="edit_permission">
				<Title><![CDATA['.Yii::t('controllers/UsersController','LABEL_PERMISSIONS').']]></Title>
				<Description><![CDATA['.Yii::t('controllers/UsersController','LABEL_PERMISSIONS_DESCRIPTION').']]></Description>
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