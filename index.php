<?php

	if (file_exists("config/config.inc.php"))
	{
		require ("config/config.inc.php");
	}
	if (file_exists("config/variables.inc.php"))
	{
		require ("config/variables.inc.php");
	}

	require("version.php");

	$appfolder="..";
	$corefolder="core";

	chdir($corefolder);
	require("index.php");
?>
