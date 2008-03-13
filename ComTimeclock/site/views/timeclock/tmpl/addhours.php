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

JHTML::_('behavior.tooltip'); 

$headerColSpan = 3;
$this->totals     = array();
if (empty($this->days)) $this->days = 7;

$headerColSpan    = ($this->period["length"]+2+($this->period["length"]/$this->days));

$document        =& JFactory::getDocument();
$document->setTitle("Add Hours for ".$this->user->get("name")." on ".JHTML::_('date', $this->date, $shortDateFormat));

?>
<script type="text/javascript">
        Window.onDomReady(function(){
            document.formvalidator.setHandler('dateverify', 
                function (value) {
                    regex=/[1-9][0-9]{3}-[0-1]{0,1}[0-9]-[0-3]{0,1}[0-9]/;
                    return regex.test(value); 
                }     
            );
        });
</script>

<form action="index.php" method="post" name="userform" autocomplete="off" class="form-validate">
    <div class="componentheading">Add Hours</div>
    <table cellpadding="5" cellspacing="0" border="0" width="100%">
        <tr>
            <th align="right">
                <label id="date_label" for="date">
                    *<?php print JText::_("Date"); ?>:
                </label>
            </th>
            <td>
                <?php print JHTML::_("calendar", $this->date, "date", "date", "%Y-%m-%d", 'class="inputbox validate-dateverify required date_label"');?>
            </td>
            <td>
                <?php print JText::_("Required.  The date worked.  Should be of the form yyyy-mm-dd"); ?>
            </td>
        </tr>
<?php
foreach ($this->projects as $cat) {
    if (($cat->mine == false) || !$cat->published) continue;
    if (!is_null($this->projid) && !array_key_exists($this->projid, $cat->subprojects)) continue;
    ?>
        <tr>
            <td class="sectiontableheader" colspan="<?php print $headerColSpan; ?>">
                <h2><?php print JText::_("Category").": ".JText::_($cat->name); ?></h2>
            </td>
        </tr>    
    <?php
    foreach ($cat->subprojects as $pKey => $proj) {
        if ($proj->mine == false) continue;
        if (!$proj->published) continue;
        if ($proj->noHours) continue;
        if (!is_null($this->projid) && !($this->projid == $proj->id)) continue;
        $hours = ($this->data[$proj->id]->hours) ? $this->data[$proj->id]->hours : 0;
        ?>
        <tr>
            <td class="sectiontableheader" colspan="<?php print $headerColSpan; ?>">
<script>
        Window.onDomReady(function(){
            document.formvalidator.setHandler('noteverify<?php print $proj->id;?>',
                function (value) {
                    if (document.getElementById('timesheet_<?php print $proj->id;?>_hours').value > 0) {
                        return (value.length > 10);
                    } else {
                        return true;
                    } 
                }     
            );
        });
</script>

                <?php print JText::_("Project").": ".TimeclockController::formatProjId($proj->id)." ".JText::_($proj->name); ?>
            </td>
        </tr>    
        <tr>
            <th align="right">
                <label id="hours_<?php print $proj->id;?>_label" for="timesheet_<?php print $proj->id;?>_hours">
                    <?php print JText::_("Hours");?>:
                </label>
            </th>
            <td>
                <input class="inputbox validate-numeric" type="text" id="timesheet_<?php print $proj->id;?>_hours" name="timesheet[<?php print $proj->id;?>][hours]" size="10" maxlength="10" value="<?php echo $hours;?>" />
                <input type="hidden" id="timesheet_<?php print $proj->id;?>_id" name="timesheet[<?php print $proj->id;?>][id]" value="<?php echo $this->data[$proj->id]->id;?>" />
                <input type="hidden" id="timesheet_<?php print $proj->id;?>_created" name="timesheet[<?php print $proj->id;?>][created]" value="<?php echo $this->data[$proj->id]->created;?>" />
                <input type="hidden" id="timesheet_<?php print $proj->id;?>_project_id" name="timesheet[<?php print $proj->id;?>][project_id]" value="<?php echo $proj->id;?>" />
            </td>
            <td>
                <?php print JText::_("The number of hours worked.  Must be numeric."); ?>            
            </td>
        </tr>
        <tr>
            <th style="vertical-align: top;"  align="right" id="notes_<?php print $proj->id;?>_label">
                <label id="notes_<?php print $proj->id;?>_label" for="timesheet_<?php print $proj->id;?>_notes">
                    <?php echo JText::_('Notes'); ?>:
                </label>
            </th>
            <td>
                <textarea class="inputbox validate-noteverify<?php print $proj->id;?>"  id="timesheet_<?php print $proj->id;?>_notes" name="timesheet[<?php print $proj->id;?>][notes]" cols="50" rows="5"> <?php echo $this->data[$proj->id]->notes;?> </textarea>
            </td>
            <td>
                <?php print JText::_("This should be a description of what was done in the hours posted.  Minimum 10 characters."); ?>            
            </td>
        </tr>
        <tr>
            <th style="vertical-align: top;">
                 &nbsp;
            </th>
            <td>
                <button type="submit" name="task" value="applyhours" class="button validate"><?php print JText::_("Apply"); ?></button>
                <button type="submit" name="task" value="savehours" class="button validate"><?php print JText::_("Save"); ?></button>
            </td>
        </tr>    

        <?php        
    }
}



?>
    </table>
    <input type="hidden" name="referer" value="<?php print $this->referer; ?>" />
    <input type="hidden" name="option" value="com_timeclock" />
<!--    <input type="hidden" name="task" value="savehours" />-->
    <?php print JHTML::_("form.token"); ?>
</form>
<div>
    <a name="required_field" />
* <?php print JText::_("Required field"); ?>
</div>