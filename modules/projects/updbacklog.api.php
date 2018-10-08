<?

// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }


  	require_once ($appfolder."/class/project.inc.php");

	require_once($appfolder.'/external/portphp/vendor/autoload.php');
	use Port\Excel\ExcelReader;

// ---- Vérifie les paramètres
	$id=checkVar("id","numeric");
	$fonc=checkVar("fonc","varchar");
	$submit=checkVar("submit","varchar");

// ---- Récupère les infos
	$ret=array();
	$ret["type"]=$fonc;

	if (($fonc=="get") && ($id>0))
	{
		$ret["id"]=$id;
		$prj = new project_class($id,$sql);		
		$res=$prj->GetBacklog(0,true);
		$ret["day"]=$res["dte"];
		$ret["sprint"]=$res["sprint"];
		$ret["wave"]=$res["wave"];
		$ret["phases"]=$res["phases"];
	}
	else if (($fonc=="send") && ($id>0))
	{
		$query = "UPDATE ".$MyOpt["tbl"]."_bl_followup SET locked='oui' WHERE project='".$id."'";
		$sql->Update($query);
		$ret["id"]=$id;
		$ret["result"]="OK";
	}
	else if (($fonc=="mail") && ($id>0))
	{
		$prj = new project_class($id,$sql);		
		$prj->Notify();
		$ret["id"]=$id;
		$ret["result"]="OK";
		$ret["email"]=$prj->emails;
	}
	else if (($fonc=="post") && ($id>0))
	{
		$sprint=addslashes(utf8_decode(checkVar("sprint","numeric")));
		$wave=addslashes(utf8_decode(checkVar("wave","numeric")));

		$tabPhases=array();
		if (isset($_POST["phases"]))
		{
			$tabPhases=$_POST["phases"];
		}
	
		if ((is_array($tabPhases)) && (count($tabPhases)>0))
		{
			$query = "SELECT * FROM ".$MyOpt["tbl"]."_projects WHERE id='".$id."'";
			$res=$sql->QueryRow($query);

			$dte=date("Y-m-d");
			$week=date("Y-W");
			
			foreach($tabPhases as $i=>$d)
			{
				if (is_array($d))
				{
					$td=array();
					$td["project"]=$id;
					$td["week"]=$week;
					$td["dte"]=$dte;
					$td["phase"]=$i;
					$td["sprint"]=$sprint;
					$td["wave"]=$wave;
					$td["tests"]=addslashes(utf8_decode($d["tests"]));
					
					if ($submit=="yes")
					{
						$td["locked"]='oui';
					}
					
					$td["uid_maj"]=$gl_uid;
					$td["dte_maj"]=now();
					$sql->Edit("bl_followup",$MyOpt["tbl"]."_bl_followup",$d["lid"],$td);
				}
			}
		}

		$ret["result"]="OK";
	}
	else if (($fonc=="import") && ($id>0))
	{
		$sprint=addslashes(utf8_decode(checkVar("sprint","numeric")));
		$wave=addslashes(utf8_decode(checkVar("wave","numeric")));

		$tabPhases=array();

		if ((isset($_FILES)) && (count($_FILES)>0))
		{
			// error_log(print_r($_FILES,true));
			// $prj = new project_class($id,$sql);		
			// $res=$prj->GetBacklog(0,true);
			
			$tabTest=array();
			$tabMenu=array();
			$q="SELECT * FROM ".$MyOpt["tbl"]."_backlog WHERE actif='oui'";
			$sql->Query($q);
			for($i=0; $i<$sql->rows; $i++)
			{
				$sql->GetRow($i);
				$tabTest[strtolower($sql->data["name"])]=$sql->data["id"];
			}
			// error_log(print_r($tabTest,true));

			foreach($_FILES as $t=>$d)
			{
				// error_log("Import: ".$d["name"]);
				$file = new \SplFileObject($d["tmp_name"]);
				$reader = new ExcelReader($file);
				$reader->setHeaderRowNumber(0);

				// error_log(print_r($reader,true));
				$cl_day=0;
				$i=0;
				while((isset($reader->worksheet[$i][0])) && ($i<25) )
				{
					// error_log(strtolower($wc)." ".$tabTest[strtolower($wc)]);
					$wc=$reader->worksheet[$i][0];
					if (strtolower($wc)=="day")
					{
						$cl_day=$i;
					}
					else if (strtolower($wc)=="sprint")
					{
						$cl_sprint=$i;
					}
					else if (strtolower($wc)=="wave")
					{
						$cl_wave=$i;
					}
					else if (isset($tabTest[strtolower($wc)]))
					{
						$tabMenu[$tabTest[strtolower($wc)]]=$i;
					}
					$i=$i+1;
				}

				foreach($reader->worksheet[0] as $i=>$wc)
				{
					if ($wc=="day")
					{
						continue;
					}
					
					$dte=$reader->worksheet[$cl_day][$i];
					$dte=date("Y-m-d",($dte-25569)*3600*24);
					$week=date("Y-W",strtotime($dte));
					$sprint=$reader->worksheet[$cl_sprint][$i];
					$wave=$reader->worksheet[$cl_wave][$i];

					// Si dte dans le futur on passe
					if (strtotime($dte)>time())
					{
						continue;
					}
					
					foreach($tabMenu as $pid=>$ii)
					{
						$q="SELECT id FROM ".$MyOpt["tbl"]."_bl_followup WHERE dte='".$dte."' AND phase='".$pid."'";
						$res=$sql->QueryRow($q);
						
						$td=array();
						$td["project"]=$id;
						$td["week"]=$week;
						$td["dte"]=$dte;
						$td["phase"]=$pid;
						$td["sprint"]=$sprint;
						$td["wave"]=$wave;
						$td["tests"]=addslashes(utf8_decode($reader->worksheet[$ii][$i]));
						
						if ($submit=="yes")
						{
							$td["locked"]='oui';
						}
						
						$td["uid_maj"]=$gl_uid;
						$td["dte_maj"]=now();
						$sql->Edit("bl_followup",$MyOpt["tbl"]."_bl_followup",$res["id"],$td);
// error_log(print_r($td,true));
						error_log($i." ".$id.": ".$dte." ".$pid."=".$td["tests"]." -> ".$res["id"]);
					}
					$i=$i+1;
				}
			}
		}
		
		if ((is_array($tabPhases)) && (count($tabPhases)>0))
		{
			$query = "SELECT * FROM ".$MyOpt["tbl"]."_projects WHERE id='".$id."'";
			$res=$sql->QueryRow($query);

			$dte=date("Y-m-d");
			$week=date("Y-W");
			
			foreach($tabPhases as $i=>$d)
			{
				if (is_array($d))
				{
					$td=array();
					$td["project"]=$id;
					$td["week"]=$week;
					$td["dte"]=$dte;
					$td["phase"]=$i;
					$td["sprint"]=$sprint;
					$td["wave"]=$wave;
					$td["tests"]=addslashes(utf8_decode($d["tests"]));
					
					if ($submit=="yes")
					{
						$td["locked"]='oui';
					}
					
					$td["uid_maj"]=$gl_uid;
					$td["dte_maj"]=now();
					$sql->Edit("bl_followup",$MyOpt["tbl"]."_bl_followup",$d["lid"],$td);
				}
			}
		}

		$ret["result"]="OK";
	}

	
// ---- Renvoie le résultat
	echo json_encode($ret);
?>