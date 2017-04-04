<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use ChatServer\Blaster;

$PORT = 8080;
$MAX_X = 500;
$MAX_Y = 500;

require dirname(__DIR__) . '/vendor/autoload.php';


$blaster = new Blaster();

$server = IoServer::factory(
  new HttpServer( new WsServer($blaster)), $PORT
);

$server->loop->addPeriodicTimer(5, function () use ($blaster){
  $init = count($blaster->getBoxes());

  $temp_tbl = [];
  for ($i=0; $i < 5;$i++){
    $temp_tbl[$i] = array(
      "id"=> $init +$i,
      "x"=> rand(0, 500),
      "y"=> rand(0, 500));
  }
  $blaster->populateBoxes($temp_tbl);
});

echo 'started WS server on port:' .$PORT."\n";
$server->run();
