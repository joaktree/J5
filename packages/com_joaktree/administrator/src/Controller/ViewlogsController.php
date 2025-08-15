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

namespace Joaktree\Component\Joaktree\Administrator\Controller;

defined('_JEXEC') or die;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\Filesystem\File;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;

class ViewlogsController extends FormController
{
    public function upload($key = null, $urlVar = null)
    {
        // Retrieve file details from uploaded file, sent from upload form
        $file = Factory::getApplication()->input->files->get('fileupload');
        $id = (int)Factory::getApplication()->input->get('appid');

        // Clean up filename to get rid of strange characters like spaces etc.
        $filename = File::makeSafe($file['name']);

        // Set up the source and destination of the file
        $src = $file['tmp_name'];
        
        $config     = ComponentHelper::getParams('com_joaktree') ;
        $defpath    = $config->get('gedcomfile_path', 'files/com_joaktree/gedfiles');
        
        if ($id) {
            $params	= JoaktreeHelper::getJTParams($id);
            $path   = JPATH_ROOT.'/'.$params->get('gedcomfile_path',$defpath);
        } else {
            $path   = JPATH_ROOT.'/'.$defpath;
        }
        $dest = $path ."/" . $filename;

        $user = Factory::getApplication()->getIdentity();

        if (File::upload($src, $dest)) {
            $result = Text::sprintf('COM_JOAKTREE_UPLOAD_DONE', $user->username, $filename, $dest);
            JoaktreeHelper::addLog($result,'joaktree');
            echo  "<script type='text/javascript'>";
            echo "window.parent.jtUploadedFile('".$filename."');";
            echo "</script>";
            $this->input->set('tmpl', 'component');
            parent::display();
        } else {
            $result = Text::sprintf('COM_JOAKTREE_UPLOAD_NOTDONE', $user->username, $filename);
            JoaktreeHelper::addLog($result,'joaktree');
            Factory::getApplication()->enqueueMessage(Text::sprintf('COM_JOAKTREE_UPLOAD_ERROR', $filename));
        }
    }
}
