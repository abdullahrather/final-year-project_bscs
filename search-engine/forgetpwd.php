<?php
define("EW_PAGE_ID", "forgetpwd", TRUE); // Page ID
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
$bValidEmail = FALSE;
$sEmail = "";
if (@$_POST["email"] <> "") {
	$bValidEmail = FALSE;

	// Setup variables
	$sEmail = ew_StripSlashes($_POST["email"]);

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in web_users class, web_usersinfo.php

	$sFilter = "`userEmail` = '" . ew_AdjustSql($sEmail) . "'";
	$web_users->CurrentFilter = $sFilter;
	$sSql = $web_users->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if (!$rs->EOF) {
			$sUserName = $rs->fields('userID');
			$sPassword = $rs->fields('userPassword');
			if (EW_MD5_PASSWORD) {
				$rsnew = array('userPassword' => $sPassword); // Reset the password
				$conn->Execute($web_users->UpdateSQL($rsnew));
			}
			$bValidEmail = TRUE;
		}
		$rs->Close();
	}
	if ($bValidEmail) {
		$Email = new cEmail();
		$Email->Load("txt/forgetpwd.txt");
		$Email->ReplaceSender(EW_SENDER_EMAIL); // Replace Sender
		$Email->ReplaceRecipient($sEmail); // Replace Recipient
		$Email->ReplaceContent('<!--$UserName-->', $sUserName);
		$Email->ReplaceContent('<!--$Password-->', $sPassword);
		if ($Email->Send()) {
			$_SESSION[EW_SESSION_MESSAGE] = "Password sent to your email"; // Set success message
			Page_Terminate("login.php"); // Return to login page
		} else {
			$_SESSION[EW_SESSION_MESSAGE] = "Failed to send mail"; // set up error message
		}
	}
} else {
	$bValidEmail = TRUE;
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
	if (!ew_HasValue(fobj.email)) {
		if (!ew_OnError(fobj.email, "Please enter valid Email Address!"))
			return false;
	}
	if (!ew_CheckEmail(fobj.email.value)) {
		if (!ew_OnError(fobj.email, "Please enter valid Email Address!"))
			return false;
	}
	return true;
}

// end JavaScript -->
</script>
<p><span class="phpmaker">
Request Password Page<br><br>
<a href="login.php">Back to Login Page</a>
</span></p>
<?php if (@$_SESSION[EW_SESSION_MESSAGE] <> "") { ?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
} elseif (!$bValidEmail) { ?>
<p><span class="ewmsg">Invalid Email</span></p>
<?php } ?>
<form action="forgetpwd.php" method="post" onSubmit="return ew_ValidateForm(this);">
<table border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td><span class="phpmaker">User Email</span></td>
		<td><span class="phpmaker"><input type="text" name="email" id="email" value="<?php echo ew_HtmlEncode($sEmail) ?>" size="30" maxlength="50"></span></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><span class="phpmaker"><input type="submit" name="submit" id="submit" value="Send Password"></span></td>
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
