<?php

namespace App;

use App\Exceptions\CliException;

class Main
{
    public function __construct()
    {
        $this->ethName = 'enp2s0';

        $this->domainNameEnding = ['com', 'cn'];

        $this->ipv4NetworkOperators = [];

        $this->ipv6NetworkOperators = [
            '240e' => '中国电信',
            '2408' => '中国联通',
            '2409' => '中国移动'
        ];
    }


    public function ddns(string $serverName, string $type, string $rr, string $domainName, ?string $remark)
    {
        $this->validateDomainName($domainName);

        $ip = $this->validateType($type);

        $service = $this->getService($serverName);

        $recordIds = $service->listDnsRecord($domainName, $rr);

        foreach (explode(',', $rr) as $value) {
            if (isset($recordIds[$value])) {
                $service->updateDnsRecord($recordIds[$value], $type, $value, $ip);
            } else {
                $service->createDnsRecord($type, $value, $ip, $domainName);
            }
        }
    }

    /**
     * 验证主域名格式
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

    /**
     * 根据入参，获取本机对应类型的 IP 地址
     *
     * @param $type
     * @return string
     * @throws CliException
     */
    private function validateType($type): string
    {
        $result = match ($type) {
            'A' => $this->fetchIPAddr($this->getIPv4Addr()),
            'AAAA' => $this->fetchIPAddr($this->getIPv6Addr()),
            default => null
        };

        if (is_null($result)) throw new CliException('当前类型下(' . $type . ')，没有可供解析的外网ip!', 30002);

        return $result;
    }

    /**
     * @param string|null $ip
     * @return string|null
     */
    private function fetchIPAddr(?string $ip): ?string
    {
        return is_null($ip) ? $ip : explodeIpAddr($ip);
    }

    /**
     * 获取最新的 IPv4 地址
     *
     * @return string|null
     */
    private function getIPv4Addr(): ?string
    {
        return shell_exec("ifconfig {$this->ethName} | grep inet | grep -vE 'inet6|127|172|192|100|10' | tail -1 | awk '{print $2}'");
    }

    /**
     * 获取最新的 IPv6 地址
     *
     * @return string|null
     */
    private function getIPv6Addr(): ?string
    {
        return shell_exec("ifconfig {$this->ethName} | grep inet6 | grep -vE 'fe80|fec0|fc00' | tail -1 | awk '{print $2}'");
    }

    /**
     * 根据入参，转换成实例
     *
     * @param $serverName
     * @return mixed
     */
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