<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Vérifie les paramètres
	$id=checkVar("id","numeric");
	$fonc=checkVar("fonc","varchar");

// ---- Récupère les infos
	$ret=array();
	$ret["type"]=$fonc;

	if (($fonc=="delete") && ($id>0))
	{
		error_log("delete:".$id);
		$query = "DELETE FROM ".$MyOpt["tbl"]."_projects_mgr WHERE id='".$id."'";
		$sql->Delete($query);
	}
// ---- Renvoie le résultat
	echo json_encode($ret);
?>