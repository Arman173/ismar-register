<?php
// Archivo: config/smtp.php
return [
    'class' => 'yii\swiftmailer\Mailer',
    'useFileTransport' => false, // IMPORTANTE: false para enviar de verdad
    'transport' => [
        'class' => 'Swift_SmtpTransport',
        'host' => 'smtp.gmail.com',
        'username' => 'correo@gmail.com', // Tu correo real
        'password' => 'xxxx xxxx xxxx xxxx', // <--- La clave google
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