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
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Table\Table;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeErrorHandlingTrait;
class JoaktreeTable extends Table
{
    use JoaktreeErrorHandlingTrait;
}