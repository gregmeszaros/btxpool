<?php

include_once 'src/HTMLTable2JSON.php';
$helper = new HTMLTable2JSON();

$coin = 'bitcore';

// Create JSON file for each algo
$helper->tableToJSON('https://whattomine.com/coins/202-btx-timetravel10?hr=25&d_enabled=true&d=6000.235&p=300.0&fee=0.0&cost=0.0&hcost=0.0&commit=Calculate', TRUE, '', NULL, NULL, FALSE, FALSE, FALSE, FALSE, TRUE, NULL, $coin);

// Load json and store the new estimated earnings for each coin in REDIS

// The redis values can be used on page load

?>
