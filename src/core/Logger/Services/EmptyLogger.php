<?php namespace Genetsis\core\Logger\Services;

use Genetsis\core\Logger\Contracts\LoggerServiceInterface;

/**
 * Empty logger. Use this logger if you want to disable all logging capabilities without messing all up with
 * conditional controls.
 *
 * @package   Genetsis
 * @category  Service
 */
class EmptyLogger implements LoggerServiceInterface {

    /**
     * @inheritDoc
     */
    public function debug($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function info($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function notice($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function warn($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function warning($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function err($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function error($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function crit($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function critical($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function alert($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emerg($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, $method = null, $line = null)
    {
        // Nothing to do.

        return $this;
    }


}