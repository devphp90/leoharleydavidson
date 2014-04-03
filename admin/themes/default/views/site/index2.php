<?php
$connection=Yii::app()->db;   // assuming you have configured a "db" connection

$help_hint_path = '/dashboard/';

// Client Script
$cs=Yii::app()->clientScript; 

Html::include_jquery_scrollto();

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/site/index', 'LABEL_TITLE').'");

var dhxWins = new dhtmlXWindows();
dhxWins.enableAutoViewport(true);
dhxWins.setImagePath(dhx_globalImgPath);

var wins = new Object();
var wins2 = new Object();

var ajaxRequest;
var popup_grid;

var content = new Object();
content.obj = new Object();

content.obj = templateLayout_B.attachObject("revenues");

$(function(){
	$("#select_revenue_option").on("change",function(){
		if ($(this).val() == 7) {
			if (ajaxRequest) ajaxRequest.abort();
			
			$("#revenue_dates").show();			
		} else {
			$("#revenue_dates").hide();				
			
			get_revenues_stats();
		}
	});
	
	$("#revenue_start_date,#revenue_end_date").datepicker({
		dateFormat: "yy-mm-dd",
		showOn: "button",
		buttonImage: dhx_globalImgPath+"toolbar/calendar.gif",
		buttonImageOnly: true,
		buttonText: "Calendar",
		onClose: function(dateText, inst) { get_revenues_stats(); }
	});		
	
	get_revenues_stats();
	
	$("#orders_unread_comments").on("click",function(){
		popup_grid = open_orders_unread_comments();
	});
	
	$("#products_low_inventory").on("click",function(){
		popup_grid = open_products_low_inventory();
	});
	
	$("#options_low_inventory").on("click",function(){
		popup_grid = open_options_low_inventory();
	});		
	
	$("#view_more_top_buyers").on("click",function(){
		popup_grid = open_top_buyers();
	});
	
	$("#view_more_top_selling_products").on("click",function(){
		popup_grid = open_top_selling_products();
	});
	
	
	$("#select_top_buyers_option").on("change",function(){
		// we create a variable to store the default url used to get our grid data, so we can reuse it later
		popup_grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_top_buyers').'?top="+$(this).val();
		// load the initial grid
		load_grid(popup_grid.obj);
	});	
	
	$("#select_top_selling_products_option").on("change",function(){
		// we create a variable to store the default url used to get our grid data, so we can reuse it later
		popup_grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_top_selling_products').'?top="+$(this).val();
		// load the initial grid
		load_grid(popup_grid.obj);
	});	
	
	$("#revenue_data").on("click",".sales_information",function(){
		popup_grid = open_sales_information();
	});
});

function get_revenues_stats()
{
	if (ajaxRequest) ajaxRequest.abort();
	
	ajaxRequest = $.ajax({
		url: "'.CController::createUrl('get_revenue_stats').'",
		data: { "revenue_option":$("#select_revenue_option").val(),"revenue_start_date":$("#revenue_start_date").val(),"revenue_end_date":$("#revenue_end_date").val() },
		type: "POST",
		beforeSend: function(jqXHR, settings) {
			$("#revenue_data").html("");	
			ajaxOverlay("revenue_data",1);
		},
		complete: function(){
			ajaxOverlay("revenue_data",0);
		},
		success: function(data){
			$("#revenue_data").html("").append(data);
		}
	});	
}

function open_products_low_inventory()
{
	wins.obj = dhxWins.createWindow("_loadTemplateWindow", 10, 10, 700, 420);
	wins.obj.setText("Products low in inventory or out of stock");
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);
	wins.obj.denyResize();	
	
	wins.obj.attachEvent("onClose", function(win){
        // code here
		refresh_products_low_inventory_count();
		wins = new Object();
		
		return true;
    });
	
	var toolbar = new Object();
	toolbar.obj = wins.obj.attachToolbar();
	toolbar.obj.setIconsPath(dhx_globalImgPath);	
	toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);
	
	toolbar.obj.attachEvent("onClick", function(id){
		var obj = this;
		var title = "Products low in inventory";
		
		switch (id) {
			case "export_pdf":
				printGridPopup(grid.obj,"pdf",[3],title);
				break;	
			case "export_excel":
				printGridPopup(grid.obj,"excel",[3],title);
				break;		
			case "print":
				printGridPopup(grid.obj,"printview",[3],title);
				break;					
		}
	});		
	
	var grid = new Object();
	grid.obj = wins.obj.attachGrid(); 
	grid.obj.setImagePath(dhx_globalImgPath);	
	grid.obj.setHeader("'.Yii::t('global', 'LABEL_NAME').','.Yii::t('global', 'LABEL_SKU').','.Yii::t('global', 'LABEL_QTY').',&nbsp;",null,["text-align:left","text-align:left","text-align:center","text-align:center"]);
	
	
	grid.obj.setInitWidthsP("40,30,10,20");
	grid.obj.setColAlign("left,left,center,center");
	grid.obj.setColSorting("na,na,na,na");
	grid.obj.enableResizing("false,false,false,false");
	grid.obj.setSkin(dhx_skin);
	grid.obj.enableMultiline(true);
	grid.obj.enableRowsHover(true,dhx_rowhover);

	grid.obj.attachEvent("onBeforeSelect", function(new_row,old_row){
		$("input[name=\'qty["+new_row+"]\']").select();	
		
		return true;
	});	

	grid.obj.entBox.onselectstart = function(e){ (e||event).cancelBubble=true; return true; }

	grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});

	grid.obj.init();
	
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_products_low_inventory').'";
	// load the initial grid
	load_grid(grid.obj);
	
	
	return grid;
}

