<?php
/**
 * LOGIN.PHP - Página de inicio de sesión
 * Este archivo reemplaza el login.html
 */

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
    <title>NENEMI-A - Iniciar Sesión</title>
    <link rel="stylesheet" href="/css/estilos.css"  />
    <link href="/imajenes/icono-alebrige.png" rel="icon" />
    <style>
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
      
      .mensaje-info {
        background-color: #e3f2fd;
        color: #1565c0;
        border: 2px solid #42a5f5;
      }
    </style>
  </head>
  <body>
    <div class="titulo-nenemia">
      <h1><i>BIENVENIDO</i></h1>
      <h1><i>A</i></h1>
      <h1><i>NENEMI-A</i></h1>
    </div>

    <?php
    // Mostrar mensaje si la sesión expiró
    if (isset($_GET['error']) && $_GET['error'] === 'sesion_expirada') {
        echo '<div class="mensaje mensaje-info">Tu sesión ha expirado. Por favor, inicia sesión nuevamente.</div>';
    }
    
    // Mostrar mensaje si se cerró sesión exitosamente
    if (isset($_GET['logout']) && $_GET['logout'] === 'exitoso') {
        echo '<div class="mensaje mensaje-exito">Has cerrado sesión exitosamente. ¡Hasta pronto!</div>';
    }
    
    // Mostrar mensajes de error
    if (isset($_SESSION['error'])) {
        echo '<div class="mensaje mensaje-error">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    
    // Mostrar errores de validación
    if (isset($_SESSION['errores']) && !empty($_SESSION['errores'])) {
        echo '<div class="mensaje mensaje-error">';
        echo '<strong>Por favor corrige los siguientes errores:</strong><br>';
        foreach ($_SESSION['errores'] as $error) {
            echo '• ' . htmlspecialchars($error) . '<br>';
        }
        echo '</div>';
        unset($_SESSION['errores']);
    }
    ?>

    <div class="contenedor">
      <h2>Iniciar Sesión</h2>
      
      <!-- El action apunta al archivo que procesa el login -->
      <form id="forma" name="forma" action="/procesar_login.php" method="POST">
        
        <div class="elemento">
          <label for="e-mail_registro">Tu e-mail:</label>
          <input 
            type="email" 
            id="e-mail_registro" 
            name="e-mail_registro" 
            required
            placeholder="ejemplo@correo.com"
            autofocus
          />
        </div>

        <div class="elemento">
          <label for="contrasena_registro">Tu contraseña:</label>
          <input 
            type="password" 
            id="contrasena_registro" 
            name="contrasena_registro" 
            required
            placeholder="Ingresa tu contraseña"
          />
        </div>

        <!-- Opción de recordar sesión (opcional, requiere implementación adicional) -->
        <div class="elemento" style="margin-bottom: 20px;">
          <label style="display: flex; align-items: center; color: #FEFCF7; cursor: pointer;">
            <input 
              type="checkbox" 
              name="recordar" 
              id="recordar"
              style="width: auto; margin-right: 10px;"
            />
            <span>Recordarme en este dispositivo</span>
          </label>
        </div>

        <div class="elemento">
          <input type="submit" value="Iniciar Sesión"/>
        </div>

        <!-- Enlace para recuperar contraseña (puedes implementarlo después) -->
        <div style="text-align: center; margin-top: 15px;">
          <a href="/html/recuperar_contrasena.php" style="color: #FEFCF7; text-decoration: underline; font-size: 14px;">
            ¿Olvidaste tu contraseña?
          </a>
        </div>
      </form>
    </div>

    <div class="boton-inicio-sesion">
      <p>¿No Estás Registrad@?</p>
      <p>No Te Preocupes</p>
      <p>Aqui Lo Puedes Hacer Rápido</p>
      <br />
      <a href="/index.php" id="boton-inicio-sesion-href">
        <button type="button" id="boton-inicio-de-sesion">Registro De Usuario</button>
      </a>
      <br />
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
    // Validación básica del formulario
    document.getElementById('forma').addEventListener('submit', function(e) {
      const email = document.getElementById('e-mail_registro').value.trim();
      const contrasena = document.getElementById('contrasena_registro').value;
      
      if (email === '' || contrasena === '') {
        e.preventDefault();
        alert('Por favor completa todos los campos');
        return false;
      }
      
      return true;
    });
    
    // Limpiar parámetros de la URL
    if (window.location.search) {
      setTimeout(function() {
        window.history.replaceState({}, document.title, window.location.pathname);
      }, 3000); // Limpiar después de 3 segundos para dar tiempo a ver el mensaje
    }
    </script>
  </body>
</html>