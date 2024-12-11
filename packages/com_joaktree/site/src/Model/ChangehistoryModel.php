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

namespace Joaktree\Component\Joaktree\Site\Model;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\ListModel;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class ChangehistoryModel extends ListModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getTmpl()
    {
        return JoaktreeHelper::getTmpl();
    }

    public function getReturnObject()
    {
        return JoaktreeHelper::getReturnObject();
    }

    public function getListquery()
    {
        $retObj			= $this->getReturnObject();
        if (is_object($retObj)) {
            $object			= (isset($retObj->object)) ? $retObj->object : null;
            $appId			= (isset($retObj->app_id)) ? $retObj->app_id : null;
            $objectId		= (isset($retObj->object_id)) ? $retObj->object_id : null;
        } else {
            $object 		= 'prsn';
        }
        $displayAccess	= JoaktreeHelper::getDisplayAccess();
        $levels			= JoaktreeHelper::getUserAccessLevels();

        $db				= $this->_db;
        $query			= $db->getquery(true);

        // select the basics
        $query->select(' jlg.* ');
        $query->from(' #__joaktree_logs jlg ');
        $query->where(' jlg.object    = :object');

        if (!empty($appId)) {
            $query->where(' jlg.app_id    = :appid');
            $query->bind(':appid', $appId, \Joomla\Database\ParameterType::INTEGER);
        }
        if (!empty($objectId)) {
            $query->where(' jlg.object_id = :objectid');
            $query->bind(':objectid', $objectId, \Joomla\Database\ParameterType::STRING);
        }
        $query->order(' jlg.changeDateTime DESC ');

        // select the name of application
        $query->select(' japp.title AS appname ');
        $query->innerJoin(
            ' #__joaktree_applications  japp '
                         .' ON (japp.id = jlg.app_id) '
        );

        // select the name of the user
        $query->select(' usr.name AS username ');
        $query->leftJoin(
            ' #__users  usr '
                        .' ON (usr.id = jlg.user_id) '
        );

        // person
        if ($object == 'prsn') {
            // select the details of the persons
            $query->select(JoaktreeHelper::getConcatenatedFullName().' AS description ');
            $query->leftJoin(
                ' #__joaktree_persons  jpn '
                            .' ON (   jpn.app_id = jlg.app_id '
                            .'    AND jpn.id     = jlg.object_id '
                            .'    ) '
            );
            $query->leftJoin(JoaktreeHelper::getJoinAdminPersons(true));
            $query->where(
                ' EXISTS '
                         .' ( SELECT     1 '
                         .'   FROM       #__joaktree_tree_persons  jtp '
                         .'   JOIN       #__joaktree_trees         jte '
                         .'   ON         (   jte.app_id    = jtp.app_id '
                         .'              AND jte.id        = jtp.tree_id '
                         .'              AND jte.published = true '
                         .'              AND jte.access    IN '.$levels.' '
                         .'              ) '
                         .'   WHERE      jtp.app_id        = IFNULL(jpn.app_id, jtp.app_id) '
                         .'   AND        jtp.person_id     = IFNULL(jpn.id, jtp.person_id) '
                         .' ) '
            );
        }

        // repository
        if ($object == 'repo') {
            // select the details of the persons
            $query->select(' jry.name AS description ');
            $query->leftJoin(
                ' #__joaktree_repositories  jry '
                            .' ON (   jry.app_id = jlg.app_id '
                            .'    AND jry.id     = jlg.object_id '
                            .'    ) '
            );
        }

        // source
        if ($object == 'sour') {
            // select the details of the persons
            $query->select(' jse.title AS description ');
            $query->leftJoin(
                ' #__joaktree_sources  jse '
                            .' ON (   jse.app_id = jlg.app_id '
                            .'    AND jse.id     = jlg.object_id '
                            .'    ) '
            );
        }

        // select deleted items
        $query->select(' jlr.description AS deletedItem ');
        $query->leftJoin(
            ' #__joaktree_logremovals  jlr '
                        .' ON (   jlr.app_id 	= jlg.app_id '
                        .'    AND jlr.object_id	= jlg.object_id '
                        .'    AND jlr.object	= jlg.object '
                        .'    ) '
        );

        $query->bind(':object', $object, \Joomla\Database\ParameterType::STRING);

        return $query;
    }

    public function getPersonName()
    {
        $personId		= JoaktreeHelper::getPersonId(false, true);
        $appId			= JoaktreeHelper::getApplicationId(false, true);

        if (empty($appId) || empty($personId)) {
            $retObj			= $this->getReturnObject();
            if ((is_object($retObj)) && ($retObj->object == 'prsn')) {
                $appId			= $retObj->app_id;
                $personId		= $retObj->object_id;
            }
        }

        if (!empty($appId) && !empty($personId)) {
            $displayAccess	= JoaktreeHelper::getDisplayAccess();

            $db				= $this->_db;
            $query			= $db->getquery(true);

            $query->select(JoaktreeHelper::getConcatenatedFullName().' AS fullName ');
            $query->from(' #__joaktree_persons  jpn ');
            $query->where(' jpn.app_id = :appid');
            $query->where(' jpn.id     = :personid');
            $query->innerJoin(JoaktreeHelper::getJoinAdminPersons(true));

            $query->bind(':appid', $appId, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $personId, \Joomla\Database\ParameterType::STRING);

            try {
                $db->setquery($query);
                $name = $db->loadResult();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        }

        return (isset($name) ? $name : null);
    }
}
