<?php
Html::include_uploadify();		

if (Tbl_OrdersComment::model()->count('read_comment=0 AND id_user_created=0')) {
	$new_comments=1;
}else{
	$new_comments=0;	
}

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/site/index', 'LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?chapter_2.html");


// add a layout to the Main Template Layout B 
var layout = new Object();

layout.obj = templateLayout_B.attachLayout("3U");
layout.A = new Object();
layout.C = new Object();
layout.B = new Object();

layout.A.obj = layout.obj.cells("a");
layout.C.obj = layout.obj.cells("c");
layout.B.obj = layout.obj.cells("b");
layout.C.obj.setHeight(200);

//AttachTabbar to Layout A
layout.A.tabs = new Object();

layout.A.tabs.obj = layout.A.obj.attachTabbar();
layout.A.tabs.obj.setImagePath(dhx_globalImgPath);
layout.A.tabs.obj.addTab("orders_new","'.Yii::t('views/site/index', 'LABEL_LAST_ORDERS').'","100px");
layout.A.tabs.obj.addTab("orders_comments","'.Yii::t('views/site/index', 'LABEL_CUSTOMERS_COMMENTS').'","170px");
if('.$new_comments.'){layout.A.tabs.obj.setCustomStyle("orders_comments",null,null,"color:#FF0000;");}
layout.A.tabs.obj.enableTabCloseButton(false);
layout.A.tabs.obj.setTabActive("orders_new");

layout.A.obj.showHeader();
layout.A.obj.setText("'.Yii::t('views/site/index', 'LABEL_ORDERS').'");
//-----------------------------



//AttachTabbar to Layout C
layout.C.tabs = new Object();

layout.C.tabs.obj = layout.C.obj.attachTabbar();
layout.C.tabs.obj.setImagePath(dhx_globalImgPath);
layout.C.tabs.obj.addTab("stats_biggest_spenders","'.Yii::t('views/site/index', 'LABEL_STATS_BIGGEST_SPENDER').'","120px");
layout.C.tabs.obj.enableTabCloseButton(false);
layout.C.tabs.obj.setTabActive("stats_biggest_spenders");

layout.C.obj.showHeader();
layout.C.obj.setText("'.Yii::t('views/site/index', 'LABEL_STATS').'");
//-----------------------------

//AttachTabbar to Layout D
layout.B.tabs = new Object();

layout.B.tabs.obj = layout.B.obj.attachTabbar();
layout.B.tabs.obj.setImagePath(dhx_globalImgPath);
layout.B.tabs.obj.addTab("products_low_inventory","'.Yii::t('views/site/index', 'LABEL_PRODUCTS_LOW_INVENTORY').'","110px");
layout.B.tabs.obj.addTab("products_out_of_stock","'.Yii::t('views/site/index', 'LABEL_PRODUCTS_OUT_OF_STOCK').'","110px");
layout.B.tabs.obj.addTab("products_best_selling","'.Yii::t('views/site/index', 'LABEL_PRODUCTS_BEST_SELLING').'","110px");
layout.B.tabs.obj.enableTabCloseButton(false);
layout.B.tabs.obj.setTabActive("products_low_inventory");

layout.B.obj.showHeader();
layout.B.obj.setText("'.Yii::t('views/site/index', 'LABEL_PRODUCTS').'");
//-----------------------------

// Add a grid to the first tab (orders_new) in the Layout A
layout.A.tabs.orders_new = new Object();
layout.A.tabs.orders_new.grid = new Object();
layout.A.tabs.orders_new.grid.obj = layout.A.tabs.obj.cells("orders_new").attachGrid();
layout.A.tabs.orders_new.grid.obj.setImagePath(dhx_globalImgPath);	
layout.A.tabs.orders_new.grid.obj.setHeader("'.Yii::t('views/orders/index', 'LABEL_INVOICE_NO').','.Yii::t('views/orders/index', 'LABEL_ORDER_DATE').','.Yii::t('views/orders/index', 'LABEL_GRAND_TOTAL').','.Yii::t('views/orders/index', 'LABEL_STATUS').','.Yii::t('views/orders/index', 'LABEL_PRIORITY').'",null,["text-align:left","text-align:left","text-align:right","text-align:center","text-align:center"]);


layout.A.tabs.orders_new.grid.obj.setInitWidthsP("20,29,17,19,15");
layout.A.tabs.orders_new.grid.obj.setColAlign("left,left,right,center,center");
layout.A.tabs.orders_new.grid.obj.setColSorting("na,na,na,na,na");
layout.A.tabs.orders_new.grid.obj.enableResizing("false,false,false,false,false");
layout.A.tabs.orders_new.grid.obj.setSkin(dhx_skin);
layout.A.tabs.orders_new.grid.obj.enableMultiline(false);
layout.A.tabs.orders_new.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

