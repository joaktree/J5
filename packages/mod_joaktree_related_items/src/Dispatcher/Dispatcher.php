<?php
/**
 * Joomla! module Joaktree related items
 * file		JoaktreeHelper - helper.php
 *
 * @version	1.5.3
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Module linking articles to persons in Joaktree component
 *
 */
namespace Joaktree\Module\Relateditems\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dispatcher class for mod_articles_news
 *
 * @since  4.2.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     *
     * @since   4.2.0
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        return $data;
    }
}
