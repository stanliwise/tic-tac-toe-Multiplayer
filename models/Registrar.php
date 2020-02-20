<?php
    class Registrar{
        protected $boards;
        /**
         * @var array list of registered players
         */
        protected $players;

        /**
         * @var player_board This keeps a map of player and boards for easy management
         */
        protected $player_board_map;
        protected static $instance = null;

        private function __construct(){
            $this->boards = array();
            $this->players = array();
            $this->player_board_map = new \player_board;
        }

        public static function getInstance(){
            if(!self::$instance)
                $instance = new self;

            return $instance;
        }

        public static function init(){
            if(!self::$instance)
                self::$instance = new Registrar();

            return self::$instance;
        }

        /**
         * Create a new board
         * 
         * @param int $players_id The id of the registered player
         * 
         * @return bool
         */
        public function create_board(int $player_id){
            //player must be registered
            if(!isset($this->players[$player_id]))
                return false;

            //player must not be on any board
            if($this->player_board_map->get_player_board($player_id) != 0)
                return false;

            //create a new board
            $board = new Board(
                $this->players[$player_id]
            );

            $board_id = $board->getID();

            $this->player_board_map->add_board($board_id, $player_id);
            
            $this->boards[$board_id] = $board; //create board reference

            return true;
        }

        /**
         * Remove a board reference
         * 
         * @param int $boardId The id of the board
         * 
         * @return bool
         */
        public function remove_board(int $boardId){
            if(!isset($this->board[$boardId]))
                return false;

            $this->player_board_map->remove_board($boardId);
            unset($this->board[$boardId]);
            return true;
        }

        /**
         * check if a board exist
         * 
         * @param int $boardId The board id
         * 
         * @return bool
         */
        public function board_exist($boardId){
            if(isset($this->boards[$boardId]))
                return true;

            return false;
        }

        /**
         * Get a list of current board_id
         * 
         * @return array
         */
        public function get_board_list(){
            $boards_id = [];
            foreach($this->boards as $board)
                $boards_id[] = $board->getID();

            return $boards_id;
        }

        public function get_board(int $board_id){
            if(isset($this->boards[$board_id]))
                return $this->boards[$board_id];

            return false;
        }

        public function get_all_players(){
            return $this->players;
        }

        public function register_player($connection){
            $players_id = $connection->resourceId;
            if(!isset($this->players[$players_id]))
                $this->players[$players_id] = new Player($connection);

            return true;
        }

        public function get_players_not_on_board(){
            $list_of_players = [];

            //get all player on board id
            $list_of_players = array_diff_key($this->players, $this->player_board_map->get_players_id());

            //get the real players
            foreach($list_of_players as $player_id){
                $list_of_players[$player_id->getID()] = $this->players[$player_id->getID()];
            }

            //return list of players
            return $list_of_players;
        }
        
        public function add_player_to_board(int $players_id, int $boardId){
            //board must have been created initially;
            if(!$this->board_exist($boardId))
                return false;

            //player must have been added to Registrar
            if(!isset($this->players[$players_id]))
                return false;

            //don't add player to mulitple boards
            if($this->player_board_map->get_player_board($players_id) != 0)
                return false;

            //addplayer to board
            if($this->boards[$boardId]->add_player($this->players[$players_id])){
                //register player map
                $this->player_board_map->add_player_to_board($players_id, $boardId);
            }

            return true;
        }
        
        /**
         * Find the board a particular player is in, 0 means he is not in any board
         * 
         * @param int $playerId The id of the player to be found
         * 
         * @return int
         */
        public function get_player_board_id(int $playerId){
            return $this->player_board_map->get_player_board($playerId);
        }

        public function get_player_board(int $player_id){
            $player_board_id = $this->get_player_board_id($player_id);
            if($player_board_id == 0)
                return false;

            return $this->boards[$player_board_id];
        }
        
        public function remove_player(int $players_id){
            if(!isset($this->players[$players_id]))
                return false;

            //remove from board if necessary
            $this->remove_player_from_board($players_id);
            
            //delete all reference
            unset($this->players[$players_id]);

            //remove from map
            $this->player_board_map->remove_player($players_id);
            return true;
        }

        public function remove_player_from_board(int $players_id){
            //get players board using the map
            $player_board_id = $this->player_board_map->get_player_board($players_id);

            if($player_board_id == 0)
                return false;
            //get board
            $board = $this->boards[$player_board_id];
            
            //remove player
            $board->remove_player($players_id);

            //remove from map
            $this->player_board_map->remove_player($players_id);

             //delete board if there are no players
            if($board->no_of_players() == 0){
                unset($this->boards[$player_board_id]);

                //remove from map too
                $this->player_board_map->remove_board($player_board_id);
            }

            return true;
        }

        public function get_user(int $players_id){
            if(!isset($this->players[$players_id]))
                return false;

            return $this->players[$players_id];
        }


    }
?>