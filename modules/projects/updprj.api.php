<?

// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }


  	require_once ($appfolder."/class/project.inc.php");

	require_once($appfolder.'/custom/portphp/vendor/autoload.php');
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
		$res=$prj->GetFollowup(0,true);
		$ret["week"]=$res["week"]." (".$res["dte"].")";
		$ret["sprint"]=$res["sprint"];
		$ret["wave"]=$res["wave"];
		$ret["phases"]=$res["phases"];
	}
	else if (($fonc=="send") && ($id>0))
	{
		$query = "UPDATE ".$MyOpt["tbl"]."_followup SET locked='oui' WHERE project='".$id."'";
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

		if ((isset($_FILES)) && (count($_FILES)>0))
		{
			// error_log(print_r($_FILES,true));
			$prj = new project_class($id,$sql);		
			$res=$prj->GetFollowup(0,true);
			
			$tabTest=array();
			$q="SELECT * FROM ".$MyOpt["tbl"]."_testcase WHERE actif='oui'";
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

				foreach($reader->worksheet[0] as $i=>$wc)
				{
					error_log(strtolower($wc)." ".$tabTest[strtolower($wc)]);
					if (strtolower($wc)=="sprint")
					{
						$sprint=$reader->worksheet[1][$i];
					}
					else if (strtolower($wc)=="wave")
					{
						$wave=$reader->worksheet[1][$i];
					}
					else if (isset($tabTest[strtolower($wc)]))
					{
						$res["phases"][$tabTest[strtolower($wc)]]["tests"]=$reader->worksheet[1][$i];
					}
				}
			}
			// error_log(print_r($res["phases"],true));
			$tabPhases=$res["phases"];
		}
		
		if ((is_array($tabPhases)) && (count($tabPhases)>0))
		{
			$query = "SELECT * FROM ".$MyOpt["tbl"]."_projects WHERE id='".$id."'";
			$res=$sql->QueryRow($query);

			$t=7+date("N")-$res["day"];
			$tt=($t>7) ? $t-7 : $t;
			$dte=time()-$tt*86400;
			$week=date("Y-m-d",$dte);
			
			foreach($tabPhases as $i=>$d)
			{
				if (is_array($d))
				{
					$td=array();
					$td["project"]=$id;
					$td["week"]=date("Y-W",strtotime($week));
					$td["dte"]=$week;
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
					$sql->Edit("followup",$MyOpt["tbl"]."_followup",$d["lid"],$td);
				}
			}
		}

		$ret["result"]="OK";
	}
	
// ---- Renvoie le résultat
	echo json_encode($ret);
?>