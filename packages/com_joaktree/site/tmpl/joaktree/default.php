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
// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joaktree\Component\Joaktree\Site\Helper\FormHelper;

// Load Bootstrap
HtmlHelper::_('bootstrap.framework', true);
?>
<script>
//window.addEvent('domready', function() {
	window.addEventListener('domready',function() {
    set_jtTabs();
});
</script>
<?php // script for domready
  if ((is_object($this->canDo)) &&
        (
            $this->canDo->get('core.create')
        || $this->canDo->get('media.create')
        || $this->canDo->get('core.edit')
        || $this->canDo->get('core.edit.state')
        || $this->canDo->get('core.delete')
        )
     && ($this->lists['edit'])
  ) {
      $script = array();
      $script[] = '<script>';
      //$script[] = 'window.addEvent(\'domready\', function() {';
      $script[] = 'window.addEventListener(\'domready\',function(){';
      $script[] = 'toggleAjaxTabs(5, \'0\'); return false;';
      $script[] = '});';
      $script[] = '</script>';
      echo implode("\n", $script);
  }
?>
<!--  shadowbox -->
<script type="text/javascript">
	Shadowbox.init({
	    continuous:     true,
	    handleOversize: "resize",
	    slideshowDelay: <?php echo $this->lists['nextDelay']; ?>,
	    fadeDuration:   <?php echo $this->lists['transDelay']; ?>
	});	
