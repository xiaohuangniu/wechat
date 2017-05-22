<?php
/*
 +----------------------------------------------------------------------
 + Title        : 车险新流程 - 接口基类
 + Author       : 冯俊豪
 + Version      : V1.0.0.1
 + Initial-Time : 2017-02-22 11:42:00
 + Last-time    : 时间 + 修改人
 + Desc         : 车险新流程的所有接口调用，都将统一引用改基类文件
 +----------------------------------------------------------------------
*/

# 基类
class FhApi{
	private static $ApiId  = '';// 接口Sign的Channelld参数
	private static $ApiUrl = 'http://cmchannel.uat.52zzb.com/chn/';// 统一接口根地址
	public $ApiBody = null;// 签名内容Body部分，未进行Json化
	public $ApiUrl_Key = '';// API局部提交地址，不带/符号

	/*
	 * Title : Sign签名加密
	 * Author: 冯俊豪
	 * Last  : 20170222 :
	 * Return: 加密签名结果
	*/
	private function Sign(){
		return md5(json_encode($this->ApiBody) . self::ApiUrl）;
	}

	/*
	 * Title : 万能传输接口，需开启CURL功能，无需指定POST或GET模式
	 * Author: 冯俊豪
	 * Last  : 2017-02-22 11:42:00 +
	 * Return: 返回请求内容
	*/
	public static function https_request(){
		# 初始化一个cURL会话
		$curl = curl_init();
		# 自定义请求头
		$header = array();
		$header[] = 'channelld:'.self::ApiId;
		$hedaer[] = 'sign:'.$this->Sign();
		# 加入自定义请求头
		curl_setopt($curl, CURLOPT_POSTFIELDS , $header);

		# 设置请求选项, 包括具体的url
		curl_setopt($curl, CURLOPT_URL, self::ApiUrl . self::ApiUrl_Key . '/');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);// 禁用后cURL将终止从服务端进行验证
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		
		if (!empty($this->ApiBody)){
			curl_setopt($curl, CURLOPT_POST, 1);// 设置为post请求类型
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($this->ApiBody));// 设置具体的post数据
		}

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($curl);// 执行一个cURL会话并且获取相关回复
		curl_close($curl);// 释放cURL句柄,关闭一个cURL会话
		
		return $response;
	}

}