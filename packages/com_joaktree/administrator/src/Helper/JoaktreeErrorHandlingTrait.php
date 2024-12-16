<?php

/**
 * Joomla! component Joaktree
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2024)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * Joomla! 5.x conversion by Conseilgouz
 *
 */

namespace Joaktree\Component\Joaktree\Administrator\Helper;

\defined('_JEXEC') or die;

/**
 * From deprecated Joomla LegacyErrorHandlingTrait 
 *
 * Trait which contains the legacy methods that formerly were inherited from \Joomla\CMS\Object\CMSObject to set and
 * get errors in a class.
 *
 *
 */
trait JoaktreeErrorHandlingTrait
{
    /**
     * An array of error messages or Exception objects.
     *
     * @var    array
     */
    protected $_errors = [];

    /**
     * Get the most recent error message.
     *
     * @param   integer  $i         Option error index.
     * @param   boolean  $toString  Indicates if Exception objects should return their error message.
     *
     * @return  string   Error message
     *
     */
    public function getError($i = null, $toString = true)
    {
        // Find the error
        if ($i === null) {
            // Default, return the last message
            $error = end($this->_errors);
        } elseif (!\array_key_exists($i, $this->_errors)) {
            // If $i has been specified but does not exist, return false
            return false;
        } else {
            $error = $this->_errors[$i];
        }

        // Check if only the string is requested
        if ($error instanceof \Exception && $toString) {
            return $error->getMessage();
        }

        return $error;
    }

    /**
     * Return all errors, if any.
     *
     * @return  array  Array of error messages.
     *
     *
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Add an error message.
     *
     * @param   string  $error  Error message.
     *
     * @return  void
     *
     */
    public function setError($error)
    {
        $this->_errors[] = $error;
    }
}
