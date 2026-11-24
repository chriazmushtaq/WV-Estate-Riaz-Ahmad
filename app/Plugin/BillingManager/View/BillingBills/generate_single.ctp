<div class="page-title">
    <div class="title-env">
        <h1 class="title"><span class="fa fa-bolt"></span> <?php echo __('Create Single Bill'); ?></h1>
        <p class="description"><?php echo __('Generate an individual monthly billing invoice with optional rate overrides, maintenance breakdown, and real-time review.'); ?></p>
    </div>
    <div class="pull-right" style="margin-top:10px;">
        <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default" style="font-weight:700;">
            <i class="fa fa-arrow-left"></i> <?php echo __('Back to All Bills'); ?>
        </a>
    </div>
</div>

<div class="panel" style="max-width:680px;margin:0 auto;border-radius:8px;box-shadow:0 20px 40px rgba(0,0,0,0.12);border:1px solid #cbd5e1;background:#ffffff;overflow:hidden;">
    <div class="panel-heading" style="background:#ffffff;border-bottom:1px solid #f1f5f9;padding:16px 20px;border-radius:8px 8px 0 0;">
        <h3 class="panel-title" style="font-weight:700;font-size:17px;color:#1e293b;margin:0;">
            <?php echo __('Create Single Bill'); ?>
        </h3>
    </div>
    <div class="panel-body" style="padding:20px 22px;background:#ffffff;">
        <?php echo $this->Session->flash(); ?>

        <?php echo $this->Form->create('BillingBill', array('action' => 'generate_single', 'id' => 'singleBillPageForm')); ?>

        <!-- Meter Selector -->
        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                <?php echo __('Meter'); ?>
            </label>
            <select name="data[BillingBill][meter_id]" id="sbMeterId" class="form-control" required style="width:100%;font-weight:600;border-radius:6px;border:1px solid #cbd5e1;height:40px;font-size:13px;" onchange="updateSingleBillPreview()">
                <option value=""><?php echo __('Select'); ?></option>
                <?php foreach($billMeters as $bm){ 
                    $bmt = $bm['BillingMeter']; 
                    $bmp = $bm['BillingProperty']; 
                    $pId = !empty($bmt['billing_property_id']) ? (int)$bmt['billing_property_id'] : 0;
                    $bmc = isset($propToClient[$pId]) ? $propToClient[$pId] : (!empty($bmp['BillingClient']) ? $bmp['BillingClient'] : array());
                    $cName = !empty($bmc['name']) ? $bmc['name'] : 'No Consumer';
                    $pTitle = !empty($bmp['title']) ? $bmp['title'] : ('Unit #' . $pId);
                    $cCode = !empty($bmc['client_code']) ? $bmc['client_code'] : ('WV' . (7000 + $pId));
                    $mNum = !empty($bmt['meter_number']) ? $bmt['meter_number'] : '0000';
                    $uType = !empty($bmt['utility_type']) ? $bmt['utility_type'] : 'Electricity';
                ?>
                    <option value="<?php echo $bmt['id']; ?>" data-utility="<?php echo h($uType); ?>" data-meterno="<?php echo h($mNum); ?>">
                        <?php echo h($cName) . ' · Property: ' . h($pTitle) . ' · ' . h($cCode) . ' · Meter: ' . h($mNum) . ' · Type: ' . h($uType); ?>
                    </option>
                <?php } ?>
            </select>
            <div style="font-size:11px;color:#94a3b8;margin-top:3px;">
                <?php echo sprintf(__('Meters loaded: %d'), count($billMeters)); ?>
            </div>
        </div>

        <!-- Month -->
        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                <?php echo __('Month'); ?>
            </label>
            <input type="month" name="data[BillingBill][billing_period]" id="sbMonth" value="<?php echo date('Y-m'); ?>" class="form-control" required style="border-radius:6px;border:1px solid #cbd5e1;height:40px;font-weight:600;font-size:13px;" onchange="updateSingleBillPreview()" />
            <div id="sbReadingNotice" style="margin-top:6px;"></div>
        </div>

        <!-- Issue Date -->
        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                <?php echo __('Issue Date'); ?>
            </label>
            <input type="date" name="data[BillingBill][issue_date]" id="sbIssueDate" value="<?php echo date('Y-m-d'); ?>" class="form-control" required style="border-radius:6px;border:1px solid #cbd5e1;height:40px;font-weight:600;font-size:13px;" onchange="calcDefaultDueDate()" />
        </div>

        <!-- Due Date -->
        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                <?php echo __('Due Date'); ?>
            </label>
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <label style="margin:0;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#334155;">
                    <input type="checkbox" id="sbDefaultDueCheck" checked onchange="toggleDefaultDueDate()" />
                    <span><?php echo __('Default due'); ?></span>
                </label>
                <span id="sbDuePreviewText" style="font-size:11.5px;color:#94a3b8;">
                    Preview: <?php echo date('d-M-Y', strtotime('+' . $defaultDueDays . ' days')); ?>
                </span>
                <div style="margin-left:auto;min-width:180px;">
                    <input type="date" name="data[BillingBill][due_date]" id="sbDueDate" value="<?php echo date('Y-m-d', strtotime('+' . $defaultDueDays . ' days')); ?>" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:38px;font-weight:600;font-size:13px;" required />
                </div>
            </div>
        </div>

        <!-- Template -->
        <div class="form-group" style="margin-bottom:14px;">
            <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:4px;display:block;">
                <?php echo __('Template'); ?>
            </label>
            <?php echo $this->Form->input('template_id', array('type' => 'select', 'options' => $templates, 'id' => 'sbTemplateId', 'class' => 'form-control', 'style' => 'border-radius:6px;border:1px solid #cbd5e1;height:40px;font-weight:600;font-size:13px;', 'label' => false, 'div' => false)); ?>
        </div>

        <!-- Apply Maintenance Breakdown Toggle -->
        <div class="form-group" style="margin-bottom:10px;">
            <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:2px;display:block;">
                <?php echo __('Apply Maintenance Breakdown'); ?>
            </label>
            <label style="margin:0;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#334155;">
                <input type="checkbox" name="data[BillingBill][apply_maintenance]" id="sbApplyMaint" value="1" checked onclick="toggleMaintSection()" onchange="toggleMaintSection()" />
                <span><?php echo __('ON'); ?></span>
            </label>
        </div>

        <!-- Apply Other Charges Toggle -->
        <div class="form-group" style="margin-bottom:16px;">
            <label style="font-weight:600;font-size:13px;color:#334155;margin-bottom:2px;display:block;">
                <?php echo __('Apply Other Charges'); ?>
            </label>
            <label style="margin:0;font-weight:700;font-size:13px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#334155;">
                <input type="checkbox" name="data[BillingBill][apply_other_charges]" id="sbApplyOther" value="1" checked onclick="toggleOtherSection()" onchange="toggleOtherSection()" />
                <span><?php echo __('ON'); ?></span>
            </label>
        </div>

        <!-- Optional Rate Overrides Card Box -->
        <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:8px;padding:16px;margin-bottom:16px;">
            <div style="font-weight:700;font-size:13px;color:#334155;margin-bottom:12px;">
                <?php echo __('Optional Rate Overrides (Single Bill)'); ?>
            </div>

            <!-- Electricity Unit Rate -->
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:3px;display:block;">
                    <?php echo __('Electricity Unit Rate (Optional)'); ?>
                </label>
                <input type="number" step="0.01" name="data[BillingBill][rate_override_elec]" id="sbRateElec" placeholder="Default: <?php echo number_format($defaultElecRate, 2); ?>" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:38px;font-weight:600;font-size:13px;" oninput="updateSingleBillPreview()" />
            </div>

            <!-- Water Unit Rate -->
            <div class="form-group" style="margin-bottom:12px;">
                <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:3px;display:block;">
                    <?php echo __('Water Unit Rate (Optional)'); ?>
                </label>
                <input type="number" step="0.01" name="data[BillingBill][rate_override_water]" id="sbRateWater" placeholder="Default: <?php echo number_format($defaultWaterRate, 2); ?>" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:38px;font-weight:600;font-size:13px;" oninput="updateSingleBillPreview()" />
            </div>

            <!-- Gas Unit Rate -->
            <div class="form-group" style="margin-bottom:14px;">
                <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:3px;display:block;">
                    <?php echo __('Gas Unit Rate (Optional)'); ?>
                </label>
                <input type="number" step="0.01" name="data[BillingBill][rate_override_gas]" id="sbRateGas" placeholder="Default: <?php echo number_format($defaultGasRate, 2); ?>" class="form-control" style="border-radius:6px;border:1px solid #cbd5e1;height:38px;font-weight:600;font-size:13px;" oninput="updateSingleBillPreview()" />
            </div>

            <!-- Maintenance Breakdown Box (Shown when ON, hidden when OFF) -->
            <div class="form-group" id="sbMaintGroup" style="margin-bottom:14px;">
                <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:6px;display:block;">
                    <?php echo __('Maintenance Breakdown (Optional Amount Override)'); ?>
                </label>
                <div id="sbMaintBox" style="background:#ffffff;border:none;padding:0;">
                    <div style="background:#ffffff;border:1px dashed #cbd5e1;border-radius:6px;padding:10px 12px;font-size:12px;color:#94a3b8;">
                        <?php echo __('No maintenance items found for selected property.'); ?>
                    </div>
                </div>
            </div>

            <!-- Other Charges Box (Shown when ON, hidden when OFF) -->
            <div class="form-group" id="sbOtherGroup" style="margin-bottom:14px;">
                <label style="font-weight:600;font-size:12px;color:#475569;margin-bottom:6px;display:block;">
                    <?php echo __('Other Charges (Optional Amount Override)'); ?>
                </label>
                <div id="sbOtherBox" style="background:#ffffff;border:none;padding:0;">
                    <div style="background:#ffffff;border:1px dashed #cbd5e1;border-radius:6px;padding:10px 12px;font-size:12px;color:#94a3b8;">
                        <?php echo __('No active other charges for selected property.'); ?>
                    </div>
                </div>
            </div>

            <!-- Total Bill Review (Auto) - 3x3 Grid -->
            <div class="form-group" style="margin-bottom:0;">
                <label style="font-weight:700;font-size:12.5px;color:#334155;margin-bottom:6px;display:block;">
                    <?php echo __('Total Bill Review (Auto)'); ?>
                </label>
                
                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:8px;">
                    <!-- Tile 1: Consumed Units -->
                    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                        <div style="font-size:10px;font-weight:600;color:#64748b;">Consumed Units</div>
                        <div id="statUnits" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">0.00</div>
                    </div>
                    <!-- Tile 2: Utility Charges -->
                    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                        <div style="font-size:10px;font-weight:600;color:#64748b;">Utility Charges</div>
                        <div id="statUtility" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                    </div>
                    <!-- Tile 3: Maintenance Charges -->
                    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                        <div style="font-size:10px;font-weight:600;color:#64748b;">Maintenance Charges</div>
                        <div id="statMaintenance" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                    </div>
                    <!-- Tile 4: Other Charges -->
                    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                        <div style="font-size:10px;font-weight:600;color:#64748b;">Other Charges</div>
                        <div id="statOther" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                    </div>
                    <!-- Tile 5: Arrears -->
                    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                        <div style="font-size:10px;font-weight:600;color:#64748b;">Arrears</div>
                        <div id="statArrears" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                    </div>
                    <!-- Tile 6: Current Bill -->
                    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                        <div style="font-size:10px;font-weight:600;color:#64748b;">Current Bill</div>
                        <div id="statCurrentBill" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                    </div>
                    <!-- Tile 7: Total Bill (Within Due) -->
                    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                        <div style="font-size:10px;font-weight:600;color:#64748b;">Total Bill (Within Due)</div>
                        <div id="statTotalWithinDue" style="font-size:14px;font-weight:800;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                    </div>
                    <!-- Tile 8: Late Fee (After Due) -->
                    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                        <div style="font-size:10px;font-weight:600;color:#64748b;">Late Fee (After Due)</div>
                        <div id="statLateFee" style="font-size:13.5px;font-weight:700;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                    </div>
                    <!-- Tile 9: Payable After Due -->
                    <div style="background:#ffffff;border:1px solid #cbd5e1;border-radius:6px;padding:8px 10px;">
                        <div style="font-size:10px;font-weight:600;color:#64748b;">Payable After Due</div>
                        <div id="statPayableAfterDue" style="font-size:14px;font-weight:800;color:#0f172a;margin-top:2px;">PKR 0.00</div>
                    </div>
                </div>

                <!-- Duplicate Alert Banner -->
                <div id="sbDupBanner" style="margin-top:10px;background:#fff1f2;border:1px solid #fecdd3;border-radius:6px;padding:8px 12px;color:#e11d48;font-size:11.5px;font-weight:600;display:none;">
                    Bill for this Meter No (<span id="sbDupMeterNo"></span>) and month <span id="sbDupMonth"></span> already exists. New creation will be blocked.
                </div>
            </div>

        </div>

        <div style="padding-top:12px;border-top:1px solid #f1f5f9;display:flex;align-items:center;justify-content:flex-start;gap:10px;">
            <button type="submit" id="sbSubmitBtn" class="btn" style="background:#0284c7;color:#ffffff;font-weight:700;padding:8px 30px;border-radius:6px;font-size:13.5px;border:none;box-shadow:none;transition:all 0.2s ease;">
                <?php echo __('Create'); ?>
            </button>
            <a href="<?php echo $this->Html->url(array('action' => 'index')); ?>" class="btn btn-default" style="font-weight:700;border-radius:6px;">
                <?php echo __('Cancel'); ?>
            </a>
        </div>

        <?php echo $this->Form->end(); ?>
    </div>
