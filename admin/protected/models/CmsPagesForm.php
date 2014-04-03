<?php
class CmsPagesForm extends CFormModel
{
	// database fields
	public $id=0; 
	public $id_parent=0;
	public $cmspage_description=array();
	public $header_only=0;
	
	public $active=0;
	public $display=1;
	public $home_page=0;
	public $display_menu=0;
	public $sort_order=0;
	public $indexing=1;
	public $external_link=0;
	public $id_subscription_contest=0;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	 
	
	public function rules()
	{
		return array(	
		);
	}	  

	public function validate()
	{			
		$connection=Yii::app()->db;   // assuming you have configured a "db" connection
	
		// check description values for each languages	
		$columns = Html::getColumnsMaxLength(Tbl_CmspageDescription::tableName());
		
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
			
		foreach (Tbl_Language::model()->active()->findAll() as $value) {							
	
			if(!$this->header_only){
				if(!$this->external_link && !$this->id_subscription_contest){
					// if indexing is active
					if ($this->indexing) {
						
						// meta_description is required
						$meta_description = $this->cmspage_description[$value->code]['meta_description'];
						if (empty($this->cmspage_description[$value->code]['meta_description'])) {
							$this->addError('cmspage_description['.$value->code.'][meta_description]',Yii::t('global','ERROR_EMPTY'));	
						} else if (isset($columns['meta_description']) && strlen($meta_description) > $columns['meta_description']) {
							$this->addError('cmspage_description['.$value->code.'][meta_description]',Yii::t('global','ERROR_MAXLENGTH').$columns['meta_description']);	
						}			
						
						// meta_keywords is required
						$meta_keywords = $this->cmspage_description[$value->code]['meta_keywords'];
						if (empty($this->cmspage_description[$value->code]['meta_keywords'])) {
							$this->addError('cmspage_description['.$value->code.'][meta_keywords]',Yii::t('global','ERROR_EMPTY'));	
						} else if (isset($columns['meta_keywords']) && strlen($meta_keywords) > $columns['meta_keywords']) {
							$this->addError('cmspage_description['.$value->code.'][meta_keywords]',Yii::t('global','ERROR_MAXLENGTH').$columns['meta_keywords']);	
						}
					}
				}
			}
			
			if(!$this->home_page){
				// name is required
				$name = $this->cmspage_description[$value->code]['name'];
				if (empty($name)) {
					$this->addError('cmspage_description['.$value->code.'][name]',Yii::t('global','ERROR_EMPTY'));	
				} else if (isset($columns['name']) && strlen($name) > $columns['name']) {
					$this->addError('cmspage_description['.$value->code.'][name]',Yii::t('global','ERROR_MAXLENGTH').$columns['name']);	
				}						
				
				/*
				// description is required
				$description = $this->cmspage_description[$value->code]['description'];
				if (empty($description) and !$this->header_only) {
					$this->addError('cmspage_description['.$value->code.'][description]',Yii::t('global','ERROR_EMPTY'));	
				} */		
			
				if(!$this->header_only){
					if(!$this->external_link && !$this->id_subscription_contest){
						// if indexing is active
						if ($this->indexing) {						
							// alias is required
							$alias = mb_strtolower($this->cmspage_description[$value->code]['alias'],'utf-8');
							$this->cmspage_description[$value->code]['alias'] = $alias;														
							if (empty($alias)) {								
								$this->addError('cmspage_description['.$value->code.'][alias]',Yii::t('global','ERROR_EMPTY'));	
							} else {
								if (isset($columns['alias']) && strlen($alias) > $columns['alias']) {
									$this->addError('cmspage_description['.$value->code.'][alias]',Yii::t('global','ERROR_MAXLENGTH').$columns['alias']);	
								// check if alias is valid
								} else if (!preg_match('/[^0-9a-z-_\s]/',$alias)) {										
									// check if alias is already in use									
									$row = $command->queryRow(true, array(':id'=>$this->id,':id_parent'=>$this->id_parent,':alias'=>$alias,':language_code'=>$value->code));
									
									// if in use, tell us by whom
									if ($row['id']) {												
										$this->addError('cmspage_description['.$value->code.'][alias]',Yii::t('global','ERROR_ALIAS_EXIST') . ' '.$row['name']);				
									}				
								} else {
									$this->addError('cmspage_description['.$value->code.'][alias]',Yii::t('global','ERROR_ALIAS_INVALID'));					
								}
							}
						}
					}
				}else{
					$this->cmspage_description[$value->code]['alias']='';
				}
			}
		}	
		
		
		if(!$this->home_page){
			// Check if put the parent page into the same page we edit or put the parent page into a page who already is the child of the page we edit
			$criteria=new CDbCriteria; 
			$criteria->condition='id_parent=:id AND id=:id_parent'; 
			$criteria->params=array(':id_parent'=>$this->id_parent,':id'=>$this->id); 				
			
			if($this->id and (($this->id_parent == $this->id) or Tbl_Cmspage::model()->count($criteria)) and $this->display){
				$this->addError('pages',Yii::t('global','ERROR_INVALID_PAGE'));	
			}	
			
		}
		
		return $this->hasErrors() ? false:true;
	}
	
