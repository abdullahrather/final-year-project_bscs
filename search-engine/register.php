<?php
define("EW_PAGE_ID", "register", TRUE); // Page ID
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
$bUserExists = FALSE;

// Create form object
$objForm = new cFormObj();
if (@$_POST["a_register"] <> "") {

	// Get action
	$web_users->CurrentAction = $_POST["a_register"];
	LoadFormValues(); // Get form values
} else {
	$web_users->CurrentAction = "I"; // Display blank record
	LoadDefaultValues(); // Load default values
}
switch ($web_users->CurrentAction) {
	case "I": // Blank record, no action required
		break;
	case "A": // Add

		// Check for Duplicate User ID
		$sFilter = "(`userID` = '" . ew_AdjustSql($web_users->userID->CurrentValue) . "')";

		// Set up filter (Sql Where Clause) and get Return Sql
		// Sql constructor in web_users class, web_usersinfo.php

		$web_users->CurrentFilter = $sFilter;
		$sUserSql = $web_users->SQL();
		if ($rs = $conn->Execute($sUserSql)) {
			if (!$rs->EOF) {
				$bUserExists = TRUE;
				RestoreFormValues(); // Restore form values
				$_SESSION[EW_SESSION_MESSAGE] = "User Already Exists!"; // Set user exist message
			}
			$rs->Close();
		}
		if (!$bUserExists) {
			$web_users->SendEmail = TRUE; // Send email on add success
			if (AddRow()) { // Add record
				$_SESSION[EW_SESSION_MESSAGE] = "Registration Successful"; // Register success
				Page_Terminate("login.php"); // Go to login page
			} else {
				RestoreFormValues(); // Restore form values
			}
		}
}

// Render row
$web_users->RowType = EW_ROWTYPE_ADD; // Render add
RenderRow();
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "register"; // Page id

//-->
</script>
<script type="text/javascript">
<!--

function ew_ValidateForm(fobj) {
	if (fobj.a_confirm && fobj.a_confirm.value == "F")
		return true;
	var i, elm, aelm, infix;
	var rowcnt = (fobj.key_count) ? Number(fobj.key_count.value) : 1;
	for (i=0; i<rowcnt; i++) {
		infix = (fobj.key_count) ? String(i+1) : "";
		elm = fobj.elements["x" + infix + "_userID"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Please enter required field - Login ID"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_userEmail"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Please enter required field - Email Address"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_userEmail"];
		if (elm && !ew_CheckEmail(elm.value)) {
			if (!ew_OnError(elm, "Incorrect email - Email Address"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_userPassword"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Please enter required field - Password"))
				return false;
		}
		if (fobj.x_userPassword && !ew_HasValue(fobj.x_userPassword)) {
			if (!ew_OnError(fobj.x_userPassword, "Please enter password"))
				return false; 
		}
		if (fobj.c_userPassword.value != fobj.x_userPassword.value) {
			if (!ew_OnError(fobj.c_userPassword, "Mismatch Password"))
				return false; 
		}
	}
	return true;
}

//-->
</script>
<script type="text/javascript">
<!--

// js for DHtml Editor
//-->

</script>
<script type="text/javascript">
<!--

// js for Popup Calendar
//-->

</script>
<script type="text/javascript">
<!--
var ew_MultiPagePage = "Page"; // multi-page Page Text
var ew_MultiPageOf = "of"; // multi-page Of Text
var ew_MultiPagePrev = "Prev"; // multi-page Prev Text
var ew_MultiPageNext = "Next"; // multi-page Next Text

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<p><span class="phpmaker">
Registration Page<br><br>
<a href="login.php">Back to Login Page</a>
</span></p>
<?php 
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="fweb_usersregister" id="fweb_usersregister" action="register.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_register" id="a_register" value="A">
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Login ID<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $web_users->userID->CellAttributes() ?>><span id="cb_x_userID">
<input type="text" name="x_userID" id="x_userID" title="" size="30" maxlength="50" value="<?php echo $web_users->userID->EditValue ?>"<?php echo $web_users->userID->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">User Name</td>
		<td<?php echo $web_users->userName->CellAttributes() ?>><span id="cb_x_userName">
<input type="text" name="x_userName" id="x_userName" title="" size="30" maxlength="50" value="<?php echo $web_users->userName->EditValue ?>"<?php echo $web_users->userName->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Email Address<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $web_users->userEmail->CellAttributes() ?>><span id="cb_x_userEmail">
<input type="text" name="x_userEmail" id="x_userEmail" title="" size="30" maxlength="50" value="<?php echo $web_users->userEmail->EditValue ?>"<?php echo $web_users->userEmail->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Password<span class='ewmsg'>&nbsp;*</span></td>
		<td<?php echo $web_users->userPassword->CellAttributes() ?>><span id="cb_x_userPassword">
<input type="password" name="x_userPassword" id="x_userPassword" title="" value="<?php echo $web_users->userPassword->EditValue ?>" size="30" maxlength="50"<?php echo $web_users->userPassword->EditAttributes() ?>>
</span></td>
	</tr>
	<!--tr id=""-->
	<tr class="ewTableRow">
		<td class="ewTableHeader">Confirm Password</td>
		<td<?php echo $web_users->userPassword->CellAttributes() ?>>
