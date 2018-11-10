<?php
    namespace model;

    use pdo;

    class Order extends bace{

        function search(){

            $where = "where user_id={$_SESSION['id']} ";

            if(isset($_GET['create'])&&$_GET['create']){

                $create = $_GET['create'];

                $where.=" and created_at >= '{$create}'";

            }

            if(isset($_GET['pay_time'])&&$_GET['pay_time']){

                $pay_time = $_GET['pay_time'];

                $where.=" and pay_time <= {$pay_time}";

            }

            if(isset($_GET["state"])&&$_GET["state"]!==''){

                $state = $_GET["state"];

                $where.=" and state = {$state}";
        
            }

            $order = 'asc';

            $ordername = 'created_at';

            if(isset($_GET['pay_time'])&&$_GET['pay_time']){

                $ordername = 'pay_time';

                $order=$_GET['pay_time'];

            }

            $psm1 = self::$pdo->query("select count(*) from orders ");

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

            $psm = self::$pdo->query(" select * from orders {$where} order by {$ordername} {$order} {$limit}");

            $data = $psm->fetchAll(PDO::FETCH_ASSOC);

            view("order.search",['order'=>$data,'btn'=>$btn]);

        }
    //订单
        function docharge($order,$money){



             $psm = self::$pdo->prepare("insert into orders(user_id,money,sn) values(?,?,?)");

             $data = $psm->execute([

                 $_SESSION['id'],

                 $money,

                 $order,
             ]);

             if($data){

                // echo $money;

                 success("请前往充值",2,"/user/charge_search");

             }else{

                 echo "充值失败";

             }

    }

        function delete($id){

            $psm = self::$pdo->prepare("delete from orders where id = ? ");

            $data = $psm->execute([$id]);

            if($data){

               echo 1;

            }else{

                echo 0;

            }



        }

        function find($sn){

            $psm = self::$pdo->prepare("select * from orders where sn = ? ");

            $psm->execute([$sn]);

            $data = $psm->fetch(PDO::FETCH_ASSOC);

            return $data;
        }

        function setstate($sn){

            $psm = self::$pdo->prepare("update orders set state = ? where sn = ? ");

            $data = $psm->execute(['1',$sn]);

            if($data){

                echo 1;
 
             }else{
 
                 echo 0;
 
             }
 

        }

        function order_state($sn){

            $data = $this->find($sn);

            $try = 3;

            do{

                if($data['state']==0){

                    sleep(1);
    
                    $try--;
    
                }else{

                    break;

                }

            }while($try>0);

            return $data['state'];
            
        }
     
        
    }
?>