<?php
/**
 * Tests the driver class
 *
 * PHP Version 5
 *
 * <pre>
 * Timeclock is a Joomla application to keep track of employee time
 * Copyright (C) 2007 Hunt Utilities Group, LLC
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
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * </pre>
 *
 * @category   Test
 * @package    JoomlaMock
 * @subpackage TestCase
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2008 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    GIT: $Id: 04754bc6176630e0c25ce645f83af96c852bf3ac $
 * @link       https://dev.hugllc.com/index.php/Project:JoomlaMock
 */
namespace com_timeclock\tests\site\helpers;
/** Base class */
require_once SRC_PATH."/com_timeclock/site/helpers/date.php";

/**
 * Test class for driver.
 * Generated by PHPUnit_Util_Skeleton on 2007-10-30 at 08:44:25.
 *
 * @category   Test
 * @package    JoomlaMock
 * @subpackage TestCase
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2008 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://dev.hugllc.com/index.php/Project:JoomlaMock
 */
class TimeclockHelpersDateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return null
     *
     * @access protected
     */
    protected function setUp()
    {
        parent::setUp();
    }
    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return null
     *
     * @access protected
     */
    protected function tearDown()
    {
        parent::tearDown();
    }
    
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataFixDate()
    {
        return array(
            "Not a date" => array("asdf", null),
            "month out of line" => array("2013-13-21", "2014-01-21"),
            "two digit year" => array("13-12-25", null),
            "Correct date" => array("2014-12-25", "2014-12-25"),
            "Now" => array("now", date("Y-m-d")),
        );
    }
    /**
    * Checks to see if we get proper stuff from this function
    *
    * @param string $date   The date to give the function
    * @param array  $expect The expected return
    *
    * @return null
    *
    * @dataProvider dataFixDate
    */
    public function testFixDtae($date, $expect)
    {
        $this->assertSame($expect, \TimeclockHelpersDate::FixDate($date));
    }
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataCheckEmploymentDates()
    {
        return array(
            "Inside employment window" => array(
                "2001-12-25", "2002-12-25", "2002-3-15", true
            ),
            "Start Day" => array(
                "2001-12-25", "2002-12-25", "2001-12-25", true
            ),
            "End Day" => array(
                "2001-12-25", "2002-12-25", "2002-12-25", true
            ),
            "Day before" => array(
                "2001-12-25", "2002-12-25", "2001-12-24", false
            ),
            "Day after" => array(
                "2001-12-25", "2002-12-25", "2002-12-26", false
            ),
            "No end" => array(
                "2001-12-25", 0, "2012-12-25", true
            ),
        );
    }
    /**
    * Checks to see if we get proper stuff from this function
    *
    * @param string $start  The employee start date
    * @param string $end    The employee end date
    * @param string $date   The date to give the function
    * @param array  $expect The expected return
    *
    * @return null
    *
    * @dataProvider dataCheckEmploymentDates
    */
    public function testCheckEmploymentDates($start, $end, $date, $expect)
    {
        $this->assertSame(
            $expect, 
            \TimeclockHelpersDate::CheckEmploymentDates($start, $end, $date)
        );
    }
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataCompareDates()
    {
        return array(
            "same, sql" => array(
                "2002-12-25", "2002-12-25", 0
            ),
            "less than, sql" => array(
                "2001-12-25", "2002-12-25", -1
            ),
            "Greater than, sql" => array(
                "2003-12-25", "2002-12-25", 1
            ),
            "same, unix" => array(
                21, 21, 0
            ),
            "less than, unix" => array(
                15, 21, -1
            ),
            "Greater than, unix" => array(
                21, 15, 1
            ),
        );
    }
    /**
    * Checks to see if we get proper stuff from this function
    *
    * @param string $date1  The first date
    * @param string $date2  The second date
    * @param array  $expect The expected return
    *
    * @return null
    *
    * @dataProvider dataCompareDates
    */
    public function testCompareDates($date1, $date2, $expect)
    {
        $this->assertSame(
            $expect, 
            \TimeclockHelpersDate::CompareDates($date1, $date2)
        );
    }
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataFixedPayPeriodStart()
    {
        return array(
            "normal" => array(
                "2000-12-11", "2014-09-08", 14, "2014-09-01"
            ),
            "positive offset" => array(
                "2014-09-01", "2000-12-12", 14, "2000-12-11"
            ),
            "First day" => array(
                "2000-12-11", "2014-09-01", 14, "2014-09-01"
            ),
            "Last day" => array(
                "2000-12-11", "2014-09-14", 14, "2014-09-01"
            ),
        );
    }
    /**
    * Checks to see if we get proper stuff from this function
    *
    * @param mixed  $firstPeriodStart The first pay period start date.
    * @param mixed  $date             The date in question
    * @param int    $len              The length of the period
    * @param string $expect           The expected return
    *
    * @return null
    *
    * @dataProvider dataFixedPayPeriodStart
    */
    public function testFixedPayPeriodStart($firstPeriodStart, $date, $len, $expect)
    {
        $this->assertSame(
            $expect, 
            \TimeclockHelpersDate::fixedPayPeriodStart(
                $firstPeriodStart, $date, $len
            )
        );
    }
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataFixedPayPeriod()
    {
        return array(
            "normal" => array(
                "2000-12-11", 
                "2014-09-08", 
                14, 
                array(
                    'days' => 14,
                    'start' => '2014-09-01',
                    'end' => '2014-09-14',
                    'next' => '2014-09-15',
                    'prev' => '2014-08-18',
                    'dates' => array(
                        0 => '2014-09-01',
                        1 => '2014-09-02',
                        2 => '2014-09-03',
                        3 => '2014-09-04',
                        4 => '2014-09-05',
                        5 => '2014-09-06',
                        6 => '2014-09-07',
                        7 => '2014-09-08',
                        8 => '2014-09-09',
                        9 => '2014-09-10',
                        10 => '2014-09-11',
                        11 => '2014-09-12',
                        12 => '2014-09-13',
                        13 => '2014-09-14',
                    ),
                ),
            ),
            "positive offset" => array(
                "2014-09-01", 
                "2000-12-12", 
                14, 
                array(
                    'days' => 14,
                    'start' => '2000-12-11',
                    'end' => '2000-12-24',
                    'next' => '2000-12-25',
                    'prev' => '2000-11-27',
                    'dates' => array(
                        0 => '2000-12-11',
                        1 => '2000-12-12',
                        2 => '2000-12-13',
                        3 => '2000-12-14',
                        4 => '2000-12-15',
                        5 => '2000-12-16',
                        6 => '2000-12-17',
                        7 => '2000-12-18',
                        8 => '2000-12-19',
                        9 => '2000-12-20',
                        10 => '2000-12-21',
                        11 => '2000-12-22',
                        12 => '2000-12-23',
                        13 => '2000-12-24',
                    ),
                ),
            ),
            "First day" => array(
                "2000-12-11", 
                "2014-09-01", 
                14, 
                array(
                    'days' => 14,
                    'start' => '2014-09-01',
                    'end' => '2014-09-14',
                    'next' => '2014-09-15',
                    'prev' => '2014-08-18',
                    'dates' => array(
                        0 => '2014-09-01',
                        1 => '2014-09-02',
                        2 => '2014-09-03',
                        3 => '2014-09-04',
                        4 => '2014-09-05',
                        5 => '2014-09-06',
                        6 => '2014-09-07',
                        7 => '2014-09-08',
                        8 => '2014-09-09',
                        9 => '2014-09-10',
                        10 => '2014-09-11',
                        11 => '2014-09-12',
                        12 => '2014-09-13',
                        13 => '2014-09-14',
                    ),
                ),
            ),
            "Last day" => array(
                "2000-12-11", 
                "2014-09-14", 
                14, 
                array(
                    'days' => 14,
                    'start' => '2014-09-01',
                    'end' => '2014-09-14',
                    'next' => '2014-09-15',
                    'prev' => '2014-08-18',
                    'dates' => array(
                        0 => '2014-09-01',
                        1 => '2014-09-02',
                        2 => '2014-09-03',
                        3 => '2014-09-04',
                        4 => '2014-09-05',
                        5 => '2014-09-06',
                        6 => '2014-09-07',
                        7 => '2014-09-08',
                        8 => '2014-09-09',
                        9 => '2014-09-10',
                        10 => '2014-09-11',
                        11 => '2014-09-12',
                        12 => '2014-09-13',
                        13 => '2014-09-14',
                    ),
                ),
            ),
        );
    }
    /**
    * Checks to see if we get proper stuff from this function
    *
    * @param mixed  $firstPeriodStart The first pay period start date.
    * @param mixed  $date             The date in question
    * @param int    $len              The length of the period
    * @param string $expect           The expected return
    *
    * @return null
    *
    * @dataProvider dataFixedPayPeriod
    */
    public function testFixedPayPeriod($firstPeriodStart, $date, $len, $expect)
    {
        $this->assertSame(
            $expect, 
            \TimeclockHelpersDate::fixedPayPeriod(
                $firstPeriodStart, $date, $len
            )
        );
    }
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataPayPeriodDates()
    {
        return array(
            "Normal period" => array(
                "2014-09-01", // Start
                "2014-09-14", // End
                array(
                    '2014-09-01',
                    '2014-09-02',
                    '2014-09-03',
                    '2014-09-04',
                    '2014-09-05',
                    '2014-09-06',
                    '2014-09-07',
                    '2014-09-08',
                    '2014-09-09',
                    '2014-09-10',
                    '2014-09-11',
                    '2014-09-12',
                    '2014-09-13',
                    '2014-09-14',
                )             // Expect
            ),
            "End of September" => array(
                "2014-09-29", // Start
                "2014-10-12", // End
                array(
                    '2014-09-29',
                    '2014-09-30',
                    '2014-10-01',
                    '2014-10-02',
                    '2014-10-03',
                    '2014-10-04',
                    '2014-10-05',
                    '2014-10-06',
                    '2014-10-07',
                    '2014-10-08',
                    '2014-10-09',
                    '2014-10-10',
                    '2014-10-11',
                    '2014-10-12',
                )             // Expect
            ),
        );
    }
    /**
    * Checks to see if we get proper stuff from this function
    *
    * @param string $start  The first day of the payperiod
    * @param string $end    The last day of the payperiod
    * @param string $expect The expected return
    *
    * @return null
    *
    * @dataProvider dataPayPeriodDates
    */
    public function testPayPeriodDates($start, $end, $expect)
    {
        $this->assertSame(
            $expect, 
            \TimeclockHelpersDate::PayPeriodDates(
                $start, $end
            )
        );
    }
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataDays()
    {
        return array(
            "Normal period" => array(
                "2014-09-01", // Start
                "2014-09-14", // End
                14           // Expect
            ),
            "End of September" => array(
                "2014-09-29", // Start
                "2014-10-12", // End
                14             // Expect
            ),
            "Same Day" => array(
                "2014-09-29", // Start
                "2014-09-29", // End
                1             // Expect
            ),
            "Negative" => array(
                "2014-10-12", // Start
                "2014-09-29", // End
                14             // Expect
            ),
        );
    }
    /**
    * Checks to see if we get proper stuff from this function
    *
    * @param string $start  The first day of the payperiod
    * @param string $end    The last day of the payperiod
    * @param string $expect The expected return
    *
    * @return null
    *
    * @dataProvider dataDays
    */
    public function testDays($start, $end, $expect)
    {
        $this->assertSame(
            $expect, 
            \TimeclockHelpersDate::days(
                $start, $end
            )
        );
    }
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataEnd()
    {
        return array(
            "Normal period" => array(
                "2014-09-01", // Start
                14,           // Days
                "2014-09-14", // Expect
            ),
            "End of September" => array(
                "2014-09-29", // Start
                14,             // Days
                "2014-10-12", // Expect
            ),
            "Same Day" => array(
                "2014-09-29", // Start
                1,             // Days
                "2014-09-29", // Expect
            ),
            "Negative" => array(
                "2014-10-12", // Start
                -14,          // Days
                "2014-09-29", // Expect
            ),
        );
    }
    /**
    * Checks to see if we get proper stuff from this function
    *
    * @param string $start  The first day of the payperiod
    * @param string $days   The number of days
    * @param string $expect The expected return
    *
    * @return null
    *
    * @dataProvider dataEnd
    */
    public function testEnd($start, $days, $expect)
    {
        $this->assertSame(
            $expect, 
            \TimeclockHelpersDate::end(
                $start, $days
            )
        );
    }

}
