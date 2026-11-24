<?php
$moduleDocs = array(
    'Dashboards' => array('icon'=>'fa-dashboard','purpose'=>__('Daily command center for totals, payments, dues, notices, and quick navigation.'),'use'=>array(__('Review today totals before starting work.'),__('Open deals, payments, reports, and client shortcuts from one place.'),__('Use dashboard cards as first check after login.'))),
    'Calendars' => array('icon'=>'fa-calendar','purpose'=>__('Follow-up calendar for schedules, visits, reminders, and team planning.'),'use'=>array(__('Add schedule with clear title and date.'),__('Keep follow-up status updated after calls or visits.'),__('Use calendar view to avoid missing client commitments.'))),
    'Contacts' => array('icon'=>'fa-address-book','purpose'=>__('Client/customer master records with phone, email, address, documents, and deal links.'),'use'=>array(__('Create or update client details before making deals.'),__('Keep mobile, CNIC, and address clean for letters and receipts.'),__('Avoid duplicate contacts when a lead already exists.'))),
    'Purchases' => array('icon'=>'fa-shopping-cart','purpose'=>__('Purchase records and purchase payment handling.'),'use'=>array(__('Record purchase master information first.'),__('Use purchase payment pages for amount tracking.'),__('Review reports before account closing.'))),
    'Properties' => array('icon'=>'fa-building','purpose'=>__('Property inventory, maps, gallery, pricing, nearby places, frontend display, and sales status.'),'use'=>array(__('Complete title, type, location, price, area, and map before publishing.'),__('Upload property images and documents for better frontend presentation.'),__('Use edit page carefully because property changes affect deals and website listings.'))),
    'Leads' => array('icon'=>'fa-random','purpose'=>__('CRM lead capture, follow-up, kanban board, bulk import, automation, routing, and progress reports.'),'use'=>array(__('Use CRM Dashboard for daily status overview.'),__('Assign leads to the correct user or agency.'),__('Update remarks and follow-up date after every call.'),__('Use duplicate detector before creating repeated leads.'))),
    'Deals' => array('icon'=>'fa-file-text','purpose'=>__('Deals, installments, payments, ledger PDFs, receipts, transfers, and refunds.'),'use'=>array(__('Create deal only after client and property details are correct.'),__('Use Deal Payments for receipts and ledgers.'),__('Use refund and transfer modules for status changes instead of manual edits.'))),
    'Ardaccounts' => array('icon'=>'fa-balance-scale','purpose'=>__('Accounts dashboard, ledgers, journal vouchers, vendors, construction, payroll, reports, and backups.'),'use'=>array(__('Use ledgers and vouchers for daily accounting.'),__('Check cash closing before end-of-day reporting.'),__('Run backups before major restore or cleanup.'))),
    'Realestateletters' => array('icon'=>'fa-envelope-open','purpose'=>__('Letter templates, issued letters, print views, QR verification, and approval workflow.'),'use'=>array(__('Create templates with placeholders intact.'),__('Use approval flow before issuing sensitive letters.'),__('Print issued letters from the approved print view for audit safety.'))),
    'Balloting' => array('icon'=>'fa-random','purpose'=>__('Units, allotments, manual allotment, balloting run, imports, and balloting reports.'),'use'=>array(__('Import or create units first.'),__('Review settings before running balloting.'),__('Use reports to verify allotment results.'))),
    'Invpastdues' => array('icon'=>'fa-exclamation-circle','purpose'=>__('Overdue installments, demand notices, SMS/email reminders, and overdue reports.'),'use'=>array(__('Filter dues by date and project.'),__('Send demand letter, email, or SMS from the overdue page.'),__('Use the same SMS settings as the invoice reminder process.'))),
    'Invpaids' => array('icon'=>'fa-check-circle','purpose'=>__('Paid invoices and receipt-level payment review.'),'use'=>array(__('Confirm payment receipts and invoice status.'),__('Use paid invoice reports for collections audit.'),__('Export or print as required for accounts.'))),
    'Businessfeatures' => array('icon'=>'fa-rocket','purpose'=>__('Growth dashboards for SLA, source ROI, client journey, property explorer, overdue, and portal action insights.'),'use'=>array(__('Use these pages for management-level performance review.'),__('Compare lead sources and agent activity.'),__('Track client journey and overdue risk.'))),
    'Dealrefundrequests' => array('icon'=>'fa-undo','purpose'=>__('Client refund application workflow with required documents, review, approval, deduction, and refund conversion.'),'use'=>array(__('Admin selects deals allowed for client refund application.'),__('Review required attachments and client appeal history.'),__('Approve with deductions, then create refund schedule safely.'))),
    'Communicationautomations' => array('icon'=>'fa-comments','purpose'=>__('Automation rules, message templates, bulk send center, queue, logs, and communication timeline.'),'use'=>array(__('Create templates before automation rules.'),__('Preview recipients before bulk sending.'),__('Use logs and timeline for audit.'))),
    'Documentvaults' => array('icon'=>'fa-lock','purpose'=>__('Client document vault for receipts, letters, ledgers, invoices, agreements, and refund clearances.'),'use'=>array(__('Publish only documents the client should download.'),__('Keep files attached to the correct client/deal.'),__('Use vault as client portal document source.'))),
    'Recoveries' => array('icon'=>'fa-phone','purpose'=>__('Payment recovery follow-up and collection management.'),'use'=>array(__('Assign recovery work to the responsible user.'),__('Update recovery remarks after each contact.'),__('Track pending and recovered amounts.'))),
    'Paymentreminders' => array('icon'=>'fa-bell','purpose'=>__('Automated payment reminders through email/SMS/WhatsApp queue and reminder logs.'),'use'=>array(__('Configure reminder rules and templates.'),__('Check queue before sending.'),__('Review logs for failed messages.'))),
    'Seoassistants' => array('icon'=>'fa-search','purpose'=>__('SEO title, meta, and description suggestions for properties and agencies.'),'use'=>array(__('Generate SEO suggestions after property/agency details are complete.'),__('Review before applying to avoid wrong keywords.'),__('Use concise titles and readable descriptions.'))),
    'Leadduplicates' => array('icon'=>'fa-copy','purpose'=>__('Duplicate lead detection by mobile/email and previous inquiry history.'),'use'=>array(__('Search duplicate before adding a new inquiry.'),__('Review previous history before assigning.'),__('Merge or update instead of creating repeated records where possible.'))),
    'Agentperformances' => array('icon'=>'fa-line-chart','purpose'=>__('Agent and agency performance dashboard for leads, views, calls, WhatsApp clicks, deals, and conversion.'),'use'=>array(__('Use date filters for fair performance comparison.'),__('Review conversion rate along with lead volume.'),__('Use assigned properties and deals to measure actual output.'))),
    'Addresshierarchies' => array('icon'=>'fa-map-signs','purpose'=>__('Pakistan address hierarchy: country, province, district, tehsil, city, town, and block.'),'use'=>array(__('Add addresses level by level.'),__('Use import with sample CSV for bulk data.'),__('Keep hierarchy clean because contacts and properties reuse it.'))),
    'Websitecontacts' => array('icon'=>'fa-globe','purpose'=>__('Website contact messages and property inquiries from frontend forms.'),'use'=>array(__('Reply by Email, SMS, or WhatsApp from the action menu.'),__('Mark messages read after response.'),__('Property inquiries can be edited/deleted by super admin.'))),
    'Users' => array('icon'=>'fa-users','purpose'=>__('System users, agencies, agents, and rights/access management.'),'use'=>array(__('Create users with the correct group and agency.'),__('Assign rights carefully before handing login access.'),__('Keep agent/agency records verified and updated.'))),
    'Configurations' => array('icon'=>'fa-cogs','purpose'=>__('General configuration, logo, templates, currency, plans, statuses, taxes, types, units, theme, and backups.'),'use'=>array(__('Review settings before changing live behavior.'),__('Use backups before major configuration changes.'),__('Keep templates and organisation logo updated.'))),
    'Emailsettings' => array('icon'=>'fa-envelope','purpose'=>__('Email SMTP/Mail settings, email templates, and send email pages.'),'use'=>array(__('Test SMTP after changing host or password.'),__('Use templates for consistent messages.'),__('Keep sender name and email professional.'))),
    'Smssettings' => array('icon'=>'fa-comment','purpose'=>__('SMS gateway settings, SMS templates, and send SMS page.'),'use'=>array(__('Keep API URL, username, password, sender, and parameter names correct.'),__('Use invoice SMS page as known working reference.'),__('Review failed SMS messages from the gateway response.'))),
    'MediaGalleries' => array('icon'=>'fa-image','purpose'=>__('Media gallery management for reusable images and visual assets.'),'use'=>array(__('Upload optimized images.'),__('Use meaningful titles for search.'),__('Avoid duplicate large images.'))),
    'Audittrails' => array('icon'=>'fa-shield','purpose'=>__('Audit trail for important backend actions and history review.'),'use'=>array(__('Use audit trail to investigate changes.'),__('Filter by user/date where available.'),__('Keep audit data for accountability.')))
);

