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
	if (!GetDroit("AccesProjets")) { FatalError("Accès non autorisé (AccesProjets)"); }

// ---- Vérifie les variables
	$order=checkVar("order","varchar");
	$trie=checkVar("trie","varchar");
	
// ---- Charge le template
	$tmpl_x = LoadTemplate("index","");
	$tmpl_x->assign("path_module",$module."/".$mod);
	$tmpl_x->assign("aff_mod",$mod);

// ---- Affiche le menu
	$aff_menu="";
	if (file_exists("modules/".$mod."/menu.inc.php"))
	{
		require("modules/".$mod."/menu.inc.php");
	}
	$tmpl_x->assign("aff_menu",$aff_menu);

	if (GetDroit("CreeProjet"))
	{
		$tmpl_x->parse("infos.ajouter");
	}
	if (GetDroit("AccesExport"))
	{
		$tmpl_x->parse("infos.export");
		$tmpl_x->parse("corps.export");
	}
	
// ---- Liste des projets
	$lst=ListProjects($sql);

	$tabTitre=array(
		"name" => array("aff"=>$tabLang["lang_name"],"width"=>200),
		"client" => array("aff"=>$tabLang["lang_customer"],"width"=>100),
		"country" => array("aff"=>$tabLang["lang_country"],"width"=>100),
		"version" => array("aff"=>$tabLang["lang_version"],"width"=>100),
		"business" => array("aff"=>$tabLang["lang_business"],"width"=>100),
		"budget" => array("aff"=>$tabLang["lang_budget"],"width"=>100),
	);
	
	$tabValeur=array();
	foreach($lst as $i=>$d)
	{
		$prj = new project_class($i,$sql);		
		$tabValeur[$i]=$prj->AffTableLine(array("name","client","country","version","business","budget"));
	}

	if ((!isset($order)) || ($order=="")) { $order="status"; }
	if ((!isset($trie)) || ($trie=="")) { $trie="d"; }

	$tmpl_x->assign("aff_tableau",AfficheTableau($tabValeur,$tabTitre,$order,$trie));

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>