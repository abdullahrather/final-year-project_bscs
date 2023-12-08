<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
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
if ($keywords->Export == "excel") {
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment; filename=' . $sExportFile .'.xls');
}
?>
<?php

// Paging variables
$nStartRec = 0; // Start record index
$nStopRec = 0; // Stop record index
$nTotalRecs = 0; // Total number of records
$nDisplayRecs = 20;
$nRecRange = 10;
$nRecCount = 0; // Record count

// Search filters
$sSrchAdvanced = ""; // Advanced search filter
$sSrchBasic = ""; // Basic search filter
$sSrchWhere = ""; // Search where clause
$sFilter = "";

// Master/Detail
$sDbMasterFilter = ""; // Master filter
$sDbDetailFilter = ""; // Detail filter
$sSqlMaster = ""; // Sql for master record

// Set up records per page dynamically
SetUpDisplayRecs();

// Handle reset command
ResetCmd();

// Set up master detail parameters
SetUpMasterDetail();

// Check QueryString parameters
if (@$_GET["a"] <> "") {
	$keywords->CurrentAction = $_GET["a"];

	// Clear inline mode
	if ($keywords->CurrentAction == "cancel") {
		ClearInlineMode();
	}

	// Switch to inline edit mode
	if ($keywords->CurrentAction == "edit") {
		InlineEditMode();
	}
} else {

	// Create form object
	$objForm = new cFormObj;
	if (@$_POST["a_list"] <> "") {
		$keywords->CurrentAction = $_POST["a_list"]; // Get action

		// Inline Update
		if ($keywords->CurrentAction == "update" && @$_SESSION[EW_SESSION_INLINE_MODE] == "edit") {
			InlineUpdate();
		}
	}
}

// Get search criteria for advanced search
$sSrchAdvanced = AdvancedSearchWhere();

// Get basic search criteria
$sSrchBasic = BasicSearchWhere();

// Build search criteria
if ($sSrchAdvanced <> "") {
	if ($sSrchWhere <> "") $sSrchWhere .= " AND ";
	$sSrchWhere .= "(" . $sSrchAdvanced . ")";
}
if ($sSrchBasic <> "") {
	if ($sSrchWhere <> "") $sSrchWhere .= " AND ";
	$sSrchWhere .= "(" . $sSrchBasic . ")";
}

// Save search criteria
if ($sSrchWhere <> "") {
	if ($sSrchBasic == "") ResetBasicSearchParms();
	if ($sSrchAdvanced == "") ResetAdvancedSearchParms();
	$keywords->setSearchWhere($sSrchWhere); // Save to Session
	$nStartRec = 1; // Reset start record counter
	$keywords->setStartRecordNumber($nStartRec);
} else {
	RestoreSearchParms();
}

// Build filter
$sFilter = "";
if ($sDbDetailFilter <> "") {
	if ($sFilter <> "") $sFilter .= " AND ";
	$sFilter .= "(" . $sDbDetailFilter . ")";
}
if ($sSrchWhere <> "") {
	if ($sFilter <> "") $sFilter .= " AND ";
	$sFilter .= "(" . $sSrchWhere . ")";
}

// Load master record
if ($keywords->getMasterFilter() <> "" && $keywords->getCurrentMasterTable() == "webpages") {
	$rsmaster = $webpages->LoadRs($sDbMasterFilter);
	$bMasterRecordExists = ($rsmaster && !$rsmaster->EOF);
	if (!$bMasterRecordExists) {
		$keywords->setMasterFilter(""); // Clear master filter
		$keywords->setDetailFilter(""); // Clear detail filter
		$_SESSION[EW_SESSION_MESSAGE] = "No records found"; // Set no record found
		Page_Terminate("webpageslist.php"); // Return to caller
	} else {
		$webpages->LoadListRowValues($rsmaster);
		$webpages->RenderListRow();
		$rsmaster->Close();
	}
}

// Set up filter in Session
$keywords->setSessionWhere($sFilter);
$keywords->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$keywords->setReturnUrl("keywordslist.php");
?>
<?php include "header.php" ?>
<?php if ($keywords->Export == "") { ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "list"; // Page id

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
		elm = fobj.elements["x" + infix + "_keyword"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Please enter required field - KeyWord"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_webURL"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Please enter required field - URL"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_freqOfWord"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Please enter required field - Frequency"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_freqOfWord"];
		if (elm && !ew_CheckInteger(elm.value)) {
			if (!ew_OnError(elm, "Incorrect integer - Frequency"))
				return false; 
		}
		elm = fobj.elements["x" + infix + "_clusterNr"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Please enter required field - Cluster Nr"))
				return false;
		}
	}
	return true;
}

//-->
</script>
<script type="text/javascript">
<!--
var firstrowoffset = 1; // First data row start at
var lastrowoffset = 0; // Last data row end at
var EW_LIST_TABLE_NAME = 'ewlistmain'; // Table name for list page
var rowclass = 'ewTableRow'; // Row class
var rowaltclass = 'ewTableAltRow'; // Row alternate class
var rowmoverclass = 'ewTableHighlightRow'; // Row mouse over class
var rowselectedclass = 'ewTableSelectRow'; // Row selected class
var roweditclass = 'ewTableEditRow'; // Row edit class

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

