<?php

namespace IntegrationHelper\BaseLogger\Logger;

use Laminas\Log\LoggerInterface;

interface LoggerCallbackInterface
{
    public function execute(LoggerInterface $logger, string $message, string $type);
}
