<?php

// 1. Cargas el autoload de Composer primero
require(__DIR__ . '/../vendor/autoload.php');

// 2. CARGAR LAS VARIABLES DE ENTORNO AQUI
// Buscamos el archivo .env en el directorio padre (la raíz del proyecto)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad(); // Usamos safeLoad para que no de error si el archivo no existe en producción

// 3. Definir constantes de Yii dinámicamente
// Si no existe la variable, por defecto asume producción (seguridad primero)
$debug = isset($_ENV['YII_DEBUG']) ? filter_var($_ENV['YII_DEBUG'], FILTER_VALIDATE_BOOLEAN) : false;
$env = $_ENV['YII_ENV'] ?? 'prod';

// comment out the following two lines when deployed to production
// 3. Definir constantes de Yii (puedes incluso mover YII_DEBUG al .env si quisieras)
defined('YII_DEBUG') or define('YII_DEBUG', $debug);
defined('YII_ENV') or define('YII_ENV', $env);

// 4. Cargar Yii
require(__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');

$config = require(__DIR__ . '/../config/web.php');

(new yii\web\Application($config))->run();
