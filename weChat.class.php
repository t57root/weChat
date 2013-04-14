<?php
class weChat{
    var $client;
    var $cookies;
    var $token;
    var $tmpPath;

    function weChat($tmpPath){
        $this->client = new HttpClient('mp.weixin.qq.com');
        $this->tmpPath = $tmpPath;
    }

    function checkLogin(){
        $client = $this->client;
        $client->get('/cgi-bin/indexpage?t=wxm-index&lang=zh_CN&token='.$this->token);
        if(strpos($client->getContent(),ACCOUNT_NAME)){
            return true;
        }
        return false;
    }

    function doLogin(){
        $client = $this->client;
        $cookies_file = $this->tmpPath.'/mp.cookies';
        $token_file = $this->tmpPath.'/mp.weixin.token';
        if(file_exists($cookies_file) and file_exists($token_file)){
            $this->cookies = unserialize(file_get_contents($cookies_file));
            $this->token = file_get_contents($token_file);
        }
        if($this->checkLogin()) return true;
        $client->referer = 'http://mp.weixin.qq.com/cgi-bin/indexpage?t=wxm-index&lang=zh_CN&token='.$this->token;
        $client->setCookies($this->cookies);
        $client->post('/cgi-bin/login?lang=zh_CN', array(
            'username' => USERNAME,
            'pwd' => PASSWORD,
            'imgcode' => '',
            'f' => 'json',
        ));

        $content = $client->getContent();
        $info = json_decode($content,1);

        if($info['ErrCode']!=0){
            //XXX Log the $info['ErrMsg'];
            return false;
        }
        $uri = $info['ErrMsg'];
        preg_match('/token=([^&]+)/', $uri, $matches);     
                                   
        if (!$client->get($uri)) { 
            //XXX Log the error: An error occurred: '.$client->getError()
            return false;
        }                          

        $this->cookies = $client->getCookies();
        $this->token = $matches[1];      
        file_put_contents($cookies_file,serialize($this->cookies));
        file_put_contents($token_file,$this->token);
        return true;
    }

    function updateFakeid(){
        $client = $this->client;
        $client->get("/cgi-bin/contactmanagepage?t=wxm-friend&lang=zh_CN&pagesize=10&pageidx=0&type=0&groupid=0&token=".$this->token);
        $content = str_replace(array("\r","\n"),"",$client->getContent());
        preg_match('#<script id="json-friendList" type="json/text">(.+?)</script>#',$content,$matches);
        $users = json_decode($matches[1],1);
        print_r($users);
        //Update to the database
    }

    function getUserByFakeid($fakeid){
        $client = $this->client;
        $client->referer = 'http://mp.weixin.qq.com/cgi-bin/contactmanagepage?t=wxm-friend&lang=zh_CN&pagesize=10&pageidx=0&type=0&groupid=0&token='.$this->token;
        $client->post('/cgi-bin/getcontactinfo?t=ajax-getcontactinfo&lang=zh_CN&fakeid='.$fakeid, array(
            'ajax' => 1,
            'token' => $this->token,
        ));
        print $client->getContent();
    }

    function sendMsg($fakeid,$msg){
        $client = $this->client;
        $client->referer = 'http://mp.weixin.qq.com/cgi-bin/contactmanagepage?t=wxm-friend&lang=zh_CN&pagesize=10&pageidx=0&type=0&groupid=0&token='.$this->token;
        $client->post('/cgi-bin/singlesend?t=ajax-response&lang=zh_CN', array(
            'content' => $msg,
            'type' => '1',
            'ajax' => '1',
            'error' => 'false',
            'tofakeid' => $fakeid,
            'token' => $this->token,
        ));
        $result = json_decode($client->getContent(),1);
        if($result['ret']==0)
            return true;
        return false;
    }
}