</div>

<script type="text/javascript">
window.WV_METERS = <?php echo json_encode(!empty($metersData) ? $metersData : array()); ?>;
window.WV_READINGS = <?php echo json_encode(!empty($allReadingsLookup) ? $allReadingsLookup : array()); ?>;
window.WV_LATEST_READINGS = <?php echo json_encode(!empty($latestReadingsLookup) ? $latestReadingsLookup : array()); ?>;
window.WV_PROPERTIES = <?php echo json_encode(!empty($propertiesData) ? $propertiesData : array()); ?>;
window.WV_EXISTING_BILLS = <?php echo json_encode(!empty($existingBillsLookup) ? $existingBillsLookup : array()); ?>;
window.WV_LATE_FEE_RULES = <?php echo json_encode(!empty($lateFeeRules) ? $lateFeeRules : array('mode' => 'percent', 'val' => 7.0, 'min' => 200.0, 'max' => 2000.0)); ?>;
window.WV_DEFAULT_RATES = {
    elec: <?php echo (float)$defaultElecRate; ?>,
    water: <?php echo (float)$defaultWaterRate; ?>,
    gas: <?php echo (float)$defaultGasRate; ?>
};

var defaultDueDaysOffset = <?php echo (int)$defaultDueDays; ?>;

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text).replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

