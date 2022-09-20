<?php

namespace Utils;

use App\Exceptions\CliException;

class Env
{
    public function __construct()
    {
        $this->loadFile();
    }

    private function loadFile()
    {
        if (!file_exists(__DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '.env')) {
            // 抛出一个异常
            throw new CliException('缺少环境变量文件', 10001);
        }

        $env = parse_ini_file(__DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '.env', true);

        foreach ($env as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $k => $v) {
                    putenv(strtoupper($key) . '.' . strtoupper(str_replace('_', '.', $k)) . '=' . $v);
                }
            } else {
                putenv(strtoupper(str_replace('_', '.', $key)) . '=' . $value);
            }
        }
    }
}