function ew_SelectKey(elem) {
	var f = elem.form;	
	if (!f.elements["key_m[]"]) return;
	if (f.elements["key_m[]"][0]) {
		for (var i=0; i<f.elements["key_m[]"].length; i++)
			f.elements["key_m[]"][i].checked = elem.checked;	
	} else {
		f.elements["key_m[]"].checked = elem.checked;	
	}
	ew_ClickAll(elem);
}

function ew_Selected(f) {
	if (!f.elements["key_m[]"]) return false;
	if (f.elements["key_m[]"][0]) {
		for (var i=0; i<f.elements["key_m[]"].length; i++)
			if (f.elements["key_m[]"][i].checked) return true;
	} else {
		return f.elements["key_m[]"].checked;
	}
	return false;
}

//-->
</script>
<script language="JavaScript" type="text/javascript">
<!--

// Write your client script here, no need to add script tags.
// To include another .js script, use:
// ew_ClientScriptInclude("my_javascript.js"); 
//-->

</script>
<?php } ?>
<?php if ($keywords->Export == "") { ?>
<?php
$sMasterReturnUrl = "webpageslist.php";
if ($keywords->getMasterFilter() <> "" && $keywords->getCurrentMasterTable() == "webpages") {
	if ($bMasterRecordExists) {
		if ($keywords->getCurrentMasterTable() == $keywords->TableVar) $sMasterReturnUrl .= "?" . EW_TABLE_SHOW_MASTER . "=";
?>
<?php include "webpagesmaster.php" ?>
<?php
	}
}
?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $keywords->Export <> "");
$bSelectLimit = ($keywords->Export == "" && $keywords->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $keywords->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<p><span class="phpmaker" style="white-space: nowrap;">TABLE: KeyWords
<?php if ($keywords->Export == "") { ?>
&nbsp;&nbsp;<a href="keywordslist.php?export=excel">Export to Excel</a>
<?php } ?>
</span></p>
<?php if ($keywords->Export == "") { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<form name="fkeywordslistsrch" id="fkeywordslistsrch" action="keywordslist.php" >
<table class="ewBasicSearch">
	<tr>
		<td><span class="phpmaker">
			<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo ew_HtmlEncode($keywords->getBasicSearchKeyword()) ?>">
			<input type="Submit" name="Submit" id="Submit" value="Search (*)">&nbsp;
			<a href="keywordslist.php?cmd=reset">Show all</a>&nbsp;
			<a href="keywordssrch.php">Advanced Search</a>&nbsp;
		</span></td>
	</tr>
	<tr>
	<td><span class="phpmaker"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="" <?php if ($keywords->getBasicSearchType() == "") { ?>checked<?php } ?>>Exact phrase&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND" <?php if ($keywords->getBasicSearchType() == "AND") { ?>checked<?php } ?>>All words&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR" <?php if ($keywords->getBasicSearchType() == "OR") { ?>checked<?php } ?>>Any word</span></td>
	</tr>
</table>
</form>
<?php } ?>
<?php } ?>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form name="fkeywordslist" id="fkeywordslist" action="keywordslist.php" method="post">
<?php if ($keywords->Export == "") { ?>
<table>
	<tr><td><span class="phpmaker">
<?php if ($nTotalRecs > 0) { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<a href="" onClick="if (!ew_Selected(document.fkeywordslist)) alert('No records selected'); else {document.fkeywordslist.action='keywordsdelete.php';document.fkeywordslist.encoding='application/x-www-form-urlencoded';document.fkeywordslist.submit();};return false;">Delete Selected Records</a>&nbsp;&nbsp;
<?php } ?>
<?php } ?>
	</span></td></tr>