function normalizeMonthKey(raw) {
    if (!raw) return '';
    var s = String(raw).trim();
    var m1 = s.match(/^(\d{4})[-\/](\d{1,2})/);
    if (m1) {
        return m1[1] + '-' + String(m1[2]).padStart(2, '0');
    }
    var m2 = s.match(/^(\d{1,2})[-\/](\d{4})/);
    if (m2) {
        return m2[2] + '-' + String(m2[1]).padStart(2, '0');
    }
    var mNames = { "jan": "01", "feb": "02", "mar": "03", "apr": "04", "may": "05", "jun": "06", "jul": "07", "aug": "08", "sep": "09", "oct": "10", "nov": "11", "dec": "12" };
    var parts = s.toLowerCase().split(/[\s,]+/);
    var yr = '', mo = '';
    parts.forEach(function(p) {
        if (/^\d{4}$/.test(p)) yr = p;
        var sub3 = p.substr(0, 3);
        if (mNames[sub3]) mo = mNames[sub3];
    });
    if (yr && mo) return yr + '-' + mo;
    return s;
}

function formatDisplayMonth(norm) {
    if (!norm) return '';
    var parts = norm.split('-');
    if (parts.length === 2) {
        var mNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        var idx = parseInt(parts[1], 10) - 1;
        if (idx >= 0 && idx < 12) {
            return mNames[idx] + '-' + parts[0];
        }
    }
    return norm;
}

