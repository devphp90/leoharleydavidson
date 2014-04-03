<?php

/**
 * This is the model class for table "rebate_category".
 *
 * The followings are the available columns in table 'rebate_category':
 * @property string $id_rebate
 * @property string $id_category
 */
class Tbl_RebateCouponCategory extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return RebateCategory the static model class
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
		return 'rebate_coupon_category';
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
			array('id_rebate, id_category', 'required'),
			array('id_rebate, id_category', 'length', 'max'=>11),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id_rebate, id_category', 'safe', 'on'=>'search'),
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
			'id_rebate' => 'Id Rebate',
			'id_category' => 'Id Category',
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

		$criteria->compare('id_rebate',$this->id_rebate,true);
		$criteria->compare('id_category',$this->id_category,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}