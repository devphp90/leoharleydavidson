<?php
// register css/script files and scripts
Html::include_timepicker();

// Client Script
$cs=Yii::app()->clientScript;  

$cs->registerScript('dhtmlx','
//Set the title of the current page
$("#page_title").html("").append("'.Yii::t('views/giftcertificates/index','LABEL_TITLE').'");
$("#how-to-link a").prop("title","/index.html?gestion_des_certificats_cadeau.html");

// add tabs to our main content 
var tabs = new Object();
tabs.totalCount=0;
tabs.totalOpened=[];

tabs.obj = templateLayout_B.attachTabbar();
tabs.obj.setImagePath(dhx_globalImgPath);
tabs.obj.addTab("list","'.Yii::t('global','LABEL_TAB_LIST').'","70px");
tabs.obj.addTab("new","<img src=\'"+dhx_globalImgPath+"toolbar/new.gif\' />","30px");
tabs.obj.enableTabCloseButton(true);
tabs.obj.setTabActive("list");

tabs.load_tab = function(tabObj, id, name){	
	id=id?id:"";
	
	if (tabs.totalCount >= 5) { alert("'.Yii::t('global','LABEL_ALERT_TAB_LIMIT_OPENED').'"); return false; }
	
	// tab id 
	// new 
	if (!id) { 	
		var tab_id = "new"+new Date().getTime();
		var name = "'.Yii::t('global','LABEL_NEW').'";
	// edit
	} else {
		var tab_id = "edit"+id;
		
		// check if tab is opened by id		
		if (tabs.totalOpened[id]) {
			// set that tab active
			tabObj.setTabActive(tabs.totalOpened[id]); 
			return false;
		} else {
			// store id and container into array of opened tabs
			tabs.totalOpened[id] = tab_id;	
		}
	}
	
	tabObj.addTab(tab_id,name,"*");	
	tabObj.setHrefMode("ajax-html");	
	tabObj.setContentHref(tab_id,"'.CController::createUrl('edit').'?container="+tab_id+"&id="+id);
	tabObj.setTabActive(tab_id);
	
	tabs.totalCount++;
}

tabs.close_tab = function(tabObj, id, select_list) {
	if (typeof(window["Tab"+id].has_modifications) == "function" && window["Tab"+id].has_modifications() && !confirm("'.Yii::t('global','LABEL_CONFIRM_CONTINUE_WITHOUT_SAVING').'")) return false;	
	
	// remove tab
	tabObj.removeTab(id,false);
	tabs.totalCount--;		
	
	// unset variable object 
	window["Tab"+id] = null;
	
	// when closing a tab always go back to the list
	if (select_list) {
		tabObj.setTabActive("list");	
	}
	
	// loop through array of opened tabs and remove it from the list.
	for (var i=0; i<tabs.totalOpened.length; i++){
		if (tabs.totalOpened[i] == id) {
			tabs.totalOpened.splice(i,1);
		}
	}
}

tabs.obj.attachEvent("onTabClose", function(id){
	tabs.close_tab(this, id, true);
	
	//any custom code	
	return false;
// when selecting a tab	
});

tabs.obj.attachEvent("onSelect",function(id,last_id){
	switch (id) {
		// when clicking on new
		case "new":			
			tabs.load_tab(this);
			return false;
			break;	
	}
	
	return true;
});	

// add a context menu that we will use for our tabs
tabs.contextmenu = new Object();
tabs.contextmenu.obj = new dhtmlXMenuObject();
tabs.contextmenu.obj.setIconsPath(dhx_globalImgPath);
tabs.contextmenu.obj.renderAsContextMenu();

// bind contextmenu event to tabs (right click)
$(tabs.obj._tabZone).bind("contextmenu",function(e) {	
	// get current tab id
	var tab_id = $(e.target).parents("div[tab_id]").attr("tab_id");
	
	// activate when we right click on new tabs or an existing editing tab
	if (tab_id && tab_id != "list" && tab_id != "new") {			
		tabs.contextmenu.obj.clearAll();
		tabs.contextmenu.obj.detachAllEvents();
		tabs.contextmenu.obj.addNewChild(tabs.contextmenu.obj.topId, 0, "close", "'.Yii::t('global','LABEL_CONTEXT_MENU_CLOSE').'", false, null);
		tabs.contextmenu.obj.addNewChild(tabs.contextmenu.obj.topId, 1, "close_other", "'.Yii::t('global','LABEL_CONTEXT_MENU_CLOSE_OTHER').'", false, null);
		tabs.contextmenu.obj.addNewChild(tabs.contextmenu.obj.topId, 2, "close_all", "'.Yii::t('global','LABEL_CONTEXT_MENU_CLOSE_ALL').'", false, null);		
		tabs.contextmenu.obj.showContextMenu(e.pageX, e.pageY);
		
		tabs.contextmenu.obj.attachEvent("onClick", function(id, zoneId, casState){
        	switch (id) {
				case "close":
					tabs.close_tab(tabs.obj, tab_id, true);
					break;
				case "close_other":
					$.each(tabs.obj._tabs, function(key, value) {
						if (key != "list" && key != "new" && key != tab_id) { 
							tabs.close_tab(tabs.obj, key, false);
						} 
					});
					
					if (tabs.obj.getActiveTab() != tab_id) { 
						tabs.obj.setTabActive(tab_id);
					}
					break;
				case "close_all":
					$.each(tabs.obj._tabs, function(key, value) {
						if (key != "list" && key != "new") {
							tabs.close_tab(tabs.obj, key, true);
						}
					});						
					break;	
			}
        });
	}
	
	return false;
});

// first tab called "list", we add a toolbar
tabs.list = new Object();
tabs.list.obj = tabs.obj.cells("list");

tabs.list.toolbar = new Object();
tabs.list.toolbar.obj = tabs.list.obj.attachToolbar();
tabs.list.toolbar.obj.setIconsPath(dhx_globalImgPath);	
tabs.list.toolbar.obj.addButton("delete", null, "'.Yii::t('global','LABEL_BTN_DELETE').'", "toolbar/delete.png", "toolbar/delete-dis.png");
tabs.list.toolbar.obj.addSeparator("sep1", null);
tabs.list.toolbar.obj.addButton("export_pdf", null, "'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'", "toolbar/pdf.png", null);
tabs.list.toolbar.obj.addButton("export_excel", null, "'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'", "toolbar/excel.png", null);
tabs.list.toolbar.obj.addButton("print", null, "'.Yii::t('global','LABEL_BTN_PRINT').'", "toolbar/print.png", null);

tabs.list.toolbar.obj.attachEvent("onClick", function(id){
	var obj = this;
	
	switch (id) {
		case "delete":			
			var checked = tabs.list.grid.obj.getCheckedRows(0);
			
			if (checked) {
				if (confirm("'.Yii::t('views/giftcertificates/index','LABEL_ALERT_DELETE').'")) {
					checked = checked.split(",");
					var ids=[];
					var opened=0;
					
					for (var i=0;i<checked.length;++i) {
						if (checked[i]) {
							if (!tabs.totalOpened[checked[i]]) {
								ids.push("ids[]="+checked[i]);									
							} else {
								opened++;	
							}
						}
					}
					
					if (!opened) {					
						$.ajax({
							url: "'.CController::createUrl('delete').'",
							type: "POST",
							data: ids.join("&"),
							beforeSend: function(){		
								obj.disableItem(id);
							},
							complete: function(){
								obj.enableItem(id);	
							},								
							success: function(data){			
								load_grid(tabs.list.grid.obj);								
								alert("'.Yii::t('global','LABEL_ALERT_DELETE_SUCCESS').'");
							}
						});						
					} else {
						alert("'.Yii::t('views/giftcertificates/index','LABEL_ALERT_DELETE_CLOSE').'");	
					}
				}
			} else {
				alert("'.Yii::t('views/giftcertificates/index','LABEL_ALERT_NO_CHECKED').'");	
			}
			break;
		case "export_pdf":
			tabs.list.grid.obj.setColumnsVisibility("true,false,false,false");
			tabs.list.grid.obj.toPDF(export_pdf_url(),"gray");
			tabs.list.grid.obj.setColumnsVisibility("false,false,false,false");
			break;	
		case "export_excel":
			tabs.list.grid.obj.setColumnsVisibility("true,false,false,false");
			tabs.list.grid.obj.toExcel(export_excel_url(),"gray");
			tabs.list.grid.obj.setColumnsVisibility("false,false,false,false");
			break;		
		case "print":
			tabs.list.grid.obj.setColumnsVisibility("true,false,false,false");
			tabs.list.grid.obj.printView();
			tabs.list.grid.obj.setColumnsVisibility("false,false,false,false");
			break;		
	}
});

// first tab called "list", we add a grid
tabs.list.grid = new Object();
tabs.list.grid.obj = tabs.list.obj.attachGrid();
tabs.list.grid.obj.setImagePath(dhx_globalImgPath);	
tabs.list.grid.obj.setHeader("#master_checkbox,'.Yii::t('global','LABEL_CODE').','.Yii::t('global','LABEL_PRICE').','.Yii::t('views/giftcertificates/index','LABEL_LIST_GRID_CUSTOMER').','.Yii::t('views/giftcertificates/index','LABEL_LIST_GRID_SHIPPING_METHOD').','.Yii::t('views/giftcertificates/index','LABEL_LIST_GRID_SENT').','.Yii::t('views/giftcertificates/index','LABEL_LIST_GRID_SENT_DATE').','.Yii::t('global','LABEL_ENABLED').',",null,["text-align:center",,"text-align:right",,,,,"text-align:center",,"text-align:center"]);
tabs.list.grid.obj.attachHeader("&nbsp;,#text_filter_custom,#text_filter_custom,#text_filter_custom,#select_filter_custom_shipping,#select_filter_custom,#datetime_filter_custom,#select_filter_custom");

// custom text filter input
tabs.list.grid.obj._in_header_text_filter_custom=text_filter_custom;

// custom select filter input
tabs.list.grid.obj._in_header_select_filter_custom=select_filter_custom_yesno;

// custom text filter input
tabs.list.grid.obj._in_header_datetime_filter_custom=datetime_filter_custom;

tabs.list.grid.obj._in_header_select_filter_custom_shipping=function(tag,index,data){
	var obj = this;
	
	tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"0\">'.Yii::t('views/giftcertificates/edit','LABEL_SHIPPING_METHOD_0').'</option><option value=\"1\">'.Yii::t('views/giftcertificates/edit','LABEL_SHIPPING_METHOD_1').'</option></select>";	
	tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
	tag.firstChild.onchange=function(){
		load_grid(obj);
	};		
}

/*tabs.list.grid.obj._in_header_select_filter_custom_language=function(tag,index,data){
	var obj = this;
	
	tag.innerHTML="'.addslashes(str_replace("\n","",str_replace("\r","",Html::generateLanguageList("language_code","empty",array("style"=>"width:90%;font-size:8pt;font-family:Tahoma;",'prompt'=>"", "class"=>"hdr_custom_filters"))))).'";	
	tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
	tag.firstChild.onchange=function(){
		load_grid(obj);
	};		
}*/

tabs.list.grid.obj.setInitWidthsP("4,12,10,30,10,10,15,9");
tabs.list.grid.obj.setColAlign("center,left,right,left,left,center,left,center");
tabs.list.grid.obj.setColSorting("na,server,server,server,na,na,server,na");
tabs.list.grid.obj.enableResizing("false,true,true,true,true,false,true,false");
tabs.list.grid.obj.setSkin(dhx_skin);
tabs.list.grid.obj.enableRowsHover(true,dhx_rowhover_pointer);
tabs.list.grid.obj.setColumnHidden(8,true);

//Paging
tabs.list.obj.attachStatusBar().setText("<div id=\'recinfoArea\'></div>");
tabs.list.grid.obj.setPagingWTMode(true,true,true,[25,50,100,200]);
tabs.list.grid.obj.enablePaging(true, 100, 3, "recinfoArea");
tabs.list.grid.obj.setPagingSkin("toolbar", dhx_skin);
tabs.list.grid.obj.i18n.paging={
      results:"'.Yii::t('global','LABEL_GRID_PAGING_RESULTS').'",
      records:"'.Yii::t('global','LABEL_GRID_PAGING_RECORDS_FROM').'",
      to:"'.Yii::t('global','LABEL_GRID_PAGING_TO').'",
      page:"'.Yii::t('global','LABEL_GRID_PAGING_PAGE').'",
      perpage:"'.Yii::t('global','LABEL_GRID_PAGING_ROWS_PER_PAGE').'",
      first:"'.Yii::t('global','LABEL_GRID_PAGING_TO_FIRST_PAGE').'",
      previous:"'.Yii::t('global','LABEL_GRID_PAGING_PREVIOUS_PAGE').'",
      found:"'.Yii::t('global','LABEL_GRID_PAGING_FOUND_RECORDS').'",
      next:"'.Yii::t('global','LABEL_GRID_PAGING_NEXT_PAGE').'",
      last:"'.Yii::t('global','LABEL_GRID_PAGING_TO_LAST_PAGE').'",
      of:"'.Yii::t('global','LABEL_GRID_PAGING_OF').'",
      notfound:"'.Yii::t('global','LABEL_GRID_PAGING_NO_RECORDS_FOUND').'" }

tabs.list.grid.obj.init();

// set filter input names
tabs.list.grid.obj.hdr.rows[2].cells[1].getElementsByTagName("INPUT")[0].name="code";
tabs.list.grid.obj.hdr.rows[2].cells[2].getElementsByTagName("INPUT")[0].name="price";
tabs.list.grid.obj.hdr.rows[2].cells[3].getElementsByTagName("INPUT")[0].name="customer_name";
tabs.list.grid.obj.hdr.rows[2].cells[4].getElementsByTagName("SELECT")[0].name="shipping_method";
tabs.list.grid.obj.hdr.rows[2].cells[5].getElementsByTagName("SELECT")[0].name="sent";
tabs.list.grid.obj.hdr.rows[2].cells[6].getElementsByTagName("INPUT")[0].name="sent_date_start";
tabs.list.grid.obj.hdr.rows[2].cells[6].getElementsByTagName("INPUT")[1].name="sent_date_end";
tabs.list.grid.obj.hdr.rows[2].cells[7].getElementsByTagName("SELECT")[0].name="active";

tabs.list.grid.obj.enableSmartRendering(true);
// we create a variable to store the default url used to get our grid data, so we can reuse it later
tabs.list.grid.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list').'";
// load the initial grid
load_grid(tabs.list.grid.obj);

tabs.list.grid.obj.attachEvent("onRowDblClicked",function(rId,cInd){
	tabs.load_tab(tabs.obj,rId,this.cellById(rId,1).getValue());
});

tabs.list.grid.obj.attachEvent("onBeforeSorting",function(ind,type,dir){
	load_grid(this, ind, dir);
	
	return false;
});

tabs.list.grid.obj.attachEvent("onCheck", function(rId,cInd,state){
	if (cInd == 5) {
		var send_email=0;
		//Verify if shipping method is email
		if(tabs.list.grid.obj.cellById(rId,8).getValue()==0 && tabs.list.grid.obj.cellById(rId,5).getValue()==1){
			if(confirm("'.Yii::t('views/giftcertificates/index','LABEL_ALERT_SENT_BY_EMAIL').'")){
				send_email=1;
			}
		}
		$.ajax({
			url: "'.CController::createUrl('toggle_sent').'",
			type: "POST",
			data: { "id":rId,"sent":state,"send_email":send_email },
			success: function(data){	
				load_grid(tabs.list.grid.obj);
			}
		});		
	}else if (cInd == 7) {
		$.ajax({
			url: "'.CController::createUrl('toggle_active').'",
			type: "POST",
			data: { "id":rId,"active":state }
		});	
		if(state){
			tabs.list.grid.obj.setRowTextStyle(rId, "color: #000000");	
		}else{
			tabs.list.grid.obj.setRowTextStyle(rId, "color: #B5B5B5;");		
		}		
	}
});
',CClientScript::POS_END);
?>