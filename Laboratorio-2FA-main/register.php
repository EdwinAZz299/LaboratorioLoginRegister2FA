<?PHP
session_start();
include("clases/mysql.inc.php");
include("clases/SanitizarEntrada.php");
include("clases/RegistroUsuario.php");
include("comunes/loginfunciones.php"); // Assuming this has the redireccionar function
use Sonata\GoogleAuthenticator\GoogleAuthenticator; // Use Sonata's GoogleAuthenticator
use Sonata\GoogleAuthenticator\GoogleQrUrl; // Use Sonata's GoogleQrUrl

include("clases/SonataGoogleAuthenticator/GoogleAuthenticator.php"); // Include the 2FA library
include("clases/SonataGoogleAuthenticator/GoogleQrUrl.php"); // Include the 2FA QR URL generator

$db = new mod_db();
$g = new GoogleAuthenticator(); // Instantiate Sonata's Google Authenticator
$registro = new RegistroUsuario($db, $g);

$tokenizado = false;

// Obtener tokens con seguridad y comprobar que existen
$token_enviado = $_POST['tolog'] ?? '';
$token_almacenado = $_SESSION['csrf_token'] ?? '';

// Verificar que ambos tokens no estén vacíos antes de comparar
if ($token_enviado !== '' && $token_almacenado !== '' && hash_equals($token_almacenado, $token_enviado)) {
    $tokenizado = true;
} else {
    $_SESSION["reg_msg"] = "Error de seguridad: Token CSRF inválido.";
    redireccionar("register_form.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenizado) {
    $datos = $registro->prepararDatos($_POST);
    $errores = $registro->validarDatos($datos);

    if (!empty($errores)) {
        $_SESSION["reg_msg"] = implode(' ', $errores);
        redireccionar("register_form.php");
        exit;
    }

    if (!$registro->correoDisponible($datos['correo'])) {
        $_SESSION["reg_msg"] = "El correo electrónico ya está registrado.";
        redireccionar("register_form.php");
        exit;
    }

    $resultado = $registro->crearUsuario($datos);

    if ($resultado['exito']) {
        $_SESSION["reg_msg"] = "¡Registro exitoso! Por favor, configure su 2FA.";
        $_SESSION['2fa_secret'] = $resultado['secret'];
        $_SESSION['2fa_email'] = $datos['correo'];
        redireccionar("setup_2fa.php"); // Redirect to 2FA setup page
    } else {
        $_SESSION["reg_msg"] = "Error al registrar el usuario. Inténtelo de nuevo.";
        redireccionar("register_form.php");
    }

} else {
    $_SESSION["reg_msg"] = "Acceso no autorizado.";
    redireccionar("register_form.php");
}
?>
