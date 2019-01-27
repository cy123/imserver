<?php

namespace App\config;

// 配置文件
class ImConfig
{
    //  消息类型 1.聊天 2.注册 3.登录
    const MESSAGE_TYPE_CHAT = 1;
    const MESSAGE_TYPE_REGISTER = 2;
    const MESSAGE_TYPE_LOGIN = 3;
    const MESSAGE_TYPE_FRIENDS = 4;
    const MESSAGE_TYPE_ADD_FRIENDS = 5; // 添加好友
    const MESSAGE_TYPE_READ = 6; // 设置消息已读
}