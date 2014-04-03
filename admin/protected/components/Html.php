<?php
class Html extends CHtml
{
    /**
    * Makes the given URL relative to the /images directory
    */
    public static function imageUrl($url) {
        return Yii::app()->getBaseUrl().'/images/'.$url;
    }
    /**
    * Makes the given URL relative to the /css directory
    */
    public static function cssUrl($url) {
        return Yii::app()->getBaseUrl().'/css/'.$url;
    }
    /**
    * Makes the given URL relative to the /js directory
    */
    public static function jsUrl($url) {
        return Yii::app()->getBaseUrl().'/../includes/js/'.$url;
    }
	
    /**
    * Makes the given URL relative to the current theme /images directory
    */
    public static function themeImageUrl($url) {
        return Yii::app()->theme->getBaseUrl().'/images/'.$url;
    }
    /**
    * Makes the given URL relative to the /css directory
    */
    public static function themeCssUrl($url) {
        return Yii::app()->theme->getBaseUrl().'/css/'.$url;
    }
	
	/**
	 * Links to the specified CSS files.
	 * @param an array containing url and media for each file to be include
	 * @return string the CSS links.
	 */
	public static function cssFileArray($urls=array())
	{
		$output = '';
		
		if (sizeof($urls)) {
			foreach ($urls as $url) {
				$output .= self::cssFile($url[0],$url[1])."\r\n";
			}
		}
		
		return $output;
	}	
	
	public static function script($text)
	{
		return "<script type=\"text/javascript\">\n{$text}\n</script>";
	}
	
	/**
	 * Includes a JavaScript files.
	 * @param an array containing url for each file to be include
	 * @return string the JavaScript file tag
	 */
	public static function scriptFileArray($urls=array())
	{
		$output = '';
		
		if (sizeof($urls)) {
			foreach ($urls as $url) {
				$output .= self::scriptFile($url)."\r\n";
			}
		}
		
		return $output;
	}	
	
	/**
	 * Include functions
	 */
	 
	public function include_timepicker()
	{
		$include_path = Yii::app()->params['includes_js_path'];	
		
		$cs=Yii::app()->clientScript; 		
		$cs->registerScriptFile($include_path.'jquery/timepicker/jquery-ui-timepicker-addon.js', CClientScript::POS_HEAD);
		if(Yii::app()->language != 'en'){
		$cs->registerScriptFile($include_path.'jquery/i18n/jquery.ui.datepicker-'.Yii::app()->language.'.js', CClientScript::POS_HEAD);
		}
		$cs->registerCssFile($include_path.'jquery/timepicker/styles.css');		
	}

	public function include_uploadify()
	{
		$include_path = Yii::app()->params['includes_js_path'];	
		
		$cs=Yii::app()->clientScript; 			
		$cs->registerCssFile($include_path.'jquery/uploadify-v3.1.1/uploadify.css');
		$cs->registerScriptFile($include_path.'jquery/uploadify-v3.1.1/jquery.uploadify-3.1.min.js', CClientScript::POS_HEAD);	
	}
	
	public function include_jcrop()
	{
		$include_path = Yii::app()->params['includes_js_path'];	
		
		$cs=Yii::app()->clientScript; 		
		$cs->registerCssFile($include_path.'jquery/jcrop/css/jquery.Jcrop.css');
		$cs->registerScriptFile($include_path.'jquery/jcrop/jquery.Jcrop.js', CClientScript::POS_HEAD);		
	}
	
	public function include_dhtmlx()
	{		
		$dhtmlx_path = Yii::app()->params['dhtmlx_path'];
		
		$cs=Yii::app()->clientScript; 		
//		$cs->registerCssFile($include_path.'dhtmlx_3.5/dhtmlx.css');
//		$cs->registerScriptFile($include_path.'dhtmlx_3.5/dhtmlx.js', CClientScript::POS_HEAD);		
		$cs->registerCssFile($dhtmlx_path.'imgs/toolbar/css/main.css');
		$cs->registerCssFile($dhtmlx_path.'dhtmlx.css');
		$cs->registerScriptFile($dhtmlx_path.'dhtmlx.js', CClientScript::POS_HEAD);		
		$cs->registerScriptFile($dhtmlx_path.'export/dhtmlxgrid_export.js', CClientScript::POS_HEAD);		
	}
	
