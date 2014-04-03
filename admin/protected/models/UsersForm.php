<?php
class UsersForm extends CFormModel
{
	// database fields
	public $id=0; //user id
	public $gender=1;
	public $lastname; 
	public $firstname; 
	public $address; 
	public $city; 
	public $zip; 
	public $state=''; 
	public $country=''; 
	public $phone_home; 
	public $phone_cell; 
	public $email;
	public $default_language_code;
	public $deleted=0;
	public $linked_store=array();

	/**
	 * Declares the validation rules.
	 */
	 
	
	public function rules()
	{
		return array(	
		);
	}	  

	public function validate()
	{		
		if (empty($this->lastname)) {
			$this->addError('lastname',Yii::t('global','ERROR_EMPTY'));
		} 
		if (empty($this->firstname)) {
			$this->addError('firstname',Yii::t('global','ERROR_EMPTY'));
		}
		$this->zip = strtoupper(str_replace(' ','',$this->zip));
		
		if (empty($this->email)) {
			$this->addError('email',Yii::t('global','ERROR_EMPTY'));
		} else if (!$this->id && Tbl_User::model()->count('email=:email',array(':email'=>$this->email))) $this->addError('email',Yii::t('global','ERROR_EXIST'));
				
		// if adding a new user
		if (!$this->id && !$this->hasErrors('email')) {
			$exists=0;
			// check if multi store and if email exists
			foreach (Tbl_LinkedStore::model()->findAll() as $store) {
				if (!empty($store->database)) {
					if ($connection = Html::connect_other_db($store->database)) {	
						// check if current user exists
						$sql = 'SELECT
						COUNT(id)
						FROM
						user 
						WHERE
						email=:email
						LIMIT 1';
						$command_check_user = $connection->createCommand($sql);
						
						if ($command_check_user->queryScalar(array(':email'=>$this->email))) $exists=1;
					} else {
						throw new CException('Unable to connect to linked database: '.$store->database);	
					}
				}
			}
			
			if ($exists) $this->addError('email',Yii::t('views/users/edit_info_options','ERROR_EMAIL_EXIST_OTHER_STORE'));
		}

		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save Information Tab
	 */	
	public function save()
	{
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();
		
		// edit or new
		if ($this->id) {
			$model = Tbl_User::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_User;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;					
		}		
		$model->gender = $this->gender;
		$model->lastname = $this->lastname;
		$model->firstname = $this->firstname;
		$model->address = $this->address;
		$model->phone_home = $this->phone_home;
		$model->phone_cell = $this->phone_cell;
		$model->city = $this->city;
		$model->zip = $this->zip;
		$model->state = $this->state;
		$model->country = $this->country;
		$model->email = $this->email;
		$model->deleted = $this->deleted;				
		$model->id_user_modified = $current_id_user;
		$model->default_language_code = $this->default_language_code;
		$model->auth_key = md5($model->email.$model->username.$model->password);
		
		if ($model->save()) {	
			// check for linked stores
			if (sizeof($this->linked_store)) {
				foreach (Tbl_LinkedStore::model()->findAll() as $store) {
					// check store is checked
					if (isset($this->linked_store[$store->id]) && !empty($store->database)) {
						if ($connection = Html::connect_other_db($store->database)) {						
							// check if current user exists
							$sql = 'SELECT
							id
							FROM
							user 
							WHERE
							email=:email
							LIMIT 1';
							$command_check_user = $connection->createCommand($sql);
							
							$sql = 'INSERT INTO 
							user
							SET
							gender = :gender,
							lastname = :lastname,
							firstname = :firstname,
							address = :address,
							phone_home = :phone_home,
							phone_cell = :phone_cell,
							city = :city,
							zip = :zip,
							state = :state,
							country = :country,
							email = :email,
							deleted = :deleted,
							default_language_code = :default_language_code,
							auth_key = :auth_key,
							username = :username,
							password = :password,
							active = :active';
							$command_add_user = $connection->createCommand($sql);							
							
							$sql = 'UPDATE 
							user
							SET
							gender = :gender,
							lastname = :lastname,
							firstname = :firstname,
							address = :address,
							phone_home = :phone_home,
							phone_cell = :phone_cell,
							city = :city,
							zip = :zip,
							state = :state,
							country = :country,
							deleted = :deleted,
							default_language_code = :default_language_code,
							auth_key = :auth_key,
							username = :username,
							password = :password,
							active = :active
							WHERE
							email = :email							
							LIMIT 1';
							$command_upd_user = $connection->createCommand($sql);	
							
							$params = array(
								':gender' => $model->gender,
								':lastname' => $model->lastname,
								':firstname' => $model->firstname,
								':address' => $model->address,
								':phone_home' => $model->phone_home,
								':phone_cell' => $model->phone_cell,
								':city' => $model->city,
								':zip' => $model->zip,
								':state' => $model->state,
								':country' => $model->country,
								':email' => $model->email,
								':deleted' => $model->deleted,
								':default_language_code' => $model->default_language_code,
								':auth_key' => $model->auth_key,
								':username' => $model->username ? $model->username:'',
								':password' => $model->password ? $model->password:'',
								':active' => $model->active ? $model->active:0,
							);													
							
							// exists
							if ($id_user = $command_check_user->queryScalar(array(':email'=>$this->email))) {
								$command_upd_user->execute($params);
							} else {
								$command_add_user->execute($params);
								
								$id_user = $connection->getLastInsertID();
							}
							
							// save permissions
							$sql = 'DELETE FROM 
							AuthAssignment
							WHERE
							userid = :userid';
							$command_del_auth = $connection->createCommand($sql);
							$command_del_auth->execute(array(':userid'=>$id_user));
							
							$sql = 'INSERT INTO 
							AuthAssignment
							SET
							userid = :userid,
							itemname = :itemname';
							$command_add_auth = $connection->createCommand($sql);
							
							foreach (Tbl_AuthAssignment::model()->findAll('userid=:userid',array(':userid'=>$model->id)) as $row) {
								$command_add_auth->execute(array(':userid'=>$id_user,':itemname'=>$row->itemname));							
							}
						} else {
							throw new CException('Unable to connect to linked database: '.$store->database);	
						}
					}
				}
			}								
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}

		$this->id = $model->id;
		
		return true;
	}
}
