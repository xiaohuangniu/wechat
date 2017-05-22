<?php
/*
 +----------------------------------------------------------------------
 + Title        : 微信开发,建立Access_Token中控器
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-26 16:36:00
 + Last-time    : 2016-10-27 09:12:00 + 小黄牛
 + Desc         : 从今天开始,之后的教程就是微信开发的关键啦,之后的教程微信官方不会再提供代码,所以都得靠自己写,本章节重点需要使用到数据库
 +----------------------------------------------------------------------
*/
$n = new Access_Token();
echo $n->GetToken();

# 微信获取Access_Token 基础使用类
class Access_Token{
	private static $AppId        = '';// 微信的appid
	private static $AppSecret    = '';// 微信的appsecret
	private static $AppToken_Url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=%s&secret=%s';// 微信获去actoken的地址
	private static $AC_Token_Time= 6600;// 单位秒,默认110分钟
	# 将Appid与AppSecret植入占位符
	private static function StrUrl(){
		$resultStr = sprintf( self::$AppToken_Url, self::$AppId, self::$AppSecret);
		return $resultStr;
	}
	
	# 验证access_token有效期,有效则返回最新一条access_token,没则更新
	public function GetToken(){
		# 获取最新的一条access_token
		$DB   = new Model();
		$info = $DB->find('select access_token,token_time from weixin order by id desc'); 
		# 一条都没有,返回false
		if (!$info) {return false;}
		# 计算出过期时间
		$time = time() + (self::$AC_Token_Time);
		# 将过期时间进行对比 - 如果当前时间比库存时间大,则更新Access_Token,否则返回现在的Access_Token
		if (time() >= $info['token_time']){
			return self::SaveToken();
		}
		return $info['access_token'];
	}
	
	# 更新Access_Token
	private static function SaveToken(){
		# 请求TOken
		$res    = self::https_request(self::StrUrl());
		$result = json_decode($res, true); //接受一个 JSON 格式的字符串并且把它转换为 PHP 变量
		$access_token = $result['access_token'];
		# 更新一条access_token
		$DB   = new Model();
		# 计算出过期时间
		$time = time() + (self::$AC_Token_Time);
		$sql  = "INSERT INTO weixin (access_token,token_time) VALUES ('$access_token','$time')";
		$info = $DB->add($sql);
		return $access_token;
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
	
}


# PDO类
class Model{
	protected static $dbMs      = 'mysql';// 数据库类型
	protected static $dbHost    = 'localhost';// 数据库主机名
    protected static $dbName    = '';// 数据库名称
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
	public function add($Sql){
		$pdo = $this->instance;
		return $pdo->exec($Sql);
	}
}



/**
 * 表结构
 CREATE TABLE IF NOT EXISTS `weixin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `access_token` varchar(513) CHARACTER SET utf8 NOT NULL COMMENT 'access_token,最少为512字节,稳妥点存513',
  `token_time` varchar(20) CHARACTER SET utf8 NOT NULL COMMENT '过期时间戳为2小时-10分钟',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
*/