function calcDefaultDueDate() {
    var issueVal = document.getElementById('sbIssueDate').value;
    if (!issueVal) return;
    var dt = new Date(issueVal);
    dt.setDate(dt.getDate() + defaultDueDaysOffset);
    var yyyy = dt.getFullYear();
    var mm = String(dt.getMonth() + 1).padStart(2, '0');
    var dd = String(dt.getDate()).padStart(2, '0');
    var formattedDate = yyyy + '-' + mm + '-' + dd;
    
    var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var previewStr = 'Preview: ' + dd + '-' + monthNames[dt.getMonth()] + '-' + yyyy;
    var prevEl = document.getElementById('sbDuePreviewText');
    if (prevEl) prevEl.innerText = previewStr;
    
    if (document.getElementById('sbDefaultDueCheck').checked) {
        document.getElementById('sbDueDate').value = formattedDate;
    }
}

function toggleDefaultDueDate() {
    var isChecked = document.getElementById('sbDefaultDueCheck').checked;
    if (isChecked) {
        calcDefaultDueDate();
    }
}

function handleSectionVisibility() {
    var maintChecked = $('#sbApplyMaint').is(':checked');
    var otherChecked = $('#sbApplyOther').is(':checked');

    if (maintChecked) {
        $('#sbMaintGroup').show();
    } else {
        $('#sbMaintGroup').hide();
    }

    if (otherChecked) {
        $('#sbOtherGroup').show();
    } else {
        $('#sbOtherGroup').hide();
    }
}

function toggleMaintSection() {
    var isChecked = $('#sbApplyMaint').is(':checked');
    if (isChecked) {
        $('#sbMaintGroup').slideDown(150);
    } else {
        $('#sbMaintGroup').slideUp(150);
    }
    recalcTotalReview();
}

function toggleOtherSection() {
    var isChecked = $('#sbApplyOther').is(':checked');
    if (isChecked) {
        $('#sbOtherGroup').slideDown(150);
    } else {
        $('#sbOtherGroup').slideUp(150);
    }
    recalcTotalReview();
}

