<?php

namespace App\Services;

use App\Exceptions\CliException;
use App\Traits\DNS;

class CloudFlare implements DNS
{
    public function __construct()
    {
        $this->baseUrl = 'https://api.cloudflare.com/client/v4';

        $this->curl = new Curl($this->baseUrl, [
            'X-Auth-Email:' . env('cloudflare.email'),
            'X-Auth-Key:' . env('cloudflare.api.key')
        ], true);
    }

    /**
     * 此方法为 cloudflare 独有
     * 获取区域 ID
     *
     * @param $domainName
     * @return void
     * @throws CliException
     */
    private function getRecordId($domainName)
    {
        $this->zoneId = $this->curl->get('/zones', [
            'account.id' => env('cloudflare.account.id'),
            'name' => $domainName
        ])['result'][0]['id'];
    }

    /**
     * 获取主域名当前已解析 DNS 列表及对应 ID
     *
     * @param string $domainName
     * @return array
     * @throws CliException
     */
    public function listDnsRecord(string $domainName): array
    {
        $this->getRecordId($domainName);

        $response = $this->curl->get('/zones/' . $this->zoneId . '/dns_records', ['per_page' => 50000]);

        $result = [];

        foreach ($response['result'] as $value) {
            $rr = substr($value['name'], 0, -strlen($domainName) - 1);
            $result[empty($rr) ? '@' : $rr] = $value['id'];
        }

        return $result;
    }

    /**
     * 添加 DNS 解析记录
     *
     * @param string $type
     * @param string $rr
     * @param string $ip
     * @param string $domainName
     * @return mixed|void
     * @throws CliException
     */
    public function createDnsRecord(string $type, string $rr, string $ip, string $domainName)
    {
        $response = $this->curl->post('/zones/' . $this->zoneId . '/dns_records', [
            'body' => [
                'type' => $type,
                'name' => $rr,
                'content' => $ip,
                'ttl' => 1,
                'proxied' => true,
                'comment' => local('cloudflare_comment')
            ]
        ]);

        if (!$response['success']) {
            throw new CliException('服务商错误 - [' . $response['errors'][0]['code'] . '] - ' . $response['errors'][0]['message'], 40101);
        }
    }

    /**
     * 更新 DNS 解析记录
     *
     * @param string $recordId
     * @param string $type
     * @param string $rr
     * @param string $ip
     * @return mixed|void
     * @throws CliException
     */
    public function updateDnsRecord(string $recordId, string $type, string $rr, string $ip)
    {
        $response = $this->curl->put('/zones/' . $this->zoneId . '/dns_records/' . $recordId, [
            'body' => [
                'content' => $ip,
                'name' => $rr,
                'proxied' => true,
                'type' => $type,
                'ttl' => 1,
                'comment' => local('cloudflare_comment')
            ]
        ]);

        if (!$response['success']) {
            throw new CliException('服务商错误 - [' . $response['errors'][0]['code'] . '] - ' . $response['errors'][0]['message'], 40102);
        }
    }
}