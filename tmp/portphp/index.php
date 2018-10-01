<?
// https://portphp.readthedocs.io/en/latest/examples/

require_once 'vendor/autoload.php';

use Port\Excel\ExcelReader;

$file = new \SplFileObject('TravelPlan.xlsx');
$reader = new ExcelReader($file);
$reader->setHeaderRowNumber(0);

// print_r($reader);

echo "<table>";

foreach($reader->worksheet as $l=>$tc)
{
	echo "<tr>";
	foreach($tc as $c=>$d)
	{
		echo "<td>";
		echo $d." ";
		echo "</td>";
	}
	echo "</tr>";
}
echo "</table>";

?>