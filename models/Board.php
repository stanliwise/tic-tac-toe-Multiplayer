<?php
    class Board{
        private static $numbers = 1;

        protected const JUST_STARTED = 4;
        protected const DRAW = 0;
        protected const ACTIVE = 3;
        protected const X_WIN = 1;
        protected const O_WIN = 2;

        protected $id;
        protected $state;
        protected $players;
        protected $player_x;
        protected $player_o;
        protected $player_x_ready;
        protected $player_o_ready;
        protected $player_x_position;
        protected $player_o_position;
        protected $created;
        protected $creator;
        protected $turn;
        /**
         * @var array $moves Hold the moves made and the sequence of how it happen.
         */
        protected $moves;
        protected $format;

        public function __construct(Player $player)
        {
            //intialize all board values
           $this->id = self::$numbers;
           self::$numbers += 1;
           $this->player_o_position = 0;
           $this->player_x_position = 0;
           $this->player_x = $player;
           $this->player_x_ready = 0;
           $this->player_o_ready = 0;
           $this->players = [];    
           $this->created = date('Y-m-d H:i:s');
           $this->creator = $player->getname();
           $this->state = self::JUST_STARTED;
           $this->turn = $this->player_x->getID();
           $this->add_player($player);
           $this->sit($player->getID(), 1);
        }

        public function get_players(){
            return $this->players;
        }

        public function reset_game(){
            //swap players
            $temp = $this->player_x;
            $this->player_x = $this->player_o;
            $this->player_o = $temp;

            //reset ready state
            $this->player_x_ready = 0;
            $this->player_o_ready = 0;

            $this->state = self::JUST_STARTED;

            $this->turn = 0;

            $this->send_board_state();
        }

        public function start_game(){
            //can't reset an active game
            if($this->state == self::ACTIVE)
                return;

            //two players must be sitted
            if(!isset($this->player_o) || !isset($this->player_x))
                return;
            
            $this->player_o_position = 0;
            $this->player_x_position = 0;

            $this->state = self::ACTIVE;

            //turn
            $this->turn = $this->player_x->getID();
            
            $this->send_board_state('Game started');
        }

        public function remove_player(int $player_id){
            //if player is playing, no
            if(($this->state == self::ACTIVE) && (($player_id == $this->player_x->getID()) || ($player_id == $this->player_o->getID())))
                $this->resign($player_id);

            //check if player exist
            if(!isset($this->players[$player_id]))
                return false;

            //get player name
            $name = $this->players[$player_id]->getname();

            unset($this->players[$player_id]);
            
            if(isset($this->player_o) && ($player_id == $this->player_o->getID()))
                unset($this->player_o);
            elseif(isset($this->player_x) && ($player_id == $this->player_x->getID()))
                unset($this->player_x);

            //announce to all players
            $this->send_board_state($name . ' left the board');
        }

        public function add_player(Player $player){
            $this->players[$player->getId()] = $player;

            //anounce to all players
            $this->send_board_state($player->getname() . ' entered the board');
            return true;
        }

        public function has_player(int $player_id){
            return (isset($this->players[$player_id]));
        }

        public function getState(){
            return $this->state;
        }

        public function sit(int $player_id, $id){
            //check if player is on board
            if(!$this->has_player($player_id))
                return false;

            $player = $this->players[$player_id];

            if(((isset($this->player_x) && ($player == $this->player_x)) || (isset($this->player_o) && ($player == $this->player_o))) )
                return false;

            if(!isset($this->player_x) && ($id == 1)){
                $this->player_x = $player;
                $this->send_board_state();
                return true;
            }elseif(!isset($this->player_o) && ($id == 0)){
                $this->player_o = $player;
                $this->send_board_state();
                return true;
            }

            return false;
        }

        public function unsit(int $player_id){
            if($this->state == self::ACTIVE)
                return false;

            //unsit from x
            if($player_id == $this->player_x->getId()){
                unset($this->player_x);
                $this->send_board_state();
            }
            elseif($player_id == $this->player_o->getId()){ //unsit from o
                unset($this->player_o);
                $this->send_board_state();
            }
            else
                return false;

            return true;
        }

        public function ready(int $player_id){
            if($this->state == self::ACTIVE)
                return;

            if(!isset($this->player_x) || !isset($this->player_o))
                return;
            
            if($this->player_x->getID() == $player_id)
                $this->player_x_ready = 1;
            elseif($this->player_o->getID() == $player_id)
                $this->player_o_ready = 1;

            //annonuce to all players
            $this->send_board_state($this->players[$player_id]->getname() . ' is ready');

            if($this->player_x_ready && $this->player_o_ready){
                $this->start_game();
            }
        }

        public function unready(int $player_id){
            if($this->state == self::ACTIVE)
                return;
            
            if($this->player_x->getID() == $player_id)
                $this->player_x_ready = 0;
            elseif($this->player_o->getID() == $player_id)
                $this->player_o_ready = 0;
            else
                return;
            
            //announce to all players
            $this->send_board_state($this->players[$player_id]->getname() . ' is not ready');
        }

        //check board if a player has won
        public function game_won(){
            //winning spot
            $win =  [7, 56, 448, 73, 146, 292, 273, 84];
            foreach($win as $value){
                if(($value & $this->player_x_position) == $value){
                    return 1;
                }
                elseif(($value & $this->player_o_position) == $value){
                    return 2;
                }
            }

            return 0;
        }

        public function getID(){
            return $this->id;
        }

        //check if board is full and no winner(draw)
        public function game_drawn(){
            $draw = 511;
            $game = $this->player_o_position + $this->player_x_position;
            if($game == $draw)
                return true;
            
            return false;
        }

        protected function can_play(){
            //two players must be sitted
            if($this->state != self::ACTIVE)
                return false;
                
            //check if game isn't drawn
            if($this->game_drawn()){
                $this->state = self::DRAW;
                $this->reset_game(); //reset game
                $this->send_board_state('Draw');
                return false;
            }

            //check if game has not been won
            if($this->game_won()){
                if($this->game_won() == self::X_WIN){
                    $this->state = self::X_WIN;
                }else{
                    $this->state = self::O_WIN;
                }
                ($this->game_won() == self::X_WIN) ? $this->send_board_state($this->player_x->getname() .' won the game') : $this->send_board_state($this->player_o->getname() . ' won the game');

                //reset game
                $this->reset_game();
                return false;
            }

            return true;
        }

        public function make_move(int $move, int $turn){
            if($turn != $this->turn )
                return;

            if(!$this->can_play())
                return;
            
            //check if move is valid
            if($this->validate_move($move)){
                if($this->turn == $this->player_x->getID()){
                    $this->player_x_position += $move;
                }elseif($this->turn == $this->player_o->getID()){
                    $this->player_o_position += $move;
                }else{
                    return;
                }

                //switch turn
                $this->switch_turn();

                //update board state
                $this->send_board_state();
            }

            //call can play to update game state for new task
            $this->can_play();
        }

        protected function validate_move(int $move){
            $valid_moves = [1, 2, 4, 8 , 16, 32, 64, 128, 256];
            $current_board = $this->player_x_position + $this->player_o_position;
            if(in_array($move, $valid_moves)){
                if(($current_board & $move) == $move ){
                    return false;
                }

                return true;
            }

            return false;
        }

        /**
         * The amount of player on the board
         * 
         * @return int
         */
        public function no_of_players(){
            return count($this->players);
        }

        protected function switch_turn(){
            //we can't switch turns if game haven't started
            if($this->state != self::ACTIVE)
                return;
            
            //switch turns
            $this->turn = ($this->turn == $this->player_x->getID()) ? $this->player_o->getID() : $this->player_x->getID();
        }

        protected function resign(int $player_id){
            if($this->state !== self::ACTIVE)
                return;

            if($this->player_x->getID() == $player_id){
                $this->state = self::O_WIN;
                $this->reset_game();
                $this->send_board_state($this->player_x->getname() . ' resigned');
                $this->send_board_state($this->player_o->getname() . ' wins');
            }elseif($this->player_o->getID() == $player_id){
                $this->state = self::X_WIN;
                $this->send_board_state($this->player_o->getname() . ' resigned');
                $this->send_board_state($this->player_x->getname() . ' wins');
                $this->reset_game();
            }
            else
                return;
        }

        protected function send_board_state(string $log = ''){
            $message = [
                'players' => [
                    'x' => [
                        'id' => '',
                        'name'=> '',
                    ],

                    'o' => [
                        'id' => '',
                        'name' => '',
                    ]
                ],

                'board' => [
                    'info' => [], //would contain info about the board
                    'x' => 0,
                    'o' => 0,
                    'state' => 0,
                    'turn' => $this->turn
                ],

                'viewers' => [],

                'can_exit' => 0,

                'can_sit' => [
                    'x' => 0,
                    'o' => 0
                ],

                'can_play' => 0,

                'ready' => [
                    'x' => 0,
                    'o' => 0
                ],

                'log' => $log
            ];

            //set general variable
            if(isset($this->player_x)){
                $message['players']['x']['id'] = $this->player_x->getID();
                $message['players']['x']['name'] = $this->player_x->getname();
            }

            if(isset($this->player_o)){
                $message['players']['o']['id'] = $this->player_o->getID();
                $message['players']['o']['name'] = $this->player_o->getname();
            }

            //board position
            $message['board']['x'] = $this->player_x_position;
            $message['board']['o'] = $this->player_o_position;

            //state
            $message['board']['state'] = $this->state;

            //name of viewers
            foreach($this->players as $player){
                if( ((isset($this->player_x) && ($player != $this->player_x)) || ((isset($this->player_o) && ($player != $this->player_o)))) )
                    $message['viewers'][] =  $player->getname();

                $message['can_exit'] =  (($this->state == self::ACTIVE) && (($player->getID() == $this->player_x->getID()) || ($player->getID() == $this->player_o->getID()))) ? 0 : 1;

                $message['can_sit']['x'] = isset($this->player_x) ? 0 : 1;
                $message['can_sit']['o'] = isset($this->player_o) ? 0 : 1;

                $message['can_ready']['x'] = ( ($this->player_x_ready == 0) && ((isset($this->player_x) && ($this->player_x == $player))) && (isset($this->player_o)) ) ? 1 : 0;
                $message['can_ready']['o'] = ( ($this->player_o_ready == 0) && ((isset($this->player_o) && ($this->player_o == $player)))  && (isset($this->player_x))) ? 1 : 0;

                //can play - first check if game is active and if it is player turn
                $message['can_play'] = (($this->state == self::ACTIVE) && ($this->turn == $player->getID())) ? 1 : 0;

                $value = (string) json_encode($message);
                $player->message($value);
            }

        }
    }