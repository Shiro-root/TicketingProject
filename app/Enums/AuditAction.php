<?php

namespace App\Enums;

enum AuditAction: string
{
    case LOGIN = 'login';
    case LOGOUT = 'logout';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case RESTORE = 'restore';
    case ASSIGN = 'assign';
    case COMMENT = 'comment';
    case APPROVAL = 'approval';
    case DOWNLOAD_REPORT = 'download_report';
}
