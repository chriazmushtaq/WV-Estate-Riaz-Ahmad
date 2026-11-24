<style type="text/css">
.wv-import-wrap{background:#f5f8fc;border:1px solid #dbe5f0;border-radius:12px;padding:16px;}
.wv-import-card{background:#fff;border:1px solid #dce6f2;border-radius:12px;box-shadow:0 12px 28px rgba(15,23,42,.06);padding:18px;max-width:760px;}
.wv-import-card h3{color:#07152a;font-size:20px;font-weight:900;margin:0 0 12px;}
.wv-import-card p,.wv-import-card li{color:#526277;font-size:13px;font-weight:700;line-height:1.55;}
.wv-import-card .form-control{height:42px;}
</style>
<div class="page-title">
    <div class="title-env"><h1 class="title"><?php echo __('Import Address Hierarchy');?></h1></div>
</div>
<div class="wv-import-wrap">
    <?php echo $this->Session->flash();?>
    <div class="wv-import-card">
        <h3><?php echo __('Upload Address CSV or Excel File');?></h3>
        <p><?php echo __('Columns: country, province, district, tehsil, city, town, block. Header row is optional and automatically detected.');?></p>
        <ul>
            <li><?php echo __('Supported formats: CSV (UTF-8, Comma / Semicolon / Tab) and Excel (.xlsx, .xls).');?></li>
            <li><?php echo __('Existing hierarchy nodes are preserved and linked safely.');?></li>
        </ul>
        <?php echo $this->Html->link('<span class="fa fa-download"></span> '.__('Download Sample CSV'), array('action'=>'sample'), array('escape'=>false,'class'=>'btn btn-default'));?>
        <hr>
        <?php echo $this->Form->create('Addresshierarchy', array('type'=>'file'));?>
        <div class="form-group">
            <label><?php echo __('File (CSV or Excel)');?></label>
            <?php echo $this->Form->file('file', array('class'=>'form-control', 'accept'=>'.csv,.txt,.xls,.xlsx'));?>
        </div>
        <button type="submit" class="btn btn-success"><span class="fa fa-upload"></span> <?php echo __('Import');?></button>
        <?php echo $this->Html->link(__('Back'), array('action'=>'index'), array('class'=>'btn btn-default'));?>
        <?php echo $this->Form->end();?>
    </div>
</div>
