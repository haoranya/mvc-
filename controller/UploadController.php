<?php
    namespace controller;

    class UploadController{

        function upload(){
            //接收图片
            $image = $_FILES['image'];

            //生成随机的文件名

            $name = time();

            //移动图片到指定的文件夹下

            move_uploaded_file($image['tmp_name'],"public/uploads/{$name}.png");

            //此插件要求上传的图片需要返回一个json值;

            echo json_encode([

                'success'=>true,

                'file_path'=>"public/uploads/{$name}.png",

            ]);


        }


    }





?>