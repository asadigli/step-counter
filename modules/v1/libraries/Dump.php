<?php
defined('PATHACCESS') OR exit('No access');

class Dump {

  private static $dump_data = NULL;

  public static function get($key = NULL) {
    if (!self::$dump_data) {
      self::$dump_data = $GLOBALS["dump_data"];
    }
    return @self::$dump_data[$key];
  }

}
