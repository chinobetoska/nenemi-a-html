<?php
/**
 * PROCESAR_LOGIN.PHP
 * Este archivo procesa el inicio de sesión de usuarios
 * Verifica que el email exista y que la contraseña sea correcta
 */

// Iniciar la sesión
session_start();

// Incluir el archivo de conexión
require_once 'conexion.php';

// Verificar que el formulario se envió mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ==========================================
    // PASO 1: RECIBIR Y LIMPIAR LOS DATOS
    // ==========================================
    
    $email = trim($_POST['e-mail_registro'] ?? '');
    $contrasena = trim($_POST['contrasena_registro'] ?? '');
    
    // Array para errores
    $errores = [];
    
    // ==========================================
    // PASO 2: VALIDACIONES BÁSICAS
    // ==========================================
    
    if (empty($email)) {
        $errores[] = "El email es obligatorio";
    }
    
    if (empty($contrasena)) {
        $errores[] = "La contraseña es obligatoria";
    }
    
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido";
    }
    
    // Si hay errores, redirigir con mensaje
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header('Location: /html/login.html?error=validacion');
        exit();
    }
    
    // ==========================================
    // PASO 3: BUSCAR EL USUARIO EN LA BASE DE DATOS
    // ==========================================
    
    try {
        // Obtener la conexión
        $db = obtenerConexion();
        
        // Buscar el usuario por email
        // Seleccionamos el id, email, contraseña y estado activo
        $sql = "SELECT id, email, contrasena, activo FROM usuarios WHERE email = :email";
        $stmt = $db->prepare($sql);
        $stmt->execute([':email' => $email]);
        
        // Obtener el resultado
        $usuario = $stmt->fetch();
        
        // ==========================================
        // PASO 4: VERIFICAR QUE EL USUARIO EXISTE
        // ==========================================
        
        if (!$usuario) {
            // El email no existe en la base de datos
            // Por seguridad, mostramos un mensaje genérico
            // No especificamos si el email no existe o la contraseña es incorrecta
            $_SESSION['error'] = "Email o contraseña incorrectos";
            header('Location: /html/login.html?error=credenciales');
            exit();
        }
        
        // ==========================================
        // PASO 5: VERIFICAR QUE LA CUENTA ESTÉ ACTIVA
        // ==========================================
        
        if (!$usuario['activo']) {
            $_SESSION['error'] = "Esta cuenta ha sido desactivada. Contacta al soporte técnico.";
            header('Location: /html/login.html?error=inactivo');
            exit();
        }
        
        // ==========================================
        // PASO 6: VERIFICAR LA CONTRASEÑA
        // ==========================================
        
        // password_verify() compara la contraseña ingresada con el hash guardado
        // Esta función está diseñada para ser segura contra ataques de tiempo
        if (password_verify($contrasena, $usuario['contrasena'])) {
            
            // ¡La contraseña es correcta!
            
            // ==========================================
            // PASO 7: CREAR LA SESIÓN DEL USUARIO
            // ==========================================
            
            // Regenerar el ID de sesión para prevenir fijación de sesión
            session_regenerate_id(true);
            
            // Guardar información del usuario en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_logueado'] = true;
            $_SESSION['tiempo_login'] = time();
            
            // ==========================================
            // PASO 8: ACTUALIZAR ÚLTIMO ACCESO
            // ==========================================
            
            $sqlActualizar = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
            $stmtActualizar = $db->prepare($sqlActualizar);
            $stmtActualizar->execute([':id' => $usuario['id']]);
            
            // ==========================================
            // PASO 9: REGISTRAR EN TABLA DE SESIONES (OPCIONAL)
            // ==========================================
            
            // Si quieres llevar un registro más detallado de sesiones
            try {
                $sessionId = session_id();
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'desconocido';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'desconocido';
                $expiracion = date('Y-m-d H:i:s', time() + 3600); // 1 hora
                
                $sqlSesion = "INSERT INTO sesiones (id, usuario_id, ip_address, user_agent, fecha_expiracion) 
                              VALUES (:session_id, :usuario_id, :ip, :user_agent, :expiracion)
                              ON DUPLICATE KEY UPDATE fecha_expiracion = :expiracion";
                
                $stmtSesion = $db->prepare($sqlSesion);
                $stmtSesion->execute([
                    ':session_id' => $sessionId,
                    ':usuario_id' => $usuario['id'],
                    ':ip' => $ipAddress,
                    ':user_agent' => $userAgent,
                    ':expiracion' => $expiracion
                ]);
            } catch (Exception $e) {
                // Si falla el registro de sesión, no importa mucho
                // El login principal ya funcionó
            }
            
            // ==========================================
            // PASO 10: REDIRIGIR AL USUARIO
            // ==========================================
            
            // Mensaje de bienvenida
            $_SESSION['mensaje_exito'] = "¡Bienvenido de vuelta a NENEMI-A!";
            
            // Verificar si hay una URL de retorno guardada
            // Esto es útil si el usuario estaba en alguna página y le pediste que iniciara sesión
            if (isset($_SESSION['url_retorno'])) {
                $urlRetorno = $_SESSION['url_retorno'];
                unset($_SESSION['url_retorno']);
                header("Location: $urlRetorno");
            } else {
                // Redirigir a la página de inicio por defecto
                header('Location: /html/inicio.html?login=exitoso');
            }
            exit();
            
        } else {
            // La contraseña es incorrecta
            
            // ==========================================
            // OPCIONAL: REGISTRAR INTENTO FALLIDO
            // ==========================================
            
            // Podrías incrementar un contador de intentos fallidos
            // y bloquear la cuenta después de X intentos
            
            // Por ahora, solo mostramos error
            $_SESSION['error'] = "Email o contraseña incorrectos";
            header('Location: /html/login.html?error=credenciales');
            exit();
        }
        
    } catch (PDOException $e) {
        // Error de base de datos
        $_SESSION['error'] = "Error en el sistema. Por favor, intenta nuevamente.";
        
        // En desarrollo, descomentar para ver el error:
        // $_SESSION['error'] = "Error: " . $e->getMessage();
        
        header('Location: /html/login.html?error=sistema');
        exit();
        
    } catch (Exception $e) {
        // Otros errores
        $_SESSION['error'] = "Ocurrió un error inesperado";
        header('Location: /html/login.html?error=general');
        exit();
    }
    
} else {
    // Acceso directo sin POST
    $_SESSION['error'] = "Acceso no autorizado";
    header('Location: /html/login.html');
    exit();
}

/**
 * MEJORAS ADICIONALES QUE PODRÍAS IMPLEMENTAR:
 * 
 * 1. Sistema de "Recordarme" con cookies seguras
 * 2. Límite de intentos de login (anti fuerza bruta)
 * 3. Recuperación de contraseña por email
 * 4. Autenticación de dos factores (2FA)
 * 5. Registro de todos los intentos de acceso
 * 6. Notificación por email cuando hay un nuevo inicio de sesión
 * 7. Tiempo de expiración de sesión por inactividad
 */
?>