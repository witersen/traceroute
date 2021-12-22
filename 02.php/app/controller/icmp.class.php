<?php

/**
 * ICMP方式
 */
define('MICROSECOND', 1000000); // 1秒 = 1000000 微秒
define('SOL_IP', 0); // 这两项要用常量
define('IP_TTL', 2);

class icmp extends Controller {
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

    private $Location;

    function __construct() {
        /*
         * 避免子类的构造函数覆盖父类的构造函数
         */
        parent::__construct();

        /*
         * 其它自定义操作
         */
        $this->Location = new location();
    }

    /**
     * ICMP 路由追踪
     */
    public function Traceroute($requestPayload) {
        /**
         * 参数解析
         */
        $ip = trim($requestPayload["ip"]);
        $geograph = !isset($requestPayload["geograph"]) || empty($requestPayload["geograph"]) ? 0 : $requestPayload["geograph"]; //是否解析节点的地理位置 默认不解析
        $rdns = !isset($requestPayload["rdns"]) || empty($requestPayload["rdns"]) ? 0 : $requestPayload["rdns"]; //解析地址对应的主机名 默认不解析
        $seq = !isset($requestPayload["seq"]) || empty($requestPayload["seq"]) ? 1 : $requestPayload["seq"]; //初始ttl的值 默认为1
        $WaitTime = !isset($requestPayload["WaitTime"]) || empty($requestPayload["WaitTime"]) ? 1 : $requestPayload["WaitTime"]; //等待超时时间 默认为1ms
        $MaxHops = !isset($requestPayload["MaxHops"]) || empty($requestPayload["MaxHops"]) ? 30 : $requestPayload["MaxHops"]; // 初始的最大ttl值 默认为30
        $sleeptime = !isset($requestPayload["sleeptime"]) || empty($requestPayload["sleeptime"]) ? 0 : $requestPayload["sleeptime"]; // 发包的睡眠时间即间隔时间 默认为0
        $packcount = !isset($requestPayload["packcount"]) || empty($requestPayload["packcount"]) ? 10 : $requestPayload["packcount"]; // 每个点发送探测包的数量 默认为10
        if (empty($ip)) {
            $data['status'] = 0;
            $data['message'] = '参数不完整';
            return $data;
        }

        /**
         * 声明 定义
         */
        $id = rand(0, 0xFFFF);
        $success = 0; //标识是否到达目的地
        $return_ip = "";
        $return_port = "";

        $sock = socket_create(AF_INET, SOCK_RAW, getprotobyname('icmp')) or die("创建套接字失败");

        /**
         * 追踪信息临时存储
         */
        $hops = array();

        /**
         * 最外层循环，负责ICMP报文的构造和发送
         */
        while ($success == 0) {
            $single = array(
                "icmp_hop" => "*",
                "icmp_ip" => "*",
                "icmp_rdns" => "*",
                "icmp_geograph" => "*"
            );
            socket_set_option($sock, SOL_IP, IP_TTL, $seq);

            $single["icmp_hop"] = $seq;

            $packet = $this->StructIcmp($id, $seq);

            $start = microtime(true) * MICROSECOND; //设置开始时间

            $timeout = $start + MICROSECOND; //设置发送超时时间为1ms

            /**
             * 只需要自己构建ICMP数据报，IP头在发送数据之前会自动填充
             */
            for ($i = 0; $i < $packcount; $i++) {
                /**
                 * 发送十次数据进行探测，提高结果的精确度
                 */
                socket_sendto($sock, $packet, strlen($packet), 0, $ip, 0) or die('发送数据报错误'); // ICMP 没有端口号的概念，所以用0
            }

            for (;;) {
                /**
                 * 内层循环，负责数据报的超时判断、接收、类型判断、对应返回包判断、解包、读取
                 */
                $now = microtime(true) * MICROSECOND;
                if ($now >= $timeout) {
                    $single["icmp_ip"] = "*"; //发送超时
                    break; // 如果发送IP数据报发送超时，跳出循环，直接进行下一次发送操作
                }

                $read = array($sock);
                $other = array();
                $selected = socket_select($read, $other, $other, 0, $WaitTime * 1000000); // 使用非阻塞方式监控变化

                if ($selected === 0) {
                    $single["icmp_ip"] = "*";
                    break; // 超出了规定的时间，跳出循环，直接进行下一次发包操作
                } else {
                    socket_recvfrom($sock, $data, 65535, 0, $return_ip, $return_port);

                    $data = unpack('C*', $data); //解包

                    /**
                     * 判断是否为ICMP数据包
                     */
                    // 如果IP数据报头的第十个八位字段值为1，代表此包为ICMP包
                    if ($data[10] != 1) {
                        //如果不是ICMP数据包，中止本次循环，继续下次循环，接收下一个包进行判断
                        continue;
                    }

                    $found = 0;

                    /**
                     * 判断是否为我们需要的ICMP数据包
                     */
                    if (
                            ($data[21] == 0) && //如果为0代表为回送应答
                            ($data[25] == ($id & 0xFF)) && // 标识符
                            ($data[26] == ($id >> 8)) && // 标识符
                            ($data[27] == ($seq & 0xFF)) && // 序号
                            ($data[28] == ($seq >> 8)) //序号
                    ) {
                        $found = 1;
                    } else if (
                            ($data[21] == 11) && // 如果为11代表超时
//                            (count($data) >= 56) &&
                            ($data[53] == ($id & 0xFF)) && // 标识符
                            ($data[54] == ($id >> 8)) && // 标识符
                            ($data[55] == ($seq & 0xFF)) && // 序号
                            ($data[56] == ($seq >> 8)) //序号
                    ) {
                        $found = 2;
                    }
                    /**
                     * 如果有数据包为ICMP数据包，但是不是自己要的ICMP数据包，则继续循环，进行接收，直到数据包符合要求跳出循环，进行下次发包
                     */
                    //符合我们要求
                    if ($found) {
                        if ($rdns) {
                            $single["icmp_rdns"] = GetIpToHost($return_ip);
                        }
                        if ($geograph) {
                            $single["icmp_geograph"] = $this->Location->IpLocation($return_ip);
                        }
                        $single["icmp_ip"] = $return_ip;
                        //到达目标
                        if ($found == 1) {
                            $success = 1;
                            break;
                        }
                        break;
                    }
                }
            }
            array_push($hops, $single);
            ++$seq;
            if ($seq > $MaxHops) {
                break;
            }
            sleep($sleeptime);
        }

        socket_close($sock);

        $datatList = array(
            "hops" => $hops,
            "arrive" => $success == 1 ? true : false
        );

        $resultList['status'] = 1;
        $resultList['data'] = $datatList;
        $resultList['message'] = '获取数据成功';
        return $resultList;
    }

