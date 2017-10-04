<?php

$callback = $_GET['callback'] ?? FALSE;

// Are we dealing with jsonp requests (example highcharts)
if (!empty($callback)) {

  // Data
  $data = "Time,Worker1,Worker2,Total
3/9/13,5691,4346,15000
3/10/13,5403,4112,14000
3/11/13,15574,11356,22000
3/12/13,16211,11876,38000";


  /**
   *
   * SELECT
  CONCAT(HOUR(FROM_UNIXTIME(time)), ':00') AS hourly_time,
  AVG(hashrate) AS pool_hashrate,
  COUNT(*) AS number_of_submissions
  FROM hashstats
  WHERE time > (UNIX_TIMESTAMP(NOW()) - (24 * 60 * 60))
  GROUP BY hourly_time;
   */

  print $_GET['callback'] . '(' . json_encode($data) . ')';

}

// Json API requests
else {
  // Header
  $data[] = ['Time', 'Worker 1', 'Worker 2'];
  $data[] = ['01/01/2017', '5', '11'];
  $data[] = ['01/02/2017', '6', '8'];

  print json_encode($data);
}



