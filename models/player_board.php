<?php
    /**
     * This class is a simple map to maintain where a player is on the board
     * 
     * -players cannot be added to more than one board
     */
    class player_board{
        protected $list_of_boards = array();
        protected $list_of_registered_player = array();

        public function __construct()
        {
            
        }

        /**
         * Register a new board
         * 
         */
        public function add_board(int $board_id, int $player_id){
            if($this->get_player_board($player_id) != 0)
                return false;

            //if board has existed, return false
            if(isset($this->list_of_boards[$board_id]))
                return false;

            $this->list_of_board[$board_id] = array();

            //add player to board
            $this->add_player_to_board($player_id, $board_id);

            return true;
        }

        public function remove_board($board_id){
            if(!isset($this->list_of_boards[$board_id]))
                return false;

            $list_of_players = $this->list_of_boards[$board_id];

            //unregister all the players
            foreach($list_of_players as $player_id => $value)
                $this->remove_player($player_id);

            //delete board
            unset($this->list_of_boards[$board_id]);

            return true;
        }

        /**
         * Remove a player from a particular board
         */
        public function remove_player(int $player_id){
            if(!isset($this->list_of_registered_player[$player_id]))
                return false;

            $player_board = $this->list_of_registered_player[$player_id];

            //remove player from board
            unset($this->list_of_boards[$player_board][$player_id]);
            
            //unregister
            unset($this->list_of_registered_player[$player_id]);
        }

        public function add_player_to_board(int $player_id, int $board_id){
            //check player list to ensure player hasn't register else return false
            if(isset($this->list_of_registered_player[$player_id]))
                return false;

            //add player to board
            $this->list_of_boards[$board_id][$player_id] = 1;

            //register player with the value of board
            $this->list_of_registered_player[$player_id] = $board_id;
        }

        /**
         * Help us check if a player is on board already
         * 
         * @return int Return 0 if player is not on board
         */
        public function get_player_board(int $player_id){
            if(!isset($this->list_of_registered_player[$player_id]))
                return 0;

            return $this->list_of_registered_player[$player_id];
        }


        public function get_players_id(){
            return $this->list_of_registered_player;
        }
    }