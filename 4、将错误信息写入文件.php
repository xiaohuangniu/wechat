<?php
/*
 +----------------------------------------------------------------------
 + Title        : 将错误信息写入文件
 + Author       : 小黄牛
 + Version      : 无
 + Initial-Time : 2016-10-25 15:51:00
 + Last-time    : 2016-10-25 16:01:00 + 小黄牛
 + Desc         : 一般做开发经常会出错,而微信又不会显示报错内容,所以我们可以通过日志函数来将报错内容存入文件进行查看
 +----------------------------------------------------------------------
*/
class WeiXin_Error{
    // 我是用来测试日志方法
    public function Test(){
        $array = [
            'fromUsername' => '这只是个测试',
            'toUsername'   => '12sdzx3a1s',
            'txt'          => '测试完成'
        ];
        $this->Error_Log($array,true);
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

$test = new WeiXin_Error();
$test->Test();
