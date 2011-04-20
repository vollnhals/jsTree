<?php

require_once("lib/WebSocket/server.php");
require_once("jstree_application.php");

$server = new \WebSocket\Server('localhost', 8000);
$server->registerApplication('jstree', JsTreeApplication::getInstance());
$server->run();

?>
