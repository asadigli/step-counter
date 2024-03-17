<?php
defined('PATHACCESS') OR exit('No access');

ini_set('session.save_path',$_SERVER['DOCUMENT_ROOT'] . '/sessions');

class Session {

  private static $session_started = false;

  public static function set($key = NULL, $value = NULL) {
    if (!self::$session_started) {
      session_start();
      self::$session_started = true;
    }
    return $_SESSION[$key] = $value;
  }

  public static function get($key) {
    if (!self::$session_started) {
      session_start();
      self::$session_started = true;
    }
    return @$_SESSION[$key];
  }

}
