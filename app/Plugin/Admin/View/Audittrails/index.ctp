<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Audit Trail');?></h1>
    </div>
</div>

<style>
.audit-export-wrap.is-pdf-export .no-pdf { display: none !important; }
.audit-detail-modal .modal-body pre { white-space: pre-wrap; word-break: break-word; }
.audit-request-values{margin:0;min-width:190px;}
.audit-request-value{display:flex;gap:6px;line-height:1.35;margin-bottom:3px;}
.audit-request-value strong{color:#334155;white-space:nowrap;}
.audit-request-value span{color:#111827;overflow-wrap:anywhere;}
.audit-request-more{font-size:11px;color:#64748b;font-weight:600;}
</style>

<div class="panel audit-export-wrap" id="audit-trail-export">
    <div class="panel-body">
        <?php echo $this->Session->flash();?>

        <div class="no-pdf" style="margin-bottom:15px;">
            <?php
                $exportCsvUrl = array('controller' => 'Audittrails', 'action' => 'exportcsv', '?' => $this->request->query);
            ?>
            <div class="btn-group">
                <?php echo $this->Html->link('<span class="fa fa-file-excel-o"></span>&nbsp;'.__('Export CSV'), $exportCsvUrl, array('escape' => false, 'class' => 'btn btn-info'));?>
                <a href="#" class="btn btn-success" onclick="return exportAuditTrailPdf();"><span class="fa fa-file-pdf-o"></span>&nbsp;<?php echo __('Export PDF');?></a>
            </div>
        </div>

        <?php echo $this->Form->create(false, array('type' => 'get', 'class' => 'form-inline no-pdf', 'style' => 'margin-bottom:15px;'));?>
        <div class="form-group">
            <label for="AuditTrailUserFilter"><?php echo __('User');?></label>
            <?php echo $this->Form->select('user_id', $userOptions, array('empty' => __('All Users'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'AuditTrailUserFilter', 'value' => $selectedUser));?>
        </div>
        <div class="form-group">
            <label for="AuditTrailAgencyFilter"><?php echo __('Agency');?></label>
            <?php echo $this->Form->select('project_id', $agencyOptions, array('empty' => __('All Agencies'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'AuditTrailAgencyFilter', 'value' => $selectedAgency));?>
        </div>
        <div class="form-group">
            <label for="AuditTrailActivityFilter"><?php echo __('Activity');?></label>
            <?php echo $this->Form->select('activity', $activityOptions, array('empty' => __('All Activities'), 'label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'AuditTrailActivityFilter', 'value' => $selectedActivity));?>
        </div>
        <div class="form-group">
            <label for="AuditTrailControllerFilter"><?php echo __('Controller');?></label>
            <?php echo $this->Form->text('controller', array('label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'AuditTrailControllerFilter', 'value' => $controllerFilter, 'placeholder' => __('Controller')));?>
        </div>
        <div class="form-group">
            <label for="AuditTrailStart"><?php echo __('Start');?></label>
            <?php echo $this->Form->text('start', array('label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'AuditTrailStart', 'value' => $startDate, 'placeholder' => __('YYYY-MM-DD')));?>
        </div>
        <div class="form-group">
            <label for="AuditTrailEnd"><?php echo __('End');?></label>
            <?php echo $this->Form->text('end', array('label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'AuditTrailEnd', 'value' => $endDate, 'placeholder' => __('YYYY-MM-DD')));?>
        </div>
        <div class="form-group">
            <label for="AuditTrailKeyword"><?php echo __('Search');?></label>
            <?php echo $this->Form->text('q', array('label' => false, 'class' => 'form-control', 'div' => false, 'id' => 'AuditTrailKeyword', 'value' => $keyword, 'placeholder' => __('URL, IP, Data')));?>
        </div>
        <button type="submit" class="btn btn-success"><span class="fa fa-filter"></span> <?php echo __('Apply');?></button>
        <?php echo $this->Html->link('<span class="fa fa-refresh"></span> '.__('Reset'), array('action' => 'index'), array('escape' => false, 'class' => 'btn btn-default'));?>
        <?php echo $this->Form->end();?>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('User');?></th>
                        <th><?php echo __('Agency');?></th>
                        <th><?php echo __('Activity');?></th>
                        <th><?php echo __('Controller');?></th>
                        <th><?php echo __('Action');?></th>
                        <th><?php echo __('Method');?></th>
                        <th><?php echo __('IP');?></th>
                        <th><?php echo __('URL');?></th>
                        <th><?php echo __('Request');?></th>
                        <th class="no-pdf"><?php echo __('Details');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($auditRows)){ ?>
                    <tr><td colspan="11" class="text-center"><?php echo __('No audit records found.');?></td></tr>
                    <?php } ?>
                    <?php foreach((array)$auditRows as $row){
                        $log = (array)$row['Audittrail'];
                        $userName = !empty($row['User']['name']) ? $row['User']['name'] : '';
                        $agencyName = !empty($row['Agency']['name']) ? $row['Agency']['name'] : '';
                        $auditId = !empty($log['id']) ? (int)$log['id'] : 0;
                        $requestDisplay = !empty($auditRequestDisplays[$auditId]) ? (array)$auditRequestDisplays[$auditId] : array('items'=>array(),'preview'=>__('No request values'),'text'=>__('No request values'));
                        $requestItems = !empty($requestDisplay['items']) ? (array)$requestDisplay['items'] : array();
                        $encodedRequest = base64_encode((string)$requestDisplay['text']);
                    ?>
                    <tr>
                        <td><?php echo !empty($log['created']) ? h(date('d-M-Y H:i:s',strtotime($log['created']))) : '-';?></td>
                        <td><?php echo h($userName);?></td>
                        <td><?php echo h($agencyName);?></td>
                        <td><?php echo h(isset($activityOptions[$log['activity']]) ? $activityOptions[$log['activity']] : $log['activity']);?></td>
                        <td><?php echo h(isset($log['controller']) ? $log['controller'] : '');?></td>
                        <td><?php echo h(isset($log['action']) ? $log['action'] : '');?></td>
                        <td><?php echo h(isset($log['method']) ? $log['method'] : '');?></td>
                        <td><?php echo h(isset($log['ip_address']) ? $log['ip_address'] : '');?></td>
                        <td style="max-width:260px; white-space:normal;"><?php echo h(isset($log['url']) ? $log['url'] : '');?></td>
                        <td style="max-width:320px; white-space:normal;">
                            <div class="audit-request-values">
                            <?php if(empty($requestItems)){ ?>
                                <span class="text-muted"><?php echo h($requestDisplay['preview']);?></span>
                            <?php } else { ?>
                                <?php foreach(array_slice($requestItems, 0, 3) as $requestItem){ ?>
                                <div class="audit-request-value"><strong><?php echo h($requestItem['label']);?>:</strong><span><?php echo h($requestItem['value']);?></span></div>
                                <?php } ?>
                                <?php if(count($requestItems)>3){ ?><div class="audit-request-more">+<?php echo (int)(count($requestItems)-3);?> <?php echo __('more values');?></div><?php } ?>
                            <?php } ?>
                            </div>
                        </td>
                        <td class="no-pdf">
                            <button type="button"
                                class="btn btn-xs btn-primary js-audit-detail"
                                data-created="<?php echo !empty($log['created']) ? h(date('d-M-Y H:i:s',strtotime($log['created']))) : '-';?>"
                                data-user="<?php echo h($userName);?>"
                                data-agency="<?php echo h($agencyName);?>"
                                data-activity="<?php echo h(isset($log['activity']) ? $log['activity'] : '');?>"
                                data-controller="<?php echo h(isset($log['controller']) ? $log['controller'] : '');?>"
                                data-action="<?php echo h(isset($log['action']) ? $log['action'] : '');?>"
                                data-method="<?php echo h(isset($log['method']) ? $log['method'] : '');?>"
                                data-ip="<?php echo h(isset($log['ip_address']) ? $log['ip_address'] : '');?>"
                                data-url="<?php echo h(isset($log['url']) ? $log['url'] : '');?>"
                                data-request="<?php echo h($encodedRequest);?>">
                                <?php echo __('View');?>
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <?php if(!empty($auditRows)){ ?>
        <div class="text-center">
            <ul class="pagination">
                <?php echo $this->Paginator->prev('&laquo;', array('tag' => 'li', 'escape' => false), '<a href="javascript:void(0);">&laquo;</a>', array('tag' => 'li', 'escape' => false, 'class' => 'disabled'));?>
                <?php echo $this->Paginator->numbers(array('tag' => 'li', 'currentTag' => 'a', 'currentClass' => 'active', 'separator' => ''));?>
                <?php echo $this->Paginator->next('&raquo;', array('tag' => 'li', 'escape' => false), '<a href="javascript:void(0);">&raquo;</a>', array('tag' => 'li', 'escape' => false, 'class' => 'disabled'));?>
            </ul>
        </div>
        <?php } ?>
    </div>
</div>

<div class="modal fade audit-detail-modal" id="auditDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo __('Audit Detail');?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4"><strong><?php echo __('User');?>:</strong> <span class="js-audit-user"></span></div>
                    <div class="col-sm-4"><strong><?php echo __('Agency');?>:</strong> <span class="js-audit-agency"></span></div>
                    <div class="col-sm-4"><strong><?php echo __('Activity');?>:</strong> <span class="js-audit-activity"></span></div>
                </div>
                <div class="row" style="margin-top:6px;">
                    <div class="col-sm-4"><strong><?php echo __('Controller');?>:</strong> <span class="js-audit-controller"></span></div>
                    <div class="col-sm-4"><strong><?php echo __('Action');?>:</strong> <span class="js-audit-action"></span></div>
                    <div class="col-sm-4"><strong><?php echo __('Method');?>:</strong> <span class="js-audit-method"></span></div>
                </div>
                <div class="row" style="margin-top:6px;">
                    <div class="col-sm-4"><strong><?php echo __('IP');?>:</strong> <span class="js-audit-ip"></span></div>
                    <div class="col-sm-8"><strong><?php echo __('URL');?>:</strong> <span class="js-audit-url"></span></div>
                </div>
                <hr />
                <strong><?php echo __('Request Values');?></strong>
                <pre class="js-audit-request"></pre>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
function exportAuditTrailPdf(){
    if(typeof html2pdf === 'undefined'){
        alert('PDF library is not loaded.');
        return false;
    }
    var node = document.getElementById('audit-trail-export');
    if(!node){ return false; }
    node.classList.add('is-pdf-export');
    var stamp = (new Date()).toISOString().replace(/[-:T]/g,'').slice(0,14);
    html2pdf().set({
        margin: 0.2,
        filename: 'audit_trail_' + stamp + '.pdf',
        html2canvas: { scale: 2, useCORS: true },
        pagebreak: { mode: ['avoid-all','css','legacy'] }
    }).from(node).save().then(function(){
        node.classList.remove('is-pdf-export');
    }).catch(function(){
        node.classList.remove('is-pdf-export');
    });
    return false;
}

if(typeof jQuery !== 'undefined'){
    jQuery(function($){
        $('.js-audit-detail').on('click', function(){
            var $btn = $(this);
            var requestRaw = '';
            var encoded = $btn.data('request');
            if(encoded){
                try { requestRaw = atob(encoded); } catch(e){ requestRaw = ''; }
            }
            $('#auditDetailModal .js-audit-user').text($btn.data('user') || '');
            $('#auditDetailModal .js-audit-agency').text($btn.data('agency') || '');
            $('#auditDetailModal .js-audit-activity').text($btn.data('activity') || '');
            $('#auditDetailModal .js-audit-controller').text($btn.data('controller') || '');
            $('#auditDetailModal .js-audit-action').text($btn.data('action') || '');
            $('#auditDetailModal .js-audit-method').text($btn.data('method') || '');
            $('#auditDetailModal .js-audit-ip').text($btn.data('ip') || '');
            $('#auditDetailModal .js-audit-url').text($btn.data('url') || '');
            $('#auditDetailModal .js-audit-request').text(requestRaw || '-');
            $('#auditDetailModal').modal('show');
        });
    });
}
</script>
