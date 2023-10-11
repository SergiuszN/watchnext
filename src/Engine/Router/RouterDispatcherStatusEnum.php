<?php

namespace WatchNext\Engine\Router;

enum RouterDispatcherStatusEnum {
    case NOT_FOUND;
    case METHOD_NOT_ALLOWED;
    case FOUND;
}
