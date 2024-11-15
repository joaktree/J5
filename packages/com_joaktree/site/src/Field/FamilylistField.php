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
namespace Joaktree\Component\Joaktree\Site\Field;
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joaktree\Component\Joaktree\Site\Helper\JoaktreeHelper;

class FamilyListField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $type = 'FamilyList';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 * @since   11.1
	 */
	protected function getInput()
	{
		// Initialize variables.
		$html = array();
		$attr = '';
		
		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ( (string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true') {
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// we take the first of the list
		$this->value = ($this->value) ? $this->value : $options[0]->value;
		$this->id	 = ($this->id)    ? $this->id    : $options[0]->text;
		
		// Create a regular list.
		$html[] = HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		
		return implode($html);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 * @since   11.1
	 */
	protected function getOptions()
	{
		// Possible actions are: addparent, addchild, addpartner
		
		// Initialize variables.
		$appId	  = JoaktreeHelper::getApplicationId();
		$action   = JoaktreeHelper::getAction();
		$personId = JoaktreeHelper::getRelationId();
		
		// Initialize variables.
		$options = array();
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		
		$query = $db->getquery(true);
		
		switch ($action) {
			case "addparent":
				// family for adding parents
				// select all parents of the person, and for every parent its family-id
				$query->select(' jrn.family_id ');
				$query->select(' jrn.person_id_2 AS pid2 ');
				$query->from( ' #__joaktree_relations  jrn ');
				$query->where(' jrn.app_id      = '.$appId.' ');
				$query->where(' jrn.person_id_1 = '.$db->quote($personId).' ');
				$query->where(' jrn.type        IN ('.$db->quote('father').', '.$db->quote('mother').') ');
							 
				$query->select(JoaktreeHelper::getConcatenatedFullName().' AS fullName ');
				$query->innerJoin(' #__joaktree_persons  jpn '
								 .' ON (   jpn.app_id = jrn.app_id '
								 .'    AND jpn.id     = jrn.person_id_2 '
								 .'    ) '
								 );
				$query->innerJoin(JoaktreeHelper::getJoinAdminPersons());
				break;
			case "addchild":
				// family for adding children
				// select all partners of the person, and for every partner its family-id
				$query->select(' jrn.family_id ');
				$query->select(' IF( (jrn.person_id_1 = '.$db->quote($personId).') '
				              .'   , jrn.person_id_2 '
				              .'   , jrn.person_id_1 '
				              .'   ) AS pid2 '
				              );
	
				$query->from( ' #__joaktree_relations  jrn ');
				$query->where(' jrn.app_id      = '.$appId.' ');
				$query->where(' (  jrn.person_id_1 = '.$db->quote($personId).' '
							 .' OR jrn.person_id_2 = '.$db->quote($personId).' '
							 .' ) '
							 );
				$query->where(' jrn.type        = '.$db->quote('partner').' ');
							 
				$query->select(JoaktreeHelper::getConcatenatedFullName().' AS fullName ');
				$query->innerJoin(' #__joaktree_persons  jpn '
								 .' ON (   jpn.app_id = jrn.app_id '
								 .'    AND (  jpn.id     = jrn.person_id_1 '
								 .'        OR jpn.id     = jrn.person_id_2 '
								 .'        ) '
								 .'    AND jpn.id != '.$db->quote($personId).' '
								 .'    ) '
								 );
				$query->innerJoin(JoaktreeHelper::getJoinAdminPersons());		
				break;
			default:	// continue
				break;
		}
		
		if (($action == 'addparent') || ($action == 'addchild')) {
			// Set the query and get the result list.
			try{
                $db->setquery($query);
                $items = $db->loadObjectlist();	
            } catch (\Exception $e) {
				Factory::getApplication()->enqueueMessage('500 '. $db->getErrorMsg(),'error');
				return $options;
			}
	
			// Build the field options.
			if (!empty($items)) {
				foreach($items as $item) {
					$options[] = HTMLHelper::_('select.option', $item->pid2.'!'.$item->family_id, $item->fullName);
				}
			}			
		}	
		
		if (($action == 'addpartner') || ($action == 'addchild')) {
			// select family-id of single parent families.
			// new partner or new child may be added to this family 
			$query->clear();
			
			$query->select(' DISTINCT jrn.family_id ');
			$query->from(  ' #__joaktree_relations  jrn ');
			$query->where( ' jrn.app_id = '.$appId.' ');
			$query->where( ' jrn.person_id_2 = '.$db->quote($personId).' ');
			$query->where( ' jrn.type IN ('.$db->quote('father').', '.$db->quote('mother').') ');
			$query->where( ' NOT EXISTS ' 
						 . ' ( SELECT 1 '
						 . '   FROM   #__joaktree_relations  jrn2 '
						 . '   WHERE  jrn2.app_id    = jrn.app_id '
						 . '   AND    jrn2.family_id = jrn.family_id '
						 . '   AND    jrn2.type      = '.$db->quote('partner').' '
						 . ' ) '
						 );
						 
			$query->select(' GROUP_CONCAT(jpn.firstName SEPARATOR '.$db->quote(', ').') AS names ');
			$query->innerJoin(' #__joaktree_persons  jpn '
							 .' ON (   jpn.app_id = jrn.app_id '
							 .'    AND jpn.id     = jrn.person_id_1 '
							 .'    ) '
							 );
			$query->innerJoin(JoaktreeHelper::getJoinAdminPersons());
						 
			$db->setquery( $query );
			$familyId  = $db->loadObject();
		}
		
		// if familyId is unknown (either in case of addparent, of with no result for the last query
		// the familyId is set to be '0'. The option is added to the list.		
		switch ($action) {
			case "addparent":
				$options[] = HTMLHelper::_('select.option', '0!0', Text::_('JT_NEWFAMILY1'));
				break;
			case "addchild":
				$familyId = (is_object($familyId) && ($familyId->family_id)) ? $familyId->family_id : '0';
				$options[] = HTMLHelper::_('select.option', '0!'.$familyId, Text::_('JT_NEWFAMILY3'));
				break;
			case "addpartner":
				if (is_object($familyId) && ($familyId->family_id)) {
					$options[] = HTMLHelper::_('select.option', '0!'.$familyId->family_id, $familyId->names);
				}
				// finally we add the option for a new partner / new family
				$options[] = HTMLHelper::_('select.option', '0!0', Text::_('JT_NEWFAMILY2'));
				break;
			default:	// continue
				break;
		}
		
		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
