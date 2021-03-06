<?
	require_once ($appfolder."/class/project.inc.php");
	if (!GetDroit("AccesBacklog")) { FatalError("Acc�s non autoris� (AccesBacklog)"); }

// ---- V�rifie les variables
	$order=checkVar("order","varchar");
	$trie=checkVar("trie","varchar");
	
// ---- Charge le template
	$tmpl_x->assign("path_module",$module."/".$mod);
	$tmpl_x->assign("aff_mod",$mod);

// ---- Affiche le menu
	$aff_menu="";
	if (file_exists("modules/".$mod."/menu.inc.php"))
	{
		require("modules/".$mod."/menu.inc.php");
	}
	$tmpl_x->assign("aff_menu",$aff_menu);

	
// ---- Liste des projets
	$lst=ListMyProjects($sql);

	$tabTitre=array(
		"name" => array("aff"=>$tabLang["lang_name"],"width"=>200),
		"week" => array("aff"=>$tabLang["lang_week"],"width"=>100),
		"sprint" => array("aff"=>"Sprint","width"=>80),
		"wave" => array("aff"=>"Wave","width"=>80),
	);
	
	$tabPhases=array();
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_backlog WHERE actif='oui' ORDER BY id";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabPhases[$sql->data["id"]]=$sql->data["name"];
		$tabTitre["p".$sql->data["id"]]["aff"]=$sql->data["name"];
		$tabTitre["p".$sql->data["id"]]["width"]=80;
	}
	$tabTitre["action"]["aff"]="&nbsp;";
	$tabTitre["action"]["width"]=20;


	$tabValeur=array();
	foreach($lst as $id=>$d)
	{
		$prj = new project_class($id,$sql);		
		if ($prj->data["actif"]=="oui")
		{
			// $tabValeur[$i]=$prj->AffTableLine(array("name","client","country","version","business","budget"));
			$ret=$prj->GetBacklog("NA",false);
			$tabValeur[$id]["name"]["val"]=$prj->aff("name");
			$tabValeur[$id]["week"]["val"]=$ret["week"];
			$tabValeur[$id]["sprint"]["val"]=$ret["sprint"];
			$tabValeur[$id]["wave"]["val"]=$ret["wave"];

			foreach($tabPhases as $pid=>$pn)
			{
				$tabValeur[$id]["p".$pid]["val"]=$ret["phases"][$pid]["tests"];
			}
			$tabValeur[$id]["id"]["val"]=$id;
			$tabValeur[$id]["action"]["val"]=$id;
			if (($ret["locked"]!="oui") || (GetDroit("SYS")))
			{
				$tabValeur[$id]["action"]["aff"]="<div id='action_".$id."' style='display:none;'><a id='edit_".$id."' class='imgDelete' ><img src='".$module."/projects/img/icn16_editer.png'></a></div>";
			}
			else
			{
				$tabValeur[$id]["action"]["aff"]="&nbsp;";
			}
			$tmpl_x->assign("lst_id",$id);
			$tmpl_x->parse("corps.lst_edit");
		}
	}

	if ((!isset($order)) || ($order=="")) { $order="status"; }
	if ((!isset($trie)) || ($trie=="")) { $trie="d"; }

	$tmpl_x->assign("aff_tableau",AfficheTableau($tabValeur,$tabTitre,$order,$trie,"",0,"",0,"action"));

// ---- Affiche les backlogs

	$query = "SELECT * FROM ".$MyOpt["tbl"]."_backlog WHERE actif='oui' ORDER BY id";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);

		$tmpl_x->assign("form_pid", $sql->data["id"]);
		$tmpl_x->assign("form_name", $sql->data["name"]);
		$tmpl_x->parse("corps.lst_backlog");
		$tmpl_x->parse("corps.lst_form");
		$tmpl_x->parse("corps.lst_post");
	}

	
// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>