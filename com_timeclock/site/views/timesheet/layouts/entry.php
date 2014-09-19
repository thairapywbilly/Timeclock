<?php

defined('_JEXEC') or die;

?>
    <h2><?php print $displayData->name; ?></h2>
    <fieldset id="addhours-<?php print $displayData->project_id; ?>" class="addhours">
    <div style="display: none;" class="alert"></div>
<?php
    $wcomp = (bool)$displayData->params->get("wCompEnable");
    if ($wcomp) {
        $codes = TimeclockHelpersTimeclock::getWCompCodes();
        for ($i = 1; $i <= 6; $i++) {
            $name = "wcCode".$i;
            $code = isset($displayData->$name) ? (int)$displayData->$name : 0;
            if (($code == 0) && ($i > 1)) {
                continue;
            }
            $fname = "hours".$i;
            $hours = isset($displayData->data->$fname) ? $displayData->data->$fname : 0;

            $field = new stdClass();
            $name  = (empty($codes[$code])) ? JText::_("COM_TIMECLOCK_HOURS") : $codes[$code];
            $label = '<label id="'.$fname.'-lbl" for="'.$fname.'" class="required">';
            $star  = '<span class="star">&#160;*</span>';
            $field->label = $label.$name.$star."</label>";
            $field->input  = '<input type="text" size="6" maxsize="6" class="span2 hours" ';
            $field->input .= 'name="hours'.$i.'" value="'.$hours.'" />';
            print TimeclockHelpersView::getFormField($field);
        }
    } else {
        print TimeclockHelpersView::getFormSetH("hours", $displayData->form, $displayData->data);
    }
    
    $field = $displayData->form->getField("notes");
    $field->setValue(isset($displayData->data->notes) ? $displayData->data->notes : "");
    $minimum = '<span class="minchars">'.sprintf(" ".JText::_('COM_TIMECLOCK_WORK_NOTES_MIN_CHARS'), $displayData->params->get("minNoteChars"))."</span>";
    print TimeclockHelpersView::getFormField(
        (object)array(
            "label" => $field->label,
            "input" => $field->input."<br/>".$minimum
        )
    );
?>
        <button type="button" name="apply" onClick="Addhours.submitform('apply');">Save</button>
        <button type="button" name="save" onClick="Addhours.submitform('save');">Save &amp; Close</button>
        <input type="hidden" name="created" value="<?php print isset($displayData->created) ? $displayData->created : 0; ?>" />
        <input type="hidden" name="created_by" value="<?php print isset($displayData->created_by) ? $displayData->created_by : 0; ?>" />
        <input type="hidden" name="project_id" value="<?php print $displayData->project_id; ?>" />
        <input type="hidden" name="timesheet_id" value="<?php print isset($displayData->data->timesheet_id) ? $displayData->data->timesheet_id : ""; ?>" />
    </fieldset>