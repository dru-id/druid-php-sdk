<?php namespace Genetsis\core\Logger\Services;

use Genetsis\core\Logger\Contracts\LoggerServiceInterface;
use Genetsis\core\Logger\Collections\LogLevels as LogLevelsCollection;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;


/**
 * @package   Genetsis
 * @category  Service
 */
class SyslogLogger implements LoggerServiceInterface {

    /** @var \Monolog\Logger $logger */
    private $logger = null;

    /**
     * @param string $level Any of these defined in {@link LogLevelsCollection}
     */
    public function __construct($level)
    {
        $this->logger = new Logger('druid');
        $handler = new SyslogHandler('DruID', LOG_USER, $this->adaptLevel($level));
        $handler->setFormatter(new LineFormatter("%level_name% %context.method%[%context.line%]: %message%\n", null, true));
        $this->logger->pushHandler($handler);
    }

    /**
     * Adapts custom log level to "Monolog\Logger" level.
     *
     * @param string $level Any log level defined in {@link LogLevelsCollection}
     * @return integer Its equivalen used by {@link \Monolog\Logger}
     */
    protected function adaptLevel ($level)
    {
        switch ($level) {
            default:
            case LogLevelsCollection::DEBUG: return Logger::DEBUG;
            case LogLevelsCollection::INFO: return Logger::INFO;
            case LogLevelsCollection::NOTICE: return Logger::NOTICE;
            case LogLevelsCollection::WARNING: return Logger::WARNING;
            case LogLevelsCollection::ERROR: return Logger::ERROR;
            case LogLevelsCollection::CRITICAL: return Logger::CRITICAL;
            case LogLevelsCollection::ALERT: return Logger::ALERT;
            case LogLevelsCollection::EMERGENCY: return Logger::EMERGENCY;
        }
    }

    /**
     * @inheritDoc
     */
    public function debug($message, $method = null, $line = null)
    {
        $this->logger->debug($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function info($message, $method = null, $line = null)
    {
        $this->logger->info($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function notice($message, $method = null, $line = null)
    {
        $this->logger->notice($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function warn($message, $method = null, $line = null)
    {
        $this->logger->warn($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function warning($message, $method = null, $line = null)
    {
        $this->logger->warning($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function err($message, $method = null, $line = null)
    {
        $this->logger->err($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function error($message, $method = null, $line = null)
    {
        $this->logger->error($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function crit($message, $method = null, $line = null)
    {
        $this->logger->crit($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function critical($message, $method = null, $line = null)
    {
        $this->logger->critical($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function alert($message, $method = null, $line = null)
    {
        $this->logger->alert($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emerg($message, $method = null, $line = null)
    {
        $this->logger->emerg($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, $method = null, $line = null)
    {
        $this->logger->emergency($message, ['method' => $method, 'line' => $line]);
        return $this;
    }

}