	public function include_ckeditor()
	{
		
		
		$include_path = Yii::app()->params['includes_js_path'];	
		
		$cs=Yii::app()->clientScript; 		
		$cs->registerScriptFile($include_path.'ckeditor_4.3.1/ckeditor.js', CClientScript::POS_HEAD);
        $cs->registerScriptFile($include_path.'ckeditor_4.3.1/adapters/jquery.js', CClientScript::POS_HEAD);
        $cs->registerScriptFile($include_path.'ckeditor_4.3.1/ckfinder/ckfinder_v1.js', CClientScript::POS_HEAD);
		
		$cs->registerScript('ckeditor_instances','
		function load_ckeditor(container){
			var d=new Date();
			$("#"+container+" .editor").each(function(){
				var id = $(this).attr("id");
				
				//Verify if instance of CKEditor already exist and destroy it if exist
				try { 
					CKEDITOR.instances[id].destroy(true);
				} catch(e) { 
					
				}	
											
				$(this).ckeditor(function(){
						//Attach CKFinder to CKEditor
						CKFinder.config = "'.$include_path.'ckeditor_4.3.1/ckfinder/config.js";
						CKFinder.SetupCKEditor( this,"'.$include_path.'ckeditor_4.3.1/ckfinder/" );
					},
					{
						extraPlugins : "oembed,stylesheetparser",
						toolbar : [
							["Source"],
							["Cut","Copy","Paste","PasteText","PasteFromWord"],
							["JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"],
							["Undo","Redo","-","RemoveFormat"],
							["Image","oembed","Table","HorizontalRule"],
							["Format"],
							["FontSize"],
							["Bold","Italic","Strike"],
							["NumberedList","BulletedList","-","Outdent","Indent"],
							["Link","Unlink","Anchor"],
							["Maximize","-"]
						],
						uiColor : "#cce2fe",
						language : "'.Yii::app()->language.'",
						contentsCss : "'.Yii::app()->params['root_relative_url'].'_css/ckeditor.css",
						stylesSet : []												
					}
				);
			});	
		}
		', CClientScript::POS_HEAD);		
		
	}	
	
	public function include_jwplayer()
	{
		$include_path = Yii::app()->params['includes_js_path'];	
		
		$cs=Yii::app()->clientScript; 			
		$cs->registerScriptFile($include_path.'mediaplayer/jwplayer.js', CClientScript::POS_HEAD);	
	}
	
	public function include_googlemaps_api()
	{
		$cs=Yii::app()->clientScript; 			
		$cs->registerScriptFile('//maps.googleapis.com/maps/api/js?sensor=false', CClientScript::POS_HEAD);	
		
		$cs->registerScript('googlemaps_api','
		// search using address and return lat, lng
		function geocode(address, lat_id, lng_id) {
			var geocoder = new google.maps.Geocoder();			

		 	geocoder.geocode({address: address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {										
					$("#"+lat_id).val(results[0].geometry.location.lat());
					$("#"+lng_id).val(results[0].geometry.location.lng());
				} else alert("'.Yii::t('views/config/edit_store_locations_options','ERROR_GEOCODE_ADDRESS').'");		
			});					
		}
		', CClientScript::POS_HEAD);	
	}	
	
	public function df_date_obj($format_date, $format_time, $code_language, $country_code)
	{
		global $config_site;
		/*
		-1=IntlDateFormatter::NONE
		0=IntlDateFormatter::FULL
		1=IntlDateFormatter::LONG
		2=IntlDateFormatter::MEDIUM
		3=IntlDateFormatter::SHORT
		*/
		return new IntlDateFormatter( $code_language.'_'.$country_code ,$format_date, $format_time,'',IntlDateFormatter::GREGORIAN  );
	
	}
	
	public function df_date($date, $format_date=1, $format_time=3, $code_language="fr", $country_code="CA")
	{
		$df = Html::df_date_obj($format_date, $format_time, $code_language, $country_code);
		return $df->format(strtotime(date($date)));
	
	}
	
	
	public function include_dhtmlx_custom_filters()
	{		
		
		
		
		$cs=Yii::app()->clientScript; 		
		$cs->registerScript('dhtmlx_custom_filters','
		function text_filter_custom(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<input style=\"width:90%;font-size:8pt;font-family:Tahoma;-moz-user-select:text\" class=\"hdr_custom_filters\" />";	
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onkeyup=function(){
				load_grid(obj);
			};
		}		
		
		function date_filter_custom(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<input style=\"width:90%;font-size:8pt;font-family:Tahoma;-moz-user-select:text;margin-bottom:2px;\" class=\"hdr_custom_filters\" datepicker=\"datepicker\" /><br /><input style=\"width:90%;font-size:8pt;font-family:Tahoma;-moz-user-select:text\" class=\"hdr_custom_filters\" datepicker=\"datepicker\" />";	
			
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onkeyup=function(){
				load_grid(obj);
			};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};			
			
			tag.lastChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.lastChild.onkeyup=function(){
				load_grid(obj);
			};
			tag.lastChild.onchange=function(){
				load_grid(obj);
			};					
		}			
		
		function datetime_filter_custom(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<input style=\"width:90%;font-size:8pt;font-family:Tahoma;-moz-user-select:text;margin-bottom:2px;\" class=\"hdr_custom_filters\" datetimepicker=\"datetimepicker\" /><br /><input style=\"width:90%;font-size:8pt;font-family:Tahoma;-moz-user-select:text\" class=\"hdr_custom_filters\" datetimepicker=\"datetimepicker\" />";	
			
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onkeyup=function(){
				load_grid(obj);
			};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};			
			
			tag.lastChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.lastChild.onkeyup=function(){
				load_grid(obj);
			};
			tag.lastChild.onchange=function(){
				load_grid(obj);
			};					
		}				
		
		function select_filter_custom_yesno(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"1\">'.Yii::t('global','LABEL_YES').'</option><option value=\"0\">'.Yii::t('global','LABEL_NO').'</option></select>";	
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};
		}
		
		function select_filter_custom_enableddisabled(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"1\">'.Yii::t('global','LABEL_ENABLED').'</option><option value=\"0\">'.Yii::t('global','LABEL_DISABLED').'</option></select>";	
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};
		}
		
