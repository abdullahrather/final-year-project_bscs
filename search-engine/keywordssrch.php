<?php
define("EW_PAGE_ID", "search", TRUE); // Page ID
define("EW_TABLE_NAME", 'keywords', TRUE);
?>
<?php 
session_start(); // Initialize session data
ob_start(); // Turn on output buffering
?>
<?php include "ewcfg50.php" ?>
<?php include "ewmysql50.php" ?>
<?php include "phpfn50.php" ?>
<?php include "keywordsinfo.php" ?>
<?php include "userfn50.php" ?>
<?php include "webpagesinfo.php" ?>
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
$keywords->Export = @$_GET["export"]; // Get export parameter
$sExport = $keywords->Export; // Get export parameter, used in header
$sExportFile = $keywords->TableVar; // Get export file, used in header
?>
<?php

// Get action
$keywords->CurrentAction = @$_POST["a_search"];
switch ($keywords->CurrentAction) {
	case "S": // Get Search Criteria

		// Build search string for advanced search, remove blank field
		$sSrchStr = BuildAdvancedSearch();
		if ($sSrchStr <> "") {
			Page_Terminate("keywordslist.php?" . $sSrchStr); // Go to list page
		}
		break;
	default: // Restore search settings
		LoadAdvancedSearch();
}

// Render row for search
$keywords->RowType = EW_ROWTYPE_SEARCH;
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
		elm = fobj.elements["x" + infix + "_freqOfWord"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Incorrect integer - Frequency"))
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
<p><span class="phpmaker">Search TABLE: KeyWords<br><br><a href="keywordslist.php">Back to List</a></span></p>
<form name="fkeywordssearch" id="fkeywordssearch" action="keywordssrch.php" method="post" onSubmit="return ew_ValidateForm(this);">
<p>
<input type="hidden" name="a_search" id="a_search" value="S">
<table class="ewTable">
	<tr class="ewTableRow">
		<td class="ewTableHeader">KeyWord</td>
		<td<?php echo $keywords->keyword->CellAttributes() ?>><span class="ewSearchOpr">contains<input type="hidden" name="z_keyword" id="z_keyword" value="LIKE"></span></td>
		<td<?php echo $keywords->keyword->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_keyword" id="x_keyword" title="" size="30" maxlength="50" value="<?php echo $keywords->keyword->EditValue ?>"<?php echo $keywords->keyword->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableAltRow">
		<td class="ewTableHeader">URL</td>
		<td<?php echo $keywords->webURL->CellAttributes() ?>><span class="ewSearchOpr">contains<input type="hidden" name="z_webURL" id="z_webURL" value="LIKE"></span></td>
		<td<?php echo $keywords->webURL->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_webURL" id="x_webURL" title="" size="30" maxlength="200" value="<?php echo $keywords->webURL->EditValue ?>"<?php echo $keywords->webURL->EditAttributes() ?>>
</span></td>
	</tr>
	<tr class="ewTableRow">
		<td class="ewTableHeader">Frequency</td>
		<td<?php echo $keywords->freqOfWord->CellAttributes() ?>><span class="ewSearchOpr"><select name="z_freqOfWord" id="z_freqOfWord"><option value="="<?php echo ($keywords->freqOfWord->AdvancedSearch->SearchOperator=="=")?" selected":"" ?>>=</option><option value="<>"<?php echo ($keywords->freqOfWord->AdvancedSearch->SearchOperator=="<>")?" selected":"" ?>><></option><option value="<"<?php echo ($keywords->freqOfWord->AdvancedSearch->SearchOperator=="<")?" selected":"" ?>><</option><option value="<="<?php echo ($keywords->freqOfWord->AdvancedSearch->SearchOperator=="<=")?" selected":"" ?>><=</option><option value=">"<?php echo ($keywords->freqOfWord->AdvancedSearch->SearchOperator==">")?" selected":"" ?>>></option><option value=">="<?php echo ($keywords->freqOfWord->AdvancedSearch->SearchOperator==">=")?" selected":"" ?>>>=</option></select></span></td>
		<td<?php echo $keywords->freqOfWord->CellAttributes() ?>><span class="phpmaker">
<input type="text" name="x_freqOfWord" id="x_freqOfWord" title="" size="10" value="<?php echo $keywords->freqOfWord->EditValue ?>"<?php echo $keywords->freqOfWord->EditAttributes() ?>>
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
	global $keywords;
	$sSrchUrl = "";

	// Field keyword
	BuildSearchUrl($sSrchUrl, $keywords->keyword, @$_POST["x_keyword"], @$_POST["z_keyword"], @$_POST["v_keyword"], @$_POST["y_keyword"], @$_POST["w_keyword"]);

	// Field webURL
	BuildSearchUrl($sSrchUrl, $keywords->webURL, @$_POST["x_webURL"], @$_POST["z_webURL"], @$_POST["v_webURL"], @$_POST["y_webURL"], @$_POST["w_webURL"]);

	// Field freqOfWord
	BuildSearchUrl($sSrchUrl, $keywords->freqOfWord, @$_POST["x_freqOfWord"], @$_POST["z_freqOfWord"], @$_POST["v_freqOfWord"], @$_POST["y_freqOfWord"], @$_POST["w_freqOfWord"]);
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
	global $conn, $Security, $keywords;

	// Call Row Rendering event
	$keywords->Row_Rendering();

	// Common render codes for all row types
	if ($keywords->RowType == EW_ROWTYPE_VIEW) { // View row
	} elseif ($keywords->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($keywords->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($keywords->RowType == EW_ROWTYPE_SEARCH) { // Search row

		// keyword
		$keywords->keyword->EditCustomAttributes = "";
		$keywords->keyword->EditValue = ew_HtmlEncode($keywords->keyword->AdvancedSearch->SearchValue);

		// webURL
		$keywords->webURL->EditCustomAttributes = "";
		$keywords->webURL->EditValue = ew_HtmlEncode($keywords->webURL->AdvancedSearch->SearchValue);

		// freqOfWord
		$keywords->freqOfWord->EditCustomAttributes = "";
		$keywords->freqOfWord->EditValue = ew_HtmlEncode($keywords->freqOfWord->AdvancedSearch->SearchValue);
	}

	// Call Row Rendered event
	$keywords->Row_Rendered();
}
?>
<?php

// Load advanced search
function LoadAdvancedSearch() {
	global $keywords;
	$keywords->keyword->AdvancedSearch->SearchValue = $keywords->getAdvancedSearch("x_keyword");
	$keywords->webURL->AdvancedSearch->SearchValue = $keywords->getAdvancedSearch("x_webURL");
	$keywords->freqOfWord->AdvancedSearch->SearchValue = $keywords->getAdvancedSearch("x_freqOfWord");
	$keywords->freqOfWord->AdvancedSearch->SearchOperator = $keywords->getAdvancedSearch("z_freqOfWord");
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
