<?php

namespace App;

use App\Exceptions\CliException;

class Main
{
    public function __construct()
    {
        // 要获取地址的网卡名称(enp2s0)
        $this->ethName = 'eth0';

        $this->domainNameEnding = ['com', 'cn'];
    }


    public function ddns(string $serverName, string $type, string $rr, string $domainName, string $remark)
    {
        $this->validateDomainName($domainName);

        $ip = $this->validateType($type);

        $service = $this->getService($serverName);

        $recordIds = $service->listDnsRecord($domainName, $rr);

        foreach (explode(',', $rr) as $value) {
            if (isset($recordIds[$value])) {
                // 更新
                $service->updateDnsRecord($recordIds[$value], $type, $value, $ip);
            } else {
                // 创建
                $service->createDnsRecord($type, $value, $ip, $domainName);
            }
        }
        exit;
    }

    /**
     * 验证域名格式
     *
     * @param $domainName
     * @return bool
     * @throws CliException
     */
    private function validateDomainName($domainName)
    {
        foreach ($this->domainNameEnding as $val) {
            if (preg_match_all('/^[a-zA-Z0-9]+\.' . $val . '/i', $domainName)) {
                return true;
            }
        }

        throw new CliException('域名名称格式错误', 30001);
    }

    private function validateType($type)
    {
        $result = match ($type) {
            'A' => 'local' === env('app.env') ? '42.193.126.219' : $this->fetchIPAddr($this->getIPv4Addr()),
            'AAAA' => 'local' === env('app.env') ? 'fe80:0:0:0:0123:0456:0789:0abc' : $this->fetchIPAddr($this->getIPv6Addr()),
//            'AAAA' => 'local' === env('app.env') ? '2409:8a14:866:8c30:468a:5bff:fe93:bab7' : $this->fetchIPAddr($this->getIPv6Addr()),
            default => null
        };

        if (is_null($result)) throw new CliException('当前类型下(' . $type . ')，没有可供解析的外网ip!', 30002);

        return $result;
    }

    private function fetchIPAddr(?string $ip)
    {
        return is_null($ip) ? $ip : explodeIpAddr($ip);
    }

    /**
     * 获取最新的 IPv4 地址
     *
     * @return false|string|null
     */
    private function getIPv4Addr()
    {
        return shell_exec("ifconfig {$this->ethName} | grep inet | grep -vE 'inet6|127|172|192|100|10' | tail -1 | awk '{print $2}'");
    }

    /**
     * 获取最新的 IPv6 地址
     *
     * @return false|string|null
     */
    private function getIPv6Addr()
    {
        return shell_exec("ifconfig {$this->ethName} | grep inet6 | grep -vE 'fe80|fec0|fc00' | tail -1 | awk '{print $2}'");
    }

    private function getService($serverName)
    {
        $className = '\App\Services\\' . match ($serverName) {
                'cloudflare' => 'CloudFlare',
                'aliyun|ali' => 'Aliyun',
                'tencent|tengxun|tx' => 'Tencent'
            };

        return new $className();
    }
}