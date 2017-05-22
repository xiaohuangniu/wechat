<?php
# 引入Access_Token更新文件 
require_once 'Access_Token.php';
$AccessToken = new Access_Token();
# 定义Access_Token常量
define('ACCESS_TOKEN',$AccessToken->GetToken());
require_once 'Jsapi_Ticket.php';
$WeiXin = new WeiXin();
# 获取签名
$data = $WeiXin->GetTicket();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>无标题文档</title>
</head>
<!--引入 JS.SDK -->
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js" type="text/javascript"></script>
<script type="text/javascript">
wx.config({
    debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
    appId: 'wx2749f05dd0da8141', // 必填，公众号的唯一标识
    timestamp: '<?php echo $data['timestamp']; ?>', // 必填，生成签名的时间戳
    nonceStr: '<?php echo $data['noncestr']; ?>', // 必填，生成签名的随机串
    signature: '<?php echo $data['signature']; ?>',// 必填，签名，见附录1
    jsApiList: [
	  // 测试的时候千万别留空,否则会出现一个errmsg config param is empty 警告错误
      'onMenuShareTimeline',
      'onMenuShareQQ'
	] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
});
wx.ready(function(){
	wx.onMenuShareQQ({
		title: '2', // 分享标题
		desc: '2', // 分享描述
		link: '2', // 分享链接
		imgUrl: '2', // 分享图标
		success: function () { 
		   // 用户确认分享后执行的回调函数
		},
		cancel: function () { 
		   // 用户取消分享后执行的回调函数
		   alert(1);
		}
	});
});

</script>
<body>
</body>
</html>
