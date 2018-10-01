<?
	$q=array();
	
	$query="SELECT id FROM ".$MyOpt["tbl"]."_cron WHERE module='projects', script='notify'";
	$res=$sql->QueryRow($query);
	if ($res["id"]==0)
	{		
		$q[]="INSERT INTO `".$MyOpt["tbl"]."_cron` SET description='Notification avancement de projet', module='projects', script='notify', schedule='1440', actif='non'";
	}

	// Apply mysql patches
  	foreach($q as $i=>$query)
	{
		$sql->Update($query);
	}

?>