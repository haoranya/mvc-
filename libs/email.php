<?php
  namespace libs;

  class email{

    public $mailer;
    public function __construct()
    {

        $config = config('email');

        // 设置邮件服务器账号    
        $transport = (new \Swift_SmtpTransport($config['host'], $config['port']))  // 邮件服务器IP地址和端口号
        ->setUsername($config['name'])       // 发邮件账号
        ->setPassword($config['pass']);      // 授权码
        // 创建发邮件对象
        $this->mailer = new \Swift_Mailer($transport);
    }

    function send($title,$content,$to){
        $config = config('email');
       // 创建邮件消息
       $message = new \Swift_Message();

        $message->setSubject($title)  
                ->setFrom([$config['from_email'] => $config['from_name']])   
                ->setTo([
                    $to[0], 
                    $to[0] => $to[1]
                ])   
                ->setBody($content, 'text/html');     // 邮件内容及邮件内容类型
        
        // 邮件内容及邮件内容类型
        // 发送邮件
        if($config['mode']=='debug'){
            //获取邮件的所有信息;
            $str = $message->toString();
            //把邮件的信息写入到log文件中
            $log = new \libs\log('email');

            $log->log($str);

        }else{
            echo 1;
            $data = $this->mailer->send($message);
        }
       

  

    }

  }
    


?>