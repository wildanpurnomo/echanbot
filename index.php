<?php
require __DIR__. '/vendor/autoload.php';

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\MessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

//set false for production
$pass_signature = true;

//set LINE channel_access_token and channel_secret
$channel_access_token = "PeZMaXbgf3PfrAUev/InnjGwXj6d2YYTHGeI5HQ5DidC43rVNz/7R+PZj1xexo+5LM/Tur0WzCuLhpnh8qdxBe6f+13ghECCNyPP/oAYLzV2Po/s2lNZNL+Fe1bgm0qSfzYeR4daXNYlrAIkQZ6IfwdB04t89/1O/w1cDnyilFU=";
$channel_secret = "6db5b7f01c4bbff2b2082e2ba6f30b55";

//inisiasi bot object
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$configs = [
    'settings' => ['displayErrorDetails' => true],
];
$app = new Slim\App($configs);


//buat route untuk url homepage
$app->get('/', function($req, $res)
{
    echo "Welcome at Slim Framework";
});

//buat route untuk webhook
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature, $httpClient)
{
    //get request body and line signature header
    $body = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'
] : '';

    //log body and signature
    file_put_contents('php://stderr', 'Body: ',$body);

    if($pass_signature === false)
    {
        //is LINE_SIGNATURE exists in request header?
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }

        //is this request comes from LINE
        if(! SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'invalid signature');
        }
    }

    //kode aplikasi

    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                if($event['message']['type'] == 'text')
                { 
                    $userID = $event['source']['userId'];
                    $message = $event['message']['text'];
                    $msgHeader = substr($message, 0, 5);
                    
                    if(strtolower($msgHeader) === 'sakun'){
                        $mainMsg = substr($message, 4, strlen($message) - 1);

                        
                        $host = "host = ec2-50-19-127-158.compute-1.amazonaws.com";
                        $user = "user = rpcyqnvmcpuhsc";
                        $password = "password = ed21269bb5fc5a4ca773b87eebc9bf63df0fdd5321e709c408e858c9a7bde0b9";
                        $dbname = "dbname = dbtnrb8vhn7n5e";
                        $port = "port = 5432";
        
                        $db = pg_connect("$host $port $dbname $user $password");

                        $repMsg = new TextMessageBuilder('Terima kasih. Pesan telah dimasukan record Database.');
                        $sticker = new StickerMessageBuilder(1,13);

                        $finalMsg = new MultiMessageBuilder();
                        $finalMsg->add($repMsg);
                        $finalMsg->add($sticker);

                        $result = $bot->replyMessage($event['replyToken'], $finalMsg);


                    }

                    else if(strtolower($message) == 'halo'){
                        $haloRepMessage = "Halo juga! Aku E-Chan. Bot yang menghubungkan kamu dengan pemerintah\n.";
                        $haloRepMessage .= "Cek tiga fitur kami ya~";

                        $haloRep = new TextMessageBuilder($haloRepMessage);

                        $result = $bot->replyMessage($event['replyToken'], $haloRep);

                        
                    }
                    

                    else if(strtolower($message) == 'mulai'){
                        $buttonsTemplate = file_get_contents('button_template.json');

                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    "type" => "flex",
                                    "altText" => "Test Flex Message",
                                    'contents' => json_decode($buttonsTemplate)
                                ]
                            ],
                        ]);
                    }

                    else if(strtolower($message) == 'taman indah jogja'){
                        $repMsg = new TextMessageBuilder("Terima kasih. Jawaban telah di record");
                        $result = $bot->replyMessage($event['replyToken'], $repMsg);

                    }

                    
                    else{
                        $text[] = array("type" => "text", "text" => convertion($event['message']['text']));
                        $replyMessage = new TextMessageBuilder($text[0]['text']);
                        $result = $bot->replyMessage($event['replyToken'], $replyMessage);
                    }


                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());

                }

                else if($event['message']['type'] == 'sticker')
                {
                    
    
                }
            }

            else if($event['type'] == 'follow')
            {            
                $res = $bot->getProfile($event['source']['userId']);
                if($res->isSucceeded()){
                    $profile = $res->getJSONDecodedBody();

                    //welcome message
                    $message = "Halo " . $profile['displayName'] . " ! Selamat datang di E-Chan!\n";
                    $message .= "Silahkan ketik 'Halo' untuk memulai!";

                    $welcomingText = new TextMessageBuilder($message);

                }

                else{
                    $welcomingText = new TextMessageBuilder("KAPOK");
                }
                // $host = "host = ec2-50-19-127-158.compute-1.amazonaws.com";
                // $user = "user = rpcyqnvmcpuhsc";
                // $password = "password = ed21269bb5fc5a4ca773b87eebc9bf63df0fdd5321e709c408e858c9a7bde0b9";
                // $dbname = "dbname = dbtnrb8vhn7n5e";
                // $port = "port = 5432";

                // $db = pg_connect("$host $port $dbname $user $password");

                // if($db){
                //     $psql = "INSERT INTO public.basic_users (id) VALUES ('$userID')";
                //     $ret = pg_query($db, $psql);

                //     if($ret){
                //         $welcomingText = new TextMessageBuilder('Halo! UserID anda adalah '.$userID. ' dan telah dimasukkan ke DB');
                //     }

                //     else{
                //         $welcomingText = new TextMessageBuilder('Halo aja');
                //     }

                // }

                // else{
                //     $welcomingText = new TextMessageBuilder('gagal!');
                //     error_log('executing query');
                // }


                $result = $bot->replyMessage($event['replyToken'], $welcomingText);
                return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus()); 
            }

            else if($event['type' == 'unfollow'])
            {

            }
        }
    }
});

$app->get('/pushmessage', function($req,$res) use ($bot, $httpClient)
{
    $userID = "Ubbba7ed4d1d6f6423af43d003bac3e66";
    $res = $bot->getProfile($userID);

    $buttonsTemplate = file_get_contents('button_template.json');

    $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
        'replyToken' => $event['replyToken'],
        'messages'   => [
            [
                "type" => "flex",
                "altText" => "Test Flex Message",
                'contents' => json_decode($buttonsTemplate)
            ]
        ],
    ]);

    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});



$app->get('/profile', function($req,$res) use ($bot){
    $userID = "Ue577303f7dc4a12467500de28b48ef2f";
    $result = $bot->getProfile($userID);
    
    return $res->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
});

$app->run();
