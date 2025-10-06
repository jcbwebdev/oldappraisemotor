<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Created by PhpStorm.
     * User: peterbourne
     * Date: 02/12/2017
     * Time: 11:56
     */

    class Token
    {
        protected $_token;


        public function __construct()
        {

        }

        /**
         * Create token - and return
         *
         */
        public function createToken($length = 128)
        {
            $token = "";
            $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
            $codeAlphabet.= "0123456789";
            for($i=0;$i<$length;$i++)
            {
                $token .= $codeAlphabet[$this->crypto_rand_secure(0, strlen($codeAlphabet))];
            }

            return $token;
        }


        //Cryptography - Mechanism for creating UNIQUE ID for Payment references etc.
        private function crypto_rand_secure($min, $max)
        {
            $range = $max - $min;
            if ($range < 0) return $min; // not so random...
            $log = log($range, 2);
            $bytes = (int) ($log / 8) + 1; // length in bytes
            $bits = (int) $log + 1; // length in bits
            $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
            do {
                $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
                $rnd = $rnd & $filter; // discard irrelevant bits
            } while ($rnd >= $range);
            return $min + $rnd;
        }



        ###########################################################
        # Getters and Setters
        ###########################################################

        /**
         * @return mixed
         */
        public function getToken()
        {
            return $this->_token;
        }

        /**
         * @param mixed $token
         */
        public function setToken($token)
        {
            $this->_token = $token;
        }

    }