<?php

error_reporting(E_ERROR);

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');
// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'name' => 'Admin',
//	'sourceLanguage' => 'en',
    'language' => 'en',
    // preloading 'log' component
    'preload' => array('log'),
    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.models.tables.*',
        'application.components.*',
//		'application.components.widgets.*',
    ),
    'modules' => array(
        'gii' => array(
            'class' => 'system.gii.GiiModule',
            'password' => 'admin',
        // 'ipFilters'=>array(...a list of IPs...),
        // 'newFileMode'=>0666,
        // 'newDirMode'=>0777,
        ),
    ),
    // application components
    'components' => array(
        'user' => array(
            // enable cookie-based authentication
            'allowAutoLogin' => true,
            'autoRenewCookie' => true,
//			'stateKeyPrefix'=>session_id(),
        ),
        // uncomment the following to enable URLs in path-format
        'urlManager' => array(
            'urlFormat' => 'path',
            'rules' => array(
                'gii' => 'gii',
                'gii/<controller:\w+>' => 'gii/<controller>',
                'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',
                '<controller:\w+>/<id:\d+>' => '<controller>',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        // uncomment the following to use a MySQL database



        'db' => array(
            'class' => 'system.db.CDbConnection',
            'connectionString' => 'mysql:host=localhost;dbname=devleoha_01',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'enableParamLogging' => true,
            'enableProfiling' => true,
        ),
        'authManager' => array(
            'class' => 'CDbAuthManager',
            'connectionID' => 'db',
        ),
        'errorHandler' => array(
            // use 'site/error' action to display errors
            'errorAction' => 'site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    //'levels'=>'error, warning',
                    'levels' => 'error, warning',
                ),
            /*
              array(
              'class'=>'CProfileLogRoute',
              'report'=>'summary',
              'levels'=>'trace, info, error, warning',
              ),

              // uncomment the following to show log messages on web pages
              /*
              array(
              'class'=>'CWebLogRoute',
              ),
             */
            ),
        ),
    ),
    'theme' => 'default',
    'timeZone' => 'America/Montreal',
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
    // this is used in contact page
    //'adminEmail'=>'webmaster@example.com',
    ),
);