		function select_filter_custom_featured(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"1\">'.Yii::t('views/products/edit_info_options','LABEL_FEATURED').'</option><option value=\"0\">'.Yii::t('views/products/edit_info_options','LABEL_NOT_FEATURED').'</option></select>";	
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};
		}
		
		function select_filter_custom_contest(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"1\">'.Yii::t('global','LABEL_CONTEST').'</option><option value=\"0\">'.Yii::t('global','LABEL_SUBSCRIPTIONS').'</option></select>";	
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};
		}
		
		function select_filter_custom_coupon(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"1\">'.Yii::t('global','LABEL_COUPON').'</option><option value=\"0\">'.Yii::t('global','LABEL_REBATE').'</option></select>";	
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};
		}
		
		function select_filter_custom_coupon_type(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"0\">'.Yii::t('controllers/RebatecouponController','LABEL_REBATE_0').'</option><option value=\"1\">'.Yii::t('controllers/RebatecouponController','LABEL_REBATE_1').'</option><option value=\"2\">'.Yii::t('controllers/RebatecouponController','LABEL_REBATE_2').'</option><option value=\"3\">'.Yii::t('controllers/RebatecouponController','LABEL_REBATE_3').'</option><option value=\"4\">'.Yii::t('controllers/RebatecouponController','LABEL_REBATE_4').'</option><option value=\"5\">'.Yii::t('controllers/RebatecouponController','LABEL_REBATE_5').'</option></select>";	
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};
		}
		
		function select_filter_custom_product_type(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="<select style=\"width:90%;font-size:8pt;font-family:Tahoma;\" class=\"hdr_custom_filters\"><option value=\"\"></option><option value=\"0\">'.Yii::t('global','LABEL_PRODUCT_TYPE_PRODUCT').'</option><option value=\"1\">'.Yii::t('global','LABEL_PRODUCT_TYPE_COMBO').'</option><option value=\"2\">'.Yii::t('global','LABEL_PRODUCT_TYPE_BUNDLED').'</option></select>";	
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};
		}
		function select_filter_custom_language(tag,index,data){   // the name contains "_in_header_"+shortcut_name
			var obj = this;
		
			tag.innerHTML="'.str_replace("\n", "",str_replace("\"","\\\"",CHtml::dropDownList("language", "", CHtml::listData(Tbl_Language::model()->active()->findAll(), "code", "name"), array("style"=>"width:90%;font-size:8pt;font-family:Tahoma;", "class"=>"hdr_custom_filters","empty" => "")))).'";	
			tag.firstChild.onclick=function(e){(e||event).cancelBubble=true;return false;};
			tag.firstChild.onchange=function(){
				load_grid(obj);
			};
		}		
		
		function load_grid(obj, sort_ind, sort_dir) {
			var params=[];
			
			$("#"+obj.entBox.id+" .hdr_custom_filters").each(function(key,obj){
				switch (obj.type) {
					case "checkbox":
						break;
					case "text":
						var value = obj.value;
						
						if (value.length) {
							params.push("filters["+obj.name+"]="+value);
						}
						break;
					case "select-one":
						var sel = obj;
						var value = sel.options[sel.selectedIndex].value;
						
						if (value.length) {
							params.push("filters["+obj.name+"]="+value);
						}
						break;
				}		
			});
			
			// if sort dir argument is not passed, look if grid has current sorting state	
			if (!sort_dir) {
				var sort_state = obj.getSortingState();
				
				// if we have a state, then grab information
				if (sort_state.length) {
					sort_ind = sort_state[0];
					sort_dir = sort_state[1];
				}
			}
			
			// if we have a sort dir, apply
			if (sort_dir) {
				params.push("sort_col["+sort_ind+"]="+sort_dir);
			}		
			
			// grab current xml url and apply filter and sort
			var xml_url = obj.xmlOrigFileUrl+(obj.xmlOrigFileUrl.indexOf("?") != -1 ? "&":"?")+params.join("&");	
			
			// reload grid
			obj.clearAndLoad(xml_url);
			
			// apply current sort dir / required
			if (sort_dir) {
				obj.setSortImgState(true,sort_ind,sort_dir);	
			}
		}			
		
		function export_pdf_url(){
			return "/includes/php/pdf/generate.php";
		}
		
		function export_excel_url(){
			return "/includes/php/excel/generate.php";
		}		
		
		eXcell_ch.prototype.getContent = function(){
			var value = this.getValue();
			
			if (value == 1) {
				value = "X";
			} else if (value == 0) {
				value = "";
			}
			
			return value;
		}
		
		eXcell_ra.prototype.getContent = function(){
			var value = this.getValue();
			
			if (value == 1) {
				value = "X";
			} else if (value == 0) {
				value = "";
			}
			
			return value;
		};				
		
		function printGridPopup(obj,method,omit,title,desc){
			if (!obj.getAllRowIds().length) { 
				alert("'.Yii::t('components/Html','LABEL_ALERT_NO_RECORD').'"); 
				return false;
			}
			
			title = title ? title:"";
			desc = desc ? desc:"";		
			
			var dhxWins = new dhtmlXWindows();
			dhxWins.enableAutoViewport(true);
			dhxWins.setImagePath(dhx_globalImgPath);	
			dhxWins.enableAutoViewport(true);
			
			var wins = new Object();	
			
			wins.obj = dhxWins.createWindow("printWindow", 0, 0, 650, 280);
			wins.obj.setText("'.Yii::t('components/Html','LABEL_TITLE_WINDOW').'");
			wins.obj.button("minmax1").hide();
			wins.obj.button("park").hide();
			wins.obj.keepInViewport(true);
			wins.obj.setModal(true);
			wins.obj.center();
			
			wins.toolbar = new Object();
			wins.toolbar.obj = wins.obj.attachToolbar();
			wins.toolbar.obj.setIconsPath(dhx_globalImgPath);	
			
			switch (method) {
				case "pdf":		
					wins.toolbar.obj.addButton("export",null,"'.Yii::t('global','LABEL_BTN_EXPORT_PDF').'","toolbar/pdf.png", null);
					break;
				case "excel":
					wins.toolbar.obj.addButton("export",null,"'.Yii::t('global','LABEL_BTN_EXPORT_EXCEL').'","toolbar/excel.png", null);
					break;
				case "printview":
					wins.toolbar.obj.addButton("export",null,"'.Yii::t('global','LABEL_BTN_PRINT').'","toolbar/print.png", null);	
					break;
			}			
			
			wins.toolbar.obj.attachEvent("onClick",function(id){
				switch (id) {
					case "export":
						if (wins.grid.obj.getCheckedRows(0).length) { 								
							if (omit && omit.length) {
								for (col_id in omit) {
									obj.setColumnHidden(omit[col_id],true);
								}
							}
							
							var rows = wins.grid.obj.getAllRowIds();
							
							if (rows) {
								title = $("#"+wins.layout.obj.cont.obj.id+" input[name=\'print_title\']").length ? $("#"+wins.layout.obj.cont.obj.id+" input[name=\'print_title\']").val():"";
								desc = $("#"+wins.layout.obj.cont.obj.id+" textarea[name=\'print_desc\']").length ? $("#"+wins.layout.obj.cont.obj.id+" textarea[name=\'print_desc\']").val():"";
								
								rows = rows.split(",");					
								
								for (var i=0;i<rows.length;++i) {
									var row_id = rows[i];
									var cell = wins.grid.obj.cellById(row_id,0);
								
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
									var cell = wins.grid.obj.cellById(row_id,0);
									
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
							
							wins.obj.close();		
						} else {
							alert("'.Yii::t('components/Html','LABEL_ALERT_EXPORT_PDF_EXCEL_PRINT').'");	
						}
						break;	
				}
			});				
			
			wins.layout = new Object();
			wins.layout.obj = wins.obj.attachLayout("2U");
			
			wins.layout.A = new Object();
			wins.layout.A.obj = wins.layout.obj.cells("a");
			wins.layout.A.obj.setWidth(350);
			wins.layout.A.obj.hideHeader();
			wins.layout.A.obj.attachHTMLString(\'<div style="width:100%; height:100%; overflow:auto;"><div style="padding:10px;"><div><strong>'.Yii::t('components/Html','LABEL_TITLE').'</strong>&nbsp;&nbsp;'.Html::help_hint('/export-list/title').'<br /><input type="text" name="print_title" value="\'+title+\'" style="width: 100%;" /></div><div><strong>'.Yii::t('global','LABEL_DESCRIPTION').'</strong>&nbsp;&nbsp;'.Html::help_hint('/export-list/description').'<br /><textarea name="print_desc" rows="4" style="width: 100%;">\'+desc+\'</textarea></div></div></div>\');		
			
			wins.layout.B = new Object();
			wins.layout.B.obj = wins.layout.obj.cells("b");
			wins.layout.B.obj.hideHeader();		
			
			wins.grid = new Object(); 
			wins.grid.obj = wins.layout.B.obj.attachGrid();
			wins.grid.obj.setImagePath(dhx_globalImgPath);
			wins.grid.obj.setHeader("#master_checkbox,'.Yii::t('components/Html','LABEL_COLUMN').'",null,["text-align:center"]);
			wins.grid.obj.setInitWidthsP("15,85");
			wins.grid.obj.setColAlign("center,left");
			wins.grid.obj.setColTypes("ch,ro");
			wins.grid.obj.setColSorting("na,na");
			wins.grid.obj.enableResizing("false,false");
			wins.grid.obj.enableRowsHover(true,dhx_rowhover);
			wins.grid.obj.init();
			
			var columnCount = obj.getColumnsNum();
			for (var i=0;i<columnCount;i++){
				if ((!omit || omit && omit.length && $.inArray(i, omit) == -1) && obj.isColumnHidden(i) == false) {
					var columnName = obj.getColumnLabel(i);		
					wins.grid.obj.addRow(i,[1,columnName]);			
				}
			}								
		}		
		
		//Function to enable and disable grid with toolbar who have a parent grid
		function enable_grid_toolbar(layout_grid, layout_toolbar, layout_grid_parent){
			var colNum = layout_grid.getColumnsNum();
			var selectedId = layout_grid_parent.getSelectedRowId();
			var state = "";
			
			if(selectedId){
				state = "false";
			}else{
				state = "true";
				
			}
			
			for(x=0;x<colNum;x++){
				layout_grid.setColumnHidden(x,state);
			}	
			
			layout_toolbar.forEachItem(function(itemId){
				if(selectedId){
					layout_toolbar.enableItem(itemId);
				}else{
					layout_toolbar.disableItem(itemId);
				}
			});
		}
		
		if ($.fn.datepicker) {			
			$(function(){			
				$("input[datepicker=\'datepicker\']").datepicker({ dateFormat: "yy-mm-dd", changeYear: true });
			});				
		}		
		
		if ($.fn.datetimepicker) {			
			$(function(){			
				$("input[datetimepicker=\'datetimepicker\']").datetimepicker({ dateFormat: "yy-mm-dd", changeYear: true });
			});				
		}
		
		function ajaxOverlay(id,i){
			var overlay_html = \'<div class="ajaxload" style="position:absolute; z-index:2; background-color: #eaeaea; opacity:0.7; filter:alpha(opacity=70); padding:10px;"><div class="ajaxload_block"><div style="float:left; margin-right:5px;">'.CHtml::image(Yii::app()->params['dhtmlx_path'].'imgs/toolbar/ajax-loader.gif',Yii::t('components/Html','LABEL_LOADING'),array('border'=>0)).'</div><div style="float:left;"><strong>'.Yii::t('components/Html','LABEL_LOADING').'</strong></div></div></div>\';
			
			switch (i) {
				case 0:
					$("#"+id+" .ajaxload").remove();
					break;
				case 1:
					$("#"+id).prepend(overlay_html);
					$("#"+id+" .ajaxload").css("width",$("#"+id).width()-20);
					$("#"+id+" .ajaxload").css("height",$("#"+id).height()+$("#"+id).scrollTop()-20);
					$("#"+id+" .ajaxload_block").css("position","absolute");
					$("#"+id+" .ajaxload_block").css("left",($("#"+id+" .ajaxload").width()-$("#"+id+" .ajaxload_block").outerWidth())/2);
					$("#"+id+" .ajaxload_block").css("top",($("#"+id+" .ajaxload").height()-$("#"+id+" .ajaxload_block").outerHeight()+$("#"+id).scrollTop())/3);
					
					break;	
			}
		}
		function rewrite_alias(field_id_start,field_id_alias){
			//Put the back slashes in the name to use with jquery
			field_id_start = "#"+field_id_start.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
			
			var new_text = $(field_id_start).val();
			new_text = new_text.toLowerCase();
			//Replace spaces by -
			new_text = new_text.replace(/ /g,"-");

			String.prototype.replaceArray = function(find, replace) {
			  var replaceString = this;
			  var re;
			  for (var i = 0; i < find.length; i++) {
				replaceString = replaceString.split(find[i]).join(replace[i])
			  }
			  return replaceString;
			};
			
			var find = ["à","â","é","è","ê","ë","î","ï","ô","û","ù","ç","&",".",":","(",")","!"," ","%","+","?","#"];
			var replace = ["a","a","e","e","e","e","i","i","o","u","u","c","-","-","-","-","-","","-","","","",""];

			new_text = new_text.replaceArray(find, replace);
			
			new_text = new_text.replace(/[^a-zA-Z0-9-_]/g, "");

			//Check if we modify the alias field
			if(field_id_alias == ""){
				$(field_id_start).val(new_text);
				$(field_id_start).change();
			}else {
				//Put the back slashes in the name to use with jquery
				field_id_alias = "#"+field_id_alias.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
				if($(field_id_alias).val()==""){
					$(field_id_alias).val(new_text);
					//To Update the maxlenght, we call change function
					$(field_id_alias).change();
				}
			}
			
		}
		
		function rewrite_number(field_id){
			//Put the back slashes in the name to use with jquery
			field_id = "#"+field_id.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");
			
			var new_number = $(field_id).val();
			//Replace spaces by -
			new_number = new_number.replace(",",".");
			
			new_number = new_number.replace(/[^0-9.]/g, "");

			$(field_id).val(new_number);	
		}
		
		$(function(){			
			$("[maxlength]").live({
				change: change_maxlength,
				keyup: change_maxlength
			});			
		});
		function change_maxlength(){  
			// id of span that contains the displayed number for maxlength
			var id_selector = $(this).attr("id")+"_maxlength";
			id_selector = "#"+id_selector.replace(/\[/gi,"\\\\[").replace(/\]/gi,"\\\\]");				
		
			//get the limit from maxlength attribute  
			var limit = parseInt($(this).attr("maxlength"));  
			//get the current text inside the textarea  
			var text = $(this).val();  
			//count the number of characters in the text  
			var chars = text.length;  
			
			//check if there are more characters then allowed  
			if(chars > limit){  
				//and if there are use substr to get the text before the limit  
				var new_text = text.substr(0, limit);  
			
				//and change the current text with the new text  
				$(this).val(new_text);  
			}  
			
			// update the characters left
			$(id_selector).html("").append((limit-chars));
		} 
		
		function array_diff_assoc (arr1) {
			// http://kevin.vanzonneveld.net
			// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +   bugfixed by: 0m3r
			// +    revised by: Brett Zamir (http://brett-zamir.me)
			// *     example 1: array_diff_assoc({0: "Kevin", 1: "van", 2: "Zonneveld"}, {0: "Kevin", 4: "van", 5: "Zonneveld"});
			// *     returns 1: {1: "van", 2: "Zonneveld"}
			var retArr = {},
				argl = arguments.length,
				k1 = "",
				i = 1,
				k = "",
				arr = {};
		
			arr1keys: for (k1 in arr1) {
				for (i = 1; i < argl; i++) {
					arr = arguments[i];
					for (k in arr) {
						if (arr[k] === arr1[k1] && k === k1) {
							// If it reaches here, it was found in at least one array, so try next value
							continue arr1keys;
						}
					}
					retArr[k1] = arr1[k1];
				}
			}
		
			return retArr;
		}
		
		function count (mixed_var, mode) {
			// http://kevin.vanzonneveld.net
			// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
			// +      input by: Waldo Malqui Silva
			// +   bugfixed by: Soren Hansen
			// +      input by: merabi
			// +   improved by: Brett Zamir (http://brett-zamir.me)
			// +   bugfixed by: Olivier Louvignes (http://mg-crea.com/)
			// *     example 1: count([[0,0],[0,-4]], "COUNT_RECURSIVE");
			// *     returns 1: 6
			// *     example 2: count({"one" : [1,2,3,4,5]}, "COUNT_RECURSIVE");
			// *     returns 2: 6
			var key, cnt = 0;
		
			if (mixed_var === null || typeof mixed_var === "undefined") {
				return 0;
			} else if (mixed_var.constructor !== Array && mixed_var.constructor !== Object) {
				return 1;
			}
		
			if (mode === "COUNT_RECURSIVE") {
				mode = 1;
			}
			if (mode != 1) {
				mode = 0;
			}
		
			for (key in mixed_var) {
				if (mixed_var.hasOwnProperty(key)) {
					cnt++;
					if (mode == 1 && mixed_var[key] && (mixed_var[key].constructor === Array || mixed_var[key].constructor === Object)) {
						cnt += this.count(mixed_var[key], 1);
					}
				}
			}
		
			return cnt;
		}
		
		function goto_url (url) {
			document.location.href=url; 
		}		
		
		', CClientScript::POS_HEAD);				
	}		
	
	/**
	 * Generate language list
	 */
	public function generateLanguageList($attribute, $current_language_code='', $htmlOptions=array())
	{		
		//Verify if $current_language_code="empty" to select the first option who will be empty else we want to have de default language
		if($current_language_code=='empty'){
			$current_language_code = '';
		}else{
			$current_language_code = $current_language_code ? $current_language_code:Yii::app()->language;
		}
		
		return CHtml::dropDownList($attribute, $current_language_code, CHtml::listData(Tbl_Language::model()->active()->findAll(), 'code', 'name'), $htmlOptions);
	}	
	
	/**
	 * Generate currency list
	 */
	public function generateCurrencyList($attribute, $current_currency='', $htmlOptions=array())
	{	
		$current_currency = $current_currency ? $current_currency:Yii::app()->params['currency'];	
			
		return CHtml::dropDownList($attribute, $current_currency, CHtml::listData(Tbl_Currency::model()->active()->findAll(array('order'=>'code ASC')), 'code', 'code'), $htmlOptions);
	}		
		
	/**
	 * Generate country dropdown list
	 */
	public function generateCountryList($attribute, $current_country_code='', $current_language_code='', $htmlOptions=array())
	{		
		$current_language_code = $current_language_code ? $current_language_code:Yii::app()->language;
		$current_country_code = $current_country_code ? $current_country_code:Yii::app()->params['company_country_code'];
		
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$command=$connection->createCommand('SELECT
		t.code,
		tbl_country_description.name 
		FROM 
		country AS t 			
		LEFT JOIN 
		country_description AS tbl_country_description
		ON
		(t.code = tbl_country_description.country_code AND tbl_country_description.language_code=:language_code) 
		ORDER BY 
		tbl_country_description.name ASC');		
		
		return CHtml::dropDownList($attribute, $current_country_code, CHtml::listData($command->queryAll(true,array(':language_code'=>$current_language_code)),'code','name'), $htmlOptions);
	}
	
	/**
	 * Generate state dropdown list
	 */
	public function generateStateList($attribute, $current_country_code='', $current_state_code='', $current_language_code='', $htmlOptions=array())
	{		
		$current_language_code = $current_language_code ? $current_language_code:Yii::app()->language;
		$current_country_code = $current_country_code ? $current_country_code:Yii::app()->params['company_country_code'];
		$current_state_code = $current_state_code ? $current_state_code:Yii::app()->params['company_state_code'];
		$state_list = array();
		
		if ($current_country_code) {			
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			$command=$connection->createCommand('SELECT
			t.code,
			tbl_state_description.name 
			FROM 
			state AS t 			
			LEFT JOIN 
			state_description AS tbl_state_description
			ON
			(t.code = tbl_state_description.state_code AND tbl_state_description.language_code=:language_code) 
			WHERE
			t.country_code = :country_code
			ORDER BY 
			tbl_state_description.name ASC');		
			
			$state_list = CHtml::listData($command->queryAll(true,array(':language_code'=>$current_language_code,':country_code'=>$current_country_code)),'code','name');
		}
		
		return CHtml::dropDownList($attribute, $current_state_code, $state_list, $htmlOptions);
	}	
	
	/**
	 * Generate customer type dropdown list
	 */
	public function generateCustomerTypeList($attribute, $current_customer_type='', $htmlOptions=array())
	{
		$criteria=new CDbCriteria; 
		$criteria->select='id,name'; 
		$criteria->order='name ASC'; 	

		return CHtml::dropDownList($attribute, $current_customer_type, CHtml::listData(Tbl_CustomerType::model()->findAll($criteria),'id','name'), $htmlOptions);
	}
	
	/**
	 * Generate customer type dropdown list
	 */
	public function generateTaxGroupList($attribute, $current_tax_group='', $htmlOptions=array())
	{				
		return CHtml::dropDownList($attribute, $current_tax_group, CHtml::listData(Tbl_TaxGroup::model()->findAll(array('order'=>'name ASC')),'id','name'), $htmlOptions);
	}	
	
	/**
	 * Generate payment gateway list
	 */
	public function generatePaymentGatewayList($attribute, $current_payment_gateway='', $htmlOptions=array())
	{		
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$command=$connection->createCommand('SELECT
		t.id,
		t.name 
		FROM 
		payment_gateway AS t 			
		ORDER BY 
		t.name ASC');		
		
		return CHtml::dropDownList($attribute, $current_payment_gateway, CHtml::listData($command->queryAll(true),'id','name'), $htmlOptions);
	}
	
	/**
	 * Generate payment gateway list
	 */
	public function generateShippingGatewayList($attribute, $current_shipping_gateway='', $htmlOptions=array())
	{		
		
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$command=$connection->createCommand('SELECT
		t.id,
		t.name 
		FROM 
		shipping_gateway AS t 			
		ORDER BY 
		t.name ASC');		
		
		return CHtml::dropDownList($attribute, $current_shipping_gateway, CHtml::listData($command->queryAll(true),'id','name'), $htmlOptions);

	}
	
	/**
	 * Format Currency with default currency code
	 */
	public function nf($amount='')
	{
		$number_formatter = new CNumberFormatter(Yii::app()->language);
		return $number_formatter->formatCurrency($amount, Yii::app()->params['currency']);
	}
	
	/**
	 * Get columns maxlength values for varchar
	 */
	public function getColumnsMaxLength($table)
	{
		$columns = array();
		
		if (!empty($table)) {	
			foreach (Yii::app()->db->schema->getTable($table)->columns as $key => $value) {
				if ($value->type == 'string' && $value->size) {
					$columns[$key] = $value->size;
				}
			}					
		}
		
		return $columns;	
	}
	
	/**
	 * Remove Blank line in a text
	 */
	public function remove_blank_line_addslashes($text)
	{
		$new_text = addslashes(str_replace("\n","",str_replace("\r","",$text)));
		
		return $new_text;	
	}
	
	/**
	 * Show number of Star for rating product
	 */
	public function get_rated_star($rating, $size=""){
		$total_display = 5;
		$result = '';
		$int_part = floor($rating);
		
		for($x=0;$x<$total_display;$x++){
			if($x < $int_part){
				$result .= '<div class="rating_star rating_star_full'.$size.'"></div>';
			}else{
				$result .= '<div class="rating_star rating_star_empty'.$size.'"></div>';
			}
		}
		return $result;
	}
	
	/**
	 * Company signature for emails
	 */
	public function get_company_signature($type=0,$language_code='')
	{
		$app = Yii::app();
		$language_code = $language_code ? $language_code:$app->language;
		
		//Find the country and state of the company
		$country_name = '';
		$state_name = ''; 
		if($app->params['company_country_code']){
			$criteria=new CDbCriteria; 
			$criteria->condition='country_code=:country_code AND language_code=:language_code'; 
			$criteria->params=array(':country_code'=>$app->params['company_country_code'],':language_code'=>$language_code); 

			if ($country = Tbl_CountryDescription::model()->find($criteria)) {
				$country_name = $country->name;
			}
		}
		if($app->params['company_state_code']){
			$criteria=new CDbCriteria; 
			$criteria->condition='state_code=:state_code AND language_code=:language_code'; 
			$criteria->params=array(':state_code'=>$app->params['company_state_code'],':language_code'=>$language_code); 

			if ($state = Tbl_StateDescription::model()->find($criteria)) {
				$state_name = $state->name;
			}
		}		
		
		switch ($type){
			// plain
			case 0:
				return $app->params['company_company']."\r\n".
				$app->params['company_address']."\r\n".
				$app->params['company_city'].($state_name ? ', '.$state_name:'').' '.$app->params['company_zip'].' '.$country_name."\r\n".
				($app->params['company_telephone'] ? Yii::t('global','LABEL_CONTACT_US_T',array(),NULL,$language_code).' '.$app->params['company_telephone']."\r\n":'').
				($app->params['company_fax'] ? Yii::t('global','LABEL_CONTACT_US_F',array(),NULL,$language_code).' '.$app->params['company_fax']."\r\n":'').
				($app->params['company_email'] ? Yii::t('global','LABEL_CONTACT_US_E',array(),NULL,$language_code).' '.$app->params['company_email']."\r\n":'').
				"\r\n".Yii::t('global','LABEL_FOLLOW_US',array(),NULL,$language_code)."\r\n
				Web http://".$_SERVER['HTTP_HOST']."\r\n".
				($app->params['facebook'] ? 'Facebook '.$app->params['facebook']."\r\n":'').
				($app->params['twitter'] ? 'Twitter '.$app->params['twitter']."\r\n":'');
				break;
			// html
			case 1:
				return '<table border="0" cellpadding="10" cellspacing="0">
				<tr>
				<td valign="top"><a href="http://'.$_SERVER['HTTP_HOST'].'"><img border="0" src="http://'.$_SERVER['HTTP_HOST'].'/_images/logo.jpg" alt="'.$app->params['site_name'].'" /></a></td>
				<td valign="top"><strong>'.$app->params['company_company'].'</strong><br />'.
				$app->params['company_address'].'<br />'.
				$app->params['company_city'].($state_name ? ', '.$state_name:'').' '.$app->params['company_zip'].' '.$country_name.'<br />'.
				($app->params['company_telephone'] ? '<strong>'.Yii::t('global','LABEL_CONTACT_US_T',array(),NULL,$language_code).'</strong> '.$app->params['company_telephone'].'<br />':'').
				($app->params['company_fax'] ? '<strong>'.Yii::t('global','LABEL_CONTACT_US_F',array(),NULL,$language_code).'</strong> '.$app->params['company_fax'].'<br />':'').
				($app->params['company_email'] ? '<strong>'.Yii::t('global','LABEL_CONTACT_US_E',array(),NULL,$language_code).'</strong> '.$app->params['company_email'].'<br />':'').
				'<br />'.Yii::t('global','LABEL_FOLLOW_US',array(),NULL,$language_code).' <a href="http://'.$_SERVER['HTTP_HOST'].'">Web</a> '.
				($app->params['facebook'] ? '| <a href="'.$app->params['facebook'].'">Facebook</a> ':'').
				($app->params['twitter'] ? '| <a href="'.$app->params['twitter'].'">Twitter</a>':'').
				'</td>
				</tr>
				</table>';
				break;
		}
	}
	
	public function include_jquery_bubble()
	{		
		$include_path = Yii::app()->params['includes_js_path'];	
		
		$cs=Yii::app()->clientScript; 		
		$cs->registerCssFile($include_path.'jquery/jquery-bubble-popup-v3/css/jquery-bubble-popup-v3.css');
		$cs->registerScriptFile($include_path.'jquery/jquery-bubble-popup-v3/scripts/jquery-bubble-popup-v3.min.js', CClientScript::POS_HEAD);	
		$cs->registerScriptFile($include_path.'jquery/jquery.xdomainajax.js',CClientScript::POS_HEAD);	
		
		$cs->registerScript('jquery_bubble','
		$(document).on("click",".help_hint",function(){
			var bubble = $(this);
			var content = "";
			
			if( !bubble.HasBubblePopup() ) {	
				bubble.CreateBubblePopup({
					selectable: true,
					alwaysVisible: true,
					position : "top",
					align	 : "center",
					openingSpeed: 1,
					closingSpeed: 1,
					
					innerHtml: \'<div><div style="float:left; margin-right:5px;">'.CHtml::image(Yii::app()->params['dhtmlx_path'].'imgs/toolbar/ajax-loader.gif',Yii::t('components/Html','LABEL_LOADING'),array('border'=>0)).'</div><div style="float:left; margin-top:2px;"><strong>'.Yii::t('components/Html','LABEL_LOADING').'</strong></div><div style="clear:both:"></div></div>\',
					innerHtmlStyle: {
										"text-align":"left"
									},
														
					themeName: 	"azure",
					themePath: 	"'.$include_path.'jquery/jquery-bubble-popup-v3/jquerybubblepopup-themes"
					
				});				
				
				$.ajax({
					url: "'.CController::createUrl('get_help_hints').'?path="+bubble.parent("a").prop("title"),
					 type: "GET",
	
					success: function(data) {
						bubble.SetBubblePopupInnerHtml(data, true);		
						bubble.ShowBubblePopup();					
					},
					error: function(jqXHR, textStatus, errorThrown){
						bubble.SetBubblePopupInnerHtml("Unable to find hint file.",false);
					}
				});		
			}																						
		});	
		', CClientScript::POS_HEAD);			
	}	
	
	/**
	 * scrollTo
	 */
	public function include_jquery_scrollto()
	{
		$include_path = Yii::app()->params['includes_js_path'];	
		
		$cs=Yii::app()->clientScript; 		
		$cs->registerScriptFile($include_path.'jquery/jquery.scrollTo-1.4.2-min.js', CClientScript::POS_HEAD);	
	}
	
	/**
	 * help hint link + icon
	 */ 

	 
	public function help_hint($path)
	{
		return '<a href="javascript:void(0);" title="'.$path.'" tabindex="-1">'.CHtml::image(Html::imageUrl('help-icon.png'),'',array('border'=>0,'class'=>'help_hint','title'=>Yii::t('global', 'LABEL_HINT_HELP'))).'</a>';
	}
	
	
	/**
	 * auth function for multi store
	 */
	function user_store_auth()
	{
		$authenticated = 0;
		
		if ($store = Tbl_LinkedStore::model()->findByPk($id_linked_store)) {
			$url = 'http://'.$store->domain.'/admin';

		
			$process = curl_init($url); 
			curl_setopt($process, CURLOPT_HEADER, 1); 
			curl_setopt($process, CURLOPT_POSTFIELDS, $data); 
			curl_setopt($process, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($process, CURLOPT_FOLLOWLOCATION, 1); 
			curl_setopt($process, CURLOPT_POST, 1); 
			curl_close($process); 
		}
		
		return $authenticated;
	}	
	
	public function generateLinkedStoreList()
	{				
		$id_user = (!Yii::app()->user->isGuest) ? (int)Yii::app()->user->getId():0;
		$linked_stores = Tbl_LinkedStore::model()->findAll();
		$array = array();
		
		if (sizeof($linked_stores)) {
			$linked_stores = CHtml::listData($linked_stores,'domain','domain');
			
			if ($id_user && $user = Tbl_User::model()->findByPk($id_user)) {
				foreach ($linked_stores as $k => $store) {
					$url = 'http://'.$store.CController::createUrl('site/check_auth_key',array('auth_key'=>$user->auth_key,'domain'=>$_SERVER['HTTP_HOST']));
					
					$ch = curl_init(); 
					curl_setopt($ch, CURLOPT_URL, $url); 
					curl_setopt($ch, CURLOPT_HEADER, 0); 
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
					$response = curl_exec($ch); 
					curl_close($ch); 
					
					if ($response == 'true') $array['http://'.$store.CController::createUrl('site/login',array('auth_key'=>$user->auth_key,'domain'=>$_SERVER['HTTP_HOST'],'_lang'=>Yii::app()->language))] = $store;
				}
			} else {
				foreach ($linked_stores as $k => $store) {
					$array['http://'.$store.CController::createUrl('site/login')] = $store;
				}
			}
		}		
		
		return $array;
	}	
	
	public function connect_other_db($dbname)
	{
		$db = Yii::app()->getComponent('db'); 		
		
		$connection=new CDbConnection('mysql:host=localhost;dbname='.$dbname,$db->username,$db->password);
		// establish connection. You may try...catch possible exceptions
		try {
			$connection->emulatePrepare=$db->emulatePrepare;
			$connection->charset=$db->charset;
			$connection->enableParamLogging=$db->enableParamLogging;
			$connection->enableProfiling=$db->enableProfiling;
			$connection->active=true;
		} catch (Exception $e) {
			$connection = false;	
		}
		
		return $connection;
	}
	
	function empty_dir($path)
	{
		if (!is_dir($path)) throw new Exception('An error occured, while trying to delete directory contents.');
		
		$it = new RecursiveDirectoryIterator($path); 
		
		// Skip "dot" files 
		$it->setFlags(RecursiveDirectoryIterator::SKIP_DOTS); 
		
		$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST); 

		foreach($files as $file){ 
			if ($file->isDir()){ 
				rmdir($file->getRealPath()); 
			} else { 
				unlink($file->getRealPath()); 
			} 
		} 		
	}
}
?>