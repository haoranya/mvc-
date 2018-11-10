<?php 
    namespace controller;

    use model\User;

    use model\Order;

    class UserController{

        function hello(){

            $user = new User;

            $name = $user->getname();

            view("users.hello",['name'=>$name]);

        }

        function word(){

            phpinfo();

        }

        function login(){

            view("users.login");

        }

        function dologin(){

           $user = new User;

           $user ->dologin();

        }
        function logout(){

            $user = new User;

            $user ->logout();

        }

        function charge(){

            view("users.charge");

        }

        function docharge(){

            $money= $_POST['money'];

            $flake = new \libs\Snowflake(1023);

            $order_num = $flake->nextId() ;

            $order = new Order;

            $order->docharge($order_num,$money);

        }

        function charge_search(){

            $order = new Order;

            $order ->search();

        }

        function delete(){

            $id = $_POST['id'];

            $order = new Order;

            $order ->delete($id);

        }

        function order_state(){

            $sn = $_GET['sn'];

            $order = new Order;

            $data = $order->order_state($sn);           

            echo $data;
        }

        function update_money(){

            $user = new User;

            $user ->update_money();
 

        }

        //活跃用户的计算

        function active_user(){

            $user = new User;

            $user ->active_user();

        }

    }



?>