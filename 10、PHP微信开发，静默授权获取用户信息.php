1、进入微信测试号，修改网页授权获取用户基本信息为www.junphp.com
2、在根目录新建一个oauth2.php文件，写入以下代码


<?php
if (isset($_GET['code'])){
    echo $_GET['code'];
}else{
    echo "NO CODE";
}

3、在微信认证文件,写入以下代码,在微信回复文字,获取连接,点击测试code值



<?php
/*
 +----------------------------------------------------------------------
 + Title        : 获得用户CODE参数
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-28 10:04:00
 + Last-time    : 2016-10-28 10:04:00 + 小黄牛
 + Desc         : 微网站部分最重要
 +----------------------------------------------------------------------
*/
 
# 实例化示例类
$wechatObj = new wechatCallbackapiTest();
$wechatObj->responseMsg();
 
 
class wechatCallbackapiTest {
    public function responseMsg() {
        # 获得数据包的信息
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        
        # 如果数据包内的信息不为空
        if (!empty($postStr)){
            # XML文件的解析依赖libxml库,libxml_disable_entity_loader函数,是为了安全性,防止入侵者通过协议注入XML向服务器发起攻击
            libxml_disable_entity_loader(true);
            # 把XML编译成一个Class
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            # FromUserName : 发送方帐号（一个OpenID）
            $fromUsername = $postObj->FromUserName;
            # toUsername   : 开发者微信号
            $toUsername = $postObj->ToUserName;
            # keyword      : 发送过来的内容
            $keyword = trim($postObj->Content);
            # 服务器时间戳
            $time = time();
            # 这里的XML格式不能改变,是微信规定的格式,里面的参数使用[%s]占位符占用,到时候填充完成,再发送回给微信
            $textTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Content><![CDATA[%s]]></Content>
                        <FuncFlag>0</FuncFlag>
                        </xml>"; 
            # 如果发送过来的内容不为空            
            if(!empty( $keyword )){
                $APPID = ''; //APPID
                $URL   = urlencode('http://www.junphp.com/oauth2.php');	//回调地址
                $SCOPEN= 'snsapi_userinfo';//两种模式之一
                $contentStr = "OAuth2.0网页授权演示 <a href='https://open.weixin.qq.com/connect/oauth2/authorize?appid=$APPID&redirect_uri=$URL&response_type=code&scope=$SCOPEN&state=1#wechat_redirect'>点击这里体验</a>超级黄牛";//这里的参数位置顺序不能改变,点击这个链接就能通过回调获得CODE值
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                # 填充完成之后,输出回给微信
                echo $resultStr;
            }
            
        }
    }
    
}


