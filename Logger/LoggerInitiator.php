<?php

namespace IntegrationHelper\BaseLogger\Logger;

class LoggerInitiator
{
    public function __construct(array $loggerTypes)
    {
        foreach ($loggerTypes as $type) {
            $logType = $type['log_type'] ?? false;
            $filepath = $type['filepath'] ?? false;
            if($logType && $filepath) {
                $callback = $type['callback'] ?? false;
                if($callback instanceof LoggerCallbackInterface) {
                    Logger::addLogType($logType, $filepath, $callback);
                    continue;
                }
                Logger::addLogType($logType, $filepath);
            }
        }
    }
}