<input type="password" name="c_userPassword" id="c_userPassword" title="" value="<?php echo $web_users->userPassword->EditValue ?>" size="30" maxlength="50"<?php echo $web_users->userPassword->EditAttributes() ?>>
</td>
	</tr>
</table>
<p>
<input type="submit" name="btnAction" id="btnAction" value=" Register ">
</form>
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

// Load default values
function LoadDefaultValues() {
	global $web_users;
}
?>
<?php

// Load form values
function LoadFormValues() {

	// Load from form
	global $objForm, $web_users;
	$web_users->userID->setFormValue($objForm->GetValue("x_userID"));
	$web_users->userName->setFormValue($objForm->GetValue("x_userName"));
	$web_users->userEmail->setFormValue($objForm->GetValue("x_userEmail"));
	$web_users->userPassword->setFormValue($objForm->GetValue("x_userPassword"));
}

// Restore form values
function RestoreFormValues() {
	global $web_users;
	$web_users->userID->CurrentValue = $web_users->userID->FormValue;
	$web_users->userName->CurrentValue = $web_users->userName->FormValue;
	$web_users->userEmail->CurrentValue = $web_users->userEmail->FormValue;
	$web_users->userPassword->CurrentValue = $web_users->userPassword->FormValue;
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $web_users;

	// Call Row Rendering event
	$web_users->Row_Rendering();

	// Common render codes for all row types
	// userID

	$web_users->userID->CellCssStyle = "";
	$web_users->userID->CellCssClass = "";

	// userName
	$web_users->userName->CellCssStyle = "";
	$web_users->userName->CellCssClass = "";

	// userEmail
	$web_users->userEmail->CellCssStyle = "";
	$web_users->userEmail->CellCssClass = "";

	// userPassword
	$web_users->userPassword->CellCssStyle = "";
	$web_users->userPassword->CellCssClass = "";
	if ($web_users->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($web_users->RowType == EW_ROWTYPE_ADD) { // Add row

		// userID
		$web_users->userID->EditCustomAttributes = "";
		$web_users->userID->EditValue = ew_HtmlEncode($web_users->userID->CurrentValue);

		// userName
		$web_users->userName->EditCustomAttributes = "";
		$web_users->userName->EditValue = ew_HtmlEncode($web_users->userName->CurrentValue);

		// userEmail
		$web_users->userEmail->EditCustomAttributes = "";
		$web_users->userEmail->EditValue = ew_HtmlEncode($web_users->userEmail->CurrentValue);

		// userPassword
		$web_users->userPassword->EditCustomAttributes = "";
		$web_users->userPassword->EditValue = ew_HtmlEncode($web_users->userPassword->CurrentValue);
	} elseif ($web_users->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($web_users->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$web_users->Row_Rendered();
}
?>
<?php

// Add record
function AddRow() {
	global $conn, $Security, $web_users;

	// Check for duplicate key
	$bCheckKey = TRUE;
	$sFilter = $web_users->SqlKeyFilter();
	if (trim(strval($web_users->userID->CurrentValue)) == "") {
		$bCheckKey = FALSE;
	} else {
		$sFilter = str_replace("@userID@", ew_AdjustSql($web_users->userID->CurrentValue), $sFilter); // Replace key value
	}
	if ($bCheckKey) {
		$rsChk = $web_users->LoadRs($sFilter);
		if ($rsChk && !$rsChk->EOF) {
			$_SESSION[EW_SESSION_MESSAGE] = "Duplicate value for primary key";
			$rsChk->Close();
			return FALSE;
		}
	}
	$rsnew = array();

	// Field userID
	$web_users->userID->SetDbValueDef($web_users->userID->CurrentValue, "");
	$rsnew['userID'] =& $web_users->userID->DbValue;

	// Field userName
	$web_users->userName->SetDbValueDef($web_users->userName->CurrentValue, NULL);
	$rsnew['userName'] =& $web_users->userName->DbValue;

	// Field userEmail
	$web_users->userEmail->SetDbValueDef($web_users->userEmail->CurrentValue, "");
	$rsnew['userEmail'] =& $web_users->userEmail->DbValue;

	// Field userPassword
	$web_users->userPassword->SetDbValueDef($web_users->userPassword->CurrentValue, "");
	$rsnew['userPassword'] =& $web_users->userPassword->DbValue;

	// Call Row Inserting event
	$bInsertRow = $web_users->Row_Inserting($rsnew);
	if ($bInsertRow) {
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$AddRow = $conn->Execute($web_users->InsertSQL($rsnew));
		$conn->raiseErrorFn = '';
	} else {
		if ($web_users->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $web_users->CancelMessage;
			$web_users->CancelMessage = "";
		} else {
			$_SESSION[EW_SESSION_MESSAGE] = "Insert cancelled";
		}
		$AddRow = FALSE;
	}
	if ($AddRow) {

		// Call Row Inserted event
		$web_users->Row_Inserted($rsnew);
	}
	return $AddRow;
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
