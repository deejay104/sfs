<?
/*
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


class project_class extends objet_core
{
	protected $table="projects";
	protected $mod="projects";
	protected $rub="detail";

	protected $type=array(
		"name" => "varchar",
		"client" => "varchar",
		"country" => "varchar",
		"version" => "varchar",
		"business" => "varchar",
		"type" => "varchar",
		"budget" => "price",
		"status"=>"enum",
		"day"=>"enum"
	);
	protected $tabList=array(
		"status"=>array(
			"fr"=>array('1new'=>"Nouveau",'2inprg'=>'En cours','3close'=>'Terminé'),
			"en"=>array('1new'=>"New",'2inprg'=>'In progress','3close'=>'Closed'),
		),
		"day"=>array(
			"fr"=>array("1"=>"Lundi","2"=>"Mardi","3"=>"Mercredi","4"=>"Jeudi","5"=>"Vendredi","6"=>"Samedi","7"=>"Dimanche"),
			"en"=>array("1"=>"Monday","2"=>"Tuesday","3"=>"Wednesday","4"=>"Thursday","5"=>"Friday","6"=>"Saturday","7"=>"Sunday"),
		)
	);
	protected $droit=array(
		"day"=>"ModifDay"
	);
	protected $nomodif=array(
	);
	
	# Constructor
	function __construct($id=0,$sql)
	{
		global $gl_uid,$MyOpt,$tabLang;
		
		$this->data["name"]="";
		$this->data["client"]="";
		$this->data["country"]="";
		$this->data["version"]="";
		$this->data["business"]="";
		$this->data["type"]="";
		$this->data["actif"]="oui";
		$this->data["budget"]=0;
		$this->data["status"]="1new";
		$this->data["day"]=$MyOpt["InitialDay"];
		
		parent::__construct($id,$sql);		

		$this->usr_maj = new user_core($this->uid_maj,$sql,false,false);
	}

	function loadManager()
	{
		global $MyOpt;
		$sql=$this->sql;
		
		$query="SELECT * FROM ".$MyOpt["tbl"]."_projects_mgr AS mgr WHERE prjid='".$this->id."'";

		$this->manager=array();
		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{
			$sql->GetRow($i);
			$this->manager[$sql->data["uid"]]=$sql->data["id"];
		}
	}
	
	function affManager($typeaff,$type="line")
	{
		global $MyOpt;
		$sql=$this->sql;

		$ret="";
		if ($typeaff=="form")
		{
			$ret.="<div style='display:inline-block;'>";
			
			$query="SELECT * FROM ".$MyOpt["tbl"]."_projects_mgr AS mgr WHERE prjid='".$this->id."'";

			$tabMgr=array();
			$sql->Query($query);
			for($i=0; $i<$sql->rows; $i++)
			{
				$sql->GetRow($i);
				$tabMgr[$sql->data["uid"]]=$sql->data["id"];
			}

			foreach($tabMgr as $uid=>$id)
			{
				$usr=new user_core($uid,$this->sql);
				$ret.="<div id='aff_mgr_".$uid."' OnMouseOver='document.getElementById(\"mgr_del_".$uid."\").style.visibility=\"visible\";' OnMouseOut='document.getElementById(\"mgr_del_".$uid."\").style.visibility=\"hidden\";'>";
				$ret.=$usr->aff("fullname");
				$ret.="&nbsp;<a href=\"#\" OnClick=\"$(function() { $.ajax({url:'api.php?mod=projects&rub=updmgr&id=".$id."&fonc=delete'}); document.getElementById('aff_mgr_".$uid."').style.visibility='hidden'; document.getElementById('aff_mgr_".$uid."').style.height='0'; })\" class='imgDelete'><img  id='mgr_del_".$uid."' src='static/modules/projects/img/icn16_supprimer.png' style='visibility:hidden;'></a>";
				$ret.="</div>";
			}

			
			$ret.="<div id='mgr_0'></div>";
			$ret.="</div>";

			$ret.="<script>";
			$ret.="function AddManager(i) {";

			$ret.="var r=\"\";\n";
			$ret.="r=r+\"<select name='form_mgr[a\"+i+\"]' OnChange='AddManager(\"+(i+1)+\");'>\";\n";

			$lst=ListActiveUsers($sql,"prenom,nom");
			
			$ret.="r=r+\"<option value=''>Sélectionner</option>\";\n";
			foreach($lst as $i=>$tmpuid)
			{
				if (!isset($tabMgr[$tmpuid]))
				{
					$resusr=new user_core($tmpuid,$sql);
					$ret.="r=r+\"<option value='".$resusr->id."'>".$resusr->val("fullname")."</option>\";\n";
				}
			}
		
			// $query="SELECT id FROM ".$MyOpt["tbl"]."_ui ORDER BY description";
			// $sql->Query($query);
			// for($i=0; $i<$sql->rows; $i++)
			// {
				// $sql->GetRow($i);
				// if ( (GetDroit($sql->data["droit"])) && ((!isset($tabEcheance[$sql->data["id"]])) || ($tabEcheance[$sql->data["id"]]=="")) )
				// {
					// $ret.="r=r+\"<option value='".$sql->data["id"]."'>".$sql->data["description"]."</option>\";\n";
					// $n=$n+1;
				// }
			// }

			$ret.="r=r+\"</select>&nbsp;\";\n";

			$ret.="r=r+\"<div id='mgr_\"+(i+1)+\"'></div>\";\n";
			$ret.="var d=document.getElementById('mgr_'+i);\n";
			$ret.="d.innerHTML=r;\n";
				
			$ret.="}\n";
			
			$ret.="AddManager(0);\n";
			$ret.="</script>";
		}
		else
		{
			$query="SELECT * FROM ".$MyOpt["tbl"]."_projects_mgr AS mgr WHERE prjid='".$this->id."'";

			$tabMgr=array();
			$sql->Query($query);
			for($i=0; $i<$sql->rows; $i++)
			{
				$sql->GetRow($i);
				$tabMgr[$sql->data["uid"]]="ok";
			}

			
			$ret=($type=="line") ? "<span>" : "";
			$sep=($type=="line") ? "<br />" : ",";
			$s="";
			foreach($tabMgr as $uid=>$d)
			{
				$usr=new user_core($uid,$this->sql);
				$ret.=$s.$usr->aff("fullname");
				$s=$sep;
			}
			$ret.=($type=="line") ? "</span>" : "";
		}

		return $ret;
	}
	
	function SaveManager($form_mgr)
	{
		global $MyOpt,$gl_uid;
		$sql=$this->sql;
		if (count($form_mgr)>0)
		{
			foreach($form_mgr as $k=>$uid)
		  	{
				if ($uid>0)
				{
					$td=array();
					$td["uid"]=$uid;
					$td["prjid"]=$this->id;
					$td["uid_creat"]=$gl_uid;
					$td["dte_creat"]=now();
					$td["uid_maj"]=$gl_uid;
					$td["dte_maj"]=now();			
					$sql->Edit("_projects_mgr",$MyOpt["tbl"]."_projects_mgr",0,$td);
				}
		  	}
		}
	}

	function GetFollowup($init=0,$prev=true)
	{
		global $MyOpt;
		
		$sql=$this->sql;

		$ret=array();

		$t=7+date("N")-$this->data["day"];
		$tt=($t>=7) ? $t-7 : $t;
		$dte=time()-$tt*86400;
		$ret["week"]=date("Y-W",$dte);
		$ret["dte"]=date("d/m/Y",$dte);
		$ret["sprint"]=0;
		$ret["wave"]=0;
		$ret["locked"]="non";

		$ret["phases"]=array();
		$query = "SELECT * FROM ".$MyOpt["tbl"]."_testcase WHERE actif='oui' ORDER BY id";
		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$ret["phases"][$sql->data["id"]]["lid"]=0;
			$ret["phases"][$sql->data["id"]]["tests"]=$init;
			$ret["phases"][$sql->data["id"]]["name"]=$sql->data["name"];
		}

		$query = "SELECT * FROM ".$MyOpt["tbl"]."_followup WHERE project='".$this->id."' AND week='".date("Y-W",$dte-86400*7)."'";
		$sql->Query($query);

		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			if ($prev)
			{
				$ret["phases"][$sql->data["phase"]]["lid"]=0;
				$ret["phases"][$sql->data["phase"]]["tests"]=$sql->data["tests"];
			}
			if (($sql->data["sprint"]!="") && ($sql->data["sprint"]!=0))
			{
				$ret["sprint"]=$sql->data["sprint"];
			}
			if (($sql->data["wave"]!="") && ($sql->data["wave"]!=0))
			{
				$ret["wave"]=$sql->data["wave"];
			}
		}
	
		$query = "SELECT * FROM ".$MyOpt["tbl"]."_followup WHERE project='".$this->id."' AND week='".date("Y-W",$dte)."'";
		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$ret["phases"][$sql->data["phase"]]["lid"]=$sql->data["id"];
			$ret["phases"][$sql->data["phase"]]["tests"]=$sql->data["tests"];
			if (($sql->data["sprint"]!="") && ($sql->data["sprint"]!=0))
			{
				$ret["sprint"]=$sql->data["sprint"];
			}
			if (($sql->data["wave"]!="") && ($sql->data["wave"]!=0))
			{
				$ret["wave"]=$sql->data["wave"];
			}			
			if ($sql->data["locked"]=="oui")
			{
				$ret["locked"]="oui";
			}
		}
		
		return $ret;
	}

	function GetBacklog($init=0,$prev=true)
	{
		global $MyOpt;
		
		$sql=$this->sql;

		$ret=array();

		$ret["week"]=date("Y-W");
		$ret["dte"]=date("d/m/Y");
		$ret["sprint"]=0;
		$ret["wave"]=0;
		$ret["locked"]="non";

		$ret["phases"]=array();
		$query = "SELECT * FROM ".$MyOpt["tbl"]."_backlog WHERE actif='oui' ORDER BY id";
		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$ret["phases"][$sql->data["id"]]["lid"]=0;
			$ret["phases"][$sql->data["id"]]["tests"]=$init;
			$ret["phases"][$sql->data["id"]]["name"]=$sql->data["name"];
		}

		$query = "SELECT * FROM ".$MyOpt["tbl"]."_bl_followup WHERE project='".$this->id."' AND dte='".date("Y-m-d",time()-86400)."'";
		$sql->Query($query);

		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			if ($prev)
			{
				$ret["phases"][$sql->data["phase"]]["lid"]=0;
				$ret["phases"][$sql->data["phase"]]["tests"]=$sql->data["tests"];
			}
			if (($sql->data["sprint"]!="") && ($sql->data["sprint"]!=0))
			{
				$ret["sprint"]=$sql->data["sprint"];
			}
			if (($sql->data["wave"]!="") && ($sql->data["wave"]!=0))
			{
				$ret["wave"]=$sql->data["wave"];
			}
		}
	
		$query = "SELECT * FROM ".$MyOpt["tbl"]."_bl_followup WHERE project='".$this->id."' AND dte='".date("Y-m-d")."'";
		$sql->Query($query);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$ret["phases"][$sql->data["phase"]]["lid"]=$sql->data["id"];
			$ret["phases"][$sql->data["phase"]]["tests"]=$sql->data["tests"];
			if (($sql->data["sprint"]!="") && ($sql->data["sprint"]!=0))
			{
				$ret["sprint"]=$sql->data["sprint"];
			}
			if (($sql->data["wave"]!="") && ($sql->data["wave"]!=0))
			{
				$ret["wave"]=$sql->data["wave"];
			}			
			if ($sql->data["locked"]=="oui")
			{
				$ret["locked"]="oui";
			}
		}
		
		return $ret;
	}
	
	function Notify()
	{
		global $MyOpt;
		
		$sql=$this->sql;
		$this->loadManager();
		$this->emails="";
		$s="";
		$ret=true;
		foreach($this->manager as $uid=>$i)
		{
			$usr = new user_core($uid,$sql,false);
			if ($usr->data["mail"]!="")
			{
				$this->emails.=$s.$usr->data["mail"];
				$s=", ";
				$tabvar=array();
				$tabvar["name"]=$this->val("name");
				$tabvar["url"]=$MyOpt["host"]."/index.php?mod=projects&rub=myprojects";

				$ret.=SendMailFromFile("",$usr->data["mail"],"","",$tabvar,"followup");
			}
		}
		
		if ($MyOpt["sendmail"]=="off")
		{
			return false;
		}
		return $ret;
	}
	
	function IsMyProject()
	{
		global $gl_uid,$MyOpt;
		$sql=$this->sql;

		$q="SELECT id FROM ".$MyOpt["tbl"]."_projects_mgr WHERE prjid='".$this->id."' AND uid='".$gl_uid."'";
		$res=$sql->QueryRow($q);

		if ($res["id"]>0)
		{
			return true;
		}
		return false;
	}
}

function ListProjects($sql)
{
	global $MyOpt;
	return ListeObjets($sql,"projects",array("id"),array("actif"=>"oui"));
}

function ListMyProjects($sql)
{
	global $MyOpt,$gl_uid;
	// return ListeObjets($sql,"projects_mgr",array("prjid"),array("uid"=>$gl_uid));
	
	$q="SELECT prj.id AS id FROM ".$MyOpt["tbl"]."_projects AS prj LEFT JOIN ".$MyOpt["tbl"]."_projects_mgr AS mgr ON prj.id=mgr.prjid WHERE prj.actif='oui' AND mgr.uid='".$gl_uid."'";
	$sql->Query($q);

	$lst=array();
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$lst[$sql->data["id"]]=$sql->data;
	}
	return $lst;
}
?>