<?php
define("EW_PAGE_ID", "search", TRUE); // Page ID
define("EW_TABLE_NAME", 'webpages', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "webpagesinfo.php" ?>
<?php include "userfn50.php" ?>
<?php include "web_usersinfo.php" ?>
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
if (!$Security->IsLoggedIn()) {
	$Security->SaveLastUrl();
	Page_Terminate("login.php");
}
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
$webpages->Export = @$_GET["export"]; // Get export parameter
$sExport = $webpages->Export; // Get export parameter, used in header
$sExportFile = $webpages->TableVar; // Get export file, used in header
?>
<?php

// Get action
$webpages->CurrentAction = @$_POST["a_search"];
switch ($webpages->CurrentAction) {
	case "S": // Get Search Criteria

		// Build search string for advanced search, remove blank field
		$sSrchStr = BuildAdvancedSearch();
		if ($sSrchStr <> "") {
			Page_Terminate("webpageslist.php?" . $sSrchStr); // Go to list page
		}
		break;
	default: // Restore search settings
		LoadAdvancedSearch();
}

// Render row for search
$webpages->RowType = EW_ROWTYPE_SEARCH;
RenderRow();
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "search"; // Page id

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
		elm = fobj.elements["x" + infix + "_webURLFreq"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Incorrect integer - URL Frequency"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_Date_Of_Crawling"];
		if (elm && !ew_CheckUSDate(elm.value)) {
			if (!ew_OnError(elm, "Incorrect date, format = mm/dd/yyyy - Date Of Crawling"))
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
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<p><span class="phpmaker">Search TABLE: Web Links<br><br><a href="webpageslist.php">Back to List</a></span></p>
<form name="fwebpagessearch" id="fwebpagessearch" action="webpagessrch.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_search" id="a_search" value="S">
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">Web URL</td>
		<td<?php echo $webpages->webURL->CellAttributes() ?>><span class="ewSearchOpr">contains<input type="hidden" name="z_webURL" id="z_webURL" value="LIKE"></span></td>
		<td<?php echo $webpages->webURL->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_webURL" id="x_webURL" title="" size="30" maxlength="200" value="<?php echo $webpages->webURL->EditValue ?>"<?php echo $webpages->webURL->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Web Title</td>
		<td<?php echo $webpages->webTitle->CellAttributes() ?>><span class="ewSearchOpr">contains<input type="hidden" name="z_webTitle" id="z_webTitle" value="LIKE"></span></td>
		<td<?php echo $webpages->webTitle->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_webTitle" id="x_webTitle" title="" size="30" maxlength="200" value="<?php echo $webpages->webTitle->EditValue ?>"<?php echo $webpages->webTitle->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">URL Frequency</td>
		<td<?php echo $webpages->webURLFreq->CellAttributes() ?>><span class="ewSearchOpr"><select name="z_webURLFreq" id="z_webURLFreq"><option value="="<?php echo ($webpages->webURLFreq->AdvancedSearch->SearchOperator=="=")?" selected":"" ?>>=</option><option value="<>"<?php echo ($webpages->webURLFreq->AdvancedSearch->SearchOperator=="<>")?" selected":"" ?>><></option><option value="<"<?php echo ($webpages->webURLFreq->AdvancedSearch->SearchOperator=="<")?" selected":"" ?>><</option><option value="<="<?php echo ($webpages->webURLFreq->AdvancedSearch->SearchOperator=="<=")?" selected":"" ?>><=</option><option value=">"<?php echo ($webpages->webURLFreq->AdvancedSearch->SearchOperator==">")?" selected":"" ?>>></option><option value=">="<?php echo ($webpages->webURLFreq->AdvancedSearch->SearchOperator==">=")?" selected":"" ?>>>=</option></select></span></td>
		<td<?php echo $webpages->webURLFreq->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_webURLFreq" id="x_webURLFreq" title="" size="10" value="<?php echo $webpages->webURLFreq->EditValue ?>"<?php echo $webpages->webURLFreq->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">Date Of Crawling</td>
		<td<?php echo $webpages->Date_Of_Crawling->CellAttributes() ?>><span class="ewSearchOpr"><select name="z_Date_Of_Crawling" id="z_Date_Of_Crawling"><option value="="<?php echo ($webpages->Date_Of_Crawling->AdvancedSearch->SearchOperator=="=")?" selected":"" ?>>=</option><option value="<>"<?php echo ($webpages->Date_Of_Crawling->AdvancedSearch->SearchOperator=="<>")?" selected":"" ?>><></option><option value="<"<?php echo ($webpages->Date_Of_Crawling->AdvancedSearch->SearchOperator=="<")?" selected":"" ?>><</option><option value="<="<?php echo ($webpages->Date_Of_Crawling->AdvancedSearch->SearchOperator=="<=")?" selected":"" ?>><=</option><option value=">"<?php echo ($webpages->Date_Of_Crawling->AdvancedSearch->SearchOperator==">")?" selected":"" ?>>></option><option value=">="<?php echo ($webpages->Date_Of_Crawling->AdvancedSearch->SearchOperator==">=")?" selected":"" ?>>>=</option></select></span></td>
		<td<?php echo $webpages->Date_Of_Crawling->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_Date_Of_Crawling" id="x_Date_Of_Crawling" title="" size="10" value="<?php echo $webpages->Date_Of_Crawling->EditValue ?>"<?php echo $webpages->Date_Of_Crawling->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Meta Content</td>
		<td<?php echo $webpages->meta_content->CellAttributes() ?>><span class="ewSearchOpr">contains<input type="hidden" name="z_meta_content" id="z_meta_content" value="LIKE"></span></td>
		<td<?php echo $webpages->meta_content->CellAttributes() ?>><span class="phpmaker">
<textarea name="x_meta_content" id="x_meta_content" cols="35" rows="4"<?php echo $webpages->meta_content->EditAttributes() ?>><?php echo $webpages->meta_content->EditValue ?></textarea>
</span></td>
	</tr>
</table>
<p>
<input type="submit" name="Action" id="Action" value="  Search  ">
<input type="button" name="Reset" id="Reset" value="   Reset   " onclick="ew_ClearForm(this.form);">
</form>
<script language="JavaScript" type="text/javascript">
<!--

// Write your table-specific startup script here
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

// Build advanced search
function BuildAdvancedSearch() {
	global $webpages;
	$sSrchUrl = "";

	// Field webURL
	BuildSearchUrl($sSrchUrl, $webpages->webURL, @$_POST["x_webURL"], @$_POST["z_webURL"], @$_POST["v_webURL"], @$_POST["y_webURL"], @$_POST["w_webURL"]);

	// Field webTitle
	BuildSearchUrl($sSrchUrl, $webpages->webTitle, @$_POST["x_webTitle"], @$_POST["z_webTitle"], @$_POST["v_webTitle"], @$_POST["y_webTitle"], @$_POST["w_webTitle"]);

	// Field webURLFreq
	BuildSearchUrl($sSrchUrl, $webpages->webURLFreq, @$_POST["x_webURLFreq"], @$_POST["z_webURLFreq"], @$_POST["v_webURLFreq"], @$_POST["y_webURLFreq"], @$_POST["w_webURLFreq"]);

	// Field Date_Of_Crawling
	BuildSearchUrl($sSrchUrl, $webpages->Date_Of_Crawling, ew_UnFormatDateTime(@$_POST["x_Date_Of_Crawling"],6), @$_POST["z_Date_Of_Crawling"], @$_POST["v_Date_Of_Crawling"], ew_UnFormatDateTime(@$_POST["y_Date_Of_Crawling"],6), @$_POST["w_Date_Of_Crawling"]);

	// Field meta_content
	BuildSearchUrl($sSrchUrl, $webpages->meta_content, @$_POST["x_meta_content"], @$_POST["z_meta_content"], @$_POST["v_meta_content"], @$_POST["y_meta_content"], @$_POST["w_meta_content"]);
	return $sSrchUrl;
}

// Function to build search URL
function BuildSearchUrl(&$Url, &$Fld, $FldVal, $FldOpr, $FldCond, $FldVal2, $FldOpr2) {
	$sWrk = "";
	$FldParm = substr($Fld->FldVar, 2);
	$FldVal = ew_StripSlashes($FldVal);
	if (is_array($FldVal)) $FldVal = implode(",", $FldVal);
	$FldVal2 = ew_StripSlashes($FldVal2);
	if (is_array($FldVal2)) $FldVal2 = implode(",", $FldVal2);
	$FldOpr = strtoupper(trim($FldOpr));
	if ($FldOpr == "BETWEEN") {
		$IsValidValue = ($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal) && is_numeric($FldVal2));
		if ($FldVal <> "" && $FldVal2 <> "" && $IsValidValue) {
			$sWrk = "x_" . $FldParm . "=" . urlencode($FldVal) .
				"&y_" . $FldParm . "=" . urlencode($FldVal2) .
				"&z_" . $FldParm . "=" . urlencode($FldOpr);
		}
	} elseif ($FldOpr == "IS NULL" || $FldOpr == "IS NOT NULL") {
		$sWrk = "x_" . $FldParm . "=" . urlencode($FldVal) .
			"&z_" . $FldParm . "=" . urlencode($FldOpr);
	} else {
		$IsValidValue = ($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType = EW_DATATYPE_NUMBER && is_numeric($FldVal));
		if ($FldVal <> "" && $IsValidValue && ew_IsValidOpr($FldOpr, $Fld->FldDataType)) {
			$sWrk = "x_" . $FldParm . "=" . urlencode($FldVal) .
				"&z_" . $FldParm . "=" . urlencode($FldOpr);
		}
		$IsValidValue = ($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType = EW_DATATYPE_NUMBER && is_numeric($FldVal2));
		if ($FldVal2 <> "" && $IsValidValue && ew_IsValidOpr($FldOpr2, $Fld->FldDataType)) {
			if ($sWrk <> "") $sWrk .= "&v_" . $FldParm . "=" . urlencode($FldCond) . "&";
			$sWrk .= "&y_" . $FldParm . "=" . urlencode($FldVal2) .
				"&w_" . $FldParm . "=" . urlencode($FldOpr2);
		}
	}
	if ($sWrk <> "") {
		if ($Url <> "") $Url .= "&";
		$Url .= $sWrk;
	}
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $webpages;

	// Call Row Rendering event
	$webpages->Row_Rendering();

	// Common render codes for all row types
	if ($webpages->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($webpages->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($webpages->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($webpages->RowType == EW_ROWTYPE_SEARCH) { // Search row

		// webURL
		$webpages->webURL->EditCustomAttributes = "";
		$webpages->webURL->EditValue = ew_HtmlEncode($webpages->webURL->AdvancedSearch->SearchValue);

		// webTitle
		$webpages->webTitle->EditCustomAttributes = "";
		$webpages->webTitle->EditValue = ew_HtmlEncode($webpages->webTitle->AdvancedSearch->SearchValue);

		// webURLFreq
		$webpages->webURLFreq->EditCustomAttributes = "";
		$webpages->webURLFreq->EditValue = ew_HtmlEncode($webpages->webURLFreq->AdvancedSearch->SearchValue);

		// Date_Of_Crawling
		$webpages->Date_Of_Crawling->EditCustomAttributes = "";
		$webpages->Date_Of_Crawling->EditValue = ew_HtmlEncode(ew_FormatDateTime($webpages->Date_Of_Crawling->AdvancedSearch->SearchValue, 6));

		// meta_content
		$webpages->meta_content->EditCustomAttributes = "";
		$webpages->meta_content->EditValue = ew_HtmlEncode($webpages->meta_content->AdvancedSearch->SearchValue);
	}

	// Call Row Rendered event
	$webpages->Row_Rendered();
}
?>
<?php

// Load advanced search
function LoadAdvancedSearch() {
	global $webpages;
	$webpages->webURL->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_webURL");
	$webpages->webTitle->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_webTitle");
	$webpages->webURLFreq->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_webURLFreq");
	$webpages->webURLFreq->AdvancedSearch->SearchOperator = $webpages->getAdvancedSearch("z_webURLFreq");
	$webpages->Date_Of_Crawling->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_Date_Of_Crawling");
	$webpages->Date_Of_Crawling->AdvancedSearch->SearchOperator = $webpages->getAdvancedSearch("z_Date_Of_Crawling");
	$webpages->meta_content->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_meta_content");
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