function apply_product_qty(i)
{
	var id = i;
	var arr = id.split(":");
	var id_product = arr[0];
	var id_product_variant = arr[1];
	var qty = $("input[name=\'qty["+id+"]\']").val();	
	
	if (qty > 0) {		
		$.ajax({
			url: "'.CController::createUrl('apply_product_qty').'",
			data: { "qty":qty, "id_product":id_product, "id_product_variant":id_product_variant },
			success: function(data){
				load_grid(popup_grid.obj);
			}
		});	
	} else {
		$("input[name=\'qty["+id+"]\']:not(.error)").addClass("error");
		alert("Please enter a valid qty.");	
	}
}

function open_options_low_inventory()
{
	wins.obj = dhxWins.createWindow("_loadTemplateWindow", 10, 10, 700, 420);
	wins.obj.setText("Options low in inventory or out of stock");
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);	
	wins.obj.denyResize();
	
	wins.obj.attachEvent("onClose", function(win){
        // code here		
		refresh_options_low_inventory_count();
		wins = new Object();

		return true;
    });
	
	var toolbar = new Object();
	toolbar.obj = wins.obj.attachToolbar();
	toolbar.obj.setIconsPath(dhx_globalImgPath);	
	toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);
	
	toolbar.obj.attachEvent("onClick", function(id){
		var obj = this;
		var title = "Options low in inventory";
		
		switch (id) {
			case "export_pdf":
				printGridPopup(grid.obj,"pdf",[3],title);
				break;	
			case "export_excel":
				printGridPopup(grid.obj,"excel",[3],title);
				break;		
			case "print":
				printGridPopup(grid.obj,"printview",[3],title);
				break;					
		}
	});	
	
	var grid = new Object();
	grid.obj = wins.obj.attachGrid(); 
	grid.obj.setImagePath(dhx_globalImgPath);	
	grid.obj.setHeader("'.Yii::t('global', 'LABEL_NAME').','.Yii::t('global', 'LABEL_SKU').','.Yii::t('global', 'LABEL_QTY').',&nbsp;",null,["text-align:left","text-align:left","text-align:center","text-align:center"]);
	
	
	grid.obj.setInitWidthsP("40,30,10,20");
	grid.obj.setColAlign("left,left,center,center");
	grid.obj.setColSorting("na,na,na,na");
	grid.obj.enableResizing("false,false,false,false");
	grid.obj.setSkin(dhx_skin);
	grid.obj.enableMultiline(true);
	grid.obj.enableRowsHover(true,dhx_rowhover);

	grid.obj.attachEvent("onBeforeSelect", function(new_row,old_row){
		$("input[name=\'qty["+new_row+"]\']").select();	
		
		return true;
	});	

	grid.obj.entBox.onselectstart = function(e){ (e||event).cancelBubble=true; return true; }

	grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});

	grid.obj.init();
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_options_low_inventory').'";
	// load the initial grid
	load_grid(grid.obj);
	
	
	return grid;
}

function apply_option_qty(i)
{
	var id = i;
	var qty = $("input[name=\'qty["+id+"]\']").val();	
	
	if (qty > 0) {		
		$.ajax({
			url: "'.CController::createUrl('apply_option_qty').'",
			data: { "qty":qty, "id_options":id },
			success: function(data){
				load_grid(popup_grid.obj);
			}
		});	
	} else {
		$("input[name=\'qty["+id+"]\']:not(.error)").addClass("error");
		alert("Please enter a valid qty.");	
	}
}


function open_orders_unread_comments()
{
	wins.obj = dhxWins.createWindow("_loadTemplateWindow", 10, 10, 700, 420);
	wins.obj.setText("Orders - Unread comments");
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);	
	wins.obj.denyResize();
	
	wins.obj.attachEvent("onClose", function(win){
        // code here
		refresh_orders_unread_comments_count();
		wins = new Object();
		
		return true;
    });
	
	var toolbar = new Object();
	toolbar.obj = wins.obj.attachToolbar();
	toolbar.obj.setIconsPath(dhx_globalImgPath);	
	toolbar.obj.addButton("mark_read", null, "'.Yii::t('global','LABEL_BTN_MARK_READ').'", null, null);
	toolbar.obj.addSeparator("sep1", null);
	toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);	
	
	toolbar.obj.attachEvent("onClick", function(id){
		var obj = this;
		var title = "Orders - Unread comments";
		
		switch (id) {
			case "mark_read":
				var checked = popup_grid.obj.getCheckedRows(0);
				
				if (checked) {
					if (confirm("'.Yii::t('views/site/index','LABEL_ALERT_MARK_COMMENTS_AS_READ').'")) {
						checked = checked.split(",");
						var ids=[];
						
						for (var i=0;i<checked.length;++i) {
							if (checked[i]) {
								ids.push("ids[]="+checked[i]);									
							}
						}

						$.ajax({
							url: "'.CController::createUrl('mark_comment_as_read').'",
							type: "POST",
							data: ids.join("&"),
							dataType: "json",							
							success: function(data){																									
								load_grid(popup_grid.obj);
								alert("'.Yii::t('views/site/index','LABEL_ALERT_MARK_COMMENTS_AS_READ_SUCCESS').'");
							}
						});
					}
				} else {
					alert("'.Yii::t('views/site/index','LABEL_ALERT_NO_COMMENTS_CHECKED').'");	
				}			
				break;
			case "export_pdf":
				printGridPopup(grid.obj,"pdf",[0],title);
				break;	
			case "export_excel":
				printGridPopup(grid.obj,"excel",[0],title);
				break;		
			case "print":
				printGridPopup(grid.obj,"printview",[0],title);
				break;						
		}
	});
	
	
	var grid = new Object();
	grid.obj = wins.obj.attachGrid(); 
	grid.obj.setImagePath(dhx_globalImgPath);	
	grid.obj.setHeader("#master_checkbox,'.Yii::t('views/site/index', 'LABEL_CUSTOMERS_COMMENTS_DATE').','.Yii::t('views/orders/index', 'LABEL_INVOICE_NO').','.Yii::t('views/site/index', 'LABEL_CUSTOMERS_COMMENTS_COMMENTS').',&nbsp;",null,["text-align:center","text-align:left","text-align:left","text-align:left","text-align:left",null]);
	
	
	grid.obj.setInitWidthsP("10,20,15,40,15");
	grid.obj.setColAlign("center,left,left,left,center");
	grid.obj.setColSorting("na,na,na,na,na");
	grid.obj.enableResizing("false,false,false,false,false");
	grid.obj.setSkin(dhx_skin);
	grid.obj.enableMultiline(true);
	grid.obj.enableRowsHover(true,dhx_rowhover);		

	grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});

	grid.obj.init();
	
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_orders_unread_comments').'";
	// load the initial grid
	load_grid(grid.obj);
	
	
	return grid;
}