layout.A.tabs.orders_new.grid.obj.init();

// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.A.tabs.orders_new.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_orders').'";
// load the initial grid
load_grid(layout.A.tabs.orders_new.grid.obj);

layout.A.tabs.orders_new.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	goto_url("'.CController::createAbsoluteUrl('orders/').'?id_orders="+rId); 
});

// Add a grid to the tab (orders_comments) in the Layout A
layout.A.tabs.orders_comments = new Object();
layout.A.tabs.orders_comments.grid = new Object();
layout.A.tabs.orders_comments.grid.obj = layout.A.tabs.obj.cells("orders_comments").attachGrid();
layout.A.tabs.orders_comments.grid.obj.setImagePath(dhx_globalImgPath);	
layout.A.tabs.orders_comments.grid.obj.setHeader("'.Yii::t('views/orders/index', 'LABEL_INVOICE_NO').','.Yii::t('views/site/index', 'LABEL_CUSTOMERS_COMMENTS_DATE').','.Yii::t('views/site/index', 'LABEL_CUSTOMERS_COMMENTS_COMMENTS').'",null,["text-align:left","text-align:left","text-align:left"]);


layout.A.tabs.orders_comments.grid.obj.setInitWidthsP("20,25,55");
layout.A.tabs.orders_comments.grid.obj.setColAlign("left,left,left");
layout.A.tabs.orders_comments.grid.obj.setColSorting("na,na,na");
layout.A.tabs.orders_comments.grid.obj.enableResizing("false,false,false");
layout.A.tabs.orders_comments.grid.obj.setSkin(dhx_skin);
layout.A.tabs.orders_comments.grid.obj.enableMultiline(false);
layout.A.tabs.orders_comments.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

layout.A.tabs.orders_comments.grid.obj.init();

// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.A.tabs.orders_comments.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_orders_comments').'";
// load the initial grid
load_grid(layout.A.tabs.orders_comments.grid.obj);

layout.A.tabs.orders_comments.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	goto_url("'.CController::createAbsoluteUrl('orders/').'?id_orders="+rId); 
});

//-----------------------------

// Add a grid to the first tab (stats_biggest_spenders) in the Layout C
layout.C.tabs.stats_biggest_spenders = new Object();
layout.C.tabs.stats_biggest_spenders.grid = new Object();
layout.C.tabs.stats_biggest_spenders.grid.obj = layout.C.tabs.obj.cells("stats_biggest_spenders").attachGrid();
layout.C.tabs.stats_biggest_spenders.grid.obj.setImagePath(dhx_globalImgPath);	
layout.C.tabs.stats_biggest_spenders.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_TOTAL').'",null,["text-align:left","text-align:right"]);

layout.C.tabs.stats_biggest_spenders.grid.obj.setInitWidthsP("80,20");
layout.C.tabs.stats_biggest_spenders.grid.obj.setColAlign("left,right");
layout.C.tabs.stats_biggest_spenders.grid.obj.setColSorting("na,na");
layout.C.tabs.stats_biggest_spenders.grid.obj.enableResizing("false,false");
layout.C.tabs.stats_biggest_spenders.grid.obj.setSkin(dhx_skin);
layout.C.tabs.stats_biggest_spenders.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

layout.C.tabs.stats_biggest_spenders.grid.obj.init();


// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.C.tabs.stats_biggest_spenders.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_biggest_spenders').'";
// load the initial grid
load_grid(layout.C.tabs.stats_biggest_spenders.grid.obj);

layout.C.tabs.stats_biggest_spenders.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	var uri_encode_name=encodeURIComponent(this.cellById(rId,0).getValue());
	goto_url("'.CController::createAbsoluteUrl('customers/').'?id_customer="+rId+"&name_customer="+uri_encode_name);
});

//-----------------------------

// Add a grid to the tab (products_out_of_stock) in the Layout B
layout.B.tabs.products_low_inventory = new Object();
layout.B.tabs.products_low_inventory.grid = new Object();
layout.B.tabs.products_low_inventory.grid.obj = layout.B.tabs.obj.cells("products_low_inventory").attachGrid();
layout.B.tabs.products_low_inventory.grid.obj.setImagePath(dhx_globalImgPath);	
layout.B.tabs.products_low_inventory.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_VARIANT').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_QTY').'",null,["text-align:left","text-align:left","text-align:left","text-align:center"]);