    /**
     * 计算校验和
     */
    private function CheckSum($data) {
        $bit = unpack('n*', $data);
        $sum = array_sum($bit);

        if (strlen($data) % 2) {
            $temp = unpack('C*', $data[strlen($data) - 1]);
            $sum += $temp[1];
        }

        $sum = ($sum >> 16) + ($sum & 0xffff);
        $sum += ($sum >> 16);

        return pack('n*', ~$sum);
    }

    /**
     * 构造ICMP报文，传入id和seq，返回icmp数据包
     */
    private function StructIcmp($id, $seq) {
        /**
         * 构造8字节的ICMP头部分
         */
        $packet = '';
        $packet .= chr(8); // 类型
        $packet .= chr(0); // 代码
        $packet .= chr(0); // 校验和
        $packet .= chr(0); // 校验和
        $packet .= chr($id & 0xFF); // 标识符 & 0xFF保留低八位
        $packet .= chr($id >> 8); // 标识符
        $packet .= chr($seq & 0xFF); // 序号
        $packet .= chr($seq >> 8); // 序号

        /**
         * 构造56字节的ICMP数据部分(空白填充)
         */
        for ($i = 0; $i < 56; ++$i) {
            $packet .= chr(0);
        }

        $check = $this->Checksum($packet); //设置校验和

        $packet[2] = $check[0];
        $packet[3] = $check[1];

        return $packet;
    }

}
