<div class="container" style="max-width:800px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <div style="background:#0f172a;color:#fff;border-radius:16px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-size:11px;color:#38bdf8;text-transform:uppercase;font-weight:800;">📱 Field Web PWA App</div>
            <h2 style="margin:4px 0 0 0;font-size:20px;font-weight:900;">⚡ Meter Reader Mobile Portal</h2>
        </div>
        <div>
            <span style="background:rgba(255,255,255,0.1);padding:6px 12px;border-radius:8px;font-size:12px;font-weight:700;">
                <?php echo date('M Y'); ?>
            </span>
        </div>
    </div>

    <?php echo $this->Session->flash(); ?>

    <!-- Form to submit a reading -->
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 10px 25px -5px rgba(0,0,0,0.05);">
        <form method="post" action="<?php echo $this->Html->url(array('action' => 'meter_reader')); ?>" id="meterReaderForm">
            <div style="margin-bottom:16px;">
                <label style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;">Select Utility Meter <span style="color:#ef4444;">*</span></label>
                <select name="data[Reading][meter_id]" id="meterSelect" required style="width:100%;box-sizing:border-box;padding:12px;border:2px solid #cbd5e1;border-radius:10px;font-size:15px;font-weight:700;margin-top:6px;">
                    <option value="">-- Choose Meter Number / Unit --</option>
                    <?php foreach($meters as $m){ $mt = $m['BillingMeter']; $pr = $m['BillingProperty']; $cl = isset($pr['BillingClient']) ? $pr['BillingClient'] : array(); ?>
                        <option value="<?php echo $mt['id']; ?>" data-meter-no="<?php echo h($mt['meter_number']); ?>" data-property-id="<?php echo (int)$mt['billing_property_id']; ?>" data-prev="<?php echo (float)$mt['current_reading']; ?>">
                            <?php echo h($mt['meter_number']); ?> &mdash; <?php echo h(!empty($pr['title']) ? $pr['title'] : ''); ?> (<?php echo h(!empty($cl['name']) ? $cl['name'] : 'No Consumer'); ?>)
                        </option>
                    <?php } ?>
                </select>
                <input type="hidden" name="data[Reading][property_id]" id="propertyId" value="" />
                <input type="hidden" name="data[Reading][photo_url]" id="photoUrl" value="" />
            </div>

            <!-- CAMERA OCR & SCANNER SECTION -->
            <div style="background:#f8fafc;border:2px dashed #0284c7;border-radius:12px;padding:16px;margin-bottom:20px;text-align:center;">
                <div style="display:flex;align-items:center;justify-content:center;gap:10px;margin-bottom:10px;">
                    <button type="button" id="btnToggleCamera" style="background:#0284c7;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:800;cursor:pointer;font-size:13.5px;display:inline-flex;align-items:center;gap:8px;">
                        <span class="fa fa-camera"></span> 📸 Open Camera OCR Scanner
                    </button>
                    <label style="background:#334155;color:#fff;padding:10px 18px;border-radius:8px;font-weight:700;cursor:pointer;font-size:13px;margin:0;">
                        📁 Upload Photo <input type="file" id="fileUploadInput" accept="image/*" capture="environment" style="display:none;" />
                    </label>
                </div>

                <!-- Video Viewfinder Container -->
                <div id="cameraViewContainer" style="display:none;margin-top:12px;position:relative;max-width:400px;margin-left:auto;margin-right:auto;">
                    <video id="cameraVideo" playsinline autoplay style="width:100%;border-radius:10px;background:#000;border:2px solid #0284c7;"></video>
                    <div style="position:absolute;top:25%;left:10%;right:10%;bottom:35%;border:2px dashed #10b981;border-radius:6px;pointer-events:none;box-shadow:0 0 0 9999px rgba(0,0,0,0.4);">
                        <span style="position:absolute;top:4px;left:6px;color:#10b981;font-size:10px;font-weight:800;text-transform:uppercase;">Align Meter Counter</span>
                    </div>
                    <div style="margin-top:10px;display:flex;gap:8px;justify-content:center;">
                        <button type="button" id="btnCaptureFrame" style="background:#10b981;color:#fff;border:none;padding:8px 18px;border-radius:6px;font-weight:800;cursor:pointer;">
                            ⚡ Snap & Read Digits
                        </button>
                        <button type="button" id="btnCloseCamera" style="background:#dc2626;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-weight:800;cursor:pointer;">
                            ✕ Close
                        </button>
                    </div>
                </div>

                <!-- Preview Thumbnail of Captured/Uploaded Photo -->
                <div id="photoPreviewBox" style="display:none;margin-top:10px;align-items:center;justify-content:center;gap:12px;">
                    <img id="capturedThumb" src="" style="height:60px;width:60px;object-fit:cover;border-radius:8px;border:2px solid #10b981;" />
                    <span style="font-size:12px;color:#059669;font-weight:800;">&#10004; Meter Photo Attached</span>
                    <button type="button" id="btnRemovePhoto" style="background:none;border:none;color:#dc2626;font-weight:700;cursor:pointer;font-size:12px;">[Remove]</button>
                </div>
            </div>

            <!-- Readings Inputs Grid -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                <div>
                    <label style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;">Previous Reading</label>
                    <input type="number" step="0.01" readonly name="data[Reading][previous_reading]" id="prevReading" value="0.00" style="width:100%;box-sizing:border-box;padding:12px;background:#f1f5f9;border:2px solid #cbd5e1;border-radius:10px;font-size:16px;font-weight:800;color:#475569;margin-top:6px;" />
                </div>
                <div>
                    <label style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;">Current Reading <span style="color:#ef4444;">*</span></label>
                    <input type="number" step="0.01" name="data[Reading][current_reading]" id="currReading" required placeholder="0.00" style="width:100%;box-sizing:border-box;padding:12px;border:2px solid #0284c7;border-radius:10px;font-size:16px;font-weight:800;color:#0284c7;margin-top:6px;" />
                </div>
            </div>

            <!-- Solar Net Export Field (Optional) -->
            <div style="margin-bottom:16px;">
                <label style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;">Solar Export Reading (kWh - If Net-Metered)</label>
                <input type="number" step="0.01" name="data[Reading][export_reading]" id="exportReading" value="" placeholder="0.00 (Optional for Solar Customers)" style="width:100%;box-sizing:border-box;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:10px;font-size:14px;margin-top:6px;" />
            </div>

            <!-- Live Consumption & Anomaly Alert Indicator -->
            <div id="anomalyAlertBox" style="display:none;padding:12px 16px;border-radius:8px;margin-bottom:18px;font-weight:700;font-size:13px;"></div>

            <div style="margin-bottom:20px;">
                <label style="font-size:12px;font-weight:800;color:#334155;text-transform:uppercase;">Reader Staff Name</label>
                <input type="text" name="data[Reading][reader_name]" value="Field Staff" style="width:100%;box-sizing:border-box;padding:10px 14px;border:2px solid #cbd5e1;border-radius:10px;font-size:14px;margin-top:6px;" />
            </div>

            <button type="submit" style="width:100%;background:#10b981;color:#fff;font-weight:900;padding:14px;border:none;border-radius:10px;font-size:16px;cursor:pointer;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                💾 Save Reading Entry
            </button>
        </form>
    </div>
