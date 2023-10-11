<?php

return [
    'home_index' => ['GET', '/', \WatchNext\App\Application\Controller\HomepageController::class . '::' . 'index'],
    'home_test' => ['GET', '/test', \WatchNext\App\Application\Controller\HomepageController::class . '::' . 'index'],
];