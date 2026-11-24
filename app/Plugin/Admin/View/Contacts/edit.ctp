<style type="text/css">
.wv-contact-edit-page {
    background: #f4f7fb;
    margin: -10px -10px 0;
    padding: 20px;
    font-family: inherit;
}
.wv-contact-edit-hero {
    align-items: center;
    background: linear-gradient(135deg, #07152a 0%, #0f274a 60%, #1e3a8a 100%);
    border-radius: 14px;
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.18);
    color: #fff;
    display: flex;
    gap: 18px;
    justify-content: space-between;
    margin-bottom: 22px;
    overflow: hidden;
    padding: 24px 28px;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.wv-contact-edit-hero::after {
    background: radial-gradient(circle at 85% 10%, rgba(124, 249, 255, 0.22), rgba(124, 249, 255, 0) 45%);
    content: "";
    inset: 0;
    position: absolute;
    pointer-events: none;
}
.wv-contact-edit-hero > * {
    position: relative;
    z-index: 2;
}
.wv-contact-edit-title {
    align-items: center;
    display: flex;
    gap: 16px;
}
.wv-contact-edit-icon {
    align-items: center;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 12px;
    color: #7cf9ff;
    display: flex;
    font-size: 26px;
    height: 56px;
    justify-content: center;
    width: 56px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}
.wv-contact-edit-title h1 {
    color: #fff;
    font-size: 26px;
    font-weight: 900;
    line-height: 1.2;
    margin: 0 0 4px;
    letter-spacing: -0.02em;
}
.wv-contact-edit-title p {
    color: #cbd5e1;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
    margin: 0;
}
.wv-contact-edit-badge {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 999px;
    color: #fff;
    font-size: 12px;
    font-weight: 800;
    padding: 8px 16px;
    white-space: nowrap;
}

.wv-contact-edit-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
    margin-bottom: 22px;
    overflow: hidden;
}
.wv-contact-card-head {
    align-items: center;
    background: #fcfdfe;
    border-bottom: 1px solid #edf2f7;
    display: flex;
    gap: 12px;
    justify-content: space-between;
    padding: 16px 22px;
}
.wv-contact-card-head strong {
    color: #07152a;
    display: block;
    font-size: 16px;
    font-weight: 900;
}
.wv-contact-card-head span {
    color: #64748b;
    display: block;
    font-size: 12px;
    font-weight: 600;
    margin-top: 2px;
}
.wv-contact-edit-body {
    padding: 22px;
}
.wv-form-section-title {
    align-items: center;
    border-bottom: 1px solid #edf2f7;
    color: #07152a;
    display: flex;
    font-size: 14px;
    font-weight: 900;
    gap: 8px;
    letter-spacing: 0.02em;
    margin: 0 0 16px;
    padding-bottom: 8px;
    text-transform: uppercase;
}
.wv-form-section-title i {
    color: #2563eb;
    font-size: 16px;
}

.wv-contact-edit-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.wv-contact-edit-field {
    min-width: 0;
}
.wv-contact-edit-field label {
    color: #334155;
    display: block;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.04em;
    line-height: 1.25;
    margin: 0 0 6px;
    text-align: left;
    text-transform: uppercase;
}
.wv-contact-edit-field .form-control,
.wv-contact-edit-field select,
.wv-contact-edit-field textarea {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-shadow: none;
    color: #0f172a;
    font-weight: 600;
    height: 42px;
    max-width: none !important;
    min-width: 0;
    width: 100% !important;
    transition: all 0.2s ease;
}
.wv-contact-edit-field textarea.form-control {
    height: auto;
    min-height: 84px;
    resize: vertical;
}
.wv-contact-edit-field .form-control:focus,
.wv-contact-edit-field select:focus,
.wv-contact-edit-field textarea:focus {
    background: #fff;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    outline: none;
}
.wv-contact-edit-full {
    grid-column: 1 / -1;
}

