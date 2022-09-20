<?php

if (!function_exists('demo')) {
    function demo()
    {
        var_dump('我是根目录下的 helpers 文件中的 demo 函数');
        exit(0);
    }
}

if (!function_exists('env')) {
    function env(?string $key = null)
    {
        if (null === $key) throw new \App\Exceptions\CliException('变量名不能为空', 20001);

        $result = getenv(strtoupper($key));

        if (!$result) return null;

        return $result;
    }
}

if (!function_exists('explodeIpAddr')) {
    function explodeIpAddr(string $ip)
    {
        return explode('/', trim($ip))[0];
    }
}