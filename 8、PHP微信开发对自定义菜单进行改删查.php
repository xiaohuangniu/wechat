<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP微信开发对自定义菜单进行改删查
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-27 09:45:00
 + Last-time    : 2016-10-27 09:45:00 + 小黄牛
 + Desc         : 注意,以后的教程一致使用上篇博文所写的Access_Token获取类,开始真正意义上的微信开发啦
 +----------------------------------------------------------------------
*/

# 注意,菜单更新有时差,为10分钟左右
# 引入Access_Token更新文件 
require_once 'Access_Token.php';
$AccessToken = new Access_Token();
# 定义Access_Token常量
define('ACCESS_TOKEN',$AccessToken->GetToken());

# 菜单结构(参考开发者手册)
$data   = '{
     "button":[
     {	
          "type":"click",
          "name":"今日歌曲",
          "key":"V1001_TODAY_MUSIC"
      },
      {
           "name":"菜单",
           "sub_button":[
           {	
               "type":"view",
               "name":"搜索",
               "url":"http://www.soso.com/"
            },
            {
               "type":"view",
               "name":"视频",
               "url":"http://v.qq.com/"
            },
            {
               "type":"click",
               "name":"赞一下我们",
               "key":"V1001_GOOD"
            }]
       }]
 }';
$Custom = new Custom_Menu();
# 创建菜单
$Custom->createMenu($data);

class Custom_Menu{

	# 创建自定义菜单接口
	# $data : 菜单结构
	public function createMenu($data){
		$url    = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.ACCESS_TOKEN;	
		$result = self::https_request($url, $data);	
		# 这里可以打印返回值
		$data = array('创建菜单回调参数: ',$result);
		$this->Error_Log($data);
	}
	
	# 查询自定义菜单
	# return JSON格式的菜单结构
	public function searchMenu(){
		$url    = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token='.ACCESS_TOKEN;
		$result = self::https_request($url);
		# 这里可以打印返回值
		$data = array('查询菜单回调参数: ',$result);
		$this->Error_Log($data);
	}
	
	# 查询自定义菜单接口（包括菜单配置）
	# return JSON格式的菜单结构
	public function searchAllMenu(){
		$url    = 'https://api.weixin.qq.com/cgi-bin/get_current_selfmenu_info?access_token='.ACCESS_TOKEN;
		$result = self::https_request($url);
		# 这里可以打印返回值
		$data = array('查询配置菜单回调参数: ',$result);
		$this->Error_Log($data);
	}
	
	#  删除自定义菜单接口	
	public function deleteMenu(){
		$url    = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.ACCESS_TOKEN;
		$result = self::https_request($url);
		# 这里可以打印返回值
		$data = array('删除菜单回调参数: ',$result);
		$this->Error_Log($data);
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
 
