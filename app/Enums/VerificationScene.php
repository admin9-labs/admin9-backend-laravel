<?php

namespace App\Enums;

enum VerificationScene: string
{
    // 登录相关
    case LOGIN = 'login';

    // 绑定与更新
    case BIND_EMAIL = 'bind-email';
    case BIND_PHONE = 'bind-phone';

    // 重置密码
    case RESET_PASSWORD = 'reset-password';
}
