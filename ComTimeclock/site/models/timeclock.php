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

jimport('joomla.application.component.model');

/** Include the project stuff */
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'models'.DS.'projects.php';
require_once JPATH_COMPONENT_SITE.DS.'tables'.DS.'timeclocktimesheet.php';

/**
 * ComTimeclock model
 *
 * @category   UI
 * @package    ComTimeclock
 * @subpackage Com_Timeclock
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2008 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */
class TimeclockModelTimeclock extends JModel
{
    /**
     * Constructor that retrieves the ID from the request
     *
     * @return    void
     */
    function __construct()
    {
        parent::__construct();

        $others = TableTimeclockPrefs::getPref("admin_otherTimesheets");
        if ($others) $cid = JRequest::getVar('cid', 0, '', 'array');
        if (empty($cid)) {
            $u =& JFactory::getUser();
            $cid = $u->get("id");
        }
        $this->setId($cid);

        $date = JRequest::getVar('date', 0, '', 'string');
        $this->setDate($date);

        $project = JRequest::getVar('projid', 0, '', 'string');
        $this->setProject($project);
    }
    /**
     * Method to set the id
     *
     * @param int $id The ID of the Project to get
     *
     * @return    void
     */
    function setId($id)
    {
        if (is_array($id)) {
            $this->_id = (int)$id[0];
        } else {
            $this->_id = (int)$id;
        }
    }
    /**
     * Method to set the id
     *
     * @param string $date  The date to set
     * @param string $field The field to set
     *
     * @return    void
     */
    function setDate($date, $field="_date")
    {
        $this->$field = TimeClockController::fixDate($date);
        if (empty($this->$field)) $this->$field = date("Y-m-d");
    }

    /**
     * Method to set the id
     *
     * @param int $project The project to set
     *
     * @return    void
     */
    function setProject($project)
    {
        $project = (int) $project;
        if (empty($project)) {
            $this->_project = null;
        } else {
            $this->_project = $project;
        }
    }
    /**
     * Gets this hugely complex SQL query
     *
     * @param string $where1 The where clause to add. Must NOT include "WHERE"
     * @param string $where2 The where clause to add. Must NOT include "WHERE"
     *
     * @return string
     */ 
    protected function sqlQuery($where1, $where2=null)
    {
        if (is_null($where2)) $where2 = $where1;
        return "SELECT t.hours as hours, t.worked, t.project_id, t.notes,
                      j.user_id as user_id, p.name as project_name, p.type as type, 
                      u.name as author, pc.name as category_name, c.company as company_name,
                      c.name as contact_name
                      FROM #__timeclock_timesheet as t
                      LEFT JOIN #__timeclock_projects as p on t.project_id = p.id
                      RIGHT JOIN #__timeclock_users as j on j.id = p.id
                      LEFT JOIN #__users as u on j.user_id = u.id
                      LEFT JOIN #__timeclock_prefs as tp on tp.id = u.id
                      LEFT JOIN #__timeclock_projects as pc on p.parent_id = pc.id
                      LEFT JOIN #__timeclock_customers as c on p.customer = c.id
                      WHERE 
                          (".$where1." AND (p.type = 'PROJECT' OR p.type = 'PTO') AND t.created_by = j.user_id)
                          OR
                          (".$where2." AND p.type = 'HOLIDAY'
                          AND ((t.worked >= tp.startDate) AND ((t.worked <= tp.endDate) OR (tp.endDate = '0000-00-00'))))
                      ";
    }


    /**
     * Method to display the view
     *
     * @return string
     */
    function getTimesheetData()
    {
        if (empty($this->data)) {
            $where = array(
                "j.user_id = ".$this->_id,
                $this->employmentDateWhere("t.worked"),
                $this->periodWhere("t.worked"),
            );
            $where = implode(" AND ", $where);
            $query = $this->sqlQuery($where);
            $ret = $this->_getList($query);
            if (!is_array($ret)) return array();
            $this->data = array();
            foreach ($ret as $d) {
                $hours = ($d->type == "HOLIDAY") ? $d->hours * $this->getHolidayPerc($id, $d->worked) : $d->hours;
                $this->data[$d->project_id][$d->worked]['hours'] += $hours;
                $this->data[$d->project_id][$d->worked]['notes'] .= $d->notes;
            }
        }
        return $this->data;
    }


