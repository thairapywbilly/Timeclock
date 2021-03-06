<?php
    defined( '_JEXEC' ) or die( 'Restricted access' );
    $live    = empty($displayData->report_id);
    if ($live) {
        $model = TimeclockHelpersTimeclock::getModel("project");
        $options = $model->getOptions(
            array("p.published=1", "p.type = 'CATEGORY'"),
            "p.name ASC"
        );
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_CATEGORY'),
            'filter_category',
            JHtml::_('select.options', $options, 'value', 'text', $displayData->category, true)
        );
        $options = array(
            JHTML::_('select.option', "PROJECT", JText::_("COM_TIMECLOCK_PROJECT")),
            JHTML::_('select.option', "PTO", JText::_("COM_TIMECLOCK_PTO")),
            JHTML::_('select.option', "HOLIDAY", JText::_("COM_TIMECLOCK_HOLIDAY")),
            JHTML::_('select.option', "UNPAID", JText::_("COM_TIMECLOCK_VOLUNTEER")),
        );
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_PROJECT_TYPE'),
            'filter_project_type',
            JHtml::_('select.options', $options, 'value', 'text', $displayData->project_type, true)
        );

        $model = TimeclockHelpersTimeclock::getModel("department");
        $options = $model->getOptions(
            array("d.published=1"),
            "d.name ASC"
        );
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_DEPARTMENT'),
            'filter_department',
            JHtml::_('select.options', $options, 'value', 'text', $displayData->department, true)
        );

        $model = TimeclockHelpersTimeclock::getModel("customer");
        $options = $model->getOptions(
            array("c.published=1"),
            "c.company ASC"
        );
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_CUSTOMER'),
            'filter_customer',
            JHtml::_('select.options', $options, 'value', 'text', $displayData->customer, true)
        );
        $options = TimeclockHelpersView::getUsersOptions();
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_USER_MANAGER'),
            'filter_user_manager_id',
            JHtml::_('select.options', $options, 'value', 'text', $displayData->user_manager_id, true)
        );
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_PROJECT_MANAGER'),
            'filter_proj_manager_id',
            JHtml::_('select.options', $options, 'value', 'text', $displayData->proj_manager_id, true)
        );
        JHtmlSidebar::addFilter(
            JText::_('COM_TIMECLOCK_SELECT_USER'),
            'filter_user_id',
            JHtml::_('select.options', $options, 'value', 'text', $displayData->user_id, true)
        );
        $typeoptions = array(
            JHTML::_('select.option', "hours", JText::_("COM_TIMECLOCK_HOURS")),
            JHTML::_('select.option', "money", JText::_("COM_TIMECLOCK_MONEY")),
        );

    }
    $model = empty($displayData->type) ? "report" : $displayData->type;
    $model = TimeclockHelpersTimeclock::getModel($model);
    $options = $model->getReportOptions();
    JHtmlSidebar::addFilter(
        JText::_('COM_TIMECLOCK_SELECT_SAVED_REPORT'),
        'report_id',
        JHtml::_('select.options', $options, 'value', 'text', $displayData->report_id, true)
    );
?>
<div class="reportcontrol row">
    <div class="row-fluid">
        <div class="span6 dates">
            <?php if ($live) : ?>
            <h4 class="page-header"><?php echo JText::_('COM_TIMECLOCK_REPORT_DATES');?></h4>
            <h5><?php echo JText::_('COM_TIMECLOCK_FROM');?></h5>
            <?php print JHtml::_("calendar", $displayData->start, "start", "startDate", '%Y-%m-%d', array("class" => "")); ?>
            <h5><?php echo JText::_('COM_TIMECLOCK_to');?></h5>
            <?php print JHtml::_("calendar", $displayData->end, "end", "endDate", '%Y-%m-%d', array("class" => "")); ?>
            <hr class="hr-condensed" />
            <?php if ($displayData->datatype) { ?>
            Show: <?php print JHTML::_('select.genericlist', $typeoptions, 'datatype', 'class="inputbox"', 'value', 'text', $displayData->datatype); ?>
            <hr class="hr-condensed" />
            <?php } ?>
            <button type="submit" class="hidden-phone">Submit</button>
            <?php endif; ?>
        </div>
        <div class="span4 filters">
            <h4 class="page-header"><?php echo JText::_('JSEARCH_FILTER_LABEL');?></h4>
            <?php foreach (JHTMLSidebar::getFilters() as $filter) : ?>
                    <label for="<?php echo $filter['name']; ?>" class="element-invisible"><?php echo $filter['label']; ?></label>
                    <select name="<?php echo $filter['name']; ?>" id="<?php echo $filter['name']; ?>" class="span12 small" onchange="this.form.submit()">
                            <?php if (!$filter['noDefault']) : ?>
                                    <option value=""><?php echo $filter['label']; ?></option>
                            <?php endif; ?>
                            <?php echo $filter['options']; ?>
                    </select>
            <?php endforeach; ?>
            <hr class="hr-condensed hidden-desktop" />
        </div>
        <div class="span2 controls">
            <h4 class="page-header"><?php echo JText::_('COM_TIMECLOCK_CONTROLS');?></h4>
            <button class="livedata" type="button" onclick="Report.save();"><?php print JText::_("COM_TIMECLOCK_SAVE_REPORT"); ?></button>
            <button class="reportdata noreport" type="button" onclick="Report.livedata();"><?php print JText::_("COM_TIMECLOCK_LIVE_DATA"); ?></button>
            <button class="nonzero" type="button" onclick="Report.toggleZero();"><?php print JText::_("COM_TIMECLOCK_SHOW_ZERO"); ?></button>
            <button class="zero" type="button" onclick="Report.toggleZero();"><?php print JText::_("COM_TIMECLOCK_HIDE_ZERO"); ?></button>
            <button class="nonempty" type="button" onclick="Report.toggleEmpty();"><?php print JText::_("COM_TIMECLOCK_SHOW_EMPTY"); ?></button>
            <button class="empty" type="button" onclick="Report.toggleEmpty();"><?php print JText::_("COM_TIMECLOCK_HIDE_EMPTY"); ?></button>
            <hr class="hr-condensed hidden-desktop" />
        </div>
    </div>
    <div class="hidden-desktop">
        <button type="submit">Submit</button>
    </div>
</div>
