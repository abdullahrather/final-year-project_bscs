<?php
define("EW_PAGE_ID", "changepwd", TRUE); // Page ID
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
if (!$Security->IsLoggedIn()) $Security->AutoLogin();
if (!$Security->IsLoggedIn() || $Security->IsSysAdmin()) Page_Terminate("login.php");
$Security->LoadCurrentUserLevel("web_users");
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
if (@$_POST["submit"] <> "") {
	$bValidPwd = FALSE;
	$bPwdUpdated = FALSE;

	// Setup variables
	$sUsername = $Security->CurrentUserName();
	$sOPwd = ew_StripSlashes(@$_POST["opwd"]);
	$sNPwd = ew_StripSlashes(@$_POST["npwd"]);
	$sCPwd = ew_StripSlashes(@$_POST["cpwd"]);
	if ($sNPwd == $sCPwd) {
		$sFilter = "(`userID` = '" . ew_AdjustSql($sUsername) . "')";

		// Set up filter (Sql Where Clause) and get Return Sql
		// Sql constructor in web_users class, web_usersinfo.php

		$web_users->CurrentFilter = $sFilter;
		$sSql = $web_users->SQL();
		if ($rs = $conn->Execute($sSql)) {
			if (!$rs->EOF) {
				if ((EW_MD5_PASSWORD && md5($sOPwd) == $rs->fields('userPassword')) ||
					(!EW_MD5_PASSWORD && $sOPwd == $rs->fields('userPassword'))) {
					$rsnew = array('userPassword' => $sNPwd); // Change Password
					$rs->Close();
					$conn->raiseErrorFn = 'ew_ErrorFn';
					$bValidPwd = $conn->Execute($web_users->UpdateSQL($rsnew));
					$conn->raiseErrorFn = '';
					if ($bValidPwd)
						$bPwdUpdated = TRUE;
				}
			} else {
				$rs->Close();
			}
		}
	}
	if ($bPwdUpdated) {
		$_SESSION[EW_SESSION_MESSAGE] = "Password Changed"; // set up message
		Page_Terminate("index.php"); // exit page and clean up
	}
} else {
	$bValidPwd = TRUE;
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
<!-- start JavaScript

function  ew_ValidateForm(fobj) {
	if  (!ew_HasValue(fobj.opwd)) {
		if  (!ew_OnError(fobj.opwd, "Please enter password"))
			return false;
	}
	if  (!ew_HasValue(fobj.npwd)) {
		if  (!ew_OnError(fobj.npwd, "Please enter password"))
			return false;
	}
	if  (fobj.npwd.value != fobj.cpwd.value) {
		if  (!ew_OnError(fobj.cpwd, "Mismatch Password"))
			return false;
	}
	return true;
}

// end JavaScript -->
</script>
<p><span class="phpmaker">Change Password Page</span></p>
<?php if (@$_SESSION[EW_SESSION_MESSAGE] <> "") { ?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
} elseif (!$bValidPwd) { ?>
<p><span class="ewmsg">Invalid Password</span></p>
<?php } ?>
<form action="changepwd.php" method="post" onSubmit="return ew_ValidateForm(this);">
<table border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td><span class="phpmaker">Old Password</span></td>
		<td><span class="phpmaker"><input type="password" name="opwd" id="opwd" size="20"></span></td>
	</tr>
	<tr>
		<td><span class="phpmaker">New Password</span></td>
		<td><span class="phpmaker"><input type="password" name="npwd" id="npwd" size="20"></span></td>
	</tr>
	<tr>
		<td><span class="phpmaker">Confirm Password</span></td>
		<td><span class="phpmaker"><input type="password" name="cpwd" id="cpwd" size="20"></span></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><span class="phpmaker"><input type="submit" name="submit" id="submit" value="Change Password"></span></td>
	</tr>
</table>
</form>
<br>
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
