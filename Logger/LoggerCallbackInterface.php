<?php

namespace IntegrationHelper\BaseLogger\Logger;


use Psr\Log\LoggerInterface;

interface LoggerCallbackInterface
{
    public function execute(LoggerInterface $logger, string $message, string $type);
}
