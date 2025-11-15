<?php
/**
 * INDEX.PHP - Página de registro actualizada
 * Este archivo reemplaza tu index.html
 * La extensión .php permite ejecutar código PHP para mostrar mensajes y validaciones
 */

// Iniciar sesión para poder mostrar mensajes
session_start();

// Si el usuario ya está logueado, redirigir a inicio
if (isset($_SESSION['usuario_logueado']) && $_SESSION['usuario_logueado'] === true) {
    header('Location: /html/inicio.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NENEMI-A - Registro</title>
    <link rel="stylesheet" href="/nenemi-a/css/estilos.css"  />
    <link href="/imajenes/icono-alebrige.png" rel="icon" />
    <style>
      /* Estilos para mensajes de error y éxito */
      .mensaje {
        margin: 20px auto;
        padding: 15px;
        border-radius: 10px;
        max-width: 400px;
        text-align: center;
        font-size: 16px;
      }
      
      .mensaje-error {
        background-color: #ffebee;
        color: #c62828;
        border: 2px solid #ef5350;
      }
      
      .mensaje-exito {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 2px solid #66bb6a;
      }
      
      .error-campo {
        color: #c62828;
        font-size: 14px;
        margin-top: 5px;
        display: block;
      }
      
      .campo-invalido {
        border-color: #c62828 !important;
        background-color: #ffebee !important;
      }
    </style>
  </head>
  <body>
    <!--div para el titulo-->
    <div class="titulo-nenemia">
      <h1><i>BIENVENIDO</i></h1>
      <h1><i>A</i></h1>
      <h1><i>NENEMI-A</i></h1>
    </div>

    <?php
    // Mostrar mensajes de error si existen
    if (isset($_SESSION['error'])) {
        echo '<div class="mensaje mensaje-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    
    // Mostrar múltiples errores de validación
    if (isset($_SESSION['errores']) && !empty($_SESSION['errores'])) {
        echo '<div class="mensaje mensaje-error">';
        echo '<strong>Por favor corrige los siguientes errores:</strong><br>';
        foreach ($_SESSION['errores'] as $error) {
            echo '• ' . htmlspecialchars($error) . '<br>';
        }
        echo '</div>';
        unset($_SESSION['errores']);
    }
    
    // Mostrar mensaje de éxito si existe
    if (isset($_SESSION['mensaje_exito'])) {
        echo '<div class="mensaje mensaje-exito">' . htmlspecialchars($_SESSION['mensaje_exito']) . '</div>';
        unset($_SESSION['mensaje_exito']);
    }
    
    // Recuperar datos del formulario si hubo error (para no perderlos)
    $datosFormulario = $_SESSION['datos_formulario'] ?? [];
    unset($_SESSION['datos_formulario']);
    ?>

    <div class="contenedor">
      <h2>Registro De Usuarios</h2>
      
      <!-- CAMBIO IMPORTANTE: El action ahora apunta al archivo PHP que procesa el registro -->
      <form id="forma" name="forma" action="/procesar_registro.php" method="POST">
        
        <div class="elemento">
          <label for="e-mail_registro">Proporciona tu e-mail:</label>
          <input 
            type="email" 
            id="e-mail_registro" 
            name="e-mail_registro" 
            required
            value="<?php echo htmlspecialchars($datosFormulario['e-mail_registro'] ?? ''); ?>"
            placeholder="ejemplo@correo.com"
          />
        </div>

        <div class="elemento">
          <label for="telefono_registro">Proporciona tu teléfono (10 dígitos):</label>
          <input 
            type="tel" 
            id="telefono_registro" 
            name="telefono_registro" 
            required
            pattern="[0-9]{10}"
            value="<?php echo htmlspecialchars($datosFormulario['telefono_registro'] ?? ''); ?>"
            placeholder="7711234567"
            maxlength="10"
          />
          <small style="color: #FEFCF7; font-size: 12px;">Solo números, sin espacios ni guiones</small>
        </div>

        <div class="elemento">
          <label for="contrasena_registro">Proporciona una contraseña segura:</label>
          <input 
            type="password" 
            id="contrasena_registro" 
            name="contrasena_registro" 
            required
            minlength="6"
            placeholder="Mínimo 6 caracteres"
          />
          <small style="color: #FEFCF7; font-size: 12px;">La contraseña debe tener al menos 6 caracteres</small>
        </div>

        <div class="elemento">
          <input type="submit" value="Registrarse"/>
        </div>
      </form>
    </div>

    <div class="boton-inicio-sesion">
      <p>¿Ya tienes sesión?</p>
      <p>No pierdas tiempo</p>
      <p>Y</p>
      <p>Encuentra ya a tu nuevo</p>
      <p>Viaje</p>
      <br />
      <!-- CAMBIO: Ahora apunta al archivo PHP de login -->
      <a href="localhost/nenemi-a/php-html/login.php" id="boton-inicio-sesion-href">
        <button type="button" id="boton-inicio-de-sesion">INICIAR SESIÓN</button>
      </a>
    </div>

    <footer>
      <div class="footer-contenido">
        <p>&copy; 2025 NENEMI-A. Todos los derechos reservados.</p>

        <nav class="social-links">
          <a href="https://www.instagram.com/nenemi_a__oficial" target="_blank">
            <img src="/imajenes/instagram-icono.png" alt="Instagram" />
          </a>
          <a href="https://www.facebook.com/Nenem%Ia" target="_blank">
            <img src="/imajenes/icono-Facebook.png" alt="Facebook" />
          </a>
          <a href="https://www.tiktok.com/@nenemia52" target="_blank">
            <img src="/imajenes/logo-TikTok.png" alt="tiktok" />
          </a>
        </nav>

        <div class="partners">
          <img src="/imajenes/icono-alebrige.png" alt="Partner 1" />
        </div>
      </div>
    </footer>

    <script>
    // JavaScript para validación adicional en el navegador (opcional pero recomendado)
    document.getElementById('forma').addEventListener('submit', function(e) {
      const telefono = document.getElementById('telefono_registro').value;
      const contrasena = document.getElementById('contrasena_registro').value;
      
      // Validar que el teléfono solo tenga números
      if (!/^[0-9]{10}$/.test(telefono)) {
        e.preventDefault();
        alert('El teléfono debe contener exactamente 10 dígitos numéricos');
        return false;
      }
      
      // Validar longitud de contraseña
      if (contrasena.length < 6) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 6 caracteres');
        return false;
      }
      
      return true;
    });
    
    // Limpiar parámetros de la URL después de mostrar mensajes
    if (window.location.search) {
      window.history.replaceState({}, document.title, window.location.pathname);
    }
    </script>
  </body>
</html>