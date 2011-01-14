<?
/********************************************************
*
* -- This is a login form. You can either pass user name and pass here, or
*    it will ask for them. If it is passed, it will be logged in automaticly.
* -- Parameters it expects
*    - $login
*    - $password
*    - $url_error
*    - $url_success
*
********************************************************/

$output  = false;
$outside = true;
require("security.php");
require("../conf.php");

if ($_GET['r'] != '') $url_success = $_GET['r'];
$error  = "";

// if user name and password are passed
if (isset($login) && isset($pass)) {
	// ---
	if ($login != '' && $pass != '' && strtolower($def_users[strtolower($login)]) == strtolower($pass)) {
		// init session
		$_SESSION['ses_login']  	= $login;
		$_SESSION['ses_userid'] 	= 1;
		$_SESSION['ses_superuser']  = '';
		$_SESSION['ses_groups']  	= '';
		$_SESSION['ses_roles']  	= '';
		
		header("Location: $url_success");
		die();
	} else {
		$error = "Incorrect Login and/or Password";
		if ($url_error != "") {
			if (strpos($url_error, "?") > 0) $tmp_char = "?"; else $tmp_char = "&";
			$tmp_url = $url_error.$tmp_char."error=".$error;
			Header("Location: $tmp_url");
			die();
		}
		showLoginForm($error);
	}
} else {
	// display login form
	showLoginForm(); 
}

function showLoginForm($error='') {	
	global $url_error, $url_success;
	
	if ($url_success == "") {
		$url_success = "../index.php";
	}
	
	print("
		<title>Login</title>
		<style>
			table { font-family: verdana; font-size: 11px; }
			input { font-family: verdana; font-size: 11px; }
			span { font-family: verdana; font-size: 13px; }
		</style>
		<br>
		<center>
		
		".($error != "" ? "<span style='color: red'>$error</span><br><br>" : "<span>&nbsp;</span><br><br>")."

		<form method='post'>
			<input type=hidden name=url_error   value='$url_error'>
			<input type=hidden name=url_success value='$url_success'>
		<table style='box-shadow: 0px 0px 12px silver; -moz-box-shadow: 0px 0px 12px silver; -webkit-box-shadow: 0px 0px 12px silver; border: 1px solid #c3d6f1; background-image: url(images/bg_large.png); -moz-border-radius: 5px; -webkit-border-radius: 5px;'><tr><td>
			<table cellspacing=4 cellpadding=3 style='margin: 10px'>
			<tr>
				<td style='padding-bottom: 10px;'> 
					<span style='color: #555555;'>User Name</span> 
					<div style='height: 5px; font-size: 1px;'>&nbsp;</div> 
					<input type=text id=\"login\" name=\"login\" size=25 style=\"padding: 3px; height: 30px; border: 1px solid silver; font-size: 18px\"> 
				</td>
			</tr><tr>
				<td> 
					<span style='color: #555555;'>Password</span> 
					<div style='height: 5px; font-size: 1px;'>&nbsp;</div> 
					<input type=password id=\"pass\" name=\"pass\" size=25 style=\"padding: 3px; height: 30px; border: 1px solid silver; font-size: 18px\"> 
				</td>
			</tr><tr>
				<td></td>
			</tr><tr>
				<td style='padding-bottom: 0px;' align=center> 
					<input type=submit value=\"Login\" style=\"padding: 4px; padding-left: 15px; padding-right: 15px; font-size: 14px\"> 
				</td>
			</tr>
		</table>
		</td></tr></table>
		</form>
		</center>
		<script> document.getElementById('login').focus(); </script>
	");
}
?>