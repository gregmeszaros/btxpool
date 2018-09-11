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

  if ($coin_id ==  'votecoin') {

    $read_data = minerHelper::getPoolStatsEquihash($conn, $coin_id);

    // Insert basic stats
    $total_network_hash = $coin_custom['pools'][$coin_id]['poolStats']['networkSolsString'];
    $network_difficulty = $coin_custom['pools'][$coin_id]['poolStats']['networkDiff'];
    $total_pool_hash_raw = $coin_custom['pools'][$coin_id]['hashrate'] * 2;
    $total_pool_hash = $coin_custom['pools'][$coin_id]['hashrateString'];
    $ttf = $coin_custom['pools'][$coin_id]['luckHours'];
    $active_miners = $coin_custom['pools'][$coin_id]['minerCount'];
    $active_workers = $coin_custom['pools'][$coin_id]['workerCount'];

    $workers = $coin_custom['pools'][$coin_id]['workers'];

    updateWorkers($conn, $coin_id, $workers);

    print 'Total net hash: ' . $total_network_hash . "\n";
    print 'Network difficulty: ' . $network_difficulty . "\n";
    print 'Total pool hash: ' . $total_pool_hash . "\n";
    print 'Time to find: ' . $ttf . " hours \n";
    print 'Active miners: ' . $active_miners . "\n";
    print 'Active workers: ' . $active_workers . "\n";

    $set_data = minerHelper::setPoolStatsEquihash($conn, [
      'coin_id' => $coin_id,
      'networkSolsString' => $total_network_hash,
      'poolHashRate' => $total_pool_hash_raw,
      'poolSolsString' => $total_pool_hash,
      'networkDiff' => $network_difficulty,
      'minerCount' => $active_miners,
      'id' => $read_data[0]['id']
    ]);

    $read_data = minerHelper::getPoolStatsEquihash($conn, $coin_id);

    // Insert pool historial hash stats
    $t = time() - 2 * 60;

    $stmt = $conn->prepare("INSERT INTO hashstats(time, hashrate, earnings, algo) VALUES(:time, :hashrate, :earnings, :algo)");
    $stmt->execute([':time' => $t, ':hashrate' => $total_pool_hash_raw, ':earnings' => null, ':algo' => $coin_id]);

    print "VOT curl finished \n";
  }

}

/**
 * Helper function to update workers
 */
function updateWorkers($db, $coin_id, $workers) {

  $allAccounts = minerHelper::getAccounts($db, $coin_id);

  $set_workers = [];

  // Check for any new account
  foreach ($workers as $worker => $worker_data) {
    $username = explode('.', $worker)[0];

    // Calculate total hashrate for all workers
    $default = !empty($set_workers[$username]['total_hashrate']) ? $set_workers[$username]['total_hashrate'] : 0;
    print $default;
    $set_workers[$username]['total_hashrate'] = $default + $worker_data['hashrate'] * 2;
    $set_workers[$username]['workers'][] = $worker_data;

    // If the account doesn't exist we create it
    if (!isset($allAccounts[$username])) {
      minerHelper::addAccount($db, [
        'username' => $username,
        'coin_id' => $coin_id
      ]);
    }

  }

  // Update all workers / disable inactive workers
  foreach ($allAccounts as $account => $data) {

    // If we have workers set them
    if (!empty($set_workers[$account]) && is_array($set_workers[$account])) {

      $stmt = $db->prepare("UPDATE accounts SET
      workers = :workers
      WHERE username = :username");

      // Set workers array
      $stmt->execute([
        ':workers' => serialize($set_workers[$account]),
        ':username' => $account
      ]);

      // Insert pool historial hash stats
      $t = time() - 2 * 60;

      // Set total hashrate for a specific user
      $stmt = $db->prepare("INSERT INTO hashuser(userid, time, hashrate, hashrate_bad, algo) VALUES(:userid, :time, :hashrate, :hashrate_bad, :algo)");
      $stmt->execute([':userid' => $allAccounts[$account][0]['id'], ':time' => $t, ':hashrate' => $set_workers[$account]['total_hashrate'], ':hashrate_bad' => 0, ':algo' => $coin_id]);
    }
    // Otherwise make workers empty
    else {
      $stmt = $db->prepare("UPDATE accounts SET
      workers = :workers
      WHERE username = :username");

      $stmt->execute([
        ':workers' => serialize([]),
        ':username' => $account
      ]);
    }
  }
}

?>
