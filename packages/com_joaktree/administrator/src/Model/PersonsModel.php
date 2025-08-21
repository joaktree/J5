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

namespace Joaktree\Component\Joaktree\Administrator\Model;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Pagination\Pagination;
use Joomla\Registry\Registry;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class PersonsModel extends ListModel
{
    public $_persons;
    public $_trees;
    public $_pagination = null;
    public $_total         = null;

    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        parent::__construct($config, $factory);

        $app = Factory::getApplication();

        $context	= 'com_joaktree.persons.list.';

        // Get the pagination request variables
        $limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $limitstart	= $app->getUserStateFromRequest($context.'limitstart', 'limitstart', 0, 'int');

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $tree		= $app->getUserStateFromRequest($context.'filter_tree', 'filter_tree', '', 'int');
        $this->setState('filter_tree', $tree);

    }

    private function _buildquery()
    {
        $query = $this->_db->getQuery(true);

        // persons
        $query->select(' jpn.app_id                     AS app_id ');
        $query->select(' jpn.id                	        AS id ');
        $query->select(' MIN( jpn.firstName )        	AS firstName ');
        $query->select(' MIN( jpn.patronym )         	AS patronym ');
        $query->select(JoaktreeHelper::getConcatenatedFamilyName().' AS familyName ');
        $query->select(' MIN( jpn.sex )              	AS sex ');
        $query->from(' #__joaktree_persons jpn ');

        // person administration
        $query->select(' MIN( jan.default_tree_id )  	AS default_tree_id ');
        $query->select(' MIN( jan.published )         	AS published ');
        $query->select(' MIN( jan.living )            	AS living ');
        $query->select(' MIN( jan.page )              	AS page ');
        $query->select(' MIN( jan.map )              	AS map ');
        $query->select(' MIN( jan.robots )            	AS robots ');
        $query->innerJoin(
            ' #__joaktree_admin_persons  jan'
                         .' ON (   jan.app_id = jpn.app_id '
                         .'    AND jan.id     = jpn.id '
                         .'    ) '
        );

        // applications
        $query->select(' MIN( japp.title )              AS appTitle ');
        $query->innerJoin(
            ' #__joaktree_applications  japp '
                         .' ON (japp.id = jpn.app_id) '
        );

        // trees
        $query->select(' MIN( jte.name )              	AS familyTree ');
        $query->leftJoin(
            ' #__joaktree_trees jte '
                        .' ON (   jte.app_id     = jan.app_id '
                        .'    AND jte.id         = jan.default_tree_id '
                        .'    ) '
        );
        if ($this->getState('filter_tree')) {
            $query->leftJoin(
                ' #__joaktree_tree_persons jtpe '
                            .' ON (   jtpe.app_id     = jan.app_id '
                            .'    AND jtpe.person_id  = jan.id '
                            .'    ) '
            );

        }
        // births
        $query->select(' MIN( jpe1.eventDate )         	AS birthDate ');
        $query->select(' MIN( jpe1.location )        	AS birthPlace ');
        $query->leftJoin(
            ' #__joaktree_person_events jpe1 '
                        .' ON (   jpe1.app_id    = jpn.app_id '
                        .'    AND jpe1.person_id = jpn.id '
                        .'    AND jpe1.code = '.$this->_db->Quote('BIRT').' '
                        .'    ) '
        );

        // deaths
        $query->select(' MIN( jpe2.eventDate )         	AS deathDate ');
        $query->select(' MIN( jpe2.location )        	AS deathPlace ');
        $query->leftJoin(
            ' #__joaktree_person_events jpe2 '
                        .' ON (   jpe2.app_id    = jpn.app_id '
                        .'    AND jpe2.person_id = jpn.id '
                        .'    AND jpe2.code = '.$this->_db->Quote('DEAT').' '
                        .'    ) '
        );

        // burials
        $query->select(' MIN( jpe3.eventDate )        	AS burialDate ');
        $query->select(' MIN( jpe3.location )         	AS burialPlace ');
        $query->leftJoin(
            ' #__joaktree_person_events jpe3 '
                        .' ON (   jpe3.app_id    = jpn.app_id '
                        .'    AND jpe3.person_id = jpn.id '
                        .'    AND jpe3.code = '.$this->_db->Quote('BURI').' '
                        .'    ) '
        );

        // person - period
        $attribs = array();
        $attribs[] = ' IFNULL(SUBSTR( RTRIM(jpe1.eventDate), -4 ), '.$this->_db->Quote('?').' ) ';
        $attribs[] = ' SUBSTR(IFNULL( RTRIM(jpe2.eventDate), RTRIM(jpe3.eventDate) ), -4) ';
        $query->select(' MIN('.$query->concatenate($attribs, ' - ').') AS period ');

        // WHERE, GROUP BY and ORDER BY clauses for the query
        $query  =  $this->_buildContentWhere($query);
        $query->group(' jpn.id ');
        $query->group(' jpn.app_id ');
        $query->group(' jpn.namePreposition');
        $query->group(' jpn.familyName');
        $query->order(' '.$this->_buildContentOrderBy().' ');
        // ready
        return $query;
    }

    private function _buildContentWhere($query)
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.persons.list.';
        $filter_state	= $app->getUserStateFromRequest($context.'filter_state', 'filter_state', '', 'cmd');
        $filter_living	= $app->getUserStateFromRequest($context.'filter_living', 'filter_living', '', 'word');
        $filter_page	= $app->getUserStateFromRequest($context.'filter_page', 'filter_page', '', 'word');
        $filter_map		= $app->getUserStateFromRequest($context.'filter_map', 'filter_map', -1, 'int');
        $filter_tree	= $app->getUserStateFromRequest($context.'filter_tree', 'filter_tree', 0, 'int');
        $filter_apptitle = $app->getUserStateFromRequest($context.'filter_apptitle', 'filter_apptitle', 0, 'int');
        $filter_robots	= $app->getUserStateFromRequest($context.'filter_robots', 'filter_robots', -1, 'int');
        $search1		= $app->getUserStateFromRequest($context.'search1', 'search1', '', 'string');
        $search1		= strtolower($search1);
        $search2		= $app->getUserStateFromRequest($context.'search2', 'search2', '', 'string');
        $search2		= strtolower($search2);
        $search3		= $app->getUserStateFromRequest($context.'search3', 'search3', '', 'string');
        $search3		= strtolower($search3);

        if ($search1) {
            $query->where('LOWER(jpn.firstName) LIKE '.$this->_db->Quote('%'.$search1.'%'));
        }

        if ($search2) {
            $query->where('LOWER(jpn.patronym) LIKE '.$this->_db->Quote('%'.$search2.'%'));
        }

        if ($search3) {
            $query->where('LOWER(jpn.familyName) LIKE '.$this->_db->Quote('%'.$search3.'%'));
        }

        if (!($filter_state === '')) {
            $query->where('jan.published = :filter');
            $query->bind(':filter', $filter_state, \Joomla\Database\ParameterType::INTEGER);
        }

        if ($filter_living) {
            if ($filter_living == 'L') {
                $query->where('jan.living = 1');
            } elseif ($filter_living == 'D') {
                $query->where('jan.living = 0');
            }
        }

        if ($filter_page) {
            if ($filter_page == 'Y') {
                $query->where('jan.page = 1');
            } elseif ($filter_page == 'N') {
                $query->where('jan.page = 0');
            }
        }

        if ($filter_map >= 1) {
            $query->where('jan.map = :filtermap');
            $query->bind(':filtermap', $filter_map - 1, \Joomla\Database\ParameterType::INTEGER);
        }

        if ($filter_tree != 0) {
            $query->where('jtpe.tree_id = :treeid');
            $query->bind(':treeid', $filter_tree, \Joomla\Database\ParameterType::INTEGER);
        }

        if ($filter_apptitle != 0) {
            $query->where('jpn.app_id = :appid');
            $query->bind(':appid', $filter_apptitle, \Joomla\Database\ParameterType::INTEGER);
        }

        if ($filter_robots >= 1) {
            $query->where('jan.robots = :robots');
            $query->bind(':robots', $filter_robots - 1, \Joomla\Database\ParameterType::INTEGER);
        }

        return $query;
    }

    private function _buildContentOrderBy()
    {
        $app = Factory::getApplication();

        $context		= 'com_joaktree.persons.list.';
        $filter_order		= $app->getUserStateFromRequest($context.'filter_order', 'filter_order', 'jpn.id', 'cmd');
        $filter_order_Dir	= $app->getUserStateFromRequest($context.'filter_order_Dir', 'filter_order_Dir', '', 'word');

        if ($filter_order) {
            $orderby 	= ' '.$filter_order.' '.$filter_order_Dir.' ';
        } else {
            $orderby 	= ' japp.id '.$filter_order_Dir.' ';
        }

        return $orderby;
    }

    public function getPersons()
    {
        $query = $this->_buildquery();
        $this->_persons = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        if (!count($this->_persons) && $this->getState('limitstart') > 0) {
            // fix pagination : not on first page : retry
            $this->setState('limitstart', 0);
            $this->_persons = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_persons;
    }

    public function getTrees()
    {
        $query = $this->_db->getQuery(true);
        $query->select(' id ');
        $query->select(' name ');
        $query->from(' #__joaktree_trees ');
        $query->order(' name ');

        $this->_trees = $this->_getList($query);

        return $this->_trees;
    }

    public function publish()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput(); 
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');
            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' published = 1');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }
            $args = [
            'context' => 'com_joaktree.person',
            'subject' => $cids,
            'value'   => 1,
            ];
            Factory::getApplication()->getDispatcher()->dispatch(
                'onFinderChangeState',
                new FinderEvent\AfterChangeStateEvent('onFinderChangeState', $args)
            );

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function unpublish()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');
            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' published = 0');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }
            $args = [
                'context' => 'com_joaktree.person',
                'subject' => $cids,
                'value'   => 0,
                ];
            Factory::getApplication()->getDispatcher()->dispatch(
                'onFinderChangeState',
                new FinderEvent\AfterChangeStateEvent('onFinderChangeState', $args)
            );
            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function publishAll()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' published = 1 ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }
            $args = [
                'context' => 'com_joaktree.person',
                'subject' => $cids,
                'value'   => 1,
                ];
            Factory::getApplication()->getDispatcher()->dispatch(
                'onFinderChangeState',
                new FinderEvent\AfterChangeStateEvent('onFinderChangeState', $args)
            );
            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function unpublishAll()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' published = 0 ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }
            $args = [
                'context' => 'com_joaktree.person',
                'subject' => $cids,
                'value'   => 0,
                ];
            Factory::getApplication()->getDispatcher()->dispatch(
                'onFinderChangeState',
                new FinderEvent\AfterChangeStateEvent('onFinderChangeState', $args)
            );
            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function living()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' living = !living ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function livingAll()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' living = 1 ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function notLivingAll()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' living = 0 ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function page()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' page = !page ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);
                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function pageAll()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' page = 1 ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function noPageAll()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' page = 0 ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function mapStatAll()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' map = 1 ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function mapDynAll()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' map = 2 ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function noMapAll()
    {
        $canDo	= JoaktreeHelper::getActions();
        $this->input = Factory::getApplication()->getInput();
        if ($canDo->get('core.edit')) {
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid_num => $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' map = 0 ');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }

            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function save()
    {
        $canDo	= JoaktreeHelper::getActions();
        Factory::getApplication()->enqueueMessage("Sauvegarde models/persons");
        if ($canDo->get('core.edit')) {
            $this->input = Factory::getApplication()->getInput();
            $cids	= $this->input->get('cid', null, 'array');

            foreach ($cids as $cid) {
                $id	 = explode('!', $cid);
                $id[0] = (int) $id[0];         // This is app_id
                $id[1] = substr($id[1], 0, (int) JoaktreeHelper::getIdlength()); // This is person_id

                $robot	= $input->get('robot'.$cid, null, 'int');
                $map	= $input->get('map'.$cid, null, 'int');

                $query = $this->_db->getQuery(true);
                $query->update(' #__joaktree_admin_persons ');
                $query->set(' robots = :robots');
                $query->set(' map    = :map');
                $query->where(' app_id = :appid');
                $query->where(' id     = :personid');
                $query->bind(':appid', $id[0], \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':personid', $id[1], \Joomla\Database\ParameterType::STRING);
                $query->bind(':robots', $robot - 1, \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':map', $map - 1, \Joomla\Database\ParameterType::INTEGER);

                $this->_db->setQuery($query);
                $this->_db->execute(); //$this->_db->query();
            }
            $return = Text::sprintf('JTADMIN_PERSONS_UPDATED', count($cids));
        } else {
            $return = Text::_('JT_NOTAUTHORISED');
        }

        return $return;
    }

    public function getTotal()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_total)) {
            $query = $this->_buildquery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

    public function getPagination()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            $this->_pagination = new Pagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
        }

        return $this->_pagination;
    }

    public function getPatronymShowing()
    {
        $showPatr = false;

        // because patronym setting may differ for different applications
        // when at least one application has patronym showing is true
        // patronym column will be shown in the administrator.
        $query = $this->_db->getQuery(true);
        $query->select(' japp.params ');
        $query->from(' #__joaktree_applications  japp ');
        $this->_db->setQuery($query);
        $results = $this->_db->loadObjectList();

        foreach ($results as $result) {
            // load parameters into registry object
            $registry = new Registry();
            $registry->loadString($result->params, 'JSON');
            $patr = $registry->get('patronym');

            if ($patr > 0) {
                $showPatr = true;
            }

            unset($registry);
        }

        return $showPatr;
    }

    // Pascal : is this ussed somewhere ?? there aint no cookie in admin, so ?????
    // it's called in PersonsModel and "used" in Persons/HtmlView to show/hide some columns
    // but, nobody creates this cookie...

    public function getColumnSettings()
    {
        $columns 	= array();
        $params  	= ComponentHelper::getParams('com_joaktree') ;
        $indCookie	= $params->get('indCookies', true);

        if ($indCookie) {
            // retrieve info from cookies
            $input = Factory::getApplication()->getInput();

            // column GedCom = applications
            $tmp	= $input->cookie->getString('app', null);
            $columns['app'] = (isset($tmp) && ($tmp == '1')) ? true : false;
            unset($tmp);

            // column patronyms
            $tmp	= $input->cookie->getString('pat', null);
            $columns['pat'] = (isset($tmp) && ($tmp == '1')) ? true : false;
            unset($tmp);

            // column periods
            $tmp	= $input->cookie->getString('per', null);
            $columns['per'] = (isset($tmp) && ($tmp == '1')) ? true : false;
            unset($tmp);

            // column default trees
            $tmp	= $input->cookie->getString('tree', null);
            $columns['tree'] = (isset($tmp) && ($tmp == '1')) ? true : false;
            unset($tmp);

            // column map
            $tmp	= $input->cookie->getString('map', null);
            $columns['map'] = (isset($tmp) && ($tmp == '1')) ? true : false;
            unset($tmp);

            // column robots
            $tmp	= $input->cookie->getString('rob', null);
            $columns['rob'] = (isset($tmp) && ($tmp == '1')) ? true : false;
            unset($tmp);
        } else {
            // no cookies are used -> all columns will be shown
            // column GedCom = applications
            $columns['app'] = false;

            // column patronyms
            $columns['pat'] = false;

            // column periods
            $columns['per'] = false;

            // column default trees
            $columns['tree'] = false;

            // column map
            $columns['map'] = false;

            // column robots
            $columns['rob'] = false;
        }

        return $columns;
    }
}
