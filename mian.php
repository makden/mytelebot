#!/usr/bin/php
<?php



// $reply_markup = [
//    "keyboard"=>["Привет", "Hello"], 
//    "resize_keyboard"=> True, 
//    "one_time_keyboard"=> True,
// ];

$a  = json_encode([
   "keyboard"=>
      [[
         
         "Привет", "Hello"
         
      ],
      [["request_location"=>True, "text"=>"Где я нахожусь"]]
   ],
      
   "resize_keyboard"=>True,
   "one_time_keyboard"=>True
]);
$data = [
   'chat_id'=>5858754566, 
   'text'=> "test test 12", 
   'reply_markup'=>$a
];

// echo '<pre>';
// echo print_r($data);
// echo '</pre>';

// $r = get("sendMessage",$data);


// echo '<pre>';
// echo print_r($r);
// echo '</pre>';

sleep(2);

$r = get("getUpdates");


echo '<pre>';
echo print_r($r);
echo '</pre>';


function get($method,$params=[]){

   $host="https://api.telegram.org/bot";
   $token = "5607376720:AAGlecElcXiqCSE7hypcl5eJuQxQz8F70Bw";

   $ch = curl_init($host.$token . '/'.$method);
   curl_setopt($ch, CURLOPT_HEADER, false);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($ch, CURLOPT_POST, 1);
   curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, '', '&'));
   curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
   $result = curl_exec($ch);
   return $result;
   curl_close($ch);
}
