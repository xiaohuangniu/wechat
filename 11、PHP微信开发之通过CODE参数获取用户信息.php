<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP微信开发 通过CODE参数获取用户信息
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-28 11:20:00
 + Last-time    : 2016-10-28 11:20:00 + 小黄牛
 + Desc         : 微网站开发的章节很重要
 +----------------------------------------------------------------------
*/
 
# 实例化
$Test = new wechatCallbackapiTest();
$Test->responseMsg();
	
class wechatCallbackapiTest {
    
    public function responseMsg() {
		$APPID  = '';//APPID
		$SECRET = '';//APP SECRET
        $CODE   = '';//CODE
		# 用code去换取网页Access_Token
		$URL    = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$APPID&secret=$SECRET&code=$CODE&grant_type=authorization_code";
		$request= self::https_request($URL);
		$this->Error_Log(array('换取到的TOKEN信息:',$request));
		
		# 用access_token和openid去换取用户信息
		$res = json_decode($request,true);
		$access_token = $res['access_token'];
		$openid = $res['openid'];
		$URL = "https://api.weixin.qq.com/sns/userinfo?access_token=$access_token&openid=$openid";
		$request= self::https_request($URL);
		$this->Error_Log(array('换取到的用户信息:',$request));
    }
	
	# 作者:焰哥 - 用于微信接口数据传输的万能函数
	private static function https_request($url, $data = null){
		# 初始化一个cURL会话
		$curl = curl_init();  
		
		//设置请求选项, 包括具体的url
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);  //禁用后cURL将终止从服务端进行验证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		
		if (!empty($data)){
			curl_setopt($curl, CURLOPT_POST, 1);  //设置为post请求类型
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);  //设置具体的post数据
		}
		
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);		
		$response = curl_exec($curl);  //执行一个cURL会话并且获取相关回复
		curl_close($curl);  //释放cURL句柄,关闭一个cURL会话
		
		return $response;
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

/**
 * 最后分享下在微网站开发中,获取code的心得(我也是在Q群朋友的帮助下学会的)
 * 将oauth2.0认证页面设置在index.php,获取openid写入cookie
 * 根据openid去数据库查找会员信息,没有则新建会员
 * 其实这个作用,就是微网站的会员默认登录注册作用
*/