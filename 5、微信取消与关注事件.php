<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信关注事件与取消订阅事件
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-24 17:43:00
 + Last-time    : 2016-10-25 16:41:00 + 小黄牛
 + Desc         : 好好学习微信,这是5K迈向8K的关键技能
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
            # rx_type      : 请求类型
            $rx_type = trim($postObj->MsgType);
            # rx_event     : 操作的事件
            $rx_event = $postObj->Event;
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

            # 事件类型
            if($rx_type == 'event'){
                switch ($rx_event){
                    # 关注事件
                    case 'subscribe':
                        $contentStr = '感谢您关注【JunPHP】'."\n".'更多内容，敬请期待...';
                        # 返回类型			   
                        $msgType = 'text';
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType,$contentStr);
                        # 填充完成之后,输出回给微信
                        echo $resultStr;
                    break;
                    # 取消关注
                case 'unsubscribe':
                        # 取消关注将无法发送消息给对方,这个事件一般只能进行一些数据删除处理
                        $resultStr = '';
                    break;
                }
            }
            # 调用日志记录-方便出错则查看
            $this->Error_Log(array(
                'OpenID'    => $fromUsername,
                '开发者ID'   => $toUsername,
                '发送内容'   => $keyword,
                '请求类型'   => $rx_type,
                '操作事件'   => $rx_event,
                'XML'       => $resultStr
            ),false);
        }else {
            echo "";
            exit;
        }
    }
	
	/**
     * @Title  : 记录错误信息与查看部分信息
     * @Author : 小黄牛
     * @param array  : $Arr_Title  一个一维数组自定义内容
     * @param bool   : $Arr_Error  是否插入系统错误信息
     * @param string : $File       日志名
     * @return : 无
    */
    private function Error_Log($Arr_Title,$Arr_Error=false,$File='Error_log.log'){
        # 不是数组中断程序
        if (!is_array($Arr_Title)) {return false;}
        # 定义一个空的变量,用于存放日志TXT实体
        $Error_TXT = "自定义信息如下：\r\n";
        # 解析Arr_Title 自定义日志内容
        foreach ($Arr_Title as $key=>$val){
            $Error_TXT .= $key.'：'.$val."\r\n";
        }
		
        # 判断系统错误显示是否开启
        if ($Arr_Error === true) {
            # 获取刚发生的错误信息，并返回数组，无错返回null
            $Arr_Error = error_get_last();
            # 不为空则执行错误解析
            if (isset($Arr_Error)) {
                $Error_TXT .= "系统错误信息如下：\r\n";
                # 解析$Arr_Errore 系统错误信息
                foreach ($Arr_Title as $key=>$val){
                    $Error_TXT .= $key.'：'.$val."\r\n";
                }
            }
        }
		
        # 最后再写入两个换行符,以便追加查看
        $Error_TXT .= "\r\n\r\n";
        # 最后写入日志
        error_log($Error_TXT,3,$File);
    }
}