Sistema de Registro - ConCEI 3
============================

Este repositorio contiene el backend del sistema de registro y gestión de asistentes para el Congreso de Ciencias Exactas e Ingenierías (ConCEI 3), desarrollado sobre el framework Yii 2 (Basic Project Template).

El sistema gestiona la inscripción de usuarios, pagos, selección de talleres/visitas y la generación de reportes (incluyendo exportación a Excel).

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-app-basic/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-app-basic/downloads.png)](https://packagist.org/packages/yiisoft/yii2-app-basic)
[![Build Status](https://travis-ci.org/yiisoft/yii2-app-basic.svg?branch=master)](https://travis-ci.org/yiisoft/yii2-app-basic)

ESTRUCTURA DE DIRECTORIOS
-------------------

      assets/             contiene la definición de recursos (CSS/JS)
      commands/           contiene comandos de consola (controladores)
      config/             contiene las configuraciones de la aplicación y dependencias
      controllers/        contiene las clases de controladores web
      mail/               contiene los archivos de vista para correos electrónicos
      models/             contiene las clases de modelos (ActiveRecord)
      runtime/            contiene archivos generados en tiempo de ejecución (caché, logs)
      tests/              contiene varias pruebas para la aplicación
      vendor/             contiene paquetes de terceros dependientes (Composer)
      views/              contiene los archivos de vista para la aplicación web
      web/                contiene el script de entrada (index.php) y recursos públicos



REQUISITOS DEL SISTEMA
------------

- PHP: >= 7.4.0 (Se recomienda PHP 8.x).
- Base de datos: MySQL o MariaDB.
- Composer: Gestor de dependencias de PHP.
- Servidor Web: Apache
- Extensión PHP ext-gd: Habilitada para la generación y exportación de archivos Excel.


GUÍA DE INSTALACIÓN Y DESPLIEGUE
------------

### 1. Configuración de Variables de Entorno (.env)

Crea un archivo llamado .env en la raíz del proyecto e inserta el siguiente bloque de configuración, ajustando los valores a tu entorno de producción o desarrollo:

~~~
# CONFIGURACIÓN DEL ENTORNO
# Para desarrollo usa "true" y "dev". Para producción usa "false" y "prod".
YII_DEBUG=false
YII_ENV=prod

# Llave secreta para la validación de cookies (Genera un texto al azar y pégalo aquí)
COOKIE_VALIDATION_KEY=

# BASE DE DATOS
MYSQL_HOST=host_base_de_datos
MYSQL_DATABASE=nombre_base_de_datos
MYSQL_USER=root
MYSQL_PASSWORD=

# SERVIDOR SMTP (Envío de correos con smtp)
SMTP_USERNAME=correo@dominio.com
SMTP_PASSWORD=xxxxxxxxxxxxxxxx

# CORREOS ADMINISTRATIVOS DEL SISTEMA
COORDINATOR_EMAIL_1=correocordinator1@dominio.com
COORDINATOR_EMAIL_2=correocordinator2@dominio.com
# estos son opcionales, no se usan a menos que se asigne en el controlador del del registro
ADMIN_EMAIL=correoadmin@dominio.com
ACCOUNTING_EMAIL=correoaccounting@dominio.com

# API MICROSOFT GRAPH (CGTIC)
# Llenar los campos y habilitar el uso de CGTIC en vez del smtp (en el config/web.php)
GRAPH_TENANT_ID=
GRAPH_CLIENT_ID=
GRAPH_CLIENT_SECRET=
~~~


### 2. Habilitar la extensión GD en PHP

El sistema utiliza la librería `phpoffice/phpspreadsheet` para exportar la lista de asistentes a formato Excel (`.csv` / `.xlsx`). Esta librería requiere estrictamente que la extensión gráfica **GD** esté activa en el servidor.

**Para habilitarla:**
1. Abrir el archivo `php.ini` del servidor (en entornos XAMPP, accesible desde *Config > PHP (php.ini)* en el panel de Apache; en Linux usualmente en `/etc/php/8.x/apache2/php.ini`).
2. Busca la directiva `;extension=gd`.
3. Elimina el punto y coma inicial para descomentarla: `extension=gd`.
4. Guarda el archivo y reinicia el servicio del servidor web (Apache).

### 3. Instalación de Dependencias

Con el archivo `.env` creado y la extensión `gd` activa, abre una terminal en la raíz del proyecto y ejecuta el manejador de paquetes para instalar las dependencias.

Para un entorno de producción, ejecuta el siguiente comando (esto omitirá las librerías de desarrollo como `phpunit` y optimizará la carga de clases):

```bash
composer install
```

### 4. Configuración de la Base de Datos

El sistema no crea la base de datos automáticamente. Debes importar el esquema y los datos base estructurados:

1. Accede a tu gestor de base de datos MySQL/MariaDB (vía consola, phpMyAdmin, DBeaver, etc.).
2. Crea una base de datos vacía que coincida con el nombre definido en tu archivo `.env` (ej. `ismardb`).
3. Importa el archivo `.sql` del proyecto (por ejemplo, `ismardb.sql`) dentro de esta nueva base de datos.

*Opcional:* Si necesitas limpiar el caché del esquema de la base de datos tras la importación, ejecuta en la terminal de la raíz del proyecto:

```bash
php yii cache/flush-schema
```

#### Notas
- Para que funcioné el guardado de los archivos (los comprobantes y identificaciones) hay que crear en `/web/files/` un directorio `payment` y `studentid`
- En el archivo `/config/web.php` podemos cambiar el uso de smtp o de GraphMailer para el envio de emails