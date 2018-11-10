<?php
    namespace controller;

    use model\Face;

    use Intervention\Image\ImageManagerStatic as Image;

    class FaceController{

        function myself(){

            view("face.face");

        }

        function face(){

            var_dump($_FILES['file']);
            //建立根目录
            $root = ROOT."public/upload/";
            //时间子目录
            $date = date('Ymd');

            if(!is_dir($root.$date)){//判断目录是否存在

                mkdir($root.$date,0777);//不存在创建

            }

            //生成唯一的文件名

            $name = md5(time().rand(1,9999));

            //获取后缀

            $ext = strrchr($_FILES['file']['name'],'.');

            $name = $name.$ext;

            if(isset($_FILES['file'])){

                move_uploaded_file($_FILES['file']['tmp_name'],$root.$date."/".$name);

            }else{

                echo "失败";

            }

        }

        function more_img(){


            view("face.upload_img");

        }

        function upload_img(){

            echo "<pre>";

            var_dump($_FILES['file']);

              //建立根目录
              $root = ROOT."public/upload/";
              //时间子目录
              $date = date('Ymd');
  
              if(!is_dir($root.$date)){//判断目录是否存在
  
                  mkdir($root.$date,0777);//不存在创建
  
              }
  
              //生成唯一的文件名
  
              $name = md5(time().rand(1,9999));
  
              //获取后缀

              foreach($_FILES['file']['name'] as $k=>$v){

                $ext = strrchr($v,'.');
  
                $name = $name.$ext;
    
                if(isset($_FILES['file'])){
    
                    move_uploaded_file($_FILES['file']['tmp_name'][$k],$root.$date."/".$name);
    
                }else{
    
                    echo "失败";
    
                }
  

              }
  
             
        }

        function big_file(){

            view("face.big_file");

        }

        function per_upload(){

            var_dump($_POST);

            var_dump($_FILES);

            $count = $_POST['count'];//总的数量

            $i = $_POST['i'];//当前的块数

            $size = $_POST['size'];//每块大小

            $name = $_POST['name'];//所属图片资源的标识
            
            $img = $_FILES['img'];//当前块的资源

            //把每一块资源保存到 服务器

            move_uploaded_file($img['tmp_name'],ROOT."tem/".$i);

            //当所有的资源上传到服务器,就和并成一个完整的

            //因为，每个分块到达服务器的顺序不一定，所以不能根据最后一块上传成功
            //就判断说明成功上传完毕

            //所以可以做一个累加器，判断累加器的数量等于总的资源数的时候就判断完毕

            //可以在内存中实现累加

            //链接redis

            $redis = \libs\redis::getredis();

            $addNum = $redis->incr($name);//用这个名字的图片标识作为累加器

            if($addNum = $count){

                $root = ROOT."public/upload/big/";

                //创建并打开最终的大文件;

                if(!is_dir($root)){

                    mkdir($root,0777,true);

                }

                $fp = fopen($root.$name.".png","a");

                //循环所有的块资源

                for($i = 0;$i<$count;$i++){

                    //写入到大文件

                    fwrite($fp,file_get_contents(ROOT."tem/".$i));

                    //删除已经写入到文件里的资源

                    @unlink(ROOT."tem".$i);

                }

                //关闭文件

                fclose($fp);

                //删除redis里面的累加器

                $redis->del($name);

            }

        }

        function upload(){

            $upload = \libs\uploads::upload();

            $name = "file";

            $dir = "face/";

            $path = $upload->upload_image($name,$dir);

            //裁剪图片

            $image = Image::make(ROOT."public/upload/".$path);

            //crop方法里面的值需要是整数

            $image->crop((int)$_POST['x'],(int)$_POST['y'],(int)$_POST['w'],(int)$_POST['h']);
            //保存裁剪好的图片

            $image->save(ROOT."public/upload/".$path);

            $face = new Face;

            $face->set_face("/upload/".$path);

            @unlink(ROOT."public/upload".$path);

        }

    }



?>