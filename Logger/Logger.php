<?php
namespace IntegrationHelper\BaseLogger\Logger;

use Laminas\Log\Writer\Stream;
use Laminas\Log\LoggerInterface;
use Laminas\Log\Writer\WriterInterface;
use Laminas\Log\Logger as LaminasLogger;
use IntegrationHelper\BaseLogger\Exceptions\LogTypeIsset;
use Magento\Framework\App\ObjectManager;

/**
 *
 */
class Logger
{
    /**
     *
     */
    protected const DEFAULT_LOG_FOLDER = '/var/log';

    /**
     *
     */
    protected const LOG_FILE_FORMAT = '.log';

    /**
     * @var
     */
    protected static $logger;

    /**
     * @var array
     */
    protected static $loggers = [];

    /**
     * @var bool
     */
    private static $loggerInit = false;

    /**
     * @var array
     */
    protected static $writers = [];

    /**
     * @var string[]
     */
    protected static $logTypes = [
        'alert' => 'alert',
        'crit' => 'crit',
        'debug' => 'debug',
        'info' => 'info',
        'notice' => 'notice',
        'emerg' => 'emerg',
        'err' => 'err',
        'warn' => 'warn',
    ];

    /**
     * @var string[]
     */
    protected static $filepathMap = [
        'alert' => 'log-alert.log',
        'crit' => 'log-critical.log',
        'debug' => 'log-debug.log',
        'info' => 'log-info.log',
        'err' => 'log-error.log',
        'emerg' => 'log-emergency.log',
        'warn' => 'log-warning.log',
        'notice' => 'log-notice.log'
    ];

    /**
     * @param string $logType
     * @param string $filepath
     * @param LoggerCallbackInterface|null $callback
     * @return void
     * @throws LogTypeIsset
     */
    public static function addLogType(string $logType, string $filepath, LoggerCallbackInterface $callback = null)
    {
        if(array_key_exists($logType, static::$logTypes)) {
            throw new LogTypeIsset(sprintf('Logger type %s already exists', $logType));
        }

        static::$logTypes[$logType] = is_object($callback) ? $callback : $logType;
        static::$filepathMap[$logType] = trim(str_replace(
            static::DEFAULT_LOG_FOLDER,
            '',
            str_replace(static::LOG_FILE_FORMAT, '', $filepath)
        ) . static::LOG_FILE_FORMAT, '/');
    }

    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    public static function log(string $message, string $type)
    {
        static::write($message, $type);
    }

    /**
     * @param $message
     * @return void
     */
    public static function alert($message)
    {
        static::log($message, 'alert');
    }

    /**
     * @param $message
     * @return void
     */
    public static function critical($message)
    {
        static::log($message, 'crit');
    }

    /**
     * @param $message
     * @return void
     */
    public static function debug($message)
    {
        static::log($message, 'debug');
    }

    /**
     * @param $message
     * @return void
     */
    public static function info($message)
    {
        static::log($message, 'info');
    }

    /**
     * @param $message
     * @return void
     */
    public static function error($message)
    {
        static::log($message, 'err');
    }

    /**
     * @param $message
     * @return void
     */
    public static function emergency($message)
    {
        static::log($message, 'emerg');
    }

    /**
     * @param $message
     * @return void
     */
    public static function warning($message)
    {
        static::log($message, 'warn');
    }

    /**
     * @param $message
     * @return void
     */
    public static function notice($message)
    {
        static::log($message, 'notice');
    }

    /**
     * @param string $message
     * @param string $type
     * @return void
     */
    private static function write(string $message, string $type)
    {
        static::initLogger();
        $callback = static::$logTypes[$type];

        $logger = static::$loggers[$type] ?? false;
        if(!$logger) {
            static::getLogger()->addWriter(static::getWriter($type));
            static::$loggers[$type] = $logger = static::getLogger();
        }

        if(method_exists($logger, $type)) {
            $logger->{$type}($message);
        } else if(is_object($callback)) {
            $callback->execute(static::getLogger(), $message, $type);
        } else {
            $logger->info($message);
        }
    }

    /**
     * @return LoggerInterface
     */
    private static function getLogger(): LoggerInterface
    {
        if(!static::$logger) {
            static::$logger = new LaminasLogger();
        }

        return static::$logger;
    }

    /**
     * @param string $type
     * @return WriterInterface
     */
    private static function getWriter(string $type): WriterInterface
    {
        if(!array_key_exists($type, static::$writers)) {
            static::$writers[$type] = new Stream(BP . static::getFilePath($type));
        }

        return static::$writers[$type];
    }

    private static function getFilePath($logType)
    {
         return sprintf(
            '%s/%s',
            static::DEFAULT_LOG_FOLDER,
            trim(
                str_replace(
                    static::DEFAULT_LOG_FOLDER,
                    '',
                    str_replace(
                        static::LOG_FILE_FORMAT,
                        '',
                        static::$filepathMap[$logType]
                    )
                ),
                '/') . static::LOG_FILE_FORMAT
        );
    }

    private static function initLogger()
    {
        if(!static::$loggerInit) {
            ObjectManager::getInstance()->get(LoggerInitiator::class);
            static::$loggerInit = true;
        }
    }
}
