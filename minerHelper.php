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


}



