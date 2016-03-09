<?php
// The source code packaged with this file is Free Software, Copyright (C) 2005 by
// Ricardo Galli <gallir at uib dot es>.
// It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
// You can get copies of the licenses here:
//              http://www.affero.org/oagpl.html
// AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".

include('config.php');
include_once(mnminclude.'html1.php');
include_once(mnminclude.'recaptcha2.php');
include_once(mnminclude.'ban.php');
include_once(mnminclude.'Mailin.php');
define('SALT', 'A_SALT_HERE');
define('USER', decrypt($_GET['code'])); 

//echo urlencode($_GET['code']);

$globals['ads'] = false;
$user_invitator = '';

// Clean return variable
if(!empty($_REQUEST['return'])) {
	$_REQUEST['return'] = clean_input_string($_REQUEST['return']);
}

if ($current_user->user_id > 0) {
	if(!isset($_COOKIE['return_site'])) {
		$_COOKIE['return_site'] = get_server_name();
	}
	header("Location: http://".$_COOKIE['return_site'].get_user_uri($current_user->user_login));
	die;
}

if(isset($_POST["process"])) {
	$globals['secure_page'] = True;
} else {
	setcookie('return_site', get_server_name(), 0, $globals['base_url'], UserAuth::domain());
}

do_header_login(_("Regístrate en Yabber"), "post");

echo '<div class="genericform" style="background: transparent">'."\n";

if(isset($_POST["process"])) {
	switch (intval($_POST["process"])) {
		case 1:
			do_register1();
			break;
		case 2:
			do_register2();
			break;
	}
} else {
		do_register0();
	//Register by invitation
	// if (isset($_GET['code'])) {
 //    	if(check_invitation()){
 //    		do_register0();
 //    	}
	// }else{
 //    	register_error(_("Registro por invitación. Solo puedes acceder a través de otro usuario."));
	// 	$error=true;
	// 	die;
	// }
}

echo '</div>' . "\n";
do_footer();

exit;

function do_register0() {

	//echo '<script type="text/javascript">function enablebutton (button, button2, target) {var string = target.value;if (button2 != null) {button2.disabled = false;}if (string.length > 0) {button.disabled = false;} else {button.disabled = true;}}</script>';
	//echo '<div class="recoverpass" style="text-align:center"><h4><a href="login?op=recover">'._('¿has olvidado la contraseña?').'</a></h4></div>';

	echo '<form action="'.get_auth_link().'register" method="post" id="thisform" onSubmit="return check_checkfield(\'acceptlegal\', \''._('no has aceptado las condiciones de uso').'\')">' . "\n";
	echo '<fieldset class="login">' . "\n";
	echo '<legend><span class="sign">' . _("Registro") . '</span></legend>' . "\n";
	//echo '<p><label for="name">' . _("nombre de usuario") . ':</label><br />' . "\n";

	echo '<input class="login" placeholder="Nombre de usuario" type="text" name="username" id="name" value="" onkeyup="enablebutton(this.form.checkbutton1, this.form.submit, this)" size="25" tabindex="1"/>' . "\n";
	echo '<span id="checkit"><input style="margin-right: -62px" type="button" class="button" id="checkbutton1" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'username\', this.form, this.form.username)"/></span>' . "\n";
	echo '&nbsp;<p id="usernamecheckitvalue"></p></p>' . "\n";

	//echo '<p><label for="email">email:</label><br />' . "\n";
	//echo '<span class="note">'._('es importante que sea correcta, recibirás un correo para validar la cuenta').'</span> <br />';
	echo '<input class="login" type="text" placeholder="Correo electrónico" id="email" name="email" value=""  onkeyup="enablebutton(this.form.checkbutton2, this.form.submit, this)" size="25" tabindex="2"/>' . "\n";
		echo '<input style="margin-right: -62px" type="button" class="button" id="checkbutton2" disabled="disabled" value="'._('verificar').'" onclick="checkfield(\'email\', this.form, this.form.email)"/>' . "\n";
	echo '&nbsp;<p id="emailcheckitvalue"></p></p>' . "\n";

	//echo '<p><label for="password">' . _("clave") . ':</label><br />' . "\n";
	//echo '<span class="note">'._('al menos ocho caracteres, incluyendo mayúsculas, minúsculas y números').' </span><br />';
	echo '<input class="login" type="password" placeholder="Contraseña (Mayusc.+minusc.+numeros)" id="password" name="password" size="25" tabindex="3" onkeyup="return securePasswordCheck(this.form.password);"/><span id="password1-warning"></span></p>' . "\n";
	//echo '<p><label for="verify">' . _("verificación de clave") . ': </label><br />' . "\n";

	echo '<input class="login" type="password" placeholder="Repite la contraseña" id="verify" name="password2" size="25" tabindex="4" onkeyup="checkEqualFields(this.form.password2, this.form.password)"/></p>' . "\n";

	echo '<p><label><span class="note">'._('has leído y aceptas las ');
	do_legal(_('condiciones de uso'), 'target="_blank"', false);
	echo ' <input type="checkbox" id="acceptlegal" name="acceptlegal" value="accept" tabindex="5"/></span></label></p>' . "\n";

	echo '<p><input type="submit" class="button" disabled="disabled" name="submit" value="'._('crear usuario').'" class="log2" tabindex="6" /></p>' . "\n";
	echo '<input type="hidden" name="process" value="1"/>' . "\n";
	echo '<input type="hidden" name="user_invitator" value="'.USER.'"/>' . "\n";

	echo '<div style="margin-top: 20px" style="text-align:center">';
	print_oauth_icons($_REQUEST['return']);
	echo '</div>'."\n";

	echo '</fieldset>' . "\n";
	echo '</form>' . "\n";


}

