<?php

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class IndexController extends Controller
{
    protected $xml_obj;
    //
    //微信公众平台
    public function index(){
//         $res = request()->get('echostr','');
//         if($this->checkSignature() && !empty($res)){
//             echo $res;
//         }
        $this->createMenu();
        $this->responseMsg();

    }
    //配置连接
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = "index";
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            $xml_str = file_get_contents("php://input");
            file_put_contents('wx_event.log',$xml_str);
            echo "";
            die;
        }else{
            return false;
        }
    }
    public function createMenu(){
        $menu = '{
            "button": [
                        {
                            "type": "view",
                            "name": "查看天气",
                            "url": "http://mashukai.top/tianqi"
                        },
                        {
                            "name": "打卡",
                            "sub_button": [
                                {
                                    "type": "view",
                                    "name": "每日签到",
                                    "url": "http://mashukai.top/mage"
                                },
                                {
                                    "type": "click",
                                    "name": "查看积分",
                                    "key": "V1001_GOOD"
                                },
                                {
                                    "type": "view",
                                    "name": "微商城",
                                    "url": "http://msk.mashukai.top"
                                }
                            ]
                    }]
        }';
        $access_token = $this->token();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$access_token;
        $res = $this->curl($url,$menu);

    }
    public function curl($url,$menu){
        //1.关闭
        $ch = curl_init();
        //2.设置
        curl_setopt($ch,CURLOPT_URL,$url);//提交地址
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);//返回值
        curl_setopt($ch,CURLOPT_POST,1);//post提交方式
        curl_setopt($ch,CURLOPT_POSTFIELDS,$menu);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        //curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,1);
        $output = curl_exec($ch);
        //4.关闭
        curl_close($ch);
//        dump($output);
        return $output;

    }
    //处理推送事件
    public function wx(){
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = "index";
//        echo $token;die;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            $xml_str = file_get_contents("php://input");
            file_put_contents('wx_event.log',$xml_str);
            echo "";
            die;
        }else{
            return false;
        }
    }
    //获取token
    public function token(){
        $key="wx:access_token";
        $token = Redis::get($key);
        if($token){
            echo"有缓存";
        }else{
            echo"无缓存";
            $APPID="wx1d711adfd58c574a";
            $APPSECRET="b05e3cb0f8a9bd8f5aed6977fe647a39";
            $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$APPID}&secret={$APPSECRET}";
            $respon = file_get_contents($url);
            $data = json_decode($respon,true);
            $token = $data['access_token'];
            Redis::set($key,$token);//存redis
            Redis::expire($key,3600);//设置过期时间
        }
        return  $token;
    }
    public function responseMsg()
    {
        $postStr = file_get_contents("php://input");
        $postobj = simplexml_load_string($postStr);
        $this->xml_obj = $postobj;
        if ($postobj->MsgType == 'event') {
            if ($postobj->Event == 'subscribe') {
                $ToUserName = $postobj->FromUserName;
                $FromUserName = $postobj->ToUserName;
                $CreateTime = time();
                $MsgType = 'text';
                $a = [
                    "欢迎",
                    "来了老弟",
                    "什么风把你吹来了",
                    "welcome",
                ];
                $array = $a;
                $Content = $array[array_rand($array)];
                $temple = '<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[' . $Content . ']]></Content>
                         </xml>';
                $info = sprintf($temple, $ToUserName, $FromUserName, $CreateTime, $MsgType, $Content);

                echo $info;
                exit;
            }
        } else if ($postobj->MsgType == 'text') {
            //接收回复
            $msg = $postobj->Content;
            $ToUserName = $postobj->FromUserName;
            $FromUserName = $postobj->ToUserName;
            $CreateTime = time();
            $MsgType = 'text';
            switch ($msg) {
                case'命令';
                    $Content = '在吗，你是,图文';
                    break;
                case'在吗';
                    $Content = '在呢';
                    break;
                case'你是';
                    $Content = '2080';
                    break;
                case'天气';
                    $Content = 'https://devapi.qweather.com/v7/weather/now?location=101010700&key=3e53a367400347b2afce3b9692011bd7&gzip=n';
                    break;
                case'图文';
                    $Content = [
                        'Title' => '哈哈',
                        'Description' => '哈哈',
                        'PicUrl' => 'https://ss1.bdstatic.com/70cFvXSh_Q1YnxGkpoWK1HF6hhy/it/u=2583035764,1571388243&fm=26&gp=0.jpg',
                        'Url' => 'http://jd.com',
                    ];
                    $this->textimg($postobj, $Content);
                default:
                    $Content = '你可以尝试一下换个命令：比如命令';
                    break;
            }
            $temple = '<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[' . $Content . ']]></Content>
                    </xml>';
            $info = sprintf($temple, $ToUserName, $FromUserName, $CreateTime, $MsgType, $Content);
            echo $info;
            // exit;
        }
    }
    public function getuser(){
        $token = $this->token();

        $openid = $this->xml_obj->FromUserName;
        dd($openid);
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$token.'&openid='.$openid.'&lang=zh_CN';

        //请求接口
        $client = new Client();
        $response = $client->request('GET',$url,[
            'verify'    => false
        ]);
        return  json_decode($response->getBody(),true);    }



}
