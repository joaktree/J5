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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joaktree\Component\Joaktree\Administrator\Helper\Gedcompersons2;
use Joaktree\Component\Joaktree\Administrator\Helper\Gedcomsources2;
use Joaktree\Component\Joaktree\Administrator\Helper\Gedcomrepos2;
use Joaktree\Component\Joaktree\Administrator\Helper\Gedcomnotes2;
use Joaktree\Component\Joaktree\Administrator\Helper\Gedcomdocuments2;
use Joaktree\Component\Joaktree\Administrator\Helper\JoaktreeHelper;
use Joaktree\Component\Joaktree\Administrator\Helper\Names;
use Joaktree\Component\Joaktree\Administrator\Helper\Relations;

class Gedcomfile2
{
    protected $application = null;
    protected $person = null;
    protected $source = null;
    protected $repo = null;
    protected $note = null;
    protected $document = null;
    protected $procObject = null;
    protected $unicode2utf = null;
    protected $objectType ;
    protected $objectKey;
    protected $objectLines;

    public function __construct($procObject)
    {
        $this->application = Factory::getApplication();
        $this->person   = new Gedcompersons2($procObject->id);
        $this->source   = new Gedcomsources2($procObject->id);
        $this->repo     = new Gedcomrepos2($procObject->id);
        $this->note     = new Gedcomnotes2($procObject->id);
        $this->document = new Gedcomdocuments2($procObject->id);
        $this->procObject = $procObject;
    }

    /*
    ** Funtion to create an array (table) for conversion ANSEL characters to
    ** Unicode characters.
    ** Function is taken from JGEN by Solventus (http://solventus.so.funpic.de)
    ** The ANSEL to Unicode conversion is based on the table by Heiner Eichmann
    ** http://www.heiner-eichmann.de
    */
    private function prepareANSEL()
    {
        global $ANSELtable;

        // retrieve information name and location of conversion file
        $path     = JPATH_ROOT.'/media/com_joaktree/conversion';
        $filename = $path.'/ans2uni.con';

        // check if conversion file exists
        if (!@is_file($filename)) {
            $msg = '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_NOCONVFILE' . $filename);
            return $msg;
        }

        $ANSELfile = file($filename);

        // loop through the file and store in array $ANSELtable
        foreach ($ANSELfile as $ANSELline) {
            //list($ANSEL,$UNICODEt)  = explode("=",$ANSELline);
            $tmp1 = explode("=", $ANSELline);
            $ANSEL = $tmp1[0];
            $UNICODEt = $tmp1[1];
            unset($tmp1);

            //list($hex1,$hex2,$hex3) = explode("+",$ANSEL);
            $tmp2 = explode("+", $ANSEL);
            $hex1 = (isset($tmp2[0])) ? $tmp2[0] : '';
            $hex2 = (isset($tmp2[1])) ? $tmp2[1] : '';
            $hex3 = (isset($tmp2[2])) ? $tmp2[2] : '';
            unset($tmp2);
            $hex12 = $hex1.$hex2;
            $hex123 = $hex12.$hex3;

            $UNICODE = substr($UNICODEt, 0, 4);

            if ($hex2 != "") {
                $ANSELtable[$hex1] = "+";
                if ($hex3 != "") {
                    $ANSELtable[$hex12] = "+";
                    $ANSELtable[$hex123] = $UNICODE;
                } else {
                    $ANSELtable[$hex12] = $UNICODE;
                }
            } else {
                $ANSELtable[$hex1] = $UNICODE;
            }
        }

