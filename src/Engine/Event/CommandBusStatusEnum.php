<?php

namespace WatchNext\Engine\Event;

enum CommandBusStatusEnum: int
{
    case NEW = 0;
    case IN_PROGRESS = 1;
    case SUCCESS = 10;
    case ERROR = 11;
}
