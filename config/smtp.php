<?php
// Archivo: config/smtp.php

//IMPORTANTE
//Cambia los datos en el ENV, a mí no me aparece el archivo

return [
    'class' => 'yii\swiftmailer\Mailer',
    'useFileTransport' => false,
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp.gmail.com',
        //Descomentar esto
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
