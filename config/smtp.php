<?php
// Archivo: config/smtp.php

return [
    'class' => 'yii\swiftmailer\Mailer',
    'useFileTransport' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp.gmail.com',
        // AQUI HACEMOS EL CAMBIO:
        'username' => $_ENV['SMTP_USERNAME'], 
        'password' => $_ENV['SMTP_PASSWORD'],
        'port' => '587',
        'encryption' => 'tls',
        'streamOptions' => [ 
            'ssl' => [ 
                'allow_self_signed' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ],
    ],
];