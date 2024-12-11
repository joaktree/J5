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

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joomla\Input\Cookie;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class JoaktreestartModel extends BaseDatabaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getTreeId()
    {
        return JoaktreeHelper::getTreeId();
    }

    public function getUserAccess()
    {
        return JoaktreeHelper::getUserAccess();
    }

    public static function getAccess()
    {
        return JoaktreeHelper::getAccessTree();
    }

    public function getNameIndex()
    {
        $treeId = $this->getTreeId();
        $app 	= Factory::getApplication('site');
        $index	= $app->getUserState('joaktree.names.index.'.$treeId);

        if (!$index) {
            $index = array();

            // retrieve the index information from the database
            $query = $this->_db->getquery(true);
            $query->select(' DISTINCT jpn.indexNam ');
            $query->from(' #__joaktree_trees  jte ');
            $query->innerJoin(
                ' #__joaktree_persons  jpn '
                             .' ON ( jpn.app_id = jte.app_id ) '
            );
            $query->where(' jte.id = :treeid');
            $query->where(' jpn.familyName <> '.$this->_db->quote('').' ');
            $query->order(' jpn.indexNam ');
            $query->bind(':treeid', $treeId, \Joomla\Database\ParameterType::INTEGER);

            $this->_db->setquery($query);
            $results = $this->_db->loadRowList();
            $params = JoaktreeHelper::getJTParams();
            $groupCount = (int) $params->get('groupCount', '3');
            $i = 0;
            $n = 0;

            foreach ($results as $result) {
                $n++;
                $index[$i][$n] = array_shift($result);
                if (($n == $groupCount) || ($groupCount == 0)) {
                    $i++;
                    $n = 0;
                }
            }

            $app->setUserState('joaktree.names.index.'.$treeId, $index);
        }

        return $index;
    }

    public static function getNameFilter()
    {
        static $filterId;

        if (!isset($filterId)) {
            $app 		= Factory::getApplication('site');
            $params 	= $app->getParams();
            $indCookie  = $params->get('indCookies', true);

            if ($indCookie) {
                // we return the cookie
                $cookie   = new Cookie();
                $filterId = $cookie->get('jt_nam_index', 0, 'int');
            } else {
                // we return 0
                $filterId	= 0;
            }
        }

        return $filterId;
    }

    private function _buildPersonCountquery()
    {
        $treeId         = intval($this->getTreeId());
        $query	= $this->_db->getquery(true);
        $query->select(' COUNT(jtp.person_id)          AS personCount ');
        $query->from(' #__joaktree_trees             jte ');
        $query->where(' jte.id = :treeid');
        $query->where(' jte.access IN '.JoaktreeHelper::getUserAccessLevels().' ');

        $query->innerJoin(
            ' #__joaktree_tree_persons   jtp '
                         .' ON (   jtp.tree_id   = jte.id '
                         .'    AND jtp.app_id    = jte.app_id '
                         .'    ) '
        );
        $query->innerJoin(JoaktreeHelper::getJoinAdminPersons(true, 'jtp'));

        $query->bind(':treeid', $treeId, \Joomla\Database\ParameterType::INTEGER);

        return $query;
    }

    private function _buildMarriageCountquery()
    {
        $treeId         = intval($this->getTreeId());
        $query	= $this->_db->getquery(true);

        $query->select(' COUNT(jre.app_id)             AS marriageCount ');
        $query->from(' #__joaktree_trees             jte ');
        $query->where(' jte.id = :treeid');
        $query->where(' jte.access IN '.JoaktreeHelper::getUserAccessLevels().' ');

        $query->innerJoin(
            ' #__joaktree_tree_persons      jtp '
                         .' ON (   jtp.tree_id     = jte.id '
                         .'    AND jtp.app_id      = jte.app_id '
                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_relations         jre '
                         .' ON (   jre.app_id      = jtp.app_id '
                         .'    AND jre.person_id_1 = jtp.person_id '
                         .'    AND jre.type        = '.$this->_db->Quote('partner').' '
                         .'    ) '
        );
        $query->innerJoin(JoaktreeHelper::getJoinAdminPersons(true, 'jre', 1));
        $query->innerJoin(JoaktreeHelper::getJoinAdminPersons(true, 'jre', 2));

        $query->bind(':treeid', $treeId, \Joomla\Database\ParameterType::INTEGER);

        return $query;
    }

    private function _buildquery()
    {
        $params			= JoaktreeHelper::getJTParams(); //ComponentHelper::getParams('com_joaktree') ;
        $abbreviation	= (int) $params->get('abbreviation', 0);
        $levels			= JoaktreeHelper::getUserAccessLevels();
        $displayAccess	= JoaktreeHelper::getDisplayAccess();

        if (($abbreviation == null) or ($abbreviation <= 0)) {
            // no abbreviation of names
            $displayFamilyName = JoaktreeHelper::getConcatenatedDutchFamilyName(false);

        } else {
            // abbreviation on n characters, where n = $abbreviation
            $displayFamilyName = 'SUBSTR( '.JoaktreeHelper::getConcatenatedDutchFamilyName(false).' '
                                .'      , 1 '
                                .'      , '.$abbreviation.' '
                                .'      )';
        }

        $query	= $this->_db->getquery(true);

        $query->select(' COUNT('.$displayFamilyName.') AS nameCount ');
        $query->select(' '.$displayFamilyName.' AS familyName ');
        $query->from(' #__joaktree_tree_persons jtp ');
        $query->innerJoin(
            ' #__joaktree_trees        jte '
                         .' ON (   jte.app_id = jtp.app_id '
                         .'    AND jte.id     = jtp.tree_id '

                         .'    ) '
        );
        $query->innerJoin(
            ' #__joaktree_persons      jpn '
                         .' ON (   jpn.app_id = jtp.app_id '
                         .'    AND jpn.id     = jtp.person_id '
                         .'    ) '
        );
        $query->innerJoin(JoaktreeHelper::getJoinAdminPersons(false));

        // Get the WHERE clauses for the query
        $query      	=  $this->_buildContentWhere($query);
        $query->group(' '.$displayFamilyName.' ');

        return $query;
    }

    private function _buildContentWhere($query)
    {
        $app 		= Factory::getApplication('site');

        // always from the request
        $tmp		= $app->input->get('filter', null, 'string');

        // if nothing found, check the cookie -- only when we use cookies
        if (!isset($tmp)) {
            $params 	= $app->getParams();

            if ($params->get('indCookies', true)) {
                $cookie = new Cookie();
                $tmp 	= $cookie->get('jt_nam_index', 0, 'int');
            }
        }

        // if nothing found, we use 0
        $filterId = (!isset($tmp)) ? 0 : (int) $tmp;
        $index  	= $this->getNameIndex();
        $filters	= $index[$filterId];
        // done with filter for index

        $treeId 		= intval($this->getTreeId());

        $query->where('jpn.familyName <> '.$this->_db->quote('').' ');

        if ($treeId) {
            $query->where('jtp.tree_id = :treeid');
            $query->where('jte.access IN ' . JoaktreeHelper::getUserAccessLevels());
        }

        if ($filters) {
            if (count($filters) == 1) {
                $query->where(' jpn.indexNam =  "'.array_shift($filters).'" ');
            } else {
                $query->where(' jpn.indexNam IN ("'.implode('","', $filters).'" ) ');
            }
        }
        $query->bind(':treeid', $treeId, \Joomla\Database\ParameterType::INTEGER);
        return $query;
    }

    public function getNamelist()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_namelist)) {
            $query = $this->_buildquery();
            $this->_namelist = $this->_getList($query);
        }

        return $this->_namelist;
    }

    public function getTreeinfo()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_treeinfo)) {
            $treeId = intval($this->getTreeId());
            $query	= $this->_db->getquery(true);

            $query->select(' * ');
            $query->from(' #__joaktree_trees ');
            $query->where(' id = :treeid');
            $query->where(' access IN '.JoaktreeHelper::getUserAccessLevels().' ');

            $query->bind(':treeid', $treeId, \Joomla\Database\ParameterType::INTEGER);

            $this->_db->setquery($query);
            $this->_treeinfo = $this->_db->loadObject();
        }

        return $this->_treeinfo;
    }

    public function getPersonCount()
    {
        // Lets load the number of people in this tree
        if (empty($this->_personCount)) {
            $query = $this->_buildPersonCountquery();
            $this->_db->setquery($query);
            $this->_personCount = $this->_db->loadResult();
        }

        return $this->_personCount;
    }

    public function getMarriageCount()
    {
        // Lets load the number of partner-relationships in this tree
        if (empty($this->_marriageCount)) {
            $query = $this->_buildMarriageCountquery();
            $this->_db->setquery($query);
            $this->_marriageCount = $this->_db->loadResult();
        }

        return $this->_marriageCount;
    }

    public function getMenus()
    {
        static $_menuTreeId 	= array();

        // retrieve the menu item ids - if not done yet
        if (count($_menuTreeId) == 0) {
            $_menuTreeId = JoaktreeHelper::getMenus('list');
        }

        return $_menuTreeId;
    }

    public function getLastUpdate()
    {
        return JoaktreeHelper::lastUpdateDateTime();
    }
}