$defaultDoc = array('icon'=>'fa-circle-o','purpose'=>__('Backend module available from current admin menu.'),'use'=>array(__('Open the module from the sidebar.'),__('Use filters before making bulk changes.'),__('Save only after checking required fields.')));

$workflowDocs = array(
    array('title'=>__('Daily Admin Workflow'),'icon'=>'fa-sitemap','steps'=>array(__('Open Dashboard and check dues, payments, and pending tasks.'),__('Review Website Inquiries and CRM Leads.'),__('Update follow-ups, recovery remarks, and client requests.'),__('Check payments, receipts, ledgers, and overdue installments.'),__('Review reports and audit trail before closing day.'))),
    array('title'=>__('Property To Deal Workflow'),'icon'=>'fa-building-o','steps'=>array(__('Create or update property with location, images, nearby places, and documents.'),__('Create client/contact record with accurate mobile, CNIC, and address.'),__('Create deal with correct plan and installment schedule.'),__('Receive payments through Deal Payments and print receipts.'),__('Client can view documents, ledger PDFs, and eligible refund status from portal.'))),
    array('title'=>__('Letter And Document Workflow'),'icon'=>'fa-file-pdf-o','steps'=>array(__('Prepare templates with correct placeholders.'),__('Create draft letter and send for approval where required.'),__('Issue approved letter with QR verification.'),__('Print from official print view.'),__('Publish client copies to document vault when needed.'))),
    array('title'=>__('Refund Workflow'),'icon'=>'fa-undo','steps'=>array(__('Admin enables allowed deals for refund request.'),__('Client submits step-by-step application with required documents.'),__('Admin reviews, rejects for appeal, or approves with deductions.'),__('Approved request converts into deal refund and schedule.'),__('Client can view refund schedule and paid/pending status only.'))),
    array('title'=>__('Communication Workflow'),'icon'=>'fa-comments-o','steps'=>array(__('Keep email/SMS/WhatsApp templates updated.'),__('Preview automation rules before enabling.'),__('Use queue and logs to verify delivery.'),__('Review communication timeline for client/deal history.'),__('Fix gateway settings if SMS/email fails.')))
);