layout.B.tabs.products_low_inventory.grid.obj.setInitWidthsP("40,30,20,10");
layout.B.tabs.products_low_inventory.grid.obj.setColAlign("left,left,left,center");
layout.B.tabs.products_low_inventory.grid.obj.setColSorting("na,na,na,na");
layout.B.tabs.products_low_inventory.grid.obj.enableResizing("true,true,true,false");
layout.B.tabs.products_low_inventory.grid.obj.setSkin(dhx_skin);
layout.B.tabs.products_low_inventory.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

layout.B.tabs.products_low_inventory.grid.obj.init();


// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.B.tabs.products_low_inventory.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_product_low_inventory').'";
// load the initial grid
load_grid(layout.B.tabs.products_low_inventory.grid.obj);

layout.B.tabs.products_low_inventory.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	//tabs.load_tab(tabs.obj,rId,this.cellById(rId,1).getValue());
	var uri_encode_name=encodeURIComponent(this.cellById(rId,0).getValue());
	goto_url("'.CController::createAbsoluteUrl('products/').'?id_product="+rId+"&name_product="+uri_encode_name);
});

// Add a grid to the tab (products_out_of_stock) in the Layout B
layout.B.tabs.products_out_of_stock = new Object();
layout.B.tabs.products_out_of_stock.grid = new Object();
layout.B.tabs.products_out_of_stock.grid.obj = layout.B.tabs.obj.cells("products_out_of_stock").attachGrid();
layout.B.tabs.products_out_of_stock.grid.obj.setImagePath(dhx_globalImgPath);	
layout.B.tabs.products_out_of_stock.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_VARIANT').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_QTY').'",null,["text-align:left","text-align:left","text-align:left","text-align:center"]);

layout.B.tabs.products_out_of_stock.grid.obj.setInitWidthsP("40,30,20,10");
layout.B.tabs.products_out_of_stock.grid.obj.setColAlign("left,left,left,center");
layout.B.tabs.products_out_of_stock.grid.obj.setColSorting("na,na,na,na");
layout.B.tabs.products_out_of_stock.grid.obj.enableResizing("true,true,true,false");
layout.B.tabs.products_out_of_stock.grid.obj.setSkin(dhx_skin);
layout.B.tabs.products_out_of_stock.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

layout.B.tabs.products_out_of_stock.grid.obj.init();


// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.B.tabs.products_out_of_stock.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_product_out_of_stock').'";
// load the initial grid
load_grid(layout.B.tabs.products_out_of_stock.grid.obj);

layout.B.tabs.products_out_of_stock.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	//tabs.load_tab(tabs.obj,rId,this.cellById(rId,1).getValue());
	var uri_encode_name=encodeURIComponent(this.cellById(rId,0).getValue());
	goto_url("'.CController::createAbsoluteUrl('products/').'?id_product="+rId+"&name_product="+uri_encode_name); 
});

// Add a grid to the tab (products_best_selling) in the Layout D
layout.B.tabs.products_best_selling = new Object();
layout.B.tabs.products_best_selling.grid = new Object();
layout.B.tabs.products_best_selling.grid.obj = layout.B.tabs.obj.cells("products_best_selling").attachGrid();
layout.B.tabs.products_best_selling.grid.obj.setImagePath(dhx_globalImgPath);	
layout.B.tabs.products_best_selling.grid.obj.setHeader("'.Yii::t('global','LABEL_NAME').','.Yii::t('global','LABEL_VARIANT').','.Yii::t('global','LABEL_SKU').','.Yii::t('global','LABEL_QTY').'",null,["text-align:left","text-align:left","text-align:left","text-align:center"]);

layout.B.tabs.products_best_selling.grid.obj.setInitWidthsP("40,30,20,10");
layout.B.tabs.products_best_selling.grid.obj.setColAlign("left,left,left,center");
layout.B.tabs.products_best_selling.grid.obj.setColSorting("na,na,na,na");
layout.B.tabs.products_best_selling.grid.obj.enableResizing("true,true,true,false");
layout.B.tabs.products_best_selling.grid.obj.setSkin(dhx_skin);
layout.B.tabs.products_best_selling.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);

layout.B.tabs.products_best_selling.grid.obj.init();


// we create a variable to store the default url used to get our grid data, so we can reuse it later
layout.B.tabs.products_best_selling.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_products_best_selling').'";
// load the initial grid
load_grid(layout.B.tabs.products_best_selling.grid.obj);

layout.B.tabs.products_best_selling.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	//tabs.load_tab(tabs.obj,rId,this.cellById(rId,1).getValue());
	var uri_encode_name=encodeURIComponent(this.cellById(rId,0).getValue());
	goto_url("'.CController::createAbsoluteUrl('products/').'?id_product="+rId+"&name_product="+uri_encode_name); 
});

//-----------------------------

',CClientScript::POS_END);
?>