<?php 
	function hex2rgb($hex) {
	   $hex = str_replace("#", "", $hex);
	
	   if(strlen($hex) == 3) {
	      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
	      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
	      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
	      $r = hexdec(substr($hex,0,2));
	      $g = hexdec(substr($hex,2,2));
	      $b = hexdec(substr($hex,4,2));
	   }
	   $rgb = array($r, $g, $b);
	   //return implode(",", $rgb); // returns the rgb values separated by commas
	   return $rgb; // returns an array with the rgb values
	}
   include($_SERVER["DOCUMENT_ROOT"] . "/_includes/config.php");
   header('content-type: text/css');
   ob_start('ob_gzhandler');
   header('Cache-Control: max-age=31536000, must-revalidate'); 

   $rgbColorSite = "174, 35, 96";  
   if(isset($config_site["css_main_color"]) && $config_site["css_main_color"]!="") {
   		$rgbColorSite = implode(",",hex2rgb($config_site["css_main_color"]));
   }  
   
?>
/* Tools */
.hidden       { display:block !important; border:0 !important; margin:0 !important; padding:0 !important; font-size:0 !important; line-height:0 !important; width:0 !important; height:0 !important; overflow:hidden !important; }
.nobr         { white-space:nowrap !important; }
.wrap         { white-space:normal !important; }
.a-left       { text-align:left !important; }
.a-center     { text-align:center !important; }
.a-right      { text-align:right !important; }
.v-top        { vertical-align:top; }
.v-middle     { vertical-align:middle; }
.f-left,
.left         { float:left !important; }
.f-right,
.right        { float:right !important; }
.f-none       { float:none !important; }
.f-fix        { float:left; width:100%; }
.no-display   { display:none; }
.no-margin    { margin:0 !important; }
.no-padding   { padding:0 !important; }
.no-bg        { background:none !important; }
/* ======================================================================================= */


/* Layout ================================================================================ */
.wrapper {}
.page { width:1000px; margin:0 auto; padding:10px 0; text-align:left; }
.page-print { background:#fff; padding:20px; text-align:left; }
.page-empty { background:#fff; padding:20px; text-align:left; }
.page-popup { padding:20px; text-align:left; }
/* ======================================================================================= */

/* Base Columns */
.col-left { float:left; width:195px; padding:0 0 1px; }
.col-main { float:left; width:685px; padding:0 0 1px; }
.col-right { float:right; width:195px; padding:0 0 1px; }

/* 1 Column Layout */
.col1-layout .col-main { float:none; width:auto; }

/* 2 Columns Layout */
.col2-left-layout .col-main { float:right; }
.col2-right-layout .col-main {}

/* 3 Columns Layout */
.col3-layout .col-main { width:475px; margin-left:17px; }
.col3-layout .col-wrapper { float:left; width:687px; }
.col3-layout .col-wrapper .col-main { float:right; }

/* Content Columns */
.col2-set,
.col3-set,
.col4-set {
    margin-bottom:20px;
}
.col2-set .col-1 { float:left; width:48.5%; }
.col2-set .col-2 { float:right; width:48.5%; }
.col2-set .col-narrow { width:32%; }
.col2-set .col-wide { width:65%; }

.col3-set .col-1 { float:left; width:32%; }
.col3-set .col-2 { float:left; width:32%; margin-left:2%; }
.col3-set .col-3 { float:right; width:32%; }

.col4-set .col-1 { float:left; width:23.5%; }
.col4-set .col-2 { float:left; width:23.5%; margin:0 2%; }
.col4-set .col-3 { float:left; width:23.5%; }
.col4-set .col-4 { float:right; width:23.5%; }
/* ======================================================================================= */

/* Global Styles ========================================================================= */
/* Form Elements */
p.control input.checkbox,
p.control input.radio { margin-right:6px; }
/* Form Highlight */

/* Form lists */
/* Grouped fields */
.form-list { margin:0; padding:0; list-style:none; }
.form-list li { margin:0 0 25px; position:relative; }
.form-list li.comment { margin-bottom:10px; }
.form-list li.comment p { margin-bottom:0; }
.form-list li.fields { margin-bottom:0; }
.form-list li.control,
.form-list li.has-pretty-child { margin-bottom:10px; }
.form-list label { display:inline; float:left; position:relative; z-index:0; }
.form-list label.required {}
.form-list label.required em { font-style:normal; }
.form-list li.control label,
.form-list li.has-pretty-child label { float:none; vertical-align:top; line-height:1; }
.form-list li.control input.radio,
.form-list li.control input.checkbox,
.form-list li.has-pretty-child input.radio,
.form-list li.has-pretty-child input.checkbox { margin-right:6px; margin-top:-2px; }
.form-list li.control .input-box { clear:none; display:inline; width:auto; }
.form-list .input-box { display:block; clear:both; margin-bottom:0; }
.form-list .field { position:relative; margin-bottom:25px; }
.form-list input.input-text {  }
.form-list textarea { height:10em; margin-bottom:0; }
.form-list select { }
.form-list li.additional-row { border-top:1px solid #ccc; margin-top:10px; padding-top:7px; }
.form-list li.additional-row .btn-remove { float:right; margin:5px 0 0; }
.form-list .input-range input.input-text {  }
/* Customer */
.form-list .customer-name-prefix .input-box,
.form-list .customer-name-suffix .input-box,
.form-list .customer-name-prefix-suffix .input-box,
.form-list .customer-name-prefix-middlename .input-box,
.form-list .customer-name-middlename-suffix .input-box,
.form-list .customer-name-prefix-middlename-suffix .input-box { width:auto; }

.form-list .customer-dob .dob-month, 
.form-list .customer-dob .dob-day,
.form-list .customer-dob .dob-year { float:left; }
.form-list .customer-dob input.input-text { display:block; }
.form-list .customer-dob label {  }
.form-list .customer-dob .dob-day,
.form-list .customer-dob .dob-month {  }
.form-list .customer-dob .dob-year { }
.form-list .customer-dob .dob-month input.input-text, 
.form-list .customer-dob .dob-day input.input-text,
.form-list .customer-dob .dob-year input.input-text { text-align:center; }

.buttons-set { clear:both; margin:20px 0 0; padding:0; }
.buttons-set .back-link { float:left; margin-right:15px; }
.buttons-set button.button { margin-bottom:10px; }
.buttons-set p.required { margin:0 0 5px; display:none; }

.buttons-set-order {}

.fieldset { }
.fieldset .legend { }

/* Form Validation */
.validation-advice { clear:both; min-height:13px; margin:3px 0 15px; padding-left:17px; font-size:11px; line-height:13px; background:url(/_images/icons/validation_advice_bg.gif) 2px 1px no-repeat; color:#f00; }
.validation-failed { border:1px dashed #f00 !important; background:#faebe7 !important; }
.validation-passed {}
p.required { font-size:10px; text-align:right; color:#f00; }
/* Expiration date and CVV number validation fix */
.v-fix { float:left; }
.v-fix .validation-advice { display:block; width:12em; margin-right:-12em; position:relative; }

/* Global Messages  */
.success { color:#3d6611; font-weight:bold; }
.error { color:#B84947;  }
.notice { color:#ccc; }

.messages,
.messages ul { list-style:none !important; margin:0 !important; padding:0 !important; }
.messages li li { margin:0 0 3px; }

/* BreadCrumbs */
.breadcrumbs { padding:15px 0; }
.breadcrumbs ul { padding:0; margin:0 20px; }
.breadcrumbs li { display:inline; }
.breadcrumbs strong { font-weight:normal; }
.breadcrumbs span { margin:0 8px; }

/* Page Heading */
.page-title { padding:0 0 5px; margin:0 0 10px; }
.page-title h1,
.page-title h2 { font-size:18px; color:#000; text-transform: none; }
.page-title .separator { margin:0 3px; }
.page-title .link-rss { float:right; }
.title-buttons { position:relative; }
.title-buttons h1,
.title-buttons h2,
.title-buttons h3,
.title-buttons h4,
.title-buttons h5,
.title-buttons h6 { float:left; margin-right: 30px; }
.title-buttons a,
.title-buttons .separator { margin-top:0; display:inline-block; }

.subtitle,
.sub-title { clear:both; }

/* Pager */
.pager { margin:15px 0; text-align:center; padding:15px 0; }
.pager .amount { float:left; }
.pager .limiter { float:right; }
.pager .limiter label { float:left; font-size:13px; padding:6px 0; margin-right:10px; display:inline; }
.pager .pages { margin:0 0; }
.pager .pages ol { display:inline; margin:0; padding:0; float:right; }
.pager .pages li { display:inline; }
.pager .pages .current {}

/* Sorter */
.sorter { padding:0; margin:0; }
.sorter .actions { float:left; }
.sorter .view-mode { float:right; }
.sorter .sort-by { float:left; }
.sorter .link-feed {}

/* Toolbar */
.toolbar { margin-bottom:0; }
.toolbar .pager {}
.toolbar .sorter {}
.toolbar-bottom { margin-top:0; }
.toolbar .pager .pages ol { float:none; }

/* Data Table */
.data-table { width:100%; }
.data-table th { line-height:20px; padding:10px; font-weight:bold; text-transform:uppercase; font-size:15px; }
.data-table td { line-height:20px; padding:10px; }
.data-table th .tax-flag { white-space:nowrap; font-weight:normal; }
.data-table td.label,
.data-table th.label { font-weight:bold; }
.data-table td.value {}
.data-table input, data-table select, data-table textarea { margin:3px;}
.data-table p { margin:10px 0; }
.data-table .description { margin:10px 0; }

/* Shopping cart total summary row expandable to details */
tr.summary-total { cursor:pointer; }
tr.summary-total td {}
tr.summary-total .summary-collapse { float:right; text-align:right; padding-left:20px; background:url(/_images/icons/bkg_collapse.gif) 0 4px no-repeat; cursor:pointer; }
tr.show-details .summary-collapse { background-position:0 -53px; }
tr.show-details td {}
tr.summary-details td { font-size:11px; background-color:#dae1e4; color:#626465; }
tr.summary-details-first td { border-top:1px solid #d2d8db; }
tr.summary-details-excluded { font-style:italic; }

/* Shopping cart tax info */
.cart-tax-info { display:block; }
.cart-tax-info,
.cart-tax-info .cart-price { padding-right:20px; }
.cart-tax-total { display:block; padding-right:20px; background:url(/_images/icons/bkg_collapse.gif) 100% 4px no-repeat; cursor:pointer; }
.cart-tax-info .price,
.cart-tax-total .price { display:inline !important; font-weight:normal !important; }
.cart-tax-total-expanded { background-position:100% -53px; }

/* Class: std - styles for admin-controlled content */
.std .subtitle { padding:0; }
.std ol.ol { list-style:decimal outside; padding-left:1.5em; }
.std ul.disc { list-style:disc outside; padding-left:18px; margin:0 0 10px; }
.std dl dt { font-weight:bold; }
.std dl dd { margin:0 0 10px; }
.std ul,
.std ol,
.std dl,
.std p,
.std address,
.std blockquote { margin:0 0 1em; padding:0; }
.std ul { list-style:disc outside; padding-left:1.5em; }
.std ol { list-style:decimal outside; padding-left:1.5em; }
.std ul ul { list-style-type:circle; }
.std ul ul,
.std ol ol,
.std ul ol,
.std ol ul { margin:.5em 0; }
.std dt { font-weight:bold; }
.std dd { padding:0 0 0 1.5em; }
.std blockquote { font-style:italic; padding:0 0 0 1.5em; }
.std address { font-style:normal; }
.std b,
.std strong { font-weight:bold; }
.std i,
.std em { font-style:italic; }

/* Misc */
.links li { display:inline; }
.links li.first { padding-left:0 !important; }
.links li.last { background:none !important; padding-right:0 !important; }

.link-add,
.link-cart,
.link-wishlist,
.link-reorder,
.link-compare { font-weight:bold; font-size:13px; font-family: Arial, sans-serif; }
.link-print { background:url(/_images/icons/i_print.gif) 0 2px no-repeat; padding:2px 0 2px 25px; }
.link-rss { background:url(/_images/icons/i_rss.gif) 0 1px no-repeat; padding-left:18px; white-space:nowrap; }
.btn-remove { display:block; width:11px; height:11px; font-size:0; line-height:0; background-position: 0 0; background-repeat: no-repeat; text-indent:-999em; overflow:hidden; }
.btn-remove2 { display:block; width:16px; height:16px; font-size:0; line-height:0; background-position: 0 0; background-repeat: no-repeat; text-indent:-999em; overflow:hidden; }
.btn-edit    { display:block; width:11px; height:11px; font-size:0; line-height:0; background-position: 0 0; background-repeat: no-repeat; text-indent:-999em; overflow:hidden; }

.cards-list dt { margin:5px 0 0; }
.cards-list .offset { padding:2px 0 2px 20px; }


.separator { margin:0 3px; }

.divider { clear:both; display:block; font-size:0; line-height:0; height:1px; margin:10px 0; background:#ddd; text-indent:-999em; overflow:hidden; }

/* Noscript Notice */
.noscript { border:1px solid #ddd; border-width:0 0 1px; background:#ffff90; font-size:12px; line-height:1.25; text-align:center; color:#2f2f2f; }
.noscript .noscript-inner { width:1000px; margin:0 auto; padding:12px 0 12px; background:url(/_images/icons/i_notice.gif) 20px 50% no-repeat; }
.noscript p { margin:0; }

/* Demo Notice */
.demo-notice { margin:0; padding:6px 10px; background:#d75f07; font-size:12px; line-height:1.15; text-align:center; color:#fff; }

/* Cookie Notice */
.notice-cookie { border-bottom:1px solid #cfcfcf; background:#ffff90; font-size:12px; line-height:1.25; text-align:center; color:#2f2f2f; }
.notice-cookie .notice-inner { width:870px; margin:0 auto; padding:12px 0 12px 80px; background:url(/_images/icons/i_notice.gif) 20px 25px no-repeat; text-align:left; }
.notice-cookie .notice-inner p { margin:0 0 10px; border:1px dotted #cccc73; padding:10px; }
.notice-cookie .notice-inner .actions { }

/* ======================================================================================= */


/* Header ================================================================================ */
.logo { float:left; }
.header-container {}
.header {  }
.header .logo { float:left; text-decoration:none !important; }
.header .logo strong { position:absolute; top:-999em; left:-999em; width:0; height:0; font-size:0; line-height:0; text-indent:-999em; overflow:hidden; }
.header h1.logo { margin:0; padding:0; font-size:30px; }
.header .welcome-msg { font-weight:bold; text-align:right; }
.header .welcome-msg a {}
.header .links { float:right; }
.header .form-search { text-align:right; }
.header .form-search .search-autocomplete { z-index:999; }
.header .form-search .search-autocomplete ul { border:1px solid #ddd; background-color:#fff; }
.header .form-search .search-autocomplete li { padding:3px; border-bottom:1px solid #ddd; cursor:pointer; }
.header .form-search .search-autocomplete li .amount { float:right; font-weight:bold; }
.header .form-search .search-autocomplete li.selected {}
.header .form-language { clear:both; text-align:right; }
.header-container .top-container { clear:both; text-align:right; }

/********** < Navigation */
#nav { list-style:none; margin:0; padding:0; }

/* All Levels */
#nav li { text-align:left; position:relative; }
#nav li.over { z-index:998; }
#nav li.parent {}
#nav li a { display:block; text-decoration:none; }
#nav li a:hover { text-decoration:none; }
#nav li a span { display:block; white-space:nowrap; cursor:pointer; }
#nav li ul a span { white-space:normal; }

/* 1st Level */
#nav li { float:left; margin:0; padding:0; }
#nav li a { float:left; text-transform:uppercase; padding:13px 25px; }
#nav li a:hover { }
#nav li.over a,
#nav li.active a { }

/* 2nd Level */
#nav ul,
#nav div { position:absolute; width:15em; top:25px; left:-10000px; list-style:none; padding:0; margin:0; -webkit-box-shadow:0 3px 5px rgba(0, 0, 0, 0.2); -moz-box-shadow:0 3px 5px rgba(0, 0, 0, 0.2); box-shadow:0 3px 5px rgba(0, 0, 0, 0.2); }
#nav div ul { position:static; width:auto; border:none; }

/* 3rd+ leven */
#nav ul ul,
#nav ul div { top:3px; }

#nav ul li { float:none; border-top:1px solid rgba(200, 200, 200, 0.2); border-bottom:1px solid rgba(255, 255, 255, 0.5); }
#nav ul li.last { border-bottom:0; }
#nav ul li a { float:none; padding:8px 10px; background-color:transparent !important; text-transform:uppercase; }
#nav ul li a:hover {  }
#nav ul li.active > a,
#nav ul li.over > a { }

/* Show menu */
#nav li ul.shown-sub,
#nav li div.shown-sub { left:0; z-index:999; }
#nav li .shown-sub ul.shown-sub,
#nav li .shown-sub li div.shown-sub { left:14em; }

/* CUSTOM LINKS */
#nav-links { list-style:none; margin:0; padding:0; float:left; }
/* All Levels */
#nav-links li { text-align:left; position:relative; }
#nav-links li.over { z-index:998; }
#nav-links li.parent {}
#nav-links li a { display:block; text-decoration:none; }
#nav-links li a:hover { text-decoration:none; }
#nav-links li a span { display:block; white-space:nowrap; cursor:pointer; }
#nav-links li ul a span { white-space:normal; }

/* 1st Level */
#nav-links li { float:left; margin:0; padding:0; }
#nav-links li a { float:left; text-transform:uppercase; padding:13px 25px; }
#nav-links li a:hover { }
#nav-links li.over a,
#nav-links li.active a { }

/* 2nd Level */
#nav-links ul,
#nav-links div { position:absolute; width:15em; top:25px; left:-10000px; list-style:none; padding:0; margin:0; -webkit-box-shadow:0 3px 5px rgba(0, 0, 0, 0.2); -moz-box-shadow:0 3px 5px rgba(0, 0, 0, 0.2); box-shadow:0 3px 5px rgba(0, 0, 0, 0.2); }
#nav-links div ul { position:static; width:auto; border:none; }

/* 3rd+ leven */
#nav-links ul ul,
#nav-links ul div { top:3px; }

#nav-links ul li { float:none; border-top:1px solid rgba(200, 200, 200, 0.2); border-bottom:1px solid rgba(255, 255, 255, 0.5); }
#nav-links ul li.last { border-bottom:0; }
#nav-links ul li a { float:none; padding:8px 10px; background-color: transparent !important; text-transform:uppercase; }
#nav-links ul li a:hover {  }
#nav-links ul li.active > a,
#nav-links ul li.over > a { }

/* Show menu */
#nav-links li ul.shown-sub,
#nav-links li div.shown-sub { left:0; z-index:999; }
#nav-links li .shown-sub ul.shown-sub,
#nav-links li .shown-sub li div.shown-sub { left:14em; }

/********** Navigation > */
/* ======================================================================================= */


/* Sidebar =============================================================================== */
.block { margin:0 0 50px; }
.block .block-title { font-size:22px; padding:5px 0; margin-top:0; margin-bottom:10px; line-height:1.2; }
.block .block-title strong { font-weight:normal; }
.block .block-title a { text-decoration:none !important; }
.block .block-content { padding:0; }
.block .block-content .item { padding:5px 0; }
.block .btn-remove,
.block .btn-edit { float:right;}
.block .actions { text-align:left; padding: 10px; }
.block .actions a { float:right; margin-left: 10px; margin-bottom: 10px; font-weight:bold; }
.block .empty {}

.block li.odd {}
.block li.even {}

/* Mini Products List */
.mini-products-list { list-style:none; margin-left:0; padding-left:0; }
.mini-products-list li { padding:0; display:block; margin-bottom:20px; }
.mini-products-list .product-image { float:left; width:85px; padding:0; margin:0; }
.mini-products-list .product-details { margin-left:100px; }
.mini-products-list .product-name a { font-size:17px; font-weight:bold; }
.mini-products-list .product-details h4 { font-size:1em; font-weight:bold; margin:0; }
.mini-products-list .item { position:relative; }
.block-cart .mini-products-list .product-details .product-name,
.block-cart .mini-products-list .product-details .nobr small { word-wrap:break-word; }
.block-cart .mini-products-list .product-details .nobr { white-space:normal !important; }

/* Block: Account */
.block-account {}

/* Block: Currency Switcher */
.block-currency {}
.block-currency select { width:100%; border:1px solid #888; }

/* Block: Layered Navigation */
.block-layered-nav {}
.block-layered-nav dt { font-weight:bold; padding:16px 50px 16px 20px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; position:relative; }
.block-layered-nav dd { margin:0; padding:0; }
.block-layered-nav dd ol { position:relative; }
.block-layered-nav .currently {}
.block-layered-nav .btn-remove { float:right; }

.block-list .price { color: inherit; font-family: inherit; font-size:18px; }

/* Block: Cart */
.block-cart {}
.block-cart .summary {}
.block-cart .amount {}
.block-cart .subtotal { text-align:center; }
.block-cart .actions .paypal-logo { float:left; width:100%; margin:3px 0 0; text-align:right; }
.block-cart .actions .paypal-logo .paypal-or { clear:both; display:block; padding:0 55px 5px 0; }

/* Block: Wishlist */
.block-wishlist {}

/* Block: Related */
.block-related {}
.block-related li { padding:5px 0; }
.block-related input.checkbox { position:absolute; left:85px; top:14px; z-index:10; }
.block-related .product { margin-left:20px; }
.block-related .product .product-image { float:left; margin-right:-65px; }
.block-related .product .product-details { margin-left:65px; }
.block-related .mini-products-list .product-details { margin-left:115px; }
.block-related .mini-products-list .product-image { width:100px; margin:0; }

/* Block: Compare Products */
.block-compare {}
.block-compare li { padding:5px 0; }

/* Block: Recently Viewed */
.block-viewed {}

/* Block: Recently Compared */
.block-compared {}

/* Block: Poll */
.block-poll label { margin-bottom:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.block-poll input.radio { float:left; margin:1px -18px 0 0; }
.block-poll .label { display:block; margin-left:18px; }
.block-poll li { padding:3px 9px; }
.block-poll .actions { margin:5px 0 0; }
.block-poll .answer { font-weight:bold; }
.block-poll .votes { float:right; margin-left:10px; }

/* Block: Tags */
.block-tags ul,
.block-tags li { display:inline; }

/* Block: Subscribe */
.block-subscribe {}

/* Block: Reorder */
.block-reorder {}
.block-reorder li { padding:5px 0; }
.block-reorder input.checkbox { float:left; margin:3px -20px 0 0; }
.block-reorder .product-name { margin-left:20px; }

/* Block: Banner */
.block-banner {}
.block-banner .block-content { text-align:center; }

/* Block: Login */
.block-login label { font-weight:bold; color:#666; }
.block-login input.input-text { display:block; width:167px; margin:3px 0; }

/* Paypal */
.sidebar .paypal-logo { display:block; margin:10px 0 30px; text-align:center; }
.sidebar .paypal-logo a { float:none; }
/* ======================================================================================= */


/* Category Page ========================================================================= */
.category-title { border:0; margin:0 0 7px; }
.category-image { width:100%; overflow:hidden; margin:0 0 10px; text-align:center; }
.category-image img {}
.category-description { margin:0 0 10px; }
.category-products {}

/* View Type: Grid */
.products-grid { position:relative; }
.products-grid.last { border-bottom:0; }
.products-grid li.item { float:left; position:relative; }
.products-grid .product-image { position:relative; display:block; margin:0 auto 10px; }
.products-grid .product-name { margin:0 0 15px; font-size:19px; line-height:20px; height: 45px;}
.products-grid .product-name a { padding:0; }
.products-grid .price-box {}
.products-grid .availability { line-height:21px; }
.products-grid .actions { }
.products-grid .item-inner { padding: 20px; }
.products-grid .item-active .item-inner { padding: 20px; }

/* View Type: List */
.products-list li.item { padding:30px 0 30px; }
.products-list li.item.last { border-bottom:0; }
.products-list .product-image { float:left; width:228px; margin:0 0 10px; position:relative; }
.products-list .product-shop { margin-left:285px; }
.products-list .product-name { margin:0 0 5px; font-weight:bold; font-size:20px; line-height:25px; }
.products-list .product-name a {}
.products-list .availability { float:left; margin:3px 0 0; }
.products-list .desc { padding:6px 0 0; margin:0 0 25px; line-height:1.35; }
.products-list .ratings { float:left; width:100%; }
.products-list .desc .link-learn { font-size:13px; margin-top:5px; display:block; }
/* ======================================================================================= */


/* Product View ========================================================================== */
/* Rating */
.no-rating { margin:10px 0; }

.ratings { font-size:14px; line-height:18px; margin:10px 0; }
.ratings strong { float:left; margin:0 3px 0 0; }
.ratings .rating-links { margin:0; }
.ratings .rating-links .separator { margin:0 2px; }
.ratings .rating-links a { color:#cccccc; }
.ratings dt {}
.ratings dd {}
.rating-box { width:95px; height:18px; font-size:0; line-height:0; background:url(/_images/icons/bkg_rating.png) 0 0 repeat-x; text-indent:-999em; overflow:hidden; margin-bottom:5px; }
.rating-box .rating { float:left; height:18px; background:url(/_images/icons/bkg_rating.png) 0 100% repeat-x; }
.ratings .rating-box { float:left; margin-right:8px; }
.ratings .amount { color:#cccccc; }

.ratings-table th,
.ratings-table td { font-size:13px; line-height:1.15; padding:3px 0; }
.ratings-table th { font-weight:bold; padding-right:8px; }

/* Availability, Brand */
.availability,
.product-brand { margin:0 0 5px; font-weight:bold; }
.availability span,
.product-brand span { font-weight:normal; }
.availability.in-stock span {}
.availability.out-of-stock span { color:#d83820; }

.availability-only { margin:0 0 7px; }
.availability-only a { background:url(/_images/icons/i_availability_only_arrow.gif) 100% 0 no-repeat; cursor:pointer; padding-right:15px; }
.availability-only .expanded { background-position:100% -15px; }
.availability-only strong {}

.availability-only-details { margin:0 0 7px; }
.availability-only-details th { background:#f2f2f2; font-size:10px; padding:0 8px; }
.availability-only-details td { border-bottom:1px solid #ddd; font-size:11px; padding:2px 8px 1px; }
.availability-only-details tr.odd td.last {}

/* Email to a Friend */
.email-friend { margin-left:5px; margin-bottom:20px; float:left; }
.email-friend span { display:none; }

/* Alerts */
.alert-price {}
.alert-stock {}

/********** < Product Prices */
.price { white-space:nowrap !important; }
.price .sub { font-size:75%; position:relative; bottom:0; }

.price-box { margin-bottom: 5px; }
.price-box .price { font-weight:bold; }

/* Regular price */
.regular-price {}
.regular-price .price { font-weight:bold; }

/* Old price */
.old-price {}
.old-price .price-label { white-space:nowrap; }
.old-price .price { text-decoration:line-through; }

/* Special price */
.special-price {}
.special-price .price-label { font-weight:bold; white-space:nowrap; }
.special-price .price { font-weight:bold; }

/* Minimal price (as low as) */
.minimal-price {}
.minimal-price .price-label { font-weight:bold; white-space:nowrap; }

.minimal-price-link { display:block; }
.minimal-price-link .label {}
.minimal-price-link .price {  }

/* Excluding tax */
.price-excluding-tax { display:block; }
.price-excluding-tax .label { white-space:nowrap; }
.price-excluding-tax .price { font-weight:normal; }

/* Including tax */
.price-including-tax { display:block; }
.price-including-tax .label { white-space:nowrap; }
.price-including-tax .price { font-weight:bold; }

/* Configured price */
.configured-price {}
.configured-price .price-label { font-weight:bold; white-space:nowrap; }
.configured-price .price { font-weight:bold; }

/* FPT */
.weee { display:block; font-size:11px; color:#444; }
.weee .price { font-size:11px; font-weight:normal; }

/* Excl tax (for order tables) */
.price-excl-tax  { display:block; }
.price-excl-tax .label { display:block; white-space:nowrap; }
.price-excl-tax .price { display:block; }

/* Incl tax (for order tables) */
.price-incl-tax { display:block; }
.price-incl-tax .label { display:block; white-space:nowrap; }
.price-incl-tax .price { display:block; font-weight:bold; }

/* Price range */
.price-from { margin-bottom:2px; }
.price-from .price-label { font-weight:bold; white-space:nowrap; }

.price-to {}
.price-to .price-label { font-weight:bold; white-space:nowrap; }

/* Price notice next to the options */
.price-notice { padding-left:5px; }
.price-notice .price { padding-left:5px; font-weight:bold; }

/* Price as configured */
.price-as-configured {}
.price-as-configured .price-label { font-weight:bold; white-space:nowrap; }

.price-box-bundle {}
/********** Product Prices > */

/* Tier Prices */
.tier-prices .price { font-weight:bold; }
.tier-prices .benefit {}

.tier-prices-grouped {}

/* Add to Links */
.add-to-links .separator { display:none; }

/* Add to Cart */
.add-to-cart label { display:none; float:left; margin-right:5px; }
.add-to-cart .qty { margin:0; font-size:25px; height:auto; line-height:40px; font-weight:bold; width:80px; padding:4px 40px 4px 15px; text-align:center; }
.add-to-cart button.button { margin-left:5px; }
.add-to-cart .button-up,
.add-to-cart .button-down { position:absolute; left:111px; }
.add-to-cart .button-up { top:0; }
.add-to-cart .button-down { bottom:0; }
.add-to-cart button.btn-cart { font-size:15px; padding:10px 20px; margin-left:15px; }
.add-to-cart button.btn-cart.margin-none { margin-left:0; }
.add-to-cart .paypal-logo { clear:left; text-align:right; }
.add-to-cart .paypal-logo .paypal-or { clear:both; display:block; margin:5px 60px 5px 0; }
.product-view .add-to-cart .paypal-logo { margin:0; }

/* Add to Links + Add to Cart */
.add-to-box { margin:25px 0; }
.add-to-box .add-to-cart,
.product-options-bottom .add-to-cart { margin:0 0 25px; position:relative; }
.product-options-bottom .add-to-cart { margin-bottom:0; }
.add-to-box .or { float:left; margin:0 10px; }
.add-to-box .add-to-links { float:left; padding:0; margin:0 0 20px; list-style:none; }
.add-to-box .add-to-links li { display:inline; }
.add-to-links span { display:none; }

.product-view { margin-top:20px; }

.product-essential {}

.product-collateral .box-collateral { margin:0 0 15px; }

/* Product Images */
.product-view .product-img-box { }
.col3-layout .product-view .product-img-box { float:none; margin:0 auto; }

.product-image-popup { margin:0 auto; }
.product-image-popup .buttons-set { float:right; clear:none; border:0; margin:0; padding:0; }
.product-image-popup .nav { margin:0 100px; text-align:center; }
.product-image-popup .image { display:block; }
.product-image-popup .image-label {}

/* Product Shop */
.product-view .product-shop { }
.col1-layout .product-view .product-shop { }
.col3-layout .product-view .product-shop { }
.product-view .product-name {}
.product-view .short-description {}

/* Product Options */
.product-options { padding:0; margin:0; }
.product-options dt { margin-top:20px; }
.product-options dt label { font-weight:normal; font-size:15px; }
.product-options dt label em { margin-right:3px; }
.product-options dt .qty-holder { float:right; }
.product-options dt .qty-holder label { vertical-align:middle; }
.product-options dt .qty-disabled { background:none; border:0; padding:3px; color:#000; }
.product-options dd { margin:10px 0; }
.product-options dl { margin:5px 0; }
.product-options dl.last dd.last {}
.product-options dd input.datetime-picker { width:150px; }
.product-options dd .time-picker { display:-moz-inline-box; display:inline-block; padding:2px 0; vertical-align:middle; }
.product-options .options-list { list-style:none; padding:0; margin:0; }
.product-options .options-list li { margin:12px 0; }
.product-options .options-list input.radio { margin:-2px 10px 0 0; }
.product-options .options-list input.checkbox { margin:-2px 10px 0 0; }
.product-options .options-list .label { display:inline; margin-left:0; padding:0; }
.product-options ul.validation-failed { padding:0 7px; }
.product-options p.required { padding:0; display:none; }
.product-options label { display:inline; margin-bottom:0; line-height:17px; }
.product-options .qty-holder { display:block; margin:8px 0; }
.product-options .label,
.product-options .badge { background-color:transparent; text-shadow:none; }
.product-options .qty { margin-bottom:0; width:30px; }

.product-options-bottom { padding:25px 0; }
.product-options-bottom .price-box { margin:10px 0; }

/* Grouped Product */
.product-view .grouped-items-table { margin-top:20px; }

/* Block: Description */
.product-view .box-description {}

/* Block: Additional */
.product-view .box-additional {}

/* Block: Upsell */
.product-view .box-up-sell {}
.product-view .box-up-sell .products-grid td { width:25%; }

/* Block: Tags */
.product-view .box-tags {}
.product-view .box-tags .form-add label { float:left; line-height:33px; }
.product-view .box-tags .form-add .input-box { float:left; margin:0 0 0 10px; }
.product-view .box-tags .form-add input.input-text { width:200px; }
.product-view .box-tags .form-add p { clear:both; }

/* Block: Reviews */
.product-view .box-reviews {}
.product-view .box-reviews .form-add {}

/* Send a Friend */
.send-friend {}
/* ======================================================================================= */


/* Content Styles ================================================================= */
.product-name { margin-bottom:5px; }
.product-name a {}

/* Product Tags */
.tags-list li { display:inline; }

/* Advanced Search */
.advanced-search {}
.advanced-search-amount {}
.advanced-search-summary {}

/* CMS Home Page */
.cms-home .subtitle {}
.cms-index-index .subtitle {}

/* Sitemap */
.page-sitemap .links { text-align:right; margin:0 8px 10px 0; }
.page-sitemap .links a { text-decoration:none; position:relative; margin-top:20px; }
.page-sitemap .links a:hover { text-decoration:underline; }
.page-sitemap .sitemap { margin:12px; }
.page-sitemap .sitemap a {}
.page-sitemap .sitemap li { margin:3px 0; }
.page-sitemap .sitemap li.level-0 { margin:10px 0 0; font-weight:bold; }
.page-sitemap .sitemap li.level-0 a {}

/* RSS */
.rss-title h1 { background:url(/_images/icons/i_rss-big.png) 0 4px no-repeat; padding-left:27px; }
.rss-table .link-rss { display:block; line-height:1.35; background-position:0 2px; }
/* ======================================================================================= */


/* Shopping Cart ========================================================================= */
.cart {}

/* Checkout Types */
.cart .checkout-types { float:right; text-align:right; list-style:none; }
.cart .title-buttons .checkout-types li { float:left; margin:0 0 5px 5px; }
.cart .checkout-types .paypal-or { margin:0 8px; line-height:2.3; }
.cart .totals .checkout-types .paypal-or { clear:both; display:block; padding:3px 55px 8px 0; line-height:1.0; font-size:11px; }
.cart-totals .checkout-types { margin-top: 20px; }
/* Shopping Cart Table */
.cart-table .item-msg { font-size:10px; }

/* Shopping Cart Collateral boxes */
.cart .cart-collaterals { padding:25px 0 0; }

/* Discount Codes & Estimate Shipping and Tax Boxes */
.cart .discount,
.cart .shipping {}

/* Shopping Cart Totals */
.cart .totals { }
.cart .totals table { width:100%; }
.cart .totals table th,
.cart .totals table td { padding:15px 20px; text-transform:uppercase; font-weight:bold; }
.cart .totals table th { font-weight:bold; }
.data-table .grand-total th,
.data-table .grand-total td,
.cart .totals table tfoot th,
.cart .totals table tfoot td { padding: 25px 10px; font-size:22px; text-transform:uppercase; }
.data-table .grand-total th .price,
.data-table .grand-total td .price,
.cart .totals table tfoot th .price,
.cart .totals table tfoot td .price { font-size:22px; }

/* Options Tool Tip */
.item-options { margin-bottom:15px; }
.item-options dt { font-weight:normal; float:left; clear:both; }
.item-options dd { float:left; margin-left:0; padding-left:10px; }
.item-options .price { margin-left:5px; }
.truncated { cursor:help; }
.truncated a.dots { cursor:help; }
.truncated a.details { cursor:help; }
.truncated .truncated_full_value { position:relative; z-index:999; }
.truncated .truncated_full_value .item-options { position:absolute; top:-99999em; left:-99999em; z-index:999; width:150px; padding:8px; border:1px solid #ddd; background-color:#f6f6f6; }
.truncated .truncated_full_value .item-options > p { font-weight:bold; text-transform:uppercase; }
.truncated .show .item-options { top:20px; left:0; }
.col-left .truncated .show .item-options { left:30px; top:7px; }
.col-right .truncated .show .item-options  { left:-240px; top:7px; }
/* ======================================================================================= */


/* Checkout ============================================================================== */
/********** < Common Checkout Styles */
/* Shipping and Payment methods */
.sp-methods dt { font-weight:bold; margin:0 0 15px; }
.sp-methods dd { margin-bottom:20px; margin-left:0; }
.sp-methods .price { font-weight:bold; }
.sp-methods .form-list { padding-left:20px; }
.sp-methods select.month { width:350px; margin-right:10px; }
.sp-methods select.year { width:120px; padding-left:15px; }
.sp-methods input.cvv { width:350px !important; }

.sp-methods .checkmo-list li { margin:0 0 5px; }
.sp-methods .checkmo-list label { width:135px; padding-right:10px; text-align:right; }
.sp-methods .checkmo-list address { float:left; }

.sp-methods .centinel-logos a { margin-right:3px; }
.sp-methods .centinel-logos img { vertical-align:middle; }

.sp-methods .release-amounts { margin:0.5em 0 1em; }
.sp-methods .release-amounts button { float:left; margin:5px 10px 0 0; }

.please-wait { float:right; margin-right:5px; }
.please-wait img { vertical-align:middle; }
.cvv-what-is-this { cursor:help; margin-left:5px; white-space:nowrap; }

/* Tooltip */
.tool-tip { border:1px solid #ddd; background-color:#f6f6f6; padding:5px; position:absolute; z-index:9999; }
.tool-tip .btn-close { text-align:right; }
.tool-tip .btn-close a { display:block; margin:0 0 0 auto; width:15px; height:15px; background:url(/_images/icons/btn_window_close.gif) 100% 0 no-repeat; text-align:left; text-indent:-999em; overflow:hidden; }
.tool-tip .tool-tip-content { padding:5px; }

/* Gift Messages */
.gift-messages {}
.gift-messages-form { border:1px solid #ddd; background-color:#f5f5f5; }
.gift-messages-form { position:relative; }
.gift-messages-form label { float:none !important; position:static !important; }
.gift-messages-form h4 {}
.gift-messages-form .whole-order {}
.gift-messages-form .item { margin:0 0 10px; }
.gift-messages-form .item .product-img-box { float:left; width:75px; }
.gift-messages-form .item .product-image { margin:0 0 7px; }
.gift-messages-form .item .number { margin:0; font-weight:bold; text-align:center; }
.gift-messages-form .item .details { margin-left:90px; }
.gift-messages-form .item .details .product-name {}

.gift-message-link { display:block; background:url(/_images/icons/bkg_collapse.gif) 0 4px no-repeat; padding-left:20px; }
.gift-message-link.expanded { background-position:0 -53px; }
.gift-message-row {}
.gift-message-row .btn-close { float:right; }
.gift-message dt strong { font-weight:bold; }

/* Checkout Agreements */
.checkout-agreements {}
.checkout-agreements li { margin:10px 0; }
.checkout-agreements .agreement-content { border:1px solid #ddd; background-color:#f6f6f6; padding:15px; height:10em; overflow:auto; }
.checkout-agreements .agree { padding:6px; }

/* Centinel */
.centinel {}
.centinel .authentication { border:1px solid #ddd; background:#fff; }
.centinel .authentication iframe { width:99%; height:400px; background:transparent !important; margin:0 !important; padding:0 !important; border:0 !important; }

/* Generic Info Set */
.info-set {}
/********** Common Checkout Styles > */

/* One Page Checkout */
.block-progress {}
.block-progress dt { font-weight:bold; }
.block-progress dt.complete,
.block-progress dd.complete { background-color:#f6f6f6; }

.opc { position:relative; }
.opc ul,
.opc ol { margin:0; padding:0; list-style:none; }
.opc li.section { border-bottom:0; }

.opc .buttons-set.disabled button.button { display:none; }
.opc .buttons-set .please-wait { height:21px; line-height:21px; }

.opc .step-title { position:relative; padding:15px 20px; font-size:18px; font-weight:bold; line-height:1.5; margin-bottom:10px; }
.opc .step-title .number { float:left; margin-right:5px; }
.opc .step-title h2 { float:left; text-transform:uppercase; font-size:18px; font-weight:bold; line-height:1.5; margin:0; padding:0 30px 0 0; cursor:pointer; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; }
.opc .step-title a { }

.opc .allow .step-title {  }

.opc .active {}
.opc .active .step-title {  }

.opc .step { padding:40px 0 70px; position:relative; }
.opc .step form { margin-bottom:0; }
.opc .step .tool-tip { }

.opc .order-review {}
.opc .order-review .authentication {}
.opc .order-review .warning-message {}

/* Multiple Addresses Checkout */
.checkout-progress { padding:0 50px; margin:10px 0; }
.checkout-progress li { float:left; width:20%; border-top:5px solid #ccc; text-align:center; color:#ccc; }
.checkout-progress li.active { border-top-color:#000; color:#000; }

.multiple-checkout { position:relative; }
.multiple-checkout .tool-tip { top:50%; margin-top:-120px; right:10px; }
.multiple-checkout .grand-total { font-size:1.5em; text-align:right; }
.multiple-checkout .grand-total big {}
.multiple-checkout .grand-total .price {}
/* ======================================================================================= */


/* Account Login/Create Pages ============================================================ */
.account-login {}
.account-login .new-users {}
.account-login .registered-users {}

.account-create {}
/* Account Login/Create Pages ============================================================ */

/* Captcha */
.captcha-note  {}
.captcha-image { float:left; position:relative; }
.captcha-img { border:1px solid #ccc; }
.registered-users .captcha-image    {}
#checkout-step-login .captcha-image {}
.captcha-reload { position:absolute; top:2px; right:2px;}
.captcha-reload.refreshing  { animation:rotate 1.5s infinite linear; -webkit-animation:rotate 1.5s infinite linear; -moz-animation:rotate 1.5s infinite linear; }

@-webkit-keyframes rotate {
    0% { -webkit-transform:rotate(0); }
    0% { -webkit-transform:rotate(-360deg); }
}
@-moz-keyframes rotate {
    0% { -moz-transform:rotate(0); }
    0% { -moz-transform:rotate(-360deg); }
}
@keyframes rotate {
    0% { transform:rotate(0); }
    0% { transform:rotate(-360deg); }
}

/* Remember Me Popup ===================================================================== */
.window-overlay { background:url(/_images/icons/window_overlay.png) repeat; background:rgba(0, 0, 0, 0.35); position:absolute; top:0; left:0; height:100%; width:100%; z-index:990; }

.remember-me label {}
.remember-me-popup {}
.remember-me-popup h3 {}
.remember-me-popup .remember-me-popup-head {}
.remember-me-popup .remember-me-popup-head .remember-me-popup-close {}
.remember-me-popup .remember-me-popup-body {}
.remember-me-popup .remember-me-popup-body a {}
/* Remember Me Popup ===================================================================== */


/* My Account ============================================================================= */
.my-account .title-buttons .link-rss { float:none; margin:0; }

/********** < Dashboard */
.dashboard {}
.dashboard .welcome-msg {}

/* Block: Recent Orders */
.dashboard .box-recent { margin:10px 0; }

/* Block: Account Information */
.dashboard .box-info {}

/* Block: Reviews */
.dashboard .box-reviews .number { float:left; font-size:10px; font-weight:bold; line-height:1; color:#fff; margin:3px -20px 0 0; padding:2px 3px; background:#ddd; }
.dashboard .box-reviews .details { margin-left:20px; }

/* Block: Tags */
.dashboard .box-tags .number { float:left; font-size:10px; font-weight:bold; line-height:1; color:#fff; margin:3px -20px 0 0; padding:2px 3px; background:#ddd; }
.dashboard .box-tags .details { margin-left:20px; }
/********** Dashboard > */

/* Address Book */
.addresses-list {}
.addresses-list-additional li.item {}

/* Order View */
.order-info { border:1px solid #ddd; padding:5px; }
.order-info dt,
.order-info dd,
.order-info ul,
.order-info li { display:inline; }
.order-info dt { font-weight:bold; }

.order-date { margin:10px 0; }

.order-info-box {}

.order-items { width:100%; overflow-x:auto; }

.order-additional { margin:15px 0; }
/* Order Gift Message */
.gift-message dt strong { color:#666; }
.gift-message dd { font-size:13px; margin:5px 0 0; }
/* Order Comments */
.order-about dt { font-weight:bold; }
.order-about dd { font-size:13px; margin:0 0 7px; }

.tracking-table { margin:0 0 15px; }
.tracking-table th { font-weight:bold; white-space:nowrap; }

.tracking-table-popup { width:100%; }
.tracking-table-popup th { font-weight:bold; white-space:nowrap; }
.tracking-table-popup th,
.tracking-table-popup td { padding:1px 8px; }

/* Order Print Pages */
.page-print .print-head {}
.page-print .print-head img { float:left; }
.page-print .print-head address { float:left; margin-left:15px; }
/* Price Rewrites */
.page-print .gift-message-link { display:none; }
.page-print .price-excl-tax,
.page-print .price-incl-tax { display:block; white-space:nowrap; }
.page-print .cart-price,
.page-print .price-excl-tax .label,
.page-print .price-incl-tax .label,
.page-print .price-excl-tax .price,
.page-print .price-incl-tax .price { display:inline; }

/* My Reviews */
.product-review .product-img-box { float:left; width:140px;  }
.product-review .product-img-box .product-image { display:block; width:125px; }
.product-review .product-img-box .label { font-size:11px; margin:0 0 3px; }
.product-review .product-img-box .ratings .rating-box { float:none; display:block; margin:0 0 3px; }
.product-review .product-details { margin-left:150px; }
.product-review .product-name { font-size:16px; font-weight:bold; margin:0 0 10px; }
.product-review h3 {}
.product-review .ratings-table { margin:0 0 10px; }
.product-review dt { font-weight:bold; }
.product-review dd { font-size:13px; margin:5px 0 0; }
/* ======================================================================================= */


/* Footer ================================================================================ */
.footer { }
.footer ul { margin:0; padding:0; }
.footer ul li { display:block; }
.footer .icon-ordersandreturns  { display:none; }
/* ======================================================================================= */


/* Clears ================================================================================ */
.clearer:after,
.header-container:after,
.header-container .top-container:after,
.header:after,
.header .quick-access:after,
.main:after,
.footer:after,
.footer-container .bottom-container:after,
.col-main:after,
.col2-set:after,
.col3-set:after,
.col3-layout .product-options-bottom .price-box:after,
.col4-set:after,
.search-autocomplete li:after,
.block .block-content:after,
.block .actions:after,
.block li.item:after,
.block-poll li:after,
.block-layered-nav .currently li:after,
.page-title:after,
.products-grid:after,
.products-list li.item:after,
.box-account .box-head:after,
.dashboard .box .box-title:after,
.box-reviews li.item:after,
.box-tags li.item:after,
.pager:after,
.sorter:after,
.ratings:after,
.add-to-box:after,
.add-to-cart:after,
.product-essential:after,
.product-collateral:after,
.product-view .product-img-box .more-views ul:after,
.product-view .product-shop .short-description:after,
.product-view .box-description:after,
.product-view .box-tags .form-add:after,
.product-options .options-list li:after,
.product-options-bottom:after,
.product-review:after,
.cart:after,
.cart-collaterals:after,
.cart .crosssell li.item:after,
.opc .step-title:after,
.checkout-progress:after,
.multiple-checkout .place-order:after,
.group-select li:after,
.form-list li:after,
.form-list .field:after,
.buttons-set:after,
.page-print .print-head:after,
.advanced-search-summary:after,
.gift-messages-form .item:after,
.send-friend .form-list li p:after,
.button-tabs:after { display:block; content:"."; clear:both; font-size:0; line-height:0; height:0; overflow:hidden; }
/* ======================================================================================= */



#extabs span#tb4 {
	width: 100px;
}
div#noticeevents {
	border: 1px solid #999;
	background-color: #FFF;
	height: 100px;
	overflow: auto;
}
div#noticeevents div {
	border-bottom: 1px dotted #DDD;
	padding: 3px;
	margin: 0px;
}
.runner {
	float: right;
	font-size: .8em;
	background-color: #333;
	color: #FFF;
	padding: 2px 10px 5px 10px;
	cursor: pointer;
}

div.Growler {
    z-index: 100000;
}

/** Growler Notice Custom Styling **/
div.Growler-notice {
	background-color: 		#000;
	color: 					#fff;
	zoom: 					1;
	padding: 				10px 20px;
	margin:     			0 auto 0 auto;
	font-size: 				15px;
	text-align: 			center;
	display: 				none;
    width:                  420px;
	z-index:                100000;
    -webkit-box-shadow:     0 0 2px rgba(0, 0, 0, 0.5);
       -moz-box-shadow:     0 0 2px rgba(0, 0, 0, 0.5);
            box-shadow:     0 0 2px rgba(0, 0, 0, 0.5);
}

div.Growler-notice .cart-success {
    font-size: 17px;
    line-height: 23px;
    padding: 10px;
}
div.Growler-notice .button {
    margin: 5px;
}

div.Growler-notice-head {
	font-weight: 			bold;
	font-size:				12px;
    display:                none;
}

div.Growler-notice-exit {
	float: 					right;
	font-weight: 			bold;
	font-size: 				14px;
	cursor:					pointer;
}

/** Plain Theme **/
div.plain {
	color: 					#000;
	margin-top: 			5px;
	margin-bottom: 			5px;
	text-align: 			left;
	display: 				none;
	background-color: 		#EDEDED;
	border: 				1px solid #777;
}

div.plain div.Growler-notice-head {
	font-weight: 			bold;
	font-size:				12px;
	padding: 				2px 10px;
}

div.plain div.Growler-notice-exit {
	float: 					right;
	cursor:					pointer;
	margin: 				0px;
	padding: 				0px 0px 2px 2px;
	width: 					10px;
	height: 				10px;
	color: 					#BFBFBF;
}
div.plain div.Growler-notice-body {
	padding: 5px;
}


/** Mac OS X Theme **/
div.macosx {
	color: 					#000;
	margin-top: 			5px;
	margin-bottom: 			5px;
	text-align: 			left;
	display: 				none;
	background: #d7d7d7 url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/eternal/images/macosx.jpg) repeat-y 0;
	border: 				1px solid #C9C9C9;
}

div.macosx div.Growler-notice-head {
	font-weight: 			bold;
	font-size:				13px;
	padding: 				5px 10px;
}

div.macosx div.Growler-notice-exit {
	width: 					15px;
	height: 				15px;
	float: 					left;
	cursor:					pointer;
	margin: 				4px;
	margin-left: 			1px;
	font-size: 				0em;
	color: 					transparent;
	background: transparent url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/eternal/images/macosx_exit.png) no-repeat left 0;
}
div.macosx div.Growler-notice-exit:hover {
	background: transparent url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/eternal/images/macosx_exit_over.png) no-repeat left 0;
}
div.macosx div.Growler-notice-body {
	padding: 2px 0 10px 25px;
}

/** Candybars Theme **/
div.candybar {
	color: 					#000;
	margin-top: 			5px;
	margin-bottom: 			5px;
	text-align: 			left;
	display: 				none;
	background-color: 		#F5F7FA;
	border: 				1px solid #19304B;
}

div.candybar div.Growler-notice-head {
	font-weight: 			bold;
	font-size:				12px;
	background: 			url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/eternal/images/candybars.jpg) repeat-x;
	padding: 				5px 10px;
}

div.candybar div.Growler-notice-exit {
	float: 					right;
	cursor:					pointer;
	margin: 				3px;
}
div.candybar div.Growler-notice-body {
	border-top: 1px solid #999;
	padding: 10px;
}

/** Construction Theme **/
div.atwork {
	color: 					#FFF;
	margin-top: 			5px;
	margin-bottom: 			5px;
	text-align: 			left;
	display: 				none;
	background: 			#4d4d4d url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/eternal/images/atwork.png) repeat-y 0;
	border: 				1px solid #222;
}

div.atwork div.Growler-notice-head {
	font-weight: 			bold;
	font-size:				13px;
	padding: 				5px 20px;
	color: 					#efca23;
	
}

div.atwork div.Growler-notice-exit {
	float: 					right;
	cursor:					pointer;
	margin: 				3px;
}
div.atwork div.Growler-notice-body {
	padding: 2px 0 10px 25px;
}


/*
   modalbox.css
   
   Modalbox project
   
   Created by Andrew Okonetchnikov.
   Copyright 2006-2010 okonet.ru. All rights reserved.
   
   Licensed under MIT license.
*/

#MB_overlay {
	position: absolute;
	margin: auto;
	top: 0;	left: 0;
	width: 100%; height: 100%;
	z-index: 100000;
	border: 0;
	background-color: #000!important;
}
#MB_overlay[id] { position: fixed; }

#MB_windowwrapper {
	position:absolute;
	top:0;
	width:100%;
}

#MB_window {
	position:relative;
	margin-left:auto;
	margin-right:auto;
	top:0;
	left:0;
	border: 0 solid;
	text-align: left;
	z-index: 100001;
}
#MB_window[id] { position: relative; }

#MB_frame {
	position: relative;
	background-color: #EFEFEF;
	height: 100%;
}

#MB_header {
	margin: 0;
	padding: 0;
}

#MB_content {
	position: relative;
	padding: 6px .75em;
	overflow: auto;
}

#MB_caption {
	font: bold 100% "Lucida Grande", Arial, sans-serif;
	text-shadow: #FFF 0 1px 0;
	padding: .5em 2em .5em .75em;
	margin: 0;
	text-align: left;
}

#MB_close {
	display: block;
	position: absolute;
	right: 5px; top: 4px;
	padding: 2px 3px;
	font-weight: bold;
	text-decoration: none;
	font-size: 13px;
}
#MB_close:hover {
	background: transparent;
}

#MB_loading {
	padding: 1.5em;
	text-indent: -10000px;
	background: transparent url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/eternal/spinner.gif) 50% 0 no-repeat;
}

/* Color scheme */
#MB_window {
	background-color: #EFEFEF;
	color: #000;
	
	-webkit-box-shadow: 0 0 12px #000; 
	-moz-box-shadow: #000 0 0 12px; 
	box-shadow: 0 0 12px #000;
}
	#MB_frame {
		padding-bottom: 4px;
		
		-webkit-border-bottom-left-radius: 4px;
		-webkit-border-bottom-right-radius: 4px;

		-moz-border-radius-bottomleft: 4px;
		-moz-border-radius-bottomright: 4px;

		border-bottom-left-radius: 4px;
		border-bottom-right-radius: 4px;
	}
	
	#MB_content { border-top: 1px solid #F9F9F9; }

	#MB_header {
	  background-color: #DDD;
	  border-bottom: 1px solid #CCC;
	}
		#MB_caption { color: #000 }
		#MB_close { color: #777 }
		#MB_close:hover { color: #000 }


/* Alert message */
.MB_alert {
	margin: 10px 0;
	text-align: center;
}

#custommenu {
    position: relative;
    padding: 0px 0px 0px 0px;
    height: auto;
    margin: 0 auto;
}
#custommenu .home-icon {
    padding: 0 10px;
}
#custommenu .home-icon img {
    margin-top: -2px;
}
/*IE7 fix*/
*:first-child+html #custommenu {
    z-index: 998;
}
div.menu {
    float: left;
    padding: 0px 0px 0px 0px;
    margin: 0px 0px 0px 0px;
}
div.menu.active {
    position: relative;
    z-index: 1;
}
div.menu a {
    display: block;
    height: 50px;
    padding: 13px 25px;
}
div.menu a span {
    display: block;
}
div.menu a span:hover {
    cursor: pointer;
}
div.eternal-custom-menu-popup {
    position: absolute;
    z-index: 1000;
    display: none;
    text-align: left;
    padding: 0px 0px 10px 0px;
    width: 100%;
    left: 0;
    margin-top: 0;
}
div.menu a, div.eternal-custom-menu-popup a {
    text-decoration: none;
    display:block;
    cursor: pointer;
    _height: 0;
    height: auto;
}
div.level1 {
    margin-bottom: 5px;
}
div.block2 {
    padding-top: 0px;
    padding-left: 10px;
    padding-right: 10px;
    display: block;
    text-align: center;
}
a.level0 {
    text-transform: uppercase;
}
a.level1 {
    margin-top: 0;
    margin-bottom: 0;
    padding: 15px 0;
	text-transform: uppercase;
}
/* Clearfix */
div.block2:after {
    content: ".";
    display: block;
    clear: both;
    visibility: hidden;
    line-height: 0;
    height: 0;
}
html[xmlns] div.block2 {
    display: block;
}
* html div.block2 {
    height: 1%;
}
div.block2 p {
    font-size: 14px;
    margin-bottom: 3px;
}
div.block2 p a {
    display: inline;
}
div.block2 a img {
    opacity: .9;
    filter: alpha(opacity=90);
}
div.block2 a:hover img {
    opacity: 1;
    filter: alpha(opacity=100);
}
div.block2 .brand a:hover img {
}
div.eternal-custom-menu-popup hr {
    margin: 0px 0px 10px 0px;
}
/******************************************* COLUMN WIDTH ***************************** */
div.column {
    float: left;
    width: 18%; /* for 5 columns*/
    padding: 0px 1%;
    margin: 0px 0px 0px 0px;
}
/*end COLUMN WIDTH  */

div.itemSubMenu {
    margin-left: 10px;
}
div.itemSubMenu.level1 {
	margin-left: 0;
}
div.itemSubMenu a {
	padding: 10px 10px 10px 15px;
	line-height:1;
}
.clearBoth {
    clear:both;
    height: 0;
    overflow: hidden;
}
div.level1 {
    margin-bottom: 0px;
}
/*BG*/
#custommenu {
}
div.eternal-custom-menu-popup {
	font-size: 15px;
	padding: 25px 0;
    -webkit-box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
       -moz-box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
    -webkit-background-clip: padding-box;
       -moz-background-clip: padding;
            background-clip: padding-box;
}
div.eternal-custom-menu-popup > div {
    padding:0 20px;
}
div.menu a:link, div.menu a:visited {
	text-transform: uppercase;
}
div.menu a, div.eternal-custom-menu-popup a {
}
div.menu .brand a, div.eternal-custom-menu-popup .brand a {
}
div.menu.active a {
    
}
.block2 table.brand {
    float: left;
}
.block2 .single_menu_product {
    float: left;
    position: relative;
    max-width: 150px;
    overflow: hidden;
    margin-right: 20px;
    margin-left: 0px;
}
/*MOBILE MENU STYLES*/
#menu-button, .parentMenu {
    display: inline-block;
}
html[xmlns] #menu-button, html[xmlns] .parentMenu {
    display: block;
}
* html #menu-button, * html .parentMenu {
    height: 1%;
}
#menu-button:after, .parentMenu:after {
    content: ".";
    display: block;
    clear: both;
    visibility: hidden;
    line-height: 0;
    height: 0;
}
#custommenu-mobile {
    position:relative;
    margin: 0 auto;
    padding: 0;
    z-index: 999;
}
#menu-button {
    float: none;
    padding: 10px 0px 5px;
    margin: 0px 0px 10px 0px;
    width: 100%;
    text-transform: uppercase;
}
#menu-button a {
    display: block;
    float: left;
    margin-left: 45%;
    position: relative;
    padding: 0px 10px;
    text-decoration: none;
}
#menu-button:hover {
    cursor: pointer;
}
#menu-button:hover a:after {
    opacity: 1;
}
.menu-mobile div.column {
    float:none;
    padding:5px;
    background: #fff;
}
.menu-mobile a.itemMenuName {
    display: block;
    text-align: left;
}
.menu-mobile div.menu-button, .menu-mobile div.menu-mobile {
    float: none;
    padding: 0px 0px 0px 0px;
    margin: 0px 0px 0px 0px;
    width: 100%;
    border-bottom: 1px solid #fff;
}
.menu-mobile div.column {
    float: none;
    width: 100%;
    padding: 0px 0;
    margin: 0px 0px 0px 0px;
}
.menu-mobile a.level1 {
    margin-top: 0px;
    margin-bottom: 0;
    padding: 10px 10px;
}
.menu-mobile a.level2 {
    padding: 10px 10px;    
}
/*buttons level 01*/
.menu-mobile .parentMenu {
    padding: 0px 0px;
    display: block;
    text-align: left;
    border-bottom: 1px solid #dadada;
}
.menu-mobile .parentMenu a {
    padding: 10px 0px 10px 20px;
    margin-right: 40px;
    display: block;
    text-decoration: none;
    font-size: 15px;
}
.menu-mobile.level0 > .parentMenu > a {
    text-transform: uppercase;
}
.menu-mobile .parentMenu:hover {
    cursor: pointer;
}
.parentMenu {
    position: relative;
}
.parentMenu a {
    display: block;
}
#menu-button {
    cursor: pointer;
}
.menu-mobile div.level2 {
    margin-bottom: 0;
}
#custommenu-mobile .button {
    cursor: pointer;
    position: absolute;
    right: 1%;
    top: 0px;
    display: block;
    width: 40px;
    height: 38px;
    background-color: transparent;
    background-repeat: no-repeat;
    background-position: center center;
    background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyBpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NThDRkUyNDFGMTQxMTFFMkJBQjk4QzEwRTMyOEQ3NjgiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NThDRkUyNDBGMTQxMTFFMkJBQjk4QzEwRTMyOEQ3NjgiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNSBXaW5kb3dzIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5kaWQ6NTkwRDEzQjM4QkVGRTIxMUJCMDY5OTNFOEEwRTU1NzEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6NTkwRDEzQjM4QkVGRTIxMUJCMDY5OTNFOEEwRTU1NzEiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz4mB5a+AAAABlBMVEVERkX///9KadJBAAAAAnRSTlP/AOW3MEoAAAAYSURBVHjaYmAEAQYGCEUOBzeg1GiAAAMAHxQAZVYfloEAAAAASUVORK5CYII=);
    border-width: 0;
    -webkit-box-shadow: none;
       -moz-box-shadow: none;
            box-shadow: none;
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
    padding: 0;
}
#custommenu-mobile .button.open {
    background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAwAAAAMCAMAAABhq6zVAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAA2RpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMC1jMDYwIDYxLjEzNDc3NywgMjAxMC8wMi8xMi0xNzozMjowMCAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wTU09Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9tbS8iIHhtbG5zOnN0UmVmPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvc1R5cGUvUmVzb3VyY2VSZWYjIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtcE1NOk9yaWdpbmFsRG9jdW1lbnRJRD0ieG1wLmRpZDo1QjBEMTNCMzhCRUZFMjExQkIwNjk5M0U4QTBFNTU3MSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo5MDkwREIxRUYxNDExMUUyOUQwQkFFNjlBRTYxQTE1MSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo5MDkwREIxREYxNDExMUUyOUQwQkFFNjlBRTYxQTE1MSIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ1M1IFdpbmRvd3MiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo1QjBEMTNCMzhCRUZFMjExQkIwNjk5M0U4QTBFNTU3MSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo1QjBEMTNCMzhCRUZFMjExQkIwNjk5M0U4QTBFNTU3MSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/Pkzw6W4AAAAGUExURURGRf///0pp0kEAAAACdFJOU/8A5bcwSgAAABVJREFUeNpiYEQCDORwcANKjQYIMAAlLAB5tFCUeQAAAABJRU5ErkJggg==);
}
#custommenu-mobile a.level1 {
    padding-left: 40px;
}
#custommenu-mobile a.level2 {
    padding-left: 60px;
}
#custommenu-mobile a.level3 {
    padding-left: 70px;
}
#custommenu-mobile a.level4 {
    padding-left: 80px;
}
#custommenu-mobile a.level5 {
    padding-left: 90px;
}

