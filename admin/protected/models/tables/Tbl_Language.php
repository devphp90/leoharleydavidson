<?php

/**
 * This is the model class for table "language".
 *
 * The followings are the available columns in table 'language':
 * @property string $code
 * @property string $name
 * @property integer $active
 * @property integer $default_language
 */
class Tbl_Language extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Language the static model class
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
		return 'language';
	}
	
    public function scopes()
    {
        return array(
            'active'=>array(
                'condition'=>'active=1',
				'order'=>'IF(code="'.Yii::app()->language.'",0,1) ASC',
            )
        );
    }		

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('code, name, active, default_language', 'required'),
			array('active, default_language', 'numerical', 'integerOnly'=>true),
			array('code', 'length', 'max'=>2),
			array('name', 'length', 'max'=>30),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('code, name, active, default_language', 'safe', 'on'=>'search'),
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
			'code' => 'Code',
			'name' => 'Name',
			'active' => 'Active',
			'default_language' => 'Default Language',
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

		$criteria->compare('code',$this->code,true);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('active',$this->active);
		$criteria->compare('default_language',$this->default_language);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
}