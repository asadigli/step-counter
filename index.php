<?php
define("PATHACCESS", true);

$current_url    = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$last           = explode("/", $current_url, 3);
$default        = "v1";
$all_versions   = ["v1"];
$version        = $last[1] && in_array($last[1],$all_versions) ? $last[1] : $default;
$route          = @$last[2] ?: "/";
$route_2        = @$last[3] ?: "/";
$request_method = $_SERVER['REQUEST_METHOD'];
$lib_to_load    = ["SessionLibrary","Input","Response","Validation","Dump"];

if ($_SERVER["REQUEST_URI"] == "/docs/counter") {
  include_once "docs/counter.php";
  exit();
}

include_once "modules/$version/routes/app.php";

include_once "modules/$version/dump/employees.php";

$GLOBALS["dump_data"] = $dump_data;
foreach ($lib_to_load as $item) {
  include_once "modules/$version/libraries/$item.php";
}


$argument = NULL;
if (!$routes_item = @$routes[$route]) {
  foreach (array_keys($routes) as $item) {
    $cleaned_route = preg_replace("/\{[^)]+\}/","",$item);
    if (similar_text($route,$item) === strlen($cleaned_route)) {
      $argument = str_replace(array_merge(explode("//",$cleaned_route),["/"]),"",$route);
      $routes_item = @$routes[$item];
    }
  }
}

if ($routes_item) {
  if (is_array($routes_item)) {
    if (!@$routes_item[$request_method]) {
      Response::json(
        Response::rest(
          404,
          "Requested URL not found"
        )
      );
    }
    $routes_item = $routes_item[$request_method];
  }

  $route_parts = explode("/",$routes_item);
  $object_name = @$route_parts[0];
  $object_name = ucfirst($object_name);
  $method_name  = @$route_parts[1] ?: "index";


  if (file_exists("modules/$version/controllers/".$object_name.".php")) {
    include_once "modules/$version/controllers/".$object_name.".php";
    $object = new $object_name;
    return $object->{$method_name}($argument);
  }

}

Response::json(
  Response::rest(
    404,
    "Requested URL not found"
  )
);