function do_register1() {
	global $db, $globals;

	if($_POST["acceptlegal"] !== 'accept' ) {
		register_error(_("no has aceptado las condiciones de uso"));
		return;
	}
	if (!check_user_fields()) return;
	echo '<br style="clear:both" />';


	echo '<form action="'.get_auth_link().'register" method="post" id="thisform">' . "\n";
	echo '<fieldset class="login"><legend><span class="sign">'._('validación').'</span></legend>'."\n";
	ts_print_form();
	echo '<input type="submit" name="submit" class="button" value="'._('continuar').'" />';
	echo '<input type="hidden" name="process" value="2" />';
	echo '<input type="hidden" name="user_invitator" value="'.clean_input_string($_POST["user_invitator"]).'" />';
	echo '<input type="hidden" name="email" value="'.clean_input_string($_POST["email"]).'" />'; // extra sanity, in fact not needed
	echo '<input type="hidden" name="username" value="'.clean_input_string($_POST["username"]).'" />'; // extra sanity, in fact not needed
	echo '<input type="hidden" name="password" value="'.clean_input_string($_POST["password"]).'" />'; // extra sanity, in fact not needed
	echo '<input type="hidden" name="password2" value="'.clean_input_string($_POST["password2"]).'" />'; // extra sanity, in fact not needed
	echo '</fieldset></form>';
	// Add extra check: base_key is added on submit
	echo '<script type="text/javascript">addPostCode(function () { $("#thisform").submit(function () { $(this).append($("<input>", { type: "hidden", name: "base_key", value: base_key})); return true; });})</script>';
}

function do_register2() {
	global $db, $current_user, $globals;
	if ( !ts_is_human()) {
		register_error(_('el código de seguridad no es correcto'));
		return;
	}

	if (!check_user_fields())  return;

	// Extra check
	if (! check_security_key($_POST['base_key'])) {
		register_error(_('código incorrecto o pasó demasiado tiempo'));
		return;
	}

	$username=clean_input_string(trim($_POST['username'])); // sanity check
	$dbusername=$db->escape($username); // sanity check
	$password=UserAuth::hash(trim($_POST['password']));
	$email=clean_input_string(trim($_POST['email'])); // sanity check
	$dbemail=$db->escape($email); // sanity check
	$user_ip = $globals['user_ip'];
	if (!user_exists($username)) {
		if ($db->query("INSERT INTO users (user_login, user_login_register, user_email, user_email_register, user_pass, user_date, user_validated_date, user_ip) VALUES ('$dbusername', '$dbusername', '$dbemail', '$dbemail', '$password', now(), now(),'$user_ip')")) {
			//echo '<fieldset class="login">'."\n";
			//echo '<legend><span class="sign">'._("registro de usuario").'</span></legend>'."\n";
			$user=new User();
			$user->username=$username;
			if(!$user->read()) {
				register_error(_('error insertando usuario en la base de datos'));
			} else {
				// require_once(mnminclude.'mail.php');
				// $sent = send_recover_mail($user);
				// if ($sent) {
					User::increase_invitations(clean_input_string($_POST["user_invitator"]));
					$globals['user_ip'] = $user_ip; //we force to insert de log with the same IP as the form
					Log::insert('user_new', $user->id, $user->id);
					$mailin = new Mailin('https://api.sendinblue.com/v2.0','YOUR_API_KEY');
					$data = array( "email" => $email,
				        "attributes" => array("USERNAME"=>$user->username),
				        "listid" => array(5)
				    );
 
    				$mailin->create_update_user($data);
					syslog(LOG_INFO, "new user $user->id $user->username $email $user_ip");
				// } else {
				// 	register_error(_("error enviando el correo electrónico, seguramente está bloqueado"));
				// }
					//$current_user->Authenticate($user->username, false);
					//header('Location: http://titulares.in/comenzando');
			}
			$vars = compact('username');
			Haanga::Load('welcome.html', $vars);
			// echo '<p style="font-size: 30px"><strong>' ._ ('Bienvenido, ya formas parte de Yabber.') . '</strong></p>';
			// echo '<p style="font-size: 18px">Te recomendamos que te leas la <a href="/comenzando">guía de inicio</a> para saber de qué va esto y las opciones disponibles.</p>';
			// echo '<p style="font-size: 18px">Y no olvides presentarte en la <a href="/p/presentate">plataforma de presentaciones</a>! ;)';
			// echo '</fieldset>'."\n";
		} else {
			register_error(_("error insertando usuario en la base de datos"));
		}
	} else {
		register_error(_("el usuario ya existe"));
	}
}

