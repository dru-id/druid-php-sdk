<?php
namespace Genetsis\core\Config\Beans\Log;

use Genetsis\core\Logger\Collections\LogLevels;

/**
 * This class keep config parameters when log events should be sent to system log.
 *
 * @package  Genetsis
 * @category Bean
 */
class Syslog extends AbstractLog {

    /**
     * @param string $group Name to keep logs under the same group.
     * @param string $log_level One of defined at {@link \Genetsis\core\Logger\Collections\LogLevels}
     */
    public function __construct($group, $log_level = LogLevels::DEBUG)
    {
        parent::__construct($group, $log_level);
    }

}