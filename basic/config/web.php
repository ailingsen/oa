<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' =>false,
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtp.exmail.qq.com',
                'username' => 'hnoa@supernano.com',
                'password' => 'Supernanooa2016',
                'port' => '465',
                'encryption' => 'ssl',
            ],
            'messageConfig'=>[
                'charset'=>'UTF-8',
                'from'=>['hnoa@supernano.com'=>'admin']
            ],
        ],
        //memcache配置
        'memCache'=>[
            'class'=>'yii\caching\MemCache',
            'servers'=>[
                [
                    'host'=>'192.168.32.128',
                    'port'=>11211,
                    'weight'=>100,
                ],
            ],
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'oasys',
            'enableCookieValidation' => false,
            'enableCsrfValidation' => FALSE,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
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
        'log' => [
            'traceLevel' => YII_DEBUG ? 7 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
        'view' => [
            'renderers' => [
                'tpl' => [
                    'class' => 'yii\smarty\ViewRenderer',
                    //'cachePath' => '@runtime/Smarty/cache',
                ],
            ],
        ],
        /*
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        */
    ],
    'params' => $params,
    'modules' => require(__DIR__ . '/modules.php'),
    'aliases' => [
        '@upload' => dirname(__DIR__).'/../file',               //上传基本路径
        '@notice' => '@upload/notice',                          //公告上传路径
        '@task' => '@upload/task',                              //任务上传路径
        '@apply' => '@upload/apply',
        '@other' => '@upload/other',                            //其他附件
        '@resume' => dirname(__DIR__).'/web/static/resume',                //简历路径
        '@ueditor' =>dirname(__DIR__).'/web/ueditor/php/upload/image',
        '@file_root' =>'http://oa.filesupernano.com',
    ],
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