function add_reply_orders_comments(i,id_orders)
{
	wins.obj.setModal(false);
	
	wins2.obj = dhxWins.createWindow("_loadTemplateWindow2", 10, 10, 400, 600);
	wins2.obj.setText("Orders - Reply to comment");
	wins2.obj.button("park").hide();
	wins2.obj.keepInViewport(true);
	wins2.obj.denyResize();
		
	wins2.obj.setModal(true);	
	
	wins2.obj.attachEvent("onClose", function(win){
        // code here
		this.detachObject();
		this.setModal(false);	
		wins.obj.setModal(true);
		load_grid(popup_grid.obj);
		wins2 = new Object();
		
		return true;
    });
	
	var toolbar = new Object();
	toolbar.obj = wins2.obj.attachToolbar();
	toolbar.obj.setIconsPath(dhx_globalImgPath);	
	toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
	toolbar.obj.addButton("save_close",null,"'.Yii::t('global','LABEL_BTN_SAVE_CLOSE').'","toolbar/save.gif","toolbar/save_dis.gif");  
	
	toolbar.obj.attachEvent("onClick", function(id){
		var obj = this;
		var title = "'.Yii::t('views/customers/index','LABEL_CUSTOMERS').'";
		
		switch (id) {
			case "save":	
				toolbar.save(id,false);
				break;
			case "save_close":
				toolbar.save(id,true);
				break;		
		}
	});
	
	toolbar.save = function(id,close){
		var obj = toolbar.obj;
		
		if ($("#comments").val().length) {
			$.ajax({
				url: "'.CController::createUrl('add_comment').'?id="+id_orders+"&id_orders_comment="+i,
				type: "POST",
				data: $("#add_comment").serialize(),
				dataType: "json",
				beforeSend: function(){		
					obj.disableItem(id);
				},
				complete: function(){
					if (typeof obj.enableItem == "function") obj.enableItem(id);
				},
				success: function(data){					
					// clear all errors				
					$("#comments").html("").removeClass("error");	
					$("#comments_errorMsg").html("");
					if (close) {
						wins2.obj.close();
					} else {
						load_comments(i);
					}
				},
				error: function(jqXHR, textStatus, errorThrown){
					alert(jqXHR.responseText);
				}
			});				
		} else {
			$("#comments").addClass("error");
			$("#comments_errorMsg").html("").append("'.Yii::t('global','ERROR_EMPTY').'");
		}		
	}
	
	
	var layout = new Object();
	layout.obj = wins2.obj.attachObject("add_comment"); 
	load_comments(i);
}

function load_comments(i)
{
	if (ajaxRequest) ajaxRequest.abort();
	
	ajaxRequest = $.ajax({
		url: "'.CController::createUrl('get_order_comments').'",
		data: { "id_orders_comment":i },
		dataType: "json",
		beforeSend: function(jqXHR, settings) {
			$("#current_comment,#list_comments").html("");	
			ajaxOverlay("current_comment",1);
			ajaxOverlay("list_comments",1);
		},
		complete: function(){
			ajaxOverlay("current_comment",0);
			ajaxOverlay("list_comments",0);
		},
		success: function(data){			
			//if (data.current_comment) $("#current_comment").html("").append(data.current_comment);
			if (data.comments) $("#list_comments").html("").append(data.comments);				
			$("#list_comments").scrollTo( ".current_comment", 800);
			
			var height = parseInt($(".dhtmlx_window_active").css("height").replace("px",""))-parseInt($("#list_comments").offset().top)-20;
			
			$("#list_comments").css("height",height+"px");
		}
	});			
}


function refresh_products_low_inventory_count()
{
	$.ajax({
		url: "'.CController::createUrl('get_products_low_inventory_count').'",
		success: function(data){			
			$("#products_low_inventory").html("").append(data);
		}
	});		
}

function refresh_options_low_inventory_count()
{
	$.ajax({
		url: "'.CController::createUrl('get_options_low_inventory_count').'",
		success: function(data){			
			$("#options_low_inventory").html("").append(data);
		}
	});			
}

function refresh_orders_unread_comments_count()
{
	$.ajax({
		url: "'.CController::createUrl('get_orders_unread_comments_count').'",
		success: function(data){			
			$("#orders_unread_comments").html("").append(data);
		}
	});		
}