	/**
	 * Function to save 
	 */	
	public function save()
	{
		$current_datetime = date('Y-m-d H:i:s');
		$current_id_user = (int)Yii::app()->user->getId();
		
		// edit or new
		if ($this->id) {
			$model = Tbl_Cmspage::model()->findByPk($this->id);	
		} else {
			$model = new Tbl_Cmspage;	
			$model->id_user_created = $current_id_user;			
			$model->date_created = $current_datetime;
			
			$criteria=new CDbCriteria; 
			$criteria->condition='id_parent=:id_parent'; 
			$criteria->params=array(':id_parent'=>($this->id_parent?$this->id_parent:$this->id)); 				
			$criteria->order='sort_order ASC';
			
			$model->sort_order = Tbl_Cmspage::model()->count($criteria)+1;					
		}
		$model->id_user_modified = $current_id_user;
		if(!$this->home_page){
			if(!$this->display){
				$model->id_parent = 0;	
			}else{
				$model->id_parent = $this->id_parent;	
			}
			$model->active = $this->active;
			$model->header_only = $this->header_only;
			$model->display = $this->display;
			$model->display_menu = $this->display_menu;
			$model->indexing = $this->indexing;
			$model->external_link = $this->external_link;
			$model->id_subscription_contest = $this->id_subscription_contest;
		}
		if ($model->save()) {		
			foreach ($this->cmspage_description as $code => $value) {
				$criteria=new CDbCriteria; 
				$criteria->condition='id_cmspage=:id_cmspage AND language_code=:language_code'; 
				$criteria->params=array(':id_cmspage'=>$model->id,':language_code'=>$code); 					
				
				if (!$model_description = Tbl_CmspageDescription::model()->find($criteria)) {
					$model_description = new Tbl_CmspageDescription;				
					$model_description->id_cmspage = $model->id;
					$model_description->language_code = $code;
				}
				
				if(!$this->home_page){
					$model_description->name = $value['name'];
					
					$model_description->alias = $value['alias'];
					
					
					$model_description->external_link_target_blank = $value['external_link_target_blank'];
					if($this->external_link){
						$model_description->external_link_link = $value['external_link_link'];
					}else{
						$model_description->external_link_link = '';
						$model_description->external_link_target_blank = 0;
					}
					
					
				}
				$model_description->description = $value['description'];
				$model_description->meta_description = $value['meta_description'];
				$model_description->meta_keywords = $value['meta_keywords'];			
							
				if (!$model_description->save()) {
					if (!$this->id) { $model->delete(); }
					throw new CException(Yii::t('global','ERROR_SAVING_DESCRIPTION'));	
				}
			}					
		} else {
			throw new CException(Yii::t('global','ERROR_SAVING'));	
		}
		
		$this->id = $model->id;

		return true;
	}
}
