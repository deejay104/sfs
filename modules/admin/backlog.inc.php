<?
	if (!GetDroit("AccesConfigBacklog")) { FatalError("Accès non autorisé (AccesConfigBacklog)"); }

// ---- Charge le template
	$tmpl_x->assign("path_module","$module/$mod");
	$tmpl_x->assign("form_checktime",$_SESSION['checkpost']);

// ---- Vérifie les variables
	$form_name=checkVar("form_name","array");
	$id=checkVar("id","numeric");

// ---- Affiche le menu
	$aff_menu="";
	require_once("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);

// ---- Enregistre les modifications
	if (($fonc==$tabLang["lang_save"]) && (is_array($form_name)) && (!isset($_SESSION['tab_checkpost'][$checktime])))
	{
	  	foreach($form_name as $id=>$name)
	  	{
			if ($name!="")
			{
				$sql->Edit("backlog",$MyOpt["tbl"]."_backlog",$id,array("name"=>$name));
			}
		}
		$_SESSION['tab_checkpost'][$checktime]=$checktime;
	}

// ---- Supprime un poste
	if (($fonc=="delete") && ($id>0))
	{
		$sql->Edit("backlog",$MyOpt["tbl"]."_backlog",$id,array("actif"=>"non"));		
	}

// ---- Affiche la page demandée

	// Liste des mouvements
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_backlog WHERE actif='oui' ORDER BY id";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	  { 
		$sql->GetRow($i);
	
		$tmpl_x->assign("form_id", $sql->data["id"]);
		$tmpl_x->assign("form_name", $sql->data["name"]);
		$tmpl_x->parse("corps.lst_mouvement");
	  }

	// Ligne vide

	$tmpl_x->assign("form_id", "0");
	$tmpl_x->assign("form_name", "");
	$tmpl_x->parse("corps.lst_mouvement");

	$tmpl_x->parse("corps.aff_mouvement");


// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>
