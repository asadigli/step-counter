<?php
defined('PATHACCESS') OR exit('No access');

class Response {

  public static function json($data) {
    if (!is_array($data)) {
      echo $data;
      exit();
    }
    header("Content-type:application/json");
    echo json_encode($data);
    exit();
  }

  public static function rest($code = NULL, $message = NULL, $data = NULL) {
    return [
      "code"    => $code,
      "message" => $message,
      "data"    => $data ?: []
    ];
  }

}
