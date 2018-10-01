<?
	if (file_exists("config/config.inc.php"))
	  { require ("config/config.inc.php"); }
	if (file_exists("config/variables.inc.php"))
	  { require ("config/variables.inc.php"); }

	$corefolder="core";
	$appfolder="..";

	chdir($corefolder);
	require("api.php");
?>
