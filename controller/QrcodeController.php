<?php
   namespace controller;

   use Endroid\QrCode\QrCode;

   class  QrcodeController{

          function qrcode(){

            $str = $_GET['code'];

            $qrCode = new QrCode($str);

            // var_dump($qrCode);

            header('Content-Type: '.$qrCode->getContentType());

            echo  $qrCode->writeString();

          }


   }

?>