<?php 
$containerObj = $containerJS.'_obj';
$containerLayout = $containerJS.'_layout';

$script = '
'.$containerObj.' = new Object();

'.$containerObj.'.layout = new Object();
'.$containerObj.'.layout.obj = new dhtmlXLayoutObject("'.$containerLayout.'", "1C");

'.$containerObj.'.layout.A = new Object();
'.$containerObj.'.layout.A.obj = '.$containerObj.'.layout.obj.cells("a");
'.$containerObj.'.layout.A.obj.hideHeader();

'.$containerObj.'.layout.A.tree = new Object();

'.$containerObj.'.layout.A.tree.load = function(){
	var obj = '.$containerObj.'.layout.A.tree.obj;
	
	obj.loadXML(obj.xmlOrigFileUrl);	
}

'.$containerObj.'.layout.A.tree.obj = '.$containerObj.'.layout.A.obj.attachTree();
'.$containerObj.'.layout.A.tree.obj.setSkin(dhx_skin);
'.$containerObj.'.layout.A.tree.obj.setImagePath(dhx_globalImgPath);
'.$containerObj.'.layout.A.tree.obj.enableCheckBoxes(true,false);
'.$containerObj.'.layout.A.tree.obj.enableThreeStateCheckboxes(true);
'.$containerObj.'.layout.A.tree.obj.xmlOrigFileUrl = "'.CController::createUrl('xml_list_permission',array('id'=>$id)).'";	
'.$containerObj.'.layout.A.tree.load();

'.$containerObj.'.layout.A.tree.obj.attachEvent("onCheck", function(id,state){
	var obj = this;
	
	var checked = obj.getAllChecked();
	checked = checked.split(",");
	var ids=[];
	
	for (var i=0;i<checked.length;++i) {
		if (checked[i]) {
			ids.push("ids[]="+checked[i]);									
		}
	}		
	
	$.ajax({
		url: "'.CController::createUrl('save_permissions',array('id'=>$id)).'",
		type: "POST",
		data: ids.join("&")
	});			
		
});

'.$containerJS.'.layout.A.dataview.ajaxComplete();
';

echo Html::script($script); 
?>
<div id="<?php echo $containerLayout; ?>" style="width:100%; height:100%; padding:0; margin:0;"></div>