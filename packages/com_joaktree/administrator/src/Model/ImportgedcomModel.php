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

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\Trees;
use Joaktree\Component\Joaktree\Administrator\Helper\Gedcomfile2;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class processObject
{
    public $id			= null;
    public $start		= null;
    public $current	= null;
    public $end		= null;
    public $cursor		= 0;
    public $persons	= 0;
    public $families	= 0;
    public $sources	= 0;
    public $repos		= 0;
    public $notes		= 0;
    public $docs		= 0;
    public $unknown	= 0;
    public $japp_ids	= null;
    public $status		= 'new';
    public $msg		= null;
}

class ImportgedcomModel extends BaseDatabaseModel
{
    public $_data;
    public $_pagination 	= null;
    public $_total         = null;

    public function __construct()
    {
        parent::__construct();
        // $this->jt_registry = Table::getInstance('RegistryitemsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\', array('dbo' => $this->_db));
        $this->jt_registry = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Registryitems');
    }

    private function _buildquery()
    {
        // Get the WHERE and ORDER BY clauses for the query
        $wheres      =  $this->_buildContentWhere();

        if (($wheres) && (is_array($wheres))) {
            $query = $this->_db->getQuery(true);
            $query->select(' japp.* ');
            $query->from(' #__joaktree_applications  japp ');
            foreach ($wheres as $where) {
                $query->where(' '.$where.' ');
            }
            $query->order(' japp.id ');

        } else {
            // if there is no where statement, there are no applications selected.
            unset($query);
        }

        return $query;
    }

    private function _buildContentWhere()
    {
        $wheres = array();

        $procObject = $this->getProcessObject();
        $cids = $procObject->japp_ids;
        array_unshift($cids, $procObject->id);

        if (count($cids) == 0) {
            // no applications are selected
            return false;

        } else {
            // make sure the input consists of integers
            for ($i = 0;$i < count($cids);$i++) {
                $cids[$i] = (int) $cids[$i];

                if ($cids[$i] == 0) {
                    die('wrong request');
                }
            }

            // create a string
            $japp_ids = '('.implode(",", $cids).')';

            // create where
            $wheres[] = 'japp.id IN '.$japp_ids;

        }

        return $wheres;
    }

