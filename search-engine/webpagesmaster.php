<p><span class="phpmaker">Master Record: Web Links
<br><a href="<?php echo $sMasterReturnUrl ?>">Back to Master Page</a></span>
</p>
<table class="ewTable">
	<tr class="ewTableHeader">
		<td valign="top">Web URL</td>
		<td valign="top">Web Title</td>
		<td valign="top">URL Frequency</td>
		<td valign="top">Date Of Crawling</td>
		<td valign="top">Meta Content</td>
	</tr>
	<tr class="ewTableSelectRow">
		<td>
<?php if ($webpages->webURL->HrefValue <> "") { ?>
<a href="<?php echo $webpages->webURL->HrefValue ?>" target="_blank"><div<?php echo $webpages->webURL->ViewAttributes() ?>><?php echo $webpages->webURL->ViewValue ?></div></a>
<?php } else { ?>
<div<?php echo $webpages->webURL->ViewAttributes() ?>><?php echo $webpages->webURL->ViewValue ?></div>
<?php } ?>
</td>
		<td>
<?php if ($webpages->webTitle->HrefValue <> "") { ?>
<a href="<?php echo $webpages->webTitle->HrefValue ?>" target="_blank"><div<?php echo $webpages->webTitle->ViewAttributes() ?>><?php echo $webpages->webTitle->ViewValue ?></div></a>
<?php } else { ?>
<div<?php echo $webpages->webTitle->ViewAttributes() ?>><?php echo $webpages->webTitle->ViewValue ?></div>
<?php } ?>
</td>
		<td>
<div<?php echo $webpages->webURLFreq->ViewAttributes() ?>><?php echo $webpages->webURLFreq->ViewValue ?></div>
</td>
		<td>
<div<?php echo $webpages->Date_Of_Crawling->ViewAttributes() ?>><?php echo $webpages->Date_Of_Crawling->ViewValue ?></div>
</td>
		<td>
<div<?php echo $webpages->meta_content->ViewAttributes() ?>><?php echo $webpages->meta_content->ViewValue ?></div>
</td>
	</tr>
</table>
<br>
