<?php

/**
 * This is the model class for table "tpl_product_bundled_product_group".
 *
 * The followings are the available columns in table 'tpl_product_bundled_product_group':
 * @property string $id
 * @property string $id_tpl_product_bundled_product_category
 * @property string $input_type
 * @property string $required
 * @property string $sort_order
 * @property string $id_user_created
 * @property string $id_user_modified
 * @property string $date_created
 * @property string $date_modified
 */
class Tbl_TplProductBundledProductGroup extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return TplProductOption the static model class
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
		return 'tpl_product_bundled_product_group';
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
			array('id_product_option_group, sku, cost_price, price_type, price, percent, allow_qty_request, max_qty_request, qty, out_of_stock, notify, notify_qty, allow_backorders, weight, sort_order, id_user_created, id_user_modified, date_created, date_modified', 'required'),
			array('price_type, allow_qty_request, notify, allow_backorders', 'numerical', 'integerOnly'=>true),
			array('id_product_option_group, percent, max_qty_request, qty, out_of_stock, notify_qty, weight, sort_order, id_user_created, id_user_modified', 'length', 'max'=>11),
			array('sku', 'length', 'max'=>50),
			array('cost_price, price', 'length', 'max'=>13),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, id_product_option_group, sku, cost_price, price_type, price, percent, allow_qty_request, max_qty_request, qty, out_of_stock, notify, notify_qty, allow_backorders, weight, sort_order, id_user_created, id_user_modified, date_created, date_modified', 'safe', 'on'=>'search'),
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
			'tbl_tpl_product_bundled_product_group_description' => array(self::HAS_MANY, 'Tbl_TplProductBundledProductGroupDescription', 'id_tpl_product_bundled_product_group'),
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
			'sku' => 'Sku',
			'cost_price' => 'Cost Price',
			'price_type' => 'Price Type',
			'price' => 'Price',
			'percent' => 'Percent',
			'allow_qty_request' => 'Allow Qty Request',
			'max_qty_request' => 'Max Qty Request',
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
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.
		/*
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('id_product_option_group',$this->id_product_option_group,true);
		$criteria->compare('sku',$this->sku,true);
		$criteria->compare('cost_price',$this->cost_price,true);
		$criteria->compare('price_type',$this->price_type);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('percent',$this->percent,true);
		$criteria->compare('allow_qty_request',$this->allow_qty_request);
		$criteria->compare('max_qty_request',$this->max_qty_request,true);
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