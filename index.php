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
$channel_access_token = "UK2gwmP/EX4gIv+BU0XD7u3gHFzt4awqvfIoPJINFQydBwNcYGpOqm14NhoHvCNFLM/Tur0WzCuLhpnh8qdxBe6f+13ghECCNyPP/oAYLzVrw9QlI5aGZ9lz7F8cTv06ZrP8i3esZMcM/RFw30p6XAdB04t89/1O/w1cDnyilFU=";
$channel_secret = "6db5b7f01c4bbff2b2082e2ba6f30b55";

//inisiasi bot object
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$configs = ['settings' => ['displayErrorDetails' => true],];
$app = new Slim\App($configs);

//route url homepage
$app->get('/', function($req, $res){
    echo "Hello World!";
});

//route webhook bot (controller)
$app->post('/webhook', function ($request, $response) use ($bot, $pass_signature, $httpClient){
    //get request body and line signature header
    $body = file_get_contents('php://input');
    $signature = isset($_SERVER['HTTP_X_LINE_SIGNATURE']) ? $_SERVER['HTTP_X_LINE_SIGNATURE'] : '';

    //log body and signature
    file_put_contents('php://stderr', 'Body: ', $body);

    if($pass_signature === false){
        //check line signature in req header
        if(empty($signature)){
            return $response->withStatus(400, 'Signature not set');
        }

        //check line request
        if(!SignatureValidator::validateSignature($body, $channel_secret, $signature)){
            return $response->withStatus(400, 'Request not from LINE');
        }
    }

    $data = json_decode($body, true);
    if(is_array($data['events'])){
        // $host = "host = ec2-54-246-85-234.eu-west-1.compute.amazonaws.com";
        // $user = "user = iwyxaotzpdcwxp";
        // $password = "password = 94094cc3d5a2e480287f8f0a11fbc45e03685dae62b0da058f6dd44069be0bb8";
        // $dbname = "dbname = d320e4j15u7oe9";
        // $port = "port = 5432";

        // $db = pg_connect("$host $port $dbname $user $password");

        foreach($data['events'] as $event){
            if($event['type'] == 'follow'){
                $res = $bot->getProfile($event['source']['userId']);

                if($res->isSucceeded()){
                    $profile = $res->getJSONDecodedBody();

                    //retrieve user data into DB
                    $psql = "";
                    //$ret = pg_query($db, $psql);

                    
                    //welcoming message
                    //$welcomingMessage = "Hai"
                }
            }

            else if($event['type'] == 'message'){

                if($event['message']['type'] == 'text'){
                $repMessage = new TextMessageBuilder("hai");

                $result = $bot->replyMessage($event['replyToken'], $repMessage);

                return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                }


            }

            else if($event['type'] == 'unfollow'){

            }
        }
    }


});

$app->run();
