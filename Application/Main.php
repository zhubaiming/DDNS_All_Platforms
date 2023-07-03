<?php

namespace App;

use App\Exceptions\CliException;

class Main
{
    public function __construct()
    {
        $this->ethName = env('eth.name');

        $this->domainNameEnding = ['com', 'cn'];

        $this->ipv4NetworkOperators = [];

        $this->ipv6NetworkOperators = [
            '240e' => '中国电信',
            '2408' => '中国联通',
            '2409' => '中国移动'
        ];
    }

    // (new Main())->ddns($args['serverName'], $args['type'], $args['rr'], $args['domainName'], $args['remark'] ?? null);
    // (new Main())->ddns(‘cloudflare', 'AAAA', '@,www', 'xxx.com','备注1,备注2');
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
                continue;
            } else {
                throw new CliException(local('domain_name_error'), 30001);
            }
        }

        return true;
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
            'A' => $this->getIPv4Addr(),
            'AAAA' => $this->getIPv6Addr(),
            default => null
        };

        if (is_null($result)) throw new CliException($type . local('no_ip'), 30002);

        return $result;
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
        $ips = explode("\n", shell_exec("ip -o -6 addr show {$this->ethName} | grep -vE 'fe80|fec0|fc00' | grep flags"));

        $inet6 = null;
        $sec = 0;
        foreach ($ips as $ip) {
            $_sec = (int)preg_replace(['/(.*)preferred_lft(\s+)/', '/sec/'], ['', ''], $ip);

            if ($_sec > $sec) {
                $inet6 = trim(preg_replace(['/(.*)inet6(\s+)/', '/scope(.*)/', '/\/[0-9]+/'], ['', '', ''], $ip));
            }
        }

        return $inet6;
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
