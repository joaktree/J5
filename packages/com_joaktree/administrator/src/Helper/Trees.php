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

namespace Joaktree\Component\Joaktree\Administrator\Helper;

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel ;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Stream;

class personObject
{
    public $i = null;	// id
    public $s = null;	// sex
    public $t = null;	// type
    public $l = null;	// lineage
}

class procPersonObject
{
    public $id			= null;
    public $tree_ids	= null;
    public $persons	= null;
}


class Trees extends BaseDatabaseModel
{
    public function __construct($procObject)
    {
        $this->application = Factory::getApplication();
        $this->_db		   = Factory::getContainer()->get(DatabaseInterface::class);

        $this->procObject = $procObject;

        $params				= JoaktreeHelper::getJTParams($this->procObject->id);
        $path  				= JPATH_ROOT.'/'.$params->get('gedcomfile_path');
        $this->file			= $path.'/personObject.txt';
    }

    private function initPersObject($id, $tree_ids)
    {
        // store first empty object
        $newObject 				= new procPersonObject();
        $newObject->id 			= $id;
        $newObject->tree_ids 	= $tree_ids;
        $this->setProcPersonObject($newObject);
    }

    private function setProcPersonObject($procPersObject)
    {
        // create a registry item
        $stream = new Stream();
        $stream->open($this->file, 'w');
        $string = json_encode($procPersObject);
        $remaining = strlen($string);
        $fh = $stream->getFileHandle();

        // work around because the stream::write function has a bug
        do {// If the amount remaining is greater than the chunk size, then use the chunk
            $amount = ($remaining > 8192) ? 8192 : $remaining;
            $res = fwrite($fh, $string, $amount);

            if (!$res) {
                // Returned error
                $remaining = 0;
            } else {
                // Wrote something
                $string		= substr($string, $res);
                $remaining  = strlen($string);
            }
        } while ($remaining);

        $stream->close();
    }

    private function getProcPersonObject()
    {
        static $procPersObject;

        // Open the file as a stream.
        $stream = new Stream();
        $stream->open($this->file);
        $value 	= $stream->read();
        $stream->close();
        if (!$value) {
            stop1();
        }
        $procPersObject = json_decode($value);

        return $procPersObject;
    }

    private function deleteProcPersonObject()
    {
        // delete a registry item
        $stream = new Stream();
        $stream->delete($this->file);
    }

    // ========================================================
    /*
    ** function for assigning family trees and setting default trees
    ** status: start		- Start assigning family trees
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
    **         error		- An error has occured
    */
    public function assignFamilyTree()
    {
        // This is a loop using java calls
        $app_id		= (int) $this->procObject->id;

        switch ($this->procObject->status) {
            case 'start':
                $query = $this->_db->getQuery(true);

                // select all published trees
                $query->clear();
                $query->select(' id ');
                $query->from(' #__joaktree_trees ');
                $query->where(' app_id    = :appid');

                // if tree_ids are given, we only take a subset
                if (isset($this->procObject->treeIds) && (count($this->procObject->treeIds) > 0)) {
                    $query->where(' id    IN ( '.implode(',', $this->procObject->treeIds).' ) ');
                }
                $query->where(' published = true ');
                $query->order(' holds       DESC ');
                $query->order(' id          ASC ');
                $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);

                $this->_db->setQuery($query);
                $tree_ids = $this->_db->loadColumn();

                if (count($tree_ids) == 0) {
                    // insert a new tree
                    // tree name
                    $query->clear();
                    $query->select(' title ');
                    $query->from(' #__joaktree_applications ');
                    $query->where(' id	       = :appid');
                    $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);

                    $this->_db->setQuery($query);
                    $tree_name = $this->_db->loadResult();

                    // theme
                    $query->clear();
                    $query->select(' id ');
                    $query->from(' #__joaktree_themes ');
                    $query->where(' home = 1 ');

                    $this->_db->setQuery($query);
                    $tree_theme = $this->_db->loadResult();

                    // prepare insert
                    //$treeTable =  Table::getInstance('TreesTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
                    $treeTable    = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Trees');
                    $treeTable->set('app_id', $app_id);
                    $treeTable->set('name', $tree_name);
                    $treeTable->set('theme_id', $tree_theme);
                    // insert the new record
                    $ret = $treeTable->store();

                    // select all published trees
                    $query->clear();
                    $query->select(' id ');
                    $query->from(' #__joaktree_trees ');
                    $query->where(' app_id    = :appid');
                    $query->where(' published = true ');
                    $query->order(' holds       DESC ');
                    $query->order(' id          ASC ');
                    $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);

