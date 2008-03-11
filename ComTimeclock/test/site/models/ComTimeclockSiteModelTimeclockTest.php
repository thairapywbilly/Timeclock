<?php
/**
 * Tests the driver class
 *
 * PHP Version 5
 *
 * <pre>
 * ComTimeclock is a Joomla application to keep track of employee time
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
 * @package    ComTimeclockTest
 * @subpackage Test
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2008 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    SVN: $Id$    
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock:JoomlaUI
 */
/** Require the JoomlaMock stuff */
require_once dirname(__FILE__).'/../../JoomlaMock/joomla.php';
require_once dirname(__FILE__).'/../../JoomlaMock/testCases/JModelTest.php';
require_once dirname(__FILE__).'/../../../site/models/timeclock.php';

/**
 * Test class for driver.
 * Generated by PHPUnit_Util_Skeleton on 2007-10-30 at 08:44:25.
 *
 * @category   Test
 * @package    ComTimeclockTest
 * @subpackage Test
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2008 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock:JoomlaUI
 */
class ComTimeclockSiteModelTimeclockTest extends JModelTest
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
        $this->sqlFile = array(
            dirname(__FILE__)."/../../../install/timeclock_timesheet.sql",
            dirname(__FILE__)."/../../../install/timeclock_users.sql",
            dirname(__FILE__)."/../../../install/timeclock_prefs.sql",
        );
        $this->o = new TimeclockModelTimeclock();        
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
        unset($this->o);
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function dataGetDataCache()
    {
        return array(
        );
    }
    /**
     * Data provider
     *
     * @return array
     */
    public static function dataStore()
    {
        return array(
        );
    }
    /**
     * Data provider
     *
     * @return array
     */
    public static function dataStoreRet()
    {
        return array(
            /*
            array(null, "store", false, array(), "post"),
            array(null, "store", true, array("id" => 1, "prefs" => array()), "post"),
            array("bind", "store", false, array("id" => 1, "prefs" => array()), "post"),
            array("check", "store", false, array("id" => 1, "prefs" => array()), "post"),
            array("store", "store", false, array("id" => 1, "prefs" => array()), "post"),
            */
        );
    }
    /**
     * Data provider
     *
     * @return array
     */
    public static function dataGetSetDate()
    {
        return array(
            array("2009-05-12", "2009-05-12"),
            array("2002-5-2", "2002-5-2"),
            array("2002-05-22 21:24:52", "2002-05-22"),
            array("2523422002-05-2225114", "2002-05-22"),
            array("2523422002-052-2225114", date("Y-m-d")),
            array(null, date("Y-m-d")),
        );
    }
    
    /**
     * Tests get and set date
     *
     * @param string $date   The date to test
     * @param string $expect The date we expect returned
     *
     * @dataProvider dataGetSetDate()
     * @return null
     */
    function testGetSetDate($date, $expect)
    {
        $this->o->setDate($date);
        $ret = $this->o->getDate($date);
        $this->assertSame($expect, $ret);
    }

}

?>
