<?PHP
session_start();  
include ("clases/mysql.inc.php");	
$db = new mod_db();



include("clases/SanitizarEntrada.php");
include("comunes/loginfunciones.php");
include("clases/objLoginAdmin.php");

	
$tokenizado=false;
 

// $topanel=false;
// Obtener tokens con seguridad y comprobar que existen
$token_enviado = $_POST['tolog'] ?? '';
$token_almacenado = $_SESSION['csrf_token'] ?? '';

// Verificar que ambos tokens no estén vacíos antes de comparar
if ($token_enviado !== '' && $token_almacenado !== '' && hash_equals($token_almacenado, $token_enviado)) {
    $tokenizado = true;
} else {
	$tokenizado = false;
	// Registro de depuración: token CSRF no coincide o está vacío
	$sess = session_id();
	$stored = substr($token_almacenado,0,8);
	$sent = substr($token_enviado,0,8);
	$Usuario = $_POST['usuario']??'';

	error_log("[CSRF] mismatch. session_id={$sess} stored_prefix={$stored} sent_prefix={$sent} user={$Usuario}");
}
	
 
// 2. VERIFICAR QUE LA SOLICITUD ES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenizado) {

		//echo "<pre>";
		//var_dump($_SERVER);
		//echo"</pre>";

             
			$correo = SanitizarEntrada::limpiarEmail($_POST['correo'] ?? '');
			$ClaveKey = (string) ($_POST['contrasena'] ?? '');
			//echo "3l correo es: ".$correo."<br>";
			//echo "3l ClaveKey es: ".$ClaveKey."<br>";

			echo "La dirección IP es ".$_SERVER['REMOTE_ADDR'];
			$ipRemoto = $_SERVER['REMOTE_ADDR'];

			$Logearme = new ValidacionLogin($correo, $ClaveKey,$ipRemoto, $db); // Pass correo
			
		
			if ($Logearme->logger()){
					$Logearme->autenticar();
				if ($Logearme->getIntentoLogin()){
					//echo "Se ha loggeado el usuario satisfactoriamente <br>";
					//Comenzar a Crear las SESIONES
					// Fetch user details including secret_2fa
					$stmt = $db->getConexion()->prepare("SELECT id, correo, secret_2fa FROM usuarios WHERE correo = :correo");
					$stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
					$stmt->execute();
					$user = $stmt->fetch(PDO::FETCH_OBJ);

					if ($user && !empty($user->secret_2fa)) {
						// 2FA is enabled, redirect to 2FA verification page
						$_SESSION['user_id'] = $user->id;
						$_SESSION['user_correo'] = $user->correo;
						$_SESSION['2fa_required'] = true;
						redireccionar("verify_2fa.php");
					} else {
						// No 2FA or 2FA not enabled, proceed to panel
						$_SESSION['autenticado']= "SI";
						$_SESSION['Usuario']= $Logearme->getCorreo(); // Get correo
						
						if (!$Logearme->registrarIntentos()) {
							error_log("Fallo al registrar intento de login para usuario: " . $correo); // Use correo
						}
						$tokenizado=false;
						redireccionar("formularios/PanelControl.php");
					}
					// Si es exitoso puedo guardar en la base de datos el intento 
					//  y desde que ip
					// Sino lo es también debo guardar el IP
				}else {

					if (!$Logearme->registrarIntentos()) {
						error_log("Fallo al registrar intento de login para usuario: " . $correo); // Use correo
					}
					
					
					 $tokenizado=false;
					$_SESSION["emsg"] =1;
					// asegurar que la sesión se guarde antes de redirigir
					session_write_close();
					//echo "ocurrió un error ";
					 redireccionar("login.php");		
				}
			}else {

				if (!$Logearme->registrarIntentos()) {
						error_log("Fallo al registrar intento de login para usuario: " . $correo); // Use correo
				}
				//echo "hola como estas logger <br>";
				$_SESSION["emsg"] =1;
				// asegurar que la sesión se guarde antes de redirigir
				session_write_close();
				redireccionar("login.php");
			}

			
	    
    } else {
		//echo "hola como estas<br>";
		 $tokenizado=false;
		$_SESSION["emsg"] =1;
		redireccionar("login.php");
	}


?>