    public function getData()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildquery();
            $this->_data = $this->_getList($query);
        }

        return $this->_data;
    }

    /*
    ** function for processing the gedcom file
    */
    public function initialize()
    {
        $cids = Factory::getApplication()->input->get('cid', null, 'array');

        // make sure the input consists of integers
        for ($i = 0;$i < count($cids);$i++) {
            $cids[$i] = (int) $cids[$i];

            if ($cids[$i] == 0) {
                die('wrong request');
            }
        }

        // store first empty object
        $this->initObject($cids);
    }

    private function initObject($cids)
    {
        // store first empty object
        $newObject 				= new processObject();
        $newObject->id 			= array_shift($cids);
        $newObject->japp_ids 	= $cids;

        if (!$newObject->id) {
            $newObject->status = 'stop';
        }

        $this->setProcessObject($newObject);
    }


    private function setProcessObject($procObject)
    {
        // create a registry item
        if (isset($procObject->msg)) {
            $procObject->msg 		= substr($procObject->msg, 0, 1500);
        }
        $this->jt_registry->regkey 	= 'PROCESS_OBJECT';
        $this->jt_registry->value  	= json_encode($procObject);
        $this->jt_registry->storeUK();
    }

    private function getProcessObject()
    {
        static $procObject;

        // retrieve registry item
        $this->jt_registry->loadUK('PROCESS_OBJECT');
        $procObject = json_decode($this->jt_registry->value);
        unset($procObject->msg);
        return $procObject;
    }

    /*
    ** function for processing the gedcom file
    ** status: new			- New process. Nothing has happened yet.
    **         progress		- Reading through the GedCom file
    **         endload		- Finished loading GedCom file
    **         endpat		- Finished setting patronyms
    **         endrel		- Finished setting relation indicators
    **         start		- Start assigning family trees
    **         starttree	- Start assigning one tree
    **         progtree		- Processing family trees (setting up link between persons and trees)
    **         endtree		- Finished assigning family trees
    **         treedef_1 	- Finished setting up default trees 1 (1 tree per person)
    **         treedef_2 	- Finished setting up default trees 2 (1 tree per person)
    **         treedef_3 	- Finished setting up default trees 3 (1 father tree per person)
    **         treedef_4 	- Finished setting up default trees 4 (1 mother tree per person)
    **         treedef_5 	- Finished setting up default trees 5 (1 partner tree per person)
    **         treedef_6 	- Finished setting up default trees 6 (lowest tree)
    **         endtreedef 	- Finished setting up default trees 7 (lowest tree)
    **         end			- Finished full process
    **         error		- An error has occured
    */
    public function getGedcom()
    {
        $canDo	= JoaktreeHelper::getActions();
        $procObject = $this->getProcessObject();
        if (($canDo->get('core.create')) && ($canDo->get('core.edit'))) {

            switch ($procObject->status) {
                case 'new':
                    $procObject->start = date('h:i:s');
                    //$procObject->start = strftime('%H:%M:%S');
                    $procObject->msg = Text::sprintf('JTPROCESS_START_MSG', $procObject->id);
                    Log::addLogger(array('text_file' => 'joaktreeged.log.php'), Log::INFO, array('joaktreeged'));
                    Log::add('Start : '.$procObject->id, Log::INFO, "joaktreeged");
                    // no break
                case 'progress':	// continue
                case 'endload':		// continue
                case 'endpat':		// continue
                    $gedcomfile = new Gedcomfile2($procObject);
                    $resObject 	= $gedcomfile->process('all');

                    if ($resObject->status == 'endrel') {
                        $msg = Gedcomfile2::clear_gedcom();
                        if ($msg) {
                            $resObject->msg .= $msg.'<br />';
                        }
                    }

                    $procObject->current = date('h:i:s');
                    $this->setProcessObject($resObject);
                    $return = json_encode($resObject);
                    break;
                case 'endrel':
                    // Start loop throuth the assign FT
                    $procObject->status = 'start';
                    // Addition for processing tree-persons
                    // no break
                case 'start':		// continue
                case 'starttree':	// continue
                case 'progtree':	// continue
                case 'endtree':		// continue
                case 'treedef_1':	// continue
                case 'treedef_2':	// continue
                case 'treedef_3':	// continue
                case 'treedef_4':	// continue
                case 'treedef_5':	// continue
                case 'treedef_6':	// continue
                    $familyTree = new Trees($procObject);
                    $resObject 	= $familyTree->assignFamilyTree();

                    $procObject->current = date('h:i:s');
                    //$resObject->current = strftime('%H:%M:%S');
                    $this->setProcessObject($resObject);
                    $return = json_encode($resObject);
                    break;
                case 'endtreedef':
                    // we are done
                    $procObject->status  = 'index';
                    $procObject->current = date('h:i:s');
                    //$procObject->current = strftime('%H:%M:%S');
                    $this->setLastUpdateDateTime();
                    $this->setInitialChar();
                    $appId = $procObject->id;
                    $procObject->msg = Text::_('JTPROCESS_INDEX_MSG');
                    $this->setProcessObject($procObject);
                    $return = json_encode($procObject);
                    break;
                    // End: Addition for processing tree-persons
                case 'index':
                    // everything has been done : let's start finder plugin
                    PluginHelper::importPlugin('finder');
                    $obj = new \StdClass();
                    $obj->app_id = $procObject->id;
                    Factory::getApplication()->triggerEvent('onFinderAfterSave', array('com_joaktree.person', $obj ,true));
                    // we are done
                    $procObject->status  = 'end';
                    $procObject->current = date('h:i:s');
                    //$procObject->current = strftime('%H:%M:%S');
                    $procObject->end 	 = $procObject->current;
                    $this->setLastUpdateDateTime();
                    $this->setInitialChar();

                    $this->setProcessObject($procObject);
                    $return = json_encode($procObject);
                    break;
                case 'end':
                    // store first empty object
                    Log::addLogger(array('text_file' => 'joaktreeged.log.php'), Log::INFO, array('joaktreeged'));
                    Log::add('End : '.$procObject->id, Log::INFO, "joaktreeged");
                    $appId = $procObject->id;
                    $this->initObject($procObject->japp_ids);
                    $newObject = $this->getProcessObject();
                    $newObject->msg = Text::sprintf('JTPROCESS_END_MSG', $appId);

                    $return = json_encode($newObject);
                    break;
                case 'error':	// continue
                default:		// continue
                    break;
            }
        } else {

            $procObject->status = 'error';
            $procObject->msg    = Text::_('JT_NOTAUTHORISED');

            $return = json_encode($procObject);
        }

        return $return;
    }

    private function setLastUpdateDateTime()
    {
        $query = $this->_db->getQuery(true);
        $query->update(' #__joaktree_registry_items ');
        $query->set(' value  = NOW() ');
        $query->where(' regkey = '.$this->_db->quote('LAST_UPDATE_DATETIME').' ');

        $this->_db->setQuery($query);
        $this->_db->execute(); //$this->_db->query();
    }

    private function setInitialChar()
    {
        // update register with 0, meaning NO "initial character" present
        $query = $this->_db->getQuery(true);
        $query->update(' #__joaktree_registry_items ');
        $query->set(' value  = '.$this->_db->quote('0').' ');
        $query->where(' regkey = '.$this->_db->quote('INITIAL_CHAR').' ');

        $this->_db->setQuery($query);
        $this->_db->execute(); //$this->_db->query();
    }
}
