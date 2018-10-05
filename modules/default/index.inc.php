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
	
// ---- Charge le template
	$tmpl_x = LoadTemplate("index");
	$tmpl_x->assign("path_module",$module."/".$mod);
	$tmpl_x->assign("corefolder",$corefolder);
	$tmpl_x->assign("aff_mod",$mod);
	$tmpl_x->assign("TitleBackgroundHover",$MyOpt["styleColor"]["TitleBackgroundHover"]);
	$tmpl_x->assign("msgboxBackgroundOk",$MyOpt["styleColor"]["msgboxBackgroundOk"]);

// ---- Affiche le menu
	$aff_menu="";
	$tmpl_x->assign("aff_menu",$MyOpt["site_title"]);

	if (GetDroit("AccesExport"))
	{
		$tmpl_x->parse("infos.export");
		$tmpl_x->parse("corps.export");
	}

// ---- Liste des projets
	if (GetDroit("AccesProjets"))
	{
		$lst=ListProjects($sql);
	}
	else
	{
		$lst=ListMyProjects($sql);
	}

// ---- Liste des testcases
	$tabPhases=array();
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_testcase WHERE actif='oui' ORDER BY id";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabPhases[$sql->data["id"]]=$sql->data["name"];
		$tmpl_x->assign("form_pid", $sql->data["id"]);
		$tmpl_x->assign("form_name", $sql->data["name"]);
		$tmpl_x->parse("corps.lst_phases");
		$tmpl_x->parse("corps.lst_form");
		$tmpl_x->parse("corps.lst_post");
	}
	
// ---- Liste des backlog
	$tabBacklog=array();
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_backlog WHERE actif='oui' ORDER BY id";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabBacklog[$sql->data["id"]]=$sql->data["name"];
		$tmpl_x->assign("form_bl_pid", $sql->data["id"]);
		$tmpl_x->assign("form_bl_name", $sql->data["name"]);
		$tmpl_x->parse("corps.lst_bl_phases");
		$tmpl_x->parse("corps.lst_bl_form");
		$tmpl_x->parse("corps.lst_bl_post");
	}

// ---- Calcul les jours pour le graphique
	$tabGraph=array();
	$t=time()-86400*15;
	$ii=0;
	$stime=date("Y-m-d",$t);
	for($i=$t; $i<=time(); $i=$i+86400)
	{
		$tabGraph[date("Y-m-d",$i)]=$ii;
		$ii=$ii+1;
	}

