<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP微信开发 扫描二维码推送事件
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-29 09:13:00
 + Last-time    : 2016-10-29 09:13:00 + 小黄牛
 + Desc         : 这个接口跟微信支付一样重要,分销功能一定会用到,并且10个微网站,8个会用到
 + content: 
 +   用户扫描带参数的二维码时，可能推送以下两种事件:
 +   第一种:如果用户还未关注公众号，则用户可以关注公众号，关注后微信会将带场景值关注事件推送给开发者。      
 +   第二种:如果用户已经关注公众号，在用户扫描后会自动进入会话，微信也会将带场景值扫描事件推送给开发者。
 +
 + 用户未关注时，进行关注后的事件推送
 + <EventKey> qrscene_ 为前缀,后面为二维码的参数值
 + <Ticket> 二维码的ticket
 + 
 +
 + 用户已关注时的事件推送
 + <ToUserName> 开发者的微信公众号
 + <FromUserName> 发送者的Openid
 + <CreateTime> 时间戳
 + <MsgType> 消息类型 event
 + <Event> 事件类型 SCAN
 + <EventKey> 创建二维码时的scene_id
 + <Ticket> 二维码的ticket
 +----------------------------------------------------------------------------------------------------------------------------------------------
*/

# 实例化示例类
$wechatObj = new wechatCallbackapiTest();
# 由于数字认证已经通过,valid()与checkSignature()方法删除掉,没有作用了
# 只需要留下responseMsg方法,作为回调方法
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
            # rx_type      : 请求类型
            $rx_type = trim($postObj->MsgType);
            # rx_event     : 操作的事件
            $rx_event = $postObj->Event;

			switch ($rx_event){
				# 关注
				case 'SUBSCRIBE':
				   # 调用日志记录-方便出错则查看
					$this->Error_Log(array(
						'类型' => '成功关注'
					));
				break;
				# 扫描二维码事件
				case 'SCAN':
				   # 调用日志记录-方便出错则查看
					$this->Error_Log(array(
						'类型' => $rx_event,
						'scene_id' => $postObj->EventKey
					),false);
				break;
			}

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