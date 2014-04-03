<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();
// variables used to check modification
'.$containerObj.'.og_form = [];

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.layout.toolbar = new Object();

'.$containerObj.'.layout.toolbar.load = function(current_id){
	'.$containerObj.'.layout.toolbar.obj.clearAll();	
	'.$containerObj.'.layout.toolbar.obj.detachAllEvents();
	'.$containerObj.'.layout.toolbar.obj.addButton("save",null,"'.Yii::t('global','LABEL_BTN_SAVE').'","toolbar/save.gif","toolbar/save_dis.gif");  
	'.$containerObj.'.layout.toolbar.obj.addSeparator("sep02", null);
	'.$containerObj.'.layout.toolbar.obj.addButton("add_categories",null,"'.Yii::t('views/products/edit_categories','LABEL_BTN_ADD_CATEGORIES').'","toolbar/add.gif","toolbar/add_dis.gif");

	'.$containerObj.'.layout.toolbar.obj.attachEvent("onClick",function(id){
		var obj = this;
		
		switch (id) {
			case "save":	
				var obj = '.$containerObj.'.layout.A.tree.obj;
	
				var checked = obj.getAllCheckedBranches();
				checked = checked.split(",");
				var ids=[];
				
				for (var i=0;i<checked.length;++i) {
					if (checked[i]) {
						ids.push("ids[]="+checked[i]);								
					}
				}	
					
				$.ajax({
					url: "'.CController::createUrl('save_categories',array('id'=>$id)).'",
					type: "POST",
					data: ids.join("&"),
					complete: function(){
					},
					success: function(data){						
						alert("'.Yii::t('global','LABEL_ALERT_SAVE_SUCCESS').'");

						'.$containerObj.'.og_form = [];
						'.$containerObj.'.load_og_form();
					}
				});									
				break;
			case "add_categories":
				goto_url("'.CController::createAbsoluteUrl('categories/').'?task=new");
			break;
		}
	});	
};

'.$containerObj.'.layout.toolbar.obj = '.$containerObj.'.layout.obj.attachToolbar();
'.$containerObj.'.layout.toolbar.obj.setIconsPath(dhx_globalImgPath);	
'.$containerObj.'.layout.toolbar.load('.$id.');

'.$containerObj.'.layout.A.tree = new Object();

'.$containerObj.'.layout.A.tree.load = function(){
	var obj = '.$containerObj.'.layout.A.tree.obj;
	
	obj.loadXML(obj.xmlOrigFileUrl,function(){
		'.$containerObj.'.load_og_form();
	});	
}
'.$containerObj.'.layout.A.tree.obj = '.$containerObj.'.layout.A.obj.attachTree();
'.$containerObj.'.layout.A.tree.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.tree.obj.setImagePath(dhx_globalImgPath);
'.$containerObj.'.layout.A.tree.obj.enableCheckBoxes(true,false);
'.$containerObj.'.layout.A.tree.obj.enableThreeStateCheckboxes(true);

'.$containerObj.'.layout.A.tree.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_categories',array('id'=>$id)).'";	
'.$containerObj.'.layout.A.tree.load();

'.$containerJS.'.layout.A.dataview.ajaxComplete();

// load original form values
'.$containerObj.'.load_og_form = function()
{
	'.$containerObj.'.og_form = [];
	
	if ('.$containerObj.'.layout.A.tree.obj) {
		var checked = '.$containerObj.'.layout.A.tree.obj.getAllCheckedBranches();
		checked = checked.split(",");
		
		for (var i=0;i<checked.length;++i) {
			if (checked[i]) {
				'.$containerObj.'.og_form[i] = checked[i];
			}
		}	
	}
};

// check if any modifications has been made
'.$containerObj.'.has_modifications = function()
{
	// check for modifications
	var str_array=[];
	
	if ('.$containerObj.'.layout.A.tree.obj) {
		var checked = '.$containerObj.'.layout.A.tree.obj.getAllCheckedBranches();
		checked = checked.split(",");
		
		for (var i=0;i<checked.length;++i) {
			if (checked[i]) {
				str_array[i] = checked[i];
			}
		}			
	}
	
	return (count(array_diff_assoc('.$containerObj.'.og_form,str_array)) || count(array_diff_assoc(str_array,'.$containerObj.'.og_form)) ? 1:0);
};
';

echo Html::script($script); 
?>
<div id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></div>