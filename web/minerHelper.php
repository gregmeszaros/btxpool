<?php
/**
 * Created by PhpStorm.
 * User: gergelymeszaros
 * Date: 29/09/2017
 * Time: 15:01
 */

class minerHelper {

  public static function getRoutes() {
    // Check for specific coin first
    $coin = $_GET['coin'] ?? FALSE;
    if (!empty($coin)) {
      $base_route = 'index.php?coin=' . $coin . '&page=';
      $main_blockchain_url = [
        'bitcore' => 'https://chainz.cryptoid.info/btx/',
        'bulwark' => 'http://explorer.bulwarkcrypto.com/',
        'lux' => 'https://chainz.cryptoid.info/lux/',
        'verge' => 'https://verge-blockchain.info/',
        'bitsend' => 'https://chainz.cryptoid.info/bsd/',
        'raven' => 'http://threeeyed.info/'
      ];

      $explorer = $main_blockchain_url[$coin];
    }
    else {
      $base_route = 'index.php?page=';
      $explorer = '';
    }

    return [
      'index' => [
        'id' => 'home',
        'label' => 'omegapool.cc',
        'url' => $base_route . 'index',
        'template' => 'pools.html.twig'
      ],
      'miners' => [
        'id' => 'users',
        'label' => 'Miners',
        'url' => $base_route . 'miners',
        'template' => 'miners.html.twig'
      ],
      'blocks' => [
        'id' => 'cubes',
        'label' => 'Blocks',
        'url' => $base_route . 'blocks',
        'template' => 'blocks.html.twig'
      ],
      'block-earnings' => [
        'id' => 'cubes',
        'label' => 'Block earnings',
        'url' => $base_route . 'block-earnings',
        'template' => 'block-earnings.html.twig',
        'menu_exclude' => TRUE
      ],
      'dashboard' => [
        'id' => 'dashboard',
        'label' => 'Dashboard / minerpool.party',
        'url' => $base_route . 'dashboard',
        'template' => 'index.html.twig',
        'menu_exclude' => TRUE
      ],
      'explorer' => [
        'id' => 'search',
        'label' => 'Blockchain',
        'url' => $explorer,
        'target' => '_blank',
        'template' => 'miners.html.twig',
      ],
    ];
  }

  /**
   * Return human readable date and time
   * @param bool $timestamp
   * @return false|string
   */
  public static function getDateTime($timestamp = FALSE) {
    if (!empty($timestamp)) {
      return date('m/d/Y H:i:s', $timestamp);
    }
  }

  /**
   * Use same round function as it's used during payouts
   * @param $value
   * @param int $precision
   * @return float
   */
  public static function roundSimple($value, $precision = 8) {
    return round($value, $precision);
  }

  /**
   * Conversion for hash rates
   */
  public static function Itoa2($i, $precision = 1) {
    $s = '';
    if($i >= 1000*1000*1000*1000*1000)
      $s = round(floatval($i)/1000/1000/1000/1000/1000, $precision) ." P";
    else if($i >= 1000*1000*1000*1000)
      $s = round(floatval($i)/1000/1000/1000/1000, $precision) ." T";
    else if($i >= 1000*1000*1000)
      $s = round(floatval($i)/1000/1000/1000, $precision) ." G";
    else if($i >= 1000*1000)
      $s = round(floatval($i)/1000/1000, $precision) ." M";
    else if($i >= 1000)
      $s = round(floatval($i)/1000, $precision) ." k";
    else
      $s = round(floatval($i), $precision) . " ";

    return $s;
  }

  public static function miner_hashrate_constant($algo = null) {
    return pow(2, 42);		// 0x400 00000000
  }

  /**
   *
   * 300 -> 5 min
   * 3600 -> 60 x 60 (1h)
   * 86400 -> 60 x 60 x 24 (1 day)
   *
   * @return int
   */
  public static function miner_hashrate_step($step = 300) {
    return $step;
  }

  /**
   * Return available coins
   * @return array
   */
  public static function miner_getAlgos() {
    // return key / value pairs (coin ID, algo)
    return [
      '1425' => 'bitcore',
      '1426' => 'nist5',
      '1427' => 'phi',
      '1428' => 'x17',
      '1429' => 'xevan',
      '1430' => 'x16r'
    ];
  }

  /**
   * Return min payouts for coins
   * @return array
   */
  public static function miner_getMinPayouts() {
    // return key / value pairs (algo, min_payout amount)
    return [
      'bitcore' => 0.25,
      'nist5' => 0.1,
      'phi' => 0.1,
      'x17' => 1,
      'xevan' => 0.1,
      'x16r' => 0.1
    ];
  }

