<?php
//这个项目中无论使用多少此这个类，new一个对象就够了，多了浪费内存，设置为单例模式
//
//封装类
    namespace libs;

    class uploads {

        private function __construct(){}

        private function __clone(){}

        private static $obj=null;

        public static function upload(){

            if(self::$obj===null){

                self::$obj = new self;

            }

            return self::$obj;

        }

        //封装上传图片

        private $file ;//设置图片的资源

        private $root=ROOT."public/upload/";

        private $dir;//图片要保存的文件夹

        private $ext=["image/jpeg","image/png","image/jpg","image/gif","image/bmp"];//设置图片的类型

        private $_maxSize = 1024*1024*1.8;  // 最大允许上传的尺寸，1.8M
        //上传图片
        function upload_image($name,$dir){

            $this->file = $_FILES[$name];

            $this->dir = $dir;

            if(!$this->check_type()){

                die("图片类型有错");

            }
            if(!$this->check_size()){

                die("图片尺寸有错");

            }
            
            $name = $this->set_name();

            $dir = $this->two_dir();

            if(move_uploaded_file($this->file['tmp_name'],$this->root.$dir.$name)){

                echo "成功";

                return $dir.$name;

            }else{


                echo "失败";

            }

            
        }

        //设置图片的唯一的名字

        function set_name(){

            //获取后缀

            $ext = strrchr($this->file['name'],'.');

            $name = md5(date("Ymd"));

            return $name.".".$ext;

        }

        //设置二级目录

        function two_dir(){

            $date = date("Ymd");

            $dir = $this->dir.$date."/";

            if(!is_dir($this->root.$dir)){

                mkdir($this->root.$dir,0777,true);

            }

            return $dir;


        }

        //检查图片的类型
         private function check_type(){

            return in_array($this->file['type'],$this->ext);

         }
         //检查大小
        private function check_size(){

            return $this->file['size']<$this->_maxSize;

        }


    }



?>