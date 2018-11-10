<?php
   
   namespace model;

   use PDO;

   class Redbag extends bace {

        function create($id){

            $psm = self::$pdo->prepare("insert into redbags(user_id) values(?)");

            $psm->execute([$id]);

        }

   }



?>