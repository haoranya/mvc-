<?php
    namespace controller;

    class RedController{

        function redbag(){

            view("redbag.redbag");

        }

        //抢夺红包

        function rob(){

            //判断是否登录
            if(!isset($_SESSION['id'])){

                echo json_encode([
                    'status_code' => '401',
                    'message' => '未登录！'
                ]);
                exit;

            }

            //判断当前是否在时间点
            if(date('H')<9||date("H")>10){


                echo json_encode([
                    'status_code' => '403',
                    'message' => '时间段不允许！'
                ]);
                exit;

            }
            //判断是否已经抢夺了

            $key = "redbag_".date("Ymd");//拼接集合名字

            $redis = \libs\Redis::getredis();

            $exists = $redis->sismember($key,$_SESSION['id']);

            if($exists){

                echo json_encode([
                    'status_code' => '403',
                    'message' => '今天已经抢过了~'
                ]);

                exit;

            }
            //能执行到这里说明都符合条件,判断红包数量
            $stock = $redis->decr("redbag_stock");

            if($stock<0){

                echo json_encode([
                    'status_code' => '403',
                    'message' => '今天的红包已经减完了~'
                ]);
                exit;

            }

            //执行到这里说明以上的判断都没有问题

            $redis->lpush("redbag_orders",$_SESSION['id']);

            //把id放到集合里面，表示已经抢夺

            $redis->sadd($key,$_SESSION['id']);

            echo json_encode([
                'status_code' => '200',
                'message' => '恭喜你~抢到了本站的红包~'
            ]); 



        }

        //初始化

        function init(){

            //初始化库存量

            $redis = \libs\Redis::getredis();

            $redis->set('redbag_stock', 20);

            //初始化一个空的集合

            $key = "redbag_".date("Ymd");

            $redis->sadd($key,"-1");

            //设置过期
            $redis->expire($key,3600);

        }

        //后台运行，取出队列数据每当有人强到，就会将他的信息保存到队列中

        function makeOrder(){

            $redis = \libs\Redis::getredis();

            $model = new \model\Redbag;

            // 设置 socket 永不超时
            ini_set('default_socket_timeout', -1); 

            echo "开始监听红包队列... \r\n";
            
            while(true){

                $data = $redis->brpop("redbag_orders",0);

                //获取到用户id
                $userId = $data[1];
                //插到数据库
                $model->create($userId);

                echo "有人抢了红包";
            }

        }


       
    }


?>