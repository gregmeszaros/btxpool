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
    $conn = include(__DIR__ . '/../config-bitcore.php');
    break;
  case "bulwark":
    $coin_id = 1426;
    // Connect mysql
    $conn = include(__DIR__ . '/../config-bulwark.php');
    break;
  case "lux":
    $coin_id = 1427;
    $conn = include(__DIR__ . '/../config-lux.php');
    break;
  case "verge":
    $coin_id = 1428;
    $conn = include(__DIR__ . '/../config-verge.php');
    break;
  case "bitsend":
    $coin_id = 1429;
    $conn = include(__DIR__ . '/../config-bitsend.php');
    break;
  case "raven":
    $coin_id = 1430;
    $conn = include(__DIR__ . '/../config-raven.php');
    break;
  case "megacoin":
    $coin_id = 1431;
    $conn = include(__DIR__ . '/../config-megacoin.php');
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
$getCoinSettings = new Twig_SimpleFunction('getCoinSettings', function ($coin_seo_name) {
  $settings = [];
  switch ($coin_seo_name) {
    case 'bitcore':
      $settings['port'] = 8001;
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
      $settings['mine_nvidia'] = 'ccminer -a bitcore -o stratum+tcp://omegapool.cc:8001 -u your_bitcore_address -p x';
      $settings['mine_amd_download'] = 'https://github.com/LIMXTEC/BitCore/releases/download/0.14.1.6/6.Windows_Miner_05-2017.zip';
      $settings['mine_amd'] = 'sgminer --kernel timetravel10 -o stratum+tcp://omegapool.cc:8001 -u your_bitcore_address -p x -I 23';
      return $settings;
      break;
    case 'bulwark':
      $settings['port'] = 8002;
      $settings['algo'] = 'nist5';
      $settings['algosgminer'] = 'talkcoin-mod';
      $settings['intensity'] = 25;
      $settings['example_pass'] = 'x';
      $settings['symbol'] = 'BWK';
      $settings['block_explorer_payout'] = 'https://altmix.org/coins/10-Bulwark/explorer/transaction/';
      $settings['block_explorer_payout_suffix'] = '';
      $settings['crypto_url'] = 'https://crypt0.zone/calculator/details/BWK';
      $settings['whattomine_url'] = 'https://whattomine.com/coins/224-bwk-nist5';
      $settings['mine_nvidia_download'] = 'https://github.com/palginpav/ccminer/releases/tag/1.1-nist5';
      $settings['mine_nvidia'] = 'ccminer -a nist5 -o stratum+tcp://omegapool.cc:8002 -u your_bwk_address.rig_name -p x';
      $settings['mine_amd_download'] = 'https://github.com/nicehash/sgminer/releases/tag/5.6.1';
      $settings['mine_amd'] = 'sgminer --kernel talkcoin-mod -o stratum+tcp://omegapool.cc:8002 -u your_bulwark_address.rig_name -p x -I 21';
      return $settings;
      break;
    case 'lux':
      $settings['port'] = 8003;
      $settings['algo'] = 'phi';
      $settings['algosgminer'] = 'phi';
      $settings['intensity'] = 21;
      $settings['example_pass'] = 'x';
      $settings['symbol'] = 'LUX';
      $settings['block_explorer_payout'] = 'https://chainz.cryptoid.info/lux/tx.dws?';
      $settings['block_explorer_payout_suffix'] = '.htm';
      $settings['crypto_url'] = 'https://crypt0.zone/calculator/details/LUX';
      $settings['whattomine_url'] = 'https://whattomine.com/coins/212-lux-phi1612';
      $settings['mine_nvidia_download'] = 'https://github.com/anorganix/ccminer-phi/releases';
      $settings['mine_nvidia'] = 'ccminer -a phi -o stratum+tcp://omegapool.cc:8003 -u your_lux_address -p x';
      $settings['mine_amd_download'] = 'https://github.com/216k155/sgminer-phi1612-Implemented/releases';
      $settings['mine_amd'] = 'sgminer --kernel phi -o stratum+tcp://omegapool.cc:8003 -u your_lux_address -p x -I 23';
      return $settings;
      break;
    case 'verge':
      $settings['port'] = 8004;
      $settings['algo'] = 'x17';
      $settings['algosgminer'] = 'x17';
      $settings['intensity'] = 21;
      $settings['example_pass'] = 'x';
      $settings['symbol'] = 'XVG';
      $settings['block_explorer_payout'] = 'https://chainz.cryptoid.info/btx/tx.dws?';
      $settings['block_explorer_payout_suffix'] = '.htm';
      $settings['whattomine_url'] = 'https://whattomine.com/coins/219-xvg-x17';
      $settings['mine_nvidia_download'] = '';
      $settings['mine_nvidia'] = '';
      $settings['mine_amd_download'] = '';
      $settings['mine_amd'] = '';
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
      $settings['mine_nvidia'] = 'ccminer -a xevan -o stratum+tcp://omegapool.cc:8005 -u your_bitsend_address -p x -i 21';
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
      $settings['block_explorer_payout'] = 'http://explorer.threeeyed.info/tx/';
      $settings['block_explorer_payout_suffix'] = '';
      $settings['crypto_url'] = 'https://crypt0.zone/calculator/details/RVN';
      $settings['whattomine_url'] = 'https://crypt0.zone/calculator/details/RVN';
      $settings['mine_nvidia_download'] = 'https://github.com/MSFTserver/ccminer/releases/tag/2.2.5-rvn';
      $settings['mine_nvidia'] = 'ccminer-x64 -a x16r -o stratum+tcp://omegapool.cc:8006 -u your_raven_address.rig_name -p x -i 21';
      $settings['mine_amd_download'] = 'https://github.com/aceneun/sgminer-gm-x16r';
      $settings['mine_amd'] = 'sgminer --kernel x16r -o stratum+tcp://omegapool.cc:8006 -u your_raven_address.rig_name -p x';
      return $settings;
      break;
    case 'megacoin':
      $settings['port'] = 8007;
      $settings['algo'] = 'scrypt';
      $settings['algosgminer'] = 'scrypt';
      $settings['intensity'] = 21;
      $settings['example_pass'] = 'x';
      $settings['symbol'] = 'MEC';
      $settings['block_explorer_payout'] = 'https://chainz.cryptoid.info/mec/tx.dws?';
      $settings['block_explorer_payout_suffix'] = '';
      $settings['crypto_url'] = 'https://whattomine.com/coins/26-mec-scrypt';
      $settings['whattomine_url'] = 'https://whattomine.com/coins/26-mec-scrypt';
      $settings['mine_nvidia_download'] = 'https://github.com/tpruvot/ccminer/releases';
      $settings['mine_nvidia'] = 'ccminer-x64 -a scrypt -o stratum+tcp://omegapool.cc:8007 -u your_megacoin_address.rig_name -p x -i 21';
      $settings['mine_amd_download'] = 'https://github.com/aceneun/sgminer-gm-x16r';
      $settings['mine_amd'] = 'sgminer --kernel scrypt -o stratum+tcp://omegapool.cc:8007 -u your_megacoin_address.rig_name -p x';
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
