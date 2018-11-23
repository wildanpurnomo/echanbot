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
                    $welcomingMessage = "Hai";
                }
            }

            else if($event['type'] == 'message'){

                if($event['message']['type'] == 'text'){
                    $command = substr($event['message']['text'], 0, 7);

                    if(strtolower($command) == '/sa-kun'){
                        if(strlen($event['message']['text']) <= 8 ){
                            $text1 = new TextMessageBuilder('Ingin mengirim kritik atau saran ? Ketik "/sa-kun [saran kamu]" tanpa tanda kutip');
                            $text2 = new TextMessageBuilder('Contoh : /sa-kun Sebaiknya, pembangunan jembatan segera dilakukan mengingat padatnya kendaraan');
                            $sakunRepMessage = new MultiMessageBuilder();
                            $sakunRepMessage->add($text1);
                            $sakunRepMessage->add($text2);
                            $result = $bot->replyMessage($event['replyToken'], $sakunRepMessage);
                        
                            return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                        }

                        else{
                            $textMessageBuilder = new TextMessageBuilder('Terima kasih atas masukan Anda');
                            $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);
                      
                            return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                        }
                    }

                    else if (strtolower($event['message']['text'])=='sa-kun'){
                        $image = new ImageMessageBuilder('https://image.ibb.co/csuMpq/sakunmid.png', 'https://image.ibb.co/csuMpq/sakunmid.png');
                        $text1 = new TextMessageBuilder('Sa-kun (Saran Kuy LINE) merupakan salah satu layanan yang digunakan untuk menampung kritik atau saran');
                        $text2 = new TextMessageBuilder('Ingin mengirim kritik atau saran ? Ketik "/sa-kun [saran kamu]" tanpa tanda kutip');
                        $text4 = new TextMessageBuilder('Contoh : /sa-kun Sebaiknya, pembangunan jembatan segera dilakukan mengingat padatnya kendaraan');
                        $text3 = new MultiMessageBuilder();
                        $text3->add($image);
                        $text3->add($text1);
                        $text3->add($text2);
                        $text3->add($text4);
                        
                        $result = $bot->replyMessage($event['replyToken'], $text3);
                        
                        return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                    }

                    else if (strtolower($event['message']['text'])=='su-kun'){
                        $carouselTemplateBuilder = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder([
                            new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("Survey Nama Taman Baru", "Vote untuk nama taman baru!","https://travelyuk.files.wordpress.com/2010/06/butchard.jpg",[
                            new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder('Open Survey 1',"Open Survey 1","Open survey 1"),
                            ]),
                            new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("Survey Kebersihan Selokan Mataram", "Rate kebersihan Selokan Mataram!","https://s.kaskus.id/images/2015/06/20/7853087_20150620063627.jpg",[
                            new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder('Open Survey 2',"http://hilite.me/"),
                            ]),
                            ]);
                        $templateMessage = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder('Carousel Template',$carouselTemplateBuilder);
                        $text1 = new TextMessageBuilder('Untuk informasi lebih, buka  line://app/1622788685-PMKG0YeB');
                        $text3 = new MultiMessageBuilder();
                        $text3->add($text1);
                        $text3->add($templateMessage);
                        $result = $bot->replyMessage($event['replyToken'], $text3);

                        return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                    }

                    else if(strtolower($event['message']['text']) == '/list'){
                        $carouselTemplateBuilder = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder([
                            new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("Survey Nama Taman Baru", "Vote untuk nama taman baru!","https://travelyuk.files.wordpress.com/2010/06/butchard.jpg",[
                            new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder('Open Survey 1',"Open Survey 1", "Open Survey 1"),
                            ]),
                            new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder("Survey Kebersihan Selokan Mataram", "Rate kebersihan Selokan Mataram!","https://s.kaskus.id/images/2015/06/20/7853087_20150620063627.jpg",[
                            new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder('Open Survey 2',"http://hilite.me/"),
                            ]),
                            ]);
                        $templateMessage = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder('Carousel Template',$carouselTemplateBuilder);
                        $result = $bot->replyMessage($event['replyToken'], $templateMessage);

                        return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                    }  
                }
            }

            else if($event['type'] == 'postback'){
                if(strtolower($event['postback']['data']) == 'open survey 1'){
                    $flexSurvey1Template = file_get_contents('survey_1_template.json');

                    $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                        'replyToken' => $event['replyToken'],
                        'messages' => [
                            [
                                    "type" => "flex",
                                    "altText" => "Test Flex Message",
                                    "contents" => json_decode($flexSurvey1Template)
                            ]
                    
                        ],
                    ]);


                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());
                }
                
                else{
                    $repMessage = new TextMessageBuilder("Terima kasih atas partisipasinya. Pesan telah disimpan di database kami ^^");
                    $result = $bot->replyMessage($event['replyToken'], $repMessage);

                    return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus());

                }
            }

            else if($event['type'] == 'unfollow'){

            }
        }
    }
});

$app->get('/pushsikun', function($req, $res) use ($bot, $httpClient)
{

});

$app->run();
