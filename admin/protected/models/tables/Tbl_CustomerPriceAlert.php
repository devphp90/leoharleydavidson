<?php

/**
 * This is the model class for table "customer_address".
 *
 * The followings are the available columns in table 'customer_address':
 * @property string $id
 * @property string $id_customer_type
 * @property string $name
 * @property string $prefix
 * @property string $firstname
 * @property string $lastname
 * @property string $middlename
 * @property string $suffix
 * @property string $company
 * @property string $address
 * @property string $city
 * @property string $country_code
 * @property string $state_code
 * @property string $zip
 * @property string $telephone
 * @property string $fax
 * @property integer $default_billing
 * @property integer $default_shipping
 * @property integer $id_user_created
 * @property integer $id_user_modified
 * @property string $date_created
 * @property string $date_modified
 */
class Tbl_CustomerPriceAlert extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return CustomerType the static model class
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
		return 'customer_price_alert';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			/*
			array('name, taxable, id_user_created, id_user_modified, date_created','required'),
			array('taxable, id_user_created, id_user_modified', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>50),
			array('percent_discount', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name, percent_discount, taxable, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
			*/
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
			/*
			'id' => 'ID',
			'name' => 'Name',
			'percent_discount' => 'Percent Discount',
			'taxable' => 'Taxable',
			'id_user_created' => 'Id User Created',
			'id_user_modified' => 'Id User Modified',
			'date_created' => 'Date Created',
			'date_modified' => 'Date Modified',
			*/
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
		/*
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('percent_discount',$this->percent_discount,true);
		$criteria->compare('taxable',$this->taxable);
		$criteria->compare('id_user_created',$this->id_user_created);
		$criteria->compare('id_user_modified',$this->id_user_modified);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_modified',$this->date_modified,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}