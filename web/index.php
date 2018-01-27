<?php

// Add autoload
require __DIR__ . '/../vendor/autoload.php';

$redis = include_once(__DIR__ . '/../config-redis.php');

// What coin dashboard we looking at
$coin_seo_name = $_GET['coin'] ?? FALSE;
$conn = FALSE;

switch ($coin_seo_name) {
  case "bitcore":
    $coin_id = 1425;
    // Connect mysql
    $conn = include_once(__DIR__ . '/../config-bitcore.php');
    break;
  case "bulwark":
    $coin_id = 1426;
    // Connect mysql
    $conn = include_once(__DIR__ . '/../config-bulwark.php');
    break;
  case "lux":
    $coin_id = 1427;
    $conn = include_once(__DIR__ . '/../config-lux.php');
    break;
  case "verge":
    $coin_id = 1428;
    $conn = include_once(__DIR__ . '/../config-verge.php');
    break;
  default:
    $coin_id = FALSE;
}

/**
 * Helper class
 * @var minerHelper.php
 */
include_once('minerHelper.php');

// Check if $_GET['wallet'] is set or we have cookie value with a wallet
$wallet = minerHelper::checkWallet($coin_seo_name);

$data = [];
$data['miner_address'] = $wallet;
$data['coin_id'] = $coin_id;
$data['coin_seo_name'] = $coin_seo_name;

if (!empty($conn)) {
  // Get the total pool hashrate
  $total_pool_hashrate = minerHelper::getPoolHashrateStats($conn, minerHelper::miner_getAlgos()[$data['coin_id']], 1800, $redis);
  // @TODO -> geto total hashrates and miners for all coins? Get it from redis!!
}

// Get the current page
$page = $_GET['page'] ?? "index";

// Empty coin
if (empty($_GET['coin'])) {
  $page = "index";
}

// Prepare twig
$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader, [
  'cache' => __DIR__ . '/../twig_cache',
  'auto_reload' => true // Should be turned off on production
]);

// Create some custom functions to twig
$addFriendlyHash = new Twig_SimpleFunction('showFriendlyHash', function ($hashrate) {
  return minerHelper::Itoa2($hashrate) . 'h/s';
});

$addDateTime = new Twig_SimpleFunction('showDateTime', function ($timestamp) {
  return minerHelper::getDateTime($timestamp);
});

$roundSimple = new Twig_SimpleFunction('roundSimple', function ($value) {
  return minerHelper::roundSimple($value);
});

$formatConfirmations = new Twig_SimpleFunction('formatConfirmations', function ($value) {
  return minerHelper::formatConfirmations($value);
});

$formatCoinName = new Twig_SimpleFunction('formatCoinName', function ($coin_seo_name) {
  if ($coin_seo_name == 'lux') {
    // Special handling for LUX
    return 'luxcoin';
  }
  return $coin_seo_name;
});

/**
 * Return specific coin symbols
 */
$getCoinSymbol = new Twig_SimpleFunction('getCoinSymbol', function ($coin_seo_name) {
  switch ($coin_seo_name) {
    case 'bitcore':
      return 'BTX';
    break;
    case 'bulwark':
      return 'BWK';
    break;
    case 'lux':
      return 'LUX';
    break;
    case 'verge':
      return 'XVG';
    break;
  }
});

// Add the functions
$twig->addFunction($addFriendlyHash);
$twig->addFunction($addDateTime);
$twig->addFunction($roundSimple);
$twig->addFunction($formatConfirmations);
$twig->addFunction($formatCoinName);
$twig->addFunction($getCoinSymbol);

// Load available routes
$load_routes = minerHelper::getRoutes();

// If template found, load otherwise 404
if (!empty($load_routes[$page]['template'])) {
  // Return TWIG template
  $default_variables = [
    'routes' => $load_routes,
    'current_route' => $page,
    'wallet' => $wallet,
    'total_pool_hashrate' => $total_pool_hashrate ?? 0
  ];

  print $twig->render($load_routes[$page]['template'], array_merge($default_variables, minerHelper::_templateVariables($conn, $page, $data, $redis)));
}
else {
  header("HTTP/1.0 404 Not Found");
}
