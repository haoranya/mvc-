<?php
    namespace libs;

    class log{

        private $fp ;
        //参数日志文件的名称
        function __construct($name){
            //打开日志文件
            $this->fp = fopen(ROOT.'logs/'.$name.'.log',"a");

        }
        //向文件中追加内容
        function log($content){

            //构造文件的格式
            //获取时间
            $date = date("Y-m-d H:i:s");

            $c = $date.'/r/n';

            $c.=str_repeat("=",120);

            $c.=$content."/r/n/r/n";

            fwrite($this->fp,$c);
            
        }


    }




?>