#menu-content {
    -webkit-box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
       -moz-box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
            box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
    position: absolute;
    left: 0;
    right: 0;
}

.menu-mobile.level0 > .parentMenu,
.itemMenu.level1 > .parentMenu {
    border-top: 1px solid rgba(200, 200, 200, 0.2);
    border-bottom: 1px solid rgba(255, 255, 255, 0.5);    
}
.eternal-custom-menu-submenu.level1 {
    border-bottom: 1px solid rgba(255, 255, 255, 0.5);
}
/*
Theme created for use with Sequence.js (http://www.sequencejs.com/)

Theme: Modern Slide In
Version: 1.3
Theme Author: Ian Lunn @IanLunn
Author URL: http://www.ianlunn.co.uk/
Theme URL: http://www.sequencejs.com/themes/modern-slide-in/

This is a FREE theme and is available under a MIT License:
http://www.opensource.org/licenses/mit-license.php

Sequence.js and its dependencies are (c) Ian Lunn Design 2012 - 2013 unless otherwise stated.
*/

#homeslider-sequence {
    margin: 0 auto;
    position: relative;
    overflow: hidden;
    width: 100%;
    font-size: 0.625em;
    margin: 0 auto;
    position: relative;
    height: 482px;
}

.sequence-canvas {
    height: 100%;
    width: 100%;
    margin: 0;
    padding: 0;
}

.sequence-canvas > li {
    position: absolute;
    width: 100%;
    height: 100%;
    z-index: 1;
    top: -50%;
    line-height: 0;
}
.sequence-canvas li > * {
    position: absolute;
    /* only cause the left and opacity properties to transition */
    -webkit-transition-property: left, opacity;
    -moz-transition-property: left, opacity;
    -ms-transition-property: left, opacity;
    -o-transition-property: left, opacity;
    transition-property: left, opacity;
}

.sequence-next,
.sequence-prev {
    color: white;
    cursor: pointer;
    display: none;
    font-weight: bold;
    padding: 10px 15px;
    position: absolute;
    top: 50% !important;
    z-index: 1000;
    height: 45px;
    width: 60px;
    margin-top: -24px;
}

.sequence-pause {
    bottom: 0;
    cursor: pointer;
    position: absolute;
    z-index: 1000;
}

.sequence-paused {
    opacity: 0.3;
}

.sequence-prev {
    left: 3%;
}

.sequence-next {
    right: 3%;
}


#sequence-preloader {
    background: #d9d9d9;
}

.sequence-pagination {
    bottom: 2%;
    display: none;
    right: 0;
    left: 0;
    text-align: center;
    position: absolute;
    z-index: 10;
    margin: 0;
    -webkit-transition-duration: 0.5s;
       -moz-transition-duration: 0.5s;
        -ms-transition-duration: 0.5s;
         -o-transition-duration: 0.5s;
            transition-duration: 0.5s;
}

.sequence-pagination li {
    display: inline-block;
    *display: inline;
    font-size: 11px;
    height: 13px;
    margin: 0 4px;
}

.sequence-pagination li a {
    background: none repeat scroll 0 0 rgba(0, 0, 0, 0.5);
    -webkit-border-radius: 5px;
       -moz-border-radius: 5px;
            border-radius: 5px;
    -webkit-box-shadow: 0 0 3px rgba(0, 0, 0, 0.3) inset;
       -moz-box-shadow: 0 0 3px rgba(0, 0, 0, 0.3) inset;
            box-shadow: 0 0 3px rgba(0, 0, 0, 0.3) inset;
    cursor: pointer;
    display: block;
    height: 10px;
    text-indent: -9999px;
    width: 10px;
}

.sequence-next,
.sequence-prev {
    position: absolute;
    -webkit-transition-duration: 0.5s;
       -moz-transition-duration: 0.5s;
        -ms-transition-duration: 0.5s;
         -o-transition-duration: 0.5s;
            transition-duration: 0.5s;
}

.sequence-next:hover,
.sequence-prev:hover {
    -webkit-transition-duration: 0.5s;
       -moz-transition-duration: 0.5s;
        -ms-transition-duration: 0.5s;
         -o-transition-duration: 0.5s;
            transition-duration: 0.5s;
}

/* custom styles */
.sequence-canvas > li .slider-bg {
    position: relative;
    height: 100%;
}

.sequence-canvas > li .slider-wrap {
    position: relative;
    height: 100%;
    top: -50%;
    line-height: 20px;
}

.sequence-canvas > li .slider-wrap > *,
.sequence-canvas > li .slider-bg > * {
    position: absolute;
    /* only cause the left and opacity properties to transition */
    -webkit-transition-property: left, opacity;
       -moz-transition-property: left, opacity;
        -ms-transition-property: left, opacity;
         -o-transition-property: left, opacity;
            transition-property: left, opacity;
}

.sequence-canvas > li .slider-wrap img {
    opacity: 0;
    -webkit-transition-delay: 0.5s;
       -moz-transition-delay: 0.5s;
        -ms-transition-delay: 0.5s;
         -o-transition-delay: 0.5s;
            transition-delay: 0.5s;
}

.sequence-canvas > li.animate-in .slider-wrap img {
    opacity: 1;
    -webkit-transition-duration: 1s;
       -moz-transition-duration: 1s;
        -ms-transition-duration: 1s;
         -o-transition-duration: 1s;
            transition-duration: 1s;
}

.sequence-canvas > li.animate-out .slider-wrap img {
    opacity: 0;
    -webkit-transition-duration: 0.5s;
       -moz-transition-duration: 0.5s;
        -ms-transition-duration: 0.5s;
         -o-transition-duration: 0.5s;
            transition-duration: 0.5s;
}

.sequence-canvas > li .slider-bg img {
    width: 100%;
    height: 100%;
    top: 50%;
    left: 0;
    opacity: 0;
    -webkit-transition-duration: 0.5s;
       -moz-transition-duration: 0.5s;
        -ms-transition-duration: 0.5s;
         -o-transition-duration: 0.5s;
            transition-duration: 0.5s;
}
.sequence-canvas > li.animate-in .slider-bg img {
    opacity: 1;
    -webkit-transition-duration: 0.5s;
       -moz-transition-duration: 0.5s;
        -ms-transition-duration: 0.5s;
         -o-transition-duration: 0.5s;
            transition-duration: 0.5s;
}
.sequence-canvas > li.animate-out .slider-bg img {
    opacity: 0;
    -webkit-transition-duration: 0.2s;
       -moz-transition-duration: 0.2s;
        -ms-transition-duration: 0.2s;
         -o-transition-duration: 0.2s;
            transition-duration: 0.2s;
}

.sequence-canvas .slide-title {
    font-size: 33px;
    left: 60%;
    width: 48%;
    opacity: 0;
    top: 5%;
    z-index: 50;
    text-transform: uppercase;
    line-height: 1.1;
    margin: 10px 0;
    white-space: nowrap;
    -webkit-transition-delay: 2.2s;
       -moz-transition-delay: 2.2s;
        -ms-transition-delay: 2.2s;
         -o-transition-delay: 2.2s;
            transition-delay: 2.2s; 
}

.sequence-canvas .animate-in .slide-title {
    left: 50%;
    opacity: 1;
    -webkit-transition-duration: 0.7s;
       -moz-transition-duration: 0.7s;
        -ms-transition-duration: 0.7s;
         -o-transition-duration: 0.7s;
            transition-duration: 0.7s;
}

.sequence-canvas .animate-out .slide-title {
    left: 40%;
    opacity: 0;
    -webkit-transition-duration: 0.5s;
       -moz-transition-duration: 0.5s;
        -ms-transition-duration: 0.5s;
         -o-transition-duration: 0.5s;
            transition-duration: 0.5s;
}

.sequence-canvas .slide-desc {
    font-size: 26px;
    left: 60%;
    width: 48%;
    opacity: 0;
    top: 20%;
    line-height: 1.1;
    -webkit-transition-delay: 3.2s;
       -moz-transition-delay: 2.2s;
        -ms-transition-delay: 3.2s;
         -o-transition-delay: 3.2s;
            transition-delay: 3.2s; 
}

.sequence-canvas .animate-in .slide-desc {
    margin: 0;
    left: 50%;
    opacity: 1;
    -webkit-transition-duration: 0.5s;
       -moz-transition-duration: 0.5s;
        -ms-transition-duration: 0.5s;
         -o-transition-duration: 0.5s;
            transition-duration: 0.5s;
}

.sequence-canvas .animate-out .slide-desc {
    left: 40%;
    opacity: 0;
    -webkit-transition-duration: 0.3s;
       -moz-transition-duration: 0.3s;
        -ms-transition-duration: 0.3s;
         -o-transition-duration: 0.3s;
            transition-duration: 0.3s;
}

.sequence-canvas .slide-link {
    font-size: 15px;
    padding: 12px 18px;
    left: 50%;
    opacity: 0;
    top: 40%;
    line-height: 1.1;
    -webkit-transition-delay: 4.0s;
       -moz-transition-delay: 4.0s;
        -ms-transition-delay: 4.0s;
         -o-transition-delay: 4.0s;
            transition-delay: 4.0s;
}

.sequence-canvas .animate-in .slide-link {
    left: 50%;
    opacity: 1;
    -webkit-transition-duration: 0.3s;
       -moz-transition-duration: 0.3s;
        -ms-transition-duration: 0.3s;
         -o-transition-duration: 0.3s;
            transition-duration: 0.3s;
}

.sequence-canvas .animate-out .slide-link {
    left: 50%;
    opacity: 0;
    -webkit-transition-duration: 0.3s;
       -moz-transition-duration: 0.3s;
        -ms-transition-duration: 0.3s;
         -o-transition-duration: 0.3s;
            transition-duration: 0.3s;
}
.sequence-canvas .price-box {
    opacity: 0;
    top: 10%;
    line-height: 1.1;    
    -webkit-transition-delay: 1.2s;
       -moz-transition-delay: 1.2s;
        -ms-transition-delay: 1.2s;
         -o-transition-delay: 1.2s;
            transition-delay: 1.2s;
}

.sequence-canvas .animate-in .price-box {
    left: 20%;
    opacity: 1;
    -webkit-transition-duration: 0.5s;
       -moz-transition-duration: 0.5s;
        -ms-transition-duration: 0.5s;
         -o-transition-duration: 0.5s;
            transition-duration: 0.5s;
}

.sequence-canvas .animate-out .price-box {
    left: 30%;
    opacity: 0;
    -webkit-transition-duration: 0.7s;
       -moz-transition-duration: 0.7s;
        -ms-transition-duration: 0.7s;
         -o-transition-duration: 0.7s;
            transition-duration: 0.7s;
}

.sequence-canvas .price-box .price {
    font-weight: normal;
}

/* block_homeslide1 */
.sequence-canvas .block_homeslider1 .slide-title {
    left: 10%;
    top: 25%;
    width: auto;
    text-transform: none;
    line-height: 1.2;
    -webkit-transition-delay: 1.7s;
       -moz-transition-delay: 1.7s;
        -ms-transition-delay: 1.7s;
         -o-transition-delay: 1.7s;
            transition-delay: 1.7s;
}
.sequence-canvas .block_homeslider1.animate-in .slide-title {
    left: 15px;
}
.sequence-canvas .block_homeslider1.animate-out .slide-title {
    left: 10%;
}
.sequence-canvas .block_homeslider1 .slide-desc {
    left: 18%;
    top: 48%;
    width: 50%;
    font-size: 20px;
    line-height: 20px;
    -webkit-transition-delay: 2.7s;
       -moz-transition-delay: 2.7s;
        -ms-transition-delay: 2.7s;
         -o-transition-delay: 2.7s;
            transition-delay: 2.7s;
}
.sequence-canvas .block_homeslider1.animate-in .slide-desc {
    left: 15px;
}
.sequence-canvas .block_homeslider1.animate-out .slide-desc {
    left: 18%;
}
.sequence-canvas .block_homeslider1 .slide-desc img {
    margin-right: 15px;
    vertical-align: top;
}
.sequence-canvas .block_homeslider1 .slide-desc2 {
    top: 55%;
    -webkit-transition-delay: 2.9s;
       -moz-transition-delay: 2.9s;
        -ms-transition-delay: 2.9s;
         -o-transition-delay: 2.9s;
            transition-delay: 2.9s;
}
.sequence-canvas .block_homeslider1 .slide-desc3 {
    top: 62%;
    -webkit-transition-delay: 3.1s;
       -moz-transition-delay: 3.1s;
        -ms-transition-delay: 3.1s;
         -o-transition-delay: 3.1s;
            transition-delay: 3.1s;
}
.sequence-canvas .block_homeslider1 .slide-desc4 {
    top: 69%;
    -webkit-transition-delay: 3.3s;
       -moz-transition-delay: 3.3s;
        -ms-transition-delay: 3.3s;
         -o-transition-delay: 3.3s;
            transition-delay: 3.3s;
}
.sequence-canvas .block_homeslider1 .slide-desc5 {
    top: 76%;
    -webkit-transition-delay: 3.5s;
       -moz-transition-delay: 3.5s;
        -ms-transition-delay: 3.5s;
         -o-transition-delay: 3.5s;
            transition-delay: 3.5s;
}
.sequence-canvas .block_homeslider1 .model {
    bottom: 0%;
    left: 35%;
    width: 55%;
}
.sequence-canvas .block_homeslider1.animate-in .model {
    left: 40%;
}
.sequence-canvas .block_homeslider1.animate-out .model {
    left: 35%;
}

