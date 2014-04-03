<?php

class CmspagesController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{					
		// display the form
		$this->render('index');	
	}

	/**
	 * This is the action to edit or create
	 */ 
	public function actionEdit($container, $id=0)	
	{
		$model=new CmsPagesForm;
		$model_name = get_class($model);	
		
		$id = (int)$id;
		if(isset($_POST["id"])){
			$id = (int)$_POST["id"];
		}else{
			$id = (int)$id;
		}
		
		$model->id = $id;
		$protected = 0;
		$home_page = 0;
		
		$external_link = $model->external_link;
		
		if ($p = Tbl_Cmspage::model()->getDescription()->findByPk($id)) {
			$protected = $p->protected;
			$home_page = $p->home_page;
		}
				
		$this->renderPartial('edit',array('model'=>$model, 'container'=>$container, 'protected'=>$protected, 'home_page'=>$home_page, 'external_link'=>$external_link, 'model_name'=>$model_name));	
	}	
	
	/**
	 * This is the action to save the information
	 */
	public function actionSave()
	{
		$model = new CmsPagesForm;
		
		$id_parent = (int)$_POST['id_parent'];
		$model->id_parent = $id_parent;
		// collect user input data
		if(isset($_POST['CmsPagesForm']))
		{			
			// loop through each attribute and set it in our model
			foreach($_POST['CmsPagesForm'] as $name=>$value)
			{
				$model->$name=$value;
			}			
			
			// validate 
			if($model->validate()) {
				$model->save();
			}
			
			$output = array('id'=>$model->id);
			$errors = $model->getErrors();
			
			if (sizeof($errors)) { 
				$output['errors'] = $errors;
			}
								
			echo CJSON::encode($output);	
		}
	}
	
	/**
	 * This is the action to delete
	 */
	public function actionDelete()
	{
		// current category
		$ids = $_POST['ids'];
		
		if (is_array($ids) && sizeof($ids)) {
			$connection=Yii::app()->db;   // assuming you have configured a "db" connection
			
			foreach ($ids as $id) {			
				Tbl_Cmspage::model()->deleteByPk($id);
				
				$criteria=new CDbCriteria; 
				$criteria->condition='id_cmspage=:id_cmspage'; 
				$criteria->params=array(':id_cmspage'=>$id); 					
				
				Tbl_CmspageDescription::model()->deleteAll($criteria);				
			}
		}
	}			 

	/**
	 * This is the action to get an XML list
	 */
	/**
	 * This is the action to get an XML list of products
	 */
	public function actionXml_list()
	{		
		// xml header
		header("content-type: text/xml");	
		
		$eol = "\r\n";
		
		echo '<?xml version="1.0" encoding="utf-8"?>'.$eol;
		echo '<rows>'.$eol;		
		
		$this->get_pages_treegrid();		
		
		echo '</rows>'.$eol;
	}		
	
	/**
	 * This is a function to get a list of the categories and sub categories recursively
	 */
	public function get_pages_treegrid($id_parent=0)
	{
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id_parent); 				
		$criteria->order='sort_order ASC';	
		
		$eol = "\r\n";
			
		foreach (Tbl_Cmspage::model()->getDescription()->findAll($criteria) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_parent=:id_parent'; 
			$criteria2->params=array(':id_parent'=>$row->id); 				
			$criteria2->order='sort_order ASC';			
			
			$child = Tbl_Cmspage::model()->count($criteria2) ? 1:0;	
			
			if($row['display']){
				switch($row['display_menu']){
					case 0;
						$display_menu = Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU_WICH_BOTH');
					break;
					case 1;
						$display_menu = Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU_WICH_TOP_ONLY');
					break;
					case 2;
						$display_menu = Yii::t('views/cmspages/edit_options','LABEL_DISPLAY_MENU_WICH_BOTTOM_ONLY');
					break;
					
				}
			}else{
				$display_menu = '-';
			}
		
			echo '<row id="'.$row->id.'" '.($row['active']?'':'class="innactive"').'>
			<cell type="'.($row['protected']?'ro':'').'" />
			<cell><![CDATA['.CHtml::encode($row->tbl_cmspage_description[0]->name).']]></cell>
			<cell><![CDATA['.($row['home_page']?'-':CHtml::encode($row->tbl_cmspage_description[0]->alias)).']]></cell>
			<cell><![CDATA['.($row['home_page']?'-':$display_menu).']]></cell>
			<cell type="'.($row['home_page']?'ro':'').'">'.($row['home_page']?'-':$row->display).'</cell>
			<cell type="'.($row['home_page']?'ro':'').'">'.($row['home_page']?'-':$row->active).'</cell>';			
			
			if ($child) { $this->get_pages_treegrid($row->id); }
			
			echo '</row>'.$eol;
		}			
		
	}	

	
	/**
	 * This is the action to get an XML list 
	 */
	public function actionXml_list_cmspage_description($container, $id=0)
	{
		$model = new CmsPagesForm;
		$model_name = get_class($model);
		
		$id = (int)$id;
		
		if ($id) {
			if ($cmspage = Tbl_Cmspage::model()->findByPk($id)) {							
				$model->display = $cmspage->display;
				$model->header_only = $cmspage->header_only;
				$model->indexing = $cmspage->indexing;
				$model->external_link = $cmspage->external_link;
				$model->id_subscription_contest = $cmspage->id_subscription_contest;
				// grab description information 
				foreach ($cmspage->tbl_cmspage_description as $row) {
					$model->cmspage_description[$row->language_code]['name'] = $row->name;
					$model->cmspage_description[$row->language_code]['description'] = $row->description;
					$model->cmspage_description[$row->language_code]['alias'] = $row->alias;
					$model->cmspage_description[$row->language_code]['meta_description'] = $row->meta_description;
					$model->cmspage_description[$row->language_code]['meta_keywords'] = $row->meta_keywords;
					
					$model->cmspage_description[$row->language_code]['external_link_link'] = $row->external_link_link;
					$model->cmspage_description[$row->language_code]['external_link_target_blank'] = $row->external_link_target_blank;
				}	
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}		
		
		$columns = Html::getColumnsMaxLength(Tbl_CmspageDescription::tableName());		
		
		$help_hint_path = '/cms-pages/';
		
		header ("content-type: text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?><tabbar><row>';
		
		$i=0;
		foreach (Tbl_Language::model()->active()->findAll() as $value) {
			echo '<tab id="'.$value->code.'" '.(!$i ? 'selected="1"':'').' width="*">'.$value->name.'
			<content>
				<![CDATA[
					<div style="width:100%; height:100%; overflow:auto;" class="div_'.$container.'_desc">
					<div style="padding:10px;">';			
					if(!$cmspage->home_page){
					echo '<div class="row">
						<strong>'.Yii::t('controllers/CmspagesController','LABEL_TITLE').'</strong>'.
						(isset($columns['name']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_cmspage_description['.$value->code.'][name]_maxlength">'.($columns['name']-strlen($model->cmspage_description[$value->code]['name'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'title').'
						<div>'.
						CHtml::activeTextField($model,'cmspage_description['.$value->code.'][name]',array('style' => 'width: 98%;','maxlength'=>$columns['name'], 'onblur'=>'rewrite_alias($(this).attr("id"),"'.$container.'_cmspage_description['.$value->code.'][alias]");', 'id'=>$container.'_cmspage_description['.$value->code.'][name]')).'
						<br /><span id="'.$container.'_cmspage_description['.$value->code.'][name]_errorMsg" class="error"></span>
						</div>
					</div>
					
					<div class="'.$container.'_external_link_display" style="display:'.(!$model->external_link?'none':'block').'">
						 <div class="row">
							<strong>'.Yii::t('views/cmspages/edit_options','LABEL_EXTERNAL_LINK_LINK').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'url').'<br />
							'.CHtml::activeTextField($model,'cmspage_description['.$value->code.'][external_link_link]',array('style' => 'width: 300px;', 'id'=>$container.'_cmspage_description['.$value->code.'][external_link_link]')).'
							<br /><span id="'.$container.'_cmspage_description['.$value->code.'][external_link_link]_errorMsg" class="error"></span>
						</div>
						<div class="row">
							<strong>'.Yii::t('views/cmspages/edit_options','LABEL_EXTERNAL_LINK_TARGET_BLANK').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'target-blank').'
							<div>
							'.CHtml::radioButton($model_name.'[cmspage_description]['.$value->code.'][external_link_target_blank]',$model->cmspage_description[$value->code]['external_link_target_blank']?1:0,array('value'=>1,'id'=>$container.'_cmspage_description['.$value->code.'][external_link_target_blank_1]')).'&nbsp;<label for="'.$container.'_cmspage_description['.$value->code.'][external_link_target_blank_1]" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_YES').'</label>&nbsp;&nbsp;'.CHtml::radioButton($model_name.'[cmspage_description]['.$value->code.'][external_link_target_blank]',!$model->cmspage_description[$value->code]['external_link_target_blank']?1:0,array('value'=>0,'id'=>$container.'_cmspage_description['.$value->code.'][external_link_target_blank_0]')).'&nbsp;<label for="'.$container.'_cmspage_description['.$value->code.'][external_link_target_blank_0]" style="display:inline; text-align: left;">'.Yii::t('global','LABEL_NO').'</label>
							
						 	</div>        
						</div>
					</div>
					
					
					
					'; 
					}
					
					echo '
					<div class="row '.$container.'_content_display" style="display:'.($model->header_only || $model->external_link || $model->id_subscription_contest?'none':'block').'">
						<strong>'.Yii::t('controllers/CmspagesController','LABEL_CONTENT').'</strong>&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'content').'
						<div>'.
						CHtml::activeTextArea($model,'cmspage_description['.$value->code.'][description]',array('style' => 'width: 98%;', 'class'=>'editor', 'rows' => 6, 'id'=>$container.'_cmspage_description['.$value->code.'][description]')).'
						<br /><span id="'.$container.'_cmspage_description['.$value->code.'][description]_errorMsg" class="error"></span>
						</div>
					</div>
					
					
					<div class="row '.$container.'_content_display_'.$value->code.' '.$container.'_header_display" style="display:'.($model->header_only || $model->external_link || $model->id_subscription_contest?'none':'block').'">'.($cmspage->home_page?CHtml::activeHiddenField($model,'cmspage_description['.$value->code.'][name]',array('id'=>$container.'_cmspage_description['.$value->code.'][name]')):'').'
						<div class="display_indexing" '.(!$model->indexing ? 'style="display:none;"':'').'>
							<h1>'.Yii::t('global','LABEL_TITLE_SEO').'</h1>
							<strong>'.Yii::t('global','LABEL_META_DESCRIPTION').'</strong>'.
							(isset($columns['meta_description']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_cmspage_description['.$value->code.'][meta_description]_maxlength">'.($columns['meta_description']-strlen($model->cmspage_description[$value->code]['meta_description'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'meta-description').'
							<div>'.
							CHtml::activeTextArea($model,'cmspage_description['.$value->code.'][meta_description]',array('style' => 'width: 98%;', 'rows' => 2,'maxlength'=>$columns['meta_description'], 'id'=>$container.'_cmspage_description['.$value->code.'][meta_description]')).'
							<br /><span id="'.$container.'_cmspage_description['.$value->code.'][meta_description]_errorMsg" class="error"></span>
							</div>
						</div>
					</div>  
					<div class="row '.$container.'_content_display_'.$value->code.' '.$container.'_header_display" style="display:'.($model->header_only || $model->external_link || $model->id_subscription_contest?'none':'block').'">
						<div class="display_indexing" '.(!$model->indexing ? 'style="display:none;"':'').'>
							<strong>'.Yii::t('global','LABEL_META_KEYWORDS').'</strong>'.
							(isset($columns['meta_keywords']) ? '&nbsp;<em>(Maxlength: <span id="'.$container.'_cmspage_description['.$value->code.'][meta_keywords]_maxlength">'.($columns['meta_keywords']-strlen($model->cmspage_description[$value->code]['meta_keywords'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'meta-keywords').'
							<div>'.
							CHtml::activeTextArea($model,'cmspage_description['.$value->code.'][meta_keywords]',array('style' => 'width: 98%;', 'rows' => 2,'maxlength'=>$columns['meta_keywords'], 'id'=>$container.'_cmspage_description['.$value->code.'][meta_keywords]')).'
							<br /><span id="'.$container.'_cmspage_description['.$value->code.'][meta_keywords]_errorMsg" class="error"></span>
							</div>
						</div>							
					</div>';
					
					if(!$cmspage->home_page){
					     
					echo'<div class="row '.$container.'_content_display_'.$value->code.' '.$container.'_header_display" style="display:'.($model->header_only || $model->external_link || $model->id_subscription_contest?'none':'block').'">
						<div class="display_indexing" '.(!$model->indexing ? 'style="display:none;"':'').'>
							<strong>'.Yii::t('global','LABEL_ALIAS').'</strong> ('.Yii::t('global','LABEL_ALIAS_CHARACTERS_ALLOWED').'):'.
							(isset($columns['alias']) ? '&nbsp;<em>('.Yii::t('global','LABEL_MAXLENGTH').' <span id="'.$container.'_cmspage_description['.$value->code.'][alias]_maxlength">'.($columns['alias']-strlen($model->cmspage_description[$value->code]['alias'])).'</span>)</em>':'').'&nbsp;&nbsp;'.Html::help_hint($help_hint_path.'alias').'
							<div>'.
							CHtml::activeTextField($model,'cmspage_description['.$value->code.'][alias]',array('style' => 'width: 98%;','maxlength'=>$columns['alias'], 'onkeyup'=>'rewrite_alias($(this).attr("id"),"");', 'id'=>$container.'_cmspage_description['.$value->code.'][alias]')).'
							<br /><span id="'.$container.'_cmspage_description['.$value->code.'][alias]_errorMsg" class="error"></span>
							</div>
						</div>							
					</div>';
					}
					echo '</div>
					</div>
				]]>
			</content>
		</tab>';
		
			++$i;
		}
		
		echo '</row></tabbar>';
	}
	
	public function actionEdit_options($container, $id=0)
	{
		$model = new CmsPagesForm;
		
		$id = (int)$id;
		
		if ($id) {
			if ($cmspage = Tbl_Cmspage::model()->findByPk($id)) {
				$model->id = $cmspage->id;
				$model->active = $cmspage->active;	
				$model->header_only = $cmspage->header_only;
				
				$model->id_parent = $cmspage->id_parent;
				$model->display = $cmspage->display;
				$model->home_page = $cmspage->home_page;
				$model->display_menu = $cmspage->display_menu;
				$model->indexing = $cmspage->indexing;
				$model->external_link = $cmspage->external_link;
				$model->id_subscription_contest = $cmspage->id_subscription_contest;
				
				foreach ($cmspage->tbl_cmspage_description as $row) {
					$model->cmspage_description[$row->language_code]['alias'] = $row->alias;
					$model->cmspage_description[$row->language_code]['meta_description'] = $row->meta_description;
					$model->cmspage_description[$row->language_code]['meta_keywords'] = $row->meta_keywords;
					$model->cmspage_description[$row->language_code]['external_link_link'] = $row->external_link_link;
					$model->cmspage_description[$row->language_code]['external_link_target_blank'] = $row->external_link_target_blank;
				}
								
			} else {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}
		}			
		
		$this->renderPartial('edit_options',array('model'=>$model, 'container'=>$container));	
	}
	
	public function actionXml_list_pages($id=0)
	{		
		$id = (int)$id;
		
		if ($id) {
			$criteria=new CDbCriteria; 
			$criteria->condition='id=:id'; 
			$criteria->params=array(':id'=>$id); 		
			
			if (!Tbl_Cmspage::model()->count($criteria)) {
				throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
			}			
		}				
	
		// xml header
		header("content-type: text/xml");	
		
		$eol = "\r\n";
		
		echo '<?xml version="1.0" encoding="utf-8"?>'.$eol;
		echo '<tree id="0">'.$eol;		
		
		$this->get_pages_tree();		
		
		echo '</tree>'.$eol;
	}
	
	public function get_pages_tree($id_parent=0)
	{
		$id_cmspage = (int)$id_cmspage;
		$id_parent = (int)$id_parent;
		
		$criteria=new CDbCriteria; 
		$criteria->condition='id_parent=:id_parent'; 
		$criteria->params=array(':id_parent'=>$id_parent); 				
		$criteria->order='sort_order ASC';	
		
		$eol = "\r\n";
			
		foreach (Tbl_Cmspage::model()->getDescription()->findAll($criteria) as $row) {		
			$criteria2=new CDbCriteria; 
			$criteria2->condition='id_parent=:id_parent'; 
			$criteria2->params=array(':id_parent'=>$row->id); 				
			$criteria2->order='sort_order ASC';			
			
			$child = Tbl_Cmspage::model()->count($criteria2) ? 1:0;	
					
			echo '<item text="'.CHtml::encode($row->tbl_cmspage_description[0]->name).'" id="'.$row->id.'" child="'.$child.'" call="true" open="1">'.$eol;
			
			if ($child) { $this->get_pages_tree($row->id); }
			
			echo '</item>'.$eol;
		}			
		
	}
	
	public function actionSave_sort_order()
	{
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		$id = (int)$_POST['id'];
		$id_parent = (int)$_POST['id_parent'];
		$index = (int)$_POST['index'];									
		if ($cmspage = Tbl_Cmspage::model()->findByPk($id)) {		
			// check if alias is already in use				
			$command=$connection->createCommand('SELECT 
			cmspage.id,
			cmspage_description.name 
			FROM 
			cmspage 
			INNER JOIN
			cmspage_description
			ON
			(cmspage.id = cmspage_description.id_cmspage AND cmspage_description.alias=:alias AND cmspage_description.language_code=:language_code)
			WHERE 
			cmspage.id_parent=:id_parent 
			AND 
			cmspage.id!=:id');			
		
			foreach ($cmspage->tbl_cmspage_description as $row) {
				if (!empty($row->alias)) { 
					// check if alias is already in use										
					$row_check = $command->queryRow(true, array(':id'=>$cmspage->id,':id_parent'=>$id_parent,':alias'=>$row->alias,':language_code'=>$row->language_code));
					
					// if in use, tell us by whom
					if ($row_check['id']) {												
						echo 'Alias exists under '.$row_check['name'];
						exit;						
					}		
				}
			}
		
		
			$criteria=new CDbCriteria; 
			$criteria->condition='id_parent=:id_parent AND id!=:id'; 
			$criteria->params=array(':id_parent'=>$id_parent,':id'=>$id); 				
												
			$i=0;
			foreach (Tbl_Cmspage::model()->findAll($criteria) as $row) {
				if ($i == $index) {
					$cmspage->id_parent = $id_parent;
					$cmspage->sort_order = $i;
					if (!$cmspage->save()) {
						throw new CException('error saving order');	
					}
					
					++$i;
				}
				
				$row->sort_order = $i;
	
				if (!$row->save()) {
					throw new CException('error saving order');	
				}								
				
				++$i;	
			}		
			
			if ($i == $index) {								
				$cmspage->id_parent = $id_parent;
				$cmspage->sort_order = $index;
				if (!$cmspage->save()) {
					throw new CException('error saving order');	
				}			
			}		
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	/**
	 * This is the action to toggle product active status
	 */
	public function actionToggle_active()
	{
		$id = (int)$_POST['id'];
		$active = ($_POST['active']=='true'?1:0);
		
		if ($p = Tbl_Cmspage::model()->findByPk($id)) {
			$p->active = $active;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	public function actionToggle_display()
	{
		$id = (int)$_POST['id'];
		$display = ($_POST['display']=='true'?1:0);
		
		if ($p = Tbl_Cmspage::model()->findByPk($id)) {
			$p->display = $display;
			$p->id_parent = 0;
			if (!$p->save()) {
				throw new CException(Yii::t('global','ERROR_UNABLE_CHANGE_STATUS'));	
			}
		} else {
			throw new CException(Yii::t('global','ERROR_INVALID_ID'));	
		}
	}
	
	/**
	 * This action is to redirect to the right url for the product preview
	 */
	public function actionPreview($id,$language)
	{
		$id=(int)$id;	
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
		
		require(dirname(__FILE__).'/../../../_includes/function.php');
		
		$sql='SELECT
		cmspage_description.name,
		cmspage_description.alias,
		cmspage_description.external_link_link,
		cmspage.home_page,
		cmspage.external_link,
		cmspage.id_subscription_contest,
		cmspage.indexing,
		cmspage.id,
		cmspage.header_only
		FROM
		cmspage
		INNER JOIN
		cmspage_description
		ON
		(cmspage.id = cmspage_description.id_cmspage AND cmspage_description.language_code = "'.$language.'")
		WHERE
		cmspage.id = :id
		LIMIT 1';
		$command=$connection->createCommand($sql);
		if ($row = $command->queryRow(true,array(':id'=>$id))) {
			if($row['home_page'] || $row['header_only']){
				$this->redirect('/'.$language.'/');
			}else{
				if ($row['external_link']) {
					$link = $row['external_link_link'];
					if (!stristr($link,'http://') && !stristr($link,'https://')) $link = 'http://'.$link;
				} else if ($row['id_subscription_contest']) {
					$link = '/'.$language.'/registration?id='.$row['id_subscription_contest'];
				} else if (!$row['indexing']) {
					$link = '/'.$language.'/page/'.makesafetitle($row['name']).'-'.$row['id'];
				} else {
					$link = '/'.$language.'/page/'.$row['alias'];
				}
					
				
				$this->redirect($link);
			}
			
		} else echo Yii::t('global','ERROR_INVALID_PAGE');
		exit;
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