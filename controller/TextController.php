<?php
    namespace controller;

    use model\Blog;

    use libs\email;

    use model\Team_list;

    use Intervention\Image\ImageManagerStatic as Image;

    class TextController{

        //测试图片处理
        function textImage(){
           
            //要先打开处理的图片
            
            $image = Image::make(ROOT."public/upload/image/big.png");
            
            //添加水印
            $image->insert(ROOT."public/upload/water.jpg","top-right");

            //保存图片

            $image->save(ROOT."public/upload/image/water_big.png");

        }


        public function testmail()
        {
            $m = new email;
            $m->send('abc','bcd', ['fortheday@126.com', 'fortheday']);
        }

        function text(){

            $redis = new Blog;

            $redis->number();
        }

        function ajax(){

            $redis = new Blog;

            $redis->ajax();
        }

        function register(){

            view("register.register");

        }


        function team(){

            $redis = new Team_list;

            $redis->team_list();

        }

        function send (){   

            $mailer = new email;
              // 设置 PHP 永不超时
             ini_set('default_socket_timeout', -1);

             $config = config('redis');

            $redis= \libs\redis::getredis();

            while(true){

                echo "开始发邮件\r\n";
                
                $data =$redis->brpop('email',0);

                $dataarr = json_decode($data[1],true);

                $mailer->send($dataarr['title'], $dataarr['content'], $dataarr['from']);

                echo "发送完成，继续发送\r\n";
                
            }
        }

        function active(){
            $active = new Team_list;

            $active->active_user();
        }

        function textxss(){


            // 测试字符串
        $content = "你懂 <a href=''></a>  的 <a href=''>小技巧   fdaf<div>fdafd</div> fdsa <script>alert('abc');</script>";

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

        // 3. 过滤
        // 创建对象
        $purifier = new \HTMLPurifier($config);
        // 过滤
        $clean_html = $purifier->purify($content);


        echo $clean_html;



        }

        

        
       

    }




?>