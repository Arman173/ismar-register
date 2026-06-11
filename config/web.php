<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset', // IMPORTANTE
        '@npm'   => '@vendor/npm-asset',   // IMPORTANTE
    ],
	'language' => 'es-MX',
	'defaultRoute' => 'registration/submit',
    'modules' => [
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
        ]
    ],
    'components' => [
		'authManager' => [
			'class' => 'yii\rbac\DbManager',
		],
		'urlManager' => [
			'enablePrettyUrl' => true,
			'showScriptName' => false,
			'hostInfo' => 'http://localhost/',
		],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => $_ENV['COOKIE_VALIDATION_KEY'] ?? '',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        /*------ MAIL CON MICROSOFT GRAPH API ------*/
        // 'mailer' => [
        //     'class' => 'app\components\GraphMailer',
        //     'viewPath' => '@app/mail',
        //     // Para desarrollo, poner esto en 'true' para que no envíe nada y solo cree archivos .eml en runtime/mail/
        //     // Para producción (para que cURL se ejecute), cámbiar a 'false'
        //     'useFileTransport' => false, 
            
        //     // Inyección de dependencias desde params
        //     'tenant_id' => $params['graph_tenant_id'],
        //     'client_id' => $params['graph_client_id'],
        //     'client_secret' => $params['graph_client_secret'],
        // ],

        /*------ MAIL CON SMTP ------*/
        // para produccion, se recomienda configurar un servidor SMTP y descomentar esta sección
         'mailer' => (file_exists(__DIR__ . '/smtp.php')) 
             ? require(__DIR__ . '/smtp.php') 
             : [
                'class' => 'yii\swiftmailer\Mailer',
                'useFileTransport' => true,
         ],
         
        /*------ MAIL PARA DESARROLLO - SE GUARDA LOCALMENTE ------*/
        // para desarrollo, los guarda como archivos .eml en la carpeta "runtime/mail/"
        //'mailer' => [
        //    'class' => 'yii\swiftmailer\Mailer',
        //    'useFileTransport' => true,
        //],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
