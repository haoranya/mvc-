<?php 
    ini_set('session.save_handler','redis');
    ini_set('session.save_path','tcp://127.0.0.1:6379?database=3');
    // phpinfo();
    session_start();
   
    define("ROOT",dirname(__FILE__)."/../");

    require(ROOT.'vendor/autoload.php');

    //如果用户以 post 方式访问网站时，需要验证令牌

    // if($_SERVER['REQUEST_METHOD']=='POST'){

    //     if(!isset($_POST['_token'])){

    //         die("违法操作");

    //     }

    //     if($_POST['_token']!=$_SESSION["token"]){
           
    //         die("违法操作");

    //     }

    // }
    
    function autoload($class){

        $path = str_replace("\\",'/',$class).".php";
        // var_dump(ROOT.$path);
        require(ROOT.$path);
    }
    
    spl_autoload_register('autoload');

    if(php_sapi_name()=='cli'){

        $_C=ucfirst($argv[1])."Controller";
        $_A=$argv[2];

    }else{

        if(isset($_SERVER['PATH_INFO'])&&$_SERVER['PATH_INFO']){

            $arr = explode('/',$_SERVER['PATH_INFO']);
    
            $_C=ucfirst($arr[1])."Controller";
    
            $_A=$arr[2];
    
        }else{
    
            $_C = 'IndexController';
    
            $_A = 'index';
    
        }
    }

    $controller = "controller\\".$_C;

    $usercontroller = new $controller;

    $usercontroller->$_A();

    function view($viewname,$data=[]){

        extract($data);//解压数组为变量

        $path = str_replace(".","/",$viewname).".html";

        require(ROOT.'views/'.$path);

    }

    function getparams(){

        unset($_GET['num']);
        unset($_GET['show']);
        unset($_GET['page']);

        $ret = '';

        foreach($_GET as $k=>$v){

            $ret.="&$k=$v";

        }

        return $ret;

    }
    //无论调用多少次，只包含文件一次
    function config($name){

        static $config=null;//静态局部变量的特点，函数执行完毕不会销毁，知道脚本执行完毕

        if($config==null){

           $config= require(ROOT.'config.php');

        }

        return $config[$name];


    }

    function redirect($url){

        header("Location:{$url}");

    }

    function back(){

        redirect($_SERVER['HTTP_REFERER']);
            
    }
    function success($message,$type,$url,$secound=5){

        if($type==0){
           
            echo  "<script>alert('{$message}');location.href='{$url}';</script>";

        }else if($type==1){
           
            
            view("common.success",['message'=>$message,'url'=>$url,'secound'=>$secound]);

        }else if($type==2){


            $_SESSION['_MESS_']=$message;

            redirect($url);

        }

    }

    function getChar($num)  // $num为生成汉字的数量
    {
        $b = '';

        for ($i=0; $i<$num; $i++) {
            // 使用chr()函数拼接双字节汉字，前一个chr()为高位字节，后一个为低位字节
            $a = chr(mt_rand(0xB0,0xD0)).chr(mt_rand(0xA1, 0xF0));
            // 转码
            $b .= iconv('GB2312', 'UTF-8', $a);
        }

        return $b;
    }

    //过滤xss防止跨站脚本攻击
    function e($content){

        return htmlspecialchars($content);

    }

    //在线编辑器的xss选择行过滤

    function hpe($contnen){

        //保证每次过滤只产生一个对象（优化）;
        static $purifier = null;

        if($purifier===null){

         //创造唯一的对象那个

         // 1. 生成配置对象

         $config = \HTMLPurifier_Config::createDefault();

         // 2. 配置
         
         // 设置编码
         $config->set('Core.Encoding', 'utf-8');
         $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
         // 设置缓存目录
         $config->set('Cache.SerializerPath', ROOT.'cache');
         // 设置允许的 HTML 标签
         $config->set('HTML.Allowed', 'div,b,strong,i,em,a[href|title],ul,ol,ol[start],li,p[style],br,span[style],img[width|height|alt|src],*[style|class],pre,hr,code,h2,h3,h4,h5,h6,blockquote,del,table,thead,tbody,tr,th,td');
         // 设置允许的 CSS
         $config->set('CSS.AllowedProperties', 'font,font-size,font-weight,font-style,margin,width,height,font-family,text-decoration,padding-left,color,background-color,text-align');
         // 设置是否自动添加 P 标签
         $config->set('AutoFormat.AutoParagraph', TRUE);
         // 设置是否删除空标签
         $config->set('AutoFormat.RemoveEmpty', TRUE);

         // 创建对象
         $purifier = new \HTMLPurifier($config);

        }
         // 3. 过滤
         
         // 过滤
         $clean_html = $purifier->purify($content);
         echo $clean_html;
    }

     //csrf的令牌机制
     function csrf(){

        //首先判断有没有令牌，没有在生成
        if(!isset($_SESSION['token'])){

        //生成随机的令牌
        $token = md5(  rand(1,99999).microtime() );

        $_SESSION['token']=$token;

        }

        return $_SESSION['token'];
       
    }

    //生成令牌隐藏域

    function csrf_field(){

        $csrf = isset($_SESSION['_token'])?$_SESSION['_token']:csrf();

        return " <input type='hidden' name='_token' value='{$csrf}' >";
    }


   



?>