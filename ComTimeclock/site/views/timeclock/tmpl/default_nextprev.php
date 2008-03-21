<?php
/**
 * This component is the user interface for the endpoints
 *
 * PHP Version 5
 *
 * <pre>
 * com_ComTimeclock is a Joomla! 1.5 component
 * Copyright (C) 2008 Hunt Utilities Group, LLC
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, 
 * MA  02110-1301, USA.
 * </pre>
 *
 * @category   UI
 * @package    ComTimeclock
 * @subpackage Com_Timeclock
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2008 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    SVN: $Id$    
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */

defined('_JEXEC') or die('Restricted access'); 

$tip = "Go to the next pay period";
$img = "components".DS."com_timeclock".DS."images".DS."1rightarrow.png";
$text = '<img src="'.$img.'" alt="&gt;" style="border: none;" />';
$url = JROUTE::_("index.php?option=com_timeclock&view=timeclock&date=".$this->period["next"]);
$nextImg = '<a href="'.$url.'">'.$text.'</a>';
$next = '<a href="'.$url.'">'.JText::_("Next").'</a>';

$tip = "Go to the previous pay period";
$img = "components".DS."com_timeclock".DS."images".DS."1leftarrow.png";
$text = '<img src="'.$img.'" alt="&lt;" style="border: none;" />';
$url = JROUTE::_("index.php?option=com_timeclock&view=timeclock&date=".$this->period["prev"]);
$prevImg = '<a href="'.$url.'">'.$text.'</a>';
$prev = '<a href="'.$url.'">'.JText::_("Previous").'</a>';

$text = JText::_('Today');
$url = JROUTE::_("index.php?option=com_timeclock&view=timeclock");
$today = '<a href="'.$url.'">'.$text.'</a>';

?>
<table width="100%" id="nextprev">
    <tr>
        <td width="5px" align="left"><?php print $prevImg; ?></td>
        <td width="20%" align="left" style="vertical-align: middle;"><?php print $prev; ?></td>

        <td align="center" style="white-space: nowrap;">
            <?php print $today; ?>
        </td>
        <td width="20%" align="right" style="vertical-align: middle;"><?php print $next; ?></td>
        <td width="5px;" align="right"><?php print $nextImg; ?></td>
    </tr>
</table>