function open_top_buyers()
{
	wins.obj = dhxWins.createWindow("_loadTemplateWindow", 10, 10, 700, 420);
	wins.obj.setText("Top buyers");
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);	
	wins.obj.denyResize();
	
	wins.obj.attachEvent("onClose", function(win){
        // code here
		this.detachObject();
		wins = new Object();
		
		return true;
    });
	
	var layout = new Object();
	layout.obj = wins.obj.attachLayout("1C");
	layout.obj.cells("a").attachObject("top_buyers_container");
	layout.obj.cells("a").hideHeader();
	
	var toolbar = new Object();
	toolbar.obj = layout.obj.cells("a").attachToolbar();
	toolbar.obj.setIconsPath(dhx_globalImgPath);	
	toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);
	
	toolbar.obj.attachEvent("onClick", function(id){
		var obj = this;
		var title = "Top buyers";
		
		switch (id) {
			case "export_pdf":
				printGridPopup(grid.obj,"pdf",null,title);
				break;	
			case "export_excel":
				printGridPopup(grid.obj,"excel",null,title);
				break;		
			case "print":
				printGridPopup(grid.obj,"printview",null,title);
				break;					
		}
	});			
	
	var grid = new Object();
	grid.obj = new dhtmlXGridObject("top_buyers_grid");
	grid.obj.setImagePath(dhx_globalImgPath);	
	grid.obj.setHeader("'.Yii::t('global', 'LABEL_NAME').','.Yii::t('global', 'LABEL_EMAIL').','.Yii::t('global', 'LABEL_TOTAL').'",null,["text-align:left","text-align:left","text-align:right"]);
	
	
	grid.obj.setInitWidthsP("45,35,20");
	grid.obj.setColAlign("left,left,right");
	grid.obj.setColSorting("na,na,na");
	grid.obj.enableResizing("false,false,false");
	grid.obj.setSkin(dhx_skin);
	grid.obj.enableMultiline(true);
	grid.obj.enableRowsHover(true,dhx_rowhover);

	grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});

	grid.obj.init();
	
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_top_buyers').'";
	// load the initial grid
	load_grid(grid.obj);
	
	return grid;
}

function open_top_selling_products()
{
	wins.obj = dhxWins.createWindow("_loadTemplateWindow", 10, 10, 700, 420);
	wins.obj.setText("Top selling products");
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);	
	wins.obj.denyResize();
	
	wins.obj.attachEvent("onClose", function(win){
        // code here
		this.detachObject();
		wins = new Object();
		
		return true;
    });
	
	var layout = new Object();
	layout.obj = wins.obj.attachLayout("1C");
	layout.obj.cells("a").attachObject("top_selling_products_container");
	layout.obj.cells("a").hideHeader();
	
	var toolbar = new Object();
	toolbar.obj = layout.obj.cells("a").attachToolbar();
	toolbar.obj.setIconsPath(dhx_globalImgPath);	
	toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);
	
	toolbar.obj.attachEvent("onClick", function(id){
		var obj = this;
		var title = "Top selling products";
		
		switch (id) {
			case "export_pdf":
				printGridPopup(grid.obj,"pdf",null,title);
				break;	
			case "export_excel":
				printGridPopup(grid.obj,"excel",null,title);
				break;		
			case "print":
				printGridPopup(grid.obj,"printview",null,title);
				break;					
		}
	});			

	var grid = new Object();
	grid.obj = new dhtmlXGridObject("top_selling_products_grid");
	grid.obj.setImagePath(dhx_globalImgPath);	
	grid.obj.setHeader("'.Yii::t('global', 'LABEL_NAME').','.Yii::t('global', 'LABEL_QTY').','.Yii::t('global', 'LABEL_TOTAL').'",null,["text-align:left","text-align:center","text-align:right"]);
	
	
	grid.obj.setInitWidthsP("70,10,20");
	grid.obj.setColAlign("left,center,right");
	grid.obj.setColSorting("na,na,na");
	grid.obj.enableResizing("false,false,false");
	grid.obj.setSkin(dhx_skin);
	grid.obj.enableMultiline(true);
	grid.obj.enableRowsHover(true,dhx_rowhover);

	grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});

	grid.obj.init();
	
	
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_top_selling_products').'";
	// load the initial grid
	load_grid(grid.obj);	
	
	return grid;
}

function open_sales_information()
{
	wins.obj = dhxWins.createWindow("_loadTemplateWindow", 10, 10, 700, 420);
	wins.obj.setText("Sales information");
	wins.obj.button("park").hide();
	wins.obj.keepInViewport(true);
	wins.obj.setModal(true);	
	wins.obj.denyResize();	
	
	var toolbar = new Object();
	toolbar.obj = wins.obj.attachToolbar();
	toolbar.obj.setIconsPath(dhx_globalImgPath);	
	toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
	toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
	toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);
	
	toolbar.obj.attachEvent("onClick", function(id){
		var obj = this;
		var title = "Sales information";
		
		switch (id) {
			case "export_pdf":
				printGridPopup(grid.obj,"pdf",null,title);
				break;	
			case "export_excel":
				printGridPopup(grid.obj,"excel",null,title);
				break;		
			case "print":
				printGridPopup(grid.obj,"printview",null,title);
				break;					
		}
	});			
	
	var grid = new Object();
	grid.obj = wins.obj.attachGrid(); 
	grid.obj.setImagePath(dhx_globalImgPath);	
	grid.obj.setHeader("'.Yii::t('views/orders/index', 'LABEL_INVOICE_NO').','.Yii::t('views/orders/index','LABEL_ORDER_DATE').','.Yii::t('global', 'LABEL_NAME').','.Yii::t('global', 'LABEL_TOTAL').','.Yii::t('global', 'LABEL_TOTAL_PROFITS').'",null,["text-align:left","text-align:left","text-align:left","text-align:right","text-align:right"]);
	
	
	grid.obj.setInitWidthsP("15,20,25,20,20");
	grid.obj.setColAlign("left,left,left,right,right");
	grid.obj.setColSorting("na,na,na,na,na");
	grid.obj.enableResizing("false,false,false,false,false");
	grid.obj.setSkin(dhx_skin);
	grid.obj.enableMultiline(true);
	grid.obj.enableRowsHover(true,dhx_rowhover);

	grid.obj.attachEvent("onXLS", function(grid_obj){
		ajaxOverlay(grid_obj.entBox.id+" .objbox",1);
	}); 
	
	grid.obj.attachEvent("onXLE", function(grid_obj,count){
		ajaxOverlay(grid_obj.entBox.id,0);
	});

	grid.obj.init();
	
	var revenue_option = $("#select_revenue_option").val();
	var revenue_start_date = $("#revenue_start_date").val();
	var revenue_end_date = $("#revenue_end_date").val();
		
	// we create a variable to store the default url used to get our grid data, so we can reuse it later
	grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_sales_information').'?revenue_option="+revenue_option+"&revenue_start_date="+revenue_start_date+"&revenue_end_date="+revenue_end_date;
	// load the initial grid
	load_grid(grid.obj);				
	
	return grid;
}


