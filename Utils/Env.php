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
            throw new CliException(local('no_env'), 10001);
        }

        /*
         * parse_ini_file(file,process_sections)
         * 解析一个配置文件（ini 文件），并以数组的形式返回其中的设置
         * process_sections	如果设置为 TRUE，则返回一个多维数组，包括了配置文件中每一节的名称和设置。默认是 FALSE
         */
        $env = parse_ini_file(__DIR__ . \DIRECTORY_SEPARATOR . '..' . \DIRECTORY_SEPARATOR . '.env', true);

        /*
         * putenv(string $assignment)
         * 配置系统环境变量
         */
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