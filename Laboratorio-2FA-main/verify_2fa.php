<?PHP
session_start();
use Sonata\GoogleAuthenticator\GoogleAuthenticator; // Use Sonata's GoogleAuthenticator

include("clases/SonataGoogleAuthenticator/GoogleAuthenticator.php"); // Include the 2FA library
include("clases/mysql.inc.php");
include("comunes/loginfunciones.php"); // For redireccionar function

$db = new mod_db();
$g = new GoogleAuthenticator(); // Instantiate Sonata's Google Authenticator

if (!isset($_SESSION['2fa_required']) || !$_SESSION['2fa_required'] || !isset($_SESSION['user_id']) || !isset($_SESSION['user_correo'])) {
    redireccionar("login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_correo = $_SESSION['user_correo'];

// Fetch the secret_2fa from the database for the user
$stmt = $db->getConexion()->prepare("SELECT secret_2fa FROM usuarios WHERE id = :id");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_OBJ);

if (!$user || empty($user->secret_2fa)) {
    // Should not happen if 2fa_required is true, but as a fallback
    redireccionar("login.php");
    exit;
}

$secret_2fa = $user->secret_2fa;

// Handle 2FA code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['2fa_code'] ?? '';

    if ($g->checkCode($secret_2fa, $code)) { // Use Sonata's checkCode()
        // 2FA code is correct, log the user in
        $_SESSION['autenticado'] = "SI";
        $_SESSION['Usuario'] = $user_correo;
        unset($_SESSION['2fa_required']);
        unset($_SESSION['user_id']);
        unset($_SESSION['user_correo']);
        redireccionar("formularios/PanelControl.php");
    } else {
        $_SESSION['2fa_error'] = "Código 2FA incorrecto. Inténtelo de nuevo.";
        redireccionar("verify_2fa.php"); // Redirect back to the same page with error
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta name="Description" content="Verificación 2FA" />
<meta name="Keywords" content="2FA, Google Authenticator, seguridad" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Verificación en 2 Pasos</title>
<link rel="stylesheet" href="css/cmxform.css" type="text/css" />
<link rel="stylesheet" href="Estilos/Techmania.css" type="text/css" />
<link rel="stylesheet" href="Estilos/general.css"   type="text/css">
<style>
.container {
    max-width: 400px;
    margin: 50px auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
    text-align: center;
}
.error-message {
    color: red;
    margin-bottom: 10px;
}
.button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    margin-top: 10px;
}
</style>
</head>
<body>
<div class="container">
    <h2>Verificación en 2 Pasos</h2>
    <p>Por favor, introduzca el código de 6 dígitos de su aplicación Google Authenticator.</p>
    <form method="POST" action="verify_2fa.php">
        <input type="text" name="2fa_code" placeholder="Código 2FA" required autofocus maxlength="6" pattern="\d{6}">
        <br><br>
        <button type="submit" class="button">Verificar</button>
    </form>
    <?php
    if (isset($_SESSION['2fa_error'])) {
        echo '<p class="error-message">' . $_SESSION['2fa_error'] . '</p>';
        unset($_SESSION['2fa_error']);
    }
    ?>
</div>
</body>
</html>
