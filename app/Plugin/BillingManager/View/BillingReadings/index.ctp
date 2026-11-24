<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
<?php echo $this->Html->css('wv-admin-media-picker.css'); ?>
<?php echo $this->Html->script('wv-admin-media-picker.js'); ?>

<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-tachometer"></span> <?php echo __('Meter Readings Management'); ?></h1>
        <p class="description"><?php echo __('Historical log of all recorded electricity, water, and gas meter readings with attached meter photographs.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <div class="btn-group">
            <button type="button" class="btn btn-success" onclick="openAddReadingModal()" style="font-weight:700;">
                <span class="fa fa-plus-circle"></span> <?php echo __('Add Single Reading'); ?>
            </button>
            <?php echo $this->Html->link('<span class="fa fa-th"></span> ' . __('Fast Bulk Reading Grid'), array('action' => 'bulk_entry'), array('escape' => false, 'class' => 'btn btn-primary', 'style' => 'font-weight:700;')); ?>
            <?php echo $this->Html->link('<span class="fa fa-download"></span> ' . __('Export CSV'), array('action' => 'index', '?' => array_merge($this->request->query, array('export' => 'csv'))), array('escape' => false, 'class' => 'btn btn-info', 'style' => 'font-weight:700;')); ?>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-body">
        <?php echo $this->Session->flash(); ?>

        <!-- Filter Bar -->
        <div class="well" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px;margin-bottom:18px;">
            <form method="get" action="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="form-inline">
                <div class="form-group" style="margin-right:8px;">
                    <label style="font-size:11px;font-weight:bold;"><?php echo __('Month'); ?>:</label>
                    <input type="month" name="month" value="<?php echo isset($this->request->query['month']) ? h($this->request->query['month']) : ''; ?>" class="form-control input-sm" />
                </div>
                <div class="form-group" style="margin-right:8px;">
                    <label style="font-size:11px;font-weight:bold;"><?php echo __('Property Unit'); ?>:</label>
                    <select name="property_id" class="form-control input-sm" style="max-width:260px;">
                        <option value=""><?php echo __('-- All Properties --'); ?></option>
                        <?php foreach($properties as $pId => $pTitle){ ?>
                            <option value="<?php echo $pId; ?>" <?php if(isset($this->request->query['property_id']) && $this->request->query['property_id'] == $pId) echo 'selected'; ?>><?php echo h($pTitle); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="form-group" style="margin-right:8px;">
                    <label style="font-size:11px;font-weight:bold;"><?php echo __('Society'); ?>:</label>
                    <select name="society_id" class="form-control input-sm">
                        <option value=""><?php echo __('-- All Societies --'); ?></option>
                        <?php foreach($societies as $sId => $sName){ ?>
                            <option value="<?php echo $sId; ?>" <?php if(isset($this->request->query['society_id']) && $this->request->query['society_id'] == $sId) echo 'selected'; ?>><?php echo h($sName); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-sm btn-primary"><span class="fa fa-filter"></span> <?php echo __('Filter'); ?></button>
                <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-sm btn-default"><?php echo __('Reset'); ?></a>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered" style="vertical-align:middle;font-size:13px;">
                <thead style="background:#0f172a;color:#fff;">
                    <tr>
                        <th style="width:40px;">#</th>
                        <th><?php echo __('Month'); ?></th>
                        <th><?php echo __('Consumer Name'); ?></th>
                        <th><?php echo __('Property Unit'); ?></th>
                        <th><?php echo __('Meter Number'); ?></th>
                        <th><?php echo __('Prev Reading'); ?></th>
                        <th><?php echo __('Curr Reading'); ?></th>
                        <th><?php echo __('Units Consumed'); ?></th>
                        <th style="text-align:center;"><?php echo __('Meter Photo'); ?></th>
                        <th><?php echo __('Date / Reader'); ?></th>
                        <th><?php echo __('Billed'); ?></th>
                        <th style="min-width:80px;text-align:center;"><?php echo __('Actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($readings)){ ?>
                        <tr><td colspan="12" class="text-center text-muted" style="padding:24px;"><?php echo __('No readings recorded.'); ?></td></tr>
                    <?php } else { foreach($readings as $r){ 
                        $rd = $r['BillingReading']; 
                        $m = $r['BillingMeter']; 
                        $p = $r['BillingProperty']; 
                        $pId = !empty($rd['billing_property_id']) ? (int)$rd['billing_property_id'] : 0;
                        $c = isset($propToClient[$pId]) ? $propToClient[$pId] : (!empty($p['BillingClient']) ? $p['BillingClient'] : array());
                        $consumerName = !empty($c['name']) ? $c['name'] : 'N/A';
                        $consumerId = !empty($c['id']) ? ('WV70' . str_pad($c['id'], 2, '0', STR_PAD_LEFT)) : '';
                        $consumerPhone = !empty($c['phone']) ? $c['phone'] : '';
                    ?>
                        <tr>
                            <td><?php echo (int)$rd['id']; ?></td>
                            <td><strong style="color:#0284c7;"><?php echo date('M Y', strtotime($rd['reading_month'] . '-01')); ?></strong></td>
                            
                            <!-- Consumer Name Column -->
                            <td>
                                <div style="font-weight:800;color:#0f172a;font-size:13px;">
                                    <?php echo h($consumerName); ?>
                                </div>
                                <div style="display:flex;gap:6px;align-items:center;margin-top:2px;">
                                    <?php if(!empty($consumerId)){ ?>
                                        <span class="badge" style="background:#e0f2fe;color:#0369a1;font-weight:700;font-size:10.5px;padding:2px 6px;"><?php echo h($consumerId); ?></span>
                                    <?php } ?>
                                    <?php if(!empty($consumerPhone)){ ?>
                                        <small class="text-muted" style="font-size:11px;"><i class="fa fa-phone"></i> <?php echo h($consumerPhone); ?></small>
                                    <?php } ?>
                                </div>
                            </td>

                            <!-- Property Unit Column -->
                            <td>
                                <strong><?php echo h(!empty($p['title']) ? $p['title'] : ('Unit #' . $rd['billing_property_id'])); ?></strong>
                                <div class="text-muted" style="font-size:11px;"><?php echo h(!empty($p['property_no']) ? $p['property_no'] : ''); ?></div>
                            </td>

                            <td>
                                <strong><span class="fa fa-plug text-warning"></span> <?php echo h(!empty($m['meter_number']) ? $m['meter_number'] : '-'); ?></strong>
                            </td>
                            <td><?php echo number_format((float)$rd['previous_reading'], 2); ?></td>
                            <td><strong style="color:#0f172a;"><?php echo number_format((float)$rd['current_reading'], 2); ?></strong></td>
                            <td><span class="label label-info" style="font-weight:bold;"><?php echo number_format((float)$rd['units_consumed'], 2); ?></span></td>
                            <td style="text-align:center;">
                                <?php if(!empty($rd['photo_url'])){ ?>
                                    <a href="<?php echo htmlspecialchars($rd['photo_url']); ?>" target="_blank" title="Click to view full photo">
                                        <img src="<?php echo htmlspecialchars($rd['photo_url']); ?>" style="height:38px;width:38px;object-fit:cover;border-radius:4px;border:1px solid #cbd5e1;box-shadow:0 1px 3px rgba(0,0,0,0.1);" />
                                    </a>
                                <?php } else { ?>
                                    <span class="text-muted" style="font-size:11px;"><?php echo __('Not Attached'); ?></span>
                                <?php } ?>
                            </td>
                            <td>
                                <div><?php echo !empty($rd['reading_date']) ? date('d-M-Y', strtotime((string)$rd['reading_date'])) : '-'; ?></div>
                                <small class="text-muted"><?php echo h(!empty($rd['reader_name']) ? $rd['reader_name'] : 'Admin'); ?></small>
                            </td>
                            <td>
                                <?php if(!empty($rd['is_billed'])){ ?>
                                    <span class="label label-success"><?php echo __('Billed'); ?></span>
                                <?php } else { ?>
                                    <span class="label label-default"><?php echo __('Pending'); ?></span>
                                <?php } ?>

                                <?php if(!empty($rd['anomaly_flag']) && $rd['anomaly_flag'] === 'SPIKE'){ ?>
                                    <div style="margin-top:3px;"><span class="label label-warning" title="Excessive consumption spike detected"><i class="fa fa-warning"></i> SPIKE</span></div>
                                <?php } elseif(!empty($rd['anomaly_flag']) && $rd['anomaly_flag'] === 'ROLLBACK'){ ?>
                                    <div style="margin-top:3px;"><span class="label label-danger" title="Counter rollback detected"><i class="fa fa-refresh"></i> ROLLBACK</span></div>
                                <?php } ?>
                            </td>
                            <td style="text-align:center;">
                                <button type="button" class="btn btn-xs btn-default edit-reading-btn" onclick="openEditReadingModal(<?php echo htmlspecialchars(json_encode($rd)); ?>)" title="<?php echo __('Edit Reading'); ?>">
                                    <span class="fa fa-pencil text-primary"></span>
                                </button>
                                <?php echo $this->Html->link('<span class="fa fa-trash text-danger"></span>', array('action' => 'delete', $rd['id']), array('escape' => false, 'class' => 'btn btn-xs btn-default', 'title' => __('Delete'), 'onclick' => "return confirm('Delete this reading entry?');")); ?>
                            </td>
                        </tr>
                    <?php } } ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if($this->Paginator->hasPage(2)){ ?>
            <div class="row" style="margin-top:15px;">
                <div class="col-md-6" style="padding-top:8px;">
                    <span class="text-muted"><?php echo $this->Paginator->counter(array('format' => __('Showing {:start} to {:end} of {:count} total readings'))); ?></span>
                </div>
                <div class="col-md-6 text-right">
                    <ul class="pagination pagination-sm" style="margin:0;">
                        <?php echo $this->Paginator->prev('&larr; ' . __('Previous'), array('escape' => false, 'tag' => 'li'), null, array('escape' => false, 'tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a')); ?>
                        <?php echo $this->Paginator->numbers(array('separator' => '', 'currentTag' => 'a', 'currentClass' => 'active', 'tag' => 'li', 'first' => 1)); ?>
                        <?php echo $this->Paginator->next(__('Next') . ' &rarr;', array('escape' => false, 'tag' => 'li'), null, array('escape' => false, 'tag' => 'li', 'class' => 'disabled', 'disabledTag' => 'a')); ?>
                    </ul>
                </div>
            </div>
        <?php } ?>

    </div>
</div>

<!-- ========================================== -->
<!-- ADD / EDIT SINGLE READING MODAL WITH CROP  -->
<!-- ========================================== -->
<div class="modal fade" id="singleReadingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius:10px;box-shadow:0 20px 35px -10px rgba(0,0,0,0.3);">
            <?php echo $this->Form->create('BillingReading', array('url' => array('action' => 'save_single'))); ?>
            <input type="hidden" name="data[BillingReading][id]" id="singleReadingId" value="" />
            
            <div class="modal-header" style="background:#0f172a;color:#ffffff;border-radius:10px 10px 0 0;padding:16px 20px;">
                <button type="button" class="close" data-dismiss="modal" style="color:#ffffff;opacity:0.8;">&times;</button>
                <h4 class="modal-title" id="singleReadingModalTitle" style="font-weight:800;color:#38bdf8;">
                    <span class="fa fa-tachometer"></span> <?php echo __('Add Single Meter Reading'); ?>
                </h4>
            </div>

            <div class="modal-body" style="padding:22px;">
                <div class="row">
                    
                    <!-- Left Form Column -->
                    <div class="col-md-6">
                        <!-- Meter Selector -->
                        <div class="form-group" style="margin-bottom:14px;">
                            <label style="font-weight:700;font-size:12.5px;color:#0f172a;margin-bottom:4px;display:block;">
                                <?php echo __('Select Consumer / Meter No / Property'); ?> <span class="text-danger">*</span>
                            </label>
                            <select name="data[BillingReading][meter_id]" id="singleMeterSelect" class="form-control" required style="width:100%;font-weight:600;" onchange="onMeterSelected(this)">
                                <option value="" data-prev="0" data-meterno="METER"><?php echo __('-- Search Consumer Name, Meter No, or Property --'); ?></option>
                                <?php if(!empty($allMeters)){ foreach($allMeters as $m){ 
                                    $mi = $m['BillingMeter'];
                                    $mp = $m['BillingProperty'];
                                    $pId = !empty($mi['billing_property_id']) ? (int)$mi['billing_property_id'] : 0;
                                    $mc = isset($propToClient[$pId]) ? $propToClient[$pId] : (!empty($mp['BillingClient']) ? $mp['BillingClient'] : array());
                                    $pTitle = !empty($mp['title']) ? $mp['title'] : ('Unit #' . $mi['billing_property_id']);
                                    $cName = !empty($mc['name']) ? $mc['name'] : 'No Consumer';
                                    $currReading = (float)$mi['current_reading'];
                                ?>
                                    <option value="<?php echo $mi['id']; ?>" data-prev="<?php echo $currReading; ?>" data-meterno="<?php echo h($mi['meter_number']); ?>">
                                        <?php echo h($cName) . ' — ' . h($mi['meter_number']) . ' — ' . h($pTitle); ?>
                                    </option>
                                <?php } } ?>
                            </select>
                        </div>

                        <!-- Month & Date -->
                        <div class="row" style="margin-bottom:14px;">
                            <div class="col-md-6">
                                <label style="font-weight:700;font-size:12px;color:#334155;margin-bottom:3px;display:block;">
                                    <?php echo __('Reading Month'); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="month" name="data[BillingReading][reading_month]" id="singleReadingMonth" value="<?php echo date('Y-m'); ?>" class="form-control input-sm" required />
                            </div>
                            <div class="col-md-6">
                                <label style="font-weight:700;font-size:12px;color:#334155;margin-bottom:3px;display:block;">
                                    <?php echo __('Reading Date'); ?> <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="data[BillingReading][reading_date]" id="singleReadingDate" value="<?php echo date('Y-m-d'); ?>" class="form-control input-sm" required />
                            </div>
                        </div>

                        <!-- Readings & Units Box -->
                        <div style="background:#f8fafc;padding:12px;border-radius:8px;border:1px solid #e2e8f0;margin-bottom:14px;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label style="font-weight:700;font-size:11.5px;color:#475569;margin-bottom:2px;display:flex;align-items:center;justify-content:space-between;">
                                        <span><?php echo __('Previous Reading'); ?></span>
                                        <small style="color:#64748b;font-weight:600;"><i class="fa fa-lock"></i> <?php echo __('Auto/Locked'); ?></small>
                                    </label>
                                    <input type="number" step="0.01" name="data[BillingReading][previous_reading]" id="singlePrevReading" value="0.00" class="form-control input-sm" readonly="readonly" style="font-weight:700;background:#f1f5f9;color:#334155;cursor:not-allowed;border-color:#cbd5e1;" />
                                </div>
                                <div class="col-md-6">
                                    <label style="font-weight:700;font-size:11.5px;color:#0f172a;margin-bottom:2px;display:block;">
                                        <?php echo __('Current Reading'); ?> <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.01" name="data[BillingReading][current_reading]" id="singleCurrReading" value="" class="form-control input-sm" required oninput="calcSingleUnits()" style="font-weight:900;color:#0284c7;" placeholder="0.00" />
                                </div>
                            </div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;padding-top:8px;border-top:1px solid #e2e8f0;">
                                <span style="font-size:12px;font-weight:700;color:#334155;"><?php echo __('Units Consumed:'); ?></span>
                                <span id="singleUnitsDisplay" style="font-size:18px;font-weight:900;color:#16a34a;">0.00</span>
                            </div>
                        </div>

                        <!-- Reader Name -->
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-weight:700;font-size:12px;color:#334155;margin-bottom:2px;display:block;">
                                <?php echo __('Reader / Inspector Name'); ?>
                            </label>
                            <input type="text" name="data[BillingReading][reader_name]" id="singleReaderName" value="Admin" class="form-control input-sm" placeholder="Reader Name" />
                        </div>
                    </div>

                    <!-- Right Column: Image Upload, Crop & Gallery Section -->
                    <div class="col-md-6">
                        <div style="background:#f8fafc;border:2px dashed #cbd5e1;border-radius:10px;padding:16px;text-align:center;position:relative;">
                            <label style="font-weight:800;font-size:13px;color:#0f172a;margin-bottom:6px;display:block;">
                                <i class="fa fa-camera text-primary"></i> <?php echo __('Meter Image Upload & Crop'); ?>
                            </label>
                            <p style="font-size:11.5px;color:#64748b;margin:0 0 12px 0;">
                                <?php echo __('Upload dial photo or choose from gallery. You can crop dial numbers before or after uploading.'); ?>
                            </p>

                            <!-- Single Clean Unified Button Bar (Stable Position) -->
                            <div style="display:flex;gap:6px;justify-content:center;margin-bottom:12px;flex-wrap:wrap;">
                                <label class="btn btn-sm btn-primary" style="cursor:pointer;font-weight:700;margin:0;background:#0284c7;border-color:#0284c7;" title="Upload or Take Photo">
                                    <i class="fa fa-upload"></i> <?php echo __('Upload / Camera'); ?>
                                    <input type="file" id="meterCropFileInput" class="wv-no-gallery" data-wv-no-gallery="true" accept="image/*" style="display:none;" onchange="handleCropFileSelect(this)" />
                                </label>
                                <button type="button" class="btn btn-sm btn-default" onclick="wvOpenMediaPicker('singlePhotoUrl')" title="Select from Media Gallery" style="font-weight:600;">
                                    <i class="fa fa-folder-open text-warning"></i> <?php echo __('Select from Gallery'); ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-default" onclick="removeAttachedPhoto()" title="Clear Image" style="font-weight:600;">
                                    <i class="fa fa-times text-danger"></i> <?php echo __('Clear'); ?>
                                </button>
                            </div>

                            <!-- Cropping Viewport Container -->
                            <div id="cropperContainer" style="display:none;margin-bottom:12px;max-height:220px;overflow:hidden;background:#000;border-radius:6px;">
                                <img id="cropImagePreview" src="" style="max-width:100%;display:block;" />
                            </div>

                            <!-- Crop Action Bar -->
                            <div id="cropActionBar" style="display:none;margin-bottom:12px;">
                                <button type="button" id="btnApplyCrop" class="btn btn-sm btn-success" onclick="applyAndUploadCrop()" style="font-weight:700;">
                                    <i class="fa fa-crop"></i> <?php echo __('Crop & Save to "Reading" Gallery'); ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-default" onclick="cancelCrop()">
                                    <?php echo __('Cancel'); ?>
                                </button>
                            </div>

                            <!-- Attached Image Preview & Edit/Crop Action Box -->
                            <div id="attachedPhotoBox" style="display:none;background:#ffffff;border:1px solid #bbf7d0;border-radius:8px;padding:10px 14px;margin-top:10px;text-align:left;">
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <img id="attachedThumb" src="" style="width:54px;height:54px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e1;box-shadow:0 1px 3px rgba(0,0,0,0.1);" />
                                    <div style="flex:1;overflow:hidden;">
                                        <div style="font-size:11.5px;font-weight:800;color:#166534;"><i class="fa fa-check-circle"></i> <?php echo __('Attached Photo'); ?></div>
                                        <div id="attachedFileName" style="font-size:11px;color:#64748b;text-overflow:ellipsis;white-space:nowrap;overflow:hidden;"></div>
                                        
                                        <!-- Edit Crop Action Button -->
                                        <div style="margin-top:4px;display:flex;gap:6px;">
                                            <button type="button" class="btn btn-xs btn-info" onclick="editCurrentCrop()" style="font-weight:700;padding:2px 8px;">
                                                <i class="fa fa-crop"></i> <?php echo __('Edit / Crop Image'); ?>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-xs btn-danger" onclick="removeAttachedPhoto()" title="Remove photo">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Hidden URL field submitted with form -->
                            <input type="hidden" name="data[BillingReading][photo_url]" id="singlePhotoUrl" class="wv-no-gallery" data-wv-no-gallery="true" value="" />
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer" style="background:#f8fafc;border-top:1px solid #e2e8f0;border-radius:0 0 10px 10px;padding:14px 20px;">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel'); ?></button>
                <button type="submit" class="btn btn-success" style="font-weight:700;padding:8px 24px;">
                    <span class="fa fa-save"></span> <?php echo __('Save Reading'); ?>
                </button>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<script>
var cropperInstance = null;

function openAddReadingModal() {
    document.getElementById('singleReadingId').value = '';
    document.getElementById('singleReadingModalTitle').innerHTML = '<span class="fa fa-plus-circle"></span> Add Single Meter Reading';
    $('#singleMeterSelect').val('').trigger('change');
    document.getElementById('singleReadingMonth').value = '<?php echo date('Y-m'); ?>';
    document.getElementById('singleReadingDate').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('singlePrevReading').value = '0.00';
    document.getElementById('singleCurrReading').value = '';
    document.getElementById('singleUnitsDisplay').innerText = '0.00';
    document.getElementById('singlePhotoUrl').value = '';
    document.getElementById('singleReaderName').value = 'Admin';
    removeAttachedPhoto();
    cancelCrop();
    $('#singleReadingModal').modal('show');
}

function openEditReadingModal(rd) {
    document.getElementById('singleReadingId').value = rd.id || '';
    document.getElementById('singleReadingModalTitle').innerHTML = '<span class="fa fa-pencil"></span> Edit Meter Reading #' + (rd.id || '');
    $('#singleMeterSelect').val(rd.meter_id || '').trigger('change');
    document.getElementById('singleReadingMonth').value = rd.reading_month || '<?php echo date('Y-m'); ?>';
    document.getElementById('singleReadingDate').value = rd.reading_date || '<?php echo date('Y-m-d'); ?>';
    document.getElementById('singlePrevReading').value = rd.previous_reading || '0.00';
    document.getElementById('singleCurrReading').value = rd.current_reading || '';
    document.getElementById('singleUnitsDisplay').innerText = parseFloat(rd.units_consumed || 0).toFixed(2);
    document.getElementById('singlePhotoUrl').value = rd.photo_url || '';
    document.getElementById('singleReaderName').value = rd.reader_name || 'Admin';
    
    if(rd.photo_url) {
        document.getElementById('attachedThumb').src = rd.photo_url;
        document.getElementById('attachedFileName').innerText = rd.photo_url.split('/').pop();
        document.getElementById('attachedPhotoBox').style.display = 'block';
    } else {
        removeAttachedPhoto();
    }
    cancelCrop();
    $('#singleReadingModal').modal('show');
}

jQuery(document).ready(function($) {
    $('#singleReadingModal').on('shown.bs.modal', function () {
        if ($.fn.select2) {
            $('#singleMeterSelect').select2({
                dropdownParent: $('#singleReadingModal'),
                width: '100%'
            });
        }
    });

    $('#singleMeterSelect').on('change', function() {
        onMeterSelected(this);
    });
});

function onMeterSelected(sel) {
    var opt = sel.options[sel.selectedIndex];
    if(opt && opt.getAttribute('data-prev')) {
        document.getElementById('singlePrevReading').value = parseFloat(opt.getAttribute('data-prev')).toFixed(2);
        calcSingleUnits();
    }
}

function calcSingleUnits() {
    var prev = parseFloat(document.getElementById('singlePrevReading').value) || 0;
    var curr = parseFloat(document.getElementById('singleCurrReading').value) || 0;
    var units = Math.max(0, curr - prev);
    document.getElementById('singleUnitsDisplay').innerText = units.toFixed(2);
}

function handleCropFileSelect(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var cropImg = document.getElementById('cropImagePreview');
            cropImg.src = e.target.result;
            document.getElementById('cropperContainer').style.display = 'block';
            document.getElementById('cropActionBar').style.display = 'block';

            if(cropperInstance) {
                cropperInstance.destroy();
            }

            cropperInstance = new Cropper(cropImg, {
                aspectRatio: NaN,
                viewMode: 1,
                autoCropArea: 0.85,
                responsive: true
            });
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function cancelCrop() {
    if(cropperInstance) {
        cropperInstance.destroy();
        cropperInstance = null;
    }
    document.getElementById('cropperContainer').style.display = 'none';
    document.getElementById('cropActionBar').style.display = 'none';
    document.getElementById('meterCropFileInput').value = '';
}

function applyAndUploadCrop() {
    if(!cropperInstance) return;

    var btn = document.getElementById('btnApplyCrop');
    btn.disabled = true;
    btn.innerHTML = '<span class="fa fa-spinner fa-spin"></span> Uploading...';

    var canvas = cropperInstance.getCroppedCanvas({
        maxWidth: 1200,
        maxHeight: 1200,
        fillColor: '#fff',
        imageSmoothingEnabled: true,
        imageSmoothingQuality: 'high'
    });

    var base64 = canvas.toDataURL('image/jpeg', 0.90);
    var meterSel = document.getElementById('singleMeterSelect');
    var meterNo = 'METER';
    if(meterSel && meterSel.selectedIndex >= 0) {
        var opt = meterSel.options[meterSel.selectedIndex];
        if(opt && opt.getAttribute('data-meterno')) meterNo = opt.getAttribute('data-meterno');
    }

    jQuery.ajax({
        url: '<?php echo $this->Html->url(array("action" => "upload_meter_photo")); ?>',
        type: 'POST',
        data: {
            image_data: base64,
            meter_number: meterNo
        },
        dataType: 'json',
        success: function(resp) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-crop"></i> <?php echo __("Crop & Save to \"Reading\" Gallery"); ?>';
            if(resp && resp.status === 'success') {
                document.getElementById('singlePhotoUrl').value = resp.url;
                document.getElementById('attachedThumb').src = resp.url;
                document.getElementById('attachedFileName').innerText = resp.file_name;
                document.getElementById('attachedPhotoBox').style.display = 'block';
                cancelCrop();
            } else {
                alert(resp && resp.message ? resp.message : 'Crop upload failed.');
            }
        },
        error: function(xhr, status, err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-crop"></i> <?php echo __("Crop & Save to \"Reading\" Gallery"); ?>';
            alert('Upload error: ' + err);
        }
    });
}

function editCurrentCrop() {
    var url = document.getElementById('singlePhotoUrl').value;
    if (!url) return;

    var cropImg = document.getElementById('cropImagePreview');
    cropImg.src = url;
    document.getElementById('cropperContainer').style.display = 'block';
    document.getElementById('cropActionBar').style.display = 'block';
    document.getElementById('attachedPhotoBox').style.display = 'none';

    if(cropperInstance) {
        cropperInstance.destroy();
    }

    cropperInstance = new Cropper(cropImg, {
        aspectRatio: NaN,
        viewMode: 1,
        autoCropArea: 0.85,
        responsive: true
    });
}

function removeAttachedPhoto() {
    document.getElementById('singlePhotoUrl').value = '';
    document.getElementById('attachedThumb').src = '';
    document.getElementById('attachedFileName').innerText = '';
    document.getElementById('attachedPhotoBox').style.display = 'none';
    cancelCrop();
}

// Observe when Media Picker changes photo url
setInterval(function() {
    var el = document.getElementById('singlePhotoUrl');
    if (el && el.value && el.value !== document.getElementById('attachedThumb').getAttribute('data-src')) {
        document.getElementById('attachedThumb').src = el.value;
        document.getElementById('attachedThumb').setAttribute('data-src', el.value);
        document.getElementById('attachedFileName').innerText = el.value.split('/').pop();
        document.getElementById('attachedPhotoBox').style.display = 'block';
    }
}, 500);
</script>
