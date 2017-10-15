<?php

$callback = $_GET['callback'] ?? FALSE;
$type = $_GET['type'] ?? FALSE;

// Connect mysql
$conn = include_once('config.php');

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
      // Total pool hashrate graph query
      $stmt = $conn->prepare("SELECT
        CONCAT(DAY(FROM_UNIXTIME(time)), '/', MONTH(FROM_UNIXTIME(time)), '/', YEAR(FROM_UNIXTIME(time)), ' ', HOUR(FROM_UNIXTIME(time)), ':00') AS TimeDate,
        AVG(hashrate) AS Hashrate
        FROM hashstats
        WHERE time > (UNIX_TIMESTAMP(NOW()) - (24 * 60 * 60))
        GROUP BY TimeDate
        ORDER BY AVG(id) ASC");
      $stmt->execute();

      $array_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
      $data = str_putcsv($array_data);
      break;

    case 'user-hashrate':
      // Data
      $data = "Time,Worker1,Worker2,Total
        3/9/13 00:00:00,5691,4346,15000
        3/9/13 01:00:00,5403,4112,14000
        3/9/13 02:00:00,15574,11356,22000
        3/9/13 03:00:00,15574,11356,22000
        3/9/13 04:00:00,15574,11356,22000
        3/9/13 05:00:00,15574,11356,22000
        3/9/13 06:00:00,15574,11356,22000
        3/9/13 07:00:00,15574,11356,22000
        3/9/13 08:00:00,15574,11356,22000
        3/9/13 09:00:00,15574,11356,22000
        3/9/13 10:00:00,15574,11356,22000
        3/9/13 11:00:00,15574,11356,22000
        3/9/13 12:00:00,15574,11356,22000
        3/9/13 13:00:00,15574,11356,22000
        3/9/13 14:00:00,15574,11356,22000
        3/9/13 15:00:00,15574,11356,22000
        3/9/13 16:00:00,15574,11356,22000
        3/9/13 17:00:00,15574,11356,22000
        3/9/13 18:00:00,15574,11356,22000
        3/9/13 19:00:00,15574,11356,22000
        3/9/13 20:00:00,15574,11356,22000
        3/9/13 21:00:00,15574,11356,22000
        3/9/13 22:00:00,15574,11356,22000
        3/9/13 23:00:00,15574,11356,22000
        3/9/13 24:00:00,15574,11356,22000";
      break;
  }

  print $_GET['callback'] . '(' . json_encode($data, JSON_UNESCAPED_SLASHES) . ')';

}

// Json API requests
else {
  // Header
  $data = [];
  print json_encode($data);
}