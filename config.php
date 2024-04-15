<?php

/*
 * Archivo que contiene parámetros de configuración de la aplicación.
 * Es por buenas prácticas tener elementos que son variables en las aplicaciones
 * en su respectivo archivo de configuración, esto para no tener que estar 
 * modificando cada uno de los archivos en los que se hace referencia a estos
 * parámetros de configuración.
 */

// Definimos la ruta física de donde se a ubicar los archivos de nuestra aplicación.
define("APP_PATH", "C:\xampp\htdocs\VuePic");

// La ruta relativa que corresonde al root de nuestra aplicación.
define("APP_ROOT", "/VuePic/");

// Directorio donde se van a guardar los archivos subidos a la aplicación.
define("DIR_UPLOAD", "C:\xampp\htdocs\VuePic\uploads");

// Extensiones válidas para los archivos de fotos que se van a subir.
$EXT_ARCHIVOS_FOTOS = ["png", "gif", "jpg", "jpeg"];

// Extensiones de archivos con su correspondiente content-type.
$CONTENT_TYPES_EXT = [
    "jpg" => "image/jpeg",
    "jpeg" => "image/jpeg",
    "gif" => "image/gif",
    "png" => "image/png",
    "json" => "application/json",
    "pdf" => "application/pdf",
    "bin" => "application/octet-stream"
];

// Configuraciones correspondientes a la conexión a base de datos.
define("DB_DSN", "mysql:host=127.0.0.1;port=3306;dbname=foto_blog;charset=utf8mb4;");
define("DB_USERNAME", "root"); 
define("DB_PASSWORD", "");
