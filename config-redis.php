<?php

if (class_exists('Redis')) {
  try {
    $redis = new Redis();
    $connected = $redis->connect('127.0.0.1', 6379);
    if (!$connected) {
      return "Can't connect to REDIS";
    }
  } catch (Exception $e) {
    return $e->getMessage();
  }
}

return FALSE;

