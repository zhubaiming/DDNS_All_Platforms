<?php

use Utils\Env;
use App\Main;

// 设置默认时区
define('TIME_ZONE', date_default_timezone_set('Asia/Shanghai'));

// 引入自动加载
require __DIR__ . \DIRECTORY_SEPARATOR . 'vendor' . \DIRECTORY_SEPARATOR . 'autoload.php';

// 加载环境变量
new Env();

//$demo = "";
//var_dump(isset($demo));
//var_dump(empty($demo));
//exit;

// 1、获取输入参数
$args = getopt('', ['serverName:', 'type:', 'rr:', 'domainName:', 'remark::']);

/*
if (
    !isset($args['serverName']) ||
    !isset($args['type']) ||
    !isset($args['rr']) ||
    !isset($args['domainName'])
) {
    output('缺少必要参数，程序无法继续执行!');
}
*/

(new Main())->ddns(
    'local' === \env('app.env') ? 'cloudflare' : $args['serverName'],
    'local' === \env('app.env') ? 'AAAA' : $args['type'],
    'local' === \env('app.env') ? '@,www' : $args['rr'],
    'local' === \env('app.env') ? 'poolbear.cn' : $args['domainName'],
    'local' === \env('app.env') ? '备注1,备注2' : $args['remark'],
);

// ----------------------------------------------

output('ip地址更新完成，并完成解析!', 'success', 0);

/*
 * 执行命令：
 * php run.php --serverName=cloudflare --type=AAAA --rr=@,www --domainName=poolbear.cn --remark=备注1,备注2 --v
 */