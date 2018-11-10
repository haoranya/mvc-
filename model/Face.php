<?php
    namespace model;

    use PDO;

    class Face extends Bace{


        function face(){

            


        }

        function set_face($dir){

            $psm = self::$pdo->prepare("update user set face = ? where id = ?");

            $data = $psm->execute([

                $dir,
                $_SESSION['id'],

            ]);

            if($data){

                $_SESSION['face']=$dir;

                // success("上传成功",1,"/blog/blog");

            }


        }

    }



?>