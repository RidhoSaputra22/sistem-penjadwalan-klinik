<?php

namespace App\Enums;

enum AlertTypeEnum : string {
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
}
