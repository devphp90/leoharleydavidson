<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $id_linked_store;
	public $username;
	public $password;
	public $rememberMe;
	public $_lang;

	private $_identity;

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

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate()
	{
		$this->_identity=new UserIdentity($this->username,$this->password);
		if(!$this->_identity->authenticate()) {
			$this->addError('username','');
			$this->addError('password',Yii::t('global','AUTHENTICATION_FAILED'));
		}
	}
	
	public function validate()
	{
		// check if we have a username
		if (empty($this->username)) {
			$this->addError('username',Yii::t('global',Yii::t('global','ERROR_EMPTY')));
		}
		
		if (empty($this->password)) {
			$this->addError('password',Yii::t('global',Yii::t('global','ERROR_EMPTY')));
		}
		
		if (!empty($this->username) && !empty($this->password)) {
			$this->authenticate();
		}
				
		return $this->hasErrors() ? false:true;
	}

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
		
		if($this->_identity===null)
		{
			$this->_identity=new UserIdentity($this->username,$this->password);
			$this->_identity->authenticate();
			
		}
		if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
		{		
			$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
			//Yii::app()->user->setState('_lang',$_POST['_lang']);
			$this->_identity->setState('_lang',$this->_lang);
			Yii::app()->user->login($this->_identity,$duration);
			return true;
		}
		else
			return false;
	}
}
