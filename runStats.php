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
include_once('minerHelper.php');

$ip_check = minerHelper::getClientIp();
$sapi_type = php_sapi_name();
if(substr($sapi_type, 0, 3) == 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
  print "shell current time: " . time() . "\n";
} else {
  print "webserver - your IP: " . $ip_check;
  exit();
}

// Pool total hashrate
updatePoolHashrate($conn);

// Update earnings
updateEarnings($conn);

function updatePoolHashrate($db) {
  $t = time() - 2 * 60;

  // Delete old connections
  $stmt = $db->prepare("DELETE FROM stratums WHERE time < :time");
  $stmt->execute([
    ':time' => $t
  ]);

  // Delete old workers
  $stmt = $db->prepare("DELETE FROM workers WHERE pid NOT IN (SELECT pid FROM stratums)");
  $stmt->execute();

  //
  // Long term stats
  //
  $tm = floor(time() / 60 / 60) * 60 * 60;
  foreach(minerHelper::miner_getAlgos() as $algo) {
    $pool_rate = minerHelper::getPoolHashrate($db, $algo);

    // Insert pool hashrate stats
    $stmt = $db->prepare("INSERT INTO hashstats(time, hashrate, earnings, algo) VALUES(:time, :hashrate, :earnings, :algo)");
    $stmt->execute([
      ':time' => $t,
      ':hashrate' => $pool_rate['hashrate'],
      ':earnings' => null,
      ':algo' => $algo
    ]);

  }

  print minerHelper::Itoa2($pool_rate['hashrate']) . 'h/s' . "\n";

  /**
  dborun("DELETE FROM stratums WHERE time < $t");
  dborun("DELETE FROM workers WHERE pid NOT IN (SELECT pid FROM stratums)");

  // todo: cleanup could be done once per day or week...
  dborun("DELETE FROM hashstats WHERE IFNULL(hashrate,0) = 0 AND IFNULL(earnings,0) = 0");

  //////////////////////////////////////////////////////////////////////////////////////////////////////
  // long term stats

  $tm = floor(time()/60/60)*60*60;
  foreach(yaamp_get_algos() as $algo) {
    $pool_rate = yaamp_pool_rate($algo);

    $stats = getdbosql('db_hashstats', "time=$tm and algo=:algo", [':algo'=>$algo]);
    if(!$stats) {
      $stats = new db_hashstats;
      $stats->time = $tm;
      $stats->hashrate = $pool_rate;
      $stats->algo = $algo;
      $stats->earnings = null;
    }
    else {
      $percent = 1;
      $stats->hashrate = round(($stats->hashrate*(100-$percent) + $pool_rate*$percent) / 100);
    }

    $earnings = bitcoinvaluetoa(dboscalar(
      "SELECT SUM(amount*price) FROM blocks WHERE algo=:algo AND time>$tm AND category!='orphan'",
      [':algo'=>$algo]
    ));

    if (bitcoinvaluetoa($stats->earnings) != $earnings) {
      debuglog("$algo earnings: $earnings BTC");
      $stats->earnings = $earnings;
    }

    if (floatval($earnings) || $stats->hashrate)
      $stats->save();
  }
  */

}

/**
 * Update earnings when block is found
 * @param $db
 */
function updateEarnings($db) {

  // Get all new blocks
  $stmt = $db->prepare("SELECT * FROM blocks WHERE category = :category ORDER by time");
  $stmt->execute([
    ':category' => 'new'
  ]);

  $new_blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach($new_blocks as $db_block) {
    // Check for the coin details
    $stmt = $db->prepare("SELECT * FROM coins WHERE id = :coin_id");
    $stmt->execute([
      ':coin_id' => $db_block['coin_id']
    ]);

    $coin_info = $stmt->fetch(PDO::FETCH_OBJ);

    $remote_check = new WalletRPC($coin_info);

    // Get more detailed info about the block we found
    $block = $remote_check->getblock($db_block['blockhash']);

    // Check for block transaction
    $block_tx = $remote_check->gettransaction($block['tx'][0]);

    // If we found the transaction
    if (!empty($block_tx)) {
      print_r($block_tx);

      $stmt = $db->prepare("SELECT SUM(difficulty) FROM shares WHERE valid = :valid AND algo = :coin_id");
      $stmt->execute([
        ':coin_id' => $db_block['coin_id'],
        ':valid' => 1
      ]);

      $total_hash_power = $stmt->fetch(PDO::FETCH_ASSOC);
      print_r($total_hash_power);


      $stmt = $db->prepare("SELECT userid, SUM(difficulty) AS total FROM shares WHERE valid = :valid AND algo=:coin_id GROUP BY userid");
      $stmt->execute([
        ':coin_id' => $db_block['coin_id'],
        ':valid' => 1
      ]);

      $hash_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
      print_r($hash_users);

    }

  }

}
