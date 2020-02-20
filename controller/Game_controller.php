<?php

use Ratchet\RFC6455\Messaging\Message;

require dirname(__DIR__) . '/utils/Tokenizer.php';

    class Game_controller{
        protected $model;

        public function __construct(\Registrar $model)
        {
            $this->model = $model;    
        }

        //remaining what to respond to server later
        public function sit_on_board($conn, string $message){
            $tokens = Tokenizer::tokenizeInt($message);
            $id = $tokens[0];
            $board = $this->model->get_player_board($conn->resourceId);
            if(!$board)
                return;

            $board->sit($conn->resourceId, $id);
        }

        public function add_player_to_board(string $message, $conn){
            //send a sucessful message of the board id of the board to query.
            $tokens = Tokenizer::tokenizeInt($message);
            $board_id = $tokens[0];
            $this->model->add_player_to_board($conn->resourceId, $board_id);
        }

        public function create_new_board($conn){
            $this->model->create_board($conn->resourceId);

            //update viewers
            $this->announce2ListViewers();
        }

        public function resign($conn){
            $board = $this->model->get_player_board($conn->resourceId);
            if(!$board)
                return;

            $board->resign($conn->resourceId);
        }

        public function remove_player_from_board($conn){
            $this->model->remove_player_from_board($conn->resourceId);

            //make anouncement
            $this->announce2ListViewers();
        }

        public function register_player($conn){
            $register = $this->model->register_player($conn);

            if($register)
                $this->announce2ListViewers();
        }

        public function name_of_online_players(){
            $names = [];
            $players = $this->model->get_all_players();

            foreach($players as $player)
                $names[$player->getID()] = $player->getname();

            return $names;
        }

        public function remove_player($conn){
            //remove player from manager
            $this->model->remove_player($conn->resourceId);

            $this->announce2ListViewers();
        }

        public function remove_board(int $board_id){
            //get list of board players
            
            //destory board
            $this->model->remove_board($board_id);

            //send destroy message to all player
        }

        public function board_move(string $message, $conn){
            //break-move
            $tokens = Tokenizer::tokenizeInt($message);

            //first int is the move
            $move = $tokens[0];

            //get player board
            $players_board = $this->model->get_player_board($conn->resourceId);

            if(!$players_board)
                return;

            $players_board->make_move($move, $conn->resourceId);
        }

        public function unsit($conn){
            //get player board
            $players_board = $this->model->get_player_board($conn->resourceId);

            if(!$players_board)
                return;

            $players_board->unsit($conn->resourceId);
        }

        public function ready($conn){
            //get player board
            $players_board = $this->model->get_player_board($conn->resourceId);

            if(!$players_board)
                return;

            $players_board->ready($conn->resourceId);
        }

        public function unready($conn){
            //get player board
            $players_board = $this->model->get_player_board($conn->resourceId);

            if(!$players_board)
                return;

            $players_board->unready($conn->resourceId);
        }

        public function announce2ListViewers($except = [], $log = ''){
            $message = [
                'names' => $this->name_of_online_players(),
                'avaliable_board' => $this->model->get_board_list(),
                'log' => $log
            ];

            $players = $this->model->get_players_not_on_board();

            foreach($players as $player){
                if(!in_array($player->getID(), $except))
                    $player->message(json_encode($message));
            }
        }

    }