function updateSingleBillPreview() {
    handleSectionVisibility();

    var meterEl = document.getElementById('sbMeterId');
    var monthEl = document.getElementById('sbMonth');
    if (!meterEl || !monthEl) return;

    var meterId = meterEl.value;
    var month = monthEl.value;
    var btn = document.getElementById('sbSubmitBtn');

    if (!meterId || !month) {
        resetReviewBoxes();
        return;
    }

    var meter = (window.WV_METERS && window.WV_METERS[meterId]) ? window.WV_METERS[meterId] : null;
    var propId = meter ? meter.property_id : 0;
    var uType = meter ? String(meter.utility_type).toLowerCase().trim() : 'electricity';
    var meterNo = meter ? meter.meter_number : '';

    var normMonth = normalizeMonthKey(month);
    var displayMonth = formatDisplayMonth(normMonth);

    // 1. Reading Lookup for Selected Month
    var rKey1 = meterId + '_' + normMonth;
    var rKey2 = meterId + '_' + month;
    var reading = null;
    if (window.WV_READINGS) {
        if (window.WV_READINGS[rKey1]) {
            reading = window.WV_READINGS[rKey1];
        } else if (window.WV_READINGS[rKey2]) {
            reading = window.WV_READINGS[rKey2];
        }
    }

    var latestReading = (window.WV_LATEST_READINGS && window.WV_LATEST_READINGS[meterId]) ? window.WV_LATEST_READINGS[meterId] : null;

    var readingNotice = document.getElementById('sbReadingNotice');
    if (reading) {
        var pR = parseFloat(reading.previous_reading);
        var cR = parseFloat(reading.current_reading);
        var uC = parseFloat(reading.units_consumed);
        if (readingNotice) {
            readingNotice.innerHTML = '<div style="color:#065f46;background:#d1fae5;border:1.5px solid #34d399;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;margin-top:6px;"><i class="fa fa-check-circle"></i> Reading found for <strong>' + displayMonth + '</strong>: Prev <strong>' + pR.toFixed(2) + '</strong> &rarr; Curr <strong>' + cR.toFixed(2) + '</strong> = <strong>' + uC.toFixed(2) + ' consumed units</strong></div>';
        }
    } else {
        var latestInfo = '';
        if (latestReading && latestReading.reading_month) {
            latestInfo = ' (Latest saved: ' + formatDisplayMonth(latestReading.reading_month) + ' with ' + parseFloat(latestReading.units_consumed).toFixed(2) + ' units).';
        }
        if (readingNotice) {
            readingNotice.innerHTML = '<div style="color:#991b1b;background:#fee2e2;border:1.5px solid #f87171;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;margin-top:6px;"><i class="fa fa-exclamation-triangle"></i> Error / Notice: This month (<strong>' + displayMonth + '</strong>) has no saved reading.' + latestInfo + ' Consumed units set to <strong>0.00</strong>. (Bill can still be generated).</div>';
        }
    }

    // 2. Populate Maintenance Breakdown inputs
    var propData = (window.WV_PROPERTIES && window.WV_PROPERTIES[propId]) ? window.WV_PROPERTIES[propId] : null;
    var maintItems = (propData && propData.maintenance_items) ? propData.maintenance_items : [];
    
    if (maintItems.length > 0) {
        var mHtml = '';
        maintItems.forEach(function(item) {
            mHtml += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">';
            mHtml += '<span style="font-weight:700;font-size:12px;color:#1e3a8a;">' + item.name + '</span>';
            mHtml += '<input type="number" step="0.01" class="form-control sb-maint-input" name="data[BillingBill][maint_items][' + item.name + ']" value="' + parseFloat(item.amount).toFixed(2) + '" style="width:140px;height:36px;border-radius:6px;font-weight:600;text-align:right;border:1px solid #cbd5e1;" oninput="recalcTotalReview()" />';
            mHtml += '</div>';
        });
        document.getElementById('sbMaintBox').innerHTML = mHtml;
    } else {
        document.getElementById('sbMaintBox').innerHTML = '<div style="background:#ffffff;border:1px dashed #cbd5e1;border-radius:6px;padding:10px 12px;font-size:12px;color:#94a3b8;"><?php echo __("No maintenance items found for selected property."); ?></div>';
    }

    // 3. Populate Other Charges inputs
    var otherItems = (propData && propData.other_charges_items) ? propData.other_charges_items : [];
    if (otherItems.length > 0) {
        var oHtml = '';
        otherItems.forEach(function(item) {
            oHtml += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">';
            oHtml += '<span style="font-weight:700;font-size:12px;color:#1e3a8a;">' + item.name + '</span>';
            oHtml += '<input type="number" step="0.01" class="form-control sb-other-input" name="data[BillingBill][other_items][' + item.name + ']" value="' + parseFloat(item.amount).toFixed(2) + '" style="width:140px;height:36px;border-radius:6px;font-weight:600;text-align:right;border:1px solid #cbd5e1;" oninput="recalcTotalReview()" />';
            oHtml += '</div>';
        });
        document.getElementById('sbOtherBox').innerHTML = oHtml;
    } else {
        document.getElementById('sbOtherBox').innerHTML = '<div style="background:#ffffff;border:1px dashed #cbd5e1;border-radius:6px;padding:10px 12px;font-size:12px;color:#94a3b8;"><?php echo __("No active other charges for selected property."); ?></div>';
    }

    // 4. Check duplicate bill
    var dupKey1 = propId + '_' + uType + '_' + normMonth;
    var dupKey2 = propId + '_' + uType + '_' + month;
    var existingBill = null;
    if (window.WV_EXISTING_BILLS) {
        if (window.WV_EXISTING_BILLS[dupKey1]) {
            existingBill = window.WV_EXISTING_BILLS[dupKey1];
        } else if (window.WV_EXISTING_BILLS[dupKey2]) {
            existingBill = window.WV_EXISTING_BILLS[dupKey2];
        }
    }

    var dupBanner = document.getElementById('sbDupBanner');
    var dupMeterNo = document.getElementById('sbDupMeterNo');
    var dupMonth = document.getElementById('sbDupMonth');

    if (existingBill) {
        if (dupBanner) {
            dupBanner.style.display = 'block';
            dupBanner.innerHTML = '<i class="fa fa-ban"></i> Error: Bill for this Meter No (<strong>' + (meterNo || '') + '</strong>) and month <strong>' + displayMonth + '</strong> already exists (Bill #' + (existingBill.bill_number || '') + ' &mdash; Total PKR ' + parseFloat(existingBill.total_amount || 0).toFixed(2) + '). New creation is blocked.';
        }
        if (btn) {
            btn.disabled = true;
            btn.style.background = '#94a3b8';
            btn.style.cursor = 'not-allowed';
            btn.innerHTML = '🚫 <?php echo __("Duplicate Blocked"); ?>';
        }
    } else {
        if (dupBanner) dupBanner.style.display = 'none';
        if (btn) {
            btn.disabled = false;
            btn.style.background = '#0284c7';
            btn.style.cursor = 'pointer';
            btn.innerHTML = '<?php echo __("Create"); ?>';
        }
    }

    recalcTotalReview();

    // 5. Live Server Validation & Calculation via AJAX
    var previewUrl = '<?php echo $this->Html->url(array("plugin" => "billing_manager", "controller" => "billing_bills", "action" => "get_meter_bill_preview")); ?>';
    $.ajax({
        url: previewUrl,
        type: 'GET',
        dataType: 'json',
        data: {
            meter_id: meterId,
            month: normMonth,
            rate_elec: $('#sbRateElec').val(),
            rate_water: $('#sbRateWater').val(),
            rate_gas: $('#sbRateGas').val(),
            apply_maint: $('#sbApplyMaint').is(':checked') ? 1 : 0,
            apply_other: $('#sbApplyOther').is(':checked') ? 1 : 0
        },
        success: function(res) {
            if (res && res.status === 'success') {
                document.getElementById('statUnits').innerText = parseFloat(res.units_consumed).toFixed(2);
                document.getElementById('statUtility').innerText = 'PKR ' + parseFloat(res.consumption_amount).toFixed(2);
                document.getElementById('statMaintenance').innerText = 'PKR ' + parseFloat(res.maintenance_amount).toFixed(2);
                document.getElementById('statOther').innerText = 'PKR ' + parseFloat(res.other_charges_amount).toFixed(2);
                document.getElementById('statArrears').innerText = 'PKR ' + parseFloat(res.arrears_amount).toFixed(2);
                document.getElementById('statCurrentBill').innerText = 'PKR ' + parseFloat(res.current_bill).toFixed(2);
                document.getElementById('statTotalWithinDue').innerText = 'PKR ' + parseFloat(res.total_within_due).toFixed(2);
                document.getElementById('statLateFee').innerText = 'PKR ' + parseFloat(res.late_fee).toFixed(2);
                document.getElementById('statPayableAfterDue').innerText = 'PKR ' + parseFloat(res.payable_after_due).toFixed(2);

                var rNotice = document.getElementById('sbReadingNotice');
                if (res.reading_found) {
                    if (rNotice) {
                        rNotice.innerHTML = '<div style="color:#065f46;background:#d1fae5;border:1.5px solid #34d399;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;margin-top:6px;"><i class="fa fa-check-circle"></i> Reading found for <strong>' + displayMonth + '</strong>: Prev <strong>' + parseFloat(res.previous_reading).toFixed(2) + '</strong> &rarr; Curr <strong>' + parseFloat(res.current_reading).toFixed(2) + '</strong> = <strong>' + parseFloat(res.units_consumed).toFixed(2) + ' consumed units</strong></div>';
                    }
                } else {
                    var lInfo = '';
                    if (res.latest_reading_month) {
                        lInfo = ' (Latest saved: ' + formatDisplayMonth(res.latest_reading_month) + ' with ' + parseFloat(res.latest_reading_units).toFixed(2) + ' units).';
                    }
                    if (rNotice) {
                        rNotice.innerHTML = '<div style="color:#991b1b;background:#fee2e2;border:1.5px solid #f87171;border-radius:6px;padding:8px 12px;font-size:12px;font-weight:700;margin-top:6px;"><i class="fa fa-exclamation-triangle"></i> Error / Notice: This month (<strong>' + displayMonth + '</strong>) has no saved reading.' + lInfo + ' Consumed units set to <strong>0.00</strong>. (Bill can still be generated).</div>';
                    }
                }

                var dBanner = document.getElementById('sbDupBanner');
                if (res.is_duplicate) {
                    if (dBanner) {
                        dBanner.style.display = 'block';
                        dBanner.innerHTML = '<i class="fa fa-ban"></i> Error: Bill for this Meter No (<strong>' + (res.meter_number || '') + '</strong>) and month <strong>' + displayMonth + '</strong> already exists (Bill #' + (res.existing_bill_number || '') + ' &mdash; Total PKR ' + parseFloat(res.existing_total_amount || 0).toFixed(2) + '). New creation is blocked.';
                    }
                    if (btn) {
                        btn.disabled = true;
                        btn.style.background = '#94a3b8';
                        btn.style.cursor = 'not-allowed';
                        btn.innerHTML = '🚫 Duplicate Blocked';
                    }
                } else {
                    if (dBanner) dBanner.style.display = 'none';
                    if (btn) {
                        btn.disabled = false;
                        btn.style.background = '#0284c7';
                        btn.style.cursor = 'pointer';
                        btn.innerHTML = 'Create';
                    }
                }
            }
        }
    });
}

