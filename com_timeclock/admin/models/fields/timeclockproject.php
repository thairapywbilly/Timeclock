<?php
/**
 * This component is the user interface for the endpoints
 *
 * PHP Version 5
 *
 * <pre>
 * com_ComTimeclock is a Joomla! 1.6 component
 * Copyright (C) 2014 Hunt Utilities Group, LLC
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
 * @copyright  2014 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    GIT: $Id: be7f01d2715c9d55a9b71265a6e75d2a894aa0a1 $
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */

defined('_JEXEC') or die('Restricted access');
jimport('joomla.form.formfield');
/**
 * This creates a select box with the user types in it.
 *
 * @category   UI
 * @package    ComTimeclock
 * @subpackage Com_Timeclock
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2014 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */

class JFormFieldTimeclockProject extends JFormField
{
    protected $type = 'TimeclockProject';

    /**
    * Method to get the field options.
    *
    * @return      array   The field option objects.
    */
    public function getInput()
    {
        $model = TimeclockHelpersTimeclock::getModel("project");
        $list = $model->listItems(
            array("p.published=1", "p.type <> 'HOLIDAY'", "p.type <> 'CATEGORY'"),
            "p.name ASC", null, false
        );
        foreach ($list as $item) {
            $options[] = JHTML::_(
                'select.option', 
                (int)$item->project_id, 
                JText::_($item->name)
            );
        }
        $attrib = array();
        if (isset($this->class)) {
            $attrib['class'] = $this->class;
        }
        if (isset($this->onchange)) {
            $attrib['onchange'] = $this->onchange;
        }
        return JHTML::_(
            'select.genericlist',
            $options,
            $this->name,
            $attrib,
            'value',
            'text',
            $this->value,
            $this->id
        );
    }
}
