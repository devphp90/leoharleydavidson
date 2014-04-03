<?php

/**
 * This is the model class for table "product_option".
 *
 * The followings are the available columns in table 'product_option':
 * @property string $id
 * @property string $id_product_option_group
 * @property string $sku
 * @property string $cost_price
 * @property integer $price_type
 * @property string $price
 * @property string $percent
 * @property integer $allow_qty_request
 * @property string $max_qty_in_cart
 * @property string $qty
 * @property string $out_of_stock
 * @property integer $notify
 * @property string $notify_qty
 * @property integer $allow_backorders
 * @property string $weight
 * @property string $sort_order
 * @property string $id_user_created
 * @property string $id_user_modified
 * @property string $date_created
 * @property string $date_modified
 */
class Tbl_Options extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return ProductOption the static model class
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
		return 'options';
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
			array('id_product_option_group', 'required'),
			array('maxlength, price_type, track_inventory, show_qty,max_qty, notify, allow_backorders', 'numerical', 'integerOnly'=>true),
			array('id_product_option_group, percent, max_qty, qty, out_of_stock, notify_qty, weight, sort_order, id_user_created, id_user_modified, maxlength', 'length', 'max'=>11),
			array('sku', 'length', 'max'=>50),
			array('cost_price, price', 'length', 'max'=>13),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_product_option_group, maxlength, sku, cost_price, price_type, price, percent, show_qty, show_qty, qty, out_of_stock, notify, notify_qty, allow_backorders, weight, sort_order, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
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
			'tbl_options_description' => array(self::HAS_MANY, 'Tbl_OptionsDescription', 'id_options'),	
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
			'id_product_option_group' => 'Id Product Option Group',
			'maxlength' => 'Maxlength',
			'sku' => 'Sku',
			'cost_price' => 'Cost Price',
			'price_type' => 'Price Type',
			'price' => 'Price',
			'percent' => 'Percent',
			'show_qty' => 'Show Qty',
			'max_qty' => 'Max Qty',
			'qty' => 'Qty',
			'out_of_stock' => 'Out Of Stock',
			'notify' => 'Notify',
			'notify_qty' => 'Notify Qty',
			'allow_backorders' => 'Allow Backorders',
			'weight' => 'Weight',
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
		$criteria->compare('id_product_option_group',$this->id_product_option_group,true);
		$criteria->compare('sku',$this->sku,true);
		$criteria->compare('cost_price',$this->cost_price,true);
		$criteria->compare('price_type',$this->price_type);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('percent',$this->percent,true);
		$criteria->compare('show_qty',$this->show_qty);
		$criteria->compare('max_qty',$this->max_qty,true);
		$criteria->compare('qty',$this->qty,true);
		$criteria->compare('out_of_stock',$this->out_of_stock,true);
		$criteria->compare('notify',$this->notify);
		$criteria->compare('notify_qty',$this->notify_qty,true);
		$criteria->compare('allow_backorders',$this->allow_backorders);
		$criteria->compare('weight',$this->weight,true);
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