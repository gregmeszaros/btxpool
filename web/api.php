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

  $data = [];

  // Standard API JSON responses
  if(!empty($wallet) && !empty($coin)) {

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

    $data['coin'] = $coin;
    $data['miner_address'] = $user['username'];
    $data['immature_balance'] = minerHelper::roundSimple($immature_balance['immature_balance']);
    $data['pending_payout'] = minerHelper::roundSimple($user['balance']);
    $data['total_paid'] = minerHelper::roundSimple($total_paid['total_payout']);
    $data['active_workers'] = $active_workers;
    $data['request_time'] = time();

  }

  header('Content-type: application/json');
  print json_encode($data);
  exit();

}