function recalcTotalReview() {
    var meterEl = document.getElementById('sbMeterId');
    var monthEl = document.getElementById('sbMonth');
    if (!meterEl || !monthEl || !meterEl.value || !monthEl.value) {
        resetReviewBoxes();
        return;
    }

    var meterId = meterEl.value;
    var month = monthEl.value;
    var meter = (window.WV_METERS && window.WV_METERS[meterId]) ? window.WV_METERS[meterId] : null;
    var propId = meter ? meter.property_id : 0;
    var uType = meter ? String(meter.utility_type).toLowerCase().trim() : 'electricity';

    var normMonth = normalizeMonthKey(month);

    // 1. Reading & Units lookup
    var rKey1 = meterId + '_' + normMonth;
    var rKey2 = meterId + '_' + month;
    var reading = null;
    if (window.WV_READINGS) {
        if (window.WV_READINGS[rKey1]) {
            reading = window.WV_READINGS[rKey1];
        } else if (window.WV_READINGS[rKey2]) {
            reading = window.WV_READINGS[rKey2];
        }
    }

    var units = 0.0;
    if (reading) {
        units = parseFloat(reading.units_consumed);
        if (isNaN(units)) units = 0.0;
    }

    // 2. Unit Rate
    var rateElecInp = $('#sbRateElec').val();
    var rateWaterInp = $('#sbRateWater').val();
    var rateGasInp = $('#sbRateGas').val();
    var defRates = window.WV_DEFAULT_RATES || { elec: 58.34, water: 100, gas: 350 };

    var unitRate = defRates.elec;
    if (uType === 'water') {
        unitRate = (rateWaterInp !== '' && !isNaN(parseFloat(rateWaterInp))) ? parseFloat(rateWaterInp) : defRates.water;
    } else if (uType === 'gas') {
        unitRate = (rateGasInp !== '' && !isNaN(parseFloat(rateGasInp))) ? parseFloat(rateGasInp) : defRates.gas;
    } else {
        unitRate = (rateElecInp !== '' && !isNaN(parseFloat(rateElecInp))) ? parseFloat(rateElecInp) : defRates.elec;
    }

    var utilityAmt = units * unitRate;

    // 3. Maintenance Sum
    var maintTotal = 0.0;
    var applyMaint = $('#sbApplyMaint').is(':checked');
    if (applyMaint) {
        $('.sb-maint-input').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) maintTotal += val;
        });
    }

    // 4. Other Charges Sum
    var otherTotal = 0.0;
    var applyOther = $('#sbApplyOther').is(':checked');
    if (applyOther) {
        $('.sb-other-input').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) otherTotal += val;
        });
    }

    // 5. Arrears (Past unpaid balances auto carry-forward)
    var propData = (window.WV_PROPERTIES && window.WV_PROPERTIES[propId]) ? window.WV_PROPERTIES[propId] : null;
    var arrearsAmt = propData ? parseFloat(propData.arrears_amount) : 0.0;

    // 6. Current Bill & Totals
    var currentBill = utilityAmt + maintTotal + otherTotal;
    var totalWithinDue = currentBill + arrearsAmt;

    // 7. Late Fee: from backend settings rules
    var lateFee = 0.0;
    if (currentBill > 0) {
        var rules = window.WV_LATE_FEE_RULES || { mode: 'percent', val: 7.0, min: 200.0, max: 2000.0 };
        if (rules.mode === 'fixed') {
            lateFee = parseFloat(rules.val);
        } else {
            var pctAmt = currentBill * (parseFloat(rules.val) / 100.0);
            lateFee = Math.max(parseFloat(rules.min), Math.min(parseFloat(rules.max), Math.round(pctAmt)));
        }
    }
    var payableAfterDue = totalWithinDue + lateFee;

    // Update 9 Stat Tiles
    document.getElementById('statUnits').innerText = units.toFixed(2);
    document.getElementById('statUtility').innerText = 'PKR ' + utilityAmt.toFixed(2);
    document.getElementById('statMaintenance').innerText = 'PKR ' + maintTotal.toFixed(2);
    document.getElementById('statOther').innerText = 'PKR ' + otherTotal.toFixed(2);
    document.getElementById('statArrears').innerText = 'PKR ' + arrearsAmt.toFixed(2);
    document.getElementById('statCurrentBill').innerText = 'PKR ' + currentBill.toFixed(2);
    document.getElementById('statTotalWithinDue').innerText = 'PKR ' + totalWithinDue.toFixed(2);
    document.getElementById('statLateFee').innerText = 'PKR ' + lateFee.toFixed(2);
    document.getElementById('statPayableAfterDue').innerText = 'PKR ' + payableAfterDue.toFixed(2);
}

