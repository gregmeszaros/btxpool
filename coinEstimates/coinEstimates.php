<?php

include_once 'src/HTMLTable2JSON.php';

/**
 * Helper class
 * @var minerHelper.php
 */
include_once(__DIR__ . '/../web/minerHelper.php');

// Connect mysql
$conn = include_once(__DIR__ . '/../config-votecoin.php');

$coins = [];
$coins['votecoin'] = '1500';

// Get cURL resource
$curl = curl_init();
// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, array(
  CURLOPT_RETURNTRANSFER => 1,
  CURLOPT_URL => $conn->apiUrl . '/api/stats',
));

// Send the request & save response to $resp
$resp = curl_exec($curl);

$coin_custom = json_decode($resp, TRUE);

// Close request to clear up some resources
curl_close($curl);

// Loop coins and prepare the estimates
foreach ($coins as $coin_id => $coin) {

  // Raven needs a special handling as it's not on what to mine (yet)
  if ($coin_id ==  'votecoin') {

    $read_data = minerHelper::getPoolStatsEquihash($conn, $coin_id);

    $total_network_hash = $coin_custom['pools'][$coin_id]['poolStats']['networkSolsString'];
    $network_difficulty = $coin_custom['pools'][$coin_id]['poolStats']['networkDiff'];
    $total_pool_hash = $coin_custom['pools'][$coin_id]['hashrateString'];
    $ttf = $coin_custom['pools'][$coin_id]['luckHours'];
    $active_miners = $coin_custom['pools'][$coin_id]['minerCount'];
    $active_workers = $coin_custom['pools'][$coin_id]['workerCount'];

    print 'Total net hash: ' . $total_network_hash . "\n";
    print 'Network difficulty: ' . $network_difficulty . "\n";
    print 'Total pool hash: ' . $total_pool_hash . "\n";
    print 'Time to find: ' . $ttf . " hours \n";
    print 'Active miners: ' . $active_miners . "\n";
    print 'Active workers: ' . $active_workers . "\n";

    $set_data = minerHelper::setPoolStatsEquihash($conn, [
      'coin_id' => $coin_id,
      'networkSolsString' => $total_network_hash,
      'poolSolsString' => $total_pool_hash,
      'networkDiff' => $network_difficulty,
      'minerCount' => $active_miners,
      'id' => $read_data[0]['id']
    ]);

    $read_data = minerHelper::getPoolStatsEquihash($conn, $coin_id);
    print_r($read_data); die();

    print "VOT curl finished \n";
  }

}

// Store the 10mh/s earning estimate for each coin
$data = [];

// Store the values we need
//$redis->set('vot_data', json_encode($data));

?>
