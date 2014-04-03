<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	
	/*
	 * Init
	 */
	
    public function init()
    {			
		// declare app object
		$app = Yii::app();	
		
		// flash does NOT pass the session
		// thus we pass the id with a $_POST variable
		if (isset($_POST['PHPSESSID'])) { 
			$app->session->close();			
			$app->session->sessionID = $_POST['PHPSESSID'];
			$app->session->open();			
		}				
		
		// load database configuration
		foreach (Tbl_Config::model()->findAll() as $row) {
			$app->params[$row->name] = $row->value;	
		}
		
		if ($app->params['maintenance_mode']) {
			apc_clear_cache();
		}		
		
		// default language
		// if we switch languages using the dropdown
        if (isset($_POST['_lang']))
        {
			$language = trim($_POST['_lang']);
			
			// set language in app
            $app->language = $language;
						
			// set language in user session
			$app->user->setState('_lang',$language);
		// if we are logged in and have 
        } else if ($app->user->getState('_lang')) {
			
			// set language in app			
            $app->language = $app->user->getState('_lang');
		// default language in config
        } else if ($app->params['language']) {
			$app->language = $app->params['language'];
		} else {
			$app->language = 'en';	
		}
		
		// theme
		if ($app->params['backend_template']) {
			$app->theme = $app->params['backend_template'];
		}
		
		// images path		
		$root_url = realpath(Yii::getPathOfAlias('webroot').'/../').'/';				
		$root_relative_url = dirname(Yii::app()->getBaseUrl());
		
		if (!empty($root_relative_url) && substr($root_relative_url,strlen($root_relative_url)-1,1) != '/' || empty($root_relative_url)) { $root_relative_url .= '/'; }
		
		$app->params['root_url'] = $root_url;
		$app->params['root_relative_url'] = $root_relative_url;				
		
		$app->params['product_images_base_path'] = $app->params['root_url'].'images/products/';
		$app->params['product_images_base_url'] = $app->params['root_relative_url'].'images/products/';		
		
			
		// publish assets needed
		/*$am = Yii::app()->getAssetManager();
		
		// include all js files
//		if (!$app->params['maintenance_mode'] || !$app->params['includes_js_path'] = $am->getPublishedUrl($app->params['root_url'].'includes/js/',true)) {
			// change the last parameter to true if we want to overwrite js files
			$app->params['includes_js_path'] = $am->publish($app->params['root_url'].'includes/js/', true, -1, $app->params['maintenance_mode']);
//		}
		
		$app->params['includes_js_path'] .= '/';*/
		
		$app->params['includes_js_path'] = '/includes/js/';
		
		$app->params['images_path'] = '/images/';
		
		$app->params['dhtmlx_path'] = $app->params['includes_js_path'].$app->params['dhtmlx_path'];
		
		$includes_js_path = $app->params['includes_js_path'];						
		
		$cs=Yii::app()->clientScript;   
		// load jquery and jquery ui and css
		$cs->registerCoreScript('jquery');
		$cs->registerCoreScript('jquery.ui');				
		$cssCoreUrl = $cs->getCoreScriptUrl();// now that we know the core folder, register 
		$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css'); 		
		
		$cs->registerScript('jquery','
			$.ajaxSetup({
				timeout: 30000,
				error: function(jqXHR, textStatus, errorThrown){
					$.ajax({
						url: "'.CController::createUrl('site/is_logged_in').'",
						success: function(data){											
							if (data == "false") window.location.href = "'.CController::createAbsoluteUrl('site/login').'";
							else if (jqXHR && jqXHR.responseText) alert(jqXHR.responseText);
						}
					});						
				}
			});			
			

			


								
			
		', CClientScript::POS_READY);	
		
		Html::include_jquery_bubble();
    }
	/*
	public function beforeAction($action)
	{
		if ($action->id != 'login' && Yii::app()->user->isGuest) {
			$this->redirect(CController::createAbsoluteUrl('site/login'),1,302);
			return false;
		}
		
		return true;
	}*/
	
	public function updateSession()
	{
		/**
		 * Update user session data
		 */		
		if (!Yii::app()->user->isGuest)	{
			$user = Tbl_User::model()->active()->findByAttributes(array('username'=>$this->username));		
		}
	}
	
	public function actionTemplate_menu()
	{
		$app = Yii::app();
		//set content type and xml tag
		header("Content-type:text/xml");
		
		echo '<?xml version="1.0" encoding="utf-8"?>
		<menu>
			<item id="dashboard" text="'.Yii::t('components/controller', 'LABEL_MENU_DASHBOARD').'">
				<href><![CDATA['.CController::createAbsoluteUrl('site/').']]></href>
			</item>';
			//Verify if the current user have permission
			if(Yii::app()->user->checkAccess('sales')){
				echo '<item id="ms1" type="separator" />
				<item id="template_menu_sales" text="'.Yii::t('components/controller', 'LABEL_MENU_SALES').'">';
				
				echo '<item id="template_menu_orders" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_ORDERS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('orders/').']]></href>
					</item>';									
				
				echo '</item>';
			}
			//Verify if the current user have permission
			if(Yii::app()->user->checkAccess('catalog_manage_categories') or Yii::app()->user->checkAccess('catalog_manage_product')){
				echo '<item id="template_menu_ms2" type="separator" />
				<item id="template_menu_catalog" text="'.Yii::t('components/controller', 'LABEL_MENU_CATALOG').'">';
				//Verify if the current user have permission
				if(Yii::app()->user->checkAccess('catalog_manage_categories')){
					echo '<item id="template_menu_catalog_categories" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_CATEGORIES').'">
						<href><![CDATA['.CController::createAbsoluteUrl('categories/').']]></href>
					</item>';
				}				
				
				//Verify if the current user have permission
				if(Yii::app()->user->checkAccess('catalog_manage_product')){
					echo '<item id="template_menu_catalog_products" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_PRODUCTS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('products/').']]></href>
					</item>';
				}
					echo '<item id="template_menu_catalog_variants_templates" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_PRODUCT_VARIANTS_TEMPLATE').'">
					<href><![CDATA['.CController::createAbsoluteUrl('variantstemplates/').']]></href>
					</item>
					<item id="template_menu_catalog_bundled_products_groups_templates" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_BUNDLED_PRODDUCTS_GROUPS_TEMPLATE').'">
					<href><![CDATA['.CController::createAbsoluteUrl('bundledproductsgroupstemplates/').']]></href>
					</item>
					<item id="template_menu_catalog_options" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_PRODUCT_OPTIONS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('options/').']]></href>
					</item>';
					
					/*<item id="template_menu_catalog_tags" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_PRODUCT_TAGS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('tags/').']]></href>
					</item>
					<item id="template_menu_catalog_tag_templates" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_PRODUCT_TAGS_TEMPLATES').'">
						<href><![CDATA['.CController::createAbsoluteUrl('tagstemplates/').']]></href>
					</item>	*/				
				echo '</item>';
			}
			//Verify if the current user have permission
			if(Yii::app()->user->checkAccess('customers')){
				echo '<item id="template_menu_ms3" type="separator" />
				<item id="template_menu_customers" text="'.Yii::t('components/controller', 'LABEL_MENU_CUSTOMERS').'">';
				
				echo '<item id="template_menu_customers_customers" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_CUSTOMERS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('customers/').']]></href>
					</item>';
				
				echo '<item id="template_menu_settings_customer_types" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_CUSTOMERS_TYPE').'">
						<href><![CDATA['.CController::createAbsoluteUrl('customertypes/').']]></href>
					</item>';
				
				echo '</item>';
			}
			//Verify if the current user have permission
			if(Yii::app()->user->checkAccess('marketing')){
				echo '<item id="template_menu_ms4" type="separator" />
				<item id="template_menu_marketing" text="'.Yii::t('components/controller', 'LABEL_MENU_MARKETING').'">
					<item id="template_menu_marketing_rebates" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_REBATES_COUPONS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('rebatecoupon/').']]></href>
					</item>
					<item id="template_menu_marketing_subscription_contest" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_SUBSCRIPTION_CONTEST').'">
						<href><![CDATA['.CController::createAbsoluteUrl('subscriptioncontest/').']]></href>
					</item>
					<item id="template_menu_marketing_pub" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_PUB').'">
						<href><![CDATA['.CController::createAbsoluteUrl('pub/').']]></href>
					</item>
					<item id="template_menu_marketing_gift_certificates" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_GIFT_CERTIFICATES').'">
						<href><![CDATA['.CController::createAbsoluteUrl('giftcertificates/').']]></href>
					</item>
					<item id="template_menu_marketing_newsletter_subscription" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_NEWSLETTER_SUBSCRIPTION').'">
						<href><![CDATA['.CController::createAbsoluteUrl('newslettersubscription/').']]></href>
					</item>
					<item id="template_menu_marketing_news" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_NEWS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('news/').']]></href>
					</item>
				</item>';
			}
			//Verify if the current user have permission
			if(Yii::app()->user->checkAccess('cms')){
				echo '<item id="template_menu_ms5" type="separator" />
				<item id="template_menu_pages" text="'.Yii::t('components/controller', 'LABEL_MENU_PAGE_CMS').'">
					<item id="template_menu_pages_cms" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_PAGE').'">
						<href><![CDATA['.CController::createAbsoluteUrl('cmspages/').']]></href>
					</item>';
					
					include("include_main_menu/menu_pages.php");
					
				echo '						
				</item>';
			}
			
			//Verify if the current user have permission
			if(Yii::app()->user->checkAccess('reports')){
				echo '<item id="template_menu_ms6" type="separator" />
				<item id="template_menu_reports_stats" text="'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS').'">
					<item id="template_menu_reports_stats_customer_report" text="'.Yii::t('views/reports/customer_report','LABEL_TITLE').'">
						<href><![CDATA['.CController::createAbsoluteUrl('reports/customer_report').']]></href>
					</item>
					<item id="template_menu_reports_sales_report" text="'.Yii::t('components/controller','LABEL_MENU_REPORTS_STATISTICS_SALES').'">
						<href><![CDATA['.CController::createAbsoluteUrl('reports/sales_report').']]></href>
					</item>
					<item id="template_menu_reports_product_sales_report" text="'.Yii::t('components/controller','LABEL_MENU_REPORTS_STATISTICS_PRODUCT_SALES').'">
						<href><![CDATA['.CController::createAbsoluteUrl('reports/product_sales_report').']]></href>
					</item>
					<item id="template_menu_reports_tax_report" text="'.Yii::t('components/controller','LABEL_MENU_REPORTS_STATISTICS_TAX').'">
						<href><![CDATA['.CController::createAbsoluteUrl('reports/tax_report').']]></href>
					</item>
					';
					
					
					if($app->params['scorm']){
						echo '<item id="template_menu_reports_stats_scorm" text="'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_SCORM').'">
									<item id="template_menu_reports_stats_scorm_participants_course" text="'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_SCORM_PARTICIPANTS_COURSE').'"><href><![CDATA['.CController::createAbsoluteUrl('reports/scorm_report').']]></href></item>
									<item id="template_menu_reports_stats_scorm_participant" text="'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_SCORM_PARTICIPANT').'"><href><![CDATA['.CController::createAbsoluteUrl('reports/scorm_participant_report').']]></href></item>';
									
									include("include_main_menu/menu_scorm.php");
					echo '</item>';	
					
					}
					
					
					echo '<item id="template_menu_reports_stats_sales_coupon_report" text="'.Yii::t('components/controller', 'LABEL_MENU_REPORTS_STATISTICS_REBATE_COUPONS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('reports/coupon_report').']]></href>
					</item>
					
					
					
					<item id="template_menu_reports_stats_google" text="'.Yii::t('components/controller', 'LABEL_GOOGLE_ANALYTICS').'">
						<href target="blank"><![CDATA[http://www.google.com/analytics/]]></href>
					</item>
				</item>';
			}
			
			//Verify if the current user have permission
			if(Yii::app()->user->checkAccess('settings_manage_users') or Yii::app()->user->checkAccess('settings_manage_customer_types')){
				echo '<item id="template_menu_ms7" type="separator" />
				<item id="template_menu_settings" text="'.Yii::t('components/controller', 'LABEL_MENU_SETTINGS').'">
					<item id="template_menu_settings_config" text="'.Yii::t('components/controller', 'LABEL_MENU_CONFIGURATION').'">
						<href><![CDATA['.CController::createAbsoluteUrl('config/').']]></href>
					</item>
				';
				//Verify if the current user have permission
				if(Yii::app()->user->checkAccess('settings_manage_users')){
					echo '<item id="template_menu_settings_users" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_USERS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('users/').']]></href>
					</item>';
				}
				

				echo '<item id="template_menu_settings_taxes" text="'.Yii::t('components/controller', 'LABEL_MENU_TAXES').'">
					<item id="template_menu_settings_manage_taxes" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_TAXES').'">
						<href><![CDATA['.CController::createAbsoluteUrl('taxes/').']]></href>
					</item>
					<item id="template_menu_settings_manage_tax_groups" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_TAX_GROUPS').'">
						<href><![CDATA['.CController::createAbsoluteUrl('taxgroups/').']]></href>
					</item>					
					<item id="template_menu_settings_manage_tax_rules" text="'.Yii::t('components/controller', 'LABEL_MENU_MANAGE_TAX_RULES').'">
						<href><![CDATA['.CController::createAbsoluteUrl('taxrules/').']]></href>
					</item>		
				</item>';
				
				
				echo '<item id="template_menu_import_export" text="'.Yii::t('components/controller', 'LABEL_MENU_IMPORT_EXPORT').'">
					<item id="template_menu_import" text="'.Yii::t('components/controller', 'LABEL_MENU_IMPORT').'">
						<href><![CDATA['.CController::createAbsoluteUrl('import/').']]></href>
					</item>
					<item id="template_menu_export" text="'.Yii::t('components/controller', 'LABEL_MENU_EXPORT').'">
						<href><![CDATA['.CController::createAbsoluteUrl('export/').']]></href>
					</item>						
				</item>';
				
				
				echo '</item>';
			}
			echo '<item id="template_menu_ms8" type="separator" />
			<item id="template_menu_help" text="'.Yii::t('components/controller', 'LABEL_MENU_HELP').'">
				<href><![CDATA[javascript:open_help_docs(0)]]></href>
			</item>
			<item id="template_menu_ms9" type="separator" />
			<item id="template_menu_logout" text="'.Yii::t('components/controller', 'LABEL_MENU_LOGOUT').'">
				<href><![CDATA['.CController::createAbsoluteUrl('logout').']]></href>
			</item> 
			<item id="template_menu_ms10" type="separator" />';
			switch($app->params['reseller']){
				case 'Projextra':
					echo '<item id="template_menu_simple" text="'.$app->params['reseller'].'" img="toolbar/company_logo/Projextra_logo.png" imgdis="Projextra_logo.png">
					<href target="blank"><![CDATA['.$app->params['reseller_website'].']]></href>';
				break;
				case 'CPub':
					echo '<item id="template_menu_simple" text="'.$app->params['reseller'].'">
					<href target="blank"><![CDATA['.$app->params['reseller_website'].']]></href>';
				break;
				default:
					echo '<item id="template_menu_simple" text="Simple Commerce" img="toolbar/company_logo/SimpleCommerce_logo.png" imgdis="SimpleCommerce_logo.png">
					<href target="blank"><![CDATA[http://www.simplecommerce.com]]></href>';
				break;	
			}
			echo '</item>
		</menu>';
	}
		
		
	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}		
	
	/**
	 * Get help hints 
	 */
	public function actionGet_help_hints($path)
	{
		$url = 'http://www.maxvergelli.com/docs/jquery-bubble-popup-examples/test-get.html';
		$url = 'http://www.simplecommerce.com/docs/help/hints/?path='.$path.'&_lang='.Yii::app()->language;
		
		echo file_get_contents($url);
		exit;
	}
	
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column1';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();

	/**
	 * Access Rules
	 */
	
    public function accessRules()
    {
        return array(
			// allow login action for guests
			array('allow',
				'actions'=>array('login','is_logged_in','check_auth_key','is_product_sold'),
				'users'=>array('?'),
			),
			// deny all actions for guests
            array('deny',
                'actions'=>array(),
                'users'=>array('?'),
            ),
			// allow all actions for authenticated users
            array('allow',
                'actions'=>array(),
                'roles'=>array('@'),
            )
        );
    }
}