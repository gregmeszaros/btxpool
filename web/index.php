<?php

// Add autoload
require __DIR__ . '/../vendor/autoload.php';

$redis = include_once(__DIR__ . '/../config-redis.php');
$conn = FALSE;
$seo_site_name = 'omegapool.cc';

// Get the current page
$page = $_GET['page'] ?? "index";

// Empty coin
if (empty($_GET['coin'])) {
  $page = "index";
}

/**
 * Start - custom handling for bitcorepool.cc
 * *****************************************
 */
$server_name = $_SERVER['HTTP_HOST'];

if (strpos($server_name, 'bitcorepool.cc') !== false) {
  $seo_site_name = 'bitcorepool.cc';
  $_GET['coin'] = "bitcore";
  $page = $_GET['page'] ?? "dashboard";
  if ($page == 'index') {
    // No pools listing page at bitcorepool.cc
    $page = 'dashboard';
  }
}
/**
 * *****************************************
 * End - custom handling for bitcorepool.cc
 */

// What coin dashboard we looking at
$coin_seo_name = $_GET['coin'] ?? FALSE;

switch ($coin_seo_name) {
  case "bitcore":
    $coin_id = 1425;
    // Connect mysql
    $conn = include(__DIR__ . '/../config-bitcore.php');
    break;
  case "lux":
    $coin_id = 1427;
    $conn = include(__DIR__ . '/../config-lux.php');
    break;
  case "bitsend":
    $coin_id = 1429;
    $conn = include(__DIR__ . '/../config-bitsend.php');
    break;
  case "raven":
    $coin_id = 1430;
    $conn = include(__DIR__ . '/../config-raven.php');
    break;
  case "votecoin":
    $coin_id = "votecoin";
    $conn = include(__DIR__ . '/../config-votecoin.php');
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
$data['seo_site_name'] = $seo_site_name;

if (!empty($conn)) {
  if (minerHelper::poolType($coin_id) == 'yiimp') {
    // Get the total pool hashrate
    $total_pool_hashrate = minerHelper::getPoolHashrateStats($conn, minerHelper::miner_getAlgos()[$data['coin_id']], 1800, $redis);
    // @TODO -> get total hashrates and miners for all coins? Get it from redis!!
  }
  else {
    // General coin info - (Equihash pools)
    $poolStatsVotecoin = minerHelper::getPoolStatsEquihash($conn, $data['coin_id']);
    $total_pool_hashrate = [];
    $total_pool_hashrate['hashrate'] = $poolStatsVotecoin ? $poolStatsVotecoin[0]['poolHashRate'] : 0;
  }
}

// Prepare twig
$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader, [
  'cache' => __DIR__ . '/../twig_cache',
  'auto_reload' => false // Should be turned off on production
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

$formatConfirmations = new Twig_SimpleFunction('formatConfirmations', function ($value, $category) {
  return new Twig_Markup(minerHelper::formatConfirmations($value, $category), 'UTF-8' );
});

$formatCoinName = new Twig_SimpleFunction('formatCoinName', function ($coin_seo_name) {
  if ($coin_seo_name == 'lux') {
    // Special handling for LUX
    return 'luxcoin';
  }

  if ($coin_seo_name == 'raven') {
    // Special handling for RAVEN
    return 'ravencoin';
  }
  return $coin_seo_name;
});

/**
 * Return specific coin symbols
 */
$getCoinSettings = new Twig_SimpleFunction('getCoinSettings', function ($coin_seo_name) use ($seo_site_name) {
  $settings = [];
  switch ($coin_seo_name) {
    case 'bitcore':
      $settings['port'] = '8001 or 1111';
      $settings['algo'] = 'bitcore';
      $settings['algosgminer'] = 'timetravel10';
      $settings['intensity'] = 23;
      $settings['example_pass'] = 'x';
      $settings['symbol'] = 'BTX';
      $settings['block_explorer_payout'] = 'https://chainz.cryptoid.info/btx/tx.dws?';
      $settings['block_explorer_payout_suffix'] = '.htm';
      $settings['crypto_url'] = 'https://crypt0.zone/calculator/details/BTX';
      $settings['whattomine_url'] = 'https://whattomine.com/coins/202-btx-timetravel10';
      $settings['mine_nvidia_download'] = 'https://github.com/tpruvot/ccminer/releases';
      $settings['mine_nvidia'] = 'ccminer -a bitcore -o stratum+tcp://' . $seo_site_name . ':8001 -u your_bitcore_address -p x';
      $settings['mine_amd_download'] = 'https://github.com/LIMXTEC/BitCore/releases/download/0.14.1.6/6.Windows_Miner_05-2017.zip';
      $settings['mine_amd'] = 'sgminer --kernel timetravel10 -o stratum+tcp://' . $seo_site_name . ':8001 -u your_bitcore_address -p x -I 23';
      return $settings;
      break;
    case 'lux':
      $settings['port'] = 8003;
      $settings['algo'] = 'phi2';
      $settings['algosgminer'] = 'phi2';
      $settings['intensity'] = 21;
      $settings['example_pass'] = 'x';
      $settings['symbol'] = 'LUX';
      $settings['block_explorer_payout'] = 'https://chainz.cryptoid.info/lux/tx.dws?';
      $settings['block_explorer_payout_suffix'] = '.htm';
      $settings['crypto_url'] = 'https://crypt0.zone/calculator/details/LUX';
      $settings['whattomine_url'] = 'https://whattomine.com/coins/212-lux-phi1612';
      $settings['mine_nvidia_download'] = 'https://github.com/tpruvot/ccminer/releases/tag/2.3-tpruvot';
      $settings['mine_nvidia'] = 'ccminer -a phi2 -o stratum+tcp://' . $seo_site_name . ':8003 -u your_lux_address -p x';
      $settings['mine_amd_download'] = 'https://github.com/216k155/sgminer-phi1612-Implemented/releases';
      $settings['mine_amd'] = 'sgminer --kernel phi2 -o stratum+tcp://' . $seo_site_name . ':8003 -u your_lux_address -p x -I 23';
      return $settings;
      break;
    case 'bitsend':
      $settings['port'] = 8005;
      $settings['algo'] = 'xevan';
      $settings['algosgminer'] = 'xevan';
      $settings['intensity'] = 21;
      $settings['example_pass'] = 'x';
      $settings['symbol'] = 'BSD';
      $settings['block_explorer_payout'] = 'https://chainz.cryptoid.info/bsd/tx.dws?';
      $settings['block_explorer_payout_suffix'] = '.htm';
      $settings['crypto_url'] = 'https://crypt0.zone/calculator/details/BSD';
      $settings['whattomine_url'] = 'https://whattomine.com/coins/201-bsd-xevan';
      $settings['mine_nvidia_download'] = 'https://github.com/krnlx/ccminer-xevan/releases';
      $settings['mine_nvidia'] = 'ccminer -a xevan -o stratum+tcp://' . $seo_site_name . ':8005 -u your_bitsend_address -p x -i 21';
      $settings['mine_amd_download'] = '';
      $settings['mine_amd'] = '';
      return $settings;
      break;
    case 'raven':
      $settings['port'] = 8006;
      $settings['algo'] = 'x16r';
      $settings['algosgminer'] = 'x16r';
      $settings['intensity'] = 21;
      $settings['example_pass'] = 'x';
      $settings['symbol'] = 'RVN';
      $settings['block_explorer_payout'] = 'https://ravencoin.network/tx/';
      $settings['block_explorer_payout_suffix'] = '';
      $settings['crypto_url'] = 'https://crypt0.zone/calculator/details/RVN';
      $settings['whattomine_url'] = 'https://crypt0.zone/calculator/details/RVN';
      $settings['mine_nvidia_download'] = 'https://github.com/MSFTserver/ccminer/releases/tag/2.2.5-rvn';
      $settings['mine_nvidia'] = 'ccminer-x64 -a x16r -o stratum+tcp://' . $seo_site_name . ':8006 -u your_raven_address.rig_name -p x -i 21';
      $settings['mine_amd_download'] = 'https://github.com/aceneun/sgminer-gm-x16r';
      $settings['mine_amd'] = 'sgminer --kernel x16r -o stratum+tcp://' . $seo_site_name . ':8006 -u your_raven_address.rig_name -p x';
      return $settings;
      break;
  }
});

// Add the functions
$twig->addFunction($addFriendlyHash);
$twig->addFunction($addDateTime);
$twig->addFunction($roundSimple);
$twig->addFunction($formatConfirmations);
$twig->addFunction($formatCoinName);
$twig->addFunction($getCoinSettings);

// Load available routes
$load_routes = minerHelper::getRoutes($seo_site_name);

// If template found, load otherwise 404
if (!empty($load_routes[$page]['template'])) {
  // Return TWIG template
  $default_variables = [
    'routes' => $load_routes,
    'current_route' => $page,
    'wallet' => $wallet,
    'total_pool_hashrate' => $total_pool_hashrate ?? 0,
  ];

  print $twig->render($load_routes[$page]['template'], array_merge($default_variables, minerHelper::_templateVariables($conn, $page, $data, $redis)));
}
else {
  header("HTTP/1.0 404 Not Found");
}