                    $this->_db->setQuery($query);
                    $tree_ids = $this->_db->loadColumn();
                }

                // store first empty object
                $this->initPersObject($app_id, $tree_ids);

                // no break
            case 'starttree':	// continue
            case 'progtree':	// continue

            case 'endtree':
            case 'treedef_1':
            case 'treedef_2':
            case 'treedef_3':
            case 'treedef_4':
            case 'treedef_5':
            case 'treedef_6':
                $this->procObject->status = $this->assignft();
                break;
            case 'error':		// continue
            default:			// continue
                $this->procObject->status = 'error';
                break;
        }
        return $this->procObject;
    }

    private function assignft()
    {
        $procPersObject 			= $this->getProcPersonObject();
        $resPersObject				= new procPersonObject();
        $resPersObject->id 			= $procPersObject->id;
        $resPersObject->tree_ids 	= $procPersObject->tree_ids;

        $app_id			= $procPersObject->id;
        $params			= JoaktreeHelper::getJTParams($app_id);
        $query			= $this->_db->getQuery(true);

        switch ($this->procObject->status) {
            case 'start':			// continue
            case 'starttree':		// continue
            case 'progtree':
                // only for startree and progtree are we executing the first part
                $finished = false;

                // Initialize table
                //$tree_persons   =  Table::getInstance('TreepersonsTable', 'Joaktree\\Component\\Joaktree\\Administrator\\Table\\');
                $tree_persons    = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Treepersons');

                if ($this->procObject->status == 'start') {
                    // initial steps
                    $query->clear();
                    $query->delete(' #__joaktree_tree_persons ');
                    $query->where(' app_id = :appid');
                    $query->where(' tree_id IN ( '.implode(',', $procPersObject->tree_ids).' ) ');
                    $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);

                    $this->_db->setQuery($query);
                    $msg = $this->_db->execute(); //$this->_db->query();

                    // empty the default tree attribute for all persons in the system
                    $query->clear();
                    $query->update(' #__joaktree_admin_persons ');
                    $query->set(' default_tree_id = null ');
                    $query->where(' app_id = :appid');
                    $query->where(' default_tree_id IN ( '.implode(',', $procPersObject->tree_ids).' ) ');
                    $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);

                    $this->_db->setQuery($query);
                    $msg = $this->_db->execute(); //$this->_db->query();
                    $this->procObject->status = 'starttree';
                }

                // select parameters of the tree
                $tree_id		= array_shift($procPersObject->tree_ids);

                $query->clear();
                $query->select(' * ');
                $query->from(' #__joaktree_trees ');
                $query->where(' app_id    = :appid');
                $query->where(' id        = :treeid');
                $query->bind(':appid', $app_id, \Joomla\Database\ParameterType::INTEGER);
                $query->bind(':treeid', $tree_id, \Joomla\Database\ParameterType::INTEGER);
                
                $this->_db->setQuery($query);
                $tree = $this->_db->loadObject();

                $this->procObject->msg = ($this->procObject->status == 'starttree')
                                        ? Text::sprintf('JTTREE_ASSIGN', $tree->name)
                                        : null;
                if ($this->procObject->msg) {
                    JoaktreeHelper::addLog($this->procObject->msg);
                }
                if (($tree->holds == 'descendants')
                   and (isset($tree->root_person_id) && $tree->root_person_id != null)
                ) {
                    // Initialize arrays for processing
                    $now = array();
                    $next = array();

                    if ($this->procObject->status == 'starttree') {
                        // First person we start with is the root-person of the tree
                        // For logic this person is always male (even if she is not)
                        $personObject 		= new personObject();
                        $personObject->i  	= $tree->root_person_id;
                        $personObject->s 	= 'M';

                        // Therefore the type of this first person (root-person) is R
                        $personObject->t	= 'R';

                        // lineage is filled with the first person.
                        $personObject->l	= $tree->root_person_id;
                        $now[] = $personObject;
                        unset($personObject);
                    } elseif ($this->procObject->status == 'progtree') {
                        $now = $procPersObject->persons;
                    }

                    // there is an array of persons to process NOW.
                    // loop through every record in this array (i.e. person_now)
                    foreach ($now as $person) {

                        // check that the person-tree combination (primary key) does not exists yet
                        if (($tree_persons->load($person->i . '+' . $tree_id) == false)) {
                            // the person-tree combination does not exists
                            // therefore the attributes for a new record are filled
                            // primary key is combination of person-id and tree-id separated by +
                            $tree_persons->set('id', $person->i . '+' . $tree_id);
                            $tree_persons->set('app_id', $app_id);
                            $tree_persons->set('tree_id', $tree_id);
                            $tree_persons->set('person_id', $person->i);
                            $tree_persons->set('type', $person->t);
                            $tree_persons->set('lineage', $person->l);

                            // insert the new record
                            $ret = $tree_persons->store();

                            // after the new record is inserted, look for all relations of the newly inserted person
                            // the relations are husband or wife (type = P for partner of)
                            // and relations are father or mother (type = C for child)
                            $lineage = array($this->_db->quote($person->l), 'jrn.person_id_1');
                            $quer1 = '( '
                                    .'SELECT     jrn.person_id_1 AS i '
                                    .',          '.$this->_db->quote('P').' AS t '
                                    .',          NULL AS l '
                                    .',          jpn.sex AS s '
                                    .'FROM       #__joaktree_relations jrn '
                                    .'INNER JOIN #__joaktree_persons   jpn '
                                    .'ON (   jpn.app_id = jrn.app_id '
                                    .'   AND jpn.id     = jrn.person_id_1 '
                                    .'   ) '
                                    .'WHERE jrn.app_id      = '.$app_id.' '
                                    .'AND   jrn.person_id_2 = '.$this->_db->quote($person->i).' '
                                    .'AND   jrn.type        = '.$this->_db->quote('partner').' '
                                    .') UNION ( '
                                    .'SELECT     jrn.person_id_2 AS i '
                                    .',          '.$this->_db->quote('P').' AS t '
                                    .',          NULL AS l '
                                    .',          jpn.sex AS s '
                                    .'FROM       #__joaktree_relations jrn '
                                    .'INNER JOIN #__joaktree_persons   jpn '
                                    .'ON (   jpn.app_id = jrn.app_id '
                                    .'   AND jpn.id     = jrn.person_id_2 '
                                    .'   ) '
                                    .'WHERE jrn.app_id      = '.$app_id.' '
                                    .'AND   jrn.person_id_1 = '.$this->_db->quote($person->i).' '
                                    .'AND   jrn.type        = '.$this->_db->quote('partner').' '
                                    .') UNION ( '
                                    .'SELECT     jrn.person_id_1 AS i '
                                    .',          '.$this->_db->quote('C').' AS t '
                                    .',          '.$query->concatenate($lineage, ' ').' AS l '
                                    .',          jpn.sex AS s '
                                    .'FROM       #__joaktree_relations jrn '
                                    .'INNER JOIN #__joaktree_persons   jpn '
                                    .'ON (   jpn.app_id = jrn.app_id '
                                    .'   AND jpn.id     = jrn.person_id_1 '
                                    .'   ) '
                                    .'WHERE jrn.app_id      = '.$app_id.' '
                                    .'AND   jrn.person_id_2 = '.$this->_db->quote($person->i).' '
                                    .'AND   jrn.type IN ( '.$this->_db->quote('father').', '.$this->_db->quote('mother').') '
                                    .') ';

                            $this->_db->setQuery($quer1);
                            $tmp1 = $this->_db->loadObjectList();

                            $next = array_merge($next, $tmp1);
                            unset($tmp1);
                        } // end of if statement - adding new record for person-tree combination
                    } // end of for-loop for the array being processed NOW

                    // the NOW array is processed; a NEXT array is filled -> save it for later
                    $resPersObject->persons = $next;

                    // this is a loop: it can stop (a) when the next is empty
                    // (that means: there is nothing to be processed anymore)
                    if (count($next) == 0) {
                        $finished = true;
                    }

                } // end of check whether only descendants are in the tree

                if (($tree->holds == 'all')
                   || (
                       ($tree->holds == 'descendants')
                  && (!isset($tree->root_person_id) || $tree->root_person_id == null)
                       // This is an incomplete family tree.
                       // It is a 'desendant' tree without an original ancestor.
                   )
                ) {
                    $quer1 = 'UPDATE #__joaktree_tree_persons      jtp'
                            .',      ( SELECT jpn.id               AS id '
                            .'         ,      jpn.app_id           AS app_id '
                            .'         FROM   #__joaktree_persons  jpn '
                            .'         WHERE  jpn.app_id = '.$app_id.' '
                            .'       )  jpn_iv '
                            .'SET    jtp.app_id     = jpn_iv.app_id '
                            .',      jtp.tree_id    = '.$tree_id.' '
                            .',      jtp.person_id  = jpn_iv.id '
                            .',      jtp.type       = '.$this->_db->Quote('R').' '
                            .',      jtp.lineage    = null '
                            .'WHERE  jtp.id         = CONCAT_WS('.$this->_db->Quote('+').', jpn_iv.id, '.$tree_id.') ';

                    $msg   = $this->_db->setQuery($quer1);
                    $msg   = $this->_db->execute(); //$this->_db->query( );

                    $quer1 = 'INSERT IGNORE '
                            .'INTO   #__joaktree_tree_persons '
                            .'( id '
                            .', app_id '
                            .', tree_id '
                            .', person_id '
                            .', type '
                            .', lineage '
                            .') '
                            .'SELECT CONCAT_WS('.$this->_db->Quote('+').', jpn.id, '.$tree_id.') '
                            .',      jpn.app_id '
                            .',      '.$tree_id.' '
                            .',      jpn.id '
                            .',      '.$this->_db->Quote('R').' '
                            .',      null '
                            .'FROM   #__joaktree_persons  jpn '
                            .'WHERE  jpn.app_id = '.$app_id.' ';

                    $msg   = $this->_db->setQuery($quer1);
                    $msg   = $this->_db->execute(); //$this->_db->query( );

                    //				// everyone gets the same default tree
                    //				$query->clear();
                    //				$query->update(' #__joaktree_admin_persons ');
                    //				$query->set(   ' default_tree_id = '.$tree_id.' ');
                    //				$query->where( ' app_id          = '.$app_id.' ');
                    //				$query->where( ' default_tree_id IS NULL ');
                    //
                    //				$msg   = $this->_db->setQuery( $query );
                    //				$msg   = $this->_db->query( );

                    // the whole tree is done by one set of statements
                    $finished = true;
                    $resPersObject->persons = null;
                } // end of check whether all persons are in the tree

                if ($finished) {
                    // finished with this tree - up to the next tree
                    $resPersObject->tree_ids = $procPersObject->tree_ids;
                    if (count($resPersObject->tree_ids) > 0) {
                        $returnStatus = 'starttree';
                    } else {
                        // we are realy done
                        $returnStatus = 'endtree';
                    }
                } else {
                    $returnStatus = 'progtree';
                }

                // save the PersonObject
                $this->setProcPersonObject($resPersObject);
                break;

            case 'endtree':
                // PERSONS WITH 0 or 1 TREE
                // after filling the tree_person table, the default tree for every person is determined
                // for which it is not yet filled

                // different default trees for different persons
                // select all persons in the system without default tree

                // if 0 trees are found, there is no tree (and thus no default tree) for this person
                // if 1 tree is found, this tree is the default tree for this person
                $quer1 = 'UPDATE     #__joaktree_admin_persons  jan '
                        .',          ( SELECT     jtp1.person_id '
                         .'             ,          jtp1.tree_id '
                         .'             FROM       #__joaktree_tree_persons  jtp1 '
                         .'             INNER JOIN ( SELECT   jtp2.person_id '
                        .'                          ,        MIN(jtp2.tree_id) AS tree_id '
                        .'                          FROM     #__joaktree_tree_persons  jtp2 '
                        .'                          WHERE    jtp2.app_id = '.$app_id.' '
                        .'                          GROUP BY jtp2.person_id '
                        .'                          HAVING   COUNT(jtp2.tree_id) = 1 '
                        .'                        ) iv_jtp2 '
                        .'             ON         (   iv_jtp2.person_id = jtp1.person_id '
                        .'                        AND iv_jtp2.tree_id   = jtp1.tree_id '
                        .'                        ) '
                        .'             WHERE      jtp1.app_id = '.$app_id.' '
                        .'           ) iv_jtp1 '
                        .'SET        jan.default_tree_id = iv_jtp1.tree_id '
                        .'WHERE      jan.app_id          = '.$app_id.' '
                        .'AND        jan.id              = iv_jtp1.person_id '
                        .'AND        jan.default_tree_id IS NULL ';

                $msg   = $this->_db->setQuery($quer1);
                $msg   = $this->_db->execute(); //$this->_db->query( );

                $this->procObject->msg = Text::_('JTTREE_DEFAULT');
                $returnStatus = 'treedef_1';
                JoaktreeHelper::addLog($this->procObject->msg);
                break;
            case 'treedef_1':
                // PERSONS WITH EXACTLY 1 TREE
                // select all persons in the system without default tree
                // and update the default tree
                $quer1 = 'UPDATE     #__joaktree_admin_persons  jan '
                        .',          ( SELECT     jtp1.person_id '
                         .'             ,          jtp1.tree_id '
                         .'             FROM       #__joaktree_tree_persons  jtp1 '
                         .'             INNER JOIN ( SELECT     jtp2.person_id '
                        .'                          ,          MIN(jtp2.tree_id) AS tree_id '
                        .'                          FROM       #__joaktree_tree_persons  jtp2 '
                        .'                          INNER JOIN #__joaktree_trees  jte '
                        .'                          ON    (   jte.app_id   = jtp2.app_id '
                        .'                                AND jte.id       = jtp2.tree_id  '
                        .'                                ) '
                        .'                          WHERE      jtp2.app_id = '.$app_id.' '
                        .'                          GROUP BY   jtp2.person_id '
                        .'                          HAVING     COUNT(jtp2.tree_id) = 1 '
                        .'                        ) iv_jtp2 '
                        .'             ON         (   iv_jtp2.person_id = jtp1.person_id '
                        .'                        AND iv_jtp2.tree_id   = jtp1.tree_id '
                        .'                        ) '
                        .'             WHERE      jtp1.app_id = '.$app_id.' '
                        .'           ) iv_jtp1 '
                        .'SET        jan.default_tree_id = iv_jtp1.tree_id '
                        .'WHERE      jan.app_id          = '.$app_id.' '
                        .'AND        jan.id              = iv_jtp1.person_id '
                        .'AND        jan.default_tree_id IS NULL ';

                $msg   = $this->_db->setQuery($quer1);
                $msg   = $this->_db->execute(); //$this->_db->query( );

                $returnStatus = 'treedef_2';
                break;
            case 'treedef_2':
                // PERSONS WITH MORE THAN 1 TREE
                // select all persons in the system without default tree
                // update with default tree of first father
                $quer1 = 'UPDATE     #__joaktree_admin_persons  jan '
                        .',          ( SELECT     jrn.person_id_1      AS person_id '
                         .'             ,          MIN(jan2.default_tree_id) AS tree_id '
                         .'             FROM       #__joaktree_relations     jrn '
                         .'             INNER JOIN #__joaktree_admin_persons jan2 '
                         .'             ON (   jan2.app_id = jrn.app_id '
                        .'                AND jan2.id     = jrn.person_id_2 '
                        .'                ) '
                        .'             WHERE      jrn.app_id        = '.$app_id.' '
                        .'             AND        jrn.type          = '.$this->_db->quote('father').' '
                        .'             AND        IFNULL(jrn.orderNumber_1, 0) = '
                        .'              ( SELECT IFNULL(MIN(jrn2.orderNumber_1), 0) '
                        .'                FROM   #__joaktree_relations     jrn2 '
                        .'                WHERE  jrn2.app_id      = jrn.app_id '
                        .'                AND    jrn2.person_id_1 = jrn.person_id_1 '
                        .'                AND    jrn2.type        = jrn.type '
                        .'              ) '
                        .'             GROUP BY  jrn.person_id_1 '
                        .'           ) iv_jrn '
                        .'SET        jan.default_tree_id = iv_jrn.tree_id '
                        .'WHERE      jan.app_id          = '.$app_id.' '
                        .'AND        jan.id              = iv_jrn.person_id '
                        .'AND        jan.default_tree_id IS NULL ';

                $msg   = $this->_db->setQuery($quer1);
                $msg   = $this->_db->execute(); //$this->_db->query( );

                $returnStatus = 'treedef_3';
                break;
            case 'treedef_3':
                // select all persons in the system without default tree
                // update with default tree of first mother
                $quer1 = 'UPDATE     #__joaktree_admin_persons  jan '
                        .',          ( SELECT     jrn.person_id_1      AS person_id '
                         .'             ,          MIN(jan2.default_tree_id) AS tree_id '
                         .'             FROM       #__joaktree_relations     jrn '
                         .'             INNER JOIN #__joaktree_admin_persons jan2 '
                         .'             ON (   jan2.app_id = jrn.app_id '
                        .'                AND jan2.id     = jrn.person_id_2 '
                        .'                ) '
                        .'             WHERE      jrn.app_id        = '.$app_id.' '
                        .'             AND        jrn.type          = '.$this->_db->quote('mother').' '
                        .'             AND        IFNULL(jrn.orderNumber_1, 0) = '
                        .'              ( SELECT IFNULL(MIN(jrn2.orderNumber_1), 0) '
                        .'                FROM   #__joaktree_relations     jrn2 '
                        .'                WHERE  jrn2.app_id      = jrn.app_id '
                        .'                AND    jrn2.person_id_1 = jrn.person_id_1 '
                        .'                AND    jrn2.type        = jrn.type '
                        .'              ) '
                        .'             GROUP BY  jrn.person_id_1 '
                        .'           ) iv_jrn '
                        .'SET        jan.default_tree_id = iv_jrn.tree_id '
                        .'WHERE      jan.app_id          = '.$app_id.' '
                        .'AND        jan.id              = iv_jrn.person_id '
                        .'AND        jan.default_tree_id IS NULL ';

                $msg   = $this->_db->setQuery($quer1);
                $msg   = $this->_db->execute(); //$this->_db->query( );

                $returnStatus = 'treedef_4';
                break;
            case 'treedef_4':
                // select all persons in the system without default tree
                // update with default tree of first partner (a)
                $quer1 = 'UPDATE     #__joaktree_admin_persons  jan '
                        .',          ( SELECT     jrn.person_id_1      AS person_id '
                         .'             ,          MIN(jan2.default_tree_id) AS tree_id '
                         .'             FROM       #__joaktree_relations     jrn '
                         .'             INNER JOIN #__joaktree_admin_persons jan2 '
                         .'             ON (   jan2.app_id = jrn.app_id '
                        .'                AND jan2.id     = jrn.person_id_2 '
                        .'                ) '
                        .'             WHERE      jrn.app_id        = '.$app_id.' '
                        .'             AND        jrn.type          = '.$this->_db->quote('partner').' '
                        .'             AND        IFNULL(jrn.orderNumber_1, 0) = '
                        .'              ( SELECT IFNULL(MIN(jrn2.orderNumber_1), 0) '
                        .'                FROM   #__joaktree_relations     jrn2 '
                        .'                WHERE  jrn2.app_id      = jrn.app_id '
                        .'                AND    jrn2.person_id_1 = jrn.person_id_1 '
                        .'                AND    jrn2.type        = jrn.type '
                        .'              ) '
                        .'             GROUP BY  jrn.person_id_1 '
                        .'           ) iv_jrn '
                        .'SET        jan.default_tree_id = iv_jrn.tree_id '
                        .'WHERE      jan.app_id          = '.$app_id.' '
                        .'AND        jan.id              = iv_jrn.person_id '
                        .'AND        jan.default_tree_id IS NULL ';

                $msg   = $this->_db->setQuery($quer1);
                $msg   = $this->_db->execute(); //$this->_db->query( );

                // update with default tree of first partner (b)
                $quer1 = 'UPDATE     #__joaktree_admin_persons  jan '
                        .',          ( SELECT     jrn.person_id_2      AS person_id '
                         .'             ,          MIN(jan2.default_tree_id) AS tree_id '
                         .'             FROM       #__joaktree_relations     jrn '
                         .'             INNER JOIN #__joaktree_admin_persons jan2 '
                         .'             ON (   jan2.app_id = jrn.app_id '
                        .'                AND jan2.id     = jrn.person_id_1 '
                        .'                ) '
                        .'             WHERE      jrn.app_id        = '.$app_id.' '
                        .'             AND        jrn.type          = '.$this->_db->quote('partner').' '
                        .'             AND        IFNULL(jrn.orderNumber_2, 0) = '
                        .'              ( SELECT IFNULL(MIN(jrn2.orderNumber_2), 0) '
                        .'                FROM   #__joaktree_relations     jrn2 '
                        .'                WHERE  jrn2.app_id      = jrn.app_id '
                        .'                AND    jrn2.person_id_1 = jrn.person_id_2 '
                        .'                AND    jrn2.type        = jrn.type '
                        .'              ) '
                        .'             GROUP BY  jrn.person_id_2 '
                        .'           ) iv_jrn '
                        .'SET        jan.default_tree_id = iv_jrn.tree_id '
                        .'WHERE      jan.app_id          = '.$app_id.' '
                        .'AND        jan.id              = iv_jrn.person_id '
                        .'AND        jan.default_tree_id IS NULL ';

                $msg   = $this->_db->setQuery($quer1);
                $msg   = $this->_db->execute(); //$this->_db->query( );

                $returnStatus = 'treedef_5';
                break;
            case 'treedef_5':
                // PERSONS WITH MORE THAN 1 TREE
                // select all persons in the system without default tree
                // update with first tree (lowest number)

                $quer1 = 'UPDATE     #__joaktree_admin_persons  jan '
                        .',          ( SELECT     jtp1.person_id '
                         .'             ,          jtp1.tree_id '
                         .'             FROM       #__joaktree_tree_persons  jtp1 '
                         .'             INNER JOIN ( SELECT     jtp2.person_id '
                        .'                          ,          MIN(jtp2.tree_id) AS tree_id '
                        .'                          FROM       #__joaktree_tree_persons  jtp2 '
                        .'                          INNER JOIN #__joaktree_trees  jte '
                        .'                          ON    (   jte.app_id   = jtp2.app_id '
                        .'                                AND jte.id       = jtp2.tree_id  '
                        .'                                ) '
                        .'                          WHERE      jtp2.app_id = '.$app_id.' '
                        .'                          GROUP BY   jtp2.person_id '
                        .'                        ) iv_jtp2 '
                        .'             ON         (   iv_jtp2.person_id = jtp1.person_id '
                        .'                        AND iv_jtp2.tree_id   = jtp1.tree_id '
                        .'                        ) '
                        .'             WHERE      jtp1.app_id = '.$app_id.' '
                        .'           ) iv_jtp1 '
                        .'SET        jan.default_tree_id = iv_jtp1.tree_id '
                        .'WHERE      jan.app_id          = '.$app_id.' '
                        .'AND        jan.id              = iv_jtp1.person_id '
                        .'AND        jan.default_tree_id IS NULL ';

                $msg   = $this->_db->setQuery($quer1);
                $msg   = $this->_db->execute(); //$this->_db->query( );

                $returnStatus = 'treedef_6';
                break;
            case 'treedef_6':
                // PERSONS WITH MORE THAN 1 TREE
                // select all persons in the system without default tree
                // update with first tree (lowest number)
                $quer1 = 'UPDATE     #__joaktree_admin_persons  jan '
                        .',          ( SELECT     jtp1.person_id '
                         .'             ,          jtp1.tree_id '
                         .'             FROM       #__joaktree_tree_persons  jtp1 '
                         .'             INNER JOIN ( SELECT   jtp2.person_id '
                        .'                          ,        MIN(jtp2.tree_id) AS tree_id '
                        .'                          FROM     #__joaktree_tree_persons  jtp2 '
                        .'                          WHERE    jtp2.app_id = '.$app_id.' '
                        .'                          GROUP BY jtp2.person_id '
                        .'                        ) iv_jtp2 '
                        .'             ON         (   iv_jtp2.person_id = jtp1.person_id '
                        .'                        AND iv_jtp2.tree_id   = jtp1.tree_id '
                        .'                        ) '
                        .'             WHERE      jtp1.app_id = '.$app_id.' '
                        .'           ) iv_jtp1 '
                        .'SET        jan.default_tree_id = iv_jtp1.tree_id '
                        .'WHERE      jan.app_id          = '.$app_id.' '
                        .'AND        jan.id              = iv_jtp1.person_id '
                        .'AND        jan.default_tree_id IS NULL ';

                $msg   = $this->_db->setQuery($quer1);
                $msg   = $this->_db->execute(); //$this->_db->query( );

                $this->deleteProcPersonObject();
                $returnStatus = 'endtreedef';
                break;
            case 'error':
            default:
                $returnStatus = 'error';
                break;
        } // end of switch
        return $returnStatus;
    }
}
