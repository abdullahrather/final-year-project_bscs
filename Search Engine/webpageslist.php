<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
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
if ($webpages->Export == "excel") {
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

// Check QueryString parameters
if (@$_GET["a"] <> "") {
	$webpages->CurrentAction = $_GET["a"];

	// Clear inline mode
	if ($webpages->CurrentAction == "cancel") {
		ClearInlineMode();
	}

	// Switch to inline edit mode
	if ($webpages->CurrentAction == "edit") {
		InlineEditMode();
	}
} else {

	// Create form object
	$objForm = new cFormObj;
	if (@$_POST["a_list"] <> "") {
		$webpages->CurrentAction = $_POST["a_list"]; // Get action

		// Inline Update
		if ($webpages->CurrentAction == "update" && @$_SESSION[EW_SESSION_INLINE_MODE] == "edit") {
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
	$webpages->setSearchWhere($sSrchWhere); // Save to Session
	$nStartRec = 1; // Reset start record counter
	$webpages->setStartRecordNumber($nStartRec);
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

// Set up filter in Session
$webpages->setSessionWhere($sFilter);
$webpages->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$webpages->setReturnUrl("webpageslist.php");
?>
<?php include "header.php" ?>
<?php if ($webpages->Export == "") { ?>
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
		elm = fobj.elements["x" + infix + "_webURL"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Please enter required field - Web URL"))
				return false;
		}
		elm = fobj.elements["x" + infix + "_webURLFreq"];
		if (elm && !ew_HasValue(elm)) {
			if (!ew_OnError(elm, "Please enter required field - URL Frequency"))
				return false;
		}
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
<?php if ($webpages->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $webpages->Export <> "");
$bSelectLimit = ($webpages->Export == "" && $webpages->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $webpages->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<p><span class="phpmaker" style="white-space: nowrap;">TABLE: Web Links
<?php if ($webpages->Export == "") { ?>
&nbsp;&nbsp;<a href="webpageslist.php?export=excel">Export to Excel</a>
<?php } ?>
</span></p>
<?php if ($webpages->Export == "") { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<form name="fwebpageslistsrch" id="fwebpageslistsrch" action="webpageslist.php" >
<table class="ewBasicSearch">
	<tr>
		<td><span class="phpmaker">
			<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo ew_HtmlEncode($webpages->getBasicSearchKeyword()) ?>">
			<input type="Submit" name="Submit" id="Submit" value="Search (*)">&nbsp;
			<a href="webpageslist.php?cmd=reset">Show all</a>&nbsp;
			<a href="webpagessrch.php">Advanced Search</a>&nbsp;
		</span></td>
	</tr>
	<tr>
	<td><span class="phpmaker"><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="" <?php if ($webpages->getBasicSearchType() == "") { ?>checked<?php } ?>>Exact phrase&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND" <?php if ($webpages->getBasicSearchType() == "AND") { ?>checked<?php } ?>>All words&nbsp;&nbsp;<input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" id="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR" <?php if ($webpages->getBasicSearchType() == "OR") { ?>checked<?php } ?>>Any word</span></td>
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
<form name="fwebpageslist" id="fwebpageslist" action="webpageslist.php" method="post">
<?php if ($webpages->Export == "") { ?>
<table>
	<tr><td><span class="phpmaker">
<?php if ($nTotalRecs > 0) { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<a href="" onClick="if (!ew_Selected(document.fwebpageslist)) alert('No records selected'); else {document.fwebpageslist.action='webpagesdelete.php';document.fwebpageslist.encoding='application/x-www-form-urlencoded';document.fwebpageslist.submit();};return false;">Delete Selected Records</a>&nbsp;&nbsp;
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
	$OptionCnt++; // detail
}
if ($Security->IsLoggedIn()) {
	$OptionCnt++; // multi select
}
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($webpages->Export <> "") { ?>
Web URL
<?php } else { ?>
	<a href="webpageslist.php?order=<?php echo urlencode('webURL') ?>&ordertype=<?php echo $webpages->webURL->ReverseSort() ?>">Web URL&nbsp;(*)<?php if ($webpages->webURL->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($webpages->webURL->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<!--<td valign="top">
<?php if ($webpages->Export <> "") { ?>
Web Title
<?php } else { ?>
	<a href="webpageslist.php?order=<?php echo urlencode('webTitle') ?>&ordertype=<?php echo $webpages->webTitle->ReverseSort() ?>">Web Title&nbsp;(*)<?php if ($webpages->webTitle->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($webpages->webTitle->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>-->
		<td valign="top">
<?php if ($webpages->Export <> "") { ?>
URL Frequency
<?php } else { ?>
	<a href="webpageslist.php?order=<?php echo urlencode('webURLFreq') ?>&ordertype=<?php echo $webpages->webURLFreq->ReverseSort() ?>">URL Frequency<?php if ($webpages->webURLFreq->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($webpages->webURLFreq->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($webpages->Export <> "") { ?>
Date Of Crawling
<?php } else { ?>
	<a href="webpageslist.php?order=<?php echo urlencode('Date_Of_Crawling') ?>&ordertype=<?php echo $webpages->Date_Of_Crawling->ReverseSort() ?>">Date Of Crawling<?php if ($webpages->Date_Of_Crawling->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($webpages->Date_Of_Crawling->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($webpages->Export <> "") { ?>
Meta Content
<?php } else { ?>
	<a href="webpageslist.php?order=<?php echo urlencode('meta_content') ?>&ordertype=<?php echo $webpages->meta_content->ReverseSort() ?>">Meta Content<?php if ($webpages->meta_content->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($webpages->meta_content->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($webpages->Export == "") { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap>&nbsp;</td>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap><input type="checkbox" class="phpmaker" onClick="ew_SelectKey(this);"></td>
<?php } ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $webpages->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$webpages->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
$nEditRowCnt = 0;
if ($webpages->CurrentAction == "edit") $RowIndex = 1;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$webpages->CssClass = "ewTableRow";
	$webpages->CssStyle = "";

	// Init row event
	$webpages->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$webpages->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$webpages->RowType = EW_ROWTYPE_VIEW; // Render view
	if ($webpages->CurrentAction == "edit") {
		if (CheckInlineEditKey() && $nEditRowCnt == 0) { // Inline edit
			$webpages->RowType = EW_ROWTYPE_EDIT; // Render edit
		}
	}
		if ($webpages->RowType == EW_ROWTYPE_EDIT && $webpages->EventCancelled) { // Update failed
			if ($webpages->CurrentAction == "edit") {
				RestoreFormValues(); // Restore form values
			}
		}
		if ($webpages->RowType == EW_ROWTYPE_EDIT) { // Edit row
			$nEditRowCnt++;
			$webpages->CssClass = "ewTableEditRow";
			$webpages->RowClientEvents = "onmouseover='this.edit=true;ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";
		}
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $webpages->DisplayAttributes() ?>>
		<!-- webURL -->
		<td<?php echo $webpages->webURL->CellAttributes() ?>>
<?php if ($webpages->RowType == EW_ROWTYPE_EDIT) { // Edit Record ?>
<?php if ($webpages->webURL->HrefValue <> "") { ?>
<a title="<?php echo $webpages->webTitle->ViewValue ?>" href="<?php echo $webpages->webURL->HrefValue ?>" target="_blank"><div<?php echo $webpages->webURL->ViewAttributes() ?>><?php echo $webpages->webURL->EditValue ?></div></a>
<?php } else { ?>
<div<?php echo $webpages->webURL->ViewAttributes() ?>><?php echo $webpages->webURL->EditValue ?></div>
<?php } ?>
<input type="hidden" name="x<?php echo $RowIndex ?>_webURL" id="x<?php echo $RowIndex ?>_webURL" value="<?php echo ew_HtmlEncode($webpages->webURL->CurrentValue) ?>">
<?php } else { ?>
<?php if ($webpages->webURL->HrefValue <> "") { ?>
<a title="<?php echo $webpages->webTitle->ViewValue ?>" href="<?php echo $webpages->webURL->HrefValue ?>" target="_blank"><div<?php echo $webpages->webURL->ViewAttributes() ?>><?php echo $webpages->webURL->ViewValue ?></div></a>
<?php } else { ?>
<div<?php echo $webpages->webURL->ViewAttributes() ?>><?php echo $webpages->webURL->ViewValue ?></div>
<?php } ?>
<?php } ?>
</td>
		<!-- webTitle -->
		<!--<td<?php echo $webpages->webTitle->CellAttributes() ?>>
<?php if ($webpages->RowType == EW_ROWTYPE_EDIT) { // Edit Record ?>
<input type="text" name="x<?php echo $RowIndex ?>_webTitle" id="x<?php echo $RowIndex ?>_webTitle" title="" size="30" maxlength="200" value="<?php echo $webpages->webTitle->EditValue ?>"<?php echo $webpages->webTitle->EditAttributes() ?>>
<?php } else { ?>
<?php if ($webpages->webTitle->HrefValue <> "") { ?>
<a href="<?php echo $webpages->webTitle->HrefValue ?>" target="_blank"><div<?php echo $webpages->webTitle->ViewAttributes() ?>><?php echo $webpages->webTitle->ViewValue ?></div></a>
<?php } else { ?>
<div<?php echo $webpages->webTitle->ViewAttributes() ?>><?php echo $webpages->webTitle->ViewValue ?></div>
<?php } ?>
<?php } ?>
</td>-->
		<!-- webURLFreq -->
		<td<?php echo $webpages->webURLFreq->CellAttributes() ?>>
<?php if ($webpages->RowType == EW_ROWTYPE_EDIT) { // Edit Record ?>
<input type="text" name="x<?php echo $RowIndex ?>_webURLFreq" id="x<?php echo $RowIndex ?>_webURLFreq" title="" size="10" value="<?php echo $webpages->webURLFreq->EditValue ?>"<?php echo $webpages->webURLFreq->EditAttributes() ?>>
<?php } else { ?>
<div<?php echo $webpages->webURLFreq->ViewAttributes() ?>><?php echo $webpages->webURLFreq->ViewValue ?></div>
<?php } ?>
</td>
		<!-- Date_Of_Crawling -->
		<td<?php echo $webpages->Date_Of_Crawling->CellAttributes() ?>>
<?php if ($webpages->RowType == EW_ROWTYPE_EDIT) { // Edit Record ?>
<input type="text" name="x<?php echo $RowIndex ?>_Date_Of_Crawling" id="x<?php echo $RowIndex ?>_Date_Of_Crawling" title="" size="10" value="<?php echo $webpages->Date_Of_Crawling->EditValue ?>"<?php echo $webpages->Date_Of_Crawling->EditAttributes() ?>>
<?php } else { ?>
<div<?php echo $webpages->Date_Of_Crawling->ViewAttributes() ?>><?php echo $webpages->Date_Of_Crawling->ViewValue ?></div>
<?php } ?>
</td>
		<!-- meta_content -->
		<td<?php echo $webpages->meta_content->CellAttributes() ?>>
<?php if ($webpages->RowType == EW_ROWTYPE_EDIT) { // Edit Record ?>
<textarea name="x<?php echo $RowIndex ?>_meta_content" id="x<?php echo $RowIndex ?>_meta_content" cols="35" rows="4"<?php echo $webpages->meta_content->EditAttributes() ?>><?php echo $webpages->meta_content->EditValue ?></textarea>
<?php } else { ?>
<div<?php echo $webpages->meta_content->ViewAttributes() ?>><?php echo $webpages->meta_content->ViewValue ?></div>
<?php } ?>
</td>
<?php if ($webpages->RowType == EW_ROWTYPE_EDIT) { ?>
<?php if ($webpages->CurrentAction == "edit") { ?>
<td colspan="<?php echo $OptionCnt ?>"><span class="phpmaker">
<a href="" onClick="if (ew_ValidateForm(document.fwebpageslist)) document.fwebpageslist.submit();return false;">Update</a>&nbsp;<a href="webpageslist.php?a=cancel">Cancel</a>
<input type="hidden" name="a_list" id="a_list" value="update">
</span></td>
<?php } ?>
<?php } else { ?>
<?php if ($webpages->Export == "") { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap><span class="phpmaker">
<a href="<?php echo $webpages->InlineEditUrl() ?>">Inline Edit</a>
</span></td>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap><span class="phpmaker">
<?
	$qry = "SELECT COUNT(keyword) as totalKW FROM keywords WHERE webURL='".$webpages->webURL->CurrentValue."'";
	//echo $qry;
	$rec = mysql_fetch_array(mysql_query($qry));
	//print_r($rec);
	//echo $rec['totalKW']."----";
?>
<a href="keywordslist.php?<?php echo EW_TABLE_SHOW_MASTER ?>=webpages&webURL=<?php echo urlencode(strval($webpages->webURL->CurrentValue)) ?>">KeyWords (<? echo $rec['totalKW']; ?>) ...</a>
</span></td>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<td nowrap><span class="phpmaker">
<input type="checkbox" name="key_m[]" id="key_m[]" value="<?php echo ew_HtmlEncode($webpages->webURL->CurrentValue) ?>" class="phpmaker" onclick='ew_ClickMultiCheckbox(this);'>
</span></td>
<?php } ?>
<?php } ?>
<?php } ?>
	</tr>
<?php if ($webpages->RowType == EW_ROWTYPE_EDIT) { ?>
<?php } ?>
<?php
	}
	$rs->MoveNext();
}
?>
</table>
<?php if ($webpages->Export == "") { ?>
<table>
	<tr><td><span class="phpmaker">
<?php if ($nTotalRecs > 0) { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<a href="" onClick="if (!ew_Selected(document.fwebpageslist)) alert('No records selected'); else {document.fwebpageslist.action='webpagesdelete.php';document.fwebpageslist.encoding='application/x-www-form-urlencoded';document.fwebpageslist.submit();};return false;">Delete Selected Records</a>&nbsp;&nbsp;
<?php } ?>
<?php } ?>
	</span></td></tr>
</table>
<?php } ?>
<?php } ?>
<?php if ($webpages->CurrentAction == "edit") { ?>
<input type="hidden" name="key_count" id="key_count" value="<?php echo $RowIndex ?>">
<?php } ?>
</form>
<?php

// Close recordset and connection
if ($rs) $rs->Close();
?>
<?php if ($webpages->Export == "") { ?>
<form action="webpageslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td nowrap>
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="webpageslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>First</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="webpageslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Previous</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="webpageslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="webpageslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Next</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="webpageslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Last</b></a>&nbsp;
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
<option value="ALL"<?php if ($webpages->getRecordsPerPage() == -1) echo " selected" ?>>All Records</option>
</select>
		</span></td>
<?php } ?>
	</tr>
</table>
</form>
<?php } ?>
<?php if ($webpages->Export == "") { ?>
<?php } ?>
<?php if ($webpages->Export == "") { ?>
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
	global $nDisplayRecs, $nStartRec, $webpages;
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
		$webpages->setRecordsPerPage($nDisplayRecs); // Save to Session

		// Reset start position
		$nStartRec = 1;
		$webpages->setStartRecordNumber($nStartRec);
	} else {
		if ($webpages->getRecordsPerPage() <> "") {
			$nDisplayRecs = $webpages->getRecordsPerPage(); // Restore from Session
		} else {
			$nDisplayRecs = 20; // Load default
		}
	}
}

//  Exit out of inline mode
function ClearInlineMode() {
	global $webpages;
	$webpages->setKey("webURL", ""); // Clear inline edit key
	$webpages->CurrentAction = ""; // Clear action
	$_SESSION[EW_SESSION_INLINE_MODE] = ""; // Clear inline mode
}

// Switch to Inline Edit Mode
function InlineEditMode() {
	global $Security, $webpages;
	$bInlineEdit = TRUE;
	if (@$_GET["webURL"] <> "") {
		$webpages->webURL->setQueryStringValue($_GET["webURL"]);
	} else {
		$bInlineEdit = FALSE;
	}
	if ($bInlineEdit) {
		if (LoadRow()) {
			$webpages->setKey("webURL", $webpages->webURL->CurrentValue); // Set up inline edit key
			$_SESSION[EW_SESSION_INLINE_MODE] = "edit"; // Enable inline edit
		}
	}
}

// Peform update to inline edit record
function InlineUpdate() {
	global $objForm, $webpages;
	$objForm->Index = 1; 
	LoadFormValues(); // Get form values
	if (CheckInlineEditKey()) { // Check key
		$webpages->SendEmail = TRUE; // Send email on update success
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
		$webpages->EventCancelled = TRUE; // Cancel event
		$webpages->CurrentAction = "edit"; // Stay in edit mode
	}
}

// Check inline edit key
function CheckInlineEditKey() {
	global $webpages;

	//CheckInlineEditKey = True
	if (strval($webpages->getKey("webURL")) <> strval($webpages->webURL->CurrentValue)) {
		return FALSE;
	}
	return TRUE;
}

// Return Advanced Search Where based on QueryString parameters
function AdvancedSearchWhere() {
	global $Security, $webpages;
	$sWhere = "";

	// Field webURL
	BuildSearchSql($sWhere, $webpages->webURL, @$_GET["x_webURL"], @$_GET["z_webURL"], @$_GET["v_webURL"], @$_GET["y_webURL"], @$_GET["w_webURL"]);

	// Field webTitle
	BuildSearchSql($sWhere, $webpages->webTitle, @$_GET["x_webTitle"], @$_GET["z_webTitle"], @$_GET["v_webTitle"], @$_GET["y_webTitle"], @$_GET["w_webTitle"]);

	// Field webURLFreq
	BuildSearchSql($sWhere, $webpages->webURLFreq, @$_GET["x_webURLFreq"], @$_GET["z_webURLFreq"], @$_GET["v_webURLFreq"], @$_GET["y_webURLFreq"], @$_GET["w_webURLFreq"]);

	// Field Date_Of_Crawling
	BuildSearchSql($sWhere, $webpages->Date_Of_Crawling, ew_UnFormatDateTime(@$_GET["x_Date_Of_Crawling"],6), @$_GET["z_Date_Of_Crawling"], @$_GET["v_Date_Of_Crawling"], ew_UnFormatDateTime(@$_GET["y_Date_Of_Crawling"],6), @$_GET["w_Date_Of_Crawling"]);

	// Field meta_content
	BuildSearchSql($sWhere, $webpages->meta_content, @$_GET["x_meta_content"], @$_GET["z_meta_content"], @$_GET["v_meta_content"], @$_GET["y_meta_content"], @$_GET["w_meta_content"]);

	//AdvancedSearchWhere = sWhere
	// Set up search parm

	if ($sWhere <> "") {

		// Field webURL
		SetSearchParm($webpages->webURL, @$_GET["x_webURL"], @$_GET["z_webURL"], @$_GET["v_webURL"], @$_GET["y_webURL"], @$_GET["w_webURL"]);

		// Field webTitle
		SetSearchParm($webpages->webTitle, @$_GET["x_webTitle"], @$_GET["z_webTitle"], @$_GET["v_webTitle"], @$_GET["y_webTitle"], @$_GET["w_webTitle"]);

		// Field webURLFreq
		SetSearchParm($webpages->webURLFreq, @$_GET["x_webURLFreq"], @$_GET["z_webURLFreq"], @$_GET["v_webURLFreq"], @$_GET["y_webURLFreq"], @$_GET["w_webURLFreq"]);

		// Field Date_Of_Crawling
		SetSearchParm($webpages->Date_Of_Crawling, ew_UnFormatDateTime(@$_GET["x_Date_Of_Crawling"],6), @$_GET["z_Date_Of_Crawling"], @$_GET["v_Date_Of_Crawling"], ew_UnFormatDateTime(@$_GET["y_Date_Of_Crawling"],6), @$_GET["w_Date_Of_Crawling"]);

		// Field meta_content
		SetSearchParm($webpages->meta_content, @$_GET["x_meta_content"], @$_GET["z_meta_content"], @$_GET["v_meta_content"], @$_GET["y_meta_content"], @$_GET["w_meta_content"]);
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
	global $webpages;
	$FldParm = substr($Fld->FldVar, 2);
	$FldVal = ew_StripSlashes($FldVal);
	if (is_array($FldVal)) $FldVal = implode(",", $FldVal);
	$FldVal2 = ew_StripSlashes($FldVal2);
	if (is_array($FldVal2)) $FldVal2 = implode(",", $FldVal2);
	$webpages->setAdvancedSearch("x_" . $FldParm, $FldVal);
	$webpages->setAdvancedSearch("z_" . $FldParm, $FldOpr);
	$webpages->setAdvancedSearch("v_" . $FldParm, $FldCond);
	$webpages->setAdvancedSearch("y_" . $FldParm, $FldVal2);
	$webpages->setAdvancedSearch("w_" . $FldParm, $FldOpr2);
}

// Return Basic Search sql
function BasicSearchSQL($Keyword) {
	$sKeyword = ew_AdjustSql($Keyword);
	$sql = "";
	$sql .= "`webURL` LIKE '%" . $sKeyword . "%' OR ";
	$sql .= "`webTitle` LIKE '%" . $sKeyword . "%' OR ";
	if (substr($sql, -4) == " OR ") $sql = substr($sql, 0, strlen($sql)-4);
	return $sql;
}

// Return Basic Search Where based on search keyword and type
function BasicSearchWhere() {
	global $Security, $webpages;
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
		$webpages->setBasicSearchKeyword($sSearchKeyword);
		$webpages->setBasicSearchType($sSearchType);
	}
	return $sSearchStr;
}

// Clear all search parameters
function ResetSearchParms() {

	// Clear search where
	global $webpages;
	$sSrchWhere = "";
	$webpages->setSearchWhere($sSrchWhere);

	// Clear basic search parameters
	ResetBasicSearchParms();

	// Clear advanced search parameters
	ResetAdvancedSearchParms();
}

// Clear all basic search parameters
function ResetBasicSearchParms() {

	// Clear basic search parameters
	global $webpages;
	$webpages->setBasicSearchKeyword("");
	$webpages->setBasicSearchType("");
}

// Clear all advanced search parameters
function ResetAdvancedSearchParms() {

	// Clear advanced search parameters
	global $webpages;
	$webpages->setAdvancedSearch("x_webURL", "");
	$webpages->setAdvancedSearch("x_webTitle", "");
	$webpages->setAdvancedSearch("x_webURLFreq", "");
	$webpages->setAdvancedSearch("z_webURLFreq", "");
	$webpages->setAdvancedSearch("x_Date_Of_Crawling", "");
	$webpages->setAdvancedSearch("z_Date_Of_Crawling", "");
	$webpages->setAdvancedSearch("x_meta_content", "");
}

// Restore all search parameters
function RestoreSearchParms() {
	global $sSrchWhere, $webpages;
	$sSrchWhere = $webpages->getSearchWhere();

	// Restore advanced search settings
	RestoreAdvancedSearchParms();
}

// Restore all advanced search parameters
function RestoreAdvancedSearchParms() {

	// Restore advanced search parms
	global $webpages;
	 $webpages->webURL->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_webURL");
	 $webpages->webTitle->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_webTitle");
	 $webpages->webURLFreq->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_webURLFreq");
	 $webpages->webURLFreq->AdvancedSearch->SearchOperator = $webpages->getAdvancedSearch("z_webURLFreq");
	 $webpages->Date_Of_Crawling->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_Date_Of_Crawling");
	 $webpages->Date_Of_Crawling->AdvancedSearch->SearchOperator = $webpages->getAdvancedSearch("z_Date_Of_Crawling");
	 $webpages->meta_content->AdvancedSearch->SearchValue = $webpages->getAdvancedSearch("x_meta_content");
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $webpages;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$webpages->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$webpages->CurrentOrderType = @$_GET["ordertype"];

		// Field webURL
		$webpages->UpdateSort($webpages->webURL);

		// Field webTitle
		$webpages->UpdateSort($webpages->webTitle);

		// Field webURLFreq
		$webpages->UpdateSort($webpages->webURLFreq);

		// Field Date_Of_Crawling
		$webpages->UpdateSort($webpages->Date_Of_Crawling);

		// Field meta_content
		$webpages->UpdateSort($webpages->meta_content);
		$webpages->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $webpages->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($webpages->SqlOrderBy() <> "") {
			$sOrderBy = $webpages->SqlOrderBy();
			$webpages->setSessionOrderBy($sOrderBy);
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $webpages;

	// Get reset cmd
	if (@$_GET["cmd"] <> "") {
		$sCmd = $_GET["cmd"];

		// Reset search criteria
		if (strtolower($sCmd) == "reset" || strtolower($sCmd) == "resetall") {
			ResetSearchParms();
		}

		// Reset Sort Criteria
		if (strtolower($sCmd) == "resetsort") {
			$sOrderBy = "";
			$webpages->setSessionOrderBy($sOrderBy);
			$webpages->webURL->setSort("");
			$webpages->webTitle->setSort("");
			$webpages->webURLFreq->setSort("");
			$webpages->Date_Of_Crawling->setSort("");
			$webpages->meta_content->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$webpages->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $webpages;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$webpages->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$webpages->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $webpages->getStartRecordNumber();
		}
	} else {
		$nStartRec = $webpages->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$webpages->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$webpages->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$webpages->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load form values
function LoadFormValues() {

	// Load from form
	global $objForm, $webpages;
	$webpages->webURL->setFormValue($objForm->GetValue("x_webURL"));
	$webpages->webTitle->setFormValue($objForm->GetValue("x_webTitle"));
	$webpages->webURLFreq->setFormValue($objForm->GetValue("x_webURLFreq"));
	$webpages->Date_Of_Crawling->setFormValue($objForm->GetValue("x_Date_Of_Crawling"));
	$webpages->Date_Of_Crawling->CurrentValue = ew_UnFormatDateTime($webpages->Date_Of_Crawling->CurrentValue, 6);
	$webpages->meta_content->setFormValue($objForm->GetValue("x_meta_content"));
}

// Restore form values
function RestoreFormValues() {
	global $webpages;
	$webpages->webURL->CurrentValue = $webpages->webURL->FormValue;
	$webpages->webTitle->CurrentValue = $webpages->webTitle->FormValue;
	$webpages->webURLFreq->CurrentValue = $webpages->webURLFreq->FormValue;
	$webpages->Date_Of_Crawling->CurrentValue = $webpages->Date_Of_Crawling->FormValue;
	$webpages->Date_Of_Crawling->CurrentValue = ew_UnFormatDateTime($webpages->Date_Of_Crawling->CurrentValue, 6);
	$webpages->meta_content->CurrentValue = $webpages->meta_content->FormValue;
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

		// webURL
		$webpages->webURL->EditCustomAttributes = "";
		$webpages->webURL->EditValue = $webpages->webURL->CurrentValue;
		$webpages->webURL->CssStyle = "";
		$webpages->webURL->CssClass = "";
		$webpages->webURL->ViewCustomAttributes = "";

		// webTitle
		$webpages->webTitle->EditCustomAttributes = "";
		$webpages->webTitle->EditValue = ew_HtmlEncode($webpages->webTitle->CurrentValue);

		// webURLFreq
		$webpages->webURLFreq->EditCustomAttributes = "";
		$webpages->webURLFreq->EditValue = ew_HtmlEncode($webpages->webURLFreq->CurrentValue);

		// Date_Of_Crawling
		$webpages->Date_Of_Crawling->EditCustomAttributes = "";
		$webpages->Date_Of_Crawling->EditValue = ew_HtmlEncode(ew_FormatDateTime($webpages->Date_Of_Crawling->CurrentValue, 6));

		// meta_content
		$webpages->meta_content->EditCustomAttributes = "";
		$webpages->meta_content->EditValue = ew_HtmlEncode($webpages->meta_content->CurrentValue);
	} elseif ($webpages->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$webpages->Row_Rendered();
}
?>
<?php

// Update record based on key values
function EditRow() {
	global $conn, $Security, $webpages;
	$sFilter = $webpages->SqlKeyFilter();
	$sFilter = str_replace("@webURL@", ew_AdjustSql($webpages->webURL->CurrentValue), $sFilter); // Replace key value
	$webpages->CurrentFilter = $sFilter;
	$sSql = $webpages->SQL();
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

		// Field webURL
		// Field webTitle

		$webpages->webTitle->SetDbValueDef($webpages->webTitle->CurrentValue, NULL);
		$rsnew['webTitle'] =& $webpages->webTitle->DbValue;

		// Field webURLFreq
		$webpages->webURLFreq->SetDbValueDef($webpages->webURLFreq->CurrentValue, 0);
		$rsnew['webURLFreq'] =& $webpages->webURLFreq->DbValue;

		// Field Date_Of_Crawling
		$webpages->Date_Of_Crawling->SetDbValueDef(ew_UnFormatDateTime($webpages->Date_Of_Crawling->CurrentValue, 6), NULL);
		$rsnew['Date_Of_Crawling'] =& $webpages->Date_Of_Crawling->DbValue;

		// Field meta_content
		$webpages->meta_content->SetDbValueDef($webpages->meta_content->CurrentValue, NULL);
		$rsnew['meta_content'] =& $webpages->meta_content->DbValue;

		// Call Row Updating event
		$bUpdateRow = $webpages->Row_Updating($rsold, $rsnew);
		if ($bUpdateRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$EditRow = $conn->Execute($webpages->UpdateSQL($rsnew));
			$conn->raiseErrorFn = '';
		} else {
			if ($webpages->CancelMessage <> "") {
				$_SESSION[EW_SESSION_MESSAGE] = $webpages->CancelMessage;
				$webpages->CancelMessage = "";
			} else {
				$_SESSION[EW_SESSION_MESSAGE] = "Update cancelled";
			}
			$EditRow = FALSE;
		}
	}

	// Call Row Updated event
	if ($EditRow) {
		$webpages->Row_Updated($rsold, $rsnew);
	}
	$rs->Close();
	return $EditRow;
}
?>
<?php

// Load advanced search
function LoadAdvancedSearch() {
	global $webpages;
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