/* block_homeslide2 */
.sequence-canvas .block_homeslider2 .slide-title {
    left: 88%;
    top: 25%;
    width: auto;
}
.sequence-canvas .block_homeslider2.animate-in .slide-title {
    left: 65%;
}
.sequence-canvas .block_homeslider2.animate-out .slide-title {
    left: 88%;
}
.sequence-canvas .block_homeslider2 .slide-desc {
    left: 88%;
    top: 37%;
    width: 33%;
    font-size: 22px;
}
.sequence-canvas .block_homeslider2.animate-in .slide-desc {
    left: 65%;
}
.sequence-canvas .block_homeslider2.animate-out .slide-desc {
    left: 88%;
}
.sequence-canvas .block_homeslider2 .slide-link {
    left: 70%;
    top: 54%;
}
.sequence-canvas .block_homeslider2.animate-in .slide-link {
    left: 65%;
}
.sequence-canvas .block_homeslider2.animate-out .slide-link {
    left: 70%;
}
.sequence-canvas .block_homeslider2 .boy {
    bottom: 17%;
    left: -10%;
    width: 68%;
}
.sequence-canvas .block_homeslider2.animate-in .boy {
    left: 0;
}
.sequence-canvas .block_homeslider2.animate-out .boy {
    left: -10%;
}
.sequence-canvas .block_homeslider2 .phone {
    bottom: 0.5%;
    left: 50%;
    width: 30%;
}
.sequence-canvas .block_homeslider2.animate-in .phone {
    left: 33%;
}
.sequence-canvas .block_homeslider2.animate-out .phone {
    left: 50%;
}
.sequence-canvas .block_homeslider2 .price-box {
    left: 25%;
    top: 10%;
}
.sequence-canvas .block_homeslider2.animate-in .price-box {
    left: 31%;
}
.sequence-canvas .block_homeslider2.animate-out .price-box {
    left: 25%;
}

/* block_homeslide3 */
.sequence-canvas .block_homeslider3 .slide-title {
    left: 10%;
    top: 6%;
    width: auto;
}
.sequence-canvas .block_homeslider3.animate-in .slide-title {
    left: 0%;
}
.sequence-canvas .block_homeslider3.animate-out .slide-title {
    left: 10%;
}
.sequence-canvas .block_homeslider3 .slide-desc {
    left: 10%;
    top: 17%;
}
.sequence-canvas .block_homeslider3.animate-in .slide-desc {
    left: 0%;
}
.sequence-canvas .block_homeslider3.animate-out .slide-desc {
    left: 10%;
}
.sequence-canvas .block_homeslider3 .slide-link {
    left: 5%;
    top: 32%;
}
.sequence-canvas .block_homeslider3.animate-in .slide-link {
    left: 0%;
}
.sequence-canvas .block_homeslider3.animate-out .slide-link {
    left: 5%;
}
.sequence-canvas .block_homeslider3 .man {
    bottom: 0%;
    left: 10%;
    width: 46%;
}
.sequence-canvas .block_homeslider3.animate-in .man {
    left: 0;
}
.sequence-canvas .block_homeslider3.animate-out .man {
    left: 10%;
}
.sequence-canvas .block_homeslider3 .tv {
    left: 50%;
    top: 16%;
    width: 30%;
    -webkit-transition-delay: 0.9s;
       -moz-transition-delay: 0.9s;
        -ms-transition-delay: 0.9s;
         -o-transition-delay: 0.9s;
            transition-delay: 0.9s;
}
.sequence-canvas .block_homeslider3.animate-in .tv {
    left: 69.5%;
}
.sequence-canvas .block_homeslider3.animate-out .tv {
    left: 50%;
}
.sequence-canvas .block_homeslider3 .tablet {
    left: 50%;
    top: 43.5%;
    width: 8.5%;
    -webkit-transition-delay: 0.9s;
       -moz-transition-delay: 0.9s;
        -ms-transition-delay: 0.9s;
         -o-transition-delay: 0.9s;
            transition-delay: 0.9s;
}
.sequence-canvas .block_homeslider3.animate-in .tablet {
    left: 62%;
}
.sequence-canvas .block_homeslider3.animate-out .tablet {
    left: 50%;
}
.sequence-canvas .block_homeslider3 .iphone {
    left: 50%;
    top: 55%;
    width: 6.5%;
    -webkit-transition-delay: 0.9s;
       -moz-transition-delay: 0.9s;
        -ms-transition-delay: 0.9s;
         -o-transition-delay: 0.9s;
            transition-delay: 0.9s;
}
.sequence-canvas .block_homeslider3.animate-in .iphone {
    left: 54%;
}
.sequence-canvas .block_homeslider3.animate-out .iphone {
    left: 50%;
}




@media (min-width: 1200px) {
    #homeslider-sequence {
        height: <?php echo $config_site["banner_height"];?>px;
    }
    .sequence-next,
    .sequence-prev {
        margin-top: -22px;
    }
    
    .sequence-canvas .slide-title {
        font-size: 35px;
    }
    .sequence-canvas .slide-desc {
        font-size: 28px;
    }
    
    /* block_homeslide1 */
    .sequence-canvas .block_homeslider1 .slide-title {
        top: 28%;
    }
    
    /* block_homeslide2 */
    .sequence-canvas .block_homeslider2 .slide-title {
        top: 27%;
    }
    .sequence-canvas .block_homeslider2 .slide-desc {
        width: 33%;
        font-size: 22px;
    }
    .sequence-canvas .block_homeslider2 .slide-link {
        top: 49%;
    }
    .sequence-canvas .block_homeslider2.animate-in .slide-title,
    .sequence-canvas .block_homeslider2.animate-in .slide-desc,
    .sequence-canvas .block_homeslider2.animate-in .slide-link {
        left: 65%;
    }
    
    /* block_homeslide3 */
    .sequence-canvas .block_homeslider3 .slide-title {
        top: 7%;
    }
    .sequence-canvas .block_homeslider3 .slide-link {
        top: 30%;
    }
    .sequence-canvas .block_homeslider3.animate-in .slide-title,
    .sequence-canvas .block_homeslider3.animate-in .slide-desc,
    .sequence-canvas .block_homeslider3.animate-in .slide-link {
        left: 2%;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    #homeslider-sequence {
        height: 370px;
    }
    .sequence-next,
    .sequence-prev {
        margin-top: -22px;
    }
    .sequence-canvas .slide-title {
        font-size: 30px;
    }
    .sequence-canvas .slide-desc {
        font-size: 23px;
    }
    .sequence-canvas .slide-link {
        font-size: 14px;
        padding: 8px 14px;
    }
    
    /* block_homeslide1 */
    .sequence-canvas .block_homeslider1 .slide-title {
        top: 15%;
    }
    .sequence-canvas .block_homeslider1 .model {
        left: 44%;
    }
    .sequence-canvas .block_homeslider1 .slide-desc {
        top: 43%;
    }
    .sequence-canvas .block_homeslider1 .slide-desc2 {
        top: 51%;
    }
    .sequence-canvas .block_homeslider1 .slide-desc3 {
        top: 59%;
    }
    .sequence-canvas .block_homeslider1 .slide-desc4 {
        top: 67%;
    }
    .sequence-canvas .block_homeslider1 .slide-desc5 {
        top: 75%;
    }
    
    /* block_homeslide2 */
    .sequence-canvas .block_homeslider2 .slide-title {
        top: 27%;
    }
    .sequence-canvas .block_homeslider2 .slide-desc {
        top: 42%;
        width: 45%;
        font-size: 18px;
    }
    .sequence-canvas .block_homeslider2 .slide-link {
        top: 57%;
    }
    .sequence-canvas .block_homeslider2.animate-in .slide-title,
    .sequence-canvas .block_homeslider2.animate-in .slide-desc,
    .sequence-canvas .block_homeslider2.animate-in .slide-link {
        left: 63%;
    }
    
    /* block_homeslide3 */
    .sequence-canvas .block_homeslider3 .slide-title {
        top: 5%;
    }
    .sequence-canvas .block_homeslider3 .slide-desc {
        width: 60%;
        top: 20%;
    }
    .sequence-canvas .block_homeslider3 .slide-link {
        top: 37%;
    }
    .sequence-canvas .block_homeslider3.animate-in .slide-title,
    .sequence-canvas .block_homeslider3.animate-in .slide-desc,
    .sequence-canvas .block_homeslider3.animate-in .slide-link,
    .sequence-canvas .block_homeslider3.animate-in .man {
        left: 5%;
    }
}

@media (max-width: 767px) {
    
    .product-essential .product-image img {
        width: 100%;
        margin:auto;
    }
    
    #homeslider-sequence {
        height: 340px;
    }
    .sequence-canvas .slide-title {
        font-size: 20px;
    }
    .sequence-canvas .slide-desc {
        display: none;
    }
    .sequence-canvas .slide-link {
        font-size: 11px;
        padding: 8px 10px;
        -webkit-transition-delay: 3.0s;
           -moz-transition-delay: 3.0s;
            -ms-transition-delay: 3.0s;
             -o-transition-delay: 3.0s;
                transition-delay: 3.0s;
    }
    
    /* block_homeslide1 */
    .sequence-canvas .block_homeslider1 .slide-title {
        top: 22%;
        line-height: 1.5;
    }
    
    /* block_homeslide2 */
    .sequence-canvas .block_homeslider2 .slide-title {
        top: 25%
    }
    .sequence-canvas .block_homeslider2 .slide-link {
        top: 40%;
    }
    .sequence-canvas .block_homeslider2.animate-in .slide-title,
    .sequence-canvas .block_homeslider2.animate-in .slide-link {
        left: 62%;
    }
    
    /* block_homeslide3 */
    .sequence-canvas .block_homeslider3 .slide-title {
        top: 10%
    }
    .sequence-canvas .block_homeslider3 .slide-link {
        top: 25%;
    }
    .sequence-canvas .block_homeslider3.animate-in .slide-title,
    .sequence-canvas .block_homeslider3.animate-in .slide-link {
        left: 2%;
    }
}

@media (max-width: 650px) {
    #homeslider-sequence {
        height: 280px;
    }
    
    /* block_homeslide1 */
    .sequence-canvas .block_homeslider1 .slide-title {
        top: 17%;
    }
    
    /* block_homeslide2 */
    .sequence-canvas .block_homeslider2 .slide-title {
        top: 25%;
    }
    .sequence-canvas .block_homeslider2 .slide-link {
        top: 50%;
    }
    
    /* block_homeslide3 */
    .sequence-canvas .block_homeslider3 .slide-title {
        top: 10%;
        width: 65%;
    }
    .sequence-canvas .block_homeslider3 .slide-link {
        top: 35%;
    }
}

@media (max-width: 480px) {
    #homeslider-sequence {
        height: 165px;
    }
    .sequence-pagination {
        display: none !important;
    }
    
    /* block_homeslide1 */
    .sequence-canvas .block_homeslider1 .slide-title {
        top: 55%;
        line-height: 1.2;
    }
    
    /* block_homeslide2 */
    .sequence-canvas .block_homeslider2 .slide-title {
        top: 30%;
    }
    .sequence-canvas .block_homeslider2 .slide-link {
        top: 66%;
    }
    .sequence-canvas .block_homeslider2.animate-in .slide-title,
    .sequence-canvas .block_homeslider2.animate-in .slide-link {
        left: 48%;
    }
    
    /* block_homeslide3 */
    .sequence-canvas .block_homeslider3 .slide-title {
        top: 0;
    }
    .sequence-canvas .block_homeslider3 .slide-link {
        top: 47%;
    }
}

@media (max-width: 350px) {
   .product-essential .more-images {
        display: none; 
    }
}



/*!
 * bootstrap-select v1.1.1
 * http://silviomoreto.github.io/bootstrap-select/
 *
 * Copyright 2013 bootstrap-select
 * Licensed under the MIT license
 */

.bootstrap-select.btn-group,
.bootstrap-select.btn-group[class*="span"] {
    float: none;
    display: inline-block;
    margin-bottom: 10px;
    margin-left: 0;
}
.form-search .bootstrap-select.btn-group,
.form-inline .bootstrap-select.btn-group,
.form-horizontal .bootstrap-select.btn-group {
    margin-bottom: 0;
}

.bootstrap-select.btn-group.pull-right,
.bootstrap-select.btn-group[class*="span"].pull-right,
.row-fluid .bootstrap-select.btn-group[class*="span"].pull-right,
.row .bootstrap-select.btn-group[class*="span"].pull-right {
    float: right;
}

.input-append .bootstrap-select.btn-group {
    margin-left: -1px;
}

.input-prepend .bootstrap-select.btn-group {
    margin-right: -1px;
}

.bootstrap-select:not([class*="span"]) {
    width: 100%;
    margin: 0;
}

.bootstrap-select {
    /*width: 100%\9; IE8 and below*/
    width: 100% \0/; /*IE9 and below*/
}

.bootstrap-select > .btn {
    width: 100%;
}

.error .bootstrap-select .btn {
    border: 1px solid #b94a48;
}

.bootstrap-select.show-menu-arrow.open > .btn {
    z-index: 999;
}

.bootstrap-select .btn:focus {
    outline: thin dotted #333333 !important;
    outline: 5px auto -webkit-focus-ring-color !important;
    outline-offset: -2px;
}

.bootstrap-select.btn-group .btn .filter-option {
    overflow: hidden;
    position: absolute;
    left: 12px;
    right: 25px;
    text-align: left;
    text-transform: none;
}

.bootstrap-select.btn-group .btn .caret {
    position: absolute;
    top: 50%;
    right: 12px;
    margin-top: -2px;
}

.bootstrap-select .dropdown-menu li {
    margin: 0;
}

.bootstrap-select.btn-group > .disabled,
.bootstrap-select.btn-group .dropdown-menu li.disabled > a {
    cursor: not-allowed;
}

.bootstrap-select.btn-group > .disabled:focus {
    outline: none !important;
}

.bootstrap-select.btn-group[class*="span"] .btn {
    width: 100%;
}

.bootstrap-select.btn-group .dropdown-menu {
    min-width: 100%;
    -moz-box-sizing: border-box;
    -webkit-box-sizing: border-box;
    box-sizing: border-box;
}

.bootstrap-select.btn-group .dropdown-menu.inner {
    position: static;
    border: 0;
    padding: 0;
    margin: 0;
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
    -webkit-box-shadow: none;
       -moz-box-shadow: none;
            box-shadow: none;
}

.bootstrap-select.btn-group .dropdown-menu dt {
    display: block;
    padding: 3px 20px;
    cursor: default;
}

.bootstrap-select.btn-group .div-contain {
    overflow: hidden;
}

.bootstrap-select.btn-group .dropdown-menu li {
    position: relative;
}

.bootstrap-select.btn-group .dropdown-menu li > a.opt {
    padding-left: 35px;
}

.bootstrap-select.btn-group .dropdown-menu li > a {
    cursor: pointer;
}

.bootstrap-select.btn-group .dropdown-menu li > dt small {
    font-weight: normal;
}

.bootstrap-select.btn-group.show-tick .dropdown-menu li.selected a i.check-mark {
    display: inline-block;
    position: absolute;
    right: 15px;
    margin-top: 4px;
}
.bv3 .bootstrap-select.btn-group.show-tick .dropdown-menu li.selected a i.check-mark {
    margin-top: 8px;
}

.bootstrap-select.btn-group .dropdown-menu li a i.check-mark {
    display: none;
}

.bootstrap-select.btn-group.show-tick .dropdown-menu li a span.text {
    margin-right: 34px;
}

.bootstrap-select.btn-group .dropdown-menu li small {
    padding-left: 0.5em;
}

.bootstrap-select.btn-group .dropdown-menu li:not(.disabled) > a:hover small,
.bootstrap-select.btn-group .dropdown-menu li:not(.disabled) > a:focus small {
    color: #64b1d8;
    color: rgba(255,255,255,0.4);
}

.bootstrap-select.btn-group .dropdown-menu li > dt small {
    font-weight: normal;
}

.bootstrap-select.show-menu-arrow .dropdown-toggle:before {
    content: '';
    display: inline-block;
    border-left: 7px solid transparent;
    border-right: 7px solid transparent;
    border-bottom: 7px solid #CCC;
    border-bottom-color: rgba(0, 0, 0, 0.2);
    position: absolute;
    bottom: -4px;
    left: 9px;
    display: none;
}

.bootstrap-select.show-menu-arrow .dropdown-toggle:after {
    content: '';
    display: inline-block;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-bottom: 6px solid white;
    position: absolute;
    bottom: -4px;
    left: 10px;
    display: none;
}

.bootstrap-select.show-menu-arrow.dropup .dropdown-toggle:before {
  bottom: auto;
  top: -3px;
  border-top: 7px solid #ccc;
  border-bottom: 0;
  border-top-color: rgba(0, 0, 0, 0.2);
}

.bootstrap-select.show-menu-arrow.dropup .dropdown-toggle:after {
  bottom: auto;
  top: -3px;
  border-top: 6px solid #ffffff;
  border-bottom: 0;
}

.bootstrap-select.show-menu-arrow.pull-right .dropdown-toggle:before {
    right: 12px;
    left: auto;
}
.bootstrap-select.show-menu-arrow.pull-right .dropdown-toggle:after {
    right: 13px;
    left: auto;
}

.bootstrap-select.show-menu-arrow.open > .dropdown-toggle:before,
.bootstrap-select.show-menu-arrow.open > .dropdown-toggle:after {
    display: block;
}

.mobile-device {
    position: absolute;
    top: 0;
    left: 0;
    display: block !important;
    width: 100%;
    height: 100% !important;
    opacity: 0;
}
/*
 * jQuery FlexSlider v2.0
 * http://www.woothemes.com/flexslider/
 *
 * Copyright 2012 WooThemes
 * Free to use under the GPLv2 license.
 * http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Contributing author: Tyler Smith (@mbmufffin)
 */

 
/* Browser Resets */
.flex-container a:active,
.flexslider a:active,
.flex-container a:focus,
.flexslider a:focus  {outline: none;}
.slides,
.flex-control-nav,
.flex-direction-nav {margin: 0; padding: 0; list-style: none !important;} 

/* FlexSlider Necessary Styles
*********************************/ 
.flexslider {margin: 0; padding: 0;}
.flexslider .slides > li {display: none; -webkit-backface-visibility: hidden;} /* Hide the slides before the JS is loaded. Avoids image jumping */
.flexslider .slides img {width: 100%; display: block;}
.flex-pauseplay span {text-transform: capitalize;}

/* Clearfix for the .slides element */
.slides:after {content: "."; display: block; clear: both; visibility: hidden; line-height: 0; height: 0;} 
html[xmlns] .slides {display: block;} 
* html .slides {height: 1%;}

/* No JavaScript Fallback */
/* If you are not using another script, such as Modernizr, make sure you
 * include js that eliminates this class on page load */
.no-js .slides > li:first-child {display: block;}


/* FlexSlider Default Theme
*********************************/
.flexslider {margin: 0 0 60px; background: #fff; border: 4px solid #fff; position: relative; -webkit-border-radius: 4px; -moz-border-radius: 4px; -o-border-radius: 4px; border-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,.2); -webkit-box-shadow: 0 1px 4px rgba(0,0,0,.2); -moz-box-shadow: 0 1px 4px rgba(0,0,0,.2); -o-box-shadow: 0 1px 4px rgba(0,0,0,.2); zoom: 1;}
.flex-viewport {max-height: 2000px; -webkit-transition: all 1s ease; -moz-transition: all 1s ease; transition: all 1s ease; overflow:hidden;}
.loading .flex-viewport {max-height: 300px;}
.flexslider .slides {zoom: 1; padding:0; }

.carousel li {margin-right: 5px}


/* Direction Nav */
.flex-direction-nav {*height: 0;}
.flex-direction-nav li { list-style:none; margin:0; padding:0; }
.flex-direction-nav a {width: 30px; height: 30px; margin: -20px 0 0; display: block; background: url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/jquery/images/bg_direction_nav.png) no-repeat 0 0; position: absolute; top: 50%; z-index: 10; cursor: pointer; text-indent: -9999px; opacity: 0; -webkit-transition: all .3s ease;}
.flex-direction-nav .flex-next {background-position: 100% 0; right: -36px; }
.flex-direction-nav .flex-prev {left: -36px;}
.flexslider:hover .flex-next {opacity: 0.8; right: 5px;}
.flexslider:hover .flex-prev {opacity: 0.8; left: 5px;}
.flexslider:hover .flex-next:hover, .flexslider:hover .flex-prev:hover {opacity: 1;}
.flex-direction-nav .flex-disabled {cursor: default;}

/* Control Nav */
.flex-control-nav {width: 100%; position: absolute; bottom: 8px; text-align: center;}
.flex-control-nav li {margin: 0 3px; display: inline-block; zoom: 1; *display: inline;}
.flex-control-paging li a {width: 7px; height: 7px; display: block; background: #666; background: rgba(0,0,0,0.5); cursor: pointer; text-indent: -9999px; -webkit-border-radius: 20px; -moz-border-radius: 20px; -o-border-radius: 20px; border-radius: 20px; box-shadow: inset 0 0 3px rgba(0,0,0,0.3);}
.flex-control-paging li a:hover { background: #333; background: rgba(0,0,0,0.7); }
.flex-control-paging li a.flex-active { background: #000; background: rgba(0,0,0,0.9); cursor: default; }

.flex-control-thumbs {margin: 5px 0 0; position: static; overflow: hidden;}
.flex-control-thumbs li {width: 25%; float: left; margin: 0;}
.flex-control-thumbs img {width: 100%; display: block; opacity: .7; cursor: pointer;}
.flex-control-thumbs img:hover {opacity: 1;}
.flex-control-thumbs .flex-active {opacity: 1; cursor: default;}

@media screen and (max-width: 860px) {
  .flex-direction-nav .flex-prev {opacity: 1; left: 0;}
  .flex-direction-nav .flex-next {opacity: 1; right: 0;}
}
/*
    Colorbox Core Style:
    The following CSS is consistent between example themes and should not be altered.
*/
#colorbox, #cboxOverlay, #cboxWrapper{position:absolute; top:0; left:0; z-index:100000; overflow:hidden;}
#cboxOverlay{position:fixed; width:100%; height:100%;}
#cboxMiddleLeft, #cboxBottomLeft{clear:left;}
#cboxContent{position:relative;}
#cboxLoadedContent{overflow:auto; -webkit-overflow-scrolling: touch;}
#cboxTitle{margin:0;}
#cboxLoadingOverlay, #cboxLoadingGraphic{position:absolute; top:0; left:0; width:100%; height:100%;}
#cboxPrevious, #cboxNext, #cboxClose, #cboxSlideshow{cursor:pointer;}
.cboxPhoto{float:left; margin:auto; border:0; display:block; max-width:none; -ms-interpolation-mode:bicubic;}
.cboxIframe{width:100%; height:100%; display:block; border:0;}
#colorbox, #cboxContent, #cboxLoadedContent{box-sizing:content-box; -moz-box-sizing:content-box; -webkit-box-sizing:content-box;}

/* 
    User Style:
    Change the following styles to modify the appearance of Colorbox.  They are
    ordered & tabbed in a way that represents the nesting of the generated HTML.
*/
#cboxOverlay{background-color: black; filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=80) !important; opacity: 0.8 !important;}
#colorbox{outline:0;}
    #cboxContent{margin-bottom:32px; overflow:visible; background:#000;}
        .cboxIframe{background:#fff;}
        #cboxError{padding:50px; border:1px solid #ccc;}
        #cboxLoadedContent{background:#cecfc5; padding:4px; border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px;}
        #cboxLoadedContent > div {background:#fff; border-radius:4px; -moz-border-radius:4px; -webkit-border-radius:4px;}
        #cboxLoadingGraphic{background:url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/jquery/images/loading.gif) no-repeat center center;}
        #cboxLoadingOverlay{background:#000;}
        #cboxTitle{position:absolute; top:-22px; left:0; color:#000;}
        #cboxCurrent{position:absolute; top:-22px; right:205px; text-indent:-9999px;}

        /* these elements are buttons, and may need to have additional styles reset to avoid unwanted base styles */
        #cboxPrevious, #cboxNext, #cboxSlideshow, #cboxClose {border:0; padding:0; margin:0; overflow:visible; text-indent:-9999px; width:20px; height:20px; position:absolute; top:-20px; background:url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/jquery/images/controls.png) no-repeat 0 0;}
        
        /* avoid outlines on :active (mouseclick), but preserve outlines on :focus (tabbed navigating) */
        #cboxPrevious:active, #cboxNext:active, #cboxSlideshow:active, #cboxClose:active {outline:0;}

        #cboxPrevious{background-position:0px 0px; right:44px;}
        #cboxPrevious:hover{background-position:0px -25px;}
        #cboxNext{background-position:-25px 0px; right:22px;}
        #cboxNext:hover{background-position:-25px -25px;}
        #cboxClose{position:absolute; bottom:-35px; right:4px; width:30px; height:30px; background:url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/jquery/images/close.png) top right no-repeat; top:auto; outline:none; filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=70); opacity:0.7;}
        #cboxClose:hover{width:30px; height:30px; right:1px; background-position:0 0; filter:progid:DXImageTransform.Microsoft.Alpha(Opacity=100); opacity:1;}
        .cboxSlideshow_on #cboxPrevious, .cboxSlideshow_off #cboxPrevious{right:66px;}
        .cboxSlideshow_on #cboxSlideshow{background-position:-75px -25px; right:44px;}
        .cboxSlideshow_on #cboxSlideshow:hover{background-position:-100px -25px;}
        .cboxSlideshow_off #cboxSlideshow{background-position:-100px 0px; right:44px;}
        .cboxSlideshow_off #cboxSlideshow:hover{background-position:-75px -25px;}

/*
 * CSS Styles that are needed by jScrollPane for it to operate correctly.
 *
 * Include this stylesheet in your site or copy and paste the styles below into your stylesheet - jScrollPane
 * may not operate correctly without them.
 */

.jspContainer
{
	overflow: hidden;
	position: relative;
}

.jspPane
{
	position: absolute;
}

.jspVerticalBar
{
	position: absolute;
	top: 0;
	right: 2px;
	width: 3px;
	height: 100%;
    background: #eaeaea;
    border-radius: 3px;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
}

.jspHorizontalBar
{
	position: absolute;
	bottom: 2px;
	left: 0;
    width: 100%;
	height: 3px;
    background: #eaeaea;
    border-radius: 3px;
    -moz-border-radius: 3px;
    -webkit-border-radius: 3px;
}

.jspVerticalBar *,
.jspHorizontalBar *
{
	margin: 0;
	padding: 0;
}
.jspVerticalBar .jspTrack {
    margin: 0 -2px;
}
.jspHorizontalBar .jspTrack {
    margin: -2px 0;
}

.jspCap
{
	display: none;
}

.jspHorizontalBar .jspCap
{
	float: left;
}

.jspTrack
{
	position: relative;
}

.jspDrag
{
    background-image: url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/jquery/images/scroll_track_bg.png);
    background-position: center center;
    background-repeat: no-repeat;
	position: relative;
	top: 0;
	left: 0;
	cursor: pointer;
}

.jspHorizontalBar .jspTrack,
.jspHorizontalBar .jspDrag
{
	float: left;
	height: 100%;
}

.jspArrow
{
	background: #50506d;
	text-indent: -20000px;
	display: block;
	cursor: pointer;
}

.jspArrow.jspDisabled
{
	cursor: default;
	background: #80808d;
}

.jspVerticalBar .jspArrow
{
	height: 16px;
}

.jspHorizontalBar .jspArrow
{
	width: 16px;
	float: left;
	height: 100%;
}

.jspVerticalBar .jspArrow:focus
{
	outline: none;
}

.jspCorner
{
	background: #eeeef4;
	float: left;
	height: 100%;
}

/* Yuk! CSS Hack for IE6 3 pixel bug :( */
* html .jspCorner
{
	margin: 0 -3px 0 0;
}
.prettycheckbox,
.prettyradio {
    display: inline;
    padding:0;
    margin:0;
}

.prettycheckbox > a,
.prettyradio > a {
    width: 14px;
    height: 14px;
    display: block;
    float: left;
    cursor: pointer;
    margin:0;
    background-position: center center;
    background-repeat: no-repeat;
    margin-right: 8px;
    margin-top: 1px;
}

.prettycheckbox > a:focus,
.prettyradio > a:focus {outline: 0 none;}

.prettycheckbox label,
.prettyradio label {
    display: block;
    float: left;
    margin: -3px 5px 0;
    cursor: pointer;
}

.prettycheckbox.disabled > a,
.prettyradio.disabled > a,
.prettycheckbox.disabled label,
.prettyradio.disabled label{
    cursor:not-allowed;
}


/*========== Bootstrap Styles ==========*/
body {
    line-height: 1.42857;
}
*,
*:before,
*:after {
  -webkit-box-sizing: border-box;
     -moz-box-sizing: border-box;
          box-sizing: border-box;
}
li {
    line-height: inherit;
}
.alert, 
.alert h4 {
    font-size: 14px;
}
select {
    height: auto;
    line-height: auto;
}
select option {
    padding-right: 10px;
}
.label,
.badge {
    font-size: 14px;
    text-shadow: none;
    font-weight: normal;
    white-space: normal;
}
.radio, 
.checkbox {
    min-height: 12px;
    display: inline-block;
}
input[type="radio"], 
input[type="checkbox"] {
    margin-top: -2px;
}
th.label,
td.label {
    display: table-cell;
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
}
label {
    display: inline-block;
    font-weight: normal;
}
table {
    border-collapse: separate;
}
small {
    line-height: 1;
}
.dropdown-menu {
    max-width: inherit;
}
.dropdown-menu a {
    padding-left: 0;
    padding-right: 0;
}
[class^="icon-"], [class*=" icon-"] {
    background-repeat: no-repeat;
    display: inline-block;
    height: 14px;
    line-height: 14px;
    margin-top: 1px;
    vertical-align: text-top;
    width: 14px;
    padding: 0;
}
h1, h2, h3, h4, h5, h6, .h1, .h2, .h3, .h4, .h5, .h6 {
    line-height: 1.1;
}
form {
    margin-bottom: 0;
}

/*========== Common Styles ==========*/
form h2,
form h3,
.section h3 {
    font-size: 25px;
    margin-top: 20px;
    text-transform: uppercase;
}
.box h3,
.block .block-title, .slide-title {
    text-transform: uppercase;
}
.page-title h1,
.page-title h2,
.main h1,
.main h2,
.footer-banner h2 { 
    text-transform: none; 
}
a {
    transition:            color 300ms ease-in-out, background-color 300ms ease-in-out, opacity 150ms ease-in-out;
    -moz-transition:       color 300ms ease-in-out, background-color 300ms ease-in-out, opacity 150ms ease-in-out;
    -webkit-transition:    color 300ms ease-in-out, background-color 300ms ease-in-out, opacity 150ms ease-in-out;
    -o-transition:         color 450ms ease-in-out, background-color 300ms ease-in-out, opacity 150ms ease-in-out;
}
a:hover, a:focus {
    text-decoration: none;
}
p.desc {
    font-size: 16px;
    text-overflow: ellipsis;
    white-space: nowrap;
    overflow: hidden;
    clear: both;
    margin: 0 0 15px 0;
}
.dropdown-menu {
    border-radius: 0;
    -moz-border-radius: 0;
    -webkt-border-radius: 0;
    border-width: 0;
    right: auto;
    left: 0;
    width: auto;
    min-width: 0;
    margin: 0;
    padding: 0;    
    z-index: 99999;
}
.header-container .dropdown-menu,
.footer-container .dropdown-menu {
    right: 0;
    left: auto;
}
.dropdown-menu > li {
    white-space: nowrap;    
}
.dropdown-menu > li > a,
.dropdown-menu > li > div {
    padding: 8px 18px;
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
    border-width: 0;
    font-size: 13px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}
.dropdown-menu > li > a {
    display: block;
}
.header-container .dropdown-menu > li > a,
.header-container .dropdown-menu > li > div,
.footer-container .dropdown-menu > li > a,
.footer-container .dropdown-menu > li > div {
    padding: 8px 12px;
}
.dropdown-select {
    margin-right: 3px;
}
.container-shadow {
    background: transparent url(/_images/icons/container_shadow.png) center top no-repeat;
    height: 15px;
    bottom: -15px;
    position:absolute;
    left: 0;
    right: 0;
}
.calendar {
    z-index: 1000;
}

select, 
textarea, 
input.input-text, 
input[type="text"], 
input[type="password"], 
input[type="datetime"], 
input[type="datetime-local"], 
input[type="date"], 
input[type="month"], 
input[type="time"], 
input[type="week"], 
input[type="number"], 
input[type="email"], 
input[type="url"], 
input[type="search"], 
input[type="tel"], 
input[type="color"], 
.uneditable-input {
    -webkit-box-shadow: none;
       -moz-box-shadow: none;
            box-shadow: none;
    padding: 6px 6px;
    text-shadow: none;
    height: auto;
    vertical-align: bottom;
}

.btn,
.button,
.button-inverse,
.slider-arrow,
.button-arrow,
.dropdown .arrow,
.elastislide-next,
.elastislide-prev,
.button-up,
.button-down,
.form-list .bootstrap-select.btn-group .btn:hover .caret,
.form-list .bootstrap-select.btn-group .btn:focus .caret,
.buttons-set .back-link a,
.scrolltop,
.fraction-slider .prev,
.fraction-slider .next,
.bx-wrapper .bx-controls-direction a,
.button-tabs li a,
.tp-leftarrow,
.tp-rightarrow {
    font-size: 13px;
    display: inline-block;
    *display: inline;
    padding: 4px 12px;
    margin-bottom: 0;
    *margin-left: .3em;
    line-height: 20px;
    color: #f4f4f4;
    text-align: center;
    text-shadow: none;
    border-width: 1px;
    border-style: solid;
    border-color: #e6e6e6 #e6e6e6 #b3b3b3;
    border-color: rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.1) rgba(0, 0, 0, 0.25);
    vertical-align: middle;
    cursor: pointer;
    -webkit-border-radius: 4px;
       -moz-border-radius: 4px;
            border-radius: 4px;
    *zoom: 1;
    -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
       -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
    filter: progid:DXImageTransform.Microsoft.gradient(enabled=false) !important;
    background-position: center center;
    background-repeat: no-repeat;
}
.button,
.btn,
.slider-arrow,
.button-arrow,
.dropdown .arrow,
.elastislide-next,
.elastislide-prev,
.button-up,
.button-down,
.buttons-set .back-link a,
.scrolltop,
.fraction-slider .prev,
.fraction-slider .next,
.bx-wrapper .bx-controls-direction a,
.tp-leftarrow,
.tp-rightarrow {
    padding: 7px 12px 6px;
    text-shadow: none;
    transition:            color 450ms ease-in-out, background-color 150ms ease-in-out, opacity 600ms ease-in-out, background-position 300ms ease-in-out;
    -moz-transition:       color 450ms ease-in-out, background-color 150ms ease-in-out, opacity 600ms ease-in-out, background-position 300ms ease-in-out;
    -webkit-transition:    color 450ms ease-in-out, background-color 150ms ease-in-out, opacity 600ms ease-in-out, background-position 300ms ease-in-out;
    -o-transition:         color 450ms ease-in-out, background-color 150ms ease-in-out, opacity 600ms ease-in-out, background-position 300ms ease-in-out;
}
.btn-large,
.buttons-set button.button,
.buttons-set .back-link a {
    font-size: 17px;
    padding: 11px 19px;
    text-transform: uppercase;
}
.buttons-set .back-link a {
    display: inline-block;
}
.btn-mini {
    padding: 2px 6px;
}
.slider-arrow,
.button-arrow,
.dropdown .arrow,
.elastislide-next,
.elastislide-prev,
.button-up,
.button-down,
.prettycheckbox > a,
.prettyradio > a,
.scrolltop,
.fraction-slider .prev,
.fraction-slider .next,
.bx-wrapper .bx-controls-direction a,
.tp-leftarrow,
.tp-rightarrow {
    transition:            color 450ms ease-in-out, background-color 150ms ease-in-out, opacity 600ms ease-in-out, background-position 300ms ease-in-out;
    -moz-transition:       color 450ms ease-in-out, background-color 150ms ease-in-out, opacity 600ms ease-in-out, background-position 300ms ease-in-out;
    -webkit-transition:    color 450ms ease-in-out, background-color 150ms ease-in-out, opacity 600ms ease-in-out, background-position 300ms ease-in-out;
    -o-transition:         color 450ms ease-in-out, background-color 150ms ease-in-out, opacity 600ms ease-in-out, background-position 300ms ease-in-out;
}
table .button.nav {
    color: #333;
}
.dropdown-select .button {
    text-transform: none !important;
    background-color: #fff !important;
    padding-right: 40px;
}
.dropdown-select .arrow {
    width: 30px;
    height: 30px;
    position: absolute;
    top: 0;
    right: 0;
    padding: 0;
}
.button-asc,
.button-desc {
    width: 30px;
    height: 30px;
    padding: 0;
}
.button-viewall,
.button-grid,
.button-list {
    width: 35px;
    height: 30px;
    text-indent: -999em;
    padding: 0;
}
.button-viewall {
    width: 35px;
    height: 30px;
    padding: 0;
}
.button.next,
.button.prev {
    width: 30px;
    height: 30px;
    padding: 0;
}
.elastislide-next,
.elastislide-prev {
    width: 30px;
    height: 25px;
    padding: 0;
}
.button-up,
.button-down {
    width: 25px;
    height: 25px;
    text-indent: -999em;
}
.button-arrow.btn-remove {
    background-position: center center !important;
    width: 25px;
    height: 25px;
    padding: 0;
    margin: 0 5px;
}
.link-wishlist, 
.link-compare,
.link-friend,
.link-edit {
    width: 35px;
    height: 35px;
    padding: 0;
    text-indent: -999em;
}
.link-wishlist.no-image, 
.link-compare.no-image,
.link-friend.no-image,
.link-edit.no-image {
    background-image: none !important;
    background-color: transparent !important;
    text-indent: 0 !important;
    width: auto !important;
    height: auto !important;
}
.link-cart {
    background-color: transparent;
    border-width: 0;
}
.btn-remove {
    background-image: url(/_images/icons/btn_remove.png);
    margin: 0;
}
.btn-edit {
    background-image: url(/_images/icons/btn_edit.png);
    margin: 0;
}
.btn-remove:hover,
.btn-remove:focus,
.green .btn-remove:hover,
.green .btn-remove:focus  { background-position: 0 -11px; }
.blue .btn-remove:hover,
.blue .btn-remove:focus   { background-position: 0 -22px; }
.orange .btn-remove:hover,
.orange .btn-remove:focus { background-position: 0 -33px; }
.pink .btn-remove:hover,
.pink .btn-remove:focus   { background-position: 0 -44px; }
.btn-edit:hover,
.btn-edit:focus,
.green .btn-edit:hover,
.green .btn-edit:focus  { background-position: 0 -11px; }
.blue .btn-edit:hover,
.blue .btn-edit:focus   { background-position: 0 -22px; }
.orange .btn-edit:hover,
.orange .btn-edit:focus { background-position: 0 -33px; }
.pink .btn-edit:hover,
.pink .btn-edit:focus   { background-position: 0 -22px; }
.carousel-control.left,
.carousel-control.right,
.dropdown-menu > li > a:hover,
.dropdown-menu > li > a:focus,
.dropdown-submenu:hover > a,
.dropdown-submenu:focus > a,
.dropdown-menu > .active > a,
.dropdown-menu > .active > a:hover,
.dropdown-menu > .active > a:focus,
.dropdown-menu > .disabled > a:hover,
.dropdown-menu > .disabled > a:focus,
.btn-default,
.btn-primary,
.btn-warning,
.btn-danger,
.btn-success,
.btn-info,
.btn-inverse,
.navbar,
.navbar-inner,
.navbar .btn-navbar,
.navbar-inverse,
.navbar-inverse .navbar-inner,
.navbar-inverse .btn-navbar,
.progress,
.progress-bar,
.progress-bar-danger,
.progress-bar-success,
.progress-bar-info,
.progress-bar-warning,
.progress .bar,
.progress-danger .bar,
.progress .bar-danger,
.progress-success .bar,
.progress .bar-success,
.progress-info .bar,
.progress .bar-info,
.progress-warning .bar,
.progress .bar-warning,
.alert-success,
.alert-info,
.alert-warning,
.alert-danger,
.list-group-item.active,
.list-group-item.active:hover,
.list-group-item.active:focus,
.panel-default > .panel-heading,
.panel-primary > .panel-heading,
.panel-success > .panel-heading,
.panel-info > .panel-heading,
.panel-warning > .panel-heading,
.panel-danger > .panel-heading,
.well {
    background-image: none;
    filter: progid:DXImageTransform.Microsoft.gradient(enabled=false) !important;
}
.flexslider {
    background: transparent;
    -webkit-border-raidus: 0;
       -moz-border-radius: 0;
            border-radius: 0;
    -webkit-box-shadow: none;
       -moz-box-shadow: none;
            box-shadow: none;
    border-width: 0;
    margin: 0;
}
.flex-direction-nav a {
    opacity: 1;
}
.flexslider:hover .flex-next {
    opacity: 1;
}
.flexslider:hover .flex-prev {
    opacity: 1;
}
.flex-direction-nav .flex-next {
    background-position: center center;
    right: 0;
}
.flex-direction-nav .flex-next:hover,
.flex-direction-nav .flex-next:focus {
    background-position: center center;
}
.flex-direction-nav .flex-next:hover,
.flex-direction-nav .flex-next:focus,
.flexslider:hover .flex-next {
    right: 0;
}
.flex-direction-nav .flex-prev {
    right: 32px;
    left: auto;
    background-position: center center;
}
.flex-direction-nav .flex-prev:hover,
.flex-direction-nav .flex-prev:focus {
    background-position: center center;
}
.flex-direction-nav .flex-prev:hover,
.flex-direction-nav .flex-prev:focus,
.flexslider:hover .flex-prev {
    right: 32px;
    left: auto;
}
.block-list .flex-direction-nav a {
    top: 0;
    width: 28px;
    height: 20px;
    padding: 0;
    margin-top: -38px;
}
.block-list #block-related .flex-direction-nav a {
    margin-top: -87px;
}
.calendar .button {
    display: table-cell !important;
}
.std .button-tabs {
    margin: 0 0 15px;
    padding: 0;
    list-style: none;
}
.std .button-tabs li {
    float: left;
    margin-left: 3px;
    margin-bottom: 3px;
    list-style-position: outside;
}
.main .button-tabs li a {
    background-color: #575a59;
    color: #ffffff;
    padding: 4px 30px;
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
    -webkit-box-shadow: none;
       -moz-box-shadow: none;
            box-shadow: none;
}
.subtitle {
    margin-top: 0;
    margin-bottom: 15px;
}
.std .tab-content {
    margin-bottom: 25px;
}

/*========== Header Top ==========*/
.header-top {
    
}
.header-top .right {
    margin-right: 0;
}
.header-top-below {
    text-align: right;
}
.header-top-below p {
    padding: 10px;
    position: static !important;
}
.welcome-msg,
.login-link {
    float: right;
    padding: 8px 20px;
    margin: 0;
}
.welcome-msg {
    padding-right: 0;
    display: none;
}
.login-link {
    padding-left: 10px;
}

/*========== Header ==========*/
.header-container {
    position: relative;
    z-index: 10001;
}
.header {
    padding-top: 25px;
}
.header .logo {
    margin-bottom: 11px;
    position: relative;
    z-index: 1;
}
.header-right {
    float: right;
}
.header-container {
    -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.15);
       -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.15);
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.header-container.fixed {
    -webkit-box-shadow: none;
       -moz-box-shadow: none;
            box-shadow: none;
}
.header-container.fixed .header-menu {
    position: fixed;
    z-index: 1;
    top: 0;
    left: 0;
    right: 0;
    margin-top: 0;
    -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.15);
       -moz-box-shadow: 0 1px 3px rgba(0,0,0,0.15);
            box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.header-container.fixed #custommenu > .menu.active > .parentMenu > a,
