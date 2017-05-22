<?php
/*
 +----------------------------------------------------------------------
 + Title        : PHP微信开发 JS-SDK配置，jsapi_ticket权限签名获取
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-31 10:31:00
 + Last-time    : 2016-10-31 10:31:00 + 小黄牛
 + Desc         : 这个对于整篇JS-SDK接入来说，最核心，最重要部分
 +
 + 获取签名流程与解析:
 +    解析：
 +    签名获取的流程有点类似之前的Access_Token,只不过这次存的是加密之后的签名字符串等信息
 + 
 +    流程:
 +    1,使用Access_Token,获取jsapi_ticket(有效期7200S,也就代表着当获取到的那刻起,2小时候签名失效)
 +    2,使用jsapi_ticket,结合根据规则生成签名,规则如下;
 +      noncestr（随机字符串）
 +      有效的jsapi_ticket
 +      timestamp（时间戳）,time()
 +      url（当前网页的URL，不包含#及其后面部分）
 +      对所有待签名参数按照字段名的ASCII 码从小到大排序（字典序）后
 +      使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1
 +      这里需要注意的是所有参数名均为小写字符(这里注意框架的路由URL大小写命名和设置)
 +      最后,对string1作sha1加密，字段名和字段值都采用原始值，不进行URL 转义。
 +
 +    结合以上一点,我们总结出要保存的字段,有一下几个:
 +    1,由于调用JS-SDK并不需要jsapi_ticket值,所以这个不需要保存
 +    2,每个页面都需要生成对应签名,所以需要保存一个不带有#及其后面部分的URL,进行URL识别
 +    3,JS-SDK需要三个参数:,所以我们还需要以下3个字段
 +       timestamp: , // 必填，生成签名的时间戳
 +       nonceStr: '', // 必填，生成签名的随机串
 +       signature: '',// 必填，签名，见附录1
 +    4,最后还缺一个字段,过期时间戳
 +    5,总结字段有:
 +       主键, URL子键 ,生成签名的时间戳 ,生成签名的随机串 ,最终生成的签名 ,过期时间戳
 +----------------------------------------------------------------------------------------------------------------------------------------------
*/
# 引入Access_Token更新文件 
require_once 'Access_Token.php';
$AccessToken = new Access_Token();
# 定义Access_Token常量
define('ACCESS_TOKEN',$AccessToken->GetToken());
$WeiXin = new WeiXin();
# 获取签名
var_dump($WeiXin->GetTicket());

class WeiXin{
	private static $AppToken_Url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi';// 获取jsapi_ticket的地址
	private static $Ticket_Time  = 6600;// 过期时间,单位秒,默认110分钟
	private static $Ticket_Length = 15;// nonceStr随机签名参数的长度
	
	# 验证jsapi_ticket有效期,有效则返回最新一条access_token,没则更新
	public function GetTicket(){
		# 获取ticket
		$url  = self::Weixin_Url();
		$DB   = new Model_2();
		$info = $DB->find("select * from jsapi_ticket where url = '$url'"); 
		# 一条都没有,更新
		if (!$info) {
			return self::SaveTicket();
		}
		# 计算出过期时间
		$time = time() + (self::$Ticket_Time);
		# 将过期时间进行对比 - 如果当前时间比库存时间大,则更新Access_Token,否则返回现在的Access_Token
		if (time() >= $info['out_time']){
			return self::SaveTicket();
		}
		return $info;
	}
	
	
	# 将Access_Token植入占位符 - 并请求到jsapi_ticket
	private static function Weixin_Ticket(){
		$url = sprintf( self::$AppToken_Url, ACCESS_TOKEN);
		# 发起请求
		$result = self::https_request($url);
		self::Error_Log(array(
			'请求ticket时返回的JSON' => $result
		));//记录日志,以防报错
		$res    = json_decode($result,true);
		# 返回jsapi_ticket
		return $res['ticket'];
	}
	
	# 更新签名
	private static function SaveTicket(){
		# 获取参数
		$data = self::Weixin_Sha1();
		# 计算出过期时间
		$timestamp = $data['timestamp'];
		$time = $timestamp + (self::$Ticket_Time);
		$url  = $data['url'];
		$noncestr = $data['noncestr'];// 随机字符串
		$sign = $data['sign'];// 签名字符串
		# 查询是否存在该签名
		$DB   = new Model_2();
		$info = $DB->find("select id from jsapi_ticket where url = '$url'"); 
		# 存在数据,则修改
		if($info){
			$sql = "UPDATE jsapi_ticket SET url='$url',timestamp='$timestamp',noncestr='$noncestr',signature='$sign',out_time='$time' WHERE id = ".$info['id'];
		}else{
			# 不存在写入
			$sql  = "INSERT INTO jsapi_ticket (url,timestamp,noncestr,signature,out_time) VALUES ('$url','$timestamp','$noncestr','$sign','$time')";
		}
		$DB->Sql($sql);
		return $data;
	}
	
