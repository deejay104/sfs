<?
/*
    MnMs Framework
    Copyright (C) 2018 Matthieu Isorez

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>

<?
	require_once ($appfolder."/class/project.inc.php");
	if (!GetDroit("AccesProjet")) { FatalError("Accès non autorisé (AccesProjet)"); }

// ---- Charge le template
	$tmpl_x = new XTemplate (MyRep("detail.htm"));
	$tmpl_x->assign("path_module",$module."/".$mod);
	$tmpl_x->assign("form_checktime",$_SESSION['checkpost']);

// ---- Initialise les variables
	$id=checkVar("id","numeric");
	$order=checkVar("order","varchar");
	$trie=checkVar("trie","varchar");
		
// ---- Enregistrer
	$msg_erreur="";
	$msg_confirmation="";
	if (($fonc=="Enregistrer") && (!isset($_SESSION['tab_checkpost'][$checktime])))
	{
		$prj=new project_class($id,$sql);
		if (count($form_data)>0)
		{
			foreach($form_data as $k=>$v)
		  	{
		  		$msg_erreur.=$prj->Valid($k,$v);
		  	}
		}
		if ($prj->Save()>0)
		{
			$msg_confirmation.="Vos données ont été enregistrées.<BR>";
		}
		$prj->SaveManager($form_mgr);
		if ($id==0)
		{
			$id=$prj->id;
		}

		$_SESSION['tab_checkpost'][$checktime]=$checktime;
	}
// ---- Supprimer
	if (($fonc=="supprimer") && ($id>0) && (GetDroit("SupprimeAmelioration")))
	{
		$prj=new project_class($id,$sql);
		$prj->Delete();
		$mod="projects";
		$affrub="index";
	}


// ---- Affiche le menu
	$aff_menu="";
	if (file_exists("modules/".$mod."/menu.inc.php"))
	{
		require("modules/".$mod."/menu.inc.php");
	}
	$tmpl_x->assign("aff_menu",$aff_menu);
	$tmpl_x->assign("form_id",$id);

	if (GetDroit("AccesProjets"))
	{
		$tmpl_x->parse("infos.liste");
	}
	if (GetDroit("CreeProjet"))
	{
		$tmpl_x->parse("infos.ajouter");
	}
	if (GetDroit("ModifProjet"))
	{
		$tmpl_x->parse("infos.modifier");
	}
	if (GetDroit("SupprimeProjet"))
	{
		$tmpl_x->parse("infos.supprimer");
	}

	
// ---- Modifie les infos
	if (($id==0) && (GetDroit("CreeProjet")))
	{
		$typeaff="form";
	}
	else if (($fonc=="modifier") && (GetDroit("ModifProjet")))
	{
		$typeaff="form";
	}
	else
	{
		$typeaff="html";
	}

// ---- Charge la fiche

	$prj = new project_class($id,$sql);

	$prj->Render("form",$typeaff);

	if ($typeaff=="form")
	{
		$tmpl_x->parse("corps.submit");
	}

	$tmpl_x->assign("form_manager",$prj->AffManager($typeaff));

// ---- Affiche l'historique
	$tabTitre=array(
		"week" => array("aff"=>"Semaine","width"=>100),
		"dte" => array("aff"=>"Date","width"=>100),
		"sprint" => array("aff"=>"Sprint","width"=>50),
		"wave" => array("aff"=>"Wave","width"=>50),
	);
	
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_testcase WHERE actif='oui' ORDER BY id";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabTitre["t".$sql->data["id"]]["aff"]=$sql->data["name"];
		$tabTitre["t".$sql->data["id"]]["width"]=150;
	}

	$tabValeur=array();
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_followup WHERE project='".$id."' ORDER BY week,phase,id";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabValeur[$sql->data["week"]]["week"]["val"]=$sql->data["week"];
		$tabValeur[$sql->data["week"]]["week"]["aff"]=$sql->data["week"];
		$tabValeur[$sql->data["week"]]["dte"]["val"]=strtotime($sql->data["dte"]);
		$tabValeur[$sql->data["week"]]["dte"]["aff"]=sql2date($sql->data["dte"]);
		$tabValeur[$sql->data["week"]]["t".$sql->data["phase"]]["val"]=0;
		$tabValeur[$sql->data["week"]]["t".$sql->data["phase"]]["aff"]=$sql->data["tests"];
		if (($sql->data["sprint"]!="") && ($sql->data["sprint"]!=0))
		{
			$tabValeur[$sql->data["week"]]["sprint"]["val"]=$sql->data["sprint"];
			$tabValeur[$sql->data["week"]]["sprint"]["aff"]=$sql->data["sprint"];
		}
		if (($sql->data["wave"]!="") && ($sql->data["wave"]!=0))
		{
			$tabValeur[$sql->data["week"]]["wave"]["val"]=$sql->data["wave"];
			$tabValeur[$sql->data["week"]]["wave"]["aff"]=$sql->data["wave"];
		}
	}

	if ((!isset($order)) || ($order=="")) { $order="week"; }
	if ((!isset($trie)) || ($trie=="")) { $trie="d"; }

	$tmpl_x->assign("aff_tableau",AfficheTableau($tabValeur,$tabTitre,$order,$trie,"&id=".$id));

// ---- Messages
	if ($msg_erreur!="")
	{
		affInformation($msg_erreur,"error");
	}		

	if ($msg_confirmation!="")
	{
		affInformation($msg_confirmation,"ok");
	}

	
// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>