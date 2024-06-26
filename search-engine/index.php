<?php
define("EW_PAGE_ID", "index"); // Page ID
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
if (!$Security->IsLoggedIn()) $Security->AutoLogin();
if ($Security->IsLoggedIn()) {
	Page_Terminate("keywordslist.php"); // Exit and go to default page
}
if ($Security->IsLoggedIn()) {
	Page_Terminate("webpageslist.php");
}
if ($Security->IsLoggedIn()) {
	Page_Terminate("web_userslist.php");
}
if ($Security->IsLoggedIn()) {
?>
You do not have the right permission to view the page
<br>
<a href="logout.php">Back to Login Page</a>
<?php
} else {
	Page_Terminate("login.php"); // Exit and go to login page
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
?>
