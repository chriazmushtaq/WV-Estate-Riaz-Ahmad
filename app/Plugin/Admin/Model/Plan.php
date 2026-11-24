<?php
class Plan extends AppModel {
    public $cacheSources = false;
    public $validationDomain = 'validation';
    public $actsAs = array('search-master.Searchable');
    public $filterArgs = array('keyword' => array('type' => 'like','field'=>'Plan.name'));
    public $validate = array('name' => array('alphaNumeric' => array('rule' => 'alphaNumericCustom','required' => true,'allowEmpty' => false,'message' => 'Only letters and numbers allowed')),
                            'status' => array('alphaNumeric'=>array('rule' => 'alphaNumeric','required' =>true)));

    public function ensureScheduleTemplateSchema()
    {
        $columns = $this->query("SHOW COLUMNS FROM `plans` LIKE 'total_amount'");
        if(empty($columns))
        {
            $this->query("ALTER TABLE `plans` ADD COLUMN `total_amount` decimal(15,2) NOT NULL DEFAULT 0 AFTER `installment`");
        }
        $this->query("CREATE TABLE IF NOT EXISTS `plan_schedule_rows` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `plan_id` int(11) NOT NULL,
            `payment_type` varchar(30) NOT NULL DEFAULT 'custom',
            `title` varchar(120) NOT NULL,
            `amount_mode` varchar(12) NOT NULL DEFAULT 'fixed',
            `amount_value` decimal(15,4) NOT NULL DEFAULT 0,
            `repeat_count` int(11) NOT NULL DEFAULT 1,
            `interval_months` int(11) NOT NULL DEFAULT 0,
            `sort_order` int(11) NOT NULL DEFAULT 0,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `plan_id` (`plan_id`),
            KEY `sort_order` (`sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8") ;
        // The column may have been added after Cake cached this model schema.
        // Refresh it so total_amount is available in the same request.
        $this->schema(true);
        return true;
    }
}
?>
