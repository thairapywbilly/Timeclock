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

// Call DfProjectTimeclockXmlTest::main() if this source file is executed directly.
if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "DfProjectTimeclockXmlTest::main");
}
// This is for the joomla extensions
if (!defined('_VALID_MOS')) {
    define('_VALID_MOS', true);
}

/** The test case class */
require_once "PHPUnit/Framework/TestCase.php";
/** The test suite class */
require_once "PHPUnit/Framework/TestSuite.php";
require_once dirname(__FILE__).'/../../test/JoomlaMock/joomla.php';
require_once dirname(__FILE__).'/../../test/JoomlaMock/JoomlaXmlTestCase.php';

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
class DfProjectTimeclockXmlTest extends JoomlaXmlTestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     *
     * @access protected
     */
    protected function setUp() 
    {
        parent::setUp();
        $dir = dirname(__FILE__);
        $this->basedir = substr($dir, 0, strlen($dir) - 4);        
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     *
     * @access protected
     */
    protected function tearDown() 
    {
        parent::tearDown();
    }
    

    /**
     * Data provider for testName
     *
     * @return array()
     */
    public static function dataName()
    {
        return self::getDataName("com_dfprojecttimeclock", "dfprojecttimeclock");    
    }

    /**
     * Data provider for testFiles
     *
     * @return array()
     */
    public static function dataFiles()
    {
        return self::getDataFiles("com_dfprojecttimeclock", dirname(__FILE__)."/..");    
    }

}

// Call DfProjectTimeclockXmlTest::main() if this source file is executed directly.
if (PHPUnit_MAIN_METHOD == "DfProjectTimeclockXmlTest::main") {
    DfProjectTimeclockXmlTest::main();
}

?>
