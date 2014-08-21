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
 * @version    SVN: $Id$
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/** Include the project stuff */
$base      = JPATH_SITE."/components/com_timeclock";
$adminbase = JPATH_ADMINISTRATOR."/components/com_timeclock";

require_once $adminbase.'/models/users.php';
require_once $adminbase.'/models/projects.php';
require_once $adminbase.'/models/customers.php';
require_once $base.'/tables/timeclocktimesheet.php';
require_once $base.'/controller.php';

/**
 * ComTimeclock model
 *
 * Dates are set by either the parameter "date" and a period type (i.e. "month")
 * or by two date parameters ("startDate" and "endDate").  This should be sent
 * on the URL.
 *
 * @category   UI
 * @package    ComTimeclock
 * @subpackage Com_Timeclock
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2014 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */
class TimeclockModelTimeclock extends JModelLegacy
{

    /** @var string The type of period */
    protected $periods = array(
        "month" => array(
            "start" => "Y-m-01",
            "end" => "Y-m-t",
        ),
        "year" => array(
            "start" => "Y-01-01",
            "end" => "Y-12-31",
        ),
        "day" => array(
            "start" => "Y-m-d",
            "end" => "Y-m-d",
        ),
        "default" => array(
            "start" => "Y-m-01",
            "end" => "Y-m-t",
        ),
    );
    /** @var string The start date in MySQL format */
    protected $period = array(
        "type" => null,
    );
    /** @var string The start date in MySQL format */
    protected $weekStart = 0;

    /** @var int The project we are dealing with */
    private $_project = 0;

