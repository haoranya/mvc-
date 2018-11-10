<?php
    namespace model;

    use PDO;

    // select user.face user.email from conments left join user on user.id = comments.user_id where comments.blog_id = 337

    //select count(comments) from user left join on user.id = comments.user_id 
    class Conment extends bace{

        function insert_conment($content,$blog_id){

            $psm = self::$pdo->prepare("insert into conments(blog_id,content,user_id) values(?,?,?)");

            $data = $psm->execute([

                $blog_id,
                $content,
                $_SESSION['id'],

            ]);

            if($data){

                return true;

            }else{

                return false;

            }

        }

        function get_conments($blog_id){

            $psm = self::$pdo->prepare("select * from conments c left join user u on c.user_id = u.id where c.blog_id = ? ");

            $psm->execute([$blog_id]);

            $data = $psm->fetchAll(PDO::FETCH_ASSOC);

            return $data;

        }


    }


?>

