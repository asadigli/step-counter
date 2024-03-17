<?php
defined('PATHACCESS') OR exit('No access');

class Validation {

  public static function validateArray($params = [],$keys = [], $deliminiter = "OR",$not_autorized = false){
    if(!$params) return false;
    $status = true;
    $data = [];
    foreach ($params as $index => $param) {
      foreach ($keys as $index_sub => $key) {
        if (!isset($params[$key]) || (!is_int($params[$key]) && !$params[$key])) {
          $data[] = $key;
          $status = false;
        }
      }
    }
    if (!$status) {
      Response::json(
        Response::rest(
          401,
          "Missed parameters",
          array_unique($data)
        )
      );
    }
  }


  public static function checkExist($reference_id = NULL, $group = NULL, $path = "dump") {
    if (!$reference_id || !$group) return null;
    if (!in_array($reference_id, array_map(function($item){
        return $item["id"];
    },$path === "dump" ? Dump::get($group) : Session::get($group)))) {
      Response::json(
        Response::rest(
          401,
          "Unknown $group [$reference_id]"
        )
      );
    }
  }

}