    /**
     * Constructor that retrieves the ID from the request
     *
     * @return    void
     */
    function __construct()
    {
        parent::__construct();

        $type = $this->get("type");
        $this->setPeriodType($type);

        $firstWeekDay = TimeclockHelper::getParam("firstWeekDay");
        if (!empty($firstWeekDay)) {
            $this->weekStart = $firstWeekDay;
        }

        $others = TimeclockHelper::getUserParam("otherTimesheets");
        if ($others) {
            $cid = JRequest::getVar('cid', 0, '', 'array');
        }
        if (empty($cid)) {
            $u = JFactory::getUser();
            $cid = $u->get("id");
        }
        $this->setId($cid);
    }
    /**
     * Method to set the id
     *
     * @param int $id The ID of the Project to get
     *
     * @return    void
     */
    public function setPeriodType($type)
    {
        if (empty($type)) {
            $type = TimeclockHelper::getParam("timesheetView");
            if (empty($type)) {
                $type = "payperiod";
            }
        }
        $this->set($type, "type");

        $date = JRequest::getString('date', date("Y-m-d"));
        $this->setDate(self::fixDate($date), "date", true);
        $startDate = JRequest::getString('startDate');
        $this->setPeriodDate($startDate, "start");
        $endDate = JRequest::getString('endDate');
        $this->setPeriodDate($endDate, "end");

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
     * Get the type of period
     *
     * @param string $data  to store
     * @param string $field the field to store it into
     *
     * @return string
     */
    function set($data, $field = NULL)
    {
        return $this->period[$field] = $data;
    }
    /**
     * Get the type of period
     *
     * @param string $data  to store
     * @param string $field the field to store it into
     *
     * @return string
     */
    function setUnix($data, $field)
    {
        return $this->period["unix"][$field] = $data;
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $field   The field to set
     * @param mixed  $default The default to use
     *
     * @return array
     */
    function get($field, $default = NULL)
    {
        return isset($this->period[$field]) ? $this->period[$field] : null;
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $field The field to set
     *
     * @return array
     */
    function getUnix($field)
    {
        return $this->period["unix"][$field];
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $date  Date to use if it is set in MySQL format ("Y-m-d")
     * @param string $field The field to save the date in
     * @param bool   $force Make it return a valid date no matter what
     *
     * @return null
     */
    function setDate($date, $field, $force=false)
    {
        $date = self::fixDate($date);
        if (empty($date) && $force) {
            $date = date("Y-m-d");
        }
        $this->setUnix(self::dateUnixSql($date), $field);
        return $this->set($date, $field);
    }

    /**
     * Where statement for the reporting period dates
     *
     * @param string $date  Date to use if it is set in MySQL format ("Y-m-d")
     * @param string $field The field to save the date in
     *
     * @return null
     */
    function setPeriodDate($date, $field)
    {
        $date = self::fixDate($date);
        $this->setDate($date, $field);
        if ($this->get($field)) {
            return;
        }
        $date = $this->get("date");
        $type = $this->get("type");
        $method = "get".$type.$field;
        $unixDate = self::dateUnixSql($date);
        if (method_exists($this, $method)) {
            $dateFormat = $this->$method($date);
        } else {
            $dateFormat = $this->periods[$type][$field];
        }
        if (empty($dateFormat)) {
            $dateFormat = $this->periods["default"][$field];
        }
        $date = date($dateFormat, $unixDate);

        return self::setDate($date, $field);
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
        if (empty($where2)) {
            $where2 = $where1;
        }
        return "SELECT DISTINCT t.id as id,
            (t.hours1 + t.hours2 + t.hours3 + t.hours4 + t.hours5 + t.hours6)
            as hours,
            t.worked, t.project_id, t.notes,
            t.hours1 as hours1, t.hours2 as hours2, t.hours3 as hours3,
            t.hours4 as hours4, t.hours5 as hours5, t.hours6 as hours6,
            p.wcCode1 as wcCode1, p.wcCode2 as wcCode2, p.wcCode3 as wcCode3,
            p.wcCode4 as wcCode4, p.wcCode5 as wcCode5, p.wcCode6 as wcCode6,
            t.created_by as created_by, p.name as project_name, p.type as type,
            u.name as author, pc.name as category_name, c.company as company_name,
            c.name as contact_name, p.id as project_id, u.id as user_id,
            p.parent_id as category_id
            FROM      #__timeclock_timesheet as t
            LEFT JOIN #__timeclock_projects as p on t.project_id = p.id
            LEFT JOIN #__timeclock_users as j on (j.id = p.id OR p.type != 'HOLIDAY')
            LEFT JOIN #__users as u on j.user_id = u.id
            LEFT JOIN #__timeclock_projects as pc on p.parent_id = pc.id
            LEFT JOIN #__timeclock_customers as c on p.customer = c.id
            WHERE
            (
                ".$where1." AND (p.type = 'PROJECT' OR p.type = 'PTO')
                AND (j.user_id = t.created_by OR j.user_id IS NULL)
            )
            OR
            (
                ".$where2." AND p.type = 'HOLIDAY' AND u.block = 0
            )
            ";
    }


    /**
     * Method to display the view
     *
     * @param string $where      The where clause to add. Must NOT include "WHERE"
     * @param int    $limitstart The record to start on
     * @param int    $limit      The max number of records to retrieve
     * @param string $orderby    The orderby clause.  Must include "ORDER BY"
     *
     * @return string
     */
    function getTimesheetData(
        $where = "1", $limitstart=null, $limit=null, $orderby=""
    ) {
        if (empty($this->data)) {
            $db = TimeclockHelper::getParam("decimalPlaces");

            $where = array(
                "t.created_by = ".$this->_db->Quote($this->_id),
                $this->employmentDateWhere("t.worked"),
                $this->periodWhere("t.worked"),
            );
            $holidaywhere = array(
                "j.user_id = ".$this->_db->Quote($this->_id),
                $this->employmentDateWhere("t.worked"),
                $this->periodWhere("t.worked"),
            );
            $where = implode(" AND ", $where);
            $holidaywhere = implode(" AND ", $holidaywhere);
            $query = $this->sqlQuery($where, $holidaywhere);
            $this->data = $this->_getList($query);
            if (!is_array($this->data)) {
                return array();
            }
            foreach ($this->data as $k => $d) {
                if ($d->type == "HOLIDAY") {
                    $hperc = $this->getHolidayPerc($d->user_id, $d->worked);
                    $this->data[$k]->hours =  $d->hours * $hperc;
                }
                $this->data[$k]->hours = round($this->data[$k]->hours, $db);
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
        return TimeclockHelper::getUserParam("holidayperc", $id, $date) / 100;
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
        return self::dateWhere($field, $dates["start"], $dates["end"]);
    }

    /**
     * Where statement for dates
     *
     * @param string $field The field to use
     * @param string $start The start date
     * @param string $end   The end date
     *
     * @return string
     */
    function dateWhere($field, $start, $end="")
    {
        $ret = "($field >= ".$this->_db->Quote($start)."";

        if (($end != '0000-00-00') && !empty($end)) {
            $ret .= " AND $field <= ".$this->_db->Quote($end)."";
        }
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
                "start" => self::fixDate(
                    TimeclockHelper::getUserParam("startDate")
                ),
                "end"   => self::fixDate(
                    TimeclockHelper::getUserParam("endDate")
                ),
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
                $eDatesUnix[$key] = self::dateUnixSql($val);
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
        $period = $this->getPeriodDates();
        return self::dateWhere($field, $period["start"], $period["end"]);
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s").  If left
     *                      blank the date read from the request variables is used.
     *
     * @return array
     */
    function getPayPeriodStart($date)
    {
        $type = TimeclockHelper::getParam("payPeriodType");
        if (trim(strtolower($type)) == "month") {
            return self::getPayPeriodMonthStart($date);
        }
        return self::_getPayPeriodFixedStart($date);
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s").  If left
     *                      blank the date read from the request variables is used.
     *
     * @return array
     */
    function getPayPeriodEnd($date)
    {
        $type = TimeclockHelper::getParam("payPeriodType");
        if (trim(strtolower($type)) == "month") {
            return self::getPayPeriodMonthEnd($date);
        }
        return self::_getPayPeriodFixedEnd($date);
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s").  If left
     *                      blank the date read from the request variables is used.
     *
     * @return array
     */
    function getQuarterStart($date)
    {
        $date = self::explodeDate($date);
        if ($date["m"] < 4) {
            return date("Y-01-01");
        }
        if ($date["m"] < 7) {
            return date("Y-04-01");
        }
        if ($date["m"] < 10) {
            return date("Y-07-01");
        }
        return date("Y-10-01");
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s").  If left
     *                      blank the date read from the request variables is used.
     *
     * @return array
     */
    function getQuarterEnd($date)
    {
        $date = self::explodeDate($date);
        if ($date["m"] < 4) {
            return date("Y-03-31");
        }
        if ($date["m"] < 7) {
            return date("Y-06-30");
        }
        if ($date["m"] < 10) {
            return date("Y-09-30");
        }
        return date("Y-12-31");
    }

    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s").  If left
     *                      blank the date read from the request variables is used.
     *
     * @return array
     */
    function getPayPeriodMonthStart($date)
    {
        $first = TimeclockHelper::getParam("firstPayPeriodStart");
        $first = self::explodeDate($first);
        $dateFormat = $this->periods["month"]["start"];
        $unixDate = self::dateUnixSql($date);
        $start = date($dateFormat, $unixDate);
        $s = self::explodeDate($start);
        $this->set((int) date("t", $unixDate), "length");
        return self::_date($s["m"], $s["d"]+$first["d"]-1, $s["y"]);
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s").  If left
     *                      blank the date read from the request variables is used.
     *
     * @return array
     */
    function getPayPeriodMonthEnd($date)
    {
        $unixDate = self::dateUnixSql($date);
        $s = self::getPayPeriodMonthStart($date);
        $s = self::explodeDate($s);
        $len = (int) date("t", $unixDate);
        $this->set($len, "length");
        $end = self::_date($s["m"], $s["d"]+$len-1, $s["y"]);
        return $end;
    }


    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s").  If left
     *                      blank the date read from the request variables is used.
     *
     * @return array
     */
    function getFixedStart($date)
    {
        // Get the pay period start
        $startTime = TimeclockHelper::getParam("firstViewPeriodStart");
        $len = TimeclockHelper::getParam("viewPeriodLengthFixed");
        return self::getOffsetFromDate($date, $startTime, $len);
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s").  If left
     *                      blank the date read from the request variables is used.
     *
     * @return array
     */
    function getFixedEnd($date)
    {
        $len = TimeclockHelper::getParam("viewPeriodLengthFixed");
        $s = self::getFixedStart($date);
        $s = self::explodeDate($s);
        $this->set($len, "length");
        $end = self::_date($s["m"], $s["d"]+$len-1, $s["y"]);
        return $end;
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param int    $date      The date in Mysql ("Y-m-d") format.
     * @param string $startTime The start date in MySQL format (YYYY-MM-DD)
     * @param int    $len       The length of the period
     *
     * @return array
     */
    function getOffsetFromDate($date, $startTime, $len)
    {
        // Get this date
        $uDate = self::dateUnixSql($date);
        $d = self::explodeDate($date);

        $start = self::dateUnixSql($startTime);

        // Get the time difference in days
        $timeDiff = round(($uDate - $start) / 86400);
        if ($len != 0) {
            $days = $timeDiff % $len;
        } else {
            $days = 0;
        }
        return self::_date($d["m"], ($d["d"] - $days), $d["y"]);

    }


    /**
     * Where statement for the reporting period dates
     *
     * @param int $date The date in Mysql ("Y-m-d") format.
     *
     * @return array
     */
    private function _getPayPeriodFixedEnd($date)
    {

        $s = self::_getPayPeriodFixedStart($date);
        $s = self::explodeDate($s);
        $length = TimeclockHelper::getParam("payPeriodLengthFixed");
        $this->set($length, "length");
        $end = self::_date($s["m"], $s["d"]+$length-1, $s["y"]);
        return $end;
    }

    /**
     * Where statement for the reporting period dates
     *
     * @return array
     */
    function getLength()
    {
        $startUnix = self::dateUnixSql($this->get("start"));
        $endUnix = self::dateUnixSql($this->get("end"));
        $length = (int)round(($endUnix - $startUnix) / 86400) + 1;
        return $this->set($length, "length");
    }

    /**
     * Where statement for the reporting period dates
     *
     * @return array
     */
    function getPeriodDates()
    {
        if (!$this->get("_done")) {
            $startDate = $this->get("start");
            $endDate   = $this->get("end");
            $s = self::explodeDate($startDate);
            $e = self::explodeDate($endDate);

            $length = $this->getLength();
            // These are all of the dates in the pay period
            for ($i = 0; $i < $length; $i++) {
                $this->period['dates'][self::_date($s["m"], $s["d"]+$i, $s["y"])]
                    = self::dateUnix($s["m"], $s["d"]+$i, $s["y"]);
            }

            // Get the start and end
            $this->setUnix(
                self::dateUnix($s["m"], $s["d"]-$length, $s["y"]),
                "prev"
            );
            $this->setUnix(
                self::dateUnix($s["m"], $s["d"]-1, $s["y"]),
                "prevend"
            );
            $this->setUnix(
                self::dateUnix($e["m"], $e["d"]+1, $e["y"]),
                "next"
            );
            $this->setUnix(
                self::dateUnix($e["m"], $e["d"]+$length, $e["y"]),
                "nextend"
            );

            $this->set(self::_date($this->getUnix('prev')), "prev");
            $this->set(self::_date($this->getUnix('prevend')), "prevend");
            $this->set(self::_date($this->getUnix('next')), "next");
            $this->set(self::_date($this->getUnix('nextend')), "nextend");
            $this->set(true, "_done");
        }
        return $this->period;
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param int $date The date in Mysql ("Y-m-d") format.
     *
     * @return array
     */
    private function _getPayPeriodFixedStart($date)
    {
        // Get the pay period start
        $startTime = TimeclockHelper::getParam("firstPayPeriodStart");

        // Get the length in days
        $len = TimeclockHelper::getParam("payPeriodLengthFixed");
        return self::getOffsetFromDate($date, $startTime, $len);
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
        if (!(is_null($d) && is_null($y))) {
            $m = self::dateUnix($m, $d, $y);
        }
        return date("Y-m-d", $m);
    }


    /**
     * Method to display the view
     *
     * @return string
     */
    function getData()
    {
        $query = "SELECT t.*,
                  (t.hours1 + t.hours2 + t.hours3 + t.hours4 + t.hours5 + t.hours6)
                  as hours
                  FROM #__timeclock_timesheet as t
                  WHERE t.worked =".$this->_db->Quote($this->get("date"))."
                     AND t.created_by = ".$this->_db->Quote($this->_id)."
                  ";
        $ret = $this->_getList($query);
        if (!is_array($ret)) {
            return array();
        }
        $data = array();
        foreach ($ret as $d) {
            $data[$d->project_id] = $d;
        }
        return $data;
    }
    /**
     * Method to display the view
     *
     * @param string $where The where clause to use
     * @param int    $id    The user id to use
     *
     * @return string
     */
    function getTotal($where, $id=null)
    {
        if (empty($id)) {
            $id = $this->_id;
        }
        $key = urlencode($id.$where);
        if (!isset($this->_totals[$key])) {
            $query = "SELECT
                    SUM(t.hours1 + t.hours2 + t.hours3 + t.hours4
                        + t.hours5 + t.hours6) as hours
                    FROM #__timeclock_timesheet as t
                    LEFT JOIN #__timeclock_projects as p on t.project_id = p.id
                    WHERE t.created_by = ".$this->_db->Quote($id);
            if (!empty($where)) {
                $query .= " AND ".$where;
            }
            $ret = $this->_getList($query);
            if (!is_array($ret)) {
                $this->_totals[$key] = 0;
            } else {
                $this->_totals[$key] = $ret[0]->hours;
            }
        }
        return $this->_totals[$key];
    }

    /**
     * Method to display the view
     *
     * @param string $where The extra where clause
     *
     * @return string
     */
    function getNextHoliday($where=1)
    {
        $key = urlencode(date("Ymd").$where);
        if (!isset($this->_holidays[$key])) {
            $query = $this->sqlQuery("0", $where);
            $ret = $this->_getList($query);
            if (!is_array($ret)) {
                $this->_holidays[$key] = false;
            } else {
                $this->_holidays[$key] = $ret[0]->worked." 06:00:00";
            }
        }
        return $this->_holidays[$key];
    }


    /**
     * Method to display the view
     *
     * @param int $id The id of the user to use
     *
     * @return string
     */
    function daysSinceStart($id=null)
    {
        if (empty($id)) {
            $id = $this->_id;
        }
        $start = TimeclockHelper::getUserParam("startDate", $id);
        $diff = time() - strtotime($start);
        return $diff/86400;
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
        $user = JFactory::getUser();
        if (empty($date)) {
            return false;
        }
        // This makes the totals recompute
        unset($this->_totals);

        $ret = true;
        foreach ($timesheet as $data) {
            $htotal = 0;
            for ($i = 1; $i < 7; $i++) {
                if (!isset($data["hours".$i])) {
                    $data["hours".$i] = 0.0;
                } else {
                    $data["hours".$i] = (float) $data["hours".$i];
                }
                $htotal += $data["hours".$i];
            }
            // If there are no hours don't create a record.
            // If there is already a record allow 0 hours.
            if (empty($htotal) && empty($data["id"])) {
                continue;
            }
            // Remove white space from the notes
            $data["notes"] = trim($data["notes"]);
            $data["id"] = (int) $data["id"];
            $data["created_by"] = $user->get("id");
            $data["worked"] = $date;
            if (empty($data["created"])) {
                $data["created"] = date("Y-m-d H:i:s");
            }
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


    /**
     * Method to display the view
     *
     * @param string $date The date to enter time
     *
     * @access public
     * @return null
     */
    public function checkDates($date)
    {
        $model = $this->getModel("Timeclock");
        $date = self::dateUnixSql($date);
        $eDates = $model->getEmploymentDatesUnix();
        return self::checkEmploymentDates($eDates["start"], $eDates["end"], $date);
    }
    /**
     * Method to display the view
     *
     * @param string $start The date the employee started
     * @param string $end   The date the employee ended
     * @param string $date  The date to enter time
     *
     * @access public
     * @return null
     */
    static public function checkEmploymentDates($start, $end, $date)
    {
        if ($date < $start) {
            return false;
        }
        if (($date > $end) && !empty($end)) {
            return false;
        }
        return true;
    }


    /**
     * Format the project id
     *
     * @param int $id The project ID
     *
     * @return string
     */
    static public function formatProjId($id)
    {
        return sprintf("%04d", (int)$id);
    }

    /**
     * Where statement for the reporting period dates
     *
     * @param string $date Date to use in MySQL format ("Y-m-d H:i:s")
     *
     * @return array
     */
    static public function fixDate($date)
    {
        static $fixDate;
        if (empty($fixDate[$date])) {
            preg_match(
                "/[1-9][0-9]{3}-[0-1]{0,1}[0-9]-[0-3]{0,1}[0-9]/",
                $date,
                $ret
            );
            if (isset($ret[0])) {
                $fixDate[$date] = $ret[0];
            } else {
                $fixDate[$date] = null;
            }
        }
        return $fixDate[$date];
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param int $m The month
     * @param int $d The day
     * @param int $y The year
     *
     * @return array
     */
    static public function dateUnix($m, $d, $y)
    {
        return mktime(6, 0, 0, (int)$m, (int)$d, (int)$y);
    }

    /**
     * Where statement for the reporting period dates
     *
     * @param string $date1 The first date in Mysql ("Y-m-d") format.
     * @param string $date2 The second date in Mysql ("Y-m-d") format.
     *
     * @return array
     */
    static public function compareDates($date1, $date2)
    {
        $date1 = self::dateUnixSql($date1);
        $date2 = self::dateUnixSql($date2);
        if ($date1 < $date2) {
            return -1;
        }
        if ($date1 > $date2) {
            return 1;
        }
        return 0;
    }

    /**
     * Where statement for the reporting period dates
     *
     * @param string $sqlDate The date in Mysql ("Y-m-d") format.
     *
     * @return array
     */
    static public function dateUnixSql($sqlDate)
    {
        $date = self::explodeDate($sqlDate);
        if (empty($date["y"])) {
            return 0;
        }
        return self::dateUnix($date["m"], $date["d"], $date["y"]);
    }
    /**
     * Where statement for the reporting period dates
     *
     * @param int $date The date in Mysql ("Y-m-d") format.
     *
     * @return array
     */
    static public function explodeDate($date)
    {

        $date = self::fixDate($date);
        $date = explode("-", $date);

        return array(
            "y" => isset($date[0]) ? $date[0] : null,
            "m" => isset($date[1]) ? $date[1] : null,
            "d" => isset($date[2]) ? $date[2] : null,
        );
    }


}

?>
