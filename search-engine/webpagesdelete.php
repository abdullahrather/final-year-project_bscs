<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
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

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["webURL"] <> "") {
	$webpages->webURL->setQueryStringValue($_GET["webURL"]);
	$sKey .= $webpages->webURL->QueryStringValue;
} else {
	$bSingleDelete = FALSE;
}
if ($bSingleDelete) {
	$nKeySelected = 1; // Set up key selected count
	$arRecKeys[0] = $sKey;
} else {
	if (isset($_POST["key_m"])) { // Key in form
		$nKeySelected = count($_POST["key_m"]); // Set up key selected count
		$arRecKeys = ew_StripSlashes($_POST["key_m"]);
	}
}
if ($nKeySelected <= 0) Page_Terminate($webpages->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";

	// Set up key field
	$sKeyFld = $sKey;
	$sFilter .= "`webURL`='" . ew_AdjustSql($sKeyFld) . "' AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in webpages class, webpagesinfo.php

$webpages->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$webpages->CurrentAction = $_POST["a_delete"];
} else {
	$webpages->CurrentAction = "I"; // Display record
}
switch ($webpages->CurrentAction) {
	case "D": // Delete
		$webpages->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Delete Successful"; // Set up success message
			Page_Terminate($webpages->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($webpages->getReturnUrl()); // Return to caller
}
?>
<?php include "header.php" ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "delete"; // Page id

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<p><span class="phpmaker">Delete from TABLE: Web Links<br><br><a href="<?php echo $webpages->getReturnUrl() ?>">Go Back</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="webpagesdelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Web URL</td>
		<td valign="top">Web Title</td>
		<td valign="top">URL Frequency</td>
		<td valign="top">Date Of Crawling</td>
		<td valign="top">Meta Content</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$webpages->CssClass = "ewTableRow";
	$webpages->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$webpages->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$webpages->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $webpages->DisplayAttributes() ?>>
		<td<?php echo $webpages->webURL->CellAttributes() ?>>
<?php if ($webpages->webURL->HrefValue <> "") { ?>
<a href="<?php echo $webpages->webURL->HrefValue ?>" target="_blank"><div<?php echo $webpages->webURL->ViewAttributes() ?>><?php echo $webpages->webURL->ViewValue ?></div></a>
<?php } else { ?>
<div<?php echo $webpages->webURL->ViewAttributes() ?>><?php echo $webpages->webURL->ViewValue ?></div>
<?php } ?>
</td>
		<td<?php echo $webpages->webTitle->CellAttributes() ?>>
<?php if ($webpages->webTitle->HrefValue <> "") { ?>
<a href="<?php echo $webpages->webTitle->HrefValue ?>" target="_blank"><div<?php echo $webpages->webTitle->ViewAttributes() ?>><?php echo $webpages->webTitle->ViewValue ?></div></a>
<?php } else { ?>
<div<?php echo $webpages->webTitle->ViewAttributes() ?>><?php echo $webpages->webTitle->ViewValue ?></div>
<?php } ?>
</td>
		<td<?php echo $webpages->webURLFreq->CellAttributes() ?>>
<div<?php echo $webpages->webURLFreq->ViewAttributes() ?>><?php echo $webpages->webURLFreq->ViewValue ?></div>
</td>
		<td<?php echo $webpages->Date_Of_Crawling->CellAttributes() ?>>
<div<?php echo $webpages->Date_Of_Crawling->ViewAttributes() ?>><?php echo $webpages->Date_Of_Crawling->ViewValue ?></div>
</td>
		<td<?php echo $webpages->meta_content->CellAttributes() ?>>
<div<?php echo $webpages->meta_content->ViewAttributes() ?>><?php echo $webpages->meta_content->ViewValue ?></div>
</td>
	</tr>
<?php
	$rs->MoveNext();
}
$rs->Close();
?>
</table>
<p>
<input type="submit" name="Action" id="Action" value="Confirm Delete">
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

