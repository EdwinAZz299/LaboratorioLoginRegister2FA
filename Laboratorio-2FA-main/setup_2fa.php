<?PHP
session_start();
use Sonata\GoogleAuthenticator\GoogleQrUrl; // Use Sonata's GoogleQrUrl

include("clases/SonataGoogleAuthenticator/GoogleQrUrl.php"); // Include the 2FA QR URL generator
include("comunes/loginfunciones.php"); // For redireccionar function

if (!isset($_SESSION['2fa_secret']) || !isset($_SESSION['2fa_email'])) {
    redireccionar("login.php"); // Redirect if session data is not set
    exit;
}

$secret = $_SESSION['2fa_secret'];
$email = $_SESSION['2fa_email'];
$appName = "MiAppLogin"; // Replace with your application name

$otpAuthUrl = GoogleQrUrl::generate($email, $secret, $appName); // Use Sonata's GoogleQrUrl
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpAuthUrl);

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta name="Description" content="Configuración 2FA" />
<meta name="Keywords" content="2FA, Google Authenticator, seguridad" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Configuración de Verificación en 2 Pasos</title>
<link rel="stylesheet" href="css/cmxform.css" type="text/css" />
<link rel="stylesheet" href="Estilos/Techmania.css" type="text/css" />
<link rel="stylesheet" href="Estilos/general.css"   type="text/css">
<style>
.container {
    max-width: 600px;
    margin: 50px auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #f9f9f9;
    text-align: center;
}
.qr-code {
    margin: 20px 0;
}
.secret-key {
    font-size: 1.2em;
    font-weight: bold;
    color: #333;
    margin-bottom: 20px;
}
.instructions {
    text-align: left;
    margin-bottom: 20px;
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
    <h2>Configuración de Verificación en 2 Pasos (2FA)</h2>
    <p>Para habilitar la verificación en 2 pasos, escanee el siguiente código QR con su aplicación Google Authenticator (o similar).</p>
    <div class="qr-code">
        <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="QR Code">
    </div>
    <p>O introduzca la clave manualmente:</p>
    <div class="secret-key">
        Clave Secreta: <?php echo $secret; ?>
    </div>
    <div class="instructions">
        <ol>
            <li>Abra su aplicación Google Authenticator.</li>
            <li>Toque el signo '+' y seleccione "Escanear un código QR" o "Introducir clave de configuración".</li>
            <li>Escanee el código QR de arriba o introduzca la clave secreta manualmente.</li>
            <li>Una vez configurado, puede proceder a iniciar sesión.</li>
        </ol>
    </div>
    <a href="login.php" class="button">Ir a Iniciar Sesión</a>
</div>
</body>
</html>
