<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	private $_id;	
	
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$criteria=new CDbCriteria; 
		$criteria->condition='username=:username'; 
		$criteria->params=array(':username'=>$this->username); 			
		
		$user = Tbl_User::model()->active()->find($criteria);		
		if ($user) $this->password = md5(md5(md5(md5(md5($this->password)))));
			
		if(empty($user)){
			$this->errorCode=self::ERROR_USERNAME_INVALID;
		}else if($user->password!==$this->password){
			$this->errorCode=self::ERROR_PASSWORD_INVALID;
		}else {
			$this->_id=$user->id;
			$this->username=$user->username;
			Yii::app()->user->setState('fullname',$user->firstname.' '.$user->lastname);
			Yii::app()->user->setState('gender',$user->gender);			
			$this->errorCode=self::ERROR_NONE;	
			$user->lastlogin = date('Y-m-d H:i:s');
			$user->save();
		}
		return !$this->errorCode;
	}
	
	/**
	 * @return integer the ID of the user record
	 */
	public function getId()
	{
		return $this->_id;
	}	
	
	/**
	 * @set the id
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}			
}