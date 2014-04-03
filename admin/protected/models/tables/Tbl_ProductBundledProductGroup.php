<?php

/**
 * This is the model class for table "product_bundled_product_group".
 *
 * The followings are the available columns in table 'product_bundled_product_group':
 * @property string $id
 * @property string $id_product
 * @property integer $input_type
 * @property string $required
 * @property string $sort_order 
 * @property string $id_user_created
 * @property string $id_user_modified
 * @property string $date_created
 * @property string $date_modified
 */
class Tbl_ProductBundledProductGroup extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductOptionGroup the static model class
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
		return 'product_bundled_product_group';
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
			array('id_product', 'required'),
			array('input_type', 'numerical', 'integerOnly'=>true),
			array('id_product, sort_order, id_user_created, id_user_modified', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_product, input_type, sort_order, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
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
			'tbl_product_bundled_product_group_description' => array(self::HAS_MANY, 'Tbl_ProductBundledProductGroupDescription', 'id_product_bundled_product_group'),
			'tbl_product_bundled_product_group_product' => array(self::HAS_MANY, 'Tbl_ProductBundledProductGroupProduct', 'id_product_bundled_product_group'),
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
			'id_product' => 'Id Product',
			'input_type' => 'Input Type',
			'sort_order' => 'Sort Order',
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
		/*
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('id_product',$this->id_product,true);
		$criteria->compare('input_type',$this->input_type);
		$criteria->compare('sort_order',$this->sort_order,true);
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