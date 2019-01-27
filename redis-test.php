<?php

include './service/RedisTool.php';

//$redis = new RedisTool();
//RedisTool::set('kk', 88888);
//$redis->set('k', '6666');
//$redis->hSet('1', 'name', 'gg');
//$redis->hSet('1', 'age', '18');
//$redis->hSet('1', 'wi', '1234');
//$redis->hSet('1', 'nme', '666');

\App\service\RedisTool::sAddArray(30,[26,27,28]);
$arr = \App\service\RedisTool::sMembers(30);
var_dump($arr);