.header-container.fixed #nav li.over a.level-top,
.header-container.fixed #nav-links li.over a.level-top {
    padding-bottom: 13px;
}
.header-container.fixed #custommenu > .menu.active,
.header-container.fixed #nav > li.over,
.header-container.fixed #nav-links > li.over {
    margin-bottom: 0;
}
.header-container.fixed #nav ul, 
.header-container.fixed #nav div,
.header-container.fixed #nav-links ul,
.header-container.fixed #nav-links div {
    top: 51px;
}
.header-container.fixed .header-menu #mini-cart,
.header-container.fixed .header-menu .block-currency,
.header-container.fixed .header-menu .block-language {
    display: none;
}
.header-container.fixed .header .logo {
    position: fixed;
    top: 6px;
    z-index: 10;
    margin-top: 0;
}
.header-container.fixed .header .logo img {
    height: 40px;
}

/*========== Top Links ==========*/
.toplinks {
    margin: 0;
    padding: 0;
}
.toplinks a {
    text-decoration: none;
    line-height: 20px;
    margin: 8px;
    display: inline-block;
    white-space: nowrap;
}

.toplinks a .count {
    letter-spacing: 1px;
}

.toplinks [class*="icon"] {
    width: 16px;
    height: 16px;
    padding: 0;
    background: transparent url(/_images/icons/icon_16x16.png);
    margin-right: 3px;
}

/* customize icons */
.toplinks .icon-mywishlist                  { background-position: 0px 0; }
.toplinks a:hover .icon-mywishlist          { background-position: 0px -16px; }
.blue .toplinks a:hover .icon-mywishlist    { background-position: 0px -32px; }
.orange .toplinks a:hover .icon-mywishlist  { background-position: 0px -48px; }
.pink .toplinks a:hover .icon-mywishlist    { background-position: 0px -64px; }

.toplinks .icon-myaccount                   { background-position: -16px 0; }
.toplinks a:hover .icon-myaccount           { background-position: -16px -16px; }
.blue .toplinks a:hover .icon-myaccount     { background-position: -16px -32px; }
.orange .toplinks a:hover .icon-myaccount   { background-position: -16px -48px; }
.pink .toplinks a:hover .icon-myaccount     { background-position: -16px -64px; }

.toplinks .icon-mycart                      { background-position: -32px 0; }
.toplinks a:hover .icon-mycart              { background-position: -32px -16px; }
.blue .toplinks a:hover .icon-mycart        { background-position: -32px -32px; }
.orange .toplinks a:hover .icon-mycart      { background-position: -32px -48px; }
.pink .toplinks a:hover .icon-mycart        { background-position: -32px -64px; }

.toplinks .icon-checkout                    { background-position: -48px 0 ; }
.toplinks a:hover .icon-checkout            { background-position: -48px -16px; }
.blue .toplinks a:hover .icon-checkout      { background-position: -48px -32px; }
.orange .toplinks a:hover .icon-checkout    { background-position: -48px -48px; }
.pink .toplinks a:hover .icon-checkout      { background-position: -48px -64px; }

/*========== Currency Swicher, Language Selector ==========*/
.block-currency, 
.block-language {
    float: right;
    margin-bottom: 0;
    margin-left: 4px;    
    height: 36px;
}
.block-currency .block-title, 
.block-language .block-title,
.store-switcher .block-title {
    display: none;
}
.block-currency a, 
.block-language a,
.store-switcher a {
    color: #fff;
    text-decoration: none;
    font-size: 14px;
    line-height: 1.4 !important;
}
.block-currency .block-content, 
.block-language .block-content,
.store-switcher .block-content {
    padding: 0;
}
.block-currency .block-content > a, 
.block-language .block-content > a,
.store-switcher .block-content > a {
    padding: 8px 12px;
    display: block;
    text-align: center;
}
.block-language .icon-flag {
    width: 16px;
    height: 12px;
    display: inline-block;
    margin-right: 5px;
    margin-top: 3px;
    background-position: center center;
    border: 1px solid #eee;
    padding: 0;
}
.block-currency .dropdown-toggle .symbol,
.block-currency .dropdown-menu .symbol {
    display: none;
    padding: 0;
    min-width: 18px;
    text-align: center;
}
.store-switcher {
    margin-bottom: 0;
    margin-top: 5px;
}
.store-switcher .block-content {
    float: right;
}
.store-switcher .dropdown-menu {
    top: auto;
    bottom: 38px;
    -webkit-box-shadow: 0 -5px 10px rgba(0, 0, 0, 0.2);
       -moz-box-shadow: 0 -5px 10px rgba(0, 0, 0, 0.2);
            box-shadow: 0 -5px 10px rgba(0, 0, 0, 0.2);
}
.store-switcher ul li,
.footer .store-switcher ul li {
    margin: 0;
}
.store-switcher li a {
    background-image: none !important;
}

/*========== Main Menu ==========*/
.mobile-header .header-menu {
    padding-bottom: 0;
}
.mobile-header #menu-button {
    padding-bottom: 7px;
}
.mobile-header.fixed #menu-button {
    padding-bottom: 7px !important;
}
.header-menu .container,
.header-menu .container-fluid {
    position: relative;
}
.nav-container {
    float: left;
    position: relative;
    z-index: 1;
}
.mobile-header .nav-container {
    position: static;
}
#nav, 
#nav-links {
    float: none;
    display: inline;
}
.header-menu-right .nav-container {
    float: right;
}
.header-menu-right div.eternal-custom-menu-popup {
    right: -36px;
    left: auto;
}
#menu-button a, 
div.menu a, 
#nav li a, 
#nav-links li a, 
.menu-mobile.level0 > .parentMenu > a {
    line-height: 25px;
}
#custommenu-mobile {
    position: static;
}
div.itemSubMenu a {
    line-height: 1.4;
    padding: 8px 10px 8px 15px;
}
div.itemSubMenu a,
.nav-container .menu-mobile .parentMenu a span {
    background: transparent url(/_images/icons/li_green.png) left center no-repeat;
}
.nav-container div.itemSubMenu a,
.nav-container .menu-mobile .parentMenu a span {
    background-image: url(/_images/icons/li_green.png);
}
.blue .nav-container div.itemSubMenu a,
.blue .nav-container .menu-mobile .parentMenu a span {
    background-image: url(/_images/icons/li_blue.png);
}
.orange .nav-container div.itemSubMenu a,
.orange .nav-container .menu-mobile .parentMenu a span {
    background-image: url(/_images/icons/li_black.png);
}
.orange .nav-container div.itemSubMenu a:hover,
.orange .nav-container div.itemSubMenu a:focus,
.orange .nav-container .menu-mobile .parentMenu a:hover span,
.orange .nav-container .menu-mobile .parentMenu a:focus span {
    background-image: url(/_images/icons/li_orange.png);
}
.pink .nav-container div.itemSubMenu a,
.pink .nav-container .menu-mobile .parentMenu a span {
    background-image: url(/_images/icons/li_black.png);
}
.pink .nav-container div.itemSubMenu a:hover,
.pink .nav-container div.itemSubMenu a:focus,
.pink .nav-container .menu-mobile .parentMenu a:hover span,
.pink .nav-container .menu-mobile .parentMenu a:focus span {
    background-image: url(/_images/icons/li_pink.png);
}
.nav-container .menu-mobile .parentMenu a span {
    padding-left: 15px;
}

.nav-container .menu-mobile.level0 > .parentMenu > a span,
.nav-container #custommenu-mobile a.level1 span {
    padding-left: 0;
    background-image: none !important;
}

.mobile-header #menu-button {
    margin: 0;
}

#custommenu-mobile .btn-navbar {
    padding: 7px;
    margin-right: 5px;
    margin-left: 12px;
    margin-top: -5px;
}

#custommenu-mobile .btn-navbar .icon-bar {
    display: block;
    width: 21px;
    height: 3px;
    -webkit-border-radius: 1px;
       -moz-border-radius: 1px;
            border-radius: 1px;
    -webkit-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.25);
       -moz-box-shadow: 0 1px 0 rgba(0, 0, 0, 0.25);
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.25);
}
#custommenu-mobile #menu-button {
    display: block;
    width: auto;
}
#menu-button a, 
#menu-button a:link, 
#menu-button a:visited {
    margin-left: 0;
    line-height: 30px;
}
.btn-navbar .icon-bar + .icon-bar {
    margin-top: 3px;
}
#custommenu-mobile #menu-content {
    border-width: 5px 0 0 0 !important;
    margin: 0;
}
.menu-mobile .parentMenu {
    border-width: 0;
}

/* Breadcrumbs */
.bv3 .breadcrumbs ul {
    margin: 0;
}

/*========== Quick Access ==========*/
.quick-access {
    margin-top: 8px;
    float: right;
    position: relative;
    z-index: 2;
}
.form-search label {
    display: none;
}
#search_mini_form {
    margin-bottom: 0;
    float: left;
    position: relative;
}
.form-search .input-text {
    width: 160px;
    position: absolute;
    right: 36px;
    padding: 8px;
    line-height: 17px;
    font-size: 13px;;
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
}
.header-menu-right .form-search .input-text {
    display: none;
}
.form-search .button,
.form-search .button:hover,
.form-search .button:focus {
    text-indent: -999em;
    width: 35px;
    height: 35px;
    margin-left: 1px;
    padding: 0;
    position: relative;
    z-index: 0;
}
#mini-cart {
    float: left;
    margin-left: 8px;
    margin-right: 0;
}
.header-both {
    margin-bottom: 10px;
}
.header-top #mini-cart,
.header-both #mini-cart {
    float: right;
    margin-left: 10px;
}
#mini-cart .dropdown-toggle {
    font-size: 14px;
    padding-top: 7px;
    height: 35px;
    text-transform: none;
}
.header-top #mini-cart .dropdown-toggle {
    height: 36px;
    padding-top: 8px;
}
.header-both .block-currency,
.header-both .block-language,
.header-both #mini-cart {
    margin-top: -6px;
}
.header-both .block-currency,
.header-both .block-language {
    height: 35px;
    padding-bottom: 7px;
}
#mini-cart .dropdown-toggle .price {
    font-weight: normal;
}
#mini-cart .icon-cart {
    background-position: 0 0;
    margin-right: 2px;
    margin-left: 2px;
    margin-top: 2px;
}
#mini-cart .dropdown-menu {
    width: 300px;
    padding: 20px;
    top: 43px;
}
.header-right #mini-cart .dropdown-menu,
.header-top #mini-cart .dropdown-menu {
    top: 42px;
}
#mini-cart .block-subtitle {
    font-weight: normal;
    margin-bottom: 15px;
}
#mini-cart .product-name a { 
    font-size:16px; 
}
#mini-cart .mini-products-list .product-name {
    padding-right: 35px;
}
#mini-cart .mini-products-list .product-name,
#mini-cart .mini-products-list .price {
    /*padding-right: 35px;*/
    font-size: 16px;
}
#mini-cart .mini-products-list img {
    /*width: 100%;
    height: auto;*/
}
.block .mini-products-list .item {
    position: relative;
    padding: 10px 0;
}
.mini-products-images-list .item {
    display: inline-block;
    width: 70px;
}
.mini-products-list .product-details .btn-remove {
    position: absolute;
    top: 8px;
    right: 2px;
}
.block-compare .mini-products-list .product-details .btn-remove,
.block-wishlist .mini-products-list .product-details .btn-remove {
    top: -5px;
}
.mini-products-list .product-details .btn-edit {
    position: absolute;
    top: 8px;
    right: 20px;
}
#mini-cart .prices-wrap {
    padding-top: 7px;
    padding-right: 100px;
    font-weight: bold;
    font-size: 15px;
    text-transform: uppercase;
}
#mini-cart .prices {
    margin: 6px 0;
}
#mini-cart .actions {
    float: right;
}
#mini-cart .actions .button {
    display: block;
    width: 100px;
    text-align: center;
    margin-bottom: 3px;
}
.block_mini_cart_above_products {
    margin-bottom: 15px;
}

/*========== Footer ==========*/
.footer,
.footer h3 {
    line-height: 2;
}
.brand-slider-wrap {
    padding: 0 0 30px 0;
}
.footer-top {
    padding: 20px 10px 50px;
}
.footer-top > div {
    position: relative;
}
.footer-bottom {
    padding: 20px 10px;
    margin-top: 0;
}
.footer h3,
.footer .title {
    margin-bottom: 20px;
    margin-top: 50px;
    text-transform: uppercase;
}
.footer ul li {
    margin: 5px 0;
}
.footer ul li a {
    background: transparent url(/_images/icons/li_green.png) left center no-repeat;
    padding-left: 15px;
    line-height: 2;
}
.green .footer ul li a  { background-image: url(/_images/icons/li_green.png); }
.blue .footer ul li a   { background-image: url(/_images/icons/li_blue.png); }
.orange .footer ul li a { background-image: url(/_images/icons/li_white.png); }
.orange .footer ul li a:hover,
.orange .footer ul li a:focus { background-image: url(/_images/icons/li_orange.png); }
.pink .footer ul li a   { background-image: url(/_images/icons/li_black.png); }
.pink .footer ul li a:hover,
.pink .footer ul li a:focus { background-image: url(/_images/icons/li_pink.png); }
.footer .copyright {
    text-align: right;
}
.footer .copyright address {
    padding: 15px 0 0;
    margin-bottom: 0;
}
.scrolltop {
    height: 38px;
    width: 38px;
    padding: 0;
    display: inline-block;
}
#topcontrol {
    z-index: 10000;
}

/*========== Footer Facebook like box ==========*/
.footer .fblike-box {
    background-color: transparent; 
    border-width: 0;
    position: relative;
}
.facebook-like-wrap {
    overflow: hidden;
}
.footer .fblike-box .icon-fblike {
    background-position: 0 0;
    margin-top: 1px;
    margin-right: 5px;
}
.footer .fblike-box .button {
    float: right;
    margin-top: 8px;
    text-transform: none;
}
.fb-persons {
    margin-left: -20px;
    margin-right: -20px;
}
.fb-person {
    float: left;
    text-align: center;
    width: 60px;
    height: 100px;
    margin: 0 0 0 20px;
    font-size: 13px;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}
.fb-person img {
    width: 100%;
    height: auto;
}
.fb-person a,
.fb-person span {
    display: block;
}
.fb-person a:hover,
.fb-person span:hover {
    -webkit-box-shadow: 0 0 2px rgba(0, 0, 0, 0.25);
       -moz-box-shadow: 0 0 2px rgba(0, 0, 0, 0.25);
            box-shadow: 0 0 2px rgba(0, 0, 0, 0.25);
}
.footer .fblike-box a:hover img,
.footer .fblike-box span:hover img {
    opacity: 0.85;
}

/*========== Footer Twitter Tweets ==========*/
.footer-tweets {
    -webkit-box-shadow: 0 -1px 1px rgba(255, 255, 255, 0.3);
       -moz-box-shadow: 0 -1px 1px rgba(255, 255, 255, 0.3);
            box-shadow: 0 -1px 1px rgba(255, 255, 255, 0.3);
}
.footer-tweets #twitter-slider {
    position: relative;
    padding: 0 140px 0 80px;
}
.footer-tweets .twitter-slider1 {
    text-align: center;
    padding: 0 70px;
}
.footer-tweets .photo img {
    -webkit-border-radius: 24px;
       -moz-border-radius: 24px;
            border-radius: 24px;
    -webkit-box-shadow: inset 0px 0px 3px 1px rgba(0,0,0,0.2);
       -moz-box-shadow: inset 0px 0px 3px 1px rgba(0,0,0,0.2);
            box-shadow: inset 0px 0px 3px 1px rgba(0,0,0,0.2);
    margin-bottom: 20px;
    margin-top: 30px;
}
.footer-tweets .text {
    font-size: 16px;
    margin-bottom: 10px;
}
.footer-tweets .date {
    font-style: italic;
    font-size: 14px;
    margin-bottom: 30px;
}
.footer-tweets .twitter-slider2 .text {
    margin-top: 40px;
    margin-bottom: 5px;
}
.footer-tweets ul li {
    margin: 0;
}
#twitter-slider .flex-direction-nav a {
    width: 35px;
    height: 25px;
    padding: 0;
    top: 55%;
    border-width: 0;
    background-color: transparent;
}
#twitter-slider .flex-direction-nav .flex-next {
    right: 10px;
}
.twitter-slider1 .flex-direction-nav .flex-prev {
    right: auto;
    left: 10px;
}
.twitter-slider2 .flex-direction-nav .flex-prev {
    right: 47px;
}
.footer-tweets .twitter-icon {
    position: absolute;
    left: 10px;
    width: 45px;
    height: 38px;
    padding: 0;
    display: block;
}
#twitter-footer-slider * {
    line-height: 1.5;
}
#twitter-footer-slider .photo {
    text-align: center;
    margin-bottom: 15px;
}
#twitter-footer-slider .date {
    font-size: 12px;
    margin-bottom: 15px;
}
.footer-tweets .text a,
#twitter-footer-slider .text a {
    background-image: none !important;
    padding-left: 0;
}

/*========== Footer Subscribe ==========*/
.footer-subscribe .block-subscribe {
    margin-bottom: 0;
    border-width: 0;
    background-color: transparent;
    padding: 30px 0 25px;
}
.footer-subscribe .block-title {
    float: left;
    padding: 8px 0 10px;
    margin: 0;
}
.footer-subscribe form {
    float: right;
}
.footer-subscribe .input-box input {
    width: 370px;
    padding: 8px 20px;
    font-size: 16px;
    height: 44px;
}
.footer-subscribe .input-box,
.footer-subscribe .actions {
    float: left;
    padding: 0;
    margin-left: 10px;
}

/*========== Main Content ==========*/
.cms-index-index .main {
    padding-top: 50px;
}
.main-slider {
    overflow: hidden;
    position: relative;
}
.main-slider .shadow-left,
.main-slider .shadow-right {
    position: absolute;
    top: 0;
    bottom: 0;
    background-color: #f9f9f9;
    opacity: 0.5;
}
.main-slider .shadow-left {
    left: 0;
}
.main-slider .shadow-right {
    right: 0;
}
.main-banner {
    padding: 50px 0 0;
    text-align: center;
}
.main-banner .container {
    padding-left: 0;
    padding-right: 0;
}
.main-banner img {
    width: 100%;
    height: auto;
    margin-bottom: 20px;
}
.main-banner img:hover {
    opacity :0.85; 
    filter: alpha(opacity=85);
}
.main-container {
    
}
.main {
    padding-top: 20px;
    padding-bottom: 0;
    position: relative;
}
.main.nobc {
    padding-top: 40px;
}
.main-content {
    padding: 0 0 50px;
}
.bv3.bsl .main-content {
    padding-left: 15px;
}
.bv3.bsr .main-content {
    padding-right: 15px;
}
.main-content-right {
    float: right !important;
}
.main h2.subtitle,
.main .widget .widget-title h2,
.main .page-title h1,
.footer-banner h2.subtitle {
    font-size: 40px;
    line-height: 1;
    padding-left: 8px;
    position: relative;
}
.main h2.subtitle .line,
.main .widget .widget-title h2 .line,
.main .page-title h1 .line,
.footer-banner h2.subtitle .line {
    position: absolute;
    left: 240px;
    right: 30px;
    top: 23px;
}
.line-title {
    display: inline-block;
    margin-right: 20px;
}
.footer-banner h2.subtitle .line {
    left: 260px;
}
.page-title .back-link, 
.page-title button.button {
    margin-top: 24px;
}
.page-title .link-print {
    background-position: left center;
    color: #888;
}
.std ul, 
.std ol, 
.std dl, 
.std p, 
.std address, 
.std blockquote {
    line-height: 1.6;
}

/*========== Sidebar, Block ==========*/
.sidebar {
    padding: 0 0 50px;
}
.sidebar-left {
    margin-left: 0 !important;
}
.bv3 .sidebar-right {
    padding-left: 15px;
}
.bv3 .sidebar-left {
    padding-right: 15px;
}
.sidebar ol,
.sidebar ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.sidebar .block-content li {
    padding: 5px 0;
}
.sidebar .block-venedor-ads .block-content li {
    padding: 0;
}
.sidebar .block dt {
    padding: 15px 45px 15px 15px;
    text-transform: uppercase;
}
.sidebar .block dd {
    padding: 15px;
    margin: 0;
}
.sidebar address {
    margin-bottom: 0;
}
.block .slider-title {
    overflow: hidden; 
    text-overflow: ellipsis; 
    white-space: nowrap; 
}
.block-layered-nav .block-subtitle {
    font-size: 18px;
}
.block-layered-nav dt {
    font-size: 16px;
}
.block-layered-nav .currently ol {
    margin: 0;
    padding: 10px 15px;
}
.block-layered-nav .currently span.value {
    font-weight: bold;
}
.block-layered-nav li {
    padding: 5px 0;
}
.block-layered-nav .label {
    margin-right: 5px;
}
.block-layered-nav .button-arrow {
    width: 25px;
    height: 25px;
    padding: 0;
    margin-top: -2px;
    position: absolute;
    right: 15px;
}

#narrow-by-list2 dd {
    display: block !important;
}
#venedor-ads {
    position: relative;
}
#venedor-ads p {
    padding: 0;
    margin: 0;
}
.block-list .slider-title {
    padding-right: 62px;
}
.sidebar .block-compare.mini-products-list .item {
    padding-top: 20px;
    padding-bottom: 10px;
}
.sidebar .block-compare.mini-products-list .btn-remove {
    top: -5px;
}
.sidebar .mini-products-list .block .block-title {
    padding-right: 32px;
}
.block-list .old-price {
    margin-bottom: 3px;
}
.block-list .old-price .price {
    font-size: 16px;
}
.block-list .price-label {
    font-size: 14px;
}
.block .block-content .mini-products-list .item {
    padding: 10px 0;
}
.mini-products-list .ratings {
    display: inline-block;
    margin: 3px 0;
}
.mini-products-list .ratings .rating-links {
    display: none;
}
.block-subscribe {
    text-align: center;
    padding: 20px 15px 15px;
}
.form-subscribe-header {
    font-size: 16px;
    margin-bottom: 15px;
}
.block-subscribe .input-text {
    width: 90%;
}
.block-subscribe .actions {
    text-align: center;
}

/*========== Category ==========*/
.category-banner {
    padding: 25px 0;
    font-size: 16px;
    position: relative;
}
.category-banner ul {
    margin: 0;
    padding: 0;
    list-style: none;
}
.category-banner p {
    line-height: 1.4;
}
#category-banner-slider .product-image img,
.category-banner .category-image img {
    width: 100%;
    height: auto;
}
#category-banner-slider .product-image,
.category-banner .category-image {
    position: relative;
    margin: 20px auto;
    width: 380px;
}
.main-content .category-banner .category-image {
    width: 100%;
}
#category-banner-slider .product-image {
    background-color: #fff;
    border: 1px solid #e1e1e1;
    -webkit-box-shadow: 0 0 7px rgba(0, 0, 0, 0.2);
       -moz-box-shadow: 0 0 7px rgba(0, 0, 0, 0.2);
            box-shadow: 0 0 7px rgba(0, 0, 0, 0.2);
}
#category-banner-slider .product-details,
.category-banner .category-details {
    padding: 30px 25px 30px 65px;
}
.sales {
    text-shadow: none;
}
#category-banner-slider .sales {
    position: absolute;
    padding: 10px 20px;
    line-height: inherit;
    font-size: 25px;
    left: -3px;
    top: 20px;
}
#category-banner-slider .slide-shadow {
    position: absolute;
    left: -5px;
    right: -5px;
    bottom: -15px;
    height: 15px;
    background: transparent url(/_images/icons/category_banner_slider_shadow.png) center top no-repeat;
}
.category-banner .product-name,
.category-banner .category-title h1 {
    font-size: 43px;
    line-height: 45px;
    margin: 35px 0 15px;
}
.category-banner .price {
    font-size: 25px;
    font-weight: bold;
    margin: 10px 0 15px;
}
.category-banner .button {
    font-size: 15px;
    padding: 12px 20px;
    margin-top: 20px;
}
.slider-arrow,
.main-slider .fraction-slider .prev,
.main-slider .fraction-slider .next,
.main-slider .bx-wrapper .bx-controls-direction a,
.main-slider .tp-leftarrow,
.main-slider .tp-rightarrow {
    position: absolute;
    top: 45%;
    width: 60px;
    height: 45px;
    padding: 0px;
}
.main-slider .bx-wrapper .bx-controls-direction a {
    width: 75px;
    height: 60px;  
}
.slider-arrow .shadow {
    display: block;
    width: 7px;
    height: 51px;
    position: absolute;
    top: -3px;
}
.slider-arrow.prev {
    left: -3px;
}
.slider-arrow.prev .shadow {
    background-image: url(/_images/icons/icon_arrow_prev_shadow.png);
    right: -8px;
}
.slider-arrow.next {
    right: -3px;
}
.slider-arrow.next .shadow {
    background-image: url(/_images/icons/icon_arrow_next_shadow.png);
    left: -8px;
}
.main-content .category-banner {
    border-width: 0;
    margin: 0;
    padding: 0;
    background: transparent;
}
.main-content .category-banner .category-image img {
    width: 100%;
}
.main-content .category-banner .category-wrap {
    position: absolute;
    top: 30px;
    left: 50px;
    width: 320px;
}
.main-content .category-banner .category-title {
    margin: 0;
    padding: 0;
}
.main-content .category-banner .category-title h1 {
    font-size: 35px;
    margin-top: 0;
    margin-bottom: 15px;
    padding-left: 0;
    border-width: 0;
}
.main-content .category-banner .category-description {
    font-size: 16px;
}
#category-full-description {
    display: none;
    font-size: 16px;
    color: #585858;
    padding: 25px;
}
#colorbox #category-full-description {
    display: block;
}
.main-content .category-banner .button {
    margin-top: 10px;
}
#category-full-description h1 {
    margin-bottom: 10px;
    margin-top: 0;
    font-size: 35px;
    color: #585858;
}
.products-grid, 
.products-list,
ul.products-grid,
ul.products-list,
ol.products-grid,
ol.products-list { 
    margin: 0 !important; 
    padding: 0 !important; 
    list-style: none !important;
}
.products-grid .item-first {
    margin-left: 0;
}
.products-grid li.item {
    margin-top: 10px;
    margin-bottom: 15px;
}
.bv3 .products-grid li.item {
    padding-left: 10px;
    padding-right: 10px;
}
.products-grid .item-inner {
    padding: 20px 5px 21px;
    border: 1px solid #e8e8e8;
}
.products-grid .item-active .item-inner {
    padding: 20px 5px 20px;
}
.products-grid .item-active.addlinks-block .item-inner {
    position: absolute;
    height: auto !important;
    left: 10px;
    right: 10px;
}
.flexslider.products-grid .item-active.addlinks-block .item-inner {
    position: static;
}
.flexslider.products-grid .item,
.flexslider.products-grid .item-inner {
    background-color: transparent !important;
    border: medium none !important;
    -webkit-box-shadow: none !important;
       -moz-box-shadow: none !important;
            box-shadow: none !important;
    padding: 0 !important;
}
.flexslider.products-grid .item-inner {
    padding: 0 5px !important;
}
.bv3 .products-grid .product-image {
    width: <?php echo $image_listing_width;?>px;
    height:<?php echo $image_listing_height;?>px;
    max-width:100%
}
.products-grid .product-image img {
    width:100%
}
.product-image img.hover-image {
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    filter: alpha(opacity=0);
    display:none;
}
.products-grid.upsell-products .item-active .item-inner,
.products-grid.crosssell-products .item-active .item-inner {
    border-width: 0 !important;
    padding: 21px 5px 21px;
    -webkit-box-shadow: none;
       -moz-box-shadow: none;
            box-shadow: none;
}
.flexslider.products-grid .item-active {
    padding: 0 !important;
}

.flexslider.products-grid .item-active .item-inner {
    padding: 0 5px !important;
}
.flexslider.products-grid .addlinks-block .item-inner,
.flexslider.products-grid .item-active.addlinks-block .item-inner {
    padding-bottom: 20px !important;
}
.products-grid .btn-cart {
    white-space: nowrap;
    overflow: hidden;
    vertical-align: top;
    margin: 0;
}
.products-grid .item-active .btn-cart,
body.mobile .products-grid .btn-cart,
.products-grid .hover-disable .btn-cart {
    text-indent: -999em;
    width: 35px !important;
}
.products-grid .item-active .addlinks-block .btn-cart, 
body.mobile .products-grid .addlinks-block .btn-cart, 
.products-grid .hover-disable.addlinks-block .btn-cart {
    width: auto !important;
}
.products-grid .addlinks-block .btn-cart {
    display: block !important;
    background-image: none !important;
    text-indent: 0 !important;
    width: auto !important;
    margin: 0 auto 15px !important;
}
.products-grid .ratings {
    margin-top: 20px;
}
.products-grid .ratings .rating-box {
    margin-left: 0;
    margin-right: 0;
}
.products-grid .ratings .amount {
    display: none;
    white-space: nowrap;
    overflow: hidden;
}
body.mobile .products-grid .ratings .amount,
.products-grid .hover-disable .ratings .amount {
    display: inline-block !important;
    width: 80px !important;
    margin-left: 0 !important;
}
.products-grid .availability {
    margin-top: 8px;
    vertical-align: top;
    display: inline-block;
}
.products-grid .actions {
    height: 35px;
    position: relative;
}
.products-grid .addlinks-block .actions {
    height: auto;
}
.products-grid .add-to-links {
    display: none;
    overflow: hidden;
    white-space: nowrap;
}
body.mobile .products-grid .add-to-links,
.products-grid .hover-disable .add-to-links {
    width: 101px !important;
    display: inline-block !important;
    padding-left: 15px !important;
    margin-left: 0 !important;
}
.products-grid .addlinks-block .add-to-links {
    display: block !important;
    padding-left: 0 !important;
    margin-left: 0 !important;
    height: 0;
}
body.mobile .products-grid .addlinks-block .add-to-links,
.products-grid .hover-disable.addlinks-block .add-to-links {
    display: block !important;
    padding-left: 0 !important;
    margin-left: 0 !important;
    height: auto !important;
    width: auto !important;
}
.flexslider.products-grid .addlinks-block .add-to-links {
    position: absolute;
    left: 0;
    right: 0;
    width: 100% !important;
}
.products-list .add-to-links a {
    display: inline-block;
}
.products-list .add-to-links {
    padding-left: 10px;
}
.products-grid .reviews-wrap {
    height: 30px;
    text-align: center;
}
.products-grid .ratings .rating-box {
    float: none;
    display: inline-block;
    vertical-align: top;
    margin-bottom: 0;
}

.price-slider .priceTextBox {
    width: 60px;
}
.products-list .product-image img,
.products-list .category-image img {
    width: 100%;
    height: auto;
}