function printGridPopup(obj,method,omit,title,desc){
	if (!obj.getAllRowIds().length) { 
		alert("'.Yii::t('components/Html','LABEL_ALERT_NO_RECORD').'"); 
		return false;
	}
	
	title = title ? title:"";
	desc = desc ? desc:"";		
	
	var winsPrint = new Object();	
	
	winsPrint.obj = dhxWins.createWindow("printWindow", 0, 0, 650, 280);
	winsPrint.obj.setText("'.Yii::t('components/Html','LABEL_TITLE_WINDOW').'");
	winsPrint.obj.button("minmax1").hide();
	winsPrint.obj.button("park").hide();
	winsPrint.obj.keepInViewport(true);
	if (wins && wins.obj) wins.obj.setModal(false);
	
	winsPrint.obj.setModal(true);
	winsPrint.obj.center();
	
	winsPrint.obj.attachEvent("onClose",function(){
		if (wins && wins.obj) {
			this.setModal(false);
			wins.obj.setModal(true);
		}
		
		return true;
	});
	
	winsPrint.toolbar = new Object();
	winsPrint.toolbar.obj = winsPrint.obj.attachToolbar();
	winsPrint.toolbar.obj.setIconsPath(dhx_globalImgPath);	
	
	switch (method) {
		case "pdf":		
			winsPrint.toolbar.obj.addButton("export",null,"'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'","toolbar/pdf.png", null);
			break;
		case "excel":
			winsPrint.toolbar.obj.addButton("export",null,"'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'","toolbar/excel.png", null);
			break;
		case "printview":
			winsPrint.toolbar.obj.addButton("export",null,"'.Yii::t('global','LABEL_BTN_PRINT').'","toolbar/print.png", null);	
			break;
	}			
	
	winsPrint.toolbar.obj.attachEvent("onClick",function(id){
		switch (id) {
			case "export":
				if (winsPrint.grid.obj.getCheckedRows(0).length) { 								
					if (omit && omit.length) {
						for (col_id in omit) {
							obj.setColumnHidden(omit[col_id],true);
						}
					}
					
					var rows = winsPrint.grid.obj.getAllRowIds();
					
					if (rows) {
						title = $("#"+winsPrint.layout.obj.cont.obj.id+" input[name=\'print_title\']").length ? $("#"+winsPrint.layout.obj.cont.obj.id+" input[name=\'print_title\']").val():"";
						desc = $("#"+winsPrint.layout.obj.cont.obj.id+" textarea[name=\'print_desc\']").length ? $("#"+winsPrint.layout.obj.cont.obj.id+" textarea[name=\'print_desc\']").val():"";
						
						rows = rows.split(",");					
						
						for (var i=0;i<rows.length;++i) {
							var row_id = rows[i];
							var cell = winsPrint.grid.obj.cellById(row_id,0);
						
							if (!cell.isChecked()) {
								obj.setColumnHidden(row_id,true);
							}
						}					
					
						switch (method) {
							case "pdf":		
								obj.toPDF(export_pdf_url(),"gray",null,null,null,title,desc);
								break;
							case "excel":
								obj.toExcel(export_excel_url(),"gray",null,null,null,title,desc);
								break;
							case "printview":
								var html_output="";
								
								if (title.length) {
									html_output += "<h1>"+title+"</h1>";	
								}
								
								if (desc.length) {
									html_output += "<div><em><pre style=\'padding:0;margin:0;\'>"+desc+"</pre></em></div><br />";	
								}
								
								obj.printView(html_output);
								break;
						}														
						
						for (var i=0;i<rows.length;++i) {
							var row_id = rows[i];
							var cell = winsPrint.grid.obj.cellById(row_id,0);
							
							if (!cell.isChecked()) {
								obj.setColumnHidden(row_id,false);
							}
						}								
					}
					
					if (omit && omit.length) {
						for (col_id in omit) {
							obj.setColumnHidden(omit[col_id],false);
						}
					}		
					
					winsPrint.obj.close();		
				} else {
					alert("'.Yii::t('components/Html','LABEL_ALERT_EXPORT_PDF_EXCEL_PRINT').'");	
				}
				break;	
		}
	});				
	
	winsPrint.layout = new Object();
	winsPrint.layout.obj = winsPrint.obj.attachLayout("2U");
	
	winsPrint.layout.A = new Object();
	winsPrint.layout.A.obj = winsPrint.layout.obj.cells("a");
	winsPrint.layout.A.obj.setWidth(350);
	winsPrint.layout.A.obj.hideHeader();
	winsPrint.layout.A.obj.attachHTMLString(\'<div style="width:100%; height:100%; overflow:auto;"><div style="padding:10px;"><div><strong>'.Yii::t('components/Html','LABEL_TITLE').'</strong><br /><input type="text" name="print_title" value="\'+title+\'" style="width: 100%;" /></div><div><strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong><br /><textarea name="print_desc" rows="4" style="width: 100%;">\'+desc+\'</textarea></div></div></div>\');		
	
	winsPrint.layout.B = new Object();
	winsPrint.layout.B.obj = winsPrint.layout.obj.cells("b");
	winsPrint.layout.B.obj.hideHeader();		
	
	winsPrint.grid = new Object(); 
	winsPrint.grid.obj = winsPrint.layout.B.obj.attachGrid();
	winsPrint.grid.obj.setImagePath(dhx_globalImgPath);
	winsPrint.grid.obj.setHeader("#master_checkbox,'.Yii::t('components/Html','LABEL_COLUMN').'",null,["text-align:center"]);
	winsPrint.grid.obj.setInitWidthsP("15,85");
	winsPrint.grid.obj.setColAlign("center,left");
	winsPrint.grid.obj.setColTypes("ch,ro");
	winsPrint.grid.obj.setColSorting("na,na");
	winsPrint.grid.obj.enableResizing("false,false");
	winsPrint.grid.obj.enableRowsHover(true,dhx_rowhover);
	winsPrint.grid.obj.init();
	
	var columnCount = obj.getColumnsNum();
	for (var i=0;i<columnCount;i++){
		if ((!omit || omit && omit.length && $.inArray(i, omit) == -1) && obj.isColumnHidden(i) == false) {
			var columnName = obj.getColumnLabel(i);		
			
			winsPrint.grid.obj.addRow(i,[1,columnName]);			
		}
	}								
}	    

',CClientScript::POS_END);

// unread comments
$total_unread_comments = Tbl_OrdersComment::model()->count('read_comment=0 AND id_user_created=0');

// unsettled orders / not cancelled, incomplete, declined or completed
$total_unsettled_orders = Tbl_Orders::model()->count('status NOT IN (-1, 0, 4, 7)');

// products low in inventory or out of stock
$sql = 'SELECT 
COUNT(id) AS total
FROM 
(
	(
		SELECT 
		product.id,
		0 AS id_product_variant
		FROM 
		product
		
		WHERE
		product.active=1
		AND
		product.product_type=0 
		AND
		product.has_variants = 0
		AND 
		product.track_inventory = 1 				
		AND 
		(
			(product.in_stock = 1 AND product.notify = 1 AND product.notify_qty > 0 AND product.qty <= product.notify_qty)
			OR
			(product.in_stock = 0 OR (product.in_stock = 1 AND product.qty<=product.out_of_stock))
		)
	)
	UNION
	(
		SELECT 
		product.id,
		product_variant.id AS id_product_variant
		FROM 
		product
		INNER JOIN 
		product_variant 
		ON 
		(product.id = product_variant.id_product)
		
		WHERE
		product.product_type=0 		
		AND
		product.active=1
		AND
		product_variant.active=1
		AND 
		product.track_inventory = 1 
		AND 
		(
			(product.in_stock = 1 AND product.notify = 1 AND product_variant.notify_qty > 0 AND product_variant.qty <= product_variant.notify_qty)
			OR
			(product.in_stock = 0 OR product_variant.in_stock = 0 OR (product.in_stock = 1 AND product_variant.in_stock = 1 AND product_variant.qty<=product.out_of_stock))
		)									
		GROUP BY 
		product.id,
		product_variant.id
	)
) AS t';
$command=$connection->createCommand($sql);					
		
$row_product = $command->queryRow(true);
$total_products_low_inventory = $row_product['total'];	

// products low in inventory or out of stock
$sql = 'SELECT 
COUNT(options.id) AS total
FROM
options
WHERE
options.active = 1
AND
options.track_inventory = 1 
AND
( 
	options.in_stock = 0
	OR					
	(
		options.in_stock = 1
		AND 
		(options.notify = 1 AND options.qty <= options.notify_qty OR options.qty = options.out_of_stock)
	)
)';
$command=$connection->createCommand($sql);					
		
$row_option = $command->queryRow(true);
$total_options_low_inventory = $row_option['total'];	
?>
<div style="width:100%; height:100%; overflow:auto;" id="revenues">	
<div style="padding:10px;">	
    <div class="row" style="width:350px; border:1px solid #CCC; padding:10px; float:left; margin-right:5px;">
        <div>
            <div style="float:left; margin-right:5px;">
                <div style="float:left; margin-right:5px;">
                <?php
                $options=array(
                    0 => 'This Week',
                    1 => 'This month',
                    2 => 'Last 3 months',
                    3 => 'Last 6 months',
                    4 => 'This year',
                    5 => 'Last 5 years',
                    6 => 'Last 10 years',
                    7 => 'Custom',
                );
                
                echo CHtml::dropDownList('revenue_option',0,$options,array('id'=>'select_revenue_option','style'=>'font-size:14px;'));
                ?>
                </div>                
                <div style="float:left;"><?php echo Html::help_hint($help_hint_path.'revenue-option'); ?></div>
                <div style="clear:both;"></div>
            </div>                
            <div style="float:left; display:none;" id="revenue_dates">
                <div style="float:left; margin-right:5px;">
                    <strong>Start Date</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'revenue-start-date'); ?><br />            
                    <?php echo CHtml::textField('revenue_start_date','',array('id'=>'revenue_start_date')); ?>
                </div>
                <div style="float:left;">
                    <strong>End Date</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'revenue-end-date'); ?><br />            
                    <?php echo CHtml::textField('revenue_end_date','',array('id'=>'revenue_end_date')); ?>
                </div>                
                <div style="clear:both;"></div>                        
            </div>
            <div style="clear:both;"></div>        
        </div>  
        
        <div class="row" id="revenue_data" style="min-height:50px;"></div>
	</div>        
    
    <div class="row" style="width:350px; border:1px solid #CCC; padding:10px; float:left; margin-right:5px;">
    	<table border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td valign="top"><strong style="font-size:18px;">Unsettled Orders</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'unsettled-orders'); ?></td>
            <td align="center" valign="top" style="font-size:18px; <?php if ($total_unsettled_orders > 25 && $total_unsettled_orders < 100) { echo 'color: #E839D7;'; } else if ($total_unsettled_orders > 100) { echo 'color: #F00;'; } ?>">
				<a href="<?php echo CController::createUrl('orders/index',array('unsettled'=>1)); ?>"><?php echo $total_unsettled_orders; ?></a>
			</td>
		</tr>
		<tr>
			<td valign="top"><strong style="font-size:18px;">Unread Comments</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'unread-comments'); ?></td>
            <td align="center" valign="top" style="font-size:18px;"><a href="javascript:void(0);" id="orders_unread_comments"><?php echo $total_unread_comments; ?></a></td>
		</tr>      
		<tr>
			<td valign="top"><strong style="font-size:18px;">Products Low In Inventory</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'products-low-in-inventory'); ?></td>
            <td align="center" valign="top" style="font-size:18px;"><a href="javascript:void(0);" id="products_low_inventory"><?php echo $total_products_low_inventory; ?></a></td>
		</tr>          
		<tr>
			<td valign="top"><strong style="font-size:18px;">Options Low In Inventory</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'options-low-in-inventory'); ?></td>
            <td align="center" valign="top" style="font-size:18px;"><a href="javascript:void(0);" id="options_low_inventory"><?php echo $total_options_low_inventory; ?></a></td>
		</tr>         
        </table>                    
    </div>
    
    <div style="clear:both;"></div>        
    
    <div class="row" style="width:350px; border:1px solid #CCC; padding:10px; float:left; margin-right:5px; margin-top:5px;">
    	<table border="0" cellpadding="5" cellspacing="0" width="100%">
		<tr>
			<td valign="top"><strong style="font-size:18px;">Top Buyer</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'top-buyer'); ?>
            <div style="margin-top:5px; font-size:14px;">
            <?php
				// get top buyer
				$sql = 'SELECT 
				customer.id,
				customer.firstname,
				customer.lastname,
				customer.email,
				SUM(orders.grand_total) AS total,
				MAX(orders.id) AS id_orders,
				MAX(orders.date_order) AS last_transaction_date
				FROM
				customer
				INNER JOIN
				orders
				ON
				(customer.id = orders.id_customer)
				WHERE
				orders.status NOT IN (-1, 0, 4)
				GROUP BY 
				customer.id
				ORDER BY 
				total DESC		
				LIMIT 1						
				';
				$command=$connection->createCommand($sql);					
						
				if ($row_top_buyer = $command->queryRow(true)) {
			?>
            <div style="margin-bottom:5px;"><?php echo $row_top_buyer['firstname'].' '.$row_top_buyer['lastname']; ?><br /><?php echo $row_top_buyer['email']; ?><br />Total of <?php echo Html::nf($row_top_buyer['total']); ?><br />Last transaction: #<?php echo $row_top_buyer['id_orders']; ?><br />Last transaction date: <?php echo $row_top_buyer['last_transaction_date']; ?></div>
            <a href="#" id="view_more_top_buyers"><strong>View more</strong></a>
            <?php 
				} else { 
					echo '<p>No Sales.</p>';
				}
			?>
            </div>
            </td>
		</tr>
        <tr>            
			<td valign="top"><strong style="font-size:18px;">Best Selling Product</strong>&nbsp;&nbsp;<?php echo Html::help_hint($help_hint_path.'best-selling-product'); ?>
            <div style="margin-top:5px; font-size:14px;">
            <?php
				// get top selling product
				$sql = 'SELECT 
				product.id,
				product_description.name,
				SUM(orders_item_product.qty) AS qty,
				MAX(orders.date_order) AS last_transaction_date,
				product.has_variants
				FROM
				product
				INNER JOIN
				product_description
				ON
				(product.id = product_description.id_product AND product_description.language_code = :language_code)
				
				INNER JOIN
				orders_item_product
				ON
				(product.id = orders_item_product.id_product)
				
				INNER JOIN
				(orders_item CROSS JOIN orders)
				ON
				(orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)

				
				WHERE
				orders.status NOT IN (-1, 0, 4)
				GROUP BY 
				product.id
				ORDER BY 
				qty DESC								
				LIMIT 1
				';
				$command=$connection->createCommand($sql);			
				
				// get total amount
				$sql = 'SELECT (IFNULL((SELECT 
				SUM(orders_item_product.subtotal+orders_item_product.taxes)
				FROM
				orders_item_product
				
				INNER JOIN
				(orders_item CROSS JOIN orders)
				ON
				(orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
				
				WHERE
				orders_item_product.id_product = :id_product
				AND				
				orders.status NOT IN (-1, 0, 4)),0)-IFNULL((SELECT
				SUM(orders_discount_item_product.amount) 				
				FROM
				orders_discount_item_product
				INNER JOIN
				(orders_item_product CROSS JOIN orders_item CROSS JOIN orders)
				ON
				(orders_discount_item_product.id_orders_item_product = orders_item_product.id AND orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
				WHERE
				orders_item_product.id_product = :id_product
				AND				
				orders.status NOT IN (-1, 0, 4)),0)) AS total				
				';
				$command_total=$connection->createCommand($sql);	
				
				$sql = 'SELECT 
				orders_item_product.id_product_variant,
				SUM(orders_item_product.qty) AS qty
				FROM 
                orders_item_product
                INNER JOIN
                (orders_item CROSS JOIN orders)
                ON
                (orders_item_product.id_orders_item = orders_item.id AND orders_item.id_orders = orders.id)
                
				WHERE 
                orders.status NOT IN (-1, 0, 4)
                AND
				orders_item_product.id_product = :id_product
                GROUP BY
                orders_item_product.id_product_variant
				ORDER BY 
				qty DESC,
				orders.date_order DESC
				LIMIT 1';
				$command_variant_id=$connection->createCommand($sql);					
				
				$sql = 'SELECT 
				GROUP_CONCAT(CONCAT(product_variant_group_description.name,": ",product_variant_group_option_description.name) ORDER BY product_variant_group.sort_order ASC,product_variant_group_option.sort_order ASC SEPARATOR ", ") AS name
				FROM 
				product_variant 								
				INNER JOIN 
				(product_variant_option 
				CROSS JOIN product_variant_group 
				CROSS JOIN product_variant_group_option 
				CROSS JOIN product_variant_group_option_description
				CROSS JOIN product_variant_group_description)						
				ON
				(product_variant.id = product_variant_option.id_product_variant 
				AND product_variant_option.id_product_variant_group = product_variant_group.id 
				AND product_variant_option.id_product_variant_group_option = product_variant_group_option.id 
				AND product_variant_group_option.id = product_variant_group_option_description.id_product_variant_group_option
				AND product_variant_group_option_description.language_code = :language_code
				AND product_variant_group_option.id_product_variant_group = product_variant_group_description.id_product_variant_group
				AND product_variant_group_description.language_code = product_variant_group_option_description.language_code)
				
				
				WHERE 
				product_variant.id = :id_product_variant
				GROUP BY 
				product_variant.id';
				$command_variant=$connection->createCommand($sql);					
						
				if ($row_top_selling_product = $command->queryRow(true,array(':language_code'=>Yii::app()->language))) {					
					$row_top_selling_product_total = $command_total->queryRow(true,array(':id_product'=>$row_top_selling_product['id']));												
					// if product has a variant get best selling variant
					if ($row_top_selling_product['has_variants']) {
						if ($row_top_selling_product_variant_id = $command_variant_id->queryRow(true,array(':id_product'=>$row_top_selling_product['id']))) {
							$row_top_selling_product_variant = $command_variant->queryRow(true,array(':id_product_variant'=>$row_top_selling_product_variant_id['id_product_variant'],':language_code'=>Yii::app()->language));													
						}
					}
					
			?>                        
            <div style="margin-top:5px; margin-bottom:5px;">
            	<?php echo $row_top_selling_product['name']; ?><br />
            	<?php echo $row_top_selling_product['qty']; ?> units sold for a total of <?php echo Html::nf($row_top_selling_product_total['total']); ?>
                <?php if ($row_top_selling_product['has_variants'] && $row_top_selling_product_variant) {
					echo '<ul><li>Top selling variant: '.$row_top_selling_product_variant['name'].'</li></ul>';
				}?>
            </div>                
            <a href="#" id="view_more_top_selling_products"><strong>View more</strong></a>
            <?php 
				} else {
					echo '<p>No Sales.</p>';
				}
			?>
            </div></td>
		</tr>      
        </table>                    
    </div>  
   
    <div style="clear:both;"></div>            
    
