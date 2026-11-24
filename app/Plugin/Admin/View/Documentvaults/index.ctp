<?php
$selectedDealId = !empty($dealId) ? (int)$dealId : '';
$selectedDocType = !empty($docType) ? $docType : '';
?>

<style type="text/css">
.wv-vault-page { color:#06162b; }
.wv-vault-hero {
    background: linear-gradient(135deg,#07152a 0%,#0d2747 58%,#9f002d 100%);
    border-radius: 8px;
    color:#fff;
    margin-bottom:14px;
    padding:18px 20px;
}
.wv-vault-hero h2 { font-size:24px; font-weight:900; margin:0 0 4px; }
.wv-vault-hero p { color:#dceeff; font-weight:700; margin:0; }
.wv-vault-stats { display:grid; gap:10px; grid-template-columns:repeat(4,minmax(0,1fr)); margin-bottom:14px; }
.wv-vault-stat {
    background:#fff;
    border:1px solid #dbe4ee;
    border-radius:8px;
    padding:13px 14px;
    box-shadow:0 10px 24px rgba(7,21,42,.06);
}
.wv-vault-stat span { color:#5c7088; display:block; font-size:11px; font-weight:900; text-transform:uppercase; }
.wv-vault-stat strong { color:#9f002d; display:block; font-size:24px; font-weight:900; margin-top:4px; }
.wv-vault-panel {
    background:#fff;
    border:1px solid #dbe4ee;
    border-radius:8px;
    margin-bottom:14px;
    padding:12px;
}
.wv-vault-panel h4 { font-weight:900; margin:0 0 12px; }
.wv-vault-upload {
    border:2px dashed #9bb0c4;
    border-radius:8px;
    background:#f7fafc;
    padding:14px;
}
.wv-vault-table th { background:#07152a; color:#fff; font-size:11px; text-transform:uppercase; }
.wv-vault-table td { vertical-align:middle !important; }
.wv-vault-type {
    background:#edf4ff;
    border:1px solid #cbdcf3;
    border-radius:999px;
    display:inline-block;
    font-size:11px;
    font-weight:900;
    padding:5px 9px;
}
.wv-vault-secure {
    background:#fff4f6;
    border-left:4px solid #9f002d;
    border-radius:8px;
    font-weight:800;
    margin-bottom:12px;
    padding:10px 12px;
}
.wv-vault-actions { white-space:nowrap; }
.wv-vault-actions .btn { margin:1px; }
@media (max-width: 767px) {
    .wv-vault-stats { grid-template-columns:1fr 1fr; }
}
</style>

<div class="wv-vault-page">
    <?php echo $this->Session->flash(); ?>

    <div class="wv-vault-hero">
        <h2><?php echo __('Document Vault'); ?></h2>
        <p><?php echo __('Secure upload for CNIC, booking form, allotment letter, agreement, and payment proof. New vault uploads are not public.'); ?></p>
    </div>

    <div class="wv-vault-stats">
        <div class="wv-vault-stat"><span><?php echo __('Total Documents'); ?></span><strong><?php echo (int)$summary['total']; ?></strong></div>
        <div class="wv-vault-stat"><span><?php echo __('CNIC'); ?></span><strong><?php echo (int)$summary['cnic']; ?></strong></div>
        <div class="wv-vault-stat"><span><?php echo __('Agreements'); ?></span><strong><?php echo (int)$summary['agreement']; ?></strong></div>
        <div class="wv-vault-stat"><span><?php echo __('Payment Proofs'); ?></span><strong><?php echo (int)$summary['payment_proof']; ?></strong></div>
    </div>

    <div class="wv-vault-panel">
        <h4><span class="fa fa-lock"></span>&nbsp;<?php echo __('Upload Secure Document'); ?></h4>
        <div class="wv-vault-secure">
            <?php echo __('Files uploaded here are stored in the admin vault and opened only through logged-in admin access.'); ?>
        </div>
        <form method="post" enctype="multipart/form-data" action="<?php echo $this->Html->url(array('controller'=>'Documentvaults','action'=>'index')); ?>">
            <div class="row">
                <div class="col-sm-5">
                    <div class="form-group">
                        <label><?php echo __('Deal / Client'); ?></label>
                        <select name="data[DealDocument][deal_id]" class="form-control" required="required">
                            <option value=""><?php echo __('Please Select'); ?></option>
                            <?php foreach($dealOptions as $optionId => $optionLabel){ ?>
                                <option value="<?php echo (int)$optionId; ?>" <?php echo (string)$selectedDealId === (string)$optionId ? 'selected="selected"' : ''; ?>><?php echo h($optionLabel); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label><?php echo __('Document Type'); ?></label>
                        <select name="data[DealDocument][document_type]" class="form-control" required="required">
                            <?php foreach($documentTypeOptions as $typeKey => $typeLabel){ ?>
                                <option value="<?php echo h($typeKey); ?>"><?php echo h($typeLabel); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label><?php echo __('Date'); ?></label>
                        <input type="date" name="data[DealDocument][document_date]" class="form-control" value="<?php echo h(date('Y-m-d')); ?>" />
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <label><?php echo __('File'); ?></label>
                        <input type="file" name="data[DealDocument][upload_file]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.webp" required="required" />
                    </div>
                </div>
            </div>
            <div class="wv-vault-upload">
                <button type="submit" class="btn btn-success"><span class="fa fa-upload"></span>&nbsp;<?php echo __('Upload To Vault'); ?></button>
                <span style="font-size:12px;font-weight:800;margin-left:8px;"><?php echo __('Supported: PDF, DOC, DOCX, JPG, PNG, GIF, WEBP'); ?></span>
            </div>
        </form>
    </div>

    <div class="wv-vault-panel">
        <h4><span class="fa fa-search"></span>&nbsp;<?php echo __('Search Vault'); ?></h4>
        <form method="get" class="row" action="<?php echo $this->Html->url(array('controller'=>'Documentvaults','action'=>'index')); ?>">
            <div class="col-sm-4">
                <label><?php echo __('Deal / Client'); ?></label>
                <select name="deal_id" class="form-control">
                    <option value=""><?php echo __('All Deals'); ?></option>
                    <?php foreach($dealOptions as $optionId => $optionLabel){ ?>
                        <option value="<?php echo (int)$optionId; ?>" <?php echo (string)$selectedDealId === (string)$optionId ? 'selected="selected"' : ''; ?>><?php echo h($optionLabel); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-3">
                <label><?php echo __('Document Type'); ?></label>
                <select name="document_type" class="form-control">
                    <option value=""><?php echo __('All Types'); ?></option>
                    <?php foreach($documentTypeOptions as $typeKey => $typeLabel){ ?>
                        <option value="<?php echo h($typeKey); ?>" <?php echo $selectedDocType === $typeKey ? 'selected="selected"' : ''; ?>><?php echo h($typeLabel); ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-sm-3">
                <label><?php echo __('Keyword'); ?></label>
                <input type="text" name="keyword" class="form-control" value="<?php echo h($keyword); ?>" placeholder="<?php echo __('Client, file, property, agency'); ?>" />
            </div>
            <div class="col-sm-2">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-block"><span class="fa fa-search"></span>&nbsp;<?php echo __('Search'); ?></button>
            </div>
        </form>
    </div>

    <div class="wv-vault-panel">
        <div class="table-responsive">
            <table class="table table-bordered table-striped wv-vault-table">
                <thead>
                    <tr>
                        <th><?php echo __('#'); ?></th>
                        <th><?php echo __('Date'); ?></th>
                        <th><?php echo __('Type'); ?></th>
                        <th><?php echo __('Client'); ?></th>
                        <th><?php echo __('Father / Guardian'); ?></th>
                        <th><?php echo __('Membership / Property'); ?></th>
                        <th><?php echo __('Stored File'); ?></th>
                        <th><?php echo __('Storage'); ?></th>
                        <th><?php echo __('Action'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($documents)){ $sr = $this->Paginator->counter(array('format'=>'{:start}')); foreach($documents as $row){
                        $doc = !empty($row['DealDocument']) ? $row['DealDocument'] : array();
                        $deal = !empty($row['Deal']) ? $row['Deal'] : array();
                        $client = !empty($row['Client']) ? $row['Client'] : array();
                        $property = !empty($row['Property']) ? $row['Property'] : array();
                        $project = !empty($row['Project']) ? $row['Project'] : array();
                        $docId = !empty($doc['id']) ? (int)$doc['id'] : 0;
                    ?>
                        <tr>
                            <td><?php echo (int)$sr++; ?></td>
                            <td><?php echo !empty($doc['document_date']) && $doc['document_date'] !== '0000-00-00' ? h($this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear, $doc['document_date'])) : '-'; ?></td>
                            <td><span class="wv-vault-type"><?php echo h(!empty($doc['document_type']) ? $doc['document_type'] : 'Others'); ?></span></td>
                            <td>
                                <strong><?php echo h(!empty($client['name']) ? $client['name'] : '-'); ?></strong><br>
                                <small><?php echo h(!empty($project['name']) ? $project['name'] : '-'); ?></small>
                            </td>
                            <td>
                                <?php
                                $fatherName = !empty($client['fathername']) ? trim((string)$client['fathername']) : '';
                                $relationType = !empty($client['relation_type']) ? trim((string)$client['relation_type']) : '';
                                if($fatherName !== ''){
                                    echo '<strong style="color:#1e293b;">' . h($fatherName) . '</strong>';
                                    if($relationType !== ''){
                                        echo '<small class="text-muted" style="display:block;font-size:11px;font-weight:600;">' . h($relationType) . '</small>';
                                    }
                                } else {
                                    echo '<span class="text-muted">-</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <strong><?php echo h(!empty($deal['invoice_no']) ? $deal['invoice_no'] : '-'); ?></strong><br>
                                <small><?php echo h(!empty($property['name']) ? $property['name'] : '-'); ?></small>
                            </td>
                            <td><?php echo h(!empty($doc['photo']) ? $doc['photo'] : '-'); ?></td>
                            <td><?php echo (!empty($doc['dir']) && $doc['dir'] === 'dealvault') ? __('Secure Vault') : __('Legacy File'); ?></td>
                            <td class="wv-vault-actions">
                                <?php
                                echo $this->Html->link('<span class="fa fa-eye"></span>', array('controller'=>'Documentvaults','action'=>'viewfile',$docId), array('escape'=>false,'target'=>'_blank','class'=>'btn btn-info btn-xs','title'=>__('View')));
                                echo $this->Html->link('<span class="fa fa-download"></span>', array('controller'=>'Documentvaults','action'=>'download',$docId), array('escape'=>false,'class'=>'btn btn-success btn-xs','title'=>__('Download')));
                                echo $this->Form->postLink('<span class="fa fa-trash"></span>', array('controller'=>'Documentvaults','action'=>'delete',$docId), array('escape'=>false,'class'=>'btn btn-danger btn-xs','title'=>__('Delete')), __('Delete this secure document?'));
                                ?>
                            </td>
                        </tr>
                    <?php }} else { ?>
                        <tr><td colspan="8" class="text-center"><?php echo __('No documents found.'); ?></td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="text-center">
            <?php echo $this->Paginator->numbers(array('class'=>'pagination')); ?>
        </div>
    </div>
</div>