// ------------------------------------------------
//  Function DeleteRows
//  - Delete Records based on current filter
function DeleteRows() {
	global $conn, $Security, $webpages;
	$DeleteRows = TRUE;
	$sWrkFilter = $webpages->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in webpages class, webpagesinfo.php

	$webpages->CurrentFilter = $sWrkFilter;
	$sSql = $webpages->SQL();
	$conn->raiseErrorFn = 'ew_ErrorFn';
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';
	if ($rs === FALSE) {
		return FALSE;
	} elseif ($rs->EOF) {
		$_SESSION[EW_SESSION_MESSAGE] = "No records found"; // No record found
		$rs->Close();
		return FALSE;
	}
	$conn->BeginTrans();

	// Clone old rows
	$rsold = ($rs) ? $rs->GetRows() : array();
	if ($rs) $rs->Close();

	// Call row deleting event
	if ($DeleteRows) {
		foreach ($rsold as $row) {
			$DeleteRows = $webpages->Row_Deleting($row);
			if (!$DeleteRows) break;
		}
	}
	if ($DeleteRows) {
		$sKey = "";
		foreach ($rsold as $row) {
			$sThisKey = "";
			if ($sThisKey <> "") $sThisKey .= EW_COMPOSITE_KEY_SEPARATOR;
			$sThisKey .= $row['webURL'];
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$DeleteRows = $conn->Execute($webpages->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($webpages->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $webpages->CancelMessage;
			$webpages->CancelMessage = "";
		} else {
			$_SESSION[EW_SESSION_MESSAGE] = "Delete cancelled";
		}
	}
	if ($DeleteRows) {
		$conn->CommitTrans(); // Commit the changes
	} else {
		$conn->RollbackTrans(); // Rollback changes
	}

	// Call recordset deleted event
	if ($DeleteRows) {
		foreach ($rsold as $row) {
			$webpages->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $webpages;

	// Call Recordset Selecting event
	$webpages->Recordset_Selecting($webpages->CurrentFilter);

	// Load list page sql
	$sSql = $webpages->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$webpages->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $webpages;
	$sFilter = $webpages->SqlKeyFilter();
	$sFilter = str_replace("@webURL@", ew_AdjustSql($webpages->webURL->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$webpages->Row_Selecting($sFilter);

	// Load sql based on filter
	$webpages->CurrentFilter = $sFilter;
	$sSql = $webpages->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$webpages->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $webpages;
	$webpages->webURL->setDbValue($rs->fields('webURL'));
	$webpages->webTitle->setDbValue($rs->fields('webTitle'));
	$webpages->webURLFreq->setDbValue($rs->fields('webURLFreq'));
	$webpages->Date_Of_Crawling->setDbValue($rs->fields('Date_Of_Crawling'));
	$webpages->meta_content->setDbValue($rs->fields('meta_content'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $webpages;

	// Call Row Rendering event
	$webpages->Row_Rendering();

	// Common render codes for all row types
	// webURL

	$webpages->webURL->CellCssStyle = "";
	$webpages->webURL->CellCssClass = "";

	// webTitle
	$webpages->webTitle->CellCssStyle = "";
	$webpages->webTitle->CellCssClass = "";

	// webURLFreq
	$webpages->webURLFreq->CellCssStyle = "";
	$webpages->webURLFreq->CellCssClass = "";

	// Date_Of_Crawling
	$webpages->Date_Of_Crawling->CellCssStyle = "";
	$webpages->Date_Of_Crawling->CellCssClass = "";

	// meta_content
	$webpages->meta_content->CellCssStyle = "";
	$webpages->meta_content->CellCssClass = "";
	if ($webpages->RowType == EW_ROWTYPE_VIEW) { // View row

		// webURL
		$webpages->webURL->ViewValue = $webpages->webURL->CurrentValue;
		$webpages->webURL->CssStyle = "";
		$webpages->webURL->CssClass = "";
		$webpages->webURL->ViewCustomAttributes = "";

		// webTitle
		$webpages->webTitle->ViewValue = $webpages->webTitle->CurrentValue;
		$webpages->webTitle->CssStyle = "";
		$webpages->webTitle->CssClass = "";
		$webpages->webTitle->ViewCustomAttributes = "";

		// webURLFreq
		$webpages->webURLFreq->ViewValue = $webpages->webURLFreq->CurrentValue;
		$webpages->webURLFreq->CssStyle = "";
		$webpages->webURLFreq->CssClass = "";
		$webpages->webURLFreq->ViewCustomAttributes = "";

		// Date_Of_Crawling
		$webpages->Date_Of_Crawling->ViewValue = $webpages->Date_Of_Crawling->CurrentValue;
		$webpages->Date_Of_Crawling->ViewValue = ew_FormatDateTime($webpages->Date_Of_Crawling->ViewValue, 6);
		$webpages->Date_Of_Crawling->CssStyle = "";
		$webpages->Date_Of_Crawling->CssClass = "";
		$webpages->Date_Of_Crawling->ViewCustomAttributes = "";

		// meta_content
		$webpages->meta_content->ViewValue = ew_TruncateMemo($webpages->meta_content->CurrentValue, 75);
		if (!is_null($webpages->meta_content->ViewValue)) $webpages->meta_content->ViewValue = str_replace("\n", "<br>", $webpages->meta_content->ViewValue); 
		$webpages->meta_content->CssStyle = "";
		$webpages->meta_content->CssClass = "";
		$webpages->meta_content->ViewCustomAttributes = "";

		// webURL
		if (!is_null($webpages->webURL->CurrentValue)) {
			$webpages->webURL->HrefValue = ((!empty($webpages->webURL->ViewValue)) ? $webpages->webURL->ViewValue : $webpages->webURL->CurrentValue);
			if ($webpages->Export <> "") $webpages->webURL->HrefValue = ew_ConvertFullUrl($webpages->webURL->HrefValue);
		} else {
			$webpages->webURL->HrefValue = "";
		}

		// webTitle
		if (!is_null($webpages->webURL->CurrentValue)) {
			$webpages->webTitle->HrefValue = ((!empty($webpages->webURL->ViewValue)) ? $webpages->webURL->ViewValue : $webpages->webURL->CurrentValue);
			if ($webpages->Export <> "") $webpages->webTitle->HrefValue = ew_ConvertFullUrl($webpages->webTitle->HrefValue);
		} else {
			$webpages->webTitle->HrefValue = "";
		}

		// webURLFreq
		$webpages->webURLFreq->HrefValue = "";

		// Date_Of_Crawling
		$webpages->Date_Of_Crawling->HrefValue = "";

		// meta_content
		$webpages->meta_content->HrefValue = "";
	} elseif ($webpages->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($webpages->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($webpages->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$webpages->Row_Rendered();
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
