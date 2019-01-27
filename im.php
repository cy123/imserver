<?php

require 'Autoload.php';
require 'ImServer.php';
require 'function.php';

// 自动载入
$loader = new Aotuload();
$loader->register();
$loader->addNamespace('App', __DIR__);

// 启动websocket服务器
new ImServer();