</table>
<?php } ?>
<?php if ($nTotalRecs > 0) { ?>
<table id="ewlistmain" class="ewTable">
<?php
	$OptionCnt = 0;
if ($Security->IsLoggedIn()) {
	$OptionCnt++; // edit
}
if ($Security->IsLoggedIn()) {
	$OptionCnt++; // multi select
}
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($keywords->Export <> "") { ?>
KeyWord
<?php } else { ?>
	<a href="keywordslist.php?order=<?php echo urlencode('keyword') ?>&ordertype=<?php echo $keywords->keyword->ReverseSort() ?>">KeyWord&nbsp;(*)<?php if ($keywords->keyword->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($keywords->keyword->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($keywords->Export <> "") { ?>
URL
<?php } else { ?>
	<a href="keywordslist.php?order=<?php echo urlencode('webURL') ?>&ordertype=<?php echo $keywords->webURL->ReverseSort() ?>">URL<?php if ($keywords->webURL->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($keywords->webURL->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($keywords->Export <> "") { ?>
Frequency
<?php } else { ?>
	<a href="keywordslist.php?order=<?php echo urlencode('freqOfWord') ?>&ordertype=<?php echo $keywords->freqOfWord->ReverseSort() ?>">Frequency<?php if ($keywords->freqOfWord->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($keywords->freqOfWord->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($keywords->Export <> "") { ?>
Cluster Nr
<?php } else { ?>
	<a href="keywordslist.php?order=<?php echo urlencode('clusterNr') ?>&ordertype=<?php echo $keywords->clusterNr->ReverseSort() ?>">Cluster Nr<?php if ($keywords->clusterNr->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($keywords->clusterNr->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($keywords->Export == "") { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap><input type="checkbox" class="phpmaker" onClick="ew_SelectKey(this);"></td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $keywords->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$keywords->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
$nEditRowCnt = 0;
if ($keywords->CurrentAction == "edit") $RowIndex = 1;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$keywords->CssClass = "ewTableRow";
	$keywords->CssStyle = "";

	// Init row event
	$keywords->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$keywords->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$keywords->RowType = EW_ROWTYPE_VIEW; // Render view
	if ($keywords->CurrentAction == "edit") {
		if (CheckInlineEditKey() && $nEditRowCnt == 0) { // Inline edit
			$keywords->RowType = EW_ROWTYPE_EDIT; // Render edit
		}
	}
		if ($keywords->RowType == EW_ROWTYPE_EDIT && $keywords->EventCancelled) { // Update failed
			if ($keywords->CurrentAction == "edit") {
				RestoreFormValues(); // Restore form values
			}
		}
		if ($keywords->RowType == EW_ROWTYPE_EDIT) { // Edit row
			$nEditRowCnt++;
			$keywords->CssClass = "ewTableEditRow";
			$keywords->RowClientEvents = "onmouseover='this.edit=true;ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";
		}
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $keywords->DisplayAttributes() ?>>
		<!-- keyword -->
		<td<?php echo $keywords->keyword->CellAttributes() ?>>
<?php if ($keywords->RowType == EW_ROWTYPE_EDIT) { // Edit Record ?>
<div<?php echo $keywords->keyword->ViewAttributes() ?>><?php echo $keywords->keyword->EditValue ?></div>
<input type="hidden" name="x<?php echo $RowIndex ?>_keyword" id="x<?php echo $RowIndex ?>_keyword" value="<?php echo ew_HtmlEncode($keywords->keyword->CurrentValue) ?>">
<?php } else { ?>
<div<?php echo $keywords->keyword->ViewAttributes() ?>><?php echo $keywords->keyword->ViewValue ?></div>
<?php } ?>
</td>
		<!-- webURL -->
		<td<?php echo $keywords->webURL->CellAttributes() ?>>
<?php if ($keywords->RowType == EW_ROWTYPE_EDIT) { // Edit Record ?>
<?php if ($keywords->webURL->HrefValue <> "") { ?>
<a href="<?php echo $keywords->webURL->HrefValue ?>" target="_blank"><div<?php echo $keywords->webURL->ViewAttributes() ?>><?php echo $keywords->webURL->EditValue ?></div></a>
<?php } else { ?>
<div<?php echo $keywords->webURL->ViewAttributes() ?>><?php echo $keywords->webURL->EditValue ?></div>
<?php } ?>
<input type="hidden" name="x<?php echo $RowIndex ?>_webURL" id="x<?php echo $RowIndex ?>_webURL" value="<?php echo ew_HtmlEncode($keywords->webURL->CurrentValue) ?>">
<?php } else { ?>
<?php if ($keywords->webURL->HrefValue <> "") { ?>
<a href="<?php echo $keywords->webURL->HrefValue ?>" target="_blank"><div<?php echo $keywords->webURL->ViewAttributes() ?>><?php echo $keywords->webURL->ViewValue ?></div></a>
<?php } else { ?>
<div<?php echo $keywords->webURL->ViewAttributes() ?>><?php echo $keywords->webURL->ViewValue ?></div>
<?php } ?>
<?php } ?>
</td>
		<!-- freqOfWord -->
		<td<?php echo $keywords->freqOfWord->CellAttributes() ?>>
<?php if ($keywords->RowType == EW_ROWTYPE_EDIT) { // Edit Record ?>
<input type="text" name="x<?php echo $RowIndex ?>_freqOfWord" id="x<?php echo $RowIndex ?>_freqOfWord" title="" size="10" value="<?php echo $keywords->freqOfWord->EditValue ?>"<?php echo $keywords->freqOfWord->EditAttributes() ?>>
<?php } else { ?>
<div<?php echo $keywords->freqOfWord->ViewAttributes() ?>><?php echo $keywords->freqOfWord->ViewValue ?></div>
<?php } ?>
</td>
		<!-- clusterNr -->
		<td<?php echo $keywords->clusterNr->CellAttributes() ?>>
<?php if ($keywords->RowType == EW_ROWTYPE_EDIT) { // Edit Record ?>
<?php if ($keywords->clusterNr->getSessionValue() <> "") { ?>
<div<?php echo $keywords->clusterNr->ViewAttributes() ?>><?php echo $keywords->clusterNr->ViewValue ?></div>
<input type="hidden" id="x<?php echo $RowIndex ?>_clusterNr" name="x<?php echo $RowIndex ?>_clusterNr" value="<?php echo ew_HtmlEncode($keywords->clusterNr->CurrentValue) ?>">
<?php } else { ?>
<select id="x<?php echo $RowIndex ?>_clusterNr" name="x<?php echo $RowIndex ?>_clusterNr"<?php echo $keywords->clusterNr->EditAttributes() ?>>
<!--option value="">Please Select</option-->
<?php
if (is_array($keywords->clusterNr->EditValue)) {
	$arwrk = $keywords->clusterNr->EditValue;
	$rowswrk = count($arwrk);
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($keywords->clusterNr->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected" : "";	
?>
<option value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
<?php if ($arwrk[$rowcntwrk][2] <> "") { ?>
<?php echo ew_ValueSeparator($rowcntwrk) ?><?php echo $arwrk[$rowcntwrk][2] ?>
<?php } ?>
</option>
<?php
			}
}
?>
</select>
<?php } ?>
<?php } else { ?>
<div<?php echo $keywords->clusterNr->ViewAttributes() ?>><?php echo $keywords->clusterNr->ViewValue ?></div>
<?php } ?>
</td>
<?php if ($keywords->RowType == EW_ROWTYPE_EDIT) { ?>
<?php if ($keywords->CurrentAction == "edit") { ?>
<td colspan="<?php echo $OptionCnt ?>"><span class="phpmaker">
<a href="" onClick="if (ew_ValidateForm(document.fkeywordslist)) document.fkeywordslist.submit();return false;">Update</a>&nbsp;<a href="keywordslist.php?a=cancel">Cancel</a>
<input type="hidden" name="a_list" id="a_list" value="update">
</span></td>
<?php } ?>
<?php } else { ?>
<?php if ($keywords->Export == "") { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap><span class="phpmaker">
<a href="<?php echo $keywords->InlineEditUrl() ?>">Inline Edit</a>
</span></td>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap><span class="phpmaker">
<input type="checkbox" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($keywords->keyword->CurrentValue . EW_COMPOSITE_KEY_SEPARATOR . $keywords->webURL->CurrentValue) ?>" class="phpmaker" onclick='ew_ClickMultiCheckbox(this);'>
</span></td>
<?php } ?>
<?php } ?>
<?php } ?>
	</tr>
<?php if ($keywords->RowType == EW_ROWTYPE_EDIT) { ?>
<?php } ?>
<?php
	}
	$rs->MoveNext();
}
?>
</table>
<?php if ($keywords->Export == "") { ?>
<table>
	<tr><td><span class="phpmaker">
<?php if ($nTotalRecs > 0) { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<a href="" onClick="if (!ew_Selected(document.fkeywordslist)) alert('No records selected'); else {document.fkeywordslist.action='keywordsdelete.php';document.fkeywordslist.encoding='application/x-www-form-urlencoded';document.fkeywordslist.submit();};return false;">Delete Selected Records</a>&nbsp;&nbsp;
<?php } ?>
<?php } ?>
	</span></td></tr>
</table>
<?php } ?>
<?php } ?>
<?php if ($keywords->CurrentAction == "edit") { ?>
<input type="hidden" name="key_count" id="key_count" value="<?php echo $RowIndex ?>">
<?php } ?>
</form>
<?php

// Close recordset and connection
if ($rs) $rs->Close();
?>
<?php if ($keywords->Export == "") { ?>
<form action="keywordslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td nowrap>
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="keywordslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>First</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="keywordslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Previous</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="keywordslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="keywordslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Next</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="keywordslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Last</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->ButtonCount > 0) { ?><br><?php } ?>
	Records <?php echo $Pager->FromIndex ?> to <?php echo $Pager->ToIndex ?> of <?php echo $Pager->RecordCount ?>
<?php } else { ?>	
	<?php if ($sSrchWhere == "0=101") { ?>
	Please enter search criteria
	<?php } else { ?>
	No records found
	<?php } ?>
<?php } ?>
</span>
		</td>
<?php if ($nTotalRecs > 0) { ?>
		<td nowrap>&nbsp;&nbsp;&nbsp;&nbsp;</td>
		<td align="right" valign="top" nowrap><span class="phpmaker">Records Per Page&nbsp;
<select name="<?php echo EW_TABLE_REC_PER_PAGE ?>" id="<?php echo EW_TABLE_REC_PER_PAGE ?>" onChange="this.form.submit();" class="phpmaker">
<option value="5"<?php if ($nDisplayRecs == 5) echo " selected" ?>>5</option>
<option value="10"<?php if ($nDisplayRecs == 10) echo " selected" ?>>10</option>
<option value="20"<?php if ($nDisplayRecs == 20) echo " selected" ?>>20</option>
<option value="50"<?php if ($nDisplayRecs == 50) echo " selected" ?>>50</option>
<option value="ALL"<?php if ($keywords->getRecordsPerPage() == -1) echo " selected" ?>>All Records</option>
</select>
		</span></td>
<?php } ?>
	</tr>
</table>
</form>
<?php } ?>
<?php if ($keywords->Export == "") { ?>
<?php } ?>
<?php if ($keywords->Export == "") { ?>
<script language="JavaScript" type="text/javascript">
<!--

// Write your table-specific startup script here
// document.write("page loaded");
//-->

</script>
<?php } ?>
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

// Set up number of records displayed per page
function SetUpDisplayRecs() {
	global $nDisplayRecs, $nStartRec, $keywords;
	$sWrk = @$_GET[EW_TABLE_REC_PER_PAGE];
	if ($sWrk <> "") {
		if (is_numeric($sWrk)) {
			$nDisplayRecs = intval($sWrk);
		} else {
			if (strtolower($sWrk) == "all") { // Display all records
				$nDisplayRecs = -1;
			} else {
				$nDisplayRecs = 20; // Non-numeric, load default
			}
		}
		$keywords->setRecordsPerPage($nDisplayRecs); // Save to Session

		// Reset start position
		$nStartRec = 1;
		$keywords->setStartRecordNumber($nStartRec);
	} else {
		if ($keywords->getRecordsPerPage() <> "") {
			$nDisplayRecs = $keywords->getRecordsPerPage(); // Restore from Session
		} else {
			$nDisplayRecs = 20; // Load default
		}
	}
}

//  Exit out of inline mode
function ClearInlineMode() {
	global $keywords;
	$keywords->setKey("keyword", ""); // Clear inline edit key
	$keywords->setKey("webURL", ""); // Clear inline edit key
	$keywords->CurrentAction = ""; // Clear action
	$_SESSION[EW_SESSION_INLINE_MODE] = ""; // Clear inline mode
}

// Switch to Inline Edit Mode
function InlineEditMode() {
	global $Security, $keywords;
	$bInlineEdit = TRUE;
	if (@$_GET["keyword"] <> "") {
		$keywords->keyword->setQueryStringValue($_GET["keyword"]);
	} else {
		$bInlineEdit = FALSE;
	}
	if (@$_GET["webURL"] <> "") {
		$keywords->webURL->setQueryStringValue($_GET["webURL"]);
	} else {
		$bInlineEdit = FALSE;
	}
	if ($bInlineEdit) {
		if (LoadRow()) {
			$keywords->setKey("keyword", $keywords->keyword->CurrentValue); // Set up inline edit key
			$keywords->setKey("webURL", $keywords->webURL->CurrentValue); // Set up inline edit key
			$_SESSION[EW_SESSION_INLINE_MODE] = "edit"; // Enable inline edit
		}
	}
}

// Peform update to inline edit record
function InlineUpdate() {
	global $objForm, $keywords;
	$objForm->Index = 1; 
	LoadFormValues(); // Get form values
	if (CheckInlineEditKey()) { // Check key
		$keywords->SendEmail = TRUE; // Send email on update success
		$bInlineUpdate = EditRow(); // Update record
	} else {
		$bInlineUpdate = FALSE;
	}
	if ($bInlineUpdate) { // Update success
		$_SESSION[EW_SESSION_MESSAGE] = "Update successful"; // Set success message
		ClearInlineMode(); // Clear inline edit mode
	} else {
		if (@$_SESSION[EW_SESSION_MESSAGE] == "") {
			$_SESSION[EW_SESSION_MESSAGE] = "Update failed"; // Set update failed message
		}
		$keywords->EventCancelled = TRUE; // Cancel event
		$keywords->CurrentAction = "edit"; // Stay in edit mode
	}
}

// Check inline edit key
function CheckInlineEditKey() {
	global $keywords;

	//CheckInlineEditKey = True
	if (strval($keywords->getKey("keyword")) <> strval($keywords->keyword->CurrentValue)) {
		return FALSE;
	}
	if (strval($keywords->getKey("webURL")) <> strval($keywords->webURL->CurrentValue)) {
		return FALSE;
	}
	return TRUE;
}

// Return Advanced Search Where based on QueryString parameters
function AdvancedSearchWhere() {
	global $Security, $keywords;
	$sWhere = "";

	// Field keyword
	BuildSearchSql($sWhere, $keywords->keyword, @$_GET["x_keyword"], @$_GET["z_keyword"], @$_GET["v_keyword"], @$_GET["y_keyword"], @$_GET["w_keyword"]);

	// Field webURL
	BuildSearchSql($sWhere, $keywords->webURL, @$_GET["x_webURL"], @$_GET["z_webURL"], @$_GET["v_webURL"], @$_GET["y_webURL"], @$_GET["w_webURL"]);

	// Field freqOfWord
	BuildSearchSql($sWhere, $keywords->freqOfWord, @$_GET["x_freqOfWord"], @$_GET["z_freqOfWord"], @$_GET["v_freqOfWord"], @$_GET["y_freqOfWord"], @$_GET["w_freqOfWord"]);

	//AdvancedSearchWhere = sWhere
	// Set up search parm

	if ($sWhere <> "") {

		// Field keyword
		SetSearchParm($keywords->keyword, @$_GET["x_keyword"], @$_GET["z_keyword"], @$_GET["v_keyword"], @$_GET["y_keyword"], @$_GET["w_keyword"]);

		// Field webURL
		SetSearchParm($keywords->webURL, @$_GET["x_webURL"], @$_GET["z_webURL"], @$_GET["v_webURL"], @$_GET["y_webURL"], @$_GET["w_webURL"]);

		// Field freqOfWord
		SetSearchParm($keywords->freqOfWord, @$_GET["x_freqOfWord"], @$_GET["z_freqOfWord"], @$_GET["v_freqOfWord"], @$_GET["y_freqOfWord"], @$_GET["w_freqOfWord"]);
	}
	return $sWhere;
}

// Build search sql
function BuildSearchSql(&$Where, &$Fld, $FldVal, $FldOpr, $FldCond, $FldVal2, $FldOpr2) {
	$sWrk = "";
	$FldParm = substr($Fld->FldVar, 2);
	$FldVal = ew_StripSlashes($FldVal);
	if (is_array($FldVal)) $FldVal = implode(",", $FldVal);
	$FldVal2 = ew_StripSlashes($FldVal2);
	if (is_array($FldVal2)) $FldVal2 = implode(",", $FldVal2);
	$FldOpr = strtoupper(trim($FldOpr));
	if ($FldOpr == "") $FldOpr = "=";
	$FldOpr2 = strtoupper(trim($FldOpr2));
	if ($FldOpr2 == "") $FldOpr2 = "=";
	if ($Fld->FldDataType == EW_DATATYPE_BOOLEAN) {
		if ($FldVal <> "") $FldVal = ($FldVal == "1") ? $Fld->TrueValue : $Fld->FalseValue;
		if ($FldVal2 <> "") $FldVal2 = ($FldVal2 == "1") ? $Fld->TrueValue : $Fld->FalseValue;
	} elseif ($Fld->FldDataType == EW_DATATYPE_DATE) {
		if ($FldVal <> "") $FldVal = ew_UnFormatDateTime($FldVal, $Fld->FldDateTimeFormat);
		if ($FldVal2 <> "") $FldVal2 = ew_UnFormatDateTime($FldVal2, $Fld->FldDateTimeFormat);
	}
	if ($FldOpr == "BETWEEN") {
		$IsValidValue = (($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal) && is_numeric($FldVal2)));
		if ($FldVal <> "" && $FldVal2 <> "" && $IsValidValue) {
			$sWrk = $Fld->FldExpression . " BETWEEN " . ew_QuotedValue($FldVal, $Fld->FldDataType) .
				" AND " . ew_QuotedValue($FldVal2, $Fld->FldDataType);
		}
	} elseif ($FldOpr == "IS NULL" || $FldOpr == "IS NOT NULL") {
		$sWrk = $Fld->FldExpression . " " . $FldOpr;
	} else {
		$IsValidValue = (($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal)));
		if ($FldVal <> "" && $IsValidValue && ew_IsValidOpr($FldOpr, $Fld->FldDataType)) {
			$sWrk = $Fld->FldExpression . SearchString($FldOpr, $FldVal, $Fld->FldDataType);
		}
		$IsValidValue = (($Fld->FldDataType <> EW_DATATYPE_NUMBER) ||
			($Fld->FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal2)));
		if ($FldVal2 <> "" && $IsValidValue && ew_IsValidOpr($FldOpr2, $Fld->FldDataType)) {
			if ($sWrk <> "") {
				$sWrk .= " " . (($FldCond=="OR")?"OR":"AND") . " ";
			}
			$sWrk .= $Fld->FldExpression . SearchString($FldOpr2, $FldVal2, $Fld->FldDataType);
		}
	}
	if ($sWrk <> "") {
		if ($Where <> "") $Where .= " AND ";
		$Where .= "(" . $sWrk . ")";
	}
}

// Return search string
function SearchString($FldOpr, $FldVal, $FldType) {
	if ($FldOpr == "LIKE" || $FldOpr == "NOT LIKE") {
		return " " . $FldOpr . " " . ew_QuotedValue("%" . $FldVal . "%", $FldType);
	} elseif ($FldOpr == "STARTS WITH") {
		return " LIKE " . ew_QuotedValue($FldVal . "%", $FldType);
	} else {
		return " " . $FldOpr . " " . ew_QuotedValue($FldVal, $FldType);
	}
}

// Set search parm
function SetSearchParm($Fld, $FldVal, $FldOpr, $FldCond, $FldVal2, $FldOpr2) {
	global $keywords;
	$FldParm = substr($Fld->FldVar, 2);
	$FldVal = ew_StripSlashes($FldVal);
	if (is_array($FldVal)) $FldVal = implode(",", $FldVal);
	$FldVal2 = ew_StripSlashes($FldVal2);
	if (is_array($FldVal2)) $FldVal2 = implode(",", $FldVal2);
	$keywords->setAdvancedSearch("x_" . $FldParm, $FldVal);
	$keywords->setAdvancedSearch("z_" . $FldParm, $FldOpr);
	$keywords->setAdvancedSearch("v_" . $FldParm, $FldCond);
	$keywords->setAdvancedSearch("y_" . $FldParm, $FldVal2);
	$keywords->setAdvancedSearch("w_" . $FldParm, $FldOpr2);
}

// Return Basic Search sql
function BasicSearchSQL($Keyword) {
	$sKeyword = ew_AdjustSql($Keyword);
	$sql = "";
	$sql .= "`keyword` LIKE '%" . $sKeyword . "%' OR ";
	if (substr($sql, -4) == " OR ") $sql = substr($sql, 0, strlen($sql)-4);
	return $sql;
}

// Return Basic Search Where based on search keyword and type
function BasicSearchWhere() {
	global $Security, $keywords;
	$sSearchStr = "";
	$sSearchKeyword = ew_StripSlashes(@$_GET[EW_TABLE_BASIC_SEARCH]);
	$sSearchType = @$_GET[EW_TABLE_BASIC_SEARCH_TYPE];
	if ($sSearchKeyword <> "") {
		$sSearch = trim($sSearchKeyword);
		if ($sSearchType <> "") {
			while (strpos($sSearch, "  ") !== FALSE)
				$sSearch = str_replace("  ", " ", $sSearch);
			$arKeyword = explode(" ", trim($sSearch));
			foreach ($arKeyword as $sKeyword) {
				if ($sSearchStr <> "") $sSearchStr .= " " . $sSearchType . " ";
				$sSearchStr .= "(" . BasicSearchSQL($sKeyword) . ")";
			}
		} else {
			$sSearchStr = BasicSearchSQL($sSearch);
		}
	}
	if ($sSearchKeyword <> "") {
		$keywords->setBasicSearchKeyword($sSearchKeyword);
		$keywords->setBasicSearchType($sSearchType);
	}
	return $sSearchStr;
}

// Clear all search parameters
function ResetSearchParms() {

	// Clear search where
	global $keywords;
	$sSrchWhere = "";
	$keywords->setSearchWhere($sSrchWhere);

	// Clear basic search parameters
	ResetBasicSearchParms();

	// Clear advanced search parameters
	ResetAdvancedSearchParms();
}

// Clear all basic search parameters
function ResetBasicSearchParms() {

	// Clear basic search parameters
	global $keywords;
	$keywords->setBasicSearchKeyword("");
	$keywords->setBasicSearchType("");
}

// Clear all advanced search parameters
function ResetAdvancedSearchParms() {

	// Clear advanced search parameters
	global $keywords;
	$keywords->setAdvancedSearch("x_keyword", "");
	$keywords->setAdvancedSearch("x_webURL", "");
	$keywords->setAdvancedSearch("x_freqOfWord", "");
	$keywords->setAdvancedSearch("z_freqOfWord", "");
}

// Restore all search parameters
function RestoreSearchParms() {
	global $sSrchWhere, $keywords;
	$sSrchWhere = $keywords->getSearchWhere();

	// Restore advanced search settings
	RestoreAdvancedSearchParms();
}

// Restore all advanced search parameters
function RestoreAdvancedSearchParms() {

	// Restore advanced search parms
	global $keywords;
	 $keywords->keyword->AdvancedSearch->SearchValue = $keywords->getAdvancedSearch("x_keyword");
	 $keywords->webURL->AdvancedSearch->SearchValue = $keywords->getAdvancedSearch("x_webURL");
	 $keywords->freqOfWord->AdvancedSearch->SearchValue = $keywords->getAdvancedSearch("x_freqOfWord");
	 $keywords->freqOfWord->AdvancedSearch->SearchOperator = $keywords->getAdvancedSearch("z_freqOfWord");
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $keywords;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$keywords->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$keywords->CurrentOrderType = @$_GET["ordertype"];

		// Field keyword
		$keywords->UpdateSort($keywords->keyword);

		// Field webURL
		$keywords->UpdateSort($keywords->webURL);

		// Field freqOfWord
		$keywords->UpdateSort($keywords->freqOfWord);

		// Field clusterNr
		$keywords->UpdateSort($keywords->clusterNr);
		$keywords->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $keywords->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($keywords->SqlOrderBy() <> "") {
			$sOrderBy = $keywords->SqlOrderBy();
			$keywords->setSessionOrderBy($sOrderBy);
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $keywords;

	// Get reset cmd
	if (@$_GET["cmd"] <> "") {
		$sCmd = $_GET["cmd"];

		// Reset search criteria
		if (strtolower($sCmd) == "reset" || strtolower($sCmd) == "resetall") {
			ResetSearchParms();
		}

		// Reset master/detail keys
		if (strtolower($sCmd) == "resetall") {
			$keywords->setMasterFilter(""); // Clear master filter
			$sDbMasterFilter = "";
			$keywords->setDetailFilter(""); // Clear detail filter
			$sDbDetailFilter = "";
			$keywords->webURL->setSessionValue("");
		}

		// Reset Sort Criteria
		if (strtolower($sCmd) == "resetsort") {
			$sOrderBy = "";
			$keywords->setSessionOrderBy($sOrderBy);
			$keywords->keyword->setSort("");
			$keywords->webURL->setSort("");
			$keywords->freqOfWord->setSort("");
			$keywords->clusterNr->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$keywords->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $keywords;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$keywords->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$keywords->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $keywords->getStartRecordNumber();
		}
	} else {
		$nStartRec = $keywords->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$keywords->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$keywords->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$keywords->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load form values
function LoadFormValues() {

	// Load from form
	global $objForm, $keywords;
	$keywords->keyword->setFormValue($objForm->GetValue("x_keyword"));
	$keywords->webURL->setFormValue($objForm->GetValue("x_webURL"));
	$keywords->freqOfWord->setFormValue($objForm->GetValue("x_freqOfWord"));
	$keywords->clusterNr->setFormValue($objForm->GetValue("x_clusterNr"));
}

// Restore form values
function RestoreFormValues() {
	global $keywords;
	$keywords->keyword->CurrentValue = $keywords->keyword->FormValue;
	$keywords->webURL->CurrentValue = $keywords->webURL->FormValue;
	$keywords->freqOfWord->CurrentValue = $keywords->freqOfWord->FormValue;
	$keywords->clusterNr->CurrentValue = $keywords->clusterNr->FormValue;
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

		// keyword
		$keywords->keyword->EditCustomAttributes = "";
		$keywords->keyword->EditValue = $keywords->keyword->CurrentValue;
		$keywords->keyword->CssStyle = "";
		$keywords->keyword->CssClass = "";
		$keywords->keyword->ViewCustomAttributes = "";

		// webURL
		$keywords->webURL->EditCustomAttributes = "";
		$keywords->webURL->EditValue = $keywords->webURL->CurrentValue;
		$keywords->webURL->CssStyle = "";
		$keywords->webURL->CssClass = "";
		$keywords->webURL->ViewCustomAttributes = "";

		// freqOfWord
		$keywords->freqOfWord->EditCustomAttributes = "";
		$keywords->freqOfWord->EditValue = ew_HtmlEncode($keywords->freqOfWord->CurrentValue);

		// clusterNr
		$keywords->clusterNr->EditCustomAttributes = "";
		if ($keywords->clusterNr->getSessionValue() <> "") {
			$keywords->clusterNr->CurrentValue = $keywords->clusterNr->getSessionValue();
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
		} else {
		$sSqlWrk = "SELECT `clusterNr`, `clusterNr`, `clusterDesc` FROM `clusters`";
		$rswrk = $conn->Execute($sSqlWrk);
		$arwrk = ($rswrk) ? $rswrk->GetRows() : array();
		if ($rswrk) $rswrk->Close();
		array_unshift($arwrk, array("", "Please Select", ""));
		$keywords->clusterNr->EditValue = $arwrk;
		}
	} elseif ($keywords->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$keywords->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $keywords;
	$sFilter = $keywords->SqlKeyFilter();
	$sFilter = str_replace("@keyword@", ew_AdjustSql($keywords->keyword->CurrentValue), $sFilter); // Replace key value
	$sFilter = str_replace("@webURL@", ew_AdjustSql($keywords->webURL->CurrentValue), $sFilter); // Replace key value
	$keywords->CurrentFilter = $sFilter;
	$sSql = $keywords->SQL();
	$conn->raiseErrorFn = 'ew_ErrorFn';
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';
	if ($rs === FALSE)
		return FALSE;
	if ($rs->EOF) {
		$EditRow = FALSE; // Update Failed
	} else {

		// Save old values
		$rsold =& $rs->fields;
		$rsnew = array();

		// Field keyword
		// Field webURL
		// Field freqOfWord

		$keywords->freqOfWord->SetDbValueDef($keywords->freqOfWord->CurrentValue, 0);
		$rsnew['freqOfWord'] =& $keywords->freqOfWord->DbValue;

		// Field clusterNr
		$keywords->clusterNr->SetDbValueDef($keywords->clusterNr->CurrentValue, 0);
		$rsnew['clusterNr'] =& $keywords->clusterNr->DbValue;

		// Call Row Updating event
		$bUpdateRow = $keywords->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($keywords->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($keywords->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $keywords->CancelMessage;
				$keywords->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Update cancelled";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$keywords->Row_Updated($rsold, $rsnew);
	}
	$rs->Close();
	return $EditRow;
}
?>
<?php

// Load advanced search
function LoadAdvancedSearch() {
	global $keywords;
}
?>
<?php

// Set up Master Detail based on querystring parameter
function SetUpMasterDetail() {
	global $nStartRec, $sDbMasterFilter, $sDbDetailFilter, $keywords;
	$bValidMaster = FALSE;

	// Get the keys for master table
	if (@$_GET[EW_TABLE_SHOW_MASTER] <> "") {
		$sMasterTblVar = $_GET[EW_TABLE_SHOW_MASTER];
		if ($sMasterTblVar == "") {
			$bValidMaster = TRUE;
			$sDbMasterFilter = "";
			$sDbDetailFilter = "";
		}
		if ($sMasterTblVar == "webpages") {
			$bValidMaster = TRUE;
			$sDbMasterFilter = $keywords->SqlMasterFilter_webpages();
			$sDbDetailFilter = $keywords->SqlDetailFilter_webpages();
			if (@$_GET["webURL"] <> "") {
				$GLOBALS["webpages"]->webURL->setQueryStringValue($_GET["webURL"]);
				$keywords->webURL->setQueryStringValue($GLOBALS["webpages"]->webURL->QueryStringValue);
				$keywords->webURL->setSessionValue($keywords->webURL->QueryStringValue);
				$sDbMasterFilter = str_replace("@webURL@", ew_AdjustSql($GLOBALS["webpages"]->webURL->QueryStringValue), $sDbMasterFilter);
				$sDbDetailFilter = str_replace("@webURL@", ew_AdjustSql($GLOBALS["webpages"]->webURL->QueryStringValue), $sDbDetailFilter);
			} else {
				$bValidMaster = FALSE;
			}
		}
	}
	if ($bValidMaster) {

		// Save current master table
		$keywords->setCurrentMasterTable($sMasterTblVar);

		// Reset start record counter (new master key)
		$nStartRec = 1;
		$keywords->setStartRecordNumber($nStartRec);
		$keywords->setMasterFilter($sDbMasterFilter); // Set up master filter
		$keywords->setDetailFilter($sDbDetailFilter); // Set up detail filter

		// Clear previous master session values
		if ($sMasterTblVar <> "webpages") {
			if ($keywords->webURL->QueryStringValue == "") $keywords->webURL->setSessionValue("");
		}
	} else {
		$sDbMasterFilter = $keywords->getMasterFilter(); //  Restore master filter
		$sDbDetailFilter = $keywords->getDetailFilter(); // Restore detail filter
	}
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
