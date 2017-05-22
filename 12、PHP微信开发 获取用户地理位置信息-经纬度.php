<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP微信开发 获取用户地理位置信息-经纬度
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-28 16:41:00
 + Last-time    : 2016-10-28 16:41:00 + 小黄牛
 + Desc         : 这个接口比较坑,要求手机一直开着GPS,不然就获取不到,所以这个接口意义应该不大
 +----------------------------------------------------------------------
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
				# 地理位置事件消息 - 大写
				case 'LOCATION':
				   # 调用日志记录-方便出错则查看
					$this->Error_Log(array(
						'纬度' => $postObj->Latitude,
						'经度' => $postObj->Longitude,
						'精度' => $postObj->Precision
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