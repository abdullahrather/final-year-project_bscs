<?php
define("EW_PAGE_ID", "logout", TRUE); // Page ID
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
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
$bValidate = TRUE;
$sUsername = $Security->CurrentUserName();

// Call User LoggingOut event
$bValidate = User_LoggingOut($sUsername);
if (!$bValidate) {
	$sLastUrl = $Security->LastUrl();
	if ($sLastUrl == "") $sLastUrl = "index.php";
	Page_Terminate($sLastUrl); // Go to last accessed url
} else {
	if (@$_COOKIE[EW_PROJECT_NAME]['AutoLogin'] == "") { // Not autologin
		setCookie(EW_PROJECT_NAME . '[UserName]', ""); // clear user name cookie
	}
	setCookie(EW_PROJECT_NAME . '[Password]', ""); // clear password cookie
	setCookie(EW_PROJECT_NAME . '[LastUrl]', ""); // clear last url

	// Call User LoggedOut event
	User_LoggedOut($sUsername);

	// Unset all of the session variables
	$_SESSION = array();

	// Delete the session cookie and kill the session
	if (isset($_COOKIE[session_name()])) {
		setcookie(session_name(), '', time()-42000, '/');
	}

	// Finally, destroy the session
	@session_destroy();
	Page_Terminate("login.php"); // Go to login page
}
?>
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

// User Logging Out event
function User_LoggingOut($usr) {

	// Enter your code here
	// To cancel, set return value to False

	return TRUE;
}

// User Logged Out event
function User_LoggedOut($usr) {

	//echo "User Logged Out";
}
?>
