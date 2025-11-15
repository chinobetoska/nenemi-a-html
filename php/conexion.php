<?php

 */


define('DB_HOST', 'localhost');        // El servidor donde está MySQL (siempre localhost en XAMPP)
define('DB_NAME', 'nenemia_db');       // El nombre de tu base de datos (cámbialo según tu necesidad)
define('DB_USER', 'root');             // Usuario por defecto de XAMPP
define('DB_PASS', '');                 // Contraseña vacía por defecto en XAMPP
define('DB_CHARSET', 'utf8mb4');       // Codificación para caracteres especiales y emojis


  
  @return PDO|null Retorna el objeto de conexión o null si hay error
 
function obtenerConexion() {
    try {
        // Crear el DSN (Data Source Name) - es como la dirección de la base de datos
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        // Opciones de configuración para PDO
        $opciones = [
            // Modo de errores: lanza excepciones cuando hay problemas
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Modo de obtención: devuelve resultados como arrays asociativos
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // Desactiva la emulación de prepared statements para mayor seguridad
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        // Crear la conexión PDO
        $conexion = new PDO($dsn, DB_USER, DB_PASS, $opciones);
        
        return $conexion;
        
    } catch (PDOException $e) {
        // Si hay un error, lo mostramos de forma clara
        // En producción, deberías registrar esto en un log en lugar de mostrarlo
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}


?>