  /**
   * Format block confirmations
   * @param int $confirmations
   */
  public static function formatConfirmations($confirmations = 0) {
    if ($confirmations >= 0 && $confirmations < 100) {
      return "Immature " . "(" . $confirmations . ")";
    }
    if ($confirmations > 100) {
      return "Confirmed " . "(100+)";
    }
  }

  /**
   * Get the last found block time (minutes ago)
   * @param $time
   * @return string
   */
  public static function lastFoundBlockTime($time) {
    $time = time() - $time; // to get the time since that moment
    $time = ($time < 1)? 1 : $time;
    $tokens = array (
      31536000 => 'year',
      2592000 => 'month',
      604800 => 'week',
      86400 => 'day',
      3600 => 'hour',
      60 => 'minute',
      1 => 'second'
    );

    foreach ($tokens as $unit => $text) {
      if ($time < $unit) continue;
      $numberOfUnits = floor($time / $unit);
      return $numberOfUnits.' '. $text . (($numberOfUnits>1)? 's' : '');
    }
  }

  /**
   * Return user data for specified ID
   * @param $db
   * @param $account_id
   */
  public static function getAccount($db, $account_id = null, $miner_address = null) {

    // Return account for specific user ID
    if (!empty($account_id)) {
      $stmt = $db->prepare("SELECT * FROM accounts WHERE id=:account_id");
      $stmt->execute([
        ':account_id' => (int) $account_id
      ]);
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Return account for miner address
    if (!empty($miner_address)) {
      $stmt = $db->prepare("SELECT * FROM accounts WHERE username=:miner_address");
      $stmt->execute([
        ':miner_address' => $miner_address
      ]);
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }
  }

  /**
   * Load list of miners (cache the query for 1 minute)
   * @param $db
   * @param $coin_id
   * @return mixed
   */
  public static function getMiners($db, $coin_id) {
    $stmt = $db->prepare("SELECT DISTINCT userid, username, COUNT(w.id) AS workers_count FROM accounts ac INNER JOIN workers w ON ac.id = w.userid WHERE ac.coinid = :coin_id GROUP BY userid ORDER BY workers_count DESC");
    $stmt->execute([
      ':coin_id' => $coin_id,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Returns active workers for the miner address
   * @param Database connection
   * @param $miner_address
   * @return array
   */
  public static function getWorkers($db, $miner_address = "") {
    if (!empty($miner_address)) {
      $stmt = $db->prepare("SELECT * FROM workers where name = :miner_address");
      $stmt->execute([
        ':miner_address' => $miner_address
      ]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    return [];
  }

  /**
   * Get total miners count
   * @param $db
   * @param $coin_id
   */
  public static function countMiners($db, $coin_id, $redis = FALSE) {
    $stmt = $db->prepare("SELECT COUNT(DISTINCT(ac.id)) AS total_count FROM accounts ac INNER JOIN workers w ON ac.id = w.userid WHERE ac.coinid = :coin_id;");
    $stmt->execute([
      ':coin_id' => $coin_id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get total workers count
   * @param $db
   * @param $coin_id
   */
  public static function countWorkers($db, $coin_id, $redis = FALSE) {
    $stmt = $db->prepare("SELECT COUNT(w.id) as total_count FROM accounts ac INNER JOIN workers w ON ac.id = w.userid WHERE ac.coinid = :coin_id;");
    $stmt->execute([
      ':coin_id' => $coin_id
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Shows how many users are connected to each port
   */
  public static function countStratumConnections($db, $algo = FALSE, $redis = FALSE) {
    $stmt = $db->prepare("SELECT port, workers FROM stratums ORDER BY workers ASC;");
    $stmt->execute();

    return array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC));
  }

  /**
   * Get total shares submitted for the round
   * @param $db
   * @param $algo
   */
  public static function sumTotalShares($db, $algo, $user_id = FALSE, $redis = FALSE) {

    if (!empty($user_id)) {
      $stmt = $db->prepare("SELECT SUM(difficulty) AS total_user_hash FROM shares WHERE valid = :valid AND algo= :algo AND userid = :user_id;");
      $stmt->execute([
        ':valid' => 1,
        ':algo' => $algo,
        ':user_id' => $user_id
      ]);

      return $stmt->fetch(PDO::FETCH_ASSOC);

    }

    $stmt = $db->prepare("SELECT SUM(difficulty) AS total_user_hash FROM shares WHERE valid = :valid AND algo= :algo;");
    $stmt->execute([
      ':valid' => 1,
      ':algo' => $algo
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get the last X number of blocks
   * @param $db
   * @param string $miner_address
   */
  public static function getBlocks($db, $coin_id, $miner_address = "") {
    $limit = 30;

    // @TODO -> if we have miner address join with earnings!
    // @TODO -> cache the call for 2 mins
    $stmt = $db->prepare("SELECT * FROM blocks WHERE coin_id = :coin_id ORDER BY height DESC LIMIT 0, 50");
    $stmt->execute([
      ':coin_id' => $coin_id
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get some network information (RPC call to wallet through a cron job and saved to REDIS DB)
   * @param bool $coin_id
   * @param bool $redis
   */
  public static function getNetworkInfo($coin_id = FALSE, $redis = FALSE) {
    if (!empty($redis) && is_object($redis)) {
      $network_info = json_decode($redis->get('network_info_' . $coin_id), TRUE);
      return $network_info;
    }

    return [];
  }

  /**
   * There is a cron job running which will load estimates for 10mh/s with current difficulty for any coin
   * With this value we can calculate the earning estimate for the coin
   * @param bool $redis
   */
  public static function getCoinEstimates($redis = FALSE) {
    if (!empty($redis) && is_object($redis)) {
      $coin_estimates = json_decode($redis->get('coin_estimates'), TRUE);
      return $coin_estimates;
    }

    return ['btx' => 1, 'lux' => 0.1];
  }

  /**
   * Stats for user and worker
   * Cached for 5 minutes
   * @param $db
   * @param $coin_id
   * @param $version
   * @param $miner_address
   * @return mixed
   */
  public static function getHashrateStats($db, $coin_id, $version, $worker_id, $miner_address, $step = 300, $redis = FALSE) {
    $algo = self::miner_getAlgos()[$coin_id];
    $target = self::miner_hashrate_constant($algo);
    $interval = self::miner_hashrate_step($step);
    $delay = time()-$interval;

    $check_shares = $db->prepare("SELECT count(*) AS total_share_count FROM shares WHERE valid = 1 AND coinid = :coin_id");
    $check_shares->execute([':coin_id' => $coin_id]);

    // How many shares are submitted
    $tt_share_check = $check_shares->fetch(PDO::FETCH_ASSOC);

    // Add stats entry if we have at least 10 entries from each active miner (when block is found the shares are reset causing stats issues)
    $active_miners = self::countMiners($db, $coin_id)['total_count'];

    if ($tt_share_check['total_share_count'] > ($active_miners * 15)) {

      $stmt = $db->prepare("SELECT sum(difficulty) * :target / :interval / 1000 AS hashrate FROM shares WHERE valid AND time > :delay
AND workerid IN (SELECT id FROM workers WHERE algo=:algo AND id = :worker_id AND version=:version AND name = :miner_address)");
      $stmt->execute([
        ':target' => $target,
        ':interval' => $interval,
        ':delay' => $delay,
        ':algo' => $algo,
        ':worker_id' => $worker_id,
        ':version' => $version,
        'miner_address' => $miner_address
      ]);

      $data = [];
      $data = $stmt->fetch(PDO::FETCH_ASSOC);

      // If we have redis connection try to load old cached data
      if (!empty($redis) && is_object($redis)) {
        // Cache for 1hour (just in case)
        $redis->set('users_worker_hashrate_' . $miner_address . '_' . $worker_id . '_' . $step, json_encode($data), 3600 * 24);
        print '<!–- worker_users_hashrate ' . $miner_address . '_' . $worker_id . '_' . $step . ' - return from mysql/redis –>';
        return $data;
      }

      print '<!–- worker_users_hashrate ' . $miner_address . '_' . $worker_id . '_' . $step . ' - return from mysql –>';
      return $data;

    }

    // If we have redis connection try to load old cached data
    if (!empty($redis) && is_object($redis)) {
      $data = [];

      // Return cached data or cache a new stat info
      $worker_hashrate = json_decode($redis->get('users_worker_hashrate_' . $miner_address . '_' . $worker_id . '_' . $step), TRUE);

      // We have the data cached
      if (!empty($worker_hashrate)) {
        $data = $worker_hashrate;
        print '<!–- worker_users_hashrate ' . $miner_address . '_' . $worker_id . '_' . $step . ' - return from redis –>';
        return $data;
      }
    }

  }

  /**
   * Total hashrate for specific coin
   * @param $algo
   */
  public static function getPoolHashrate($db, $algo) {
    $target = self::miner_hashrate_constant($algo);
    $interval = self::miner_hashrate_step();
    $delay = time()-$interval;

    $stmt = $db->prepare("SELECT sum(difficulty) * :target / :interval / 1000 AS hashrate FROM shares WHERE valid AND time > :delay AND algo=:algo");
    $stmt->execute([
      ':target' => $target,
      ':interval' => $interval,
      ':delay' => $delay,
      ':algo' => $algo
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Total hashrate stats for specific coin
   * @param $algo
   */
  public static function getPoolHashrateStats($db, $algo, $step = 300, $redis = FALSE) {
    $interval = self::miner_hashrate_step($step);
    $delay = time()-$interval;

    // If we have redis connection try to load cached data first
    if (!empty($redis) && is_object($redis)) {
      $data = [];
      $total_hashrate = $redis->get($algo . '__total_pool_hashrate_' . $step);

      // We have the data cached
      if (!empty($total_hashrate)) {
        $data['hashrate'] = $total_hashrate;
        print '<!–- total_pool_hashrate - return from redis –>';
        return $data;
      }
    }

    $stmt = $db->prepare("SELECT avg(hashrate) AS hashrate FROM hashstats WHERE time > :delay AND algo=:algo");
    $stmt->execute([
      ':delay' => $delay,
      ':algo' => $algo
    ]);

    if (!empty($redis) && is_object($redis)) {
      $data = [];
      // We didn't have cached value let's cache it now
      $data = $stmt->fetch(PDO::FETCH_ASSOC);

      // Cache for 5 mins
      $redis->set($algo . '__total_pool_hashrate_' . $step, $data['hashrate'], 300);
    }

    print '<!–- total_pool_hashrate - return from mysql –>';
    // No cache just pure sql
    return $data ?? $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Total hashrate for specific coin and user
   * @param $algo
   */
  public static function getUserPoolHashrate($db, $algo) {
    $target = self::miner_hashrate_constant($algo);
    $interval = self::miner_hashrate_step();
    $delay = time()-$interval;

    // Return results for all users grouped by accounts
    $stmt = $db->prepare("SELECT userid, userid, sum(difficulty) * :target / :interval / 1000 AS hashrate FROM shares WHERE valid AND time > :delay AND algo=:algo GROUP BY userid");
    $stmt->execute([
      ':target' => $target,
      ':interval' => $interval,
      ':delay' => $delay,
      ':algo' => $algo
    ]);
    return array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC));
  }

  /**
   * Total hashrate for specific coin and user
   * @param $algo
   */
  public static function getUserPoolHashrateStats($db, $algo, $step = 300, $redis = FALSE) {
    $interval = self::miner_hashrate_step($step);
    $delay = time()-$interval;

    // If we have redis connection try to load cached data first
    if (!empty($redis) && is_object($redis)) {
      $data = [];
      $total_users_hashrate = json_decode($redis->get($algo . '_total_users_hashrate_' . $step), TRUE);

      // We have the data cached
      if (!empty($total_users_hashrate)) {
        $data = $total_users_hashrate;
        print '<!–- total_users_hashrate ' . $step . ' - return from redis –>';
        return $data;
      }
    }

    $stmt = $db->prepare("SELECT userid, userid, avg(hashrate) AS hashrate FROM hashuser WHERE time > :delay AND algo=:algo GROUP BY userid");
    $stmt->execute([
      ':delay' => $delay,
      ':algo' => $algo
    ]);

    if (!empty($redis) && is_object($redis)) {
      $data = [];
      // We didn't have cached value let's cache it now
      $data = array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC));

      // Cache for 5 mins
      $redis->set($algo . '_total_users_hashrate_' . $step, json_encode($data), 300);
    }

    print '<!–- total_users_hashrate ' . $step . ' - return from mysql –>';
    // No cache just pure sql
    return $data ?? array_map('reset', $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC));
  }

  // Function to get the client IP address
  public static function getClientIp() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
      $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
      $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
      $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
      $ipaddress = getenv('REMOTE_ADDR');
    else
      $ipaddress = 'UNKNOWN';
    return $ipaddress;
  }

  /**
   * Checks if we have active wallet
   */
  public static function checkWallet($coin_seo_name = FALSE) {

    // First check if we have something in get
    if (!empty($_GET['wallet'])) {
      // Update cookie
      setcookie($coin_seo_name . '__wallet', $_GET['wallet'], time() + (86400 * 30 * 30), "/"); // 1 month
      return $_GET['wallet'];
    }

    if(!empty($_COOKIE[$coin_seo_name . '__wallet'])) {
      // We have cookie
      return $_COOKIE[$coin_seo_name . '__wallet'];
    }

    return FALSE;
  }

  /**
   * Define pool fee
   * @param float $fee (percentage)
   * @return array
   */
  public static function getPoolFee($fee = 1.5) {
    return [
      'bitcore' => 1.25,
      'nist5' => 0.5,
      'phi' => 0.5,
      'x17' => 0.5,
      'xevan' => 1,
      'x16r' => 0.5
    ];
  }

  /**
   * Deduct pool fee from amount for specified algo
   * @param $amount
   * @param $algo
   * @return mixed
   */
  public static function takePoolFee($amount, $algo = 'bitcore') {
    $percent = self::getPoolFee()[$algo];
    return $amount - ($amount * $percent / 100.0);
  }

  /**
   * Add the earning for user for the specified block found
   * @param $db
   * @param $user
   * @param $block
   * @param $coin_id
   * @param $amount_earned
   */
  public static function addEarning($db, $user, $block, $amount_earned) {

    // Status -1 - immature
    // Status 0 - mature/confirmed, balance not updated
    // Status 1 - mature/confirmed, balance updated
    $stmt = $db->prepare("INSERT INTO earnings(userid, coinid, blockid, create_time, amount, price, status) 
VALUES(:userid, :coinid, :blockid, :create_time, :amount, :price, :status)");
    $stmt->execute([
      ':userid' => $user['id'],
      ':coinid' => $block['coin_id'],
      ':blockid' => $block['id'],
      ':create_time' => $block['time'],
      ':amount' => $amount_earned,
      ':price' => $block['amount'],
      ':status' => -1
    ]);

    return TRUE;
  }

  /**
   * Get the immature balance for specific user ID
   * @param $db
   * @param $user_id
   */
  public static function getImmatureBalance($db, $coin_id, $user_id) {
    $stmt = $db->prepare("SELECT userid, SUM(amount) AS immature_balance FROM earnings WHERE userid = :userid AND coinid = :coinid AND status = :status");
    $stmt->execute([
      ':userid' => $user_id,
      ':coinid' => $coin_id,
      ':status' => -1
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get the pending balance for specific user ID
   * Balance matured / waiting for payout
   * @param $db
   * @param $user_id
   */
  public static function getPendingBalance($db, $coin_id, $user_id) {
    $stmt = $db->prepare("SELECT id, balance AS pending_balance FROM accounts WHERE id = :userid AND coinid = :coinid");
    $stmt->execute([
      ':userid' => $user_id,
      ':coinid' => $coin_id,
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get total earnings for specific user ID
   * Cache for 1 minute
   * @param $db
   * @param $user_id
   */
  public static function getUserEarnings($db, $coin_id, $user_id, $step = 300, $redis = FALSE) {
    $interval = self::miner_hashrate_step($step);
    $delay = time()-$interval;

    $stmt = $db->prepare("SELECT userid, SUM(amount) AS total_earnings FROM earnings WHERE userid = :userid AND coinid = :coinid AND create_time > :delay");
    $stmt->execute([
      ':userid' => $user_id,
      ':coinid' => $coin_id,
      ':delay' => $delay
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Returns earnings breakdown for specific block
   * @param $db
   * @param $coin_id
   * @param $block_id
   * @param bool $redis
   */
  public static function getBlockEarnings($db, $coin_id, $block_id, $redis = FALSE) {

    // If we have redis connection try to load cached data first
    if (!empty($redis) && is_object($redis)) {
      $data = [];

      $block_earnings = json_decode($redis->get('block_earnings_' . $block_id), TRUE);

      // We have the data cached
      if (!empty($block_earnings)) {
        $data = $block_earnings;
        print '<!–- block_earnings ' . $block_id . ' - return from redis –>';
        return $data;
      }

    }

    // Mysql query
    $stmt = $db->prepare("SELECT ac.id, ac.username, e.amount AS amount_earned, b.height, b.amount, e.amount / b.amount * 100 AS round_share FROM earnings e INNER JOIN accounts ac ON ac.id = e.userid INNER JOIN blocks b ON e.blockid = b.id WHERE e.coinid = :coin_id AND e.blockid = :block_id ORDER BY e.amount DESC");
    $stmt->execute([
      ':coin_id' => $coin_id,
      ':block_id' => $block_id
    ]);

    if (!empty($redis) && is_object($redis)) {
      $data = [];
      // We didn't have cached value let's cache it now
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

      // Cache for 5 mins
      $redis->set('block_earnings_' . $block_id, json_encode($data), 600);
    }

    print '<!–- block_earnings ' . $block_id . ' - return from mysql –>';

    // Return with standard fallback
    return $data ?? $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Get specific block reward
   * @param $db
   * @param $coin_id
   * @param $block_id
   * @param bool $redis
   * @return mixed
   */
  public static function getBlockReward($db, $coin_id, $block_id, $redis = FALSE) {
    $stmt = $db->prepare("SELECT amount FROM blocks WHERE id = :block_id AND coin_id = :coin_id");
    $stmt->execute([
      ':coin_id' => $coin_id,
      ':block_id' => $block_id
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get total payout for the user
   */
  public static function getTotalPayout($db, $coin_id, $user_id, $redis = FALSE) {
    // @TODO -> cache this
    $stmt = $db->prepare("SELECT idcoin, SUM(amount) AS total_payout FROM payouts WHERE account_id = :user_id AND idcoin = :coin_id");
    $stmt->execute([
      ':user_id' => $user_id,
      ':coin_id' => $coin_id
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * List last 30 payouts for the user
   */
  public static function getPayouts($db, $coin_id, $user_id, $redis = FALSE) {
    // @TODO -> cache this
    $stmt = $db->prepare("SELECT * FROM payouts WHERE account_id = :user_id AND idcoin = :coin_id ORDER BY id DESC LIMIT 0, 30");
    $stmt->execute([
      ':user_id' => $user_id,
      ':coin_id' => $coin_id
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Prepare some route specific variables
   * @param $db
   * @param null $route
   * @param $data
   * @return array
   */
  public static function _templateVariables($db = FALSE, $route = null, $data = FALSE, $redis = FALSE) {

    if (!empty($db)) {
      $hashrates_30_min = minerHelper::getUserPoolHashrateStats($db, minerHelper::miner_getAlgos()[$data['coin_id']], 1800, $redis);
      $hashrates_3_hours = minerHelper::getUserPoolHashrateStats($db, minerHelper::miner_getAlgos()[$data['coin_id']], 60 * 60 * 3, $redis);
      $hashrates_24_hours = minerHelper::getUserPoolHashrateStats($db, minerHelper::miner_getAlgos()[$data['coin_id']], 60 * 60 * 24, $redis);

      if (!empty($data['miner_address'])) {

        // Load the user (@TODO avoid loading on all pages?)
        $user = self::getAccount($db, null, $data['miner_address']);

        // User specific hashrate
        if (!empty($hashrates_30_min[$user['id']])) {
          $hashrate_user_30_min = $hashrates_30_min[$user['id']];
        }

        // User specific hashrate
        if (!empty($hashrates_24_hours[$user['id']])) {
          $hashrate_user_24_hours = $hashrates_24_hours[$user['id']];
        }

      }
    }

    switch ($route) {
      case 'index':

        // General coin info
        $network_info_bitcore = self::getNetworkInfo(1425, $redis);
        $network_info_bulwark = self::getNetworkInfo(1426, $redis);
        $network_info_lux = self::getNetworkInfo(1427, $redis);
        $network_info_bitsend = self::getNetworkInfo(1429, $redis);
        $network_info_raven = self::getNetworkInfo(1430, $redis);

        $conn_btx = include(__DIR__ . '/../config-bitcore.php');
        $conn_bulwark = include(__DIR__ . '/../config-bulwark.php');
        $conn_lux = include(__DIR__ . '/../config-lux.php');
        $conn_verge = include(__DIR__ . '/../config-verge.php');
        $conn_bitsend = include(__DIR__ . '/../config-bitsend.php');
        $conn_raven = include(__DIR__ . '/../config-raven.php');

        $pool_hashrate_bitcore = minerHelper::getPoolHashrateStats($conn_btx, minerHelper::miner_getAlgos()[1425], 1800, $redis);
        $pool_hashrate_bulwark = minerHelper::getPoolHashrateStats($conn_bulwark, minerHelper::miner_getAlgos()[1426], 1800, $redis);
        $pool_hashrate_lux = minerHelper::getPoolHashrateStats($conn_lux, minerHelper::miner_getAlgos()[1427], 1800, $redis);
        $pool_hashrate_verge = minerHelper::getPoolHashrateStats($conn_verge, minerHelper::miner_getAlgos()[1428], 1800, $redis);
        $pool_hashrate_bitsend = minerHelper::getPoolHashrateStats($conn_bitsend, minerHelper::miner_getAlgos()[1429], 1800, $redis);
        $pool_hashrate_raven = minerHelper::getPoolHashrateStats($conn_raven, minerHelper::miner_getAlgos()[1430], 1800, $redis);

        $total_miners_bitcore = self::countMiners($conn_btx,1425)['total_count'] ?? 0;
        $total_miners_bulwark = self::countMiners($conn_bulwark,1426)['total_count'] ?? 0;
        $total_miners_lux = self::countMiners($conn_lux,1427)['total_count'] ?? 0;
        $total_miners_verge = self::countMiners($conn_verge,1428)['total_count'] ?? 0;
        $total_miners_bitsend = self::countMiners($conn_bitsend,1429)['total_count'] ?? 0;
        $total_miners_raven = self::countMiners($conn_raven,1430)['total_count'] ?? 0;

        return [
          'total_hashrate_bitcore_gh' => $network_info_bitcore['hashrate_gh'],
          'total_hashrate_bulwark_gh' => $network_info_bulwark['hashrate_gh'],
          'total_hashrate_lux_gh' => $network_info_lux['hashrate_gh'],
          'total_hashrate_bitsend_gh' => $network_info_bitsend['hashrate_gh'],
          'total_hashrate_raven_gh' => $network_info_raven['hashrate_gh'],
          'difficulty_bitcore' => $network_info_bitcore['difficulty'],
          'difficulty_bulwark' => $network_info_bulwark['difficulty'],
          'difficulty_lux' => $network_info_lux['difficulty'],
          'difficulty_bitsend' => $network_info_bitsend['difficulty'],
          'difficulty_raven' => $network_info_raven['difficulty'],
          'pool_hashrate_bitcore' => $pool_hashrate_bitcore['hashrate'],
          'pool_hashrate_bulwark' => $pool_hashrate_bulwark['hashrate'],
          'pool_hashrate_lux' => $pool_hashrate_lux['hashrate'],
          'pool_hashrate_verge' => $pool_hashrate_verge['hashrate'],
          'pool_hashrate_bitsend' => $pool_hashrate_bitsend['hashrate'],
          'pool_hashrate_raven' => $pool_hashrate_raven['hashrate'],
          'total_miners_bitcore' => $total_miners_bitcore,
          'total_miners_bulwark' => $total_miners_bulwark,
          'total_miners_lux' => $total_miners_lux,
          'total_miners_verge' => $total_miners_verge,
          'total_miners_bitsend' => $total_miners_bitsend,
          'total_miners_raven' => $total_miners_raven,
          'gpus' => json_encode(
            [
              'btx' => ['7.5' => 'GTX 1050ti', '13.0' => 'GTX 1060', '20.0' => 'GTX 1070', '31.0' => 'GTX 1080ti', '12.0' => 'RX 480', '12.5' => 'RX 580'],
              'bwk' => ['16.0' => 'GTX 1050ti', '32.0' => 'GTX 1060', '47.0' => 'GTX 1070', '80.0' => 'GTX 1080ti', '20.0' => 'RX 480', '22.0' => 'RX 580'],
              'lux' => ['6.0' => 'GTX 1050ti', '12.0' => 'GTX 1060', '19.0' => 'GTX 1070', '34.0' => 'GTX 1080ti', '15.0' => 'RX 480', '15.5' => 'RX 580'],
              'bsd' => ['1.2' => 'GTX 1050ti', '2.3' => 'GTX 1060', '3.2' => 'GTX 1070', '5.4' => 'GTX 1080ti'],
              'rvn' => ['4.2' => 'GTX 1050ti', '7.0' => 'GTX 1060', '8.5' => 'GTX 1070', '16.0' => 'GTX 1080ti', '5.0' => 'RX 480', '5.2' => 'RX 580'],
            ]
          ),
          'estimated_earnings_coins' => json_encode(minerHelper::getCoinEstimates($redis)),
        ];
        break;
      case 'dashboard':

        // If we have miner address
        if (!empty($data['miner_address'])) {

          $block_rewards = [];
          $block_rewards[1425] = 3.125;
          $block_rewards[1426] = 33.33333334;
          $block_rewards[1427] = 10;
          $block_rewards[1429] = 5;
          $block_rewards[1430] = 5000;

          // Get workers for miner address
          $workers = self::getWorkers($db, $data['miner_address']);
          foreach ($workers as $key => $worker) {
            $worker_hashrate = self::getHashrateStats($db, $data['coin_id'], $worker['version'], $worker['id'], $worker['name'],300, $redis);
            $worker_hashrate_15_mins = self::getHashrateStats($db, $data['coin_id'], $worker['version'], $worker['id'], $worker['name'], 900, $redis);
            $workers[$key]['worker'] = $worker['worker'];
            $workers[$key]['hashrate'] = self::Itoa2($worker_hashrate['hashrate']) . 'h/s';
            $workers[$key]['hashrate_15_mins'] = self::Itoa2($worker_hashrate_15_mins['hashrate']) . 'h/s';
          }

          // Estimated earnings
          $total_shares = self::sumTotalShares($db, self::miner_getAlgos()[$data['coin_id']]);
          $total_user_shares = self::sumTotalShares($db, self::miner_getAlgos()[$data['coin_id']], $user['id']);
          $user_round_share = $total_user_shares['total_user_hash'] / $total_shares['total_user_hash'] * 100;
          $user_estimated_earning = $block_rewards[$data['coin_id']] / 100 * $user_round_share;

          // Immature balance
          $immature_balance = self::getImmatureBalance($db, $data['coin_id'], $user['id']);
          $pending_balance = self::getPendingBalance($db, $data['coin_id'], $user['id']);

          // Earnings
          $earnings_last_hour = self::getUserEarnings($db, $data['coin_id'], $user['id'], 60 * 60 * 1);
          $earnings_last_3_hours = self::getUserEarnings($db, $data['coin_id'], $user['id'], 60 * 60 * 3);
          $earnings_last_24_hours = self::getUserEarnings($db, $data['coin_id'], $user['id'], 60 * 60 * 24);
          $earnings_last_7_days = self::getUserEarnings($db, $data['coin_id'], $user['id'], 60 * 60 * 24 * 7);
          $earnings_last_30_days = self::getUserEarnings($db, $data['coin_id'], $user['id'], 60 * 60 * 24 * 30);

          // Payouts
          $payouts = self::getPayouts($db, $data['coin_id'], $user['id']);
          $total_paid = self::getTotalPayout($db, $data['coin_id'], $user['id']);
          
          // Network info
          $network_info = self::getNetworkInfo($data['coin_id'], $redis);
        }

        return [
          'workers' => $workers ?? [],
          'workers_count' => !empty($workers) ? count($workers) : 0,
          'user' => $user ?? FALSE,
          'total_count_miners' => self::countMiners($db, $data['coin_id']) ?? FALSE,
          'total_count_workers' => self::countWorkers($db, $data['coin_id']) ?? FALSE,
          'min_payout' => self::miner_getMinPayouts()[self::miner_getAlgos()[$data['coin_id']]],
          'pool_fee' => self::getPoolFee()[self::miner_getAlgos()[$data['coin_id']]],
          'round_share' => $user_round_share,
          'estimated_earning' => $user_estimated_earning,
          'immature_balance' => $immature_balance ?? FALSE,
          'pending_balance' => $pending_balance ?? FALSE,
          'earnings_last_hour' => $earnings_last_hour ?? FALSE,
          'earnings_last_3_hours' => $earnings_last_3_hours ?? FALSE,
          'earnings_last_24_hours' => $earnings_last_24_hours ?? FALSE,
          'earnings_last_7_days' => $earnings_last_7_days ?? FALSE,
          'earnings_last_30_days' => $earnings_last_30_days ?? FALSE,
          'payouts' => $payouts ?? [],
          'total_paid' => $total_paid ?? FALSE,
          'hashrate_user_30_min' => $hashrate_user_30_min ?? FALSE,
          'hashrate_user_24_hours' => $hashrate_user_24_hours ?? FALSE,
          'coin_seo_name' => $data['coin_seo_name'],
          'stratum_connections' => self::countStratumConnections($db) ?? FALSE,
          'difficulty' => $network_info['difficulty'],
          'load_charts' => TRUE
        ];
        break;
      case 'miners':
        // Load all miners
        return [
          'miners' => minerHelper::getMiners($db, $data['coin_id']),
          'total_count_miners' => self::countMiners($db, $data['coin_id']) ?? FALSE,
          'total_count_workers' => self::countWorkers($db, $data['coin_id']) ?? FALSE,
          'hahsrates_30_min' => $hashrates_30_min,
          'hashrates_3_hours' => $hashrates_3_hours,
          'hashrate_user_30_min' => $hashrate_user_30_min ?? FALSE,
          'hashrate_user_24_hours' => $hashrate_user_24_hours ?? FALSE,
          'coin_seo_name' => $data['coin_seo_name'],
          'load_miner_charts' => TRUE
        ];
        break;
      case 'blocks':
        $blocks = minerHelper::getBlocks($db, $data['coin_id']);

        // Load last 30 blocks
        return [
          'blocks' => $blocks,
          'last_found' => self::lastFoundBlockTime($blocks[0]['time']),
          'hashrate_user_30_min' => $hashrate_user_30_min ?? FALSE,
          'hashrate_user_24_hours' => $hashrate_user_24_hours ?? FALSE,
          'coin_seo_name' => $data['coin_seo_name'],
          'load_blocks_charts' => TRUE
        ];
        break;
      case 'block-earnings':
        $block_id = $_GET['id'] ?? FALSE;
        $block_earnings = minerHelper::getBlockEarnings($db, $data['coin_id'], $block_id, $redis);
        $block_reward = minerHelper::getBlockReward($db, $data['coin_id'], $block_id);

        // Load last 30 blocks
        return [
          'block_earnings' => $block_earnings,
          'block_reward' => $block_reward,
          'block_reward_after_fee' => self::takePoolFee($block_reward['amount'], self::miner_getAlgos()[$data['coin_id']]),
          'pool_fee_amount' => $block_reward['amount'] - self::takePoolFee($block_reward['amount'], self::miner_getAlgos()[$data['coin_id']]),
          'pool_fee_percent' => self::getPoolFee()[self::miner_getAlgos()[$data['coin_id']]],
          'hashrate_user_30_min' => $hashrate_user_30_min ?? FALSE,
          'hashrate_user_24_hours' => $hashrate_user_24_hours ?? FALSE,
          'coin_seo_name' => $data['coin_seo_name'],
        ];
        break;
    }

    return [];
  }

}



