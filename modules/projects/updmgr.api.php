<?
// ---- Refuse l'acc�s en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- V�rifie les param�tres
	$id=checkVar("id","numeric");
	$fonc=checkVar("fonc","varchar");

// ---- R�cup�re les infos
	$ret=array();
	$ret["type"]=$fonc;

	if (($fonc=="delete") && ($id>0))
	{
		error_log("delete:".$id);
		$query = "DELETE FROM ".$MyOpt["tbl"]."_projects_mgr WHERE id='".$id."'";
		$sql->Delete($query);
	}
// ---- Renvoie le r�sultat
	echo json_encode($ret);
?>