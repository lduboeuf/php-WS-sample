<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use ChatServer\Chat;

$PORT = 8080;

require dirname(__DIR__) . '/vendor/autoload.php';

$server = IoServer::factory(
  new HttpServer(
      new WsServer(
          new Chat()
      )
  ),
  $PORT
);

echo 'started WS server on port:' .$PORT."\n";
$server->run();