function resetReviewBoxes() {
    document.getElementById('statUnits').innerText = '0.00';
    document.getElementById('statUtility').innerText = 'PKR 0.00';
    document.getElementById('statMaintenance').innerText = 'PKR 0.00';
    document.getElementById('statOther').innerText = 'PKR 0.00';
    document.getElementById('statArrears').innerText = 'PKR 0.00';
    document.getElementById('statCurrentBill').innerText = 'PKR 0.00';
    document.getElementById('statTotalWithinDue').innerText = 'PKR 0.00';
    document.getElementById('statLateFee').innerText = 'PKR 0.00';
    document.getElementById('statPayableAfterDue').innerText = 'PKR 0.00';
    var readingNotice = document.getElementById('sbReadingNotice');
    if (readingNotice) readingNotice.innerHTML = '';
    var dupBanner = document.getElementById('sbDupBanner');
    if (dupBanner) dupBanner.style.display = 'none';
    var btn = document.getElementById('sbSubmitBtn');
    if (btn) {
        btn.disabled = false;
        btn.style.background = '#0284c7';
        btn.style.cursor = 'pointer';
        btn.innerHTML = '<?php echo __("Create"); ?>';
    }
}

jQuery(document).ready(function($) {
    if ($.fn.select2) {
        $('#sbMeterId').select2({
            placeholder: '<?php echo __("Select"); ?>',
            allowClear: true,
            width: '100%'
        }).on('change select2:select select2:unselect', function() {
            updateSingleBillPreview();
        });
    } else {
        $('#sbMeterId').on('change', updateSingleBillPreview);
    }

    $('#sbMonth, #sbRateElec, #sbRateWater, #sbRateGas').on('input change blur keyup paste', updateSingleBillPreview);
    $('#sbIssueDate').on('change blur', calcDefaultDueDate);
    $('#sbDefaultDueCheck').on('change', toggleDefaultDueDate);

    handleSectionVisibility();
    calcDefaultDueDate();
    if ($('#sbMeterId').val()) {
        updateSingleBillPreview();
    } else {
        var firstVal = $('#sbMeterId option[value!=""]').first().val();
        if (firstVal) {
            $('#sbMeterId').val(firstVal).trigger('change');
        }
    }
});
</script>
