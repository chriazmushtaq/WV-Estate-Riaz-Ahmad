<?php
App::uses('AdminAppController', 'Admin.Controller');
App::uses('CakeNumber', 'Utility');
App::uses('CakeEmail', 'Network/Email');
App::uses('Router', 'Routing');
App::uses('Security', 'Utility');

class RealestatelettersController extends AdminAppController
{
    public $uses = array('Admin.Realestateletter');
    public $helpers = array('Html', 'Form', 'Session', 'Paginator', 'Tinymce', 'Js' => array('Jquery'));
    public $components = array('Session', 'Paginator', 'search-master.Prg', 'CustomFunction');
    public $presetVars = true;
    var $paginate = array('page' => 1, 'order' => array('Realestateletter.id' => 'desc'));
    protected $_letterTypeArr = array();
    protected $_autoLetterTypeArr = array();
    protected $_lettersTableName = 'real_estate_letters';
    protected $_lettersTrashTableName = 'real_estate_letters_trash';
    protected $_agencyLogoUrlCache = array();
    protected $_agencyUserInfoCache = array();

    public function beforeFilter()
    {
        $this->_letterTypeArr = $this->_letterTypes();
        $this->_autoLetterTypeArr = $this->_autoLetterTypes();
        if($this->_shouldRunControllerBootstrap('realestateletters.schema', '2026-06-21.approval.1'))
        {
            $this->_ensureLettersSchema();
            $this->_ensureLettersTrashSchema();
            $this->_ensureLetterApprovalColumns();
            $this->_ensureLetterReferenceSettingsColumns();
            $this->_ensureTemplateSignatureColumns();
            $this->_ensureDefaultLetterTemplates();
            $this->_ensureLetterNotificationTemplates();
            $this->_ensureAdvancedTemporaryPossessionTemplate();
        }
        if($this->_shouldRunControllerBootstrap('realestateletters.booking_payment_amount_placeholder', '2026-06-10.3'))
        {
            $this->_ensureBookingPaymentAmountPlaceholder();
        }
        if($this->_shouldRunControllerBootstrap('realestateletters.booking_occupation_placeholder', '2026-08-19.2'))
        {
            $this->_ensureBookingOccupationPlaceholder();
        }
        parent::beforeFilter();
        $this->set('letterTypeArr', $this->_letterTypeArr);
        $this->set('autoLetterTypeArr', $this->_autoLetterTypeArr);
        $this->set('letterStatusArr', array('Published' => __('Issued'), 'Unpublished' => __('Cancle')));
        $this->set('chargeWorkStatusArr', $this->_chargeWorkStatusOptions());
        $this->set('lettersCanUseAgencyFilters', $this->_lettersCanUseAgencyFilters());
        $this->set('lettersProjectName', $this->_lettersProjectList());
    }

    protected function _autoLetterTypes()
    {
        return array(
            'RLT_WELCOME' => __('Welcome Letter'),
            'RLT_BOOKING' => __('Booking Letter'),
            'RLT_CHARGE' => __('Deal Charges Agreement'),
            'RLT_DEAL_AGREEMENT' => __('Deal Agreement Ashtam'),
            'RLT_REFUND_CLEARANCE' => __('Refund Clearance / No Claim')
        );
    }

    protected function _letterTypes()
    {
        return array(
            'RLT_WELCOME' => __('Welcome Letter'),
            'RLT_BOOKING' => __('Booking Letter'),
            'RLT_CHARGE' => __('Deal Charges Agreement'),
            'RLT_DEAL_AGREEMENT' => __('Deal Agreement Ashtam'),
            'RLT_REFUND' => __('Refund Agreement'),
            'RLT_REFUND_CLEARANCE' => __('Refund Clearance / No Claim'),
            'RLT_RENTAL_AGREEMENT' => __('Rental Agreement'),
            'RLT_RENTAL_RECEIPT' => __('Rental Receipt'),
            'RLT_DEMARCATION' => __('Demarcation Letter'),
            'RLT_DESIGN_MAP_APPROVAL' => __('Design / Map Approval Letter'),
            'RLT_NOC' => __('NOC Letter'),
            'RLT_ALLOT' => __('Allotment Letter'),
            'RLT_DEMAND' => __('Payment Demand Notice'),
            'RLT_RECEIPT' => __('Payment Acknowledgement'),
            'RLT_POSSESSION' => __('Possession Letter'),
            'RLT_TRANSFER' => __('Transfer / Mutation Letter'),
            'RLT_CANCEL' => __('Cancellation Letter'),
            'RLT_FINAL_CANCEL' => __('Final Cancellation')
        );
    }
    protected function _chargeWorkStatusOptions()
    {
        return array(
            'In Progress' => __('In Progress'),
            'Handover To Client' => __('Handover To Client'),
            'Completed' => __('Completed'),
            'Pending' => __('Pending')
        );
    }

    protected function _chargeAgreementFieldNames()
    {
        return array(
            'charge_work_details',
            'charge_work_status',
            'charge_payment_method',
            'charge_payment_deadline',
            'agreement_place',
            'agency_representative_name',
            'agency_representative_designation',
            'witness_one_name',
            'witness_one_cnic',
            'witness_one_contact',
            'witness_one_address',
            'witness_two_name',
            'witness_two_cnic',
            'witness_two_contact',
            'witness_two_address'
        );
    }

    protected function _dealAgreementFieldNames()
    {
        return array(
            'agreement_payment_plan',
            'agreement_penalty_rate',
            'agreement_possession_months',
            'agreement_extra_work_amount',
            'agreement_extra_work_schedule',
            'agreement_cancellation_deduction_rate',
            'agreement_refund_period',
            'agreement_court_jurisdiction',
            'agreement_property_block',
            'agreement_property_unit_no',
            'agreement_property_floor',
            'agreement_property_covered_area',
            'agreement_property_location',
            'agency_representative_father',
            'agency_representative_cnic'
        );
    }

    protected function _agreementFieldNames()
    {
        return array_merge($this->_chargeAgreementFieldNames(), $this->_dealAgreementFieldNames());
    }

    protected function _ensureChargeAgreementColumns($tableName)
    {
        $tableName = trim((string)$tableName);
        if ($tableName === '')
        {
            return;
        }
        $columns = array(
            'charge_work_details' => "ALTER TABLE `{$tableName}` ADD COLUMN `charge_work_details` longtext NULL AFTER `description`",
            'charge_work_status' => "ALTER TABLE `{$tableName}` ADD COLUMN `charge_work_status` varchar(60) DEFAULT 'In Progress' AFTER `charge_work_details`",
            'charge_payment_method' => "ALTER TABLE `{$tableName}` ADD COLUMN `charge_payment_method` varchar(120) DEFAULT '' AFTER `charge_work_status`",
            'charge_payment_deadline' => "ALTER TABLE `{$tableName}` ADD COLUMN `charge_payment_deadline` varchar(120) DEFAULT '' AFTER `charge_payment_method`",
            'agreement_place' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_place` varchar(191) DEFAULT '' AFTER `charge_payment_deadline`",
            'agency_representative_name' => "ALTER TABLE `{$tableName}` ADD COLUMN `agency_representative_name` varchar(191) DEFAULT '' AFTER `agreement_place`",
            'agency_representative_designation' => "ALTER TABLE `{$tableName}` ADD COLUMN `agency_representative_designation` varchar(120) DEFAULT '' AFTER `agency_representative_name`",
            'witness_one_name' => "ALTER TABLE `{$tableName}` ADD COLUMN `witness_one_name` varchar(191) DEFAULT '' AFTER `agency_representative_designation`",
            'witness_one_cnic' => "ALTER TABLE `{$tableName}` ADD COLUMN `witness_one_cnic` varchar(60) DEFAULT '' AFTER `witness_one_name`",
            'witness_one_contact' => "ALTER TABLE `{$tableName}` ADD COLUMN `witness_one_contact` varchar(120) DEFAULT '' AFTER `witness_one_cnic`",
            'witness_one_address' => "ALTER TABLE `{$tableName}` ADD COLUMN `witness_one_address` varchar(255) DEFAULT '' AFTER `witness_one_contact`",
            'witness_two_name' => "ALTER TABLE `{$tableName}` ADD COLUMN `witness_two_name` varchar(191) DEFAULT '' AFTER `witness_one_address`",
            'witness_two_cnic' => "ALTER TABLE `{$tableName}` ADD COLUMN `witness_two_cnic` varchar(60) DEFAULT '' AFTER `witness_two_name`",
            'witness_two_contact' => "ALTER TABLE `{$tableName}` ADD COLUMN `witness_two_contact` varchar(120) DEFAULT '' AFTER `witness_two_cnic`",
            'witness_two_address' => "ALTER TABLE `{$tableName}` ADD COLUMN `witness_two_address` varchar(255) DEFAULT '' AFTER `witness_two_contact`"
        );
        foreach ($columns as $sql)
        {
            try
            {
                $this->Realestateletter->query($sql);
            }
            catch (Exception $e)
            {
                // Ignore if the column already exists.
            }
        }
    }

    protected function _ensureDealAgreementColumns($tableName)
    {
        $tableName = trim((string)$tableName);
        if ($tableName === '')
        {
            return;
        }
        $columns = array(
            'agreement_payment_plan' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_payment_plan` varchar(191) DEFAULT '' AFTER `agency_representative_designation`",
            'agreement_penalty_rate' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_penalty_rate` varchar(60) DEFAULT '0.1%' AFTER `agreement_payment_plan`",
            'agreement_possession_months' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_possession_months` varchar(80) DEFAULT '36 Ù…Ø§Û' AFTER `agreement_penalty_rate`",
            'agreement_extra_work_amount' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_extra_work_amount` varchar(80) DEFAULT '' AFTER `agreement_possession_months`",
            'agreement_extra_work_schedule' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_extra_work_schedule` varchar(191) DEFAULT '' AFTER `agreement_extra_work_amount`",
            'agreement_cancellation_deduction_rate' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_cancellation_deduction_rate` varchar(60) DEFAULT '20%' AFTER `agreement_extra_work_schedule`",
            'agreement_refund_period' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_refund_period` varchar(80) DEFAULT '90 Ø§ÛŒØ§Ù…' AFTER `agreement_cancellation_deduction_rate`",
            'agreement_court_jurisdiction' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_court_jurisdiction` varchar(120) DEFAULT 'Ù„Ø§ÛÙˆØ±' AFTER `agreement_refund_period`",
            'agreement_property_block' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_property_block` varchar(191) DEFAULT '' AFTER `agreement_court_jurisdiction`",
            'agreement_property_unit_no' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_property_unit_no` varchar(120) DEFAULT '' AFTER `agreement_property_block`",
            'agreement_property_floor' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_property_floor` varchar(120) DEFAULT '' AFTER `agreement_property_unit_no`",
            'agreement_property_covered_area' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_property_covered_area` varchar(120) DEFAULT '' AFTER `agreement_property_floor`",
            'agreement_property_location' => "ALTER TABLE `{$tableName}` ADD COLUMN `agreement_property_location` varchar(255) DEFAULT '' AFTER `agreement_property_covered_area`",
            'agency_representative_father' => "ALTER TABLE `{$tableName}` ADD COLUMN `agency_representative_father` varchar(191) DEFAULT '' AFTER `agency_representative_name`",
            'agency_representative_cnic' => "ALTER TABLE `{$tableName}` ADD COLUMN `agency_representative_cnic` varchar(60) DEFAULT '' AFTER `agency_representative_father`"
        );
        foreach ($columns as $sql)
        {
            try
            {
                $this->Realestateletter->query($sql);
            }
            catch (Exception $e)
            {
                // Ignore if the column already exists.
            }
        }
    }

      protected function _ensureLettersSchema()
      {
          $tableSql = "CREATE TABLE IF NOT EXISTS `{$this->_lettersTableName}` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `deal_id` int(11) NOT NULL,
              `client_id` int(11) DEFAULT NULL,
              `template_id` int(11) DEFAULT NULL,
              `user_id` int(11) DEFAULT NULL,
              `refund_id` int(11) DEFAULT NULL,
              `letter_type` varchar(30) DEFAULT NULL,
              `reference_no` varchar(60) DEFAULT NULL,
              `title` varchar(255) DEFAULT NULL,
            `letter_date` date DEFAULT NULL,
            `description` longtext,
            `charge_work_details` longtext NULL,
            `charge_work_status` varchar(60) DEFAULT 'In Progress',
            `charge_payment_method` varchar(120) DEFAULT '',
            `charge_payment_deadline` varchar(120) DEFAULT '',
            `agreement_place` varchar(191) DEFAULT '',
            `agency_representative_name` varchar(191) DEFAULT '',
            `agency_representative_father` varchar(191) DEFAULT '',
            `agency_representative_cnic` varchar(60) DEFAULT '',
            `agency_representative_designation` varchar(120) DEFAULT '',
            `agreement_payment_plan` varchar(191) DEFAULT '',
            `agreement_penalty_rate` varchar(60) DEFAULT '0.1%%',
            `agreement_possession_months` varchar(80) DEFAULT '36 Ù…Ø§Û',
            `agreement_extra_work_amount` varchar(80) DEFAULT '',
            `agreement_extra_work_schedule` varchar(191) DEFAULT '',
            `agreement_cancellation_deduction_rate` varchar(60) DEFAULT '20%%',
            `agreement_refund_period` varchar(80) DEFAULT '90 Ø§ÛŒØ§Ù…',
            `agreement_court_jurisdiction` varchar(120) DEFAULT 'Ù„Ø§ÛÙˆØ±',
            `agreement_property_block` varchar(191) DEFAULT '',
            `agreement_property_unit_no` varchar(120) DEFAULT '',
            `agreement_property_floor` varchar(120) DEFAULT '',
            `agreement_property_covered_area` varchar(120) DEFAULT '',
            `agreement_property_location` varchar(255) DEFAULT '',
            `witness_one_name` varchar(191) DEFAULT '',
            `witness_one_cnic` varchar(60) DEFAULT '',
            `witness_one_contact` varchar(120) DEFAULT '',
            `witness_one_address` varchar(255) DEFAULT '',
            `witness_two_name` varchar(191) DEFAULT '',
            `witness_two_cnic` varchar(60) DEFAULT '',
            `witness_two_contact` varchar(120) DEFAULT '',
            `witness_two_address` varchar(255) DEFAULT '',
            `status` varchar(15) NOT NULL DEFAULT 'Published',
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `deal_id` (`deal_id`),
            KEY `client_id` (`client_id`),
            KEY `template_id` (`template_id`),
            KEY `user_id` (`user_id`),
            KEY `letter_type` (`letter_type`)
        ) ENGINE=InnoDB %s";
        $sqlVariants = array(
            sprintf($tableSql, "DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"),
            sprintf($tableSql, "DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"),
            sprintf($tableSql, "")
        );
        foreach ($sqlVariants as $sql)
        {
            try
            {
                $this->Realestateletter->query($sql);
            }
            catch (Exception $e)
            {
                // Try the next compatible SQL variant.
            }
            if ($this->_lettersTableExists())
            {
                break;
            }
        }
          if (!$this->_lettersTableExists())
          {
              return;
          }
          try
          {
              $this->Realestateletter->query("ALTER TABLE `{$this->_lettersTableName}` ADD COLUMN `refund_id` int(11) DEFAULT NULL AFTER `user_id`");
          }
          catch (Exception $e)
          {
              // Ignore if column already exists.
          }
          $this->_ensureChargeAgreementColumns($this->_lettersTableName);
          $this->_ensureDealAgreementColumns($this->_lettersTableName);
          $this->_ensureLetterApprovalColumns();
          try
          {
              $this->Realestateletter->schema(true);
          }
          catch (Exception $e)
        {
            // Keep request flow alive even if schema refresh fails.
        }
    }

    protected function _ensureLetterApprovalColumns()
    {
        $columns = array(
            'approval_status' => "ALTER TABLE `{$this->_lettersTableName}` ADD COLUMN `approval_status` varchar(30) NOT NULL DEFAULT 'Issued' AFTER `status`",
            'approved_by' => "ALTER TABLE `{$this->_lettersTableName}` ADD COLUMN `approved_by` int(11) NOT NULL DEFAULT 0 AFTER `approval_status`",
            'approved_at' => "ALTER TABLE `{$this->_lettersTableName}` ADD COLUMN `approved_at` datetime DEFAULT NULL AFTER `approved_by`",
            'issued_by' => "ALTER TABLE `{$this->_lettersTableName}` ADD COLUMN `issued_by` int(11) NOT NULL DEFAULT 0 AFTER `approved_at`",
            'issued_at' => "ALTER TABLE `{$this->_lettersTableName}` ADD COLUMN `issued_at` datetime DEFAULT NULL AFTER `issued_by`"
        );
        foreach($columns as $sql)
        {
            try
            {
                $this->Realestateletter->query($sql);
            }
            catch(Exception $e)
            {
                // Ignore if the column already exists.
            }
        }
        try
        {
            $this->Realestateletter->schema(true);
        }
        catch(Exception $e)
        {
        }
    }

      protected function _ensureLettersTrashSchema()
      {
          $tableSql = "CREATE TABLE IF NOT EXISTS `{$this->_lettersTrashTableName}` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `letter_id` int(11) NOT NULL,
              `deal_id` int(11) DEFAULT NULL,
              `client_id` int(11) DEFAULT NULL,
              `template_id` int(11) DEFAULT NULL,
              `user_id` int(11) DEFAULT NULL,
              `project_id` int(11) NOT NULL DEFAULT 0,
              `refund_id` int(11) DEFAULT NULL,
              `letter_type` varchar(30) DEFAULT NULL,
              `reference_no` varchar(60) DEFAULT NULL,
            `title` varchar(255) DEFAULT NULL,
            `letter_date` date DEFAULT NULL,
            `description` longtext,
            `charge_work_details` longtext NULL,
            `charge_work_status` varchar(60) DEFAULT 'In Progress',
            `charge_payment_method` varchar(120) DEFAULT '',
            `charge_payment_deadline` varchar(120) DEFAULT '',
            `agreement_place` varchar(191) DEFAULT '',
            `agency_representative_name` varchar(191) DEFAULT '',
            `agency_representative_father` varchar(191) DEFAULT '',
            `agency_representative_cnic` varchar(60) DEFAULT '',
            `agency_representative_designation` varchar(120) DEFAULT '',
            `agreement_payment_plan` varchar(191) DEFAULT '',
            `agreement_penalty_rate` varchar(60) DEFAULT '0.1%%',
            `agreement_possession_months` varchar(80) DEFAULT '36 Ù…Ø§Û',
            `agreement_extra_work_amount` varchar(80) DEFAULT '',
            `agreement_extra_work_schedule` varchar(191) DEFAULT '',
            `agreement_cancellation_deduction_rate` varchar(60) DEFAULT '20%%',
            `agreement_refund_period` varchar(80) DEFAULT '90 Ø§ÛŒØ§Ù…',
            `agreement_court_jurisdiction` varchar(120) DEFAULT 'Ù„Ø§ÛÙˆØ±',
            `agreement_property_block` varchar(191) DEFAULT '',
            `agreement_property_unit_no` varchar(120) DEFAULT '',
            `agreement_property_floor` varchar(120) DEFAULT '',
            `agreement_property_covered_area` varchar(120) DEFAULT '',
            `agreement_property_location` varchar(255) DEFAULT '',
            `witness_one_name` varchar(191) DEFAULT '',
            `witness_one_cnic` varchar(60) DEFAULT '',
            `witness_one_contact` varchar(120) DEFAULT '',
            `witness_one_address` varchar(255) DEFAULT '',
            `witness_two_name` varchar(191) DEFAULT '',
            `witness_two_cnic` varchar(60) DEFAULT '',
            `witness_two_contact` varchar(120) DEFAULT '',
            `witness_two_address` varchar(255) DEFAULT '',
            `status` varchar(15) NOT NULL DEFAULT 'Published',
            `deleted_by` int(11) NOT NULL DEFAULT 0,
            `deleted_at` datetime DEFAULT NULL,
            `created` datetime DEFAULT NULL,
            `modified` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_letter_id` (`letter_id`),
            KEY `project_id` (`project_id`),
            KEY `deal_id` (`deal_id`),
            KEY `letter_type` (`letter_type`),
            KEY `reference_no` (`reference_no`),
            KEY `deleted_at` (`deleted_at`)
        ) ENGINE=InnoDB %s";
        $sqlVariants = array(
            sprintf($tableSql, "DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci"),
            sprintf($tableSql, "DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci"),
            sprintf($tableSql, "")
        );
          foreach ($sqlVariants as $sql)
          {
              try
              {
                  $this->Realestateletter->query($sql);
              }
              catch (Exception $e)
              {
                  // Try the next compatible SQL variant.
              }
          }
          try
          {
              $this->Realestateletter->query("ALTER TABLE `{$this->_lettersTrashTableName}` ADD COLUMN `refund_id` int(11) DEFAULT NULL AFTER `project_id`");
          }
          catch (Exception $e)
          {
              // Ignore if column already exists.
          }
          $this->_ensureChargeAgreementColumns($this->_lettersTrashTableName);
          $this->_ensureDealAgreementColumns($this->_lettersTrashTableName);
      }

    protected function _ensureTemplateSignatureColumns()
    {
        try
        {
            $this->loadModel('Template');
        }
        catch (Exception $e)
        {
            return;
        }
        $columns = array(
            'ceo_signature' => "ALTER TABLE `templates` ADD COLUMN `ceo_signature` varchar(255) DEFAULT '' AFTER `is_new`",
            'ceo_signature_dir' => "ALTER TABLE `templates` ADD COLUMN `ceo_signature_dir` varchar(120) DEFAULT 'letter_signatures' AFTER `ceo_signature`",
            'chairman_signature' => "ALTER TABLE `templates` ADD COLUMN `chairman_signature` varchar(255) DEFAULT '' AFTER `ceo_signature_dir`",
            'chairman_signature_dir' => "ALTER TABLE `templates` ADD COLUMN `chairman_signature_dir` varchar(120) DEFAULT 'letter_signatures' AFTER `chairman_signature`"
        );
        foreach($columns as $sql)
        {
            try
            {
                $this->Template->query($sql);
            }
            catch(Exception $e)
            {
                // Ignore if the column already exists.
            }
        }
        try
        {
            $this->Template->schema(true);
        }
        catch(Exception $e)
        {
            // Keep normal letter flow safe if schema refresh fails.
        }
    }

    protected function _lettersTableExists()
    {
        try
        {
            $result = $this->Realestateletter->query("SHOW TABLES LIKE '{$this->_lettersTableName}'");
            return !empty($result);
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    protected function _ensureLettersMenuPermission()
    {
        try
        {
            $Page = ClassRegistry::init('Page');
            $PageRight = ClassRegistry::init('PageRight');
            $Ugroup = ClassRegistry::init('Ugroup');
            $rootId = 0;
            $rootExisting = $Page->find('first', array(
                'fields' => array('Page.id'),
                'conditions' => array(
                    'LOWER(Page.controller_name)' => 'realestateletters',
                    'LOWER(Page.action_name)' => 'dashboard',
                    'Page.parent_id' => 0
                ),
                'recursive' => -1
            ));
            $rootData = array(
                'model_name' => 'Letters',
                'page_name' => 'Letters',
                'controller_name' => 'Realestateletters',
                'action_name' => 'dashboard',
                'icon' => 'fa fa-file-text-o',
                'parent_id' => 0,
                'ordering' => 16,
                'published' => 'Yes',
                'sel_name' => ''
            );
            if (!empty($rootExisting['Page']['id']))
            {
                $rootId = (int)$rootExisting['Page']['id'];
                $rootData['id'] = $rootId;
                $Page->save(array('Page' => $rootData));
            }
            else
            {
                $Page->create();
                if ($Page->save(array('Page' => $rootData)))
                {
                    $rootId = (int)$Page->id;
                }
            }
            if ($rootId <= 0)
            {
                return;
            }

            $menuPageIds = array($rootId);
            $children = array(
                array('name' => 'Dashboard', 'action' => 'dashboard', 'icon' => 'fa fa-dashboard', 'ordering' => 1),
                array('name' => 'Manage Letters', 'action' => 'index', 'icon' => 'fa fa-file-text-o', 'ordering' => 2),
                array('name' => 'Manage Templates', 'action' => 'templates', 'icon' => 'fa fa-files-o', 'ordering' => 3),
                array('name' => 'Letters Trash', 'action' => 'trash', 'icon' => 'fa fa-trash', 'ordering' => 4)
            );
            foreach ($children as $child)
            {
                $childExisting = $Page->find('first', array(
                    'fields' => array('Page.id'),
                    'conditions' => array(
                        'LOWER(Page.controller_name)' => 'realestateletters',
                        'LOWER(Page.action_name)' => strtolower($child['action']),
                        'Page.parent_id' => $rootId,
                        'Page.id <>' => $rootId
                    ),
                    'recursive' => -1
                ));
                $childData = array(
                    'model_name' => 'Letters',
                    'page_name' => $child['name'],
                    'controller_name' => 'Realestateletters',
                    'action_name' => $child['action'],
                    'icon' => $child['icon'],
                    'parent_id' => $rootId,
                    'ordering' => $child['ordering'],
                    'published' => 'Yes',
                    'sel_name' => ''
                );
                if (!empty($childExisting['Page']['id']))
                {
                    $childData['id'] = (int)$childExisting['Page']['id'];
                    $Page->save(array('Page' => $childData));
                    $menuPageIds[] = (int)$childExisting['Page']['id'];
                }
                else
                {
                    $Page->create();
                    if ($Page->save(array('Page' => $childData)))
                    {
                        $menuPageIds[] = (int)$Page->id;
                    }
                }
            }

            $groups = $Ugroup->find('list', array('fields' => array('id', 'id')));
            foreach ($groups as $groupId)
            {
                $groupId = (int)$groupId;
                if ($groupId <= 0)
                {
                    continue;
                }
                foreach ($menuPageIds as $pageId)
                {
                    $exists = $PageRight->find('count', array(
                        'conditions' => array(
                            'PageRight.page_id' => (int)$pageId,
                            'PageRight.ugroup_id' => $groupId
                        ),
                        'recursive' => -1
                    ));
                    if ($exists > 0)
                    {
                        continue;
                    }
                    $PageRight->create();
                    $PageRight->save(array('PageRight' => array(
                        'page_id' => (int)$pageId,
                        'ugroup_id' => $groupId,
                        'view_right' => 1
                    )));
                }
            }
        }
        catch (Exception $e)
        {
            // Permission/menu bootstrap should never break normal page flow.
        }
    }

    protected function _finalCancellationTemplateBody()
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINAL DEMAND: Deal Cancellation Warning</title>
    <style>
        @page{size:A4;margin:5mm;}
        *{box-sizing:border-box;}
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;color:#222;line-height:1.3;background:#f8fafc;margin:0;padding:12px 8px;-webkit-font-smoothing:antialiased;}
        .container{width:198mm;max-width:100%;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-top:3px solid #8c1d1d;box-shadow:0 6px 14px rgba(0,0,0,.045);padding:15px 18px;box-sizing:border-box;position:relative;overflow:hidden;}
        .container:before{content:"FINAL NOTICE";position:absolute;top:51%;left:50%;transform:translate(-50%,-50%) rotate(-45deg);font-size:62px;font-weight:900;color:rgba(140,29,29,.022);pointer-events:none;z-index:1;white-space:nowrap;letter-spacing:0;}
        .container>*{position:relative;z-index:2;}
        .header{display:table;width:100%;border-bottom:1px solid #d9e1ec;padding-bottom:7px;margin-bottom:7px;}
        .logo-section{display:table-cell;vertical-align:middle;width:72px;}
        .logo-placeholder{width:52px;height:auto;max-height:44px;object-fit:contain;}
        .company-details{display:table-cell;vertical-align:middle;text-align:right;}
        .company-name{font-size:16px;font-weight:800;text-transform:uppercase;color:#8c1d1d;margin:0 0 2px 0;letter-spacing:0;}
        .company-info{font-size:8.8px;color:#555;margin:0;letter-spacing:0;line-height:1.25;}
        .meta-section{display:table;width:100%;margin-bottom:7px;font-size:9.8px;border-bottom:1px dashed #e2e8f0;padding-bottom:4px;}
        .meta-col-left{display:table-cell;text-align:left;color:#555;}
        .meta-col-right{display:table-cell;text-align:right;color:#555;}
        .meta-section strong{color:#222;}
        .warning-banner{background:#fff5f5;border-left:3px solid #8c1d1d;padding:6px 8px;margin-bottom:7px;border-radius:0 2px 2px 0;}
        .warning-title{color:#8c1d1d;font-size:14px;font-weight:800;margin:0 0 1px 0;letter-spacing:0;text-transform:uppercase;}
        .warning-subtitle{font-size:9.2px;font-weight:bold;color:#5c1313;margin:0;letter-spacing:0;text-transform:uppercase;}
        .client-info{background:#fbfcfd;border:1px solid #e2e8f0;border-radius:2px;padding:6px 8px;margin-bottom:7px;font-size:10.2px;}
        .client-title{font-size:8px;text-transform:uppercase;color:#8c1d1d;margin:0 0 2px 0;letter-spacing:0;font-weight:800;}
        .client-name{font-size:11.5px;font-weight:700;color:#000;margin:0 0 1px 0;}
        .client-address{color:#555;line-height:1.25;}
        .letter-body{font-family:Georgia,Cambria,"Times New Roman",Times,serif;font-size:10.7px;text-align:justify;color:#2c2c2c;margin-bottom:7px;}
        .letter-body p{margin:0 0 4px 0;line-height:1.3;}
        .highlight-text{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-weight:700;color:#000;padding:0 2px;text-decoration:underline;}
        .table-container{margin:5px 0;border:1px solid #e2e8f0;border-radius:2px;overflow:hidden;}
        .financial-summary{width:100%;border-collapse:collapse;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;font-size:9.6px;}
        .financial-summary th{background:#f8fafc;border-bottom:1px solid #d9e1ec;color:#222;padding:4px 5px;text-align:left;font-weight:700;text-transform:uppercase;font-size:8.2px;letter-spacing:0;}
        .financial-summary td{border-bottom:1px solid #e2e8f0;padding:4px 5px;color:#222;}
        .financial-summary tr:last-child td{border-bottom:none;}
        .alert-box{background:#fff5f5;border:1px solid rgba(140,29,29,.2);border-left:3px solid #8c1d1d;border-radius:2px;padding:6px 8px;margin:5px 0;}
        .alert-box h4{color:#8c1d1d;margin:0 0 3px 0;font-size:9.8px;font-weight:800;text-transform:uppercase;letter-spacing:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}
        .alert-box p{margin:0 0 4px 0;font-size:9.8px;line-height:1.28;color:#3f3f3f;}
        .alert-box p:last-child{margin-bottom:0;font-weight:bold;color:#5c1313;}
        .delivery-note-box{background:#f1f5f9;border-left:2px solid #475569;padding:5px 7px;margin:5px 0;font-size:8.8px;color:#334155;line-height:1.22;border-radius:0 2px 2px 0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}
        .delivery-note-box strong{color:#1e293b;display:inline;margin-right:5px;text-transform:uppercase;font-size:8.3px;letter-spacing:0;}
        .bank-details-box{background:#fafbfd;border:1px solid #e2e8f0;border-radius:2px;padding:5px 7px;margin:5px 0;font-size:8.9px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;line-height:1.25;}
        .bank-title{display:inline;font-weight:800;font-size:8.7px;margin:0 7px 0 0;text-transform:uppercase;color:#222;letter-spacing:0;border:0;padding:0;}
        .bank-line{display:inline;color:#333;white-space:nowrap;}
        .bank-item{display:inline;margin-right:8px;white-space:nowrap;}
        .bank-item strong{font-weight:700;color:#555;}
        .signatures-section{display:table;width:100%;margin-top:10px;border-top:1px solid #e2e8f0;padding-top:7px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;page-break-inside:avoid;}
        .signature-block{display:table-cell;width:50%;text-align:center;vertical-align:bottom;}
        .sig-image-container{height:72px;margin:0 auto 2px;text-align:center;display:flex;align-items:flex-end;justify-content:center;}
        .sig-image{max-height:72px;max-width:270px;display:inline-block;}
        .signature-line{width:190px;border-top:1px solid #222;margin:2px auto 3px;height:1px;}
        .digital-stamp-mock{border:1px dashed #cbd5e1;padding:4px 8px;width:125px;margin:0 auto;color:#94a3b8;font-size:7.5px;font-style:italic;border-radius:2px;text-transform:uppercase;letter-spacing:0;line-height:1.15;}
        .signer-title{font-weight:700;font-size:8.8px;color:#222;margin:0 0 1px 0;text-transform:uppercase;letter-spacing:0;}
        .signer-subtitle{font-size:8px;color:#555;margin:0;letter-spacing:0;}
        .footer{margin-top:7px;border-top:1px solid #8c1d1d;padding-top:4px;text-align:center;font-size:8px;color:#555;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;line-height:1.2;}
        .footer strong{color:#8c1d1d;text-transform:uppercase;letter-spacing:0;}
        @media print{
            html,body{background:#fff;padding:0;margin:0;}
            .container{border:none;box-shadow:none;padding:0;width:100%;max-width:100%;min-height:287mm;margin:0;display:flex;flex-direction:column;}
            body,.container,.container *{color:#000!important;text-shadow:none!important;}
            .container:before{color:rgba(0,0,0,.02);}
            .warning-banner,.alert-box,.delivery-note-box,.financial-summary th{-webkit-print-color-adjust:exact;print-color-adjust:exact;}
            .warning-banner{background:#f8fafc!important;border-left:3px solid #000!important;}
            .alert-box{background:#f8fafc!important;border:1px solid #000!important;}
            .delivery-note-box{background:#f8fafc!important;border-left:2px solid #000!important;}
            .warning-title,.company-name,.signer-title{color:#000!important;}
            .financial-summary th{background:#f1f5f9!important;border-bottom:1px solid #000!important;}
            .letter-body{flex:1;display:flex;flex-direction:column;justify-content:space-between;font-size:11.2px;line-height:1.36;margin-bottom:6mm;}
            .letter-body p{line-height:1.36;margin-bottom:0;}
            .table-container,.alert-box,.bank-details-box,.delivery-note-box{margin:0;}
            .financial-summary{font-size:10px;}
            .financial-summary th{font-size:8.6px;padding:4px 5px;}
            .financial-summary td{padding:4px 5px;}
            .alert-box p{font-size:10.2px;line-height:1.32;margin-bottom:2px;}
            .delivery-note-box{font-size:9.2px;line-height:1.28;}
            .bank-details-box{font-size:9.2px;}
            .signatures-section{page-break-inside:avoid;margin-top:0;}
            .footer{page-break-inside:avoid;}
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <div class="logo-section">
            <div class="logo-placeholder">{{agency_logo}}</div>
        </div>
        <div class="company-details">
            <h1 class="company-name">{{agency_name}}</h1>
            <p class="company-info">{{agency_address}}</p>
            <p class="company-info">Phone: {{agency_phone}} | E-mail: {{agency_email}}</p>
        </div>
    </div>
    <div class="meta-section">
        <div class="meta-col-left">Ref. No: <strong>{{letter_ref_no}}</strong></div>
        <div class="meta-col-right">Date: <strong>{{letter_date}}</strong></div>
    </div>
    <div class="warning-banner">
        <h2 class="warning-title">Final Deal Cancellation &amp; Revocation Warning</h2>
        <p class="warning-subtitle">Subject: Notice of Absolute Forfeiture &amp; Property Reallocation</p>
    </div>
    <div class="client-info">
        <div class="client-title">Notice Addressee (Defaulter Client)</div>
        <div class="client-name">{{client_name}} S/O {{client_father_name}}</div>
        <div class="client-address">{{client_address}}</div>
        <div style="margin-top:2px;font-size:10px;font-weight:bold;color:#222;">Mobile Number: <span style="font-family:monospace;font-size:10px;">{{client_mobile}}</span></div>
    </div>
    <div class="letter-body">
        <p>Dear Sir/Madam,</p>
        <p>We refer to our previous notices and the Payment Demand Letter sent under Ref: <strong>{{letter_ref_no}}</strong>. Despite several administrative grace margins, your payments towards the purchase of your <span class="highlight-text">{{property_type}}</span>, Property/File Code: <span class="highlight-text">{{property_file}}</span>, situated at <span class="highlight-text">{{project_name}}</span>, remain severely delinquent.</p>
        <p>The total agreed consideration for the aforementioned property is <span class="highlight-text">{{deal_total_amount}} ({{deal_total_amount_words}})</span>. You have defaulted on multiple consecutive monthly schedules, accumulating a major outstanding balance.</p>
        <div class="table-container">
            <table class="financial-summary">
                <thead>
                    <tr>
                        <th>Overdue Installment Milestones</th>
                        <th style="text-align:center;">Total Default Count</th>
                        <th style="text-align:right;">Total Outstanding Overdue (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight:500;">{{overdue_installments_label}}</td>
                        <td style="text-align:center;color:#8c1d1d;font-weight:bold;">{{overdue_installments_count}} Consecutive Installments</td>
                        <td style="text-align:right;color:#8c1d1d;font-weight:bold;">{{overdue_amount}}</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="font-size:11px;background:#fafbfd;border-top:1px solid #e2e8f0;color:#555;">
                            <strong>Amount in Words:</strong> <span style="color:#222;font-weight:600;text-transform:capitalize;">{{overdue_amount_words}}</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>Under the signed and agreed Terms &amp; Conditions of booking, failure to pay <strong>{{overdue_installments_count}}</strong> consecutive monthly installments triggers immediate revocation of allotment rights, leading directly to booking termination and the absolute forfeiture of initial deposit milestones.</p>
        <div class="alert-box">
            <h4>&#128680; Formal Declaration of Impending Legal Action &amp; Allotment Revocation</h4>
            <p><strong>FINAL REQUISITION:</strong> If the total outstanding overdue amount of <strong>{{overdue_amount}}</strong> is not cleared and credited in full within <strong>{{final_notice_deadline_days}} days</strong> of this notice, your booking, deal, and property allotment for <strong>{{property_file}}</strong> will stand <strong>permanently cancelled without further recourse.</strong></p>
            <p>Following the cancellation, the company will exercise its complete legal rights to immediately revoke physical possession, purge the register records, and reallocate or sell this property to other prospective buyers on the waiting list. {{agency_name}} shall also initiate recovery litigation to claim any accrued default damages.</p>
        </div>
        <div class="bank-details-box">
            <div class="bank-title">Authorized Bank Payment Channels</div>
            <div class="bank-line">
                <span class="bank-item"><strong>Title:</strong> {{bank_account_title}}</span>
                <span class="bank-item"><strong>Bank:</strong> {{bank_name}}</span>
                <span class="bank-item"><strong>A/C:</strong> {{bank_account_no}}</span>
                <span class="bank-item"><strong>IBAN:</strong> {{bank_iban}}</span>
                <span class="bank-item"><strong>Branch:</strong> {{bank_branch}}</span>
            </div>
        </div>
        <div class="delivery-note-box">
            <strong>Proof of Delivery &amp; Legal Service Status</strong>
            This is an officially dispatched and legally recognized <strong>FINAL NOTICE</strong>. It has been transmitted to you concurrently through multiple verified physical and electronic channels: (1) Hand-delivered/Posted physically to your registered residential home address, (2) Dispatched to your property's absolute physical possession address, (3) Electronically delivered to your registered email on record, and (4) Transmitted via WhatsApp messenger notification to your registered telephone line.
            <br><span style="font-weight:bold;color:#8c1d1d;">Delivery is legally deemed active and fully acknowledged upon dispatch. Counter-signature of receipt or returned postal receipt by the defaulting client is not required for this legal notice to take immediate, absolute effect.</span>
        </div>
        <p>We urge you to treat this notification with the highest priority to safeguard your property value and prevent immediate legal termination of your unit booking.</p>
        <p>Yours Sincerely,</p>
    </div>
    <div class="signatures-section">
        <div class="signature-block">
            <div class="sig-image-container">{{ceo_signature}}</div>
            <div class="signature-line"></div>
            <p class="signer-title">Chief Executive Officer</p>
            <p class="signer-subtitle">{{agency_name}}</p>
        </div>
        <div class="signature-block">
            <div class="sig-image-container">{{chairman_signature}}</div>
            <div class="signature-line"></div>
            <p class="signer-title">Chairman of the Board</p>
            <p class="signer-subtitle">{{agency_name}}</p>
        </div>
    </div>
    <div class="footer">
        <strong>{{agency_name}}</strong><br>
        Office Address: {{agency_address}}<br>
        Contact: {{agency_phone}} | Email: {{agency_email}}
    </div>
</div>
</body>
</html>
HTML;
    }

    protected function _dealChargeAgreementTemplateBody()
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاہدہ برائے اضافی کام / اضافی چارجز</title>
    <style>
        @font-face {
            font-family: 'Jameel Noori Nastaleeq';
            src: url('/whitevillas/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
                 url('/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
                 url('../fonts/JameelNooriNastaleeq.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Jameel Noori Nastaleeq Kasheeda';
            src: url('/whitevillas/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
                 url('/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
                 url('../fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @page { size: Legal portrait; margin: 12mm 14mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        html, body { margin: 0; padding: 0; background: #ffffff; color: #000000; }
        body {
            font-family: "Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", sans-serif;
            font-size: 16px;
            line-height: 1.65;
            direction: rtl;
        }
        .ashtam-page {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #000;
            padding: 8mm 9mm;
            position: relative;
            overflow: hidden;
        }
        .ashtam-page:before {
            content: "اشٹام معاہدہ";
            position: absolute;
            top: 49%;
            left: 50%;
            transform: translate(-50%,-50%) rotate(-35deg);
            font-size: 58px;
            font-weight: 900;
            font-family: "Jameel Noori Nastaleeq Kasheeda", serif;
            color: rgba(0,0,0,.035);
            white-space: nowrap;
            pointer-events: none;
        }
        .ashtam-page > * { position: relative; z-index: 2; }
        .letterhead { display: grid; grid-template-columns: 82px minmax(0,1fr) 150px; gap: 12px; align-items: center; border-bottom: 2px solid #000; padding-bottom: 7px; margin-bottom: 7px; direction: ltr; }
        .logo-box { width: 78px; height: 70px; display: flex; align-items: center; justify-content: center; direction: ltr; }
        .agency-copy { text-align: center; direction: rtl; }
        .agency-copy h1 { margin: 0; font-family: Arial, Helvetica, sans-serif; font-size: 20px; line-height: 1.2; text-transform: uppercase; font-weight: 900; letter-spacing: 0; color: #000; }
        .agency-copy p { margin: 2px 0 0; font-family: "Jameel Noori Nastaleeq", serif; font-size: 14px; line-height: 1.4; color: #000; }
        .meta-box { font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.5; text-align: left; direction: ltr; color: #000; }
        .bismillah { text-align: center; font-family: "Jameel Noori Nastaleeq Kasheeda", serif; font-size: 20px; font-weight: 900; line-height: 1.65; margin: 4px 0 2px; }
        .doc-title { text-align: center; border: 2px solid #000; padding: 6px 8px; margin: 6px 0 10px; background: #ffffff; }
        .doc-title h2 { margin: 0; font-family: "Jameel Noori Nastaleeq Kasheeda", serif; font-size: 20px; line-height: 1.65; font-weight: 900; }
        .doc-title p { margin: 0; font-family: "Jameel Noori Nastaleeq Kasheeda", serif; font-size: 15px; font-weight: 700; line-height: 1.5; }
        .section-title { font-family: "Jameel Noori Nastaleeq Kasheeda", serif; font-size: 16px; font-weight: 900; border-bottom: 1px solid #000; margin: 8px 0 5px; padding-bottom: 2px; line-height: 1.65; }
        p { margin: 0 0 6px; text-align: justify; text-justify: inter-word; font-size: 16px; line-height: 1.65; }
        .info-grid { width: 100%; border-collapse: collapse; margin: 6px 0 8px; font-size: 16px; line-height: 1.65; }
        .info-grid th, .info-grid td { border: 1px solid #000; padding: 5px 8px; vertical-align: top; }
        .info-grid th { width: 22%; background: #ffffff; font-weight: 900; white-space: nowrap; font-family: "Jameel Noori Nastaleeq Kasheeda", serif; }
        .terms { counter-reset: term; margin-top: 6px; }
        .term { display: grid; grid-template-columns: 24px minmax(0,1fr); gap: 6px; margin: 0 0 6px; break-inside: avoid; font-size: 16px; line-height: 1.65; text-align: justify; text-justify: inter-word; }
        .term:before { counter-increment: term; content: counter(term) "۔"; font-weight: 900; text-align: center; }
        .term strong { font-weight: 900; }
        .charge-table { width: 100%; border-collapse: collapse; margin: 6px 0 8px; font-size: 16px; line-height: 1.65; }
        .charge-table th, .charge-table td { border: 1px solid #000; padding: 5px 8px; text-align: center; vertical-align: top; }
        .charge-table th { background: #ffffff; font-weight: 900; font-family: "Jameel Noori Nastaleeq Kasheeda", serif; }
        .amount-highlight { font-weight: 900; text-decoration: underline; }
        .signatures { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; line-height: 1.5; }
        .signatures td { border: 1px solid #000; padding: 6px 8px; vertical-align: top; width: 50%; }
        .line { display: inline-block; border-bottom: 1px solid #000; min-width: 170px; height: 18px; vertical-align: bottom; }
        .en-inline { display: inline-block; direction: ltr; unicode-bidi: embed; font-family: Arial, Helvetica, sans-serif; font-size: 13.5px; line-height: 1; border-bottom: 1px solid #000; padding: 0 4px 1px 4px; margin: 0 2px; text-align: center; vertical-align: baseline; font-weight: bold; }
        .footer { border-top: 1.5px solid #000; margin-top: 8px; padding-top: 4px; text-align: center; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.35; direction: ltr; }
        @media print {
            @page { size: Legal portrait; margin: 12mm 14mm; }
            html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
            .ashtam-page { width: 100% !important; min-height: 0 !important; height: auto !important; margin: 0 auto !important; background: #fff !important; border: 1px solid #000 !important; padding: 0 !important; box-shadow: none !important; }
            body, .ashtam-page, .ashtam-page * { color: #000 !important; }
        }
    </style>
</head>
<body>
<div class="ashtam-page">
    <div class="letterhead">
        <div class="logo-box">{{agency_logo}}</div>
        <div class="agency-copy">
            <h1>{{agency_name}}</h1>
            <p>مجاز ڈیلر و تعمیراتی خدمات | وائٹ ولاز پراجیکٹ</p>
        </div>
        <div class="meta-box">
            <div><strong>Ref:</strong> {{charge_agreement_ref_no}}</div>
            <div><strong>Date:</strong> {{charge_agreement_date}}</div>
            <div><strong>File No:</strong> {{deal_invoice_no}}</div>
        </div>
    </div>
    <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</div>
    <div class="doc-title">
        <h2>معاہدہ برائے اضافی کام و اضافی چارجز</h2>
        <p>پراجیکٹ: {{project_name}} | یونٹ نمبر: {{property_name}}</p>
    </div>
    <p>یہ تحریر مورخہ <strong>{{charge_agreement_date}}</strong> بمقام <strong>{{agreement_place}}</strong>، فریق اوّل یعنی <strong>{{agency_name}}</strong> اور فریق دوم یعنی <strong>{{client_name}}</strong> ولد / زوجہ <strong>{{client_father_name}}</strong>، شناختی کارڈ نمبر <strong>{{client_cnic}}</strong>، موبائل نمبر <strong>{{client_mobile}}</strong> کے مابین بقائمی ہوش و حواس بلا جبر و اکراہ تحریر کی گئی۔</p>
    <div class="section-title">پراپرٹی و الاٹمنٹ تفصیل</div>
    <table class="info-grid">
        <tr>
            <th>پراجیکٹ / ایجنسی:</th>
            <td>{{project_name}} ({{agency_name}})</td>
            <th>پراپرٹی / یونٹ:</th>
            <td>{{property_name}} &bull; {{property_unit}} ({{property_area}})</td>
        </tr>
        <tr>
            <th>کلائنٹ نام:</th>
            <td>{{client_name}} ولد / زوجہ {{client_father_name}}</td>
            <th>شناختی کارڈ / فون:</th>
            <td>{{client_cnic}} &bull; {{client_mobile}}</td>
        </tr>
    </table>
    <div class="section-title">اضافی چارجز و اخراجات کی تفصیل</div>
    {{charge_rows_table}}
    <table class="info-grid" style="margin-top:4px;">
        <tr>
            <th>کل منظور شدہ رقم:</th>
            <td class="amount-highlight">{{charge_grand_total}} ({{charge_grand_total_words_urdu}})</td>
            <th>کام کی موجودہ حالت:</th>
            <td><strong>{{charge_work_status}}</strong></td>
        </tr>
    </table>
    <div class="section-title">ضوابط و شرائطِ اضافی کام</div>
    <div class="terms">
        <div class="term">اضافی کام فریق دوم کی باقاعدہ منظوری و طے شدہ تفصیلات کے مطابق مکمل کیا جائے گا۔</div>
        <div class="term">طے شدہ اضافی چارجز کی بروقت ادائیگی شیڈول کے مطابق فریق دوم پر لازم ہوگی۔</div>
        <div class="term">مکمل ادائیگی اور سائٹ تصدیق کے بعد متعلقہ اضافی تعمیرات یا سروس فریق دوم کے حوالے کی جائے گی۔</div>
    </div>
    <table class="signatures">
        <tr>
            <td>
                <strong>دستخط فریق اوّل (مجاز نمائندہ):</strong><br>
                نام: {{agency_representative_name}}<br>
                دستخط / مہر: <span class="line"></span>
            </td>
            <td>
                <strong>دستخط فریق دوم (کلائنٹ / الاٹی):</strong><br>
                نام: {{client_name}}<br>
                دستخط / انگوٹھا: <span class="line"></span>
            </td>
        </tr>
    </table>
    <div class="footer">
        {{agency_address}} &bull; Phone: {{agency_phone}} &bull; Email: {{agency_email}}
    </div>
</div>
</body>
</html>
HTML;
    }

    protected function _dealAshtamAgreementTemplateBody()
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="ur" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>معاہدہ بیع / بکنگ / الاٹمنٹ فائل</title>
    <style>
        @font-face {
            font-family: 'Jameel Noori Nastaleeq';
            src: url('/whitevillas/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
                 url('/fonts/JameelNooriNastaleeq.ttf') format('truetype'),
                 url('../fonts/JameelNooriNastaleeq.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Jameel Noori Nastaleeq Kasheeda';
            src: url('/whitevillas/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
                 url('/fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype'),
                 url('../fonts/JameelNooriNastaleeqKasheeda.ttf') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }
        @page { size: Legal portrait; margin: 12mm 14mm; }
        * { box-sizing: border-box; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        html, body {
            margin: 0;
            padding: 0;
            direction: rtl;
            unicode-bidi: embed;
            background: #ffffff;
            color: #000000;
            font-family: "Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", sans-serif;
            font-size: 16px;
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
        }
        .page {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            padding: 0;
            background: #fff;
            border: none;
            box-shadow: none;
            position: relative;
            overflow: visible;
            display: flex;
            flex-direction: column;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 58px;
            font-weight: 900;
            color: rgba(0,0,0,.035);
            font-family: Arial, sans-serif;
            pointer-events: none;
            user-select: none;
            z-index: 1;
            white-space: nowrap;
            border: 6px double rgba(0,0,0,.035);
            padding: 4px 20px;
            border-radius: 12px;
        }
        .page > *:not(.watermark) { position: relative; z-index: 2; }
        .letterhead { text-align: center; border-bottom: 2px solid #000000; padding-bottom: 6px; margin-bottom: 8px; }
        .head-row { display: grid; grid-template-columns: 60px minmax(0,1fr) 60px; align-items: center; gap: 10px; }
        .logo-box { width: 56px; height: 56px; border: 1px solid #000000; padding: 2px; display: flex; align-items: center; justify-content: center; overflow: hidden; direction: ltr; }
        .agency-name { font-size: 18px; font-weight: 900; line-height: 1.25; color: #000000; font-family: Arial, Helvetica, sans-serif; text-transform: uppercase; }
        .agency-sub { font-size: 13px; color: #333333; margin-top: 2px; font-family: "Jameel Noori Nastaleeq", sans-serif; }
        .agency-contact { font-size: 10.5px; color: #333333; margin-top: 2px; font-family: Arial, Helvetica, sans-serif; direction: ltr; }
        .document-title {
            text-align: center;
            font-family: "Jameel Noori Nastaleeq Kasheeda", "Jameel Noori Nastaleeq", serif;
            font-size: 20px;
            font-weight: 900;
            text-decoration: underline;
            text-underline-offset: 4px;
            margin: 6px 0 2px;
            line-height: 1.65;
            color: #000000;
        }
        .document-subtitle {
            text-align: center;
            font-family: "Jameel Noori Nastaleeq Kasheeda", "Jameel Noori Nastaleeq", serif;
            font-size: 16px;
            font-weight: 700;
            color: #000000;
            margin-bottom: 6px;
            line-height: 1.65;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            font-weight: 800;
            color: #000000;
            margin-bottom: 6px;
            background: #ffffff;
            padding: 4px 8px;
            border: 1px solid #000000;
            border-radius: 4px;
            line-height: 1.65;
        }
        p {
            margin: 0 0 6px;
            direction: rtl;
            text-align: justify;
            text-justify: inter-word;
            line-height: 1.65;
            font-size: 16px;
        }
        .section-title {
            font-family: "Jameel Noori Nastaleeq Kasheeda", "Jameel Noori Nastaleeq", serif;
            font-size: 16px;
            font-weight: 900;
            text-decoration: underline;
            text-underline-offset: 3px;
            margin: 8px 0 4px;
            color: #000000;
            line-height: 1.65;
            page-break-after: avoid;
        }
        .clause, .numbered-clause {
            font-size: 16px;
            line-height: 1.65;
            margin-bottom: 5px;
            direction: rtl;
            text-align: justify;
            text-justify: inter-word;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .field { border-bottom: 1px solid #000000; padding: 0 4px; font-weight: 900; color: #000000; display: inline; }
        .amount-box {
            border: 1.5px solid #000000;
            background: #ffffff;
            padding: 6px 12px;
            margin: 8px 0;
            text-align: center;
            font-size: 16px;
            font-weight: 900;
            line-height: 1.65;
            border-radius: 4px;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .note-box {
            border: 1px solid #000000;
            border-right: 4px solid #000000;
            background: #ffffff;
            padding: 6px 10px;
            margin: 6px 0;
            font-size: 16px;
            line-height: 1.65;
            text-align: justify;
            text-justify: inter-word;
            border-radius: 0 4px 4px 0;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .en-inline {
            display: inline-block;
            direction: ltr;
            unicode-bidi: embed;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13.5px;
            line-height: 1;
            border-bottom: 1px solid #000000;
            padding: 0 4px 1px 4px;
            margin: 0 2px;
            text-align: center;
            vertical-align: baseline;
            font-weight: bold;
            color: #000000;
        }
        .field .en-inline { border-bottom: none; }
        .sign-area, .witness-area { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 12px; page-break-inside: avoid; break-inside: avoid; }
        .sign-box { border: 1px solid #000000; background: #ffffff; border-radius: 4px; padding: 8px 10px; min-height: 90px; line-height: 1.4; font-size: 14px; page-break-inside: avoid; break-inside: avoid; }
        .sign-title { font-family: "Jameel Noori Nastaleeq Kasheeda", serif; font-weight: 900; color: #000000; border-bottom: 1px solid #000000; padding-bottom: 3px; margin-bottom: 5px; font-size: 15px; line-height: 1.4; }
        .sign-line { border-bottom: 1px solid #000000; display: block; min-height: 20px; margin-top: 3px; margin-bottom: 3px; }
        .thumb-box { height: 32px; border: 1px dashed #000000; background: #ffffff; border-radius: 3px; margin-top: 4px; display: flex; align-items: center; justify-content: center; font-size: 12px; color: #333333; }
        .footer { border-top: 1.5px solid #000000; padding-top: 4px; text-align: center; font-size: 11px; line-height: 1.25; color: #000000; font-family: Arial, Helvetica, sans-serif; margin-top: auto; }
        .page-body { flex: 1 1 auto; }
        @media print {
            body { background: #fff !important; color: #000 !important; padding: 0 !important; }
            .page { width: 100% !important; max-width: 100% !important; height: auto !important; min-height: 0 !important; margin: 0 auto !important; border: none !important; box-shadow: none !important; border-radius: 0 !important; }
            .field { border-bottom: 1px solid #000 !important; background: transparent !important; color: #000 !important; }
            .amount-box, .note-box, .sign-box { border-color: #000 !important; background: transparent !important; }
            .watermark { color: rgba(0,0,0,.02) !important; border-color: rgba(0,0,0,.02) !important; }
            .footer { color: #000 !important; }
        }
    </style>
</head>
<body>
<main class="page">
    <div class="watermark">{{agency_name}}</div>
    <header class="letterhead">
        <div class="head-row">
            <div class="logo-box">{{agency_logo}}</div>
            <div>
                <div class="agency-name">{{agency_name}}</div>
                <div class="agency-sub">آفیشل سیلز، بکنگ اینڈ مارکیٹنگ ریکارڈ</div>
            </div>
            <div></div>
        </div>
        <div class="agency-contact">Office: {{agency_address}} | Contact: {{agency_phone}} | Email: {{agency_email}}</div>
    </header>
    <div class="page-body">
        <div class="document-title">معاہدہ بیع / بکنگ / الاٹمنٹ فائل</div>
        <div class="document-subtitle">پراجیکٹ: {{project_name}}</div>
        <div class="meta-row">
            <div>معاہدہ نمبر: <span class="field">{{letter_ref_no}}</span></div>
            <div>تاریخِ معاہدہ: <span class="field">{{letter_date}}</span></div>
            <div>دفتری مقام: <span class="field">{{agreement_place}}</span></div>
        </div>
        <p>یہ معاہدہ آج مورخہ <span class="field">{{agreement_date_text_urdu}}</span> بروز <span class="field">{{agreement_day_name_urdu}}</span> بمقام <span class="field">{{agreement_place}}</span>، فریق اوّل یعنی <strong>{{agency_name}}</strong>، دفتر واقع <span class="field">{{agency_address}}</span>، بذریعہ مجاز نمائندہ <span class="field">{{agency_representative_name}}</span> ولد <span class="field">{{agency_representative_father}}</span>، شناختی کارڈ نمبر <span class="field">{{agency_representative_cnic}}</span>، عہدہ <span class="field">{{agency_representative_designation}}</span>، جسے آئندہ عبارت میں <strong>فریق اوّل / ایجنسی / پراجیکٹ</strong> کہا جائے گا، اور فریق دوم یعنی کلائنٹ / خریدار <strong>{{client_name}}</strong> ولد / زوجہ <span class="field">{{client_father_name}}</span>، شناختی کارڈ نمبر <span class="field">{{client_cnic}}</span>، موبائل نمبر <span class="field">{{client_mobile}}</span>، ساکن <span class="field">{{client_address}}</span>، جسے آئندہ عبارت میں <strong>فریق دوم / کلائنٹ / خریدار</strong> کہا جائے گا، کے درمیان باہمی رضامندی، آزاد مرضی اور بغیر کسی دباؤ کے تحریر پایا۔</p>
        <div class="section-title">تمہید</div>
        <p>چونکہ فریق اوّل ہاؤسنگ پروجیکٹ <strong>{{project_name}}</strong> میں جائیدادوں کی مارکیٹنگ، الاٹمنٹ، ریکارڈ اور متعلقہ تعمیراتی امور کی نگرانی کے جائز قانونی حقوق رکھتا ہے۔ فریق دوم نے اپنی مرضی، ذاتی تسلی، موقع ملاحظہ، سوسائٹی نقشہ، قیمت، سائز، قسط پلان اور شرائط کو تفصیلاً سمجھنے کے بعد درج ذیل جائیداد حاصل کرنے پر آمادگی کا اظہار کیا ہے۔</p>
        <div class="section-title">پراپرٹی / یونٹ کی تفصیل</div>
        <p>پراجیکٹ کا نام: <span class="field">{{project_name}}</span>، بلاک / سیکٹر: <span class="field">{{agreement_property_block}}</span>، پراپرٹی قسم: <span class="field">{{property_type}}</span>، یونٹ / ولا / فائل نمبر: <span class="field">{{agreement_property_unit_no}}</span>، منزل / فلور: <span class="field">{{agreement_property_floor}}</span>، سائز / رقبہ: <span class="field">{{property_area}}</span>، کورڈ ایریا: <span class="field">{{agreement_property_covered_area}}</span>، لوکیشن / سٹریٹ: <span class="field">{{agreement_property_location}}</span>۔</p>
        <div class="amount-box">کل قیمتِ خرید: <span class="field">{{deal_total_amount}}</span> &nbsp; | &nbsp; ڈاؤن پیمنٹ / بیعانہ: <span class="field">{{deal_booking_amount}}</span><br>بقایا رقم: <span class="field">{{deal_balance_amount}}</span> &nbsp; | &nbsp; ادائیگی پلان: <span class="field">{{agreement_payment_plan}}</span></div>
        <div class="section-title">پراپرٹی کی نوعیت اور مخصوص شرائط</div>
        {{property_agreement_terms}}
        <div class="section-title">عمومی ضوابط و شرائطِ معاہدہ</div>
        <div class="clause"><strong>1. اقساط کی بروقت ادائیگی:</strong> فریق دوم طے شدہ شیڈول کے مطابق اقساط کی ادائیگی کا پابند ہوگا۔ مسلسل 2 اقساط کی عدم ادائیگی پر سوسائٹی قواعد کے مطابق نوٹس جاری کیا جائے گا۔</div>
        <div class="clause"><strong>2. اضافی و ترقیاتی چارجز:</strong> گورنمنٹ ٹیکسز، یوٹیلیٹی کنکشن فیس، کارنر / پارک فیسنگ اور سوسائٹی رولز کے تحت منظور شدہ ڈیولپمنٹ چارجز علیحدہ لاگو ہوں گے۔</div>
        <div class="clause"><strong>3. انتقال و الاٹمنٹ حقوق:</strong> کل واجبات کی 100% مکمل ادائیگی اور کلیئرنس کے بعد فریق دوم کے نام باقاعدہ فائنل الاٹمنٹ / پوزیشن جاری کی جائے گی۔</div>
        <div class="clause"><strong>4. منسوخی و ری فنڈ پالیسی:</strong> اگر فریق دوم خود معاہدہ ختم کرنا چاہے تو منظور شدہ ضوابط و کٹوتی پالیسی کے تحت پروسیس کیا جائے گا۔</div>
        <div class="section-title">دستخط و تصدیق فریقین</div>
        <div class="sign-area">
            <div class="sign-box">
                <div class="sign-title">دستخط فریق اوّل (مجاز نمائندہ / ایجنسی)</div>
                <div>نام: {{agency_representative_name}}</div>
                <span class="sign-line"></span>
                <div class="thumb-box">مہر و دستخط</div>
            </div>
            <div class="sign-box">
                <div class="sign-title">دستخط فریق دوم (کلائنٹ / خریدار)</div>
                <div>نام: {{client_name}}</div>
                <span class="sign-line"></span>
                <div class="thumb-box">انگوٹھا نشان / دستخط</div>
            </div>
        </div>
        <div class="section-title">گواہانِ معاہدہ</div>
        <div class="witness-area">
            <div class="sign-box">
                <div class="sign-title">گواہ نمبر 1</div>
                <div>نام: <span class="field">{{witness_one_name}}</span></div>
                <div>شناختی کارڈ: <span class="field">{{witness_one_cnic}}</span></div>
                <span class="sign-line"></span>
            </div>
            <div class="sign-box">
                <div class="sign-title">گواہ نمبر 2</div>
                <div>نام: <span class="field">{{witness_two_name}}</span></div>
                <div>شناختی کارڈ: <span class="field">{{witness_two_cnic}}</span></div>
                <span class="sign-line"></span>
            </div>
        </div>
    </div>
    <footer class="footer">
        White Villas Real Estate Management &bull; Generated &amp; Verified Official Legal Agreement
    </footer>
</main>
</body>
</html>
HTML;
    }

    protected function _refundCompletionClearanceTemplateBody()
    {
        $file = APP . 'Lib' . DS . 'LetterTemplates' . DS . 'refund_completion_clearance.html';
        if (is_readable($file))
        {
            $body = file_get_contents($file);
            if (trim((string)$body) !== '')
            {
                return (string)$body;
            }
        }
        return '<p><strong>Date:</strong> {{refund_completion_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Refund clearance has been issued for <strong>{{client_name}}</strong>, deal <strong>{{deal_invoice_no}}</strong>.</p><p><strong>Deal Refund ID:</strong> {{deal_refund_id}}<br><strong>Refund Amount:</strong> {{refund_net_refund}}</p><div style="width:150px;height:150px;">{{refund_completion_qr}}</div>';
    }

    protected function _rentalReceiptTemplateBody()
    {
        $file = APP.'Lib'.DS.'LetterTemplates'.DS.'rental_receipt.html';
        if (is_readable($file))
        {
            $body = file_get_contents($file);
            if (trim((string)$body) !== '')
            {
                return (string)$body;
            }
        }
        return '<h2 style="text-align:center;">RENTAL PAYMENT RECEIPT</h2><p><strong>Receipt:</strong> {{receipt_no}}<br><strong>Date:</strong> {{receipt_date}}<br><strong>Tenant:</strong> {{tenant_name}}<br><strong>Agreement:</strong> {{agreement_no}}<br><strong>Rent Month:</strong> {{rent_month_label}}</p><p><strong>Amount Received:</strong> {{rent_amount}}<br><strong>Amount in Words:</strong> {{rent_amount_words}}</p>';
    }
      protected function _ensureDefaultLetterTemplates()
      {
          static $isEnsured = false;
          if ($isEnsured)
          {
              return;
          }
          $isEnsured = true;
          $this->loadModel('Template');
          $defaults = array(
            'RLT_WELCOME' => array(
                'name' => 'Welcome Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>Welcome to {{site_organization}}. We are pleased to confirm your new booking in <strong>{{project_name}}</strong>.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Property:</strong> {{property_name}}<br><strong>Type:</strong> {{property_type}}<br><strong>Area:</strong> {{property_area}}</p><p>Thank you for trusting us.</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_BOOKING' => array(
                'name' => 'Booking Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This is to confirm your booking of <strong>{{property_name}}</strong> in {{project_name}}.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Booking Date:</strong> {{deal_date}}<br><strong>Total Amount:</strong> {{deal_total_amount}}<br><strong>Booking Amount:</strong> {{deal_booking_amount}}<br><strong>Property Type:</strong> {{property_type}}<br><strong>Area:</strong> {{property_area}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}</p><p>Regards,<br>{{site_organization}}</p>'
            ),
              'RLT_CHARGE' => array(
                  'name' => 'Deal Charges Agreement',
                  'description' => $this->_dealChargeAgreementTemplateBody()
              ),
              'RLT_DEAL_AGREEMENT' => array(
                  'name' => 'Deal Agreement Ashtam',
                  'description' => $this->_dealAshtamAgreementTemplateBody()
              ),
              'RLT_REFUND' => array(
                  'name' => 'Refund Agreement',
                  'description' => '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REFUND REQUEST APPLICATION - {{agency_name}}</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@400..700&display=swap" rel="stylesheet">
    <style>
        .form-container {
            /* OPTIMIZED FOR A4 PREVIEW */
            max-width: 800px; 
            font-family: \'Times New Roman\', serif; 
            margin: 8px auto 40px auto; 
            background-color: white;
            padding: 1rem 2.5rem 2.5rem 2.5rem; 
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            color: #333;
            border-top: 12px solid #DC2626; 
            position: relative;
            overflow: hidden;
        }
        .form-section-title {
            background-color: #fef2f2;
            color: #b91c1c;
            font-weight: 700;
            padding: 0.5rem 1rem;
            margin-top: 0; 
            margin-bottom: 1rem;
            border-left: 5px solid #DC2626;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .input-line {
            border-bottom: 1px solid #9ca3af;
            padding-left: 0.5rem;
            flex-grow: 1;
        }
        .input-group {
            display: flex;
            align-items: center; 
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        .section-box {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.5rem;
            margin-bottom: 1.5rem;
        }
        .refund-calc-header {
            font-size: 16px; 
            font-weight: 700; 
            color: #DC2626;
            background-color: #fef2f2;
            padding: 0.5rem;
            border-radius: 0.25rem;
            border-bottom: 2px solid #DC2626;
            text-align: center;
        }
        /* Table Styles for the Disbursement Schedule */
        .schedule-table-container table {
            width: 100%;
            text-align: left;
            border-collapse: collapse;
        }
        .schedule-table-container th, .schedule-table-container td {
            padding: 6px 10px;
            border: 1px solid #e5e7eb;
            font-size: 0.75rem;
        }
        .schedule-table-container thead {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        /* Tailwind-lite utility fallbacks (for print/no CDN) */
        .flex{display:flex;}
        .items-start{align-items:flex-start;}
        .items-center{align-items:center;}
        .items-end{align-items:flex-end;}
        .justify-between{justify-content:space-between;}
        .flex-col{flex-direction:column;}
        .flex-grow{flex:1 1 0%;}
        .flex-1{flex:1 1 0%;}
        .flex-[2]{flex:2 1 0%;}
        .flex-shrink-0{flex-shrink:0;}
        .space-x-6 > :not([hidden]) ~ :not([hidden]){margin-left:1.5rem;}
        .space-y-4 > :not([hidden]) ~ :not([hidden]){margin-top:1rem;}
        .space-y-1 > :not([hidden]) ~ :not([hidden]){margin-top:.25rem;}
        .border{border:1px solid #e5e7eb;}
        .border-2{border:2px solid #e5e7eb;}
        .border-b-2{border-bottom:2px solid #dc2626;}
        .border-b-4{border-bottom:4px solid #dc2626;}
        .border-gray-200{border-color:#e5e7eb;}
        .border-gray-300{border-color:#d1d5db;}
        .border-red-600{border-color:#dc2626;}
        .rounded{border-radius:.25rem;}
        .rounded-lg{border-radius:.5rem;}
        .bg-gray-50{background:#f9fafb;}
        .bg-red-50{background:#fef2f2;}
        .text-gray-900{color:#111827;}
        .text-gray-800{color:#1f2937;}
        .text-gray-700{color:#374151;}
        .text-gray-600{color:#4b5563;}
        .text-gray-500{color:#6b7280;}
        .text-gray-400{color:#9ca3af;}
        .text-red-600{color:#dc2626;}
        .text-red-700{color:#b91c1c;}
        .uppercase{text-transform:uppercase;}
        .tracking-tighter{letter-spacing:-.02em;}
        .tracking-widest{letter-spacing:.15em;}
        .leading-none{line-height:1;}
        .leading-tight{line-height:1.25;}
        .text-xs{font-size:.75rem;}
        .text-sm{font-size:.875rem;}
        .text-lg{font-size:1.125rem;}
        .text-2xl{font-size:1.5rem;}
        .text-3xl{font-size:1.875rem;}
        .font-bold{font-weight:700;}
        .font-black{font-weight:900;}
        .font-extrabold{font-weight:800;}
        .p-1{padding:.25rem;}
        .p-2{padding:.5rem;}
        .p-4{padding:1rem;}
        .pb-4{padding-bottom:1rem;}
        .pb-1{padding-bottom:.25rem;}
        .px-8{padding-left:2rem;padding-right:2rem;}
        .pr-1{padding-right:.25rem;}
        .mb-6{margin-bottom:1.5rem;}
        .mb-4{margin-bottom:1rem;}
        .mb-1{margin-bottom:.25rem;}
        .mt-3{margin-top:.75rem;}
        .mt-8{margin-top:2rem;}
        .ml-1{margin-left:.25rem;}
        .w-12{width:3rem;}
        .w-24{width:6rem;}
        .h-24{height:6rem;}
        .w-28{width:7rem;}
        .h-32{height:8rem;}
        .text-right{text-align:right;}
        .text-center{text-align:center;}
        .overflow-hidden{overflow:hidden;}
        .shadow-sm{box-shadow:0 1px 2px 0 rgba(0,0,0,.05);}
        .grid{display:grid;}
        .grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr));}
        .grid-cols-4{grid-template-columns:repeat(4,minmax(0,1fr));}
        .gap-4{gap:1rem;}
        .gap-x-4{column-gap:1rem;}
        .border-t{border-top:1px solid #9ca3af;}
        .border-dashed{border-style:dashed;}
        .min-h\\[80px\\]{min-height:80px;}
        .min-h\\[2\\.5rem\\]{min-height:2.5rem;}
        .tracking-wider{letter-spacing:.05em;}
        .text-\\[10px\\]{font-size:10px;}
        .text-\\[11px\\]{font-size:11px;}
        .w-full{width:100%;}
        .h-full{height:100%;}
        .object-cover{object-fit:cover;}
        .border-red-100{border-color:#fee2e2;}
        .border-gray-300{border-color:#d1d5db;}
        .border-r{border-right:1px solid #d1d5db;}
        .border-l{border-left:1px solid #d1d5db;}
        @media print {
            @page {
                size: A4 portrait;
                margin: 1.2cm; 
            }
            body {
                margin: 0;
                padding: 0;
                background: none;
                font-family: \'Times New Roman\', serif !important; 
            }
            .form-container {
                max-width: 100%;
                width: 100%;
                margin: 0 auto;
                padding: 0;
                box-shadow: none;
                border-top: none;
                border: none;
            }
            .text-red-600, .text-red-700 {
                color: #000 !important;
            }
            .input-line {
                border-bottom: 1px solid #000;
                word-wrap: break-word;
            }
            .page-break-after-print {
                page-break-after: always;
            }
            footer {
                font-size: 10px; 
          }
      }
    </style>
</head>

<body class="bg-gray-50 p-4">

    <div class="form-container">
        
        <!-- AGENCY HEADER DESIGN -->
        <header class="mb-3 flex justify-between items-center border-b-2 border-red-600 pb-2 gap-4" style="display:flex;align-items:center;justify-content:space-between;gap:14px;">
            <div class="flex items-center space-x-4 flex-1 min-w-0" style="display:grid;grid-template-columns:110px minmax(0,1fr);align-items:center;gap:14px;flex:1 1 auto;min-width:0;">
                <!-- Agency Logo Left Side -->
                <div class="w-24 h-24 flex-shrink-0 flex items-center justify-center overflow-hidden wv-letter-logo-box" style="width:100px;height:100px;display:flex;align-items:center;justify-content:center;background:transparent;border:0;padding:0;overflow:visible;">
                    {{agency_logo}}
                </div>

                <!-- Agency Letterhead Text -->
                <div class="flex-grow wv-letter-agency-copy" style="text-align:center;min-width:0;">
                    <h1 class="text-2xl font-black text-gray-900 uppercase leading-tight" style="text-align:center;margin:0;color:#111827;font-size:24px;line-height:1.15;font-weight:900;text-transform:uppercase;">{{agency_name}}</h1>
                    <div class="mt-2 text-[11px] text-gray-600 leading-tight space-y-0.5">
                        <p class="wv-agency-address-line" style="margin:0;text-align:center;font-weight:700;color:#374151;">{{agency_address}}</p>
                        <!-- Combined Phone and Email in one row -->
                        <div class="flex items-center space-x-6 wv-letter-contact-row" style="display:flex;align-items:center;justify-content:center;gap:24px;margin-top:2px;text-align:center;">
                            <p class="flex items-center" style="margin:0;"><span class="font-bold w-12">Phone:</span> {{agency_phone}}</p>
                            <p class="flex items-center" style="margin:0;"><span class="font-bold w-12 text-right pr-1">Email:</span> {{agency_email}}</p>
                        </div>
                    </div>
                    <div class="wv-letter-header-title" style="margin-top:8px;text-align:center;">
                        <p style="margin:0 auto;padding:0 18px 4px 18px;border-bottom:3px solid #111827;display:inline-block;color:#111827;font-size:17px;font-weight:900;line-height:1.15;text-transform:uppercase;">DEAL PAYMENT REFUND FORM</p>
                    </div>
                </div>
            </div>
            
            <!-- Letter Meta and Applicant Photo -->
            <div class="flex flex-col items-end space-y-2 flex-shrink-0">
                <div class="text-right text-xs font-bold text-gray-700">
                    <p>Ref: <span class="font-normal text-gray-900">{{letter_ref_no}}</span></p>
                    <p>Date: <span class="font-normal text-gray-900">{{letter_date}}</span></p>
                </div>
                <div class="w-20 h-24 border-2 border-gray-300 overflow-hidden bg-gray-50 shadow-sm">
                    {{applicant_photo}}
                </div>
            </div>
        </header>

        <!-- PROPERTY IDENTIFIER BLOCK -->
        <div class="flex justify-between items-center text-sm font-extrabold mb-6 pb-2 border-b border-gray-200">
            <div class="ml-1">
                <span class="text-gray-500 font-extrabold">Project / Property:</span> 
                <span class="text-red-600 ml-1 font-semibold">{{property_name}}</span>
            </div>
            
            <div>
                <span class="text-gray-500 font-extrabold">Deal Invoice:</span> 
                <span class="text-gray-800 ml-1 font-semibold">{{deal_invoice_no}}</span>
            </div>
        </div>

        <!-- PERSONAL INFORMATION -->
        <div class="section-box mt-0">
            <div class="form-section-title">Personal Information</div>
            <div class="space-y-3 p-2">
                <div class="input-group">
                    <label class="w-44 flex-shrink-0 font-bold">Applicant Name:</label>
                    <div class="input-line text-sm font-medium uppercase">{{client_name}}</div>
                    <label class="ml-4 flex-shrink-0 font-bold">{{client_relation_type_label}}:</label>
                    <div class="input-line text-sm">{{client_father_name}}</div>
                </div>
                
                <div class="input-group">
                    <label class="w-44 flex-shrink-0 font-bold">CNIC / NICOP:</label>
                    <div class="input-line text-sm">{{client_cnic}}</div>
                    <label class="ml-4 flex-shrink-0 font-bold">Passport No:</label>
                    <div class="input-line text-sm">{{refund_passport_no}}</div>
                </div>

                <div class="input-group items-start"> 
                    <label class="w-44 flex-shrink-0 font-bold pt-1">Permanent Address:</label>
                    <div class="input-line text-sm min-h-[2.5rem] leading-relaxed">{{client_address}}</div>
                </div>

                <div class="grid grid-cols-2 gap-x-4">
                    <div class="input-group">
                        <label class="w-20 flex-shrink-0 font-bold">Mobile:</label>
                        <div class="input-line text-sm font-semibold">{{client_mobile}}</div>
                    </div>
                    <div class="input-group">
                        <label class="w-20 flex-shrink-0 font-bold">E-mail:</label>
                        <div class="input-line text-sm">{{client_email}}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ORIGINAL PROPERTY DETAILS & REFUND CALCULATION -->
        <div class="section-box">
            <div class="form-section-title">Deal & Financial Statement</div>
            <div class="grid grid-cols-10 gap-4 p-2">
                <div class="col-span-4 space-y-4"> 
                    <div class="font-bold text-xs mb-1 text-gray-500 uppercase tracking-widest">Inventory Details</div>
                    <div class="input-group">
                        <label class="w-28 flex-shrink-0 font-extrabold">Property:</label>
                        <div class="input-line text-sm font-medium">{{property_name}}</div>
                    </div>
                    <div class="input-group">
                        <label class="w-28 flex-shrink-0 font-extrabold">Type:</label>
                        <div class="input-line text-sm font-medium">{{property_type}}</div>
                    </div>
                    <div class="input-group">
                        <label class="w-28 flex-shrink-0 font-extrabold">Block:</label>
                        <div class="input-line text-sm font-medium">{{ballot_block_name}}</div>
                    </div>
                </div>

                <div class="col-span-6"> 
                    <div class="w-full border-2 border-red-100 p-4 text-xs text-gray-800 bg-gray-50 rounded-lg">
                        <p class="refund-calc-header mb-3 uppercase tracking-tighter">Refund Assessment</p>
                        <div class="space-y-2">
                            <div class="flex justify-between border-b border-gray-200 pb-1">
                                <span class="font-bold">Total Paid Amount:</span>
                                <span class="font-black text-gray-900">{{refund_total_paid}}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-200 pb-1">
                                <span class="font-bold">{{refund_adjustment_label}} ({{refund_adjustment_percent}}):</span>
                                <span class="font-bold {{refund_adjustment_color}}">{{refund_adjustment_sign}} {{refund_adjustment_amount}}</span>
                            </div>
                            <div class="flex justify-between pt-1">
                                <span class="text-sm font-black text-gray-900">Net Refundable Amount:</span>
                                <span class="text-sm font-black text-red-700">{{refund_net_refund}}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REASON FOR CANCELLATION -->
        <div class="section-box page-break-after-print">
            <div class="form-section-title">Reason for Cancellation</div>
            <div class="p-2">
                <div class="min-h-[80px] border-b border-dashed border-gray-400 text-base font-medium leading-relaxed">
                    {{refund_reason}}
                </div>
                <p class="text-[10px] text-gray-400 mt-2 uppercase font-bold">
                    *Management approval required for processing based on the statement above.
                </p>
            </div>
        </div>

        <!-- REFUND DISBURSEMENT SCHEDULE -->
        <div class="section-box">
            <div class="form-section-title">Disbursement Schedule</div>
            <div class="p-2">
                <div class="flex justify-between items-center mb-4 bg-gray-50 p-2 border border-gray-200 rounded">
                    <div class="text-center px-4 flex-1">
                        <p class="text-[10px] uppercase font-bold text-gray-500">Total Cycles</p>
                        <p class="text-lg font-black text-gray-900">{{refund_cycle_count}}</p>
                    </div>
                    <!-- Frequency column widened with increased padding and flex-grow -->
                    <div class="text-center px-12 border-l border-r border-gray-300 flex-[2]">
                        <p class="text-[10px] uppercase font-bold text-gray-500">Frequency</p>
                        <p class="text-lg font-black text-red-600 uppercase">{{refund_cycle_frequency}}</p>
                    </div>
                    <div class="text-center px-4 flex-1">
                        <p class="text-[10px] uppercase font-bold text-gray-500">Cycle Amount</p>
                        <p class="text-lg font-black text-red-700">{{refund_cycle_amount}}</p>
                    </div>
                </div>

                <div class="schedule-table-container">
                    {{refund_schedule_table}}
                </div>
            </div>
        </div>

        <!-- BANKING DETAILS -->
        <div class="section-box">
            <div class="form-section-title">Bank Account Details</div>
            <div class="space-y-3 p-2">
                <div class="input-group">
                    <label class="w-40 flex-shrink-0 font-bold">Account Title:</label>
                    <div class="input-line text-sm font-medium uppercase">{{refund_account_title}}</div>
                </div>
                <div class="input-group items-start"> 
                    <label class="w-40 flex-shrink-0 font-bold pt-1">Account No / IBAN:</label>
                    <div class="input-line text-lg font-black text-gray-900 min-h-[2.5rem] tracking-wider">{{refund_account_no}}</div>
                </div>
                <div class="grid grid-cols-2 gap-x-4">
                    <div class="input-group">
                        <label class="w-32 flex-shrink-0 font-bold">Bank Name:</label>
                        <div class="input-line text-sm uppercase">{{refund_bank_name}}</div>
                    </div>
                    <div class="input-group">
                        <label class="w-32 flex-shrink-0 font-bold">Branch:</label>
                        <div class="input-line text-sm">{{refund_bank_branch}}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TERMS & CONDITIONS -->
        <div class="section-box p-4 border-red-600 border-2 bg-red-50">
            <h4 class="font-black text-red-800 uppercase mb-2 text-sm">Declaration & Terms</h4>
            <div class="terms-text space-y-2">
                <ol class="list-decimal list-inside pl-4 font-medium">
                    <li>{{refund_adjustment_statement}}</li>
                    <li>Refund cycle will commence as per management approval and schedule.</li>
                    <li>No claims will be entertained against the property unit once the first cycle is disbursed.</li>
                </ol>
            </div>
        </div>
        
        <!-- FOOTER / SIGNATURES -->
        <footer class="mt-12 text-xs">
            <div class="grid grid-cols-4 gap-4 text-center border-t border-gray-400 pt-1">
                <div class="font-bold pt-2 flex flex-col items-center">
                    <div class="h-10 w-full"></div> 
                    <span class="mt-1 uppercase">Officer Signature</span>
                </div>
                <div class="font-bold pt-2 flex flex-col items-center">
                    <div class="h-10 w-full"></div> 
                    <span class="mt-1 uppercase">Management Sign</span>
                </div>
                <div class="font-bold pt-2 flex flex-col items-center">
                    <div class="h-10 w-full"></div> 
                    <span class="mt-1 uppercase">Applicant Sign</span>
                </div>
                <div class="font-bold pt-2 flex flex-col items-center">
                    <div class="h-10 w-full"></div> 
                    <span class="mt-1 uppercase">Thumb Impression</span>
                </div>
            </div>
            <p class="text-center text-red-600 font-black mt-8 uppercase tracking-widest text-[11px]">
                Official {{agency_name}} Refund Authorization Document
            </p>
        </footer>

    </div>
</body>
</html>'
              ),
            'RLT_DEMARCATION' => array(
                'name' => 'Demarcation Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This letter confirms that demarcation has been completed for your allotted unit.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Property Number:</strong> {{ballot_property_no}}<br><strong>Block:</strong> {{ballot_block_name}}<br><strong>Street Address:</strong> {{ballot_street_address}}<br><strong>Area:</strong> {{ballot_area_full}}</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_DESIGN_MAP_APPROVAL' => array(
                'name' => 'Design / Map Approval Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>Your design/map submission for <strong>{{property_name}}</strong> has been approved subject to applicable by-laws and authority rules.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Project:</strong> {{project_name}}<br><strong>Property Type:</strong> {{property_type}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Block:</strong> {{ballot_block_name}}<br><strong>Street Address:</strong> {{ballot_street_address}}<br><strong>Area:</strong> {{ballot_area_full}}</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_NOC' => array(
                'name' => 'NOC Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>No Objection Certificate is hereby issued for <strong>{{property_name}}</strong> (Membership {{deal_invoice_no}}), subject to company policy and record verification.</p><p><strong>Project:</strong> {{project_name}}<br><strong>Property Type:</strong> {{property_type}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Property Number:</strong> {{ballot_property_no}}<br><strong>Block:</strong> {{ballot_block_name}}<br><strong>Street Address:</strong> {{ballot_street_address}}</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_ALLOT' => array(
                'name' => 'Allotment Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This letter confirms allotment of <strong>{{property_name}}</strong> ({{property_type}} / {{property_contract}}) in {{project_name}}.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Property Number:</strong> {{ballot_property_no}}<br><strong>Block:</strong> {{ballot_block_name}}<br><strong>Street Address:</strong> {{ballot_street_address}}<br><strong>Area:</strong> {{ballot_area_full}}<br><strong>Total Amount:</strong> {{deal_total_amount}}</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_DEMAND' => array(
                'name' => 'Payment Demand Notice',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>To,<br>{{client_name}}<br>{{client_address}}</p><p>This is to remind you that your payment against <strong>{{property_name}}</strong> (Membership {{deal_invoice_no}}) is due as per your agreed plan.</p><p><strong>Property Type:</strong> {{property_type}}<br><strong>Area:</strong> {{property_area}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}</p><p>Please clear outstanding dues at the earliest.</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_RECEIPT' => array(
                'name' => 'Payment Acknowledgement',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>We acknowledge receipt of your payment for <strong>{{property_name}}</strong> (Membership {{deal_invoice_no}}).</p><p><strong>Project:</strong> {{project_name}}<br><strong>Property Type:</strong> {{property_type}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}</p><p>Thank you for your cooperation.</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_RENTAL_RECEIPT' => array(
                'name' => 'Rental Receipt',
                'description' => $this->_rentalReceiptTemplateBody()
            ),
            'RLT_POSSESSION' => array(
                'name' => 'Possession Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This letter confirms possession of <strong>{{property_name}}</strong> is handed over to you.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Property:</strong> {{property_name}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Street Address:</strong> {{ballot_street_address}}<br><strong>Area:</strong> {{ballot_area_full}}</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_TRANSFER' => array(
                'name' => 'Transfer / Mutation Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This is to certify that transfer / mutation request for <strong>{{property_name}}</strong> has been processed as per company policy.</p><p><strong>Membership No:</strong> {{deal_invoice_no}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}<br><strong>Property Number:</strong> {{ballot_property_no}}</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_CANCEL' => array(
                'name' => 'Cancellation Letter',
                'description' => '<p><strong>Date:</strong> {{letter_date}}<br><strong>Reference:</strong> {{letter_ref_no}}</p><p>Dear {{client_name}},</p><p>This is to confirm cancellation of booking for <strong>{{property_name}}</strong> (Membership {{deal_invoice_no}}) as per your request / company policy.</p><p><strong>Project:</strong> {{project_name}}<br><strong>Property Type:</strong> {{property_type}}<br><strong>Allotment No:</strong> {{ballot_allotment_no}}</p><p>Regards,<br>{{site_organization}}</p>'
            ),
            'RLT_REFUND_CLEARANCE' => array(
                'name' => 'Refund Clearance / No Claim',
                'description' => $this->_refundCompletionClearanceTemplateBody()
            ),
            'RLT_FINAL_CANCEL' => array(
                'name' => 'Final Cancellation Notice',
                'description' => $this->_finalCancellationTemplateBody()
            )
        );
        foreach ($defaults as $typeCode => $templateInfo)
        {
            $existingConditions = array(
                'Template.type' => $typeCode,
                'Template.name' => $templateInfo['name']
            );
            if ($typeCode === 'RLT_CHARGE' || $typeCode === 'RLT_DEAL_AGREEMENT' || $typeCode === 'RLT_REFUND_CLEARANCE')
            {
                $existingConditions = array(
                    'Template.type' => $typeCode,
                    'OR' => array(
                        array('Template.is_new !=' => 'Yes'),
                        array('Template.is_new' => null),
                        array('Template.is_new' => '')
                    )
                );
            }
            $existing = $this->Template->find('first', array(
                'conditions' => $existingConditions,
                'fields' => array('Template.id', 'Template.name', 'Template.description', 'Template.is_new'),
                'order' => array('Template.id' => 'asc'),
                'recursive' => -1
            ));
            if (!empty($existing['Template']['id']))
            {
                // Template already exists in database: preserve all user customizations and edits
                continue;
            }

            $this->Template->create();
            $this->Template->save(array('Template' => array(
                'name' => $templateInfo['name'],
                'description' => $templateInfo['description'],
                'type' => $typeCode,
                'status' => 'Published',
                'is_new' => 'No'
            )));
        }
    }

    protected function _ensureBookingPaymentAmountPlaceholder()
    {
        try
        {
            $this->loadModel('Template');
            $rows = (array)$this->Template->find('all', array(
                'fields' => array('Template.id', 'Template.description'),
                'conditions' => array(
                    'Template.type' => 'RLT_BOOKING',
                    'Template.description LIKE' => '%{{deal_booking_amount}}%'
                ),
                'recursive' => -1
            ));
            foreach($rows as $row)
            {
                if(empty($row['Template']['id']))
                {
                    continue;
                }
                $description = isset($row['Template']['description']) ? (string)$row['Template']['description'] : '';
                $updated = preg_replace(
                    '/(<div\b[^>]*\bid=["\']pkr-amount-numeric["\'][^>]*>)\s*\{\{deal_booking_amount\}\}\s*(<\/div>)/i',
                    '$1{{deal_booking_payment_amount}}$2',
                    $description
                );
                if($updated === null)
                {
                    $updated = $description;
                }
                if($updated === $description)
                {
                    $updated = str_replace(
                        'Amount (PKR):</label>'."\r\n".'                            <div id="pkr-amount-numeric" class="input-line font-bold text-emerald-900">{{deal_booking_amount}}</div>',
                        'Amount (PKR):</label>'."\r\n".'                            <div id="pkr-amount-numeric" class="input-line font-bold text-emerald-900">{{deal_booking_payment_amount}}</div>',
                        $updated
                    );
                }
                if($updated !== $description)
                {
                    $this->Template->id = (int)$row['Template']['id'];
                    $this->Template->save(array('Template' => array(
                        'id' => (int)$row['Template']['id'],
                        'description' => $updated
                    )), array('validate' => false, 'callbacks' => false));
                }
            }

            $letterRows = (array)$this->Realestateletter->find('all', array(
                'fields' => array('Realestateletter.id', 'Realestateletter.description'),
                'conditions' => array(
                    'Realestateletter.letter_type' => 'RLT_BOOKING',
                    'Realestateletter.description LIKE' => '%{{deal_booking_amount}}%'
                ),
                'recursive' => -1
            ));
            foreach($letterRows as $row)
            {
                if(empty($row['Realestateletter']['id']))
                {
                    continue;
                }
                $description = isset($row['Realestateletter']['description']) ? (string)$row['Realestateletter']['description'] : '';
                $updated = preg_replace(
                    '/(<div\b[^>]*\bid=["\']pkr-amount-numeric["\'][^>]*>)\s*\{\{deal_booking_amount\}\}\s*(<\/div>)/i',
                    '$1{{deal_booking_payment_amount}}$2',
                    $description
                );
                if($updated === null)
                {
                    $updated = $description;
                }
                if($updated === $description)
                {
                    $updated = str_replace(
                        'Amount (PKR):</label>'."\r\n".'                            <div id="pkr-amount-numeric" class="input-line font-bold text-emerald-900">{{deal_booking_amount}}</div>',
                        'Amount (PKR):</label>'."\r\n".'                            <div id="pkr-amount-numeric" class="input-line font-bold text-emerald-900">{{deal_booking_payment_amount}}</div>',
                        $updated
                    );
                }
                if($updated !== $description)
                {
                    $this->Realestateletter->id = (int)$row['Realestateletter']['id'];
                    $this->Realestateletter->save(array('Realestateletter' => array(
                        'id' => (int)$row['Realestateletter']['id'],
                        'description' => $updated
                    )), array('validate' => false, 'callbacks' => false));
                }
            }
        }
        catch(Exception $e)
        {
            // Template cleanup must not block letter printing.
        }
    }

    protected function _ensureBookingOccupationPlaceholder()
    {
        try
        {
            $this->loadModel('Template');
            $rows = (array)$this->Template->find('all', array(
                'fields' => array('Template.id', 'Template.description'),
                'conditions' => array(
                    'Template.type' => 'RLT_BOOKING',
                    'Template.description LIKE' => '%Occupation:%'
                ),
                'recursive' => -1
            ));
            foreach($rows as $row)
            {
                if(empty($row['Template']['id']))
                {
                    continue;
                }
                $description = isset($row['Template']['description']) ? (string)$row['Template']['description'] : '';
                if(strpos($description, '{{deal_occupation}}') !== false || strpos($description, '{{occupation}}') !== false)
                {
                    continue;
                }
                $updated = preg_replace(
                    '/(<label\b[^>]*>\s*Occupation:\s*<\/label>\s*<div\b[^>]*class=["\'][^"\']*\binput-line\b[^"\']*["\'][^>]*>)\s*(?:&nbsp;)?\s*(<\/div>)/i',
                    '$1{{deal_occupation}}$2',
                    $description,
                    1
                );
                if($updated !== null && $updated !== $description)
                {
                    $this->Template->id = (int)$row['Template']['id'];
                    $this->Template->save(array('Template' => array(
                        'id' => (int)$row['Template']['id'],
                        'description' => $updated
                    )), array('validate' => false, 'callbacks' => false, 'fieldList' => array('description')));
                }
            }

            $letterRows = (array)$this->Realestateletter->find('all', array(
                'fields' => array('Realestateletter.id', 'Realestateletter.description'),
                'conditions' => array(
                    'Realestateletter.letter_type' => 'RLT_BOOKING',
                    'Realestateletter.description LIKE' => '%Occupation:%'
                ),
                'recursive' => -1
            ));
            foreach($letterRows as $row)
            {
                if(empty($row['Realestateletter']['id']))
                {
                    continue;
                }
                $description = isset($row['Realestateletter']['description']) ? (string)$row['Realestateletter']['description'] : '';
                if(strpos($description, '{{deal_occupation}}') !== false || strpos($description, '{{occupation}}') !== false)
                {
                    continue;
                }
                $updated = preg_replace(
                    '/(<label\b[^>]*>\s*Occupation:\s*<\/label>\s*<div\b[^>]*class=["\'][^"\']*\binput-line\b[^"\']*["\'][^>]*>)\s*(?:&nbsp;)?\s*(<\/div>)/i',
                    '$1{{deal_occupation}}$2',
                    $description,
                    1
                );
                if($updated !== null && $updated !== $description)
                {
                    $this->Realestateletter->id = (int)$row['Realestateletter']['id'];
                    $this->Realestateletter->save(array('Realestateletter' => array(
                        'id' => (int)$row['Realestateletter']['id'],
                        'description' => $updated
                    )), array('validate' => false, 'callbacks' => false, 'fieldList' => array('description')));
                }
            }
        }
        catch(Exception $e)
        {
            // Keep letters available if an older template cannot be upgraded.
        }
    }

      protected function _defaultTemplateSyncTokens($typeCode)
      {
          $typeCode = trim((string)$typeCode);
          $map = array(
              'RLT_WELCOME' => array('{{deal_invoice_no}}', '{{property_name}}', '{{project_name}}'),
              'RLT_BOOKING' => array('{{deal_invoice_no}}', '{{property_name}}', '{{ballot_allotment_no}}'),
              'RLT_CHARGE' => array('{{deal_invoice_no}}', '{{deal_charges_total}}', '{{deal_charges_types_urdu}}', '{{deal_charges_total_words_urdu}}', '{{charge_work_details}}', '{{charge_work_status_urdu}}', '{{deal_charges_table}}'),
              'RLT_DEAL_AGREEMENT' => array('{{deal_invoice_no}}', '{{property_agreement_terms}}', '{{agreement_property_unit_no}}', '{{agreement_payment_plan}}', '{{witness_one_name}}', '{{witness_two_name}}'),
              'RLT_REFUND' => array('{{refund_total_paid}}', '{{refund_deduction_percent}}', '{{refund_net_refund}}'),
              'RLT_REFUND_CLEARANCE' => array('{{deal_refund_id}}', '{{refund_completion_date}}', '{{refund_completion_qr}}', '{{refund_net_refund}}'),
              'RLT_DEMARCATION' => array('{{ballot_allotment_no}}', '{{ballot_property_no}}', '{{ballot_block_name}}', '{{ballot_street_address}}', '{{ballot_area_full}}'),
            'RLT_DESIGN_MAP_APPROVAL' => array('{{deal_invoice_no}}', '{{property_name}}', '{{ballot_allotment_no}}', '{{ballot_block_name}}', '{{ballot_street_address}}'),
            'RLT_NOC' => array('{{deal_invoice_no}}', '{{property_name}}', '{{ballot_allotment_no}}', '{{ballot_property_no}}', '{{ballot_street_address}}'),
            'RLT_ALLOT' => array('{{deal_invoice_no}}', '{{property_name}}', '{{ballot_allotment_no}}', '{{ballot_property_no}}', '{{ballot_block_name}}', '{{ballot_street_address}}', '{{ballot_area_full}}'),
            'RLT_DEMAND' => array('{{deal_invoice_no}}', '{{property_name}}', '{{ballot_allotment_no}}'),
            'RLT_RECEIPT' => array('{{deal_invoice_no}}', '{{property_name}}', '{{ballot_allotment_no}}'),
            'RLT_POSSESSION' => array('{{deal_invoice_no}}', '{{property_name}}', '{{ballot_allotment_no}}', '{{ballot_street_address}}'),
            'RLT_TRANSFER' => array('{{deal_invoice_no}}', '{{property_name}}', '{{ballot_allotment_no}}', '{{ballot_property_no}}'),
            'RLT_CANCEL' => array('{{deal_invoice_no}}', '{{property_name}}', '{{ballot_allotment_no}}'),
            'RLT_FINAL_CANCEL' => array('{{overdue_amount}}', '{{overdue_installments_count}}', '{{bank_account_no}}', '{{ceo_signature}}', '{{chairman_signature}}')
        );
        return isset($map[$typeCode]) ? (array)$map[$typeCode] : array();
    }

    protected function _letterNotificationTypeMap()
    {
        return array(
            'RLT_WELCOME' => 'LWL',
            'RLT_BOOKING' => 'LBK',
            'RLT_CHARGE' => 'LCH',
            'RLT_DEAL_AGREEMENT' => 'AGR',
            'RLT_DEMARCATION' => 'LDM',
            'RLT_DESIGN_MAP_APPROVAL' => 'LMP',
            'RLT_NOC' => 'NOC',
            'RLT_ALLOT' => 'LAT',
            'RLT_DEMAND' => 'PDN',
            'RLT_RECEIPT' => 'PAC',
            'RLT_POSSESSION' => 'POS',
            'RLT_TRANSFER' => 'TRF',
            'RLT_CANCEL' => 'CAN',
            'RLT_FINAL_CANCEL' => 'FCL'
        );
    }

    protected function _letterIssueFallbackTypeCode()
    {
        return 'LIS';
    }

    protected function _letterCancelTypeCode()
    {
        return 'LCN';
    }

    protected function _letterNotificationTemplateDefaults()
    {
        $defaults = array();
        foreach ((array)$this->_letterNotificationTypeMap() as $letterType => $typeCode)
        {
            $typeLabel = isset($this->_letterTypeArr[$letterType]) ? trim((string)$this->_letterTypeArr[$letterType]) : trim((string)$letterType);
            if ($typeLabel === '')
            {
                $typeLabel = 'Letter';
            }
            $defaults[$typeCode] = array(
                'sms_name' => 'Letter Issued ' . $typeLabel,
                'sms_body' => 'Dear $clientName, your ' . $typeLabel . ' has been issued. Ref: $referenceNo, File: $invoiceNo, Property: $propertyName. $url',
                'email_name' => 'Letter Issued ' . $typeLabel,
                'email_body' => '<p>Dear $clientName,</p><p>Your <strong>' . $typeLabel . '</strong> has been issued.</p><p><strong>Reference No:</strong> $referenceNo<br><strong>Membership No:</strong> $invoiceNo<br><strong>Property:</strong> $propertyName<br><strong>Project:</strong> $projectName</p><p>You can view/print from: <a href="$url" target="_blank">$url</a></p><p>Regards,<br>$siteName<br>$contactNo</p>'
            );
        }

        $fallbackCode = $this->_letterIssueFallbackTypeCode();
        $defaults[$fallbackCode] = array(
            'sms_name' => 'Letter Issued Generic',
            'sms_body' => 'Dear $clientName, your $letterType has been issued. Ref: $referenceNo, File: $invoiceNo. $url',
            'email_name' => 'Letter Issued Generic',
            'email_body' => '<p>Dear $clientName,</p><p>Your <strong>$letterType</strong> has been issued.</p><p><strong>Reference No:</strong> $referenceNo<br><strong>Membership No:</strong> $invoiceNo<br><strong>Property:</strong> $propertyName</p><p><a href="$url" target="_blank">View Letter</a></p><p>Regards,<br>$siteName</p>'
        );

        $cancelCode = $this->_letterCancelTypeCode();
        $defaults[$cancelCode] = array(
            'sms_name' => 'Letter Cancel Notification',
            'sms_body' => 'Dear $clientName, your $letterType has been cancelled. Ref: $referenceNo, File: $invoiceNo, Property: $propertyName.',
            'email_name' => 'Letter Cancel Notification',
            'email_body' => '<p>Dear $clientName,</p><p>Your <strong>$letterType</strong> has been cancelled.</p><p><strong>Reference No:</strong> $referenceNo<br><strong>Membership No:</strong> $invoiceNo<br><strong>Property:</strong> $propertyName</p><p>For details please contact: $contactNo</p><p>Regards,<br>$siteName</p>'
        );
        return $defaults;
    }

    protected function _ensureLetterNotificationTemplates()
    {
        try
        {
            $this->loadModel('Smstemplate');
            $this->loadModel('Emailtemplate');
        }
        catch (Exception $e)
        {
            return;
        }

        $defaults = (array)$this->_letterNotificationTemplateDefaults();
        foreach ($defaults as $typeCode => $templateData)
        {
            $typeCode = strtoupper(substr(trim((string)$typeCode), 0, 3));
            if ($typeCode === '')
            {
                continue;
            }
            try
            {
                $smsExists = (array)$this->Smstemplate->find('first', array(
                    'fields' => array('Smstemplate.id'),
                    'conditions' => array('Smstemplate.type' => $typeCode),
                    'order' => array('Smstemplate.id' => 'asc'),
                    'recursive' => -1
                ));
                if (empty($smsExists['Smstemplate']['id']))
                {
                    $this->Smstemplate->create();
                    $this->Smstemplate->save(array('Smstemplate' => array(
                        'name' => isset($templateData['sms_name']) ? (string)$templateData['sms_name'] : 'Letter SMS',
                        'description' => isset($templateData['sms_body']) ? (string)$templateData['sms_body'] : '',
                        'status' => 'Published',
                        'type' => $typeCode
                    )), array('validate' => false, 'callbacks' => false));
                }
            }
            catch (Exception $e)
            {
                // Keep flow running even if SMS template insert fails.
            }

            try
            {
                $emailExists = (array)$this->Emailtemplate->find('first', array(
                    'fields' => array('Emailtemplate.id'),
                    'conditions' => array('Emailtemplate.type' => $typeCode),
                    'order' => array('Emailtemplate.id' => 'asc'),
                    'recursive' => -1
                ));
                if (empty($emailExists['Emailtemplate']['id']))
                {
                    $this->Emailtemplate->create();
                    $this->Emailtemplate->save(array('Emailtemplate' => array(
                        'name' => isset($templateData['email_name']) ? (string)$templateData['email_name'] : 'Letter Email',
                        'description' => isset($templateData['email_body']) ? (string)$templateData['email_body'] : '',
                        'status' => 'Published',
                        'type' => $typeCode
                    )), array('validate' => false, 'callbacks' => false));
                }
            }
            catch (Exception $e)
            {
                // Keep flow running even if Email template insert fails.
            }
        }
    }

    protected function _letterNotificationTypeForEvent($letterType, $event = 'issue')
    {
        $letterType = trim((string)$letterType);
        $event = strtolower(trim((string)$event));
        if ($event === 'cancel')
        {
            return $this->_letterCancelTypeCode();
        }
        $map = (array)$this->_letterNotificationTypeMap();
        if (isset($map[$letterType]))
        {
            return $map[$letterType];
        }
        return $this->_letterIssueFallbackTypeCode();
    }

    protected function _letterNotificationRecipientMobile($letterRow)
    {
        $client = isset($letterRow['Client']) ? (array)$letterRow['Client'] : array();
        $mobile = isset($client['mobile']) ? trim((string)$client['mobile']) : '';
        if ($mobile === '')
        {
            $mobile = isset($client['phone']) ? trim((string)$client['phone']) : '';
        }
        return preg_replace('/\s+/', '', (string)$mobile);
    }

    protected function _letterWhatsappNumber($mobileNo)
    {
        $mobileNo = preg_replace('/\D+/', '', (string)$mobileNo);
        if ($mobileNo === '')
        {
            return '';
        }
        if (substr($mobileNo, 0, 2) === '00')
        {
            $mobileNo = substr($mobileNo, 2);
        }
        if (substr($mobileNo, 0, 1) === '0')
        {
            $mobileNo = '92'.substr($mobileNo, 1);
        }
        return $mobileNo;
    }

    protected function _letterPrintUrl($letterRow)
    {
        $letter = isset($letterRow['Realestateletter']) ? (array)$letterRow['Realestateletter'] : array();
        $deal = isset($letterRow['Deal']) ? (array)$letterRow['Deal'] : array();
        $letterId = isset($letter['id']) ? (int)$letter['id'] : 0;
        if ($letterId <= 0)
        {
            return '';
        }
        $dealId = isset($letter['deal_id']) ? (int)$letter['deal_id'] : 0;
        if ($dealId <= 0 && isset($deal['id']))
        {
            $dealId = (int)$deal['id'];
        }
        if ($dealId > 0)
        {
            return Router::url(array(
                'plugin' => false,
                'admin' => false,
                'controller' => 'MyDeals',
                'action' => 'printletter',
                $dealId,
                $letterId
            ), true);
        }
        return Router::url(array('controller' => 'Realestateletters', 'action' => 'printletter', $letterId), true);
    }

    protected function _buildLetterWhatsappMessage($letterRow, $event = 'issue')
    {
        $event = strtolower(trim((string)$event));
        if ($event !== 'cancel')
        {
            $event = 'issue';
        }
        $map = (array)$this->_letterNotificationTokenMap($letterRow, $event);
        $clientName = isset($map['$clientName']) ? trim((string)$map['$clientName']) : '';
        $letterType = isset($map['$letterType']) ? trim((string)$map['$letterType']) : '';
        $referenceNo = isset($map['$referenceNo']) ? trim((string)$map['$referenceNo']) : '';
        $invoiceNo = isset($map['$invoiceNo']) ? trim((string)$map['$invoiceNo']) : '';
        $propertyName = isset($map['$propertyName']) ? trim((string)$map['$propertyName']) : '';
        $letterDate = isset($map['$date']) ? trim((string)$map['$date']) : '';
        $contactNo = isset($map['$contactNo']) ? trim((string)$map['$contactNo']) : '';
        $siteName = isset($map['$siteName']) ? trim((string)$map['$siteName']) : '';
        $letterUrl = $this->_letterPrintUrl($letterRow);
        if ($letterUrl === '' && !empty($map['$url']))
        {
            $letterUrl = trim((string)$map['$url']);
        }
        $statusText = ($event === 'cancel') ? 'cancelled' : 'issued';

        $lines = array();
        $lines[] = '*Letter Notification*';
        $lines[] = '';
        $lines[] = 'Dear *'.($clientName !== '' ? $clientName : 'Customer').'*,';
        $lines[] = 'Your *'.($letterType !== '' ? $letterType : 'letter').'* has been '.$statusText.'.';
        $lines[] = '';
        if ($referenceNo !== '') $lines[] = '*Reference No:* '.$referenceNo;
        if ($invoiceNo !== '') $lines[] = '*Membership No:* '.$invoiceNo;
        if ($propertyName !== '') $lines[] = '*Property/File:* '.$propertyName;
        if ($letterDate !== '') $lines[] = '*Letter Date:* '.$letterDate;
        if ($letterUrl !== '') $lines[] = '*View / Print Letter:* '.$letterUrl;
        $lines[] = '';
        $lines[] = 'Regards,';
        $lines[] = '*'.($siteName !== '' ? $siteName : (string)$this->siteName).'*';
        if ($contactNo !== '') $lines[] = 'Contact: '.$contactNo;
        return trim(implode("\n", $lines));
    }

    protected function _buildLetterWhatsappLink($letterRow, $event = 'issue')
    {
        $mobileNo = $this->_letterNotificationRecipientMobile($letterRow);
        $whatsappNo = $this->_letterWhatsappNumber($mobileNo);
        if ($whatsappNo === '')
        {
            return array('success' => false, 'message' => __('Client WhatsApp number is missing.'));
        }
        $message = $this->_buildLetterWhatsappMessage($letterRow, $event);
        if ($message === '')
        {
            return array('success' => false, 'message' => __('Unable to prepare WhatsApp message.'));
        }
        return array(
            'success' => true,
            'message' => __('WhatsApp letter notification is ready.'),
            'url' => 'https://wa.me/'.$whatsappNo.'?text='.rawurlencode($message)
        );
    }

    protected function _letterNotificationRecipientEmail($letterRow)
    {
        $client = isset($letterRow['Client']) ? (array)$letterRow['Client'] : array();
        return trim((string)(isset($client['email']) ? $client['email'] : ''));
    }

    protected function _letterNotificationTokenMap($letterRow, $event = 'issue')
    {
        $event = strtolower(trim((string)$event));
        if ($event !== 'cancel')
        {
            $event = 'issue';
        }
        $letter = isset($letterRow['Realestateletter']) ? (array)$letterRow['Realestateletter'] : array();
        $deal = isset($letterRow['Deal']) ? (array)$letterRow['Deal'] : array();
        $client = isset($letterRow['Client']) ? (array)$letterRow['Client'] : array();
        $property = isset($letterRow['Property']) ? (array)$letterRow['Property'] : array();
        $project = isset($letterRow['Project']) ? (array)$letterRow['Project'] : array();

        $placeholderMap = (array)$this->_placeholderMap($letterRow);
        $letterTypeCode = isset($letter['letter_type']) ? trim((string)$letter['letter_type']) : '';
        $letterTypeLabel = isset($this->_letterTypeArr[$letterTypeCode]) ? (string)$this->_letterTypeArr[$letterTypeCode] : $letterTypeCode;
        $letterDate = '';
        if (!empty($letter['letter_date']))
        {
            $letterDate = $this->_formatDateValue($letter['letter_date'], date($this->dtFormat));
        }
        else
        {
            $letterDate = date($this->dtFormat);
        }
        $letterTypeCode = isset($letter['letter_type']) ? trim((string)$letter['letter_type']) : '';
        $letterTypeLabel = isset($this->_letterTypeArr[$letterTypeCode]) ? (string)$this->_letterTypeArr[$letterTypeCode] : $letterTypeCode;
        if ($letterTypeLabel === '')
        {
            $letterTypeLabel = isset($letter['title']) ? trim((string)$letter['title']) : '';
        }
        $referenceNo = isset($letter['reference_no']) ? trim((string)$letter['reference_no']) : '';
        $invoiceNo = isset($deal['invoice_no']) ? trim((string)$deal['invoice_no']) : '';
        $propertyName = isset($property['name']) ? trim((string)$property['name']) : '';
        $projectName = isset($project['name']) ? trim((string)$project['name']) : '';
        $clientName = isset($client['name']) ? trim((string)$client['name']) : '';
        $allotmentNo = isset($placeholderMap['{{ballot_allotment_no}}']) ? trim((string)$placeholderMap['{{ballot_allotment_no}}']) : '';
        $letterId = isset($letter['id']) ? (int)$letter['id'] : 0;
        $letterUrl = $this->_letterPrintUrl($letterRow);
        $eventLabel = ($event === 'cancel') ? 'Cancelled' : 'Issued';

        $variableMap = array(
            '$siteName' => (string)$this->siteName,
            '$contactNo' => (string)$this->contact,
            '$url' => $letterUrl,
            '$date' => $letterDate,
            '$clientName' => $clientName,
            '$email' => $this->_letterNotificationRecipientEmail($letterRow),
            '$mobileNo' => $this->_letterNotificationRecipientMobile($letterRow),
            '$referenceNo' => $referenceNo,
            '$letterNo' => $referenceNo,
            '$invoiceNo' => $invoiceNo,
            '$propertyName' => $propertyName,
            '$projectName' => $projectName,
            '$allotmentNo' => $allotmentNo,
            '$letterType' => $letterTypeLabel,
            '$letterTitle' => isset($letter['title']) ? (string)$letter['title'] : $letterTypeLabel,
            '$letterEvent' => $eventLabel,
            '$letterStatus' => isset($letter['status']) ? (string)$letter['status'] : ''
        );

        return array_merge($placeholderMap, $variableMap);
    }

    protected function _renderLetterNotificationTemplate($templateBody, $letterRow, $event = 'issue')
    {
        $templateBody = (string)$templateBody;
        if ($templateBody === '')
        {
            return '';
        }
        return strtr($templateBody, $this->_letterNotificationTokenMap($letterRow, $event));
    }

    protected function _publishedNotificationTemplate($modelName, $typeCode)
    {
        $typeCode = strtoupper(substr(trim((string)$typeCode), 0, 3));
        if ($typeCode === '')
        {
            return array();
        }
        try
        {
            $this->loadModel($modelName);
            return (array)$this->{$modelName}->find('first', array(
                'fields' => array($modelName . '.id', $modelName . '.name', $modelName . '.description', $modelName . '.status'),
                'conditions' => array(
                    $modelName . '.type' => $typeCode,
                    $modelName . '.status' => 'Published'
                ),
                'order' => array($modelName . '.id' => 'asc'),
                'recursive' => -1
            ));
        }
        catch (Exception $e)
        {
            return array();
        }
    }

    protected function _sendLetterSmsNotification($letterRow, $event = 'issue')
    {
        if (empty($this->smsNotification))
        {
            return;
        }
        if (empty($this->smsSettingArr['Smssetting']['api']))
        {
            return;
        }
        $mobileNo = $this->_letterNotificationRecipientMobile($letterRow);
        if ($mobileNo === '')
        {
            return;
        }

        $letterType = isset($letterRow['Realestateletter']['letter_type']) ? trim((string)$letterRow['Realestateletter']['letter_type']) : '';
        $templateTypeCode = $this->_letterNotificationTypeForEvent($letterType, $event);
        $templateRow = $this->_publishedNotificationTemplate('Smstemplate', $templateTypeCode);
        if (empty($templateRow['Smstemplate']['id']) && strtolower(trim((string)$event)) !== 'cancel' && $templateTypeCode !== $this->_letterIssueFallbackTypeCode())
        {
            $templateRow = $this->_publishedNotificationTemplate('Smstemplate', $this->_letterIssueFallbackTypeCode());
        }
        if (empty($templateRow['Smstemplate']['id']))
        {
            return;
        }
        $message = $this->_renderLetterNotificationTemplate(
            isset($templateRow['Smstemplate']['description']) ? $templateRow['Smstemplate']['description'] : '',
            $letterRow,
            $event
        );
        if (trim($message) === '')
        {
            return;
        }
        try
        {
            $this->CustomFunction->sendSms($mobileNo, $message, $this->smsSettingArr);
        }
        catch (Exception $e)
        {
            // Ignore send failure; letter save/status flow must continue.
        }
    }

    protected function _sendLetterEmailNotification($letterRow, $event = 'issue')
    {
        if (empty($this->emailNotification))
        {
            return;
        }
        $email = $this->_letterNotificationRecipientEmail($letterRow);
        if ($email === '')
        {
            return;
        }

        $letterType = isset($letterRow['Realestateletter']['letter_type']) ? trim((string)$letterRow['Realestateletter']['letter_type']) : '';
        $templateTypeCode = $this->_letterNotificationTypeForEvent($letterType, $event);
        $templateRow = $this->_publishedNotificationTemplate('Emailtemplate', $templateTypeCode);
        if (empty($templateRow['Emailtemplate']['id']) && strtolower(trim((string)$event)) !== 'cancel' && $templateTypeCode !== $this->_letterIssueFallbackTypeCode())
        {
            $templateRow = $this->_publishedNotificationTemplate('Emailtemplate', $this->_letterIssueFallbackTypeCode());
        }
        if (empty($templateRow['Emailtemplate']['id']))
        {
            return;
        }

        $message = $this->_renderLetterNotificationTemplate(
            isset($templateRow['Emailtemplate']['description']) ? $templateRow['Emailtemplate']['description'] : '',
            $letterRow,
            $event
        );
        if (trim($message) === '')
        {
            return;
        }

        $subject = isset($templateRow['Emailtemplate']['name']) ? trim((string)$templateRow['Emailtemplate']['name']) : '';
        if ($subject === '')
        {
            $subject = 'Letter Notification';
        }

        try
        {
            $Email = new CakeEmail();
            $Email->transport($this->emailSettype);
            if ($this->emailSettype === "Smtp" || $this->emailSettype === "SMTP")
            {
                $Email->config(array(
                    'host' => $this->emailHost,
                    'port' => $this->emailPort,
                    'username' => $this->emailUsername,
                    'password' => $this->emailPassword
                ));
            }
            $Email->from(array($this->siteEmail => $this->siteName));
            $Email->to($email);
            $Email->template('default');
            $Email->emailFormat('html');
            $Email->subject($subject);
            $Email->send($message);
        }
        catch (Exception $e)
        {
            // Ignore send failure; letter save/status flow must continue.
        }
    }

    protected function _dispatchLetterNotifications($letterRow, $event = 'issue')
    {
        if (empty($letterRow['Realestateletter']['id']))
        {
            return;
        }
        $event = strtolower(trim((string)$event));
        if ($event !== 'cancel')
        {
            $event = 'issue';
        }
        $this->_sendLetterEmailNotification($letterRow, $event);
        $this->_sendLetterSmsNotification($letterRow, $event);

        // Automated WhatsApp & In-Portal Letter Notification Delivery
        if ($event === 'issue' && !empty($letterRow['Realestateletter']['id'])) {
            try {
                App::uses('WhatsAppAutomationComponent', 'Controller/Component');
                $waLetter = new WhatsAppAutomationComponent(new ComponentCollection());
                $waLetter->sendLetterIssuedAlert((int)$letterRow['Realestateletter']['id']);
            } catch (Exception $eWaLet) {}
        }
    }

    protected function _letterNotificationEventByStatus($statusText)
    {
        $statusText = trim((string)$statusText);
        if ($statusText === 'Unpublished')
        {
            return 'cancel';
        }
        return 'issue';
    }

    protected function _letterIndexRedirectUrlFromRow($row, $projectId = 0)
    {
        $typeCode = isset($row['Realestateletter']['letter_type']) ? trim((string)$row['Realestateletter']['letter_type']) : '';
        $dealId = isset($row['Realestateletter']['deal_id']) ? (int)$row['Realestateletter']['deal_id'] : 0;
        $projectId = (int)$projectId;
        $redirectUrl = array('action' => 'index');
        if ($typeCode !== '')
        {
            $redirectUrl[] = $typeCode;
        }
        if ($dealId > 0)
        {
            $redirectUrl[] = $dealId;
        }
        if ($projectId > 0)
        {
            $redirectUrl['?'] = array('project_id' => $projectId);
        }
        return $redirectUrl;
    }

    protected function _temporaryPossessionTemplateName()
    {
        return 'Temporary Possession Letter';
    }

    protected function _temporaryPossessionTemplateBody()
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{letter_title}}</title>
    <style>
        /* WV_TEMPORARY_POSSESSION_TEMPLATE_V2 */
        @page {
            size: legal;
            margin: 0.5in;
        }
        body {
            margin: 0;
            padding: 0;
            background: #f3f4f6;
            font-family: Arial, sans-serif;
            color: #111;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .legal-page {
            width: 8.5in;
            min-height: 14in;
            margin: 24px auto;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
            box-sizing: border-box;
            padding: 2in 0.5in 1in;
            position: relative;
        }
        .floating-tools {
            position: fixed;
            right: 20px;
            bottom: 24px;
            z-index: 999;
        }
        .floating-tools button {
            display: block;
            margin-top: 10px;
            border: 0;
            border-radius: 999px;
            color: #fff;
            font-weight: 700;
            padding: 11px 18px;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .btn-word { background: #15a8b4; }
        .btn-pdf { background: #d12229; }
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        .meta-left {
            flex: 1;
            padding-right: 14px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            font-size: 16px;
            font-weight: 700;
        }
        .meta-right {
            text-align: right;
            line-height: 1.5;
        }
        .title-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin: 12px 0 18px;
        }
        .temp-possession-title-qr {
            width: 76px;
            min-width: 76px;
            height: 76px;
            border: 1px solid #000;
            padding: 2px;
            background: #fff;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .temp-possession-title-qr img {
            width: 100%;
            height: 100%;
            display: block;
        }
        .title-wrap-spacer {
            width: 76px;
            min-width: 76px;
            height: 76px;
        }
        .title-wrap h1 {
            margin: 0;
            color: #d12229;
            font-size: 34px;
            font-style: italic;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 0 #15a8b4;
            text-align: center;
            flex: 1 1 auto;
        }
        .photo-box {
            width: 110px;
            min-height: 140px;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 4px;
            box-sizing: border-box;
            overflow: hidden;
        }
        .photo-box img {
            max-width: 100%;
            max-height: 130px;
            display: block;
        }
        .editable {
            border-bottom: 1px dashed #888;
            display: inline-block;
            min-width: 40px;
            padding: 0 3px;
        }
        .intro {
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.5;
            margin: 0 0 14px;
        }
        .thick-table {
            width: 100%;
            border-collapse: collapse;
            border: 3px solid #000;
            margin-bottom: 14px;
            table-layout: fixed;
        }
        .thick-table th {
            border: 3px solid #000;
            background: #8c73a6;
            color: #fff;
            text-transform: uppercase;
            text-align: left;
            font-size: 16px;
            padding: 8px;
        }
        .thick-table td {
            border: 3px solid #000;
            padding: 8px;
            font-size: 14px;
            font-weight: 700;
            vertical-align: top;
            word-wrap: break-word;
        }
        .id-media img {
            max-width: 150px;
            max-height: 90px;
            margin-right: 8px;
        }
        .box {
            border: 2px solid #000;
            background: #f9fafb;
            padding: 10px;
            margin-bottom: 14px;
        }
        .box h3 {
            margin: 0 0 8px;
            color: #d12229;
            text-transform: uppercase;
            text-decoration: underline;
            font-size: 14px;
        }
        .box ol {
            margin: 0;
            padding-left: 20px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.45;
        }
        .box li { margin-bottom: 8px; }
        .terms h3 {
            margin: 0 0 8px;
            font-size: 14px;
            text-transform: uppercase;
            text-decoration: underline;
        }
        .terms ol {
            margin: 0;
            padding-left: 20px;
            font-size: 14px;
            font-weight: 700;
            line-height: 1.45;
        }
        .terms li { margin-bottom: 8px; }
        .sign-row {
            margin-top: 56px;
            display: flex;
            justify-content: space-between;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
        }
        .sign-col { width: 31%; }
        .sign-line {
            height: 24px;
            border-bottom: 2px solid #000;
            margin-bottom: 7px;
        }
        @media print {
            body { background: #fff; }
            .legal-page {
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: auto;
                padding-top: 2in !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                padding-bottom: 1in !important;
            }
            .no-print { display: none !important; }
            .editable { border-bottom: 0 !important; }
        }
    </style>
</head>
<body>
    <div class="floating-tools no-print">
        <button class="btn-word" onclick="downloadAsWord()">Download as Word</button>
        <button class="btn-pdf" onclick="window.print()">Download as PDF</button>
    </div>

    <div class="legal-page" id="wv-letter-page">
        <div class="top-header">
            <div class="meta-left">
                <div class="meta-row">
                    <div>Ref: <span class="editable">{{letter_ref_no}}</span></div>
                    <div class="meta-right">
                        <div>Date: <span class="editable">{{letter_date}}</span></div>
                        <div>File No: <span class="editable">{{deal_invoice_no}}</span></div>
                    </div>
                </div>
                <div class="title-wrap">
                    <div class="temp-possession-title-qr" title="Letter QR">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&amp;ecc=M&amp;qzone=2&amp;margin=1&amp;data=Letter%20Type%3A%20{{letter_type_label}}%0AClient%20Name%3A%20{{client_name}}%0AAlloted%20Property%20Unit%20No%20%3A{{property_unit}}%0AIssue%20Date%3A%20{{letter_date}}" alt="Temporary Possession QR">
                    </div>
                    <h1>{{letter_title}}</h1>
                    <div class="title-wrap-spacer"></div>
                </div>
            </div>
            <div class="photo-box">
                {{client_profile_photo}}
            </div>
        </div>

        <p class="intro">
            This is to certify that <span class="editable">{{letter_title}}</span> of Property ID
            <span class="editable">{{property_name}}</span>, Building <span class="editable">{{project_name}}</span>,
            Floor <span class="editable">{{property_unit}}</span> is being handed over to the allottee detailed below.
        </p>

        <table class="thick-table">
            <thead>
                <tr>
                    <th>Applicant Detail</th>
                    <th>{{letter_title}} Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>NAME: <span class="editable">{{client_name}}</span></td>
                    <td>Property ID: <span class="editable">{{property_name}}</span></td>
                </tr>
                <tr>
                    <td>S/O, W/O: <span class="editable">{{client_father_name}}</span></td>
                    <td>Building: <span class="editable">{{project_name}}</span></td>
                </tr>
                <tr>
                    <td>CNIC: <span class="editable">{{client_cnic}}</span></td>
                    <td>Floor: <span class="editable">{{property_unit}}</span></td>
                </tr>
                <tr>
                    <td>Address: <span class="editable">{{client_address}}</span></td>
                    <td>Cell No: <span class="editable">{{client_mobile}}</span></td>
                </tr>
                <tr>
                    <td class="id-media">
                        <strong>ID Front:</strong><br>{{client_id_front_image}}
                    </td>
                    <td class="id-media">
                        <strong>ID Back:</strong><br>{{client_id_back_image}}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="box">
            <h3>Special Conditions Regarding Multiple Floors</h3>
            <ol>
                <li>The allottee is currently being granted <span class="editable">{{letter_title}}</span> of the specific floor mentioned above for immediate use or construction.</li>
                <li>It is acknowledged that the allottee has booked and owns a total of <span class="editable">Three (3)</span> floors in the project.</li>
                <li>At final possession, after completion, the allottee will be handed over all 3 floors floor-wise on one side of the newly constructed building.</li>
            </ol>
        </div>

        <div class="terms">
            <h3>General Terms and Conditions</h3>
            <ol>
                <li>On personal inspection of the plot or floor, I am fully satisfied and certify that there is no encroachment.</li>
                <li>I shall abide by the by-laws of TMA and construct the building as per approved plans.</li>
                <li>Before applying for construction, I shall obtain a No Objection Certificate and submit it to the relevant department.</li>
                <li>This <span class="editable">{{letter_title}}</span> does not grant final ownership rights until all dues and conditions are fulfilled.</li>
            </ol>
        </div>

        <div class="sign-row">
            <div class="sign-col">
                <div class="sign-line"></div>
                <div>Possession Department</div>
            </div>
            <div class="sign-col">
                <div class="sign-line"></div>
                <div>Design Wing</div>
            </div>
            <div class="sign-col">
                <div class="sign-line"></div>
                <div>Allottee</div>
            </div>
        </div>
    </div>

    <script>
        function downloadAsWord() {
            var sourceNode = document.getElementById('wv-letter-page');
            if (!sourceNode) {
                return;
            }
            var clone = sourceNode.cloneNode(true);
            var html = ''
                + "<html xmlns:o='urn:schemas-microsoft-com:office:office' "
                + "xmlns:w='urn:schemas-microsoft-com:office:word' "
                + "xmlns='http://www.w3.org/TR/REC-html40'>"
                + "<head><meta charset='utf-8'><title>{{letter_title}}</title>"
                + "<style>"
                + "@page Section1{size:8.5in 14in;margin:2.1in 0.5in 1.2in 0.5in;}"
                + "div.Section1{page:Section1;font-family:Arial,sans-serif;color:#111;}"
                + "table{border-collapse:collapse;width:100%;}"
                + "th,td{border:2px solid #000;padding:8px;vertical-align:top;}"
                + ".editable{border:0;}"
                + "</style></head><body><div class='Section1'>"
                + clone.outerHTML
                + "</div></body></html>";
            var blob = new Blob(['\ufeff', html], { type: 'application/msword' });
            var url = URL.createObjectURL(blob);
            var a = document.createElement('a');
            a.href = url;
            a.download = '{{letter_title}}.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    </script>
</body>
</html>
HTML;
    }

    protected function _ensureAdvancedTemporaryPossessionTemplate()
    {
        $this->loadModel('Template');
        $typeCode = 'RLT_POSSESSION';
        $templateName = $this->_temporaryPossessionTemplateName();
        $latestTemplateBody = $this->_temporaryPossessionTemplateBody();
        $existing = $this->Template->find('first', array(
            'fields' => array('Template.id', 'Template.description'),
            'conditions' => array(
                'Template.type' => $typeCode,
                'Template.name' => $templateName
            ),
            'recursive' => -1
        ));
        if (!empty($existing['Template']['id']))
        {
            $existingDescription = isset($existing['Template']['description']) ? (string)$existing['Template']['description'] : '';
            $hasV2Marker = (strpos($existingDescription, 'WV_TEMPORARY_POSSESSION_TEMPLATE_V2') !== false);
            if (!$hasV2Marker)
            {
                $hasV1Marker = (strpos($existingDescription, 'WV_TEMPORARY_POSSESSION_TEMPLATE_V1') !== false);
                $hasTitleQrBlock = (strpos($existingDescription, 'temp-possession-title-qr') !== false);
                $hasLetterTypeToken = (strpos($existingDescription, '{{letter_type_label}}') !== false);
                if ($hasV1Marker || !$hasTitleQrBlock || !$hasLetterTypeToken)
                {
                    $this->Template->id = (int)$existing['Template']['id'];
                    $this->Template->save(array('Template' => array(
                        'id' => (int)$existing['Template']['id'],
                        'description' => $latestTemplateBody
                    )), array('validate' => false, 'callbacks' => false, 'fieldList' => array('description')));
                }
            }
            return;
        }
        $this->Template->create();
        $this->Template->save(array('Template' => array(
            'name' => $templateName,
            'description' => $latestTemplateBody,
            'type' => $typeCode,
            'status' => 'Published',
            'is_new' => 'No'
        )), false);
    }

    protected function _referenceNo()
    {
        $last = $this->Realestateletter->find('first', array(
            'fields' => array('Realestateletter.id'),
            'order' => array('Realestateletter.id' => 'desc'),
            'recursive' => -1
        ));
        $nextId = 1;
        if (!empty($last['Realestateletter']['id']))
        {
            $nextId = ((int)$last['Realestateletter']['id']) + 1;
        }
        return 'REL-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    protected function _lettersReferenceDefaults()
    {
        $prefixBase = trim((string)$this->siteShort);
        if ($prefixBase === '')
        {
            $prefixBase = 'REL';
        }
        return array(
            'letters_prefix' => $prefixBase . '-' . date('Y') . '-',
            'letters_next_no' => 1,
            'welcome_letter_prefix' => 'WEL-' . date('Y') . '-',
            'welcome_letter_next_no' => 1,
            'booking_letter_prefix' => 'BKG-' . date('Y') . '-',
            'booking_letter_next_no' => 1,
            'charge_letter_prefix' => 'CHG-' . date('Y') . '-',
            'charge_letter_next_no' => 1,
            'deal_agreement_letter_prefix' => 'AGR-' . date('Y') . '-',
            'deal_agreement_letter_next_no' => 1,
            'demarcation_letter_prefix' => 'DEM-' . date('Y') . '-',
            'demarcation_letter_next_no' => 1,
            'design_map_approval_prefix' => 'DMA-' . date('Y') . '-',
            'design_map_approval_next_no' => 1,
            'noc_letter_prefix' => 'NOC-' . date('Y') . '-',
            'noc_letter_next_no' => 1,
            'allot_letter_prefix' => 'ALT-' . date('Y') . '-',
            'allot_letter_next_no' => 1,
            'demand_letter_prefix' => 'DMN-' . date('Y') . '-',
            'demand_letter_next_no' => 1,
            'receipt_letter_prefix' => 'RCP-' . date('Y') . '-',
            'receipt_letter_next_no' => 1,
            'possession_letter_prefix' => 'POS-' . date('Y') . '-',
            'possession_letter_next_no' => 1,
            'transfer_letter_prefix' => 'TRF-' . date('Y') . '-',
            'transfer_letter_next_no' => 1,
            'cancel_letter_prefix' => 'CNL-' . date('Y') . '-',
            'cancel_letter_next_no' => 1,
            'final_cancel_letter_prefix' => 'FCL-' . date('Y') . '-',
            'final_cancel_letter_next_no' => 1,
            'refund_letter_prefix' => 'RFD-' . date('Y') . '-',
            'refund_letter_next_no' => 1
        );
    }

    protected function _letterReferenceFieldMap($letterType = '')
    {
        $map = array(
            'prefix_field' => 'letters_prefix',
            'next_field' => 'letters_next_no',
            'default_prefix' => 'REL-' . date('Y') . '-'
        );
        $letterType = trim((string)$letterType);
        if ($letterType === 'RLT_WELCOME')
        {
            $map = array('prefix_field' => 'welcome_letter_prefix', 'next_field' => 'welcome_letter_next_no', 'default_prefix' => 'WEL-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_BOOKING')
        {
            $map = array('prefix_field' => 'booking_letter_prefix', 'next_field' => 'booking_letter_next_no', 'default_prefix' => 'BKG-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_CHARGE')
        {
            $map = array('prefix_field' => 'charge_letter_prefix', 'next_field' => 'charge_letter_next_no', 'default_prefix' => 'CHG-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_DEAL_AGREEMENT')
        {
            $map = array('prefix_field' => 'deal_agreement_letter_prefix', 'next_field' => 'deal_agreement_letter_next_no', 'default_prefix' => 'AGR-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_DEMARCATION')
        {
            $map = array('prefix_field' => 'demarcation_letter_prefix', 'next_field' => 'demarcation_letter_next_no', 'default_prefix' => 'DEM-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_DESIGN_MAP_APPROVAL')
        {
            $map = array('prefix_field' => 'design_map_approval_prefix', 'next_field' => 'design_map_approval_next_no', 'default_prefix' => 'DMA-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_NOC')
        {
            $map = array('prefix_field' => 'noc_letter_prefix', 'next_field' => 'noc_letter_next_no', 'default_prefix' => 'NOC-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_ALLOT')
        {
            $map = array('prefix_field' => 'allot_letter_prefix', 'next_field' => 'allot_letter_next_no', 'default_prefix' => 'ALT-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_DEMAND')
        {
            $map = array('prefix_field' => 'demand_letter_prefix', 'next_field' => 'demand_letter_next_no', 'default_prefix' => 'DMN-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_RECEIPT')
        {
            $map = array('prefix_field' => 'receipt_letter_prefix', 'next_field' => 'receipt_letter_next_no', 'default_prefix' => 'RCP-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_POSSESSION')
        {
            $map = array('prefix_field' => 'possession_letter_prefix', 'next_field' => 'possession_letter_next_no', 'default_prefix' => 'POS-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_TRANSFER')
        {
            $map = array('prefix_field' => 'transfer_letter_prefix', 'next_field' => 'transfer_letter_next_no', 'default_prefix' => 'TRF-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_CANCEL')
        {
            $map = array('prefix_field' => 'cancel_letter_prefix', 'next_field' => 'cancel_letter_next_no', 'default_prefix' => 'CNL-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_FINAL_CANCEL')
        {
            $map = array('prefix_field' => 'final_cancel_letter_prefix', 'next_field' => 'final_cancel_letter_next_no', 'default_prefix' => 'FCL-' . date('Y') . '-');
        }
        elseif ($letterType === 'RLT_REFUND')
        {
            $map = array('prefix_field' => 'refund_letter_prefix', 'next_field' => 'refund_letter_next_no', 'default_prefix' => 'RFD-' . date('Y') . '-');
        }
        return $map;
    }

    protected function _lettersReferenceSettingsRow($projectId = 0)
    {
        $projectId = (int)$projectId;
        $defaults = $this->_lettersReferenceDefaults();
        if ($projectId <= 0)
        {
            return array_merge($defaults, array('project_id' => 0));
        }
        try
        {
            $this->loadModel('Admin.Ardsetting');
        }
        catch (Exception $e)
        {
            return array_merge($defaults, array('project_id' => $projectId));
        }
        try
        {
            $row = $this->Ardsetting->find('first', array(
                'conditions' => array('Ardsetting.project_id' => $projectId),
                'order' => array('Ardsetting.id' => 'desc'),
                'recursive' => -1
            ));
        }
        catch (Exception $e)
        {
            $row = array();
        }
        $merged = array_merge($defaults, array('project_id' => $projectId));
        if (!empty($row['Ardsetting']))
        {
            $merged = array_merge($merged, $row['Ardsetting']);
        }
        if (!empty($row['Ardsetting']['id']))
        {
            $merged['id'] = (int)$row['Ardsetting']['id'];
        }
        return $merged;
    }

    protected function _letterReferenceExists($referenceNo, $projectId = 0)
    {
        $referenceNo = trim((string)$referenceNo);
        if ($referenceNo === '')
        {
            return false;
        }
        $conditions = array('Realestateletter.reference_no' => $referenceNo);
        $query = array(
            'conditions' => $conditions,
            'recursive' => -1
        );
        $projectId = (int)$projectId;
        if ($projectId > 0)
        {
            $query['joins'] = array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))
            );
            $conditions[] = array('Property.project_id' => $projectId);
            $query['conditions'] = $conditions;
        }
        return ((int)$this->Realestateletter->find('count', $query) > 0);
    }

    protected function _findNextAvailableLetterReferenceNo($projectId = 0, $consume = false, $letterType = '')
    {
        $projectId = (int)$projectId;
        if ($projectId <= 0)
        {
            return $this->_referenceNo();
        }
        $settings = $this->_lettersReferenceSettingsRow($projectId);
        $defaults = $this->_lettersReferenceDefaults();
        $referenceMap = $this->_letterReferenceFieldMap($letterType);
        $prefixField = isset($referenceMap['prefix_field']) ? (string)$referenceMap['prefix_field'] : 'letters_prefix';
        $nextField = isset($referenceMap['next_field']) ? (string)$referenceMap['next_field'] : 'letters_next_no';
        $fallbackPrefix = isset($referenceMap['default_prefix']) ? (string)$referenceMap['default_prefix'] : 'REL-';
        $prefix = trim((string)(isset($settings[$prefixField]) ? $settings[$prefixField] : ''));
        if ($prefix === '' && $prefixField !== 'letters_prefix')
        {
            $prefix = trim((string)(isset($settings['letters_prefix']) ? $settings['letters_prefix'] : ''));
        }
        if ($prefix === '')
        {
            $prefix = trim((string)(isset($defaults[$prefixField]) ? $defaults[$prefixField] : ''));
        }
        if ($prefix === '')
        {
            $prefix = $fallbackPrefix;
        }
        if ($prefix === '')
        {
            $prefix = 'REL-';
        }
        if (strlen($prefix) > 20)
        {
            $prefix = substr($prefix, 0, 20);
        }
        $nextNo = max(1, (int)(isset($settings[$nextField]) ? $settings[$nextField] : 1));
        if ($nextNo <= 0 && $nextField !== 'letters_next_no')
        {
            $nextNo = max(1, (int)(isset($settings['letters_next_no']) ? $settings['letters_next_no'] : 1));
        }
        $candidate = '';
        $guard = 0;
        while (true)
        {
            $candidate = $prefix . $nextNo;
            if (!$this->_letterReferenceExists($candidate, $projectId))
            {
                break;
            }
            $nextNo++;
            $guard++;
            if ($guard > 100000)
            {
                break;
            }
        }
        if ($consume)
        {
            try
            {
                $this->loadModel('Admin.Ardsetting');
                $saveRow = array(
                    'project_id' => $projectId,
                    $prefixField => $prefix,
                    $nextField => $nextNo + 1
                );
                if (!empty($settings['id']))
                {
                    $saveRow['id'] = (int)$settings['id'];
                    $this->Ardsetting->id = (int)$settings['id'];
                }
                else
                {
                    $this->Ardsetting->create();
                }
                $this->Ardsetting->save(array('Ardsetting' => $saveRow), false);
            }
            catch (Exception $e)
            {
                // Keep letter creation working even if settings write fails.
            }
        }
        return $candidate;
    }

    protected function _peekLetterReferenceNo($projectId = 0, $letterType = '')
    {
        return $this->_findNextAvailableLetterReferenceNo((int)$projectId, false, $letterType);
    }

    protected function _nextLetterReferenceNo($projectId = 0, $letterType = '')
    {
        return $this->_findNextAvailableLetterReferenceNo((int)$projectId, true, $letterType);
    }

    protected function _ensureLetterReferenceSettingsColumns()
    {
        try
        {
            $this->loadModel('Admin.Ardsetting');
        }
        catch (Exception $e)
        {
            return;
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `letters_prefix` varchar(20) DEFAULT 'REL-' AFTER `deal_invoice_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `letters_next_no` int(11) NOT NULL DEFAULT 1 AFTER `letters_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `welcome_letter_prefix` varchar(20) DEFAULT 'WEL-' AFTER `letters_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `welcome_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `welcome_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `booking_letter_prefix` varchar(20) DEFAULT 'BKG-' AFTER `welcome_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `booking_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `booking_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_prefix` varchar(20) DEFAULT 'CHG-' AFTER `booking_letter_next_no`");
        }
        catch (Exception $e)
        {
            try
            {
                $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_prefix` varchar(20) DEFAULT 'CHG-'");
            }
            catch (Exception $ignored)
            {
                // Ignore if column already exists.
            }
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `charge_letter_prefix`");
        }
        catch (Exception $e)
        {
            try
            {
                $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `charge_letter_next_no` int(11) NOT NULL DEFAULT 1");
            }
            catch (Exception $ignored)
            {
                // Ignore if column already exists.
            }
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_agreement_letter_prefix` varchar(20) DEFAULT 'AGR-' AFTER `charge_letter_next_no`");
        }
        catch (Exception $e)
        {
            try
            {
                $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_agreement_letter_prefix` varchar(20) DEFAULT 'AGR-'");
            }
            catch (Exception $ignored)
            {
                // Ignore if column already exists.
            }
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_agreement_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `deal_agreement_letter_prefix`");
        }
        catch (Exception $e)
        {
            try
            {
                $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `deal_agreement_letter_next_no` int(11) NOT NULL DEFAULT 1");
            }
            catch (Exception $ignored)
            {
                // Ignore if column already exists.
            }
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `demarcation_letter_prefix` varchar(20) DEFAULT 'DEM-' AFTER `charge_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `demarcation_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `demarcation_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `design_map_approval_prefix` varchar(20) DEFAULT 'DMA-' AFTER `demarcation_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `design_map_approval_next_no` int(11) NOT NULL DEFAULT 1 AFTER `design_map_approval_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `noc_letter_prefix` varchar(20) DEFAULT 'NOC-' AFTER `design_map_approval_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `noc_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `noc_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `allot_letter_prefix` varchar(20) DEFAULT 'ALT-' AFTER `noc_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `allot_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `allot_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `demand_letter_prefix` varchar(20) DEFAULT 'DMN-' AFTER `allot_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `demand_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `demand_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `receipt_letter_prefix` varchar(20) DEFAULT 'RCP-' AFTER `demand_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `receipt_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `receipt_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `possession_letter_prefix` varchar(20) DEFAULT 'POS-' AFTER `receipt_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `possession_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `possession_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `transfer_letter_prefix` varchar(20) DEFAULT 'TRF-' AFTER `possession_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `transfer_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `transfer_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `cancel_letter_prefix` varchar(20) DEFAULT 'CNL-' AFTER `transfer_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `cancel_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `cancel_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `final_cancel_letter_prefix` varchar(20) DEFAULT 'FCL-' AFTER `cancel_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `final_cancel_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `final_cancel_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `refund_letter_prefix` varchar(20) DEFAULT 'RFD-' AFTER `final_cancel_letter_next_no`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
        try
        {
            $this->Ardsetting->query("ALTER TABLE `ardaccounts_settings` ADD COLUMN `refund_letter_next_no` int(11) NOT NULL DEFAULT 1 AFTER `refund_letter_prefix`");
        }
        catch (Exception $e)
        {
            // Ignore if column already exists.
        }
    }

    protected function _demarcationAllowedForDeal($dealId)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0)
        {
            return false;
        }
        $snapshot = $this->_ballotingSnapshotByDeal($dealId);
        return !empty($snapshot);
    }

    protected function _lettersCanUseAgencyFilters()
    {
        return $this->_isSuperAdminSession();
    }

    protected function _lettersNormalizeProjectId($projectId = null)
    {
        $projectId = (int)$projectId;
        if (!$this->_lettersCanUseAgencyFilters()) {
            return (int)$this->agencyId;
        }
        return max(0, $projectId);
    }

    protected function _lettersSelectedProjectId($default = 0)
    {
        $projectId = (int)$default;
        if (isset($this->request->query['project_id'])) {
            $projectId = (int)$this->request->query['project_id'];
        }
        if (isset($this->request->data['project_id'])) {
            $projectId = (int)$this->request->data['project_id'];
        }
        if (isset($this->request->data['Realestateletter']['project_id'])) {
            $projectId = (int)$this->request->data['Realestateletter']['project_id'];
        }
        return $this->_lettersNormalizeProjectId($projectId);
    }

    protected function _lettersProjectList()
    {
        if ($this->_lettersCanUseAgencyFilters()) {
            return (array)$this->CustomFunction->agencyWiseList('ADR', $this->agencyId);
        }
        return (array)$this->CustomFunction->agencyWiseList('AGY', $this->agencyId);
    }

    protected function _lettersAgencyCondition($field, $projectId = null)
    {
        $projectId = $this->_lettersNormalizeProjectId($projectId);
        if ($projectId > 0) {
            return array($field => $projectId);
        }
        if (!$this->_lettersCanUseAgencyFilters() && (int)$this->agencyId > 0) {
            return array($field => (int)$this->agencyId);
        }
        return array();
    }

    protected function _lettersCanDeleteTrashPermanently()
    {
        $userType = strtoupper(trim((string)$this->userType));
        return ($this->_isSuperAdminSession() || in_array($userType, array('AGY', 'MAR'), true));
    }

    protected function _lettersDashboardScopeConditions($projectId = null)
    {
        $conditions = array();
        $userType = strtoupper(trim((string)$this->userType));
        if ($this->_isSuperAdminSession() || $userType === 'ADR')
        {
            $normalizedProjectId = $this->_lettersNormalizeProjectId($projectId);
            if ($normalizedProjectId > 0)
            {
                $conditions[] = array('Property.project_id' => $normalizedProjectId);
            }
            return $conditions;
        }
        if ($userType === 'AGY')
        {
            $agencyCondition = $this->_lettersAgencyCondition('Property.project_id', $projectId);
            if (!empty($agencyCondition))
            {
                $conditions[] = $agencyCondition;
            }
            return $conditions;
        }
        if ((int)$this->luserId > 0)
        {
            $conditions[] = array('Property.user_id' => (int)$this->luserId);
        }
        return $conditions;
    }

    protected function _loadLettersTrashModel()
    {
        if (ClassRegistry::isKeySet('RealestateletterTrash'))
        {
            $this->RealestateletterTrash = ClassRegistry::getObject('RealestateletterTrash');
            return;
        }
        App::uses('RealestateletterTrash', 'Admin.Model');
        $this->RealestateletterTrash = ClassRegistry::init('Admin.RealestateletterTrash');
    }

    protected function _trashRowProjectId($letterRow)
    {
        if (!empty($letterRow['Property']['project_id']))
        {
            return (int)$letterRow['Property']['project_id'];
        }
        return $this->_lettersNormalizeProjectId(0);
    }

    protected function _moveLetterToTrash($letterRow)
    {
        $this->_loadLettersTrashModel();
        $letter = isset($letterRow['Realestateletter']) ? (array)$letterRow['Realestateletter'] : array();
        $letterId = isset($letter['id']) ? (int)$letter['id'] : 0;
        if ($letterId <= 0)
        {
            return false;
        }
        $projectId = $this->_trashRowProjectId($letterRow);
        $this->RealestateletterTrash->deleteAll(array('RealestateletterTrash.letter_id' => $letterId), false);
        $trashSave = array(
            'letter_id' => $letterId,
            'deal_id' => isset($letter['deal_id']) ? (int)$letter['deal_id'] : null,
            'client_id' => isset($letter['client_id']) ? (int)$letter['client_id'] : null,
            'template_id' => isset($letter['template_id']) ? (int)$letter['template_id'] : null,
            'user_id' => isset($letter['user_id']) ? (int)$letter['user_id'] : null,
            'project_id' => (int)$projectId,
            'letter_type' => isset($letter['letter_type']) ? (string)$letter['letter_type'] : '',
            'reference_no' => isset($letter['reference_no']) ? (string)$letter['reference_no'] : '',
            'title' => isset($letter['title']) ? (string)$letter['title'] : '',
            'letter_date' => isset($letter['letter_date']) ? (string)$letter['letter_date'] : null,
            'description' => isset($letter['description']) ? (string)$letter['description'] : '',
            'status' => isset($letter['status']) ? (string)$letter['status'] : 'Published',
            'deleted_by' => (int)$this->luserId,
            'deleted_at' => date('Y-m-d H:i:s')
        );
        foreach ($this->_agreementFieldNames() as $fieldName)
        {
            if (isset($letter[$fieldName]))
            {
                $trashSave[$fieldName] = $letter[$fieldName];
            }
        }
        $this->RealestateletterTrash->create();
        return (bool)$this->RealestateletterTrash->save(array('RealestateletterTrash' => $trashSave), false);
    }

    protected function _lettersTrashScopedRow($id, $projectId = null)
    {
        $id = (int)$id;
        if ($id <= 0)
        {
            return array();
        }
        $projectId = $this->_lettersNormalizeProjectId($projectId);
        $this->_loadLettersTrashModel();
        $conditions = array('RealestateletterTrash.id' => $id);
        $agencyCondition = $this->_lettersAgencyCondition('RealestateletterTrash.project_id', $projectId);
        if (!empty($agencyCondition))
        {
            $conditions[] = $agencyCondition;
        }
        return (array)$this->RealestateletterTrash->find('first', array(
            'conditions' => $conditions,
            'recursive' => -1
        ));
    }

    protected function _lettersRestoreTrashRecord($trashRow, &$error = '')
    {
        $error = '';
        $trash = isset($trashRow['RealestateletterTrash']) ? (array)$trashRow['RealestateletterTrash'] : array();
        if (empty($trash['id']))
        {
            $error = __('Invalid trash record.');
            return false;
        }
        $letterId = isset($trash['letter_id']) ? (int)$trash['letter_id'] : 0;
        $exists = 0;
        if ($letterId > 0)
        {
            $exists = (int)$this->Realestateletter->find('count', array(
                'conditions' => array('Realestateletter.id' => $letterId),
                'recursive' => -1
            ));
        }
        if ($exists > 0)
        {
            if ($this->RealestateletterTrash->delete((int)$trash['id']))
            {
                return true;
            }
            $error = __('Unable to remove duplicate trash record.');
            return false;
        }

        $restoreData = array(
            'deal_id' => isset($trash['deal_id']) ? (int)$trash['deal_id'] : null,
            'client_id' => isset($trash['client_id']) ? (int)$trash['client_id'] : null,
            'template_id' => isset($trash['template_id']) ? (int)$trash['template_id'] : null,
            'user_id' => isset($trash['user_id']) ? (int)$trash['user_id'] : null,
            'letter_type' => isset($trash['letter_type']) ? (string)$trash['letter_type'] : '',
            'reference_no' => isset($trash['reference_no']) ? (string)$trash['reference_no'] : '',
            'title' => isset($trash['title']) ? (string)$trash['title'] : '',
            'letter_date' => isset($trash['letter_date']) ? (string)$trash['letter_date'] : null,
            'description' => isset($trash['description']) ? (string)$trash['description'] : '',
            'status' => isset($trash['status']) ? (string)$trash['status'] : 'Published'
        );
        foreach ($this->_agreementFieldNames() as $fieldName)
        {
            if (isset($trash[$fieldName]))
            {
                $restoreData[$fieldName] = $trash[$fieldName];
            }
        }
        if ($letterId > 0)
        {
            $restoreData['id'] = $letterId;
        }
        $this->Realestateletter->create();
        if (!$this->Realestateletter->save(array('Realestateletter' => $restoreData), false))
        {
            $error = __('Unable to restore letter from trash.');
            return false;
        }
        if (!$this->RealestateletterTrash->delete((int)$trash['id']))
        {
            $error = __('Letter restored, but trash cleanup failed.');
        }
        return true;
    }

    protected function _lettersDeleteTrashRecord($trashRow, &$error = '')
    {
        $error = '';
        $trash = isset($trashRow['RealestateletterTrash']) ? (array)$trashRow['RealestateletterTrash'] : array();
        $id = isset($trash['id']) ? (int)$trash['id'] : 0;
        if ($id <= 0)
        {
            $error = __('Invalid trash record.');
            return false;
        }
        if (!$this->RealestateletterTrash->delete($id))
        {
            $error = __('Unable to delete trash record.');
            return false;
        }
        return true;
    }

    protected function _activeDealChargeRows($dealId)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0)
        {
            return array();
        }
        try
        {
            $rows = (array)$this->Realestateletter->query(
                "SELECT `id`,`charge_name`,`cycle_type`,`cycle_count`,`cycle_amount`,`total_amount`,`start_date`
                 FROM `deal_charges`
                 WHERE `deal_id`=".$dealId." AND `status`=1
                 ORDER BY `id` ASC"
            );
        }
        catch (Exception $e)
        {
            return array();
        }
        $charges = array();
        foreach ($rows as $row)
        {
            $charge = array();
            if (isset($row['deal_charges']) && is_array($row['deal_charges']))
            {
                $charge = $row['deal_charges'];
            }
            elseif (isset($row[0]) && is_array($row[0]))
            {
                $charge = $row[0];
            }
            else
            {
                foreach ((array)$row as $part)
                {
                    if (is_array($part) && isset($part['charge_name']))
                    {
                        $charge = $part;
                        break;
                    }
                }
            }
            if (!empty($charge))
            {
                $charges[] = $charge;
            }
        }
        return $charges;
    }

    protected function _dealHasActiveCharges($dealId)
    {
        return count($this->_activeDealChargeRows($dealId)) > 0;
    }

    protected function _dealList($selectedDealId = null, $projectId = null)
    {
        $this->loadModel('Deal');
        $conditions = array();
        $agencyCondition = $this->_lettersAgencyCondition('Property.project_id', $projectId);
        if (!empty($agencyCondition))
        {
            $conditions[] = $agencyCondition;
        }
        $rows = $this->Deal->find('all', array(
            'fields' => array('Deal.id', 'Deal.invoice_no', 'Client.name', 'Property.name'),
            'joins' => array(
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))
            ),
            'conditions' => $conditions,
            'order' => array('Client.name' => 'asc', 'Deal.id' => 'desc'),
            'recursive' => -1
        ));
        $dealOptions = array();
        foreach ((array)$rows as $row)
        {
            $dealId = isset($row['Deal']['id']) ? (int)$row['Deal']['id'] : 0;
            if ($dealId <= 0)
            {
                continue;
            }
            $invoiceNo = isset($row['Deal']['invoice_no']) ? trim((string)$row['Deal']['invoice_no']) : '';
            $clientName = isset($row['Client']['name']) ? trim((string)$row['Client']['name']) : '';
            $propertyName = isset($row['Property']['name']) ? trim((string)$row['Property']['name']) : '';
            $parts = array();
            if ($clientName !== '') $parts[] = $clientName;
            if ($invoiceNo !== '') $parts[] = $invoiceNo;
            if ($propertyName !== '') $parts[] = $propertyName;
            $dealOptions[$dealId] = implode(' | ', $parts);
            if ($dealOptions[$dealId] === '')
            {
                $dealOptions[$dealId] = __('Deal') . ' #' . $dealId;
            }
        }
        if ($selectedDealId && !isset($dealOptions[$selectedDealId]))
        {
            $selectedDeal = $this->_dealDetails($selectedDealId, false, $projectId);
            if (!empty($selectedDeal['Deal']['id']))
            {
                $selectedParts = array();
                if (!empty($selectedDeal['Client']['name'])) $selectedParts[] = $selectedDeal['Client']['name'];
                if (!empty($selectedDeal['Deal']['invoice_no'])) $selectedParts[] = $selectedDeal['Deal']['invoice_no'];
                if (!empty($selectedDeal['Property']['name'])) $selectedParts[] = $selectedDeal['Property']['name'];
                $dealOptions[(int)$selectedDeal['Deal']['id']] = implode(' | ', $selectedParts);
                if ($dealOptions[(int)$selectedDeal['Deal']['id']] === '')
                {
                    $dealOptions[(int)$selectedDeal['Deal']['id']] = __('Deal') . ' #' . (int)$selectedDeal['Deal']['id'];
                }
            }
        }
        return $dealOptions;
    }

    protected function _templateList()
    {
        $this->loadModel('Template');
        $types = array_keys($this->_letterTypeArr);
        $rows = $this->Template->find('all', array(
            'fields' => array('Template.id', 'Template.name', 'Template.type', 'Template.description', 'Template.status'),
            'conditions' => array('Template.type' => $types, 'Template.status' => 'Published'),
            'order' => array('Template.type' => 'asc', 'Template.name' => 'asc'),
            'recursive' => -1
        ));
        $templateOptions = array();
        $templateBodyMap = array();
        $templateTypeMap = array();
        foreach ((array)$rows as $row)
        {
            $id = isset($row['Template']['id']) ? (int)$row['Template']['id'] : 0;
            if ($id <= 0)
            {
                continue;
            }
            $type = isset($row['Template']['type']) ? trim((string)$row['Template']['type']) : '';
            $labelType = isset($this->_letterTypeArr[$type]) ? $this->_letterTypeArr[$type] : $type;
            $templateOptions[$id] = '[' . $labelType . '] ' . trim((string)$row['Template']['name']);
            $templateBodyMap[$id] = isset($row['Template']['description']) ? (string)$row['Template']['description'] : '';
            $templateTypeMap[$id] = $type;
        }
        return array($templateOptions, $templateBodyMap, $templateTypeMap);
    }

    protected function _defaultTemplateIdForType($typeCode)
    {
        $typeCode = trim((string)$typeCode);
        if ($typeCode === '' || !isset($this->_letterTypeArr[$typeCode]))
        {
            return 0;
        }
        $this->loadModel('Template');
        $rows = (array)$this->Template->find('all', array(
            'fields' => array('Template.id', 'Template.status', 'Template.is_new'),
            'conditions' => array('Template.type' => $typeCode, 'Template.status' => 'Published'),
            'order' => array('Template.id' => 'asc'),
            'recursive' => -1
        ));
        if (empty($rows))
        {
            return 0;
        }
        $publishedId = 0;
        foreach ($rows as $row)
        {
            $id = isset($row['Template']['id']) ? (int)$row['Template']['id'] : 0;
            if ($id <= 0)
            {
                continue;
            }
            $status = isset($row['Template']['status']) ? trim((string)$row['Template']['status']) : '';
            $isNew = isset($row['Template']['is_new']) ? strtolower(trim((string)$row['Template']['is_new'])) : '';
            if ($status === 'Published' && $publishedId <= 0)
            {
                $publishedId = $id;
            }
            if ($status === 'Published' && $isNew !== 'yes')
            {
                return $id;
            }
        }
        if ($publishedId > 0)
        {
            return $publishedId;
        }
        return 0;
    }

    protected function _dealDetails($dealId, $includeHidden = false, $projectId = null)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0)
        {
            return array();
        }
        $this->loadModel('Deal');
        $conditions = array('Deal.id' => $dealId);
        if (!$includeHidden)
        {
            $agencyCondition = $this->_lettersAgencyCondition('Property.project_id', $projectId);
            if (!empty($agencyCondition))
            {
                $conditions[] = $agencyCondition;
            }
        }
        return (array)$this->Deal->find('first', array(
            'conditions' => $conditions,
            'recursive' => 0
        ));
    }

    protected function _availableClientColumns($wantedColumns = array())
    {
        $wantedMap = array();
        foreach ((array)$wantedColumns as $columnName)
        {
            $columnName = strtolower(trim((string)$columnName));
            if ($columnName !== '')
            {
                $wantedMap[$columnName] = true;
            }
        }
        if (empty($wantedMap))
        {
            return array();
        }
        $available = array();
        try
        {
            $rows = (array)$this->Realestateletter->query("SHOW COLUMNS FROM `clients`");
            foreach ($rows as $row)
            {
                $field = '';
                if (isset($row['Field']))
                {
                    $field = (string)$row['Field'];
                }
                else
                {
                    foreach ((array)$row as $part)
                    {
                        if (is_array($part) && isset($part['Field']))
                        {
                            $field = (string)$part['Field'];
                            break;
                        }
                    }
                }
                $fieldKey = strtolower(trim($field));
                if ($fieldKey !== '' && isset($wantedMap[$fieldKey]))
                {
                    $available[$fieldKey] = true;
                }
            }
        }
        catch (Exception $e)
        {
            return array();
        }
        return $available;
    }

    protected function _availableProjectColumns($wantedColumns = array())
    {
        $wantedMap = array();
        foreach ((array)$wantedColumns as $columnName)
        {
            $columnName = strtolower(trim((string)$columnName));
            if ($columnName !== '')
            {
                $wantedMap[$columnName] = true;
            }
        }
        if (empty($wantedMap))
        {
            return array();
        }
        $available = array();
        try
        {
            $rows = (array)$this->Realestateletter->query("SHOW COLUMNS FROM `projects`");
            foreach ($rows as $row)
            {
                $field = '';
                if (isset($row['Field']))
                {
                    $field = (string)$row['Field'];
                }
                else
                {
                    foreach ((array)$row as $part)
                    {
                        if (is_array($part) && isset($part['Field']))
                        {
                            $field = (string)$part['Field'];
                            break;
                        }
                    }
                }
                $fieldKey = strtolower(trim($field));
                if ($fieldKey !== '' && isset($wantedMap[$fieldKey]))
                {
                    $available[$fieldKey] = true;
                }
            }
        }
        catch (Exception $e)
        {
            return array();
        }
        return $available;
    }

    protected function _availableDealColumns($wantedColumns = array())
    {
        $wantedMap = array();
        foreach ((array)$wantedColumns as $columnName)
        {
            $columnName = trim((string)$columnName);
            if ($columnName !== '')
            {
                $wantedMap[$columnName] = true;
            }
        }
        if (empty($wantedMap))
        {
            return array();
        }
        $available = array();
        try
        {
            $rows = (array)$this->Realestateletter->query("SHOW COLUMNS FROM `deals`");
            foreach ($rows as $row)
            {
                $fieldName = '';
                if (isset($row['COLUMNS']['Field']))
                {
                    $fieldName = (string)$row['COLUMNS']['Field'];
                }
                elseif (isset($row[0]['Field']))
                {
                    $fieldName = (string)$row[0]['Field'];
                }
                $fieldKey = trim($fieldName);
                if ($fieldKey !== '' && isset($wantedMap[$fieldKey]))
                {
                    $available[$fieldKey] = true;
                }
            }
        }
        catch (Exception $e)
        {
            return array();
        }
        return $available;
    }

    protected function _letterDetails($id, $projectId = null)
    {
        $id = (int)$id;
        if ($id <= 0)
        {
            return array();
        }
        $conditions = array('Realestateletter.id' => $id);
        $agencyCondition = $this->_lettersAgencyCondition('Property.project_id', $projectId);
        if (!empty($agencyCondition))
        {
            $conditions[] = $agencyCondition;
        }
        $dealFields = array(
            'Deal.id', 'Deal.invoice_no', 'Deal.date', 'Deal.total_amount', 'Deal.discount', 'Deal.booking_amount'
        );
        $dealNomineeFieldMap = array(
            'nominee_name' => 'Deal.nominee_name',
            'nominee_cnic' => 'Deal.nominee_cnic',
            'nominee_mobile' => 'Deal.nominee_mobile',
            'nominee_relation' => 'Deal.nominee_relation',
            'occupation' => 'Deal.occupation',
            'deal_status' => 'Deal.deal_status',
            'status' => 'Deal.status'
        );
        $availableDealNomineeFields = $this->_availableDealColumns(array_keys($dealNomineeFieldMap));
        foreach ($dealNomineeFieldMap as $columnName => $fieldName)
        {
            if (isset($availableDealNomineeFields[$columnName]))
            {
                $dealFields[] = $fieldName;
            }
        }
        $clientFields = array(
            'Client.id', 'Client.name', 'Client.fathername', 'Client.pincode', 'Client.phone', 'Client.mobile', 'Client.email', 'Client.address'
        );
        $clientMediaFieldMap = array(
            'relation_type' => 'Client.relation_type',
            'gender' => 'Client.gender',
            'photo' => 'Client.photo',
            'image' => 'Client.image',
            'dir' => 'Client.dir',
            'dirt' => 'Client.dirt',
            'id_front' => 'Client.id_front',
            'id_front_dir' => 'Client.id_front_dir',
            'id_back' => 'Client.id_back',
            'id_back_dir' => 'Client.id_back_dir'
        );
        $availableClientMedia = $this->_availableClientColumns(array_keys($clientMediaFieldMap));
        foreach ($clientMediaFieldMap as $columnName => $fieldName)
        {
            if (isset($availableClientMedia[$columnName]))
            {
                $clientFields[] = $fieldName;
            }
        }
        $projectFields = array('Project.id', 'Project.name');
        $projectFieldMap = array(
            'phone' => 'Project.phone',
            'mobile' => 'Project.mobile',
            'email' => 'Project.email',
            'address' => 'Project.address',
            'photo' => 'Project.photo',
            'logo' => 'Project.logo',
            'image' => 'Project.image'
        );
        $availableProjectFields = $this->_availableProjectColumns(array_keys($projectFieldMap));
        foreach ($projectFieldMap as $columnName => $fieldName)
        {
            if (isset($availableProjectFields[$columnName]))
            {
                $projectFields[] = $fieldName;
            }
        }
        $fields = array_merge(
            array(
                'Realestateletter.*'
            ),
            $dealFields,
            $clientFields,
            array(
                'Property.id', 'Property.name', 'Property.area', 'Property.price', 'Property.project_id',
                'Type.name', 'Contract.name', 'Unit.name',
            ),
            $projectFields,
            array(
                'Template.id', 'Template.name', 'Template.description',
                'Template.ceo_signature', 'Template.ceo_signature_dir',
                'Template.chairman_signature', 'Template.chairman_signature_dir'
            )
        );
        return (array)$this->Realestateletter->find('first', array(
            'fields' => $fields,
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
                array('table' => 'types', 'alias' => 'Type', 'type' => 'LEFT', 'conditions' => array('Type.id=Property.type_id')),
                array('table' => 'contracts', 'alias' => 'Contract', 'type' => 'LEFT', 'conditions' => array('Contract.id=Property.contract_id')),
                array('table' => 'units', 'alias' => 'Unit', 'type' => 'LEFT', 'conditions' => array('Unit.id=Property.unit_id')),
                array('table' => 'projects', 'alias' => 'Project', 'type' => 'LEFT', 'conditions' => array('Project.id=Property.project_id')),
                array('table' => 'templates', 'alias' => 'Template', 'type' => 'LEFT', 'conditions' => array('Template.id=Realestateletter.template_id'))
            ),
            'conditions' => $conditions,
            'recursive' => -1
        ));
    }

    protected function _ballotingSnapshotByDeal($dealId)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0)
        {
            return array();
        }
        try
        {
            $rows = (array)$this->Realestateletter->query("SHOW TABLES LIKE 'balloting_allotments'");
            if (empty($rows))
            {
                return array();
            }
        }
        catch (Exception $e)
        {
            return array();
        }
        $row = array();
        try
        {
            $row = (array)$this->Realestateletter->query(
                "SELECT BA.*, U.permanent_type as unit_permanent_type, U.unit_area as unit_unit_area, U.area_unit as unit_area_unit,
                        T.name as type_name, UN.name as unit_name, C.name as category_name
                 FROM balloting_allotments BA
                 LEFT JOIN balloting_units U ON U.id=BA.balloting_unit_id
                 LEFT JOIN types T ON T.id=BA.property_type_id
                 LEFT JOIN units UN ON UN.id=BA.unit_id
                 LEFT JOIN categories C ON C.id=BA.category_id
                 WHERE BA.deal_id=".$dealId." AND BA.status='Allotted'
                 ORDER BY BA.id DESC
                 LIMIT 1"
            );
        }
        catch (Exception $e)
        {
            return array();
        }
        if (empty($row[0]))
        {
            return array();
        }
        $r = $row[0];
        $ba = isset($r['BA']) ? (array)$r['BA'] : $r;
        $blockName = isset($ba['block_name']) ? trim((string)$ba['block_name']) : '';
        if ($blockName === '')
        {
            $blockName = 'N/A';
        }
        $result = array(
            'allotment_no' => isset($ba['allotment_no']) ? (string)$ba['allotment_no'] : '',
            'permanent_type' => isset($ba['permanent_type']) && trim((string)$ba['permanent_type']) !== '' ? (string)$ba['permanent_type'] : (isset($r['unit_permanent_type']) ? (string)$r['unit_permanent_type'] : ''),
            'property_no' => isset($ba['property_no']) ? (string)$ba['property_no'] : '',
            'street_no' => isset($ba['street_no']) ? (string)$ba['street_no'] : '',
            'block_name' => $blockName,
            'street_address' => isset($ba['street_address']) ? (string)$ba['street_address'] : '',
            'unit_area' => isset($ba['unit_area']) ? (string)$ba['unit_area'] : (isset($r['unit_unit_area']) ? (string)$r['unit_unit_area'] : ''),
            'area_unit' => isset($ba['area_unit']) ? (string)$ba['area_unit'] : (isset($r['unit_area_unit']) ? (string)$r['unit_area_unit'] : ''),
            'property_type' => isset($r['type_name']) ? (string)$r['type_name'] : '',
            'unit_name' => isset($r['unit_name']) ? (string)$r['unit_name'] : '',
            'category_name' => isset($r['category_name']) ? (string)$r['category_name'] : '',
            'allotment_date' => isset($ba['allotment_date']) ? (string)$ba['allotment_date'] : ''
        );
        return $result;
    }

    protected function _contactImageUrl($dir, $file)
    {
        $file = trim((string)$file);
        if ($file === '')
        {
            return '';
        }
        $dir = trim((string)$dir);
        if ($dir === '')
        {
            $dir = 'contact';
        }
        $asset = trim($dir, '/\\') . '/' . ltrim($file, '/\\');
        return $this->_imageAssetUrl($asset);
    }

    protected function _imageAssetUrl($asset)
    {
        $asset = trim(str_replace('\\', '/', (string)$asset), '/');
        if ($asset === '')
        {
            return '';
        }
        $base = rtrim((string)Router::url('/', false), '/');
        return $base . '/img/' . $asset;
    }

    protected function _resolveImageFromCandidates($candidates)
    {
        foreach ((array)$candidates as $candidate)
        {
            if (!is_string($candidate))
            {
                continue;
            }
            $candidate = trim(str_replace('\\', '/', $candidate), '/');
            if ($candidate === '')
            {
                continue;
            }
            $filePath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $candidate);
            if (@file_exists($filePath))
            {
                return $this->_imageAssetUrl($candidate);
            }
        }
        return '';
    }

    protected function _resolveClientPhotoUrl($client)
    {
        $photo = isset($client['photo']) ? trim((string)$client['photo']) : '';
        if ($photo === '' && !empty($client['image']))
        {
            $photo = trim((string)$client['image']);
        }
        if ($photo === '')
        {
            return $this->_resolveImageFromCandidates(array('User.png'));
        }
        $dir = isset($client['dir']) ? trim((string)$client['dir']) : '';
        $dirt = isset($client['dirt']) ? trim((string)$client['dirt']) : '';
        $candidates = array(
            ($dir !== '' ? trim($dir, '/\\') . '/' . $photo : ''),
            ($dirt !== '' ? trim($dirt, '/\\') . '/' . $photo : ''),
            'client/' . $photo,
            'contact/' . $photo,
            'user/' . $photo,
            ($dir !== '' ? trim($dir, '/\\') . '_thumb/' . $photo : ''),
            ($dirt !== '' ? trim($dirt, '/\\') . '_thumb/' . $photo : ''),
            'client_thumb/' . $photo,
            'contact_thumb/' . $photo,
            'user_thumb/' . $photo,
            $photo
        );
        $resolved = $this->_resolveImageFromCandidates($candidates);
        if ($resolved !== '')
        {
            return $resolved;
        }
        return $this->_resolveImageFromCandidates(array('User.png'));
    }

    protected function _resolveAgencyLogoUrl($project)
    {
        $project = (array)$project;
        $projectId = isset($project['id']) ? (int)$project['id'] : 0;
        $cacheKey = ($projectId > 0) ? (string)$projectId : md5(serialize($project));
        if(isset($this->_agencyLogoUrlCache[$cacheKey]))
        {
            return (string)$this->_agencyLogoUrlCache[$cacheKey];
        }

        $agencyLogoFile = '';
        if($projectId > 0)
        {
            try
            {
                $User = ClassRegistry::init('User');
                $userFields = array('User.id');
                if($User->hasField('photo'))
                {
                    $userFields[] = 'User.photo';
                }
                $baseConditions = array(
                    'User.project_id' => $projectId,
                    'User.photo <>' => ''
                );
                $agencyUserRow = array();
                if($User->hasField('ugroup_id'))
                {
                    $agencyUserRow = $User->find('first', array(
                        'fields' => $userFields,
                        'conditions' => array_merge($baseConditions, array('User.ugroup_id' => 4)),
                        'order' => array('User.id' => 'asc'),
                        'recursive' => -1
                    ));
                }
                if(empty($agencyUserRow))
                {
                    $agencyUserRow = $User->find('first', array(
                        'fields' => $userFields,
                        'conditions' => $baseConditions,
                        'order' => array('User.id' => 'asc'),
                        'recursive' => -1
                    ));
                }
                if(!empty($agencyUserRow['User']['photo']))
                {
                    $agencyLogoFile = trim((string)$agencyUserRow['User']['photo']);
                }
            }
            catch(Exception $e)
            {
                $agencyLogoFile = '';
            }
        }

        $logoUrl = $this->_resolveImageFromCandidates(array(
            ($agencyLogoFile !== '' ? 'user_thumb/' . $agencyLogoFile : ''),
            ($agencyLogoFile !== '' ? 'user/' . $agencyLogoFile : ''),
            ($agencyLogoFile !== '' ? $agencyLogoFile : ''),
            (!empty($project['logo']) ? 'agency_thumb/' . $project['logo'] : ''),
            (!empty($project['logo']) ? 'agency/' . $project['logo'] : ''),
            (!empty($project['logo']) ? $project['logo'] : ''),
            (!empty($project['image']) ? 'agency_thumb/' . $project['image'] : ''),
            (!empty($project['image']) ? 'agency/' . $project['image'] : ''),
            (!empty($project['image']) ? $project['image'] : ''),
            (!empty($project['photo']) ? 'agency_thumb/' . $project['photo'] : ''),
            (!empty($project['photo']) ? 'agency/' . $project['photo'] : ''),
            (!empty($project['photo']) ? $project['photo'] : '')
        ));
        $this->_agencyLogoUrlCache[$cacheKey] = (string)$logoUrl;
        return (string)$logoUrl;
    }

    protected function _agencyUserInfo($projectId)
    {
        $projectId = (int)$projectId;
        if ($projectId <= 0)
        {
            return array();
        }
        if (isset($this->_agencyUserInfoCache[$projectId]))
        {
            return (array)$this->_agencyUserInfoCache[$projectId];
        }
        $info = array();
        try
        {
            $User = ClassRegistry::init('User');
            $fields = array('User.id', 'User.name');
            foreach (array('address', 'phone', 'mobile', 'email', 'photo') as $field)
            {
                if ($User->hasField($field))
                {
                    $fields[] = 'User.' . $field;
                }
            }
            $conditions = array('User.project_id' => $projectId);
            $row = array();
            if ($User->hasField('ugroup_id'))
            {
                $row = (array)$User->find('first', array(
                    'fields' => $fields,
                    'conditions' => array_merge($conditions, array('User.ugroup_id' => 4)),
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if (empty($row['User']))
            {
                $row = (array)$User->find('first', array(
                    'fields' => $fields,
                    'conditions' => $conditions,
                    'order' => array('User.id' => 'asc'),
                    'recursive' => -1
                ));
            }
            if (!empty($row['User']))
            {
                $info = (array)$row['User'];
            }
        }
        catch (Exception $e)
        {
            $info = array();
        }
        $this->_agencyUserInfoCache[$projectId] = $info;
        return $info;
    }

    protected function _agencyLogoImageTag($url, $altText)
    {
        $url = trim((string)$url);
        if ($url === '')
        {
            return '';
        }
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $safeAlt = htmlspecialchars((string)$altText, ENT_QUOTES, 'UTF-8');
        return '<img src="' . $safeUrl . '" alt="' . $safeAlt . '" style="display:block;max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;margin:auto;" />';
    }

    protected function _resolveClientDocUrl($fileName, $dirName, $fallbackDir)
    {
        $fileName = trim((string)$fileName);
        if ($fileName === '')
        {
            return '';
        }
        $dirName = trim((string)$dirName);
        $fallbackDir = trim((string)$fallbackDir);
        $candidates = array(
            ($dirName !== '' ? trim($dirName, '/\\') . '_thumb/' . $fileName : ''),
            ($dirName !== '' ? trim($dirName, '/\\') . '/' . $fileName : ''),
            ($fallbackDir !== '' ? trim($fallbackDir, '/\\') . '_thumb/' . $fileName : ''),
            ($fallbackDir !== '' ? trim($fallbackDir, '/\\') . '/' . $fileName : ''),
            'contact_thumb/' . $fileName,
            'contact/' . $fileName,
            $fileName
        );
        return $this->_resolveImageFromCandidates($candidates);
    }

    protected function _contactImageTag($url, $altText)
    {
        $url = trim((string)$url);
        if ($url === '')
        {
            return '';
        }
        $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $safeAlt = htmlspecialchars((string)$altText, ENT_QUOTES, 'UTF-8');
        return '<img src="' . $safeUrl . '" alt="' . $safeAlt . '" style="display:block;width:100%;height:100%;max-width:100%;max-height:100%;object-fit:contain;object-position:center;" />';
    }

    protected function _formatDateValue($dateValue, $fallback = '')
    {
        $value = trim((string)$dateValue);
        if ($value === '')
        {
            return $fallback;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false)
        {
            return $fallback;
        }
        return date($this->dtFormat, $timestamp);
    }

    protected function _letterAmountInWords($amount, $currencyWord = 'Rupees')
    {
        $amount = trim(str_replace(',', '', (string)$amount));
        if ($amount === '' || !is_numeric($amount))
        {
            return '';
        }
        $negative = ((float)$amount < 0);
        $amount = round(abs((float)$amount), 2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount - $whole) * 100);
        if ($fraction === 100)
        {
            $whole++;
            $fraction = 0;
        }
        $words = $this->_letterIntegerToWords($whole);
        if (trim((string)$currencyWord) !== '')
        {
            $words .= ' '.trim((string)$currencyWord);
        }
        if ($fraction > 0)
        {
            $words .= ' And '.$this->_letterIntegerToWords($fraction).' Paisa';
        }
        if ($negative)
        {
            $words = 'Minus '.$words;
        }
        return trim($words).' Only';
    }

    protected function _letterIntegerToWords($number)
    {
        $number = (int)$number;
        if ($number === 0)
        {
            return 'Zero';
        }
        $scaleNames = array('', 'Thousand', 'Lakh', 'Crore', 'Arab', 'Kharab');
        $groups = array($number % 1000);
        $number = (int)floor($number / 1000);
        while ($number > 0)
        {
            $groups[] = $number % 100;
            $number = (int)floor($number / 100);
        }
        $parts = array();
        for ($i = count($groups) - 1; $i >= 0; $i--)
        {
            $groupValue = (int)$groups[$i];
            if ($groupValue === 0)
            {
                continue;
            }
            $groupWords = ($i === 0) ? $this->_letterUnderThousandToWords($groupValue) : $this->_letterUnderHundredToWords($groupValue);
            if (!empty($scaleNames[$i]))
            {
                $groupWords .= ' '.$scaleNames[$i];
            }
            $parts[] = $groupWords;
        }
        return implode(' ', $parts);
    }

    protected function _letterUnderThousandToWords($number)
    {
        $number = (int)$number;
        $parts = array();
        if ($number >= 100)
        {
            $parts[] = $this->_letterUnderTwentyWords((int)floor($number / 100)).' Hundred';
            $number = $number % 100;
        }
        if ($number > 0)
        {
            $parts[] = $this->_letterUnderHundredToWords($number);
        }
        return implode(' ', $parts);
    }

    protected function _letterUnderHundredToWords($number)
    {
        $number = (int)$number;
        if ($number < 20)
        {
            return $this->_letterUnderTwentyWords($number);
        }
        $tens = array(2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty', 6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety');
        $ten = (int)floor($number / 10);
        $unit = $number % 10;
        $words = isset($tens[$ten]) ? $tens[$ten] : '';
        if ($unit > 0)
        {
            $words .= ' '.$this->_letterUnderTwentyWords($unit);
        }
        return trim($words);
    }

    protected function _letterUnderTwentyWords($number)
    {
        $ones = array(0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen');
        $number = (int)$number;
        return isset($ones[$number]) ? $ones[$number] : '';
    }

    protected function _letterAmountInUrduWords($amount, $currencyWord = 'روپے')
    {
        $amount = trim(str_replace(',', '', (string)$amount));
        if ($amount === '' || !is_numeric($amount))
        {
            return '';
        }
        $negative = ((float)$amount < 0);
        $amount = round(abs((float)$amount), 2);
        $whole = (int)floor($amount);
        $fraction = (int)round(($amount - $whole) * 100);
        if ($fraction === 100)
        {
            $whole++;
            $fraction = 0;
        }
        $words = $this->_letterIntegerToUrduWords($whole);
        if (trim((string)$currencyWord) !== '')
        {
            $words .= ' ' . trim((string)$currencyWord);
        }
        if ($fraction > 0)
        {
            $words .= ' اور ' . $this->_letterIntegerToUrduWords($fraction) . ' پیسے';
        }
        if ($negative)
        {
            $words = 'منفی ' . $words;
        }
        return trim($words) . ' صرف';
    }

    protected function _letterIntegerToUrduWords($number)
    {
        $number = (int)$number;
        if ($number === 0)
        {
            return 'صفر';
        }
        $scaleNames = array('', 'ہزار', 'لاکھ', 'کروڑ', 'ارب', 'کھرب');
        $groups = array($number % 1000);
        $number = (int)floor($number / 1000);
        while ($number > 0)
        {
            $groups[] = $number % 100;
            $number = (int)floor($number / 100);
        }
        $parts = array();
        for ($i = count($groups) - 1; $i >= 0; $i--)
        {
            $groupValue = (int)$groups[$i];
            if ($groupValue === 0)
            {
                continue;
            }
            $groupWords = ($i === 0) ? $this->_letterUnderThousandToUrduWords($groupValue) : $this->_letterUnderHundredToUrduWords($groupValue);
            if (!empty($scaleNames[$i]))
            {
                $groupWords .= ' ' . $scaleNames[$i];
            }
            $parts[] = $groupWords;
        }
        return implode(' ', $parts);
    }

    protected function _letterUnderThousandToUrduWords($number)
    {
        $number = (int)$number;
        $parts = array();
        if ($number >= 100)
        {
            $parts[] = $this->_letterUnderHundredToUrduWords((int)floor($number / 100)) . ' سو';
            $number = $number % 100;
        }
        if ($number > 0)
        {
            $parts[] = $this->_letterUnderHundredToUrduWords($number);
        }
        return implode(' ', $parts);
    }

    protected function _letterUnderHundredToUrduWords($number)
    {
        $words = array(
            0 => '', 1 => 'ایک', 2 => 'دو', 3 => 'تین', 4 => 'چار', 5 => 'پانچ', 6 => 'چھ', 7 => 'سات', 8 => 'آٹھ', 9 => 'نو',
            10 => 'دس', 11 => 'گیارہ', 12 => 'بارہ', 13 => 'تیرہ', 14 => 'چودہ', 15 => 'پندرہ', 16 => 'سولہ', 17 => 'سترہ', 18 => 'اٹھارہ', 19 => 'انیس',
            20 => 'بیس', 21 => 'اکیس', 22 => 'بائیس', 23 => 'تئیس', 24 => 'چوبیس', 25 => 'پچیس', 26 => 'چھبیس', 27 => 'ستائیس', 28 => 'اٹھائیس', 29 => 'انتالیس',
            30 => 'تیس', 31 => 'اکتیس', 32 => 'بتیس', 33 => 'تینتیس', 34 => 'چونتیس', 35 => 'پینتیس', 36 => 'چھتیس', 37 => 'سینتیس', 38 => 'اڑتیس', 39 => 'انتالیس',
            40 => 'چالیس', 41 => 'اکتالیس', 42 => 'بیالیس', 43 => 'تینتالیس', 44 => 'چوالیس', 45 => 'پینتالیس', 46 => 'چھیاستالیس', 47 => 'سینتالیس', 48 => 'اڑتالیس', 49 => 'انچاس',
            50 => 'پچاس', 51 => 'اکیاون', 52 => 'باون', 53 => 'ترپن', 54 => 'چون', 55 => 'پچپن', 56 => 'چھپن', 57 => 'ستاون', 58 => 'اٹھاون', 59 => 'انسٹھ',
            60 => 'ساٹھ', 61 => 'اکسٹھ', 62 => 'باسٹھ', 63 => 'تریسٹھ', 64 => 'چونسٹھ', 65 => 'پینسٹھ', 66 => 'چھیاسٹھ', 67 => 'سڑسٹھ', 68 => 'اڑسٹھ', 69 => 'انہتر',
            70 => 'ستر', 71 => 'اکہتر', 72 => 'بہتر', 73 => 'تہتر', 74 => 'چوہتر', 75 => 'پچہتر', 76 => 'چھہتر', 77 => 'ستتر', 78 => 'اٹھہتر', 79 => 'اناسی',
            80 => 'اسی', 81 => 'اکیاسی', 82 => 'بیاسی', 83 => 'تراسی', 84 => 'چوراسی', 85 => 'پچاسی', 86 => 'چھیاسی', 87 => 'ستاسی', 88 => 'اٹھاسی', 89 => 'نواسی',
            90 => 'نوے', 91 => 'اکانوے', 92 => 'بانوے', 93 => 'ترانوے', 94 => 'چورانوے', 95 => 'پچانوے', 96 => 'چھیانوے', 97 => 'ستانوے', 98 => 'اٹھانوے', 99 => 'ننانوے'
        );
        $number = (int)$number;
        return isset($words[$number]) ? $words[$number] : '';
    }

    protected function _translateDealChargeUrdu($chargeName)
    {
        $chargeName = trim((string)$chargeName);
        if ($chargeName === '')
        {
            return '';
        }
        $key = strtolower(trim(preg_replace('/\s+/', ' ', str_replace(array('-', '_'), ' ', $chargeName))));
        $map = array(
            'extra work' => 'اضافی کام',
            'additional work' => 'اضافی کام',
            'development charges' => 'ترقیاتی چارجز',
            'development charge' => 'ترقیاتی چارجز',
            'development' => 'ترقیاتی چارجز',
            'processing fee' => 'پروسیسنگ فیس',
            'deal transfer processing fee' => 'ڈیل ٹرانسفر پروسیسنگ فیس',
            'transfer fee' => 'ٹرانسفر فیس',
            'maintenance charges' => 'مینٹیننس چارجز',
            'maintenance charge' => 'مینٹیننس چارجز',
            'late fee' => 'لیٹ فیس',
            'corner charges' => 'کارنر چارجز',
            'corner charge' => 'کارنر چارجز',
            'floor charges' => 'فلور چارجز',
            'floor charge' => 'فلور چارجز',
            'possession charges' => 'قبضہ چارجز',
            'possession charge' => 'قبضہ چارجز'
        );
        return isset($map[$key]) ? $map[$key] : $chargeName;
    }

    protected function _chargeWorkStatusUrdu($status)
    {
        $status = trim((string)$status);
        $map = array(
            'In Progress' => 'کام جاری ہے',
            'Handover To Client' => 'کلائنٹ کے حوالے کر دیا گیا',
            'Completed' => 'کام مکمل ہو چکا ہے',
            'Pending' => 'کام زیر التواء ہے'
        );
        return isset($map[$status]) ? $map[$status] : ($status !== '' ? $status : 'کام جاری ہے');
    }

    protected function _dealAgreementPropertyCategory($typeText = '', $propertyText = '', $contractText = '')
    {
        $haystack = strtolower(trim((string)$typeText . ' ' . (string)$propertyText . ' ' . (string)$contractText));
        if (preg_match('/(apartment|flat|اپارٹ|فلیٹ)/u', $haystack))
        {
            return 'apartment';
        }
        if (preg_match('/(plot|file|پلاٹ|فائل)/u', $haystack))
        {
            return 'plot';
        }
        if (preg_match('/(town\s*house|townhouse|town|ٹاؤن|ٹاون)/u', $haystack))
        {
            return 'townhouse';
        }
        if (preg_match('/(villa|vila|ولا)/u', $haystack))
        {
            return 'villa';
        }
        if (preg_match('/(house|home|گھر|مکان)/u', $haystack))
        {
            return 'townhouse';
        }
        return 'plot';
    }

    protected function _dealAgreementPropertyTermsHtml($category)
    {
        $category = strtolower(trim((string)$category));
        if ($category === 'villa')
        {
            return '<div class="note-box"><strong>ولا (Independent Villa) کی شرائط:</strong><br>'
                . 'ولا کی مندرجہ بالا قیمت میں آزاد سٹرکچر، فرنٹ کار پورچ، پچھلا ہوا دار واشنگ ایریا، اور سوسائٹی رولز کے مطابق روف رائٹس شامل ہوں گے۔ خریدار اپنی حدود کے اندر سوسائٹی قوانین کے مطابق سہولیات استعمال کرنے کا حق رکھتا ہے، تاہم بیرونی ڈیزائن، ایلیویشن، رنگ یا ساخت میں تبدیلی تحریری اجازت کے بغیر نہیں کی جائے گی۔'
                . '</div>';
        }
        if ($category === 'townhouse')
        {
            return '<div class="note-box"><strong>ٹاؤن ہاؤس (Townhouse) کی شرائط:</strong><br>'
                . 'ٹاؤن ہاؤس کی قیمت کلسٹر ماڈل پر مبنی ہے۔ دائیں یا بائیں جانب مشترکہ سٹرکچرل دیواریں، کلسٹر فرنٹ/لان، یوٹیلیٹی لائنز، سیوریج، گیس اور واٹر سپلائی سوسائٹی رولز کے تحت منظم ہوں گے۔ کسی بھی مشترکہ حصے میں انفرادی ردوبدل تحریری اجازت کے بغیر ممنوع ہوگا۔'
                . '</div>';
        }
        if ($category === 'apartment')
        {
            return '<div class="note-box"><strong>اپارٹمنٹ (Apartment) کی شرائط:</strong><br>'
                . 'اپارٹمنٹ کی صورت میں مالکانہ/الاٹمنٹ حقوق صرف متعلقہ یونٹ کے اندرونی رقبے تک محدود ہوں گے۔ سیڑھیاں، لفٹ، لابی، راہداری، چھت، پارکنگ اور دیگر مشترکہ حصے کمیونٹی اثاثہ تصور ہوں گے، جنہیں کوئی فرد انفرادی طور پر مقفل، تبدیل یا مستقل طور پر اپنے نام استعمال نہیں کر سکے گا۔'
                . '</div>';
        }
        return '<div class="note-box"><strong>پلاٹ / فائل (Plot/File) کی شرائط:</strong><br>'
            . 'اس بکنگ میں طے شدہ رقبہ/فائل/یونٹ کے الاٹمنٹ حقوق شامل ہیں۔ تعمیراتی مٹیریل، عمارت، فٹنگ یا اضافی ڈیویلپمنٹ اس وقت تک شامل نہیں ہوگی جب تک فریقین تحریری طور پر اس کے لیے الگ معاہدہ نہ کریں۔ ترقیاتی چارجز، سرکاری واجبات اور سوسائٹی قوانین کے تحت لاگو فیسیں علیحدہ قابلِ ادائیگی ہوں گی۔'
            . '</div>';
    }

    protected function _letterUrduDayName($dateValue)
    {
        $timestamp = strtotime((string)$dateValue);
        if (!$timestamp)
        {
            $timestamp = time();
        }
        $map = array(
            'Sunday' => 'اتوار',
            'Monday' => 'پیر',
            'Tuesday' => 'منگل',
            'Wednesday' => 'بدھ',
            'Thursday' => 'جمعرات',
            'Friday' => 'جمعہ',
            'Saturday' => 'ہفتہ'
        );
        $english = date('l', $timestamp);
        return isset($map[$english]) ? $map[$english] : $english;
    }

    protected function _letterDateUrduText($dateValue)
    {
        $timestamp = strtotime((string)$dateValue);
        if (!$timestamp)
        {
            $timestamp = time();
        }
        $months = array(
            1 => 'جنوری',
            2 => 'فروری',
            3 => 'مارچ',
            4 => 'اپریل',
            5 => 'مئی',
            6 => 'جون',
            7 => 'جولائی',
            8 => 'اگست',
            9 => 'ستمبر',
            10 => 'اکتوبر',
            11 => 'نومبر',
            12 => 'دسمبر'
        );
        $month = (int)date('n', $timestamp);
        $monthName = isset($months[$month]) ? $months[$month] : date('F', $timestamp);
        return date('d', $timestamp) . ' ' . $monthName . '، ' . date('Y', $timestamp);
    }

    protected function _dealChargesTableHtml($chargeRows)
    {
        if (empty($chargeRows))
        {
            return '';
        }
        $html = '<table class="charge-table"><thead><tr>'
            . '<th>چارج / کام</th><th>دورانیہ</th><th>فی سائیکل رقم</th><th>کل رقم</th><th>شروع تاریخ</th>'
            . '</tr></thead><tbody>';
        foreach ((array)$chargeRows as $charge)
        {
            $chargeName = isset($charge['charge_name']) ? trim((string)$charge['charge_name']) : '';
            $cycleType = isset($charge['cycle_type']) ? strtoupper(trim((string)$charge['cycle_type'])) : '';
            $cycleLabels = array('M' => 'ماہانہ', 'Q' => 'سہ ماہی', 'H' => 'ششماہی', 'Y' => 'سالانہ', 'O' => 'ایک بار');
            $cycleLabel = isset($cycleLabels[$cycleType]) ? $cycleLabels[$cycleType] : $cycleType;
            if ($cycleLabel === '')
            {
                $cycleLabel = '-';
            }
            $cycleCount = isset($charge['cycle_count']) ? (int)$charge['cycle_count'] : 0;
            $cycleAmount = isset($charge['cycle_amount']) ? (float)$charge['cycle_amount'] : 0;
            $totalAmount = isset($charge['total_amount']) ? (float)$charge['total_amount'] : 0;
            $startDate = isset($charge['start_date']) ? trim((string)$charge['start_date']) : '';
            $startDate = ($startDate !== '' && $startDate !== '0000-00-00') ? $this->_formatDateValue($startDate, $startDate) : '-';
            $html .= '<tr>'
                . '<td>' . h($this->_translateDealChargeUrdu($chargeName)) . '</td>'
                . '<td>' . h($cycleLabel . ' / ' . $cycleCount) . '</td>'
                . '<td>' . h($this->currency . CakeNumber::format($cycleAmount)) . '</td>'
                . '<td><strong>' . h($this->currency . CakeNumber::format($totalAmount)) . '</strong></td>'
                . '<td>' . h($startDate) . '</td>'
                . '</tr>';
        }
        $html .= '</tbody></table>';
        return $html;
    }

    protected function _joinLetterList($items)
    {
        $items = array_values(array_filter(array_map('trim', (array)$items)));
        $count = count($items);
        if ($count === 0)
        {
            return '-';
        }
        if ($count === 1)
        {
            return $items[0];
        }
        $last = array_pop($items);
        return implode(', ', $items).' & '.$last;
    }

    protected function _dealOverdueSummaryForLetter($dealId, $asOfDate = null)
    {
        $dealId = (int)$dealId;
        $empty = array(
            'count' => 0,
            'label' => '-',
            'amount' => 0,
            'amount_text' => $this->currency.CakeNumber::format(0),
            'amount_words' => $this->_letterAmountInWords(0)
        );
        if ($dealId <= 0)
        {
            return $empty;
        }
        $asOfDate = trim((string)$asOfDate);
        if ($asOfDate === '' || strtotime($asOfDate) === false)
        {
            $asOfDate = date('Y-m-d');
        }
        else
        {
            $asOfDate = date('Y-m-d', strtotime($asOfDate));
        }
        try
        {
            $rows = (array)$this->Realestateletter->query(
                "SELECT PP.id, PP.name, PP.amount, PP.date
                 FROM plans_payments PP
                 LEFT JOIN deals_payments DP ON DP.plans_payment_id=PP.id
                 WHERE PP.deal_id=".$dealId."
                   AND (PP.is_deal_charge=0 OR PP.is_deal_charge IS NULL)
                   AND PP.date<='".$asOfDate."'
                   AND DP.id IS NULL
                 ORDER BY PP.date ASC, PP.id ASC"
            );
        }
        catch(Exception $e)
        {
            $rows = array();
        }
        $names = array();
        $amount = 0;
        foreach($rows as $row)
        {
            $data = isset($row['PP']) ? (array)$row['PP'] : (isset($row['plans_payments']) ? (array)$row['plans_payments'] : array());
            $name = isset($data['name']) ? trim((string)$data['name']) : '';
            if ($name !== '')
            {
                $names[] = $name;
            }
            $amount += isset($data['amount']) ? (float)$data['amount'] : 0;
        }
        $count = count($rows);
        if ($count <= 0)
        {
            return $empty;
        }
        $label = ($count > 3) ? __('Overdue Installments').' ('.$count.')' : $this->_joinLetterList($names);
        return array(
            'count' => $count,
            'label' => $label,
            'amount' => $amount,
            'amount_text' => $this->currency.CakeNumber::format((float)$amount),
            'amount_words' => $this->_letterAmountInWords($amount)
        );
    }

    protected function _firstDealPaymentForLetter($dealId)
    {
        $dealId = (int)$dealId;
        $empty = array(
            'receipt_no' => 'N/A',
            'payment_method' => 'Un-Paid',
            'remarks' => '',
            'payment_date' => '',
            'amount' => 0
        );
        if ($dealId <= 0)
        {
            return $empty;
        }
        try
        {
            $rows = (array)$this->Realestateletter->query(
                "SELECT DP.id, DP.receipt_no, DP.paymenttype_id, DP.remarks, DP.payment_date, DP.total_amount, DP.amount,
                        PT.name AS payment_method
                 FROM deals_payments DP
                 LEFT JOIN paymenttypes PT ON PT.id=DP.paymenttype_id
                 WHERE DP.deal_id=".(int)$dealId."
                 ORDER BY DP.id ASC
                 LIMIT 1"
            );
        }
        catch (Exception $e)
        {
            return $empty;
        }
        if (empty($rows[0]))
        {
            return $empty;
        }
        $dp = isset($rows[0]['DP']) ? (array)$rows[0]['DP'] : (isset($rows[0]['deals_payments']) ? (array)$rows[0]['deals_payments'] : array());
        $pt = isset($rows[0]['PT']) ? (array)$rows[0]['PT'] : (isset($rows[0]['paymenttypes']) ? (array)$rows[0]['paymenttypes'] : array());
        $flat = isset($rows[0][0]) ? (array)$rows[0][0] : array();
        $receiptNo = isset($dp['receipt_no']) ? trim((string)$dp['receipt_no']) : '';
        if ($receiptNo === '')
        {
            $receiptNo = 'N/A';
        }
        $paymentMethod = isset($flat['payment_method']) ? trim((string)$flat['payment_method']) : '';
        if ($paymentMethod === '' && isset($pt['payment_method']))
        {
            $paymentMethod = trim((string)$pt['payment_method']);
        }
        if ($paymentMethod === '' && isset($pt['name']))
        {
            $paymentMethod = trim((string)$pt['name']);
        }
        if ($paymentMethod === '')
        {
            $paymentMethod = 'Un-Paid';
        }
        $amount = 0;
        if (isset($dp['total_amount']) && (float)$dp['total_amount'] > 0)
        {
            $amount = (float)$dp['total_amount'];
        }
        elseif (isset($dp['amount']))
        {
            $amount = (float)$dp['amount'];
        }
        if ($receiptNo !== 'N/A')
        {
            try
            {
                $db = $this->Realestateletter->getDataSource();
                $receiptRows = (array)$this->Realestateletter->query(
                    "SELECT
                        COALESCE(SUM(CASE WHEN DP.amount IS NOT NULL AND DP.amount > 0 THEN DP.amount ELSE DP.total_amount END),0) AS receipt_paid_total,
                        GROUP_CONCAT(DISTINCT PP.name ORDER BY PP.id ASC SEPARATOR ', ') AS payment_names
                     FROM deals_payments DP
                     LEFT JOIN plans_payments PP ON PP.id=DP.plans_payment_id
                     WHERE DP.deal_id=".(int)$dealId."
                       AND DP.receipt_no=".$db->value($receiptNo, 'string')
                );
                if(isset($receiptRows[0][0]['receipt_paid_total']) && (float)$receiptRows[0][0]['receipt_paid_total'] > 0)
                {
                    $amount = (float)$receiptRows[0][0]['receipt_paid_total'];
                }
                if(!empty($receiptRows[0][0]['payment_names']))
                {
                    $dp['payment_names'] = trim((string)$receiptRows[0][0]['payment_names']);
                }
            }
            catch(Exception $e)
            {
                // Keep the first-row amount if grouped receipt lookup fails.
            }
        }
        return array(
            'receipt_no' => $receiptNo,
            'payment_method' => $paymentMethod,
            'remarks' => isset($dp['remarks']) ? trim((string)$dp['remarks']) : '',
            'payment_date' => !empty($dp['payment_date']) ? $this->_formatDateValue($dp['payment_date'], '') : '',
            'amount' => $amount,
            'payment_names' => isset($dp['payment_names']) ? trim((string)$dp['payment_names']) : ''
        );
    }

    protected function _dealTotalPaidForLetter($dealId)
    {
        $dealId = (int)$dealId;
        if ($dealId <= 0)
        {
            return 0;
        }
        try
        {
            $rows = (array)$this->Realestateletter->query(
                "SELECT COALESCE(SUM(CASE WHEN DP.amount IS NOT NULL AND DP.amount > 0 THEN DP.amount ELSE DP.total_amount END),0) AS paid_total
                 FROM deals_payments DP
                 WHERE DP.deal_id=".(int)$dealId
            );
        }
        catch (Exception $e)
        {
            return 0;
        }
        if (!empty($rows[0][0]['paid_total']))
        {
            return (float)$rows[0][0]['paid_total'];
        }
        return 0;
    }

    protected function _letterDealQrUrl($projectName, $clientName, $membershipNo, $paymentLabel, $totalPaid, $cacheKey = '', $dealQrData = array())
    {
        $dealQrData = (array)$dealQrData;
        if (!empty($dealQrData['verification_url']))
        {
            $qrText = trim((string)$dealQrData['verification_url']);
        }
        elseif (!empty($dealQrData))
        {
            $mobile = isset($dealQrData['agency_mobile']) ? trim((string)$dealQrData['agency_mobile']) : '';
            $phone = isset($dealQrData['agency_phone']) ? trim((string)$dealQrData['agency_phone']) : '';
            $contactParts = array();
            if ($mobile !== '')
            {
                $contactParts[] = __('Mobile') . ': ' . $mobile;
            }
            if ($phone !== '')
            {
                $contactParts[] = __('Phone') . ': ' . $phone;
            }
            $contactLine = implode(' | ', $contactParts);
            if ($contactLine === '')
            {
                $contactLine = '-';
            }
            $qrText = implode("\n", array(
                strtoupper(__('Agency Details')),
                trim((string)$projectName),
                __('Contact') . ': ' . $contactLine,
                '',
                strtoupper(__('Deal Details')),
                __('Membership No') . ': ' . trim((string)$membershipNo),
                __('Client Name') . ': ' . trim((string)$clientName),
                __('CNIC') . ': ' . (isset($dealQrData['client_cnic']) && trim((string)$dealQrData['client_cnic']) !== '' ? trim((string)$dealQrData['client_cnic']) : '-'),
                __('Deal Status') . ': ' . (isset($dealQrData['deal_status']) && trim((string)$dealQrData['deal_status']) !== '' ? trim((string)$dealQrData['deal_status']) : '-'),
                '',
                strtoupper(__('Property Details')),
                __('Property File #') . ': ' . (isset($dealQrData['property_file']) && trim((string)$dealQrData['property_file']) !== '' ? trim((string)$dealQrData['property_file']) : '-'),
                __('Property Area') . ': ' . (isset($dealQrData['property_area']) && trim((string)$dealQrData['property_area']) !== '' ? trim((string)$dealQrData['property_area']) : '-'),
                __('Property Type') . ': ' . (isset($dealQrData['property_type']) && trim((string)$dealQrData['property_type']) !== '' ? trim((string)$dealQrData['property_type']) : '-'),
                __('Contract Type') . ': ' . (isset($dealQrData['contract_type']) && trim((string)$dealQrData['contract_type']) !== '' ? trim((string)$dealQrData['contract_type']) : '-')
            ));
        }
        else
        {
            $qrText = implode("\n", array(
                'Project: '.trim((string)$projectName),
                'Client: '.trim((string)$clientName),
                'Deal: '.trim((string)$membershipNo),
                'Payment: '.trim((string)$paymentLabel),
                'Total Paid: '.$this->currency.CakeNumber::format((float)$totalPaid)
            ));
        }
        return 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&ecc=L&qzone=2&margin=8&format=png&charset-source=UTF-8&charset-target=UTF-8&data='
            . rawurlencode($qrText)
            . '&v=' . rawurlencode((string)$cacheKey)
            . '&cb=' . rawurlencode(date('YmdHis'));
    }

    protected function _letterVerificationToken($letterId, $dealId, $clientId)
    {
        return substr(Security::hash((int)$letterId.'|'.(int)$dealId.'|'.(int)$clientId.'|whitevillas-letter-verify', 'sha256', true), 0, 24);
    }

    protected function _letterVerificationUrl($letter)
    {
        $letter = (array)$letter;
        $letterId = !empty($letter['id']) ? (int)$letter['id'] : 0;
        if($letterId <= 0)
        {
            return '';
        }
        $dealId = !empty($letter['deal_id']) ? (int)$letter['deal_id'] : 0;
        $clientId = !empty($letter['client_id']) ? (int)$letter['client_id'] : 0;
        $token = $this->_letterVerificationToken($letterId, $dealId, $clientId);
        return Router::url('/verify/letter/' . $letterId . '/' . $token, true);
    }

    protected function _refundCompletionQrText($refundId, $clientName, $amountText, $dateText)
    {
        $refundId = (int)$refundId;
        $refundLabel = $refundId > 0 ? 'RFD-' . $refundId : '-';
        return implode("\n", array(
            'Deal Refund ID: ' . $refundLabel,
            'Client Name: ' . trim((string)$clientName),
            'Refund Total Deal Amount: ' . trim((string)$amountText),
            'Date: ' . trim((string)$dateText)
        ));
    }

    protected function _refundCompletionQrUrl($refundId, $clientName, $amountText, $dateText, $cacheKey = '')
    {
        $qrText = $this->_refundCompletionQrText($refundId, $clientName, $amountText, $dateText);
        return 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&ecc=L&qzone=2&margin=8&format=png&charset-source=UTF-8&charset-target=UTF-8&data='
            . rawurlencode($qrText)
            . '&v=' . rawurlencode((string)$cacheKey)
            . '&cb=' . rawurlencode(date('YmdHis'));
    }

    protected function _primaryPaymentBankAccountForLetter($projectId)
    {
        $projectId = (int)$projectId;
        if ($projectId <= 0)
        {
            return array();
        }
        try
        {
            $this->loadModel('Admin.Ardpaymentbankaccount');
            $rows = $this->Ardpaymentbankaccount->activeRowsForProject($projectId);
            return (array)$this->Ardpaymentbankaccount->primaryRow($rows);
        }
        catch(Exception $e)
        {
            return array();
        }
    }

    protected function _templateSignatureUrl($template, $fileField, $dirField)
    {
        $fileName = isset($template[$fileField]) ? trim((string)$template[$fileField]) : '';
        if ($fileName === '')
        {
            return '';
        }
        $dirName = isset($template[$dirField]) ? trim((string)$template[$dirField]) : 'letter_signatures';
        if ($dirName === '')
        {
            $dirName = 'letter_signatures';
        }
        return $this->_resolveImageFromCandidates(array(
            trim($dirName, '/\\').'/'.$fileName,
            trim($dirName, '/\\').'_thumb/'.$fileName,
            'letter_signatures/'.$fileName,
            $fileName
        ));
    }

    protected function _signatureImageTag($url, $altText, $fallbackTitle)
    {
        $url = trim((string)$url);
        if ($url !== '')
        {
            $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            $safeAlt = htmlspecialchars((string)$altText, ENT_QUOTES, 'UTF-8');
            return '<img class="sig-image" src="'.$safeUrl.'" alt="'.$safeAlt.'" style="max-height:72px;max-width:270px;display:inline-block;" />';
        }
        return '<div class="digital-stamp-mock">Securely Signed<br><strong>'.htmlspecialchars((string)$fallbackTitle, ENT_QUOTES, 'UTF-8').'</strong><br>Verification Ref</div>';
    }

    /**
     * Wrap dynamic English text, dates, CNICs, plot/file numbers, and currency values
     * inside <span dir="ltr" class="en-inline">...</span> for bi-directional Nastaleeq alignment.
     */
    protected function _wrapEnInline($value)
    {
        $val = trim((string)$value);
        if ($val === '' || $val === 'N/A' || $val === '-')
        {
            return $val;
        }
        if (strpos($val, 'en-inline') !== false)
        {
            return $val;
        }
        return '<span dir="ltr" class="en-inline">' . $val . '</span>';
    }

    protected function _placeholderMap($letterRow)
    {
        $letter = isset($letterRow['Realestateletter']) ? (array)$letterRow['Realestateletter'] : array();
        $deal = isset($letterRow['Deal']) ? (array)$letterRow['Deal'] : array();
        $client = isset($letterRow['Client']) ? (array)$letterRow['Client'] : array();
        $property = isset($letterRow['Property']) ? (array)$letterRow['Property'] : array();
        $unit = isset($letterRow['Unit']) ? (array)$letterRow['Unit'] : array();
        $type = isset($letterRow['Type']) ? (array)$letterRow['Type'] : array();
        $contract = isset($letterRow['Contract']) ? (array)$letterRow['Contract'] : array();
        $project = isset($letterRow['Project']) ? (array)$letterRow['Project'] : array();
        $letterTypeCode = isset($letter['letter_type']) ? trim((string)$letter['letter_type']) : '';
        $letterTypeLabel = isset($this->_letterTypeArr[$letterTypeCode]) ? (string)$this->_letterTypeArr[$letterTypeCode] : $letterTypeCode;
        if (trim((string)$letterTypeLabel) === '')
        {
            $letterTypeLabel = '-';
        }

        $letterDate = '';
        if (!empty($letter['letter_date']))
        {
            $letterDate = $this->_formatDateValue($letter['letter_date'], date($this->dtFormat));
        }
        else
        {
            $letterDate = date($this->dtFormat);
        }

        $propertyArea = '';
        if (!empty($property['area']))
        {
            $propertyArea = trim((string)$property['area']);
            if (!empty($unit['name']))
            {
                $propertyArea .= ' '.trim((string)$unit['name']);
            }
        }

        $profileUrl = $this->_resolveClientPhotoUrl($client);
        $idFrontDir = isset($client['id_front_dir']) ? trim((string)$client['id_front_dir']) : 'contact';
        if ($idFrontDir === '')
        {
            $idFrontDir = 'contact';
        }
        $idBackDir = isset($client['id_back_dir']) ? trim((string)$client['id_back_dir']) : 'contact';
        if ($idBackDir === '')
        {
            $idBackDir = 'contact';
        }
        $idFrontUrl = $this->_resolveClientDocUrl(
            isset($client['id_front']) ? $client['id_front'] : '',
            $idFrontDir,
            'contact_id_front'
        );
        $idBackUrl = $this->_resolveClientDocUrl(
            isset($client['id_back']) ? $client['id_back'] : '',
            $idBackDir,
            'contact_id_back'
        );
        $ballot = $this->_ballotingSnapshotByDeal(isset($deal['id']) ? (int)$deal['id'] : 0);
        $ballotArea = isset($ballot['unit_area']) ? trim((string)$ballot['unit_area']) : '';
        if ($ballotArea !== '' && !empty($ballot['area_unit']))
        {
            $ballotArea .= ' '.trim((string)$ballot['area_unit']);
        }
        $ballotDate = !empty($ballot['allotment_date']) ? $this->_formatDateValue($ballot['allotment_date'], '') : '';
        if (empty($project['id']) && !empty($property['project_id']))
        {
            $project['id'] = (int)$property['project_id'];
        }
        $propertyName = isset($property['name']) && trim((string)$property['name']) !== '' ? trim((string)$property['name']) : '';
        if ($propertyName === '' && !empty($ballot['property_no'])) {
            $propertyName = trim((string)$ballot['property_no']);
        }
        if ($propertyName === '' && !empty($property['uniq_id'])) {
            $propertyName = trim((string)$property['uniq_id']);
        }
        if ($propertyName === '' && !empty($deal['invoice_no'])) {
            $propertyName = trim((string)$deal['invoice_no']);
        }

        $ballotBlockName = isset($ballot['block_name']) ? trim((string)$ballot['block_name']) : '';
        if ($ballotBlockName === '' || strtoupper($ballotBlockName) === 'N/A') {
            $projNameCandidate = isset($project['name']) ? (string)$project['name'] : '';
            if (preg_match('/([A-Za-z0-9_-]+\s+Block)/i', $projNameCandidate, $matchesBlock)) {
                $ballotBlockName = trim($matchesBlock[1]);
            }
        }
        $ballotBlockDisplay = $this->_letterValueOrNa($ballotBlockName);
        $projectIdForAgency = isset($project['id']) ? (int)$project['id'] : 0;
        $agencyUserInfo = $this->_agencyUserInfo($projectIdForAgency);
        $agencyLogoUrl = $this->_resolveAgencyLogoUrl($project);
        $agencyName = isset($project['name']) && trim((string)$project['name']) !== '' ? (string)$project['name'] : '';
        if ($agencyName === '' && !empty($agencyUserInfo['name']))
        {
            $agencyName = (string)$agencyUserInfo['name'];
        }
        if ($agencyName === '')
        {
            $agencyName = (string)$this->siteOrganization;
        }
        $agencyAddress = isset($project['address']) && trim((string)$project['address']) !== '' ? (string)$project['address'] : '';
        if ($agencyAddress === '' && !empty($agencyUserInfo['address']))
        {
            $agencyAddress = (string)$agencyUserInfo['address'];
        }
        if ($agencyAddress === '')
        {
            $agencyAddress = (string)$this->siteAddress;
        }
        $agencyPhone = '';
        foreach (array(
            isset($project['phone']) ? $project['phone'] : '',
            isset($project['mobile']) ? $project['mobile'] : '',
            isset($agencyUserInfo['phone']) ? $agencyUserInfo['phone'] : '',
            isset($agencyUserInfo['mobile']) ? $agencyUserInfo['mobile'] : ''
        ) as $phoneCandidate)
        {
            $phoneCandidate = trim((string)$phoneCandidate);
            if ($phoneCandidate !== '')
            {
                $agencyPhone = $phoneCandidate;
                break;
            }
        }
        if ($agencyPhone === '')
        {
            $agencyPhone = (string)$this->contact;
        }
        $agencyEmail = isset($project['email']) && trim((string)$project['email']) !== '' ? (string)$project['email'] : '';
        if ($agencyEmail === '' && !empty($agencyUserInfo['email']))
        {
            $agencyEmail = (string)$agencyUserInfo['email'];
        }
        if ($agencyEmail === '')
        {
            $agencyEmail = (string)$this->siteEmail;
        }

        $template = isset($letterRow['Template']) ? (array)$letterRow['Template'] : array();
        $overdueSummary = $this->_dealOverdueSummaryForLetter(
            isset($deal['id']) ? (int)$deal['id'] : 0,
            !empty($letter['letter_date']) ? (string)$letter['letter_date'] : date('Y-m-d')
        );
        $firstDealPayment = $this->_firstDealPaymentForLetter(isset($deal['id']) ? (int)$deal['id'] : 0);
        $primaryBank = $this->_primaryPaymentBankAccountForLetter($projectIdForAgency);
        $ceoSignatureUrl = $this->_templateSignatureUrl($template, 'ceo_signature', 'ceo_signature_dir');
        $chairmanSignatureUrl = $this->_templateSignatureUrl($template, 'chairman_signature', 'chairman_signature_dir');
        $dealTotalAmount = isset($deal['total_amount']) ? (float)$deal['total_amount'] : 0;
        $dealBookingAmount = isset($deal['booking_amount']) ? (float)$deal['booking_amount'] : 0;
        $dealBalanceAmount = max(0, $dealTotalAmount - $dealBookingAmount);

        $dealChargeTypeLabels = array(
            'M' => __('Monthly'),
            'Q' => __('Quarterly'),
            'H' => __('Biannual'),
            'Y' => __('Yearly'),
            'O' => __('One Time')
        );
        $dealChargeCount = 0;
        $dealChargeTotal = 0;
        $dealChargeTypes = '';
        $dealChargeTypesUrdu = '';
        $dealChargeRows = array();
        $latestChargeType = '';
        $latestChargeCycleType = '';
        $latestChargeCycleCount = 0;
        $latestChargeCycleAmount = 0;
        $latestChargeTotalAmount = 0;
        $latestChargeStartDate = '';
        $dealId = isset($deal['id']) ? (int)$deal['id'] : 0;
        if ($dealId > 0)
        {
            try
            {
                $sumRows = $this->Realestateletter->query(
                    "SELECT
                        COUNT(*) AS charge_count,
                        COALESCE(SUM(`total_amount`),0) AS charge_total,
                        GROUP_CONCAT(DISTINCT TRIM(`charge_name`) ORDER BY TRIM(`charge_name`) SEPARATOR ', ') AS charge_types
                     FROM `deal_charges`
                     WHERE `deal_id`=".(int)$dealId." AND `status`=1"
                );
                if (!empty($sumRows[0][0]))
                {
                    if (isset($sumRows[0][0]['charge_count']))
                    {
                        $dealChargeCount = (int)$sumRows[0][0]['charge_count'];
                    }
                    if (isset($sumRows[0][0]['charge_total']) && $sumRows[0][0]['charge_total'] !== null)
                    {
                        $dealChargeTotal = round((float)$sumRows[0][0]['charge_total'], 2);
                    }
                    if (isset($sumRows[0][0]['charge_types']) && $sumRows[0][0]['charge_types'] !== null)
                    {
                        $dealChargeTypes = trim((string)$sumRows[0][0]['charge_types']);
                    }
                }
                $latestRows = $this->Realestateletter->query(
                    "SELECT `charge_name`,`cycle_type`,`cycle_count`,`cycle_amount`,`total_amount`,`start_date`
                     FROM `deal_charges`
                     WHERE `deal_id`=".(int)$dealId." AND `status`=1
                     ORDER BY `id` DESC
                     LIMIT 1"
                );
                if (!empty($latestRows[0][0]))
                {
                    $latest = (array)$latestRows[0][0];
                    $latestChargeType = isset($latest['charge_name']) ? trim((string)$latest['charge_name']) : '';
                    $latestChargeCycleType = isset($latest['cycle_type']) ? strtoupper(trim((string)$latest['cycle_type'])) : '';
                    $latestChargeCycleCount = isset($latest['cycle_count']) ? (int)$latest['cycle_count'] : 0;
                    $latestChargeCycleAmount = isset($latest['cycle_amount']) ? round((float)$latest['cycle_amount'], 2) : 0;
                    $latestChargeTotalAmount = isset($latest['total_amount']) ? round((float)$latest['total_amount'], 2) : 0;
                    $latestChargeStartDate = isset($latest['start_date']) ? (string)$latest['start_date'] : '';
                }
            }
            catch (Exception $e)
            {
                // Keep letter rendering safe even if deal charges table is not available.
            }
            $dealChargeRows = $this->_activeDealChargeRows($dealId);
            if (!empty($dealChargeRows))
            {
                $dealChargeCount = count($dealChargeRows);
                $dealChargeTotal = 0;
                $chargeNameSet = array();
                $chargeNameUrduSet = array();
                foreach ($dealChargeRows as $chargeRow)
                {
                    $rowChargeName = isset($chargeRow['charge_name']) ? trim((string)$chargeRow['charge_name']) : '';
                    $rowTotal = isset($chargeRow['total_amount']) ? (float)$chargeRow['total_amount'] : 0;
                    $dealChargeTotal += $rowTotal;
                    if ($rowChargeName !== '')
                    {
                        $chargeNameSet[$rowChargeName] = $rowChargeName;
                        $rowChargeUrdu = $this->_translateDealChargeUrdu($rowChargeName);
                        if ($rowChargeUrdu !== '')
                        {
                            $chargeNameUrduSet[$rowChargeUrdu] = $rowChargeUrdu;
                        }
                    }
                }
                $dealChargeTotal = round($dealChargeTotal, 2);
                $dealChargeTypes = !empty($chargeNameSet) ? implode(', ', array_values($chargeNameSet)) : $dealChargeTypes;
                $dealChargeTypesUrdu = !empty($chargeNameUrduSet) ? implode('ØŒ ', array_values($chargeNameUrduSet)) : '';
                $latest = end($dealChargeRows);
                if (is_array($latest))
                {
                    $latestChargeType = isset($latest['charge_name']) ? trim((string)$latest['charge_name']) : $latestChargeType;
                    $latestChargeCycleType = isset($latest['cycle_type']) ? strtoupper(trim((string)$latest['cycle_type'])) : $latestChargeCycleType;
                    $latestChargeCycleCount = isset($latest['cycle_count']) ? (int)$latest['cycle_count'] : $latestChargeCycleCount;
                    $latestChargeCycleAmount = isset($latest['cycle_amount']) ? round((float)$latest['cycle_amount'], 2) : $latestChargeCycleAmount;
                    $latestChargeTotalAmount = isset($latest['total_amount']) ? round((float)$latest['total_amount'], 2) : $latestChargeTotalAmount;
                    $latestChargeStartDate = isset($latest['start_date']) ? (string)$latest['start_date'] : $latestChargeStartDate;
                }
            }
        }
        if ($dealChargeTypes === '')
        {
            $dealChargeTypes = $latestChargeType;
        }
        if ($dealChargeTypes === '')
        {
            $dealChargeTypes = '-';
        }
        if ($latestChargeType === '')
        {
            $latestChargeType = '-';
        }
        if ($dealChargeTypesUrdu === '')
        {
            $dealChargeTypesUrdu = $this->_translateDealChargeUrdu($dealChargeTypes);
        }
        $latestChargeCycleTypeLabel = isset($dealChargeTypeLabels[$latestChargeCycleType]) ? $dealChargeTypeLabels[$latestChargeCycleType] : $latestChargeCycleType;
        if (trim((string)$latestChargeCycleTypeLabel) === '')
        {
            $latestChargeCycleTypeLabel = '-';
        }
        $latestChargeStartDateLabel = ($latestChargeStartDate !== '') ? $this->_formatDateValue($latestChargeStartDate, '') : '';

        $dealId = isset($deal['id']) ? (int)$deal['id'] : 0;
        $refundData = array();
        $refundId = 0;
        $refundPaidTotal = 0.0;
        $refundLastPaidDate = '';
        $refundScheduleTable = '';
        if ($dealId > 0)
        {
            try
            {
                $this->loadModel('Admin.Dealrefund');
                $this->loadModel('Admin.Dealrefundschedule');
                $refundId = isset($letter['refund_id']) ? (int)$letter['refund_id'] : 0;
                $refundRow = array();
                if ($refundId > 0)
                {
                    $refundRow = (array)$this->Dealrefund->find('first', array(
                        'conditions' => array('Dealrefund.id' => $refundId),
                        'recursive' => -1
                    ));
                }
                if (empty($refundRow['Dealrefund']['id']))
                {
                    $refundRow = (array)$this->Dealrefund->find('first', array(
                        'conditions' => array('Dealrefund.deal_id' => $dealId),
                        'order' => array('Dealrefund.id' => 'desc'),
                        'recursive' => -1
                    ));
                }
                if (!empty($refundRow['Dealrefund']['id']))
                {
                    $refundData = (array)$refundRow['Dealrefund'];
                    $refundId = (int)$refundData['id'];
                    $scheduleRows = (array)$this->Dealrefundschedule->find('all', array(
                        'conditions' => array('Dealrefundschedule.refund_id' => $refundId),
                        'order' => array('Dealrefundschedule.cycle_no' => 'asc'),
                        'recursive' => -1
                    ));
                    if (!empty($scheduleRows))
                    {
                        $scheduleHtml = '<div class="schedule-table-container">';
                        $scheduleHtml .= '<table>';
                        $scheduleHtml .= '<thead><tr><th>Cycle</th><th>Tentative Date</th><th>Amount (PKR)</th><th>Status</th></tr></thead><tbody>';
                        foreach ($scheduleRows as $schedRow)
                        {
                            $s = isset($schedRow['Dealrefundschedule']) ? $schedRow['Dealrefundschedule'] : array();
                            $cycleNo = isset($s['cycle_no']) ? (int)$s['cycle_no'] : 0;
                            $schedDate = isset($s['scheduled_date']) ? (string)$s['scheduled_date'] : '';
                            $schedLabel = $schedDate !== '' ? $this->_formatDateValue($schedDate, $schedDate) : '';
                            $schedAmount = isset($s['amount']) ? (float)$s['amount'] : 0;
                            $schedStatus = isset($s['status']) ? (string)$s['status'] : '';
                            if (strtolower(trim($schedStatus)) === 'paid')
                            {
                                $paidAmount = isset($s['paid_amount']) ? round((float)$s['paid_amount'], 2) : 0;
                                if ($paidAmount <= 0)
                                {
                                    $paidAmount = $schedAmount;
                                }
                                $refundPaidTotal = round($refundPaidTotal + $paidAmount, 2);
                                $paidDate = isset($s['paid_date']) ? trim((string)$s['paid_date']) : '';
                                if ($paidDate !== '' && $paidDate !== '0000-00-00' && ($refundLastPaidDate === '' || strcmp($paidDate, $refundLastPaidDate) > 0))
                                {
                                    $refundLastPaidDate = $paidDate;
                                }
                            }
                            $scheduleHtml .= '<tr><td>Cycle '.(int)$cycleNo.'</td><td>'.h($schedLabel).'</td><td>'.h(number_format($schedAmount, 2)).'</td><td>'.h($schedStatus).'</td></tr>';
                        }
                        $scheduleHtml .= '</tbody></table></div>';
                        $refundScheduleTable = $scheduleHtml;
                    }
                }
            }
            catch (Exception $e)
            {
                $refundData = array();
                $refundId = 0;
                $refundPaidTotal = 0.0;
                $refundLastPaidDate = '';
                $refundScheduleTable = '';
            }
        }

        $refundRequestDate = isset($refundData['request_date']) ? (string)$refundData['request_date'] : '';
        if ($refundRequestDate !== '' && $refundRequestDate !== '0000-00-00')
        {
            $refundRequestDate = $this->_formatDateValue($refundRequestDate, date($this->dtFormat));
        }
        $refundTotalPaid = isset($refundData['total_paid']) ? round((float)$refundData['total_paid'], 2) : 0;
        $refundDeductionPercent = isset($refundData['deduction_percent']) ? round((float)$refundData['deduction_percent'], 2) : 0;
        $refundDeductionAmount = isset($refundData['deduction_amount']) ? round((float)$refundData['deduction_amount'], 2) : 0;
        $refundNet = isset($refundData['net_refund']) ? round((float)$refundData['net_refund'], 2) : 0;
        $isRefundMarkup = ($refundDeductionPercent < 0 || $refundDeductionAmount < 0);
        $refundAdjPercentAbs = abs($refundDeductionPercent);
        $refundAdjAmountAbs = abs($refundDeductionAmount);
        $refundAdjPercentText = number_format($refundAdjPercentAbs, 2) . '%';
        $refundAdjAmountText = $this->currency . CakeNumber::format((float)$refundAdjAmountAbs);
        $refundAdjLabel = $isRefundMarkup ? 'Markup' : 'Deduction';
        $refundAdjSign = $isRefundMarkup ? '(+)' : '(-)';
        $refundAdjColor = $isRefundMarkup ? 'text-green-600' : 'text-red-600';
        $refundAdjStatement = $isRefundMarkup ?
            ('The markup of ' . $refundAdjPercentText . ' is final and non-reversible.') :
            ('The deduction of ' . $refundAdjPercentText . ' is final and non-reversible.');
        $refundCycleCount = isset($refundData['cycle_count']) ? (int)$refundData['cycle_count'] : 0;
        $refundCycleFrequency = isset($refundData['cycle_frequency']) ? (string)$refundData['cycle_frequency'] : '';
        $refundCycleAmount = ($refundCycleCount > 0) ? round($refundNet / $refundCycleCount, 2) : 0;
        if (!empty($refundData['per_cycle_amount']))
        {
            $refundCycleAmount = (float)$refundData['per_cycle_amount'];
        }

        $refundCompletionDate = $letterDate;
        if ($refundLastPaidDate !== '' && $refundLastPaidDate !== '0000-00-00')
        {
            $refundCompletionDate = $this->_formatDateValue($refundLastPaidDate, $letterDate);
        }
        $refundDisplayId = $refundId > 0 ? 'RFD-' . $refundId : '';
        $refundQrAmountText = $this->currency . CakeNumber::format((float)$refundNet);
        $refundCompletionQrText = $this->_refundCompletionQrText($refundId, isset($client['name']) ? (string)$client['name'] : '', $refundQrAmountText, $refundCompletionDate);
        $refundCompletionQrUrl = $this->_refundCompletionQrUrl($refundId, isset($client['name']) ? (string)$client['name'] : '', $refundQrAmountText, $refundCompletionDate, ($refundId > 0 ? 'refund-' . $refundId : 'letter-' . (isset($letter['id']) ? (int)$letter['id'] : 0)));
        $refundCompletionQrImg = '<img src="' . htmlspecialchars($refundCompletionQrUrl, ENT_QUOTES, 'UTF-8') . '" alt="Deal Refund QR Code" style="display:block;width:100%;height:100%;object-fit:contain;" />';
        $letterText = function($field, $fallback = '') use ($letter) {
            $value = isset($letter[$field]) ? trim((string)$letter[$field]) : '';
            return ($value !== '') ? $value : $fallback;
        };
        $chargeWorkDetails = $letterText('charge_work_details', $dealChargeTypesUrdu !== '' ? $dealChargeTypesUrdu : $dealChargeTypes);
        $chargeWorkStatus = $letterText('charge_work_status', 'In Progress');
        $chargePaymentMethod = $letterText('charge_payment_method', 'باہمی طے شدہ طریقہ کار / بینک ٹرانسفر / نقد رسید');
        $chargePaymentDeadline = $letterText('charge_payment_deadline', 'باہمی طے شدہ شیڈول / آخری تاریخ کے مطابق');
        $agreementPlace = $letterText('agreement_place', $agencyAddress !== '' ? $agencyAddress : $agencyName);
        $agencyRepresentativeName = $letterText('agency_representative_name', $agencyName);
        $agencyRepresentativeDesignation = $letterText('agency_representative_designation', 'مجاز افسر');
        $witnessOneName = $letterText('witness_one_name', '________________');
        $witnessOneCnic = $letterText('witness_one_cnic', '________________');
        $witnessOneContact = $letterText('witness_one_contact', '________________');
        $witnessOneAddress = $letterText('witness_one_address', '________________');
        $witnessTwoName = $letterText('witness_two_name', '________________');
        $witnessTwoCnic = $letterText('witness_two_cnic', '________________');
        $witnessTwoContact = $letterText('witness_two_contact', '________________');
        $witnessTwoAddress = $letterText('witness_two_address', '________________');
        $agreementDateSource = !empty($letter['letter_date']) ? (string)$letter['letter_date'] : date('Y-m-d');
        $agreementPaymentPlan = $letterText('agreement_payment_plan', 'بمطابق منظور شدہ ادائیگی شیڈول');
        $agreementPenaltyRate = $letterText('agreement_penalty_rate', '0.1%');
        $agreementPossessionMonths = $letterText('agreement_possession_months', '36 ماہ');
        $agreementExtraWorkAmount = $letterText('agreement_extra_work_amount', '0');
        $agreementExtraWorkSchedule = $letterText('agreement_extra_work_schedule', 'بمطابق مٹیریل خریداری مرحلہ');
        $agreementCancellationDeductionRate = $letterText('agreement_cancellation_deduction_rate', '20%');
        $agreementRefundPeriod = $letterText('agreement_refund_period', '90 ایام');
        $agreementCourtJurisdiction = $letterText('agreement_court_jurisdiction', 'لاہور');
        $agencyRepresentativeFather = $letterText('agency_representative_father', '________________');
        $agencyRepresentativeCnic = $letterText('agency_representative_cnic', '________________');
        $fallbackPropertyBlock = isset($ballot['block_name']) ? trim((string)$ballot['block_name']) : '';
        if ($fallbackPropertyBlock === '')
        {
            $fallbackPropertyBlock = 'N/A';
        }
        $agreementPropertyBlock = $letterText('agreement_property_block', $fallbackPropertyBlock);
        $fallbackUnitNo = isset($ballot['property_no']) ? trim((string)$ballot['property_no']) : '';
        if ($fallbackUnitNo === '' && !empty($property['name']))
        {
            $fallbackUnitNo = (string)$property['name'];
        }
        $agreementPropertyUnitNo = $letterText('agreement_property_unit_no', $fallbackUnitNo);
        $fallbackFloor = isset($unit['name']) ? trim((string)$unit['name']) : '';
        if ($fallbackFloor === '' && !empty($type['name']))
        {
            $fallbackFloor = (string)$type['name'];
        }
        $agreementPropertyFloor = $letterText('agreement_property_floor', $fallbackFloor);
        $agreementPropertyCoveredArea = $letterText('agreement_property_covered_area', '________________');
        $fallbackLocation = isset($ballot['street_address']) ? trim((string)$ballot['street_address']) : '';
        if ($fallbackLocation === '' && !empty($project['address']))
        {
            $fallbackLocation = (string)$project['address'];
        }
        if ($fallbackLocation === '' && !empty($project['name']))
        {
            $fallbackLocation = (string)$project['name'];
        }
        $agreementPropertyLocation = $letterText('agreement_property_location', $fallbackLocation);
        $propertyAgreementCategory = $this->_dealAgreementPropertyCategory(
            isset($type['name']) ? (string)$type['name'] : '',
            isset($property['name']) ? (string)$property['name'] : '',
            isset($contract['name']) ? (string)$contract['name'] : ''
        );
        $propertyAgreementTerms = $this->_dealAgreementPropertyTermsHtml($propertyAgreementCategory);
        $dealTotalWithCharges = $dealTotalAmount;
        $clientNameForQr = isset($client['name']) ? trim((string)$client['name']) : '';
        $dealNoForQr = isset($deal['invoice_no']) ? trim((string)$deal['invoice_no']) : '';
        $firstPaymentAmountForQr = isset($firstDealPayment['amount']) ? (float)$firstDealPayment['amount'] : 0;
        $paymentLabelForQr = isset($firstDealPayment['payment_method']) ? trim((string)$firstDealPayment['payment_method']) : '';
        if ($paymentLabelForQr === '')
        {
            $paymentLabelForQr = 'Un-Paid';
        }
        if ($firstPaymentAmountForQr > 0)
        {
            $paymentLabelForQr .= ' - '.$this->currency.CakeNumber::format($firstPaymentAmountForQr);
        }
        $dealTotalPaidForQr = $this->_dealTotalPaidForLetter($dealId);
        $agencyMobileForQr = '';
        foreach (array(
            isset($project['mobile']) ? $project['mobile'] : '',
            isset($agencyUserInfo['mobile']) ? $agencyUserInfo['mobile'] : ''
        ) as $mobileCandidate)
        {
            $mobileCandidate = trim((string)$mobileCandidate);
            if ($mobileCandidate !== '')
            {
                $agencyMobileForQr = $mobileCandidate;
                break;
            }
        }
        $agencyPhoneForQr = '';
        foreach (array(
            isset($project['phone']) ? $project['phone'] : '',
            isset($agencyUserInfo['phone']) ? $agencyUserInfo['phone'] : '',
            $this->contact
        ) as $phoneCandidate)
        {
            $phoneCandidate = trim((string)$phoneCandidate);
            if ($phoneCandidate !== '')
            {
                $agencyPhoneForQr = $phoneCandidate;
                break;
            }
        }
        $dealStatusForQr = '-';
        if (!empty($deal['deal_status']))
        {
            $dealStatusForQr = trim((string)$deal['deal_status']);
        }
        elseif (!empty($deal['status']))
        {
            $dealStatusForQr = trim((string)$deal['status']);
        }
        $letterVerificationUrl = $this->_letterVerificationUrl($letter);
        $letterDealQrUrl = $this->_letterDealQrUrl(
            $agencyName,
            $clientNameForQr,
            $dealNoForQr,
            $paymentLabelForQr,
            $dealTotalPaidForQr,
            ($dealId > 0 ? 'deal-'.$dealId : 'letter-'.(isset($letter['id']) ? (int)$letter['id'] : 0)),
            array(
                'agency_mobile' => $agencyMobileForQr,
                'agency_phone' => $agencyPhoneForQr,
                'client_cnic' => isset($client['pincode']) ? (string)$client['pincode'] : '-',
                'deal_status' => $dealStatusForQr,
                'property_file' => isset($property['name']) ? (string)$property['name'] : '-',
                'property_area' => $propertyArea,
                'property_type' => isset($type['name']) ? (string)$type['name'] : '-',
                'contract_type' => isset($contract['name']) ? (string)$contract['name'] : '-',
                'verification_url' => $letterVerificationUrl
            )
        );
        $letterDealQrImg = '<img src="'.htmlspecialchars($letterDealQrUrl, ENT_QUOTES, 'UTF-8').'" alt="Deal QR Code" style="display:block;width:100%;height:100%;object-fit:contain;" />';
        $letterVerificationToken = $this->_letterVerificationToken(isset($letter['id']) ? (int)$letter['id'] : 0, $dealId, isset($client['id']) ? (int)$client['id'] : 0);
        $letterSecurityHash = 'WV-SEC-' . strtoupper(substr(hash('sha256', (isset($letter['id']) ? (int)$letter['id'] : 0) . '|' . $letterVerificationToken), 0, 8)) . '-' . date('Y');
        $letterVerificationQrUrl = $letterVerificationUrl !== '' ? ('https://api.qrserver.com/v1/create-qr-code/?size=400x400&ecc=H&margin=4&format=png&data=' . rawurlencode($letterVerificationUrl)) : $letterDealQrUrl;
        $letterVerificationQrImg = '<img src="'.htmlspecialchars($letterVerificationQrUrl, ENT_QUOTES, 'UTF-8').'" alt="Official Verification QR" style="display:block;width:100%;height:100%;object-fit:contain;image-rendering:pixelated;" />';
        $firstDealPaymentAmount = (float)(isset($firstDealPayment['amount']) ? $firstDealPayment['amount'] : 0);
        $firstDealPaymentNames = isset($firstDealPayment['payment_names']) ? (string)$firstDealPayment['payment_names'] : '';
        $clientNameDisplay = isset($client['name']) ? (string)$client['name'] : '';
        $clientFatherNameDisplay = isset($client['fathername']) ? (string)$client['fathername'] : '';
        $clientAddressDisplay = isset($client['address']) ? (string)$client['address'] : '';
        $clientRelationType = isset($client['relation_type']) ? (string)$client['relation_type'] : '';
        $clientGender = isset($client['gender']) ? (string)$client['gender'] : '';
        $clientNameWithFather = $this->_formatClientNameWithRelation($clientNameDisplay, $clientFatherNameDisplay, $clientRelationType, $clientGender);
        $clientRelationPrefix = $this->_resolveRelationPrefix($clientRelationType, $clientGender);
        if ($letterTypeCode === 'RLT_REFUND_CLEARANCE')
        {
            $clientNameParts = $this->_letterRefundClientNameParts($clientNameDisplay, $clientFatherNameDisplay);
            $clientNameDisplay = $clientNameParts['name'];
            $clientFatherNameDisplay = $clientNameParts['fathername'];
            $clientNameWithFather = $this->_formatClientNameWithRelation($clientNameDisplay, $clientFatherNameDisplay, $clientRelationType, $clientGender);
            $clientNameDisplay = $this->_letterRomanTextToUrdu($clientNameDisplay);
            $clientFatherNameDisplay = $this->_letterRomanTextToUrdu($clientFatherNameDisplay);
            $clientAddressDisplay = $this->_letterRomanTextToUrdu($clientAddressDisplay);
        }
        elseif ($letterTypeCode === 'RLT_REFUND' || !empty($refundData['id']))
        {
            if (!empty($refundData['applicant_name']))
            {
                $clientNameDisplay = (string)$refundData['applicant_name'];
            }
            if (!empty($refundData['father_name']))
            {
                $clientFatherNameDisplay = (string)$refundData['father_name'];
            }
            if (!empty($refundData['relation_type']))
            {
                $clientRelationType = (string)$refundData['relation_type'];
            }
            if (!empty($refundData['cnic']))
            {
                $client['pincode'] = (string)$refundData['cnic'];
            }
            if (!empty($refundData['mobile_no']))
            {
                $client['mobile'] = (string)$refundData['mobile_no'];
            }
            if (!empty($refundData['email']))
            {
                $client['email'] = (string)$refundData['email'];
            }
            if (!empty($refundData['address']))
            {
                $clientAddressDisplay = (string)$refundData['address'];
            }
            $clientNameWithFather = $this->_formatClientNameWithRelation($clientNameDisplay, $clientFatherNameDisplay, $clientRelationType, $clientGender);
            $clientRelationPrefix = $this->_resolveRelationPrefix($clientRelationType, $clientGender);
        }

        $isBookingOrWelcome = in_array($letterTypeCode, array('RLT_BOOKING', 'RLT_WELCOME'), true);
        $en = function($v) use ($isBookingOrWelcome) {
            return $isBookingOrWelcome ? $v : $this->_wrapEnInline($v);
        };

        return array(
            '{{site_name}}' => (string)$this->siteName,
            '{{site_organization}}' => (string)$this->siteOrganization,
            '{{site_address}}' => (string)$this->siteAddress,
            '{{site_contact}}' => (string)$this->contact,
            '{{site_email}}' => (string)$this->siteEmail,
            '{{agency_logo_url}}' => $agencyLogoUrl,
            '{{agency_logo}}' => $this->_agencyLogoImageTag($agencyLogoUrl, $agencyName),
            '{{agency_name}}' => $agencyName,
            '{{agency_address}}' => $agencyAddress,
            '{{agency_phone}}' => $agencyPhone,
            '{{agency_email}}' => $agencyEmail,
            '{{today}}' => $en(date($this->dtFormat)),
            '{{letter_date}}' => $en($letterDate),
            '{{agreement_date_text_urdu}}' => $this->_letterDateUrduText($agreementDateSource),
            '{{agreement_day_name_urdu}}' => $this->_letterUrduDayName($agreementDateSource),
            '{{letter_ref_no}}' => $en(isset($letter['reference_no']) ? (string)$letter['reference_no'] : ''),
            '{{letter_title}}' => isset($letter['title']) ? (string)$letter['title'] : '',
            '{{letter_type_label}}' => $letterTypeLabel,
            '{{client_name}}' => $clientNameDisplay,
            '{{client_father_name}}' => $clientFatherNameDisplay,
            '{{client_name_with_father}}' => $clientNameWithFather,
            '{{client_name_with_so}}' => $clientNameWithFather,
            '{{applicant_name}}' => $clientNameWithFather,
            '{{applicant_name_with_father}}' => $clientNameWithFather,
            '{{applicant_name_with_so}}' => $clientNameWithFather,
            '{{client_relation_prefix}}' => $clientRelationPrefix,
            '{{client_father_with_prefix}}' => (trim((string)$clientFatherNameDisplay) !== '' ? ($clientRelationPrefix . ' ' . trim((string)$clientFatherNameDisplay)) : ''),
            '{{client_cnic}}' => $en(isset($client['pincode']) ? (string)$client['pincode'] : ''),
            '{{client_phone}}' => $en(isset($client['phone']) ? (string)$client['phone'] : ''),
            '{{client_mobile}}' => $en(isset($client['mobile']) ? (string)$client['mobile'] : ''),
            '{{client_email}}' => isset($client['email']) ? (string)$client['email'] : '',
            '{{client_address}}' => $clientAddressDisplay,
            '{{client_occupation}}' => !empty($deal['occupation']) ? (string)$deal['occupation'] : 'N/A',
            '{{occupation}}' => !empty($deal['occupation']) ? (string)$deal['occupation'] : 'N/A',
            '{{deal_occupation}}' => !empty($deal['occupation']) ? (string)$deal['occupation'] : 'N/A',
            '{{nominee_name}}' => isset($deal['nominee_name']) ? (string)$deal['nominee_name'] : '',
            '{{nominee_cnic}}' => $en(isset($deal['nominee_cnic']) ? (string)$deal['nominee_cnic'] : ''),
            '{{nominee_mobile}}' => $en(isset($deal['nominee_mobile']) ? (string)$deal['nominee_mobile'] : ''),
            '{{nominee_phone}}' => $en(isset($deal['nominee_mobile']) ? (string)$deal['nominee_mobile'] : ''),
            '{{nominee_relation}}' => isset($deal['nominee_relation']) ? (string)$deal['nominee_relation'] : '',
            '{{client_profile_photo_url}}' => $profileUrl,
            '{{client_id_front_image_url}}' => $idFrontUrl,
            '{{client_id_back_image_url}}' => $idBackUrl,
            '{{client_profile_photo}}' => $this->_contactImageTag($profileUrl, 'Client Profile Photo'),
            '{{client_id_front_image}}' => $this->_contactImageTag($idFrontUrl, 'Client ID Front'),
            '{{client_id_back_image}}' => $this->_contactImageTag($idBackUrl, 'Client ID Back'),
            '{{applicant_photo}}' => $this->_contactImageTag($profileUrl, 'Applicant Photo'),
            '{{applicant_photo_url}}' => $profileUrl,
            '{{deal_invoice_no}}' => $en(isset($deal['invoice_no']) ? (string)$deal['invoice_no'] : ''),
            '{{deal_date}}' => $en(!empty($deal['date']) ? $this->_formatDateValue($deal['date'], '') : ''),
            '{{deal_status}}' => $dealStatusForQr,
            '{{deal_total_amount}}' => $en(isset($deal['total_amount']) ? $this->currency.CakeNumber::format((float)$deal['total_amount']) : ''),
            '{{deal_total_amount_words}}' => $this->_letterAmountInWords($dealTotalAmount),
            '{{deal_total_amount_words_urdu}}' => $this->_letterAmountInUrduWords($dealTotalAmount),
            '{{deal_total_with_charges}}' => $en($this->currency.CakeNumber::format((float)$dealTotalWithCharges)),
            '{{deal_total_with_charges_words_urdu}}' => $this->_letterAmountInUrduWords($dealTotalWithCharges),
            '{{deal_discount}}' => $en(isset($deal['discount']) ? $this->currency.CakeNumber::format((float)$deal['discount']) : ''),
            '{{deal_booking_amount}}' => $en(isset($deal['booking_amount']) ? $this->currency.CakeNumber::format((float)$deal['booking_amount']) : ''),
            '{{deal_balance_amount}}' => $en($this->currency.CakeNumber::format((float)$dealBalanceAmount)),
            '{{deal_balance_amount_words_urdu}}' => $this->_letterAmountInUrduWords($dealBalanceAmount),
            '{{deal_booking_receipt_no}}' => $en(isset($firstDealPayment['receipt_no']) ? (string)$firstDealPayment['receipt_no'] : ''),
            '{{deal_booking_payment_method}}' => isset($firstDealPayment['payment_method']) ? (string)$firstDealPayment['payment_method'] : '',
            '{{deal_booking_payment_remarks}}' => isset($firstDealPayment['remarks']) ? (string)$firstDealPayment['remarks'] : '',
            '{{deal_booking_payment_date}}' => $en(isset($firstDealPayment['payment_date']) ? (string)$firstDealPayment['payment_date'] : ''),
            '{{deal_booking_payment_amount}}' => $en($this->currency.CakeNumber::format($firstDealPaymentAmount)),
            '{{deal_booking_payment_amount_words}}' => $this->_letterAmountInWords($firstDealPaymentAmount),
            '{{deal_booking_payment_amount_words_urdu}}' => $this->_letterAmountInUrduWords($firstDealPaymentAmount),
            '{{deal_booking_payment_against}}' => $firstDealPaymentNames,
            '{{first_deal_payment_receipt_no}}' => $en(isset($firstDealPayment['receipt_no']) ? (string)$firstDealPayment['receipt_no'] : ''),
            '{{first_deal_payment_method}}' => isset($firstDealPayment['payment_method']) ? (string)$firstDealPayment['payment_method'] : '',
            '{{first_deal_payment_amount}}' => $en($this->currency.CakeNumber::format($firstDealPaymentAmount)),
            '{{first_deal_payment_amount_words}}' => $this->_letterAmountInWords($firstDealPaymentAmount),
            '{{first_deal_payment_amount_words_urdu}}' => $this->_letterAmountInUrduWords($firstDealPaymentAmount),
            '{{first_deal_payment_against}}' => $firstDealPaymentNames,
            '{{letter_deal_qr_url}}' => $letterDealQrUrl,
            '{{deal_letter_qr_url}}' => $letterDealQrUrl,
            '{{letter_deal_qr}}' => $letterDealQrImg,
            '{{deal_letter_qr}}' => $letterDealQrImg,
            '{{letter_verification_url}}' => $letterVerificationUrl,
            '{{letter_verification_qr_url}}' => $letterVerificationQrUrl,
            '{{letter_verification_qr}}' => $letterVerificationQrImg,
            '{{letter_security_hash}}' => $letterSecurityHash,
            '{{letter_security_token}}' => $letterSecurityHash,
            '{{overdue_installments_label}}' => isset($overdueSummary['label']) ? (string)$overdueSummary['label'] : '-',
            '{{overdue_installments_count}}' => $en(isset($overdueSummary['count']) ? (string)$overdueSummary['count'] : '0'),
            '{{overdue_amount}}' => $en(isset($overdueSummary['amount_text']) ? (string)$overdueSummary['amount_text'] : $this->currency.CakeNumber::format(0)),
            '{{overdue_amount_words}}' => isset($overdueSummary['amount_words']) ? (string)$overdueSummary['amount_words'] : $this->_letterAmountInWords(0),
            '{{final_notice_deadline_days}}' => '7',
            '{{bank_name}}' => isset($primaryBank['bank_name']) ? (string)$primaryBank['bank_name'] : '',
            '{{bank_account_title}}' => isset($primaryBank['account_title']) ? (string)$primaryBank['account_title'] : '',
            '{{bank_account_no}}' => $en(isset($primaryBank['account_no']) ? (string)$primaryBank['account_no'] : ''),
            '{{bank_iban}}' => $en(isset($primaryBank['iban']) ? (string)$primaryBank['iban'] : ''),
            '{{bank_branch}}' => isset($primaryBank['branch']) ? (string)$primaryBank['branch'] : '',
            '{{bank_swift_code}}' => isset($primaryBank['swift_code']) ? (string)$primaryBank['swift_code'] : '',
            '{{agency_bank_name}}' => isset($primaryBank['bank_name']) ? (string)$primaryBank['bank_name'] : '',
            '{{agency_account_title}}' => isset($primaryBank['account_title']) ? (string)$primaryBank['account_title'] : '',
            '{{agency_account_no}}' => $en(isset($primaryBank['account_no']) ? (string)$primaryBank['account_no'] : ''),
            '{{agency_iban}}' => $en(isset($primaryBank['iban']) ? (string)$primaryBank['iban'] : ''),
            '{{ceo_signature_url}}' => $ceoSignatureUrl,
            '{{chairman_signature_url}}' => $chairmanSignatureUrl,
            '{{ceo_signature}}' => $this->_signatureImageTag($ceoSignatureUrl, 'CEO Signature', 'CEO Office'),
            '{{chairman_signature}}' => $this->_signatureImageTag($chairmanSignatureUrl, 'Chairman Signature', 'Board Chairman'),
            '{{deal_charges_total}}' => $en($this->currency.CakeNumber::format((float)$dealChargeTotal)),
            '{{deal_charges_total_words}}' => $this->_letterAmountInWords($dealChargeTotal),
            '{{deal_charges_total_words_urdu}}' => $this->_letterAmountInUrduWords($dealChargeTotal),
            '{{deal_charges_count}}' => $en((string)$dealChargeCount),
            '{{deal_charges_types}}' => $dealChargeTypes,
            '{{deal_charges_types_urdu}}' => $dealChargeTypesUrdu,
            '{{deal_charges_table}}' => $this->_dealChargesTableHtml($dealChargeRows),
            '{{deal_charge_type}}' => $latestChargeType,
            '{{deal_charge_type_urdu}}' => $this->_translateDealChargeUrdu($latestChargeType),
            '{{deal_charge_cycle_type}}' => $latestChargeCycleTypeLabel,
            '{{deal_charge_cycle_count}}' => $en((string)$latestChargeCycleCount),
            '{{deal_charge_cycle_amount}}' => $en($this->currency.CakeNumber::format((float)$latestChargeCycleAmount)),
            '{{deal_charge_total_amount}}' => $en($this->currency.CakeNumber::format((float)$latestChargeTotalAmount)),
            '{{deal_charge_total_amount_words_urdu}}' => $this->_letterAmountInUrduWords($latestChargeTotalAmount),
            '{{deal_charge_start_date}}' => $en($latestChargeStartDateLabel),
            '{{charge_work_details}}' => $chargeWorkDetails,
            '{{charge_work_status}}' => $chargeWorkStatus,
            '{{charge_work_status_urdu}}' => $this->_chargeWorkStatusUrdu($chargeWorkStatus),
            '{{charge_payment_method}}' => $chargePaymentMethod,
            '{{charge_payment_deadline}}' => $chargePaymentDeadline,
            '{{agreement_place}}' => $agreementPlace,
            '{{agency_representative_name}}' => $agencyRepresentativeName,
            '{{agency_representative_father}}' => $agencyRepresentativeFather,
            '{{agency_representative_cnic}}' => $en($agencyRepresentativeCnic),
            '{{agency_representative_designation}}' => $agencyRepresentativeDesignation,
            '{{agreement_payment_plan}}' => $agreementPaymentPlan,
            '{{agreement_penalty_rate}}' => $en($agreementPenaltyRate),
            '{{agreement_possession_months}}' => $en($agreementPossessionMonths),
            '{{agreement_extra_work_amount}}' => $en($agreementExtraWorkAmount),
            '{{agreement_extra_work_schedule}}' => $agreementExtraWorkSchedule,
            '{{agreement_cancellation_deduction_rate}}' => $en($agreementCancellationDeductionRate),
            '{{agreement_refund_period}}' => $en($agreementRefundPeriod),
            '{{agreement_court_jurisdiction}}' => $agreementCourtJurisdiction,
            '{{agreement_property_block}}' => $en($agreementPropertyBlock),
            '{{agreement_property_unit_no}}' => $en($agreementPropertyUnitNo),
            '{{agreement_property_floor}}' => $en($agreementPropertyFloor),
            '{{agreement_property_covered_area}}' => $en($agreementPropertyCoveredArea),
            '{{agreement_property_location}}' => $agreementPropertyLocation,
            '{{property_agreement_category}}' => $propertyAgreementCategory,
            '{{property_agreement_terms}}' => $propertyAgreementTerms,
            '{{witness_one_name}}' => $witnessOneName,
            '{{witness_one_cnic}}' => $en($witnessOneCnic),
            '{{witness_one_contact}}' => $en($witnessOneContact),
            '{{witness_one_address}}' => $witnessOneAddress,
            '{{witness_two_name}}' => $witnessTwoName,
            '{{witness_two_cnic}}' => $en($witnessTwoCnic),
            '{{witness_two_contact}}' => $en($witnessTwoContact),
            '{{witness_two_address}}' => $witnessTwoAddress,
            '{{refund_id}}' => $refundId > 0 ? (string)$refundId : '',
            '{{deal_refund_id}}' => $refundDisplayId,
            '{{refund_request_date}}' => $en($refundRequestDate),
            '{{refund_total_paid}}' => $en($this->currency.CakeNumber::format((float)$refundTotalPaid)),
            '{{refund_deduction_percent}}' => ($isRefundMarkup ? ('+' . $refundAdjPercentText . ' (Markup)') : $refundAdjPercentText),
            '{{refund_deduction_amount}}' => $en($refundAdjAmountText),
            '{{refund_adjustment_label}}' => $refundAdjLabel,
            '{{refund_adjustment_sign}}' => $refundAdjSign,
            '{{refund_adjustment_percent}}' => $refundAdjPercentText,
            '{{refund_adjustment_amount}}' => $en($refundAdjAmountText),
            '{{refund_adjustment_color}}' => $refundAdjColor,
            '{{refund_adjustment_statement}}' => $refundAdjStatement,
            '{{refund_father_name}}' => !empty($refundData['father_name']) ? (string)$refundData['father_name'] : $clientFatherNameDisplay,
            '{{refund_relation_type}}' => !empty($refundData['relation_type']) ? (string)$refundData['relation_type'] : $clientRelationType,
            '{{client_relation_type_label}}' => (!empty($clientRelationType) ? $clientRelationType : 'Guardian'),
            '{{refund_net_refund}}' => $en($this->currency.CakeNumber::format((float)$refundNet)),
            '{{refund_net_refund_words}}' => $this->_letterAmountInWords($refundNet),
            '{{refund_net_refund_words_urdu}}' => $this->_letterAmountInUrduWords($refundNet),
            '{{refund_paid_total}}' => $en($this->currency.CakeNumber::format((float)$refundPaidTotal)),
            '{{refund_completion_date}}' => $en($refundCompletionDate),
            '{{refund_cycle_count}}' => $en((string)$refundCycleCount),
            '{{refund_cycle_frequency}}' => $refundCycleFrequency,
            '{{refund_cycle_amount}}' => $en($this->currency.CakeNumber::format((float)$refundCycleAmount)),
            '{{refund_account_title}}' => isset($refundData['account_title']) ? (string)$refundData['account_title'] : '',
            '{{refund_account_no}}' => $en(isset($refundData['account_no']) ? (string)$refundData['account_no'] : ''),
            '{{refund_bank_name}}' => isset($refundData['bank_name']) ? (string)$refundData['bank_name'] : '',
            '{{refund_bank_branch}}' => isset($refundData['bank_branch_city']) ? (string)$refundData['bank_branch_city'] : '',
            '{{refund_reason}}' => isset($refundData['reason']) ? (string)$refundData['reason'] : '',
            '{{refund_schedule_table}}' => $refundScheduleTable,
            '{{refund_completion_qr_url}}' => $refundCompletionQrUrl,
            '{{refund_completion_qr}}' => $refundCompletionQrImg,
            '{{refund_completion_qr_text}}' => $refundCompletionQrText,
            '{{refund_completion_qr_text_json}}' => json_encode($refundCompletionQrText),
            '{{property_name}}' => $propertyName,
            '{{Property_Name}}' => $propertyName,
            '{{PROPERTY_NAME}}' => $propertyName,
            '{{PropertyName}}' => $propertyName,
            '{{property_title}}' => $propertyName,
            '{{property_file}}' => $propertyName,
            '{{property_area}}' => $en($propertyArea),
            '{{property_unit}}' => isset($unit['name']) ? (string)$unit['name'] : '',
            '{{property_price}}' => $en(isset($property['price']) ? $this->currency.CakeNumber::format((float)$property['price']) : ''),
            '{{property_type}}' => isset($type['name']) ? (string)$type['name'] : '',
            '{{property_contract}}' => isset($contract['name']) ? (string)$contract['name'] : '',
            '{{project_name}}' => isset($project['name']) ? (string)$project['name'] : '',
            '{{ballot_allotment_no}}' => $en(isset($ballot['allotment_no']) ? (string)$ballot['allotment_no'] : ''),
            '{{ballot_permanent_type}}' => isset($ballot['permanent_type']) ? (string)$ballot['permanent_type'] : '',
            '{{ballot_property_no}}' => $en(isset($ballot['property_no']) ? (string)$ballot['property_no'] : ''),
            '{{ballot_street_no}}' => isset($ballot['street_no']) ? (string)$ballot['street_no'] : '',
            '{{ballot_block_name}}' => $ballotBlockDisplay,
            '{{block_name}}' => $ballotBlockDisplay,
            '{{Block_Name}}' => $ballotBlockDisplay,
            '{{BLOCK_NAME}}' => $ballotBlockDisplay,
            '{{ballot_street_address}}' => isset($ballot['street_address']) ? (string)$ballot['street_address'] : '',
            '{{ballot_unit_area}}' => isset($ballot['unit_area']) ? (string)$ballot['unit_area'] : '',
            '{{ballot_area_unit}}' => isset($ballot['area_unit']) ? (string)$ballot['area_unit'] : '',
            '{{ballot_area_full}}' => $en($ballotArea),
            '{{ballot_property_type}}' => isset($ballot['property_type']) ? (string)$ballot['property_type'] : '',
            '{{ballot_unit_name}}' => isset($ballot['unit_name']) ? (string)$ballot['unit_name'] : '',
            '{{ballot_category_name}}' => isset($ballot['category_name']) ? (string)$ballot['category_name'] : '',
            '{{ballot_allotment_date}}' => $en($ballotDate),
            '{{allotment_no}}' => $en(isset($ballot['allotment_no']) ? (string)$ballot['allotment_no'] : ''),
            '{{street_address}}' => isset($ballot['street_address']) ? (string)$ballot['street_address'] : '',
            '{{client_id}}' => isset($deal['client_id']) ? (string)$deal['client_id'] : (isset($client['id']) ? (string)$client['id'] : '')
        );
    }

    protected function _renderLetterBody($letterRow)
    {
        $description = '';
        if (isset($letterRow['Template']['description']) && !$this->_letterBodyIsBlank($letterRow['Template']['description']))
        {
            $description = (string)$letterRow['Template']['description'];
        }
        elseif (isset($letterRow['Realestateletter']['description']))
        {
            $description = (string)$letterRow['Realestateletter']['description'];
        }
        $rendered = strtr($description, $this->_placeholderMap($letterRow));
        $rendered = preg_replace('/\{\{\s*[^}]+\s*\}\}/', 'N/A', $rendered);
        // Ensure applicant/client profile photo displays in full without aggressive cropping
        $rendered = preg_replace_callback('/<img\b([^>]*\b(?:id=["\']client-photo["\']|alt=["\'](?:Applicant|Client)\s+Photo["\'])[^>]*)>/i', function($m) {
            $attrs = $m[1];
            $attrs = preg_replace('/\bobject-cover\b/i', 'object-contain bg-white', $attrs);
            if (stripos($attrs, 'object-fit') === false) {
                if (stripos($attrs, 'style=') !== false) {
                    $attrs = preg_replace('/style=["\']([^"\']*)["\']/i', 'style="$1;object-fit:contain !important;object-position:center !important;"', $attrs);
                } else {
                    $attrs .= ' style="object-fit:contain !important;object-position:center !important;"';
                }
            }
            return '<img' . $attrs . '>';
        }, $rendered);
        // Strip obsolete centered "Booking Form" title between Property Name and ID No if present
        $rendered = preg_replace('/<div[^>]*class=["\'][^"\']*text-center[^"\']*["\'][^>]*style=["\'][^"\']*font-size:\s*17px[^"\']*["\'][^>]*>\s*Booking Form\s*<\/div>\s*/i', '', $rendered);
        // When client and father name are separate, ensure "Name of Applicant:" displays full applicant name with S/O Father Name
        $client = isset($letterRow['Client']) ? (array)$letterRow['Client'] : array();
        $cName = isset($client['name']) ? trim((string)$client['name']) : '';
        $cFather = isset($client['fathername']) ? trim((string)$client['fathername']) : '';
        $cRel = isset($client['relation_type']) ? trim((string)$client['relation_type']) : '';
        $cGen = isset($client['gender']) ? trim((string)$client['gender']) : '';
        $cFull = $this->_formatClientNameWithRelation($cName, $cFather, $cRel, $cGen);

        if ($cFull !== '' && $cFull !== $cName)
        {
            $rendered = preg_replace_callback(
                '/(Name\s+of\s+Applicant\s*:\s*<\/label>\s*<div[^>]*class=["\'][^"\']*input-line[^"\']*["\'][^>]*>)\s*([^<]*?)\s*(<\/div>)/i',
                function($m) use ($cFull, $cName) {
                    $currentVal = trim($m[2]);
                    if ($currentVal === '' || strcasecmp($currentVal, $cName) === 0 || strcasecmp($currentVal, '{{client_name}}') === 0 || strcasecmp($currentVal, '{{client_name_with_father}}') === 0) {
                        return $m[1] . $cFull . $m[3];
                    }
                    return $m[0];
                },
                $rendered
            );
        }

        if ($cFull !== '')
        {
            // Ensure Urdu declaration displays full client name with relation/father name
            $rendered = str_replace('{{client_name_with_father}}', $cFull, $rendered);
            $rendered = preg_replace_callback(
                '/(<div[^>]*class=["\'][^"\']*urdu-declaration[^"\']*["\'][^>]*>.*?میں\s*<span[^>]*>)(.*?)(<\/span>.*?اس فارم کی پشت پر درج)/isu',
                function($m) use ($cFull) {
                    return $m[1] . $cFull . $m[3];
                },
                $rendered
            );
        }
        $letterType = isset($letterRow['Realestateletter']['letter_type']) ? trim((string)$letterRow['Realestateletter']['letter_type']) : '';
        if ($letterType === 'RLT_REFUND' || stripos($rendered, 'DEAL PAYMENT REFUND FORM') !== false)
        {
            if (stripos($rendered, 'Markup') !== false)
            {
                $rendered = preg_replace('/Deduction\s*\(\+?([^)]*Markup[^)]*)\)\s*:/i', 'Markup ($1):', $rendered);
                $rendered = preg_replace('/<span\b([^>]*text-red-600[^>]*)>\s*\(\-\)\s*/i', '<span$1 style="color:#16a34a !important;">(+) ', $rendered);
                $rendered = preg_replace('/The\s+deduction\s+of\s+(\+?[^<]*Markup[^<]*)\s+is\s+final/i', 'The markup of $1 is final', $rendered);
            }
        }
        $isWelcomeLetter = ($letterType === 'RLT_WELCOME' || stripos($rendered, 'WELCOME LETTER') !== false);
        if ($isWelcomeLetter)
        {
            if (strpos($rendered, 'wv-welcome-a4') === false)
            {
                $rendered = preg_replace('/(<div\s+class=["\'][^"\']*form-container)([^"\']*["\'])/i', '$1 wv-welcome-a4$2', $rendered, 1);
            }
            $rendered = preg_replace('/(height|max-height):\s*297mm\s*!important;?/i', '', $rendered);
            $rendered = preg_replace('/width:\s*210mm\s*!important;?/i', '', $rendered);
        }
        // Ensure universal floral border frame is present for all single-page letters
        if (strpos($rendered, 'wv-floral-border-frame') === false && (strpos($rendered, 'form-container') !== false || strpos($rendered, 'wv-welcome') !== false))
        {
            $rendered = preg_replace('/(<div\s+class=["\'][^"\']*form-container[^"\']*["\'][^>]*>)/i', "$1\n" . $this->_floralBorderFrameSvg() . "\n", $rendered, 1);
        }

        // Ensure outer border line is present outside the stars
        if (strpos($rendered, '<div class="wv-outer-border-line"') === false && strpos($rendered, 'form-container') !== false)
        {
            $rendered = preg_replace('/(<div\s+class=["\'][^"\']*form-container[^"\']*["\'][^>]*>)/i', "$1\n        <div class=\"wv-outer-border-line\"></div>", $rendered, 1);
        }

        // Ensure inner border line is present inside the stars
        if (strpos($rendered, '<div class="wv-inner-border-line"') === false && strpos($rendered, 'form-container') !== false)
        {
            if (strpos($rendered, 'wv-floral-border-frame') !== false) {
                $rendered = preg_replace('/(<div\s+class=["\'][^"\']*wv-floral-border-frame[^"\']*["\'][^>]*>.*?<\/div>\s*<\/div>)/is', "$1\n        <div class=\"wv-inner-border-line\"></div>\n", $rendered, 1);
            } else {
                $rendered = preg_replace('/(<div\s+class=["\'][^"\']*form-container[^"\']*["\'][^>]*>)/i', "$1\n        <div class=\"wv-inner-border-line\"></div>\n", $rendered, 1);
            }
        }
        if ((strpos($rendered, '.wv-outer-border-line') === false || strpos($rendered, '.wv-inner-border-line') === false) && strpos($rendered, 'form-container') !== false)
        {
            $borderLinesCss = "\n        .wv-outer-border-line { position: absolute; top: 5px; left: 5px; right: 5px; bottom: 5px; border: 1.5px solid #064e3b; pointer-events: none; z-index: 52; box-sizing: border-box; }\n        .wv-inner-border-line { position: absolute; top: 31px; left: 31px; right: 31px; bottom: 31px; border: 1.5px solid #064e3b; pointer-events: none; z-index: 52; box-sizing: border-box; }\n";
            $rendered = preg_replace('/(<\/style>)/i', $borderLinesCss . '$1', $rendered, 1);
        }

        if ($isBookingOrWelcome)
        {
            $rendered = preg_replace('/padding\s*:\s*(?:30px\s+24px|44px\s+42px\s+38px\s+42px|44px\s+44px\s+38px\s+44px|24px|28px\s+24px)(?:\s*!important)?/i', 'padding: 60px 44px 38px 44px', $rendered);
            $rendered = preg_replace('/(<div[^>]*class=["\']col-span-)4(\s+space-y-3\s+flex\s+flex-col\s+justify-center["\']>)/i', '$13$2', $rendered);
            $rendered = preg_replace('/(<div[^>]*class=["\']col-span-)6(\s+flex\s+justify-center\s+items-stretch\s+p-0\.5\s+pt-0["\']>)/i', '$17$2', $rendered);
        }

        $isBookingOrWelcome = (
            in_array($letterType, array('RLT_BOOKING', 'RLT_WELCOME'), true) ||
            stripos($rendered, '13in') !== false ||
            stripos($rendered, '12.18in') !== false ||
            stripos($rendered, 'form-container') !== false ||
            stripos($rendered, 'wv-welcome') !== false
        );

        if ($letterType === 'RLT_BOOKING' || stripos($rendered, 'BOOKING') !== false)
        {
            $rendered = preg_replace('/padding\s*:\s*(?:30px\s+24px|44px\s+42px\s+38px\s+42px|44px\s+44px\s+38px\s+44px|24px|28px\s+24px)(?:\s*!important)?/i', 'padding: 60px 44px 38px 44px', $rendered);
            $rendered = preg_replace('/(<div[^>]*class=["\']col-span-)4(\s+space-y-3\s+flex\s+flex-col\s+justify-center["\']>)/i', '$13$2', $rendered);
            $rendered = preg_replace('/(<div[^>]*class=["\']col-span-)6(\s+flex\s+justify-center\s+items-stretch\s+p-0\.5\s+pt-0["\']>)/i', '$17$2', $rendered);

            $rendered = preg_replace(
                '/<span\b([^>]*\bid=["\']display-date["\'][^>]*)>(.*?)<\/span>/i',
                '<span class="block text-emerald-950 font-black text-sm mt-0.5 tracking-wide" id="display-date" style="font-size:13px;font-weight:900;color:#064e3b;line-height:1.2;">$2</span>',
                $rendered
            );
            $rendered = preg_replace('/<body\b([^>]*\bclass=["\'][^"\']*)flex justify-center([^"\']*["\'][^>]*)>/i', '<body$1flex flex-col items-center$2>', $rendered);

            // Move microprint security line after signatures if still positioned above barcode
            if (stripos($rendered, 'microprint-security-line') !== false && strpos($rendered, 'border-top:1px dashed rgba(6,78,59,0.25)') === false)
            {
                $microRegex = '/\s*<!-- Anti-Forgery Microprint Security Line[^>]*-->\s*<div class="select-none overflow-hidden h-2\.5 flex items-center justify-center text-center w-full"[^>]*>.*?<\/div>/s';
                if (preg_match($microRegex, $rendered)) {
                    $rendered = preg_replace($microRegex, '', $rendered);
                    $newMicroprintBottom = "\n                <!-- Anti-Forgery Microprint Security Line at the very end after signatures -->\n" .
                    '                <div class="select-none overflow-hidden h-2.5 flex items-center justify-center text-center w-full mt-1 pt-0.5" style="border-top:1px dashed rgba(6,78,59,0.25);">' . "\n" .
                    '                    <span class="microprint-security-line truncate w-full text-[6px] tracking-wider text-emerald-950/70" style="font-size:6.5px;letter-spacing:0.04em;">' . "\n" .
                    '                        * SECURE DOCUMENT * ORIGINAL BOOKING COPY * ID: {{deal_invoice_no}} * VERIFY VIA SYSTEM QR CODE * NOT FOR RE-EXCHANGE * VALID STAMPED COPY * SECURE DOCUMENT * ORIGINAL BOOKING COPY * ID: {{deal_invoice_no}} * VERIFY VIA SYSTEM QR CODE * NOT FOR RE-EXCHANGE * VALID STAMPED COPY *' . "\n" .
                    '                    </span>' . "\n" .
                    '                </div>' . "\n";
                    $footerClose = '</footer>';
                    $posFooter = strrpos($rendered, $footerClose);
                    if ($posFooter !== false) {
                        $rendered = substr_replace($rendered, $newMicroprintBottom . '            ' . $footerClose, $posFooter, strlen($footerClose));
                    }
                }
            }
        }

        if (!$isBookingOrWelcome)
        {
            // Strip out conflicting legacy inline font-size and excessive line-height attributes
            $rendered = preg_replace('/style=(["\'])(.*?)\bfont-size\s*:\s*[\d\.]+(?:px|pt|rem)\s*;?(.*?)\1/i', 'style=$1$2$3$1', $rendered);
            $rendered = preg_replace('/style=(["\'])(.*?)\bline-height\s*:\s*(?:[2-9]|1\.[7-9]|1\.[0-5]|3|4)[\d\.]*(?:px|pt|rem)?\s*;?(.*?)\1/i', 'style=$1$2$3$1', $rendered);
            $rendered = preg_replace('/style=["\']\s*["\']/i', '', $rendered);

            // Strip obsolete duplicate style tags that had hardcoded line-height: 1.85 or other conflicting overrides
            $rendered = preg_replace('/<style\b[^>]*id=["\']wv-urdu-font-definitions["\'][^>]*>.*?<\/style>/is', '', $rendered);

            // Auto-wrap any unwrapped CNICs and currency patterns in Urdu text
            $rendered = preg_replace_callback('/(?<!class=["\']en-inline["\']>)\b(\d{5}-\d{7}-\d)\b/', function($m) {
                return '<span dir="ltr" class="en-inline">' . $m[1] . '</span>';
            }, $rendered);
            $rendered = preg_replace_callback('/(?<!class=["\']en-inline["\']>)\b(Rs\.?\s*[\d,]+(?:\.\d+)?)\b/i', function($m) {
                return '<span dir="ltr" class="en-inline">' . $m[1] . '</span>';
            }, $rendered);
        }

        if ($letterType === 'RLT_DEAL_AGREEMENT')
        {
            $rendered = $this->_prepareDealAgreementPrintHtml($rendered);
        }
        elseif ($letterType === 'RLT_REFUND_CLEARANCE')
        {
            $rendered = $this->_prepareRefundCompletionPrintHtml($rendered);
        }
        else
        {
            $rendered = $this->_compactFullHtmlLetterHeader($rendered);
        }
        return $this->_preserveFullHtmlPreviewPrint($rendered);
    }

    protected function _letterBodyIsBlank($html)
    {
        $text = (string)$html;
        $text = preg_replace('/<br\s*\/?>/i', '', $text);
        $text = str_ireplace(array('&nbsp;', '&#160;'), ' ', $text);
        $text = trim(strip_tags($text));
        return $text === '';
    }

    protected function _letterValueOrNa($value)
    {
        $value = trim((string)$value);
        return ($value !== '') ? $value : 'N/A';
    }

    protected function _letterRomanTextToUrdu($text)
    {
        $text = trim((string)$text);
        if ($text === '')
        {
            return '';
        }
        if (preg_match('/[\x{0600}-\x{06FF}]/u', $text))
        {
            return $text;
        }
        $text = preg_replace('/\bS\s*\/\s*O\b/i', 'ÙˆÙ„Ø¯', $text);
        $text = preg_replace('/\bD\s*\/\s*O\b/i', 'Ø¯Ø®ØªØ±', $text);
        $text = preg_replace('/\bW\s*\/\s*O\b/i', 'Ø²ÙˆØ¬Û', $text);
        $dictionary = $this->_letterRomanUrduDictionary();
        return preg_replace_callback('/[A-Za-z]+/', function($matches) use ($dictionary) {
            $word = (string)$matches[0];
            if (strlen($word) <= 1)
            {
                return $word;
            }
            $key = strtolower($word);
            if (isset($dictionary[$key]))
            {
                return $dictionary[$key];
            }
            return $this->_letterRomanWordToUrdu($word);
        }, $text);
    }

    protected function _resolveRelationPrefix($relationType = '', $gender = '')
    {
        $relLower = strtolower(trim((string)$relationType));
        $genderLower = strtolower(trim((string)$gender));

        if ($relLower === 'husband' || strpos($relLower, 'wife') !== false || strpos($relLower, 'w/o') !== false)
        {
            return 'W/O';
        }
        if ($genderLower === 'female' || $genderLower === 'f' || $relLower === 'daughter' || strpos($relLower, 'd/o') !== false)
        {
            return 'D/O';
        }
        return 'S/O';
    }

    protected function _formatClientNameWithRelation($clientName, $fatherName, $relationType = '', $gender = '')
    {
        $clientName = trim((string)$clientName);
        $fatherName = trim((string)$fatherName);

        if ($clientName === '')
        {
            return $fatherName;
        }

        // If client name already contains an explicit relation (S/O, D/O, W/O, etc.), client and father name are already combined
        if (preg_match('/\b(?:s\s*[\/\.]?\s*o|d\s*[\/\.]?\s*o|w\s*[\/\.]?\s*o|son\s+of|daughter\s+of|wife\s+of|bint\s+e|walad\s+e)\b/i', $clientName))
        {
            return $clientName;
        }

        // If father name is empty, just return the client name
        if ($fatherName === '')
        {
            return $clientName;
        }

        $prefix = $this->_resolveRelationPrefix($relationType, $gender);
        return $clientName . ' ' . $prefix . ' ' . $fatherName;
    }

    protected function _letterRefundClientNameParts($name, $fatherName)
    {
        $name = trim((string)$name);
        $fatherName = trim((string)$fatherName);
        if (preg_match('/^(.*?)\s*(?:S\s*\/\s*O|D\s*\/\s*O|W\s*\/\s*O|SON\s+OF|DAUGHTER\s+OF|WIFE\s+OF)\s+(.+)$/i', $name, $matches))
        {
            $baseName = trim((string)$matches[1]);
            $relationName = trim((string)$matches[2]);
            if ($baseName !== '')
            {
                $name = $baseName;
            }
            if ($fatherName === '' && $relationName !== '')
            {
                $fatherName = $relationName;
            }
        }
        return array('name' => $name, 'fathername' => $fatherName);
    }

    protected function _letterRomanWordToUrdu($word)
    {
        $word = strtolower(trim((string)$word));
        if ($word === '')
        {
            return '';
        }
        $chunks = array(
            'sch' => 'Ø³Ú©', 'sh' => 'Ø´', 'ch' => 'Ú†', 'kh' => 'Ø®', 'gh' => 'Øº',
            'ph' => 'Ù', 'th' => 'ØªÚ¾', 'dh' => 'Ø¯Ú¾', 'bh' => 'Ø¨Ú¾', 'jh' => 'Ø¬Ú¾',
            'zh' => 'Ú˜', 'aa' => 'Ø§', 'ee' => 'ÛŒ', 'oo' => 'Ùˆ', 'ou' => 'Ùˆ',
            'ai' => 'Û’', 'ay' => 'Û’', 'ei' => 'ÛŒ', 'ie' => 'ÛŒ', 'qu' => 'Ù‚',
            'ck' => 'Ú©', 'ng' => 'Ù†Ú¯'
        );
        $chars = array(
            'a' => 'Ø§', 'b' => 'Ø¨', 'c' => 'Ú©', 'd' => 'Ø¯', 'e' => 'ÛŒ',
            'f' => 'Ù', 'g' => 'Ú¯', 'h' => 'Û', 'i' => 'ÛŒ', 'j' => 'Ø¬',
            'k' => 'Ú©', 'l' => 'Ù„', 'm' => 'Ù…', 'n' => 'Ù†', 'o' => 'Ùˆ',
            'p' => 'Ù¾', 'q' => 'Ù‚', 'r' => 'Ø±', 's' => 'Ø³', 't' => 'Øª',
            'u' => 'Ùˆ', 'v' => 'Ùˆ', 'w' => 'Ùˆ', 'x' => 'Ú©Ø³', 'y' => 'ÛŒ',
            'z' => 'Ø²'
        );
        $out = '';
        $len = strlen($word);
        for ($i = 0; $i < $len;)
        {
            $matched = false;
            foreach ($chunks as $roman => $urdu)
            {
                $partLen = strlen($roman);
                if (substr($word, $i, $partLen) === $roman)
                {
                    $out .= $urdu;
                    $i += $partLen;
                    $matched = true;
                    break;
                }
            }
            if ($matched)
            {
                continue;
            }
            $char = substr($word, $i, 1);
            $out .= isset($chars[$char]) ? $chars[$char] : $char;
            $i++;
        }
        return $out;
    }

    protected function _letterRomanUrduDictionary()
    {
        return array(
            'muhammad' => 'محمد', 'mohammad' => 'محمد', 'mohammed' => 'محمد', 'mohd' => 'محمد',
            'ahmad' => 'احمد', 'ahmed' => 'احمد', 'ali' => 'علی', 'hassan' => 'حسن', 'hasan' => 'حسن',
            'hussain' => 'حسین', 'hussein' => 'حسین', 'qadeer' => 'قدیر', 'qadir' => 'قادر',
            'abdul' => 'عبدال', 'rehman' => 'رحمان', 'rahman' => 'رحمان', 'rasheed' => 'رشید', 'rashid' => 'رشید',
            'naseer' => 'نصیر', 'nasir' => 'ناصر', 'naveed' => 'نوید', 'waheed' => 'وحید', 'saeed' => 'سعید',
            'shahid' => 'شاہد', 'shahzad' => 'شہزاد', 'tariq' => 'طارق', 'nadeem' => 'ندیم', 'naeem' => 'نعیم',
            'saleem' => 'سلیم', 'jameel' => 'جمیل', 'shakeel' => 'شکیل', 'khalid' => 'خالد', 'aslam' => 'اسلم',
            'akram' => 'اکرم', 'imran' => 'عمران', 'usman' => 'عثمان', 'umar' => 'عمر', 'umer' => 'عمر',
            'farhan' => 'فرحان', 'adnan' => 'عدنان', 'rizwan' => 'رضوان', 'irfan' => 'عرفان', 'sultan' => 'سلطان',
            'nazir' => 'نذیر', 'nazeer' => 'نذیر', 'ameer' => 'امیر', 'amir' => 'عامر', 'sajjad' => 'سجاد',
            'abid' => 'عابد', 'iftikhar' => 'افتخار', 'zubair' => 'زبیر', 'fatima' => 'فاطمہ', 'sadia' => 'سعدیہ',
            'saira' => 'سائرہ', 'iqra' => 'اقراء', 'ayesha' => 'عائشہ', 'aisha' => 'عائشہ',
            'house' => 'مکان', 'home' => 'مکان', 'flat' => 'فلیٹ', 'plot' => 'پلاٹ', 'street' => 'گلی',
            'st' => 'گلی', 'road' => 'روڈ', 'rd' => 'روڈ', 'block' => 'بلاک', 'sector' => 'سیکٹر',
            'phase' => 'فیز', 'town' => 'ٹاؤن', 'colony' => 'کالونی', 'mohalla' => 'محلہ', 'mohala' => 'محلہ', 'mohlah' => 'محلہ',
            'mohalha' => 'محلہ', 'village' => 'گاؤں', 'tehsil' => 'تحصیل', 'district' => 'ضلع',
            'lahore' => 'لاہور', 'kasur' => 'قصور', 'chunian' => 'چونیاں', 'chuniyan' => 'چونیاں',
            'karachi' => 'کراچی', 'islamabad' => 'اسلام آباد', 'rawalpindi' => 'راولپنڈی', 'multan' => 'ملتان',
            'faisalabad' => 'فیصل آباد', 'gujranwala' => 'گوجرانوالہ', 'sialkot' => 'سیالکوٹ',
            'pakistan' => 'پاکستان', 'canada' => 'کینیڈا'
        );
    }

    protected function _prepareDealAgreementPrintHtml($html)
    {
        $html = (string)$html;
        if (!$this->_isFullHtmlDocument($html))
        {
            return $html;
        }
        if (strpos($html, 'wv-deal-agreement-print-fix') !== false)
        {
            return $html;
        }
        $fontStack = '"Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", sans-serif';
        $fontKasheedaStack = '"Jameel Noori Nastaleeq Kasheeda", "Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", serif';
        $fixCss = '<style id="wv-deal-agreement-print-fix">'
            . '@font-face { font-family: "Jameel Noori Nastaleeq"; src: url("/fonts/JameelNooriNastaleeq.ttf") format("truetype"), url("/whitevillas/fonts/JameelNooriNastaleeq.ttf") format("truetype"), url("../fonts/JameelNooriNastaleeq.ttf") format("truetype"); font-weight: normal; font-style: normal; font-display: swap; }'
            . '@font-face { font-family: "Jameel Noori Nastaleeq Kasheeda"; src: url("/fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"), url("/whitevillas/fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"), url("../fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"); font-weight: normal; font-style: normal; font-display: swap; }'
            . '*, *:before, *:after { box-sizing: border-box !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }'
            . '@page { size: Legal portrait; margin: 12mm 14mm !important; }'
            . 'html,body { margin: 0 !important; padding: 0 !important; width: 100% !important; background: #fff !important; color: #000000 !important; direction: rtl !important; unicode-bidi: embed !important; text-align: right !important; font-family: ' . $fontStack . ' !important; font-size: 16px !important; line-height: 1.65 !important; }'
            . '.page { width: 100% !important; max-width: 100% !important; min-height: 0 !important; height: auto !important; max-height: none !important; box-sizing: border-box !important; margin: 0 auto !important; padding: 0 !important; background: #ffffff !important; border: none !important; box-shadow: none !important; position: relative !important; overflow: visible !important; display: flex !important; flex-direction: column !important; justify-content: space-between !important; page-break-after: always !important; page-break-inside: avoid !important; break-after: page !important; break-inside: avoid !important; text-align: right !important; }'
            . '.page:last-of-type { page-break-after: auto !important; break-after: auto !important; }'
            . '.watermark { position: absolute !important; top: 50% !important; left: 50% !important; transform: translate(-50%, -50%) rotate(-30deg) !important; font-size: 58px !important; font-weight: 900 !important; color: rgba(0,0,0,0.035) !important; font-family: Arial, Helvetica, sans-serif !important; pointer-events: none !important; user-select: none !important; z-index: 1 !important; white-space: nowrap !important; border: 6px double rgba(0,0,0,0.035) !important; padding: 4px 20px !important; border-radius: 12px !important; }'
            . '.page > *:not(.watermark) { position: relative !important; z-index: 2 !important; }'
            . '.letterhead { text-align: center !important; border-bottom: 2px solid #000000 !important; padding-bottom: 6px !important; margin-bottom: 8px !important; flex: 0 0 auto !important; }'
            . '.head-row { display: grid !important; grid-template-columns: 60px minmax(0, 1fr) 60px !important; align-items: center !important; gap: 10px !important; }'
            . '.logo-box { width: 56px !important; height: 56px !important; border: 1px solid #000000 !important; padding: 2px !important; display: flex !important; align-items: center !important; justify-content: center !important; overflow: hidden !important; }'
            . '.logo-box img { max-width: 100% !important; max-height: 100% !important; object-fit: contain !important; }'
            . '.agency-name { font-size: 18px !important; font-weight: 900 !important; line-height: 1.25 !important; color: #000000 !important; font-family: Arial, Helvetica, sans-serif !important; text-transform: uppercase !important; }'
            . '.agency-sub { font-size: 13px !important; color: #333333 !important; margin-top: 2px !important; line-height: 1.4 !important; font-family: ' . $fontStack . ' !important; }'
            . '.agency-contact { font-size: 10.5px !important; color: #333333 !important; margin-top: 2px !important; font-family: Arial, Helvetica, sans-serif !important; direction: ltr !important; }'
            . '.document-title { font-family: ' . $fontKasheedaStack . ' !important; text-align: center !important; font-size: 20px !important; font-weight: 900 !important; text-decoration: underline !important; text-underline-offset: 4px !important; margin: 4px 0 2px !important; line-height: 1.65 !important; color: #000000 !important; }'
            . '.document-subtitle { font-family: ' . $fontKasheedaStack . ' !important; text-align: center !important; font-size: 16px !important; font-weight: 700 !important; color: #000000 !important; margin-bottom: 6px !important; line-height: 1.65 !important; }'
            . '.meta-row { display: flex !important; justify-content: space-between !important; align-items: center !important; font-size: 14px !important; font-weight: 800 !important; color: #000000 !important; margin-bottom: 6px !important; background: #ffffff !important; padding: 4px 8px !important; border: 1px solid #000000 !important; border-radius: 4px !important; line-height: 1.65 !important; }'
            . '.page-body { flex: 1 1 auto !important; display: flex !important; flex-direction: column !important; justify-content: flex-start !important; overflow: visible !important; }'
            . 'p { margin: 0 0 6px !important; text-align: justify !important; text-justify: inter-word !important; line-height: 1.65 !important; font-size: 16px !important; font-family: ' . $fontStack . ' !important; }'
            . '.section-title { font-family: ' . $fontKasheedaStack . ' !important; font-size: 16px !important; font-weight: 900 !important; text-decoration: underline !important; text-underline-offset: 3px !important; margin: 8px 0 4px !important; line-height: 1.65 !important; color: #000000 !important; page-break-after: avoid !important; break-after: avoid !important; }'
            . '.clause, .numbered-clause { font-size: 16px !important; line-height: 1.65 !important; margin-bottom: 5px !important; text-align: justify !important; text-justify: inter-word !important; font-family: ' . $fontStack . ' !important; page-break-inside: avoid !important; break-inside: avoid !important; }'
            . '.field { border-bottom: 1px solid #000000 !important; padding: 0 4px !important; font-weight: 900 !important; color: #000000 !important; display: inline !important; }'
            . '.amount-box { border: 1.5px solid #000000 !important; background: #ffffff !important; padding: 6px 12px !important; margin: 8px 0 !important; text-align: center !important; font-size: 16px !important; font-weight: 900 !important; line-height: 1.65 !important; border-radius: 4px !important; page-break-inside: avoid !important; break-inside: avoid !important; }'
            . '.note-box { border: 1px solid #000000 !important; border-right: 4px solid #000000 !important; background: #ffffff !important; padding: 6px 10px !important; margin: 6px 0 !important; font-size: 16px !important; line-height: 1.65 !important; border-radius: 0 4px 4px 0 !important; page-break-inside: avoid !important; break-inside: avoid !important; text-align: justify !important; text-justify: inter-word !important; }'
            . '.en-inline { display: inline-block !important; direction: ltr !important; unicode-bidi: embed !important; font-family: Arial, Helvetica, sans-serif !important; font-size: 13.5px !important; line-height: 1 !important; border-bottom: 1px solid #000000 !important; padding: 0 4px 1px 4px !important; margin: 0 2px !important; text-align: center !important; vertical-align: baseline !important; font-weight: bold !important; color: #000000 !important; }'
            . '.field .en-inline { border-bottom: none !important; }'
            . '.sign-area, .witness-area { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 12px !important; margin-top: 12px !important; page-break-inside: avoid !important; break-inside: avoid !important; }'
            . '.sign-box { border: 1px solid #000000 !important; background: #ffffff !important; border-radius: 4px !important; padding: 8px 10px !important; min-height: 90px !important; line-height: 1.4 !important; font-size: 14px !important; page-break-inside: avoid !important; break-inside: avoid !important; }'
            . '.sign-title { font-family: ' . $fontKasheedaStack . ' !important; font-weight: 900 !important; color: #000000 !important; border-bottom: 1px solid #000000 !important; padding-bottom: 3px !important; margin-bottom: 5px !important; font-size: 15px !important; line-height: 1.4 !important; }'
            . '.sign-line { border-bottom: 1px solid #000000 !important; display: block !important; min-height: 20px !important; margin-top: 3px !important; margin-bottom: 3px !important; }'
            . '.thumb-box { height: 32px !important; border: 1px dashed #000000 !important; background: #ffffff !important; border-radius: 3px !important; margin-top: 4px !important; display: flex !important; align-items: center !important; justify-content: center !important; font-size: 12px !important; color: #333333 !important; }'
            . '.footer { border-top: 1.5px solid #000000 !important; padding-top: 4px !important; text-align: center !important; font-size: 11px !important; line-height: 1.25 !important; color: #000000 !important; font-family: Arial, Helvetica, sans-serif !important; margin-top: auto !important; flex: 0 0 auto !important; }'
            . '@media print { html, body { text-align: right !important; background: #fff !important; } .page { margin: 0 auto !important; border: none !important; box-shadow: none !important; text-align: right !important; overflow: visible !important; width: 100% !important; max-width: 100% !important; min-height: 0 !important; height: auto !important; max-height: none !important; } }'
            . '</style>';
        if (stripos($html, '</head>') !== false)
        {
            return str_ireplace('</head>', $fixCss.'</head>', $html);
        }
        return $fixCss.$html;
    }

    protected function _prepareRefundCompletionPrintHtml($html)
    {
        $html = (string)$html;
        if (!$this->_isFullHtmlDocument($html))
        {
            return $html;
        }
        if (strpos($html, 'wv-refund-clearance-print-fix') !== false)
        {
            return $html;
        }
        $fontStack = '"Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", sans-serif';
        $fontKasheedaStack = '"Jameel Noori Nastaleeq Kasheeda", "Jameel Noori Nastaleeq", "Noto Nastaliq Urdu", serif';
        $fixCss = '<style id="wv-refund-clearance-print-fix">'
            . '@font-face { font-family: "Jameel Noori Nastaleeq"; src: url("/fonts/JameelNooriNastaleeq.ttf") format("truetype"), url("/whitevillas/fonts/JameelNooriNastaleeq.ttf") format("truetype"), url("../fonts/JameelNooriNastaleeq.ttf") format("truetype"); font-weight: normal; font-style: normal; font-display: swap; }'
            . '@font-face { font-family: "Jameel Noori Nastaleeq Kasheeda"; src: url("/fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"), url("/whitevillas/fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"), url("../fonts/JameelNooriNastaleeqKasheeda.ttf") format("truetype"); font-weight: normal; font-style: normal; font-display: swap; }'
            . '*, *:before, *:after { box-sizing: border-box !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }'
            . '@page { size: Legal portrait; margin: 12mm 14mm !important; }'
            . 'html,body{background:#fff!important;color:#000!important;font-family:' . $fontStack . '!important;font-size:16px!important;line-height:1.65!important;direction:rtl!important;}'
            . '.legal-page-canvas{width:100%!important;max-width:100%!important;min-height:0!important;height:auto!important;overflow:visible!important;background:#fff!important;color:#000!important;box-sizing:border-box!important;padding:0!important;}'
            . '.stamp-top-gap{height:3.65in!important;min-height:0!important;flex:0 0 auto!important;}'
            . '.legal-frame{border-right:3px double #000000!important;border-left:1px solid #cccccc!important;padding-right:1.45rem!important;padding-left:.85rem!important;}'
            . '.urdu-legal,.legal-frame,.signature-block{font-family:' . $fontStack . '!important;letter-spacing:0!important;}'
            . '.urdu-legal,#urdu-paragraphs p{font-size:16px!important;line-height:1.65!important;word-spacing:0!important;text-align:justify!important;text-justify:inter-word!important;margin-top:6px!important;margin-bottom:0!important;}'
            . '.en-inline{display:inline-block!important;direction:ltr!important;unicode-bidi:embed!important;font-family:Arial,Helvetica,sans-serif!important;font-size:13.5px!important;line-height:1!important;border-bottom:1px solid #000000!important;padding:0 4px 1px 4px!important;margin:0 2px!important;text-align:center!important;vertical-align:baseline!important;font-weight:bold!important;color:#000000!important;}'
            . '.signature-block{display:block!important;margin-top:14px!important;padding-top:10px!important;border-top:1px solid #000000!important;page-break-inside:avoid!important;break-inside:avoid!important;color:#000000!important;}'
            . '.signature-block .grid{display:grid!important;}'
            . '.signature-block .grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))!important;}'
            . '.signature-block .grid-cols-3{grid-template-columns:repeat(3,minmax(0,1fr))!important;}'
            . '.signature-block .gap-8{gap:1.15rem!important;}'
            . '.signature-block .gap-2{gap:.35rem!important;}'
            . '.signature-block .text-center{text-align:center!important;}'
            . '.signature-block .h-10{height:34px!important;}'
            . '.signature-block .h-8{height:27px!important;}'
            . '.signature-block .border-b{border-bottom:1px dashed #000000!important;}'
            . '.signature-block .border-t{border-top:1px solid #000000!important;}'
            . '.signature-block .w-3\/4{width:75%!important;}'
            . '.signature-block .w-11\/12{width:91.666667%!important;}'
            . '.signature-block .mx-auto{margin-left:auto!important;margin-right:auto!important;}'
            . '.signature-block .flex{display:flex!important;}'
            . '.signature-block .flex-col{flex-direction:column!important;}'
            . '.signature-block .items-center{align-items:center!important;}'
            . '.signature-block .justify-center{justify-content:center!important;}'
            . '.signature-block .justify-end{justify-content:flex-end!important;}'
            . '.signature-block p{margin:2px 0!important;line-height:1.4!important;font-size:14px!important;}'
            . '.qr-container-fixed{width:1.55in!important;height:1.55in!important;display:flex!important;align-items:center!important;justify-content:center!important;background:#fff!important;padding:.06in!important;box-sizing:border-box!important;border:1px solid #000000!important;}'
            . '#ashtam-qrcode{width:100%!important;height:100%!important;display:flex!important;align-items:center!important;justify-content:center!important;}'
            . '#ashtam-qrcode img{display:block!important;width:100%!important;height:100%!important;max-width:100%!important;max-height:100%!important;object-fit:contain!important;}'
            . '@media print{@page{size:Legal portrait;margin:12mm 14mm!important;}'
            . 'html,body{width:auto!important;min-height:0!important;margin:0!important;padding:0!important;background:#fff!important;color:#000!important;}'
            . '.no-print{display:none!important;}'
            . '.print-container,main{width:100%!important;max-width:100%!important;margin:0!important;padding:0!important;background:#fff!important;box-shadow:none!important;overflow:visible!important;}'
            . '.legal-page-canvas{width:100%!important;min-height:0!important;height:auto!important;margin:0!important;padding:0!important;box-shadow:none!important;border:0!important;border-radius:0!important;}'
            . '.stamp-top-gap{height:3.65in!important;}'
            . '.signature-block{page-break-inside:avoid!important;break-inside:avoid!important;}'
            . '*{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;color-adjust:exact!important;}}'
            . '</style>';
        if (stripos($html, '</head>') !== false)
        {
            return str_ireplace('</head>', $fixCss.'</head>', $html);
        }
        return $fixCss.$html;
    }

    protected function _preserveFullHtmlPreviewPrint($html)
    {
        $html = (string)$html;
        if (!$this->_isFullHtmlDocument($html))
        {
            return $html;
        }
        if (strpos($html, 'wv-letter-preview-print-match') !== false)
        {
            return $html;
        }
        $fixCss = '<style id="wv-letter-preview-print-match">'
            . '@media print{'
            . '*{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important;}'
            . '.page{border:1px solid #e2e8f0!important;border-radius:4px!important;background:#fff!important;box-shadow:none!important;}'
            . '.container{width:198mm!important;max-width:100%!important;margin:0 auto!important;padding:15px 18px!important;box-sizing:border-box!important;border:1px solid #e5e5e5!important;border-top:6px solid #8c1d1d!important;background:#fff!important;box-shadow:none!important;}'
            . '.ashtam-page{border:1px solid #111!important;background:#fff!important;box-shadow:none!important;}'
            . '.form-container{box-shadow:none!important;}'
            . '}'
            . '</style>';
        if (stripos($html, '</head>') !== false)
        {
            return str_ireplace('</head>', $fixCss.'</head>', $html);
        }
        return $fixCss.$html;
    }

    protected function _letterPdfFileName($letterRow)
    {
        $client = isset($letterRow['Client']) ? (array)$letterRow['Client'] : array();
        $property = isset($letterRow['Property']) ? (array)$letterRow['Property'] : array();
        $letter = isset($letterRow['Realestateletter']) ? (array)$letterRow['Realestateletter'] : array();
        $parts = array(
            isset($client['name']) ? (string)$client['name'] : '',
            isset($property['name']) ? (string)$property['name'] : ''
        );
        $base = trim(implode(' - ', array_filter(array_map('trim', $parts))));
        if ($base === '' && !empty($letter['reference_no']))
        {
            $base = (string)$letter['reference_no'];
        }
        if ($base === '')
        {
            $base = 'Real Estate Letter';
        }
        $base = preg_replace('/[^\pL\pN]+/u', '-', $base);
        $base = trim((string)$base, '-');
        if ($base === '')
        {
            $base = 'Real-Estate-Letter';
        }
        return $base . '.pdf';
    }

    protected function _letterPdfPageFormat($renderedDescription)
    {
        if (isset($_GET['paper']) && strtolower(trim($_GET['paper'])) === 'a4')
        {
            return 'a4';
        }
        $html = strtolower((string)$renderedDescription);
        if (strpos($html, '13in') !== false || strpos($html, '8.5in 13in') !== false || strpos($html, '12.18in') !== false || strpos($html, '8.5in 12.18in') !== false || strpos($html, 'form-container') !== false || strpos($html, 'wv-welcome') !== false)
        {
            return array(8.5, 13);
        }
        if (strpos($html, 'paper-a4') !== false)
        {
            return 'a4';
        }
        return 'legal';
    }

    protected function _floralBorderFrameSvg()
    {
        return '<div class="wv-outer-border-line" style="position:absolute;top:5px;left:5px;right:5px;bottom:5px;border:1.5px solid #064e3b;pointer-events:none;z-index:52;box-sizing:border-box;"></div>
        <div class="wv-floral-border-frame" style="position:absolute;top:6px;left:6px;right:6px;bottom:6px;pointer-events:none;z-index:50;box-sizing:border-box;">
            <div style="position:absolute;top:0;left:0;width:24px;height:24px;background-image:url(\'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2724%27%20height%3D%2724%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cdefs%3E%3Cg%20id%3D%27flower%27%3E%3Ccircle%20cx%3D%2712%27%20cy%3D%2712%27%20r%3D%273.5%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C9%2010%2C4%2012%2C4%20C14%2C4%2014%2C9%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C15%2010%2C20%2012%2C20%20C14%2C20%2014%2C15%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C9%2C10%204%2C10%204%2C12%20C4%2C14%209%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C15%2C10%2020%2C10%2020%2C12%20C20%2C14%2015%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C11%206.5%2C7.5%207.5%2C6.5%20C8.5%2C5.5%2011%2C10%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C10%2014.5%2C6.5%2015.5%2C7.5%20C16.5%2C8.5%2012%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C14%207.5%2C15.5%206.5%2C14.5%20C5.5%2C13.5%2010%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C13%2C14%2016.5%2C14.5%2015.5%2C15.5%20C14.5%2C16.5%2011%2C13%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3C%2Fg%3E%3C%2Fdefs%3E%3Cpath%20d%3D%27M24%2C2%20L2%2C2%20L2%2C24%27%20fill%3D%27none%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cpath%20d%3D%27M24%2C22%20L22%2C22%20L22%2C24%27%20fill%3D%27none%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cuse%20href%3D%27%23flower%27%20x%3D%270%27%20y%3D%270%27%2F%3E%3C%2Fsvg%3E\');background-repeat:no-repeat;background-size:24px 24px;"></div>
            <div style="position:absolute;top:0;left:24px;right:24px;height:24px;background-image:url(\'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2724%27%20height%3D%2724%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cdefs%3E%3Cg%20id%3D%27flower%27%3E%3Ccircle%20cx%3D%2712%27%20cy%3D%2712%27%20r%3D%273.5%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C9%2010%2C4%2012%2C4%20C14%2C4%2014%2C9%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C15%2010%2C20%2012%2C20%20C14%2C20%2014%2C15%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C9%2C10%204%2C10%204%2C12%20C4%2C14%209%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C15%2C10%2020%2C10%2020%2C12%20C20%2C14%2015%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C11%206.5%2C7.5%207.5%2C6.5%20C8.5%2C5.5%2011%2C10%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C10%2014.5%2C6.5%2015.5%2C7.5%20C16.5%2C8.5%2012%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C14%207.5%2C15.5%206.5%2C14.5%20C5.5%2C13.5%2010%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C13%2C14%2016.5%2C14.5%2015.5%2C15.5%20C14.5%2C16.5%2011%2C13%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3C%2Fg%3E%3C%2Fdefs%3E%3Cline%20x1%3D%270%27%20y1%3D%272%27%20x2%3D%2724%27%20y2%3D%272%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cline%20x1%3D%270%27%20y1%3D%2722%27%20x2%3D%2724%27%20y2%3D%2722%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cuse%20href%3D%27%23flower%27%20x%3D%270%27%20y%3D%270%27%2F%3E%3C%2Fsvg%3E\');background-repeat:repeat-x;background-size:24px 24px;"></div>
            <div style="position:absolute;top:0;right:0;width:24px;height:24px;background-image:url(\'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2724%27%20height%3D%2724%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cdefs%3E%3Cg%20id%3D%27flower%27%3E%3Ccircle%20cx%3D%2712%27%20cy%3D%2712%27%20r%3D%273.5%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C9%2010%2C4%2012%2C4%20C14%2C4%2014%2C9%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C15%2010%2C20%2012%2C20%20C14%2C20%2014%2C15%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C9%2C10%204%2C10%204%2C12%20C4%2C14%209%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C15%2C10%2020%2C10%2020%2C12%20C20%2C14%2015%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C11%206.5%2C7.5%207.5%2C6.5%20C8.5%2C5.5%2011%2C10%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C10%2014.5%2C6.5%2015.5%2C7.5%20C16.5%2C8.5%2012%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C14%207.5%2C15.5%206.5%2C14.5%20C5.5%2C13.5%2010%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C13%2C14%2016.5%2C14.5%2015.5%2C15.5%20C14.5%2C16.5%2011%2C13%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3C%2Fg%3E%3C%2Fdefs%3E%3Cpath%20d%3D%27M0%2C2%20L22%2C2%20L22%2C24%27%20fill%3D%27none%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cpath%20d%3D%27M0%2C22%20L2%2C22%20L2%2C24%27%20fill%3D%27none%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cuse%20href%3D%27%23flower%27%20x%3D%270%27%20y%3D%270%27%2F%3E%3C%2Fsvg%3E\');background-repeat:no-repeat;background-size:24px 24px;"></div>
            <div style="position:absolute;top:24px;left:0;bottom:24px;width:24px;background-image:url(\'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2724%27%20height%3D%2724%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cdefs%3E%3Cg%20id%3D%27flower%27%3E%3Ccircle%20cx%3D%2712%27%20cy%3D%2712%27%20r%3D%273.5%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C9%2010%2C4%2012%2C4%20C14%2C4%2014%2C9%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C15%2010%2C20%2012%2C20%20C14%2C20%2014%2C15%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C9%2C10%204%2C10%204%2C12%20C4%2C14%209%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C15%2C10%2020%2C10%2020%2C12%20C20%2C14%2015%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C11%206.5%2C7.5%207.5%2C6.5%20C8.5%2C5.5%2011%2C10%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C10%2014.5%2C6.5%2015.5%2C7.5%20C16.5%2C8.5%2012%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C14%207.5%2C15.5%206.5%2C14.5%20C5.5%2C13.5%2010%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C13%2C14%2016.5%2C14.5%2015.5%2C15.5%20C14.5%2C16.5%2011%2C13%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3C%2Fg%3E%3C%2Fdefs%3E%3Cline%20x1%3D%272%27%20y1%3D%270%27%20x2%3D%272%27%20y2%3D%2724%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cline%20x1%3D%2722%27%20y1%3D%270%27%20x2%3D%2722%27%20y2%3D%2724%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cuse%20href%3D%27%23flower%27%20x%3D%270%27%20y%3D%270%27%2F%3E%3C%2Fsvg%3E\');background-repeat:repeat-y;background-size:24px 24px;"></div>
            <div style="position:absolute;top:24px;right:0;bottom:24px;width:24px;background-image:url(\'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2724%27%20height%3D%2724%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cdefs%3E%3Cg%20id%3D%27flower%27%3E%3Ccircle%20cx%3D%2712%27%20cy%3D%2712%27%20r%3D%273.5%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C9%2010%2C4%2012%2C4%20C14%2C4%2014%2C9%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C15%2010%2C20%2012%2C20%20C14%2C20%2014%2C15%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C9%2C10%204%2C10%204%2C12%20C4%2C14%209%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C15%2C10%2020%2C10%2020%2C12%20C20%2C14%2015%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C11%206.5%2C7.5%207.5%2C6.5%20C8.5%2C5.5%2011%2C10%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C10%2014.5%2C6.5%2015.5%2C7.5%20C16.5%2C8.5%2012%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C14%207.5%2C15.5%206.5%2C14.5%20C5.5%2C13.5%2010%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C13%2C14%2016.5%2C14.5%2015.5%2C15.5%20C14.5%2C16.5%2011%2C13%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3C%2Fg%3E%3C%2Fdefs%3E%3Cline%20x1%3D%272%27%20y1%3D%270%27%20x2%3D%272%27%20y2%3D%2724%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cline%20x1%3D%2722%27%20y1%3D%270%27%20x2%3D%2722%27%20y2%3D%2724%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cuse%20href%3D%27%23flower%27%20x%3D%270%27%20y%3D%270%27%2F%3E%3C%2Fsvg%3E\');background-repeat:repeat-y;background-size:24px 24px;"></div>
            <div style="position:absolute;bottom:0;left:0;width:24px;height:24px;background-image:url(\'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2724%27%20height%3D%2724%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cdefs%3E%3Cg%20id%3D%27flower%27%3E%3Ccircle%20cx%3D%2712%27%20cy%3D%2712%27%20r%3D%273.5%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C9%2010%2C4%2012%2C4%20C14%2C4%2014%2C9%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C15%2010%2C20%2012%2C20%20C14%2C20%2014%2C15%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C9%2C10%204%2C10%204%2C12%20C4%2C14%209%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C15%2C10%2020%2C10%2020%2C12%20C20%2C14%2015%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C11%206.5%2C7.5%207.5%2C6.5%20C8.5%2C5.5%2011%2C10%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C10%2014.5%2C6.5%2015.5%2C7.5%20C16.5%2C8.5%2012%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C14%207.5%2C15.5%206.5%2C14.5%20C5.5%2C13.5%2010%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C13%2C14%2016.5%2C14.5%2015.5%2C15.5%20C14.5%2C16.5%2011%2C13%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3C%2Fg%3E%3C%2Fdefs%3E%3Cpath%20d%3D%27M2%2C0%20L2%2C22%20L24%2C22%27%20fill%3D%27none%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cpath%20d%3D%27M22%2C0%20L22%2C2%20L24%2C2%27%20fill%3D%27none%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cuse%20href%3D%27%23flower%27%20x%3D%270%27%20y%3D%270%27%2F%3E%3C%2Fsvg%3E\');background-repeat:no-repeat;background-size:24px 24px;"></div>
            <div style="position:absolute;bottom:0;left:24px;right:24px;height:24px;background-image:url(\'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2724%27%20height%3D%2724%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cdefs%3E%3Cg%20id%3D%27flower%27%3E%3Ccircle%20cx%3D%2712%27%20cy%3D%2712%27%20r%3D%273.5%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C9%2010%2C4%2012%2C4%20C14%2C4%2014%2C9%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C15%2010%2C20%2012%2C20%20C14%2C20%2014%2C15%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C9%2C10%204%2C10%204%2C12%20C4%2C14%209%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C15%2C10%2020%2C10%2020%2C12%20C20%2C14%2015%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C11%206.5%2C7.5%207.5%2C6.5%20C8.5%2C5.5%2011%2C10%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C10%2014.5%2C6.5%2015.5%2C7.5%20C16.5%2C8.5%2012%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C14%207.5%2C15.5%206.5%2C14.5%20C5.5%2C13.5%2010%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C13%2C14%2016.5%2C14.5%2015.5%2C15.5%20C14.5%2C16.5%2011%2C13%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3C%2Fg%3E%3C%2Fdefs%3E%3Cline%20x1%3D%270%27%20y1%3D%272%27%20x2%3D%2724%27%20y2%3D%272%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cline%20x1%3D%270%27%20y1%3D%2722%27%20x2%3D%2724%27%20y2%3D%2722%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cuse%20href%3D%27%23flower%27%20x%3D%270%27%20y%3D%270%27%2F%3E%3C%2Fsvg%3E\');background-repeat:repeat-x;background-size:24px 24px;"></div>
            <div style="position:absolute;bottom:0;right:0;width:24px;height:24px;background-image:url(\'data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%27http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%27%20width%3D%2724%27%20height%3D%2724%27%20viewBox%3D%270%200%2024%2024%27%3E%3Cdefs%3E%3Cg%20id%3D%27flower%27%3E%3Ccircle%20cx%3D%2712%27%20cy%3D%2712%27%20r%3D%273.5%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C9%2010%2C4%2012%2C4%20C14%2C4%2014%2C9%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C15%2010%2C20%2012%2C20%20C14%2C20%2014%2C15%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C9%2C10%204%2C10%204%2C12%20C4%2C14%209%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C15%2C10%2020%2C10%2020%2C12%20C20%2C14%2015%2C14%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C10%2C11%206.5%2C7.5%207.5%2C6.5%20C8.5%2C5.5%2011%2C10%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C10%2014.5%2C6.5%2015.5%2C7.5%20C16.5%2C8.5%2012%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C11%2C14%207.5%2C15.5%206.5%2C14.5%20C5.5%2C13.5%2010%2C11%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3Cpath%20d%3D%27M12%2C12%20C13%2C14%2016.5%2C14.5%2015.5%2C15.5%20C14.5%2C16.5%2011%2C13%2012%2C12%20Z%27%20fill%3D%27%2523064e3b%27%2F%3E%3C%2Fg%3E%3C%2Fdefs%3E%3Cpath%20d%3D%27M0%2C22%20L22%2C22%20L22%2C0%27%20fill%3D%27none%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cpath%20d%3D%27M0%2C2%20L2%2C2%20L2%2C0%27%20fill%3D%27none%27%20stroke%3D%27%2523064e3b%27%20stroke-width%3D%271.5%27%2F%3E%3Cuse%20href%3D%27%23flower%27%20x%3D%270%27%20y%3D%270%27%2F%3E%3C%2Fsvg%3E\');background-repeat:no-repeat;background-size:24px 24px;"></div>
        </div>
        <div class="wv-inner-border-line" style="position:absolute;top:31px;left:31px;right:31px;bottom:31px;border:1.5px solid #064e3b;pointer-events:none;z-index:52;box-sizing:border-box;"></div>';
    }

    protected function _compactFullHtmlLetterHeader($html)
    {
        $html = (string)$html;
        if (!$this->_isFullHtmlDocument($html))
        {
            return $html;
        }
        $headerTitleHtml = '<div class="wv-letter-header-title" style="margin-top:8px;text-align:center;">'
            . '<p style="margin:0 auto;padding:0 18px 4px 18px;border-bottom:3px solid #111827;display:inline-block;color:#111827;font-size:17px;font-weight:900;line-height:1.15;text-transform:uppercase;">DEAL PAYMENT REFUND FORM</p>'
            . '</div>';
        $titlePattern = '#\s*<!--\s*TITLE\s*&\s*TYPE\s*-->\s*<div\s+class="flex flex-col items-center mb-6">\s*<h2[^>]*>\s*REFUND\s+(?:REQUEST\s+)?APPLICATION\s*</h2>\s*<p[^>]*>\s*(?:CANCELLATION\s*(?:&amp;|&)\s*REFUND|DEAL\s+PAYMENT\s+REFUND)\s+FORM\s*</p>\s*</div>#i';
        $titleWasMoved = false;
        $html = preg_replace($titlePattern, '', $html, 1, $titleWasMoved);
        if ($titleWasMoved > 0 && strpos($html, 'wv-letter-header-title') === false)
        {
            $insertPattern = '#(<div\s+class="mt-(?:2|3)\s+text-\[11px\]\s+text-gray-600\s+leading-tight\s+space-y-(?:0\.5|1)"(?:\s+style="[^"]*")?>.*?</div>\s*)(</div>\s*</div>\s*<!--\s*Letter Meta and Applicant Photo\s*-->)#s';
            $html = preg_replace($insertPattern, '$1' . $headerTitleHtml . '$2', $html, 1);
        }
        if ($titleWasMoved > 0 && strpos($html, 'wv-letter-header-title') === false)
        {
            $html = preg_replace('#(<!--\s*Letter Meta and Applicant Photo\s*-->)#', $headerTitleHtml . '$1', $html, 1);
        }
        $html = preg_replace(
            '#<p\s+class="flex\s+items-center">\s*<span\s+class="font-bold\s+w-12">\s*Address:\s*</span>\s*(.*?)\s*</p>#is',
            '<p class="wv-agency-address-line" style="margin:0;text-align:center;font-weight:700;color:#374151;">$1</p>',
            $html,
            1
        );
        $html = preg_replace(
            '#<div\s+class="flex-grow">\s*(<h1)#i',
            '<div class="flex-grow wv-letter-agency-copy" style="text-align:center;min-width:0;">$1',
            $html,
            1
        );
        $html = preg_replace(
            '#<div\s+class="flex\s+items-center\s+space-x-6">\s*(<p\s+class="flex\s+items-center"><span\s+class="font-bold\s+w-12">\s*Phone:)#i',
            '<div class="flex items-center space-x-6 wv-letter-contact-row" style="display:flex;align-items:center;justify-content:center;gap:24px;margin-top:2px;text-align:center;">$1',
            $html,
            1
        );
        $html = preg_replace(
            '#<h2([^>]*)>\s*REFUND\s+REQUEST\s+APPLICATION\s*</h2>#i',
            '<h2$1>REFUND APPLICATION</h2>',
            $html,
            1
        );
        $html = preg_replace('#<h2[^>]*>\s*REFUND\s+APPLICATION\s*</h2>\s*#i', '', $html, 1);
        $html = preg_replace_callback(
            '#(<h2[^>]*>\s*REFUND\s+APPLICATION\s*</h2>)#i',
            function($matches) {
                $title = $matches[1];
                $title = preg_replace('/font-size\s*:\s*22px/i', 'font-size:18px', $title);
                $title = preg_replace('/padding\s*:\s*0\s+18px\s+4px\s+18px/i', 'padding:0 16px 4px 16px', $title);
                return $title;
            },
            $html,
            1
        );
        $html = preg_replace(
            '#<p([^>]*)>\s*CANCELLATION\s*(?:&amp;|&)\s*REFUND\s+FORM\s*</p>#i',
            '<p$1>DEAL PAYMENT REFUND FORM</p>',
            $html,
            1
        );
        $html = preg_replace_callback(
            '#(<p[^>]*>\s*DEAL\s+PAYMENT\s+REFUND\s+FORM\s*</p>)#i',
            function($matches) {
                $title = $matches[1];
                if (strpos($title, 'border-bottom') === false)
                {
                    $title = preg_replace('/style="([^"]*)"/i', 'style="$1;padding:0 14px 3px 14px;border-bottom:2px solid #111827;display:inline-block;color:#111827;font-size:15px;font-weight:900;line-height:1.15;text-transform:uppercase;"', $title, 1);
                }
                $title = preg_replace('/font-size\s*:\s*11px/i', 'font-size:15px', $title);
                $title = preg_replace('/font-weight\s*:\s*700/i', 'font-weight:900', $title);
                $title = preg_replace('/color\s*:\s*#4b5563/i', 'color:#111827', $title);
                return $title;
            },
            $html,
            1
        );
        if (stripos($html, '</head>') !== false && strpos($html, 'wv-letter-print-header-fix') === false)
        {
            $headerCss = '<style id="wv-letter-print-header-fix">'
                . 'body{padding-top:0!important;}'
                . '.form-container{padding-top:1rem!important;}'
                . '.form-container>header{margin-top:0!important;}'
                . '.form-container>header img{object-fit:contain!important;object-position:center!important;}'
                . '.wv-letter-logo-box{background:transparent!important;border:0!important;box-shadow:none!important;padding:0!important;overflow:visible!important;}'
                . '.wv-letter-logo-box img{max-width:100%!important;max-height:100%!important;}'
                . '.wv-letter-agency-copy{text-align:center!important;min-width:0!important;}'
                . '.wv-agency-address-line{text-align:center!important;margin:0!important;font-weight:700!important;color:#374151!important;}'
                . '.wv-letter-contact-row{justify-content:center!important;text-align:center!important;gap:24px!important;}'
                . '.wv-letter-contact-row p{margin:0!important;}'
                . '@media print{body{padding-top:0!important}.form-container{padding-top:0!important}.form-container>header{margin-top:0!important}}'
                . '</style>';
            $html = str_ireplace('</head>', $headerCss . '</head>', $html);
        }
        $replacements = array(
            'class="mb-6 flex justify-between items-start border-b-2 border-red-600 pb-4"' => 'class="mb-3 flex justify-between items-center border-b-2 border-red-600 pb-2 gap-4" style="margin-bottom:.75rem;display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #dc2626;padding-bottom:.5rem;gap:14px;"',
            'class="mb-3 flex justify-between items-center border-b-2 border-red-600 pb-2 gap-4"' => 'class="mb-3 flex justify-between items-center border-b-2 border-red-600 pb-2 gap-4" style="margin-bottom:.75rem;display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #dc2626;padding-bottom:.5rem;gap:14px;"',
            'class="flex items-start space-x-6"' => 'class="flex items-center space-x-4 flex-1 min-w-0" style="display:grid;grid-template-columns:110px minmax(0,1fr);align-items:center;gap:14px;flex:1 1 auto;min-width:0;"',
            'class="flex items-center space-x-4 flex-1 min-w-0"' => 'class="flex items-center space-x-4 flex-1 min-w-0" style="display:grid;grid-template-columns:110px minmax(0,1fr);align-items:center;gap:14px;flex:1 1 auto;min-width:0;"',
            'class="w-24 h-24 flex-shrink-0 flex items-center justify-center bg-gray-50 border rounded p-1 overflow-hidden"' => 'class="w-24 h-24 flex-shrink-0 flex items-center justify-center overflow-hidden wv-letter-logo-box" style="width:100px;height:100px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:transparent;border:0;padding:0;overflow:visible;"',
            'class="w-20 h-20 flex-shrink-0 flex items-center justify-center bg-white border rounded p-1 overflow-hidden"' => 'class="w-24 h-24 flex-shrink-0 flex items-center justify-center overflow-hidden wv-letter-logo-box" style="width:100px;height:100px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:transparent;border:0;padding:0;overflow:visible;"',
            'class="w-24 h-24 flex-shrink-0 flex items-center justify-center overflow-hidden wv-letter-logo-box"' => 'class="w-24 h-24 flex-shrink-0 flex items-center justify-center overflow-hidden wv-letter-logo-box" style="width:100px;height:100px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:transparent;border:0;padding:0;overflow:visible;"',
            'class="text-3xl font-black text-gray-900 uppercase leading-none"' => 'class="text-2xl font-black text-gray-900 uppercase leading-tight" style="font-size:24px;line-height:1.15;font-weight:900;text-transform:uppercase;color:#111827;margin:0;text-align:center;"',
            'class="text-2xl font-black text-gray-900 uppercase leading-tight"' => 'class="text-2xl font-black text-gray-900 uppercase leading-tight" style="font-size:24px;line-height:1.15;font-weight:900;text-transform:uppercase;color:#111827;margin:0;text-align:center;"',
            'class="mt-3 text-[11px] text-gray-600 leading-tight space-y-1"' => 'class="mt-2 text-[11px] text-gray-600 leading-tight space-y-0.5" style="margin-top:.5rem;font-size:11px;color:#4b5563;line-height:1.25;"',
            'class="mt-2 text-[11px] text-gray-600 leading-tight space-y-0.5"' => 'class="mt-2 text-[11px] text-gray-600 leading-tight space-y-0.5" style="margin-top:.5rem;font-size:11px;color:#4b5563;line-height:1.25;"',
            'class="flex flex-col items-end space-y-4"' => 'class="flex flex-col items-end space-y-2 flex-shrink-0" style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;flex-shrink:0;"',
            'class="flex flex-col items-end space-y-2 flex-shrink-0"' => 'class="flex flex-col items-end space-y-2 flex-shrink-0" style="display:flex;flex-direction:column;align-items:flex-end;gap:.5rem;flex-shrink:0;"',
            'class="w-28 h-32 border-2 border-gray-300 overflow-hidden bg-gray-50 shadow-sm"' => 'class="w-20 h-24 border-2 border-gray-300 overflow-hidden bg-gray-50 shadow-sm" style="width:5rem;height:6rem;border:2px solid #d1d5db;overflow:hidden;background:#f9fafb;box-shadow:0 1px 2px rgba(0,0,0,.05);"',
            'class="w-20 h-24 border-2 border-gray-300 overflow-hidden bg-gray-50 shadow-sm"' => 'class="w-20 h-24 border-2 border-gray-300 overflow-hidden bg-gray-50 shadow-sm" style="width:5rem;height:6rem;border:2px solid #d1d5db;overflow:hidden;background:#f9fafb;box-shadow:0 1px 2px rgba(0,0,0,.05);"'
        );
        return strtr($html, $replacements);
    }

    protected function _isFullHtmlDocument($html)
    {
        $html = trim((string)$html);
        if ($html === '')
        {
            return false;
        }
        $prefix = strtolower(substr($html, 0, 600));
        if (strpos($prefix, '<!doctype html') !== false || strpos($prefix, '<html') !== false)
        {
            return true;
        }
        if (strpos($prefix, '<head') !== false && strpos($prefix, '<body') !== false)
        {
            return true;
        }
        return false;
    }

      protected function _placeholdersGuideByCategory()
      {
          return array(
              'Site / Organization' => array(
                  '{{site_name}}', '{{site_organization}}', '{{site_address}}', '{{site_contact}}', '{{site_email}}', '{{agency_logo_url}}', '{{agency_logo}}', '{{today}}'
              ),
              'Agency / Project' => array(
                  '{{agency_name}}', '{{agency_address}}', '{{agency_phone}}', '{{agency_email}}', '{{agency_logo_url}}', '{{agency_logo}}', '{{project_name}}'
              ),
            'Letter Meta' => array(
                '{{letter_date}}', '{{letter_ref_no}}', '{{letter_title}}', '{{letter_type_label}}'
            ),
            'Client Details' => array(
                '{{client_name}}', '{{client_name_with_father}}', '{{client_father_name}}', '{{applicant_name}}', '{{client_cnic}}', '{{client_phone}}', '{{client_mobile}}', '{{client_email}}', '{{client_address}}', '{{client_occupation}}'
            ),
              'Nominee Details' => array(
                  '{{nominee_name}}', '{{nominee_cnic}}', '{{nominee_mobile}}', '{{nominee_phone}}', '{{nominee_relation}}'
              ),
              'Client Images' => array(
                  '{{client_profile_photo}}', '{{client_id_front_image}}', '{{client_id_back_image}}',
                  '{{client_profile_photo_url}}', '{{client_id_front_image_url}}', '{{client_id_back_image_url}}',
                  '{{applicant_photo}}', '{{applicant_photo_url}}'
              ),
            'Deal Details' => array(
                '{{deal_invoice_no}}', '{{deal_date}}', '{{deal_status}}', '{{occupation}}', '{{deal_occupation}}', '{{deal_total_amount}}', '{{deal_total_amount_words}}', '{{deal_total_amount_words_urdu}}', '{{deal_total_with_charges}}', '{{deal_total_with_charges_words_urdu}}', '{{deal_discount}}', '{{deal_booking_amount}}', '{{deal_balance_amount}}', '{{deal_balance_amount_words_urdu}}',
                '{{deal_booking_receipt_no}}', '{{deal_booking_payment_method}}', '{{deal_booking_payment_remarks}}', '{{deal_booking_payment_date}}', '{{deal_booking_payment_amount}}',
                '{{deal_booking_payment_amount_words}}', '{{deal_booking_payment_amount_words_urdu}}', '{{deal_booking_payment_against}}',
                '{{first_deal_payment_receipt_no}}', '{{first_deal_payment_method}}', '{{first_deal_payment_amount}}', '{{first_deal_payment_amount_words}}',
                '{{first_deal_payment_amount_words_urdu}}', '{{first_deal_payment_against}}', '{{letter_deal_qr_url}}', '{{deal_letter_qr_url}}', '{{letter_deal_qr}}', '{{deal_letter_qr}}',
                '{{letter_verification_url}}', '{{letter_verification_qr_url}}', '{{letter_verification_qr}}'
            ),
              'Overdue / Final Notice' => array(
                  '{{overdue_installments_label}}', '{{overdue_installments_count}}', '{{overdue_amount}}',
                  '{{overdue_amount_words}}', '{{final_notice_deadline_days}}'
              ),
              'Payment Bank' => array(
                  '{{bank_name}}', '{{bank_account_title}}', '{{bank_account_no}}', '{{bank_iban}}', '{{bank_branch}}', '{{bank_swift_code}}',
                  '{{agency_bank_name}}', '{{agency_account_title}}', '{{agency_account_no}}', '{{agency_iban}}'
              ),
              'Template Signatures' => array(
                  '{{ceo_signature}}', '{{ceo_signature_url}}', '{{chairman_signature}}', '{{chairman_signature_url}}'
              ),
              'Deal Charges' => array(
                  '{{deal_charges_total}}', '{{deal_charges_total_words}}', '{{deal_charges_total_words_urdu}}', '{{deal_charges_count}}', '{{deal_charges_types}}', '{{deal_charges_types_urdu}}',
                  '{{deal_charges_table}}', '{{deal_charge_type}}', '{{deal_charge_type_urdu}}', '{{deal_charge_cycle_type}}', '{{deal_charge_cycle_count}}',
                  '{{deal_charge_cycle_amount}}', '{{deal_charge_total_amount}}', '{{deal_charge_total_amount_words_urdu}}', '{{deal_charge_start_date}}',
                  '{{charge_work_details}}', '{{charge_work_status}}', '{{charge_work_status_urdu}}', '{{charge_payment_method}}', '{{charge_payment_deadline}}',
                  '{{agreement_place}}', '{{agency_representative_name}}', '{{agency_representative_designation}}',
                  '{{witness_one_name}}', '{{witness_one_cnic}}', '{{witness_one_contact}}', '{{witness_one_address}}',
                  '{{witness_two_name}}', '{{witness_two_cnic}}', '{{witness_two_contact}}', '{{witness_two_address}}'
              ),
              'Deal Agreement Ashtam' => array(
                  '{{agreement_date_text_urdu}}', '{{agreement_day_name_urdu}}', '{{agreement_place}}',
                  '{{agency_representative_name}}', '{{agency_representative_father}}', '{{agency_representative_cnic}}', '{{agency_representative_designation}}',
                  '{{agreement_payment_plan}}', '{{agreement_penalty_rate}}', '{{agreement_possession_months}}',
                  '{{agreement_extra_work_amount}}', '{{agreement_extra_work_schedule}}', '{{agreement_cancellation_deduction_rate}}',
                  '{{agreement_refund_period}}', '{{agreement_court_jurisdiction}}', '{{agreement_property_block}}',
                  '{{agreement_property_unit_no}}', '{{agreement_property_floor}}', '{{agreement_property_covered_area}}',
                  '{{agreement_property_location}}', '{{property_agreement_category}}', '{{property_agreement_terms}}',
                  '{{witness_one_name}}', '{{witness_one_cnic}}', '{{witness_one_contact}}', '{{witness_one_address}}',
                  '{{witness_two_name}}', '{{witness_two_cnic}}', '{{witness_two_contact}}', '{{witness_two_address}}'
              ),
              'Refund Details' => array(
                  '{{refund_request_date}}', '{{refund_total_paid}}', '{{refund_deduction_percent}}', '{{refund_deduction_amount}}',
                  '{{refund_net_refund}}', '{{refund_cycle_count}}', '{{refund_cycle_frequency}}', '{{refund_cycle_amount}}',
                  '{{refund_account_title}}', '{{refund_account_no}}', '{{refund_bank_name}}', '{{refund_bank_branch}}',
                  '{{refund_reason}}', '{{refund_schedule_table}}', '{{refund_passport_no}}'
              ),
              'Rental Receipt' => array(
                  '{{receipt_no}}', '{{receipt_date}}', '{{print_date}}', '{{rent_month}}', '{{rent_month_label}}',
                  '{{rent_amount}}', '{{rent_amount_words}}', '{{expense_amount}}', '{{net_amount}}',
                  '{{payment_mode}}', '{{payment_reference}}', '{{allocated_months}}', '{{agreement_no}}',
                  '{{tenant_name}}', '{{tenant_cnic}}', '{{tenant_mobile}}', '{{tenant_email}}', '{{tenant_address}}',
                  '{{total_rent_paid}}', '{{outstanding_rent}}', '{{overdue_rent}}', '{{advance_credit}}',
                  '{{receipt_qr}}', '{{receipt_qr_url}}', '{{agency_contact}}'
              ),
              'Rental Agreement' => array(
                  '{{agreement_no}}', '{{tenant_name}}', '{{tenant_cnic}}', '{{tenant_mobile}}',
                  '{{tenant_email}}', '{{tenant_address}}', '{{monthly_rent}}', '{{security_deposit}}',
                  '{{start_date}}', '{{end_date}}', '{{due_day}}'
              ),
              'Property Details' => array(
                  '{{property_name}}', '{{property_file}}', '{{property_area}}', '{{property_unit}}', '{{property_price}}', '{{property_type}}', '{{property_contract}}', '{{project_name}}'
              ),
            'Balloting / Allotment' => array(
                '{{ballot_allotment_no}}', '{{ballot_permanent_type}}', '{{ballot_property_no}}', '{{ballot_street_no}}', '{{ballot_block_name}}',
                '{{ballot_street_address}}', '{{ballot_unit_area}}', '{{ballot_area_unit}}', '{{ballot_area_full}}', '{{ballot_property_type}}',
                '{{ballot_unit_name}}', '{{ballot_category_name}}', '{{ballot_allotment_date}}', '{{allotment_no}}', '{{street_address}}'
            )
        );
    }

    protected function _placeholdersGuide()
    {
        $groups = (array)$this->_placeholdersGuideByCategory();
        $flat = array();
        foreach ($groups as $tokens)
        {
            foreach ((array)$tokens as $token)
            {
                $token = trim((string)$token);
                if ($token !== '' && !in_array($token, $flat, true))
                {
                    $flat[] = $token;
                }
            }
        }
        return $flat;
    }

    protected function _placeholderCategoriesByType($typeCode = null)
    {
        $typeCode = strtoupper(trim((string)$typeCode));
        $common = array('Site / Organization', 'Agency / Project', 'Letter Meta', 'Client Details', 'Property Details', 'Template Signatures');

        $typeMap = array(
            'RLT_WELCOME' => array_merge($common, array('Deal Details', 'Nominee Details', 'Client Images')),
            'RLT_BOOKING' => array_merge($common, array('Deal Details', 'Nominee Details', 'Client Images', 'Balloting / Allotment')),
            'RLT_CHARGE' => array_merge($common, array('Deal Charges', 'Deal Details')),
            'RLT_DEAL_AGREEMENT' => array_merge($common, array('Deal Agreement Ashtam', 'Deal Details', 'Nominee Details')),
            'RLT_REFUND' => array_merge($common, array('Refund Details', 'Deal Details', 'Payment Bank', 'Client Images')),
            'RLT_REFUND_CLEARANCE' => array_merge($common, array('Refund Details', 'Deal Details', 'Payment Bank')),
            'RLT_RENTAL_AGREEMENT' => array('Site / Organization', 'Agency / Project', 'Letter Meta', 'Rental Agreement', 'Template Signatures'),
            'RLT_RENTAL_RECEIPT' => array('Site / Organization', 'Agency / Project', 'Letter Meta', 'Rental Receipt', 'Rental Agreement', 'Template Signatures'),
            'RLT_DEMARCATION' => array_merge($common, array('Balloting / Allotment', 'Deal Details')),
            'RLT_DESIGN_MAP_APPROVAL' => array_merge($common, array('Balloting / Allotment', 'Deal Details')),
            'RLT_NOC' => array_merge($common, array('Balloting / Allotment', 'Deal Details')),
            'RLT_ALLOT' => array_merge($common, array('Balloting / Allotment', 'Deal Details', 'Nominee Details', 'Client Images')),
            'RLT_DEMAND' => array_merge($common, array('Overdue / Final Notice', 'Payment Bank', 'Deal Details')),
            'RLT_RECEIPT' => array_merge($common, array('Deal Details', 'Payment Bank')),
            'RLT_POSSESSION' => array_merge($common, array('Balloting / Allotment', 'Deal Details')),
            'RLT_TRANSFER' => array_merge($common, array('Balloting / Allotment', 'Deal Details', 'Nominee Details', 'Client Images')),
            'RLT_CANCEL' => array_merge($common, array('Overdue / Final Notice', 'Payment Bank', 'Deal Details')),
            'RLT_FINAL_CANCEL' => array_merge($common, array('Overdue / Final Notice', 'Payment Bank', 'Deal Details'))
        );

        if ($typeCode !== '' && isset($typeMap[$typeCode])) {
            return $typeMap[$typeCode];
        }
        return array();
    }

    protected function _setPlaceholderGuideVars($selectedType = null)
    {
        $allCategories = $this->_placeholdersGuideByCategory();
        $this->set('placeholderGuideByCategory', $allCategories);
        $this->set('placeholderGuide', $this->_placeholdersGuide());

        $categoryTypeMap = array();
        foreach (array_keys($this->_letterTypes()) as $t) {
            $categoryTypeMap[$t] = $this->_placeholderCategoriesByType($t);
        }
        $this->set('placeholderCategoryTypeMap', $categoryTypeMap);
        $this->set('selectedLetterTypeForGuide', $selectedType);
    }

    protected function _prepareTemplateDrivenData()
    {
        $this->_ensureDefaultLetterTemplates();
        list($templateOptions, $templateBodyMap, $templateTypeMap) = $this->_templateList();
        $this->set('templateOptions', $templateOptions);
        $this->set('templateBodyMap', $templateBodyMap);
        $this->set('templateTypeMap', $templateTypeMap);
    }

    protected function _extractTypeAndDeal($first = null, $second = null)
    {
        $letterType = '';
        $dealId = 0;
        $firstValue = trim((string)$first);
        $secondValue = trim((string)$second);

        if ($firstValue !== '')
        {
            if (isset($this->_letterTypeArr[$firstValue]))
            {
                $letterType = $firstValue;
            }
            elseif (is_numeric($firstValue))
            {
                $dealId = (int)$firstValue;
            }
        }
        if ($secondValue !== '')
        {
            if ($letterType === '' && isset($this->_letterTypeArr[$secondValue]))
            {
                $letterType = $secondValue;
            }
            elseif ($dealId <= 0 && is_numeric($secondValue))
            {
                $dealId = (int)$secondValue;
            }
        }
        return array($letterType, $dealId);
    }

    protected function _prepareChargeAgreementRequestData($selectedType, $deal = array())
    {
        $selectedType = trim((string)$selectedType);
        if ($selectedType !== 'RLT_CHARGE')
        {
            return;
        }
        if (empty($this->request->data['Realestateletter']) || !is_array($this->request->data['Realestateletter']))
        {
            $this->request->data['Realestateletter'] = array();
        }
        $dealId = isset($deal['Deal']['id']) ? (int)$deal['Deal']['id'] : 0;
        $chargeRows = $this->_activeDealChargeRows($dealId);
        $chargeNames = array();
        foreach ($chargeRows as $chargeRow)
        {
            $chargeName = isset($chargeRow['charge_name']) ? trim((string)$chargeRow['charge_name']) : '';
            if ($chargeName !== '')
            {
                $chargeNames[$chargeName] = $this->_translateDealChargeUrdu($chargeName);
            }
        }
        $defaults = array(
            'charge_work_details' => !empty($chargeNames) ? implode('ØŒ ', array_values($chargeNames)) : '',
            'charge_work_status' => 'In Progress',
            'charge_payment_method' => 'Ø¨Ø§ÛÙ…ÛŒ Ø·Û’ Ø´Ø¯Û Ø·Ø±ÛŒÙ‚Û Ú©Ø§Ø± / Ø¨ÛŒÙ†Ú© Ù¹Ø±Ø§Ù†Ø³ÙØ± / Ù†Ù‚Ø¯ Ø±Ø³ÛŒØ¯',
            'charge_payment_deadline' => 'Ø¨Ø§ÛÙ…ÛŒ Ø·Û’ Ø´Ø¯Û Ø´ÛŒÚˆÙˆÙ„ / Ø¢Ø®Ø±ÛŒ ØªØ§Ø±ÛŒØ® Ú©Û’ Ù…Ø·Ø§Ø¨Ù‚',
            'agreement_place' => '',
            'agency_representative_name' => '',
            'agency_representative_designation' => 'مجاز افسر'
        );
        if (!empty($deal['Project']['address']))
        {
            $defaults['agreement_place'] = (string)$deal['Project']['address'];
        }
        elseif (!empty($deal['Project']['name']))
        {
            $defaults['agreement_place'] = (string)$deal['Project']['name'];
        }
        foreach ($defaults as $field => $value)
        {
            if (!isset($this->request->data['Realestateletter'][$field]) || trim((string)$this->request->data['Realestateletter'][$field]) === '')
            {
                $this->request->data['Realestateletter'][$field] = $value;
            }
        }
    }

    protected function _prepareDealAgreementRequestData($selectedType, $deal = array())
    {
        $selectedType = trim((string)$selectedType);
        if ($selectedType !== 'RLT_DEAL_AGREEMENT')
        {
            return;
        }
        if (empty($this->request->data['Realestateletter']) || !is_array($this->request->data['Realestateletter']))
        {
            $this->request->data['Realestateletter'] = array();
        }
        $dealId = isset($deal['Deal']['id']) ? (int)$deal['Deal']['id'] : 0;
        $ballot = $this->_ballotingSnapshotByDeal($dealId);
        $defaults = array(
            'agreement_place' => '',
            'agency_representative_name' => '',
            'agency_representative_father' => '',
            'agency_representative_cnic' => '',
            'agency_representative_designation' => 'مجاز افسر',
            'agreement_payment_plan' => 'بمطابق منظور شدہ ادائیگی شیڈول',
            'agreement_penalty_rate' => '0.1%',
            'agreement_possession_months' => '36 ماہ',
            'agreement_extra_work_amount' => '0',
            'agreement_extra_work_schedule' => 'بمطابق مٹیریل خریداری مرحلہ',
            'agreement_cancellation_deduction_rate' => '20%',
            'agreement_refund_period' => '90 ایام',
            'agreement_court_jurisdiction' => 'لاہور',
            'agreement_property_block' => $this->_letterValueOrNa(isset($ballot['block_name']) ? (string)$ballot['block_name'] : ''),
            'agreement_property_unit_no' => isset($ballot['property_no']) ? trim((string)$ballot['property_no']) : '',
            'agreement_property_floor' => '',
            'agreement_property_covered_area' => '',
            'agreement_property_location' => isset($ballot['street_address']) ? trim((string)$ballot['street_address']) : ''
        );
        if ($defaults['agreement_property_unit_no'] === '' && !empty($deal['Property']['name']))
        {
            $defaults['agreement_property_unit_no'] = (string)$deal['Property']['name'];
        }
        if (!empty($deal['Unit']['name']))
        {
            $defaults['agreement_property_floor'] = (string)$deal['Unit']['name'];
        }
        elseif (!empty($deal['Type']['name']))
        {
            $defaults['agreement_property_floor'] = (string)$deal['Type']['name'];
        }
        if ($defaults['agreement_property_location'] === '' && !empty($deal['Project']['address']))
        {
            $defaults['agreement_property_location'] = (string)$deal['Project']['address'];
        }
        elseif ($defaults['agreement_property_location'] === '' && !empty($deal['Project']['name']))
        {
            $defaults['agreement_property_location'] = (string)$deal['Project']['name'];
        }
        if (!empty($deal['Project']['address']))
        {
            $defaults['agreement_place'] = (string)$deal['Project']['address'];
        }
        elseif (!empty($deal['Project']['name']))
        {
            $defaults['agreement_place'] = (string)$deal['Project']['name'];
        }
        foreach ($defaults as $field => $value)
        {
            if (!isset($this->request->data['Realestateletter'][$field]) || trim((string)$this->request->data['Realestateletter'][$field]) === '')
            {
                $this->request->data['Realestateletter'][$field] = $value;
            }
        }
    }

    protected function _letterCountsByType($projectId = null)
    {
        $conditions = $this->_lettersDashboardScopeConditions($projectId);
        $rows = $this->Realestateletter->find('all', array(
            'fields' => array('Realestateletter.letter_type', 'COUNT(Realestateletter.id) AS total_count'),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))
            ),
            'conditions' => $conditions,
            'group' => array('Realestateletter.letter_type'),
            'recursive' => -1
        ));
        $counts = array();
        foreach ($this->_letterTypeArr as $typeCode => $label)
        {
            $counts[$typeCode] = 0;
        }
        foreach ((array)$rows as $row)
        {
            $typeCode = isset($row['Realestateletter']['letter_type']) ? trim((string)$row['Realestateletter']['letter_type']) : '';
            if (!isset($counts[$typeCode]))
            {
                continue;
            }
            $counts[$typeCode] = isset($row[0]['total_count']) ? (int)$row[0]['total_count'] : 0;
        }
        return $counts;
    }

    public function dashboard()
    {
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        $counts = $this->_letterCountsByType($projectId);
        $this->loadModel('Template');
        $templateCount = (int)$this->Template->find('count', array(
            'conditions' => array('Template.type' => array_keys($this->_letterTypeArr)),
            'recursive' => -1
        ));
        $conditions = array();
        $conditions = $this->_lettersDashboardScopeConditions($projectId);
        $recentLetters = $this->Realestateletter->find('all', array(
            'fields' => array('Realestateletter.*', 'Deal.invoice_no', 'Client.name', 'Property.name'),
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))
            ),
            'conditions' => $conditions,
            'order' => array('Realestateletter.id' => 'desc'),
            'limit' => 8,
            'recursive' => -1
        ));
        $totalLetters = 0;
        foreach ($counts as $countValue)
        {
            $totalLetters += (int)$countValue;
        }
        $this->set('totalLetters', $totalLetters);
        $this->set('templateCount', $templateCount);
        $this->set('typeCounts', $counts);
        $this->set('recentLetters', $recentLetters);
        $this->set('lettersProjectId', $projectId);
    }

    public function templates($type = null)
    {
        $this->_ensureDefaultLetterTemplates();
        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';
        $selectedType = isset($this->request->query['type']) ? trim((string)$this->request->query['type']) : '';
        if ($selectedType === '' && $type !== null && $type !== 'admin' && !is_numeric($type) && isset($this->_letterTypeArr[trim((string)$type)]))
        {
            $selectedType = trim((string)$type);
        }
        if (!isset($this->_letterTypeArr[$selectedType]))
        {
            $selectedType = '';
        }
        $conditions = array('Template.type' => array_keys($this->_letterTypeArr));
        if ($selectedType !== '')
        {
            $conditions[] = array('Template.type' => $selectedType);
        }
        if ($keyword !== '')
        {
            $conditions[] = array(
                'OR' => array(
                    'Template.name LIKE' => '%'.$keyword.'%',
                    'Template.description LIKE' => '%'.$keyword.'%'
                )
            );
        }
        $this->loadModel('Template');
        $this->Paginator->settings = array(
            'page' => 1,
            'order' => array('Template.id' => 'desc'),
            'conditions' => $conditions,
            'limit' => $this->pageLimit,
            'maxLimit' => $this->maxLimit
        );
        $this->set('Template', $this->Paginator->paginate('Template'));
        $this->set('searchKeyword', $keyword);
        $this->set('selectedType', $selectedType);
        $this->set('letterTypeArr', $this->_letterTypeArr);
    }

    protected function _prepareTemplateSignatureUploadData($existingTemplate = array())
    {
        if (empty($this->request->data['Template']) || !is_array($this->request->data['Template']))
        {
            return;
        }
        $uploadMap = array(
            'ceo_signature_upload' => array('file' => 'ceo_signature', 'dir' => 'ceo_signature_dir', 'label' => __('CEO signature')),
            'chairman_signature_upload' => array('file' => 'chairman_signature', 'dir' => 'chairman_signature_dir', 'label' => __('Chairman signature'))
        );
        foreach($uploadMap as $uploadField => $target)
        {
            $upload = isset($this->request->data['Template'][$uploadField]) ? $this->request->data['Template'][$uploadField] : array();
            unset($this->request->data['Template'][$uploadField]);
            if (empty($upload) || !is_array($upload))
            {
                if (!isset($this->request->data['Template'][$target['file']]) && isset($existingTemplate[$target['file']]))
                {
                    $this->request->data['Template'][$target['file']] = $existingTemplate[$target['file']];
                }
                if (!isset($this->request->data['Template'][$target['dir']]) && isset($existingTemplate[$target['dir']]))
                {
                    $this->request->data['Template'][$target['dir']] = $existingTemplate[$target['dir']];
                }
                continue;
            }
            $error = isset($upload['error']) ? (int)$upload['error'] : UPLOAD_ERR_NO_FILE;
            if ($error === UPLOAD_ERR_NO_FILE)
            {
                if (!isset($this->request->data['Template'][$target['file']]) && isset($existingTemplate[$target['file']]))
                {
                    $this->request->data['Template'][$target['file']] = $existingTemplate[$target['file']];
                }
                if (!isset($this->request->data['Template'][$target['dir']]) && isset($existingTemplate[$target['dir']]))
                {
                    $this->request->data['Template'][$target['dir']] = $existingTemplate[$target['dir']];
                }
                continue;
            }
            if ($error !== UPLOAD_ERR_OK || empty($upload['tmp_name']))
            {
                throw new Exception(__('%s upload failed. Please select the image again.', $target['label']));
            }
            $extension = strtolower(pathinfo(isset($upload['name']) ? (string)$upload['name'] : '', PATHINFO_EXTENSION));
            if (!in_array($extension, array('jpg', 'jpeg', 'png', 'gif', 'webp'), true))
            {
                throw new Exception(__('%s must be JPG, PNG, GIF, or WEBP.', $target['label']));
            }
            $imageInfo = @getimagesize($upload['tmp_name']);
            if (empty($imageInfo))
            {
                throw new Exception(__('%s must be a valid image file.', $target['label']));
            }
            $folder = WWW_ROOT.'img'.DS.'letter_signatures';
            if (!is_dir($folder) && !@mkdir($folder, 0775, true))
            {
                throw new Exception(__('Unable to create signature upload folder.'));
            }
            $fileName = preg_replace('/[^a-z0-9_\-\.]/i', '', $target['file'].'_'.date('YmdHis').'_'.uniqid('', true).'.'.$extension);
            $targetPath = $folder.DS.$fileName;
            if (!@move_uploaded_file($upload['tmp_name'], $targetPath))
            {
                if (!@rename($upload['tmp_name'], $targetPath))
                {
                    throw new Exception(__('%s could not be saved.', $target['label']));
                }
            }
            $this->request->data['Template'][$target['file']] = $fileName;
            $this->request->data['Template'][$target['dir']] = 'letter_signatures';
        }
    }

    protected function _templateSaveFieldList()
    {
        return array(
            'id', 'name', 'description', 'type', 'status', 'is_new',
            'ceo_signature', 'ceo_signature_dir',
            'chairman_signature', 'chairman_signature_dir'
        );
    }

    public function templateadd($type = null)
    {
        $selectedType = trim((string)$type);
        if (!isset($this->_letterTypeArr[$selectedType]))
        {
            $selectedType = '';
        }
        $this->loadModel('Template');
        $this->_setPlaceholderGuideVars($selectedType);
        if (!$this->request->is('post') && $selectedType !== '')
        {
            $this->request->data['Template']['type'] = $selectedType;
            $this->request->data['Template']['status'] = 'Published';
        }
        if ($this->request->is('post'))
        {
            $this->Template->create();
            try
            {
                $postedType = isset($this->request->data['Template']['type']) ? trim((string)$this->request->data['Template']['type']) : '';
                if (!isset($this->_letterTypeArr[$postedType]))
                {
                    $this->Session->setFlash(__('Please select a valid letter template type.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                if (strlen(trim((string)$this->request->data['Template']['status'])) <= 0)
                {
                    $this->request->data['Template']['status'] = 'Published';
                }
                if (strlen(trim((string)$this->request->data['Template']['is_new'])) <= 0)
                {
                    $this->request->data['Template']['is_new'] = 'Yes';
                }
                $this->_prepareTemplateSignatureUploadData();
                if ($this->Template->save($this->request->data, array('fieldList' => $this->_templateSaveFieldList())))
                {
                    $this->Session->setFlash(__('Letter template has been saved.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('controller' => 'Realestateletters', 'action' => 'templates'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
            }
        }
    }

    public function templateedit($id = null)
    {
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->loadModel('Template');
        $post = $this->Template->find('first', array(
            'conditions' => array(
                'Template.id' => (int)$id,
                'Template.type' => array_keys($this->_letterTypeArr)
            ),
            'recursive' => -1
        ));
        if (empty($post['Template']['id']))
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $initialType = isset($post['Template']['type']) ? $post['Template']['type'] : null;
        $this->_setPlaceholderGuideVars($initialType);
        if ($this->request->is(array('post', 'put')))
        {
            $this->Template->id = (int)$id;
            try
            {
                if (empty($this->request->data['Template']) || !is_array($this->request->data['Template']))
                {
                    $this->request->data['Template'] = array();
                }
                $this->request->data['Template']['id'] = (int)$id;
                $postedType = isset($this->request->data['Template']['type']) ? trim((string)$this->request->data['Template']['type']) : '';
                if (!isset($this->_letterTypeArr[$postedType]))
                {
                    $this->Session->setFlash(__('Please select a valid letter template type.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                // Mark as custom so default template seeder never overwrites user customizations
                $this->request->data['Template']['is_new'] = 'Yes';
                $this->_prepareTemplateSignatureUploadData(isset($post['Template']) ? (array)$post['Template'] : array());
                if ($this->Template->save($this->request->data, array('fieldList' => $this->_templateSaveFieldList())))
                {
                    $this->Session->setFlash(__('Letter template has been updated.'), 'flash', array('alert' => 'success'));
                    return $this->redirect(array('controller' => 'Realestateletters', 'action' => 'templates'));
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
            }
        }
        if (!$this->request->data)
        {
            $this->request->data = $post;
        }
    }

    public function templatepreview()
    {
        $this->autoRender = false;
        $this->layout = null;
        $content = isset($this->request->data['description']) ? (string)$this->request->data['description'] : (isset($this->request->data['content']) ? (string)$this->request->data['content'] : '');
        $type = isset($this->request->data['type']) ? trim((string)$this->request->data['type']) : '';
        $dummyRow = array('Template' => array('type' => $type, 'description' => $content));
        $rendered = $this->_renderTemplatePreviewDescription($dummyRow);
        $this->response->type('html');
        $this->response->body($rendered);
    }

    public function templateview($id = null)
    {
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->loadModel('Template');
        $post = $this->Template->find('first', array(
            'conditions' => array(
                'Template.id' => (int)$id,
                'Template.type' => array_keys($this->_letterTypeArr)
            ),
            'recursive' => -1
        ));
        if (empty($post['Template']['id']))
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->set('post', $post);
        $templateDescription = isset($post['Template']['description']) ? (string)$post['Template']['description'] : '';
        $previewDescription = $this->_renderTemplatePreviewDescription($post);
        $this->set('previewDescription', $previewDescription);
        $this->set('isFullHtmlTemplate', $this->_isFullHtmlDocument($templateDescription));
    }

    public function templateprint($id = null)
    {
        $this->layout = null;
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $this->loadModel('Template');
        $post = $this->Template->find('first', array(
            'conditions' => array(
                'Template.id' => (int)$id,
                'Template.type' => array_keys($this->_letterTypeArr)
            ),
            'recursive' => -1
        ));
        if (empty($post['Template']['id']))
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $templateDescription = isset($post['Template']['description']) ? (string)$post['Template']['description'] : '';
        $renderedDescription = $this->_renderTemplatePreviewDescription($post);
        $letterType = isset($post['Template']['type']) ? (string)$post['Template']['type'] : '';
        $this->set('post', $post);
        $this->set('templateDescription', $renderedDescription);
        $this->set('renderedDescription', $renderedDescription);
        $this->set('isFullHtmlTemplate', $this->_isFullHtmlDocument($templateDescription));
        $this->set('letterType', $letterType);
        $this->set('letterTypeLabel', isset($this->_letterTypeArr[$letterType]) ? $this->_letterTypeArr[$letterType] : $letterType);
        $this->set('pdfFileName', 'Template-' . (isset($post['Template']['name']) ? Inflector::slug($post['Template']['name'], '-') : $letterType) . '.pdf');
        $this->set('pdfPageFormat', $this->_letterPdfPageFormat($renderedDescription));
        $this->set('mode', 'print');
    }

    protected function _templatePreviewSvgDataUri($label, $width = 320, $height = 220)
    {
        $label = trim((string)$label);
        if ($label === '')
        {
            $label = 'Preview';
        }
        $width = max(80, (int)$width);
        $height = max(60, (int)$height);
        $safeLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $fontSize = ($width <= 120) ? 12 : 18;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="'.$width.'" height="'.$height.'" viewBox="0 0 '.$width.' '.$height.'">'
            . '<rect width="100%" height="100%" rx="8" fill="#f8fafc"/>'
            . '<rect x="1" y="1" width="'.($width - 2).'" height="'.($height - 2).'" rx="8" fill="none" stroke="#cbd5e1" stroke-width="2"/>'
            . '<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" fill="#334155" font-family="Arial, Helvetica, sans-serif" font-size="'.$fontSize.'" font-weight="700">'.$safeLabel.'</text>'
            . '</svg>';
        return 'data:image/svg+xml;charset=UTF-8,'.rawurlencode($svg);
    }

    protected function _templatePreviewImageTag($url, $altText, $style = '')
    {
        $url = trim((string)$url);
        if ($url === '')
        {
            $url = $this->_templatePreviewSvgDataUri($altText, 180, 120);
        }
        if ($style === '')
        {
            $style = 'display:block;width:100%;height:100%;object-fit:contain;object-position:center;';
        }
        return '<img src="'.htmlspecialchars($url, ENT_QUOTES, 'UTF-8').'" alt="'.htmlspecialchars((string)$altText, ENT_QUOTES, 'UTF-8').'" style="'.htmlspecialchars($style, ENT_QUOTES, 'UTF-8').'" />';
    }

    protected function _templatePreviewPlaceholderMap($template = array())
    {
        $tokens = $this->_placeholdersGuide();
        $map = array();
        foreach ($tokens as $token)
        {
            $token = trim((string)$token);
            if ($token !== '')
            {
                $map[$token] = 'N/A';
            }
        }

        $today = date('d-M-Y');
        $agencyName = 'White Villas Awami Block';
        $clientName = 'SANA ULLAH S/O MUHAMMAD SHARIF';
        $dealNo = 'WV-L/AB/2024-4012';
        $propertyName = 'MA7055386-5M';
        $typeCode = isset($template['type']) ? trim((string)$template['type']) : '';
        $isBookingOrWelcome = in_array($typeCode, array('RLT_BOOKING', 'RLT_WELCOME'), true);
        $en = function($v) use ($isBookingOrWelcome) {
            return $isBookingOrWelcome ? $v : $this->_wrapEnInline($v);
        };
        $logoUrl = $this->_resolveImageFromCandidates(array(
            'logo-website.fw.png',
            'organisation-logo-20260511101817-recovered.png',
            'admin.png'
        ));
        if ($logoUrl === '')
        {
            $logoUrl = $this->_templatePreviewSvgDataUri('WHITE VILLAS', 180, 120);
        }
        $clientPhotoUrl = $this->_templatePreviewSvgDataUri('Client Photo', 180, 220);
        $idFrontUrl = $this->_templatePreviewSvgDataUri('CNIC Front', 260, 160);
        $idBackUrl = $this->_templatePreviewSvgDataUri('CNIC Back', 260, 160);
        $qrUrl = $this->_letterDealQrUrl(
            $agencyName,
            $clientName,
            $dealNo,
            'Online Bank - '.$this->currency.CakeNumber::format(30000),
            880000,
            'template-preview-'.$today,
            array(
                'agency_mobile' => '+923391101156',
                'agency_phone' => '+923391101156',
                'client_cnic' => '35202-1234567-1',
                'deal_status' => 'Active',
                'property_file' => $propertyName,
                'property_area' => '5 Marla',
                'property_type' => 'PLOT',
                'contract_type' => 'Installment'
            )
        );

        $sampleRows = '<table style="width:100%;border-collapse:collapse;font-size:12px;">'
            . '<tr><th style="border:1px solid #cbd5e1;padding:6px;text-align:left;">Charge</th><th style="border:1px solid #cbd5e1;padding:6px;text-align:right;">Amount</th></tr>'
            . '<tr><td style="border:1px solid #cbd5e1;padding:6px;">Extra Work</td><td style="border:1px solid #cbd5e1;padding:6px;text-align:right;">'.$this->currency.CakeNumber::format(2100000).'</td></tr>'
            . '</table>';
        $refundSchedule = '<table style="width:100%;border-collapse:collapse;font-size:12px;">'
            . '<tr><th style="border:1px solid #cbd5e1;padding:6px;">Cycle</th><th style="border:1px solid #cbd5e1;padding:6px;">Date</th><th style="border:1px solid #cbd5e1;padding:6px;">Amount</th></tr>'
            . '<tr><td style="border:1px solid #cbd5e1;padding:6px;">1</td><td style="border:1px solid #cbd5e1;padding:6px;">'.$today.'</td><td style="border:1px solid #cbd5e1;padding:6px;">'.$this->currency.CakeNumber::format(50000).'</td></tr>'
            . '</table>';

        $ceoSignatureUrl = $this->_templateSignatureUrl($template, 'ceo_signature', 'ceo_signature_dir');
        $chairmanSignatureUrl = $this->_templateSignatureUrl($template, 'chairman_signature', 'chairman_signature_dir');

        return array_merge($map, array(
            '{{site_name}}' => (string)$this->siteName,
            '{{site_organization}}' => (string)$this->siteOrganization,
            '{{site_address}}' => (string)$this->siteAddress,
            '{{site_contact}}' => (string)$this->contact,
            '{{site_email}}' => (string)$this->siteEmail,
            '{{today}}' => $en($today),
            '{{agency_name}}' => $agencyName,
            '{{agency_address}}' => 'Khoud Pur Road, Marka, Lahore',
            '{{agency_phone}}' => '+923391101156',
            '{{agency_email}}' => 'info@whitevillas.com.pk',
            '{{agency_logo_url}}' => $logoUrl,
            '{{agency_logo}}' => $this->_agencyLogoImageTag($logoUrl, $agencyName),
            '{{project_name}}' => $agencyName,
            '{{agency_contact}}' => '+923391101156 | info@whitevillas.com.pk',
            '{{receipt_no}}' => $en('RR-00025'),
            '{{receipt_date}}' => $en($today),
            '{{print_date}}' => $en($today),
            '{{rent_month}}' => $en(date('Y-m')),
            '{{rent_month_label}}' => $en(date('M Y')),
            '{{rent_amount}}' => $en($this->currency.CakeNumber::format(21000)),
            '{{rent_amount_words}}' => 'Twenty One Thousand Rupees Only',
            '{{expense_amount}}' => $en($this->currency.CakeNumber::format(0)),
            '{{net_amount}}' => $en($this->currency.CakeNumber::format(21000)),
            '{{payment_mode}}' => 'Online Bank',
            '{{payment_reference}}' => 'Rental payment received',
            '{{allocated_months}}' => date('M Y').' ('.$this->currency.CakeNumber::format(21000).')',
            '{{agreement_no}}' => $en('RA-00025'),
            '{{tenant_name}}' => $clientName,
            '{{tenant_cnic}}' => $en('35202-1234567-1'),
            '{{tenant_mobile}}' => $en('+923001234567'),
            '{{tenant_email}}' => 'client@example.com',
            '{{tenant_address}}' => 'House # 16, Street # 47, Lahore',
            '{{monthly_rent}}' => $en($this->currency.CakeNumber::format(21000)),
            '{{security_deposit}}' => $en($this->currency.CakeNumber::format(42000)),
            '{{start_date}}' => $en($today),
            '{{end_date}}' => $en(date('d-M-Y', strtotime('+1 year'))),
            '{{due_day}}' => $en('5'),
            '{{total_rent_paid}}' => $en($this->currency.CakeNumber::format(84000)),
            '{{outstanding_rent}}' => $en($this->currency.CakeNumber::format(168000)),
            '{{overdue_rent}}' => $en($this->currency.CakeNumber::format(21000)),
            '{{advance_credit}}' => $en($this->currency.CakeNumber::format(0)),
            '{{receipt_qr_url}}' => $qrUrl,
            '{{receipt_qr}}' => $this->_templatePreviewImageTag($qrUrl, 'Rental Receipt QR'),
            '{{letter_date}}' => $en($today),
            '{{letter_ref_no}}' => $en('REL-2026-0001'),
            '{{letter_title}}' => 'Welcome Letter',
            '{{letter_type_label}}' => 'Welcome Letter',
            '{{client_name}}' => $clientName,
            '{{client_name_with_father}}' => $clientName,
            '{{applicant_name}}' => $clientName,
            '{{applicant_name_with_father}}' => $clientName,
            '{{client_father_name}}' => 'MUHAMMAD SHARIF',
            '{{client_cnic}}' => $en('35202-1234567-1'),
            '{{client_phone}}' => $en('+923001234567'),
            '{{client_mobile}}' => $en('+923001234567'),
            '{{client_email}}' => 'client@example.com',
            '{{client_address}}' => 'House # 16, Street # 47, Lahore',
            '{{client_occupation}}' => 'Business',
            '{{occupation}}' => 'Business',
            '{{deal_occupation}}' => 'Business',
            '{{nominee_name}}' => 'MUHAMMAD AHMAD',
            '{{nominee_cnic}}' => $en('35202-7654321-1'),
            '{{nominee_mobile}}' => $en('+923451234567'),
            '{{nominee_phone}}' => $en('+923451234567'),
            '{{nominee_relation}}' => 'Brother',
            '{{client_profile_photo_url}}' => $clientPhotoUrl,
            '{{client_id_front_image_url}}' => $idFrontUrl,
            '{{client_id_back_image_url}}' => $idBackUrl,
            '{{applicant_photo_url}}' => $clientPhotoUrl,
            '{{client_profile_photo}}' => $this->_templatePreviewImageTag($clientPhotoUrl, 'Client Photo'),
            '{{client_id_front_image}}' => $this->_templatePreviewImageTag($idFrontUrl, 'CNIC Front'),
            '{{client_id_back_image}}' => $this->_templatePreviewImageTag($idBackUrl, 'CNIC Back'),
            '{{applicant_photo}}' => $this->_templatePreviewImageTag($clientPhotoUrl, 'Applicant Photo'),
            '{{deal_invoice_no}}' => $en($dealNo),
            '{{deal_date}}' => $en($today),
            '{{deal_status}}' => 'Active',
            '{{deal_total_amount}}' => $en($this->currency.CakeNumber::format(3600000)),
            '{{deal_total_amount_words}}' => 'Thirty Six Lakh Rupees Only',
            '{{deal_total_amount_words_urdu}}' => 'Thirty Six Lakh Rupees Only',
            '{{deal_total_with_charges}}' => $en($this->currency.CakeNumber::format(5700000)),
            '{{deal_total_with_charges_words_urdu}}' => 'Fifty Seven Lakh Rupees Only',
            '{{deal_discount}}' => $en($this->currency.CakeNumber::format(100000)),
            '{{deal_booking_amount}}' => $en($this->currency.CakeNumber::format(30000)),
            '{{deal_balance_amount}}' => $en($this->currency.CakeNumber::format(2720000)),
            '{{deal_balance_amount_words_urdu}}' => 'Twenty Seven Lakh Twenty Thousand Rupees Only',
            '{{deal_booking_receipt_no}}' => $en('WV/AB/2026-1010'),
            '{{deal_booking_payment_method}}' => 'Online Bank',
            '{{deal_booking_payment_remarks}}' => 'Booking amount received',
            '{{deal_booking_payment_date}}' => $en($today),
            '{{deal_booking_payment_amount}}' => $en($this->currency.CakeNumber::format(30000)),
            '{{deal_booking_payment_amount_words}}' => 'Thirty Thousand Rupees Only',
            '{{deal_booking_payment_amount_words_urdu}}' => 'Thirty Thousand Rupees Only',
            '{{deal_booking_payment_against}}' => 'Booking Amount',
            '{{first_deal_payment_receipt_no}}' => $en('WV/AB/2026-1010'),
            '{{first_deal_payment_method}}' => 'Online Bank',
            '{{first_deal_payment_amount}}' => $en($this->currency.CakeNumber::format(30000)),
            '{{first_deal_payment_amount_words}}' => 'Thirty Thousand Rupees Only',
            '{{first_deal_payment_amount_words_urdu}}' => 'Thirty Thousand Rupees Only',
            '{{first_deal_payment_against}}' => 'Booking Amount',
            '{{letter_deal_qr_url}}' => $qrUrl,
            '{{deal_letter_qr_url}}' => $qrUrl,
            '{{letter_deal_qr}}' => $this->_templatePreviewImageTag($qrUrl, 'Deal QR Code'),
            '{{deal_letter_qr}}' => $this->_templatePreviewImageTag($qrUrl, 'Deal QR Code'),
            '{{bank_name}}' => 'United Bank (UBL)',
            '{{bank_account_title}}' => 'White Villas',
            '{{bank_account_no}}' => $en('0773310485346'),
            '{{bank_iban}}' => $en('PK50UNIL0109000310485346'),
            '{{bank_branch}}' => '0773',
            '{{bank_swift_code}}' => 'UNILPKKA',
            '{{agency_bank_name}}' => 'United Bank (UBL)',
            '{{agency_account_title}}' => 'White Villas',
            '{{agency_account_no}}' => $en('0773310485346'),
            '{{agency_iban}}' => $en('PK50UNIL0109000310485346'),
            '{{ceo_signature_url}}' => $ceoSignatureUrl,
            '{{chairman_signature_url}}' => $chairmanSignatureUrl,
            '{{ceo_signature}}' => $this->_signatureImageTag($ceoSignatureUrl, 'CEO Signature', 'CEO Office'),
            '{{chairman_signature}}' => $this->_signatureImageTag($chairmanSignatureUrl, 'Chairman Signature', 'Board Chairman'),
            '{{property_name}}' => $en($propertyName),
            '{{property_file}}' => $en($propertyName),
            '{{property_area}}' => $en('5 Marla'),
            '{{property_unit}}' => 'Marla',
            '{{property_price}}' => $en($this->currency.CakeNumber::format(3600000)),
            '{{property_type}}' => 'PLOT',
            '{{property_contract}}' => 'Installment',
            '{{ballot_allotment_no}}' => $en('AB-4012'),
            '{{ballot_permanent_type}}' => 'Permanent',
            '{{ballot_property_no}}' => $en('122'),
            '{{ballot_street_no}}' => $en('47'),
            '{{ballot_block_name}}' => 'N/A',
            '{{ballot_street_address}}' => 'Street # 47, Awami Block',
            '{{ballot_unit_area}}' => $en('5'),
            '{{ballot_area_unit}}' => 'Marla',
            '{{ballot_area_full}}' => $en('5 Marla'),
            '{{ballot_property_type}}' => 'PLOT',
            '{{ballot_unit_name}}' => 'Marla',
            '{{ballot_category_name}}' => 'Residential',
            '{{ballot_allotment_date}}' => $en($today),
            '{{allotment_no}}' => $en('AB-4012'),
            '{{street_address}}' => 'Street # 47, Awami Block',
            '{{overdue_installments_label}}' => 'Overdue Installments (3)',
            '{{overdue_installments_count}}' => $en('3'),
            '{{overdue_amount}}' => $en($this->currency.CakeNumber::format(445000)),
            '{{overdue_amount_words}}' => 'Four Lakh Forty Five Thousand Rupees Only',
            '{{final_notice_deadline_days}}' => $en('7'),
            '{{deal_charges_total}}' => $en($this->currency.CakeNumber::format(2100000)),
            '{{deal_charges_total_words}}' => 'Twenty One Lakh Rupees Only',
            '{{deal_charges_total_words_urdu}}' => 'Twenty One Lakh Rupees Only',
            '{{deal_charges_count}}' => $en('1'),
            '{{deal_charges_types}}' => 'Extra Work',
            '{{deal_charges_types_urdu}}' => 'Extra Work',
            '{{deal_charges_table}}' => $sampleRows,
            '{{deal_charge_type}}' => 'Extra Work',
            '{{deal_charge_type_urdu}}' => 'Extra Work',
            '{{deal_charge_cycle_type}}' => 'One Time',
            '{{deal_charge_cycle_count}}' => $en('1'),
            '{{deal_charge_cycle_amount}}' => $en($this->currency.CakeNumber::format(2100000)),
            '{{deal_charge_total_amount}}' => $en($this->currency.CakeNumber::format(2100000)),
            '{{deal_charge_total_amount_words_urdu}}' => 'Twenty One Lakh Rupees Only',
            '{{deal_charge_start_date}}' => $en($today),
            '{{charge_work_details}}' => 'Extra finishing and construction work',
            '{{charge_work_status}}' => 'In Progress',
            '{{charge_work_status_urdu}}' => 'In Progress',
            '{{charge_payment_method}}' => 'Bank Transfer / Cash Receipt',
            '{{charge_payment_deadline}}' => $en($today),
            '{{agreement_place}}' => 'Lahore',
            '{{agency_representative_name}}' => 'Authorized Officer',
            '{{agency_representative_father}}' => 'N/A',
            '{{agency_representative_cnic}}' => 'N/A',
            '{{agency_representative_designation}}' => 'Manager',
            '{{agreement_payment_plan}}' => '36 monthly installments',
            '{{agreement_penalty_rate}}' => '0.1%',
            '{{agreement_possession_months}}' => '36 Months',
            '{{agreement_extra_work_amount}}' => $en($this->currency.CakeNumber::format(2100000)),
            '{{agreement_extra_work_schedule}}' => 'As mutually agreed',
            '{{agreement_cancellation_deduction_rate}}' => '20%',
            '{{agreement_refund_period}}' => '90 Days',
            '{{agreement_court_jurisdiction}}' => 'Lahore',
            '{{agreement_property_block}}' => 'N/A',
            '{{agreement_property_unit_no}}' => $en($propertyName),
            '{{agreement_property_floor}}' => 'Ground Floor',
            '{{agreement_property_covered_area}}' => 'N/A',
            '{{agreement_property_location}}' => 'Awami Block, Lahore',
            '{{property_agreement_category}}' => 'Plot / File',
            '{{property_agreement_terms}}' => $this->_dealAgreementPropertyTermsHtml('plot'),
            '{{witness_one_name}}' => 'Witness One',
            '{{witness_one_cnic}}' => $en('35202-0000000-1'),
            '{{witness_one_contact}}' => $en('+923001111111'),
            '{{witness_one_address}}' => 'Lahore',
            '{{witness_two_name}}' => 'Witness Two',
            '{{witness_two_cnic}}' => $en('35202-0000000-2'),
            '{{witness_two_contact}}' => $en('+923002222222'),
            '{{witness_two_address}}' => 'Lahore',
            '{{refund_request_date}}' => $en($today),
            '{{refund_total_paid}}' => $en($this->currency.CakeNumber::format(880000)),
            '{{refund_deduction_percent}}' => '20%',
            '{{refund_deduction_amount}}' => $en($this->currency.CakeNumber::format(176000)),
            '{{refund_net_refund}}' => $en($this->currency.CakeNumber::format(704000)),
            '{{refund_cycle_count}}' => $en('3'),
            '{{refund_cycle_frequency}}' => 'Monthly',
            '{{refund_cycle_amount}}' => $en($this->currency.CakeNumber::format(234666.67)),
            '{{refund_account_title}}' => $clientName,
            '{{refund_account_no}}' => $en('1234567890'),
            '{{refund_bank_name}}' => 'United Bank (UBL)',
            '{{refund_bank_branch}}' => 'Lahore',
            '{{refund_reason}}' => 'Client request',
            '{{refund_schedule_table}}' => $refundSchedule,
            '{{refund_passport_no}}' => 'N/A',
            '{{client_id}}' => $en('1001')
        ));
    }

    protected function _renderTemplatePreviewDescription($post)
    {
        $template = isset($post['Template']) ? (array)$post['Template'] : array();
        $description = isset($template['description']) ? (string)$template['description'] : '';
        $typeCode = isset($template['type']) ? trim((string)$template['type']) : '';
        $isBookingOrWelcome = (
            in_array($typeCode, array('RLT_BOOKING', 'RLT_WELCOME'), true) ||
            stripos($description, '13in') !== false ||
            stripos($description, '12.18in') !== false ||
            stripos($description, 'form-container') !== false ||
            stripos($description, 'wv-welcome') !== false
        );

        if ($typeCode === 'RLT_BOOKING' || stripos($description, 'BOOKING') !== false)
        {
            $description = preg_replace(
                '/<span\b([^>]*\bid=["\']display-date["\'][^>]*)>(.*?)<\/span>/i',
                '<span class="block text-emerald-950 font-black text-sm mt-0.5 tracking-wide" id="display-date" style="font-size:13px;font-weight:900;color:#064e3b;line-height:1.2;">$2</span>',
                $description
            );
            $description = preg_replace('/<body\b([^>]*\bclass=["\'][^"\']*)flex justify-center([^"\']*["\'][^>]*)>/i', '<body$1flex flex-col items-center$2>', $description);
        }

        if (!$isBookingOrWelcome)
        {
            // Strip out conflicting legacy inline font-size and excessive line-height attributes
            $description = preg_replace('/style=(["\'])(.*?)\bfont-size\s*:\s*[\d\.]+(?:px|pt|rem)\s*;?(.*?)\1/i', 'style=$1$2$3$1', $description);
            $description = preg_replace('/style=(["\'])(.*?)\bline-height\s*:\s*(?:[2-9]|1\.[7-9]|1\.[0-5]|3|4)[\d\.]*(?:px|pt|rem)?\s*;?(.*?)\1/i', 'style=$1$2$3$1', $description);
            $description = preg_replace('/style=["\']\s*["\']/i', '', $description);
            $description = preg_replace('/<style\b[^>]*id=["\']wv-urdu-font-definitions["\'][^>]*>.*?<\/style>/is', '', $description);
        }

        $rendered = strtr($description, $this->_templatePreviewPlaceholderMap($template));
        $rendered = preg_replace('/\{\{\s*[^}]+\s*\}\}/', 'N/A', $rendered);

        // Ensure universal floral border frame is present for single-page letters
        if (strpos($rendered, 'wv-floral-border-frame') === false && (strpos($rendered, 'form-container') !== false || strpos($rendered, 'wv-welcome') !== false))
        {
            $rendered = preg_replace('/(<div\s+class=["\'][^"\']*form-container[^"\']*["\'][^>]*>)/i', "$1\n" . $this->_floralBorderFrameSvg() . "\n", $rendered, 1);
        }

        // Ensure outer border line is present outside the stars
        if (strpos($rendered, '<div class="wv-outer-border-line"') === false && strpos($rendered, 'form-container') !== false)
        {
            $rendered = preg_replace('/(<div\s+class=["\'][^"\']*form-container[^"\']*["\'][^>]*>)/i', "$1\n        <div class=\"wv-outer-border-line\"></div>", $rendered, 1);
        }

        // Ensure inner border line is present inside the stars
        if (strpos($rendered, '<div class="wv-inner-border-line"') === false && strpos($rendered, 'form-container') !== false)
        {
            if (strpos($rendered, 'wv-floral-border-frame') !== false) {
                $rendered = preg_replace('/(<div\s+class=["\'][^"\']*wv-floral-border-frame[^"\']*["\'][^>]*>.*?<\/div>\s*<\/div>)/is', "$1\n        <div class=\"wv-inner-border-line\"></div>\n", $rendered, 1);
            } else {
                $rendered = preg_replace('/(<div\s+class=["\'][^"\']*form-container[^"\']*["\'][^>]*>)/i', "$1\n        <div class=\"wv-inner-border-line\"></div>\n", $rendered, 1);
            }
        }
        if ((strpos($rendered, '.wv-outer-border-line') === false || strpos($rendered, '.wv-inner-border-line') === false) && strpos($rendered, 'form-container') !== false)
        {
            $borderLinesCss = "\n        .wv-outer-border-line { position: absolute; top: 5px; left: 5px; right: 5px; bottom: 5px; border: 1.5px solid #064e3b; pointer-events: none; z-index: 52; box-sizing: border-box; }\n        .wv-inner-border-line { position: absolute; top: 31px; left: 31px; right: 31px; bottom: 31px; border: 1.5px solid #064e3b; pointer-events: none; z-index: 52; box-sizing: border-box; }\n";
            $rendered = preg_replace('/(<\/style>)/i', $borderLinesCss . '$1', $rendered, 1);
        }

        if ($isBookingOrWelcome)
        {
            $rendered = preg_replace('/padding\s*:\s*(?:30px\s+24px|44px\s+42px\s+38px\s+42px|44px\s+44px\s+38px\s+44px|24px|28px\s+24px)(?:\s*!important)?/i', 'padding: 60px 44px 38px 44px', $rendered);
            $rendered = preg_replace('/(<div[^>]*class=["\']col-span-)4(\s+space-y-3\s+flex\s+flex-col\s+justify-center["\']>)/i', '$13$2', $rendered);
            $rendered = preg_replace('/(<div[^>]*class=["\']col-span-)6(\s+flex\s+justify-center\s+items-stretch\s+p-0\.5\s+pt-0["\']>)/i', '$17$2', $rendered);
        }

        if (!$isBookingOrWelcome)
        {
            // Auto-wrap any unwrapped CNICs and currency patterns in Urdu text
            $rendered = preg_replace_callback('/(?<!class=["\']en-inline["\']>)\b(\d{5}-\d{7}-\d)\b/', function($m) {
                return '<span dir="ltr" class="en-inline">' . $m[1] . '</span>';
            }, $rendered);
            $rendered = preg_replace_callback('/(?<!class=["\']en-inline["\']>)\b(Rs\.?\s*[\d,]+(?:\.\d+)?)\b/i', function($m) {
                return '<span dir="ltr" class="en-inline">' . $m[1] . '</span>';
            }, $rendered);
        }

        if ($typeCode === 'RLT_DEAL_AGREEMENT')
        {
            $rendered = $this->_prepareDealAgreementPrintHtml($rendered);
        }
        elseif ($typeCode === 'RLT_REFUND_CLEARANCE')
        {
            $rendered = $this->_prepareRefundCompletionPrintHtml($rendered);
        }
        else
        {
            $rendered = $this->_compactFullHtmlLetterHeader($rendered);
        }
        return $this->_preserveFullHtmlPreviewPrint($rendered);
    }

    public function templatestatus($id = null, $status = null)
    {
        if (empty($this->userPermissionArr['update_right']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'templates'));
        }
        $id = (int)$id;
        if ($id <= 0)
        {
            $this->Session->setFlash(__('Invalid template.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'templates'));
        }
        $this->loadModel('Template');
        $row = (array)$this->Template->find('first', array(
            'fields' => array('Template.id', 'Template.type', 'Template.status'),
            'conditions' => array(
                'Template.id' => $id,
                'Template.type' => array_keys($this->_letterTypeArr)
            ),
            'recursive' => -1
        ));
        if (empty($row['Template']['id']))
        {
            $this->Session->setFlash(__('Invalid template.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'templates'));
        }
        $currentStatus = isset($row['Template']['status']) ? trim((string)$row['Template']['status']) : 'Published';
        $statusText = strtolower(trim((string)$status));
        if ($statusText === 'published')
        {
            $newStatus = 'Published';
        }
        elseif ($statusText === 'unpublished')
        {
            $newStatus = 'Unpublished';
        }
        else
        {
            $newStatus = ($currentStatus === 'Published') ? 'Unpublished' : 'Published';
        }
        try
        {
            $this->Template->id = (int)$id;
            if ($this->Template->saveField('status', $newStatus, array('validate' => false)))
            {
                $this->Session->setFlash(__('Template status updated.'), 'flash', array('alert' => 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Unable to update template status.'), 'flash', array('alert' => 'danger'));
            }
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Unable to update template status.'), 'flash', array('alert' => 'danger'));
        }
        $redirectType = isset($row['Template']['type']) ? trim((string)$row['Template']['type']) : '';
        if (isset($this->request->query['type']) && isset($this->_letterTypeArr[trim((string)$this->request->query['type'])]))
        {
            $redirectType = trim((string)$this->request->query['type']);
        }
        if ($redirectType !== '')
        {
            return $this->redirect(array('action' => 'templates', '?' => array('type' => $redirectType)));
        }
        return $this->redirect(array('action' => 'templates'));
    }

    public function templatedeleteall()
    {
        $this->loadModel('Template');
        try
        {
            if ($this->request->is('post'))
            {
                foreach ((array)$this->data['Template']['id'] as $value)
                {
                    $this->Template->deleteAll(array(
                        'Template.id' => (int)$value,
                        'Template.type' => array_keys($this->_letterTypeArr),
                        'Template.is_new' => 'Yes'
                    ));
                }
                $this->Session->setFlash(__('Letter template has been deleted.'), 'flash', array('alert' => 'success'));
            }
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Unable to delete selected template(s).'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'templates'));
    }

    public function index($first = null, $second = null)
    {
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        list($selectedLetterType, $dealId) = $this->_extractTypeAndDeal($first, $second);
        if ($selectedLetterType === '' && isset($this->request->query['type']))
        {
            $queryType = trim((string)$this->request->query['type']);
            if (isset($this->_letterTypeArr[$queryType]))
            {
                $selectedLetterType = $queryType;
            }
        }
        if ($dealId <= 0 && isset($this->request->query['deal_id']) && is_numeric($this->request->query['deal_id']))
        {
            $dealId = (int)$this->request->query['deal_id'];
        }
        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';

        $conditions = array();
        if ($dealId > 0)
        {
            $conditions[] = array('Realestateletter.deal_id' => $dealId);
        }
        if ($selectedLetterType !== '')
        {
            $conditions[] = array('Realestateletter.letter_type' => $selectedLetterType);
        }
        $agencyCondition = $this->_lettersAgencyCondition('Property.project_id', $projectId);
        if (!empty($agencyCondition))
        {
            $conditions[] = $agencyCondition;
        }
        if ($keyword !== '')
        {
            $conditions[] = array('OR' => array(
                'Realestateletter.reference_no LIKE' => '%'.$keyword.'%',
                'Realestateletter.title LIKE' => '%'.$keyword.'%',
                'Client.name LIKE' => '%'.$keyword.'%',
                'Deal.invoice_no LIKE' => '%'.$keyword.'%',
                'Property.name LIKE' => '%'.$keyword.'%'
            ));
        }

        $this->Paginator->settings = $this->paginate;
        $this->Paginator->settings['joins'] = array(
            array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=Realestateletter.deal_id')),
            array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
            array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id')),
            array('table' => 'templates', 'alias' => 'Template', 'type' => 'LEFT', 'conditions' => array('Template.id=Realestateletter.template_id'))
        );
        $this->Paginator->settings['fields'] = array(
            'Realestateletter.*',
            'Deal.invoice_no', 'Deal.date',
            'Client.name', 'Client.fathername', 'Client.relation_type', 'Client.gender', 'Client.phone',
            'Property.name',
            'Template.name'
        );
        $this->Paginator->settings['recursive'] = -1;
        $this->Paginator->settings['conditions'] = $conditions;
        $this->Paginator->settings['limit'] = $this->pageLimit;
        $this->Paginator->settings['maxLimit'] = $this->maxLimit;
        $this->Realestateletter->recursive = -1;
        $this->Realestateletter->unbindModel(array(
            'belongsTo' => array('Deal', 'Template')
        ), false);
        $this->set('Realestateletter', $this->Paginator->paginate('Realestateletter'));
        $this->set('currentDealId', $dealId);
        $this->set('currentLetterType', $selectedLetterType);
        $this->set('searchKeyword', $keyword);
        $this->set('lettersProjectId', $projectId);

        $dealHeader = array();
        if ($dealId > 0)
        {
            $dealHeader = $this->_dealDetails($dealId, false, $projectId);
        }
        $this->set('dealHeader', $dealHeader);

        if ($this->request->is('ajax'))
        {
            $this->render('index', 'ajax');
        }
    }

    public function add($first = null, $second = null)
    {
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        list($forcedLetterType, $dealId) = $this->_extractTypeAndDeal($first, $second);
        if ($forcedLetterType === '' && $this->request->is('post') && !empty($this->request->data['Realestateletter']['_force_type']))
        {
            $postedForcedType = isset($this->request->data['Realestateletter']['letter_type']) ? trim((string)$this->request->data['Realestateletter']['letter_type']) : '';
            if (isset($this->_letterTypeArr[$postedForcedType]))
            {
                $forcedLetterType = $postedForcedType;
            }
        }
        $this->set('dealOptions', $this->_dealList($dealId, $projectId));
        $this->_prepareTemplateDrivenData();
        $this->_setPlaceholderGuideVars();
        $this->set('forcedLetterType', $forcedLetterType);
        $this->set('lettersProjectId', $projectId);

        if (!$this->request->is('post'))
        {
            if ($dealId > 0)
            {
                $this->request->data['Realestateletter']['deal_id'] = $dealId;
            }
            if ($forcedLetterType !== '')
            {
                $this->request->data['Realestateletter']['letter_type'] = $forcedLetterType;
                $defaultTemplateId = $this->_defaultTemplateIdForType($forcedLetterType);
                if ($defaultTemplateId > 0)
                {
                    $this->request->data['Realestateletter']['template_id'] = $defaultTemplateId;
                }
            }
            $this->request->data['Realestateletter']['letter_date'] = date($this->dtFormat);
            $this->request->data['Realestateletter']['status'] = 'Unpublished';
            $this->request->data['Realestateletter']['approval_status'] = 'Draft';
            $previewProjectId = (int)$projectId;
            if ($dealId > 0)
            {
                $previewDeal = $this->_dealDetails($dealId, false, $projectId);
                if (!empty($previewDeal['Property']['project_id']))
                {
                    $previewProjectId = $this->_lettersNormalizeProjectId((int)$previewDeal['Property']['project_id']);
                }
                $this->_prepareChargeAgreementRequestData($forcedLetterType, $previewDeal);
                $this->_prepareDealAgreementRequestData($forcedLetterType, $previewDeal);
            }
            $this->request->data['Realestateletter']['reference_no'] = $this->_peekLetterReferenceNo($previewProjectId, $forcedLetterType);
        }

        if ($this->request->is('post'))
        {
            $this->Realestateletter->create();
            try
            {
                if ($dealId > 0)
                {
                    $this->request->data['Realestateletter']['deal_id'] = $dealId;
                }
                if ($forcedLetterType !== '')
                {
                    $this->request->data['Realestateletter']['letter_type'] = $forcedLetterType;
                }
                $dealIdPosted = isset($this->request->data['Realestateletter']['deal_id']) ? (int)$this->request->data['Realestateletter']['deal_id'] : 0;
                $deal = $this->_dealDetails($dealIdPosted, false, $projectId);
                if (empty($deal['Deal']['id']))
                {
                    $this->Session->setFlash(__('Invalid deal selected.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                $this->request->data['Realestateletter']['deal_id'] = (int)$deal['Deal']['id'];
                $this->request->data['Realestateletter']['client_id'] = isset($deal['Client']['id']) ? (int)$deal['Client']['id'] : null;
                $this->request->data['Realestateletter']['user_id'] = (int)$this->luserId;

                $selectedType = isset($this->request->data['Realestateletter']['letter_type']) ? trim((string)$this->request->data['Realestateletter']['letter_type']) : '';
                if (!isset($this->_letterTypeArr[$selectedType]))
                {
                    $this->Session->setFlash(__('Please select a valid letter type.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                if ($selectedType === 'RLT_DEMARCATION' && !$this->_demarcationAllowedForDeal((int)$deal['Deal']['id']))
                {
                    $this->Session->setFlash(__('Demarcation letter can only be created after allotment.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                if ($selectedType === 'RLT_CHARGE' && !$this->_dealHasActiveCharges((int)$deal['Deal']['id']))
                {
                    $this->Session->setFlash(__('Deal charges agreement can only be created after adding active deal charges.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                $this->_prepareChargeAgreementRequestData($selectedType, $deal);
                $this->_prepareDealAgreementRequestData($selectedType, $deal);

                $selectedTemplateId = isset($this->request->data['Realestateletter']['template_id']) ? (int)$this->request->data['Realestateletter']['template_id'] : 0;
                if ($selectedTemplateId <= 0)
                {
                    $selectedTemplateId = $this->_defaultTemplateIdForType($selectedType);
                    if ($selectedTemplateId > 0)
                    {
                        $this->request->data['Realestateletter']['template_id'] = $selectedTemplateId;
                    }
                }
                if ($selectedTemplateId > 0)
                {
                    $this->loadModel('Template');
                    $selectedTemplate = $this->Template->find('first', array(
                        'fields' => array('Template.id', 'Template.type', 'Template.description', 'Template.status'),
                        'conditions' => array('Template.id' => $selectedTemplateId, 'Template.status' => 'Published'),
                        'recursive' => -1
                    ));
                    if (empty($selectedTemplate['Template']['id']))
                    {
                        $this->Session->setFlash(__('Invalid template selected.'), 'flash', array('alert' => 'danger'));
                        return;
                    }
                    if (trim((string)$selectedTemplate['Template']['type']) !== $selectedType)
                    {
                        $this->Session->setFlash(__('Selected template type does not match letter type.'), 'flash', array('alert' => 'danger'));
                        return;
                    }
                    $templateBody = (string)$selectedTemplate['Template']['description'];
                    $postedBody = isset($this->request->data['Realestateletter']['description']) ? (string)$this->request->data['Realestateletter']['description'] : '';
                    if ($this->_letterBodyIsBlank($postedBody))
                    {
                        $this->request->data['Realestateletter']['description'] = $templateBody;
                    }
                }
                if (strlen(trim((string)$this->request->data['Realestateletter']['reference_no'])) <= 0)
                {
                    $this->request->data['Realestateletter']['reference_no'] = '';
                }
                $referenceProjectId = isset($deal['Property']['project_id']) ? (int)$deal['Property']['project_id'] : (int)$projectId;
                $referenceProjectId = $this->_lettersNormalizeProjectId($referenceProjectId);
                $postedReferenceNo = trim((string)$this->request->data['Realestateletter']['reference_no']);
                $autoReferenceNo = $this->_peekLetterReferenceNo($referenceProjectId, $selectedType);
                $shouldConsumeReference = ($postedReferenceNo === '' || $postedReferenceNo === $autoReferenceNo);
                if ($shouldConsumeReference)
                {
                    $postedReferenceNo = $this->_nextLetterReferenceNo($referenceProjectId, $selectedType);
                }
                if ($postedReferenceNo === '')
                {
                    $postedReferenceNo = $this->_referenceNo();
                }
                if (!$shouldConsumeReference && $this->_letterReferenceExists($postedReferenceNo, $referenceProjectId))
                {
                    $this->Session->setFlash(__('Reference no already exists for this agency.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                $this->request->data['Realestateletter']['reference_no'] = $postedReferenceNo;
                if (strlen(trim((string)$this->request->data['Realestateletter']['title'])) <= 0)
                {
                    $this->request->data['Realestateletter']['title'] = $this->_letterTypeArr[$selectedType].' - '.(isset($deal['Deal']['invoice_no']) ? $deal['Deal']['invoice_no'] : '');
                }
                if (strlen(trim((string)$this->request->data['Realestateletter']['status'])) <= 0)
                {
                    $this->request->data['Realestateletter']['status'] = 'Unpublished';
                }
                if (empty($this->request->data['Realestateletter']['approval_status']))
                {
                    $this->request->data['Realestateletter']['approval_status'] = ($this->request->data['Realestateletter']['status'] === 'Published') ? 'Issued' : 'Draft';
                }
                unset($this->request->data['Realestateletter']['_force_type']);
                if ($this->Realestateletter->save($this->request->data))
                {
                    $savedLetterId = (int)$this->Realestateletter->id;
                    if ($savedLetterId > 0)
                    {
                        $savedLetter = $this->_letterDetails($savedLetterId, $projectId);
                        if (!empty($savedLetter['Realestateletter']['id']))
                        {
                            $savedStatus = isset($savedLetter['Realestateletter']['status']) ? trim((string)$savedLetter['Realestateletter']['status']) : '';
                            if ($savedStatus === 'Published')
                            {
                                $this->_dispatchLetterNotifications($savedLetter, 'issue');
                            }
                        }
                    }
                    $this->Session->setFlash(__('Real Estate Letter has been saved.'), 'flash', array('alert' => 'success'));
                    $redirectUrl = array('action' => 'index');
                    if ($selectedType !== '')
                    {
                        $redirectUrl[] = $selectedType;
                    }
                    $redirectUrl[] = (int)$deal['Deal']['id'];
                    if ($projectId > 0) {
                        $redirectUrl['?'] = array('project_id' => (int)$projectId);
                    }
                    return $this->redirect($redirectUrl);
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
            }
        }
    }

    public function approvals()
    {
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        $approvalStatus = isset($this->request->query['approval_status']) ? trim((string)$this->request->query['approval_status']) : '';
        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';
        $conditions = array();
        $agencyCondition = $this->_lettersAgencyCondition('Property.project_id', $projectId);
        if(!empty($agencyCondition))
        {
            $conditions[] = $agencyCondition;
        }
        if($approvalStatus !== '' && in_array($approvalStatus, array('Draft','Approved','Issued'), true))
        {
            $conditions['Realestateletter.approval_status'] = $approvalStatus;
        }
        if($keyword !== '')
        {
            $conditions[] = array('OR' => array(
                'Realestateletter.reference_no LIKE' => '%'.$keyword.'%',
                'Realestateletter.title LIKE' => '%'.$keyword.'%',
                'Client.name LIKE' => '%'.$keyword.'%',
                'Deal.invoice_no LIKE' => '%'.$keyword.'%',
                'Property.name LIKE' => '%'.$keyword.'%'
            ));
        }
        $this->Paginator->settings = array(
            'Realestateletter' => array(
                'fields' => array('Realestateletter.*','Deal.invoice_no','Client.name','Property.name','Template.name'),
                'joins' => array(
                    array('table'=>'deals','alias'=>'Deal','type'=>'LEFT','conditions'=>array('Deal.id=Realestateletter.deal_id')),
                    array('table'=>'clients','alias'=>'Client','type'=>'LEFT','conditions'=>array('Client.id=Deal.client_id')),
                    array('table'=>'properties','alias'=>'Property','type'=>'LEFT','conditions'=>array('Property.id=Deal.property_id')),
                    array('table'=>'templates','alias'=>'Template','type'=>'LEFT','conditions'=>array('Template.id=Realestateletter.template_id'))
                ),
                'conditions' => $conditions,
                'order' => array('Realestateletter.id' => 'desc'),
                'limit' => $this->pageLimit,
                'maxLimit' => $this->maxLimit,
                'recursive' => -1
            )
        );
        $letters = $this->Paginator->paginate('Realestateletter');
        $this->set(compact('letters','projectId','approvalStatus','keyword'));
    }

    public function draftletter($id = null)
    {
        return $this->_updateApprovalStatus($id, 'Draft');
    }

    public function approveletter($id = null)
    {
        return $this->_updateApprovalStatus($id, 'Approved');
    }

    public function issueletter($id = null)
    {
        return $this->_updateApprovalStatus($id, 'Issued');
    }

    protected function _updateApprovalStatus($id, $target)
    {
        if(empty($this->userPermissionArr['update_right']) && empty($this->userPermissionArr['save_right']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action'=>'approvals'));
        }
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        $id = (int)$id;
        $row = $this->_letterDetails($id, $projectId);
        if(empty($row['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid letter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action'=>'approvals','?'=>array('project_id'=>$projectId)));
        }
        $target = in_array($target, array('Draft','Approved','Issued'), true) ? $target : 'Draft';
        $save = array(
            'id' => $id,
            'approval_status' => $target
        );
        if($target === 'Draft')
        {
            $save['status'] = 'Unpublished';
            $save['approved_by'] = 0;
            $save['approved_at'] = null;
            $save['issued_by'] = 0;
            $save['issued_at'] = null;
        }
        elseif($target === 'Approved')
        {
            $save['status'] = 'Unpublished';
            $save['approved_by'] = (int)$this->luserId;
            $save['approved_at'] = date('Y-m-d H:i:s');
        }
        elseif($target === 'Issued')
        {
            $save['status'] = 'Published';
            if(empty($row['Realestateletter']['approved_by']))
            {
                $save['approved_by'] = (int)$this->luserId;
                $save['approved_at'] = date('Y-m-d H:i:s');
            }
            $save['issued_by'] = (int)$this->luserId;
            $save['issued_at'] = date('Y-m-d H:i:s');
        }
        try
        {
            $this->Realestateletter->create();
            if($this->Realestateletter->save(array('Realestateletter'=>$save), array('validate'=>false)))
            {
                if($target === 'Issued')
                {
                    $updatedRow = $this->_letterDetails($id, $projectId);
                    if(!empty($updatedRow['Realestateletter']['id']))
                    {
                        $this->_dispatchLetterNotifications($updatedRow, 'issue');
                    }
                }
                $this->Session->setFlash(__('Letter approval status updated.'), 'flash', array('alert' => 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Unable to update letter approval.'), 'flash', array('alert' => 'danger'));
            }
        }
        catch(Exception $e)
        {
            $this->Session->setFlash(__('Unable to update letter approval.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action'=>'approvals','?'=>array('project_id'=>$projectId)));
    }

    public function edit($id = null)
    {
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        if (!$id)
        {
            throw new NotFoundException(__('Invalid post'));
        }
        $post = $this->_letterDetails($id, $projectId);
        if (empty($post['Realestateletter']['id']))
        {
            throw new NotFoundException(__('Invalid post'));
        }

        $this->set('dealOptions', $this->_dealList((int)$post['Realestateletter']['deal_id'], $projectId));
        $this->_prepareTemplateDrivenData();
        $this->_setPlaceholderGuideVars();
        $this->set('lettersProjectId', $projectId);

        if ($this->request->is(array('post', 'put')))
        {
            $this->Realestateletter->id = (int)$id;
            try
            {
                $dealIdPosted = isset($this->request->data['Realestateletter']['deal_id']) ? (int)$this->request->data['Realestateletter']['deal_id'] : 0;
                $deal = $this->_dealDetails($dealIdPosted, false, $projectId);
                if (empty($deal['Deal']['id']))
                {
                    $this->Session->setFlash(__('Invalid deal selected.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                $selectedType = isset($this->request->data['Realestateletter']['letter_type']) ? trim((string)$this->request->data['Realestateletter']['letter_type']) : '';
                if (!isset($this->_letterTypeArr[$selectedType]))
                {
                    $this->Session->setFlash(__('Please select a valid letter type.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                $existingType = isset($post['Realestateletter']['letter_type']) ? trim((string)$post['Realestateletter']['letter_type']) : '';
                if ($selectedType === 'RLT_DEMARCATION' && $existingType !== 'RLT_DEMARCATION' && !$this->_demarcationAllowedForDeal((int)$deal['Deal']['id']))
                {
                    $this->Session->setFlash(__('Demarcation letter can only be created after allotment.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                if ($selectedType === 'RLT_CHARGE' && !$this->_dealHasActiveCharges((int)$deal['Deal']['id']))
                {
                    $this->Session->setFlash(__('Deal charges agreement can only be created after adding active deal charges.'), 'flash', array('alert' => 'danger'));
                    return;
                }
                $this->_prepareChargeAgreementRequestData($selectedType, $deal);
                $this->_prepareDealAgreementRequestData($selectedType, $deal);
                $selectedTemplateId = isset($this->request->data['Realestateletter']['template_id']) ? (int)$this->request->data['Realestateletter']['template_id'] : 0;
                if ($selectedTemplateId > 0)
                {
                    $this->loadModel('Template');
                    $selectedTemplate = $this->Template->find('first', array(
                        'fields' => array('Template.id', 'Template.type', 'Template.description', 'Template.status'),
                        'conditions' => array('Template.id' => $selectedTemplateId, 'Template.status' => 'Published'),
                        'recursive' => -1
                    ));
                    if (empty($selectedTemplate['Template']['id']) || trim((string)$selectedTemplate['Template']['type']) !== $selectedType)
                    {
                        $this->Session->setFlash(__('Selected template type does not match letter type.'), 'flash', array('alert' => 'danger'));
                        return;
                    }
                    $templateBody = (string)$selectedTemplate['Template']['description'];
                    $postedBody = isset($this->request->data['Realestateletter']['description']) ? (string)$this->request->data['Realestateletter']['description'] : '';
                    if ($this->_letterBodyIsBlank($postedBody))
                    {
                        $this->request->data['Realestateletter']['description'] = $templateBody;
                    }
                }
                $this->request->data['Realestateletter']['id'] = (int)$id;
                $this->request->data['Realestateletter']['deal_id'] = (int)$deal['Deal']['id'];
                $this->request->data['Realestateletter']['client_id'] = isset($deal['Client']['id']) ? (int)$deal['Client']['id'] : null;
                if (strlen(trim((string)$this->request->data['Realestateletter']['reference_no'])) <= 0)
                {
                    $this->request->data['Realestateletter']['reference_no'] = $post['Realestateletter']['reference_no'];
                }
                if ($this->Realestateletter->save($this->request->data))
                {
                    $this->Session->setFlash(__('Real Estate Letter has been updated.'), 'flash', array('alert' => 'success'));
                    $redirectUrl = array('action' => 'index');
                    if ($selectedType !== '')
                    {
                        $redirectUrl[] = $selectedType;
                    }
                    $redirectUrl[] = (int)$deal['Deal']['id'];
                    if ($projectId > 0) {
                        $redirectUrl['?'] = array('project_id' => (int)$projectId);
                    }
                    return $this->redirect($redirectUrl);
                }
            }
            catch (Exception $e)
            {
                $this->Session->setFlash($e->getMessage(), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'index'));
            }
        }
        if (!$this->request->data)
        {
            if (!empty($post['Realestateletter']['letter_date']))
            {
                $post['Realestateletter']['letter_date'] = $this->_formatDateValue($post['Realestateletter']['letter_date'], '');
            }
            $this->request->data = $post;
        }
    }

    public function deleteall()
    {
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        try
        {
            if ($this->request->is('post'))
            {
                $deletedCount = 0;
                foreach ((array)$this->data['Realestateletter']['id'] as $value)
                {
                    $row = $this->_letterDetails((int)$value, $projectId);
                    if (empty($row['Realestateletter']['id'])) {
                        continue;
                    }
                    if ($this->_moveLetterToTrash($row))
                    {
                        if ($this->Realestateletter->delete((int)$value))
                        {
                            $deletedCount++;
                        }
                    }
                }
                if ($deletedCount > 0)
                {
                    $this->Session->setFlash(__('Selected letter(s) moved to trash.'), 'flash', array('alert' => 'success'));
                }
                else
                {
                    $this->Session->setFlash(__('No letters were moved to trash.'), 'flash', array('alert' => 'warning'));
                }
            }
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Unable to delete selected records.'), 'flash', array('alert' => 'danger'));
        }
        $redirectUrl = array('action' => 'index');
        if ($projectId > 0) {
            $redirectUrl['?'] = array('project_id' => $projectId);
        }
        return $this->redirect($redirectUrl);
    }

    public function trash()
    {
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        if ($this->request->is('post'))
        {
            $bulkAction = '';
            if (isset($this->request->data['RealestateletterTrash']['bulk_action']))
            {
                $bulkAction = strtolower(trim((string)$this->request->data['RealestateletterTrash']['bulk_action']));
            }
            elseif (isset($this->request->data['bulk_action']))
            {
                $bulkAction = strtolower(trim((string)$this->request->data['bulk_action']));
            }
            $selectedIds = array();
            if (isset($this->request->data['trash_ids']) && is_array($this->request->data['trash_ids']))
            {
                $selectedIds = $this->request->data['trash_ids'];
            }
            elseif (isset($this->request->data['RealestateletterTrash']['trash_ids']) && is_array($this->request->data['RealestateletterTrash']['trash_ids']))
            {
                $selectedIds = $this->request->data['RealestateletterTrash']['trash_ids'];
            }
            $selectedIds = array_values(array_unique(array_map('intval', $selectedIds)));
            $selectedIds = array_filter($selectedIds, function($trashId){ return $trashId > 0; });
            if (empty($selectedIds))
            {
                $this->Session->setFlash(__('Please select at least one trash item.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
            }
            if (!in_array($bulkAction, array('restore', 'delete'), true))
            {
                $this->Session->setFlash(__('Please select valid bulk action.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
            }
            if ($bulkAction === 'delete' && !$this->_lettersCanDeleteTrashPermanently())
            {
                $this->Session->setFlash(__('Only super admin or agency admin can delete from trash permanently.'), 'flash', array('alert' => 'danger'));
                return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
            }

            $successCount = 0;
            $failedCount = 0;
            $firstError = '';
            foreach ($selectedIds as $trashId)
            {
                $trashRow = $this->_lettersTrashScopedRow((int)$trashId, $projectId);
                $error = '';
                $ok = false;
                if (empty($trashRow['RealestateletterTrash']['id']))
                {
                    $error = __('Trash entry not found.');
                }
                else
                {
                    if ($bulkAction === 'restore')
                    {
                        $ok = $this->_lettersRestoreTrashRecord($trashRow, $error);
                    }
                    else
                    {
                        $ok = $this->_lettersDeleteTrashRecord($trashRow, $error);
                    }
                }
                if ($ok)
                {
                    $successCount++;
                }
                else
                {
                    $failedCount++;
                    if ($firstError === '' && $error !== '')
                    {
                        $firstError = $error;
                    }
                }
            }
            if ($failedCount > 0)
            {
                $message = ($bulkAction === 'restore') ? __('Bulk restore completed.') : __('Bulk permanent delete completed.');
                $message .= ' ' . __('Success: %d, Failed: %d.', $successCount, $failedCount);
                if ($firstError !== '')
                {
                    $message .= ' ' . __('First error: %s', $firstError);
                }
                $this->Session->setFlash($message, 'flash', array('alert' => 'danger'));
            }
            else
            {
                if ($bulkAction === 'restore')
                {
                    $this->Session->setFlash(__('Selected letters restored successfully.'), 'flash', array('alert' => 'success'));
                }
                else
                {
                    $this->Session->setFlash(__('Selected letters deleted permanently from trash.'), 'flash', array('alert' => 'success'));
                }
            }
            return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
        }

        $keyword = isset($this->request->query['keyword']) ? trim((string)$this->request->query['keyword']) : '';
        $selectedType = isset($this->request->query['type']) ? trim((string)$this->request->query['type']) : '';
        if ($selectedType !== '' && !isset($this->_letterTypeArr[$selectedType]))
        {
            $selectedType = '';
        }
        $this->_loadLettersTrashModel();
        $conditions = array();
        $agencyCondition = $this->_lettersAgencyCondition('RealestateletterTrash.project_id', $projectId);
        if (!empty($agencyCondition))
        {
            $conditions[] = $agencyCondition;
        }
        if ($selectedType !== '')
        {
            $conditions[] = array('RealestateletterTrash.letter_type' => $selectedType);
        }
        if ($keyword !== '')
        {
            $conditions[] = array('OR' => array(
                'RealestateletterTrash.reference_no LIKE' => '%'.$keyword.'%',
                'RealestateletterTrash.title LIKE' => '%'.$keyword.'%',
                'Client.name LIKE' => '%'.$keyword.'%',
                'Deal.invoice_no LIKE' => '%'.$keyword.'%',
                'Property.name LIKE' => '%'.$keyword.'%'
            ));
        }
        $this->Paginator->settings = array(
            'page' => 1,
            'order' => array('RealestateletterTrash.id' => 'desc'),
            'conditions' => $conditions,
            'joins' => array(
                array('table' => 'deals', 'alias' => 'Deal', 'type' => 'LEFT', 'conditions' => array('Deal.id=RealestateletterTrash.deal_id')),
                array('table' => 'clients', 'alias' => 'Client', 'type' => 'LEFT', 'conditions' => array('Client.id=Deal.client_id')),
                array('table' => 'properties', 'alias' => 'Property', 'type' => 'LEFT', 'conditions' => array('Property.id=Deal.property_id'))
            ),
            'fields' => array(
                'RealestateletterTrash.*',
                'Deal.invoice_no',
                'Client.name',
                'Property.name'
            ),
            'recursive' => -1,
            'limit' => $this->pageLimit,
            'maxLimit' => $this->maxLimit
        );
        $this->set('trashRows', $this->Paginator->paginate('RealestateletterTrash'));
        $this->set('lettersProjectId', $projectId);
        $this->set('searchKeyword', $keyword);
        $this->set('selectedType', $selectedType);
        $this->set('canDeleteTrashPermanently', $this->_lettersCanDeleteTrashPermanently());
    }

    public function restoretrash($id = null)
    {
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        $id = (int)$id;
        if ($id <= 0)
        {
            $this->Session->setFlash(__('Invalid trash record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
        }
        $this->_loadLettersTrashModel();
        $trashRow = $this->_lettersTrashScopedRow($id, $projectId);
        if (empty($trashRow['RealestateletterTrash']['id']))
        {
            $this->Session->setFlash(__('Invalid trash record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
        }
        $error = '';
        if ($this->_lettersRestoreTrashRecord($trashRow, $error))
        {
            $this->Session->setFlash(__('Letter restored from trash.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            if ($error === '')
            {
                $error = __('Unable to restore letter from trash.');
            }
            $this->Session->setFlash($error, 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
    }

    public function deletetrash($id = null)
    {
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        if (!$this->_lettersCanDeleteTrashPermanently())
        {
            $this->Session->setFlash(__('Only super admin or agency admin can delete from trash permanently.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
        }
        $id = (int)$id;
        if ($id <= 0)
        {
            $this->Session->setFlash(__('Invalid trash record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
        }
        $trashRow = $this->_lettersTrashScopedRow($id, $projectId);
        if (empty($trashRow['RealestateletterTrash']['id']))
        {
            $this->Session->setFlash(__('Invalid trash record.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
        }
        $error = '';
        if ($this->_lettersDeleteTrashRecord($trashRow, $error))
        {
            $this->Session->setFlash(__('Letter deleted permanently from trash.'), 'flash', array('alert' => 'success'));
        }
        else
        {
            if ($error === '')
            {
                $error = __('Unable to delete trash record.');
            }
            $this->Session->setFlash($error, 'flash', array('alert' => 'danger'));
        }
        return $this->redirect(array('action' => 'trash', '?' => array('project_id' => $projectId)));
    }

    public function view($id = null)
    {
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $post = $this->_letterDetails($id);
        if (empty($post['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid Post.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $this->set('post', $post);
        $renderedDescription = $this->_renderLetterBody($post);
        $this->set('renderedDescription', $renderedDescription);
        $this->set('isFullHtmlTemplate', $this->_isFullHtmlDocument($renderedDescription));
        $letterType = isset($post['Realestateletter']['letter_type']) ? $post['Realestateletter']['letter_type'] : '';
        $this->set('letterTypeLabel', isset($this->_letterTypeArr[$letterType]) ? $this->_letterTypeArr[$letterType] : $letterType);
    }

    public function printletter($id = null)
    {
        $this->layout = null;
        $this->response->disableCache();
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $post = $this->_letterDetails($id);
        if (empty($post['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid Post.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $renderedDescription = $this->_renderLetterBody($post);
        $letterType = isset($post['Realestateletter']['letter_type']) ? $post['Realestateletter']['letter_type'] : '';
        $this->set('post', $post);
        $this->set('renderedDescription', $renderedDescription);
        $this->set('isFullHtmlTemplate', $this->_isFullHtmlDocument($renderedDescription));
        $this->set('letterType', $letterType);
        $this->set('letterTypeLabel', isset($this->_letterTypeArr[$letterType]) ? $this->_letterTypeArr[$letterType] : $letterType);
        $this->set('pdfFileName', $this->_letterPdfFileName($post));
        $this->set('pdfPageFormat', $this->_letterPdfPageFormat($renderedDescription));
        $this->set('mode', 'print');
    }

    public function exportletterpdf($id = null)
    {
        $this->layout = null;
        if (!$id)
        {
            $this->Session->setFlash(__('Invalid Post.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $post = $this->_letterDetails($id);
        if (empty($post['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid Post.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $renderedDescription = $this->_renderLetterBody($post);
        $letterType = isset($post['Realestateletter']['letter_type']) ? $post['Realestateletter']['letter_type'] : '';
        $this->set('post', $post);
        $this->set('renderedDescription', $renderedDescription);
        $this->set('isFullHtmlTemplate', $this->_isFullHtmlDocument($renderedDescription));
        $this->set('letterType', $letterType);
        $this->set('letterTypeLabel', isset($this->_letterTypeArr[$letterType]) ? $this->_letterTypeArr[$letterType] : $letterType);
        $this->set('pdfFileName', $this->_letterPdfFileName($post));
        $this->set('pdfPageFormat', $this->_letterPdfPageFormat($renderedDescription));
        $this->set('mode', 'pdf');
    }

    public function sendsms($id = null)
    {
        if (empty($this->userPermissionArr['save_right']) && empty($this->userPermissionArr['update_right']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        $id = (int)$id;
        if ($id <= 0)
        {
            $this->Session->setFlash(__('Invalid letter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
        }
        $row = $this->_letterDetails($id, $projectId);
        if (empty($row['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid letter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
        }
        try
        {
            $event = $this->_letterNotificationEventByStatus(isset($row['Realestateletter']['status']) ? $row['Realestateletter']['status'] : '');
            $this->_sendLetterSmsNotification($row, $event);
            $this->Session->setFlash(__('Letter SMS notification sent.'), 'flash', array('alert' => 'success'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Unable to send letter SMS notification.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect($this->_letterIndexRedirectUrlFromRow($row, $projectId));
    }

    public function sendemail($id = null)
    {
        if (empty($this->userPermissionArr['save_right']) && empty($this->userPermissionArr['update_right']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        $id = (int)$id;
        if ($id <= 0)
        {
            $this->Session->setFlash(__('Invalid letter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
        }
        $row = $this->_letterDetails($id, $projectId);
        if (empty($row['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid letter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
        }
        try
        {
            $event = $this->_letterNotificationEventByStatus(isset($row['Realestateletter']['status']) ? $row['Realestateletter']['status'] : '');
            $this->_sendLetterEmailNotification($row, $event);
            $this->Session->setFlash(__('Letter email notification sent.'), 'flash', array('alert' => 'success'));
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Unable to send letter email notification.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect($this->_letterIndexRedirectUrlFromRow($row, $projectId));
    }

    public function whatsapp($id = null)
    {
        if (empty($this->userPermissionArr['save_right']) && empty($this->userPermissionArr['update_right']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        $id = (int)$id;
        if ($id <= 0)
        {
            $this->Session->setFlash(__('Invalid letter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
        }
        $row = $this->_letterDetails($id, $projectId);
        if (empty($row['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid letter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
        }
        $event = $this->_letterNotificationEventByStatus(isset($row['Realestateletter']['status']) ? $row['Realestateletter']['status'] : '');
        $result = $this->_buildLetterWhatsappLink($row, $event);
        if (empty($result['success']))
        {
            $this->Session->setFlash($result['message'], 'flash', array('alert' => 'danger'));
            return $this->redirect($this->_letterIndexRedirectUrlFromRow($row, $projectId));
        }
        return $this->redirect($result['url']);
    }

    public function letterstatus($id = null, $status = null)
    {
        if (empty($this->userPermissionArr['update_right']))
        {
            $this->Session->setFlash(__('No Permission!'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index'));
        }
        $projectId = $this->_lettersSelectedProjectId($this->_lettersCanUseAgencyFilters() ? 0 : (int)$this->agencyId);
        $id = (int)$id;
        if ($id <= 0)
        {
            $this->Session->setFlash(__('Invalid letter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
        }
        $row = $this->_letterDetails($id, $projectId);
        if (empty($row['Realestateletter']['id']))
        {
            $this->Session->setFlash(__('Invalid letter.'), 'flash', array('alert' => 'danger'));
            return $this->redirect(array('action' => 'index', '?' => array('project_id' => $projectId)));
        }
        $currentStatus = isset($row['Realestateletter']['status']) ? trim((string)$row['Realestateletter']['status']) : 'Published';
        $statusText = strtolower(trim((string)$status));
        if ($statusText === 'published')
        {
            $newStatus = 'Published';
        }
        elseif ($statusText === 'unpublished')
        {
            $newStatus = 'Unpublished';
        }
        else
        {
            $newStatus = ($currentStatus === 'Published') ? 'Unpublished' : 'Published';
        }
        try
        {
            $this->Realestateletter->id = $id;
            if ($this->Realestateletter->saveField('status', $newStatus, array('validate' => false)))
            {
                if ($newStatus !== $currentStatus)
                {
                    $updatedRow = $this->_letterDetails($id, $projectId);
                    if (empty($updatedRow['Realestateletter']['id']))
                    {
                        $row['Realestateletter']['status'] = $newStatus;
                        $updatedRow = $row;
                    }
                    if ($newStatus === 'Published')
                    {
                        $this->_dispatchLetterNotifications($updatedRow, 'issue');
                    }
                    elseif ($newStatus === 'Unpublished')
                    {
                        $this->_dispatchLetterNotifications($updatedRow, 'cancel');
                    }
                }
                $this->Session->setFlash(__('Letter status updated.'), 'flash', array('alert' => 'success'));
            }
            else
            {
                $this->Session->setFlash(__('Unable to update letter status.'), 'flash', array('alert' => 'danger'));
            }
        }
        catch (Exception $e)
        {
            $this->Session->setFlash(__('Unable to update letter status.'), 'flash', array('alert' => 'danger'));
        }
        return $this->redirect($this->_letterIndexRedirectUrlFromRow($row, $projectId));
    }
}
?>


