<?php

/**
 * This is the model class for table "product_variant_group_option".
 *
 * The followings are the available columns in table 'product_variant_group_option':
 * @property string $id
 * @property string $id_product_variant_group
 * @property string $sku
 * @property integer $swatch_type
 * @property string $color
 * @property string $color2
 * @property string $color3
 * @property string $filename
 * @property string $sort_order
 * @property string $id_user_created
 * @property string $id_user_modified
 * @property string $date_created
 * @property string $date_modified
 */
class Tbl_ProductVariantGroupOption extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductVariantGroupOption the static model class
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
		return 'product_variant_group_option';
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
			array('id_product_variant_group, sku', 'required'),
			array('swatch_type', 'numerical', 'integerOnly'=>true),
			array('id_product_variant_group, sort_order, id_user_created, id_user_modified', 'length', 'max'=>11),
			array('sku', 'length', 'max'=>50),
			array('color, color2, color3', 'length', 'max'=>7),
			array('filename', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_product_variant_group, sku, swatch_type, color, color2, color3, filename, sort_order, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
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
			'tbl_product_variant_group_option_description' => array(self::HAS_MANY, 'Tbl_ProductVariantGroupOptionDescription', 'id_product_variant_group_option')
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
			'id_product_variant_group' => 'Id Product Variant Group',
			'sku' => 'Sku',
			'swatch_type' => 'Swatch Type',
			'color' => 'Color',
			'color2' => 'Color2',
			'color3' => 'Color3',
			'filename' => 'Filename',
			'sort_order' => 'Sort Order',
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
		$criteria->compare('id_product_variant_group',$this->id_product_variant_group,true);
		$criteria->compare('sku',$this->sku,true);
		$criteria->compare('swatch_type',$this->swatch_type);
		$criteria->compare('color',$this->color,true);
		$criteria->compare('color2',$this->color2,true);
		$criteria->compare('color3',$this->color3,true);
		$criteria->compare('filename',$this->filename,true);
		$criteria->compare('sort_order',$this->sort_order,true);
		$criteria->compare('id_user_created',$this->id_user_created,true);
		$criteria->compare('id_user_modified',$this->id_user_modified,true);
		$criteria->compare('date_created',$this->date_created,true);
		$criteria->compare('date_modified',$this->date_modified,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
		*/
	}
}