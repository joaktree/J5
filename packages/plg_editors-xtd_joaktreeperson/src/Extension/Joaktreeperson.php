<?php
/**
 * Joomla! plugin Joaktree content
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud (2017-2024)
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Joomla! 5.x conversion by Conseilgouz
 *
 */
namespace Joaktree\Plugin\EditorsXtd\Joaktreeperson\Extension;
// no direct access
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Editor\Button\Button;
use Joomla\Event\SubscriberInterface;

class Joaktreeperson extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    /**
     * @param  $event
     * @return void
     *
     * @since   5.2.0
     */
    public function onEditorButtonsSetup($event): void
    {
        $subject  = $event->getButtonsRegistry();
        $disabled = $event->getDisabledButtons();

        if (\in_array($this->_name, $disabled)) {
            return;
        }

        $button = $this->onDisplay($event->getEditorId());

        if ($button) {
            $subject->add($button);
        }
    }

	function onDisplay($name)
	{
        $this->loadLanguage();
		/*
		 * Javascript to insert the link
		 * View element calls jSelectArticle when an article is clicked
		 * jSelectArticle creates the link tag, sends it to the editor,
		 * and closes the select frame.
		 */
        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $this->getApplication()->getDocument()->getWebAssetManager();

        // Register the asset "editor-button.<button name>", will be loaded by the button layout
        if (!$wa->assetExists('script', 'editor-button.' . $this->_name)) {
            $wa->registerScript(
                'editor-button.' . $this->_name,
                'com_joaktree/admin-article-joaktree-person.js',
                [],
                [],
                ['editors', 'joomla.dialog']
            );
        }
        /*
		 * Use the built-in element view to select the article.
		 * Currently uses blank class.
		 */
		$link = 'index.php?option=com_joaktree&amp;view=persons&amp;layout=element&amp;select=1&amp;tmpl=component&amp;'.Session::getFormToken().'=1';

        $button = new Button(
            $this->_name,
            [
                'action'  => 'modal',
                'link'    => $link,
                'text'    => Text::_('PLG_JOAKTREE_BUTTON_PERSONLINK'),
                'icon'    => 'tree',
                'iconSVG' => '<svg viewBox="0 0 576 512" width="24" height="24"><path d="M519.442 288.651c-41.519 0-59.5 31.593-82.058 31.593C377.'
                    . '409 320.244 432 144 432 144s-196.288 80-196.288-3.297c0-35.827 36.288-46.25 36.288-85.985C272 19.216 243.885 0 210.'
                    . '539 0c-34.654 0-66.366 18.891-66.366 56.346 0 41.364 31.711 59.277 31.711 81.75C175.885 207.719 0 166.758 0 166.758'
                    . 'v333.237s178.635 41.047 178.635-28.662c0-22.473-40-40.107-40-81.471 0-37.456 29.25-56.346 63.577-56.346 33.673 0 61'
                    . '.788 19.216 61.788 54.717 0 39.735-36.288 50.158-36.288 85.985 0 60.803 129.675 25.73 181.23 25.73 0 0-34.725-120.1'
                    . '01 25.827-120.101 35.962 0 46.423 36.152 86.308 36.152C556.712 416 576 387.99 576 354.443c0-34.199-18.962-65.792-56'
                    . '.558-65.792z"></path></svg>',
                // This is whole Plugin name, it is needed for keeping backward compatibility
                'name' => $this->_type . '_' . $this->_name,
            ]
        );

		return $button;
	}
}
