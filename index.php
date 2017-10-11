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
$coin_id = 1425;

// Check if $_GET['wallet'] is set or we have cookie value with a wallet
$wallet = minerHelper::checkWallet();

$data = [];
$data['miner_address'] = $wallet;
$data['coin_id'] = $coin_id;

// Get the current page
$page = $_GET['page'] ?? "index";

// Prepare twig
$loader = new Twig_Loader_Filesystem(__DIR__ . '/templates');
$twig = new Twig_Environment($loader, [
  'cache' => __DIR__ . '/twig_cache',
  'auto_reload' => true // Should be turned off on production
]);

// Create some custom functions to twig
$function = new Twig_SimpleFunction('showFriendlyHash', function ($hashrate) {
  return minerHelper::Itoa2($hashrate) . 'h/s';
});

// Add the function
$twig->addFunction($function);

// Load available routes
$load_routes = minerHelper::getRoutes();

// If template found, load otherwise 404
if (!empty($load_routes[$page]['template'])) {
  // Return TWIG template
  $default_variables = [
    'routes' => $load_routes,
    'current_route' => $page,
    'load_charts' => $load_routes[$page]['load_charts'] ?? FALSE,
    'wallet' => $wallet
  ];

  print $twig->render($load_routes[$page]['template'], array_merge($default_variables, minerHelper::_templateVariables($conn, $page, $data)));
}
else {
  header("HTTP/1.0 404 Not Found");
}
