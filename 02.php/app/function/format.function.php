<?php

/*
 * 格式化相关的公共函数
 */

// 匹配IP地址
function matchIp($unknown) {
    $pat = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))\.){3}((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))$/";
    if (preg_match($pat, $unknown)) {
        return $unknown;
    } else {
        return ' ';
    }
}

// 传入IP或域名，返回IP
function host2ip($host) {
    $host = trim($host); // 移除无关格式
    if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        return $host;
    } else {
        $ip = gethostbyname($host);
        if ($ip === $host) {
            die("无效的主机名称:$host\n");
        }
        return $ip;
    }
}

// 传入以空格分隔的字符串，返回其中第一次出现的IP地址
function getip($str) {
    $str = trim($str);
    $arr = explode(' ', $str);
    foreach ($arr as $value) {
        if (matchIp($value) != ' ') {
            return $value;
        }
    }
    return '*';
}

// 传入IP地址，返回对应的主机名称，没有则返回传入值
function GetIpToHost($ip) {
    $ip = trim($ip); // 移除无关格式
    if (matchIp($ip) != ' ') {
        $hostname = gethostbyaddr($ip);
        return $hostname;
    }
}
