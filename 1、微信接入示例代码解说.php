<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信接入示例代码解说
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-24 15:26:00
 + Last-time    : 2016-10-24 15:26:00 + 小黄牛
 + Desc         : 如果你是刚刚接触微信,并准备自力更生,可以看下这篇文章
 +----------------------------------------------------------------------
*/

# 这里是你在微信后台 -> 基本配置 -> 服务器配置中填写的Token令牌值
define("TOKEN", "weixin");
# 实例化示例类
$wechatObj = new wechatCallbackapiTest();
# 调用API数字认证方法 - 第一次绑定服务器地址的时候用到,绑定成功之后就不需要再用了
$wechatObj->valid();


class wechatCallbackapiTest {
	
    // 处理数字验证,并返回认证结果给微信
	public function valid(){
        # 接收微信向你服务器发送过来的随机字符串
        $echoStr = $_GET["echostr"];
        # 执行checkSignature , 进行数字认证
        if($this->checkSignature()){
            # 认证通过返回随机字符串给微信,告诉它认证通过了
            echo $echoStr;
            exit;
        }
    }

    // 第一次绑定服务器地址的时候,用不到这个方法
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
                # 如果发送过来的内容不给控            
                if(!empty( $keyword )){
                    # 返回给微信的字符串类型
              	    $msgType = "text";
                    # 要返回给微信显示的内容
                    $contentStr = "Welcome to wechat world!";
                    /**
                     * ---------这里有些重要----------
                     * 使用sprintf 向上面的$textTpl填充变量
                     * ToUserName   = $fromUsername
                     * FromUserName = $toUsername
                     * ……
                     * 对应顺序位置
                    */
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    # 填充完成之后,输出回给微信
                    echo $resultStr;
                }else{
                    echo "Input something...";
                }

        }else {
            echo "";
            exit;
        }
    }
		
    // 执行数字验证,只有在第一次绑定服务器地址的时候用到,绑定完就可以删除了
	private function checkSignature(){
        # Token令牌 常量没设置,返回错误信息
        if (!defined("TOKEN")) {
            throw new Exception('TOKEN is not defined!');
        }
        # 获得微信发送过来的加密签名
        $signature = $_GET["signature"];
        # 时间戳
        $timestamp = $_GET["timestamp"];
        # 随机数
        $nonce = $_GET["nonce"];
        
        # 获得Token令牌		
        $token = TOKEN;
        # Token + 时间戳 + 随机数 = 组合成数组
        $tmpArr = array($token, $timestamp, $nonce);
        // 对数组进行升序重新排序
        sort($tmpArr, SORT_STRING);
        # 把数组for拼接成字符串
        $tmpStr = implode( $tmpArr );
        # 进行sha1加密
        $tmpStr = sha1( $tmpStr );
		
        # 最后与微信发送过来的加密签名进行对比,成功返回true
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}