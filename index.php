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
        $host = "host = ec2-23-21-201-12.compute-1.amazonaws.com";
        $user = "user = bqgtvmqhgoocpi";
        $password = "password = 2302bdf02990ee73a5a3f718a2b0ca280f65f27f62ef67e6b3850e05c1eceb37";
        $dbname = "dbname = df4d3civge3daf";
        $port = "port = 5432";

        $db = pg_connect("$host $port $dbname $user $password");

        foreach($data['events'] as $event){
            if($event['type'] == 'follow'){
                $res = $bot->getProfile($event['source']['userId']);

                if($res->isSucceeded()){
                    $profile = $res->getJSONDecodedBody();
                    $userId = $profile['userId'];
                    $displayName = $profile['displayName'];

                    //retrieve user data into DB
                    $psql = "INSERT INTO public.users_info(userid, displayName, timestamp) VALUES ('$userId','$displayName',CURRENT_TIMESTAMP)";
                    $ret = pg_query($db, $psql);

                    if($ret){
                        //welcoming message
                        $message1 = new TextMessageBuilder("Halo " . $displayName . " ! Selamat datang di E-Chan!\n");
                        $image = new ImageMessageBuilder("https://image.ibb.co/dEkLFV/sasisu.png","https://image.ibb.co/dEkLFV/sasisu.png");
                        $message2 = new TextMessageBuilder("Aku punya 3 fitur : Sa-kun untuk memberi saran, Su-kun untuk mengisi kuisioner dan Si-kun untuk memberikan informasi menarik");

                        $welcomingText = new MultiMessageBuilder();
                        $welcomingText->add($message1);
                        $welcomingText->add($image);
                        $welcomingText->add($message2);
                    }

                    else{
                        $welcomingText = new TextMessageBuilder("Halo " . $displayName . " ! Selamat datang di E-Chan! Sayangnya database sedang error...");
                    }


                }

                $result = $bot->replyMessage($event['replyToken'], $welcomingText);
                return $response->withJson($result->getJSONDecodedBody(), $result->getHTTPStatus()); 
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

                else if(strtolower($event['postback']['data']) == 'open survey 2'){
                    $flexSurvey2Template = file_get_contents('survey_2_template.json');

                    $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                        'replyToken' => $event['replyToken'],
                        'messages' => [
                            [
                                    "type" => "flex",
                                    "altText" => "Test Flex Message",
                                    "contents" => json_decode($flexSurvey2Template)
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
                $res = $bot->getProfile($event['source']['userId']);

                if($res->isSucceeded()){
                    $profile = $res->getJSONDecodedBody();
                    $userId = $profile['userId'];

                    //retrieve user data into DB
                    $psql = "DELETE FROM public.users_info WHERE userid = '$userId'";
                    $ret = pg_query($db, $psql);
                }
            }
        }
    }
});

$app->get('/pushsikun', function($req, $res) use ($bot, $httpClient)
{

});

$app->run();
