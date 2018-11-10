<?php 

    namespace model;

    use PDO;

    use PhpOffice\PhpSpreadsheet\Spreadsheet;//用于生成excel

    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;//用于生成excel

    class Blog extends Bace{


        //判断日志是否点赞

        function hasAgreed($id){

            $psm = self::$pdo->prepare("select count(*) from blog_agree where user_id = ? and blog_id = ?");
             
            $psm->execute([

                $_SESSION['id'],

                $id

            ]);

            $count = $psm->fetch(PDO::FETCH_COLUMN);

            if($count==1){

                return false;

            }

            $psm = self::$pdo->prepare("insert into blog_agree(user_id,blog_id) values(?,?);");
             
            $data = $psm->execute([

                $_SESSION['id'],

                $id

            ]);

            if($data){

                $psm = self::$pdo->prepare("update blog set agree_count = agree_count+1 where id = ?");
             
                $result = $psm->execute([
    
                    $id
    
                ]);

                if($result){

                    return true;

                }



            }
            



        }

        //获取点击者

        function hit($id){

            $psm = self::$pdo->prepare("select * from blog_agree ba left join user u on ba.user_id=u.id where ba.blog_id = ?");
             
            $psm->execute([

                $id

            ]);

            $data = $psm->fetchAll(PDO::FETCH_ASSOC);

            if($data){

    
                return json_encode(["data"=>$data]);

            }

           

        }


        //给某一个日志生成静态页面
        //参数，日志id
        function makeHtml($id){

            //取出日志的信息
          $blog = $this->edit($id);

            //打开缓冲区
            ob_start();

            view("blog.private",['v'=>$blog]);

            //从缓冲区取出视图并写到静态页面

            $str = ob_get_contents();

            file_put_contents(ROOT."public/contents/{$id}.html",$str);

            ob_clean();

        }

        function deleteHtml($id){

            @unlink(ROOT."public/contents/{$id}.html");

        }

        function search(){

            if(isset($_SESSION['id'])){

                $where = "where user_id={$_SESSION['id']} ";

                if(isset($_GET['keyword'])&&$_GET['keyword']){
    
                    $keyword = $_GET['keyword'];
    
                    $where.=" and (title like '%{$keyword}%' or content like '%{$keyword}%')";
    
                }
                if(isset($_GET['start'])&&$_GET['start']){
    
                    $start = $_GET['start'];
    
                    $where.=" and created_at >= '{$start}'";
    
                }
    
                if(isset($_GET['end'])&&$_GET['end']){
    
                    $end = $_GET['end'];
    
                    $where.=" and created_at <= {$end}";
    
                }
    
                if(isset($_GET["is_show"])&&$_GET["is_show"]!==''){
    
                    $is_show = $_GET["is_show"];
    
                    $where.=" and is_show = {$is_show}";
            
                }
    
                $order = 'asc';
    
                $ordername = 'created_at';
    
                if(isset($_GET['show'])&&$_GET['show']){
    
                    $ordername = 'is_show';
    
                    $order=$_GET['show'];
    
                }
    
                if(isset($_GET['num'])&&$_GET['num']){
    
                    $ordername = 'see_num';
    
                    $order=$_GET['num'];
    
                }
    
                $psm1 = self::$pdo->query("select count(*) from blog ");
    
                $data1 = $psm1->fetch(PDO::FETCH_NUM);
    
                $totalnum = $data1[0];
    
                $perpage=15;
    
                $pagenum = ceil($totalnum/$perpage);
    
                $nowpage = 1;
    
                if(isset($_GET['page'])&&$_GET['page']){
    
                    $nowpage = $_GET['page'];
    
                }
    
                $startnum = ($nowpage-1)*$perpage;
    
                $btn='';
    
                for($i=1;$i<=$pagenum;$i++){
    
                    $getparams=getparams();
    
                    if($nowpage==$i){
    
                        $class = 'class="choose"';
                        
                    }else{
    
                        $class="";
    
                    }
    
                    $btn.="<a {$class} href='?page={$i}{$getparams}'>{$i}</a>";
    
                }
    
                $limit = " limit {$startnum},{$perpage}";
    
                $psm = self::$pdo->query(" select * from blog {$where} order by {$ordername} {$order} {$limit}");
    
                $data = $psm->fetchAll(PDO::FETCH_ASSOC);
    
                ob_start();
    
                view("blog.blog",['blogs'=>$data,'btn'=>$btn]);
                
            }else{

                header("Location:/user/login");

            }



        }

        function index(){

             $psm = self::$pdo->query("select * from blog order by id desc limit 20");
             
             $data20 = $psm->fetchAll(PDO::FETCH_ASSOC);

             //链接redis

             $redis = \libs\Redis::getredis();

             //获取活跃用户

             $str = $redis->get("active_users");

             $users = json_decode($str);

             view("index.index",['data20'=>$data20,'users'=>$users]);

             ob_start();

             foreach($data20 as $v){

                view("index.static",['v'=>$v]);

               $str = ob_get_contents();

               file_put_contents(ROOT.'public/static20/'.$v['id'].'.html', $str);

               ob_clean();

               
             }

        }

        function number(){

            $id = $_GET['id'];

            $key = "blog_".$id;
            //redis的本地链接
            $redis= \libs\redis::getredis();

            //操作数据

            //检测redis里面是否存在这颗key

            if($redis->hexists("see_num",$key)){
                //自增1
                $redis->hincrby("see_num","{$key}",1);

                $value = $redis->hget("see_num","{$key}");

                $name = "user_money:".$_SESSION['id'];

                $money = $redis->get($name);

                // echo $value;

                echo json_encode([

                    'value'=>$value,

                    'email'=>isset($_SESSION['email'])?$_SESSION['email']:'',

                    'money'=>$money,

                ]);

            }else{
                //redis里面不岑在此key则从数据库读取，进行自增1，然后保存到redis
                $psm = self::$pdo->query("select see_num from blog where id = {$id}");

                $num = $psm->fetch(PDO::FETCH_COLUMN);

                $num++;

                self::$pdo->exec("update blog set see_num = {$num} where id = {$id}");

                $redis->hsetnx('see_num',$key,$num); 

                $name = "user_money:".$_SESSION['id'];

                $money = $redis->get($name);
     
                echo json_encode([

                    'value'=>$num,
                    
                    'email'=>isset($_SESSION['email'])?$_SESSION['email']:'',

                    'money'=>$money,

                ]);

            }

        }

        function ajax(){

            //redis的本地链接
             $redis= \libs\redis::getredis();
            //操作数据
           
            $arr=$redis->hgetall('see_num');

            var_dump($arr);

            foreach($arr as $k=>$v){

               $keys= explode('_',$k);

            //    var_dump($keys[1]);

               $psm =  self::$pdo->prepare("update blog set see_num={$v} where id={$keys[1]}");

               $psm->execute();

            }    
        }

        function docreate($title,$content,$is_show,$see_num){

           $psm = self::$pdo->prepare("insert into blog(title,content,is_show,see_num,user_id) values(?,?,?,?,?)");

           $data = $psm->execute([$title,$content,$is_show,$see_num,$_SESSION['id']]);

           if($data){

            $id = self::$pdo->lastInsertId();

            return $id;

           }else{

            echo "发布失败";

           }

        }

        // function delete($id){

        //     $psm = self::$pdo->prepare("delete from blog where id = ? and user_id = ?");

        //     $data = $psm->execute([

        //         $id,$_SESSION['id']

        //     ]);
            
        //     if($data){

        //         success("删除成功",0,'/blog/blog');

        //     }


        // }

        function delete($id){

            $psm = self::$pdo->prepare("delete from blog where id = ? and user_id = ?");

            $data = $psm->execute([

                $id,$_SESSION['id']

            ]);
            
            if($data){


               echo 1;

            }


        }

        function edit($id){

            $psm = self::$pdo->prepare("select * from blog where id = ?");

            $psm->execute([

                $id

            ]);

           return  $data = $psm->fetch();

           

        }

        function update($id,$title,$content,$is_show){

            $psm = self::$pdo->prepare("update blog set title = ? ,content = ? , is_show = ?  where id = ?");

            $data = $psm->execute([

                $title,$content,$is_show,$id

            ]);

            if($data){

                success("更新成功",2,"blog/blog");

            }

        }

        function blog(){

            for($i=1;$i<=200;$i++){

            $str = getChar(rand(50,100));

            $num = rand(20,100);

            $see_num = rand(0,1);

            $user_id = rand(1,20);

            $psm=self::$pdo->prepare('insert into blog(title,content,is_show,see_num,user_id) values(?,?,?,?,?)');

            $data = $psm->execute([$str,$str,$see_num,$num,$user_id]);

        }

        }

        function user(){

            for($i=1;$i<=20;$i++){

                $name = rand(10000,9999999999);

                $email = $name."@126.com";
    
                $pwd = md5(123456);

                $psm = self::$pdo->prepare("insert into user(email,pwd) values(?,?)");

                $data = $psm->execute([$email,$pwd]);

            }

        }

        function private($id){

            $user_id = $_SESSION['id'];

            $psm = self::$pdo->prepare("select * from blog where id = ? and user_id = ? ");

            $psm->execute([

                $id,$user_id

            ]);

            $data = $psm->fetch();

            if($data){

                view("blog.private",['v'=>$data]);

            }else{

                echo "无权访问";

            }

           


        }

        function excel($user_id){

            $psm = self::$pdo->prepare("select * from blog where user_id = ? limit 20");

            $psm->execute([

                $user_id

            ]);

            $data = $psm->fetchAll();

            // var_dump($data);

            //获取当前的标签页

            $spreadsheet = new Spreadsheet();

           

            //获取当前的工作

            $sheet = $spreadsheet->getActiveSheet();
            
            //设置第一行内容

            $sheet->setCellValue('A1', '标题');
            $sheet->setCellValue('B1', '内容');
            $sheet->setCellValue('C1', '发表时间');
            $sheet->setCellValue('D1', '是否公开');
            
            //从第二行写入数据
                
            $i = 2;

            foreach($data as $v){
                echo 1;
                $sheet->setCellValue('A'.$i, $v['title']);
                $sheet->setCellValue('B'.$i, $v['content']);
                $sheet->setCellValue('C'.$i, $v['created_at']);
                $sheet->setCellValue('D'.$i, $v["is_show"]);

                $i++;

            }

           

            //生成excel文件

            $date = date("Ymd");

            $writer = new Xlsx($spreadsheet);
            
            $writer->save(ROOT . "excel/".$date."xlsx");

            //下载文件的路径

            $file = ROOT . "excel/".$date."xlsx";

            //下载文件的名字

            $fileName = "最新的20条日志".$date."xlsx";

            //告诉浏览器这是一个二进制文件流
            Header ( "Content-Type: application/octet-stream" ); 
            // 请求范围的度量单位
            Header ( "Accept-Ranges: bytes" );
            // 告诉浏览器文件尺寸
            Header ( "Accept-Length: " . filesize ( $file ) ); 
            //开始下载，下载时的文件名
            Header ( "Content-Disposition: attachment; filename=" . $fileName );

            // 读取服务器上的一个文件并以文件流的形式输出给浏览器

            readfile($file);

        }


    }

?>