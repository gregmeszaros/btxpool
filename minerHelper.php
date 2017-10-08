<?php
/**
 * Created by PhpStorm.
 * User: gergelymeszaros
 * Date: 29/09/2017
 * Time: 15:01
 */

class minerHelper {

  public static function getRoutes() {
    $base_route = 'index.php?page=';

    return [
      'index' => [
        'id' => 'home',
        'label' => 'Home',
        'url' => $base_route . 'index',
        'template' => 'index.html.twig',
        'load_charts' => TRUE
      ],
      'miners' => [
        'id' => 'users',
        'label' => 'Miners',
        'url' => $base_route . 'miners',
        'template' => 'miners.html.twig',
      ],
      'explorer' => [
        'id' => 'search',
        'label' => 'Blockchain',
        'url' => 'https://chainz.cryptoid.info/btx/',
        'target' => '_blank',
        'template' => 'miners.html.twig',
      ],
    ];
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
      $s = round(floatval($i), $precision);

    return $s;
  }

  public static function miner_hashrate_constant($algo=null) {
    return pow(2, 42);		// 0x400 00000000
  }

  public static function miner_hashrate_step() {
    return 300;
  }

  /**
   * Return available coins
   * @return array
   */
  public static function miner_getAlgos() {
    // return key / value pairs (coin ID, algo)
    return [
      '1425' => 'bitcore'
    ];
  }

  /**
   * Return user data for specified ID
   * @param $db
   * @param $account_id
   */
  public static function getAccount($db, $account_id = null) {
    if (!empty($account_id)) {
      $stmt = $db->query("SELECT * FROM accounts where id = :account_id");
      $stmt->execute([
        ':account_id' => $account_id
      ]);
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }
  }

  /**
   * Returns active workers for the miner address
   * @param Database connection
   * @param $miner_address
   * @return array
   */
  public static function getWorkers($db, $miner_address = "") {
    $stmt = $db->query("SELECT * FROM workers");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function getHashrate($db, $algo, $version, $miner_address) {
    $target = self::miner_hashrate_constant($algo);
    $interval = self::miner_hashrate_step();
    $delay = time()-$interval;

    $stmt = $db->prepare("SELECT sum(difficulty) * :target / :interval / 1000 AS hashrate FROM shares WHERE valid AND time > :delay
AND workerid IN (SELECT id FROM workers WHERE algo=:algo and version=:version)");
    $stmt->execute([
      ':target' => $target,
      ':interval' => $interval,
      ':delay' => $delay,
      ':algo' => $algo,
      ':version' => $version
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);

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
   * Define pool fee
   * @param float $fee (percentage)
   * @return float
   */
  public static function getPoolFee($fee = 1.5) {
    return [
      'bitcore' => 1.5
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

}



