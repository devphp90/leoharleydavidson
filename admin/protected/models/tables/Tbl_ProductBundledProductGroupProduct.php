<?php

/**
 * This is the model class for table "product_bundled_product_group_product".
 *
 * The followings are the available columns in table 'product_bundled_product_group_product':
 * @property string $id 
 * @property string $id_product_bundled_product_group
 * @property string $id_product
 * @property string $id_product_variant 
 * @property string $price_type
 * @property string $price
 * @property string $qty
 * @property string $user_defined_qty
 * @property string $sort_order
 * @property string $id_user_created
 * @property string $id_user_modified
 * @property string $date_created
 * @property string $date_modified       
 */
class Tbl_ProductBundledProductGroupProduct extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductOptionGroupDescription the static model class
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
		return 'product_bundled_product_group_product';
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
			array('id_product_option_group, language_code, name', 'required'),
			array('id_product_option_group', 'length', 'max'=>11),
			array('language_code', 'length', 'max'=>2),
			array('name', 'length', 'max'=>50),
			array('description', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id_product_option_group, language_code, name, description', 'safe', 'on'=>'search'),
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
			'id_product_option_group' => 'Id Product Option Group',
			'language_code' => 'Language Code',
			'name' => 'Name',
			'description' => 'Description',
			*/
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		/*
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id_product_option_group',$this->id_product_option_group,true);
		$criteria->compare('language_code',$this->language_code,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));*/
	}
}