/*========== Product Styles ==========*/
.product-image img,
.category-image img {
            transition: opacity 300ms ease-in-out;
       -moz-transition: opacity 300ms ease-in-out;
    -webkit-transition: opacity 300ms ease-in-out;
         -o-transition: opacity 300ms ease-in-out;
}
.sidebar .product-image img,
.sidebar .category-image img {
    width: 100%;
    height: auto;
}
.products-grid .product-image img:hover { 
    opacity: 1; 
    filter: alpha(opacity=100); 
}
.products-grid .item-active .product-image img.primary-image,
.products-list .item-active .product-image img.primary-image {
    /*opacity: 0 !important;
    filter: alpha(opacity=0) !important; 
    display:none;*/
}
.products-grid .item-active .product-image img.hover-image,
.products-list .item-active .product-image img.hover-image {
    opacity: 1 !important;
    filter: alpha(opacity=100) !important; 
    display:none;
}
.product-essential .product-img-box {
    position: relative;
    padding-right: 0;
}
.product-essential .product-image {
    margin-left: 14px;
    margin-right: auto;
    margin-bottom: 30px;
    width: 404px;
    position: relative;
    float: left;
}
.product-essential .product-img-box * {
    -webkit-box-sizing: content-box;
       -moz-box-sizing: content-box;
            box-sizing: content-box;
}
.product-essential .product-image.no-gallery {
    margin-left: auto;
}
.product-essential .product-image img {
    /*width: 100%;*/
    height: auto;
    margin:auto;
}
.product-essential .button-viewall {
    position: absolute;
    left: 5px;
    bottom: 5px;
    text-indent: -999em;
    z-index: 9999;
}
.product-essential .more-images {
    width: 92px;
    float: left;
    overflow: hidden;
    margin-top: -20px;
    padding-top: 20px;
    padding-bottom: 20px; 
}
.product-essential .more-images .normal-list {
    margin-top: -6px;
}
.product-essential .more-images ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.product-essential .elastislide-vertical {
    margin-top: -6px;
}
.product-essential .more-images img {
    padding: 6px 0;
    width: 100%;
}
.product-image .tax-details {
    display: none;
}
.product-image .delivery-time-details {
    display: none;
}
.elastislide-vertical nav span.elastislide-next,
.elastislide-vertical nav span.elastislide-prev {
    background-position: center center !important;
}
.cloud-zoom-big {
    background-color: #fff;
}
.bv3 .product-essential .product-shop {
    padding-left: 15px;
}
.product-name h1 {
    font-size: 25px;
    font-weight: bold;
    line-height: 1.4;
    margin: 10px 0;
}
.product-shop > .price-box,
.product-shop > .price-box-bundle {
    display: none;
}
.product-view .product-shop .data-table .price {
    color: inherit;
}
.product-shop .price-review,
.products-grid .price-review {
    margin: 15px 0 5px;
}
.products-grid .price-review {
    margin: 15px 0 0;
}
.products-grid .price-review .price-box,
.products-grid .price-review .review-wrap {
    margin-bottom: 5px;
}
.products-grid .price-review .minimal-price-link {
    display: none !important;
}
.product-shop .price-review > .price-box,
.product-shop .price-review > .price-box-bundle,
.product-shop .price-review > .ratings,
.products-grid .price-review > .price-box,
.products-grid .price-review > .price-box-bundle,
.products-grid .price-review .reviews-wrap {
    display: inline-block;
    float: none;
    width: auto;
    height: auto;
}
.product-essential .product-shop .price-review > .price-box,
.product-essential .product-shop .price-review > .price-box-bundle,
.product-essential .product-shop .price-review > .ratings {
    display: block;
    margin-bottom: 10px;
}
.product-shop .price-review > .price-box,
.products-grid .price-review > .price-box {
    position: static;
    background-color: transparent;
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
    width: auto;
    height: auto;
    font-size: 20px;
    margin-right: 30px;
    text-align: left;
}
.products-grid .price-review > .price-box {
    margin-right: 0;
}
.product-shop .price-review > .price-box *,
.products-grid .price-review > .price-box * {
    display: inline;
}
.product-shop .price-review > .price-box p,
.products-grid .price-review > .price-box p {
    margin: 0;
}
.product-shop .price-review > .price-box .price,
.products-grid .price-review > .price-box .price {
    margin-right: 10px;
}
.product-shop .price-review > .price-box .old-price,
.products-grid .price-review > .price-box .old-price {
    font-size: 18px;
}
.product-shop .price-review > .ratings,
.products-grid .price-review .ratings {
    margin-bottom: -2px;
    margin-top: 0;
    vertical-align: bottom;
}
.products-grid .price-review .reviews-wrap .amount {
    display: none !important;
}
.product-view .product-shop .price {
    font-size: 25px;
}
.product-view .product-shop .old-price .price {
    font-size: 20px;
}
.add-to-box .addthis-icons { 
    margin-top: 2px; 
    float: left; 
    width: 245px;
    margin-left: 15px;
}
.add-to-box .addthis-icons * {      
    -webkit-box-sizing: content-box; 
       -moz-box-sizing: content-box; 
            box-sizing: content-box; 
}
.add-to-box .addthis-icons .addthis_toolbox { 
    
}
.add-to-box .addthis-icons > span { 
    display: none; 
    float: left; 
    line-height: 32px; 
    margin-right: 7px;
}
.product-collateral {
    /*margin-top: 30px;*/
}
.product-tabs {
    margin-bottom: 50px;
}
#product-tabs,
#cart-tabs {
    margin: 0;
    padding: 0;
    position: relative;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
}
#product-tabs > dt,
#cart-tabs > dt {
    text-transform: uppercase;
    font-size: 15px;
    padding: 16px 20px;
    width: 210px;
    cursor: pointer;
    color: #868883;
}
#product-tabs > dt.open,
#cart-tabs > dt.open {
    position: relative;
    background-color: #fff;
    width: 211px;
    z-index: 10;
    color: #565656;
}    
#product-tabs > dd,
#cart-tabs > dd {
    display: none;
    position: absolute;
    left: 210px;
    right: 0;
    top: 0;
    background-color: #fff;
    margin: 0;
    padding: 30px;
    line-height: 1.8;
    border-bottom-width: 0 !important;
}
#product-tabs > dd h2,
#cart-tabs > dd h2 {
    display: none;
}
.box-reviews dl {
    margin-bottom: 0;
}
.box-reviews dt {
    font-size: 18px;
    padding: 20px 0 10px;
}
.review-title {
    margin-top: 0;
    line-height: 1.4;
    padding-bottom: 15px;
    margin-bottom: 0;
}
.review-title span {
    display: block;
}
.box-reviews > .rating-box {
    position: absolute;
    top: 30px;
    right: 30px;
}
.box-reviews dd {
    margin-left: 0;
    padding-bottom: 20px;
}
.box-reviews .author {
    font-style: italic;
    font-size: 14px;
    font-weight: bold;
    margin-right: 5px;
}
.box-reviews .date {
    color: #bdbdbd;
    font-size: 14px;
    font-style: italic;
}
.box-reviews .ratings-table {
    margin: 10px 0;
}
.ratings-table .rating-box {
    background-image: url(/_images/icons/bkg_rating_small.png);
    height: 13px;
    width: 70px;
    margin-bottom: 0;
}
.ratings-table .rating-box .rating {
    background-image: url(/_images/icons/bkg_rating_small.png);
    height: 13px;
}
.box-reviews ul {
    margin: 0;
    padding: 0;
    list-style: none;
}
#product-review-table {
    margin: 20px 0;
}
#product-review-table th,
#product-review-table td {
    text-align: center;
}
.box-reviews .form-add h3 {
    margin-top: 30px;
    font-size: 20px;
}
.box-reviews .form-add h4 {
    font-size: 17px;
}
.box-reviews textarea {
    width: 95%;
}
.upsell-products .item .product-image,
.crosssell-products .item .product-image {
    width: 190px;
    margin-left: auto;
    margin-right: auto;    
}
#product-tabs > dd h2.product-name {
    display: block;
}
#product-tabs .crosssell .flex-direction-nav a {
    width: 50px;
    height: 40px;
    top: 112px;
}
#product-tabs .crosssell .flex-direction-nav .flex-prev {
    right: auto;
    left: -10px;
}
#product-tabs .crosssell .flex-direction-nav .flex-next {
    right: -10px;
}
.box-up-sell .flex-direction-nav a,
.box-cross-sell .flex-direction-nav a,
#brand-slider .flex-direction-nav a,
.featured-products .flex-direction-nav a {
    width: 50px;
    height: 40px;
    top: 0;
    margin-top: -52px;
}
#brand-slider .flex-direction-nav a {
    margin-top: -80px;
}
.box-up-sell .flex-direction-nav .flex-prev,
.box-cross-sell .flex-direction-nav .flex-prev,
#brand-slider .flex-direction-nav .flex-prev,
.featured-products .flex-direction-nav .flex-prev {
    right: 53px;
}
.main .box-up-sell h2.subtitle .line,
.main .box-cross-sell h2.subtitle .line,
.brand-slider-wrap h2.subtitle .line,
.featured-products h2.subtitle .line {
    right: 130px;
}
.featured-products {
    position: relative;
}
.product-view .box-tags .form-add label {
    line-height: 42px;
}
.product-view .box-tags .form-add .button {
    height: 42px;
}
#addTagForm {
    margin-bottom: 15px;
}
#addTagForm .input-text {
    height: 42px;
}
.product-view .box-up-sell {
    margin-top: 50px;
}
.mini-products-list .price-box * {
    display: inline;
}
.mini-products-list .price-box .price {
    margin-right: 10px;
}

/*========== Price Style ==========*/
.price-box .product-image {
    display: none;
}
.price-box .minimal-price-link {
    display: none;
}
.price-box * {
    line-height: 1.2 !important;
}
.products-grid .price,
.products-list .price
.product-essential .product-img-box .price {
    line-height: 1.2;
}
.products-grid .old-price,
.products-list .old-price {
    margin: 0;
}
.old-price .price-label,
.special-price .price-label,
.price-from .price-label,
.price-to .price-label {
    display: none !important;
}
.products-grid .price-from,
.products-list .price-from {
    padding-bottom: 6px;
    margin-bottom: 0;
    background: transparent url(/_images/icons/icon_from_to.png) bottom center no-repeat;
}
.products-grid .price-box {
    position: absolute;
    -webkit-border-radius: 60px;
       -moz-border-radius: 60px;
            border-radius: 60px;
    text-align: center;
    width: 100px;
    height: 40px;
    bottom: -10px;
    right: -10px;
    font-size: 13px;
    margin: 0;
    line-height: 1.2;
}
.products-grid .regular-price,
.products-grid .price-box > .price {
    display: block;
    margin-top: 13px;
}
.products-grid .old-price {
    display: block;
    margin-top: 2px;
    font-size: 11px;
}
.products-grid .price-from {
    display: block;
    margin-top: 17px;
}
.products-grid .minimal-price {
    display: block;
    margin-top: 25px;
}
.products-grid .minimal-price .price-label {
    display: block;
    font-size: 13px;
}

.products-list .price-box {
    -webkit-border-radius: 45px;
       -moz-border-radius: 45px;
            border-radius: 45px;
    position: absolute;
    width: 90px;
    height: 90px;
    bottom: -10px;
    right: -10px;
    font-size: 18px;
    margin: 0;
    text-align: center;
    line-height: 1.2;    
}
.products-list .regular-price,
.products-list .price-box > .price {
    display: block;
    margin-top: 34px;
}
.products-list .old-price {
    display: block;
    margin-top: 24px;
    font-size: 16px;
}
.products-list .price-from {
    display: block;
    margin-top: 20px;
}
.products-list .minimal-price {
    display: block;
    margin-top: 26px;
}
.products-list .minimal-price .price-label {
    display: block;
    font-size: 14px;
}

.product-essential .product-img-box .price-box,
.slider-wrap .price-box {
    -webkit-border-radius: 87px;
       -moz-border-radius: 87px;
            border-radius: 87px;
    position: absolute;
    width: 150px;
    height: 50px;
    bottom: -10px;
    right: -10px;
    font-size: 17px;
    line-height: 1.2;
    margin: 0;
    text-align: center;
}
.product-essential .product-img-box .price-box {
    z-index: 10000;
}
.product-essential .product-img-box .regular-price,
.product-essential .product-img-box .price-box > .price,
.slider-wrap .price-box .price {
    display: block;
    margin-top: 15px;
}
.product-essential .product-img-box .old-price {
    display: block;
   margin-top: 5px;
        margin-bottom: 0;
        font-size: 13px;
}
.product-essential .product-img-box .price-from {
    background: transparent url(/_images/icons/icon_from_to_large.png) bottom center no-repeat;
    padding-bottom: 7px;
    margin-bottom: 4px;
}
.product-essential .product-img-box .price-from,
.product-essential .product-img-box .price-to {
    font-size: 21px;
}
.product-essential .product-img-box .price-from,
.product-essential .product-img-box .minimal-price {
    display: block;
    margin-top: 25px;
}
.product-essential .product-img-box .minimal-price .price-label {
    display: block;
    font-size: 17px;
    margin-bottom: 6px;
}

.products-grid .labels,
.products-list .labels,
.product-essential .product-img-box .labels {
    position: absolute;
    top: 10px;
    left: 0;
    width: 60px;
    font-size: 16px;
    line-height: 1;
    text-align: center;
}
.products-grid .labels.top-right,
.products-list .labels.top-right,
.product-essential .product-img-box .labels.top-right {
    left: auto;
    right: 0;
}
.products-grid .labels.bottom-left,
.products-list .labels.bottom-left,
.product-essential .product-img-box .labels.bottom-left {
    top: auto;
    bottom: 10px;
}
.products-grid .labels.bottom-right,
.products-list .labels.bottom-right,
.product-essential .product-img-box .labels.bottom-right {
    left: auto;
    right: 0;
    top: auto;
    bottom: 10px;
}
.products-grid .new,
.products-grid .sales,
.products-list .new,
.products-list .sales,
.product-essential .product-img-box .new,
.product-essential .product-img-box .sales {
    padding: 8px 0;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 2px;
}
.products-grid .new.circle,
.products-grid .sales.circle,
.products-list .new.circle,
.products-list .sales.circle,
.product-essential .product-img-box .new.circle,
.product-essential .product-img-box .sales.circle {
    width: 60px;
    height: 60px;
    -webkit-border-radius: 30px;
       -moz-border-radius: 30px;
            border-radius: 30px;
    padding: 21px 0;
    margin-top: -15px;
    margin-left: -5px;
}
.products-grid .top-right .new.circle,
.products-grid .top-right .sales.circle,
.products-list .top-right .new.circle,
.products-list .top-right .sales.circle,
.product-essential .product-img-box .top-right .new.circle,
.product-essential .product-img-box .top-right .sales.circle {
    margin-left: 5px;
}
.products-grid .bottom-left .new.circle,
.products-grid .bottom-left .sales.circle,
.products-list .bottom-left .new.circle,
.products-list .bottom-left .sales.circle,
.product-essential .product-img-box .bottom-left .new.circle,
.product-essential .product-img-box .bottom-left .sales.circle {
    margin-bottom: -15px;
}
.products-grid .bottom-right .new.circle,
.products-grid .bottom-right .sales.circle,
.products-list .bottom-right .new.circle,
.products-list .bottom-right .sales.circle,
.product-essential .product-img-box .bottom-right .new.circle,
.product-essential .product-img-box .bottom-right .sales.circle {
    margin-left: 5px;
    margin-bottom: -15px;
}
.product-essential .product-img-box .labels {
    width: 70px;
    font-size: 18px;
    top: 13px;
    z-index: 10000;
}
.product-essential .product-img-box .labels.bottom-left,
.product-essential .product-img-box .labels.bottom-right {
    bottom: 13px;
}
.product-essential .product-img-box .new,
.product-essential .product-img-box .sales {
    padding: 10px 0;
    margin-bottom: 2px;
}
.product-essential .product-img-box .new.circle,
.product-essential .product-img-box .sales.circle {
    width: 70px;
    height: 70px;
    -webkit-border-radius: 35px;
       -moz-border-radius: 35px;
            border-radius: 35px;
    -webkit-box-sizing: border-box;
       -moz-box-sizing: border-box;
            box-sizing: border-box;
    padding: 26px 0;
    margin-top: -21px;
    margin-left: -8px;
}
.product-essential .product-img-box .top-right .new.circle,
.product-essential .product-img-box .top-right .sales.circle {
    margin-left: 8px;
}
.product-essential .product-img-box .bottom-left .new.circle,
.product-essential .product-img-box .bottom-left .sales.circle {
    margin-bottom: -21px;
}
.product-essential .product-img-box .bottom-right .new.circle,
.product-essential .product-img-box .bottom-right .sales.circle {
    margin-bottom: -21px;
    margin-left: 8px;
}
.add-to-cart .qty {
    height: 42px;
    width: 137px;
}
.add-to-cart .button-up,
.add-to-cart .button-down {
    height: 21px;
}
.qty-holder {
    position: relative;
}
.qty-holder label {
    width: 30px;
    line-height: 42px;
}
.qty-holder .qty {
    padding: 10px 39px 10px 16px;
    text-align: center;
    width: 83px;
    height: 42px;
}
.data-table .qty-holder {
    display: inline-block;
}
.data-table .qty-holder .qty {
    width: 86px;
    height: 42px;
    margin: 0;
}
.qty-holder .button-up,
.qty-holder .button-down {
    position: absolute;
    height: 21px;
    width: 23px;
    padding: 0;
    left: 94px;
    top: 0; 
}
.add-to-cart-alt .qty-holder .button-up, 
.add-to-cart-alt .qty-holder .button-down {
    left: 63px;
}
.add-to-cart-alt .qty-holder {
    margin-bottom: 5px;
}
.add-to-cart-alt .btn-cart,
.data-table .add-to-cart-alt .button {
    display: block;
    margin: 0;
    padding: 6px 5px;
}
.qty-holder .button-down {
    top: 21px;
}
.qty-holder.nolabel .button-up,
.qty-holder.nolabel .button-down {
    left: 63px;
}
.product-shop .button-up, 
.product-shop .button-down {
    height: 21px;
}

/*========== Category Toolbar, Pager ==========*/
.toolbar {
    position: relative;
    border-width: 0 !important;
    padding: 15px 0;
    margin-bottom: 15px;
}
.toolbar-bottom {
    border-width: 0 !important;
}
.toolbar .pager {
    text-align: right;
    margin: 15px 0 0;
    padding: 15px 0 0;
}
.sorter .dropdown-toggle {
    width: 120px;
    text-align: left;
}
.sorter .dropdown-menu > li {
    width: 120px;
}
.pager .dropdown-toggle {
    width: 85px;
    text-align: left;
}
.pager .dropdown-menu > li {
    width: 85px;
    display: block;
    text-align: left;
}
.pager .button,
.toolbar .button, 
.toolbar .btn {
    padding: 4px 10px;
}
.toolbar .pager .limiter {
    float: right;
    position: absolute;
    top: 15px;
    right: 100px;
}
.toolbar-bottom .pager .limiter {
    position: static;
}
.toolbar .pager .pages {
    float: right;
    margin-left: 30px;
}
.pager .button {
    padding: 4px 11px;
}
.toolbar .actions {
    margin-right: 30px;
}
.pager .limiter label,
.toolbar label {
    display: inline; 
    float: left;
    text-transform: lowercase; 
    margin-right: 10px;
    padding: 6px 0; 
    font-size: 13px;
}
.toolbar .sorter .sort-by {
    margin-right: 30px;
}
.pager .amount {
    margin-right: 20px;
}
.toolbar-bottom .sorter {
    display: none;
}
.product-view .box-reviews .pager {
    border-width: 0;
    padding-top: 15px;
}

/*========== Shoppint Cart, Compare Products Data Table ==========*/
.data-table {
    border-right-width: 0 !important;
    border-bottom-width: 0 !important;
}
.data-table li {
    list-style-position: inside;
}
.data-table select {
    width: 94px;
}
.data-table .button {
    margin: 0 5px;
    white-space: nowrap;
}
.data-table .input-text {
    width: 80px;
}
.data-table .product-name {
    margin-top: 10px;
    font-size: 20px;
    line-height: 1.4;
    font-weight: bold;
}
.data-table .actions {
    margin: 15px 0 0;
}
.data-table .price {
    font-size: 18px;
}
.cart .totals table tfoot td .price {
    font-size: 22px;
}
.cart-table th,
.cart-table td {
    padding: 30px 10px;
}
.cart-table th {
    text-align: center;
}
.cart-table td.column-name {
    padding: 30px;
}
.cart-table thead th,
.cart-table thead td,
.cart-table tfoot th,
.cart-table tfoot td {
    padding: 20px 20px;
}
.cart-table tr {
    vertical-align: top;
}
.data-table .product-image {
    float: left;
    width: 180px;
}
.data-table a.product-image {
    float: none;
    text-align: center;
    display: block;
}
.my-account .data-table .product-image,
.my-wishlist .data-table .product-image,
.order-review .data-table .product-image {
    width: auto;
    height: auto;
}
.data-table .product-shop {
    padding-left: 210px;
}
.data-table .item-options {
    float: left;
    line-height: 25px;
    width: 100%;
}
.data-table .cart-price {
    margin-top: 15px;
    margin-bottom: 15px;
    display: block;
}
.data-table .total-price {
    position: relative;
    margin: 0 auto;
}
.cart-table .total-price {
    width: 140px;
}
.data-table .total-price .cart-price {
    padding-right: 40px;
}
.data-table .total-price .price {
    line-height: 28px;
}
.cart-table .btn-remove {
    position: absolute;
    top: 0;
    right: 10px;
    bottom: 0;
    margin: 0;
}
.data-table .add-to-cart {
    position: relative;
    width: 112px;
    margin: 5px auto;
}
.data-table .add-to-cart .qty {
    padding: 9px 35px 9px 15px;
    font-size: 20px;
    height: 26px;
    width: 60px;
}
.cart-table .add-to-cart .qty {
    width: 112px;
    height: 46px;
}
.data-table .add-to-cart .button-up,
.data-table .add-to-cart .button-down {
    height: 23px;
    width: 23px;
    padding: 0;
    left: 89px; 
}
.data-table .add-to-links {
    list-style:none;
    margin: 10px 0; 
}
.data-table thead tr.mobile-row,
.data-table .mobile-label {
    display: none;
}
.data-table .btn-continue {
    float: left;
}
.cart-totals {
    margin-bottom: 40px;
}
.checkout-types li {
    margin-bottom: 15px;
}
.cart-tabs {
    margin-bottom: 100px;
}
.cart-tabs ul {
    margin: 0;
    padding: 0;
    list-style: none;
}
.compare-table {
    margin-bottom: 20px;
}
.compare-table th {
    text-align: right;
    padding: 15px;
    vertical-align: top;
}
.compare-table td {
    text-align: center;
    padding: 15px;
    vertical-align: top;
}
.compare-table .product-image {
    width: 170px;
    margin: 15px auto 10px;
    display: inline-block;
    float: none;
}
.compare-table .product-image img {
    width: 100%;
    height: auto;
}
.compare-table .ratings .rating-box {
    float: none;
    margin-left: auto;
    margin-right: auto;
}
.compare-table ul {
    padding: 0;
    margin: 0;
    list-style: none;
}
.compare-table .add-to-links {
    display: inline;
}
.compare-table .add-to-links li {
    display: inline;
}
.compare-table .btn-remove {
    margin-top: 20px;
}
.compare-table td {
    background-color: #fff;
    border-right-width: 0 !important;
    border-bottom-width: 0 !important;
} 
.compare-table td.last {
    border-right-width: 1px !important;
}
.compare-table th {
    border-bottom-width: 0 !important;
}
.compare-table tr.last th,
.compare-table tr.last td {
    border-bottom-width: 1px !important;
}

.compare-table-mobile {
    display: none;
}
.compare-table-mobile th {
    text-align: center;
}
.compare-table-mobile th,
.compare-table-mobile td {
    border-bottom-width: 1px !important;
}
.compare-table-mobile tr td {
    background-color: #fff !important;
}

/*========== Form Fields ==========*/
.form-list label [class^="icon-"], 
.form-list label [class*=" icon-"] {
    width: 20px;
    height: 20px;
    background-image: url(/_images/icons/icon_20x20.png) !important;
    margin-top: -2px;
    margin-right: 3px;
}
.form-list .icon-people          { background-position: -40px -20px; }
.blue .form-list .icon-people    { background-position: -40px -40px; }
.orange .form-list .icon-people  { background-position: -40px -60px; }
.pink .form-list .icon-people    { background-position: -40px -80px; }

.form-list .icon-email           { background-position: -60px -20px; }
.blue .form-list .icon-email     { background-position: -60px -40px; }
.orange .form-list .icon-email   { background-position: -60px -60px; }
.pink .form-list .icon-email     { background-position: -60px -80px; }

.form-list .icon-subject         { background-position: -80px -20px; }
.blue .form-list .icon-subject   { background-position: -80px -40px; }
.orange .form-list .icon-subject { background-position: -80px -60px; }
.pink .form-list .icon-subject   { background-position: -80px -80px; }

.form-list .icon-upload          { background-position: -100px -20px; }
.blue .form-list .icon-upload    { background-position: -100px -40px; }
.orange .form-list .icon-upload  { background-position: -100px -60px; }
.pink .form-list .icon-upload    { background-position: -100px -80px; }

.form-list .icon-comment         { background-position: -120px -20px; }
.blue .form-list .icon-comment   { background-position: -120px -40px; }
.orange .form-list .icon-comment { background-position: -120px -60px; }
.pink .form-list .icon-comment   { background-position: -120px -80px; }

.form-list .icon-phone           { background-position: -140px -20px; }
.blue .form-list .icon-phone     { background-position: -140px -40px; }
.orange .form-list .icon-phone   { background-position: -140px -60px; }
.pink .form-list .icon-phone     { background-position: -140px -80px; }

.form-list .icon-fax             { background-position: -160px -20px; }
.blue .form-list .icon-fax       { background-position: -160px -40px; }
.orange .form-list .icon-fax     { background-position: -160px -60px; }
.pink .form-list .icon-fax       { background-position: -160px -80px; }

.form-list .icon-password        { background-position: -180px -20px; }
.blue .form-list .icon-password  { background-position: -180px -40px; }
.orange .form-list .icon-password{ background-position: -180px -60px; }
.pink .form-list .icon-password  { background-position: -180px -80px; }

.form-list .icon-captcha         { background-position: -180px -20px; }
.blue .form-list .icon-captcha   { background-position: -180px -40px; }
.orange .form-list .icon-captcha { background-position: -180px -60px; }
.pink .form-list .icon-captcha   { background-position: -180px -80px; }

.form-list .icon-company         { background-position: -200px -20px; }
.blue .form-list .icon-company   { background-position: -200px -40px; }
.orange .form-list .icon-company { background-position: -200px -60px; }
.pink .form-list .icon-company   { background-position: -200px -80px; }

.form-list .icon-address         { background-position: -220px -20px; }
.blue .form-list .icon-address   { background-position: -220px -40px; }
.orange .form-list .icon-address { background-position: -220px -60px; }
.pink .form-list .icon-address   { background-position: -220px -80px; }

.form-list .icon-city            { background-position: -240px -20px; }
.blue .form-list .icon-city      { background-position: -240px -40px; }
.orange .form-list .icon-city    { background-position: -240px -60px; }
.pink .form-list .icon-city      { background-position: -240px -80px; }

.form-list .icon-zipcode         { background-position: -260px -20px; }
.blue .form-list .icon-zipcode   { background-position: -260px -40px; }
.orange .form-list .icon-zipcode { background-position: -260px -60px; }
.pink .form-list .icon-zipcode   { background-position: -260px -80px; }

.form-list .icon-country         { background-position: -280px -20px; }
.blue .form-list .icon-country   { background-position: -280px -40px; }
.orange .form-list .icon-country { background-position: -280px -60px; }
.pink .form-list .icon-country   { background-position: -280px -80px; }

.form-list .icon-state           { background-position: -300px -20px; }
.blue .form-list .icon-state     { background-position: -300px -40px; }
.orange .form-list .icon-state   { background-position: -300px -60px; }
.pink .form-list .icon-state     { background-position: -300px -80px; }

.form-list .icon-tax             { background-position: -440px -20px; }
.blue .form-list .icon-tax       { background-position: -440px -40px; }
.orange .form-list .icon-tax     { background-position: -440px -60px; }
.pink .form-list .icon-tax       { background-position: -440px -80px; }

.form-list .icon-date            { background-position: -460px -20px; }
.blue .form-list .icon-date      { background-position: -460px -40px; }
.orange .form-list .icon-date    { background-position: -460px -60px; }
.pink .form-list .icon-date      { background-position: -460px -80px; }

.form-list .icon-card            { background-position: -480px -20px; }
.blue .form-list .icon-card      { background-position: -480px -40px; }
.orange .form-list .icon-card    { background-position: -480px -60px; }
.pink .form-list .icon-card      { background-position: -480px -80px; }

/*========== Checkout Page ==========*/
#checkoutSteps {
    list-style: none;
    margin: 0;
    padding: 0;
}
.opc .step-title a {
    position: absolute;
    right: 14px;
    top: 14px;
    width: 28px;
    height: 28px;
    background-position: center center;
    background-repeat: no-repeat;
}
.opc h3 {
    margin: 0 0 20px;
}
.opc .form-list h3 {
    margin-top: 0;
}
.opc ul,
.opc ol {
    margin: 20px 0 0;
}
.opc ul li,
.opc ol li {
    margin-bottom: 25px;
}
.opc fieldset > .form-list {
    margin-top: 0;
}
#opc-review .buttons-set {
    margin-top: 40px;
}
#opc-review .step {
    padding-bottom: 20px;
}
#shipping-zip-form {
    margin-bottom: 25px;
}

/*========== Form List ==========*/
.form-list p {
    margin:0 0 20px;
}

.fieldset p {
    line-height: 1.6;
    margin-bottom: 20px;
}
.form-list li {
    margin-bottom: 25px;
}
.form-list li.control,
.form-list li.options,
.form-list li.has-pretty-child {
    margin-bottom: 20px;
}
.forgot-password {
    font-size: 13px;
    color: #c72928;
    position: absolute;
    right: 0;
    top: 50px;
}
.form-list input.input-text {
    width: 100%;
    padding: 13px 15px 13px 165px;
    margin-bottom: 0;
    line-height: 20px;
}
.form-list textarea {
    width: 100%;
    padding: 60px 15px 13px;
    margin-bottom: 0;
    line-height: 20px;
}
.form-list select {
    padding: 12px 15px 12px 165px;
    margin: 0;
    width: 100%;
    line-height: 20px;
    height: 48px;
}
.form-list .bootstrap-select {
    position: absolute;
    /*display: block !important;*/
    width: auto;
    height: 46px;
    left: 1px;
    top: 1px;
    bottom: 1px;
    right: 1px;
}
.form-list .bootstrap-select > .btn {
    width: 100%;
    padding: 13px 0;
    margin-bottom: 0;
    border-width: 0 !important;
    position: absolute;
    top: 0;
    bottom: 0;
}
.form-list .bootstrap-select.btn-group .btn .filter-option {
    left: 165px;
    right: 55px;
    white-space: nowrap;
    text-overflow: ellipsis;
}
.form-list .bootstrap-select.btn-group .btn .caret {
    right: 0;
    top: 0;
    bottom: 0;
    width: 46px;
    height: auto;
    margin: 0;
    padding: 0 !important;
    background-position: center center;
    background-repeat: no-repeat !important;
    border-width: 0;
}
.form-list .bootstrap-select.btn-group .dropdown-menu {
    margin-top: 1px;
    left: -1px;
    right: -1px;
}
.form-list li label {
    font-size: 14px;
    position: absolute;
    left: 1px;
    top: 1px;
    bottom: 1px;
    width: 150px;
    margin: 0;
    padding: 0 15px;
    line-height: 46px;
    /*height: 46px;*/
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
    z-index: 1000;
}
.form-list li label.label-wide {
    width: auto;
    height: 46px;
    right: 1px;
}
.form-list li.control label,
.form-list li.options label, 
.form-list li.has-pretty-child label {
    font-size: 15px;
    position: static;
    padding: 0;
    height: auto;
    width: auto;
    vertical-align: top;
    line-height: 18px;
    background-color: transparent;
    border-width: 0;
}
.opc .sp-methods dt label {
    margin: -3px 5px 0;
}
.form-list .validation-advice {
    position: absolute;
}
.sp-methods .prettycheckbox > a, 
.sp-methods .prettyradio > a { 
    margin-left: 0; 
}
.form-list .customer-dob .dob-day {
    width: 38%;
    margin-right: 3%;
}
.form-list .span6 .customer-dob .dob-day,
.form-list .col-sm-6 .customer-dob .dob-day,
.span6 .form-list .customer-dob .dob-day,
.col-sm-6 .form-list .customer-dob .dob-day {
    width: 50%;
}
.form-list .customer-dob .dob-month {
    width: 23%;
    margin-right: 3%;
}
.form-list .span6 .customer-dob .dob-month,
.form-list .col-sm-6 .customer-dob .dob-month,
.span6 .form-list .customer-dob .dob-month,
.col-sm-6 .form-list .customer-dob .dob-month {
    width: 18%;
}
.form-list .customer-dob .dob-year {
    width: 33%;
}
.form-list .span6 .customer-dob .dob-year,
.form-list .col-sm-6 .customer-dob .dob-year,
.span6 .form-list .customer-dob .dob-year,
.col-sm-6 .form-list .customer-dob .dob-year {
    width: 26%;
}
.form-list .customer-dob .dob-month input.input-text,
.form-list .customer-dob .dob-year input.input-text {
    padding-left: 15px;
}
.form-list .input-range {
    line-height: 46px;
}
.form-list .input-range .separator {
    width: 4%;
    margin: 0;
    text-align: center;
    display: inline-block;
}
.form-list .input-range .start input.input-text {
    width: 54%;
}
.form-list .input-range .end input.input-text {
    width: 34%;
    padding-left: 15px;
}
.legend {
    margin-bottom: 20px;
}
.span6 .sp-methods select.month,
.col-sm-6 .sp-methods select.month { width:320px; }
.span6 .sp-methods select.year,
.col-sm-6 .sp-methods select.year { width:120px; }


/*========== Contact Us ==========*/
#contact-gmap {
    width: 100%;
    height: 280px;
    margin-bottom: 20px;
}
ul.contact-details {
    margin: 0;
    padding: 0;
    list-style: none;
}
ul.contact-details li {
    margin: 20px 0;
    line-height: 24px;
}
ul.contact-details span {
    display: inline-block;
}
ul.contact-details .button-inverse {
    margin-right: 15px;
    margin-top: 7px;
    padding: 7px;
    float: left;
}
ul.contact-details [class^="icon-"], 
ul.contact-details [class*=" icon-"] {
    width: 20px;
    height: 20px;
    background-image: url(/_images/icons/icon_20x20.png) !important;
    margin: 0;
    padding: 0;
}
ul.contact-details .icon-email { background-position: 0 0; }
ul.contact-details .icon-skype { background-position: -20px 0; }
ul.contact-details .icon-phone { background-position: -140px 0; }
ul.contact-details .icon-device { background-position: -520px 0; }

/*========== My Account ==========*/
.sidebar .block-account ul li {
    background: transparent url(/_images/icons/li_green.png) left center no-repeat;
    padding-left: 15px;
}
.green .sidebar .block-account ul li { background-image: url(/_images/icons/li_green.png); }
.blue .sidebar .block-account ul li { background-image: url(/_images/icons/li_blue.png); }
.orange .sidebar .block-account ul li { background-image: url(/_images/icons/li_orange.png); }
.pink .sidebar .block-account ul li { background-image: url(/_images/icons/li_pink.png); }
.main-content .welcome-msg {
    padding: 10px 0;
}
.box-head h2 {
    margin-top: 25px;
}
.box-head a,
.box-title a {
    margin-bottom: 5px;
    display: inline-block;
}
.box-content p {
    line-height: 1.4;
}
.box-content a {
    margin-top: 5px;
    display: inline-block;
}
.dashboard .box-account ol,
.dashboard .box-account ul,
.addresses-list ol {
    list-style: none;
    margin: 0;
    padding: 0;
}
.dashboard .box-account li {
    margin: 5px 0;
}
.dashboard .box-account .product-name {
    font-size: 18px;
    line-height: 1;
}
.addresses-list h3 {
    margin-top: 20px;
}
.my-wishlist textarea {
    display: block;
    height: 80px;
    width: 95%;
    margin-left: auto;
    margin-right: auto;
}

/* Home Slider Styles */
#homeslider-bxslider {
    margin: 0;
    padding: 0;
}
.homeslider-products .bx-wrapper {
    margin: 0 auto;
}
.homeslider-products .bx-wrapper .bx-viewport {
    overflow: visible !important;    
}
.homeslider-products .bx-wrapper .product-image img {
    width: 100%;
    height: auto;
}
.homeslider-products .bx-wrapper .product-image img:hover,
.homeslider-products .bx-wrapper li.hover .product-image img {
    opacity: 0.4;
    filter: alpha(opacity=40);
}
.homeslider-products .product-details {
    position: absolute;
    top: 97%;
    right: 3%;
    bottom: 3%;
    left: 3%;
    text-align: center;
    overflow: hidden;
}
.homeslider-products li.hover .product-details {
    border:1px solid #fff;
}
.homeslider-products .product-name {
    font-size: 40px;
    line-height: 1.2;
    margin-top: 0;
    margin-bottom: 15px;
}
.homeslider-products .price-box .price {
    font-size: 35px;
    font-weight: bold;
}
.homeslider-products .price-box {
    margin-bottom: 15px;
}
.homeslider-products .product-desc {
    padding: 0 60px;
    font-size: 16px;
    line-height: 1.4;
}
.homeslider-products .btn-cart,
.homeslider-products .add-to-cart .btn-cart {
    margin-top: 15px;
}
.homeslider-products .bx-wrapper .bx-pager, 
.homeslider-products .bx-wrapper .bx-controls-auto {
    bottom: 3%;
}

/*========== Brands Slider ==========*/
#brand-slider {
    margin-top: 42px;
    margin-bottom: 30px;
}
#brand-slider img {
    width: 170px;
    margin: 0 auto;
}

/* System Config Styles */
/* Created at 2013-12-18 13:57:21 */

/*========== Common Styles ==========*/
body {
    color: #737373;
    font-size: 15px;
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;

    background-color: #ffffff;
}

label, input, button, select, textarea {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
}

a {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}

a:hover, a:focus {
    color: #838383;
}

h1, h2, h3, h4, h5, h6,
.block .block-title,
.slide-title {
    color: #565656;
    font-weight: normal;
    font-family: "Oswald", "Helvetica Neue", Helvetica, Arial, sans-serif;
}

h1 a, h2 a, h3 a, h4 a, h5 a, h6 a, .block .block-title a,
.slide-title a {
    color: #565656;
}
h1 a:hover, h2 a:hover, h3 a:hover, h4 a:hover, h5 a:hover, h6 a:hover, .block .block-title a:hover,
h1 a:hover, h2 a:focus, h3 a:focus, h4 a:focus, h5 a:focus, h6 a:focus, .block .block-title a:focus,
.slide-title a:hover, .slide-title a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}
p.desc {
    font-family: "PT Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
}

.main-container .button,
.main-container .btn,
.button-arrow,
#mini-cart .button,
.button-tabs li a,
#Growler .button {
    text-transform: uppercase;
}

.button,
.btn,
.button-inverse,
.sidebar .button,
.sidebar .btn,
.dropdown .dropdown-menu .button,
.buttons-set .back-link a,
.scrolltop,
.button-tabs li a {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
    border-width: 1px;
    border-color: #444645;
    background-color: #444645;
    background-image: none;
    color: #e8e8e8;
    }

.slider-arrow,
.flex-direction-nav .flex-prev,
.flex-direction-nav .flex-next,
.button-arrow,
.pager .button,
.toolbar .button,
.toolbar .btn,
.dropdown .arrow,
.dropdown .button,
.elastislide-next,
.elastislide-prev,
.opc .step-title a,
.prettycheckbox > a,
.prettyradio > a,
.fraction-slider .prev,
.fraction-slider .next,
.bx-wrapper .bx-controls-direction a,
.tp-leftarrow,
.tp-rightarrow {
    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
    border-width: 1px;
    border-color: #e0e0e0;
    border-style: solid;
    background-color: #fafafa;
    color: #757575;
    }
.pager .button,
.toolbar .button,
.toolbar .btn,
.toolbar .button-arrow,
.toolbar .dropdown .button .arrow {
    background-color: #f2f2f2;
}
.toolbar .button-dark,
.toolbar .btn-dark,
.pager .button-dark,
.pager .btn-dark {
    background-color: #e0e0e0;
}

.prettycheckbox > a,
.prettyradio > a {
    border-color: #c3c3c3;
}

.button:hover,
.button:focus,
.btn:hover,
.btn:focus,
.button-inverse,
.sidebar .button:hover,
.sidebar .button:focus,
.sidebar .btn:hover,
.sidebar .btn:focus,
.pager .button:hover,
.pager .button:focus,
.toolbar .button:hover,
.toolbar .button:focus,
.toolbar .btn:hover,
.toolbar .btn:focus,
.dropdown .dropdown-menu .button:hover,
.dropdown .dropdown-menu .button:focus,
.form-list .bootstrap-select.btn-group .btn:hover .caret,
.form-list .bootstrap-select.btn-group .btn:focus .caret,
.buttons-set .back-link a:hover,
.buttons-set .back-link a:focus,
.scrolltop,
.button-tabs li a:hover,
.button-tabs li a:focus {
    border-color: rgba(<?php echo $rgbColorSite;?>, 1);
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
    background-image: none;
    color: #ffffff;
}

.slider-arrow:hover,
.slider-arrow:focus,
.button-arrow:hover,
.button-arrow:focus,
.flex-direction-nav .flex-prev:hover,
.flex-direction-nav .flex-prev:focus,
.flex-direction-nav .flex-next:hover,
.flex-direction-nav .flex-next:focus,
.dropdown.open .arrow,
.toolbar .dropdown.open .arrow,
.elastislide-next:hover,
.elastislide-next:focus,
.elastislide-prev:hover,
.elastislide-prev:focus,
.opc .step-title a:hover,
.opc .step-title a:focus,
.prettycheckbox > a.checked,
.prettyradio > a.checked,
.fraction-slider .prev:hover,
.fraction-slider .prev:focus,
.fraction-slider .next:hover,
.fraction-slider .next:focus,
.bx-wrapper .bx-controls-direction a:hover,
.bx-wrapper .bx-controls-direction a:focus,
.tp-leftarrow:hover,
.tp-leftarrow:focus,
.tp-rightarrow:hover,
.tp-rightarrow:focus {
    border-color: rgba(<?php echo $rgbColorSite;?>, 1);
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
    background-image: none;
    color: #ffffff;
}

