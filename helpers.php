<?php

if (!function_exists('local')) {
    function local(string $key)
    {
        $local = getenv(strtoupper('app.local')) ?? 'zh';

        $arr = include(__DIR__ . \DIRECTORY_SEPARATOR . 'Locals' . \DIRECTORY_SEPARATOR . $local . '.php');

        if (array_key_exists($key, $arr)) {
            return $arr[$key];
        } else {
            return '';
        }
    }
}

if (!function_exists('env')) {
    function env(?string $key = null)
    {
        if (null === $key) throw new \App\Exceptions\CliException(local('no_variable_name'), 20001);

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

if (!function_exists('writelog')) {
    function writelog(string $text)
    {
        if (is_file(__DIR__ . \DIRECTORY_SEPARATOR . 'Logs' . \DIRECTORY_SEPARATOR . date('Y-m-d') . '.log')) {
            // 文件存在
            file_put_contents(__DIR__ . \DIRECTORY_SEPARATOR . 'Logs' . \DIRECTORY_SEPARATOR . date('Y-m-d') . '.log', $text, FILE_APPEND);
        } else {
            // 文件不存在
            file_put_contents(__DIR__ . \DIRECTORY_SEPARATOR . 'Logs' . \DIRECTORY_SEPARATOR . date('Y-m-d') . '.log', $text);
        }
    }
}