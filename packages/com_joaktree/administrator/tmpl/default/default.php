<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;		// replace JText
use Joomla\CMS\HTML\HTMLHelper;		//replace JHtml

?>

<form action="index.php?option=com_joaktree" method="post" id="adminForm" name="adminForm">
<?php echo HTMLHelper::_( 'form.token' ); ?>
<?php if(!empty( $this->sidebar)) { ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<?php  $divClassSpan = 'span10'; ?>
<?php } else { ?>
	<?php  $divClassSpan = ''; ?>	
<?php } ?>
	<div id="j-main-container" class="<?php echo $divClassSpan; ?>">

	<table class="adminform">
		<tr>
			<td width="30%" valign="top">
				<div id="cpanel">
					
					<div style="float: left;">
						<div class="jt-icon">
							<a href="index.php?option=com_joaktree&view=applications">
								<img src="../media/com_joaktree/images/icon-48-app.png" />
								<br />
								<span><?php echo Text::_('JT_SUBMENU_APPLICATIONS'); ?></span>
							</a>
						</div>
					</div>
	
					<div style="float: left;">
						<div class="jt-icon">
							<a href="index.php?option=com_joaktree&view=trees">
								<img src="../media/com_joaktree/images/icon-48-familytree.png" />
								<br />
								<span><?php echo Text::_('JT_SUBMENU_FAMILYTREES'); ?></span>
							</a>
						</div>
					</div>
					
					<div style="float: left;">
						<div class="jt-icon">
							<a href="index.php?option=com_joaktree&view=maps">
								<img src="../media/com_joaktree/images/icon-48-map.png" />
								<br />
								<span><?php echo Text::_('JT_SUBMENU_MAPS'); ?></span>
							</a>
						</div>
					</div>
	
					<div style="float: left;">
						<div class="jt-icon">
							<a href="index.php?option=com_joaktree&view=persons">
								<img src="../media/com_joaktree/images/icon-48-person.png" />
								<br />
								<span><?php echo Text::_('JT_SUBMENU_PERSONS'); ?></span>
							</a>
						</div>
					</div>
	
					<div style="float: left;">
						<div class="jt-icon">
							<a href="index.php?option=com_joaktree&view=settings&layout=personname">
								<img src="../media/com_joaktree/images/icon-48-display2.png" />
								<br />
								<span><?php echo Text::_('JT_SUBMENU_PERSON_NAMEDISPLAY'); ?></span>
							</a>
						</div>
					</div>
	
					<div style="float: left;">
						<div class="jt-icon">
							<a href="index.php?option=com_joaktree&view=settings&layout=personevent">
								<img src="../media/com_joaktree/images/icon-48-display1.png" />
								<br />
								<span><?php echo Text::_('JT_SUBMENU_PERSON_EVENTDISPLAY'); ?></span>
							</a>
						</div>
					</div>
	
					<div style="float: left;">
						<div class="jt-icon">
							<a href="index.php?option=com_joaktree&view=settings&layout=relationevent">
								<img src="../media/com_joaktree/images/icon-48-display3.png" />
								<br />
								<span><?php echo Text::_('JT_SUBMENU_RELATION_EVENTDISPLAY'); ?></span>
							</a>
						</div>
					</div>
	
					<div style="float: left;">
						<div class="jt-icon">
							<a href="index.php?option=com_joaktree&view=themes">
								<img src="../media/com_joaktree/images/icon-48-theme.png" />
								<br />
								<span><?php echo Text::_('JT_SUBMENU_THEMES'); ?></span>
							</a>
						</div>
					</div>
					
					<div style="float: left;">
						<div class="jt-icon">
							<a href="https://www.joomxtensions.com/download/JoaktreeManuelv<?php echo $this->lists['version']; ?>.pdf" target="_manual">
								<img src="../media/com_joaktree/images/icon-48-help.png" />
								<br />
								<span><?php echo Text::_('JHELP'); ?></span>
							</a>
						</div>
					</div>
				</div>
			</td>
			<td width="60%" valign="top">
				<div style="300px;border:1px solid #ccc;background:#fff;margin:15px;padding:15px">
					<div style="float: right; margin:10px;">
						<img src="../media/com_joaktree/images/logo-100x60-joaktree.png" alt="logo-joaktree" />
					</div>
					<h1 style="margin: 0;"><?php echo Text::_('COM_JOAKTREE'); ?></h1>
					<h2><?php echo Text::_('COM_JOAKTREE_DESC'); ?></h2>
					<p><span class="jt-default"><?php echo Text::_('JT_VERSION');?>:</span><?php echo $this->lists['version'] ;?></p>
					<p><span class="jt-default"><?php echo Text::_('JT_COPYRIGHT');?>:</span>
						&copy; 2009 - 2014 Niels van Dantzig -&nbsp;(joaktree.com)
					<br/>
						&copy; <?php echo date("Y"); ?> Robert Gastaud
						&nbsp;(<a href="https://joomxtensions.com/" target="_joaktree">joomxtensions.com</a>)
					</p>					
					<div style="clear:both">&nbsp;</div>
					
					<h3><?php echo Text::_('COM_JOAKTREE_LICENSES');?></h3>
					<p><span class="jt-default"><?php echo Text::_('COM_JOAKTREE'); ?></span>
						<a href="http://www.gnu.org/licenses/gpl.html" target="_blank"><abbr title="GNU General Public License">GPL</abbr></a>
					</p>
					
					<p style="font-size: 110%; color: red;"><?php echo Text::_('MBJ_DISCAIMER'); ?></p>
					
					<?php foreach ($this->lists['providers'] as $provider) { ?>
						<p><span class="jt-default"><?php echo $provider->name; ?></span>
							<a href="<?php echo $provider->license; ?>" target="_viewlicense"><?php echo $provider->license; ?></a>
					
						</p>
					<?php } ?>
					
					<div style="clear:both">&nbsp;</div>
					<h3><?php echo Text::_('COM_JOAKTREE_CREDITS');?></h3>
					<p><strong><?php echo Text::_('Character set conversion');?></strong></p>
					<p class="license"><?php echo Text::_('ANSEL to UNICODE');?></p>
					<p>The routine for conversion of ANSEL character set to UNICODE is based on the routine of <a href="http://solventus.so.funpic.de" target="_blank">JGen_0.9.80 by Solventus</a></p>
					<p>This ANSEL to UNICODE conversion is based on the table by <a href="http://www.heiner-eichmann.de" target="_blank">Heiner Eichmann (http://www.heiner-eichmann.de)</a></p>
					<p>&nbsp;</p>
		
					<p class="license"><?php echo Text::_('UNICODE to UTF-8');?></p>
					<p>The routine for conversion of UNICODE character set to UTF-8 is based on the routine of <a href="http://hsivonen.iki.fi/php-utf8/" target="_blank">Henri Sivonen</a></p>
					<p>The code has been adapted from the <a href="http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUTF8ToUnicode.cpp" target="_blank">UTF-8 to UTF-16</a> 
					   and <a href="http://lxr.mozilla.org/seamonkey/source/intl/uconv/src/nsUnicodeToUTF8.cpp" target="_blank">UTF-16 to UTF-8</a> 
					   converters of Mozilla. 
					   Hence, the code is provided under an <a href="http://www.mozilla.org/MPL/NPL-1.1.html" target="_blank"><abbr title="Netscape Public License">NPL</abbr> 1.1</a>/
					   <a href="http://www.gnu.org/licenses/gpl.html" target="_blank"><abbr title="GNU General Public License">GPL</abbr></a> 2.0/
					   <a href="http://www.gnu.org/licenses/lgpl.html" target="_blank"><abbr title="GNU Lesser General Public License">LGPL</abbr></a> 2.1 tri-license.
					</p>
					<p>&nbsp;</p>
		
					<p><strong><?php echo Text::_('Pictures');?></strong></p>
					<p>The images are shown using routine from ImageSlideShow by <a href="http://briask.com/blog/download-imageslideshow/" target="_blank">Briask</a>.
					</p> 
					<p><a href="http://www.shadowbox-js.com/index.html" target="_blank">Shadowbox.js</a> is used for viewing as well.
					</p>
					<p>The ImageSlideShow is provide under a <a href="http://www.gnu.org/licenses/gpl.html" target="_blank"><abbr title="GNU General Public License">GPL</abbr></a> licence.
					</p>
				</div>
			</td>
		</tr>
	</table>
</div>

<input type="hidden" name="option" value="com_joaktree" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="joaktree" />


</form>