</div>

<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('meterSelect');
    var prevInput = document.getElementById('prevReading');
    var currInput = document.getElementById('currReading');
    var alertBox = document.getElementById('anomalyAlertBox');
    var videoElem = document.getElementById('cameraVideo');
    var cameraContainer = document.getElementById('cameraViewContainer');
    var streamObj = null;

    select.addEventListener('change', function() {
        var opt = select.options[select.selectedIndex];
        var propId = opt.getAttribute('data-property-id') || 0;
        var prev = opt.getAttribute('data-prev') || 0;
        document.getElementById('propertyId').value = propId;
        prevInput.value = parseFloat(prev).toFixed(2);
        currInput.value = '';
        validateAnomaly();
    });

    currInput.addEventListener('input', validateAnomaly);

    function validateAnomaly() {
        var prev = parseFloat(prevInput.value) || 0;
        var curr = parseFloat(currInput.value);
        if(isNaN(curr)) {
            alertBox.style.display = 'none';
            return;
        }

        var units = curr - prev;
        if(curr < prev) {
            alertBox.style.display = 'block';
            alertBox.style.background = '#fee2e2';
            alertBox.style.border = '1.5px solid #ef4444';
            alertBox.style.color = '#991b1b';
            alertBox.innerHTML = '⚠️ <strong>Rollback Alert:</strong> Current reading is less than previous reading (' + prev.toFixed(2) + '). Verify counter carefully!';
        } else if(units > 600) {
            alertBox.style.display = 'block';
            alertBox.style.background = '#fffbeb';
            alertBox.style.border = '1.5px solid #f59e0b';
            alertBox.style.color = '#92400e';
            alertBox.innerHTML = '⚡ <strong>High Consumption Alert:</strong> Consumed ' + units.toFixed(1) + ' units. Please double-check photo and meter digits before saving.';
        } else {
            alertBox.style.display = 'block';
            alertBox.style.background = '#f0fdf4';
            alertBox.style.border = '1.5px solid #86efac';
            alertBox.style.color = '#166534';
            alertBox.innerHTML = '✓ <strong>Consumption Validated:</strong> ' + units.toFixed(1) + ' units consumed.';
        }
    }

    // Camera Handler
    document.getElementById('btnToggleCamera').addEventListener('click', function() {
        if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            cameraContainer.style.display = 'block';
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(function(stream) {
                streamObj = stream;
                videoElem.srcObject = stream;
            })
            .catch(function(err) {
                alert('Could not access device camera: ' + err.message);
                cameraContainer.style.display = 'none';
            });
        } else {
            alert('Camera access is not supported on this browser.');
        }
    });

    document.getElementById('btnCloseCamera').addEventListener('click', function() {
        if(streamObj) {
            streamObj.getTracks().forEach(function(track) { track.stop(); });
        }
        cameraContainer.style.display = 'none';
    });

    document.getElementById('btnCaptureFrame').addEventListener('click', function() {
        if(!videoElem.videoWidth) return;
        var canvas = document.createElement('canvas');
        canvas.width = videoElem.videoWidth;
        canvas.height = videoElem.videoHeight;
        var ctx = canvas.getContext('2d');
        ctx.drawImage(videoElem, 0, 0);

        var dataUrl = canvas.toDataURL('image/jpeg', 0.85);
        uploadAndAttachPhoto(dataUrl);

        // Turn off camera
        if(streamObj) {
            streamObj.getTracks().forEach(function(track) { track.stop(); });
        }
        cameraContainer.style.display = 'none';
    });

    // File Upload Handler
    document.getElementById('fileUploadInput').addEventListener('change', function(e) {
        if(e.target.files && e.target.files[0]) {
            var reader = new FileReader();
            reader.onload = function(evt) {
                uploadAndAttachPhoto(evt.target.result);
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    function uploadAndAttachPhoto(base64) {
        var opt = select.options[select.selectedIndex];
        var meterNo = opt ? opt.getAttribute('data-meter-no') : 'METER';

        var formData = new FormData();
        formData.append('image_data', base64);
        formData.append('meter_number', meterNo);

        fetch('<?php echo $this->Html->url(array('controller' => 'billing_readings', 'action' => 'upload_meter_photo')); ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if(data.status === 'success') {
                document.getElementById('photoUrl').value = data.url;
                document.getElementById('capturedThumb').src = data.url;
                document.getElementById('photoPreviewBox').style.display = 'flex';
            }
        })
        .catch(function(err) {});
    }

    document.getElementById('btnRemovePhoto').addEventListener('click', function() {
        document.getElementById('photoUrl').value = '';
        document.getElementById('photoPreviewBox').style.display = 'none';
    });
});
</script>
