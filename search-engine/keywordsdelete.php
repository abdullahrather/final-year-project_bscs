<?php
define("EW_PAGE_ID", "delete", TRUE); // Page ID
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

// Load Key Parameters
$sKey = "";
$bSingleDelete = TRUE; // Initialize as single delete
$arRecKeys = array();
$nKeySelected = 0; // Initialize selected key count
$sFilter = "";
if (@$_GET["keyword"] <> "") {
	$keywords->keyword->setQueryStringValue($_GET["keyword"]);
	$sKey .= $keywords->keyword->QueryStringValue;
} else {
	$bSingleDelete = FALSE;
}
if (@$_GET["webURL"] <> "") {
	$keywords->webURL->setQueryStringValue($_GET["webURL"]);
	if ($sKey <> "") $sKey .= EW_COMPOSITE_KEY_SEPARATOR;
	$sKey .= $keywords->webURL->QueryStringValue;
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
if ($nKeySelected <= 0) Page_Terminate($keywords->getReturnUrl()); // No key specified, exit

// Build filter
foreach ($arRecKeys as $sKey) {
	$sFilter .= "(";
	$arKeyFlds = explode(EW_COMPOSITE_KEY_SEPARATOR, trim($sKey)); // Split key by separator
	if (count($arKeyFlds) <> 2) Page_Terminate($keywords->getReturnUrl()); // Invalid key, exit

	// Set up key field
	$sKeyFld = $arKeyFlds[0];
	$sFilter .= "`keyword`='" . ew_AdjustSql($sKeyFld) . "' AND ";

	// Set up key field
	$sKeyFld = $arKeyFlds[1];
	$sFilter .= "`webURL`='" . ew_AdjustSql($sKeyFld) . "' AND ";
	if (substr($sFilter, -5) == " AND ") $sFilter = substr($sFilter, 0, strlen($sFilter)-5) . ") OR ";
}
if (substr($sFilter, -4) == " OR ") $sFilter = substr($sFilter, 0, strlen($sFilter)-4);

// Set up filter (Sql Where Clause) and get Return Sql
// Sql constructor in keywords class, keywordsinfo.php

$keywords->CurrentFilter = $sFilter;

// Get action
if (@$_POST["a_delete"] <> "") {
	$keywords->CurrentAction = $_POST["a_delete"];
} else {
	$keywords->CurrentAction = "I"; // Display record
}
switch ($keywords->CurrentAction) {
	case "D": // Delete
		$keywords->SendEmail = TRUE; // Send email on delete success
		if (DeleteRows()) { // delete rows
			$_SESSION[EW_SESSION_MESSAGE] = "Delete Successful"; // Set up success message
			Page_Terminate($keywords->getReturnUrl()); // Return to caller
		}
}

// Load records for display
$rs = LoadRecordset();
$nTotalRecs = $rs->RecordCount(); // Get record count
if ($nTotalRecs <= 0) { // No record found, exit
	$rs->Close();
	Page_Terminate($keywords->getReturnUrl()); // Return to caller
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
<p><span class="phpmaker">Delete from TABLE: KeyWords<br><br><a href="<?php echo $keywords->getReturnUrl() ?>">Go Back</a></span></p>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form action="keywordsdelete.php" method="post">
<p>
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($arRecKeys as $sKey) { ?>
<input type="hidden" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($sKey) ?>">
<?php } ?>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">KeyWord</td>
		<td valign="top">URL</td>
		<td valign="top">Frequency</td>
		<td valign="top">Cluster Nr</td>
	</tr>
<?php
$nRecCount = 0;
$i = 0;
while (!$rs->EOF) {
	$nRecCount++;

	// Set row class and style
	$keywords->CssClass = "ewTableRow";
	$keywords->CssStyle = "";

	// Display alternate color for rows
	if ($nRecCount % 2 <> 1) {
		$keywords->CssClass = "ewTableAltRow";
	}

	// Get the field contents
	LoadRowValues($rs);

	// Render row value
	$keywords->RowType = EW_ROWTYPE_VIEW; // view
	RenderRow();
?>
	<tr<?php echo $keywords->DisplayAttributes() ?>>
		<td<?php echo $keywords->keyword->CellAttributes() ?>>
<div<?php echo $keywords->keyword->ViewAttributes() ?>><?php echo $keywords->keyword->ViewValue ?></div>
</td>
		<td<?php echo $keywords->webURL->CellAttributes() ?>>
<?php if ($keywords->webURL->HrefValue <> "") { ?>
<a href="<?php echo $keywords->webURL->HrefValue ?>" target="_blank"><div<?php echo $keywords->webURL->ViewAttributes() ?>><?php echo $keywords->webURL->ViewValue ?></div></a>
<?php } else { ?>
<div<?php echo $keywords->webURL->ViewAttributes() ?>><?php echo $keywords->webURL->ViewValue ?></div>
<?php } ?>
</td>
		<td<?php echo $keywords->freqOfWord->CellAttributes() ?>>
<div<?php echo $keywords->freqOfWord->ViewAttributes() ?>><?php echo $keywords->freqOfWord->ViewValue ?></div>
</td>
		<td<?php echo $keywords->clusterNr->CellAttributes() ?>>
<div<?php echo $keywords->clusterNr->ViewAttributes() ?>><?php echo $keywords->clusterNr->ViewValue ?></div>
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
	global $conn, $Security, $keywords;
	$DeleteRows = TRUE;
	$sWrkFilter = $keywords->CurrentFilter;

	// Set up filter (Sql Where Clause) and get Return Sql
	// Sql constructor in keywords class, keywordsinfo.php

	$keywords->CurrentFilter = $sWrkFilter;
	$sSql = $keywords->SQL();
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
			$DeleteRows = $keywords->Row_Deleting($row);
			if (!$DeleteRows) break;
		}
	}
	if ($DeleteRows) {
		$sKey = "";
		foreach ($rsold as $row) {
			$sThisKey = "";
			if ($sThisKey <> "") $sThisKey .= EW_COMPOSITE_KEY_SEPARATOR;
			$sThisKey .= $row['webURL'];
			if ($sThisKey <> "") $sThisKey .= EW_COMPOSITE_KEY_SEPARATOR;
			$sThisKey .= $row['webURL'];
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$DeleteRows = $conn->Execute($keywords->DeleteSQL($row)); // Delete
			$conn->raiseErrorFn = '';
			if ($DeleteRows === FALSE)
				break;
			if ($sKey <> "") $sKey .= ", ";
			$sKey .= $sThisKey;
		}
	} else {

		// Set up error message
		if ($keywords->CancelMessage <> "") {
			$_SESSION[EW_SESSION_MESSAGE] = $keywords->CancelMessage;
			$keywords->CancelMessage = "";
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
			$keywords->Row_Deleted($row);
		}	
	}
	return $DeleteRows;
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $keywords;

	// Call Recordset Selecting event
	$keywords->Recordset_Selecting($keywords->CurrentFilter);

	// Load list page sql
	$sSql = $keywords->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$keywords->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $keywords;
	$sFilter = $keywords->SqlKeyFilter();
	$sFilter = str_replace("@keyword@", ew_AdjustSql($keywords->keyword->CurrentValue), $sFilter); // Replace key value
	$sFilter = str_replace("@webURL@", ew_AdjustSql($keywords->webURL->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$keywords->Row_Selecting($sFilter);

	// Load sql based on filter
	$keywords->CurrentFilter = $sFilter;
	$sSql = $keywords->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$keywords->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $keywords;
	$keywords->keyword->setDbValue($rs->fields('keyword'));
	$keywords->webURL->setDbValue($rs->fields('webURL'));
	$keywords->freqOfWord->setDbValue($rs->fields('freqOfWord'));
	$keywords->clusterNr->setDbValue($rs->fields('clusterNr'));
}
?>
<?php

// Render row values based on field settings
function RenderRow() {
	global $conn, $Security, $keywords;

	// Call Row Rendering event
	$keywords->Row_Rendering();

	// Common render codes for all row types
	// keyword

	$keywords->keyword->CellCssStyle = "";
	$keywords->keyword->CellCssClass = "";

	// webURL
	$keywords->webURL->CellCssStyle = "";
	$keywords->webURL->CellCssClass = "";

	// freqOfWord
	$keywords->freqOfWord->CellCssStyle = "";
	$keywords->freqOfWord->CellCssClass = "";

	// clusterNr
	$keywords->clusterNr->CellCssStyle = "";
	$keywords->clusterNr->CellCssClass = "";
	if ($keywords->RowType == EW_ROWTYPE_VIEW) { // View row

		// keyword
		$keywords->keyword->ViewValue = $keywords->keyword->CurrentValue;
		$keywords->keyword->CssStyle = "";
		$keywords->keyword->CssClass = "";
		$keywords->keyword->ViewCustomAttributes = "";

		// webURL
		$keywords->webURL->ViewValue = $keywords->webURL->CurrentValue;
		$keywords->webURL->CssStyle = "";
		$keywords->webURL->CssClass = "";
		$keywords->webURL->ViewCustomAttributes = "";

		// freqOfWord
		$keywords->freqOfWord->ViewValue = $keywords->freqOfWord->CurrentValue;
		$keywords->freqOfWord->CssStyle = "";
		$keywords->freqOfWord->CssClass = "";
		$keywords->freqOfWord->ViewCustomAttributes = "";

		// clusterNr
		if (!is_null($keywords->clusterNr->CurrentValue)) {
			$sSqlWrk = "SELECT `clusterNr`, `clusterDesc` FROM `clusters` WHERE `clusterNr` = " . ew_AdjustSql($keywords->clusterNr->CurrentValue) . "";
			$rswrk = $conn->Execute($sSqlWrk);
			if ($rswrk) {
				if (!$rswrk->EOF) {
					$keywords->clusterNr->ViewValue = $rswrk->fields('clusterNr');
					$keywords->clusterNr->ViewValue .= ew_ValueSeparator(0) . $rswrk->fields('clusterDesc');
				}
				$rswrk->Close();
			} else {
				$keywords->clusterNr->ViewValue = $keywords->clusterNr->CurrentValue;
			}
		} else {
			$keywords->clusterNr->ViewValue = NULL;
		}
		$keywords->clusterNr->CssStyle = "";
		$keywords->clusterNr->CssClass = "";
		$keywords->clusterNr->ViewCustomAttributes = "";

		// keyword
		$keywords->keyword->HrefValue = "";

		// webURL
		if (!is_null($keywords->webURL->CurrentValue)) {
			$keywords->webURL->HrefValue = ((!empty($keywords->webURL->ViewValue)) ? $keywords->webURL->ViewValue : $keywords->webURL->CurrentValue);
			if ($keywords->Export <> "") $keywords->webURL->HrefValue = ew_ConvertFullUrl($keywords->webURL->HrefValue);
		} else {
			$keywords->webURL->HrefValue = "";
		}

		// freqOfWord
		$keywords->freqOfWord->HrefValue = "";

		// clusterNr
		$keywords->clusterNr->HrefValue = "";
	} elseif ($keywords->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($keywords->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($keywords->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$keywords->Row_Rendered();
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
