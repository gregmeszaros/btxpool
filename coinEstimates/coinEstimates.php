<?php

include_once 'src/HTMLTable2JSON.php';

/**
 * Helper class
 * @var minerHelper.php
 */
include_once(__DIR__ . '/../web/minerHelper.php');
$redis = include_once(__DIR__ . '/../config-redis.php');

$helper = new HTMLTable2JSON();

$coins = [];
$coins['btx'] = '1425';
$coins['bwk'] = '1426';
$coins['lux'] = '1427';
$coins['bsd'] = '1429';
$coins['rvn'] = '1430';

// Loop coins and prepare the estimates
foreach ($coins as $coin_id => $coin) {

  // Raven needs a special handling as it's not on what to mine (yet)
  if ($coin_id ==  'rvn') {

    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => 'http://www.ravencalc.xyz/getprofit.php',
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => array(
        'rate' => 10,
      )
    ));
    // Send the request & save response to $resp
    $resp = curl_exec($curl);

    $coin_custom = json_decode($resp, TRUE);

    // Close request to clear up some resources
    curl_close($curl);

    print "RAVEN curl finished \n";

  }
  else {
    // Get network difficulty
    $network_info = minerHelper::getNetworkInfo($coin, $redis);

    switch ($coin_id) {
      case 'btx':
        // Create JSON file for each algo
        $helper->tableToJSON('https://whattomine.com/coins/202-btx-timetravel10?hr=10&d_enabled=true&d=' . $network_info['difficulty'] . '&p=300.0&fee=0.0&cost=0.0&hcost=0.0&commit=Calculate', TRUE, '', NULL, NULL, FALSE, FALSE, FALSE, FALSE, TRUE, NULL, $coin);
        print "\n";
        break;
      case 'bwk':
        // Create JSON file for each algo
        $helper->tableToJSON('https://whattomine.com/coins/224-bwk-nist5?hr=10&d_enabled=true&d=' . $network_info['difficulty'] . '&p=300.0&fee=0.0&cost=0.0&hcost=0.0&commit=Calculate', TRUE, '', NULL, NULL, FALSE, FALSE, FALSE, FALSE, TRUE, NULL, $coin);
        print "\n";
        break;
      case 'lux':
        // Create JSON file for each algo
        $helper->tableToJSON('https://whattomine.com/coins/212-lux-phi1612?hr=10&d_enabled=true&d=' . $network_info['difficulty'] . '&p=300.0&fee=0.0&cost=0.0&hcost=0.0&commit=Calculate', TRUE, '', NULL, NULL, FALSE, FALSE, FALSE, FALSE, TRUE, NULL, $coin);
        print "\n";
        break;
      case 'bsd':
        // Create JSON file for each algo
        $helper->tableToJSON('https://whattomine.com/coins/201-bsd-xevan?hr=10&d_enabled=true&d=' . $network_info['difficulty'] . '&p=300.0&fee=0.0&cost=0.0&hcost=0.0&commit=Calculate', TRUE, '', NULL, NULL, FALSE, FALSE, FALSE, FALSE, TRUE, NULL, $coin);
        print "\n";
        break;
    }
  }

}

// Store the 10mh/s earning estimate for each coin
$data = [];

// Load json and store the new estimated earnings for each coin in REDIS
foreach ($coins as $coin_id => $coin) {
  if ($coin_id ==  'rvn') {
    $data[$coin_id] = $coin_custom['in_rvn'];
  }
  else {
    // Read JSON file
    $json = file_get_contents(__DIR__  . '/json/' . $coin . '.json');

    // Decode JSON
    $json_data = json_decode($json,TRUE);
    $data[$coin_id] = str_replace(',', '', $json_data['Est. Rewards'][1]['cell_text']);
  }
}

// Store the values we need
$redis->set('coin_estimates', json_encode($data));

?>
