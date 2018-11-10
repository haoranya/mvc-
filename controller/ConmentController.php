<?php
    namespace controller;

    class ConmentController{

        //发表评论

        function conments(){

            //接收特殊数据  原始数据  如 xml json $_POST 等php 无法直接接收

            //接收原始数据  

            $data = file_get_contents("php://input");//固定写法

            //转换为数组

            $_POST = json_decode($data,true);

            //检查是否登录

            if(!isset($_SESSION['id'])){

                echo json_encode([

                    'status_code' => '401',

                    'message' => '未登录！'

                ]);

                exit;

            }

            //接收表单的数据

            $content = e($_POST['content']);//防止跨站xss脚本攻击

            $blog_id = $_POST['blog_id'];

            //链接评论模型

            $conment = new \model\Conment;

            //调用模型方法插入数据库

            $data = $conment->insert_conment($content,$blog_id);

            //返回新发表的数据，进行过滤

            if($data){

                echo json_encode([

                    'status_code' => '200',

                    'message' => '发表成功',

                    'data'=>[
    
                        'content'=>$content,
    
                        "email"=>$_SESSION['email'],
    
                        "face"=>"/".$_SESSION['face'],
    
                        "created_at"=>date("Y-m-d H:i:s"),
    
                    ]
                ]);

            }

           

            
        }

        function get_conments(){

            $blog_id = $_GET['blog_id'];

             //链接评论模型

             $conment = new \model\Conment;

             //调用模型方法插入数据库
 
             $data = $conment->get_conments($blog_id);
 
             //返回新发表的数据，进行过滤

             if($data){

                echo json_encode([

                    'status_code' => '200',

                    'message' => '获取成功',

                    'data'=>$data,

                ]);

             }

        }

    }



?>