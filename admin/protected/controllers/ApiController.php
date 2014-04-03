<?php

class ApiController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}

	public function actionIs_product_sold($sku)
	{
		if ($p = Tbl_Product::model()->find('sku=:sku',array(':sku'=>$sku))) {
			// get list of product groups
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection					
			
			$sql='SELECT
			IF(orders.id IS NOT NULL,orders.date_order,o.date_order) AS date_order,			
			IF(orders.id IS NOT NULL,orders.id,o.id) AS id_order			
			FROM 
			product 
			
			LEFT JOIN
			(orders_item_product CROSS JOIN orders_item CROSS JOIN orders) 
			ON
			(product.id = orders_item_product.id_product AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders_item.id AND orders_item.id_orders = orders.id)
			
			LEFT JOIN
			(orders_item_product AS oip CROSS JOIN orders_item_product AS oip_parent CROSS JOIN orders_item AS oi CROSS JOIN orders AS o)
			ON
			(product.id = oip.id_product AND oip.id_orders_item_product = oip_parent.id AND oip_parent.id_orders_item = oi.id AND oi.id_orders = o.id)
			
			WHERE
			product.id = :id 
			AND 
			(
				(
					orders.id IS NOT NULL 
					AND
					orders.status NOT IN (-1,0,4)
				)
				OR 
				(
					o.id IS NOT NULL 
					AND
					o.status NOT IN (-1,0,4)
				)
			)
			ORDER BY 
			IF(orders.id IS NOT NULL,orders.date_order,o.date_order) DESC
			LIMIT 1';		
			$command = $connection->createCommand($sql);
			
			$order = array();
			if ($row = $command->queryRow(true,array(':id'=>$p->id))) $order = $row;
			
			echo base64_encode(serialize($order));
			
			// adad
		}		
	}
	
	/**
	 * Filters
	 */
		
    public function filters()
    {
        return array(
            'accessControl',
        );
    }
	
	
	/**
	 * Access Rules
	 */
	
	/*
    public function accessRules()
    {
        return array(	
        );
    }*/
	
}