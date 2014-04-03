<?php

/**
 * This is the model class for table "product_related".
 *
 * The followings are the available columns in table 'product_related':
 * @property string $id_product
 * @property string $id_product_related
 * @property string $sort_order
 */
class Tbl_ProductRatingCount extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductRelated the static model class
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
		return 'product_rating_count';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(

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

		/*$criteria=new CDbCriteria;

		$criteria->compare('id_product',$this->id_product,true);
		$criteria->compare('id_product_related',$this->id_product_related,true);
		$criteria->compare('sort_order',$this->sort_order,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));*/
	}
}