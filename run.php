<?php

use Utils\Env;
use App\Main;

define('TIME_ZONE', date_default_timezone_set('Asia/Shanghai'));

require __DIR__ . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'autoload.php';

new Env();

$args = getopt('', ['serverName:', 'type:', 'rr:', 'domainName:', 'remark::']);

if (
    !isset($args['serverName']) ||
    !isset($args['type']) ||
    !isset($args['rr']) ||
    !isset($args['domainName'])
) {
    throw new \App\Exceptions\CliException('缺少必要参数，程序无法继续执行!', 10002);
}

(new Main())->ddns($args['serverName'], $args['type'], $args['rr'], $args['domainName'], $args['remark']);

log('[' . $_SERVER['REQUEST_TIME'] . '] - [success] - [0] - ip地址更新完成，并完成解析!' . PHP_EOL . PHP_EOL);
exit(0);