<table width="100%" border="0" cellspacing="0" cellpadding="2">
<?php if (IsLoggedIn()) { ?>
	<tr><td><span class="phpmaker"><a href="keywordslist.php?cmd=resetall">KeyWords</a></span></td></tr>
<?php } ?>
<?php if (IsLoggedIn()) { ?>
	<tr><td><span class="phpmaker"><a href="webpageslist.php?cmd=resetall">Web Links</a></span></td></tr>
<?php } ?>
<?php if (IsLoggedIn() && !IsSysAdmin()) { ?>
	<tr><td><span class="phpmaker"><a href="changepwd.php">Change Password</a></span></td></tr>
<?php } ?>
<?php if (IsLoggedIn()) { ?>
	<tr><td><span class="phpmaker"><a href="logout.php">Logout</a></span></td></tr>
<?php } elseif (substr(ew_ScriptName(), -1*strlen("login.php")) <> "login.php") { ?>
	<tr><td><span class="phpmaker"><a href="login.php">Login</a></span></td></tr>
<?php } ?>
	<tr><td><span class="phpmaker"><a href="indexSearchPortal.php">Search Portal</a></span></td></tr>
</table>