    /**
     * Gets the perc of holiday pay this user should get
     *
     * @param int    $id   The user id to check
     * @param string $date The date to check
     *
     * @return int
     */
    function getHolidayPerc($id, $date)
    {
        static $perc;
        $key = $id.$date;
        if (!isset($perc[$key])) {
            $hist = TableTimeclockPrefs::getPref("history", "user", $id);
            if (is_array($hist["admin_holidayperc"])) {
                ksort($hist["admin_holidayperc"]);
                foreach ($hist["admin_holidayperc"] as $d => $h) {
                    if (TimeclockController::compareDates($date, $d) < 0) {
                        $perc[$key] = $h/100;
                        break;
                    }
                }
            }
            if (!isset($perc[$key])) $perc[$key] = TableTimeclockPrefs::getPref("admin_holidayperc", "user", $id) / 100;
        }
        return $perc[$key];
    }
    
    /**
     * Where statement for employment dates
     *
     * @param string $field The field to use
     *
     * @return string
     */
    function employmentDateWhere($field)
    {
        $dates = self::getEmploymentDates();
        $ret = "($field >= '".$dates["start"]."'";
        
        if (($dates["end"] != '0000-00-00') && !empty($dates["end"])) $ret .= " AND $field <= '".$dates["end"]."'";

        $ret .= ")";
        return $ret;    
    }

    /**
     * Where statement for employment dates
     *
     * @return array
     */
    function getEmploymentDates()
    {
        static $eDates;
        if (empty($eDates)) {
            $eDates = array(
                "start" => TimeclockController::fixDate(TableTimeclockPrefs::getPref("startDate")),
                "end"   => TimeclockController::fixDate(TableTimeclockPrefs::getPref("endDate")),
            );
        }
        return $eDates;
    }

    /**
     * Where statement for employment dates
     *
     * @return array
     */
    function getEmploymentDatesUnix()
    {
        static $eDatesUnix;
        if (empty($eDatesUnix)) {
            $eDatesUnix = self::getEmploymentDates();
            foreach ($eDatesUnix as $key => $val) {
                $eDatesUnix[$key] = TimeclockController::dateUnixSql($val);
            }
        }
        return $eDatesUnix;    
    }

