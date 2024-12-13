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
namespace Joaktree\Component\Joaktree\Administrator\Table;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;
use Joaktree\Component\Joaktree\Administrator\Helper\Relations;

class PersonsTable extends Table implements VersionableTableInterface
{
    public $app_id				= null; // PK
    public $id					= null; // PK
    public $indexNam           = null;
    public $firstName			= null;
    public $patronym			= null;
    public $namePreposition	= null;
    public $familyName			= null;
    public $prefix				= null;
    public $suffix				= null;
    public $sex				= null;
    public $indNote			= 0;
    public $indCitation		= null;
    public $indHasParent		= null;
    public $indHasPartner		= null;
    public $indHasChild		= null;
    public $indIsWitness		= null;
    public $lastUpdateTimeStamp = null;

    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_joaktree.persons';
        $pk = array('app_id', 'id');
        parent::__construct('#__joaktree_persons', $pk, $db);
    }

    public function check($cit = false)
    {
        // mandatory fields
        if (empty($this->app_id)) {
            return false;
        }
        if (empty($this->id)) {
            return false;
        }

        // set the indications
        if (!$this->checkNotesAndReferences()) {
            return false;
        }

        return true;
    }

    private function checkNotesAndReferences()
    {
        // check for citations
        $query = $this->_db->getQuery(true);
        $query->select(' COUNT(jcn.objectOrderNumber) AS indCit ');
        $query->from(' #__joaktree_citations jcn ');
        $query->where(' jcn.app_id      = :appid');
        $query->where(
            ' (  jcn.person_id_1 = :personid'
                     . ' OR jcn.person_id_2 = :personid'
                     . ' ) '
        );
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);

        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();
        $this->indCitation = ($result) ? 1 : 0; // pascal

        // check for notes
        $query->clear();
        $query->select(' COUNT(jpe.orderNumber) AS indNot ');
        $query->from(' #__joaktree_person_notes jpe ');
        $query->where(' jpe.app_id     = :appid');
        $query->where(' jpe.person_id  = :personid');
        $query->where(' jpe.nameOrderNumber   IS NULL ');
        $query->where(' jpe.eventOrderNumber  IS NULL ');
        $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
        $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);

        $this->_db->setQuery($query);
        $result = $this->_db->loadResult();
        $this->indNote = ($result) ? 1 : 0; // pascal

        return true;
    }

    public function insert()
    {
        if (!empty($this->familyName)) {
            $this->indexNam = mb_strtoupper(mb_substr($this->familyName, 0, 1));
        }
        $this->lastUpdateTimeStamp = Factory::getDate()->toSql();
        $ret = $this->_db->insertObject($this->_tbl, $this, $this->_tbl_keys);
        return $ret;
    }

    public function update()
    {
        if (!empty($this->familyName)) {
            $this->indexNam = mb_strtoupper(mb_substr($this->familyName, 0, 1));
        }
        $this->lastUpdateTimeStamp = Factory::getDate()->toSql();
        $ret = $this->_db->updateObject($this->_tbl, $this, $this->_tbl_keys, true);
        return $ret;
    }

    public function store($updateNulls = false)
    {
        if (!empty($this->familyName)) {
            $this->indexNam = mb_strtoupper(mb_substr($this->familyName, 0, 1));
        }
        $this->lastUpdateTimeStamp = Factory::getDate()->toSql();
        return parent::store($updateNulls);
    }

    public function delete($pk = null)
    {
        // cascading delete
        $query = $this->_db->getQuery(true);
        $ret = true;

        // joaktree_person_events
        if ($ret) {
            $table = 'joaktree_person_events';
            //$query->clear();
            $query->delete(' #__joaktree_person_events ');
            $query->where(' app_id    = :appid');
            $query->where(' person_id = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_person_names
        if ($ret) {
            $table = 'joaktree_person_names';
            $query->clear();
            $query->delete(' #__joaktree_person_names ');
            $query->where(' app_id    = :appid');
            $query->where(' person_id = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_person_notes
        if ($ret) {
            $table = 'joaktree_person_notes';
            $query->clear();
            $query->delete(' #__joaktree_person_notes ');
            $query->where(' app_id    = :appid');
            $query->where(' person_id = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_person_documents + joaktree_documents
        if ($ret) {
            $table = 'joaktree_person_document';
            $query->clear();
            $query->delete(' #__joaktree_person_documents ');
            $query->where(' app_id    = :appid');
            $query->where(' person_id = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        if ($ret) {
            $table = 'joaktree_documents';
            $query->clear();
            $query->delete(' #__joaktree_documents ');
            $query->where(' app_id    = :appid');
            $query->where(
                ' NOT EXISTS ( '
                         . '  SELECT 1 '
                         . '  FROM   #__joaktree_person_documents  jpd '
                         . '  WHERE  jpd.app_id      = app_id '
                         . '  AND    jpd.document_id = id '
                         . '  ) '
            );
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_relations
        if ($ret) {
            $table = 'joaktree_relations';
            $query->clear();
            // First select which relations exists (one direction)
            $query->select(' person_id_1 ');
            $query->from(' #__joaktree_relations ');
            $query->where(' app_id = :appid');
            $query->where(' person_id_2 = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $relations = $this->_db->loadColumn();
            $query->clear();

            // Second select which relations exists (second direction)
            $query->select(' person_id_2 ');
            $query->from(' #__joaktree_relations ');
            $query->where(' app_id = :appid');
            $query->where(' person_id_1 = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $tmp = $this->_db->loadColumn();
            $relations = array_merge($relations, $tmp);
            $query->clear();

            // now we start deleting ...
            $query->delete(' #__joaktree_relations ');
            $query->where(' app_id = :appid');
            $query->where(
                ' (  person_id_1 = :personid'
                         . ' OR person_id_2 = :personid'
                         . ' ) '
            );
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();

            // Finally, we reset the relation indicators for the remaining relations
            if ($ret) {
                $table = 'joaktree_persons (relationIndicators)';
                $ret = Relations::setRelationIndicators($this->app_id, $relations);
            }
        }

        // joaktree_relation_events
        if ($ret) {
            $table = 'joaktree_relation_events';
            $query->clear();
            $query->delete(' #__joaktree_relation_events ');
            $query->where(' app_id = :appid');
            $query->where(
                ' (  person_id_1 = :personid'
                         . ' OR person_id_2 = :personid'
                         . ' ) '
            );
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);

            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_relation_notes
        if ($ret) {
            $table = 'joaktree_relation_notes';
            $query->clear();
            $query->delete(' #__joaktree_relation_notes ');
            $query->where(' app_id = :appid');
            $query->where(
                ' (  person_id_1 = :personid'
                         . ' OR person_id_2 = :personid'
                         . ' ) '
            );
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_citations
        if ($ret) {
            $table = 'joaktree_citations';
            $query->clear();
            $query->delete(' #__joaktree_citations ');
            $query->where(' app_id = :appid');
            $query->where(
                ' (  person_id_1 = :personid'
                         . ' OR person_id_2 = :personid'
                         . ' ) '
            );
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_notes
        if ($ret) {
            $table = 'joaktree_notes';
            $query->clear();
            $query->delete(' #__joaktree_notes ');
            $query->where(' app_id    = :appid');
            $query->where(
                ' NOT EXISTS ( '
                         . '  SELECT 1 '
                         . '  FROM   #__joaktree_person_notes  jpn '
                         . '  WHERE  jpn.app_id  = app_id '
                         . '  AND    jpn.note_id = id '
                         . '  ) '
            );
            $query->where(
                ' NOT EXISTS ( '
                         . '  SELECT 1 '
                         . '  FROM   #__joaktree_relation_notes  jrn '
                         . '  WHERE  jrn.app_id  = app_id '
                         . '  AND    jrn.note_id = id '
                         . '  ) '
            );
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_tree_persons
        if ($ret) {
            $table = 'joaktree_tree_persons';
            $query->clear();
            $query->delete(' #__joaktree_tree_persons ');
            $query->where(' app_id    = :appid');
            $query->where(' person_id = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_persons
        if ($ret) {
            $table = 'joaktree_persons';
            $query->clear();
            $query->delete(' #__joaktree_persons ');
            $query->where(' app_id = :appid');
            $query->where(' id     = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
            
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        // joaktree_admin_persons
        if ($ret) {
            $table = 'joaktree_admin_persons';
            $query->clear();
            $query->delete(' #__joaktree_admin_persons ');
            $query->where(' app_id = :appid');
            $query->where(' id     = :personid');
            $query->bind(':appid', $this->app_id, \Joomla\Database\ParameterType::INTEGER);
            $query->bind(':personid', $this->id, \Joomla\Database\ParameterType::STRING);
           
            $this->_db->setQuery($query);
            $ret = $this->_db->execute(); //$this->_db->query();
        }

        if (!$ret) {
            $this->setError('Cascading table '.$table.': Error -> '.$this->_db->getErrorMsg());
        }

        return $ret;
    }
    /**
     * Get the type alias for the table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