.add-to-cart .qty,
.qty-holder .qty {
    background-color: #fff;
    border: 1px solid #e0e0e0;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
}

.button-inverse,
.button-tabs li.active a {
    border-color: rgba(<?php echo $rgbColorSite;?>, 1) !important;
    background-color: rgba(<?php echo $rgbColorSite;?>, 1) !important;
    background-image: none !important;
    color: #ffffff !important;
}
.button-inverse:hover,
.button-inverse:focus {
    border-color: #444645 !important;
    background-color: #444645 !important;
    color: #e8e8e8 !important;
}
.scrolltop:hover,
.scrolltop:focus {
    border-color: rgba(<?php echo $rgbColorSite;?>, 0.9) !important;
    background-color: rgba(<?php echo $rgbColorSite;?>, 0.9) !important;
    color: #ffffff !important;
}

.dropdown-select .button {
    border-color: #e0e0e0 !important;
    background-color: #fff !important;
    color: #757575 !important;
}
.toolbar span.button-active {
    border-color: rgba(<?php echo $rgbColorSite;?>, 1);
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
    color: #ffffff;
}

select, 
textarea, 
input.input-text, 
input[type="text"], 
input[type="password"], 
input[type="datetime"], 
input[type="datetime-local"], 
input[type="date"], 
input[type="month"], 
input[type="time"], 
input[type="week"], 
input[type="number"], 
input[type="email"], 
input[type="url"], 
input[type="search"], 
input[type="tel"], 
input[type="color"], 
.uneditable-input,
.form-list .bootstrap-select > .btn,
.form-list .bootstrap-select > .btn:hover,
.form-list .bootstrap-select > .btn:focus,
.btn-group.open .btn.dropdown-toggle {
    border-width: 1px;
    border-style: solid;
    border-color: #e0e0e0;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px; 
    color: #a4a4a4;   
    background-color: #ffffff;
    -webkit-box-shadow: 0 0 0 #000;
       -moz-box-shadow: 0 0 0 #000;
            box-shadow: 0 0 0 #000;
}

form h2,
form h3,
.section h3 {
    color: #444645;
}
.main h2.subtitle,
.main .widget .widget-title h2,
.main .page-title h1,
.footer-banner h2.subtitle {
    color: #444645;
    border-left: 4px solid rgba(<?php echo $rgbColorSite;?>, 1);
}
.main h2.subtitle .line,
.main .widget .widget-title h2 .line, 
.main .page-title h1 .line,
.footer-banner h2.subtitle .line {
    border-top: 1px solid #e0e0e0;
}

.opc .input-text {
    
}

/*========== Header Top ==========*/
.header-top {
    font-size: 14px;
    border-top: 4px solid rgba(<?php echo $rgbColorSite;?>, 1);
    background-color: #f4f4f4;
    height: 40px;
}

/*========== Header ==========*/
.header {
    font-size: 14px;
    background-color: #ffffff;
        background-position: center center;
    background-repeat: repeat;
}
.header-contact .block {
    border: 1px solid #e7e7e7;
    background-color: #f7f7f7;
}
.header a {
    color: #737373;
}
.header a:hover,
.header a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.header .logo {
    margin-top: 0;
}

/*========== Top Links ==========*/
.toplinks a {
    color: #7f7d74;
}
.toplinks a:hover,
.toplinks a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}

/*========== Currency, Language Selector, Store Switcher ==========*/
.block-currency, 
.block-language {
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.dropdown-menu {
    border-color: #dcdcdc;
}
.dropdown-menu > li > a,
.dropdown-submenu:focus > a {
    background-color: #fff;
    background-image: none;
    color: #737373;
}

.dropdown-menu > li > a:hover,
.dropdown-menu > li > a:focus,
.dropdown-menu > .active > a,
.dropdown-menu > .active > a:hover,
.dropdown-menu > .active > a:focus,
.dropdown-submenu:hover > a {
    background-color: rgba(<?php echo $rgbColorSite;?>, 1) !important;
    background-image: none;
    color: #ffffff;
}
.block-currency .block-content > a, 
.block-language .block-content > a,
.store-switcher .block-content > a {
    color: #ffffff;
}
.block-currency,
.block-language,
.block-currency .dropdown-menu > li > a, 
.block-currency .dropdown-submenu:focus > a,
.block-language .dropdown-menu > li > a, 
.block-language .dropdown-submenu:focus > a {
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.block-currency .dropdown-menu > li > a, 
.block-currency .dropdown-submenu:focus > a,
.block-language .dropdown-menu > li > a, 
.block-language .dropdown-submenu:focus > a {
    background-color: #e2e2e2;
    color: #444645;
    border-width: 1px 0 0 0;
    border-style: solid;
    border-color: #ffffff;
}
.block-currency .block-content > a, 
.block-language .block-content > a {
    color: #ffffff;
    border-width: 0;
    border-style: solid;
    border-color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.block-currency .block-content.open > a, 
.block-language .block-content.open > a {
    border-width: 0;
    border-style: solid;
    border-color: rgba(<?php echo $rgbColorSite;?>, 0.9);
}
.block-currency .block-content.open > a, 
.block-language .block-content.open > a,
.block-currency .dropdown-menu > li > a:hover,
.block-currency .dropdown-menu > li > a:focus,
.block-currency .dropdown-menu > .active > a,
.block-currency .dropdown-menu > .active > a:hover,
.block-currency .dropdown-menu > .active > a:focus,
.block-currency .dropdown-submenu:hover > a,
.block-language .dropdown-menu > li > a:hover,
.block-language .dropdown-menu > li > a:focus,
.block-language .dropdown-menu > .active > a,
.block-language .dropdown-menu > .active > a:hover,
.block-language .dropdown-menu > .active > a:focus,
.block-language .dropdown-submenu:hover > a {
    color: #ffffff !important;
    background-color: rgba(<?php echo $rgbColorSite;?>, 0.9) !important;
}

/*========== Main Menu ==========*/
.header-menu {
    background-color: #ffffff;
        background-position: center center;
    background-repeat: repeat;
    margin-top: 0;
    padding-bottom: 15px;
}
#menu-button a,
div.menu a,
#nav li a,
#nav-links li a,
.menu-mobile.level0 > .parentMenu > a {
    color: #494940;
    background-color: #ffffff;
    font-size: 17px;
    font-weight: bold;
    font-family: "PT Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.block2 h3,
.block2 h3 a {
    color: #4c4e4d;
    font-size: 19px;
    font-weight: bold;
    font-family: "PT Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
#menu-button a {
    background-color: transparent;
}
div.menu a,
#nav li a,
#nav-links li a {
    background-color: #ffffff;
}
div.menu a:hover,
div.menu a:focus,
div.menu.active a,
#nav li a:hover,
#nav li a:focus,
#nav li.active a,
#nav li.over a,
#nav-links li a:hover,
#nav-links li a:focus,
#nav-links li.active a,
#nav-links li.over a {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
    background-color: ;
}
#custommenu > .menu.active,
#nav > li.over,
#nav-links > li.over {
    margin-bottom: -15px;
}
#custommenu > .menu.active > .parentMenu > a,
#nav li.over a.level-top,
#nav-links li.over a.level-top {
    padding-bottom: 28px;
}
.mobile-header #menu-button {
    padding-bottom: 15px;
}
#nav ul, 
#nav div,
#nav-links ul,
#nav-links div {
    top: 66.28569px;
}
div.eternal-custom-menu-popup,
#custommenu-mobile #menu-content,
#nav ul,
#nav div,
#nav-links ul,
#nav-links div {
    color: #4c4e4d;
    border-style: solid;
    border-width: 3px 0 0 0;
    border-color: rgba(<?php echo $rgbColorSite;?>, 1);
    background-color: #fafafa;
}
div.eternal-custom-menu-popup a,
.menu-mobile .parentMenu a,
.menu-mobile.level0 > .parentMenu > a,  
#nav ul li a,
#nav div a,
#nav div a:hover,
#nav div a:focus,
#nav .active ul li a,
#nav .over ul li a,
#nav-links ul li a,
#nav-links div a,
#nav-links div a:hover,
#nav-links div a:focus,
#nav-links .active ul li a,
#nav-links .over ul li a {
    color: #4c4e4d;
}
#nav ul li a:hover,
#nav ul li a:focus,
#nav ul li.over > a,
#nav-links ul li a:hover,
#nav-links ul li a:focus,
#nav-links ul li.over > a {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}
div.eternal-custom-menu-popup a:hover,
div.eternal-custom-menu-popup a:focus,
.menu-mobile .parentMenu a:hover,
.menu-mobile .parentMenu a:focus,
.menu-mobile.level0 > .parentMenu > a:hover,
.menu-mobile.level0 > .parentMenu > a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}
div.eternal-custom-menu-popup a.level1,
.menu-mobile a.level1 {
    font-size: 16px;
    font-weight: bold;
    font-family: "Gudea", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
div.column {
    width: 20%;
}
#menu-button .btn-navbar .icon-bar {
    background-color: #ffffff;
}
#menu-button a .btn-navbar,
#menu-button a:hover .btn-navbar,
#menu-button a:focus .btn-navbar {
    border-color: #444645;
    background-color: #444645;
}
#menu-button a:hover .btn-navbar .icon-bar,
#menu-button a:focus .btn-navbar .icon-bar,
#menu-button .btn-navbar:hover .icon-bar {
    background-color: #ffffff;
}
#menu-button a {
    color: #494940;    
    font-size: 20px;
}
#custommenu-mobile .menu-mobile,
#custommenu-mobile .itemMenuName,
#custommenu-mobile .itemMenu {
    background-color: #ffffff;
}
#custommenu-mobile .menu-mobile.level0 > .parentMenu,
#custommenu-mobile .menu-mobile.level0 > .parentMenu > a {
    background-color: #fafafa;
}
#custommenu-mobile .menu-mobile .itemMenu.level1 > .parentMenu,
#custommenu-mobile .menu-mobile .itemMenu.level1 > .parentMenu > a {
    background-color: #f6f6f6;
}

/*========== Quick Access, Wishlist, Compare Links ==========*/
.form-search .button {
    background-image: url(/_images/icons/icon_search.png);
}
.form-search .button:hover,
.form-search .button:focus {
    background-image: url(/_images_/icons/icon_search.png);
}
#mini-cart .icon-cart {
    background-image: url(/_images_/icons/icon_cart.png);
}
#mini-cart.open .icon-cart {
    background-image: url(/_images/icons/icon_cart.png);
}
.link-wishlist {
    background-image: url(/_images/icons/gift_box.png);
}
.link-wishlist:hover,
.link-wishlist:focus {
    background-image: url(/_images/icons/gift_box.png);
}
.link-friend {
    background-image: url(/_images/icons/white/icon_friend.png);
}
.link-friend:hover,
.link-friend:focus {
    background-image: url(/_images/icons/white/icon_friend.png);
}
.link-compare {
    background-image: url(/_images/icons/white/icon_compare.png);
}
.link-compare:hover,
.link-compare:focus {
    background-image: url(/_images/icons/white/icon_compare.png);
}
.link-edit {
    background-image: url(/_images/icons/white/icon_edit.png);
}
.link-edit:hover,
.link-edit:focus {
    background-image: url(/_images/icons/white/icon_edit.png);
}
.icon-fblike {
    background-image: url(/_images/icons/white/icon_fblike.png);
}
:hover .icon-fblike,
:focus .icon-fblike {
    background-image: url(/_images/icons/white/icon_fblike.png);
}

#search_mini_form .button,
#mini-cart > .button,
.store-switcher .button {
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
    -webkit-box-shadow: 0 0 0 #000;
       -moz-box-shadow: 0 0 0 #000;
            box-shadow: 0 0 0 #000;
}

#search_mini_form .button,
#mini-cart > .button,
.store-switcher .button,
.link-wishlist,
.link-compare,
.link-friend,
.link-edit,
.footer-top .button,
.footer-bottom .button,
#Growler .button {
    color: #ffffff;
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
    border-color: rgba(<?php echo $rgbColorSite;?>, 1);
}

#search_mini_form .button:hover,
#search_mini_form .button:focus,
#mini-cart > .button:hover,
#mini-cart > .button:focus,
.store-switcher .button:hover,
.store-switcher .button:focus,
.link-wishlist:hover,
.link-wishlist:focus,
.link-compare:hover,
.link-compare:focus,
.link-friend:hover,
.link-friend:focus,
.link-edit:hover,
.link-edit:focus,
.footer-top .button:hover, 
.footer-top .button:focus, 
.footer-bottom .button:hover, 
.footer-bottom .button:focus,
#Growler .button:hover,
#Growler .button:focus {
    color: #ffffff;
    -webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
       -moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05);
    border-color: rgba(<?php echo $rgbColorSite;?>, 0.9);
    background-color: rgba(<?php echo $rgbColorSite;?>, 0.9);
}
#mini-cart.open > .button {
    -webkit-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
       -moz-box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.2) inset, 0 1px 2px rgba(0, 0, 0, 0.05) !important;
    border-color: rgba(<?php echo $rgbColorSite;?>, 0.9) !important;
    background-color: rgba(<?php echo $rgbColorSite;?>, 0.9) !important;
}
#mini-cart .dropdown-menu {
    border-top: 3px solid rgba(<?php echo $rgbColorSite;?>, 0.9) !important;
}
.mini-products-list .price,
#mini-cart .block-content .price {
    color: #e82c0c;
}
.link-wishlist.no-image,
.link-compare.no-image,
.link-friend.no-image,
.link-edit.no-image {
    color: rgba(<?php echo $rgbColorSite;?>, 1) !important;
}
.link-wishlist.no-image:hover,
.link-wishlist.no-image:focus,
.link-compare.no-image:hover,
.link-compare.no-image:focus,
.link-friend.no-image:hover,
.link-friend.no-image:focus,
.link-edit.no-image:hover,
.link-edit.no-image:focus {
    color: #838383 !important;
}
#mini-cart > .button {
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
    color: #ffffff;
    border-color: rgba(<?php echo $rgbColorSite;?>, 1);
    border-width: 0;
    border-style: solid;
}
#mini-cart > .button:hover,
#mini-cart > .button:focus,
#mini-cart.open > .button {
    background-color: rgba(<?php echo $rgbColorSite;?>, 0.9) !important; 
    color: #ffffff !important;   
    border-color: rgba(<?php echo $rgbColorSite;?>, 0.9) !important;
    border-width: 0 !important;
    border-style: solid;
}
#mini-cart .icon-cart {
    background-image: url(/_images/icons/icon_cart.png);
}
#mini-cart.open .icon-cart {
    background-image: url(/_images/icons/icon_cart.png);
}
#search_mini_form .input-text {
    color: #a4a4a4;
    background-color: ;
    border-color: #e0e0e0;
    border-width: ;
    border-style: solid;
}
#search_mini_form .button {
    background-image: url(/_images/icons/icon_search.png);
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
    border-color: rgba(<?php echo $rgbColorSite;?>, 1);
    border-width: ;
    border-style: solid;
}
#search_mini_form .button:hover,
#search_mini_form .button:focus {
    background-image: url(/_images/icons/icon_search.png);
    background-color: rgba(<?php echo $rgbColorSite;?>, 0.9);
    border-color: rgba(<?php echo $rgbColorSite;?>, 0.9);
    border-width: ;
    border-style: solid;
}

/*========== Footer ==========*/
.footer {
    color: #cccccc;
    background-color: #444645;
        background-position: center center;
    background-repeat: repeat;
    font-size: 15px;
    font-weight: normal;
    font-family: "Gudea", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.footer-bottom {
    color: #cccccc;
    background-color: #444645;
}
.footer h3,
.footer .title {
    color: #e3e3e3;
    font-size: 19px;
    font-weight: normal;
    font-family: "Oswald", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.footer a {
    color: #cccccc;
}
.footer a:hover,
.footer a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 0.9);
}
.footer .copyright address {
    font-size: 14px;
}
.footer .social-links .icon {
    background-color: #626664;
    border-color: #626664;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
}
.footer .fblike-box a {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.footer .fblike-box a:hover,
.footer .fblike-box a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 0.9);
}
.footer .fblike-box a.button {
    color: #ffffff;
}
.footer .fblike-box a.button:hover,
.footer .fblike-box a.button:focus {
    color: #ffffff;
}
.footer-tweets {
    background-color: rgba(<?php echo $rgbColorSite;?>, 0.9);
    color: #444645;
}
.footer-tweets a,
#twitter-footer-slider a {
    color: #cdfa7e;
}
.footer-tweets a:hover,
#twitter-footer-slider a:hover {
    color: #686a69;
}
.footer-tweets .date,
#twitter-footer-slider .date {
    color: #cdfa7e;
}
.footer-tweets .twitter-icon {
    bottom: -15px;
    background-image: url(http://thesmartwave.net/venedor/media/eternal/venedor/icon/icon_twitter.png);
}
.footer-top {
    border-bottom: 1px solid #383938;
}
.footer-bottom {
    border-top: 1px solid #535554;
}
.footer-subscribe {
    background-color: #d6d6d6;
}
.footer-subscribe .block-title {
    color: #444b4c;
}
.footer-subscribe .input-box input {
    border: 3px solid #ffe019;
    background-color: #d6d6d6;
    color: #727b7c; 
}
.footer-bottom a {
    color: #cccccc;
}
.footer-bottom a:hover,
.footer-bottom a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 0.9);
}

/*========== Main ==========*/
.main-slider {
    border-top: 0 solid #dcdcdc;
    border-bottom: 1px solid #dcdcdc;
    background-color: #dcdcdc;
}

/*========== Breadcrumbs ==========*/
.breadcrumbs {
    color: #ffffff;
    border-top: 0 solid #d5d5d5;
    border-bottom: 0 solid #d5d5d5;
    font-size: 13px;
    font-weight: normal;
    font-family: "Oswald", "Helvetica Neue", Helvetica, Arial, sans-serif;
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
        background-position: center center;
    background-repeat: repeat;
}
.breadcrumbs a {
    color: #ffffff;
}
.breadcrumbs a:hover,
.breadcrumbs a:focus {
    color: #eeeeee;
}

/*========== Product ==========*/
.price {
    font-weight: bold;
    font-family: "Gudea", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.sales, 
.new {
    color: #ffffff;
    background-color: #c72929;
    font-weight: normal;
    font-family: "Oswald", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.new {
    color: #ffffff;
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.product-name,
.product-name h1,
.product-name h2,
.product-name h3,
.product-name h4,
#product-tabs > dt,
#cart-tabs > dt,
.review-title,
.author,
.fraction-slider .slide-title {
    font-family: "PT Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.product-name,
.product-name a {
    color: #565656;
}
a.product-name:hover,
a.product-name:focus,
.product-name a:hover,
.product-name a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.product-options .label,
.product-options .badge {
    color: #737373;
}
.author {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.ratings .rating-links span,
.ratings .rating-links a,
.ratings .amount { 
    color: #cccccc; 
}
.ratings .rating-links a:hover {
    color: #737373; 
}
.rating-box .rating {
    background-image: url(/_images/icons/bkg_rating.png);
}
.ratings-table .rating-box .rating {
    background-image: url(/_images/icons/bkg_rating_small.png);
}
.product-essential .elastislide-vertical {
    margin-top: -8.4px !important;
}
.product-essential .more-images {
    /*height: 582.4px;*/
}
.product-essential .more-images img {
    padding: 8.4px 0 !important;
}

/*========== Category ==========*/
.category-banner {
    color:  #585858;
    text-shadow: 1px 0 1px #ffffff;
    border-top: 0 solid #d5d5d5;
    border-bottom: 0 solid #d5d5d5;
    background-color: #f2f2f2;
        background-position: center center;
    background-repeat: repeat;
}
.main-content .category-banner .category-title h1,
.main-content .category-banner .category-description,
.category-banner .product-name,
.category-banner .category-title h1 {
    color:  #585858;
    text-shadow: 1px 0 1px #ffffff;
}
.category-banner .price {
    color: #e82c0c;
}
.slider-arrow.prev,
.toolbar .view-mode .last,
.toolbar .dropdown-select .arrow,
.fraction-slider .prev,
.bx-wrapper .bx-controls-direction .bx-prev,
.tp-leftarrow {
    -webkit-border-radius: 0 3px 3px 0;
       -moz-border-radius: 0 3px 3px 0;
            border-radius: 0 3px 3px 0;
}
.slider-arrow.next,
.toolbar .view-mode .first,
.fraction-slider .next,
.bx-wrapper .bx-controls-direction .bx-next,
.tp-rightarrow {
    -webkit-border-radius: 3px 0 0 3px;
       -moz-border-radius: 3px 0 0 3px;
            border-radius: 3px 0 0 3px;
}
.toolbar .view-mode .first.last {
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
}
.block-layered-nav .button-arrow,
.opc .step-title a {
    -webkit-border-radius: 0 0 3px 3px;
       -moz-border-radius: 0 0 3px 3px;
            border-radius: 0 0 3px 3px;
}
.block-layered-nav .button-arrow.open,
.opc .active .step-title a {
    -webkit-border-radius: 3px 3px 0 0;
       -moz-border-radius: 3px 3px 0 0;
            border-radius: 3px 3px 0 0;
}
.add-to-cart .button-up,
.qty-holder .button-up {
    -webkit-border-radius: 0 3px 0 0;
       -moz-border-radius: 0 3px 0 0;
            border-radius: 0 3px 0 0;
}
.add-to-cart .button-down,
.qty-holder .button-down {
    -webkit-border-radius: 0 0 3px 0;
       -moz-border-radius: 0 0 3px 0;
            border-radius: 0 0 3px 0;
}

.block-layered-nav .button-arrow,
.elastislide-vertical .elastislide-next,
.button-down,
.opc .step-title a {
    background-image: url(/_images/icons/grey/icon_arrow_down.png);
}
.block-layered-nav .button-arrow:hover,
.block-layered-nav .button-arrow:focus,
.elastislide-vertical .elastislide-next:hover,
.elastislide-vertical .elastislide-next:focus,
.button-down:hover,
.button-down:focus,
.opc .step-title a:hover,
.opc .step-title a:focus {
    background-image: url(/_images/icons/white/icon_arrow_down.png);
}
.block-layered-nav .button-arrow.open,
.elastislide-vertical .elastislide-prev,
.button-up,
.opc .active .step-title a {
    background-image: url(/_images/icons/grey/icon_arrow_up.png);    
}
.block-layered-nav .button-arrow.open:hover,
.block-layered-nav .button-arrow.open:focus,
.elastislide-vertical .elastislide-prev:hover,
.elastislide-vertical .elastislide-prev:focus,
.button-up:hover,
.button-up:focus,
.opc .active .step-title a:hover,
.opc .active .step-title a:focus {
    background-image: url(/_images/icons/white/icon_arrow_up.png);
}
.scrolltop {
    background-image: url(/_images/icons/white/icon_arrow_up_large.png);    
}
.scrolltop:hover,
.scrolltop:focus {
    background-image: url(/_images/icons/white/icon_arrow_up_large.png);
}
.slider-arrow.prev,
.large-icons .flex-direction-nav .flex-prev,
.fraction-slider .prev,
.tp-leftarrow {
    background-image: url(/_images/icons/grey/icon_arrow_prev_large.png);        
}
.bx-wrapper .bx-controls-direction .bx-prev {
    background-image: url(/_images/icons/grey/icon_arrow_prev_big.png);        
}
.slider-arrow.prev:hover,
.slider-arrow.prev:focus,
.large-icons .flex-direction-nav .flex-prev:hover,
.large-icons .flex-direction-nav .flex-prev:focus,
.fraction-slider .prev:hover,
.fraction-slider .prev:focus,
.tp-leftarrow:hover,
.tp-leftarrow:focus {
    background-image: url(/_images/icons/white/icon_arrow_prev_large.png);    
}
.bx-wrapper .bx-controls-direction .bx-prev:hover,
.bx-wrapper .bx-controls-direction .bx-prev:focus {
    background-image: url(/_images/icons/white/icon_arrow_prev_big.png);
}
.slider-arrow.next,
.large-icons .flex-direction-nav .flex-next,
.fraction-slider .next,
.tp-rightarrow {
    background-image: url(/_images/icons/grey/icon_arrow_next_large.png);    
}
.bx-wrapper .bx-controls-direction .bx-next {
    background-image: url(/_images/icons/grey/icon_arrow_next_big.png);    
}
.slider-arrow.next:hover,
.slider-arrow.next:focus,
.large-icons .flex-direction-nav .flex-next:hover,
.large-icons .flex-direction-nav .flex-next:focus,
.fraction-slider .next:hover,
.fraction-slider .next:focus,
.tp-rightarrow:hover,
.tp-rightarrow:focus {
    background-image: url(/_images/icons/white/icon_arrow_next_large.png);    
}
.bx-wrapper .bx-controls-direction .bx-next:hover,
.bx-wrapper .bx-controls-direction .bx-next:focus {
    background-image: url(/_images/icons/white/icon_arrow_next_big.png);    
}
.flex-direction-nav .flex-prev {
    background-image: url(/_images/icons/grey/icon_arrow_prev_small.png);        
}
.flex-direction-nav .flex-prev:hover,
.flex-direction-nav .flex-prev:focus {
    background-image: url(/_images/icons/white/icon_arrow_prev_small.png);        
}
.flex-direction-nav .flex-next {
    background-image: url(/_images/icons/grey/icon_arrow_next_small.png);    
}
.flex-direction-nav .flex-next:hover,
.flex-direction-nav .flex-next:focus {
    background-image: url(/_images/icons/white/icon_arrow_next_small.png);    
}
.button-asc {
    background-image: url(/_images/icons/grey/icon_asc.png);
}
.button-asc:hover,
.button-asc:focus {
    background-image: url(/_images/icons/white/icon_asc.png);
}
.button-desc {
    background-image: url(/_images/icons/grey/icon_desc.png);
}
.button-desc:hover,
.button-desc:focus {
    background-image: url(/_images/icons/white/icon_desc.png);
}
.dropdown-select .arrow {
    background-image: url(/_images/icons/grey/icon_arrow_down.png);
}
.dropdown-select.open .arrow,
.toolbar .dropdown-select.open .arrow {
    background-image: url(/_images/icons/white/icon_arrow_down.png);
}
.toolbar .button-grid,
.button-viewall {
    background-image: url(/_images/icons/grey/icon_grid.png);
}
.toolbar .button-grid:hover,
.toolbar .button-grid:focus,
.button-viewall:hover,
.button-viewall:focus,
.toolbar .button-active.button-grid {
    background-image: url(/_images/icons/white/icon_grid.png) !important;
}
.toolbar .button-list {
    background-image: url(/_images/icons/grey/icon_list.png);
}
.toolbar .button-list:hover,
.toolbar .button-list:focus,
.toolbar .button-active.button-list {
    background-image: url(/_images/icons/white/icon_list.png) !important;
}
.button-arrow.btn-remove {
    background-image: url(/_images/icons/grey/icon_remove.png);
}
.button-arrow.btn-remove:hover,
.button-arrow.btn-remove:focus {
    background-image: url(/_images/icons/white/icon_remove.png);
}
.button.next {
    background-image: url(/_images/icons/grey/icon_arrow_next.png);
}
.button.next:hover,
.button.next:focus {
    background-image: url(/_images/icons/white/icon_arrow_next.png);
}
.button.prev {
    background-image: url(/_images/icons/grey/icon_arrow_prev.png);
}
.button.prev:hover,
.button.prev:focus {
    background-image: url(/_images/icons/white/icon_arrow_prev.png);
}
.iradio.hover {
    background-image: url(/_images/icons/white/icon_radio.png);
}
.iradio.checked {
    background-image: url(/_images/icons/white/icon_radio.png);
}
.prettycheckbox:hover > a,
.prettyradio:hover > a {
    background-image: url(/_images/icons/white/icon_checkbox.png);
}
.prettycheckbox > a.checked,
.prettyradio > a.checked {
    background-image: url(/_images/icons/white/icon_checkbox.png);
}
.products-grid .item-active .btn-cart,
body.mobile .products-grid .btn-cart,
.products-grid .hover-disable .btn-cart {
    background-image: url(/_images/icons/grey/icon_addcart.png);
}
.products-grid .item-active .btn-cart:hover,
.products-grid .item-active .btn-cart:focus {
    background-image: url(/_images/icons/white/icon_addcart.png);
}
.products-grid .price-box,
.products-list .price-box,
.product-view .product-img-box .price-box {
    background-color: rgba(<?php echo $rgbColorSite;?>, 0.9);
}
.products-grid .price, 
.products-grid .price-label,
.products-list .price,
.products-list .price-label,
.product-view .product-img-box .price,
.product-view .product-img-box .price-label,
.product-view .product-shop .price,
.product-view .product-shop .price-label {
    color: #ffffff;
}
.slider-wrap .price-box,
.caption.price-box {
    background-color: rgba(<?php echo $rgbColorSite;?>, 0.9);
}
.caption.slide-title .color {
    color: rgba(<?php echo $rgbColorSite;?>, 0.9);
}
.slider-wrap .price-box .price,
.caption.price-box .price {
    color: #ffffff;    
}
.products-grid .old-price .price,
.products-grid .old-price .price-label,
.products-list .old-price .price,
.products-list .old-price .price-label,
.product-view .product-img-box .old-price .price,
.product-view .product-img-box .old-price .price-label,
.product-view .product-shop .old-price .price,
.product-view .product-shop .old-price .price-label,
.product-view .product-shop .price-notice .price,
.product-view .product-shop .price-notice .price-label {
    color: #eee;
    font-weight: normal !important;
}
.products-grid li.item {
    text-align: center;
}
.products-grid .item-inner {
    background-color: ;
    border: 1px solid #e8e8e8;
    -webkit-box-shadow: 0 0 0 #cccccc;
       -moz-box-shadow: 0 0 0 #cccccc;
            box-shadow: 0 0 0 #cccccc;
    padding: 20px 5px;
}
.products-grid .item-active .item-inner {
    background-color: #ffffff;
    border: 1px solid #e8e8e8;
    -webkit-box-shadow: 0 0 1px #e8e8e8;
       -moz-box-shadow: 0 0 1px #e8e8e8;
            box-shadow: 0 0 1px #e8e8e8;
    padding: 20px 5px;
}

/*========== Toolbar ===========*/
.pager,
.toolbar-bottom {
    border-top: 1px solid #e0e0e0;
}
.toolbar {
    border-bottom: 1px solid #e0e0e0;
}

/*========== Sidebar, Block, Data Table ==========*/
.block .block-title {
    color: #565656;
}
.sidebar {
    font-size: 15px;
    font-family: "Gudea", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.product-options dt label,
.box-reviews dt,
.data-table,
.fraction-slider .slide-subtitle {
        font-family: "Gudea", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.sidebar .block-layered-nav .price {
    font-size: 15px;
    font-weight: normal;
}
.block .block-title {
    font-weight: bold;
    text-transform: uppercase;
}
.block-layered-nav .block-subtitle,
.block-layered-nav dt {
    color: #565656;
    font-weight: bold;
    text-transform: uppercase;
}
.label,
.badge {
    color: #737373;
    background-color: transparent;
}
.block-layered-nav .currently ol {
    border: 1px solid #dcdcdc;
    background-color: #f4f4f4;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
}
.sidebar a,
.sidebar .block-layered-nav .price,
.sidebar .block-layered-nav .price .sub {
    color: #838383;    
}
.sidebar a:hover,
.sidebar a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 1);    
}
.sidebar .link-cart, .sidebar .link-wishlist, .sidebar .link-reorder, .sidebar .link-compare,
.block .actions a {
    color: rgba(<?php echo $rgbColorSite;?>, 1);   
}
.sidebar .link-cart:hover, .sidebar .link-wishlist:hover, .sidebar .link-reorder:hover, .sidebar .link-compare:hover, 
.sidebar .link-cart:focus, .sidebar .link-wishlist:focus, .sidebar .link-reorder:focus, .sidebar .link-compare:focus,
.block .actions a:hover, .block .actions a:focus {
    color: #838383;   
}
.sidebar .block dl {
    border: 1px solid #dcdcdc;
    border-bottom-width: 0;
    background-color: #fcfcfc;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
}
#product-tabs,
#cart-tabs {
    border: 1px solid #dcdcdc;
    border-bottom-width: 0;
    background-color: #f7f7f7;
}
#product-tabs,
#cart-tabs {
    border-bottom-width: 1px;
}
.box-reviews dd, 
.review-title {
    border-bottom: 1px solid #dcdcdc;
}
#product-tabs > dt,
#cart-tabs > dt {
    border-bottom: 1px solid #dcdcdc;
}
#product-tabs > dd,
#cart-tabs > dd {
    border-left: 1px solid #dcdcdc;
    border-bottom: 1px solid #dcdcdc;
}
.sidebar .block dt {
    color: #565656;
    border-bottom: 1px solid #dcdcdc;
    background-color: #f4f4f4;
}
.sidebar .block dd {
    border-bottom: 1px solid #dcdcdc;
    background-color: #fcfcfc;
}
.block-venedor-ads,
.block-subscribe {
    border: 1px solid #dcdcdc;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
    background-color: #f7f7f7;
}
.block-list .price {
    color: #e82c0c;
}
.block-list .old-price .price,
.product-shop .tier-prices .price {
    color: #737373;
}
.data-table {
    border: 1px solid #dcdcdc;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
}
.data-table thead tr,
.data-table tfoot tr {
    background-color: #f7f7f7;
}
.data-table thead th,
.data-table thead td,
.data-table tfoot th,
.data-table tfoot td,
.cart .totals table th, 
.cart .totals table td {
    color: #565656;
    border-right: 1px solid #dcdcdc;
    border-bottom: 1px solid #dcdcdc;
}
.data-table tbody th,
.data-table tbody td {
    border-right: 1px solid #dcdcdc;
    border-bottom: 1px solid #dcdcdc;
}
.cart-table tbody th,
.cart-table tbody td {
    border-right: 1px solid #eeeeee;
    border-bottom: 1px solid #eeeeee;
}
.data-table tbody th.last,
.data-table tbody td.last {
    border-right: 1px solid #dcdcdc;
}
.data-table tbody tr.last th,
.data-table tbody tr.last td {
    border-bottom: 1px solid #dcdcdc;
}
.data-table .price {
    color: #565656;
}
.data-table .total-price .price,
.cart .totals table tfoot td .price,
.compare-table .price {
    color: #e82c0c;
}
.item-options dd {
    color: #565656;
}
.compare-table th {
    color: #565656;
}
.compare-table tr.odd th {
    background-color: #ececec;
}
.compare-table tr.even th {
    background-color: #e5e5e5;
}
.compare-table tr.even td {
    background-color: #f7f7f7;
}
.my-account .data-table .price,
.my-wishlist .data-table .price {
    font-size: 15px;
    color: #737373;
    font-weight: normal;
}
/* scroll pane, price slider */
.jspDrag,
.price-slider .jslider .jslider-pointer {
    background-image: none;
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
}
.price-slider .jslider .jslider-bg .l {
    -webkit-border-radius: 3px 0 0 3px;
       -moz-border-radius: 3px 0 0 3px;
            border-radius: 3px 0 0 3px;
}
.price-slider .jslider .jslider-bg .r {
    -webkit-border-radius: 0 3px 3px 0;
       -moz-border-radius: 0 3px 3px 0;
            border-radius: 0 3px 3px 0;
}
.price-slider .jslider .jslider-bg .v {
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
}
    
/*========== Checkout Page ==========*/
.opc .step-title,
.opc .step-title h2,
.op_block_title { 
    color: #777777;
    font-family: "Gudea", "Helvetica Neue", Helvetica, Arial, sans-serif;
}
.opc .step-title,.op_block_title {
    border: 1px solid #dcdcdc;
    background-color: #f4f4f4;
}
.opc,
#onepagecheckout_orderform {
    color: #797878;
}
.op_block_title{ text-transform:uppercase; font-size:18px; padding:15px 20px; font-weight:bold; line-height:1.5; margin-bottom:10px; }
/*========== Form List ==========*/
.form-list,
.form-list li .prettyradio label {
    color: #797878;
}
.form-list li label {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
    background-color: #f4f4f4;
    -webkit-border-radius: 3px 0 0 3px;
       -moz-border-radius: 3px 0 0 3px;
            border-radius: 3px 0 0 3px;
    border-right: 1px solid #dcdcdc;
}
.form-list li label.label-wide {
    border-right-width: 0;
    border-bottom: 1px solid #dcdcdc;
}
.form-list .bootstrap-select.btn-group .btn .caret {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
    background-color: #f4f4f4;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
    border: 1px solid #f4f4f4;
    border-left: 1px solid #dcdcdc;
    background-image: url(/_images/icons/grey/icon_arrow_down_large.png);
}
.form-list .bootstrap-select.btn-group .btn:hover .caret,
.form-list .bootstrap-select.btn-group .btn:focus .caret {
    background-image: url(/_images/icons/white/icon_arrow_down_large.png);
}
.form-list li.control label {
    color: #737373;
    background-color: transparent;
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
    border-right-width: 0;
}

/*========== My Account ==========*/
.dashboard .box-account .number {
    background-color: #f4f4f4;
    border: 1px solid #dcdcdc;
    color: #737373;
}

/*========== Slider Controls, Background Colors ==========*/
.flex-control-paging li a,
.sequence-pagination li a,
.fs-pager-wrapper a,
.bx-wrapper .bx-pager.bx-default-pager a,
.tp-bullets.simplebullets.round .bullet {
    background-color: #565656;
}
.flex-control-paging li a.flex-active,
.sequence-pagination li.current a,
.fs-pager-wrapper .active,
.bx-wrapper .bx-pager.bx-default-pager a.active,
.tp-bullets.simplebullets.round .bullet:hover, 
.tp-bullets.simplebullets.round .bullet.selected {
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.fraction-slider .slide-subtitle {
    background-color: #444645;
    color: #e8e8e8;
}
.fraction-slider .slide-title {
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
    color: #ffffff;
}
.homeslider-products .product-name a,
.homeslider-products .product-desc {
    color: #444645;
}
.homeslider-products .product-name a:hover,
.homeslider-products .product-name a:focus {
    color: rgba(<?php echo $rgbColorSite;?>, 1);
}
.homeslider-products .price-box .price {
    color: #e82c0c;
}
.homeslider-products .bx-wrapper .bx-controls-direction a {
    -webkit-border-radius: 0;
       -moz-border-radius: 0;
            border-radius: 0;
    -webkit-box-shadow: 0 0 0 #000;
       -moz-box-shadow: 0 0 0 #000;
            box-shadow: 0 0 0 #000;
}
.main-slider .bx-wrapper .bx-controls-direction a:hover,
.main-slider .bx-wrapper .bx-controls-direction a:focus {
    -webkit-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
       -moz-box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2), 0 1px 2px rgba(0, 0, 0, 0.05);
}
#twitter-slider .flex-direction-nav .flex-prev {
    background-image: url(/_images/icons/grey/icon_tt_slider_prev.png);        
}
#twitter-slider .flex-direction-nav .flex-prev:hover,
#twitter-slider .flex-direction-nav .flex-prev:focus {
    background-image: url(/_images/icons/white/icon_tt_slider_prev.png);  
}
#twitter-slider .flex-direction-nav .flex-next {
    background-image: url(/_images/icons/grey/icon_tt_slider_next.png);        
}
#twitter-slider .flex-direction-nav a:hover,
#twitter-slider .flex-direction-nav a:focus {
    background-color: #444645;       
}
#twitter-slider .flex-direction-nav .flex-next:hover,
#twitter-slider .flex-direction-nav .flex-next:focus {
    background-image: url(/_images/icons/white/icon_tt_slider_next.png); 
}

