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
 * @version    GIT: $Id: 93d6ff9d8b8f51890b03950b9dfe1fae4b63b119 $
 * @link       https://dev.hugllc.com/index.php/Project:JoomlaMock
 */
namespace com_timeclock\tests\site\models;
/** Base class */
require_once SRC_PATH."/com_timeclock/site/models/default.php";

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
class ModelTestBase extends \com_timeclock\TestCaseDatabase
{
    /** This is the model we are testing */
    protected $model;
    
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataGet()
    {
        return array(
            "Not Default" => array("type", "asdf", "fsda", "fsda"),
            "Default" => array("type", "asdf", null, "asdf"),
            "Nulls" => array("type", null, null, null),
            "Arrays" => array("type", null, array(1), array(1)),
        );
    }
    /**
    * test the set routine when an extra class exists
    *
    * @param string $name    The name of the variable to test.
    * @param mixed  $default The default value to use
    * @param mixed  $value   The value to set it to.  Not set if null.
    * @param array  $expect  The expected return
    *
    * @return null
    *
    * @dataProvider dataGet
    */
    public function testGet($name, $default, $value, $expect)
    {
        $model = $this->model;
        $obj = new $model();
        if (!is_null($value)) {
            $obj->set($name, $value);
        }
        $this->assertSame($expect, $obj->get($name, $default));
    }
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataSet()
    {
        return array(
            "Two" => array(
                "type", 
                array(
                    "asdf",
                    "fdsa",
                ), 
                array(
                    null,
                    "asdf"
                )
            ),
            "Three" => array(
                "type", 
                array(
                    "test1",
                    "test2",
                    null,
                ), 
                array(
                    null,
                    "test1",
                    "test2"
                )
            ),
        );
    }
    /**
    * test the set routine when an extra class exists
    *
    * @param string $name    The name of the variable to test.
    * @param array  $values  The values to set it to.
    * @param array  $expects The expected return
    *
    * @return null
    *
    * @dataProvider dataSet
    */
    public function testSet($name, $values, $expects)
    {
        $model = $this->model;
        $obj = new $model();
        foreach ((array)$values as $key => $value) {
            $this->assertSame(
                $expects[$key], 
                $obj->set($name, $value),
                "key $key failed"
            );
        }
    }
    /**
    * data provider for testGetList
    *
    * @return array
    */
    public static function dataGetList()
    {
        return array(
        );
    }
    /**
    * test the set routine when an extra class exists
    *
    * @param mixed $input   The name of the variable to test.
    * @param array $options The options to give the mock session.
    * @param array $expects The expected return
    *
    * @return null
    *
    * @dataProvider dataGetList
    */
    public function testGetList($input, $options, $expects)
    {
        $this->setSession($options);
        $this->setInput($input);
        $model = $this->model;
        $obj = new $model();
        $ret = $obj->listItems();
        $this->checkReturn($ret, $expects);
    }
    /**
    * data provider for testGet
    *
    * @return array
    */
    public static function dataGetState()
    {
        return array(
            "default" => array("_limit", "asdf", array(), array(), "asdf"),
            "Null" => array("_limit", null, array(), array(), null),
            "Class name" => array(
                null, null, array(), array(), "Joomla\Registry\Registry"
            ),
        );
    }
    /**
    * test the set routine when an extra class exists
    *
    * @param string $name    The name of the variable to test.
    * @param mixed  $default The default value to use
    * @param mixed  $input   The name of the variable to test.
    * @param array  $options The options to give the mock session.
    * @param array  $expect  The expected return
    *
    * @return null
    *
    * @dataProvider dataGetState
    */
    public function testGetState($name, $default, $input, $options, $expect)
    {
        $this->setSession($options);
        $this->setInput($input);
        $model = $this->model;
        $obj = new $model();
        $reg = $obj->getState($name, $default);
        if (!is_object($reg)) {
            $this->assertSame($expect, $reg);
        } else {
            $this->assertSame($expect, get_class($reg));
        }
    }
    /**
    * Checks the return and expect to see if they are equal
    *
    * @param array $ret     The return given
    * @param array $expects The expected return
    *
    * @return null
    *
    * @dataProvider dataUnpublish
    */
    protected function checkReturn($ret, $expects)
    {
        $check = array();
        foreach ($ret as $key => $return) {
            if (isset($expects[$key]) && is_array($expects[$key])) {
                $check[$key] = array();
                foreach ($expects[$key] as $k => $v) {
                    $check[$key][$k] = $return->$k;
                }
            } else {
                $check[$key] = get_object_vars($return);
            }
            
        }
        $this->assertInternalType("array", $ret, "Return is not an array");
        $this->assertEquals($expects, $check);
    }
}

?>
