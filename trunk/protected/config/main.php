<?php
function pr($data=array(), $end='', $stop=false)
{
    print_r($data);
    echo $end;
    if($stop) die;
}
function pd($data=array(), $end='', $stop=true)
{
    print_r($data);
    echo $end;
    if($stop) die;
}

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'API',
    'language'=>'zh_cn',

	// preloading 'log' component
	'preload'=>array('log'),

	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	'modules'=>array(
		// uncomment the following to enable the Gii tool
		/*
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'',
		 	// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
		*/
	),

	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
		),
		// uncomment the following to enable URLs in path-format
		/*
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),
		*/
		// uncomment the following to use a MySQL database

		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=wordpress',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
		),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
            'errorAction'=>'site/error',
        ),
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
				),
				// uncomment the following to show log messages on web pages
				/*
				array(
					'class'=>'CWebLogRoute',
				),
				*/
			),
		),
        'cache'=>array(
            'class'=>'CMemCache',
            'servers'=>array(
                array(
                    'host'=>'127.0.0.1',
                    'port'=>11211,
                    'weight'=>100,
                ),
            ),
        ),
        'fcache'=>array(
            'class'=>'CFileCache',
            'cacheFileSuffix'=>'.php',
        ),
        'CURL' =>array(
            'class' => 'application.extensions.Curl',
        ),
        'doc360' =>array(
            'class' => 'application.extensions.doc360',
        ),
        'xuk' =>array(
            'class' => 'application.extensions.xuk',
        ),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
        'adminEmail'=>'',
        'wpadmin'=>'admin',
        'wppass'=>'',
        'host'=>$_SERVER['SERVER_NAME'],
		'ad1'=>'
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4726192443658314";
/* 728x90-首页横幅 */
google_ad_slot = "1018549157";
google_ad_width = 728;
google_ad_height = 90;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
',
    'ad2'=>'
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4726192443658314";
/* 728x15-链接单元 */
google_ad_slot = "4456137852";
google_ad_width = 728;
google_ad_height = 15;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
',
        'ad3'=>'
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4726192443658314";
/* 160x600-宽幅摩天大楼 */
google_ad_slot = "0203809730";
google_ad_width = 160;
google_ad_height = 600;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
',
        'ad4'=>'
<script type="text/javascript"><!--
google_ad_client = "ca-pub-4726192443658314";
/* 336x280-大矩形 */
google_ad_slot = "3354931463";
google_ad_width = 336;
google_ad_height = 280;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
',

	),
);