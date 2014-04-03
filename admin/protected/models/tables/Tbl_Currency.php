<?php

/**
 * This is the model class for table "currency".
 *
 * The followings are the available columns in table 'currency':
 * @property string $code
 * @property string $name
 * @property integer $active
 */
class Tbl_Currency extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Currency the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
    public function defaultScope()
    {
        return array(
			'condition' => 'active=1',			
			'order'=>'code ASC',
        );
    }		
	
    public function scopes()
    {
        return array(		
        );
    }		

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'currency';
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
			array('code, name, active', 'required'),
			array('active', 'numerical', 'integerOnly'=>true),
			array('code', 'length', 'max'=>3),
			array('name', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('code, name, active', 'safe', 'on'=>'search'),
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
			'code' => 'Code',
			'name' => 'Name',
			'active' => 'Active',
			*/
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

		$criteria->compare('code',$this->code,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('active',$this->active);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));*/
	}
}