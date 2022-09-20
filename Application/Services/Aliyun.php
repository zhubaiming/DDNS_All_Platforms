<?php

namespace App\Services;

use App\Traits\DNS;

class Aliyun implements DNS
{
    public function listDnsRecord(string $domainName): array
    {
        // TODO: Implement listDnsRecord() method.
    }

    public function createDnsRecord(string $type, string $rr, string $ip, string $domainName)
    {
        // TODO: Implement createDnsRecord() method.
    }

    public function updateDnsRecord(string $recordId, string $type, string $rr, string $ip)
    {
        // TODO: Implement updateDnsRecord() method.
    }
}