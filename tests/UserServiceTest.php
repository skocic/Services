<?php

require __DIR__ . '/../libraries/autoload.php';

use Services\UserService;

$service = UserService::create();

$result = $service->unblockById('548c78cef1ac618e338b4567');

var_dump($result);
