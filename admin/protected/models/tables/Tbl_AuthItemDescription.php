<?php

/**
 * This is the model class for table "AuthItem_description".
 *
 * The followings are the available columns in table 'AuthItem_description':
 * @property string $name_AuthItem
 * @property string $language_code
 * @property string $name
 * @property string $description
 */
class Tbl_AuthItemDescription extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return CategoryDescription the static model class
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
		return 'AuthItem_description';
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
			array('id_category, language_code, name, description, meta_description, meta_keywords, alias', 'required'),
			array('id_category', 'length', 'max'=>11),
			array('language_code', 'length', 'max'=>2),
			array('name', 'length', 'max'=>50),
			array('description, meta_keywords', 'length', 'max'=>255),
			array('meta_description', 'length', 'max'=>100),
			array('alias', 'length', 'max'=>150),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id_category, language_code, name, description, meta_description, meta_keywords, alias', 'safe', 'on'=>'search'),
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
			'id_category' => 'Id Category',
			'language_code' => 'Language Code',
			'name' => 'Name',
			'description' => 'Description',
			'meta_description' => 'Meta Description',
			'meta_keywords' => 'Meta Keywords',
			'alias' => 'Alias',
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

		$criteria->compare('id_category',$this->id_category,true);
		$criteria->compare('language_code',$this->language_code,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('meta_description',$this->meta_description,true);
		$criteria->compare('meta_keywords',$this->meta_keywords,true);
		$criteria->compare('alias',$this->alias,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));*/
	}
}