<?php
class PlanScheduleRow extends AppModel
{
    public $useTable = 'plan_schedule_rows';
    public $cacheSources = false;
    public $belongsTo = array('Plan');
}
?>
