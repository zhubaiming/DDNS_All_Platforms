<?php

namespace App\Services;

use App\Exceptions\CliException;
use App\Traits\DNS;

class CloudFlare implements DNS
{
    public function __construct()
    {
        $this->baseUrl = 'https://api.cloudflare.com/client/v4';

        $this->curl = new Curl($this->baseUrl, ['Authorization:Bearer ' . env('cloudflare.api.token')], true);
    }

    private function getRecordId($domainName)
    {
        $this->zoneId = $this->curl->get('/zones', [
            'account.id' => env('cloudflare.account.id'),
            'name' => $domainName
        ])['result'][0]['id'];
    }

    /**
     * @inheritDoc
     */
    public function listDnsRecord(string $domainName): array
    {
        $this->getRecordId($domainName);

        $response = $this->curl->get('/zones/' . $this->zoneId . '/dns_records');

        $result = [];

        foreach ($response['result'] as $value) {
            $rr = substr($value['name'], 0, -strlen($domainName) - 1);
            $result[empty($rr) ? '@' : $rr] = $value['id'];
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function createDnsRecord(string $type, string $rr, string $ip, string $domainName)
    {
        $response = $this->curl->post('/zones/' . $this->zoneId . '/dns_records', [
            'body' => [
                'type' => $type,
                'name' => $rr,
                'content' => $ip,
                'ttl' => 1,
                'proxied' => true
            ]
        ]);

        if ($response['success']) {

        } else {
            throw new CliException('服务商错误 - [' . $response['errors'][0]['code'] . '] - ' . $response['errors'][0]['message'], 40101);
        }
    }

    /**
     * @inheritDoc
     */
    public function updateDnsRecord(string $recordId, string $type, string $rr, string $ip)
    {
        $response = $this->curl->put('/zones/' . $this->zoneId . '/dns_records/' . $recordId, [
            'body' => [
                'type' => $type,
                'name' => $rr,
                'content' => $ip,
                'ttl' => 1,
                'proxied' => false
            ]
        ]);

        if ($response['success']) {

        } else {
            throw new CliException('服务商错误 - [' . $response['errors'][0]['code'] . '] - ' . $response['errors'][0]['message'], 40102);
        }
    }
}