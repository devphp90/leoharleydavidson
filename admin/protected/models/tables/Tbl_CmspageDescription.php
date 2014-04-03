<?php

/**
 * This is the model class for table "cmspage_description".
 *
 * The followings are the available columns in table 'cmspage_description':
 * @property string $id_cmspage
 * @property string $language_code
 * @property string $name
 * @property string $description
 * @property string $alias
 */
class Tbl_CmspageDescription extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return CmspageDescription the static model class
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
		return 'cmspage_description';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			/*array('id_tag, language_code, name, description, alias', 'required'),
			array('id_tag', 'length', 'max'=>11),
			array('language_code', 'length', 'max'=>2),
			array('name', 'length', 'max'=>50),
			array('description, alias', 'length', 'max'=>150),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id_tag, language_code, name, description, alias', 'safe', 'on'=>'search'),*/
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
		return array(/*
			'id_tag' => 'Id Tag',
			'language_code' => 'Language Code',
			'name' => 'Name',
			'description' => 'Description',
			'alias' => 'Alias',*/
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

		$criteria->compare('id_tag',$this->id_tag,true);
		$criteria->compare('language_code',$this->language_code,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('alias',$this->alias,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}