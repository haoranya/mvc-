<?php   
    namespace libs;

    class redis{

        private static $redis = null;

        private function __construct(){}

        private function __clone(){}

       public static function getredis(){

        if(self::$redis===null){

            $config = config('redis');

            self::$redis=new \Predis\Client($config);

        }
        return self::$redis;

        }



    }








?>