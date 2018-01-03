<?php

// Connect mysql
$conn = include_once('config.php');

// RPC wallet
include_once('wallet-rpc.php');
include_once('easybitcoin.php');

/**
 * Helper class
 * @var minerHelper.php
 */
include_once('web/minerHelper.php');

// Send payouts
sendExtraPayouts($conn, 1425, 0.01);

/**
 * Function to check for users with balances pending to payout
 * @param $db
 */
function sendExtraPayouts($db, $coin_id = 1425, $extra_payout = FALSE) {

  // Check for the coin details
  $stmt = $db->prepare("SELECT * FROM coins WHERE id = :coin_id");
  $stmt->execute([
    ':coin_id' => $coin_id
  ]);

  // Get coin data
  $coin_info = $stmt->fetch(PDO::FETCH_OBJ);

  // Connect to wallet
  $remote = new WalletRPC($coin_info);

  $min_payout = minerHelper::miner_getMinPayouts()[minerHelper::miner_getAlgos()[$coin_id]];
  print 'Wallet min. payout: ' . $min_payout . "\n";

  // Process extra payout
  if (!empty($extra_payout)) {
    print 'Activating extra payout: ' . $extra_payout . "\n";
    $min_payout =  $extra_payout;
  }

  $info = $remote->getinfo();
  if(!$info) {
    print "Send payouts: can't connect to " . $coin_info->symbol . " wallet" . "\n";
    return;
  }

  // Get the accounts which due payout
  $stmt = $db->prepare("SELECT * FROM accounts WHERE balance > :min_payout AND coinid = :coin_id ORDER BY balance DESC");
  $stmt->execute([
    ':min_payout' => $min_payout,
    ':coin_id' => $coin_id
  ]);

  $balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($balances as $user_account) {
    print 'Send payouts: ' . $user_account['id'] . ' -- ' . $user_account['balance'] . "\n";
    // Try to clear the balance
    $tx = $remote->sendtoaddress($user_account['username'], round($user_account['balance'], 8));

    if(!$tx) {
      $error = $remote->error;
      print "Send payouts ERROR: " . $error . ' -- ' . $user_account['username'] . ' -- ' . $user_account['balance'];
    }
    else {
      // Add entry about the transaction
      $stmt = $db->prepare("INSERT INTO payouts(account_id, time, amount, fee, tx, idcoin) VALUES(:account_id, :time, :amount, :fee, :tx, :idcoin)");
      $stmt->execute([
        ':account_id' => $user_account['id'],
        ':time' => time(),
        ':amount' => $user_account['balance'],
        ':fee' => 0,
        ':tx' => $tx,
        ':idcoin' => $coin_id
      ]);

      // Deduct user balance
      $stmt = $db->prepare("UPDATE accounts SET balance = balance - :payout WHERE id = :userid");
      $stmt->execute([
        ':payout' => $user_account['balance'],
        ':userid' => $user_account['id']
      ]);

    }
  }
}