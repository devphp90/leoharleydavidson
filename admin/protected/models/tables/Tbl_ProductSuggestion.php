<?php

/**
 * This is the model class for table "product_suggestion".
 *
 * The followings are the available columns in table 'product_suggestion':
 * @property integer $id_product
 * @property integer $id_product_suggestion
 * @property string $sort_order
 */
class Tbl_ProductSuggestion extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductSuggestion the static model class
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
		return 'product_suggestion';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_product, id_product_suggestion, sort_order', 'required'),
			array('id_product, id_product_suggestion', 'numerical', 'integerOnly'=>true),
			array('sort_order', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id_product, id_product_suggestion, sort_order', 'safe', 'on'=>'search'),
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
			'id_product' => 'Id Product',
			'id_product_suggestion' => 'Id Product Suggestion',
			'sort_order' => 'Sort Order',
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

		$criteria->compare('id_product',$this->id_product);
		$criteria->compare('id_product_suggestion',$this->id_product_suggestion);
		$criteria->compare('sort_order',$this->sort_order,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}