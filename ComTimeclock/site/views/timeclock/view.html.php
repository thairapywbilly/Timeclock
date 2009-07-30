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

jimport('joomla.application.component.view');

/**
 * HTML View class for the ComTimeclockWorld Component
 *
 * @category   UI
 * @package    ComTimeclock
 * @subpackage Com_Timeclock
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2008 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */

class TimeclockViewTimeclock extends JView
{
    /**
     * The display function
     *
     * @param string $tpl The template to use
     *
     * @return null
     */
    function display($tpl = null)
    {
        global $mainframe;

        $this->_getCookies();

        $layout          = JRequest::getVar('layout');
        $model           =& $this->getModel();
        $user            = JFactory::getUser();
        $user_id         = $user->get("id");
        $employmentDates = $model->getEmploymentDatesUnix();
        $date            = $model->get("date");
        $this->_params   =& $mainframe->getParams('com_timeclock');
        $this->_getProjects($user_id);
        $this->assignRef("employmentDates", $employmentDates);
        $this->assignRef("user", $user);
        $this->assignRef("date", $date);

        if (method_exists($this, $layout)) {
            $this->$layout($tpl);
        } else {
            $this->timesheet($tpl);
        }

    }


    /**
     * The display function
     *
     * @param string $tpl The template to use
     *
     * @return null
     */
    function timesheet($tpl = null)
    {
        global $mainframe;

        $model   =& $this->getModel();
        $period  = $model->getPeriodDates();
        $this->_timesheetData();

        if (!is_object($this->_params)) {
            $this->_params =& $mainframe->getParams('com_timeclock');
        }
        $today_color      = $this->_params->get("today_color");
        $today_background = $this->_params->get("today_background");

        $this->assignRef("today_color", $today_color);
        $this->assignRef("today_background", $today_background);
        $this->assignRef("period", $period);

        parent::display($tpl);
    }

    /**
     * The display function
     *
     * @param string $tpl The template to use
     *
     * @return null
     */
    function addhours($tpl = null)
    {

        $model    =& $this->getModel();
        $data     = $model->getData();
        $referer  = JRequest::getVar(
            'referer',
            $_SERVER["HTTP_REFERER"],
            '',
            'string'
        );
        $projid   = JRequest::getVar('projid', null, '', 'string');

        $maxHours = TableTimeclockPrefs::getPref("maxDailyHours", "system");
        $decimalPlaces = TableTimeclockPrefs::getPref("decimalPlaces", "system");

        $this->assignRef("projid", $projid);
        $this->assignRef("referer", $referer);
        $this->assignRef("data", $data);
        $this->assignRef("maxHours", $maxHours);
        $this->assignRef("decimalPlaces", $decimalPlaces);

        JHTML::_('behavior.tooltip');
        JHTML::_('behavior.formvalidation');

        parent::display($tpl);
    }

    /**
     * Checks employment dates and says if the user can enter hours on that date
     *
     * @param int $date The unix date to check
     *
     * @return bool
     */
    function checkDate($date)
    {
        return TimeclockController::checkEmploymentDates(
            $this->employmentDates["start"],
            $this->employmentDates["end"],
            $date
        );
    }

    /**
     * Get timesheet data
     *
     * @param int $user_id The ID of the user to get projects for
     *
     * @return null
     */
    private function _getProjects($user_id)
    {
        $projModel =& JModel::getInstance("Projects", "TimeclockAdminModel");
        $projects  = $projModel->getUserProjects($user_id);
        $cats      = TableTimeclockPrefs::getPref("Timeclock_Category");
        foreach ($projects as $k => $p) {
            if (isset($cats[$p->id])) {
                $projects[$k]->show = false;
            } else {
                $projects[$k]->show = true;
            }
        }

        $this->assignRef("projects", $projects);
    }
    /**
     * The display function
     *
     * @return null
     */
    function _getCookies()
    {
        $set = JRequest::getVar('Timeclock_Set', null, '', 'string', "COOKIE");
        if (!is_array($_COOKIE) || is_null($set)) {
            return;
        }
        $cookie = array();
        foreach ($_COOKIE as $name => $value) {
            if (strtolower(substr(trim($name),0,18)) == "timeclock_category") {
                if (trim(strtolower($value)) == "closed") {
                    $key = (int)substr(trim($name), 18);
                    $cookie[$key] = "closed";
                }
            }
        }
        TableTimeclockPrefs::setPref("Timeclock_Category", $cookie);
    }
    /**
     * Get timesheet data
     *
     * @return null
     */
    private function _timesheetData()
    {
        $model =& $this->getModel();
        $data  = $model->getTimesheetData();
        $hours = array();
        foreach ($data as $k => $d) {
            $hours[$d->project_id][$d->worked]['hours'] += $d->hours;
            $hours[$d->project_id][$d->worked]['notes'] .= $d->notes;
            $totals["proj"][$d->project_id]             += $d->hours;
            $totals["worked"][$d->worked]               += $d->hours;
            $totals["total"]                            += $d->hours;
        }
        $this->assignRef("hours", $hours);
        $this->assignRef("totals", $totals);
    }
}

?>