<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP微信开发 生成带参数的二维码 - 临时版
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-28 16:55:00
 + Last-time    : 2016-10-28 16:55:00 + 小黄牛
 + Desc         : 这个接口跟微信支付一样重要,分销功能一定会用到,并且10个微网站,8个会用到
 + content: 
 +   微信目前支持两种带参数的二维码             
 +   第一种:临时二维码
 +         有过期时间，最长可以设置为在二维码生成后的30天（即2592000秒）后过期，但能够生成较多数量;临时二维码主要用于帐号绑定等不要求二维码永久保存的业务场景
 +   第二种:永久二维码
 +         无过期时间的，但数量较少（目前为最多10万个）;永久二维码主要用于适用于帐号绑定、用户来源统计等场景,分销不用说,肯定得用这个了
 +  
 +   生成带参数的二维码的过程包括两步:
 +   第一:首先创建二维码ticket
 +   第二:凭借urlencode(ticket)到指定URL换取二维码。
 +----------------------------------------------------------------------------------------------------------------------------------------------
*/

# 注意,菜单更新有时差,为10分钟左右
# 引入Access_Token更新文件 
require_once 'Access_Token.php';
$AccessToken = new Access_Token();
# 定义Access_Token常量
define('ACCESS_TOKEN',$AccessToken->GetToken());
$WeiXin = new WeiXin();
$WeiXin->Temporary_QR();

class WeiXin{
	# 生成临时二维码
	public function Temporary_QR(){
		# 请求这个连接获得ticket
		$url  = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.ACCESS_TOKEN;
		# 设置二维码参数
		$data = array(
			'expire_seconds' => 2592000,//有效时间,秒
			'action_name'    => 'QR_SCENE',//二维码类型，QR_SCENE为临时
			# 场景参数
			'action_info'    => array(
				'scene' => array(
					'scene_id'   => 123 // 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）	
				)
			)
		);
		$result = self::https_request($url,json_encode($data));//参数一定要转成JSON
		$this->Error_Log(array('ticket参数'=>$result));//记录日志,以防报错
		$res    = json_decode($result,true);
		# $ticket存在数据库 ,要显示的时候访问下面的地址
		$ticket = $res['ticket'];
		
		# 使用ticket请求这个链接,获得二维码
		$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($ticket);
		$img = self::https_request($url);
		# 查看二维码
		echo "<img src='$url' />";
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
 
