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

// ---- Variables
	$id=checkVar("id","numeric");
	
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
	$tabGraphWeek=array();
	
	$t=time()-86400*$MyOpt["MaxWeekly"];
	$wtime=date("Y-m-d",$t);
	$ii=0;
	for($i=$t; $i<=time()+86400*6; $i=$i+86400*7)
	{
		$tabGraphWeek[date("Y-W",$i)]=$ii;
		$ii=$ii+1;
	}

	$tabGraph=array();
	$t=time()-86400*$MyOpt["MaxDaily"];
	$ii=0;
	$stime=date("Y-m-d",$t);
	for($i=$t; $i<=time(); $i=$i+86400)
	{
		$tabGraph[date("Y-m-d",$i)]=$ii;
		$ii=$ii+1;
	}

// ---- Affiche la liste des projets
	foreach($lst as $pid=>$d)
	{
		$prj = new project_class($pid,$sql);
		if ($prj->data["actif"]=="oui")
		{
			$tmpl_x->assign("form_prj_id",$pid);
			$tmpl_x->assign("form_prj_name",$prj->val("name"));
			$tmpl_x->assign("form_prj_selected",($pid==$id) ? "selected" : "");
			$tmpl_x->parse("corps.lst_projects");

			if ($id==0)
			{
				$id=$pid;
			}
		}
	}
			
	$prj = new project_class($id,$sql);
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
	$tmpl_x->assign("form_week_data", $ret["week"]);

	$ok="Enregistré";
	$mail="";

	// Weekly Report
	foreach($tabPhases as $tid=>$pn)
	{
		$tmpl_x->assign("form_phases_pid", $pid);
		$tmpl_x->assign("form_phases_title", $pn);
		// $tmpl_x->assign("form_phases_data", $ret["phases"][$tid]["tests"]);
		$tmpl_x->parse("corps.lst_phases_title");
		// $tmpl_x->parse("corps.lst_weekly.lst_phases_data");
		
		if ($ret["phases"][$tid]["tests"]=="NA")
		{
			$ok="A soumettre";
			$mail="ok";
			$tmpl_x->assign("form_color", $MyOpt["styleColor"]["msgboxBackgroundError"]);
		}
	}
	// $tmpl_x->parse("corps.lst_weekly");

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

	// Affiche l'historique
	$tabValeur=array();
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_followup WHERE project='".$id."' AND dte>='".$wtime."' ORDER BY week,phase,id";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabValeur[$sql->data["week"]]["week"]=$sql->data["week"];
		$tabValeur[$sql->data["week"]]["dte"]=strtotime($sql->data["dte"]);
		$tabValeur[$sql->data["week"]][$sql->data["phase"]]=$sql->data["tests"];
		if (($sql->data["sprint"]!="") && ($sql->data["sprint"]!=0))
		{
			$tabValeur[$sql->data["week"]]["sprint"]=$sql->data["sprint"];
		}
		if (($sql->data["wave"]!="") && ($sql->data["wave"]!=0))
		{
			$tabValeur[$sql->data["week"]]["wave"]=$sql->data["wave"];
		}
	}
	foreach($tabGraphWeek as $w=>$d)
	{
		$tmpl_x->assign("aff_tst_day", $w);
		$tmpl_x->parse("corps.lst_tst_day");

		$tmpl_x->assign("form_week_data",$w);
	
		foreach($tabPhases as $tid=>$pn)
		{
			$tmpl_x->assign("form_phases_data", (isset($tabValeur[$w][$tid])) ? $tabValeur[$w][$tid] : "NA");
			$tmpl_x->parse("corps.lst_weekly.lst_phases_data");
		}
		$tmpl_x->parse("corps.lst_weekly");
	}

	foreach($tabPhases as $i=>$n)
	{
		$tmpl_x->assign("graph_tst_name", $n);
		
		$s="";
		foreach($tabValeur as $w=>$d)
		{
			if ((isset($tabGraphWeek[$d["week"]])) && (isset($d[$i])))
			{
				$s.="[".$tabGraphWeek[$d["week"]].",".$d[$i]."],";
				$tmpl_x->assign("graph_tst_series", $s);
			}
		}
		$tmpl_x->parse("corps.lst_graph_tst");
	}
	
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
		// $tmpl_x->assign("form_backlog_data", $ret["phases"][$pid]["tests"]);
		$tmpl_x->parse("corps.lst_backlog_name");
		$tmpl_x->parse("corps.lst_bl_title");
		
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
			// $tmpl_x->parse("corps.aff_send");
			$tmpl_x->assign("aff_bl_send", "inline-block");
			$tmpl_x->parse("corps.lst_bl_send");
		}
	}
	
	$tmpl_x->assign("form_backlog_status", $ok);

	// Graph des backlog
	// foreach($tabBacklog as $i=>$n)
	// {
		// $tmpl_x->assign("graph_bl_name", $n);

		// foreach($tabGraph as $d=>$ii)
		// {
			// $tmpl_x->assign("aff_bl_day", date("d.m",strtotime($d)));
			// $tmpl_x->parse("corps.lst_bl_day");
		// }


		// $s="";
		// $query = "SELECT * FROM ".$MyOpt["tbl"]."_bl_followup WHERE project='".$id."' AND phase='".$i."' AND dte>='".$stime."' AND dte<=NOW() ORDER BY dte";
		// $sql->Query($query);
		// for($i=0; $i<$sql->rows; $i++)
		// { 
			// $sql->GetRow($i);
			// $s.="[".$tabGraph[$sql->data["dte"]].",".$sql->data["tests"]."],";
		// }
		// $tmpl_x->assign("graph_bl_series", $s);
		// $tmpl_x->parse("corps.lst_graph_backlog");
	// }
	$tabValeur=array();
	$query = "SELECT * FROM ".$MyOpt["tbl"]."_bl_followup WHERE project='".$id."' AND dte>='".$stime."' AND dte<=NOW()";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabValeur[$sql->data["dte"]]["week"]=$sql->data["week"];
		$tabValeur[$sql->data["dte"]]["dte"]=strtotime($sql->data["dte"]);
		$tabValeur[$sql->data["dte"]][$sql->data["phase"]]=$sql->data["tests"];
		if (($sql->data["sprint"]!="") && ($sql->data["sprint"]!=0))
		{
			$tabValeur[$sql->data["dte"]]["sprint"]=$sql->data["sprint"];
		}
		if (($sql->data["wave"]!="") && ($sql->data["wave"]!=0))
		{
			$tabValeur[$sql->data["dte"]]["wave"]=$sql->data["wave"];
		}
	}

	foreach($tabGraph as $d=>$ii)
	{
		$tmpl_x->assign("aff_bl_day", date("d.m",strtotime($d)));
		$tmpl_x->parse("corps.lst_bl_day");
		
		$tmpl_x->assign("form_daily_data",$d);
	
		foreach($tabBacklog as $tid=>$pn)
		{
			$tmpl_x->assign("form_backlog_data", (isset($tabValeur[$d][$tid])) ? $tabValeur[$d][$tid] : "NA");
			$tmpl_x->parse("corps.lst_bl_daily.lst_bl_data");
		}
		$tmpl_x->parse("corps.lst_bl_daily");
	}
	foreach($tabBacklog as $i=>$n)
	{
		$tmpl_x->assign("graph_bl_name", $n);
		
		$s="";
		foreach($tabValeur as $w=>$d)
		{
			$s.="[".$tabGraph[$w].",".$d[$i]."],";
			$tmpl_x->assign("graph_bl_series", $s);
		}
		$tmpl_x->parse("corps.lst_graph_backlog");
	}

	
	
// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>