	# 进行权限签名加密
	private static function Weixin_Sha1(){
		# 生成签名的时间戳
        $timestamp = time();
        # 生成签名的随机字符串
        $noncestr  = self::Weixin_Rand();
		# 生成签名的jsapi_ticket
		$jsapi_ticket = self::Weixin_Ticket();
        # 生成签名的url
        $url = self::Weixin_Url();
        # 组合成数组 - 手动字典序
        $tmpArr = array(
			'jsapi_ticket' => $jsapi_ticket,
			'noncestr'     => $noncestr,
			'timestamp'    => $timestamp,
			'url'          => $url
		);
		# 合成键值兑换字符串
		$str = '';
		foreach ($tmpArr as $key => $val) {
			$str .= $key.'='.$val.'&';
		}
		$str = rtrim($str, '&');
        # 进行sha1加密签名
		$tmpStr = sha1( $str );
		# 将需要保存到数据库的内容组合成数组,并返回
		$data = array(
			'noncestr' => $noncestr, // 随机字符串
			'timestamp'=> $timestamp,// 时间戳
			'url'      => $url,      // 识别url
			'sign'     => $tmpStr    // 签名字符串
		);
		self::Error_Log(array(
			'生成的签名JSON' =>  json_encode($data)
		));//记录日志,以防报错
		return $data;
	}
	
	# 生成随机字符串
	private static function Weixin_Rand(){   
		$pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
		$key = '';
		for($i=0 ;$i<self::$Ticket_Length ;$i++)   {   
			$key .= $pattern{mt_rand(0,35)};    //生成php随机数   
		}   
		return $key;   
	} 

	# 获得当前完整的URL地址,并不带#及其后面部分
	private static function Weixin_Url(){
		# 获得网页协议头部
		if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {$http = 'https://';}
		else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'){ $http = 'https://';}
		else if ( ! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {$http = 'https://';}
		else {$http = 'http://';}
		# 返回完整URL
		return $http.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	# 用于微信接口数据传输的万能函数
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

# PDO类
class Model_2{
	protected static $dbMs      = 'mysql';// 数据库类型
	protected static $dbHost    = 'bdm23250574.my3w.com';// 数据库主机名
    protected static $dbName    = 'localhost';// 数据库名称
	protected static $dbCharset = 'utf8';// 数据库编码
	protected static $dbUser    = '';// 数据库连接用户名
	protected static $dbPwd     = '';// 数据库密码
	
	private $instance;// 数据库PDO连接实例
	
	# 获得PDO实例
	public function __construct(){ $this->dbPdo();}
	
	# 执行PDO链接
	private function dbPdo(){
		$dbn = self::$dbMs.':host='.self::$dbHost.';dbname='.self::$dbName.';charset='.self::$dbCharset;
		$dbh = new PDO($dbn,self::$dbUser,self::$dbPwd);
		$this->instance = $dbh;
	}
	
	# 执行SELECT查询获取单条记录，返回一维数组
	public function find($Sql){
		$pdo = $this->instance;
		$res = $pdo->query($Sql);
		$res->setFetchMode(PDO::FETCH_ASSOC); //数字索引方式
		return $res->fetch();
	}
	
	# 添加操作
	public function Sql($Sql){
		$pdo = $this->instance;
		return $pdo->exec($Sql);
	}
}

/**
 * 表结构如下:
 CREATE TABLE IF NOT EXISTS `jsapi_ticket` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `url` varchar(150) CHARACTER SET utf8 NOT NULL COMMENT 'URL子键,用于识别对应路由',
  `timestamp` varchar(20) CHARACTER SET utf8 NOT NULL COMMENT '生成签名时的时间戳',
  `noncestr` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '生成签名的随机串',
  `signature` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT '最终生成的签名',
  `out_time` varchar(20) CHARACTER SET utf8 NOT NULL COMMENT '过期时间戳为2小时-10分钟',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='微信的jsapi_ticket' AUTO_INCREMENT=1 ;
*/