/* Uploads Container & Modern Cards */
.wv-uploads-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}
.wv-upload-box {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    transition: all 0.25s ease;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
}
.wv-upload-box:hover {
    border-color: #93c5fd;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.08);
}
.wv-upload-header {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f1f5f9;
}
.wv-upload-title {
    font-size: 12px;
    font-weight: 800;
    color: #1e293b;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.wv-upload-status-badge {
    font-size: 10px;
    font-weight: 800;
    padding: 3px 8px;
    border-radius: 999px;
    text-transform: uppercase;
}
.wv-status-uploaded {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.wv-status-none {
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}
.wv-status-new {
    background: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}
.wv-status-removed {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.wv-upload-stage {
    width: 100%;
    height: 150px;
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
    cursor: pointer;
    transition: all 0.2s ease;
    margin: 0;
    padding: 4px;
}
.wv-upload-stage:hover {
    border-color: #3b82f6;
    background: #f0f7ff;
}
.wv-upload-stage img {
    max-width: 100%;
    max-height: 100%;
    width: auto;
    height: auto;
    object-fit: contain;
    border-radius: 6px;
    transition: transform 0.2s ease;
    background: #fff;
}
.wv-upload-stage:hover img {
    transform: scale(1.02);
}
.wv-upload-empty-placeholder {
    text-align: center;
    color: #94a3b8;
    padding: 10px;
}
.wv-upload-empty-placeholder i {
    font-size: 32px;
    display: block;
    margin-bottom: 6px;
    color: #cbd5e1;
}
.wv-upload-empty-placeholder span {
    font-size: 11px;
    font-weight: 700;
    display: block;
    color: #64748b;
}

.wv-upload-meta {
    width: 100%;
    margin-top: 10px;
    font-size: 11px;
    color: #64748b;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-height: 18px;
}

.wv-upload-actions {
    display: flex;
    gap: 8px;
    width: 100%;
    margin-top: 10px;
}
.wv-upload-actions label.btn-browse {
    flex: 1;
    background: #2563eb !important;
    color: #fff !important;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 800;
    padding: 8px 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border: 0;
    cursor: pointer;
    margin: 0;
    transition: background 0.2s ease;
}
.wv-upload-actions label.btn-browse:hover {
    background: #1d4ed8 !important;
}
.wv-upload-actions .btn-remove {
    background: #fee2e2 !important;
    color: #dc2626 !important;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 800;
    padding: 8px 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    cursor: pointer;
    transition: background 0.2s ease;
}
.wv-upload-actions .btn-remove:hover {
    background: #fca5a5 !important;
}

/* Bottom Action Bar */
.wv-contact-edit-actions {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    display: flex;
    gap: 12px;
    justify-content: flex-start;
    padding: 18px 24px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
}
.wv-contact-edit-actions .btn {
    border: 0;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 800;
    padding: 12px 24px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}
.wv-contact-edit-actions .btn-success {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
    color: #fff !important;
}
.wv-contact-edit-actions .btn-success:hover {
    background: #047857 !important;
}
.wv-contact-edit-actions .btn-secondary {
    background: #07152a !important;
    color: #fff !important;
}
.wv-contact-edit-actions .btn-secondary:hover {
    background: #1e293b !important;
}

/* CRM Lead / Deal block styling */
.lead-contact-reverse {
    border: 1px solid #fde68a;
    border-radius: 10px;
    background: #fffbeb;
    margin-top: 20px;
    overflow: hidden;
}
.lead-contact-reverse .panel-heading {
    background: #fef3c7;
    border-bottom: 1px solid #fde68a;
    color: #92400e;
    padding: 12px 18px;
    font-weight: 800;
}
.lead-contact-reverse .panel-body {
    padding: 16px 18px;
}
.lead-contact-blocked {
    border-color: #fecaca;
    background: #fef2f2;
}
.lead-contact-blocked .panel-heading {
    background: #fee2e2;
    border-bottom-color: #fecaca;
    color: #991b1b;
}

/* Ensure no modal ever renders visible inline */
.modal {
    display: none !important;
}

@media(max-width: 1199px) {
    .wv-uploads-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media(max-width: 991px) {
    .wv-contact-edit-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}
@media(max-width: 640px) {
    .wv-contact-edit-page {
        padding: 12px;
        margin: 0;
    }
    .wv-contact-edit-hero {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 18px;
    }
    .wv-contact-edit-grid {
        grid-template-columns: 1fr;
    }
    .wv-uploads-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
if(!function_exists('wvResolveContactImage')){
    function wvResolveContactImage($filename, $customDir = '', $webroot = '/'){
        if(empty($filename)){
            return array('original' => '', 'thumb' => '');
        }
        $file = basename(trim((string)$filename));
        $dir = !empty($customDir) ? basename(trim((string)$customDir)) : 'contact';
        $candidates = array(
            'contact/' . $file,
            'contact_thumb/' . $file,
            $dir . '/' . $file,
            $dir . '_thumb/' . $file,
            'client/' . $file,
            'client_thumb/' . $file,
            'Uploads/clients/' . $file,
            'Uploads/images/' . $file
        );
        $base = rtrim((string)$webroot, '/');
        $original = '';
        $thumb = '';
        foreach(array_unique($candidates) as $candidate){
            $realPath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $candidate);
            if(file_exists($realPath)){
                if($original === '' && strpos($candidate, '_thumb') === false){
                    $original = $base . '/img/' . $candidate;
                }
                if($thumb === ''){
                    $thumb = $base . '/img/' . $candidate;
                }
            }
        }
        if($original === '') $original = $base . '/img/contact/' . $file;
        if($thumb === '') $thumb = $base . '/img/contact_thumb/' . $file;
        return array('original' => $original, 'thumb' => $thumb);
    }
}
?>

<div class="wv-contact-edit-page">
    <?php echo $this->Session->flash();?>

    <div class="wv-contact-edit-hero">
        <div class="wv-contact-edit-title">
            <div class="wv-contact-edit-icon"><i class="fa fa-id-card"></i></div>
            <div>
                <h1><?php echo __('Edit Customer Contact');?></h1>
                <p><?php echo __('Update client personal profile, communication channels, address hierarchy and KYC document photos.');?></p>
            </div>
        </div>
        <div class="wv-contact-edit-badge">
            <i class="fa fa-shield"></i>&nbsp;<?php echo __('KYC & Identity Management');?>
        </div>
    </div>

    <?php echo $this->Form->create('Contact', array('controller' => 'Contacts', 'name' => 'post_req', 'id' => 'post_req', 'type' => 'file'));?>

    <?php foreach ($Contact as $k => $post): 
        $id = $post['Contact']['id'];
        $form_no = $k;
        $contactLinkedLeads = isset($linkedLeadMap[$id]) ? (array)$linkedLeadMap[$id] : array();
        $contactDealCount = isset($linkedDealCountMap[$id]) ? (int)$linkedDealCountMap[$id] : 0;
    ?>
        <?php echo $this->Form->input("$k.Contact.id", array('type' => 'hidden', 'value' => $id));?>

        <div class="wv-contact-edit-card">
            <div class="wv-contact-card-head">
                <div>
                    <strong><?php echo h($post['Contact']['name'] ? $post['Contact']['name'] : __('Customer Details'));?> &nbsp;<span class="label label-primary" style="font-size:11px;vertical-align:middle;">#<?php echo $id;?></span></strong>
                    <span><?php echo __('Personal details, category, contact information and official address.');?></span>
                </div>
            </div>

            <div class="wv-contact-edit-body">
                <!-- Section 1: Personal & Profile Info -->
                <div class="wv-form-section-title">
                    <i class="fa fa-user"></i>&nbsp;<?php echo __('Customer Profile & Relationship Details');?>
                </div>

                <div class="wv-contact-edit-grid">
                    <?php if($userType != 'AGT' && $userType != 'AGY' && isset($agency)): ?>
                    <div class="wv-contact-edit-field">
                        <label><?php echo __('Agency / Project');?></label>
                        <?php echo $this->Form->select("$k.Contact.project_id", $agency, array('label' => false, 'class' => 'form-control', 'empty' => __('Select Agency / Project'), 'div' => false));?>
                    </div>
                    <?php endif; ?>

                    <div class="wv-contact-edit-field">
                        <label><?php echo __('Category');?></label>
                        <?php echo $this->Form->select("$k.Contact.category_id", $category, array('label' => false, 'class' => 'form-control', 'empty' => __('Select Category'), 'div' => false));?>
                    </div>

                    <div class="wv-contact-edit-field">
                        <label><?php echo __('Full Name *');?></label>
                        <?php echo $this->Form->input("$k.Contact.name", array('label' => false, 'class' => 'form-control', 'placeholder' => __('Full Name'), 'div' => false, 'required' => true));?>
                    </div>

                    <div class="wv-contact-edit-field">
                        <label><?php echo __('Relationship Type');?></label>
                        <?php echo $this->Form->input("$k.Contact.relation_type", array(
                            'label' => false,
                            'class' => 'form-control',
                            'div' => false,
                            'type' => 'select',
                            'options' => array(
                                'Father' => __('Father (S/O, D/O)'),
                                'Husband' => __('Husband (W/O)'),
                                'Wife' => __('Wife'),
                                'Mother' => __('Mother'),
                                'Guardian' => __('Guardian'),
                                'Son' => __('Son'),
                                'Daughter' => __('Daughter'),
                                'Other' => __('Other')
                            ),
                            'default' => 'Father',
                            'empty' => __('Select Relationship')
                        ));?>
                    </div>

                    <div class="wv-contact-edit-field">
                        <label><?php echo __('Father / Guardian / Spouse Name');?></label>
                        <?php echo $this->Form->input("$k.Contact.fathername", array('label' => false, 'class' => 'form-control', 'placeholder' => __('Father / Guardian Name'), 'div' => false));?>
                    </div>

                    <div class="wv-contact-edit-field">
                        <label><?php echo __('Gender');?></label>
                        <?php echo $this->Form->input("$k.Contact.gender", array(
                            'label' => false,
                            'class' => 'form-control',
                            'div' => false,
                            'type' => 'select',
                            'options' => array('Male' => 'Male', 'Female' => 'Female', 'Other' => 'Other'),
                            'empty' => __('Select Gender')
                        ));?>
                    </div>
                </div>

                <!-- Section 2: Contact Channels & Identity -->
                <div class="wv-form-section-title">
                    <i class="fa fa-phone"></i>&nbsp;<?php echo __('Contact Channels & Identity Numbers');?>
                </div>

                <div class="wv-contact-edit-grid">
                    <div class="wv-contact-edit-field">
                        <label><?php echo __('Cell Phone (WhatsApp) *');?></label>
                        <?php echo $this->Form->input("$k.Contact.mobile", array('label' => false, 'class' => 'form-control', 'placeholder' => __('+923012345678'), 'div' => false, 'required' => true));?>
                    </div>

                    <div class="wv-contact-edit-field">
                        <label><?php echo __('Mobile SMS');?></label>
                        <?php echo $this->Form->input("$k.Contact.phone", array('label' => false, 'class' => 'form-control', 'placeholder' => __('+923001234567'), 'div' => false));?>
                    </div>

                    <div class="wv-contact-edit-field">
                        <label><?php echo __('Email Address');?></label>
                        <?php echo $this->Form->input("$k.Contact.email", array('label' => false, 'class' => 'form-control', 'placeholder' => __('client@example.com'), 'div' => false));?>
                    </div>

                    <div class="wv-contact-edit-field">
                        <label><?php echo __('CNIC / National ID / ZIP');?></label>
                        <?php echo $this->Form->input("$k.Contact.pincode", array('label' => false, 'class' => 'form-control contact-cnic', 'placeholder' => '35201-1234567-1', 'div' => false, 'maxlength' => 15));?>
                    </div>

                    <div class="wv-contact-edit-field wv-contact-edit-full">
                        <?php echo $this->element('address_hierarchy_fields', array('fieldPrefix' => $k.'.Contact', 'current' => isset($post['Contact']) ? $post['Contact'] : array(), 'wrapperId' => 'ContactEditAddressHierarchy'.$k));?>
                    </div>

                    <div class="wv-contact-edit-field wv-contact-edit-full">
                        <label><?php echo __('Street / Postal Address');?></label>
                        <?php echo $this->Form->input("$k.Contact.address", array('label' => false, 'class' => 'form-control', 'placeholder' => __('House #, Street #, Sector / Area details...'), 'div' => false));?>
                    </div>
                </div>

                <!-- Section 3: Identity Documents & Photos (Uploads) -->
                <div class="wv-form-section-title">
                    <i class="fa fa-camera"></i>&nbsp;<?php echo __('KYC Documents: Profile Image & CNIC Photos');?>
                </div>

                <div class="wv-uploads-grid">
                    <!-- 1. Profile Photo -->
                    <?php 
                        $hasPhoto = !empty($post['Contact']['photo']);
                        $photoUrl = '';
                        if ($hasPhoto) {
                            $photoImgs = wvResolveContactImage($post['Contact']['photo'], !empty($post['Contact']['dirt']) ? $post['Contact']['dirt'] : 'contact', $this->webroot);
                            $photoUrl = $photoImgs['original'];
                        }
                    ?>
                    <div class="wv-upload-box js-upload-box" data-field="photo">
                        <div class="wv-upload-header">
                            <span class="wv-upload-title"><?php echo __('Profile Photo');?></span>
                            <span class="wv-upload-status-badge <?php echo $hasPhoto ? 'wv-status-uploaded' : 'wv-status-none';?> js-status-badge">
                                <?php echo $hasPhoto ? __('Uploaded') : __('No Photo');?>
                            </span>
                        </div>
                        <label for="Contact<?php echo $k;?>Photo" class="wv-upload-stage" title="<?php echo __('Click to change or upload image');?>">
                            <img src="<?php echo h($photoUrl);?>" alt="Profile Photo" class="js-preview-img" style="<?php echo $hasPhoto ? '' : 'display:none;';?>">
                            <div class="wv-upload-empty-placeholder js-placeholder" style="<?php echo $hasPhoto ? 'display:none;' : '';?>">
                                <i class="fa fa-user-circle-o"></i>
                                <span><?php echo __('Click to Select Photo');?></span>
                            </div>
                        </label>
                        <div class="wv-upload-meta js-meta">
                            <?php echo $hasPhoto ? h(basename($post['Contact']['photo'])) : __('JPG, PNG or WebP');?>
                        </div>
                        <div class="wv-upload-actions">
                            <label for="Contact<?php echo $k;?>Photo" class="btn-browse">
                                <i class="fa fa-folder-open"></i>&nbsp;<?php echo $hasPhoto ? __('Change Photo') : __('Browse Photo');?>
                            </label>
                            <button type="button" class="btn btn-remove js-btn-remove" title="<?php echo __('Remove Photo');?>" style="<?php echo $hasPhoto ? '' : 'display:none;';?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        <input type="file" name="data[<?php echo $k;?>][Contact][photo]" class="js-file-input" accept="image/*" style="display:none;" id="Contact<?php echo $k;?>Photo">
                        <input type="hidden" name="data[<?php echo $k;?>][Contact][remove_photo]" class="js-remove-flag" value="0">
                    </div>

                    <!-- 2. CNIC Front -->
                    <?php 
                        $hasIdFront = !empty($post['Contact']['id_front']);
                        $idFrontUrl = '';
                        if ($hasIdFront) {
                            $idFrontImgs = wvResolveContactImage($post['Contact']['id_front'], !empty($post['Contact']['id_front_dir']) ? $post['Contact']['id_front_dir'] : 'contact', $this->webroot);
                            $idFrontUrl = $idFrontImgs['original'];
                        }
                    ?>
                    <div class="wv-upload-box js-upload-box" data-field="id_front">
                        <div class="wv-upload-header">
                            <span class="wv-upload-title"><?php echo __('CNIC Front');?></span>
                            <span class="wv-upload-status-badge <?php echo $hasIdFront ? 'wv-status-uploaded' : 'wv-status-none';?> js-status-badge">
                                <?php echo $hasIdFront ? __('Uploaded') : __('No File');?>
                            </span>
                        </div>
                        <label for="Contact<?php echo $k;?>IdFront" class="wv-upload-stage" title="<?php echo __('Click to change or upload image');?>">
                            <img src="<?php echo h($idFrontUrl);?>" alt="CNIC Front" class="js-preview-img" style="<?php echo $hasIdFront ? '' : 'display:none;';?>">
                            <div class="wv-upload-empty-placeholder js-placeholder" style="<?php echo $hasIdFront ? 'display:none;' : '';?>">
                                <i class="fa fa-id-card-o"></i>
                                <span><?php echo __('Click to Select CNIC Front');?></span>
                            </div>
                        </label>
                        <div class="wv-upload-meta js-meta">
                            <?php echo $hasIdFront ? h(basename($post['Contact']['id_front'])) : __('Front side photo');?>
                        </div>
                        <div class="wv-upload-actions">
                            <label for="Contact<?php echo $k;?>IdFront" class="btn-browse">
                                <i class="fa fa-folder-open"></i>&nbsp;<?php echo $hasIdFront ? __('Change CNIC Front') : __('Browse CNIC Front');?>
                            </label>
                            <button type="button" class="btn btn-remove js-btn-remove" title="<?php echo __('Remove CNIC Front');?>" style="<?php echo $hasIdFront ? '' : 'display:none;';?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        <input type="file" name="data[<?php echo $k;?>][Contact][id_front]" class="js-file-input" accept="image/*" style="display:none;" id="Contact<?php echo $k;?>IdFront">
                        <input type="hidden" name="data[<?php echo $k;?>][Contact][remove_id_front]" class="js-remove-flag" value="0">
                    </div>

                    <!-- 3. CNIC Back -->
                    <?php 
                        $hasIdBack = !empty($post['Contact']['id_back']);
                        $idBackUrl = '';
                        if ($hasIdBack) {
                            $idBackImgs = wvResolveContactImage($post['Contact']['id_back'], !empty($post['Contact']['id_back_dir']) ? $post['Contact']['id_back_dir'] : 'contact', $this->webroot);
                            $idBackUrl = $idBackImgs['original'];
                        }
                    ?>
                    <div class="wv-upload-box js-upload-box" data-field="id_back">
                        <div class="wv-upload-header">
                            <span class="wv-upload-title"><?php echo __('CNIC Back');?></span>
                            <span class="wv-upload-status-badge <?php echo $hasIdBack ? 'wv-status-uploaded' : 'wv-status-none';?> js-status-badge">
                                <?php echo $hasIdBack ? __('Uploaded') : __('No File');?>
                            </span>
                        </div>
                        <label for="Contact<?php echo $k;?>IdBack" class="wv-upload-stage" title="<?php echo __('Click to change or upload image');?>">
                            <img src="<?php echo h($idBackUrl);?>" alt="CNIC Back" class="js-preview-img" style="<?php echo $hasIdBack ? '' : 'display:none;';?>">
                            <div class="wv-upload-empty-placeholder js-placeholder" style="<?php echo $hasIdBack ? 'display:none;' : '';?>">
                                <i class="fa fa-id-card-o"></i>
                                <span><?php echo __('Click to Select CNIC Back');?></span>
                            </div>
                        </label>
                        <div class="wv-upload-meta js-meta">
                            <?php echo $hasIdBack ? h(basename($post['Contact']['id_back'])) : __('Back side photo');?>
                        </div>
                        <div class="wv-upload-actions">
                            <label for="Contact<?php echo $k;?>IdBack" class="btn-browse">
                                <i class="fa fa-folder-open"></i>&nbsp;<?php echo $hasIdBack ? __('Change CNIC Back') : __('Browse CNIC Back');?>
                            </label>
                            <button type="button" class="btn btn-remove js-btn-remove" title="<?php echo __('Remove CNIC Back');?>" style="<?php echo $hasIdBack ? '' : 'display:none;';?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        <input type="file" name="data[<?php echo $k;?>][Contact][id_back]" class="js-file-input" accept="image/*" style="display:none;" id="Contact<?php echo $k;?>IdBack">
                        <input type="hidden" name="data[<?php echo $k;?>][Contact][remove_id_back]" class="js-remove-flag" value="0">
                    </div>

                    <!-- 4. Side / Additional Photo -->
                    <?php 
                        $hasSidePhoto = !empty($post['Contact']['side_photo']);
                        $sidePhotoUrl = '';
                        if ($hasSidePhoto) {
                            $sidePhotoImgs = wvResolveContactImage($post['Contact']['side_photo'], !empty($post['Contact']['side_photo_dir']) ? $post['Contact']['side_photo_dir'] : 'contact', $this->webroot);
                            $sidePhotoUrl = $sidePhotoImgs['original'];
                        }
                    ?>
                    <div class="wv-upload-box js-upload-box" data-field="side_photo">
                        <div class="wv-upload-header">
                            <span class="wv-upload-title"><?php echo __('Gallery / Side');?></span>
                            <span class="wv-upload-status-badge <?php echo $hasSidePhoto ? 'wv-status-uploaded' : 'wv-status-none';?> js-status-badge">
                                <?php echo $hasSidePhoto ? __('Uploaded') : __('No File');?>
                            </span>
                        </div>
                        <label for="Contact<?php echo $k;?>SidePhoto" class="wv-upload-stage" title="<?php echo __('Click to change or upload image');?>">
                            <img src="<?php echo h($sidePhotoUrl);?>" alt="Side Photo" class="js-preview-img" style="<?php echo $hasSidePhoto ? '' : 'display:none;';?>">
                            <div class="wv-upload-empty-placeholder js-placeholder" style="<?php echo $hasSidePhoto ? 'display:none;' : '';?>">
                                <i class="fa fa-picture-o"></i>
                                <span><?php echo __('Optional Additional Photo');?></span>
                            </div>
                        </label>
                        <div class="wv-upload-meta js-meta">
                            <?php echo $hasSidePhoto ? h(basename($post['Contact']['side_photo'])) : __('Additional picture');?>
                        </div>
                        <div class="wv-upload-actions">
                            <label for="Contact<?php echo $k;?>SidePhoto" class="btn-browse">
                                <i class="fa fa-folder-open"></i>&nbsp;<?php echo $hasSidePhoto ? __('Change Photo') : __('Browse Photo');?>
                            </label>
                            <button type="button" class="btn btn-remove js-btn-remove" title="<?php echo __('Remove Photo');?>" style="<?php echo $hasSidePhoto ? '' : 'display:none;';?>">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                        <input type="file" name="data[<?php echo $k;?>][Contact][side_photo]" class="js-file-input" accept="image/*" style="display:none;" id="Contact<?php echo $k;?>SidePhoto">
                        <input type="hidden" name="data[<?php echo $k;?>][Contact][remove_side_photo]" class="js-remove-flag" value="0">
                    </div>
                </div>

                <!-- Section 4: CRM / Deal Links -->
                <?php if($contactDealCount > 0): ?>
                    <div class="panel lead-contact-reverse lead-contact-blocked">
                        <div class="panel-heading">
                            <strong><span class="fa fa-lock"></span>&nbsp;<?php echo __('Active Deal Customer');?></strong>
                        </div>
                        <div class="panel-body">
                            <p style="margin:0;font-weight:700;color:#991b1b;">
                                <?php echo sprintf(__('%s linked active deal(s) attached to this contact. Account ledger, booking payments and letters are actively tied to this customer.'), $contactDealCount);?>
                            </p>
                        </div>
                    </div>
                <?php elseif(!empty($contactLinkedLeads)): ?>
                    <div class="panel lead-contact-reverse">
                        <div class="panel-heading">
                            <strong><span class="fa fa-undo"></span>&nbsp;<?php echo __('CRM Lead Relationship');?></strong>
                        </div>
                        <div class="panel-body">
                            <p class="text-muted" style="margin-bottom:8px;">
                                <?php echo __('This contact was converted from the CRM lead(s) below. Select any lead you wish to unlink:');?>
                            </p>
                            <ul class="list-unstyled" style="margin:0;">
                                <?php foreach($contactLinkedLeads as $leadRow): 
                                    $leadId = !empty($leadRow['Lead']['id']) ? (int)$leadRow['Lead']['id'] : 0;
                                    $leadName = !empty($leadRow['Lead']['lead_name']) ? $leadRow['Lead']['lead_name'] : __('Unnamed Lead');
                                    $leadMobile = !empty($leadRow['Lead']['lead_mobile']) ? $leadRow['Lead']['lead_mobile'] : '';
                                ?>
                                    <li style="padding:4px 0;">
                                        <label>
                                            <?php echo $this->Form->checkbox("$k.Contact.unlink_lead_ids.", array('value' => $leadId, 'hiddenField' => false, 'class' => 'contact-unlink-leads'));?>
                                            <strong><?php echo h($leadName);?> (#<?php echo $leadId;?>)</strong> &nbsp;<span class="text-muted"><?php echo h($leadMobile);?></span>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    <?php endforeach; ?>

    <!-- Bottom Action Bar -->
    <div class="wv-contact-edit-actions">
        <button type="submit" class="btn btn-success">
            <span class="fa fa-check-circle"></span>&nbsp;<?php echo __('Save & Update Contact');?>
        </button>
        <?php echo $this->Html->link('<span class="fa fa-times"></span>&nbsp;'.__('Cancel / Close'), array('action' => 'index'), array('class' => 'btn btn-secondary', 'escape' => false));?>
    </div>

    <?php echo $this->Form->end();?>
</div>

<script type="text/javascript">
(function($){
    // CNIC Formatter
    function formatCnic(val) {
        var digits = (val || '').replace(/\D/g, '').substring(0, 13);
        if (digits.length > 12) {
            return digits.substring(0, 5) + '-' + digits.substring(5, 12) + '-' + digits.substring(12, 13);
        } else if (digits.length > 5) {
            return digits.substring(0, 5) + '-' + digits.substring(5, 12);
        }
        return digits;
    }

    $(document).ready(function(){
        if (typeof $.fn.validationEngine === 'function') {
            $('#post_req').validationEngine();
        }

        // CNIC auto-hyphenation
        $('.contact-cnic').on('input blur', function(){
            $(this).val(formatCnic($(this).val()));
        });

        // Instant live image preview on file selection
        $(document).on('change', '.js-file-input', function(){
            var file = this.files && this.files[0];
            if (!file) return;

            var $box = $(this).closest('.wv-upload-box');
            var $previewImg = $box.find('.js-preview-img');
            var $placeholder = $box.find('.js-placeholder');
            var $statusBadge = $box.find('.js-status-badge');
            var $meta = $box.find('.js-meta');
            var $removeFlag = $box.find('.js-remove-flag');
            var $btnRemove = $box.find('.js-btn-remove');

            var reader = new FileReader();
            reader.onload = function(e){
                $previewImg.attr('src', e.target.result).show();
                $placeholder.hide();
                $statusBadge.removeClass('wv-status-none wv-status-uploaded wv-status-removed')
                            .addClass('wv-status-new').text('New File Selected');
                var sizeKb = Math.round(file.size / 1024);
                $meta.html('<strong>' + file.name + '</strong> (' + sizeKb + ' KB)');
                $removeFlag.val('0');
                $btnRemove.show();
            };
            reader.readAsDataURL(file);
        });

        // Drag and drop onto stage
        $('.wv-upload-stage').on('dragover dragenter', function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.wv-upload-box').addClass('drag-over');
        });
        $('.wv-upload-stage').on('dragleave dragend drop', function(e){
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.wv-upload-box').removeClass('drag-over');
        });
        $('.wv-upload-stage').on('drop', function(e){
            var files = e.originalEvent.dataTransfer ? e.originalEvent.dataTransfer.files : null;
            if (files && files.length) {
                var $box = $(this).closest('.wv-upload-box');
                var $fileInput = $box.find('.js-file-input');
                try {
                    var dt = new DataTransfer();
                    dt.items.add(files[0]);
                    $fileInput[0].files = dt.files;
                } catch(err) {}
                $fileInput.trigger('change');
            }
        });

        // Remove button handler
        $('.js-btn-remove').on('click', function(e){
            e.preventDefault();
            e.stopPropagation();
            if (confirm('Are you sure you want to remove this photo?')) {
                var $box = $(this).closest('.wv-upload-box');
                $box.find('.js-remove-flag').val('1');
                $box.find('.js-file-input').val('');
                $box.find('.js-preview-img').attr('src', '').hide();
                $box.find('.js-placeholder').show();
                $box.find('.js-status-badge').removeClass('wv-status-uploaded wv-status-new')
                    .addClass('wv-status-removed').text('Removed');
                $box.find('.js-meta').text('Marked for removal');
                $(this).hide();
            }
        });
    });
})(jQuery);
</script>
