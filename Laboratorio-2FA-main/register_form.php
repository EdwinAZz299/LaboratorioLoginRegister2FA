<!DOCTYPE html>
<html lang="es">
<head>

<meta name="Description" content="Registro de Usuario" />
<meta name="Keywords" content="registro, usuario, nuevo" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="Distribution" content="Global" />
<meta name="Author" content="Irina Fong - dreamsweb7@gmail.com" />
<meta name="Robots" content="index,follow" />

<script src="jquery/jquery-latest.js" type="text/javascript"></script> 
<script src="jquery/jquery.validate.js"  type="text/javascript"></script>
<link rel="shortcut icon"  href="patria/5564844.png">

<link rel="stylesheet" href="css/cmxform.css" type="text/css" />
<link rel="stylesheet" href="Estilos/Techmania.css" type="text/css" />
<link rel="stylesheet" href="Estilos/general.css"   type="text/css">
<title>Registro de Nuevo Usuario</title>

<script type="text/javascript">
  $(document).ready(function(){
    $("#registroUser").validate({
 		 rules: {
            nombre: "required",
            apellido: "required",
    		correo: {
                required: true,
                email: true
            },
			contrasena: {
                required: true,
                minlength: 6
            },
            confirm_contrasena: {
                required: true,
                equalTo: "#contrasena"
            },
            sexo: "required"
		 }
	});
 });
</script>
  
<style>
.alerta-error{
    background-color: #fff9f9;
    color: #6a0e0e;
    border: 1px solid #eee;
    border-left: 5px solid #d32f2f;
    border-radius: 4px; 
    padding: 12px 18px; 
    margin: 10px auto; 
    display: flex;
    align-items: center;
    max-width: 450px;
    font-size: 15px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
</style>

</head>

<body>
<div id="wrap">
  <div id="headerlogin"></div>
  <p>
    <a href="login.php"><img src="img/regresar.gif" alt="Atr&aacute;s" width="90" height="30" longdesc="login.php" /></a></p>

              <?php
                session_start();
                $csrf_token = bin2hex(random_bytes(32));
                $_SESSION['csrf_token'] = $csrf_token;
                ?>

   <div align="center">
    <form  class="cmxform" id="registroUser"  name="registroUser" method="post" action="register.php">
           <br />
          <input type="hidden" name="tolog"  id="tolog"  value="<?php echo $csrf_token; ?>">
          <table width="89%" border="0" align="center">
            <tr>
              <td height="19" colspan="2"  align="center">Registro de Usuario | UTP</td></tr>
            <tr>
              <td width="25%">Nombre:</td>
              <td width="42%"><input  id="nombre" name="nombre" type="text" minlength="2" /></td>
            </tr>
            <tr>
              <td width="25%">Apellido:</td>
              <td width="42%"><input  id="apellido" name="apellido" type="text" minlength="2" /></td>
            </tr>
            <tr>
              <td>Correo:</td>
              <td><input  id="correo" name="correo" type="text" minlength="4" /></td>
            </tr>
            <tr>
              <td>Contrase&ntilde;a:</td>
              <td><input  id="contrasena" name="contrasena" type="password" />
              <span id="toggleContrasena" 
              style="position:absolute; right:8px; top:5px; cursor:pointer; user-select:none;">👁️</span></td>
            </tr>
            <tr>
              <td>Confirmar Contrase&ntilde;a:</td>
              <td><input  id="confirm_contrasena" name="confirm_contrasena" type="password" /></td>
            </tr>
            <tr>
                <td>Sexo:</td>
                <td>
                    <select id="sexo" name="sexo">
                        <option value="">Seleccione</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                </td>
            </tr>
			      <tr>
                    <td colspan="2" align="center">                     
                        <div align="center"><input name="Submit" type="submit" class="clear" value="Registrar" /></div>
	        </tr>
            
      <div id="error"><font color="#FF0000">
      <?php
        if (!empty($_SESSION["reg_msg"])) {
         echo '<div class="alerta-error">';
         echo '<strong>¡Error de Registro!</strong> ' . $_SESSION["reg_msg"];
         echo '</div>';
        unset($_SESSION["reg_msg"]);
        }
      ?>
      </font>
      <br />
      <br />
      <br />
      </div>
      </table><br />
    </form></div>
    <br />

  
  <?PHP include("comunes/footer.php");?>
</div>

<script>
const toggle = document.getElementById('toggleContrasena');
const input = document.getElementById('contrasena');

toggle.addEventListener('click', () => {
  const isPassword = input.type === 'password';
  input.type = isPassword ? 'text' : 'password';
  toggle.textContent = isPassword ? '🙈' : '👁️';
});
</script>
</body>
</html>
