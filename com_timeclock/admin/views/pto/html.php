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
 * @version    GIT: $Id: 2ca951f3d4bf855d0f6f272fc59dcb457433906e $
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

 
/**
 * HTML view class for pto
 *
 * @category   Timeclock
 * @package    Timeclock
 * @subpackage com_timeclock
 * @author     Scott Price <prices@hugllc.com>
 * @copyright  2014 Hunt Utilities Group, LLC
 * @license    http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link       https://dev.hugllc.com/index.php/Project:ComTimeclock
 */
class TimeclockViewsPtoHtml extends JViewHtml
{
    /** Whether we are adding or editing */
    protected $add = false;
    /** This is the form we might have */
    protected $form = null;
    /**
    * Renders this view
    *
    * @return unknown
    */
    function render()
    {
        $app = JFactory::getApplication();
        $layout = $this->getLayout();
        if ($layout == "add") {
            $this->add = true;
            $layout = "edit";
            $this->setLayout($layout);
        }

        JHTML::stylesheet(
            JURI::base().'/components/com_timeclock/css/timeclock.css', 
            array(), 
            true
        );

        $this->params = JComponentHelper::getParams('com_timeclock');
        $this->state  = $this->model->getState();
        if ($layout == 'edit') {
            if ($this->add) {
                $this->data = $this->model->getNew();
            } else {
                $this->data = $this->model->getItem();
            }
            $this->getForm();
            $this->editToolbar();
        } else {
            //retrieve task list from model
            $this->data = $this->model->listItems();
            $this->_reportListView = new JLayoutFile('entry', __DIR__.'/layouts');
            $this->sortFields = $this->model->checkSortFields($this->getSortFields());
            TimeclockHelpersView::addSubmenu("pto");
            $this->listToolbar();
            $this->sidebar = JHtmlSidebar::render();

        }
        $this->pagination = $this->model->getPagination();
        //display
        return parent::render();
    } 
    /**
    * Adds the toolbar for this view.
    *
    * @return unknown
    */
    protected function listToolbar()
    {
        $actions = TimeclockHelpersTimeclock::getActions();
        // Get the toolbar object instance
        $bar = JToolBar::getInstance('toolbar');
        JToolbarHelper::title(
            JText::_("COM_TIMECLOCK_TIMECLOCK_DEPARTMENTS"), "clock"
        );
        if ($actions->get('core.admin'))
        {
            JToolbarHelper::preferences('com_timeclock');
        }
        if ($actions->get('core.create')) {
            JToolbarHelper::addNew('pto.add');
        }

        if (($actions->get('core.edit')) || ($actions->get('core.edit.own')))
        {
            JToolbarHelper::editList('pto.edit');
            JToolbarHelper::deleteList("Delete Selected Records?", 'pto.delete');
        }
        if ($actions->get('core.edit.state'))
        {
            JToolbarHelper::publish('pto.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('pto.unpublish', 'JTOOLBAR_UNPUBLISH', true);
        }
        JHtmlSidebar::setAction('index.php?option=com_timeclock');

        $options = TimeclockHelpersView::getUsersOptions();
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_USER'),
            'filter_user_id',
            JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.user_id'), true)
        );
        $options = array(
            JHTML::_('select.option', "ACCTUAL", JText::_("COM_TIMECLOCK_AUTOMATIC_ACCRUAL")),
            JHTML::_('select.option', "CARRYOVER", JText::_("COM_TIMECLOCK_CARRYOVER")),
            JHTML::_('select.option', "MANUAL", JText::_("COM_TIMECLOCK_MANUAL")),
            JHTML::_('select.option', "DONATION", JText::_("COM_TIMECLOCK_DONATION")),
        );
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_TYPE'),
            'filter_type',
            JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.type'), true)
        );
        $options = TimeclockHelpersView::getYearOptions();
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_YEAR'),
            'filter_year',
            JHtml::_('select.options', $options, 'value', 'text', $this->state->get('filter.year'), true)
        );
    }
    /**
    * Adds the toolbar for this view.
    *
    * @return unknown
    */
    protected function editToolbar()
    {
        $add = empty($this->data->report_id);
        $title = ($add) ? JText::_("COM_TIMECLOCK_ADD") : JText::_("COM_TIMECLOCK_EDIT");

        JToolbarHelper::title(
            JText::sprintf("COM_TIMECLOCK_DEPARTMENT_EDIT_TITLE", $title), "clock"
        );
        JToolBarHelper::apply("apply");
        JToolBarHelper::save("save");
        JToolBarHelper::cancel("cancel");
    }
    /**
     * Returns an array of fields the table can be sorted by
     *
     * @return  array  Array containing the field name to sort by as the key and display text as value
     *
     * @since   3.0
     */
    protected function getSortFields()
    {
        return array(
            'r.name' => JText::_('COM_TIMECLOCK_NAME'),
            'r.created_by' => JText::_('COM_TIMECLOCK_CREATED_BY'),
            'r.report_id' => JText::_('JGRID_HEADING_ID'),
            'r.published' => JText::_('JSTATUS'),
        );
    }
    /**
     * Returns an JForm object
     *
     * @return  object JForm object for this form
     *
     * @since   3.0
     */
    public function getForm()
    {
        if (!is_object($this->form)) {
            $this->form = JForm::getInstance(
                'report', 
                JPATH_COMPONENT_ADMINISTRATOR."/forms/pto.xml"
            );
        }
        return $this->form;
    }
    /**
     * Returns an JForm object
     *
     * @return  object JForm object for this form
     *
     * @since   3.0
     */
    public function get()
    {
        var_dump(func_get_args());
    }
    
}