        $msg = false;
        return $msg;
    }


    /*
    ** Funtion to convert a string ANSEL characters to UNICODE or UTF-8
    ** Function for conversion from ANSEL to UNICODE is taken from JGEN
    ** by Solventus (http://solventus.so.funpic.de)
    ** Function for conversion of UNICODE to UTF-8 by Henri Sivonen, hsivonen@iki.fi,
    ** based on Mozilla Communicator client code (http://www.mozilla.org/NPL/)
    */
    private function convertANSEL($instring)
    {
        // Initialization
        global $ANSELtable;
        $length = strlen($instring);
        $counter = 0;
        $outstring = "";

        // loop through the string
        while ($counter <= $length) {
            // extract next character
            $chr1 = substr($instring, $counter, 1);

            // convert character to ASCII integer
            $ord1 = ord($chr1);

            // convert ASCII integer to hexadecimal string (2 positions)
            $hex1 = strtoupper(dechex($ord1));
            if (strlen($hex1) == 1) {
                $hex1 = "0".$hex1;
            }

            // character is extracted, so we move one position along the intial string
            $counter++;

            // analyse the character
            if ($ord1 < 128) {
                // for ASCII integer values to 128, no conversion is needed
                $outstring .= $chr1;
            } else {
                // conversion is needed
                // check whether the character itself is in conversion table
                if ($ANSELtable[$hex1] == "+") {
                    // character is NOT independently in conversion table
                    // extract next character, and convert to hexadecimal string (2 positions)
                    $chr2 = substr($instring, $counter, 1);
                    $hex2 = strtoupper(dechex(ord($chr2)));
                    if (strlen($hex2) == 1) {
                        $hex2 = "0".$hex2;
                    }

                    // character is extracted, so we move one position along the intial string
                    $counter++;

                    // check whether the two characters are in conversion table
                    $hex12 = $hex1.$hex2;
                    if ($ANSELtable[$hex12] == "+") {
                        // two character combination is NOT in conversion table
                        // extract next character, and convert to hexadecimal string (2 positions)
                        $chr3 = substr($instring, $counter, 1);
                        $hex3 = strtoupper(dechex(ord($chr3)));
                        if (strlen($hex3) == 1) {
                            $hex3 = "0".$hex3;
                        }

                        // character is extracted, so we move one position along the intial string
                        $counter++;

                        // retrieve three character combination from conversion table
                        // and take its UNICODE value.
                        $hex123 = $hex12.$hex3;
                        $UNICODE = $ANSELtable[$hex123];

                    } else {
                        // two character combination is in conversion table
                        // take its UNICODE value
                        $UNICODE = $ANSELtable[$hex12];
                    }
                } else {
                    // character is by itself in conversion table
                    // take its UNICODE value
                    $UNICODE = $ANSELtable[$hex1];
                }

                // ANSEL character is converted to UNICODE
                if ($this->unicode2utf == true) {
                    // now conversion from unicode to UTF-8 starts
                    // when so indicated in parameters
                    $dest = '';
                    $src  = hexdec($UNICODE);

                    if (($src >= 0) && ($src <= 0x007f)) {
                        $dest .= chr($src);
                    } elseif ($src <= 0x07ff) {
                        $dest .= chr(0xc0 | ($src >> 6));
                        $dest .= chr(0x80 | ($src & 0x003f));
                    } elseif ($src == 0xFEFF) {
                        // nop -- zap the BOM
                        $dest = '';
                    } elseif ($src >= 0xD800 && $src <= 0xDFFF) {
                        // found a surrogate
                        $dest = '';
                    } elseif ($src <= 0xffff) {
                        $dest .= chr(0xe0 | ($src >> 12));
                        $dest .= chr(0x80 | (($src >> 6) & 0x003f));
                        $dest .= chr(0x80 | ($src & 0x003f));
                    } elseif ($src <= 0x10ffff) {
                        $dest .= chr(0xf0 | ($src >> 18));
                        $dest .= chr(0x80 | (($src >> 12) & 0x3f));
                        $dest .= chr(0x80 | (($src >> 6) & 0x3f));
                        $dest .= chr(0x80 | ($src & 0x3f));
                    } else {
                        // out of range
                        $dest = '';
                    }

                    $outstring .= $dest;
                } else {
                    // no conversion from UNICODE to UTF
                    $outstring .= chr($UNICODE);
                }
            }
        }

        return $outstring;
    }


    /*
    ** private function to proces one gedcom object.
    */
    private function process_object()
    {
        // depending on type
        switch ($this->objectType) {
            case "INDI":	// process person
                $ret = $this->person->process($this->objectKey, $this->objectLines);
                $this->procObject->persons++;
                if (!$ret) {
                    $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_NOSUCPERSON', $this->objectKey);
                }
                break;

            case "FAM": 	// process family
                $ret = $this->person->family($this->objectKey, $this->objectLines);
                $this->procObject->families++;
                if (!$ret) {
                    $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_NOSUCFAMILY', $this->objectKey);
                }
                break;

            case "SOUR":	// process source
                $ret = $this->source->process($this->objectKey, $this->objectLines);
                $this->procObject->sources++;
                if (!$ret) {
                    $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_NOSUCSOURCE', $this->objectKey);
                }
                break;

            case "REPO":	// process repository
                $ret = $this->repo->process($this->objectKey, $this->objectLines);
                $this->procObject->repos++;
                if (!$ret) {
                    $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_NOSUCREPO', $this->objectKey);
                }
                break;

            case "NOTE":	// process note
                $ret = $this->note->process($this->objectKey, $this->objectLines);
                $this->procObject->notes++;
                if (!$ret) {
                    $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_NOSUCNOTE', $this->objectKey);
                }
                break;

            case "OBJE":	// process document
                $ret = $this->document->process($this->objectKey, $this->objectLines);
                $this->procObject->docs++;
                if (!$ret) {
                    $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_NOSUCDOC', $this->objectKey);
                }
                break;

            default:		if (($this->objectType != 'HEAD')
                                && ($this->objectType != 'SUBM')
                                && ($this->objectType != 'TRLR')
                                && ($this->objectType != 'LABL')
                                && ($this->objectType != '_TODO')
            ) {
                $this->procObject->unknown++;
                $this->application->enqueueMessage($this->procObject->unknown.': '.$this->objectType, 'notice');
                $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_UNKNOWNOBJ', $this->procObject->unknown.': '.$this->objectType);
            }
                break;
        }

        // we are ready -> empty objectLines
        array_splice($this->objectLines, 0);

    }

    /*
    ** Main function to import the gedcom file.
    */
    public function process($part)
    {
        // initialize parameters and paths / filename
        $params				= JoaktreeHelper::getJTParams($this->procObject->id);
        $conversion_type 	= $params->get('unicode2utf');
        $path  				= JPATH_ROOT.'/'.$params->get('gedcomfile_path');
        $filename			= $path.'/'.$params->get('gedcomfile_name');
        $patronymSetting	= (int) $params->get('patronym');
        $truncate_rel_value	= (int) $params->get('truncrelations');
        //		$procStep			= (int) $params->get('processStep', 1);
        $procStepSize		= (int) $params->get('procStepSize', 50);
        $ret				= true;
        //		$indAjax 			= ($procStep == 1);
        $indAjax 			= true;
        $map_def            = $params->get('map');

        // check if gedcom file exists
        if (!@is_file($filename)) {
            $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . '<br />'.Text::sprintf('JTGEDCOM_MESSAGE_NOGEDCOM', $filename);
            $this->procObject->status = 'error';
            return $this->procObject;
        }

        // initialize array
        $objectLine   		= array();
        $this->objectLines  = array();

        // What type of conversion
        if ($conversion_type == 0) {
            // no conversion
            $conversion = false;
        } elseif (($conversion_type == 1)) {
            // conversion from ANSEL to UTF-8
            $conversion = true;
            $this->unicode2utf = true;
        } elseif (($conversion_type == 2)) {
            // conversion from ANSEL to Unicode
            $conversion = true;
            $this->unicode2utf = false;
        } else {
            // parameter has unknown value: no conversion
            $conversion = false;
        }

        // remove double and trailing characters, like comma's
        if ($params->get('removeChar')) {
            $removeChar = $params->get('removeChar');
        } else {
            $removeChar = false;
        }

        // initialize counters
        $teller0 = 0; // counter for gedcom objects
        $tellert = 0; // counter for total number of lines in file

        // Loop through the array looking for header info.
        $ansel = false;
        $char_done = false;
        $vers_done = false;

        // open file
        $handle = @fopen($filename, "r");
        if (($handle) && ($this->procObject->status == 'new')) {
            // loop through the lines
            while (!feof($handle)) {
                $line = fgets($handle, 4096);
                $line = trim($line);
                // we are ready
                if (($char_done) and ($vers_done)) {
                    break;
                }

                // remove end-of-line characters
                $line      = rtrim($line, "\r\n");

                // split line into three parts with space as deviding character
                $elements  = explode(" ", $line, 3);
                if (!isset($elements[0])) {
                    $elements[0] = null;
                } else {
                    $elements[0] = trim($elements[0]);
                }
                if (!isset($elements[1])) {
                    $elements[1] = null;
                } else {
                    $elements[1] = trim($elements[1]);
                }
                if (!isset($elements[2])) {
                    $elements[2] = null;
                } else {
                    $elements[2] = trim($elements[2]);
                }

                // first part of line is the level;
                $level     = $elements[0];

                // process only the header: so is this the header
                if ($level == 0) {
                    if ($elements[1] == 'HEAD') {
                        $ind_header = true;
                    } else {
                        $ind_header = false;
                    }
                }

                // process only the header
                if ($ind_header == true) {
                    // see whether we have to transer ANSEL to UTF-8
                    // other character sets are left alone.
                    if ($elements[1] == 'CHAR') {
                        $char_done = true;
                        if ($elements[2] == 'ANSEL') {
                            $ansel = true;
                        }
                    }

                    // check the version of GEDCOM: is this the GEDCOM and not SOURCE?
                    if ($elements[1] == 'GEDC') {
                        $ind_get_vers = true;
                    } else {
                        $ind_get_vers = false;
                    }

                    // check version of GEDCOM
                    if (($elements[1] == 'VERS') and ($ind_get_vers)) {
                        $vers_done = true;
                        $version = substr($elements[2], 0, 3);
                        if (($version != '5.5') and ($version != '5.5.1')) {
                            $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . '<br />'.Text::_('JTGEDCOM_MESSAGE_NOV55');
                            $this->procObject->status = 'error';
                            return $this->procObject;
                        }
                    }
                } // end of if ind_header == true
            } // end of loop
        }

        // if charcter set is ANSEL
        if (($conversion == true) and ($ansel == true)) {
            $result = $this->prepareANSEL();
            if ($result) {
                $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . $result;
            }
        }

        $this->objectType = 'START';

        switch ($part) {
            case "person": 	$filterTag = 'INDI';
                break;
            case "family": 	$filterTag = 'FAM';
                // remove relations
                if (($truncate_rel_value == 1) && ($this->procObject->status == 'new')) {
                    $relation_notes  = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relationnotes');
                    $retdelete 		 = $relation_notes->truncateApp($this->procObject->id);
                    $relation_events = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relationevents');
                    $retdelete 		 = $relation_events->truncateApp($this->procObject->id);
                    $relations		 = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relations');
                    $retdelete 		 = $relations->truncateApp($this->procObject->id);
                    $relation_citations = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Citations');
                    $retdelete 		    = $relation_citations->truncateRelationCitations($this->procObject->id);
                }
                break;
            case "source": 	$filterTag = 'SOUR';
                break;
            case "repository": 	$filterTag = 'REPO';
                break;
            case "note": 	$filterTag = 'NOTE';
                break;
            case "document": 	$filterTag = 'OBJE';
                break;
            case "all": 	// same as default
            default:		$filterTag = null;
                // remove relations
                if (($truncate_rel_value == 1) && ($this->procObject->status == 'new')) {
                    $relation_notes  = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relationnotes');
                    $retdelete 		 = $relation_notes->truncateApp($this->procObject->id);
                    $relation_events = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relationevents');
                    $retdelete 		 = $relation_events->truncateApp($this->procObject->id);
                    $relations		 = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Relations');
                    $retdelete 		 = $relations->truncateApp($this->procObject->id);
                    $relation_citations = Factory::getApplication()->bootComponent('com_joaktree')->getMVCFactory()->createTable('Citations');
                    $retdelete 		    = $relation_citations->truncateRelationCitations($this->procObject->id);
                }
                break;
        }
        $indProcess = false;

        if (!$indAjax) {
            // we will not be looping back to the caller
            // instead we will go one time through the whole GedCom file
            // so status is set now to 'progress'
            $this->procObject->status = 'progress';
        }

        // Loop through the array.
        if (($handle)
            && (
                ($this->procObject->status == 'progress')
               || ($this->procObject->status == 'new')
            )
        ) {
            // move to the beginning
            fseek($handle, $this->procObject->cursor);

            // loop through the lines
            while (!feof($handle)) {
                $line = fgets($handle);
                $line = trim($line);

                $tellert++;

                // remove end-of-line characters
                $line      = rtrim($line, "\r\n");

                // if ANSEL convert the line
                if (($conversion == true) and ($ansel == true)) {
                    $line = $this->convertANSEL($line);
                }

                // remove double or trailing characters
                if ($removeChar) {
                    $remove = true;
                    while ($remove) {
                        $line = str_replace($removeChar.$removeChar, $removeChar, $line, $countReplace);
                        if ($countReplace == 0) {
                            $remove = false;
                        }
                    }
                    $line = trim($line, $removeChar);
                }

                // split line into three parts with space as deviding character
                $elements  = explode(" ", $line, 3);
                if (!isset($elements[0])) {
                    $elements[0] = null;
                } else {
                    $elements[0] = trim($elements[0]);
                }
                if (!isset($elements[1])) {
                    $elements[1] = null;
                } else {
                    $elements[1] = trim($elements[1]);
                }
                if (!isset($elements[2])) {
                    $elements[2] = null;
                } else {
                    $elements[2] = trim($elements[2]);
                }

                // first part of line is the level;
                $level     = $elements[0];

                // the level 0 lines are the main lines (=gedcom objects)
                // these lines are stored in object table
                // when level is greater than 0, it is a depending line and
                // stored in object-line table
                if ($level == 0) {
                    // level = 0: we dealing with the object

                    // first process the previous object (if existing)
                    if ($indProcess) {
                        try {
                            $this->process_object();
                        } catch (\Exception $e) {
                            if ($indAjax) {
                                $this->procObject->status = 'error';
                                $this->procObject->msg = 'line '.$this->objectType.', key '.$this->objectKey.', Error : '.$e->getMessage().'<br>Query : '.$e->getTraceAsString();
                                fclose($handle);
                                return $this->procObject;
                            }
                        }
                        if (($indAjax) && ($teller0 == $procStepSize)) {
                            $this->procObject->cursor = $fileCursor;
                            $this->procObject->status = 'progress';
                            fclose($handle);
                            return $this->procObject;
                        }
                    }

                    // set boolean to check the first objectline
                    $indFirstLine = true;

                    if ($elements[2] == null) {
                        // third element is empty for level 0 line
                        // tag is in the second element
                        // there is no value. this will be filled with value from counter object_value_id
                        $this->objectType = $elements[1];
                        $this->objectKey  = null;
                    } else {
                        // third element is not empty. this countains the tag
                        // second element contains the value
                        // value has to be stripped from @ characters

                        // However third element may also contain a value ... it is weird but true
                        $subelems  = explode(" ", $elements[2], 2);
                        if (!isset($subelems[0])) {
                            $subelems[0] = null;
                        } else {
                            $subelems[0] = trim($subelems[0]);
                        }
                        if (!isset($subelems[1])) {
                            $subelems[1] = null;
                        } else {
                            $subelems[1] = trim($subelems[1]);
                        }

                        if ($subelems[1] == null) {
                            // everything is ok and normal
                            $this->objectType		= $elements[2];
                            $this->objectKey		= ltrim(rtrim($elements[1], '@'), '@');
                        } else {
                            // extra value found in the line!!
                            $this->objectType		= $subelems[0];
                            $this->objectKey		= ltrim(rtrim($elements[1], '@'), '@');

                            // this is really a new tag.
                            $objectLine['object_id'] 	=  $this->objectKey;
                            $objectLine['level'] 		=  1;
                            $objectLine['tag'] 			=  "TEXT";
                            $objectLine['value'] 		=  $subelems[1];

                            // keep the object line
                            $this->objectLines[] = $objectLine;
                            $indFirstLine = false;
                        }

                    }

                    $teller0++;

                    if ($filterTag) {

                        if ($filterTag == $this->objectType) {
                            $indProcess = true;
                        } else {
                            $indProcess = false;
                        }
                    } else {
                        $indProcess = true;
                    }

                } else {
                    if ($indProcess) {
                        // level <> 0: we dealing with object lines
                        // element 2 contains the tag; element 3 contains the value
                        $tag = $elements[1];
                        $value =  $elements[2];

                        // replace special characters in value
                        // & (= ampersand)
                        if ($value) {
                            $value = str_replace("&", "&#38;", $value);
                            // < (= less than sign)
                            $value = str_replace("<", "&#60;", $value);
                            // > (= greater than sign)
                            $value = str_replace(">", "&#62;", $value);
                            // ' (= single quote)
                        }
                        //$value = str_replace("'", "&#39;", $value);
                        //$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

                        // Deal with the first line if it is not a real tag
                        if ($indFirstLine) {
                            // Aldfaer uses for notes as first line CONC.
                            // This should be text, and here we fix it.
                            if ($tag == 'CONC' or $tag == 'CONT') {
                                $tag = 'TEXT';
                            }

                            $indFirstLine = false;
                        }

                        if ($tag == 'CONC' or $tag == 'CONT') {
                            // this line is a continutation of the previouse line
                            $objectLine = array_pop($this->objectLines);

                            if ($tag == 'CONC') {
                                $objectLine['value'] = $objectLine['value'] . $value;
                            } elseif ($tag == 'CONT') {
                                $objectLine['value'] = $objectLine['value'] . '&#10;&#13;' . $value;
                            }

                            // keep the object line
                            $this->objectLines[] = $objectLine;

                        } else {
                            // this is really a new tag.
                            $objectLine['object_id'] 	=  $this->objectKey;
                            $objectLine['level'] 		=  $level;
                            $objectLine['tag'] 			=  $tag;
                            $objectLine['value'] 		=  $value;

                            // keep the object line
                            $this->objectLines[] = $objectLine;
                        }
                    }
                } // end of check on level = 0

                $fileCursor = ftell($handle);
            } // end of loop through array

        }
        if ($handle) {
            fclose($handle);
        }
        // if the number of objects is smaller than the step size
        // status never reached 'progress'. It is still 'new'.
        if (($indAjax) && ($this->procObject->status == 'new') && ($teller0 < $procStepSize)) {
            $this->procObject->cursor = $fileCursor;
            $this->procObject->status = 'progress';
            return $this->procObject;
        }

        $this->procObject->cursor = isset($fileCursor) ? $fileCursor : 0;
        if ($this->procObject->status == 'progress') {
            // status is progress: this means our last action was reading the file
            // we are done with that for now - time for a new status
            $this->procObject->status = 'endload';
            if ($indAjax) {
                // we are looping back to the caller
                return $this->procObject;
            }
        }

        if (($patronymSetting != 0) && ($patronymSetting != 1) && ($this->procObject->status == 'endload')) {
            // we are not setting the patronyms - just update the status to the next
            $this->procObject->status = 'endpat';
            if ($indAjax) {
                // we are looping back to the caller
                return $this->procObject;
            }
        }

        // if setting is NOT to retrieve patronyms from name string, patronyms have to determined now
        if ((($patronymSetting == 0) || ($patronymSetting == 1)) && ($this->procObject->status == 'endload')) {
            $ret = Names::setPatronyms($this->procObject->id);

            if (!$ret) {
                // if no result - an error occured and we stop
                $this->procObject->status = 'error';
                $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . Text::_('JTGEDCOM_MESSAGE_NOSUCPATRONYMS') ;
                return $this->procObject;
            } else {
                // we are done - so change the status
                $this->procObject->status = 'endpat';
                if ($indAjax) {
                    // we are looping back to the caller
                    $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . Text::_('JTGEDCOM_MESSAGE_SUCPATRONYMS') ;
                    return $this->procObject;
                }
            }
        }

        // Set the indicators for different types of relationships after processing all persons
        if ($this->procObject->status == 'endpat') {
            $ret = Relations::setRelationIndicators($this->procObject->id);

            // if no result - an error occured and we stop
            if (!$ret) {
                $this->procObject->status = 'error';
                $this->procObject->msg =  (isset($this->procObject->msg) ? $this->procObject->msg : '') . Text::_('JTGEDCOM_MESSAGE_NOSUCRELINDICATORS') ;
                return $this->procObject;
            } else {
                $this->procObject->status = 'endrel';
                if ($indAjax) {
                    // we are looping back to the caller
                    $this->procObject->msg = (isset($this->procObject->msg) ? $this->procObject->msg : '') . Text::_('JTGEDCOM_MESSAGE_SUCRELINDICATORS') ;
                    return $this->procObject;
                }
            }
        }

        return $this->procObject;
    }

    /*
    ** Main function to clear the gedcom file from tables
    */
    public static function clear_gedcom()
    {
        $db              = Factory::getContainer()->get(DatabaseInterface::class);
        $ret = true;

        $query = 'TRUNCATE #__joaktree_gedcom_objectlines';
        if ($ret) {
            $ret = $db->setQuery($query);
        }
        if ($ret) {
            $ret = $db->execute();
        } //$db->query(); }

        $query = 'TRUNCATE #__joaktree_gedcom_objects';
        if ($ret) {
            $ret = $db->setQuery($query);
        }
        if ($ret) {
            $ret = $db->execute();
        } //$db->query(); }}

        if (!$ret) {
            $msg = '+'.Text::_('JTGEDCOM_MESSAGE_NOSUCCLRTABLES') ;
        } else {
            $msg = false;
        }

        return $msg;
    }

    /*
    ** Main function to delete data from the database
    */
    public static function deleteGedcomData($app_id, $indAdminTable)
    {
        $app_id			= (int) $app_id;
        $db             = Factory::getContainer()->get(DatabaseInterface::class);
        $deleteTable 	= array();

        if ($indAdminTable) {
            $deleteTable[] = '#__joaktree_admin_persons';
            $deleteTable[] = '#__joaktree_maps';
        }

        $deleteTable[] = '#__joaktree_citations';
        $deleteTable[] = '#__joaktree_documents';
        $deleteTable[] = '#__joaktree_logremovals';
        $deleteTable[] = '#__joaktree_logs';
        $deleteTable[] = '#__joaktree_notes';
        $deleteTable[] = '#__joaktree_persons';
        $deleteTable[] = '#__joaktree_person_documents';
        $deleteTable[] = '#__joaktree_person_events';
        $deleteTable[] = '#__joaktree_person_names';
        $deleteTable[] = '#__joaktree_person_notes';
        $deleteTable[] = '#__joaktree_relations';
        $deleteTable[] = '#__joaktree_relation_events';
        $deleteTable[] = '#__joaktree_relation_notes';
        $deleteTable[] = '#__joaktree_repositories';
        $deleteTable[] = '#__joaktree_sources';
        $deleteTable[] = '#__joaktree_tree_persons';

        foreach ($deleteTable as $query_num => $table) {
            $query = 'DELETE FROM '.$table.' WHERE app_id = '.$app_id.' ';
            $db->setQuery($query);
            $db->execute();
        }
        // delete Smart Search indexes
        PluginHelper::importPlugin('finder');
        $obj = new \StdClass();
        $obj->app_id = $app_id;
        Factory::getApplication()->triggerEvent('onFinderAfterDelete', array('com_joaktree.person', $obj ,true));

        $msg = text::_('JTGEDCOM_MESSAGE_DELETEGEDCOM');

        return $msg;
    }

}