<form id="add_comment" style="display:none; padding:10px;">
	<div class="row">    
    	<strong>Add Reply</strong>
        <div>
        <?php echo CHtml::textArea('comments','',array('id'=>'comments','style'=>'width:100%;')); ?>
        <br /><span id="comments_errorMsg" class="error"></span>
        </div>
    </div>
    <div id="list_comments" style="border-top:1px solid #ccc; overflow:auto;"></div>
</form>    

<div id="top_buyers_container" style="display:none; padding:10px;">
	<div class="row">
        <div style="float:left; margin-right:5px;">
        <?php
        $options=array(
            5 => 'Top 5',
            10 => 'Top 10',
            50 => 'Top 50',
            100 => 'Top 100',
        );
        
        echo CHtml::dropDownList('top_buyers',0,$options,array('id'=>'select_top_buyers_option','style'=>'font-size:14px;'));
        ?>
        </div>                
        <div style="float:left;"><?php echo Html::help_hint($help_hint_path.'top-buyers-option'); ?></div>
        <div style="clear:both;"></div>
    </div>
    <div id="top_buyers_grid" style="width:100%; height:300px;"></div>
</div>

<div id="top_selling_products_container" style="display:none; padding:10px;">
	<div class="row">
        <div style="float:left; margin-right:5px;">
        <?php
        $options=array(
            5 => 'Top 5',
            10 => 'Top 10',
            50 => 'Top 50',
            100 => 'Top 100',
        );
        
        echo CHtml::dropDownList('top_buyers',0,$options,array('id'=>'select_top_selling_products_option','style'=>'font-size:14px;'));
        ?>
        </div>                
        <div style="float:left;"><?php echo Html::help_hint($help_hint_path.'top-selling-products-option'); ?></div>
        <div style="clear:both;"></div>
    </div>
    <div id="top_selling_products_grid" style="width:100%; height:300px;"></div>
</div>

<div id="sales_container" style="display:none;"></div>
</div>
</div>