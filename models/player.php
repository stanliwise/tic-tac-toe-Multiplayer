<?php 
 class Player{
     protected $conn;
     protected $id;
     protected $name;
     protected $rating;
     protected $login_time;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->id = $this->conn->resourceId;
        $this->name = self::generate_name();
    }
     
    public function getname(){
        return $this->name;
    }

    public function getID(){
        return $this->id;
    }

    private static function generate_name(){
        //generate random id; 
        $refId = '';
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $characters .= 'ABCDEFGHIJKLMNOPWRSTUVWXYZ'; 
        $characters .= '0123456789';
        
        for($i = 0; $i < 8; $i++){
            $randomNum = rand(0,61);
            $refId .= $characters[$randomNum];
        }
        
        return $refId;
    }

    public function message($message){
        $this->conn->send($message);
    }
 }