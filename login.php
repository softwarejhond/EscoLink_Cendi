<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Extender vida de la sesión (1 día)
ini_set('session.gc_maxlifetime', 86400);
session_set_cookie_params(86400);

// Inicializar la sesión
session_start();

// Verificar si el usuario ya está autenticado
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("location: main.php");
    exit;
}

// Establecer tiempo de vida de la sesión en segundos
$inactividad = 86400;

// Comprobar si $_SESSION["timeout"] está establecida
if (isset($_SESSION["timeout"])) {
    $sessionTTL = time() - $_SESSION["timeout"];
    if ($sessionTTL > $inactividad) {
        session_unset();
        session_destroy();
        header("location: login.php");
        exit;
    }
}

$_SESSION["timeout"] = time();

require_once "conexion.php";

$username = $password = "";
$username_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (empty(trim($_POST["username"]))) {
        $username_err = "Por favor ingrese su usuario.";
    } else if (!filter_var(trim($_POST["username"]), FILTER_VALIDATE_INT)) {
        $username_err = "El usuario debe ser un número.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Por favor ingrese su contraseña.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (isset($_POST['rememberMe'])) {
        setcookie('username', $username, time() + (30 * 24 * 60 * 60), "/");
        setcookie('password', $password, time() + (30 * 24 * 60 * 60), "/");
    } else {
        setcookie('username', '', time() - 3600, "/");
        setcookie('password', '', time() - 3600, "/");
    }

    if (empty($username_err) && empty($password_err)) {
        $sql = "SELECT id, username, password, nombre, rol, foto, extra_rol, orden, email, genero, telefono, direccion, edad FROM users WHERE username = ? AND orden = 1";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) === 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $nombre, $rol, $foto, $extra_rol, $orden, $email, $genero, $telefono, $direccion, $edad);
                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            $_SESSION['loggedin'] = true;
                            $_SESSION['nombre'] = htmlspecialchars($nombre);
                            $_SESSION['rol'] = $rol;
                            $_SESSION['username'] = htmlspecialchars($username);
                            $_SESSION['foto'] = 'img/fotoUsuarios/' . htmlspecialchars($foto);
                            $_SESSION['extra_rol'] = $extra_rol;
                            $_SESSION['orden'] = $orden;

                            $campos_incompletos = false;
                            $campos_faltantes = array();

                            if (empty($email)) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Email";
                            }
                            if (empty($genero)) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Género";
                            }
                            if (empty($telefono)) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Teléfono";
                            }
                            if (empty($direccion)) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Dirección";
                            }
                            if (empty($edad) || $edad == 0) {
                                $campos_incompletos = true;
                                $campos_faltantes[] = "Edad";
                            }

                            $_SESSION['campos_incompletos'] = $campos_incompletos;
                            $_SESSION['campos_faltantes'] = $campos_faltantes;

                            header("location: main.php");
                            exit;
                        } else {
                            $password_err = "Contraseña incorrecta.";
                        }
                    }
                } else {
                    $sql_check = "SELECT orden FROM users WHERE username = ?";
                    if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
                        mysqli_stmt_bind_param($stmt_check, "s", $username);
                        if (mysqli_stmt_execute($stmt_check)) {
                            mysqli_stmt_store_result($stmt_check);
                            if (mysqli_stmt_num_rows($stmt_check) === 1) {
                                mysqli_stmt_bind_result($stmt_check, $user_orden);
                                mysqli_stmt_fetch($stmt_check);
                                if ($user_orden == 0) {
                                    $username_err = "Usuario deshabilitado. Contacte al administrador.";
                                } else {
                                    $username_err = "Usuario no existe.";
                                }
                            } else {
                                $username_err = "Usuario no existe.";
                            }
                        }
                        mysqli_stmt_close($stmt_check);
                    } else {
                        $username_err = "Usuario no existe.";
                    }
                }
            } else {
                echo "Algo salió mal, por favor vuelve a intentarlo.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}
include("conexion.php");
$queryCompany = mysqli_query($conn, "SELECT nombre,nit FROM company");
while ($empresaLog = mysqli_fetch_array($queryCompany)) {
    $empresa = $empresaLog['nombre'] . '</label>';
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escolink</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/contadores.css">
    <link rel="stylesheet" href="css/login1.css?v=3.0">
    <link rel="icon" href="img/logos/logo_escolink.png" type="image/x-icon">
    <style>
        @font-face {
            font-family: 'Sparose';
            src: url('css/fonts/fonnts.com-Sparose.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        #splash-screen {
            position: fixed;
            inset: 0;
            background: #0c0c1d;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        #splash-screen.hidden {
            opacity: 0;
            visibility: hidden;
        }
        #splash-screen .splash-logo {
            width: 220px;
            max-width: 70vw;
            animation: splashPulse 1.8s ease-in-out infinite;
        }
        @keyframes splashPulse {
            0%, 100% { transform: scale(1);   opacity: 1; }
            50%       { transform: scale(1.06); opacity: 0.8; }
        }
        .splash-dots {
            display: flex;
            gap: 8px;
            margin-top: 32px;
        }
        .splash-dots span {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--bs-cyan-dark);
            animation: splashBounce 1.2s ease-in-out infinite;
        }
        .splash-dots span:nth-child(2) { animation-delay: 0.2s; }
        .splash-dots span:nth-child(3) { animation-delay: 0.4s; }
        @keyframes splashBounce {
            0%, 80%, 100% { transform: translateY(0);    opacity: 0.4; }
            40%            { transform: translateY(-12px); opacity: 1; }
        }
    </style>
</head>

<body>
    <div id="vanta-bg"></div>

    <div id="splash-screen">
        <img src="./img/logos/logo_escolink_blanco.png" alt="Escolink" class="splash-logo">
        <div class="splash-dots">
            <span></span><span></span><span></span>
        </div>
    </div>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo-wrapper">
                <img src="./img/logos/logo_escolink_blanco.png" alt="Logo Escolink">
            </div>

            <h2 class="form-title">Iniciar sesión</h2>

            <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
                <div class="form-group">
                    <label for="username">Usuario</label>
                    <input type="text"
                        id="username"
                        name="username"
                        required
                        placeholder="Ingrese su número de documento"
                        value="<?php echo isset($_COOKIE['username']) ? htmlspecialchars($_COOKIE['username']) : ''; ?>">
                    <?php if (!empty($username_err)) : ?>
                        <div class="error-message"><?php echo $username_err ?></div>
                    <?php endif ?>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Ingrese su contraseña"
                            value="<?php echo isset($_COOKIE['password']) ? htmlspecialchars($_COOKIE['password']) : ''; ?>">
                        <button class="toggle-password" type="button" id="togglePassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <?php if (!empty($password_err)) : ?>
                        <div class="error-message"><?php echo $password_err ?></div>
                    <?php endif ?>
                </div>

                <div class="remember-row">
                    <input type="checkbox" id="rememberMe" name="rememberMe"
                        <?php echo isset($_COOKIE['username']) ? 'checked' : ''; ?>>
                    <label for="rememberMe">Recordarme</label>
                </div>

                <button type="submit" class="btn-submit" name="iniciar">
                    <span>Iniciar sesión</span>
                </button>
            </form>

            <div class="made-by-section">
                <small>
                    Made by <img src="img/eagle_blanco.svg" alt="Eagle Software"> <a href="https://www.agenciaeaglesoftware.com/" class="eagle-link-footer" target="_blank">Eagle Software</a> &copy; <?php echo date("Y"); ?>. <br>Todos los derechos reservados a <?php echo $empresa ?>.
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vanta@0.5.24/dist/vanta.dots.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>
    <script>
        window.addEventListener('load', function () {
            var splash = document.getElementById('splash-screen');
            setTimeout(function () {
                splash.classList.add('hidden');
                splash.addEventListener('transitionend', function () {
                    splash.remove();
                }, { once: true });
            }, 600);

            VANTA.DOTS({
                el: "#vanta-bg",
                mouseControls: true,
                touchControls: true,
                gyroControls: false,
                minHeight: 200.00,
                minWidth: 200.00,
                scale: 1.00,
                scaleMobile: 1.00,
                color: 0x30336b,
                color2: 0x007a7a,
                backgroundColor: 0x0c0c1d
            });
        });

        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    </script>
</body>

</html>
