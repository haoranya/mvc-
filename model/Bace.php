<?php
    namespace model;
    
    class Bace {

    public static $pdo = null;

    function __construct(){

        if(self::$pdo===null){

        $db = config('db');

        self::$pdo = new \PDO("mysql:host={$db['host']};dbname={$db['dbname']}",$db['user'],$db['pwd']);

        self::$pdo->exec("set names utf8");
        
    }
 }
     //开启事务
     function startTran(){

        self::$pdo->exec("start transation");

     }
     //提交事务
     function commit(){

        self::$pdo->exec("commit");

     }

     //回滚事务

     function rollback(){

        self::$pdo->exec("rollback");

     }



}
?>