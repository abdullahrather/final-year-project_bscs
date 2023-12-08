<?php
define("EW_PAGE_ID", "login", TRUE); // Page ID
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "web_usersinfo.php" ?>
<?php include "userfn50.php" ?>
<?php
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Always modified
header("Cache-Control: private, no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache"); // HTTP/1.0
?>
<?php

// Open connection to the database
$conn = ew_Connect();

?>
<?php
$Security = new cAdvancedSecurity();
?>
<?php

// Common page loading event (in userfn*.php)
Page_Loading();
?>
<?php

// Page load event, used in current page
Page_Load();
?>
<?php
$sLastUrl = $Security->LastUrl(); // Get Last Url
if ($sLastUrl == "") $sLastUrl = "index.php";
if (!$Security->IsLoggedIn()) $Security->AutoLogin();
$bValidate = FALSE;
if (@$_POST["submit"] <> "") {

	// Setup variables
	$sUsername = ew_StripSlashes(@$_POST["username"]);
	$sPassword = ew_StripSlashes(@$_POST["password"]);
	$sLoginType = strtolower(@$_POST["rememberme"]);
	$bValidate = TRUE;
} else {
	if ($Security->IsLoggedIn()) {
		if (@$_SESSION[EW_SESSION_MESSAGE] == "") Page_Terminate($sLastUrl); // Return to last accessed page
	}
}
if ($bValidate) {
	$bValidPwd = FALSE;

	// Call loggin in event
	$bValidate = User_LoggingIn($sUsername, $sPassword);
	if ($bValidate) {
		$bValidPwd = $Security->ValidateUser($sUsername, $sPassword);
		if (!$bValidPwd) $_SESSION[EW_SESSION_MESSAGE] = "Incorrect user ID or password"; // Invalid User ID/password
	} else {
		if (@$_SESSION[EW_SESSION_MESSAGE] == "") $_SESSION[EW_SESSION_MESSAGE] = "Login cancelled"; // Login cancelled
	}
	if ($bValidPwd) {

		// Write cookies
		$expirytime = time() + 365*24*60*60; // Change cookie expiry time here
		if ($sLoginType == "a") { // Auto login
			setcookie(EW_PROJECT_NAME . '[AutoLogin]',  "autologin", $expirytime); // Set up autologin cookies
			setcookie(EW_PROJECT_NAME . '[UserName]', $sUsername, $expirytime); // Set up user name cookies
			setcookie(EW_PROJECT_NAME . '[Password]', TEAencrypt($sPassword, EW_RANDOM_KEY), $expirytime); // Set up password cookies
		} elseif ($sLoginType == "u") { // Remember user name
			setcookie(EW_PROJECT_NAME . '[AutoLogin]', "rememberusername", $expirytime); // Set up remember user name cookies
			setcookie(EW_PROJECT_NAME . '[UserName]', $sUsername, $expirytime); // Set up user name cookies			
		} else {
			setcookie(EW_PROJECT_NAME . '[AutoLogin]', "", $expirytime); // Clear autologin cookies
		}

		// Call loggedin event
		User_LoggedIn($sUsername);
		Page_Terminate($sLastUrl); // Return to last accessed url
	}
}
?>
<?php include "header.php" ?>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<script type="text/javascript">
<!--

function ew_ValidateForm(fobj) {
	if (!ew_HasValue(fobj.username)) {
		if  (!ew_OnError(fobj.username, "Please enter user ID"))
			return false;
	}
	if (!ew_HasValue(fobj.password)) {
		if (!ew_OnError(fobj.password, "Please enter password"))
			return false;
	}
	return true;
}

//-->
</script>
<p><span class="phpmaker">Login Page</span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo @$_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="login.php" method="post" onSubmit="return ew_ValidateForm(this);">
<table border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td><span class="phpmaker">User Name</span></td>
		<td><span class="phpmaker"><input type="text" name="username" id="username" size="20" value="<?php echo @$_COOKIE[EW_PROJECT_NAME]['UserName'] ?>"></span></td>
	</tr>
	<tr>
		<td><span class="phpmaker">Password</span></td>
		<td><span class="phpmaker"><input type="password" name="password" id="password" size="20"></span></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><span class="phpmaker">
		<?php if (@$_COOKIE[EW_PROJECT_NAME]['AutoLogin'] == "autologin") { ?>
		<input type="radio" name="rememberme" id="rememberme" value="a" checked>Auto login until I logout explicitly<br><input type="radio" name="rememberme" id="rememberme" value="u">Save my user name<br><input type="radio" name="rememberme" id="rememberme" value="n">Always ask for my user name and password
		<?php } elseif (@$_COOKIE[EW_PROJECT_NAME]['AutoLogin'] == "rememberusername") { ?>
		<input type="radio" name="rememberme" id="rememberme" value="a">Auto login until I logout explicitly<br><input type="radio" name="rememberme" id="rememberme" value="u" checked>Save my user name<br><input type="radio" name="rememberme" id="rememberme" value="n">Always ask for my user name and password
		<?php } else { ?>
		<input type="radio" name="rememberme" id="rememberme" value="a">Auto login until I logout explicitly<br><input type="radio" name="rememberme" id="rememberme" value="u">Save my user name<br><input type="radio" name="rememberme" id="rememberme" value="n" checked>Always ask for my user name and password
		<?php } ?>
		</span></td>
	</tr>
	</tr>
	<tr>
		<td colspan="2" align="center"><span class="phpmaker"><input type="submit" name="submit" id="submit" value="   Login   "></span></td>
	</tr>
</table>
</form>
<br>
<p><span class="phpmaker">
<a href="forgetpwd.php">Forgot Password</a>&nbsp;&nbsp;&nbsp;&nbsp;
<a href="register.php">Register</a>&nbsp;&nbsp;&nbsp;&nbsp;
</span></p>
<script language="JavaScript" type="text/javascript">
<!--

// Write your startup script here
// document.write("page loaded");
//-->

</script>
<?php include "footer.php" ?>
<?php

// If control is passed here, simply terminate the page without redirect
Page_Terminate();

// -----------------------------------------------------------------
//  Subroutine Page_Terminate
//  - called when exit page
//  - clean up connection and objects
//  - if url specified, redirect to url, otherwise end response
function Page_Terminate($url = "") {
	global $conn;

	// Page unload event, used in current page
	Page_Unload();

	// Global page unloaded event (in userfn*.php)
	Page_Unloaded();

	 // Close Connection
	$conn->Close();

	// Go to url if specified
	if ($url <> "") {
		ob_end_clean();
		header("Location: $url");
	}
	exit();
}
?>
<?php

// Page Load event
function Page_Load() {

	//echo "Page Load";
}

// Page Unload event
function Page_Unload() {

	//echo "Page Unload";
}
?>
<?php

// User Logging In event
function User_LoggingIn($usr, $pwd) {

	// Enter your code here
	// To cancel, set return value to False

	return TRUE;
}

// User Logged In event
function User_LoggedIn($usr) {

	//echo "User Logged In";
}
?>