    /**
     * Where statement for the reporting period dates
     *
     * @param string $field The field to use
     *
     * @return string
     */
    function periodWhere($field)
    {
        $period = $this->getPeriod();
        $start = $period["start"];
        $end = $period["end"];
        $ret = "($field >= '$start' AND $field <= '$end')";

        return $ret;    
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s").  If left blank
     *                      the date read from the request variables is used.
     *
     * @return array
     */ 
    function getPeriod($date=null)
    {
        // This should be the last one.
        return self::_getPeriodFixed($date);
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param int $date The date in Mysql ("Y-m-d") format.
     *
     * @return array
     */ 
    private function _getPeriodFixed($date)
    {
        static $periods;

        $date = self::getDate($date);

        $start = self::_getPeriodFixedStart($date);        
        $return =& $periods[$start];
        if (!isset($return)) {
            $start = TimeclockController::explodeDate($start);
            
            $periodLength = TableTimeclockPrefs::getPref("payPeriodLengthFixed", "system");
    
            $y = $start["y"];
            $m = $start["m"];
            $d = $start["d"];
    
            // These are all of the dates in the pay period
            for ($i = 0; $i < $periodLength; $i++) {
                $return['dates'][self::_date($m, $d+$i, $y)] = TimeclockController::dateUnix($m, $d+$i, $y);
            }
    
            // Get the start and end
            $return['unix']['start']        = TimeclockController::dateUnix($m, $d, $y);
            $return['unix']['end']          = TimeclockController::dateUnix($m, $d+$periodLength-1, $y);
            $return['unix']['prev']         = TimeclockController::dateUnix($m, $d-$periodLength, $y);
            $return['unix']['prevend']      = TimeclockController::dateUnix($m, $d-1, $y);
            $return['unix']['next']         = TimeclockController::dateUnix($m, $d+$periodLength, $y);
            $return['unix']['nextend']      = TimeclockController::dateUnix($m, $d+(2*$periodLength), $y);
            $return['start']        = self::_date($return['unix']['start']);
            $return['end']          = self::_date($return['unix']['end']);
            $return['prev']         = self::_date($return['unix']['prev']);
            $return['prevend']      = self::_date($return['unix']['prevend']);
            $return['next']         = self::_date($return['unix']['next']);
            $return['nextend']      = self::_date($return['unix']['nextend']);

            $return['length']       = $periodLength;
        }    
        return $return;
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param int $date The date in Mysql ("Y-m-d") format.
     *
     * @return array
     */ 
    private function _getPeriodFixedStart($date)
    {
        // Get this date
        $uDate = TimeclockController::dateUnixSql($date);
        $d = TimeclockController::explodeDate($date);

        // Get the pay period start
        $startTime = TableTimeclockPrefs::getPref("firstPayPeriodStart", "system");
        $start = TimeclockController::dateUnixSql($startTime);

        // Get the length in days
        $len = TableTimeclockPrefs::getPref("payPeriodLengthFixed", "system");

        // Get the time difference in days
        $timeDiff = round(($uDate - $start) / 86400);
        $days = $timeDiff % $len;

        return self::_date($d["m"], ($d["d"] - $days), $d["y"]);
    }

    /**
     * Where statement for the reporting period dates
     *
     * @param int $m The month or the unix date if $d and $y are null
     * @param int $d The day
     * @param int $y The year
     *
     * @return array
     */ 
    private function _date($m, $d=null, $y=null)
    {
        if (!(is_null($d) && is_null($y))) $m = TimeclockController::dateUnix($m, $d, $y);
        return date("Y-m-d", $m);
    }

    /**
     * Where statement for the reporting period dates
     *
     * @param string $date  Date to use in MySQL format ("Y-m-d")
     * @param string $field The field to set
     *
     * @return array
     */ 
    function getDate($date=null, $field="_date")
    {
        $date = TimeclockController::fixDate($date);
        if (!empty($date)) return $date;        
        if (is_object($this)) return $this->$field;
        return date("Y-m-d");
    }

    /**
     * Method to display the view
     *
     * @return string
     */
    function getData()
    {
        $query = "SELECT t.*
                  FROM #__timeclock_timesheet as t
                  WHERE t.worked ='".$this->_date."'
                     AND t.created_by = '".$this->_id."'
                  ";

        $ret = $this->_getList($query);
        if (!is_array($ret)) return array();
        $data = array();
        foreach ($ret as $d) {
            $data[$d->project_id] = $d;
        }
        return $data;
    }


    /**
     * Checks in an item
     *
     * @return bool
     */
    function store()
    {
        $row = $this->getTable("TimeclockTimesheet");
        $timesheet = JRequest::getVar('timesheet', array(), '', 'array');
        $date = JRequest::getVar('date', '', '', 'string');
        $user =& JFactory::getUser();        
        if (empty($date)) return false;
        
        $ret = true;
        foreach ($timesheet as $data) {
            $data["hours"] = (int) $data["hours"];
            // If there are no hours don't create a record.
            // If there is already a record allow 0 hours.
            if (empty($data["hours"]) && empty($data["id"])) continue; 

            // Remove white space from the notes
            $data["notes"] = trim($data["notes"]);
            $data["id"] = (int) $data["id"];
            $data["created_by"] = $user->get("id");
            $data["worked"] = $date;
            if (empty($data["created"])) $data["created"] = date("Y-m-d H:i:s");

            if (!$row->bind($data)) {
                $this->setError($this->_db->getErrorMsg());
                $ret = false;
                continue;
            }
            // Make sure the record is valid
            if (!$row->check()) {
                $this->setError($this->_db->getErrorMsg());
                $ret = false;
                continue;
            }
        
            // Store the web link table to the database
            if (!$row->store()) {
                $this->setError($this->_db->getErrorMsg());
                $ret = false;
                continue;
            }
        }
        return $ret;
    }

}

?>