/* ajax cart */
div.Growler-notice .cart-success {
        font-family: "PT Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
}

@media (min-width: 1200px) {
    /* product */
        .product-essential .elastislide-vertical {
        margin-top: -9.8px !important;
    }
    .product-essential .more-images {
        /*height: 621.6px;*/
    }
    .product-essential .more-images img {
        padding: 9.8px 0 !important;
    }
    }
@media (min-width: 768px) and (max-width: 991px) {
    /* header */
    .header .logo, 
    .header h1.logo {
        margin-bottom: 0px;
    }
        .mobile-header .header-menu {
        margin-top: 15px;
    }
        .mobile-header .quick-access1 {
        bottom: 75px !important;
    }
    /* product */
        .product-essential .elastislide-vertical {
        margin-top: -11.2px !important;
    }
    .product-essential .more-images {
        /*height: 806.4px;*/
    }
    .product-essential .more-images img {
        padding: 11.2px 0 !important;
    }
    }
@media (max-width: 767px) {
    /* header */
    .nav-container {
        background-color: transparent;
        background-image: none;
    }

    /* product */
        .product-essential .elastislide-vertical {
        margin-top: -8.8666666666667px !important;
    }
    .product-essential .more-images {
        /*height: 350.93333333333px;*/
    }
    .product-essential .more-images img {
        padding: 8.8666666666667px 0 !important;
    }
        /* home slider buttons */
        .main-slider .slider-arrow.prev,
    .main-slider .fraction-slider .prev,
    .main-slider .bx-wrapper .bx-controls-direction .bx-prev,
    .main-slider .tp-leftarrow {
        background-image: url(/_images/icons/grey/icon_arrow_prev.png);        
    }
    .main-slider .slider-arrow.prev:hover,
    .main-slider .slider-arrow.prev:focus,
    .main-slider .fraction-slider .prev:hover,
    .main-slider .fraction-slider .prev:focus,
    .main-slider .bx-wrapper .bx-controls-direction .bx-prev:hover,
    .main-slider .bx-wrapper .bx-controls-direction .bx-prev:focus,
    .main-slider .tp-leftarrow:hover,
    .main-slider .tp-leftarrow:focus {
        background-image: url(/_images/icons/white/icon_arrow_prev.png);    
    }
    .main-slider .slider-arrow.next,
    .main-slider .fraction-slider .next,
    .main-slider .bx-wrapper .bx-controls-direction .bx-next,
    .main-slider .tp-rightarrow {
        background-image: url(/_images/icons/grey/icon_arrow_next.png);    
    }
    .main-slider .slider-arrow.next:hover,
    .main-slider .slider-arrow.next:focus,
    .main-slider .fraction-slider .next:hover,
    .main-slider .fraction-slider .next:focus,
    .main-slider .bx-wrapper .bx-controls-direction .bx-next:hover,
    .main-slider .bx-wrapper .bx-controls-direction .bx-next:focus,
    .main-slider .tp-rightarrow:hover,
    .main-slider .tp-rightarrow:focus {
        background-image: url(/_images/icons/white/icon_arrow_next.png);    
    }
    }



@media (min-width: 1200px) {
    /* common */
    .button-up,
    .button-down,
    .add-to-cart .button-up,
    .add-to-cart .button-down {
        height: 26px;
    }
    .welcome-msg { 
        display: block; 
    }
    #category-banner-slider .product-details,
    .category-banner .category-details {
        padding: 40px;
    }
    .main-content .category-banner .category-wrap {
        top: 50px;
    }
    .main-content .category-banner .button {
        margin-top: 10px;
    }
    .mini-products-images-list .item { 
        width:87px; 
    }
    
    /* footer */
    .footer .fblike-box .button {
        margin-right: 40px;
    }
    .fb-persons {
        margin-left: -25px;
    }
    .fb-person {
        margin-left: 25px;
    }
    
    /* toolbar */
    .toolbar {
        border-bottom-width: 1px !important;
        margin-bottom: 30px; 
    }
    .toolbar-bottom {
        border-top-width: 1px !important;
        margin-top: 30px;
    }
    .toolbar-bottom .toolbar {
        border-bottom-width: 0 !important;
    }
    .sorter .view-mode {
        float: left;
    }
    .toolbar .sorter {
        float: left;
    }
    .toolbar .pager {
        padding-left: 450px;
        padding: 0;
        margin: 0;
        border-top-width: 0 !important;        
    }
    .toolbar .pager .limiter {
        position: static;
    }
    
    /* category */
    .products-grid .product-image,
    .bv3 .products-grid .product-image {
        width: <?php echo $image_listing_width;?>px;
        height:<?php echo $image_listing_height;?>px;
        max-width:100%
    }
    #upsell-products-list .products-grid .product-image {
        width: <?php echo $image_suggest_width;?>px;
        height:<?php echo $image_suggest_height;?>px;
        max-width:100%
    }
    .products-grid .item-active .btn-cart,
    body.mobile .products-grid .btn-cart,
    .products-grid .hover-disable .btn-cart {
        text-indent: 0;
        width: auto !important;
        background-image: none !important;
    }
    .price-slider .priceTextBox {
        width: 75px;
    }

    /* product */
    .product-essential-inner > .product-img-box,
    .product-essential-inner > .product-shop {
        width: 50%;
        margin-left: 0;
    }
    .product-essential .product-image {
        width: 430px;
        margin-left: 18px;
    }
    .bv3 .product-essential .product-image {
        margin-left: 14px;
    }
    .product-essential .more-images {
        width: 97px;
    }
    .product-essential .elastislide-vertical {
        margin-top: -7px;
    }
    .product-essential .more-images img {
        padding: 7px 0;
    }
    .bv3 .product-essential .product-shop {
        padding-left: 25px;
    }
    .add-to-box .addthis-icons { 
        width: 350px;
        margin-left: 40px;
    }
    .add-to-box .addthis-icons > span {
        display: inline;
    }
    .add-to-cart .qty { 
        padding:9px 40px 9px 15px; 
        width: 137px;
        height: 52px;
    }
    .add-to-cart button.btn-cart { 
        font-size: 19px; 
        padding: 15px 25px;
        margin-left: 25px;
    }
    #product-tabs > dt,
    #cart-tabs > dt {
        width: 230px;
    }
    #product-tabs > dt.open,
    #cart-tabs > dt.open {
        width: 231px;
    }
    #product-tabs > dd,
    #cart-tabs > dd {
        left: 230px;
    }
    .review-title  {
        padding-right: 100px;
    }
    .review-title span {
        display: inline;
    }
    .product-view .box-tags .form-add input.input-text { 
        width: 250px; 
    }
    .upsell-products .item .product-image,
    .crosssell-products .item .product-image {
        width: 228px;
    }
    #product-tabs .crosssell .flex-direction-nav a {
        top: 132px;
    }
    .product-shop .button-up,
    .product-shop .button-down {
        height: 26px;
    }
    .product-shop .qty-holder .button-up,
    .product-shop .qty-holder .button-down {
        height: 21px;
    }
    
    /* block, sidebar */
    .block .block-title {
        font-size: 25px;
    }
    .block-list .flex-direction-nav a {
        margin-top: -40px;
    }
    .block-subscribe {
        padding: 25px 25px 20px;
    }
    
    /* Price */
    .products-grid .price-box {
        -webkit-border-radius: 62px;
           -moz-border-radius: 62px;
                border-radius: 62px;
        width: 125px;
        height: 50px;
        font-size: 16px;
    }
    .products-grid .regular-price,
    .products-grid .price-box > .price {
        margin-top: 16px;
    }
    .products-grid .old-price {
        margin-top: 3px;
        font-size: 14px;
    }
    .products-grid .price-from {
        margin-top: 20px;
    }
    .products-grid .minimal-price {
        margin-top: 27px
    }
    .products-grid .minimal-price .price-label {
        font-size: 14px;
    }
    .products-grid .price-review .reviews-wrap {
        float: right;
        margin-top: 3px;
    }
        
    .product-essential .product-img-box .price-box,
    .slider-wrap .price-box {
        -webkit-border-radius: 100px;
           -moz-border-radius: 100px;
                border-radius: 100px;
        width: 150px;
        height: 50px;
        font-size: 17px;
    }
    .product-essential .product-img-box .regular-price,
    .product-essential .product-img-box .price-box > .price,
    .slider-wrap .price-box .price {
        margin-top: 15px;
    }
    .product-essential .product-img-box .old-price {
        margin-top: 3px;
        margin-bottom: 0px;
        font-size: 14px;
    }
    .product-essential .product-img-box .price-from {
        padding-bottom: 10px;
        margin-bottom: 7px;
    }
    .product-essential .product-img-box .price-from,
    .product-essential .product-img-box .price-to {
        font-size: 25px;
    }
    .product-essential .product-img-box .price-from,
    .product-essential .product-img-box .minimal-price {
        margin-top: 29px;
    }
    .product-essential .product-img-box .minimal-price .price-label {
        font-size: 20px;
        margin-bottom: 8px;
    }
    .product-essential .product-img-box .labels {
        width: 80px;
        font-size: 20px;
    }
    .product-essential .product-img-box .new.circle,
    .product-essential .product-img-box .sales.circle {
        width: 80px;
        height: 80px;
        -webkit-border-radius: 40px;
           -moz-border-radius: 40px;
                border-radius: 40px;
        padding: 30px 0;
    }
    
    /* data-table */
    .compare-table .product-image {
        width: 180px;
    }
    
    /* form list */
    .form-list .input-range .start input.input-text {
        width: 52%;
    }
    .form-list .input-range .end input.input-text {
        width: 37%;
    }
    .form-list .customer-dob .dob-day {
        width: 36%;
    }
    .form-list .customer-dob .dob-month {
        width: 25%;
    }
    .form-list .span6 .customer-dob .dob-day,
    .form-list .col-sm-6 .customer-dob .dob-day,
    .span6 .form-list .customer-dob .dob-day,
    .col-sm-6 .form-list .customer-dob .dob-day {
        width: 48%;
    }
    .form-list .span6 .customer-dob .dob-month,
    .form-list .col-sm-6 .customer-dob .dob-month,
    .span6 .form-list .customer-dob .dob-month,
    .col-sm-6 .form-list .customer-dob .dob-month {
        width: 21%;
    }
    .form-list .span6 .customer-dob .dob-year,
    .form-list .col-sm-6 .customer-dob .dob-year,
    .span6 .form-list .customer-dob .dob-year,
    .col-sm-6 .form-list .customer-dob .dob-year  {
        width: 25%;
    }
    
    /* contact us */
    #contact-gmap {
        height: 350px;
    }
    #contactForm .contact-info {
        width: 40.1709%;
    }
    #contactForm .contact-comment {
        width: 57.265%;
        margin-left: 2.5641%;
    }
    
    /* home slider */
    .homeslider-products .product-name {
        font-size: 45px;
        margin-bottom: 20px;
    }
    .homeslider-products .price-box .price {
        font-size: 40px;
    }
    .homeslider-products .price-box {
        margin-bottom: 20px;
    }
    .homeslider-products .product-desc {
        padding: 0 85px;
        font-size: 18px;
    }
    .homeslider-products .btn-cart,
    .homeslider-products .add-to-cart .btn-cart {
        margin-top: 20px;
    }
 
    /* twitter tweets */
    .footer-tweets #twitter-slider {
        padding: 0 150px 0 100px;
    }
    .footer-tweets .twitter-slider1 {
        padding: 0 80px;
    }
}

@media (min-width: 768px) and (max-width: 991px) {
    .main h2.subtitle, 
    .main .widget .widget-title h2, 
    .main .page-title h1,
    .footer-banner h2.subtitle {
        font-size: 38px;
    }
    .main h2.subtitle .line,
    .main .widget .widget-title h2 .line,
    .main .page-title h1 .line {
        left: 190px;
    }
    .footer-banner h2.subtitle .line {
        left: 240px;
    }
    h2 {
        font-size: 30px;
    }
    .page-title button.button {
        margin-top: 22px;
    }
    
    /* header */
    .header {
        padding-top: 60px;
    }
    .header-top .container { 
        position: relative; 
    }
    .login-link { 
        position: absolute; 
        right: 0; 
        top: 40px; 
        padding-right: 5px; 
        font-size: 15px;
    }
    .login-link.static {
        position: static;
    }
    .container-fluid .login-link {
        right: 25px;
    }
    .bv3 .login-link {
        right: 12px;
    }    
    .quick-access {
        float: none;
        position: absolute;
        right: 0;
        bottom: 90px;
    }
    .bv3 .quick-access {
        right: 15px;
    }
    .header-top-both .block-language {
        margin-left: 10px;
        margin-right: 10px;
    }
    .header-top-both #mini-cart {
        margin-left: 0;
    }
    .header-top-both .mobile-hide {
        display: none;
    }
    .header-top-both .block-currency .dropdown-toggle .name, 
    .header-top-both .block-currency .dropdown-menu .name, 
    .header-top-both .block-language .dropdown-toggle .name,
    .header-top-both .block-language .dropdown-menu .name {
        display: none;
    }
    .header-top-both .block-currency .dropdown-toggle .symbol,
    .header-top-both .block-currency .dropdown-menu .symbol {
        display: block;
    }
    .header-top-both .block-language .dropdown-toggle .icon-flag,
    .header-top-both .block-language .dropdown-menu .icon-flag {
        margin-right: 0;
    }
    #search_mini_form .form-search .input-text {
        position: static;
        display: inline-block !important;
        width: 160px !important;
        left: 160px !important;
    }
    .header-right {
        margin-top: -20px;
    }
    .header-menu-right {
        margin-top: 0;
    }
    .header-menu-right .nav-container {
        float: left;
    }
    .header-menu-right div.eternal-custom-menu-popup {
        right: auto;
        left: 0;
    }
        
    /* footer */
    .footer-top .span3,
    .footer-top .col-md-3 {
        width: 31.4917%;        
    }
    .footer-top .footer-column-4 {
        width: 100%;
        margin-left: 0;
    }
    .scrolltop {
        display: none;
    }
    .footer-subscribe .block-title, 
    .footer-subscribe form {
        text-align: center;
        float: none;
        margin-bottom: 10px;
    }
    .footer-subscribe .input-box,
    .footer-subscribe .actions {
        float: none;
    }
    .footer-subscribe .input-box {
        margin-bottom: 20px;
    }
    
    /* main content */
    .main-content,
    .sidebar {
        padding: 0 0 40px;
    }
    
    /* category */
    #category-banner-slider .slide-shadow {
        background-image: url(/_images/icons/category_banner_slider_shadow_small.png);
    }
    #category-banner-slider .product-image,
    .category-banner .category-image {
        width: 290px;
    }
    #category-banner-slider .product-details,
    .category-banner .category-details {
        padding: 0 0 0 25px;
    }
    #category-banner-slider .sales {
        top: 10px;
        font-size: 23px;
        padding: 7px 15px;
    }
    .main-content .category-banner .category-wrap {
        top: 30px;
        left: 30px;
    }
    .main-content .category-banner .category-description {
        display: none;
    }
    .main-content .category-banner .button {
        padding: 6px 12px;
        font-size: 12px;
        margin-top: 0;
    }
    .products-list .product-image { 
        width: 170px; 
    }
    .products-list .product-shop { 
        margin-left: 200px; 
    }
    .products-list .ratings .rating-box {
        float: none;
    }
    .price-slider .priceTextBox {
        width: 55px;
    }
    
    /* sidebar */
    .mini-products-images-list .item { 
        width: 72px; 
    }
    
    /* toolbar */
    .toolbar .pager .limiter {
        position: static;
        float: left;
    }
    
    /* product */
    ..product-essential-inner {
        margin-left: 0;
    }
    .product-essential .product-img-box,
    .product-essential .product-shop {
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }
    .product-essential .product-image {
        width: 560px;
        margin-left: 16px;
    }
    .product-essential .more-images {
        width: 128px;
    }
    .product-essential .elastislide-vertical {
        margin-top: -8px;
    }
    .product-essential .more-images img {
        padding: 8px 0;
    }
    .product-essential .product-name h1 {
        margin-top: 20px;
    }
    .add-to-box .add-links-wrap {
        width: 455px;
    }
    .product-shop .price-review > .price-box,
    .product-shop .price-review > .price-box-bundle,
    .product-shop .price-review > .ratings {
        display: block;
        width: 100%;
        float: left;
        margin-bottom: 10px;
    }
    display: inline-block;
    .add-to-box .addthis-icons > span {
        display: inline;
    }
    .add-to-box .addthis-icons { 
        width: 300px; 
    }
    .block-list #block-related .flex-direction-nav a {
        margin-top: -68px;
    }
    .box-up-sell .flex-direction-nav a,
    .box-cross-sell .flex-direction-nav a,
    #brand-slider .flex-direction-nav a,
    .featured-products .flex-direction-nav a {
        margin-top: -91px;
    }
    #brand-slider .flex-direction-nav a {
        margin-top: -77px;
    }
    
    /* Price */
    .products-list .price-box {
        -webkit-border-radius: 36px;
           -moz-border-radius: 36px;
                border-radius: 36px;
        width: 72px;
        height: 72px;
        font-size: 16px;
    }
    .products-list .regular-price,
    .products-list .price-box > .price {
        margin-top: 26px;
    }
    .products-list .old-price {
        margin-top: 14px;
        font-size: 16px;
    }
    .products-list .price-from {
        margin-top: 15px;
    }
    .products-list .price-from {
        padding-bottom: 3px;
        margin-bottom: -2px;
    }
    .products-list .minimal-price {
        margin-top: 24px;
    }
    .products-list .minimal-price .price-label {
        font-size: 12px;
        margin-bottom: -3px;
    }
    
    .product-essential .product-img-box .price-box {
       
        
         -webkit-border-radius: 100px;
           -moz-border-radius: 100px;
                border-radius: 100px;
        width: 150px;
        height: 50px;
        font-size: 17px;
        bottom:-10px;
        right: -10px;
    }
    .product-essential .product-img-box .regular-price,
    .product-essential .product-img-box .price-box > .price {
        margin-top: 15px;
    }
    .product-essential .product-img-box .old-price {
       margin-top: 5px;
        margin-bottom: 0;
        font-size: 13px;
    }
    .product-essential .product-img-box .price-from {
        padding-bottom: 10px;
        margin-bottom: 7px;
    }
    .product-essential .product-img-box .price-from,
    .product-essential .product-img-box .price-to {
        font-size: 23px;
    }
    .product-essential .product-img-box .price-from,
    .product-essential .product-img-box .minimal-price {
        margin-top: 44px;
    }
    .product-essential .product-img-box .minimal-price .price-label {
        font-size: 17px;
        margin-bottom: 8px;
    }
    .product-essential .product-img-box .labels {
        width: 90px;
        font-size: 22px;
    }
    .product-essential .product-img-box .new.circle,
    .product-essential .product-img-box .sales.circle {
        width: 90px;
        height: 90px;
        -webkit-border-radius: 45px;
           -moz-border-radius: 45px;
                border-radius: 45px;
        padding: 34px 0;
    }
    
    .slider-wrap .price-box {
        -webkit-border-radius: 50px;
           -moz-border-radius: 50px;
                border-radius: 50px;
        width: 100px;
        height: 100px;
        font-size: 23px;
    }

    /* Data Table */
    .data-table .product-image {
        width: 150px;
        margin-bottom: 15px;
        float: none;
    }
    .data-table .product-shop {
        float: none;
        padding-left: 0;
    }
    .compare-table .product-image {
        width: 150px;
    }
    .compare-table .product-image img {
        width: 100%;
        height: auto;
    }
    .compare-table .btn-cart {
        margin-bottom: 10px;
    }
    .compare-table .add-to-links {
        display: block;
        margin: 0;
    }
    .compare-table .btn-remove {
        margin-top: 0;
    }
    .data-table .nobr {
        white-space: normal !important;
    }
    .my-account .data-table .price,
    .my-account .data-table th,
    .my-account .data-table td,
    .my-wishlist .data-table .price,
    .my-wishlist .data-table th,
    .my-wishlist .data-table td {
        font-size: 15px;
        padding: 10px 5px;
    }
    
    /* shopping cart */
    .cart-tabs,
    .cart-totals {
        width: auto;
        margin-left: 20px;
        float: none;
    }
    
    /* form list */
    .form-list input.input-text {
        padding-left: 140px;
        padding-right: 10px;
    }
    .form-list select {
        padding-left: 140px;
    }
    .form-list .bootstrap-select.btn-group .btn .filter-option {
        left: 140px;
    }
    .form-list li label {
        width: 130px;
        padding: 0 10px;
    }
    .form-list .input-range .start input.input-text {
        width: 56%;
    }
    .form-list .input-range .end input.input-text {
        width: 28%;
        padding-left: 10px;
    }
    .form-list .input-range .separator {
        width: 4%;
    }
    .form-list .customer-dob .dob-day {
        width: 39.5%;
    }
    .form-list .customer-dob .dob-month {
        width: 21.5%;
    }
    .form-list .span6 .customer-dob .dob-day,
    .form-list .col-sm-6 .customer-dob .dob-day,
    .span6 .form-list .customer-dob .dob-day,
    .col-sm-6 .form-list .customer-dob .dob-day {
        width: 55%;
    }
    .form-list .span6 .customer-dob .dob-month,
    .form-list .col-sm-6 .customer-dob .dob-month, 
    .span6 .form-list .customer-dob .dob-month,
    .col-sm-6 .form-list .customer-dob .dob-month {
        width: 18%;
    }
    .form-list .span6 .customer-dob .dob-year,
    .form-list .col-sm-6 .customer-dob .dob-year,
    .span6 .form-list .customer-dob .dob-year,
    .col-sm-6 .form-list .customer-dob .dob-year {
        width: 21%;
    }
    .span6 .sp-methods select.month,
    .col-sm-6 .sp-methods select.month { width:230px; }
    .span6 .sp-methods select.year,
    .col-sm-6 .sp-methods select.year { width:100px; }
    
    /* contact us */
    #contact-gmap {
        height: 215px;
    }
    
    /* home slider */
    .homeslider-products .product-name {
        font-size: 35px;
        margin-bottom: 10px;
    }
    .homeslider-products .price-box .price {
        font-size: 30px;
    }
    .homeslider-products .price-box {
        margin-bottom: 5px;
    }
    .homeslider-products .product-desc {
        padding: 0 10px;
        font-size: 15px;
    }
    .homeslider-products .btn-cart,
    .homeslider-products .add-to-cart .btn-cart {
        margin-top: 10px;
    }
    
    /* twitter tweets */
    .footer-tweets #twitter-slider {
        padding: 0 100px 0 60px;
    }
    .footer-tweets .twitter-slider1 {
        padding: 0 60px;
    }
}

@media (max-width: 767px) {
    /* common */
    body {
        padding-right: 0;
        padding-left: 0;
        padding-top: 0 !important;
    }
    .bv3 .std .row {
        margin-left: 0;
        margin-right: 0;
    }
    .bv3 .std .row > div {
        padding-left: 0;
        padding-right: 0;
    }
    .mobile-hide {
        display: none;
    }
    .btn-large,
    .buttons-set button.button,
    .buttons-set .back-link a {
        padding: 8px 15px;
        font-size: 15px;
    }
    .bv3.bsl .main-content {
        padding-left: 0;
    }
    .bv3.bsr .main-content {
        padding-right: 0;
    }
    .main-content-right {
        float: none !important;
    }
    .main h2.subtitle, 
    .main .widget .widget-title h2, 
    .main .page-title h1,
    .footer-banner h2.subtitle {
        font-size: 30px;
        padding-left: 5px;
    }
    .main h2.subtitle .line,
    .main .widget .widget-title h2 .line,
    .main .page-title h1 .line,
    .footer-banner h2.subtitle .line {
        display: none;
    }
    h2 {
        font-size: 25px;
    }
    .page-title button.button {
        margin-top: 16px;
    }
    p.desc {
        font-size: 14px;
    }
    
    /* header */
    .header {
        padding-top: 75px;
    }
    .header-top .container { 
        position: relative; 
    }
    .header .logo, 
    .header h1.logo {
        margin-bottom: 90px;
        float: none;    
        text-align: center;
        display: block;
    }
    .header-container.fixed .header-menu {
        position: static;
    }
    .toplinks {
        margin-left: 10px;
    }
    .toplinks a span {
        display: none;
    }
    .toplinks a {
        padding-left: 12px;
        padding-right: 12px;
        margin-left: 0;
        margin-right: 0;
        border-left: 1px solid rgba(0,0,0,0.2);
    }
    .toplinks li.last a {
        border-right: 1px solid rgba(0,0,0,0.2);
    }
    .toplinks [class*="icon"] {
        margin-right: 0;
    }
    .login-link { 
        position: absolute; 
        right: 0; 
        top: 40px; 
        padding-right: 10px;
        font-size: 15px; 
    }
    .login-link.static {
        position: static;
    }
    .bv3 .login-link {
        right: 15px;
    }
    .block-language {
        margin-left: 10px;
        margin-right: 10px;
    }
    .header-top #mini-cart {
        margin-left: 0;
    }
    .block-currency .dropdown-toggle .name, 
    .block-currency .dropdown-menu .name, 
    .block-language .dropdown-toggle .name,
    .block-language .dropdown-menu .name {
        display: none;
    }
    .block-currency .dropdown-toggle .symbol,
    .block-currency .dropdown-menu .symbol {
        display: block;
    }
    .block-language .dropdown-toggle .icon-flag,
    .block-language .dropdown-menu .icon-flag {
        margin-right: 0;
    }
    .block-currency, 
    .block-language, 
    .block-currency .dropdown-menu > li > a, 
    .block-currency .dropdown-submenu:focus > a, 
    .block-language .dropdown-menu > li > a, 
    .block-language .dropdown-submenu:focus > a {
        width: auto;
    }    
    .quick-access {
        float: right;
        right: auto;
        bottom: auto;
        margin-top: 18px;
        position: static;
    }
    #search_mini_form {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 90px;
        width: 100%;
    }
    #search_mini_form .form-search {
        width: 205px;
        margin: 0 auto;
    }
    #search_mini_form .form-search .input-text {
        position: static;
        display: inline-block !important;
        width: 160px !important;
        left: 160px !important;
    }
    #mini-cart {
        margin-right: 10px;
    }
    .nav-container {
        position: static;
    }    
    .header-both {
        position: absolute;
        right: 10px;
        bottom: 15px;
        z-index: 1000;
        margin-bottom: 0;
    }  
    .header-both2 {
        right: auto;
        left: 140px;
    }  
    .header-both #mini-cart {
        margin-left: 0;
    }
    .header-menu {
        margin-bottom: 0px !important;
        margin-top: 0;
        padding-bottom: 0 !important;
    }
    .header-right #mini-cart .dropdown-menu {
        top: 50px;
    }
    #custommenu-mobile {
        margin: 10px auto 10px;
    }
    .mobile-header #menu-button {
        margin-bottom: 10px;
        padding-bottom: 0;
    }
    .header-menu-right .nav-container {
        float: left;
    }
    .header-menu-right div.eternal-custom-menu-popup {
        right: auto;
        left: 0;
    }
    /* footer */
    .footer-bottom .social-links,
    .footer-bottom .copyright {
        text-align: center;
    }
    .scrolltop {
        display: none;
    }
    #brand-slider {
        margin-top: 40px;
    }
    .footer-subscribe .block-title, 
    .footer-subscribe form {
        text-align: center;
        float: none;
        margin-bottom: 10px;
    }
    .footer-subscribe form {
        margin-bottom: 5px;
    }
    .footer-subscribe .input-box, 
    .footer-subscribe .actions {
        display: block; 
        float: none;   
        margin: 0;
    }
    .footer-subscribe .input-box {
        margin-bottom: 20px;
    }
    .footer-subscribe .input-box input {
        width: 260px;
    }
    
    /* main content */
    .main-banner img {
        max-width: 350px;
    }
    .main-banner .container > div {
        padding-left: 0;
        padding-right: 0;
    }
    .main-content,
    .sidebar {
        padding: 0 0 40px;
    }
    .main-container .container,
    .footer-banner .container {
        padding-left: 10px;
        padding-right: 10px;
    }
    .bv3 .main-container .container,
    .bv3 .footer-banner .container {
        padding-left: 15px;
        padding-right: 15px;
    }
    
    /* block, sidebar */
    .sidebar-right,
    .sidebar-left,
    .bv3 .sidebar-right,
    .bv3 .sidebar-left {
        padding: 0 0 40px;
    }
    .sidebar .block-venedor-ads {
        width: 300px;
        margin-left: auto;
        margin-right: auto;
    }
    .block .block-title {
        font-size: 25px;
    }
    .mini-products-images-list .item { 
        width: 92px; 
    }
    .block-list .flex-direction-nav a {
        margin-top: -40px;
    }
    
    /* category */
    .category-banner {
        padding-left: 0;
        padding-right: 0;
    }
    #category-banner-slider .product-image,
    .category-banner .category-image {
        width: 290px;
    }
    #category-banner-slider .slide-shadow {
        background-image: url(/_images/icons/category_banner_slider_shadow_small.png);
    }
    #category-banner-slider .product-details,
    .category-banner .category-details {
        padding: 0 10px 20px;
    }
    #category-banner-slider .sales {
        top: 10px;
        font-size: 23px;
        padding: 7px 15px;
    }
    .slider-arrow,
    .main-slider .fraction-slider .prev,
    .main-slider .fraction-slider .next,
    .main-slider .bx-wrapper .bx-controls-direction a,
    .main-slider .tp-leftarrow,
    .main-slider .tp-rightarrow {
        width: 55px;
        height: 36px;
        padding: 0;
    }
    .category-banner .product-name,
    .category-banner .category-title h1 {
        font-size: 35px;
    }
    .category-banner .price {
        font-size: 20px;
    }
    .category-banner .button {
        padding: 6px 12px;
        font-size: 12px;
    }
    .main-content .category-banner .category-wrap {
        top: 10px;
        left: 20px;
    }
    .category-banner .category-description {
        display: none;
    }
    .main-content .category-banner .category-wrap {
        width: auto;
        right: 20px;
    }
    .main-content .category-banner .category-title h1 {
        margin-bottom: 10px;
    }
    .main-content .category-banner .button {
        margin-top: 0;
    }
    .category-banner .category-image-wrap {
        float: left;
        width: 40%;
        margin-left: 5%;
    }
    .category-banner .category-image-wrap .category-image {
        width: 100%;
    }
    .category-banner .category-details-wrap {
        float: left;
        width: 55%;
    }
    .category-banner .category-details-wrap .category-title h1 {
        margin-bottom: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        font-size: 30px;
    }
    .category-banner .category-details-wrap .button {
        margin-top: 0;
    }
    .products-grid li.item {
        height: auto !important;
        width: 100%;
        text-align: center;
    }
    .bv3 .products-grid li.item {
        padding-left: 0;
        padding-right: 0;
    }
    .products-grid li.item .item-inner {
        height: auto !important;
    }
    .products-grid .item-active.addlinks-block .item-inner {
        position: static;
    }
    .products-grid .addlinks-block .btn-cart {
        margin-left: auto !important;
    }
    .products-grid .addlinks-block .add-to-links,
    .flexslider.products-grid .addlinks-block .add-to-links {
        margin-left: auto !important;
        margin-right: auto !important;
        left: 0;
    }
    .products-grid .item-active .btn-cart,
    body.mobile .products-grid .btn-cart,
    .products-grid .hover-disable .btn-cart {
        text-indent: 0;
        width: auto !important;
        background-image: none !important;
    }
    .products-grid .product-image {
        width: <?php echo $image_listing_width;?>px;
        height:<?php echo $image_listing_height;?>px;
        max-width:100%
    }
    .products-list .product-image { 
        width: 150px; 
        margin-right: 20px;
        margin-bottom: 40px; 
    }
    .products-list .product-image.no-price {
        margin-bottom: 20px;
    }
    .products-list .product-shop { 
        margin-left: 0;
    }
    .products-list .ratings,
    .products-list .ratings .rating-box {
        float: none;
    }
    .products-list .ratings:after {
        clear: none;
    }
    .products-list .actions {
        float: left;
        width: 100%;
    }
    .price-slider .priceTextBox {
        width: 75px;
    }
    
    /* toolbar */
    .toolbar .actions {
        margin-right: 0;
    }
    .toolbar .sorter {
        padding-bottom: 42px;
    }
    .toolbar .sorter .sort-by {
        float: right;
        margin-right: 0;
    }
    .toolbar .pager .limiter {
        position: absolute;
        left: 0;
        top: 60px;
    }
    .toolbar .sorter .view-mode {
        position: absolute;
        right: 0;
        top: 60px;
    }
    .toolbar .pager {
        text-align: center;
    }
    .toolbar .pager .pages {
        float: none;
    }
    .toolbar-bottom .pager .limiter {
        display: none;
    }
    
    /* product */
    .product-essential .product-image {
        width: 238px;
        margin-left: 7px;
    }
    .product-essential .more-images {
        width: 50px;
    }
    .product-essential .elastislide-vertical {
        margin-top: -6.33333px;
    }
    .product-essential .more-images img {
        padding: 6.33333px 0;        
    }
    .products-grid .price-review > .price-box,
    .products-grid .price-review > .price-box-bundle,
    .products-grid .price-review .reviews-wrap {
        display: block;
        text-align: center;
        margin-bottom: 10px;
    }
    .product-shop .price-review > .price-box,
    .product-shop .price-review > .price-box-bundle,
    .product-shop .price-review > .ratings {
        margin-bottom: 10px;
        display: block;
    }
    .product-shop .price-review > .price-box .minimal-price-link {
        display: block;
    }
    .elastislide-vertical nav span {
        margin-left: -16px;
    }
    .email-friend { 
        margin-left: 0;
        display: inline-block; 
    }
    .email-friend span,
    .add-to-links span { 
        display: inline-block;
        line-height: 34px;
        margin-left: 10px;
        margin-right: 30px;
        font-size: 13px;
        text-transform: uppercase; 
    }
    .add-to-box .addthis-icons {
        float: none;
        clear: both;
        margin-left: 0;
    }
    .block-list #block-related .flex-direction-nav a {
        margin-top: -68px;
    }
    .block-related .block-subtitle {
        font-size: 14px;
    }
    #product-tabs > dt,
    #cart-tabs > dt {
        width: auto;
        padding: 16px 15px;
    }
    #product-tabs > dt.open,
    #cart-tabs > dt.open {
        width: auto;
        padding: 16px 15px;
        margin-right: 0;
        border-bottom-width: 1px !important;
    }    
    #product-tabs > dd,
    #cart-tabs > dd {
        position: relative;
        left: 0;
        border-left-width: 0;
        padding: 96%;
        padding: 20px 15px;
        border-bottom-width: 1px !important;
    }
    #product-tabs > dd:last-child,
    #cart-tabs > dd:last-child {
        border-bottom-width: 0 !important;
    }
    #product-review-table,
    .box-reviews .form-list {
        font-size: 14px;
    }
    #product-review-table th {
        font-size: 13px;
        padding: 3px;
    }
    .product-view .box-tags .form-add label {
        float: none;
        display: block;
        margin-bottom: 10px;
    }
    .product-view .box-tags .form-add .input-box {
        margin-left: 0;
    }
    #product-tabs .crosssell .flex-direction-nav .flex-prev {
        left: 10px;
    }
    #product-tabs .crosssell .flex-direction-nav .flex-next {
        right: 10px;
    }  
    .upsell-products .item .product-image {
        width: 228px;
    }
    .box-up-sell .flex-direction-nav a,
    .box-cross-sell .flex-direction-nav a,
    #brand-slider .flex-direction-nav a,
    .featured-products .flex-direction-nav a {
        width: 40px;
        height: 30px;
        margin-top: -80px;
    }
    .box-up-sell .flex-direction-nav .flex-prev, 
    .box-cross-sell .flex-direction-nav .flex-prev, 
    #brand-slider .flex-direction-nav .flex-prev,
    .featured-products .flex-direction-nav .flex-prev {
        right: 43px;
    }
    #brand-slider .flex-direction-nav a {
        margin-top: -68px;
    }
    
    /* Price */
    .products-grid .price-box, 
    .product-essential .product-img-box .price-box {
        -webkit-border-radius: 65px;
           -moz-border-radius: 65px;
                border-radius: 65px;
        width: 130px;
        height: 50px;
        font-size: 16px;
    }
    .products-grid .regular-price,
    .products-grid .price-box > .price,
    .product-essential .product-img-box .regular-price,
    .product-essential .product-img-box .price-box > .price {
        margin-top: 15px;
    }
    .products-grid .old-price,
    .product-essential .product-img-box .old-price {
        margin-top: 5px;
        margin-bottom: 0;
        font-size: 13px;
    }
    .products-grid .price-from,
    .product-essential .product-img-box .price-from {
        padding-bottom: 6px;
        margin-bottom: 0;
        background: transparent url(/_images/icons/icon_from_to.png) bottom center no-repeat;
    }
    .products-grid .price-from,
    .product-essential .product-img-box .price-from {
        margin-top: 20px;
    }
    .products-grid .minimal-price,
    .product-essential .product-img-box .minimal-price {
        margin-top: 27px;
    }
    .products-grid .minimal-price .price-label,
    .product-essential .product-img-box .minimal-price .price-label {
        font-size: 14px;
        margin-bottom: 0;
    }
    .products-grid .price-review {
        text-align: center;
    }
    
    .products-list .price-box {
        -webkit-border-radius: 36px;
           -moz-border-radius: 36px;
                border-radius: 36px;
        width: 72px;
        height: 72px;
        font-size: 16px;
    }
    .products-list .regular-price,
    .products-list .price-box > .price {
        margin-top: 26px;
    }
    .products-list .old-price {
        margin-top: 14px;
        font-size: 14px;
    }
    .products-list .price-from {
        margin-top: 15px;
    }
    .products-list .price-from {
        padding-bottom: 3px;
        margin-bottom: -2px;
    }
    .products-list .minimal-price {
        margin-top: 24px;
    }
    .products-list .minimal-price .price-label {
        font-size: 12px;
        margin-bottom: -3px;
    }
    
    .product-essential .product-img-box .price {
        line-height: 20px;
    }
    .product-essential .product-img-box .price-from, 
    .product-essential .product-img-box .price-to {
        font-size: 18px;
    }
    
    .products-list .labels {
        width: 40px;
        font-size: 13px;
    }
    .products-list .new,
    .products-list .sales {
        padding: 5px 0;
    }
    .products-list .new.circle,
    .products-list .sales.circle {
        width: 40px;
        height: 40px;
        -webkit-border-radius: 20px;
           -moz-border-radius: 20px;
                border-radius: 20px;
        padding: 14px 0;
    }
    .products-list .product-shop .desc li {
        list-style-position: inside;
    }
    .product-essential .product-img-box .labels {
        width: 60px;
        font-size: 16px;
    }
    .product-essential .product-img-box .new.circle,
    .product-essential .product-img-box .sales.circle {
        width: 60px;
        height: 60px;
        -webkit-border-radius: 30px;
           -moz-border-radius: 30px;
                border-radius: 30px;
        padding: 21px 0;
        margin-top: -15px;
        margin-left: -5px;
    }
    .product-essential .product-img-box .top-right .new.circle,
    .product-essential .product-img-box .top-right .sales.circle {
        margin-left: 5px;
    }
    .product-essential .product-img-box .bottom-left .new.circle,
    .product-essential .product-img-box .bottom-left .sales.circle {
        margin-bottom: -15px;
    }
    .product-essential .product-img-box .bottom-right .new.circle,
    .product-essential .product-img-box .bottom-right .sales.circle {
        margin-bottom: -15px;
        margin-left: 5px;
    }
    .product-essential .product-img-box .new,
    .product-essential .product-img-box .sales {
        padding: 8px 0;
    }
    div.Growler-notice {
        width: 300px;
    }
    
    .slider-wrap .price-box {
        -webkit-border-radius: 29px;
           -moz-border-radius: 29px;
                border-radius: 29px;
        width: 58px;
        height: 58px;
        font-size: 15px;
    }
    .slider-wrap .price-box .price {
        margin-top: 21px;
    }
    
    /* Data Table */
    .data-table {
        border-right-width: 1px;
    }
    .data-table th,
    .data-table td { 
        padding: 10px 5px;
    }
    .cart-table,
    .order-review .data-table,
    .my-account .data-table,
    .my-wishlist .data-table {
        border-right-width: 1px !important;
    }
    .cart-table col,
    .order-review .data-table col,
    .my-account .data-table col,
    .my-wishlist .data-table col {
        display: none;
    }
    .cart-table thead tr,
    .order-review .data-table thead tr,
    .my-account .data-table thead tr,
    .my-wishlist .data-table thead tr {
        display: none;
    }
    .cart-table thead tr.mobile-row,
    .order-review .data-table thead tr.mobile-row,
    .my-account .data-table thead tr.mobile-row,
    .my-wishlist .data-table thead tr.mobile-row {
        display: block;
    }
    .cart-table tr, 
    .cart-table td,
    .cart-table th,
    .order-review .data-table tr,
    .order-review .data-table td,
    .order-review .data-table th,
    .my-account .data-table tr,
    .my-account .data-table td,
    .my-account .data-table th,
    .my-wishlist .data-table tr,
    .my-wishlist .data-table td,
    .my-wishlist .data-table th {
        border-left: none !important;
        border-right: none !important;
        display: block;
        padding: 0 !important;
        text-align: center !important;
    }
    .cart-table td,
    .cart-table th,
    .order-review .data-table td,
    .order-review .data-table th,
    .my-account .data-table td,
    .my-account .data-table th,
    .my-wishlist .data-table td,
    .my-wishlist .data-table th {
        padding: 10px 5px !important;
    }
    .cart-table tbody td:first-child,
    .cart-table tbody th:first-child,
    .order-review .data-table tbody td:first-child,
    .order-review .data-table tbody th:first-child,
    .my-account .data-table tbody td:first-child,
    .my-account .data-table tbody th:first-child,
    .my-wishlist .data-table tbody td:first-child,
    .my-wishlist .data-table tbody th:first-child {
        padding-top: 20px !important;
    }
    .cart-table tbody td:last-child,
    .cart-table tbody th:last-child,
    .order-review .data-table tbody td:last-child,
    .order-review .data-table tbody th:last-child,
    .my-account .data-table tbody td:last-child,
    .my-account .data-table tbody th:last-child,
    .my-wishlist .data-table tbody td:last-child,
    .my-wishlist .data-table tbody th:last-child {
        padding-bottom: 20px !important;
    }
    .cart-table thead tr.mobile-row th,
    .order-review .data-table thead tr.mobile-row th,
    .my-account .data-table thead tr.mobile-row th,
    .my-wishlist .data-table thead tr.mobile-row th {
        padding: 20px 10px !important;
    }
    .cart-table tbody td,
    .cart-table tbody tr.last td,
    .order-review .data-table tbody td,
    .order-review .data-table tbody tr.last td,
    .my-account .data-table tbody td,
    .my-account .data-table tbody tr.last td,
    .my-wishlist .data-table tbody td,
    .my-wishlist .data-table tbody tr.last td {
        border-bottom-width: 0;
    }
    .cart-table tbody td.last,
    .cart-table tbody tr.last td.last,
    .order-review .data-table tbody td.last,
    .order-review .data-table tbody tr.last td.last,
    .my-account .data-table tbody td.last,
    .my-account .data-table tbody tr.last td.last {
        border-bottom-width: 1px;
    }
    .data-table .product-image {
        float: none;
        width: 150px;
        margin: 15px auto;
    }
    .data-table .product-shop {
        float: none;
        padding-left: 0;
        text-align: center;
    }
    .data-table .actions {
        margin-top: 0;
    }
    .data-table .item-options {
        float: none;
    }
    .data-table .item-options dt,
    .data-table .item-options dd {
        float: none;
        display: inline;
    }
    .data-table .cart-price {
        margin-top: 0;
        display: inline;
    }
    .data-table .total-price .cart-price {
        padding-right: 0;
    }
    .data-table .btn-remove {
        position: static;
        display: block;
        margin: 10px auto 0;
    }
    .data-table .button {
        display: inline-block;
        margin: 5px auto;
    }
    .add-to-cart-alt .btn-cart, 
    .data-table .add-to-cart-alt .button {
        margin: 0 auto;
    }
    .data-table .mobile-label {
        display: inline;
    }
    .data-table .btn-continue {
        float: none;
    }
    .compare-table {
        display: none;
    }
    .compare-table-mobile {
        display: block;
    }
    .compare-table-mobile td {
        padding: 10px;
    }
    .compare-table-mobile .product-image {
        width: 124px;
    }
    .compare-table-mobile .product-image img {
        width: 100%;
        height: auto;
    }
    .compare-table .btn-remove {
        margin-top: 20px;
    }
    .my-account .data-table .product-name,
    .my-wishlist .data-table .product-name {
        margin: 0;
        line-height: 1;
    }
    .my-account .data-table .rating-box,
    .my-wishlist .data-table .rating-box {
        margin: 0 auto;
    }
    .my-account .data-table .price, 
    .my-wishlist .data-table .price {
        display: inline;
    }
    .my-account .data-table .price-excl-tax,
    .my-account .data-table .price-incl-tax,
    .my-account .data-table .price-box,
    .my-wishlist .data-table .price-excl-tax,
    .my-wishlist .data-table .price-incl-tax,
    .my-wishlist .data-table .price-box {
        display: inline;
    }
    .my-account .data-table .add-to-cart-alt,
    .my-wishlist .data-table .add-to-cart-alt {
        margin-top: 10px;
    }
    .my-account .order-info-box {
        margin: 0;
    }
    .my-account .order-items {
        padding: 0 15px;
    }
    
    /* form list */
    .form-list li label {
        width: 115px;
        padding: 0 8px;
    }
    .form-list input.input-text, 
    .form-list select {
        padding-left: 125px;
        padding-right: 10px;
    }
    .form-list .bootstrap-select.btn-group .btn .filter-option {
        left: 125px;
    }
    .form-list .input-range .start input.input-text {
        width: 52%;
    }
    .form-list .input-range .end input.input-text {
        width: 32%;
        padding-left: 10px;
    }
    .form-list .customer-dob .dob-day {
        width: 40.5% !important;
    }
    .form-list .customer-dob .dob-month {
        width: 20.5% !important;
    }
    .form-list .customer-dob .dob-year {
        width: 33% !important;
    }
    .sp-methods select.month,
    .span6 .sp-methods select.month,
    .col-sm-6 .sp-methods select.month { 
        width: 300px; 
    }
    .sp-methods input.cvv { 
        width: 300px !important; 
    }
    
    /* contact us */
    #contact-gmap {
        height: 200px;
    }
    
    /* home slider */
    .bv3 .main-slider .container {
        padding: 0;
    }
    .homeslider-products .product-name {
        font-size: 25px;
        margin-bottom: 10px;
    }
    .homeslider-products .price-box .price {
        font-size: 20px;
    }
    .homeslider-products .price-box {
        margin-bottom: 5px;
    }
    .homeslider-products .product-desc {
        display: none;
    }
    .homeslider-products .btn-cart,
    .homeslider-products .add-to-cart .btn-cart {
        margin-top: 10px;
    }
    
    /* twitter tweets */
    .footer-tweets #twitter-slider {
        padding: 0 60px 0 60px;
    }
    .footer-tweets .twitter-slider1 {
        padding: 0 60px;
    }
    #twitter-slider.twitter-slider2 .flex-direction-nav a {
        top: auto;
        bottom: 25px;
    }
}

