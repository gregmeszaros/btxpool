<?php

$callback = $_GET['callback'] ?? FALSE;
$type = $_GET['type'] ?? FALSE;
$uid = $_GET['uid'] ?? FALSE;
$coin = $_GET['coin'] ?? "bitcore";
$wallet = $_GET['wallet'] ?? FALSE;

// Connect mysql
$conn = include_once(__DIR__ . '/../config-' . $coin . '.php');

/**
 * Convert a multi-dimensional, associative array to CSV data
 * @param  array $data the array of data
 * @return string       CSV text
 */
function str_putcsv($data) {
  # Generate CSV data from array
  $fh = fopen('php://temp', 'rw'); # don't create a file, attempt to use memory instead

  # write out the headers
  fputcsv($fh, array_keys(current($data)));

  # write out the data
  foreach ($data as $row) {
    fputcsv($fh, $row);
  }
  rewind($fh);
  $csv = stream_get_contents($fh);
  fclose($fh);

  return $csv;
}

// Are we dealing with jsonp requests (example highcharts)
if (!empty($callback)) {
  $data = '';

  // What type of graph data to return
  switch ($type) {
    case 'pool-hashrate':
      // Total pool hashrate graph query (gh/s)
      $stmt = $conn->prepare("SELECT
        CONCAT(MONTH(FROM_UNIXTIME(time)), '/', DAY(FROM_UNIXTIME(time)), '/', YEAR(FROM_UNIXTIME(time)), ' ', HOUR(FROM_UNIXTIME(time)), ':00') AS TimeDate,
        AVG(hashrate) / 1000 / 1000 / 1000 AS Hashrate
        FROM hashstats
        WHERE time > (UNIX_TIMESTAMP(NOW()) - (24 * 60 * 60))
        GROUP BY TimeDate
        ORDER BY AVG(id) ASC");
      $stmt->execute();

      $array_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data = str_putcsv($array_data);
      break;

    case 'user-hashrate':
      // Total user pool hashrate graph query (mh/s)
      $stmt = $conn->prepare("SELECT
        CONCAT(MONTH(FROM_UNIXTIME(time)), '/', DAY(FROM_UNIXTIME(time)), '/', YEAR(FROM_UNIXTIME(time)), ' ', HOUR(FROM_UNIXTIME(time)), ':00') AS TimeDate,
        AVG(hashrate) / 1000 / 1000 AS Hashrate
        FROM hashuser
        WHERE time > (UNIX_TIMESTAMP(NOW()) - (24 * 60 * 60))
        AND userid = :uid
        GROUP BY TimeDate
        ORDER BY AVG(id) ASC");
      $stmt->execute([
        ':uid' => $uid
      ]);

      $array_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data = str_putcsv($array_data);
      break;

    case 'user-payouts':
      // Sum of user payouts for 1 week period
      $stmt = $conn->prepare("SELECT
        CONCAT(MONTH(FROM_UNIXTIME(time)), '/', DAY(FROM_UNIXTIME(time)), '/', YEAR(FROM_UNIXTIME(time))) AS DayChart,
        SUM(amount) AS TotalPayout
        FROM payouts
        WHERE account_id = :uid
        GROUP BY DayChart
        ORDER BY AVG(id) DESC
        LIMIT 14");
      $stmt->execute([
        ':uid' => $uid
      ]);

      $array_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data = str_putcsv($array_data);
      break;

    case 'worker-distribution':
      // Total miners distribution graph
      $stmt = $conn->prepare("SELECT version, COUNT(*) as worker_count FROM workers GROUP BY version");
      $stmt->execute();

      $array_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data = str_putcsv($array_data);
      break;

    case 'block-difficulty':
      // Block difficulty change
      $stmt = $conn->prepare("SELECT height as Block, ROUND(difficulty, 2) as Difficulty FROM blocks ORDER BY height DESC LIMIT 0, 30");
      $stmt->execute();

      $array_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data = str_putcsv($array_data);
      break;
  }

  print $_GET['callback'] . '(' . json_encode($data, JSON_UNESCAPED_SLASHES) . ')';

}

// Json API requests
else {

  if ($type == 'coininfo') {

    /**
     * Helper class
     * @var minerHelper.php
     * Include only when we need it
     */
    include_once('minerHelper.php');
    $redis = include_once(__DIR__ . '/../config-redis.php');

    $data = [];

    $conn_btx = include(__DIR__ . '/../config-bitcore.php');
    $conn_lux = include(__DIR__ . '/../config-lux.php');
    $conn_bitsend = include(__DIR__ . '/../config-bitsend.php');
    $conn_raven = include(__DIR__ . '/../config-raven.php');

    $pool_hashrate_bitcore = minerHelper::getPoolHashrateStats($conn_btx, minerHelper::miner_getAlgos()[1425], 1800, $redis);
    $pool_hashrate_lux = minerHelper::getPoolHashrateStats($conn_lux, minerHelper::miner_getAlgos()[1427], 1800, $redis);
    $pool_hashrate_bitsend = minerHelper::getPoolHashrateStats($conn_bitsend, minerHelper::miner_getAlgos()[1429], 1800, $redis);
    $pool_hashrate_raven = minerHelper::getPoolHashrateStats($conn_raven, minerHelper::miner_getAlgos()[1430], 1800, $redis);

    $total_miners_bitcore = minerHelper::countMiners($conn_btx,1425)['total_count'] ?? 0;
    $total_miners_lux = minerHelper::countMiners($conn_lux,1427)['total_count'] ?? 0;
    $total_miners_bitsend = minerHelper::countMiners($conn_bitsend,1429)['total_count'] ?? 0;
    $total_miners_raven = minerHelper::countMiners($conn_raven,1430)['total_count'] ?? 0;

    $data['btx'] = [
      'symbol' => 'BTX',
      'name' => 'Bitcore',
      'algo' => 'TimeTravel10',
      'port' => ['8001', '1111'],
      'pool_hashrate' => $pool_hashrate_bitcore['hashrate'],
      'active_miners' => $total_miners_bitcore,
      'fee' => minerHelper::getPoolFee()['bitcore']
    ];

    $data['lux'] = [
      'symbol' => 'LUX',
      'name' => 'Luxcoin',
      'algo' => 'phi2',
      'port' => ['8003'],
      'pool_hashrate' => $pool_hashrate_lux['hashrate'],
      'active_miners' => $total_miners_lux,
      'fee' => minerHelper::getPoolFee()['phi2']
    ];

    $data['bsd'] = [
      'symbol' => 'BSD',
      'name' => 'Bitsend',
      'algo' => 'xevan',
      'port' => ['8005'],
      'pool_hashrate' => $pool_hashrate_bitsend['hashrate'],
      'active_miners' => $total_miners_bitsend,
      'fee' => minerHelper::getPoolFee()['xevan']
    ];

    $data['rvn'] = [
      'symbol' => 'RVN',
      'name' => 'Raven',
      'algo' => 'x16r',
      'port' => ['8006'],
      'pool_hashrate' => $pool_hashrate_raven['hashrate'],
      'active_miners' => $total_miners_raven,
      'fee' => minerHelper::getPoolFee()['x16r']
    ];

    header('Content-type: application/json');
    print json_encode($data);
    exit();

  }
  else {
    $data = [];

    // Standard API JSON responses
    if (!empty($wallet) && !empty($coin)) {

      /**
       * Helper class
       * @var minerHelper.php
       * Include only when we need it
       */
      include_once('minerHelper.php');
      $user = minerHelper::getAccount($conn, null, $wallet);
      $immature_balance = minerHelper::getImmatureBalance($conn, $user['coinid'], $user['id']);
      $total_paid = minerHelper::getTotalPayout($conn, $user['coinid'], $user['id']);
      $active_workers = count(minerHelper::getWorkers($conn, $wallet));

      $earnings_last_hour = minerHelper::getUserEarnings($conn, $user['coinid'], $user['id'], 60 * 60 * 1);
      $earnings_last_3_hours = minerHelper::getUserEarnings($conn, $user['coinid'], $user['id'], 60 * 60 * 3);
      $earnings_last_24_hours = minerHelper::getUserEarnings($conn, $user['coinid'], $user['id'], 60 * 60 * 24);

      $data['coin'] = $coin;
      $data['miner_address'] = $user['username'];
      $data['immature_balance'] = minerHelper::roundSimple($immature_balance['immature_balance']);
      $data['earnings_last_hour'] = minerHelper::roundSimple($earnings_last_hour['total_earnings']);
      $data['earnings_last_3_hours'] = minerHelper::roundSimple($earnings_last_3_hours['total_earnings']);
      $data['earnings_last_24_hours'] = minerHelper::roundSimple($earnings_last_24_hours['total_earnings']);
      $data['pending_payout'] = minerHelper::roundSimple($user['balance']);
      $data['total_paid'] = minerHelper::roundSimple($total_paid['total_payout']);
      $data['active_workers'] = $active_workers;
      $data['request_time'] = time();

    }

    header('Content-type: application/json');
    print json_encode($data);
    exit();
  }
}