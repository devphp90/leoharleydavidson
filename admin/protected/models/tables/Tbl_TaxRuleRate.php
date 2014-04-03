<?php

/**
 * This is the model class for table "tax_rule_rate".
 *
 * The followings are the available columns in table 'tax_rule_rate':
 * @property string $id_tax_rule
 * @property string $id_tax
 * @property string $rate
 * @property integer $stacked
 * @property string $sort_order
 */
class Tbl_TaxRuleRate extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return TaxRuleRate the static model class
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
		return 'tax_rule_rate';
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
			array('id_tax_rule, id_tax, rate, stacked, sort_order', 'required'),
			array('stacked', 'numerical', 'integerOnly'=>true),
			array('id_tax_rule, id_tax, sort_order', 'length', 'max'=>11),
			array('rate', 'length', 'max'=>6),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id_tax_rule, id_tax, rate, stacked, sort_order', 'safe', 'on'=>'search'),
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
			'id_tax_rule' => 'Id Tax Rule',
			'id_tax' => 'Id Tax',
			'rate' => 'Rate',
			'stacked' => 'Stacked',
			'sort_order' => 'Sort Order',
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

		$criteria->compare('id_tax_rule',$this->id_tax_rule,true);
		$criteria->compare('id_tax',$this->id_tax,true);
		$criteria->compare('rate',$this->rate,true);
		$criteria->compare('stacked',$this->stacked);
		$criteria->compare('sort_order',$this->sort_order,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}