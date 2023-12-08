<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<?php
include "stemmer.php";
include "DBconnection.php";
include "paging.class.php";

$p = $_REQUEST;//&$HTTP_POST_VARS;
//$g = &$HTTP_GET_VARS;
$words="";
if(isset($p['textfield']))
		$words = $p['textfield'];
//else if(isset($g['textfield']))
//		$words = $g['textfield'];
//else
//		$words = "the is an pakistan hostel army arm policy police <meta> html>";

/*
$word[] = array();
$word = explode(" ", $words);
for($i=0; $i<count($word); $i++) {
	$stem = PorterStemmer::Stem($word[$i]);
	echo $word[$i]." - ".$stem."<br />";
}*/
?>
<html>
<head>
<title>Search Engine With Int. Web Spider</title>
<link href="FYP_byMaker.css" rel="stylesheet" type="text/css" />
</head>
<body>

<table width="100%" height="100%" border="0">
<tr class="ewHeaderRow"><!-- *** Note: Only licensed users are allowed to change the logo *** -->
	<td height="50" valign="top" colspan="2"><img src="aspmkrlogo6.png" alt="" border="0">
	</td>
</tr>
<tr>
	<td height="100%" class="ewMenuColumn"><span class="phpmaker"><a href="index.php">Web Portal</a></span>
		<table height="100%" border="0" cellspacing="0" cellpadding="0">
			<tr><td class="ewMenuColumn"><!-- Area below Left Nav -->&nbsp;</td></tr> 
		</table> 
	</td>
	<td height="100%" class="ewContentColumn" valign="top">
	
	<form id="form2" name="form2" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		  <p>
			<input type="text" name="textfield" size="50" />
			<input type="submit" name="Submit" value="Search" />
			<input type="reset" name="reset" value="Clear" />
		</p>
		</form>
		<br />
		
	<?php
		echo "<b>Stemming Results:</b><br />";
		
		$keyWrdsArray[] = array();
		$keyWrdsArray = PorterStemmer::StemIt(explode(" ", $words));
		
		if(count($keyWrdsArray) <= 1) {
			echo "Please enter a keyword to search!<br />";
		} else {	
			//print_r($keyWrdsArray);
			
			$sqlreqqry="SELECT * FROM webpages WHERE webpages.webURL IN (SELECT webURL FROM keywords WHERE keywords.keyword = '".$keyWrdsArray[1]."' ";
			for( $i=2; $i<count($keyWrdsArray); $i++) {
				$sqlreqqry .= "OR keywords.keyword = '". $keyWrdsArray[$i] ."' ";
			}
			$sqlreqqry .= ")";
			//echo $sqlreqqry;
			
			$Obj=new Paging($sqlreqqry);
			$Obj->setLimit(5);//result per page
			$limit=$Obj->getLimit();
			$offset=$Obj->getOffset(@$_REQUEST["page"]);
			$Obj->setParameter("&textfield=".$words."");
			$Obj->setStyle("");
			$Obj->setActiveStyle("");
			$Obj->setTableParams("100%","top");
			
			$sqlreqqry = $sqlreqqry." limit ".$offset.",".$limit."";
			//echo $sqlreqqry;
			
			if($reqkeys = $db->get_results($sqlreqqry))
			{
				$sno=$offset+1;
				$color=0;
				echo "<br /><b>Search Results:</b><br /><br /><table border=0 cellspacing=5 cellpadding=2>";
				foreach ( $reqkeys as $reqkey)
				{	
					if($color==0)
					{
						$bgcolor="#E2E2E2";
						$color = 1;
					}
					else
					{
						$bgcolor="#CCCCCC";
						$color = 0;
					}
					
					echo "<tr bgcolor='".$bgcolor."'>
					<td>";
						echo $sno.". <a target='_blank' href='".$reqkey->webURL."'>".$reqkey->webTitle."</a>";
					echo "</td></tr>";
					$sno++;					
				}
				echo "<tr><td>";
				$Obj->getPageNo(null,null,null);
				echo "</td></tr></table>";
			} else
				echo "<br /><b>No Result Found!</b><br />";
		}
		?>
		
		
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<p>&nbsp;</p>
	</td>
</tr>
<tr class="ewFooterRow">
	<td colspan="2" height="20px"><font class="ewFooterText">&copy;2021 IQRA University. All rights reserved.</font>
	</td>
</tr>
</table>
</body>
</html>