<?php
/**
 * Admin Payment Paid Requests View
 */
?>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-primary">
            <div class="box-header with-border" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <h3 class="box-title" style="font-weight:800;font-size:18px;">
                    <i class="fa fa-credit-card text-primary"></i> <?php echo __('Payment Paid Requests (Online Submissions)'); ?>
                </h3>
                <div class="box-tools">
                    <form method="get" class="form-inline">
                        <input type="hidden" name="status" value="<?php echo h($statusFilter); ?>" />
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="q" class="form-control pull-right" placeholder="<?php echo __('Search TID, Bill # or Client...'); ?>" value="<?php echo h(!empty($this->request->query['q']) ? $this->request->query['q'] : ''); ?>">
                            <div class="input-group-btn">
                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KPI Row Cards -->
            <div class="box-body" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;padding:16px;">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-aqua">
                            <span class="info-box-icon"><i class="fa fa-file-text-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Submitted</span>
                                <span class="info-box-number"><?php echo (int)$kpis['total']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-yellow">
                            <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Pending Verification</span>
                                <span class="info-box-number"><?php echo (int)$kpis['pending']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Confirmed & Posted</span>
                                <span class="info-box-number"><?php echo (int)$kpis['confirmed']; ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 col-xs-12">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="fa fa-times-circle"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Rejected Requests</span>
                                <span class="info-box-number"><?php echo (int)$kpis['rejected']; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Filter Pills -->
                <div style="display:flex;gap:8px;margin-top:10px;">
                    <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('status' => 'all'))); ?>" class="btn btn-sm <?php echo ($statusFilter === 'all') ? 'btn-primary' : 'btn-default'; ?>">
                        All Requests (<?php echo $kpis['total']; ?>)
                    </a>
                    <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('status' => 'pending'))); ?>" class="btn btn-sm <?php echo ($statusFilter === 'pending') ? 'btn-warning' : 'btn-default'; ?>">
                        <i class="fa fa-clock-o"></i> Pending (<?php echo $kpis['pending']; ?>)
                    </a>
                    <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('status' => 'confirmed'))); ?>" class="btn btn-sm <?php echo ($statusFilter === 'confirmed') ? 'btn-success' : 'btn-default'; ?>">
                        <i class="fa fa-check"></i> Confirmed (<?php echo $kpis['confirmed']; ?>)
                    </a>
                    <a href="<?php echo $this->Html->url(array('action' => 'index', '?' => array('status' => 'rejected'))); ?>" class="btn btn-sm <?php echo ($statusFilter === 'rejected') ? 'btn-danger' : 'btn-default'; ?>">
                        <i class="fa fa-times"></i> Rejected (<?php echo $kpis['rejected']; ?>)
                    </a>
                </div>
            </div>

            <!-- Table Body -->
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover table-striped table-bordered" style="vertical-align:middle;">
                    <thead>
                        <tr style="background:#f1f5f9;color:#334155;">
                            <th>ID & Date</th>
                            <th>Consumer & Property</th>
                            <th>Bill # & Period</th>
                            <th>Payment Method & TID</th>
                            <th class="text-right">Amount (PKR)</th>
                            <th class="text-center">Proof Screenshot</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" style="width:220px;white-space:nowrap;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($requests)){ ?>
                            <?php foreach($requests as $r){ 
                                $rId = $r['BillingPaymentRef']['id'];
                                $b = !empty($r['BillingBill']) ? $r['BillingBill'] : array();
                                $c = !empty($r['BillingClient']) ? $r['BillingClient'] : array();
                                $p = !empty($r['BillingProperty']) ? $r['BillingProperty'] : array();
                                $st = $r['BillingPaymentRef']['status'];
                            ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo $rId; ?></strong><br>
                                        <small class="text-muted"><?php echo date('d-M-Y H:i', strtotime($r['BillingPaymentRef']['submission_date'])); ?></small>
                                    </td>
                                    <td>
                                        <strong style="color:#0f172a;"><?php echo h(!empty($c['name']) ? $c['name'] : '-'); ?></strong><br>
                                        <small class="label label-info">WV70<?php echo !empty($c['id']) ? str_pad($c['id'], 2, '0', STR_PAD_LEFT) : '00'; ?></small>
                                        <small class="text-muted"><?php echo h(!empty($p['property_no']) ? $p['property_no'] : '-'); ?></small>
                                    </td>
                                    <td>
                                        <strong style="font-family:monospace;color:#0284c7;"><?php echo h(!empty($b['bill_number']) ? $b['bill_number'] : '-'); ?></strong><br>
                                        <small class="text-muted"><?php echo !empty($b['billing_period']) ? date('M-Y', strtotime($b['billing_period'] . '-01')) : '-'; ?></small>
                                    </td>
                                    <td>
                                        <span class="label label-default"><?php echo h(!empty($r['BillingPaymentRef']['payment_method']) ? $r['BillingPaymentRef']['payment_method'] : 'Online'); ?></span><br>
                                        <strong style="font-family:monospace;color:#0f172a;"><?php echo h($r['BillingPaymentRef']['transaction_ref']); ?></strong>
                                    </td>
                                    <td class="text-right">
                                        <strong style="font-size:15px;color:#16a34a;">PKR <?php echo number_format((float)$r['BillingPaymentRef']['amount'], 2); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if(!empty($r['BillingPaymentRef']['slip_url'])){ ?>
                                            <a href="<?php echo h($this->Html->url($r['BillingPaymentRef']['slip_url'])); ?>" target="_blank" title="Click to view full screenshot">
                                                <img src="<?php echo h($this->Html->url($r['BillingPaymentRef']['slip_url'])); ?>" style="width:50px;height:40px;object-fit:cover;border-radius:4px;border:1px solid #cbd5e1;" />
                                            </a>
                                        <?php } else { ?>
                                            <span class="text-muted"><i class="fa fa-file-image-o"></i> No Proof</span>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if($st === 'confirmed'){ ?>
                                            <span class="label label-success"><i class="fa fa-check"></i> Confirmed</span>
                                        <?php } elseif($st === 'rejected'){ ?>
                                            <span class="label label-danger"><i class="fa fa-times"></i> Rejected</span>
                                            <?php if(!empty($r['BillingPaymentRef']['rejection_reason'])){ ?>
                                                <br><small class="text-danger" style="font-size:10px;"><?php echo h($r['BillingPaymentRef']['rejection_reason']); ?></small>
                                            <?php } ?>
                                        <?php } elseif($st === 'cancelled'){ ?>
                                            <span class="label label-default">Cancelled</span>
                                        <?php } else { ?>
                                            <span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <!-- Action Buttons Aligned in Single Line -->
                                        <div style="display:inline-flex;align-items:center;justify-content:center;gap:4px;white-space:nowrap;">
                                            <button type="button" class="btn btn-xs btn-info" onclick="openDetailsModal('<?php echo $rId; ?>')">
                                                <i class="fa fa-eye"></i> Details
                                            </button>

                                            <?php if($st === 'pending'){ ?>
                                                <a href="<?php echo $this->Html->url(array('action' => 'approve', $rId)); ?>" class="btn btn-xs btn-success" onclick="return confirm('Confirm and post this payment of PKR <?php echo number_format($r['BillingPaymentRef']['amount'], 2); ?> to Bill #<?php echo h(!empty($b['bill_number']) ? $b['bill_number'] : ''); ?>?');">
                                                    <i class="fa fa-check"></i> Approve
                                                </a>
                                                <button type="button" class="btn btn-xs btn-danger" onclick="openRejectModal('<?php echo $rId; ?>', '<?php echo h(!empty($b['bill_number']) ? $b['bill_number'] : ''); ?>')">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                            <?php } ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted" style="padding:30px;">
                                    <i class="fa fa-info-circle" style="font-size:24px;"></i><br>
                                    <?php echo __('No payment requests found matching current filter.'); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="box-footer clearfix">
                <ul class="pagination pagination-sm no-margin pull-right">
                    <?php
                        echo $this->Paginator->prev('«', array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
                        echo $this->Paginator->numbers(array('separator' => '', 'currentTag' => 'a', 'currentClass' => 'active', 'tag' => 'li', 'first' => 1));
                        echo $this->Paginator->next('»', array('tag' => 'li'), null, array('tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a'));
                    ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODALS PLACED COMPLETELY OUTSIDE THE TABLE FOR PROPER POPUP RENDERING       -->
<!-- ========================================================================= -->
<?php if(!empty($requests)){ ?>
    <?php foreach($requests as $r){ 
        $rId = $r['BillingPaymentRef']['id'];
        $b = !empty($r['BillingBill']) ? $r['BillingBill'] : array();
        $c = !empty($r['BillingClient']) ? $r['BillingClient'] : array();
        $p = !empty($r['BillingProperty']) ? $r['BillingProperty'] : array();
        $st = $r['BillingPaymentRef']['status'];
    ?>
        <div class="modal fade" id="modalDetails<?php echo $rId; ?>" tabindex="-1" role="dialog" style="display:none;">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" style="border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.3);">
                    <div class="modal-header bg-primary" style="background:#0284c7;color:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;">
                        <h4 class="modal-title" style="font-weight:800;font-size:16px;margin:0;">
                            <i class="fa fa-credit-card"></i> Payment Submission Details #<?php echo $rId; ?>
                        </h4>
                        <button type="button" class="close" onclick="closeDetailsModal('<?php echo $rId; ?>')" style="color:#fff;opacity:0.9;font-size:24px;border:none;background:none;cursor:pointer;line-height:1;">&times;</button>
                    </div>
                    <div class="modal-body" style="padding:20px;">
                        <div class="row">
                            <div class="col-md-7">
                                <table class="table table-bordered table-striped" style="font-size:13px;margin-bottom:0;">
                                    <tr>
                                        <th style="width:180px;background:#f8fafc;">Request ID & Date:</th>
                                        <td><strong>#<?php echo $rId; ?></strong> (<?php echo date('d-M-Y H:i:s', strtotime($r['BillingPaymentRef']['submission_date'])); ?>)</td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc;">Consumer Name:</th>
                                        <td><strong><?php echo h(!empty($c['name']) ? $c['name'] : '-'); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc;">Phone & Email:</th>
                                        <td><?php echo h(!empty($c['phone']) ? $c['phone'] : '-'); ?> | <?php echo h(!empty($c['email']) ? $c['email'] : '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc;">Property Unit:</th>
                                        <td><?php echo h(!empty($p['property_no']) ? $p['property_no'] : (!empty($p['title']) ? $p['title'] : '-')); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc;">Bill Number:</th>
                                        <td><strong style="color:#0284c7;font-family:monospace;"><?php echo h(!empty($b['bill_number']) ? $b['bill_number'] : '-'); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc;">Payment Method:</th>
                                        <td><span class="label label-default"><?php echo h(!empty($r['BillingPaymentRef']['payment_method']) ? $r['BillingPaymentRef']['payment_method'] : 'Online'); ?></span></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc;">TID / Reference #:</th>
                                        <td><strong style="font-family:monospace;font-size:14px;"><?php echo h($r['BillingPaymentRef']['transaction_ref']); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc;">Submitted Amount:</th>
                                        <td><strong style="font-size:16px;color:#16a34a;">PKR <?php echo number_format((float)$r['BillingPaymentRef']['amount'], 2); ?></strong></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc;">Resident Notes / Title:</th>
                                        <td><?php echo h(!empty($r['BillingPaymentRef']['notes']) ? $r['BillingPaymentRef']['notes'] : '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <th style="background:#f8fafc;">Status:</th>
                                        <td>
                                            <?php if($st === 'confirmed'){ ?>
                                                <span class="label label-success">Confirmed & Posted</span>
                                                <?php if(!empty($r['BillingPaymentRef']['approved_at'])){ ?>
                                                    <small class="text-muted" style="display:block;margin-top:2px;">Approved on <?php echo date('d-M-Y H:i', strtotime($r['BillingPaymentRef']['approved_at'])); ?></small>
                                                <?php } ?>
                                            <?php } elseif($st === 'rejected'){ ?>
                                                <span class="label label-danger">Rejected</span>
                                                <?php if(!empty($r['BillingPaymentRef']['rejection_reason'])){ ?>
                                                    <small class="text-danger" style="display:block;margin-top:2px;"><strong>Reason:</strong> <?php echo h($r['BillingPaymentRef']['rejection_reason']); ?></small>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <span class="label label-warning">Pending Verification</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-5 text-center">
                                <label style="display:block;margin-bottom:8px;font-weight:700;color:#334155;">Uploaded Payment Proof Screenshot</label>
                                <?php if(!empty($r['BillingPaymentRef']['slip_url'])){ ?>
                                    <div style="background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px;padding:8px;">
                                        <a href="<?php echo h($this->Html->url($r['BillingPaymentRef']['slip_url'])); ?>" target="_blank" title="Click for full size">
                                            <img src="<?php echo h($this->Html->url($r['BillingPaymentRef']['slip_url'])); ?>" style="width:100%;max-height:280px;object-fit:contain;border-radius:6px;" />
                                        </a>
                                        <div style="margin-top:6px;font-size:11px;color:#0284c7;font-weight:700;">
                                            <i class="fa fa-external-link"></i> Click to Open Full Resolution
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:40px 10px;color:#94a3b8;">
                                        <i class="fa fa-image" style="font-size:36px;margin-bottom:8px;"></i>
                                        <div>No screenshot attached</div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="background:#f8fafc;padding:12px 20px;">
                        <?php if($st === 'pending'){ ?>
                            <a href="<?php echo $this->Html->url(array('action' => 'approve', $rId)); ?>" class="btn btn-success" onclick="return confirm('Confirm and post this payment?');">
                                <i class="fa fa-check"></i> Approve Payment
                            </a>
                            <button type="button" class="btn btn-danger" onclick="closeDetailsModal('<?php echo $rId; ?>'); openRejectModal('<?php echo $rId; ?>', '<?php echo h(!empty($b['bill_number']) ? $b['bill_number'] : ''); ?>');">
                                <i class="fa fa-times"></i> Reject Request
                            </button>
                        <?php } ?>
                        <button type="button" class="btn btn-default" onclick="closeDetailsModal('<?php echo $rId; ?>')">Close</button>
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
<?php } ?>

<!-- Modal Reject Request -->
<div class="modal fade" id="modalRejectRequest" tabindex="-1" role="dialog" style="display:none;">
    <div class="modal-dialog modal-sm" role="document">
        <form method="post" id="formRejectRequest" action="">
            <div class="modal-content" style="border-radius:12px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.3);">
                <div class="modal-header bg-red" style="background:#ef4444;color:#fff;padding:14px 20px;display:flex;align-items:center;justify-content:space-between;">
                    <h4 class="modal-title" style="font-weight:700;margin:0;font-size:16px;"><i class="fa fa-times-circle"></i> Reject Payment Request</h4>
                    <button type="button" class="close" onclick="closeRejectModal()" style="color:#fff;opacity:0.9;font-size:24px;border:none;background:none;cursor:pointer;line-height:1;">&times;</button>
                </div>
                <div class="modal-body" style="padding:20px;">
                    <div class="form-group" style="margin-bottom:0;">
                        <label style="font-weight:700;color:#334155;margin-bottom:6px;">Reason for Rejection *</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Enter reason (e.g. TID invalid or amount mismatch...)" required style="border-radius:6px;padding:8px 12px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer" style="background:#f8fafc;padding:12px 20px;">
                    <button type="button" class="btn btn-default" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger" style="background:#ef4444;border-color:#dc2626;font-weight:700;"><i class="fa fa-times"></i> Reject & Notify Client</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openDetailsModal(id) {
    var el = document.getElementById('modalDetails' + id);
    if (!el) return;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var modalObj = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
        modalObj.show();
    } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $('#modalDetails' + id).modal('show');
    } else {
        el.style.display = 'block';
        el.classList.add('show');
        el.style.backgroundColor = 'rgba(0,0,0,0.5)';
    }
}

function closeDetailsModal(id) {
    var el = document.getElementById('modalDetails' + id);
    if (!el) return;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var modalObj = bootstrap.Modal.getInstance(el);
        if (modalObj) modalObj.hide();
    }
    if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $('#modalDetails' + id).modal('hide');
    }
    el.style.display = 'none';
    el.classList.remove('show');
}

function openRejectModal(id, billNo) {
    var actionUrl = '<?php echo $this->Html->url(array('action' => 'reject')); ?>/' + id;
    var form = document.getElementById('formRejectRequest');
    if (form) form.action = actionUrl;
    
    var el = document.getElementById('modalRejectRequest');
    if (!el) return;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var modalObj = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
        modalObj.show();
    } else if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $('#modalRejectRequest').modal('show');
    } else {
        el.style.display = 'block';
        el.classList.add('show');
        el.style.backgroundColor = 'rgba(0,0,0,0.5)';
    }
}

function closeRejectModal() {
    var el = document.getElementById('modalRejectRequest');
    if (!el) return;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        var modalObj = bootstrap.Modal.getInstance(el);
        if (modalObj) modalObj.hide();
    }
    if (typeof $ !== 'undefined' && $.fn && $.fn.modal) {
        $('#modalRejectRequest').modal('hide');
    }
    el.style.display = 'none';
    el.classList.remove('show');
}
</script>
