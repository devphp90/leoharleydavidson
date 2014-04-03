<?php
class UsersLoginForm extends CFormModel
{
	// database fields
	public $id=0; //user id
	public $username;
	public $password;
	public $confirm_password;
	public $active=0;


	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	 
	
	public function rules()
	{
		return array(	
		);
	}	  

	public function validate()
	{		
		
		$model = Tbl_User::model()->findByPk($this->id);
		
		$criteria=new CDbCriteria; 
		$criteria->condition='username=:username AND id!=:id'; 
		$criteria->params=array(':username'=>$this->username, ':id'=>$this->id);
		
		if (empty($this->username)) {
			$this->addError('username',Yii::t('global','ERROR_EMPTY'));
		}elseif (Tbl_User::model()->count($criteria)) {
			$this->addError('username',Yii::t('global','ERROR_EXIST'));	
		} 
		if (empty($this->password) and empty($model->username)) {
			$this->addError('password',Yii::t('global','ERROR_EMPTY'));
		}
		if ($this->password != $this->confirm_password) {
			$this->addError('password',Yii::t('global','ERROR_CONFIRM_PASSWORD'));
		}
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save Information Tab
	 */	
	public function save()
	{
		$current_id_user = (int)Yii::app()->user->getId();
		
		$model = Tbl_User::model()->findByPk($this->id);	
		$model->id_user_modified = $current_id_user;
		if(!empty($this->password)){
			$model->password = md5(md5(md5(md5(md5($this->password)))));
		}
		$model->username = $this->username;
		$model->active = $this->active;
		$model->auth_key = md5($model->email.$model->username.$model->password);
		
		if ($model->save()) {		
			// check for linked stores
			foreach (Tbl_LinkedStore::model()->findAll() as $store) {
				// check store is checked
				if (!empty($store->database)) {
					if ($connection = Html::connect_other_db($store->database)) {						
						$sql = 'UPDATE 
						user
						SET
						username = :username,
						password = :password,
						active = :active,						
						auth_key = :auth_key
						WHERE
						email = :email
						LIMIT 1';
						$command_upd_user = $connection->createCommand($sql);	
						
						$params = array(
							':username' => $model->username,
							':password' => $model->password,
							':active' => $model->active,
							':auth_key' => $model->auth_key,
							':email' => $model->email,
						);															
						
						$command_upd_user->execute($params);
					} else {
						throw new CException('Unable to connect to linked database: '.$store->database);	
					}
				}
			}								
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		return true;
	}
}
