<?php

/**
 * This is the model class for table "tpl_product_option_group_description".
 *
 * The followings are the available columns in table 'tpl_product_option_group_description':
 * @property string $id_tpl_product_option_group
 * @property string $language_code
 * @property string $name
 * @property string $description
 */
class Tbl_OptionsGroupDescription extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return TplProductOptionGroupDescription the static model class
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
		return 'options_group_description';
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
			array('id_tpl_product_option_group, language_code, name, description', 'required'),
			array('id_tpl_product_option_group', 'length', 'max'=>11),
			array('language_code', 'length', 'max'=>2),
			array('name', 'length', 'max'=>50),
			array('description', 'length', 'max'=>150),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id_tpl_product_option_group, language_code, name, description', 'safe', 'on'=>'search'),
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
			'id_tpl_product_option_group' => 'Id Tpl Product Option Group',
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

		$criteria->compare('id_tpl_product_option_group',$this->id_tpl_product_option_group,true);
		$criteria->compare('language_code',$this->language_code,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('description',$this->description,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}