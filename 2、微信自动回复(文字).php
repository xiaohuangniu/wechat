<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信自动回复(文字)
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-24 16:29:00
 + Last-time    : 2016-10-24 16:29:00 + 小黄牛
 + Desc         : 如果你还没看过第一章,请返回去先看
 +----------------------------------------------------------------------
*/

# 这里是你在微信后台 -> 基本配置 -> 服务器配置中填写的Token令牌值
define("TOKEN", "weixin");
# 实例化示例类
$wechatObj = new wechatCallbackapiTest();
# 由于数字认证已经通过,valid()与checkSignature()方法删除掉,没有作用了
# 只需要留下responseMsg方法,作为回调方法
$wechatObj->responseMsg();


class wechatCallbackapiTest {
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
            # 如果发送过来的内容不为空            
            if(!empty( $keyword )){
                
                # $contentStr 是要返回给微信显示的内容
                switch ($keyword) {
                    case '时间':
                        $contentStr = date('Y-m-d H:i:s',time());
                    break;  
                    case '你好':
                        $contentStr = 'Hello Jun!';
                    break;
                    default:
                        $contentStr = '没找到相关的自动回复内容!';
                }
                				
				# 返回给微信的字符串类型 - 具体有哪些类型可以在 微信后台 -> 开发者手册 -> 消息管理 第一章中看到
                # text 是文本类型
                $msgType = "text";
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
	
}