function check_user_fields() {
	global $globals, $db;
	$error = false;

	if(check_ban_proxy()) {
		register_error(_("IP no permitida"));
		$error=true;
	}
	if(!isset($_POST["username"]) || strlen($_POST["username"]) < 3) {
		register_error(_("nombre de usuario erróneo, debe ser de 3 o más caracteres alfanuméricos"));
		$error=true;
	}
	if(!check_username($_POST["username"])) {
		register_error(_("nombre de usuario erróneo, caracteres no admitidos o no comienzan con una letra"));
		$error=true;
	}
	if(user_exists(trim($_POST["username"])) ) {
		register_error(_("el usuario ya existe"));
		$error=true;
	}
	if(!check_email(trim($_POST["email"]))) {
		register_error(_("el correo electrónico no es correcto"));
		$error=true;
	}
	if(email_exists(trim($_POST["email"])) ) {
		register_error(_("dirección de correo duplicada, o fue usada recientemente"));
		$error=true;
	}
	if(preg_match('/[ \']/', $_POST["password"]) || preg_match('/[ \']/', $_POST["password2"]) ) {
		register_error(_("caracteres inválidos en la clave"));
		$error=true;
	}
	if(! check_password($_POST["password"])) {
		register_error(_("clave demasiado corta, debe ser de 6 o más caracteres e incluir mayúsculas, minúsculas y números"));
		$error=true;
	}
	if($_POST["password"] !== $_POST["password2"] ) {
		register_error(_("las claves no coinciden"));
		$error=true;
	}


	if (empty($globals['skip_ip_register'])) {
		// Check registers from the same IP network
		$user_ip = $globals['user_ip'];
		$ip_classes = explode(".", $user_ip);

		// From the same IP
		$registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 24 hour) and log_type in ('user_new', 'user_delete') and log_ip = '$user_ip'");
		if($registered > 0) {
			syslog(LOG_NOTICE, "Meneame, register not accepted by IP address ($_POST[username]) $user_ip");
			register_error(_("para registrar otro usuario desde la misma dirección debes esperar 24 horas"));
			$error=true;
		}
		if ($error) return false;

		// Check class
		// nnn.nnn.nnn
		$ip_class = $ip_classes[0] . '.' . $ip_classes[1] . '.' . $ip_classes[2] . '.%';
		$registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 6 hour) and log_type in ('user_new', 'user_delete') and log_ip like '$ip_class'");
		if($registered > 0) {
			syslog(LOG_NOTICE, "Meneame, register not accepted by IP class ($_POST[username]) $ip_class");
			register_error(_("para registrar otro usuario desde la misma red debes esperar 6 horas"). " ($ip_class)");
			$error=true;
		}
		if ($error) return false;

		// Check class
		// nnn.nnn
		$ip_class = $ip_classes[0] . '.' . $ip_classes[1] . '.%';
		$registered = (int) $db->get_var("select count(*) from logs where log_date > date_sub(now(), interval 1 hour) and log_type in ('user_new', 'user_delete') and log_ip like '$ip_class'");
		if($registered > 2) {
			syslog(LOG_NOTICE, "Meneame, register not accepted by IP class ($_POST[username]) $ip_class");
			register_error(_("para registrar otro usuario desde la misma red debes esperar unos minutos") . " ($ip_class)");
			$error=true;
		}
		if ($error) return false;
	}

	if ($error) return false;
	return true;
}


function register_error($message) {
	echo '<div class="form-error">';
	echo "<p>$message</p>";
	echo "</div>\n";
}

function check_invitation(){
	//$coded1 = base64_encode($str);
	//$coded2 = base64_encode($coded1);
	$user_invitator=decrypt($_GET['code']);
	if(User::check_invitations_number($user_invitator)<1){
		register_error(_("El usuario que te ha invitado no dispone de invitaciones o invitación no válida"));
		$error=true;
		die;
	} else {
		return true;
	}
}

function decrypt($text) 
{ 
    return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, SALT, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))); 
} 

function encrypt($text) 
{ 
    return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SALT, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)))); 
} 

