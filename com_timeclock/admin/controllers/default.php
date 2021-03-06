<?php
/**
 * This component is for tracking tim
 *
 * PHP Version 5
 *
 * <pre>
 * com_timeclock is a Joomla! 3.1 component
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
 * @category   Timeclock
 * @package    Timeclock
 * @subpackage com_timeclock
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2014 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version    GIT: $Id: 16751c233707692a830d8a351fd78574c8402659 $
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
 
/**
 * Description Here
 *
 * @category   Timeclock
 * @package    Timeclock
 * @subpackage com_timeclock
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2014 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */
class TimeclockControllersDefault extends JControllerBase
{
    /** This is our basic link */
    protected $baselink = "index.php?option=com_timeclock";
    /** This is our controller name */
    protected $controller = "about";
    /** This is our model name */
    protected $model = "default";
    /** These are our system messages */
    protected $msgs = array(
        "saved" => "",
        "saveFailed" => "",
    );
    /** This is our task */
    private $_task = null;
    /** This is our model */
    private $_model = null;
    
    /**
    * This function performs everything for this controller.  It is the goto 
    * function.
    *
    * @access public
    * @return boolean
    */
    public function execute()
    {
        $task = $this->getTask();
        $fct  = "task".ucfirst($task);
        if (method_exists($this, $fct)) {
            return $this->$fct();
        }
        return $this->editlist();
    }
    /**
    * This function performs everything for this controller.  It is the goto 
    * function.
    *
    * @access public
    * @return boolean
    */
    public function editlist()
    {
        // Get the application
        $app = $this->getApplication();
        // Get the document object.
        $document = JFactory::getDocument();
        // Get the task
        $task = $this->getTask();
        if (($task != "edit") && ($task != "list") && ($task != "add")) {
            $app->input->set('id', null);
            $task = "list";
        }
        $model = $this->getModel();
        if (($task == "edit") && !$model->checkout()) {
            $app->input->set('id', null);
            $task = "list";
        }

        // set the view
        $app->input->set('view', $task);


        // Register the layout paths for the view
        $paths = new SplPriorityQueue;
        $paths->insert(
            JPATH_COMPONENT.'/views/'.$this->controller.'/tmpl', 'normal'
        );
        $viewClass = 'TimeclockViews'.ucfirst($this->controller) . "Html";
        
        $view = new $viewClass($model, $paths);
        $view->setLayout($task);
        // Render our view.
        echo $view->render();
        return true;
    }
    /**
    * This function get the task for us
    * 
    * @return null
    */
    protected function getTask()
    {
        if (is_null($this->_task)) {
            // Get the application
            $app = $this->getApplication();
            // Get the document object.
            $document = JFactory::getDocument();
            $task = $app->input->get('task', 'list');
            $task = empty($task) ? 'list' : $task;
            $task = ($task == "display") ? 'list' : $task;
            if (strpos($task, ".")) {
                list($controller, $task) = explode(".", $task);
            }
            $this->_task = $task;
        }
        return $this->_task;
    }
    /**
    * This function saves our stuff.
    * 
    * @return null
    */
    protected function taskCancel()
    {
        $this->getApplication()->redirect($this->baselink);
        return true;
    }
    /**
    * This function saves our stuff.
    * 
    * @return null
    */
    protected function taskSave()
    {
        JRequest::checkToken('request') or jexit("JINVALID_TOKEN");
        
        // Get the application
        $app   = $this->getApplication();
        $model = $this->getModel();
        if ($index = $model->store()) {
            $app->enqueueMessage(
                $this->savedMsg(), 'message'
            );
            $model->checkin();
            $app->redirect($this->baselink);
        } else {
            $app->enqueueMessage(
                $this->saveFailedMsg(), 'warning'
            );
            $app->redirect(
                $this->baselink."&task=edit&id=".$app->input->getInt("id")
            );
        }
        return true;
    }
    /**
    * This function saves our stuff.
    * 
    * @return null
    */
    protected function taskPublish()
    {
        $this->getModel()->publish();
        $this->getApplication()->redirect($this->baselink);
        return true;
    }
    /**
    * This function saves our stuff.
    * 
    * @return null
    */
    protected function taskUnpublish()
    {
        $this->getModel()->unpublish();
        $this->getApplication()->redirect($this->baselink);
        return true;
    }
    /**
    * This function saves our stuff.
    * 
    * @return null
    */
    protected function taskCheckin()
    {
        $this->getModel()->checkin();
        $this->getApplication()->redirect($this->baselink);
        return true;
    }
    /**
    * This function saves our stuff and returns a json response
    * 
    * @return null
    */
    protected function taskApply($output = true)
    {
        JRequest::checkToken('request') or jexit("JINVALID_TOKEN");
        // Get the application
        $app   = $this->getApplication();
        $model = $this->getModel();

        if ($index = $model->store()) {
            $json = new JResponseJson(
                get_object_vars($index), 
                $this->savedMsg(),
                false,  // Error
                false    // Ignore Message Queue
            );
        } else {
            $json = new JResponseJson(
                array(), 
                $this->saveFailedMsg(), 
                true,    // Error
                false     // Ignore Message Queue
            );
        }
        if ($output) {
            $this->echoJSON($json);
            return true;
        } else {
            return $json;
        }
    }
    /**
    * This function saves our stuff and returns a json response
    * 
    * @param string $model The model to get
    *
    * @return null
    */
    protected function getModel($model = null)
    {
        if (is_null($this->_model)) {
            $model = empty($model) ? $this->model : $model;
            $this->_model = TimeclockHelpersTimeclock::getModel($this->model);
        }
        return $this->_model;
    }
    /**
    * This function returns the message to show when a controller is saved
    *
    * @return null
    */
    protected function savedMsg()
    {
        return JText::_($this->msgs["saved"]);
    }
    /**
    * This function returns the message to show when a controller is saved
    *
    * @return null
    */
    protected function saveFailedMsg()
    {
        return JText::_($this->msgs["saveFailed"]);
    }
    /**
    * This function returns the message to show when a controller is saved
    *
    * @param mixed $data The data to output
    *
    * @return null
    */
    protected function echoJSON($data)
    {
        $this->getDocument()->setMimeEncoding( 'application/json' );
        $this->setHeader('Content-Disposition','inline;filename="apply.json"');
        echo $data;
        $this->getApplication()->close();
    }
    /**
    * Returns the document
    *
    * @return null
    */
    protected function getDocument()
    {
        return JFactory::getDocument();
    }
    /**
    * Sets a header
    * 
    * @param string $header The header to send
    * @param string $value  The value to send
    *
    * @return null
    */
    protected function setHeader($header, $value)
    {
        return JResponse::setHeader($header, $value);
    }
}