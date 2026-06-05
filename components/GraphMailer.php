<?php

namespace app\components;

use Yii;
use yii\swiftmailer\Mailer;
use yii\base\InvalidConfigException;

class GraphMailer extends Mailer
{
    public $tenant_id;
    public $client_id;
    public $client_secret;

    /**
     * Sobrescribimos el método de envío para usar Graph API en lugar de SMTP
     */
    protected function sendMessage($message)
    {
        // 1. Extraer los datos del mensaje pre-armado por Yii2
        $swiftMessage = $message->getSwiftMessage();
        $subject = $swiftMessage->getSubject();
        $bodyContent = $swiftMessage->getBody(); // Obtiene el HTML renderizado

        // 2. Formatear remitente y destinatarios
        $fromEmail = $this->extractFirstEmail($message->getFrom());
        $toRecipients = $this->formatRecipients($message->getTo());
        $ccRecipients = $this->formatRecipients($message->getCc());

        if (empty($fromEmail) || empty($toRecipients)) {
            return false;
        }

        // 3. Obtener el Token (con Caché)
        $accessToken = $this->getAccessToken();

        // 4. Preparar el JSON para Graph API
        $messageData = [
            'subject' => $subject,
            'body' => [
                'contentType' => 'HTML',
                'content' => $bodyContent
            ],
            'toRecipients' => $toRecipients
        ];

        if (!empty($ccRecipients)) {
            $messageData['ccRecipients'] = $ccRecipients;
        }

        $bodyJsonSend = json_encode([
            'message' => $messageData,
            'saveToSentItems' => true
        ]);

        // 5. Enviar vía cURL
        $urlSend = "https://graph.microsoft.com/v1.0/users/$fromEmail/sendMail";
        $headers = [
            "Authorization: Bearer " . $accessToken,
            "Content-type: application/json"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $urlSend);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJsonSend);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            Yii::error("CURL Error enviando correo: " . curl_error($ch), __METHOD__);
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            Yii::info("Correo enviado exitosamente a Graph API.", __METHOD__);
            return true;
        } else {
            Yii::error("Error Graph API ($httpCode): $response", __METHOD__);
            return false;
        }
    }

    /**
     * Obtiene el token de acceso, utilizando el caché de Yii2 para optimizar
     */
    private function getAccessToken()
    {
        $cacheKey = 'microsoft_graph_token';
        $token = Yii::$app->cache->get($cacheKey);

        if ($token !== false) {
            return $token; // Retorna el token cacheado si aún es válido
        }

        $tokenBody = [
            'grant_type' => 'client_credentials',
            'scope' => 'https://graph.microsoft.com/.default',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://login.microsoftonline.com/{$this->tenant_id}/oauth2/v2.0/token");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($tokenBody));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new InvalidConfigException("Error obteniendo token Graph API: " . curl_error($ch));
        }
        curl_close($ch);

        $data = json_decode($response, true);
        if (!isset($data['access_token'])) {
            throw new InvalidConfigException("Respuesta inválida al solicitar token: " . $response);
        }

        // Microsoft da tokens de 3600 segundos (1 hr). Lo guardamos 3000 segs (50 min) por seguridad.
        Yii::$app->cache->set($cacheKey, $data['access_token'], 3000);

        return $data['access_token'];
    }

    // --- Helpers para parsear el formato de correos de SwiftMailer a Graph API ---

    private function extractFirstEmail($from)
    {
        if (empty($from)) return '';
        if (is_string($from)) return $from;
        return array_key_first($from); // Obtiene el correo si viene en formato ['correo@uady.mx' => 'Nombre']
    }

    private function formatRecipients($recipients)
    {
        $formatted = [];
        if (empty($recipients)) return $formatted;
        
        if (is_string($recipients)) {
            $formatted[] = ['emailAddress' => ['address' => $recipients]];
        } elseif (is_array($recipients)) {
            foreach ($recipients as $email => $name) {
                $address = is_int($email) ? $name : $email;
                $formatted[] = ['emailAddress' => ['address' => $address]];
            }
        }
        return $formatted;
    }
}