<?php

/**
 * This is the model class for table "product_description".
 *
 * The followings are the available columns in table 'product_description':
 * @property string $id_product
 * @property string $language_code
 * @property string $name
 * @property string $short_desc
 * @property string $description
 * @property string $meta_description
 * @property string $meta_keywords
 * @property string $alias
 */
class Tbl_NewsDescription extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductDescription the static model class
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
		return 'news_description';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			/*array('id_product, language_code, name, meta_description, meta_keywords, alias', 'required'),
			array('id_product', 'length', 'max'=>11),
			array('language_code', 'length', 'max'=>2),
			array('name, alias', 'length', 'max'=>150),
			array('short_desc, meta_keywords', 'length', 'max'=>255),
			array('meta_description', 'length', 'max'=>200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id_product, language_code, name, short_desc, description, meta_description, meta_keywords, alias', 'safe', 'on'=>'search'),*/
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
			/*'id_product' => 'Id Product',
			'language_code' => 'Language Code',
			'name' => 'Name',
			'short_desc' => 'Short Desc',
			'description' => 'Description',
			'meta_description' => 'Meta Description',
			'meta_keywords' => 'Meta Keywords',
			'alias' => 'Alias',*/
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
		$criteria->compare('language_code',$this->language_code,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('short_desc',$this->short_desc,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('meta_description',$this->meta_description,true);
		$criteria->compare('meta_keywords',$this->meta_keywords,true);
		$criteria->compare('alias',$this->alias,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));*/
	}
}