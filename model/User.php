<?php 

    namespace model;

    use pdo;

    class User extends Bace{

        public function getname(){

            return "tom";

        }

        function dologin(){

           $email = $_POST['email'];

           $pwd = md5($_POST['pwd']);

           $psm = self::$pdo->prepare("select * from user where email = ? and pwd = ?");
        
           $psm->execute([$email,$pwd]);

           $user = $psm->fetch();

           if($user){

            $_SESSION['id']=$user['id'];

            $_SESSION['email']=$user['email'];

            $_SESSION['money']=$user['money'];

            $_SESSION['face']=$user['face'];

            //链接redis

            $redis = \libs\redis::getredis();

            //设置key

            $key = "user_money:".$user['id'];

            $value = $user['money'];

            $redis->set($key,$value);

           success("登陆成功",1,'/blog/index');

           }else{

            echo "不存在";

           }
        }

        function logout(){

           unset($_SESSION['id']);

           unset($_SESSION['email']);

           success('退出成功',2,'/user/login');
        }

        function setmoney($money,$id){

            $stmt = self::$pdo->prepare("UPDATE user SET money=money+? WHERE id=?");
            $data = $stmt->execute([
                $money,
                $id
            ]);

            //链接redis
            $redis = \libs\redis::getredis();
            //拼接key
            $key = "user_money:".$_SESSION['id'];
            //更新
     
            $redis->set($key,$money);

            return $data;

        }

        function getmoney(){

              //链接redis
              $redis = \libs\redis::getredis();
              //拼接key
              $key = "user_money:".$_SESSION['id'];

             //从redis里面获取value
             $money = $redis->get($key);

             if(!$money){
 
                $psm = self::$pdo->prepare("select money from user where id = ?");

                $psm->execute([$_SESSION['id']]);
 
                $money = $psm->fetch(PDO::FETCH_COLUMN);

                if($money){

                    return $money;

                    $redis->set($key,$money);

                }
             }else{

                return $money;

             }

        }

        //测试事务

        function event(){

            //开启事务

            self::$pdo->exec("start transaction");

            //执行语句


            //判断事务是否提交还是回滚


        }

        function update_money(){

            $id = $_SESSION['id'];

            $psm = self::$pdo->prepare("select money from user where id = ?");

            $psm->execute([$_SESSION['id']]);

            $money = $psm->fetch(PDO::FETCH_COLUMN);

            echo $money;

        }

        //活跃用户的算法

        function active_user(){

            //计算日志分值

            $psm = self::$pdo->query("select user_id , count(*)*5 as fz from blog where created_at > DATE_SUB(CURDATE(), INTERVAL 1 WEEK) group by user_id");

            $data1 = $psm->fetchAll(PDO::FETCH_ASSOC);

             //计算评论分值

             $psm = self::$pdo->query("select user_id , count(*)*3 as fz from conments where created_at > DATE_SUB(CURDATE(), INTERVAL 1 WEEK) group by user_id");

             $data2 = $psm->fetchAll(PDO::FETCH_ASSOC);

              //计算点赞分值

            $psm = self::$pdo->query("select user_id , count(*) as fz from blog_agree where created_at > DATE_SUB(CURDATE(), INTERVAL 1 WEEK) group by user_id");

            $data3 = $psm->fetchAll(PDO::FETCH_ASSOC);
          
            //改变数组结构

            $arr = [];

            foreach($data1 as $v){

                $arr[$v['user_id']]=$v['fz'];

            }

            //合并数组
            foreach($data2 as $v){

                if(isset($arr[$v['user_id']])){

                    $arr[$v['user_id']] +=$v['fz'];

                }else{

                    $arr[$v['user_id']]=$v['fz'];

                }

            }

            foreach($data3 as $v){

                if(isset($arr[$v['user_id']])){

                    $arr[$v['user_id']] +=$v['fz'];

                }else{

                    $arr[$v['user_id']]=$v['fz'];

                }

            }

            //根据分值排序

            arsort($arr);

            //取出前20个分值最高的数组

            $data = array_slice($arr,0,20,true);

            //取出key也就是user_id

            $keys = array_keys($data);

            //把user_id数组转换为字符串

            $str = implode(",",$keys);

            //链接数据库，查询出活跃用户的信息

            $psm = self::$pdo->query("select email,face from  user where id in ($str)");

            $user_data = $psm->fetchAll(PDO::FETCH_ASSOC);

            // echo "<pre>";

            // var_dump($user_data);

            //保存活跃用户到redis缓存里面，因为每次打开页面就要访问，所以放到缓存里面减少数据库的压力

            //链接redis

            $redis = \libs\Redis::getredis();

            $redis->set("active_users",json_encode($user_data));
            
        }
       
    



    }

?>