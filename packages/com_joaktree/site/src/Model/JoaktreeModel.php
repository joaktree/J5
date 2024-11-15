<?php

/**
 * Joomla! component Joaktree
 * file		front end joaktree model - joaktree.php
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

namespace Joaktree\Component\Joaktree\Site\Model;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joomla\Input\Cookie;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Site\Helper\Person;

class JoaktreeModel extends BaseDatabaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getUserAccess()
    {
        return JoaktreeHelper::getUserAccess();
    }

    public function getAction()
    {
        return JoaktreeHelper::getAction();
    }

    public function getApplicationId()
    {
        return JoaktreeHelper::getApplicationId();
    }

    public function getTreeId()
    {
        return JoaktreeHelper::getTreeId();
    }

    public function getPersonId()
    {
        return JoaktreeHelper::getPersonId();
    }

    public function getTechnology()
    {
        return JoaktreeHelper::getTechnology();
    }

    public function getAccess()
    {
        return true; //JoaktreeHelper::getAccess();
    }

    public function getPerson()
    {
        static $person;
        if (!isset($person)) {
            $id = array();
            $id[ 'app_id' ]		= $this->getApplicationId();
            $id[ 'tree_id' ]	= $this->getTreeId();
            $id[ 'person_id' ]	= $this->getPersonId();
            $person	=  new Person($id, 'full');
        }

        return $person;
    }

    public function setCookie()
    {
        static $indOneTime;

        if (isset($indOneTime) && ($indOneTime)) {
            return true;
        }

        // set up cookie
        $params = JoaktreeHelper::getJTParams();
        $indCookie  = $params->get('indCookies', true);
        if ($indCookie) {
            // we fetch the cookie
            $cookie = new Cookie();
            $tmp	= $cookie->get('jt_last_persons', '', 'string');

            // prepare the array
            if ($tmp) {
                $personList = (array) json_decode(base64_decode($tmp));
            } else {
                $personList = array();
            }

            // check whether this person is already in array
            $person = $this->getPerson();
            $value  = $person->app_id.'!'.$person->id.'!'.$person->tree_id;
            if (in_array($value, $personList)) {
                // loop through array and move person to first position
                $newList   = array();
                $newList[] = $value;
                foreach ($personList as $item) {
                    if ($item != $value) {
                        $newList[] = $item;
                    }
                }

            } else {
                // place the first person to start of array
                $newList = $personList;
                array_unshift($newList, $value);
            }

            // if the array is too big, remove the last person
            if (count($newList) > 10) {
                array_pop($newList);
            }

            // and store the new cookie
            //$expire = mktime().time()+60*60*24*180;
            $expire = time() + 60 * 60 * 24 * 180;
            $cookie->set('jt_last_persons', base64_encode(json_encode($newList)), $expire, '/');
        }

        $indOneTime = true;
        return true;
    }

    public function getLayout()
    {
        if (!isset($this->_layout)) {
            $input = Factory::getApplication()->input;
            $tmp   = $input->get('layout', null, 'word');

            if (isset($tmp)) {
                if ($tmp == 'default'
                   or $tmp == '_children'
                   or $tmp == '_detailnotes'
                   or $tmp == '_detailsources'
                   or $tmp == '_grandchildren'
                   or $tmp == '_grandparents'
                   or $tmp == '_mainnotes'
                   or $tmp == '_mainsources'
                   or $tmp == '_names'
                   or $tmp == '_parents'
                   or $tmp == '_partnerevents'
                   or $tmp == '_partners'
                   or $tmp == '_personevents'
                   or $tmp == '_sourceornotebutton'
                   or $tmp == '_sourceornotetext'
                   or $tmp == '_information'
                   or $tmp == '_article'
                ) {
                    $this->_layout = $tmp;
                } else {
                    $this->_layout = null;
                }
            } else {
                $this->_layout = null;
            }
        }

        return $this->_layout;
    }

    public function getJtType()
    {
        if (!isset($this->_jttype)) {
            $input = Factory::getApplication()->input;
            $tmp   = $input->get('type', null, 'word');

            if (isset($tmp)) {
                if ($tmp == 'person'
                   or $tmp == 'name'
                   or $tmp == 'relation'
                   or $tmp == 'note'
                   or $tmp == 'article'
                ) {
                    $this->_jttype = $tmp;
                } else {
                    $this->_jttype = null;
                }
            } else {
                $this->_jttype = null;
            }
        }

        return $this->_jttype;
    }

    public function getJtSubType()
    {
        if (!isset($this->_jtsubtype)) {
            $input = Factory::getApplication()->input;
            $tmp   = $input->get('subtype', null, 'word');

            if (isset($tmp)) {
                if ($tmp == 'personAll'
                   or $tmp == 'person'
                   or $tmp == 'pevent'
                   or $tmp == 'name'
                   or $tmp == 'note'
                   or $tmp == 'relation'
                   or $tmp == 'revent'
                ) {
                    $this->_jtsubtype = $tmp;
                } else {
                    $this->_jtsubtype = null;
                }
            } else {
                $this->_jtsubtype = null;
            }
        }

        return $this->_jtsubtype;
    }

    public function getOrderNumber()
    {
        if (!isset($this->_orderNumber)) {
            $input = Factory::getApplication()->input;
            $tmp   = $input->get('orderNumber', 0, 'int');

            $this->_orderNumber = intval($tmp);

        }

        return $this->_orderNumber;
    }

    public function getRelationId()
    {
        return JoaktreeHelper::getRelationId();
    }

}
