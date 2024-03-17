<?php
spl_autoload_register('autoloader');
function autoloader(string $name) {

    if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/modules/v1/controllers/".$name.'.php')){
        require_once $_SERVER["DOCUMENT_ROOT"] . "/modules/v1/controllers/".$name.'.php';
    }
}
require($_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php");

$openapi = \OpenApi\Generator::scan([$_SERVER["DOCUMENT_ROOT"] . "/modules/v1/controllers"]);

header('Content-Type: application/json');

echo $openapi->toJSON();
