<?php
/**
 * Joomla! component Joaktree
 * file		front end personform controller - personform.php
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

namespace Joaktree\Component\Joaktree\Site\Controller;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;		//replace Route
use Joomla\CMS\Session\Session; 		//replace Session

class PersonformController extends BaseController
{
    public function __construct()
    {
        // first check token
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        // create an input object
        $this->input = Factory::getApplication()->input;

        //Get View
        if ($this->input->get('view') == '') {
            $this->input->set('view', 'personform');
        }
        /*
                if ($this->input->get('task') == 'cancel') {
                    $treeId   = $this->input->get('treeId', null, 'int');
                    $link =  'index.php?option=com_joaktree'
                    .'&view=list'
                    .'&tech=a'
                    .'&treeId='.$treeId;
                    $this->setRedirect(Route::_($link), '');
                }
        */
        parent::__construct();
    }
    public function getModel($name = 'Personform', $prefix = 'Joaktree', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function delete()
    {
        $model = $this->getModel();

        $personId = $this->input->get('personId', null, 'string');
        $treeId   = $this->input->get('treeId', null, 'int');

        $msg = $model->delete($personId);

        $link =  'index.php?option=com_joaktree'
                        .'&view=list'
                        .'&tech=a'
                        .'&treeId='.$treeId;

        $this->setRedirect(Route::_($link), $msg);
    }

    public function edit()
    {
        $personId  = $this->input->get('personId', null, 'string');
        $treeId    = $this->input->get('treeId', null, 'int');
        $object    = $this->input->get('object', null, 'string');

        $link =  'index.php?option=com_joaktree'
                        .'&view=personform'
                        .'&tech=a'
                        .'&treeId='.$treeId;

        switch ($object) {
            case "names":		$link .= '&layout=form_names'
                                        .'&personId='.$personId;
                break;
            case "state":		$link .= '&layout=form_state'
                                        .'&personId='.$personId;
                break;
            case "medialist":	$link .= '&layout=form_medialist'
                                        .'&personId='.$personId;
                break;
            case "media":		$picture = $this->input->get('picture', null, 'string');
                $link .= '&layout=form_media'
                        .'&personId='.$personId
                        .'&picture='.$picture;
                break;
            case "notes":		$link .= '&layout=form_notes'
                                        .'&personId='.$personId;
                break;
            case "references":	$link .= '&layout=form_references'
                                        .'&personId='.$personId;
                break;
            case "pictures":	$link .= '&layout=form_pictures'
                                        .'&personId='.$personId;
                break;
            case "parents":		$link .= '&layout=form_parents'
                                        .'&personId='.$personId;
                break;
            case "partners":	$link .= '&layout=form_partners'
                                        .'&personId='.$personId;
                break;
            case "partnerevents":
                $relationId = $this->input->get('relationId', null, 'string');
                $link .= '&layout=form_partner_events'
                        .'&personId='.$personId
                        .'&relationId='.$relationId;
                break;
            case "children":	$link .= '&layout=form_children'
                                        .'&personId='.$personId;
                break;
            case "newparent":	$tmp = explode('!', $personId);
                $personId = $tmp[0].'!';
                $relationId = $tmp[1];
                $link .= '&layout=default'
                        .'&personId='.$personId
                        .'&relationId='.$relationId
                        .'&action=addparent';
                break;
            case "newpartner":	$tmp = explode('!', $personId);
                $personId = $tmp[0].'!';
                $relationId = $tmp[1];
                $link .= '&layout=default'
                        .'&personId='.$personId
                        .'&relationId='.$relationId
                        .'&action=addpartner';
                break;
            case "newchild":	$tmp = explode('!', $personId);
                $personId = $tmp[0].'!';
                $relationId = $tmp[1];
                $link .= '&layout=default'
                        .'&personId='.$personId
                        .'&relationId='.$relationId
                        .'&action=addchild';
                break;
            case "pevents":		// continue
            default:			$link .= '&layout=default'
                                        .'&personId='.$personId;
                break;
        }
        if (!isset($msg)) {
            $msg = '';
        }

        $this->setRedirect(Route::_($link), $msg);
    }

    public function save()
    {
        if ((bool)Factory::getApplication()->getConfig()->get('debug')) { // Mode debug
            Factory::getApplication()->enqueueMessage("Sauvegarde site controllers/personform");
        }
        $model = $this->getModel();

        $form     	= $this->input->get('jform', null, 'array');
        $treeId   	= $this->input->get('treeId', null, 'int');

        $msg = $model->save($form);

        $link =  'index.php?option=com_joaktree'
                        .'&tech=a'
                        .'&treeId='.$treeId;

        switch ($form['type']) {
            case "media":
                $link .=  '&view=personform'
                         .'&layout=form_medialist';
                break;
            default:
                $link .= '&view=joaktree'
                        .'&action=edit';
                break;
        }

        switch ($form['action']) {
            case "addchild":	// continue
            case "addparent":	// continue
            case "addpartner":
                $link .= '&personId='.$form['person']['app_id'].'!'.$form['person']['relations']['id'][0];
                break;
            default:
                $link .= '&personId='.$form['person']['app_id'].'!'.$form['person']['id'];
                break;
        }

        $this->setRedirect(Route::_($link), $msg);
    }


    public function select()
    {
        $treeId	= $this->input->get('treeId', null, 'int');
        $form	= $this->input->get('jform', null, 'array');
        if (!isset($msg)) {
            $msg = '';
        }
        $link =  'index.php?option=com_joaktree'
                        .'&view=joaktree'
                        .'&tech=a'
                        .'&action=edit'
                        .'&treeId='.$treeId;

        if (empty($form['person']['relations']['id'][0])) {
            // It is a person without relationship .. just continue
            $link .=  '&personId='.$form['person']['app_id'].'!'.$form['person']['id'];
        } else {
            // It is a person with relationship .. save the relationship
            $model = $this->getModel();
            $msg = $model->save($form);

            if (($form['action'] == 'addparent')
               || ($form['action'] == 'addpartner')
               || ($form['action'] == 'addchild')
            ) {
                $link .= '&personId='.$form['person']['app_id'].'!'.$form['person']['relations']['id'][0];
            } else {
                $link .= '&personId='.$form['person']['app_id'].'!'.$form['person']['id'];
            }
        }

        $this->setRedirect(Route::_($link), $msg);
    }

    public function cancel()
    {
        $form	= $this->input->get('jform', null, 'array');
        $treeId	= $this->input->get('treeId', null, 'int');
        $link 	=  'index.php?option=com_joaktree'
                        .'&tech=a'
                        .'&treeId='.$treeId;

        switch ($form['type']) {
            case "parents":		// continue
            case "partners":	// continue
            case "children":
                $link .= '&view=joaktree'
                        .'&personId='.$form['person']['app_id'].'!'.$form['person']['id']
                        .'&action=edit';
                break;
            case "media":	$link .= '&view=personform'
                                    .'&personId='.$form['person']['app_id'].'!'.$form['person']['id']
                                    .'&layout=form_medialist';
                break;
            default:
                break;
        }
        if (!isset($msg)) {
            $msg = '';
        }
        $this->setRedirect(Route::_($link), $msg);
    }
}
