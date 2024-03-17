<?php
defined('PATHACCESS') OR exit('No access');

$routes["counters/add"]["POST"]             = "counters/add";
$routes["counters/all"]["GET"]              = "counters/all";
$routes["counters/delete"]["PUT"]           = "counters/delete";
$routes["counters/{id}/increment"]["POST"]  = "counters/incrementSteps/$1";
$routes["counters/{id}/delete"]["DELETE"]   = "counters/delete/$1";


$routes["teams/add"]["POST"]            = "teams/add";
$routes["teams/all"]["GET"]             = "teams/all";
$routes["teams/{id}/delete"]["DELETE"]  = "teams/delete/$1";
$routes["teams/{id}/counters"]["GET"]   = "teams/counters/$1";
$routes["teams/{id}/steps"]["GET"]      = "teams/steps/$1";