function wv_help_slug($text)
{
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i','-',(string)$text));
    return trim($slug,'-');
}

function wv_help_url($page)
{
    $controller = trim((string)(isset($page['controller_name']) ? $page['controller_name'] : ''));
    $action = trim((string)(isset($page['action_name']) ? $page['action_name'] : ''));
    if($controller === '' || $controller === '#')
    {
        return '';
    }
    return array('controller'=>$controller,'action'=>($action !== '' ? $action : 'index'));
}
?>
<style>
.wv-help-page{background:#f4f7fb;margin:-10px -10px 0;padding:18px;}
.wv-help-hero{align-items:center;background:linear-gradient(135deg,#07152a,#102746 62%,#ff0a46 150%);border-radius:12px;box-shadow:0 18px 38px rgba(15,23,42,.16);color:#fff;display:flex;justify-content:space-between;margin-bottom:16px;padding:24px;}
.wv-help-hero h1{color:#fff;font-size:30px;font-weight:900;margin:0 0 5px;}
.wv-help-hero p{color:#dbeafe;font-size:13px;font-weight:700;margin:0;}
.wv-help-pill{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);border-radius:999px;color:#fff;font-size:12px;font-weight:900;padding:10px 14px;white-space:nowrap;}
.wv-help-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px;}
.wv-stat{background:#fff;border:1px solid #dbe5f2;border-radius:10px;box-shadow:0 10px 24px rgba(15,23,42,.06);padding:14px;}
.wv-stat span{color:#64748b;display:block;font-size:11px;font-weight:900;text-transform:uppercase;}
.wv-stat strong{color:#07152a;display:block;font-size:24px;font-weight:900;margin-top:4px;}
.wv-help-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px;}
.wv-module{background:#fff;border:1px solid #dbe5f2;border-radius:10px;box-shadow:0 10px 24px rgba(15,23,42,.06);display:flex;flex-direction:column;min-height:230px;overflow:hidden;}
.wv-module-head{align-items:flex-start;display:flex;gap:12px;padding:15px;}
.wv-module-icon{align-items:center;background:#fff0f4;border:1px solid #ffd0dc;border-radius:10px;color:#ff0a46;display:flex;flex:0 0 44px;font-size:18px;height:44px;justify-content:center;width:44px;}
.wv-module h3{color:#07152a;font-size:17px;font-weight:900;line-height:1.2;margin:0 0 5px;}
.wv-module p{color:#64748b;font-size:12px;font-weight:700;line-height:1.45;margin:0;}
.wv-module-body{padding:0 15px 15px;}
.wv-module ul{display:grid;gap:7px;list-style:none;margin:0;padding:0;}
.wv-module li{color:#334155;font-size:12px;font-weight:800;line-height:1.35;padding-left:20px;position:relative;}
.wv-module li:before{color:#047857;content:"\f00c";font-family:FontAwesome;font-size:11px;left:0;position:absolute;top:1px;}
.wv-child-links{border-top:1px solid #edf2f7;display:flex;flex-wrap:wrap;gap:7px;margin-top:auto;padding:12px 15px 15px;}
.wv-child-links a,.wv-child-links span{background:#eff6ff;border-radius:999px;color:#0f2742!important;font-size:11px;font-weight:900;padding:7px 9px;text-decoration:none;}
.wv-open-main{background:#07152a!important;color:#fff!important;}
.wv-section-title{align-items:center;color:#07152a;display:flex;font-size:22px;font-weight:900;gap:10px;margin:22px 0 12px;}
.wv-workflows{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:18px;}
.wv-workflow{background:#fff;border:1px solid #dbe5f2;border-radius:10px;box-shadow:0 10px 24px rgba(15,23,42,.06);padding:16px;}
.wv-workflow h3{align-items:center;color:#07152a;display:flex;font-size:17px;font-weight:900;gap:9px;margin:0 0 12px;}
.wv-workflow ol{counter-reset:step;display:grid;gap:8px;margin:0;padding:0;}
.wv-workflow li{align-items:flex-start;background:#f8fafc;border:1px solid #e5edf7;border-radius:8px;color:#334155;display:flex;font-size:12px;font-weight:800;gap:9px;line-height:1.4;list-style:none;padding:9px;}
.wv-workflow li:before{background:#ff0a46;border-radius:50%;color:#fff;content:counter(step);counter-increment:step;flex:0 0 22px;font-size:11px;font-weight:900;height:22px;line-height:22px;text-align:center;width:22px;}
.wv-shot-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px;}
.wv-shot{background:#fff;border:1px solid #dbe5f2;border-radius:10px;box-shadow:0 10px 24px rgba(15,23,42,.06);overflow:hidden;}
.wv-shot-frame{align-items:center;background:linear-gradient(135deg,#e2e8f0,#f8fafc);border-bottom:1px solid #dbe5f2;display:flex;height:150px;justify-content:center;position:relative;}
.wv-shot-frame img{height:100%;object-fit:cover;width:100%;}
.wv-shot-placeholder{text-align:center;color:#64748b;font-size:12px;font-weight:900;}
.wv-shot-placeholder i{color:#ff0a46;display:block;font-size:30px;margin-bottom:8px;}
.wv-shot-body{padding:12px;}
.wv-shot-body h4{color:#07152a;font-size:14px;font-weight:900;margin:0 0 5px;}
.wv-shot-body p{color:#64748b;font-size:11px;font-weight:700;line-height:1.4;margin:0;}
.wv-support{background:#07152a;border-radius:12px;color:#fff;display:grid;grid-template-columns:1fr auto;gap:14px;margin-top:18px;padding:18px;}
.wv-support h3{color:#fff;font-size:18px;font-weight:900;margin:0 0 5px;}
.wv-support p{color:#dbeafe;font-size:12px;font-weight:700;margin:0;}
.wv-support a{background:#ff0a46;border-radius:8px;color:#fff!important;font-size:12px;font-weight:900;padding:10px 13px;text-decoration:none;}
@media(max-width:1180px){.wv-help-grid,.wv-shot-grid{grid-template-columns:repeat(2,minmax(0,1fr));}.wv-workflows{grid-template-columns:1fr;}}
@media(max-width:760px){.wv-help-page{margin:0;padding:12px}.wv-help-hero,.wv-support{display:block}.wv-help-pill{display:inline-block;margin-top:12px}.wv-help-stats,.wv-help-grid,.wv-shot-grid{grid-template-columns:1fr}.wv-help-hero h1{font-size:24px}.wv-support a{display:inline-block;margin-top:12px}}
</style>

<div class="wv-help-page">
    <?php echo $this->Session->flash();?>
    <div class="wv-help-hero">
        <div>
            <h1><i class="fa fa-life-ring"></i> <?php echo __('White Villas Help Center');?></h1>
            <p><?php echo __('Complete backend documentation generated from your current super admin modules.');?></p>
        </div>
        <span class="wv-help-pill"><i class="fa fa-refresh"></i> <?php echo __('Updated');?>: <?php echo h($helpUpdatedOn);?></span>
    </div>

    <div class="wv-help-stats">
        <div class="wv-stat"><span><?php echo __('Main Modules');?></span><strong><?php echo count($rootRows);?></strong></div>
        <div class="wv-stat"><span><?php echo __('Sub Pages');?></span><strong><?php $childCount=0; foreach($childrenByParent as $items){$childCount+=count($items);} echo (int)$childCount;?></strong></div>
        <div class="wv-stat"><span><?php echo __('Documentation');?></span><strong><?php echo count($workflowDocs);?></strong></div>
        <div class="wv-stat"><span><?php echo __('Access');?></span><strong><?php echo __('Super');?></strong></div>
    </div>

    <h2 class="wv-section-title"><i class="fa fa-th-large"></i> <?php echo __('Current Backend Modules');?></h2>
    <div class="wv-help-grid">
        <?php foreach($rootRows as $row): ?>
            <?php
            $page = (array)$row['Page'];
            $controller = trim((string)$page['controller_name']);
            $doc = isset($moduleDocs[$controller]) ? $moduleDocs[$controller] : $defaultDoc;
            if($controller === '' && strtolower((string)$page['page_name']) === 'invoices') { $doc = isset($moduleDocs['Invpastdues']) ? $moduleDocs['Invpastdues'] : $defaultDoc; }
            if($controller === '' && strtolower((string)$page['page_name']) === 'email & sms') { $doc = isset($moduleDocs['Smssettings']) ? $moduleDocs['Smssettings'] : $defaultDoc; }
            if($controller === '' && strtolower((string)$page['page_name']) === 'user') { $doc = isset($moduleDocs['Users']) ? $moduleDocs['Users'] : $defaultDoc; }
            if($controller === '' && strtolower((string)$page['page_name']) === 'configurations') { $doc = isset($moduleDocs['Configurations']) ? $moduleDocs['Configurations'] : $defaultDoc; }
            $mainUrl = wv_help_url($page);
            $children = !empty($childrenByParent[(int)$page['id']]) ? $childrenByParent[(int)$page['id']] : array();
            ?>
            <div class="wv-module" id="module-<?php echo h(wv_help_slug($page['page_name']));?>">
                <div class="wv-module-head">
                    <div class="wv-module-icon"><i class="fa <?php echo h($doc['icon']);?>"></i></div>
                    <div>
                        <h3><?php echo h($page['page_name']);?></h3>
                        <p><?php echo h($doc['purpose']);?></p>
                    </div>
                </div>
                <div class="wv-module-body">
                    <ul>
                        <?php foreach((array)$doc['use'] as $tip): ?>
                            <li><?php echo h($tip);?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="wv-child-links">
                    <?php if(!empty($mainUrl)): ?>
                        <?php echo $this->Html->link('<i class="fa fa-external-link"></i> '.__('Open'), $mainUrl, array('escape'=>false,'class'=>'wv-open-main'));?>
                    <?php endif; ?>
                    <?php foreach(array_slice($children,0,6) as $child): ?>
                        <?php $childPage=(array)$child['Page']; $childUrl=wv_help_url($childPage); ?>
                        <?php if(!empty($childUrl)): ?>
                            <?php echo $this->Html->link(h($childPage['page_name']), $childUrl, array('escape'=>false));?>
                        <?php else: ?>
                            <span><?php echo h($childPage['page_name']);?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if(count($children)>6): ?><span>+<?php echo count($children)-6;?> <?php echo __('more');?></span><?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="wv-section-title"><i class="fa fa-book"></i> <?php echo __('Core Workflows');?></h2>
    <div class="wv-workflows">
        <?php foreach($workflowDocs as $workflow): ?>
            <div class="wv-workflow">
                <h3><i class="fa <?php echo h($workflow['icon']);?>"></i> <?php echo h($workflow['title']);?></h3>
                <ol>
                    <?php foreach($workflow['steps'] as $step): ?><li><?php echo h($step);?></li><?php endforeach; ?>
                </ol>
            </div>
        <?php endforeach; ?>
    </div>

    <h2 class="wv-section-title"><i class="fa fa-camera"></i> <?php echo __('Screenshots & Visual Guides');?></h2>
    <div class="wv-shot-grid">
        <?php foreach(array_slice($rootRows,0,12) as $row): ?>
            <?php
            $page = (array)$row['Page'];
            $slug = wv_help_slug($page['controller_name'] !== '' ? $page['controller_name'].'-'.$page['action_name'] : $page['page_name']);
            $shotFile = 'help/'.$slug.'.png';
            $shotPath = WWW_ROOT.'img'.DS.'help'.DS.$slug.'.png';
            ?>
            <div class="wv-shot">
                <div class="wv-shot-frame">
                    <?php if(file_exists($shotPath)): ?>
                        <?php echo $this->Html->image($shotFile,array('alt'=>$page['page_name']));?>
                    <?php else: ?>
                        <div class="wv-shot-placeholder"><i class="fa fa-picture-o"></i><?php echo __('Screenshot Slot');?><br><?php echo h($page['page_name']);?></div>
                    <?php endif; ?>
                </div>
                <div class="wv-shot-body">
                    <h4><?php echo h($page['page_name']);?></h4>
                    <p><?php echo __('Place screenshot at');?> <strong>app/webroot/img/help/<?php echo h($slug);?>.png</strong></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="wv-support">
        <div>
            <h3><?php echo __('Support Notes');?></h3>
            <p><?php echo __('When reporting an issue, share the module name, page URL, screenshot, exact error text, and expected result.');?></p>
        </div>
        <?php echo $this->Html->link('<i class="fa fa-globe"></i> '.__('Open Website Inquiries'),array('controller'=>'Websitecontacts','action'=>'index'),array('escape'=>false));?>
    </div>
</div>
