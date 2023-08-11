#!/usr/bin/php
<?php



function updates($offset=0){
    $db = json_decode(file_get_contents("db.json"),true);


    $datas = json_decode(get("getUpdates?offset=$offset"),true);

    if(empty(current($datas)['result'])) sleep(2);

    $msg = $datas['result'];

  


    if(!empty($msg)){
        
        if(!empty(current($msg)['message']))
        {

            if(!in_array(current($msg)['message']['chat']['id'],$db))
            {
                 authorization(current($msg)['message']);
            }
            else
            {
                if(isset(current($msg)['message']['entities']))
                {
                    command(current($msg)['message']);
                }
                else
                {
                    message(current($msg)['message']);
                }
            }
        }

        if(!empty(current($msg)['callback_query'])){
            callback_query(current($msg)['callback_query']);
        }
     

        
    }
    echo date('H:i:s')."\n";

    

   
    $offset = end($datas['result'])['update_id'];
    updates($offset+1);

}

function authorization($data){

    if($data['text']=="/start"){
        sendStart($data);
    }else{
        $db = json_decode(file_get_contents("db.json"),true);
        $key = preg_replace('/[^0-9]+/', '', $data['text']);
        if(isset($db[$key])){
            $db[$key]=$data['chat']['id'];
            file_put_contents("db.json",json_encode($db),LOCK_EX);

            $dataSend = array(
                'text' => "Поздравляю, Вы авторизованы!",
                 'chat_id' => $data['chat']['id'],
                 'parse_mode'=>"HTML", 
                 
             );
        
           echo  get("sendMessage",$dataSend);
        }else{
            $dataSend = array(
                'text' => "Неудача!\n Попробуйте еще раз",
                 'chat_id' => $data['chat']['id'],
                 'parse_mode'=>"HTML", 
                 
             );
        
           echo  get("sendMessage",$dataSend);
        }
    }


}



function message($data){
    echo "mess";

    // Сохраняем геопозиции
    if(isset($data['location'],$data['reply_to_message'])){
        $db = json_decode(file_get_contents("db.json"),true);
        $name = array_search($data['chat']['id'],$db);
        if($name){
            $filedb = __DIR__."/datas/".$name.".csv";
            $datas = $data['chat']['id'].";".$name.";".$data['location']['latitude'].";".$data['location']['longitude'].";".date('Y-m-d H:i:s')."\n";
            file_put_contents($filedb,$datas,FILE_APPEND | LOCK_EX);
        
            $to      = 'd.makarov@diagnostika.gazprom.ru';
            $subject = 'Кординаты';
            $message = $datas;
            $headers = array(
                'From' => 'webmaster@example.com',
                'Reply-To' => 'webmaster@example.com',
                'X-Mailer' => 'PHP/' . phpversion()
            );

            //mail($to, $subject, $message, $headers);
                    
        }
      
    }else{
        if (array_key_exists('photo', $data)) {
            echo getPhoto($data['photo']);
        }
    }

}


function callback_query($data){
    echo "callback_query";

    sendMenuLoactions($data);

}

function command($data){

    switch ($data['text']) {
        case "/report":
            sendMenuLoactions($data);
            break;
        case "Отметка прибытия убытия":
            sendToMail($data);
            break;
        case "/s":
            
            break;
    }
}


function sendMenuPosition($data){
    $dataSend = array(
        'text' => "Выберите мето работы",
         'chat_id' => $data['chat']['id'],
         // отправляем клавиатуру
         'reply_markup' => getPoints(),
     );

    $r = get("sendMessage",$dataSend);



echo '<pre>';
print_r($r);
echo '</pre>';
}

function sendMenuLoactions($data){
    $keyboard  = json_encode([
    "keyboard"=>
        [
            [
              // [ "text"=>"Прибыл на объект!!","callback_data"=>1],
                ["text"=>"Мое место положение","request_location"=>True]
            ]
        ],
        
    "resize_keyboard"=>True,
    "one_time_keyboard"=>true
    ]);
    
    $data = [
    'chat_id'=>$data['chat']['id'], 
    'text'=> "Отметка прибытия убытия",
    'parse_mode'=>"HTML", 
    'reply_markup'=>$keyboard
    ];
    $r = get("sendMessage",$data);

    echo $r;
}




function getPoints(){
    $poins = [
        "ИТЦ СПБ"=>'itc1',
        "ИТЦ Саратов"=>'itc2',
        "ИТЦ Краснодар"=>'itc3',
        "ИТЦ Видное"=>'itc4',
        "ИТЦ Саратов4"=>'itc5',
        "ИТЦ Краснодар5"=>'itc6',
        "ИТЦ Видное6"=>'itc7',
    ];

   

    foreach($poins as $name=> $code){
        $menu[][]=["text" => $name, "callback_data"=>$code];
    }



    return json_encode(["inline_keyboard"=>$menu]);
}





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
   curl_close($ch);
   return $result;
 
}


function sendToMail($data){

    $dataSend = array(
        'text' => "Данные отпрвлены",
         'chat_id' => $data['chat']['id'],
         // отправляем клавиатуру
         'reply_markup' => json_encode([
            "remove_keyboard" => true
         ])
     );
     get("sendMessage",$dataSend);

    $iname=!empty($data['from']['first_name']) ? $data['from']['first_name'] : $data['from']['username'];
    $fname=!empty($data['from']['last_name']) ? $data['from']['last_name'] : $data['from']['username'];

    $text=$fname." ".$iname.", ";
     echo $text;
}

function sendStart($data){

    $dataSend = array(
        'text' => "Для авторизации введите табельный номер \n1100",
         'chat_id' => $data['chat']['id'],
         'parse_mode'=>"HTML", 
         
     );

   echo  get("sendMessage",$dataSend);
}



updates();




 function getPhoto($data)
{
    // берем последнюю картинку в массиве
    $file_id = end($data)['file_id'];
    // получаем file_path
    $file_path = getPhotoPath($file_id);
    // возвращаем результат загрузки фото
    return copyPhoto($file_path);
}

// функция получения метонахождения файла
 function getPhotoPath($file_id) {
    // получаем объект File
   // $array = json_decode($this->requestToTelegram(, "getFile"), TRUE);
    $array = json_decode(get("getFile",['file_id' => $file_id]),true);


    // возвращаем file_path
    return  $array['result']['file_path'];
}

// копируем фото к себе
 function copyPhoto($file_path) {
    // ссылка на файл в телеграме
    $file_from_tgrm = "https://api.telegram.org/file/bot5607376720:AAGlecElcXiqCSE7hypcl5eJuQxQz8F70Bw/".$file_path;

    // достаем расширение файла

    exec("cd img && wget  $file_from_tgrm");

    //file_put_contents(basename($file_from_tgrm), file_get_contents($file_from_tgrm));
    //return copy($file_from_tgrm,basename($file_from_tgrm));
}
