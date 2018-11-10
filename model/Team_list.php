<?php
    namespace model;

    use pdo;

    class Team_list extends bace{

        
        function team_list(){

            //链接redis

            $config = config('redis');

            $redis= \libs\redis::getredis();

            //接收表单信息  
            $email = $_POST['email'];

            $pwd =md5($_POST['pwd']);

            $name = explode("@",$email);

            $from = [$email,$name[0]];  
            //生成随机激活码字符串
            $code = md5(rand(1,9999));

            //设置redis字符串类型的key
            $key = "email_user:".$code;
            //设置key值 
            $value = json_encode(['email'=>$email,'pwd'=>$pwd]);
            //设置key的过去时间：
            $redis->setex($key, 300, $value);
            //设置的发送邮件的信息
            $message=json_encode([
                'title'=>"恭喜你，注册成功，请前去激活",
                'content'=>"点击一下链接进行激活：<br>点击激活
                <a href='http://localhost:9999/text/active?code={$code}'>请点击http://localhost:9999/text/active?code={$code}</a><p>如果不能点击请复制连接到浏览器</p>",
                'from'=>$from,
            ]);
            $redis->lpush('email',$message);
            echo "数据保存队列ok";
        }

        function active_user(){

            //接受激活码

            $code = $_GET['code'];

            //拼接key

            $key = "email_user:".$code;

            //链接redis取出string类型的数据

            $redis= \libs\redis::getredis();

            $data = $redis->get($key);

            if($data){
                //删除已经验证过的激活码
                $redis->del($key);
                //反序列化取出数据保存到数据库
                $arrdata = json_decode($data,true);

                $psm = self::$pdo->prepare("insert into user(email,pwd) values(?,?)");

                $psm->execute([$arrdata['email'],$arrdata['pwd']]);

                echo "<script>alert('激活成功');location.href='/text/register';</script>";

            }else{

                echo "激活码无效";

            }


        }
    }



?>