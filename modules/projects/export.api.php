<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

  	require_once ($appfolder."/class/project.inc.php");
  	require_once ("class/excel.inc.php");

	require_once($appfolder.'/external/portphp/vendor/autoload.php');
	use Port\Excel\ExcelWriter;

// ---- Vérifie les paramètres
	$type=checkVar("type","varchar");
	$limit=checkVar("limit","varchar");


// ---- Export projects
	if ($type=="list")
	{
/*
		$filename=$appfolder."/custom/tmp/".uniqid(rand(), true).".xlsx";
		$file = new \SplFileObject($filename,'w');
		$tmpl = new ExcelWriter($file);
		$tmpl->prepare();
		// $tmpl->writeItem(['first', 'last']);
		// $tmpl->writeItem(['first' => 'James', 'last' => 'Bond']);
		$tab=array( 'Week','Testing Phases','Client',"Country","Version","Sprint","Business Type","Project type","Project name","Test case","Budget","maj");
		$tmpl->writeItem($tab);
		
		$q ="SELECT prj.*,weeks.week,tst.name AS testname, followup.tests, followup.sprint FROM ".$MyOpt["tbl"]."_projects AS prj ";
		$q.="LEFT JOIN (SELECT week FROM core_followup GROUP BY week) AS weeks ON 1=1 ";
		$q.="LEFT JOIN ".$MyOpt["tbl"]."_testcase AS tst ON 1=1 ";
		$q.="LEFT JOIN ".$MyOpt["tbl"]."_followup AS followup ON (prj.id=followup.project AND tst.id=followup.phase) ";
		$q.="WHERE prj.actif='oui' ";
		if ($limit=="week")
		{
			$dte=time()-7*86400;
			$week=date("Y-W",$dte);
			$q.="AND weeks.week='".$week."'";
		}

		 $q.="ORDER BY weeks.week,prj.name;";
		
		$sql->Query($q);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$tab=array(
			"Week" => $sql->data["week"],
			"Testing Phases" => $sql->data["testname"],
			"Client" => $sql->data["client"],
			"Country" => $sql->data["country"],
			"Version" => $sql->data["version"],
			"Sprint" => $sql->data["sprint"],
			"Business Type" => $sql->data["business"],
			"Project type" => $sql->data["type"],
			"Project name" => $sql->data["name"],
			"Test case" => $sql->data["tests"],
			"Budget" => $sql->data["budget"],
			"maj" => (($sql->data["tests"]=="") ? "non" : "oui")
			);
			$tmpl->writeItem($tab);
		}
		$tmpl->finish();

		header("Content-Disposition: attachment; filename=\"projects.xlsx\"");
		header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		$filesize = filesize($filename);
		// open file for reading in binary mode
		$fp = fopen($filename, 'rb');
		// read the entire file into a binary string
		$binary = fread($fp, $filesize);
		// finally close the file
		fclose($fp);
		echo $binary;
		unlink($filename);

*/
		$myxls = new BiffWriter();
		$myxls->outfile = 'projects.xls';
		
		$myxls->xlsSetColWidth(0, 0, 10);

		$myxls->xlsWriteText(0, 0, 'Week');
		$myxls->xlsWriteText(0, 1, 'Testing Phases');
		$myxls->xlsWriteText(0, 2, 'Client');
		$myxls->xlsWriteText(0, 3, "Country");
		$myxls->xlsWriteText(0, 4, "Version");
		$myxls->xlsWriteText(0, 5, "Sprint");
		$myxls->xlsWriteText(0, 6, "Business Type");
		$myxls->xlsWriteText(0, 7, "Project type");
		$myxls->xlsWriteText(0, 8, "Project name");
		$myxls->xlsWriteText(0, 9, "Test case");
		$myxls->xlsWriteText(0, 10, "Budget");
		$myxls->xlsWriteText(0, 11, "maj");

		// $q="SELECT prj.*, followup.tests, followup.sprint,followup.week,tst.name AS testname FROM ".$MyOpt["tbl"]."_projects AS prj LEFT JOIN ".$MyOpt["tbl"]."_followup AS followup ON prj.id=followup.project LEFT JOIN ".$MyOpt["tbl"]."_testcase AS tst ON followup.phase=tst.id WHERE actif='oui'";
		$q ="SELECT prj.*,weeks.week,tst.name AS testname, followup.tests, followup.sprint FROM ".$MyOpt["tbl"]."_projects AS prj ";
		$q.="LEFT JOIN (SELECT week FROM core_followup GROUP BY week) AS weeks ON 1=1 ";
		$q.="LEFT JOIN ".$MyOpt["tbl"]."_testcase AS tst ON 1=1 ";
		$q.="LEFT JOIN ".$MyOpt["tbl"]."_followup AS followup ON (prj.id=followup.project AND tst.id=followup.phase) ";
		$q.="WHERE prj.actif='oui' ";
		if ($limit=="week")
		{
			$dte=time()-7*86400;
			$week=date("Y-W",$dte);
			$q.="AND weeks.week='".$week."'";
		}

		 $q.="ORDER BY weeks.week,prj.name;";
		
		$sql->Query($q);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$myxls->xlsWriteText($i, 0, $sql->data["week"]);
			$myxls->xlsWriteText($i, 1, $sql->data["testname"]);
			$myxls->xlsWriteText($i, 2, $sql->data["client"]);
			$myxls->xlsWriteText($i, 3, $sql->data["country"]);
			$myxls->xlsWriteText($i, 4, $sql->data["version"]);
			$myxls->xlsWriteText($i, 5, $sql->data["sprint"]);
			$myxls->xlsWriteText($i, 6, $sql->data["business"]);
			$myxls->xlsWriteText($i, 7, $sql->data["type"]);
			$myxls->xlsWriteText($i, 8, $sql->data["name"]);
			$myxls->xlsWriteText($i, 9, $sql->data["tests"]);
			$myxls->xlsWriteText($i, 10, AffMontant($sql->data["budget"]));
			$myxls->xlsWriteText($i, 11, ($sql->data["tests"]=="") ? "non" : "oui");
		}

		$myxls->xlsParse();

	}
	else if ($type=="list2")
	{
		$tabProjects=array();
		$q="SELECT * FROM ".$MyOpt["tbl"]."_projects WHERE actif='oui'";
			
		$sql->Query($q);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$tabProjects[$sql->data["id"]]=$sql->data;
		}


		$myxls = new BiffWriter();
		$myxls->outfile = 'projects.xls';
		
		$myxls->xlsSetColWidth(0, 0, 10);

		$myxls->xlsWriteText(0, 0, 'Week');
		$myxls->xlsWriteText(0, 1, 'Testing Phases');
		$myxls->xlsWriteText(0, 2, 'Client');
		$myxls->xlsWriteText(0, 3, "Country");
		$myxls->xlsWriteText(0, 4, "Version");
		$myxls->xlsWriteText(0, 5, "Sprint");
		$myxls->xlsWriteText(0, 6, "Business Type");
		$myxls->xlsWriteText(0, 7, "Project type");
		$myxls->xlsWriteText(0, 8, "Project name");
		$myxls->xlsWriteText(0, 9, "Test case");
		$myxls->xlsWriteText(0, 10, "Budget");
		$myxls->xlsWriteText(0, 11, "maj");

		$i=1;
		foreach($tabProjects as $id=>$d)
		{			 
			$prj = new project_class($id,$sql);
			$res=$prj->GetFollowup("",false);
			$week_prj=$res["week"];
			$sprint=$res["sprint"];

			foreach($res["phases"] as $ii=>$p)
			{	
				$myxls->xlsWriteText($i, 0, $week_prj);
				$myxls->xlsWriteText($i, 1, $p["name"]);
				$myxls->xlsWriteText($i, 2, $d["client"]);
				$myxls->xlsWriteText($i, 3, $d["country"]);
				$myxls->xlsWriteText($i, 4, $d["version"]);
				$myxls->xlsWriteText($i, 5, $sprint);
				$myxls->xlsWriteText($i, 6, $d["business"]);
				$myxls->xlsWriteText($i, 7, $d["type"]);
				$myxls->xlsWriteText($i, 8, $d["name"]);
				$myxls->xlsWriteText($i, 9, $p["tests"]);
				$myxls->xlsWriteText($i, 10, AffMontant($d["budget"]));
				$myxls->xlsWriteText($i, 11, ($p["tests"]=="") ? "non" : "oui");
				$i=$i+1;
			}
		}
		$myxls->xlsParse();
	}
	else if ($type=="status")
	{
		$dte=time()-7*86400;
		$week=date("Y-W",$dte);
		$tabProjects=array();

		$q="SELECT * FROM ".$MyOpt["tbl"]."_projects WHERE actif='oui'";			
		$sql->Query($q);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$tabProjects[$sql->data["id"]]=$sql->data;
			$tabProjects[$sql->data["id"]]["maj"]="oui";
			$tabProjects[$sql->data["id"]]["week"]=$week;
		}

		$q ="SELECT prj.*,weeks.week,tst.name AS testname, followup.tests, followup.sprint FROM ".$MyOpt["tbl"]."_projects AS prj ";
		$q.="LEFT JOIN (SELECT week FROM core_followup GROUP BY week) AS weeks ON 1=1 ";
		$q.="LEFT JOIN ".$MyOpt["tbl"]."_testcase AS tst ON 1=1 ";
		$q.="LEFT JOIN ".$MyOpt["tbl"]."_followup AS followup ON (prj.id=followup.project AND tst.id=followup.phase) ";
		$q.="WHERE prj.actif='oui' ";
		$q.="AND weeks.week='".$week."'";
		
		$sql->Query($q);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			if ($sql->data["tests"]=="")
			{
				$tabProjects[$sql->data["id"]]["maj"]="non";
			}
		}
			
		$myxls = new BiffWriter();
		$myxls->outfile = 'status.xls';
		
		$myxls->xlsSetColWidth(0, 0, 20);
		$myxls->xlsSetColWidth(0, 1, 10);
		$myxls->xlsSetColWidth(0, 2, 5);

		$myxls->xlsWriteText(0, 0, 'Project Name');
		$myxls->xlsWriteText(0, 1, 'Week');
		$myxls->xlsWriteText(0, 2, 'MAJ');

		$i=1;
		foreach($tabProjects as $id=>$d)
		{
			$myxls->xlsWriteText($i, 0, $d["name"]);
			$myxls->xlsWriteText($i, 1, $d["week"]);
			$myxls->xlsWriteText($i, 2, $d["maj"]);
			$i=$i+1;
		}
		$myxls->xlsParse();
		
	}
	else if ($type=="tab")
	{
		$data=array();
		
		$tabProjects=array();
		$q="SELECT * FROM ".$MyOpt["tbl"]."_projects WHERE actif='oui'";
			
		$sql->Query($q);
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$tabProjects[$sql->data["id"]]=$sql->data;
		}

		$tabTitre = array(
					"week"=>"Week",
					"phase"=>"Testing phases",
					"client"=>"Client",
					"country"=>"country",
					"version"=>"version",
					"sprint"=>"sprint",
					"business"=>"Business type",
					"type"=>"Project type",
					"name"=>"Project name",
					"tests"=>"Test case",
					"budget"=>"budget",
					"maj"=>"maj"
					);
		
		$i=1;
		foreach($tabProjects as $id=>$d)
		{			 
			$prj = new project_class($id,$sql);
			$res=$prj->GetFollowup("",false);
			$week_prj=$res["week"];
			$sprint=$res["sprint"];

			$t=7+date("N")-$res["day"];
			$tt=($t>7) ? $t-7 : $t;
			$dte=time()-$tt*86400;
			$week_actual=date("Y-W",$dte);

			foreach($res["phases"] as $ii=>$p)
			{	
				if (($limit!="week") || (($limit=="week") && ($week_prj==$week_actual)))
				{
					$data[$i]=array(
					"week"=>$week_prj,
					"phase"=>$p["name"],
					"client"=>$d["client"],
					"country"=>$d["country"],
					"version"=>$d["version"],
					"sprint"=>$sprint,
					"business"=>$d["business"],
					"type"=>$d["type"],
					"name"=>$d["name"],
					"tests"=>$p["tests"],
					"budget"=>AffMontant($d["budget"]),
					"maj"=>($p["tests"]=="") ? "oui" : "non"
					);
					$i=$i+1;
				}
			}
		}

		$filename = "projects.csv";
		header("Content-Disposition: attachment; filename=\"$filename\"");
		// header("Content-Type: application/vnd.ms-excel");
		header("Content-Type: text/csv");
		$flag = false;
		foreach($data as $row)
		{
			if (!$flag)
			{
				// display field/column names as first row
				// echo implode("\t", array_keys($row)) . "\r\n";
				echo implode("\t", $tabTitre) . "\r\n";
				// $firstline = array_map(__NAMESPACE__ . '\map_colnames', array_keys($row));
				// fputcsv($out, $firstline, ',', '"');
				echo $firstline;
				$flag = true;
			}
			array_walk($row, __NAMESPACE__ . '\cleanData');
 			echo implode("\t", array_values($row)) . "\r\n";
		}
	}
	else if ($type=="template")
	{
		$filename=$appfolder."/custom/tmp/".uniqid(rand(), true).".xlsx";
		$file = new \SplFileObject($filename,'w');
		$tmpl = new ExcelWriter($file);
		$tmpl->prepare();
		// $tmpl->writeItem(['first', 'last']);
		// $tmpl->writeItem(['first' => 'James', 'last' => 'Bond']);
		$q="SELECT * FROM ".$MyOpt["tbl"]."_testcase WHERE actif='oui'";
		$sql->Query($q);
		$tab=array();
		$tab[]="sprint";
		$tab[]="wave";
		for($i=0; $i<$sql->rows; $i++)
		{
			$sql->GetRow($i);
			$tab[]=$sql->data["name"];
		}
		$tmpl->writeItem($tab);
		$tmpl->finish();

		header("Content-Disposition: attachment; filename=\"import.xlsx\"");
		header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		$filesize = filesize($filename);
		// open file for reading in binary mode
		$fp = fopen($filename, 'rb');
		// read the entire file into a binary string
		$binary = fread($fp, $filesize);
		// finally close the file
		fclose($fp);
		echo $binary;
		unlink($filename);
	}
	else if ($type=="tmplbacklog")
	{
		$filename=$appfolder."/custom/tmp/".uniqid(rand(), true).".xlsx";
		$file = new \SplFileObject($filename,'w');
		$tmpl = new ExcelWriter($file);
		$tmpl->prepare();
		// $tmpl->writeItem(['first', 'last']);
		// $tmpl->writeItem(['first' => 'James', 'last' => 'Bond']);
		$q="SELECT * FROM ".$MyOpt["tbl"]."_backlog WHERE actif='oui'";
		$sql->Query($q);
		$tab=array();
		$tab[]="day (YYYY-MM-DD)";
		$tab[]="sprint";
		$tab[]="wave";
		for($i=0; $i<$sql->rows; $i++)
		{
			$sql->GetRow($i);
			$tab[]=$sql->data["name"];
		}
		$tmpl->writeItem($tab);
		$tmpl->finish();

		header("Content-Disposition: attachment; filename=\"backlog.xlsx\"");
		header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		$filesize = filesize($filename);
		// open file for reading in binary mode
		$fp = fopen($filename, 'rb');
		// read the entire file into a binary string
		$binary = fread($fp, $filesize);
		// finally close the file
		fclose($fp);
		echo $binary;
		unlink($filename);
	}



function cleanData(&$str)
{
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
	  // force certain number/date formats to be imported as strings
    if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
      $str = "'$str";
    }
	if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
}

function map_colnames($input)
{
	global $tabTitre;
	return isset($tabTitre[$input]) ? $tabTitre[$input] : $input;
}

?>