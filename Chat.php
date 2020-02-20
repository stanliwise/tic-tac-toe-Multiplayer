<?php
namespace myApp;
    use Ratchet\MessageComponentInterface;
    use Ratchet\ConnectionInterface;

    class Chat implements MessageComponentInterface {
        protected $clients;
        protected $Registrar;
        protected $controller;

        public function __construct(){
            $this->clients = new \SplObjectStorage;
            $this->Registrar = \Registrar::getInstance();
            $this->controller = new \Game_controller($this->Registrar);
        }

        public function onOpen(ConnectionInterface $conn){
            $this->clients->attach($conn);
            $this->controller->register_player($conn);
            echo "New Connection! ({$conn->resourceId})\n";
        }

        /**
         * This the router functionality on the server
         */
        public function onMessage(ConnectionInterface $from, $message){
            $numRecv = count($this->clients) - 1;
            //call interpreter
            switch(true){
                case preg_match('/^b-m-\d+$/', $message) : $this->controller->board_move($message, $from); 
                break;
                case preg_match('/^b-create$/', $message) : $this->controller->create_new_board($from); 
                break;
                case preg_match('/^b-sit-\d+$/', $message) : $this->controller->sit_on_board($from, $message); 
                break;
                case preg_match('/add-to-board-\d+/', $message) : $this->controller->add_player_to_board($message, $from);
                break;
                case preg_match('/^exit-board$/', $message) : $this->controller->remove_player_from_board($from); 
                break;
                case preg_match('/b-resign/', $message) : $this->controller->resign($message, $from);
                break;
                case preg_match('/b-ready/', $message) : $this->controller->ready($from);
                break;
                case preg_match('/^b-unready$/', $message) : $this->controller->unready($from);
                break;
                case preg_match("/^b-unsit$/", $message) : $this->controller->unsit($from);
                break;
                default : $from->send('Invalid command');
            }

            /*echo sprintf('Connection %d sending message "%s" to %d other connection%s'. "\n", $from->resourceId, $message, $numRecv, $numRecv == 1 ? '': 's');
            foreach($this->clients as $client){
                if($from !== $client ){
                    $client->send($message);
                }
            }*/
        }

        public function onClose(ConnectionInterface $conn){
            $this->clients->detach($conn);
            $this->controller->remove_player($conn); //unregister player
            echo "Connection {$conn->resourceId} has disconnected\n";
        }

        public function onError(ConnectionInterface $conn, \Exception $e){
            echo "An error has occured: {$e->getMessage()}\n";
            $conn->close();
        }
    }