@media (max-width: 480px) {
    /* header */
    .header {
        padding-top: 75px;
    }
    .bv3 .header-top .container {
        padding-left: 0;
        padding-right: 0;
    }
    .bv3 .login-link {
        right: 0;
    }
    .bv3 #mini-cart {
        margin-right: -5px;
    }
    .header-top #mini-cart {
        margin-right: 5px;
        margin-top: -36px;
    }
    .header-top-both {
        position: absolute;
        top: 0;
        right: 0;
    }
    .header-top-both #mini-cart,
    .header-both #mini-cart {
        display: none !important;
    }
    .header-both1 #mini-cart {
        display: block !important;
        margin-right: 0px;
    }
    .header-both2 {
        right: 10px;
        left: auto;
    }
    .quick-access2 #mini-cart {
        display: none !important;
    } 
    
    /* product */
    .add-to-cart .qty {
        width: 125px;
    }
    .add-to-cart .button-up, 
    .add-to-cart .button-down {
        left: 99px;
    }
    
    /* data table */
    .data-table select {
        width: 84px;
    }
    .data-table .input-text {
        width: 70px;
    }
    .box-reviews textarea {
        width: 92%;
    }
    
    /* checkout */
    #checkout-review-table .product-image {
        width: 100px;
    }
    #checkout-review-table .product-image img {
        width: 100%;
        height: auto;
    }
    .opc .step-title {
        padding-left: 10px;
        padding-right: 10px;
    }
    .opc .step-title,
    .opc .step-title h2 {
        font-size: 17px;
    }
    
    /* form list */
    .form-list .input-range .start input.input-text {
        width: 60%;
    }
    .form-list .input-range .end input.input-text {
        width: 20%;
    }
    .form-list .customer-dob .dob-day input.input-text {
        padding-left: 125px;
        padding-right: 10px;
    }
    .form-list .customer-dob .dob-month input.input-text,
    .form-list .customer-dob .dob-year input.input-text {
        padding-left: 10px;
        padding-right: 10px;
    }
    .form-list .customer-dob .dob-day {
        width: 55% !important;
        margin-right: 0;
    }
    .form-list .customer-dob .dob-month {
        width: 23% !important;
        margin-right: 0;
    }
    .form-list .customer-dob .dob-year {
        width: 22% !important;
    }
    .sp-methods select.month,
    .span6 .sp-methods select.month,
    .col-sm-6 .sp-methods select.month { 
        width: 200px;
    }
    .sp-methods select.year,
    .span6 .sp-methods select.year,
    .col-sm-6 .sp-methods select.year { 
        width: 80px;
    }
    .sp-methods input.cvv { 
        display: none;
    }
    
    /* home slider */
    .homeslider-products .product-name {
        font-size: 18px;
        margin-bottom: 5px;
    }
    .homeslider-products .price-box .price {
        font-size: 15px;
    }
    .homeslider-products .btn-cart,
    .homeslider-products .add-to-cart .btn-cart {
        margin-top: 0;
        font-size: 10px;
        padding: 4px 8px;
    }
}

@media (max-width: 320px) {
    /* header */
}

.store-switcher {
    display: none;
    position: fixed;
    right: 0;
    top: 200px;
    z-index: 100000;
}

/* Custom CSS */
.header-contact {
    margin-top: 0;
}
.header-contact .block {
    float: left;
    margin-left: 8px;
    margin-bottom: 10px;
    padding: 3px 15px;
    font-size: 13px;
}
.header-contact .block span {
    display: inline-block;
    line-height: 21px;
}
.header-contact .icon-phone {
    width: 20px;
    height: 20px;
    margin-right: 7px;
    background-image: url(/_images/icons/icon_20x20.png);
    margin: -1px 10px 0 0;
}
.header-contact .icon-phone,
.green .header-contact .icon-phone  { background-position: -140px -20px; }
.blue .header-contact .icon-phone   { background-position: 0 -64px; }
.orange .header-contact .icon-phone { background-position: 0 -96px; }
.pink .header-contact .icon-phone   { background-position: 0 -128px; }
.header-contact .icon-skype,
.header-contact .icon-email {
    width: 20px;
    height: 20px;
    margin: -1px 10px 0 0;
    background-image: url(/_images/icons/icon_20x20.png);
}
.header-contact .icon-skype,
.green .header-contact .icon-skype  { background-position: -20px -20px; }
.blue .header-contact .icon-skype   { background-position: -20px -40px; }
.orange .header-contact .icon-skype { background-position: -20px -60px; }
.pink .header-contact .icon-skype   { background-position: -20px -80px; }
.header-contact .icon-email,
.green .header-contactc .icon-email { background-position: 0 -20px; }
.blue .header-contact .icon-email   { background-position: 0 -40px; }
.orange .header-contact .icon-email { background-position: 0 -60px; }
.pink .header-contact .icon-email   { background-position: 0 -80px; }

#homeslider-revolution .revolution-slider {
    max-height: 480px;
}

.footer .social-links a {
    display: inline-block;
    margin: 2px;
    transition: opacity 300ms ease-in-out;
       -moz-transition: opacity 300ms ease-in-out;
    -webkit-transition: opacity 300ms ease-in-out;
         -o-transition: opacity 300ms ease-in-out;
}
.footer .social-links .icon {
    background-image: url(/_images/icons/icon_socials.png);
    width: 36px;
    height: 36px;
    padding: 0;
}
.footer .social-links .icon-facebook {
    background-position: 0 0;
}
.footer .social-links a:hover .icon-facebook {
    background-color: #3b5a9a;
}
.footer .social-links .icon-twitter {
    background-position: -36px 0;
}
.footer .social-links a:hover .icon-twitter {
    background-color: #1aa9e1;
}
.footer .social-links .icon-rss {
    background-position: -72px 0;
}
.footer .social-links a:hover .icon-rss {
    background-color: #ff8201;
}
.footer .social-links .icon-delicious {
    background-position: -108px 0;
}
.footer .social-links a:hover .icon-delicious {
    background-color: #3070c8;
}
.footer .social-links .icon-linkedin {
    background-position: -144px 0;
}
.footer .social-links a:hover .icon-linkedin {
    background-color: #0080b1;
}
.footer .social-links .icon-blog {
    background-position: -180px 0;
}
.footer .social-links a:hover .icon-blog {
    background-color: #ee2283;
}
.footer .social-links .icon-skype {
    background-position: -216px 0;
}
.footer .social-links a:hover .icon-skype {
    background-color: #00aff0;
}
.footer .social-links .icon-email {
    background-position: -252px 0;
}
.footer .social-links a:hover .icon-email {
    background-color: #c7392c;
}

/* menu custom block */
#popupmenu_custom_block .block2 .cell {
    padding: 15px 10px;
}
#popupmenu_custom_block .block2 .cell:hover {
    border: 1px solid #e0e0e0;
    padding: 14px 9px;
    -webkit-border-radius: 3px;
       -moz-border-radius: 3px;
            border-radius: 3px;
}

@media (min-width: 1200px) {
    
}

@media (min-width: 768px) and (max-width: 991px) {
    .header-contact {
        display: none;
    }
}

@media (max-width: 767px) {
    .header-contact {
        display: none;
    }
}

@media (max-width: 480px) {
    
}

@media (max-width: 320px) {
    
}


.price-slider {
    
}
.price-slider .slider-wrap {
    margin: 15px 0;
}
.price-slider .text-box {
    margin-top: 30px;
    margin-bottom: 10px;
}
.price-slider label {
    margin-right: 8px;
    font-size: 14px;
    display: inline;
    line-height: 25px;
}
.price-slider .priceTextBox {
    margin-bottom: 0;
    font-size: 14px;
    padding: 2px;
}
.price-slider #minPrice {
    margin-right: 8px;
}
.price-slider .actions {
    padding: 10px 0 0;
}
.price-slider .actions .go {
    margin-right: 8px;
}
.price-slider .jslider .jslider-value {
    font-size: 12px;
}
.price-slider .jslider .jslider-label {
    font-size: 10px;
}
.price-slider .jslider .jslider-pointer {
    cursor: pointer;
    width: 10px;
    height: 22px;
    -webkit-box-shadow: 0 1px 1px rgba(0,0,0,0.15);
       -moz-box-shadow: 0 1px 1px rgba(0,0,0,0.15);
            box-shadow: 0 1px 1px rgba(0,0,0,0.15);
}
.price-slider .jslider .jslider-bg i {
    margin-top: 2px;
    background-color: #e0dbdb;
    background-image: none;
    height: 10px;
}
.jslider .jslider-bg i,
.jslider .jslider-pointer {
    background:url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/jquery/images/jslider.png) no-repeat 0 0;
}

.jslider {
    display:block;
    width:100%;
    height:1em;
    position:relative;
    top:.6em;
    font-family: Arial,sans-serif;
}

.jslider table {
    width:100%;
    border-collapse:collapse;
    border:0;
}

.jslider td,.jslider th {
    vertical-align:top;
    text-align:left;
    border:0;
    padding:0;
}

.jslider table,.jslider table tr,
.jslider table tr td {
    width:100%;
    vertical-align:top;
}

.jslider .jslider-bg {
    position:relative;
}

.jslider .jslider-bg i {
    height:5px;
    position:absolute;
    font-size:0;
    top:0;
}

.jslider .jslider-bg .l {
    width:10%;
    background-position:0 0;
    left:0;
}

.jslider .jslider-bg .f {
    width:82%;
    left:9%;
    background-repeat:repeat-x;
    background-position:0 -20px;
}

.jslider .jslider-bg .r {
    width:10%;
    left:90%;
    background-position:right 0;
}

.jslider .jslider-bg .v {
    position:absolute;
    width:60%;
    left:20%;
    top:0;
    height:5px;
    background-repeat:repeat-x;
    background-position:0 -40px;
}

.jslider .jslider-pointer {
    width:13px;
    height:15px;
    background-position:0 -60px;
    position:absolute;
    left:20%;
    top:-4px;
    margin-left:-6px;
    cursor:hand;
}

.jslider .jslider-pointer-hover {
    background-position:-20px -60px;
}

.jslider .jslider-label {
    font-size:9px;
    line-height:12px;
    color:#000;
    opacity:.4;
    white-space:nowrap;
    position:absolute;
    top:-18px;
    left:0;
    padding:0 2px;
}

.jslider .jslider-label-to {
    left:auto;
    right:0;
}

.jslider .jslider-value {
    font-size:9px;
    white-space:nowrap;
    position:absolute;
    top:-19px;
    left:20%;
    background:#FFF;
    line-height:12px;
    -moz-border-radius:2px;
    -webkit-border-radius:2px;
    -o-border-radius:2px;
    border-radius:2px;
    padding:1px 2px 0;
}

.jslider .jslider-label small,
.jslider .jslider-value small {
    position:relative;
    top:-.4em;
}

.jslider .jslider-scale {
    position:relative;
    top:9px;
}

.jslider .jslider-scale span {
    position:absolute;
    height:5px;
    border-left:1px solid #999;
    font-size:0;
}

.jslider .jslider-scale ins {
    font-size:9px;
    text-decoration:none;
    position:absolute;
    left:0;
    top:5px;
    color:#999;
}

.jslider-single .jslider-pointer-to,
.jslider-single .jslider-value-to,
.jslider-single .jslider-bg .v,
.jslider-limitless .jslider-label {
    display:none;
}

.jslider_blue .jslider-bg i,
.jslider_blue .jslider-pointer {
    background-image:url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/jquery/images/jslider.blue.png);
}

.jslider_plastic .jslider-bg i,
.jslider_plastic .jslider-pointer {
    background-image:url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/jquery/images/jslider.plastic.png);
}

.jslider_round .jslider-bg i,
.jslider_round .jslider-pointer {
    background-image:url(http://thesmartwave.net/venedor/skin/frontend/venedor/default/css/jquery/images/jslider.round.png);
}

.jslider_round .jslider-pointer {
    width:17px;
    height:17px;
    top:-6px;
    margin-left:-8px;
}

.jslider_round_plastic .jslider-bg i,
.jslider_round_plastic .jslider-pointer {
    background-image:url(/_images/icons/jslider.round.plastic.png);
}

.jslider_round_plastic .jslider-pointer {
    width:18px;
    height:18px;
    top:-7px;
    margin-left:-8px;
}

.jslider .jslider-pointer-to,
.jslider .jslider-value-to {
    left:80%;
}


.dropdown-submenu:hover>.dropdown-menu{display:none}
#form_shipping_list ul {margin:0; padding:10px;}
#form_shipping_list li {clear:both; list-style:none;}
#form_billing_list ul {margin:0; padding:10px;}
#form_billing_list li {clear:both; list-style:none;}
#display_form_shipping, #display_form_billing {
	border-bottom: dotted 3px #CCC;
	display: none;
	margin-bottom: 10px;
	padding-bottom: 10px;
}
.fl {float:left;}
.pager .pages li {
margin-left: 5px;
}
.pager .pages li .disabled{
display:none;
}
.products-grid .item-first {clear:left;}
.filters .title {
color: #565656;
border: 1px solid #dcdcdc;
background-color: #f4f4f4;
padding: 10px;
text-transform: uppercase;
font-weight:bold;
}
.filters .list {padding: 10px;border: 1px solid #dcdcdc;border-top:none;overflow: hidden}
.filters .list .filtering_item {float:left;border: 1px solid #dcdcdc;
background-color: #f4f4f4;
padding: 5px;margin-right: 10px;}
.filters .list .filtering_text {float:left;}
.filters .list .fitering_close_btn {float:left;margin: 2px 0 0 5px;}
.hideprint {
    display:none;
}
#fancybox-wrap {
	z-index:100000 !important;
}


/* Elastislide Style */



.elastislide-list {

	list-style: none;

	display: none;

    margin: 0;

    padding: 0;

}



.no-js .elastislide-list {

	display: block;

}



.elastislide-carousel ul li {

	min-width: 20px; /* minimum width of the image (min width + border) */

}



.elastislide-wrapper {

	position: relative;

	margin: 0 auto;

	min-height: 60px;

}



.elastislide-wrapper.elastislide-loading {

	background-image: url(/_images/icons/loading-bis.gif);

	background-repeat: no-repeat;

	background-position: center center;

}



.elastislide-horizontal {

}



.elastislide-vertical {

    margin-top: -6px;

}



.elastislide-carousel {

	overflow: hidden;

	position: relative;

}



.elastislide-carousel ul {
	position: relative;
	display: block;
	list-style-type: none;
	padding: 0;
	margin: 0;
	-webkit-backface-visibility: hidden;
	-webkit-transform: translateX(0px);
	-moz-transform: translateX(0px);
	-ms-transform: translateX(0px);
	-o-transform: translateX(0px);
	transform: translateX(0px);
}



.elastislide-horizontal ul {
	white-space: nowrap;
}



.elastislide-carousel ul li {
	margin: 0;
	-webkit-backface-visibility: hidden;
}



.elastislide-horizontal ul li {
	height: 100%;
	display: inline-block;
}

.elastislide-vertical ul li {
	display: block;
}

.elastislide-carousel ul li a {
	display: inline-block;
	width: 100%;
}

.elastislide-carousel ul li a img {
	display: block;
	max-width: 100%;
    padding: 6px 0;
}

/* Navigation Arrows */
.elastislide-wrapper nav span {
	position: absolute;
	text-indent: -9000px;
	cursor: pointer;
}

.elastislide-horizontal nav span {
	top: 50%;
	left: 10px;
	margin-top: -16px;
}

.elastislide-vertical nav span {
	top: -6px;
	left: 50%;
	margin-left: -16px;
	background-position: -17px 5px;
}

.elastislide-horizontal nav span.elastislide-next {
	right: 10px;
	left: auto;
	background-position: 4px -17px;
}

.elastislide-vertical nav span.elastislide-next {
	bottom: -6px;
	top: auto;
	background-position: -17px -18px;
}

.elastislide-horizontal nav span.elastislide-next {
	right: 10px;
	left: auto;
	background-position: 4px -17px;
}

.elastislide-vertical nav span.elastislide-next {
	bottom: -6px;
	top: auto;
	background-position: -17px -18px;
}

/* This is the moving lens square underneath the mouse pointer. */

.cloud-zoom-lens {

	border: 4px solid #888;

	margin:-4px;	/* Set this to minus the border thickness. */

	background-color:#fff;	

	cursor:move;		

}



/* This is for the title text. */

.cloud-zoom-title {

	font-family:Arial, Helvetica, sans-serif;

	position:absolute !important;

	background-color:#000;

	color:#fff;

	padding:3px;

	width:100%;

	text-align:center;	

	font-weight:bold;

	font-size:10px;

	top:0px;

}



/* This is the zoom window. */

.cloud-zoom-big {

	border:4px solid #ccc;

    margin: -4px;

	overflow:hidden;

}



/* This is the loading message. */

.cloud-zoom-loading {

	color:white;	

	background:#222;

	padding:3px;

	border:1px solid #000;

}






/* line 7, ../sass/lightbox.sass */
body:after {
  content: url(/_images/icons/close-bis.png) url(/_images/icons/loading-bis.gif) url(/_images/icons/prev.png) url(/_images/icons/next.png);
  display: none;
}

/* line 11, ../sass/lightbox.sass */
.lightboxOverlay {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 10001;
  background-color: black;
  filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=80);
  opacity: 0.8;
  display: none;
}

/* line 20, ../sass/lightbox.sass */
.lightbox {
  position: absolute;
  left: 0;
  width: 100%;
  z-index: 10002;
  text-align: center;
  line-height: 0;
  font-weight: normal;
}
/* line 28, ../sass/lightbox.sass */
.lightbox .lb-image {
  display: block;
  height: auto;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  -ms-border-radius: 3px;
  -o-border-radius: 3px;
  border-radius: 3px;
}
/* line 32, ../sass/lightbox.sass */
.lightbox a img {
  border: none;
}

/* line 35, ../sass/lightbox.sass */
.lb-outerContainer {
  position: relative;
  background-color: white;
  *zoom: 1;
  width: 250px;
  height: 250px;
  margin: 0 auto;
  -webkit-border-radius: 4px;
  -moz-border-radius: 4px;
  -ms-border-radius: 4px;
  -o-border-radius: 4px;
  border-radius: 4px;
}
/* line 38, ../../../../.rvm/gems/ruby-1.9.3-p392/gems/compass-0.12.2/frameworks/compass/stylesheets/compass/utilities/general/_clearfix.scss */
.lb-outerContainer:after {
  content: "";
  display: table;
  clear: both;
}

/* line 44, ../sass/lightbox.sass */
.lb-container {
  padding: 4px;
}

/* line 47, ../sass/lightbox.sass */
.lb-loader {
  position: absolute;
  top: 43%;
  left: 0%;
  height: 25%;
  width: 100%;
  text-align: center;
  line-height: 0;
}

/* line 56, ../sass/lightbox.sass */
.lb-cancel {
  display: block;
  width: 32px;
  height: 32px;
  margin: 0 auto;
  background: url(/_images/icons/loading-bis.gif) no-repeat;
}

/* line 63, ../sass/lightbox.sass */
.lb-nav {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  width: 100%;
  z-index: 10;
}

/* line 71, ../sass/lightbox.sass */
.lb-container > .nav {
  left: 0;
}

/* line 74, ../sass/lightbox.sass */
.lb-nav a {
  outline: none;
}

/* line 77, ../sass/lightbox.sass */
.lb-prev, .lb-next {
  width: 49%;
  height: 100%;
  cursor: pointer;
  /* Trick IE into showing hover */
  display: block;
}

/* line 84, ../sass/lightbox.sass */
.lb-prev {
  left: 0;
  float: left;
}
/* line 87, ../sass/lightbox.sass */
.lb-prev:hover {
  background: url(/_images/icons/prev.png) left 48% no-repeat;
}

/* line 90, ../sass/lightbox.sass */
.lb-next {
  right: 0;
  float: right;
}
/* line 93, ../sass/lightbox.sass */
.lb-next:hover {
  background: url(/_images/icons/next.png) right 48% no-repeat;
}

/* line 96, ../sass/lightbox.sass */
.lb-dataContainer {
  margin: 0 auto;
  padding-top: 5px;
  *zoom: 1;
  width: 100%;
  -moz-border-radius-bottomleft: 4px;
  -webkit-border-bottom-left-radius: 4px;
  border-bottom-left-radius: 4px;
  -moz-border-radius-bottomright: 4px;
  -webkit-border-bottom-right-radius: 4px;
  border-bottom-right-radius: 4px;
}
/* line 38, ../../../../.rvm/gems/ruby-1.9.3-p392/gems/compass-0.12.2/frameworks/compass/stylesheets/compass/utilities/general/_clearfix.scss */
.lb-dataContainer:after {
  content: "";
  display: table;
  clear: both;
}

/* line 103, ../sass/lightbox.sass */
.lb-data {
  padding: 0 4px;
  color: #bbbbbb;
}
/* line 106, ../sass/lightbox.sass */
.lb-data .lb-details {
  width: 85%;
  float: left;
  text-align: left;
  line-height: 1.1em;
}
/* line 111, ../sass/lightbox.sass */
.lb-data .lb-caption {
  font-size: 13px;
  font-weight: bold;
  line-height: 1em;
}
/* line 115, ../sass/lightbox.sass */
.lb-data .lb-number {
  display: block;
  clear: left;
  padding-bottom: 1em;
  font-size: 12px;
  color: #999999;
}
/* line 121, ../sass/lightbox.sass */
.lb-data .lb-close {
  display: block;
  float: right;
  width: 30px;
  height: 30px;
  background: url(/_images/icons/close-bis.png) top right no-repeat;
  text-align: right;
  outline: none;
  filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=70);
  opacity: 0.7;
}
/* line 130, ../sass/lightbox.sass */
.lb-data .lb-close:hover {
  cursor: pointer;
  filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=100);
  opacity: 1;
}
div.additional_items_parent {
    margin-bottom: 5px;
    padding: 5px;
    background-color: #FFF;
    border: 1px solid #ccc;
    overflow: hidden;
}
.fr {float:right}
div.button_add_update_item_cart {float:right; margin-top:7px;}
.block-language .code {text-transform:uppercase;}
@media (min-width: 768px) {
	.block-language .code {display:none;}
}
#tier_prices {
margin-bottom: 10px;
}

@media (max-width: 1200px) {
	#menu-other-links {display:none;}
}

ul.menu-links {list-style-type:none;float:left;margin: 7px 5px 0 0;padding: 0;}
ul.menu-links li {margin-left:15px;}
ul.menu-links li a{font-weight:bold; font-size:13px;}

.jqueryslidemenu{
font-size:13px;
/*background: rgba(<?php echo $rgbColorSite;?>, 0.7);*/
width: 100%;
font-weight: bold;
}

.jqueryslidemenu ul{
margin: 0;
padding: 0;
list-style-type: none;
}

/*Top level list items*/
.jqueryslidemenu ul li{
position: relative;
display: inline;
float: left;
}

/*Top level menu link items style*/
.jqueryslidemenu ul li a{
display: block;
background: white; /*background of tabs (default state)*/
color: white;
padding: 8px 10px;
/*border-right: 1px solid #F5F5F5;*/
color: #2d2b2b;
text-decoration: none;
}

* html .jqueryslidemenu ul li a{ /*IE6 hack to get sub menu links to behave correctly*/
display: inline-block;
}

.jqueryslidemenu ul li a:link, .jqueryslidemenu ul li a:visited{
color: #494940;
}

.jqueryslidemenu ul li a:hover{
background: #ddd; /*tab link background during hover state*/
/*color: #737373;*/
}
	
/*1st sub level menu*/
.jqueryslidemenu ul li ul{
position: absolute;
left: 0;
display: block;
visibility: hidden;
z-index:1000000;
overflow: initial !important;
}

/*Sub level menu list items (undo style from Top level List Items)*/
.jqueryslidemenu ul li ul li{
display: list-item;
float: none;

}

/*All subsequent sub menu levels vertical offset after 1st level sub menu */
.jqueryslidemenu ul li ul li ul{
top: 0;

}

/* Sub level menu links style */
.jqueryslidemenu ul li ul li a{
font: normal 13px Verdana;
width: 160px; /*width of sub menus*/
padding: 10px;
margin: 0;
border-top-width: 0;
background: #f4f4f4;
/*border-bottom: 1px solid gray;*/
}

.jqueryslidemenuz ul li ul li a:hover{ /*sub menus hover style*/
background: gray;
/*color: black;*/
}

/* ######### CSS classes applied to down and right arrow images  ######### */

.downarrowclass{
position: absolute;
top: 13px;
right: 6px;
}

.rightarrowclass{
position: absolute;
top: 6px;
right: 5px;
}
.block-layered-nav .ratings {margin:0;}
@media (min-width: 1200px) {
    .header-menu{background:rgba(<?php echo $rgbColorSite;?>, 1); padding:0 !important;}
    .breadcrumbs {color:white; background:rgba(<?php echo $rgbColorSite;?>, 1);}
    .breadcrumbs a{color:white;}
    .breadcrumbs a:hover{color:white;}
    #custommenu .parentMenu a {background:rgba(<?php echo $rgbColorSite;?>, 1); color:white;}
    #custommenu .parentMenu.featured a {background:white; color:rgba(<?php echo $rgbColorSite;?>, 1);}
    .header-menu .quick-access {display:none;}
    #custommenu .eternal-custom-menu-popup {top:50px !important;opacity: 1 !important;}
}
.header-menu .itemMenuName.featured {color:rgba(<?php echo $rgbColorSite;?>, 1);}

@media (max-width: 1200px) {
	.header-right .quick-access {display:none;}
}
#menu-other-links .menu-links {background-color:rgba(<?php echo $rgbColorSite;?>, 0.8);height: 28px; padding-top: 5px;}
#menu-other-links .menu-links a{color:white;}
.fitering_close_btn a {color: white;background-color: rgba(<?php echo $rgbColorSite;?>, 1);padding: 0px 4px;font-size: 12px;font-weight: bold;}
.parentMenu.featured {padding: 1px;}
.withblock .op_block_title {margin-bottom:0;}
.withblock .op_block_detail {overflow: hidden;border: 1px solid #d4d4d4;border-top: none;margin-bottom: 10px; padding:10px;}
.withblock .op_block_title {padding: 5px 20px;}
.withblock .op_block_detail .button {padding: 2px 8px 2px;margin-right: 14px; font-size: 12px;}
.withblock .op_block_title .button {padding: 2px 8px 2px; font-size: 12px; margin-top: 3px;}
.withblock .op_block_detail form {margin: 10px;}
#form_shipping_list .button {margin-right: 10px;}
form.payment_methods ul {padding:0;list-style-type: none;}
form.payment_methods ul li {margin-bottom: 10px;}
form.payment_methods ul li label{width: 200px;}
.withblock .op_block_detail form.payment_methods .button {padding:7px 12px 6px;}
.op_block_detail ul {padding:0;list-style-type: none;}
.op_block_detail ul li {margin-bottom: 10px;}

.op_block_detail input[type="radio"] {margin:0; padding:0;}

#add_price_alert .button {background-image: url(/_images/icons/price_alert.png);}
#add_price_alert .button:hover {color: #ffffff;
    background-color: rgba(<?php echo $rgbColorSite;?>, 1);
    border-color: rgba(<?php echo $rgbColorSite;?>, 1);
}
#menu-other-links .menu-links a:hover {opacity:0.6; filter:alpha(opacity=60); /* For IE8 and earlier */}
@media (min-width: 1200px){
#custommenu .parentMenu a:hover {
	opacity:0.6; filter:alpha(opacity=60); /* For IE8 and earlier */;
}
#custommenu .parentMenu.featured a:hover {
color: rgba(<?php echo $rgbColorSite;?>, 0.7);
}
.header-menu .itemMenuName.featured a:hover{
opacity:0.7;
}
}
.tier_price_box {background: rgba(70, 136, 71, 0.8);
color: white;
border: 1px solid green;
padding: 3px 10px;
width: 75%;
}

.products-grid .add-to-links {
height:38px;
}
#cloud-zoom-big {display:none !important;}
.mousetrap {height:auto !important;}
img.ui-datepicker-trigger{ margin-left: 5px !important;}

div#coupon{
	border: dashed 2px <?php echo $config_site["css_main_color"];?>;
    width: 330px;
    padding: 15px;
    margin: 0 auto;
    text-align: center; 
}


.title_bg_text_box_error{
	background-color:#F2DEDE !important;
}
.cb {clear: both;}

input.error, select.error, textarea.error {
	border: 1px solid #B84947;
	background-color: #F2DEDE;	
}

.icon-padlock {
    width: 20px;
    height: 20px;
    margin-right: 7px;
    background-image: url(/_images/icons/icon_20x20.png);
    margin: 4px 10px 0 0;
    background-position: -180px -20px;
}
.footer .pmeans {margin: 0 7px 10px 0;float: left;}
#product_detail a.variant_color_outer_border {
	border:2px solid transparent;
	margin-right:8px; 
	margin-bottom:5px; 	
}

#product_detail a:hover.variant_color_outer_border, #product_detail a.variant_color_outer_border.selected {
	border:2px solid #ffd926;
	cursor:pointer;
}

#product_detail div.variant_color_inner_border {
	border:1px solid #ddd; 
	padding:1px;	
}

#product_detail div.variant_color {	
	width:20px; 
	height:20px; 
	margin:auto;
	display:block;	
}

#product_detail a.variant_color_outer_border input {
	display:none;	
}
.landscape .item-inner {height:420px !important;}
#upsell-products-list li .item-inner:first {border: 1px solid #e8e8e8;}