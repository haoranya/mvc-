<?php
namespace controller;

use Yansongda\Pay\Pay;

class WxpayController
{
    protected $config = [
        'app_id' => 'wx426b3015555a46be',
        'mch_id' => '1900009851',
        'key' => '8934e7d15453e97507ef794cf7b0519d',

        // 通知的地址
        'notify_url' => 'http://f7d85cb6.ngrok.io/wxpay/notify',
    ];

    // 调用微信接口进行支付
    public function pay()
    {

        $sn = $_POST['sn'];//接收订单号

        $order = new \model\Order;

        $data = $order->find($sn);

        if($data['state']==0){

            $order = [
                'out_trade_no' => $data['sn'],
                'total_fee' => $data['money']*100, // **单位：分**
                'body' => '充值'.$data['money']."分",
            ];

             // 调用接口
           $pay = Pay::wechat($this->config)->scan($order);

            if($pay->return_code=="SUCCESS"&&$pay->result_code=="SUCCESS"){

                //加载视图把支付码的字符串传递到页面
                
                view("users.wxpay",[

                    'code'=>$pay->code_url,
                    
                    'sn'=>$sn,

                ]);
            
            }else{

                echo 1;

                die();

            }

        }else{

            echo "订单不符合条件";

        }

     
    }

    public function notify()
    {
        //由于微信是在后台通知的所以看不到故写到日志里面

        //创建日志对象

        $log = new \libs\log('weinxin.log');

        $log->log('接收到微信的通知');

        $pay = Pay::wechat($this->config);

        try{

            //php默认只能接收$_GET $_POST 但一些特殊的数据不可以

            //如：json  xml数据

            //微信发送的通知是 xml  所以无法使用$_GET或者$_POST接收

            //但是可以使用php:://input来接受

            $log->log("接收到的数据是：".file_get_contents("php:://input"));

            $data = $pay->verify(); // 是的，验签就这么简单！

            if($data->result_code == 'SUCCESS' && $data->return_code == 'SUCCESS')
            {

                 //更新订单的状态

                 $order = new \model\Order;

                 $info = $order->find($data->out_trade_no);
 
                 if($info['state']==0){
 
                     //开启事务
                     $order->startTran();
 
 
                     //设置状态
                     $result = $order->setstate($data->out_trade_no);
 
                     //更新用户的余额
 
                     $user = new \model\User;
 
                     $user_data = $user->setmoney($info['money'],$info['user_id']);
                     //判断事务
                     if($result&&$user_data){
 
                        $order->commit();
 
                     }else{
 
                         $order->rollback();
 
                     }
 
                 }

            }

        } catch (Exception $e) {
            var_dump( $e->getMessage() );
        }
        
        $pay->success()->send();
    }
}