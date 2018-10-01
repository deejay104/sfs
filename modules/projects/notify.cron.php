<?
// ---------------------------------------------------------------------------------------------
//   Batch de notification 
// ---------------------------------------------------------------------------------------------
?>
<?
	if ($gl_mode!="batch")
	  { FatalError("Acces refusé","Ne peut etre executé qu'en arriere plan"); }

  	require_once ($appfolder."/class/project.inc.php");

	myPrint("Notification échéance projet : ".implode(",",$tabTre));

// ---- Liste les comptes actifs
	// $lstusr=ListActiveUsers($sql,"std",array(),"non");
	// $gl_res="OK";

	// foreach($lstusr as $i=>$id)
	// {
		// $usr = new user_class($id,$sql,false,true);
		// $ret=true;
		// $solde=$usr->CalcSolde();
		// if (($solde<-$usr->data["decouvert"]) && ($usr->data["mail"]!="") && ($usr->data["virtuel"]=="non"))
		// {
			// myPrint($usr->fullname." - Solde: ".$solde);
			// $tabvar=array();
			// $tabvar["solde"]=AffMontant($solde);
			
			// SendMailFromFile($mailtre,$usr->data["mail"],$tabTre,"[".$MyOpt["site_title"]."] Compte à découvert",$tabvar,"decouvert");
		// }
		// if (!$ret)
		// {
			// $gl_res="ERREUR";
		// }
	// }

		
	$q="SELECT prj.id AS id FROM ".$MyOpt["tbl"]."_projects AS prj LEFT JOIN ".$MyOpt["tbl"]."_projects_mgr AS mgr ON prj.id=mgr.prjid WHERE prj.actif='oui'";
	$sql->Query($q);

	$lst=array();
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$lst[$sql->data["id"]]=$sql->data;
	}

	foreach($lst as $id=>$d)
	{
		$prj = new project_class($id,$sql);

		$ret=$prj->GetFollowup("NA",false);
		$ok="Enregistré";
		$mail="";
		foreach($tabPhases as $pid=>$pn)
		{			
			if ($ret["phases"][$pid]["tests"]=="NA")
			{
				$ok="A soumettre";
				$mail="ok";
			}
		}
		if ($ret["locked"]=="oui")
		{
			$ok="Soumis";
		}
		if ($ok=="Enregistré")
		{
			$mail="ok";
		}

		// myPrint($prj->data["id"]." ".$prj->data["name"]." - status:".$ok." mail:".$mail);
		if ($mail=="ok")
		{
			$ret=$prj->Notify();
			myPrint($prj->data["id"]." ".$prj->data["name"]." - status:".$ok." mail:".$prj->emails);
		}
	}
	
	
	myPrint($gl_res);
?>