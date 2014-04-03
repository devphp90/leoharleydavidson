<?php

/**
 * This is the model class for table "AuthItem".
 *
 * The followings are the available columns in table 'AuthItem':
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $bizrule
 * @property string $data
 */
class Tbl_AuthItem extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Category the static model class
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
		return 'AuthItem';
	}
	
    public function defaultScope()
    {
        return array(
           
        );
    }		
	
    public function scopes()
    {
        return array(
			'getDescription' => array(
				'with' => array(
					'tbl_AuthItem_description' => array(
						'on' => 'tbl_AuthItem_description.language_code="'.Yii::app()->language.'"',
						'together' => true,
					),
				),		
			),					
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
			/*
			array('id_category, start_date, end_date, featured, sort_order, active, id_user_created, id_user_modified, date_created, date_modified', 'required'),
			array('featured, sort_order, active', 'numerical', 'integerOnly'=>true),
			array('id_category, id_user_created, id_user_modified', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_category, start_date, end_date, featured, sort_order, active, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
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
			'tbl_AuthItem_description' => array(self::HAS_MANY, 'Tbl_AuthItemDescription', 'name_AuthItem'),
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
			'id_category' => 'Id Category',
			'start_date' => 'Start Date',
			'end_date' => 'End Date',
			'featured' => 'Featured',
			'sort_order' => 'Sort Order',
			'active' => 'Active',
			'id_user_created' => 'Id User Created',
			'id_user_modified' => 'Id User Modified',
			'date_created' => 'Date Created',
			'date_modified' => 'Date Modified',
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
		$criteria->compare('id_category',$this->id_category,true);
		$criteria->compare('start_date',$this->start_date,true);
		$criteria->compare('end_date',$this->end_date,true);
		$criteria->compare('featured',$this->featured);
		$criteria->compare('sort_order',$this->sort_order);
		$criteria->compare('active',$this->active);
		$criteria->compare('id_user_created',$this->id_user_created,true);
		$criteria->compare('id_user_modified',$this->id_user_modified,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_modified',$this->date_modified,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));*/
	}
}