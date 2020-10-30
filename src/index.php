<?php

require_once 'vendor/autoload.php';

$query = "eventName = purchase AND eventData.category.digital = phone OR eventData.price < 600000 AND eventData.detail.cpu.detail.type = 64bit";
$query1 = "eventName = purchase";
$map = new \App\Mapping();
$parser = new \App\QueryParser($map, $query);
echo $parser->parse()->getKQL();

$parser = new \App\QueryParser($map, $query1);
echo "\n". $parser->parse()->getKQL();