<?php

/**
 * This is the model class for table "product_variant_option".
 *
 * The followings are the available columns in table 'product_variant_option':
 * @property string $id
 * @property string $id_product_variant
 * @property string $id_product_variant_group
 * @property string $id_product_variant_group_option
 */
class Tbl_ProductVariantOption extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductVariantOption the static model class
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
		return 'product_variant_option';
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
			array('id_product_variant, id_product_variant_group, id_product_variant_group_option', 'required'),
			array('id_product_variant, id_product_variant_group, id_product_variant_group_option', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_product_variant, id_product_variant_group, id_product_variant_group_option', 'safe', 'on'=>'search'),
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
			'id_product_variant' => 'Id Product Variant',
			'id_product_variant_group' => 'Id Product Variant Group',
			'id_product_variant_group_option' => 'Id Product Variant Group Option',
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
		$criteria->compare('id_product_variant',$this->id_product_variant,true);
		$criteria->compare('id_product_variant_group',$this->id_product_variant_group,true);
		$criteria->compare('id_product_variant_group_option',$this->id_product_variant_group_option,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}