<?php

// Leemos las variables que Docker le inyectó a PHP desde tu archivo .env
$dbHost = getenv('MYSQL_HOST') ?: 'localhost';
$dbName = getenv('MYSQL_DATABASE') ?: 'ismardb';
$username = getenv('MYSQL_USER') ?: 'root';
$password = getenv('MYSQL_PASSWORD') ?: '';

return [
    'class' => 'yii\db\Connection',
    // ¡EL CAMBIO MÁS IMPORTANTE! host=db
    'dsn' => "mysql:host={$dbHost};dbname={$dbName}",
    'username' => $username,
    'password' => $password,
    'charset' => 'utf8',
];