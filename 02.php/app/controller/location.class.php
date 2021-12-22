<?php

/**
 * IP地理位置接口服务
 */
class location extends Controller {
    /*
     * 注意事项：
     * 1、所有的控制器都要继承基类控制器：Controller
     * 2、基类控制器中包含：数据库连接对象、守护进程通信对象、视图层对象、公共函数等，继承后可以直接使用基类的变量和对象
     * 
     * 用法：
     * 1、使用父类的变量：$this->xxx
     * 2、使用父类的成员函数：parent::yyy()，如parent::RequestReplyExec("string");
     * 3、使用父类的非成员函数，直接用即可：zzz() 
     * 4、
     */

    function __construct() {
        /*
         * 避免子类的构造函数覆盖父类的构造函数
         */
        parent::__construct();

        /*
         * 其它自定义操作
         */
    }

    //阿里云云市场的IP地理位置接口服务
    public function IpLocation($ip) {
        $host = "https://api01.aliyun.venuscn.com";
        $path = "/ip";
        $method = "GET";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . AppCode);
        $bodys = "";
        $url = $host . $path . "?ip=" . $ip;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        //解析返回的json
        $result = json_decode(curl_exec($curl), true);
        if ($result['ret'] == 200) {
            $isp = $result['data']['isp'];
            $area = $result['data']['area'];
            $region = $result['data']['region'];
            $city = $result['data']['city'];
            $country = $result['data']['country'];
            return "$country $isp $area $region $city";
        } else {
            return "*";
        }
    }

}
