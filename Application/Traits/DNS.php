<?php

namespace App\Traits;

interface DNS
{
    /**
     * 列出 DNS 记录
     *
     * @return mixed
     */
    public function listDnsRecord(string $domainName): array;

    /**
     * 创建 DNS 记录
     *
     * @return mixed
     */
    public function createDnsRecord(string $type, string $rr, string $ip, string $domainName);

    /**
     * 更新 DNS 记录
     *
     * @return mixed
     */
    public function updateDnsRecord(string $recordId, string $type, string $rr, string $ip);
}