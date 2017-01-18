<?php
namespace Genetsis\Core\Utils\Contracts;

/**
 * Interface for these classes which contains values as a collection wrapper.
 *
 * @package  Genetsis
 * @category Contract
 */
interface CollectionInterface {

    /**
     * Checks if the required value is valid regard this collection.
     *
     * @param string $value
     * @return boolean TRUE if it is a valid value, FALSE otherwise.
     */
    public static function check($value);

}