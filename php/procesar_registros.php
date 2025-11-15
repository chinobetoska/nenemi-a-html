<?php
/**
 * PROCESAR_REGISTRO.PHP
 * Este archivo procesa el formulario de registro de usuarios
 * Valida los datos, verifica que el email no esté duplicado,
 * y guarda la información de forma segura en la base de datos
 */

// Iniciar la sesión para poder usar variables de sesión
session_start();

// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar que el formulario se envió mediante POST
// POST es más seguro que GET porque los datos no aparecen en la URL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ==========================================
    // PASO 1: RECIBIR Y LIMPIAR LOS DATOS
    // ==========================================
    
    // Recibir los datos del formulario
    // trim() elimina espacios en blanco al inicio y final
    // filter_var() limpia y valida los datos
    $email = trim($_POST['e-mail_registro'] ?? '');
    $telefono = trim($_POST['telefono_registro'] ?? '');
    $contrasena = trim($_POST['contrasena_registro'] ?? '');
    
    // Array para almacenar los errores de validación
    $errores = [];
    
    // ==========================================
    // PASO 2: VALIDAR LOS DATOS
    // ==========================================
    
    // Validar que ningún campo esté vacío
    if (empty($email)) {
        $errores[] = "El email es obligatorio";
    }
    
    if (empty($telefono)) {
        $errores[] = "El teléfono es obligatorio";
    }
    
    if (empty($contrasena)) {
        $errores[] = "La contraseña es obligatoria";
    }
    
    // Validar formato de email
    // filter_var con FILTER_VALIDATE_EMAIL verifica que el email tenga formato válido
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "El formato del email no es válido";
    }
    
    // Validar longitud de la contraseña (mínimo 6 caracteres)
    if (!empty($contrasena) && strlen($contrasena) < 6) {
        $errores[] = "La contraseña debe tener al menos 6 caracteres";
    }
    
    // Validar formato de teléfono (solo números, 10 dígitos)
    if (!empty($telefono) && !preg_match('/^[0-9]{10}$/', $telefono)) {
        $errores[] = "El teléfono debe contener exactamente 10 dígitos";
    }
    
    // ==========================================
    // PASO 3: VERIFICAR SI HAY ERRORES
    // ==========================================
    
    // Si hay errores de validación, no continuar
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        $_SESSION['datos_formulario'] = $_POST; // Guardar datos para no perderlos
        header('Location: /index.html?error=validacion');
        exit();
    }
    
    // ==========================================
    // PASO 4: VERIFICAR EMAIL DUPLICADO
    // ==========================================
    
    try {
        // Obtener la conexión a la base de datos
        $db = obtenerConexion();
        
        // Preparar consulta para verificar si el email ya existe
        // Usar prepared statements previene inyección SQL
        $sqlVerificar = "SELECT id FROM usuarios WHERE email = :email";
        $stmtVerificar = $db->prepare($sqlVerificar);
        $stmtVerificar->execute([':email' => $email]);
        
        // Si el email ya existe, mostrar error
        if ($stmtVerificar->fetch()) {
            $_SESSION['error'] = "Este email ya está registrado. Por favor, inicia sesión o usa otro email.";
            $_SESSION['datos_formulario'] = $_POST;
            header('Location: /index.html?error=duplicado');
            exit();
        }
        
        // ==========================================
        // PASO 5: ENCRIPTAR LA CONTRASEÑA
        // ==========================================
        
        // password_hash() crea un hash seguro de la contraseña
        // PASSWORD_DEFAULT usa el algoritmo más seguro disponible (actualmente bcrypt)
        // NUNCA guardes contraseñas en texto plano
        $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);
        
        // ==========================================
        // PASO 6: INSERTAR EN LA BASE DE DATOS
        // ==========================================
        
        // Preparar la consulta SQL para insertar el nuevo usuario
        $sqlInsertar = "INSERT INTO usuarios (email, telefono, contrasena) 
                        VALUES (:email, :telefono, :contrasena)";
        
        $stmtInsertar = $db->prepare($sqlInsertar);
        
        // Ejecutar la consulta con los datos validados
        $resultado = $stmtInsertar->execute([
            ':email' => $email,
            ':telefono' => $telefono,
            ':contrasena' => $contrasenaHash
        ]);
        
        // ==========================================
        // PASO 7: VERIFICAR ÉXITO Y CREAR SESIÓN
        // ==========================================
        
        if ($resultado) {
            // Obtener el ID del usuario recién insertado
            $usuarioId = $db->lastInsertId();
            
            // Crear variables de sesión para el usuario
            $_SESSION['usuario_id'] = $usuarioId;
            $_SESSION['usuario_email'] = $email;
            $_SESSION['usuario_logueado'] = true;
            
            // Registrar el último acceso
            $sqlActualizar = "UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id";
            $stmtActualizar = $db->prepare($sqlActualizar);
            $stmtActualizar->execute([':id' => $usuarioId]);
            
            // Redirigir a la página de inicio con mensaje de éxito
            $_SESSION['mensaje_exito'] = "¡Registro exitoso! Bienvenido a NENEMI-A.";
            header('Location: /html/inicio.html?registro=exitoso');
            exit();
            
        } else {
            // Si por alguna razón no se pudo insertar
            throw new Exception("No se pudo completar el registro");
        }
        
    } catch (PDOException $e) {
        // Error de base de datos
        $_SESSION['error'] = "Error en el sistema. Por favor, intenta nuevamente más tarde.";
        
        // En desarrollo, puedes descomentar esta línea para ver el error exacto:
        // $_SESSION['error'] = "Error: " . $e->getMessage();
        
        header('Location: /index.html?error=sistema');
        exit();
        
    } catch (Exception $e) {
        // Otros errores
        $_SESSION['error'] = $e->getMessage();
        header('Location: /index.html?error=general');
        exit();
    }
    
} else {
    // Si alguien intenta acceder directamente a este archivo sin enviar el formulario
    $_SESSION['error'] = "Acceso no autorizado";
    header('Location: /index.html');
    exit();
}

/**
 * NOTAS IMPORTANTES SOBRE SEGURIDAD:
 * 
 * 1. NUNCA guardes contraseñas en texto plano - siempre usa password_hash()
 * 2. SIEMPRE usa prepared statements para prevenir inyección SQL
 * 3. Valida TODOS los datos del lado del servidor, no confíes solo en validación del navegador
 * 4. Usa HTTPS en producción para que los datos viajen encriptados
 * 5. Considera agregar CAPTCHA para prevenir bots
 * 6. Implementa límite de intentos para prevenir ataques de fuerza bruta
 */
?>