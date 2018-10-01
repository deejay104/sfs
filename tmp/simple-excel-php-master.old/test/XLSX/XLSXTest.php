<?php

require_once('../../src/SimpleExcel/SimpleExcel.php');
use SimpleExcel\SimpleExcel;


$excel = new SimpleExcel('xls');                    // instantiate new object (will automatically construct the parser & writer type as CSV)

$excel->parser->loadFile('test.xlsx');            // load a CSV file from server to be parsed
                                                   

?>
