<?php

// What page/algo we are on
$algo = 'bitcore';

// Connect mysql
$conn = include('config.php');

/**
 * Helper class
 * @var minerHelper.php
 */
include_once('minerHelper.php');

// Gets data for specific miner address
$miner_address = $_GET['address'] ?? "";
$workers = minerHelper::getWorkers($conn, $miner_address);
foreach ($workers as $worker) {
  print 'Version: ' . $worker['version'];

  $hashrate = minerHelper::getHashrate($conn, $algo, $worker['version'], $worker['name']);
  print ' - hashrate: ' . minerHelper::Itoa2($hashrate['hashrate']) . 'h/s';
  print '<br />';
}
