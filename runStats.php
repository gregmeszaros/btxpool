<?php

// Connect mysql
$conn = include_once('config.php');

/**
 * Helper class
 * @var minerHelper.php
 */
include_once('minerHelper.php');

// Run stats
updatePoolHashrate($conn);

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
  print_r($pool_rate);

  print minerHelper::Itoa2($pool_rate['hashrate']) . 'h/s';

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