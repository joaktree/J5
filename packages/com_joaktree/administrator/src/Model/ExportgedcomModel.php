<?php
/**
 * Joomla! component Joaktree
 * file		jt_export_gedcom model - jt_export_gedcom.php
 *
 * @version	2.0.0
 * @author	Niels van Dantzig (2009-2014) - Robert Gastaud
 * @package	Joomla
 * @subpackage	Joaktree
 * @license	GNU/GPL
 *
 * Component for genealogy in Joomla!
 *
 */

namespace Joaktree\Component\Joaktree\Administrator\Model;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joaktree\Component\Joaktree\Administrator\Helper\Gedcomexport2;
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

class ExportgedcomModel extends BaseDatabaseModel
{
    public $_data;
    public $_pagination 	= null;
    public $_total         = null;
    public $jt_registry;

    public function __construct()
    {
        parent::__construct();

        // 		$this->jt_registry	= Table::getInstance('RegistryitemsTable','Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
        $this->jt_registry    = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Registryitems');

    }

    private function _buildquery()
    {
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

            $query = $this->_db->getQuery(true);
            $query->select(' japp.* ');
            $query->from(' #__joaktree_applications  japp ');
            $query->where(' japp.id IN ('.implode(",", $cids).') ');
            $query->order(' japp.id ');

            return $query;
        }
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
        $cids	= Factory::getApplication()->input->get('cid', null, 'array');

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
            $newObject->status = 'close';
        }

        $this->setProcessObject($newObject);
    }

    private function setProcessObject($procObject)
    {
        // create a registry item
        if (isset($procObject->msg)) {
            $procObject->msg 		= substr($procObject->msg, 0, 1500);
        }
        $this->jt_registry->regkey 	= 'EXPORT_OBJECT';
        $this->jt_registry->value  	= json_encode($procObject);
        $this->jt_registry->storeUK();
    }

    private function getProcessObject()
    {
        static $procObject;

        // retrieve registry item
        $this->jt_registry->loadUK('EXPORT_OBJECT');
        $procObject = json_decode($this->jt_registry->value);
        unset($procObject->msg);

        return $procObject;
    }

    /*
    ** function for processing the gedcom file
    */
    public function getGedcom()
    {
        $canDo	= JoaktreeHelper::getActions();
        $procObject = $this->getProcessObject();

        if (($canDo->get('core.create')) && ($canDo->get('core.edit'))) {

            if ($procObject->status == 'new') {
                $procObject->start = date('h:i:s');
                //$procObject->start = strftime('%H:%M:%S');
                $procObject->msg = Text::sprintf('JTPROCESS_START_MSG', $procObject->id);

            }

            if ($procObject->status == 'end') {
                // store first empty object
                $appId = $procObject->id;
                $this->initObject($procObject->japp_ids);
                $newObject = $this->getProcessObject();
                $newObject->msg = Text::sprintf('JTPROCESS_END_MSG', $appId);
                return json_encode($newObject);
            }

            $gedcomfile = new Gedcomexport2($procObject);
            $resObject 	= $gedcomfile->process();
            $resObject->current = date('h:i:s');
            //$resObject->current = strftime('%H:%M:%S');
            $this->setProcessObject($resObject);

            $return = json_encode($resObject);

        } else {

            $procObject->status = 'error';
            $procObject->msg    = Text::_('JT_NOTAUTHORISED');

            $return = json_encode($procObject);
        }

        return $return;
    }
}
