<?php
/**
 * Joomla! component Joaktree
 * file		std object - StdHelper.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 * This component file was created using the Joomla Component Creator by Not Web Design
 * http://www.notwebdesign.com/joomla_component_creator/
 *
 */

namespace Joaktree\Component\Joaktree\Site\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

class StdHelper extends \StdClass
{
    // From LegacyPropertyManagementTrait
    /**
     * Sets a default value if not already assigned
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $default   The default value.
     *
     * @return  mixed
     *
     */
    public function def($property, $default = null)
    {
        $value = $this->get($property, $default);

        return $this->set($property, $value);
    }

    /**
     * Returns an associative array of object properties.
     *
     * @param   boolean  $public  If true, returns only the public properties.
     *
     * @return  array
     *
     */
    public function getProperties($public = true)
    {
        $vars = get_object_vars($this);

        if ($public) {
            foreach ($vars as $key => $value) {
                if ('_' == substr($key, 0, 1)) {
                    unset($vars[$key]);
                }
            }

            // Collect all none public properties of the current class and it's parents
            $nonePublicProperties = [];
            $reflection           = new \ReflectionObject($this);
            do {
                $nonePublicProperties = array_merge(
                    $reflection->getProperties(\ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED),
                    $nonePublicProperties
                );
            } while ($reflection = $reflection->getParentClass());

            // Unset all none public properties, this is needed as get_object_vars returns now all vars
            // from the current object and not only the CMSObject and the public ones from the inheriting classes
            foreach ($nonePublicProperties as $prop) {
                if (\array_key_exists($prop->getName(), $vars)) {
                    unset($vars[$prop->getName()]);
                }
            }
        }

        return $vars;
    }
    /**
     * Returns a property of the object or the default value if the property is not set.
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $default   The default value.
     *
     * @return  mixed    The value of the property.
     *
     */
    public function get($property, $default = null)
    {
        if (isset($this->$property)) {
            return $this->$property;
        }

        return $default;
    }

    /**
     * Modifies a property of the object, creating it if it does not already exist.
     *
     * @param   string  $property  The name of the property.
     * @param   mixed   $value     The value of the property to set.
     *
     * @return  mixed  Previous value of the property.
     *
     */
    public function set($property, $value = null)
    {
        $previous        = $this->$property ?? null;
        $this->$property = $value;

        return $previous;
    }

    /**
     * Set the object properties based on a named array/hash.
     *
     * @param   mixed  $properties  Either an associative array or another object.
     *
     * @return  boolean
     *
     */
    public function setProperties($properties)
    {
        if (\is_array($properties) || \is_object($properties)) {
            foreach ((array) $properties as $k => $v) {
                // Use the set function which might be overridden.
                $this->set($k, $v);
            }

            return true;
        }

        return false;
    }
}
