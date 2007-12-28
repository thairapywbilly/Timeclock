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
 * @package    TimeclockTest
 * @subpackage Test
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2007 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    SVN: $Id$    
 * @link       https://dev.hugllc.com/index.php/Project:Timeclock
 */

// Call dftimeclockClassTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "dftimeclockClassTest::main");
}
// This is for the joomla extensions
if (!defined('_VALID_MOS')) {
    define('_VALID_MOS', true);
}

/** The test case class */
require_once "PHPUnit/Framework/TestCase.php";
/** The test suite class */
require_once "PHPUnit/Framework/TestSuite.php";
require_once dirname(__FILE__).'/../../com_dfprojecttimeclock/dfprojecttimeclock.class.php';
require_once dirname(__FILE__).'/../JoomlaMock/joomla.php';
/**
 * Test class for driver.
 * Generated by PHPUnit_Util_Skeleton on 2007-10-30 at 08:44:25.
 *
 * @category   Test
 * @package    TimeclockTest
 * @subpackage Test
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2007 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://dev.hugllc.com/index.php/Project:Timeclock
 */
class dftimeclockClassTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Runs the test methods of this class.
     *
     * @return none
     *
     * @access public
     * @static
     */
    public static function main() 
    {
        include_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("dftimeclockClassTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return none
     *
     * @access protected
     */
    protected function setUp() 
    {
        $this->o = new timesheet();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return none
     *
     * @access protected
     */
    protected function tearDown() 
    {
        unset($this->o);
    }


    /**
     * dataProvider for testFixDate
     *
     * @return array
     */
    public static function dataFixDate() 
    {
        return array(
            array("2007-12-04", 1196769600),
        );
    }
    /**
     * test registerDriver
     *
     * @param mixed $date   The date to feed the function
     * @param bool  $expect The result to expect
     *
     * @return none
     *
     * @dataProvider dataFixDate
     */
    public function testFixDate($date, $expect) 
    {
        $ret = $this->o->fixDate($date);
        $this->assertSame($expect, $ret);
    }
}

// Call dftimeclockClassTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "dftimeclockClassTest::main") {
    dftimeclockClassTest::main();
}

?>
