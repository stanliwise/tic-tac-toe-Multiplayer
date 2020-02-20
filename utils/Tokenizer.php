<?php
    class Tokenizer{
        public static function tokenizeInt(string $sentence, string $divider = "-"){
            preg_match_all("/\d+/", $sentence, $tokens);

            //make all output int
            foreach($tokens[0] as $key => $token){
                $tokens[$key] = (int) $token;
            }

            return $tokens;
        }
    }