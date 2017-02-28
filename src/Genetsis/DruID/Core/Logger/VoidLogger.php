<?php namespace Genetsis\DruID\Core\Logger;

use Psr\Log\LoggerInterface;

/**
 * Use this logger if you want to disable all logging capabilities without messing all up with conditional controls.
 *
 * @package   Genetsis\DruID
 * @category  Service
 */
class VoidLogger implements LoggerInterface
{

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = array())
    {
        // Nothing to do here.
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = array())
    {
        // Nothing to do here.
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = array())
    {
        // Nothing to do here.
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = array())
    {
        // Nothing to do here.
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = array())
    {
        // Nothing to do here.
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = array())
    {
        // Nothing to do here.
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = array())
    {
        // Nothing to do here.
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = array())
    {
        // Nothing to do here.
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        // Nothing to do here.
    }

}