// ---- Affiche la liste des projets
	foreach($lst as $id=>$d)
	{
		$prj = new project_class($id,$sql);
		if ($prj->data["actif"]=="oui")
		{
			$prj->Render("form","html");
			$tmpl_x->assign("form_id",$id);
			$tmpl_x->assign("form_manager",$prj->AffManager("html",", "));
			
			$ret=$prj->GetFollowup("NA",false);
			$tmpl_x->assign("form_sprint", $ret["sprint"]);
			$tmpl_x->assign("form_wave", $ret["wave"]);
			$tmpl_x->assign("form_week", $ret["week"]);
			$tmpl_x->assign("aff_edit", "none");
			$tmpl_x->assign("aff_send", "none");
			$tmpl_x->assign("aff_mail", "none");

			$ok="Enregistré";
			$mail="";

			// Weekly Report
			foreach($tabPhases as $pid=>$pn)
			{
				$tmpl_x->assign("form_phases_pid", $pid);
				$tmpl_x->assign("form_phases_title", $pn);
				$tmpl_x->assign("form_phases_data", $ret["phases"][$pid]["tests"]);
				$tmpl_x->parse("corps.lst_projects.lst_phases_title");
				$tmpl_x->parse("corps.lst_projects.lst_phases_data");
				
				if ($ret["phases"][$pid]["tests"]=="NA")
				{
					$ok="A soumettre";
					$mail="ok";
					$tmpl_x->assign("form_color", $MyOpt["styleColor"]["msgboxBackgroundError"]);
				}
			}
			if ($ret["locked"]=="oui")
			{
				$ok="Soumis";
				$tmpl_x->assign("form_color", $MyOpt["styleColor"]["msgboxBackgroundOk"]);
			}
			else if ((!GetDroit("AccesProjets")) || ($prj->IsMyProject()))
			{
				$tmpl_x->assign("aff_edit", "inline-block");
				$tmpl_x->parse("corps.lst_edit");
			}
			if ($ok=="Enregistré")
			{
				$mail="ok";
				$tmpl_x->assign("form_color", $MyOpt["styleColor"]["msgboxBackgroundWarning"]);
				if ((!GetDroit("AccesProjets")) || ($prj->IsMyProject()))
				{
					$tmpl_x->assign("aff_send", "inline-block");
					$tmpl_x->parse("corps.lst_send");
				}
			}
			if ((GetDroit("AccesProjets")) && ($mail=="ok"))
			{
				$tmpl_x->assign("aff_mail", "inline-block");
				$tmpl_x->parse("corps.lst_mail");
			}
			$tmpl_x->assign("form_phases_status", $ok);
			
			// Daily Backlog
			$ret=$prj->GetBacklog("NA",false);
			$tmpl_x->assign("form_bl_sprint", $ret["sprint"]);
			$tmpl_x->assign("form_bl_wave", $ret["wave"]);
			$tmpl_x->assign("form_bl_week", $ret["week"]);
			$tmpl_x->assign("aff_bl_edit", "none");
			$tmpl_x->assign("aff_bl_send", "none");
			$tmpl_x->assign("aff_bl_mail", "none");

			$ok="Enregistré";
			$mail="";
			foreach($tabBacklog as $pid=>$pn)
			{
				$tmpl_x->assign("form_backlog_pid", $pid);
				$tmpl_x->assign("form_backlog_title", $pn);
				$tmpl_x->assign("form_backlog_data", $ret["phases"][$pid]["tests"]);
				$tmpl_x->parse("corps.lst_projects.lst_backlog_name");

				
				if ($ret["phases"][$pid]["tests"]=="NA")
				{
					$ok="A soumettre";
					$mail="ok";
					$tmpl_x->assign("form_bl_color", $MyOpt["styleColor"]["msgboxBackgroundError"]);
				}
			}
			if ($ret["locked"]=="oui")
			{
				$ok="Soumis";
				$tmpl_x->assign("form_bl_color", $MyOpt["styleColor"]["msgboxBackgroundOk"]);
			}
			else if ((!GetDroit("AccesProjets")) || ($prj->IsMyProject()))
			{
				$tmpl_x->assign("aff_bl_edit", "inline-block");
				$tmpl_x->parse("corps.lst_bl_edit");
			}
			if ($ok=="Enregistré")
			{
				$mail="ok";
				$tmpl_x->assign("form_bl_color", $MyOpt["styleColor"]["msgboxBackgroundWarning"]);
				if ((!GetDroit("AccesProjets")) || ($prj->IsMyProject()))
				{
					// $tmpl_x->parse("corps.lst_projects.aff_send");
					$tmpl_x->assign("aff_bl_send", "inline-block");
					$tmpl_x->parse("corps.lst_bl_send");
				}
			}
			
			$tmpl_x->assign("form_backlog_status", $ok);

			// Graph des backlog
			foreach($tabBacklog as $i=>$n)
			{
				$tmpl_x->assign("graph_bl_name", $n);

				foreach($tabGraph as $d=>$ii)
				{
					$tmpl_x->assign("aff_bl_day", date("d.m",strtotime($d)));
					$tmpl_x->parse("corps.lst_projects.lst_bl_day");
				}


				$s="";
				$query = "SELECT * FROM ".$MyOpt["tbl"]."_bl_followup WHERE project='".$id."' AND phase='".$i."' AND dte>='".$stime."' ORDER BY dte";
				$sql->Query($query);
				for($i=0; $i<$sql->rows; $i++)
				{ 
					$sql->GetRow($i);
					$s.="[".$tabGraph[$sql->data["dte"]].",".$sql->data["tests"]."],";
				}
				$tmpl_x->assign("graph_bl_series", $s);
				$tmpl_x->parse("corps.lst_projects.lst_graph_backlog");
			}

			// Affiche le bloc projet
			$tmpl_x->parse("corps.lst_projects");
		}
	}


// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>