<?php

/**
 * This is the model class for table "orders_shipment_item".
 *
 * The followings are the available columns in table 'orders_shipment_item':
 * @property string $id
 * @property string $id_orders_shipment
 * @property string $id_orders_item_product
 * @property string $id_orders_item_option
 * @property string $qty   
 */
class Tbl_OrdersShipmentItem extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'orders_shipment_item';
	}
	
    public function scopes()
    {
        return array(
        );
    }		

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			/*array('permission, firstname, lastname, address, city, state, zip, country, phone_home, phone_cell, email, username, password, password_reset_key, default_language_code, setting, id_user_created, id_user_modified, lastlogin, active, date_created, date_modified', 'required'),
			array('gender, active', 'numerical', 'integerOnly'=>true),
			array('permission, id_user_created, id_user_modified', 'length', 'max'=>11),
			array('firstname, lastname, username', 'length', 'max'=>50),
			array('address, city, state, email', 'length', 'max'=>255),
			array('zip', 'length', 'max'=>10),
			array('country, default_language_code', 'length', 'max'=>2),
			array('phone_home, phone_cell', 'length', 'max'=>20),
			array('password, password_reset_key', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, permission, firstname, lastname, address, city, state, zip, country, phone_home, phone_cell, gender, email, username, password, password_reset_key, default_language_code, setting, id_user_created, id_user_modified, lastlogin, active, date_created, date_modified', 'safe', 'on'=>'search'),*/
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			/*'id' => 'ID',
			'permission' => 'Permission',
			'firstname' => 'Firstname',
			'lastname' => 'Lastname',
			'address' => 'Address',
			'city' => 'City',
			'state' => 'State',
			'zip' => 'Zip',
			'country' => 'Country',
			'phone_home' => 'Phone Home',
			'phone_cell' => 'Phone Cell',
			'gender' => 'Gender',
			'email' => 'Email',
			'username' => 'Username',
			'password' => 'Password',
			'password_reset_key' => 'Password Reset Key',
			'default_language_code' => 'Default Language Code',
			'setting' => 'Setting',
			'id_user_created' => 'Id User Created',
			'id_user_modified' => 'Id User Modified',
			'lastlogin' => 'Lastlogin',
			'active' => 'Active',
			'date_created' => 'Date Created',
			'date_modified' => 'Date Modified',*/
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		/*$criteria->compare('id',$this->id,true);
		$criteria->compare('permission',$this->permission,true);
		$criteria->compare('firstname',$this->firstname,true);
		$criteria->compare('lastname',$this->lastname,true);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('city',$this->city,true);
		$criteria->compare('state',$this->state,true);
		$criteria->compare('zip',$this->zip,true);
		$criteria->compare('country',$this->country,true);
		$criteria->compare('phone_home',$this->phone_home,true);
		$criteria->compare('phone_cell',$this->phone_cell,true);
		$criteria->compare('gender',$this->gender);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('password_reset_key',$this->password_reset_key,true);
		$criteria->compare('default_language_code',$this->default_language_code,true);
		$criteria->compare('setting',$this->setting,true);
		$criteria->compare('id_user_created',$this->id_user_created,true);
		$criteria->compare('id_user_modified',$this->id_user_modified,true);
		$criteria->compare('lastlogin',$this->lastlogin,true);
		$criteria->compare('active',$this->active);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_modified',$this->date_modified,true);*/

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}