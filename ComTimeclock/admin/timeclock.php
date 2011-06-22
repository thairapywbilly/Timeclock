<?php
/**
 * This component is the user interface for the endpoints
 *
 * PHP Version 5
 *
 * <pre>
 * com_ComTimeclock is a Joomla! 1.6 component
 * Copyright (C) 2008-2009, 2011 Hunt Utilities Group, LLC
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
 * @copyright  2008-2009, 2011 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    SVN: $Id$
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */

defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_timeclock'))
{
    return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// import joomla controller library
jimport('joomla.application.component.controller');

/** Require the base controller */
//require_once JPATH_COMPONENT.DS.'controller.php';

// require helper file
JLoader::register('TimeclockHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'timeclock.php');

// This loads the prefs table file.
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'tables'.DS.'timeclockprefs.php';
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'lib'.DS.'sql.inc.php';
/*
// Require specific controller if requested
if ($controller = JRequest::getCmd('controller')) {
    $path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
    if (file_exists($path)) {
        include_once $path;
    } else {
        $controller = '';
    }
}
*/
// Create the controller
//$classname    = 'TimeclockAdminController'.ucfirst($controller);
//$controller   = new $classname();
$controller = JController::getInstance('TimeclockAdmin');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();

?>
<p class="copyright">
<a href="http://www.hugllc.com/wiki/index.php/Project:Timeclock">Timeclock</a>
Copyright &copy; 2008-2009, 2011
    <a href="http://www.hugllc.com">Hunt Utilities Group, LLC</a>
<br /><?php print JText::_(COM_TIMECLOCK_FOUND_A_BUG); ?>
<a href="https://dev.hugllc.com/bugs/project_page.php?project_id=7">
<?php print JText::_(COM_TIMECLOCK_REPORT_IT_HERE); ?></a>
</p>