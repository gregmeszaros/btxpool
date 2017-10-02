<?php

// Add autoload
require __DIR__ . '/vendor/autoload.php';

// Connect mysql
$conn = include_once('config.php');

/**
 * Helper class
 * @var minerHelper.php
 */
include_once('minerHelper.php');

// What page/algo we are on
$algo = 'bitcore';

// Gets data for specific miner address
$miner_address = $_GET['address'] ?? "";

$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader, [
  'cache' => __DIR__ . '/twig_cache',
  // 'auto_reload' => true // Should be turned off on production
]);

// Get workers for miner address
$workers = minerHelper::getWorkers($conn, $miner_address);

foreach ($workers as $key => $worker) {
  $hashrate = minerHelper::getHashrate($conn, $algo, $worker['version'], $worker['name']);
  $workers[$key]['hashrate'] = minerHelper::Itoa2($hashrate['hashrate']) . 'h/s';
}

// Return TWIG template
print $twig->render('index.html.twig', [
  'name' => 'Greg',
  'workers' => $workers
]);
