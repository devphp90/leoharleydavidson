<?php

/**
 * This is the model class for table "customer_type".
 *
 * The followings are the available columns in table 'customer_type':
 * @property string $id
 * @property string $name
 * @property string $percent_discount
 * @property integer $taxable
 * @property integer $id_user_created
 * @property integer $id_user_modified
 * @property string $date_created
 * @property string $date_modified
 */
class Tbl_CustomerCoursesScorm extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return CustomerType the static model class
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
		return 'customer_courses_scorm';
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
		/*
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('percent_discount',$this->percent_discount,true);
		$criteria->compare('taxable',$this->taxable);
		$criteria->compare('id_user_created',$this->id_user_created);
		$criteria->compare('id_user_modified',$this->id_user_modified);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_modified',$this->date_modified,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));*/
	}
}