</script>
<?php if ((is_object($this->canDo)) && (
    $this->canDo->get('core.create')
                                        || $this->canDo->get('media.create')
                                        || $this->canDo->get('core.edit')
                                        || $this->canDo->get('core.edit.state')
                                        || $this->canDo->get('core.delete')
)
) {
    echo FormHelper::getSubmitScript(trim($this->person->firstName).' '.trim($this->person->familyName));
}
?>	
<div id="jt-content">
<?php if ($this->lists['userAccess']) { ?> 
<form action="<?php echo Route::_('index.php?option=com_joaktree'); ?>" method="post" name="joaktreeForm" id="joaktreeForm" class="form-validate">
<!-- user has access to information -->
	<!-- no Javascript :: show link to switch to basic view (not using Javascript) -->
	<noscript>
		<?php
        if ($this->lists['technology'] != 'b') {
            $link1 = Route::_('index.php?option=com_joaktree&view=joaktree&tech=b&treeId='.$this->person->tree_id.'&personId='.$this->person->app_id.'!'.$this->person->id);
            ?>
			<div class="jt-content-th">
				<?php echo Text::_('JT_NOJAVASCRIPT'); ?>
				<br/><a href="<?php echo $link1; ?>" rel="noindex, nofollow"><?php echo Text::_('JT_NOJAVASCRIPT_LINK'); ?></a>
			</div>
		<?php } ?>
	</noscript>
	
	<!-- no AJAX :: show link to switch to view using only Javascript -->
	<div id="noajaxid" class="jt-hide">
		<div class="jt-content-th">
		<?php
                $link2 = Route::_('index.php?option=com_joaktree&view=joaktree&tech=j&treeId='.$this->person->tree_id.'&personId='.$this->person->app_id.'!'.$this->person->id);
    ?>
		<?php echo Text::_('JT_NOAJAX'); ?>
		<br/><a href="<?php echo $link2; ?>" rel="noindex, nofollow"><?php echo Text::_('JT_NOAJAX_LINK'); ?></a>
		</div>
	</div>
	<!-- Show lineage -->
<?php
   if (is_array($this->Html) && array_key_exists('lineage', $this->Html)) {
       echo $this->Html[ 'lineage' ];
   }
    ?>
<?php
    $layout = $this->setLayout('');
    $this->display('names');
    $this->setLayout($layout);
    ?>
<!-- tabs only active with AJAX -->
<?php
        if (($this->lists['technology'] != 'b') && ($this->lists['technology'] != 'j')) {
            $html = '';
            if ((($this->person->indHasParent == true) && ($this->lists['showAncestors'] == 1))
               || (($this->person->indHasChild == true) && ($this->lists['showDescendants'] == 1))
               || ($this->person->indNote == true)
               || ($this->lists['numberArticles'] > 0)
               || (
                   (is_object($this->canDo)) &&
                        (
                            $this->canDo->get('core.create')
                    || $this->canDo->get('media.create')
                    || $this->canDo->get('core.edit')
                    || $this->canDo->get('core.edit.state')
                    || $this->canDo->get('core.delete')
                        )
               )
            ) {
                $html .= '<div id="jt-tabbar" class="jt-clearfix">';
                $html .= '<span class="jt-tabline">&nbsp;</span>';
                $link = '';
                $html .= '<a href="#" id="jt1tabid" ';
                $html .= 'class="jt-tab-active jt-tablabel-active" ';
                $html .= 'style="position: relative;" ';
                $html .= $this->lists[ 'action' ].'="toggleAjaxTabs(1, \''.$link.'\'); return false;">';
                $html .= Text::_('JT_DETAILS');
                $html .= '</a>';
                if (($this->person->indNote == true) || ($this->lists['numberArticles'] > 0)) {
                    $html .= '<span class="jt-tabline">&nbsp;</span>';
                    $link =  Route::_(
                        'index.php?format=raw&option=com_joaktree'
                            .'&view=joaktree&layout=_information'
                            .'&personId='.$this->person->app_id.'!'.$this->person->id
                            .'&treeId='.$this->person->tree_id
                            .'&technology='.$this->lists['technology']
                    );
                    $html .= '<a href="#" id="jt2tabid" class="jt-tab-inactive jt-tablabel-inactive" style="position: relative;" ';
                    $html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_INFORMATION').'" ';
                    $html .= $this->lists[ 'action' ].'="toggleAjaxTabs(2, \''.$link.'\'); return false;" >';
                    $html .= Text::_('JT_INFORMATION');
                    $html .= '</a>';
                }
                if (($this->person->indHasParent == true) && ($this->lists['showAncestors'] == 1)) {
                    $html .= '<span class="jt-tabline">&nbsp;</span>';
                    $link =  Route::_(
                        'index.php?format=raw&option=com_joaktree'
                            .'&view=ancestors&layout=_generation'
                            .'&personId='.$this->person->app_id.'!'.$this->person->id
                            .'&treeId='.$this->person->tree_id
                            .'&technology='.$this->lists['technology']
                    );
                    $html .= '<a href="#" id="jt3tabid" class="jt-tab-inactive jt-tablabel-inactive" style="position: relative;" ';
                    $html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_ANCESTORS').'" ';
                    $html .= $this->lists[ 'action' ].'="toggleAjaxTabs(3, \''.$link.'\'); return false;" >';
                    $html .= Text::_('JT_ANCESTORS');
                    $html .= '</a>';
                }
                if (($this->person->indHasChild == true) && ($this->lists['showDescendants'] == 1)) {
                    $html .= '<span class="jt-tabline">&nbsp;</span>';
                    $link =  Route::_(
                        'index.php?format=raw&option=com_joaktree'
                            .'&view=descendants&layout=_generation'
                            .'&personId='.$this->person->app_id.'!'.$this->person->id
                            .'&treeId='.$this->person->tree_id
                            .'&technology='.$this->lists['technology']
                    );
                    $html .= '<a href="#" id="jt4tabid" class="jt-tab-inactive jt-tablabel-inactive" style="position: relative;" ';
                    $html .= 'title="'.Text::_('JT_SHOW').' '.Text::_('JT_DESCENDANTS').'" ';
                    $html .= $this->lists[ 'action' ].'="toggleAjaxTabs(4, \''.$link.'\'); return false;" >';
                    $html .= Text::_('JT_DESCENDANTS');
                    $html .= '</a>';
                }
                if ((is_object($this->canDo)) &&
                        (
                            $this->canDo->get('core.create')
                    || $this->canDo->get('media.create')
                    || $this->canDo->get('core.edit')
                    || $this->canDo->get('core.edit.state')
                    || $this->canDo->get('core.delete')
                        )
                ) {
                    $html .= '<span class="jt-tabline" style="float: right;">&nbsp;</span>';
                    $link = 0;
                    $html .= '<a href="#" id="jt5tabid" ';
                    $html .= 'class="jt-tab-edit jt-tablabel-inactive" ';
                    $html .= 'style="position: relative;" ';
                    $html .= 'title="'.Text::_('JACTION_EDIT').'" ';
                    $html .= $this->lists[ 'action' ].'="toggleAjaxTabs(5, \''.$link.'\'); return false;" >';
                    $html .= Text::_('JACTION_EDIT');
                    $html .= '</a>';
                }
                $html .= '<span class="jt-tabline">&nbsp;</span>';
                $html .= '</div>';
            }
            echo $html;
        }
    ?>	
	<div id="jt1tabpageid" class="jt-show-block">
		<!-- two columns for basic information and picture -->
		<div class="jt-clearfix">
			<div class="jt-person-info">
				<div style="min-height: 12em;">
				<!-- Show person -->
				<?php
                        $layout = $this->setLayout('');
    $this->display('personevents');
    $this->setLayout($layout);
    ?>
				</div>
				<?php
        $layout = $this->setLayout('');
    $this->display('sourceornotebutton');
    $this->setLayout($layout);
    ?>
			</div> <!-- end float left -->
			<div class="jt-picture">
				<!-- Show picture -->			
				<?php
        $layout = $this->setLayout('');
    $this->display('pictures');
    $this->setLayout($layout);
    ?>
			</div> <!-- end float right -->
		</div> <!-- end clearfix -->
		<div class="jt-clearfix">
			<!-- source text is shown below the two columns -->
			<?php
    $layout = $this->setLayout('');
    $this->display('sourceornotetext');
    $this->setLayout($layout);
    ?>
			<!-- show static map -->
			<?php if (($this->person->map == 1) && ($this->lists['indStaticMap'])) {
			    if ($this->map->getService()->getProvider()->getName() == 'OpenStreetMap') {
			        $ascript = $this->map->getMapScript();
			        echo $ascript;
			    } else {
			        ?>						
				<img src="<?php echo $this->Html[ 'staticmap' ]; ?>" />	
                <?php
			            if (is_array($this->Html) && array_key_exists('staticmap', $this->Html)) {
			                //echo $this->Html[ 'staticmap' ];
			            }
			    }
			}?>  
			<!-- show interactive map -->
			<?php if (($this->person->map == 2) && ($this->lists['indInteractiveMap'])) {
			    $height = 'height: '.((int) $this->lists[ 'pxHeightMap'] + 0).'px; ';
			    ?>	
			<?php   if (is_array($this->Html) && array_key_exists('interactivemap', $this->Html)) {
			    if ($this->map->getService()->getProvider()->getName() == 'OpenStreetMap') {
			        $ascript = $this->map->getMapScript();
			        echo $ascript;
			    } else {
			        ?>
				<div style="<?php echo $height; ?>">
					<iframe 
						src="<?php echo $this->Html[ 'interactivemap' ]; ?>" 
						height="<?php echo (int) $this->lists[ 'pxHeightMap'];?>px"
						style="border:1px solid #dddddd;"
					>
					</iframe>
				</div>	
				<div class="jt-clearfix"></div>  
			<?php       }
			}
			} ?>
			<hr width="100%" size="2" />
			<?php
			$layout = $this->setLayout('');
    $this->display('parents');
    $this->setLayout($layout);
    $layout = $this->setLayout('');
    $this->display('partners');
    $this->setLayout($layout);
    $layout = $this->setLayout('');
    $this->display('children');
    $this->setLayout($layout);
    ?>
		</div> <!-- clearfix -->
	</div>
	<!-- tabs only active with AJAX -->
	<?php
    if (($this->lists['technology'] != 'b') && ($this->lists['technology'] != 'j')) {
        ?>		
		<div id="jt2tabpageid" class="jt-ajax">
			<div class="jt-ajax-loader"><?php  echo Text::_('JT_LOADING').'&nbsp;'.Text::_('JT_INFORMATION'); ?></div>
		</div>
		<div id="jt3tabpageid" class="jt-ajax">
			<div class="jt-ajax-loader"><?php  echo Text::_('JT_LOADING').'&nbsp;'.Text::_('JT_ANCESTORS'); ?></div>
		</div>
		<div id="jt4tabpageid" class="jt-ajax">
			<div class="jt-ajax-loader"><?php  echo Text::_('JT_LOADING').'&nbsp;'.Text::_('JT_DESCENDANTS'); ?></div>
		</div>
	<?php
    }
    ?>
	<input type="hidden" name="personId" value="<?php echo $this->person->app_id.'!'.$this->person->id; ?>" />
	<input type="hidden" name="relationId" value="" id="jform_person_relation_id"/ >
	<input type="hidden" name="treeId" value="<?php echo $this->person->tree_id; ?>" />
	<input type="hidden" name="tech" value="<?php echo $this->lists['technology']; ?>" />
	<input type="hidden" name="object" value="" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="controller" value="personform" />
	<?php echo HtmlHelper::_('form.token'); ?>	
</form>
<?php } else { ?>
<!-- user has NO access to information -->
	<div class="jt-content-th" >
		<div class="jt-noaccess"><?php echo Text::_('JT_NOACCESS'); ?></div>
	</div>
<?php } ?>
	<div class="jt-clearfix jt-update">
	<?php
        if ($this->lists[ 'showUpdate '] != 'N') {
            echo $this->lists[ 'lastUpdate' ].'&nbsp;|&nbsp;';
            if ($this->lists[ 'showchange' ] == 1) {
                $link =  Route::_(
                    'index.php?&option=com_joaktree'
                                    .(($this->lists['technology'] != 'b') ? '&tmpl=component' : '')
                                    .'&view=changehistory'
                                    .'&retId='.$this->lists[ 'retId' ]
                                    .'&treeId='.$this->person->tree_id
                                    .'&technology='.$this->lists['technology']
                );
                if ($this->lists['technology'] != 'b') { ?>
                            <a data-bs-toggle="modal" data-bs-target="#selectperson"><?php echo Text::_('JT_CHANGEHISTORY'); ?></a>
                            <div class="modal fade modal-xl"  id="selectperson" tabindex="-1" aria-labelledby="selectperson" aria-hidden="true">
                                <div class="modal-dialog h-75">
                                    <div class="modal-content h-100">
                                        <div class="modal-header">
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body h-100">
                                            <iframe id="iframeModalWindow" height="100%" src="<?php echo $link; ?>" name="iframe_modal"></iframe>      
                                        </div>
                                    </div>
                                </div>
                            </div>
                <?php
                } else {
                    ?>   
                    <a href="<?php echo $link;?>" rel="noindex, nofollow">
                        <?php echo Text::_('JT_CHANGEHISTORY'); ?>
                    </a>
                <?php }
                echo '&nbsp;|&nbsp';
            }
        }
?>
	</div>
	<div class="jt-stamp">
		<?php echo $this->lists[ 'CR' ]; ?>
	</div>
</div><!-- jt-content -->
