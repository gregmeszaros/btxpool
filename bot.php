<?php

// Add autoload
require __DIR__ . '/vendor/autoload.php';

$bot = new PHPTelebot('', '');

// Simple answer
$bot->cmd('*', 'Command not recognised!');

// Simple echo command
$bot->cmd('/echo|/say', function ($text) {
  if ($text == '') {
    $text = 'Command usage: /echo [text] or /say [text]';
  }

  return Bot::sendMessage($text);
});

// Show all coin settings
$bot->cmd('/coins', function () {
  $keyboard[] = [
    ['text' => 'BTX', 'callback_data' => 'btx-stratum'],
    ['text' => 'BSD', 'callback_data' => 'bsd-stratum'],
    ['text' => 'Miner links', 'callback_data' => 'links'],
  ];
  $options = [
    'reply_markup' => ['inline_keyboard' => $keyboard],
  ];

  return Bot::sendMessage('Coins', $options);
});

// Answer callback queries
$bot->on('callback', function($key) {

  switch ($key) {
    case 'btx-stratum':
      return Bot::sendMessage('BTX stratum is: ccminer -a bitcore -o stratum+tcp://omegapool.cc:8001 -u your_bitcore_address -p x');
      break;
    case 'bsd-stratum':
      return Bot::sendMessage('Download BSD miner here');
      break;
    case 'links':
      $keyboard[] = [
        ['text' => 'BTX ccminer', 'callback_data' => 'btx-ccminer'],
        ['text' => 'BSD xevan miner', 'callback_data' => 'bsd-xevan'],
      ];
      $options = [
        'reply_markup' => ['inline_keyboard' => $keyboard],
      ];

      return Bot::sendMessage('Miner links', $options);
      break;
    case 'btx-ccminer':
      return Bot::sendMessage('https://github.com/tpruvot/ccminer');
      break;
    case 'bsd-xevan':
      return Bot::sendMessage('https://github.com/LIMXTEC/Xevan-GPU-Miner/releases');
      break;
  }

});

$bot->run();