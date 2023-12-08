<?php
define("EW_PAGE_ID", "list", TRUE); // Page ID
define("EW_TABLE_NAME", 'web_users', TRUE);
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
$web_users->Export = @$_GET["export"]; // Get export parameter
$sExport = $web_users->Export; // Get export parameter, used in header
$sExportFile = $web_users->TableVar; // Get export file, used in header
?>
<?php
if ($web_users->Export == "excel") {
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
$web_users->setSessionWhere($sFilter);
$web_users->CurrentFilter = "";

// Set Up Sorting Order
SetUpSortOrder();

// Set Return Url
$web_users->setReturnUrl("web_userslist.php");
?>
<?php include "header.php" ?>
<?php if ($web_users->Export == "") { ?>
<script type="text/javascript">
<!--
var EW_PAGE_ID = "list"; // Page id

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
<?php if ($web_users->Export == "") { ?>
<?php } ?>
<?php

// Load recordset
$bExportAll = (defined("EW_EXPORT_ALL") && $web_users->Export <> "");
$bSelectLimit = ($web_users->Export == "" && $web_users->SelectLimit);
if (!$bSelectLimit) $rs = LoadRecordset();
$nTotalRecs = ($bSelectLimit) ? $web_users->SelectRecordCount() : $rs->RecordCount();
$nStartRec = 1;
if ($nDisplayRecs <= 0) $nDisplayRecs = $nTotalRecs; // Display all records
if (!$bExportAll) SetUpStartRec(); // Set up start record position
if ($bSelectLimit) $rs = LoadRecordset($nStartRec-1, $nDisplayRecs);
?>
<p><span class="phpmaker" style="white-space: nowrap;">TABLE: User Registration
<?php if ($web_users->Export == "") { ?>
&nbsp;&nbsp;<a href="web_userslist.php?export=excel">Export to Excel</a>
<?php } ?>
</span></p>
<?php if ($web_users->Export == "") { ?>
<?php } ?>
<?php
if (@$_SESSION[EW_SESSION_MESSAGE] <> "") {
?>
<p><span class="ewmsg"><?php echo $_SESSION[EW_SESSION_MESSAGE] ?></span></p>
<?php
	$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message
}
?>
<form method="post" name="fweb_userslist" id="fweb_userslist">
<?php if ($web_users->Export == "") { ?>
<table>
	<tr><td><span class="phpmaker">
	</span></td></tr>
</table>
<?php } ?>
<?php if ($nTotalRecs > 0) { ?>
<table id="ewlistmain" class="ewTable">
<?php
	$OptionCnt = 0;
?>
	<!-- Table header -->
	<tr class="ewTableHeader">
		<td valign="top">
<?php if ($web_users->Export <> "") { ?>
Login ID
<?php } else { ?>
	<a href="web_userslist.php?order=<?php echo urlencode('userID') ?>&ordertype=<?php echo $web_users->userID->ReverseSort() ?>">Login ID<?php if ($web_users->userID->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($web_users->userID->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($web_users->Export <> "") { ?>
User Name
<?php } else { ?>
	<a href="web_userslist.php?order=<?php echo urlencode('userName') ?>&ordertype=<?php echo $web_users->userName->ReverseSort() ?>">User Name<?php if ($web_users->userName->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($web_users->userName->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($web_users->Export <> "") { ?>
Email Address
<?php } else { ?>
	<a href="web_userslist.php?order=<?php echo urlencode('userEmail') ?>&ordertype=<?php echo $web_users->userEmail->ReverseSort() ?>">Email Address<?php if ($web_users->userEmail->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($web_users->userEmail->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
		<td valign="top">
<?php if ($web_users->Export <> "") { ?>
Password
<?php } else { ?>
	<a href="web_userslist.php?order=<?php echo urlencode('userPassword') ?>&ordertype=<?php echo $web_users->userPassword->ReverseSort() ?>">Password<?php if ($web_users->userPassword->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" border="0"><?php } elseif ($web_users->userPassword->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" border="0"><?php } ?></a>
<?php } ?>
		</td>
<?php if ($web_users->Export == "") { ?>
<?php } ?>
	</tr>
<?php
if (defined("EW_EXPORT_ALL") && $web_users->Export <> "") {
	$nStopRec = $nTotalRecs;
} else {
	$nStopRec = $nStartRec + $nDisplayRecs - 1; // Set the last record to display
}
$nRecCount = $nStartRec - 1;
if (!$rs->EOF) {
	$rs->MoveFirst();
	if (!$web_users->SelectLimit) $rs->Move($nStartRec - 1); // Move to first record directly
}
$RowCnt = 0;
while (!$rs->EOF && $nRecCount < $nStopRec) {
	$nRecCount++;
	if (intval($nRecCount) >= intval($nStartRec)) {
		$RowCnt++;

	// Init row class and style
	$web_users->CssClass = "ewTableRow";
	$web_users->CssStyle = "";

	// Init row event
	$web_users->RowClientEvents = "onmouseover='ew_MouseOver(this);' onmouseout='ew_MouseOut(this);' onclick='ew_Click(this);'";

	// Display alternate color for rows
	if ($RowCnt % 2 == 0) {
		$web_users->CssClass = "ewTableAltRow";
	}
	LoadRowValues($rs); // Load row values
	$web_users->RowType = EW_ROWTYPE_VIEW; // Render view
	RenderRow();
?>
	<!-- Table body -->
	<tr<?php echo $web_users->DisplayAttributes() ?>>
		<!-- userID -->
		<td<?php echo $web_users->userID->CellAttributes() ?>>
<div<?php echo $web_users->userID->ViewAttributes() ?>><?php echo $web_users->userID->ViewValue ?></div>
</td>
		<!-- userName -->
		<td<?php echo $web_users->userName->CellAttributes() ?>>
<div<?php echo $web_users->userName->ViewAttributes() ?>><?php echo $web_users->userName->ViewValue ?></div>
</td>
		<!-- userEmail -->
		<td<?php echo $web_users->userEmail->CellAttributes() ?>>
<div<?php echo $web_users->userEmail->ViewAttributes() ?>><?php echo $web_users->userEmail->ViewValue ?></div>
</td>
		<!-- userPassword -->
		<td<?php echo $web_users->userPassword->CellAttributes() ?>>
<div<?php echo $web_users->userPassword->ViewAttributes() ?>><?php echo $web_users->userPassword->ViewValue ?></div>
</td>
<?php if ($web_users->Export == "") { ?>
<?php } ?>
	</tr>
<?php
	}
	$rs->MoveNext();
}
?>
</table>
<?php if ($web_users->Export == "") { ?>
<table>
	<tr><td><span class="phpmaker">
	</span></td></tr>
</table>
<?php } ?>
<?php } ?>
</form>
<?php

// Close recordset and connection
if ($rs) $rs->Close();
?>
<?php if ($web_users->Export == "") { ?>
<form action="web_userslist.php" name="ewpagerform" id="ewpagerform">
<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td nowrap>
<span class="phpmaker">
<?php if (!isset($Pager)) $Pager = new cNumericPager($nStartRec, $nDisplayRecs, $nTotalRecs, $nRecRange) ?>
<?php if ($Pager->RecordCount > 0) { ?>
	<?php if ($Pager->FirstButton->Enabled) { ?>
	<a href="web_userslist.php?start=<?php echo $Pager->FirstButton->Start ?>"><b>First</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->PrevButton->Enabled) { ?>
	<a href="web_userslist.php?start=<?php echo $Pager->PrevButton->Start ?>"><b>Previous</b></a>&nbsp;
	<?php } ?>
	<?php foreach ($Pager->Items as $PagerItem) { ?>
		<?php if ($PagerItem->Enabled) { ?><a href="web_userslist.php?start=<?php echo $PagerItem->Start ?>"><?php } ?><b><?php echo $PagerItem->Text ?></b><?php if ($PagerItem->Enabled) { ?></a><?php } ?>&nbsp;
	<?php } ?>
	<?php if ($Pager->NextButton->Enabled) { ?>
	<a href="web_userslist.php?start=<?php echo $Pager->NextButton->Start ?>"><b>Next</b></a>&nbsp;
	<?php } ?>
	<?php if ($Pager->LastButton->Enabled) { ?>
	<a href="web_userslist.php?start=<?php echo $Pager->LastButton->Start ?>"><b>Last</b></a>&nbsp;
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
<option value="ALL"<?php if ($web_users->getRecordsPerPage() == -1) echo " selected" ?>>All Records</option>
</select>
		</span></td>
<?php } ?>
	</tr>
</table>
</form>
<?php } ?>
<?php if ($web_users->Export == "") { ?>
<?php } ?>
<?php if ($web_users->Export == "") { ?>
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
	global $nDisplayRecs, $nStartRec, $web_users;
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
		$web_users->setRecordsPerPage($nDisplayRecs); // Save to Session

		// Reset start position
		$nStartRec = 1;
		$web_users->setStartRecordNumber($nStartRec);
	} else {
		if ($web_users->getRecordsPerPage() <> "") {
			$nDisplayRecs = $web_users->getRecordsPerPage(); // Restore from Session
		} else {
			$nDisplayRecs = 20; // Load default
		}
	}
}

// Set up Sort parameters based on Sort Links clicked
function SetUpSortOrder() {
	global $web_users;

	// Check for an Order parameter
	if (@$_GET["order"] <> "") {
		$web_users->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
		$web_users->CurrentOrderType = @$_GET["ordertype"];

		// Field userID
		$web_users->UpdateSort($web_users->userID);

		// Field userName
		$web_users->UpdateSort($web_users->userName);

		// Field userEmail
		$web_users->UpdateSort($web_users->userEmail);

		// Field userPassword
		$web_users->UpdateSort($web_users->userPassword);
		$web_users->setStartRecordNumber(1); // Reset start position
	}
	$sOrderBy = $web_users->getSessionOrderBy(); // Get order by from Session
	if ($sOrderBy == "") {
		if ($web_users->SqlOrderBy() <> "") {
			$sOrderBy = $web_users->SqlOrderBy();
			$web_users->setSessionOrderBy($sOrderBy);
		}
	}
}

// Reset command based on querystring parameter cmd=
// - RESET: reset search parameters
// - RESETALL: reset search & master/detail parameters
// - RESETSORT: reset sort parameters
function ResetCmd() {
	global $sDbMasterFilter, $sDbDetailFilter, $nStartRec, $sOrderBy;
	global $web_users;

	// Get reset cmd
	if (@$_GET["cmd"] <> "") {
		$sCmd = $_GET["cmd"];

		// Reset Sort Criteria
		if (strtolower($sCmd) == "resetsort") {
			$sOrderBy = "";
			$web_users->setSessionOrderBy($sOrderBy);
			$web_users->userID->setSort("");
			$web_users->userName->setSort("");
			$web_users->userEmail->setSort("");
			$web_users->userPassword->setSort("");
		}

		// Reset start position
		$nStartRec = 1;
		$web_users->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Set up Starting Record parameters based on Pager Navigation
function SetUpStartRec() {
	global $nDisplayRecs, $nStartRec, $nTotalRecs, $nPageNo, $web_users;
	if ($nDisplayRecs == 0) return;

	// Check for a START parameter
	if (@$_GET[EW_TABLE_START_REC] <> "") {
		$nStartRec = $_GET[EW_TABLE_START_REC];
		$web_users->setStartRecordNumber($nStartRec);
	} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
		$nPageNo = $_GET[EW_TABLE_PAGE_NO];
		if (is_numeric($nPageNo)) {
			$nStartRec = ($nPageNo-1)*$nDisplayRecs+1;
			if ($nStartRec <= 0) {
				$nStartRec = 1;
			} elseif ($nStartRec >= intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1) {
				$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1;
			}
			$web_users->setStartRecordNumber($nStartRec);
		} else {
			$nStartRec = $web_users->getStartRecordNumber();
		}
	} else {
		$nStartRec = $web_users->getStartRecordNumber();
	}

	// Check if correct start record counter
	if (!is_numeric($nStartRec) || $nStartRec == "") { // Avoid invalid start record counter
		$nStartRec = 1; // Reset start record counter
		$web_users->setStartRecordNumber($nStartRec);
	} elseif (intval($nStartRec) > intval($nTotalRecs)) { // Avoid starting record > total records
		$nStartRec = intval(($nTotalRecs-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to last page first record
		$web_users->setStartRecordNumber($nStartRec);
	} elseif (($nStartRec-1) % $nDisplayRecs <> 0) {
		$nStartRec = intval(($nStartRec-1)/$nDisplayRecs)*$nDisplayRecs+1; // Point to page boundary
		$web_users->setStartRecordNumber($nStartRec);
	}
}
?>
<?php

// Load recordset
function LoadRecordset($offset = -1, $rowcnt = -1) {
	global $conn, $web_users;

	// Call Recordset Selecting event
	$web_users->Recordset_Selecting($web_users->CurrentFilter);

	// Load list page sql
	$sSql = $web_users->SelectSQL();
	if ($offset > -1 && $rowcnt > -1) $sSql .= " LIMIT $offset, $rowcnt";

	// Load recordset
	$conn->raiseErrorFn = 'ew_ErrorFn';	
	$rs = $conn->Execute($sSql);
	$conn->raiseErrorFn = '';

	// Call Recordset Selected event
	$web_users->Recordset_Selected($rs);
	return $rs;
}
?>
<?php

// Load row based on key values
function LoadRow() {
	global $conn, $Security, $web_users;
	$sFilter = $web_users->SqlKeyFilter();
	$sFilter = str_replace("@userID@", ew_AdjustSql($web_users->userID->CurrentValue), $sFilter); // Replace key value

	// Call Row Selecting event
	$web_users->Row_Selecting($sFilter);

	// Load sql based on filter
	$web_users->CurrentFilter = $sFilter;
	$sSql = $web_users->SQL();
	if ($rs = $conn->Execute($sSql)) {
		if ($rs->EOF) {
			$LoadRow = FALSE;
		} else {
			$LoadRow = TRUE;
			$rs->MoveFirst();
			LoadRowValues($rs); // Load row values

			// Call Row Selected event
			$web_users->Row_Selected($rs);
		}
		$rs->Close();
	} else {
		$LoadRow = FALSE;
	}
	return $LoadRow;
}

// Load row values from recordset
function LoadRowValues(&$rs) {
	global $web_users;
	$web_users->userID->setDbValue($rs->fields('userID'));
	$web_users->userName->setDbValue($rs->fields('userName'));
	$web_users->userEmail->setDbValue($rs->fields('userEmail'));
	$web_users->userPassword->setDbValue($rs->fields('userPassword'));
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

		// userID
		$web_users->userID->ViewValue = $web_users->userID->CurrentValue;
		$web_users->userID->CssStyle = "";
		$web_users->userID->CssClass = "";
		$web_users->userID->ViewCustomAttributes = "";

		// userName
		$web_users->userName->ViewValue = $web_users->userName->CurrentValue;
		$web_users->userName->CssStyle = "";
		$web_users->userName->CssClass = "";
		$web_users->userName->ViewCustomAttributes = "";

		// userEmail
		$web_users->userEmail->ViewValue = $web_users->userEmail->CurrentValue;
		$web_users->userEmail->CssStyle = "";
		$web_users->userEmail->CssClass = "";
		$web_users->userEmail->ViewCustomAttributes = "";

		// userPassword
		$web_users->userPassword->ViewValue = "********";
		$web_users->userPassword->CssStyle = "";
		$web_users->userPassword->CssClass = "";
		$web_users->userPassword->ViewCustomAttributes = "";

		// userID
		$web_users->userID->HrefValue = "";

		// userName
		$web_users->userName->HrefValue = "";

		// userEmail
		$web_users->userEmail->HrefValue = "";

		// userPassword
		$web_users->userPassword->HrefValue = "";
	} elseif ($web_users->RowType == EW_ROWTYPE_ADD) { // Add row
	} elseif ($web_users->RowType == EW_ROWTYPE_EDIT) { // Edit row
	} elseif ($web_users->RowType == EW_ROWTYPE_SEARCH) { // Search row
	}

	// Call Row Rendered event
	$web_users->Row_Rendered();
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
