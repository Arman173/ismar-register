<?php

// 1. Cargas el autoload de Composer primero
require(__DIR__ . '/../vendor/autoload.php');

// 2. CARGAR LAS VARIABLES DE ENTORNO AQUI
// Buscamos el archivo .env en el directorio padre (la raíz del proyecto)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // Usamos safeLoad para que no de error si el archivo no existe en producción

// comment out the following two lines when deployed to production
// 3. Definir constantes de Yii (puedes incluso mover YII_DEBUG al .env si quisieras)
defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'dev');

// 4. Cargar Yii
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
