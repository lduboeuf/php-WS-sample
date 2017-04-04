<?php
namespace ChatServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Blaster  implements MessageComponentInterface {
    protected $clients;
    private $boxes = [];


    public function __construct() {
        $this->clients = new \SplObjectStorage;
        /*
        for ($i=0; $i < 20;$i++){
          $this->boxes[$i] = array(
            "id"=> $i,
            "x"=> rand(0, $this->MAX_X),
            "y"=> rand(0, $this->MAX_Y));
        }
        */
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        //
        if (count($this->boxes)>0){
          $conn->send(json_encode(array("init"=>$this->boxes)));
        }
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        $this->datas = $msg;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        $data = json_decode($msg);
        $this->deleteBox($data->killed);

        foreach ($this->clients as $client) {
                // The sender is not the receiver, send to each client connected
                $client->send($msg);

        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    public function getBoxes(){
      return $this->boxes;
    }

    private function deleteBox($boxId){
      for ($i=0; $i < count($this->boxes); $i++) {
        if ($this->boxes[$i]["id"]==$boxId){
          unset($this->boxes[$i]);
        }
      }
    }

    public function populateBoxes($data){
      if (count($this->clients)>0){
        $this->boxes = array_merge($this->boxes, $data);
        $msg = json_encode(array("update"=>$data));

        foreach ($this->clients as $client) {
                $client->send($msg);
        }
        echo ("send update to clients: " + $msg);
      }

    }

/*
    public function run(){
      //add 5 boxes every 5 sec
      $init = count($this->boxes);
      $temp_tbl = [];
      for ($i=0; $i < 5;$i++){
        $this->$temp_tbl[$i] = array(
          "id"=> $init +$i,
          "x"=> rand(0, $this->MAX_X),
          "y"=> rand(0, $this->MAX_Y));
      }
      //add to current array
      array_push($this->boxes, $temp_tbl);
      //notify clients on update
      notifyAll(json_encode(array("update"=>$this->$temp_tbl)));
      sleep(5000);
    }
    */
}
