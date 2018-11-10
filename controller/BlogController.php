<?php
    namespace controller;

    use model\Blog;

   

    class BlogController{


        //点赞接口

        function agreement(){

            $id = $_GET['id'];

            if(!isset($_SESSION['id'])){

                // success("请先进行登录",0,"/user/login");

                echo json_encode([

                    "status_code"=>"403",

                    "message"=>"请先登录"

                ]);

                exit;

            }

            //判断当前的用户是否点过

            $blog = new Blog;

            $data = $blog->hasAgreed($id);

            if($data){

                echo json_encode([

                    "status_code"=>"200",

                    "message"=>"成功"

                ]);

            }else{

                echo json_encode([

                    "status_code"=>"403",

                    "message"=>"请先登录"

                ]);

            }

        }

        //搜索点赞者

        function hit(){

            $id = $_GET['id'];

            $blog = new  Blog;

            echo $blog->hit($id);

        }

        function create(){

            view("blog.create");

        }

        function docreate(){

            $title = $_POST['title'];

            $content = $_POST['content'];

            $is_show =$_POST['is_show'];

            $see_num = rand(50,100);

            $blog = new Blog;

            $id = $blog ->docreate($title,$content,$is_show,$see_num);

            if($is_show=="1"){

                $blog->makeHtml($id);

                success("发布成功",2,"/blog/blog");

            }
            
        }

        function blog(){

            $blog = new Blog;

            $blog ->search();

        }

        function index(){

            $blog20 = new Blog;

            $arr = $blog20 ->index();

        }

        function insert_blog(){

            $blog = new Blog;

            $blog ->blog();

        }

        function insert_user(){

            $blog = new Blog;

            $blog ->user();

        }

        function delete(){

            $id = $_POST['id'];

            $blog = new Blog;

            $blog->deleteHtml($id);

            $blog -> delete($id);

        }

        function href(){

           success("删除成功",0,'/blog/blog');

        }

        function edit(){

            $id = $_POST['id'];

            $blog = new Blog;

            $data = $blog->edit($id);

            view("blog.edit",['data'=>$data]);

        }

        function update(){

            $id = $_POST['id'];

            $title = $_POST['title'];

            $content = $_POST['content'];

            $is_show = $_POST['is_show'];

            $blog = new Blog;

            $blog->update($id,$title,$content,$is_show);

            if($is_show=='1'){

              $blog->makeHtml($id);

            }else{

                $blog->deleteHtml($id);

            }

        }

        function private(){

            $id = $_GET['id'];

            $blog = new Blog;

            $blog->private($id);

        }
        //生成excel文件
        function excel(){

            $user_id = $_SESSION['id'];

            $blog = new Blog;

            $blog->excel($user_id);


        }

    }



?>