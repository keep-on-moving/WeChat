<?php

$testObj = new Test();

if(!empty($_GET['echostr'])){

    $testObj->valid();

}else{

    $testObj->responseMsg();
}

exit;

class Test
{
    /**
     * 绑定url、token信息
     */
    public function valid(){
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            ob_clean();
            echo $echoStr;
        }
        exit();
    }
    /**
     * 检查签名，确保请求是从微信发过来的
     */
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = "******";//与在微信配置的token一致，不可泄露
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg(){

        //验证签名
        if ($this->checkSignature()){
            $postArr = $GLOBALS["HTTP_RAW_POST_DATA"];
            $postObj = simplexml_load_string( $postArr );

            /****************************************************
             *                关注事件触发的推送                   *
             * **************************************************
             */
            if( strtolower( $postObj->MsgType ) == 'event' ){
                if( strtolower( $postObj->Event ) == 'subscribe' ){
                    $toUser   = $postObj->FromUserName;
                    $fromUser = $postObj->ToUserName;
                    $time     = time();
                    $msgType  = 'text';
                    $content  = '您好，欢迎关注：【踏凌霄】'."\n".'目前支持功能：'."\n".'【1】汉译英：（回复）翻译一+内容'."\n".'【2】英译汉：（回复）翻译二+内容'."\n".'【3】回复（微信开发）查看关于微信开发的博文'."\n".'【4】天气：（回复）天气+地区（拼音）例如（天气baoding）'."\n"."\n"."踏南天，碎凌霄!么么哒！鸡年大吉！";
                    $info = $this->txtFormatForXml($toUser, $fromUser, $time, $msgType, $content);
                    echo $info;
                }

		//点击事件触发
                if( strtolower( $postObj->Event ) == 'click' ){
                    $toUser   = $postObj->FromUserName;
                    $fromUser = $postObj->ToUserName;
                    $time     = time();
                    $msgType  = 'text';
		    
                    $content  = $postObj->EventKey;
                    $info = $this->txtFormatForXml($toUser, $fromUser, $time, $msgType, $content);
                    echo $info;
                }

		//扫码事件触发
                if( strtolower( $postObj->Event ) == 'scancode_waitmsg' ){
                    $toUser   = $postObj->FromUserName;
                    $fromUser = $postObj->ToUserName;
                    $time     = time();
                    $msgType  = 'text';
		    
                    $content  = '扫描信息'."\n".$postObj->ScanCodeInfo."\n".'扫描结果'."\n".$postObj->ScanResult;
                    $info = $this->txtFormatForXml($toUser, $fromUser, $time, $msgType, $content);
                    echo $info;
                }


            }

            /****************************************************
             *                回复纯文本                          *
             * **************************************************
             */

            $content = $postObj->Content;
            if(strstr($content, '翻译一')){  //翻译一  汉译英
                $words = explode('翻译一',$content);
                $translateWord = $words[1];
                $to = 'en';
                $from = 'zh';
                $content = '翻译';
            }
            if(strstr($content, '翻译二')){  //翻译二  英译汉
                $words = explode('翻译二',$content);
                $translateWord = $words[1];
                $from = 'en';
                $to = 'zh';
                $content = '翻译';
            }

            if(strstr($content, '天气')){  //天气beijing
                $words = explode('天气',$content);
                $area = $words[1];
                $content = '天气';
            }

            if( strtolower( $postObj->MsgType ) == 'text'){
                switch ( $content ) {
		    case '天气':
                        $content = $this->xinzhiWeather($area);
                        break;
                    case '翻译':
                        $content = $this->baiduTranslateWord($translateWord, $from, $to);
                        break;
                    case '菜单':
                        $content = '正在开发中……敬请期待！';
                        break;
                    case 'acfun':
                        $content =  "<a href='http://www.acfun.cn'>( ⊙ o ⊙ )！活捉A站基佬</a>";
                        break;
                    case '微信开发':
                        //图文回复
                        $content=array(
                            array(
                                'title' => '微信公众号发送红包',
                                'description' => '微信红包准备条件，以及发送红包的实例',
                                'picUrl' => 'http://chuantu.biz/t5/120/1498705137x2890171516.jpg',
                                'url' => 'http://blog.csdn.net/qq_31617637/article/details/71972281'
                            ),
                            array(
                                'title' => '微信扫码',
                                'description' => '微信扫码',
                                'picUrl' => 'http://img.zcool.cn/community/0129a857b171280000018c1b8ceb7e.png',
                                'url' => 'http://blog.csdn.net/qq_31617637/article/details/72529078'
                            ),
                            array(
                                'title' => '微信获取openID',
                                'description' => '微信获取openID',
                                'picUrl' => 'http://img.zcool.cn/community/01f18057b171260000012e7ebc0737.png',
                                'url' => 'http://blog.csdn.net/qq_31617637/article/details/71438882'
                            ),
                            array(
                                'title' => '判断是否在微信浏览器打开',
                                'description' => '判断是否在微信浏览器打开',
                                'picUrl' => 'http://img.zcool.cn/community/01d29557b171270000012e7ebd6427.png',
                                'url' => 'http://blog.csdn.net/qq_31617637/article/details/72621263'
                            ),
                            array(
                                'title' => '【微信公众号开发】自我学习第一章：服务器配置的提交',
                                'description' => '【微信公众号开发】自我学习第一章：服务器配置的提交',
                                'picUrl' => 'http://img.zcool.cn/community/01d23757b171260000018c1b4e0f9d.png',
                                'url' => 'http://blog.csdn.net/qq_31617637/article/details/73604609'
                            )
                        );
                        break;
                    case '点歌':
                        //点歌回复
                        $content = 'http://music.163.com/#/song?id=38592976&market=baiduqk';
                        break;
                    default:
                        $content = $postObj->Content;
                        break;
                }
                $toUser   = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
                $time     = time();
                $msgType  = 'text';

                if(is_array($content)){
                    $info = $this->picFormatForXml($toUser, $fromUser, $time, $content);
                } else if($postObj->Content == '点歌') {
                    $info = $this->musicFormatForXml($toUser, $fromUser, $time, 'dream it possible', $content);
                } else {
                    $info = $this->txtFormatForXml($toUser, $fromUser, $time, $msgType, $content);
                }

                echo $info;
            }
        }

    }

    /*
     * 心知天气API
     * */
    private function xinzhiWeather($area) {
	$key ='fgqq1twwdlnlmnud';
        $temperatureUrl = 'https://api.seniverse.com/v3/weather/now.json?key='.$key.'&location='.$area.'&language=zh-Hans&unit=c';
        $lifeUrl = 'https://api.seniverse.com/v3/life/suggestion.json?key='.$key.'&location='.$area.'&language=zh-Hans';

        $data = file_get_contents($temperatureUrl);
        $lifeData = file_get_contents($lifeUrl);

        $data = json_decode($data, true);
        $lifeData = json_decode($lifeData, true);

        $str = '【天气情况以及6项生活指数报告】'."\n";
	$str .= '【地址】：%s'."\n";
	$str .= '【详细地址】：%s'."\n";
        $str .= '【时间】：%s'."\n";
        $str .= '【天气】：%s'."\n";
        $str .= '【温度】：%s度'."\n";
        $str .= '【洗车】：%s'."\n";
        $str .= '【穿衣】：%s'."\n";
        $str .= '【感冒】：%s'."\n";
        $str .= '【运动】：%s'."\n";
        $str .= '【旅游】：%s'."\n";
        $str .= '【紫外线】：%s'."\n";
	$str .= '谢谢支持！么么哒';

	if($data['results'][0]['now'] && $lifeData['results'][0]['suggestion']){
		$lifeDataParam = $lifeData['results'][0]['suggestion'];
		$info = sprintf(
		    $str,
		    $data['results'][0]['location']['name'],
		    $data['results'][0]['location']['path'],
		    date_format(new \DateTime(),'Y-m-d h:m:s'),
		    $data['results'][0]['now']['text'],
		    $data['results'][0]['now']['temperature'],
		    $lifeDataParam['car_washing']['brief'],
		    $lifeDataParam['dressing']['brief'],
		    $lifeDataParam['flu']['brief'],
		    $lifeDataParam['sport']['brief'],
		    $lifeDataParam['travel']['brief'],
		    $lifeDataParam['uv']['brief']
		);
	}else{
		$info = '该城市不适合我们试用的用户！╮(╯▽╰)╭';
	}

        return $info;
    }

    private function baiduTranslateWord($translateWord, $from, $to) {
        $appid = '20170629000060917';
        $appsecret = 'GVMG8aZHPWyE6ACIil6A';
        $salt = rand(1000000000,999999999);
        $sign = md5($appid.$translateWord.$salt.$appsecret);

        $url = 'http://api.fanyi.baidu.com/api/trans/vip/translate?q='.$translateWord.'&from='.$from.'&to='.$to.'&appid='.$appid.'&salt='.$salt.'&sign='.$sign;
        $content = file_get_contents($url);
        $data = json_decode($content, true);
        if($data['trans_result'][0]['dst']){
            return $data['trans_result'][0]['dst'];
        }else{
            return '请求返回太慢了，请重复请求';
        }
    }

    /*
     * 纯文本模板解析
     * */
    private function txtFormatForXml($toUser, $fromUser, $time, $msgType, $content ) {
        $template = "<xml>
                       <ToUserName><![CDATA[%s]]></ToUserName>
                       <FromUserName><![CDATA[%s]]></FromUserName>
                       <CreateTime>%s</CreateTime>
                       <MsgType><![CDATA[%s]]></MsgType>
                       <Content><![CDATA[%s]]></Content>
		       <FuncFlag>0</FuncFlag>
                      </xml>";
        $info     = sprintf( $template, $toUser, $fromUser, $time, $msgType, $content );
        return $info;
    }

    /*
     * 音乐模板解析
     * */
    private function musicFormatForXml($toUser, $fromUser, $time, $title, $musicUrl ) {
        $template = "<xml>
				 <ToUserName><![CDATA[%s]]></ToUserName>
				 <FromUserName><![CDATA[%s]]></FromUserName>
				 <CreateTime><![CDATA[%s]]></CreateTime>
				 <MsgType><![CDATA[music]]></MsgType>
				 <Music>
				 <Title><![CDATA[%s]]></Title>
				 <Description><![CDATA[%s]]></Description>
				 <MusicUrl><![CDATA[%s]]></MusicUrl>
				 <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
				 </Music>
				 <FuncFlag>0</FuncFlag>
				 </xml>";
        $info     = sprintf( $template, $toUser, $fromUser, $time, $title, 'HUAWEI', $musicUrl, $musicUrl );
        return $info;
    }

    /*
     * 多图文模板解析
     * $msgType 为news
     *
     * */
    private function picFormatForXml($toUser, $fromUser, $time, $content) {
        $template = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <ArticleCount>".count($content)."</ArticleCount>
                        <Articles>";

        foreach ($content as $key => $value){
            $template .= "<item>  
                            <Title><![CDATA[".$value['title']."]]></Title> 
                            <Description><![CDATA[".$value['description']."]]></Description> 
                            <PicUrl><![CDATA[".$value['picUrl']."]]></PicUrl>
                            <Url><![CDATA[".$value['url']."]]></Url>
                          </item>";
        }
        $template  .=   "</Articles>
                <FuncFlag>0</FuncFlag>
                </xml>";

        $info = sprintf( $template, $toUser, $fromUser, $time, 'news');
        return $info;
    }

}

?>
