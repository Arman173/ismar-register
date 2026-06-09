<?php

return [
    // Usamos el .env, y si no existe la variable, dejamos tu correo como fallback de seguridad
    'adminEmail' => $_ENV['ADMIN_EMAIL'] ?? '',
    'coordinatorEmail1' => $_ENV['COORDINATOR_EMAIL_1'] ?? '',
    'coordinatorEmail2' => $_ENV['COORDINATOR_EMAIL_2'] ?? '',
    'accountingEmail' => $_ENV['ACCOUNTING_EMAIL'] ?? '',

    // Credenciales de App Registration para Microsoft Graph (CGTIC)
    // Se deja vacío por defecto para que las peticiones a la API fallen de forma controlada si no se configuran
    'graph_tenant_id' => $_ENV['GRAPH_TENANT_ID'] ?? '',
    'graph_client_id' => $_ENV['GRAPH_CLIENT_ID'] ?? '',
    'graph_client_secret' => $_ENV['GRAPH_CLIENT_SECRET'] ?? '',
];