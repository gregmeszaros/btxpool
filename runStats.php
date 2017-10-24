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

// Send payouts
sendPayouts($conn);

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
  // Long term stats (pool and invidiual users)
  //
  $tm = floor(time() / 60 / 60) * 60 * 60;
  foreach(minerHelper::miner_getAlgos() as $algo) {
    $pool_rate = minerHelper::getPoolHashrate($db, $algo);

    // Insert total pool hashrate stats
    $stmt = $db->prepare("INSERT INTO hashstats(time, hashrate, earnings, algo) VALUES(:time, :hashrate, :earnings, :algo)");
    $stmt->execute([
      ':time' => $t,
      ':hashrate' => $pool_rate['hashrate'],
      ':earnings' => null,
      ':algo' => $algo
    ]);

    // Individual user stats
    $user_hashstats = minerHelper::getUserPoolHashrate($db, $algo);
    foreach ($user_hashstats as $user_hash_data) {
      $stmt = $db->prepare("INSERT INTO hashuser(userid, time, hashrate, hashrate_bad, algo) VALUES(:userid, :time, :hashrate, :hashrate_bad, :algo)");
      $stmt->execute([
        ':userid' => $user_hash_data['userid'],
        ':time' => $t,
        ':hashrate' => $user_hash_data['hashrate'],
        ':hashrate_bad' => 0,
        ':algo' => $algo
      ]);
    }

  }

  // @TODO -> Store pool and user earnings too??

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
  print "version: 1.3" . "\n";

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

    // New Wallet RPC call
    $remote_check = new WalletRPC($coin_info);

    // Get more detailed info about the block we found
    $block = $remote_check->getblock($db_block['blockhash']);

    // Check for block transaction
    $block_tx = $remote_check->gettransaction($block['tx'][0]);

    // If we found the transaction
    if (!empty($block_tx)) {
      // Get the reward from the block we found
      $reward = $block_tx['amount'];

      // Yet immature tx
      if ($reward < 0) {
        // Check for immature transaction
        if (!empty($block_tx['details'])) {
          if (!empty($block_tx['details']['amount']) && !empty($block_tx['details']['category']) && $block_tx['details']['category'] == 'immature') {
            print 'Processing immature block';
            $reward = $block_tx['details']['amount'];
          }
        }

      }

      // We continue if reward is set, when the block is found the reward is not set for few seconds
      if ($reward > 0) {
        // How much is the block reward
        $db_block['reward'] = $reward;

        // Save tx hash
        $db_block['tx_hash'] = $block_tx['txid'];

        $stmt = $db->prepare("SELECT SUM(difficulty) as total_hash FROM shares WHERE valid = :valid AND algo = :coin_id");
        $stmt->execute([':coin_id' => minerHelper::miner_getAlgos()[$db_block['coin_id']], ':valid' => 1]);

        $total_hash_power = $stmt->fetch(PDO::FETCH_ASSOC);
        print 'Total hash power: ' . $total_hash_power['total_hash'] . "\n";

        $stmt = $db->prepare("SELECT userid, SUM(difficulty) AS total_user_hash FROM shares WHERE valid = :valid AND algo=:coin_id GROUP BY userid");
        $stmt->execute([':coin_id' => minerHelper::miner_getAlgos()[$db_block['coin_id']], ':valid' => 1]);

        $hash_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($hash_users as $hash_user) {
          print 'Total hash power user ID: ' . $hash_user['userid'] . ' -- ' . $hash_user['total_user_hash'] . "\n";

          // Calculate how much each user will earn
          $amount = $reward * $hash_user['total_user_hash'] / $total_hash_power['total_hash'];
          print 'Earned: ' . $amount . "\n";

          // Earned amount
          $amount_earned = minerHelper::takePoolFee($amount, minerHelper::miner_getAlgos()[$db_block['coin_id']]);
          print 'Earned - fee deducted: ' . $amount_earned . "\n";

          // Get some user related info
          $user_data = minerHelper::getAccount($db, $hash_user['userid']);

          // Save the earning for each user when block is found
          minerHelper::addEarning($db, $user_data, $db_block, $amount_earned);
        }

        // When all earnings saved set the block from 'new' to 'immature'
        // So the other script can trigger, calculate number of confirmations, once confirmed update the earnings to mature
        $stmt = $db->prepare("UPDATE blocks SET category = :category, amount = :amount, txhash = :txhash WHERE id = :block_id");
        $stmt->execute([':category' => 'immature', ':block_id' => $db_block['id'], ':amount' => $db_block['reward'], ':txhash' => $db_block['tx_hash']]);

        // Delete shares where we calculated the earnings
        $stmt = $db->prepare("DELETE FROM shares WHERE algo = :algo AND coinid = :coin_id");
        $stmt->execute([':algo' => minerHelper::miner_getAlgos()[$db_block['coin_id']], ':coin_id' => $db_block['coin_id']]);
      }
    }
    // The cron run every minute if more than 1 block is found every minute causing issue so we break after 1 block
    break;
  }

  /////////////////////////////////////////////////
  /// //////// Update exisiting blocks /// ////////
  ///
  /// ////////                        /// ////////
  // Update all 'immature' blocks
  $stmt = $db->prepare("SELECT * FROM blocks WHERE category = :category ORDER by time");
  $stmt->execute([
    ':category' => 'immature'
  ]);

  $immature_blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($immature_blocks as $db_block) {
    // Check for the coin details
    $stmt = $db->prepare("SELECT * FROM coins WHERE id = :coin_id");
    $stmt->execute([
      ':coin_id' => $db_block['coin_id']
    ]);

    $coin_info = $stmt->fetch(PDO::FETCH_OBJ);

    // New Wallet RPC call
    $remote_check = new WalletRPC($coin_info);

    if (!empty($db_block['txhash'])) {
      $block_tx = $remote_check->gettransaction($db_block['txhash']);
      print 'Confirmations: ' . $block_tx['confirmations'] . "\n";

      // Check if the block is confirmed
      if ($block_tx['confirmations'] > 100) {
        // mature the block
        $category = 'mature';

        // @TODO
        // When mature balance > 0.5 do a payout and deduct user balance
        // Total paid -> sum(payouts)
      }
      else {
        $category = 'immature';
      }
      // Update block confirmations
      $stmt = $db->prepare("UPDATE blocks SET confirmations = :confirmations, category = :category WHERE id = :block_id");
      $stmt->execute([
        ':confirmations' => $block_tx['confirmations'],
        ':block_id' => $db_block['id'],
        ':category' => $category
      ]);

    }
    else {
      // check orphan blocks?
      print 'empty tx hash -> orphan block?';
    }
  }

  /////////////////////////////////////////////////
  /// //////// Update exisiting blocks /// ////////
  ///
  /// ////////                        /// ////////
  // Update all 'mature' blocks
  // Mature earnings, calculate user balance
  $stmt = $db->prepare("SELECT * FROM blocks WHERE category = :category ORDER by time");
  $stmt->execute([
    ':category' => 'mature'
  ]);

  $mature_blocks = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach($mature_blocks as $db_block) {

    print 'Processing block: ' . $db_block['id'] . ' -- ' . $db_block['height'] . "\n";
    $stmt = $db->prepare("SELECT userid, SUM(amount) AS immature_balance FROM earnings where blockid = :block_id AND status = -1 GROUP BY userid");
    $stmt->execute([
      ':block_id' => $db_block['id']
    ]);

    // Mature earnings, calculate user balance
    $immature_balances = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($immature_balances as $immature_balance) {
      print 'user ID: ' . $immature_balance['userid'] . ' -- ' . $immature_balance['immature_balance'] . "\n";

      // Update pending user balance
      $stmt = $db->prepare("UPDATE accounts SET balance = balance + :balance WHERE id = :userid");
      $stmt->execute([
        ':balance' => $immature_balance['immature_balance'],
        ':userid' => $immature_balance['userid']
      ]);

      // Mature earnings
      $stmt = $db->prepare("UPDATE earnings SET status = 1 WHERE status = -1 AND userid = :userid AND blockid = :block_id");
      $stmt->execute([
        ':userid' => $immature_balance['userid'],
        ':block_id' => $db_block['id']
      ]);
    }

    // Set block category to 'settled' which means the earnings matured for this block and ready to be paid
    $stmt = $db->prepare("UPDATE blocks SET category = :category WHERE id = :block_id");
    $stmt->execute([
      ':category' => 'settled',
      ':block_id' => $db_block['id']
    ]);

  }

}

/**
 * Function to check for users with balances pending to payout
 * @param $db
 */
function sendPayouts($db, $coin_id = 1425) {

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
