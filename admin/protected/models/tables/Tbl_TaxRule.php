<?php

/**
 * This is the model class for table "tax_rule".
 *
 * The followings are the available columns in table 'tax_rule':
 * @property string $id
 * @property string $id_customer_type
 * @property string $id_tax_group
 * @property string $country_code
 * @property string $state_code
 * @property string $zip_from
 * @property string $zip_to
 * @property integer $active
 * @property string $id_user_created
 * @property string $id_user_modified
 * @property string $date_created
 * @property string $date_modified
 */
class Tbl_TaxRule extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return TaxRule the static model class
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
		return 'tax_rule';
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
			array('id_customer_type, id_tax_group, country_code, state_code, zip_from, zip_to, active, id_user_created, id_user_modified, date_created, date_modified', 'required'),
			array('active', 'numerical', 'integerOnly'=>true),
			array('id_customer_type, id_tax_group, id_user_created, id_user_modified', 'length', 'max'=>11),
			array('country_code', 'length', 'max'=>2),
			array('state_code', 'length', 'max'=>3),
			array('zip_from, zip_to', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_customer_type, id_tax_group, country_code, state_code, zip_from, zip_to, active, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
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
			'id_customer_type' => 'Id Customer Type',
			'id_tax_group' => 'Id Tax Group',
			'country_code' => 'Country Code',
			'state_code' => 'State Code',
			'zip_from' => 'Zip From',
			'zip_to' => 'Zip To',
			'active' => 'Active',
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
		$criteria->compare('id_customer_type',$this->id_customer_type,true);
		$criteria->compare('id_tax_group',$this->id_tax_group,true);
		$criteria->compare('country_code',$this->country_code,true);
		$criteria->compare('state_code',$this->state_code,true);
		$criteria->compare('zip_from',$this->zip_from,true);
		$criteria->compare('zip_to',$this->zip_to,true);
		$criteria->compare('active',$this->active);
		$criteria->compare('id_user_created',$this->id_user_created,true);
		$criteria->compare('id_user_modified',$this->id_user_modified,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_modified',$this->date_modified,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}