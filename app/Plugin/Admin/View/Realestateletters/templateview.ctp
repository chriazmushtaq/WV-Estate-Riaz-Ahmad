<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('Letters - Template View');?></h1>
    </div>
</div>
<style type="text/css">
.wv-template-shell{background:#f6f9fc;border:1px solid #d9e5f2;border-radius:8px;padding:14px;}
.wv-template-topbar{align-items:center;background:#08213f;border-radius:8px;color:#fff;display:flex;gap:12px;justify-content:space-between;margin-bottom:14px;padding:12px 14px;}
.wv-template-title{font-size:17px;font-weight:800;line-height:1.2;margin:0;}
.wv-template-subtitle{color:#b8d4ee;font-size:12px;font-weight:700;margin-top:3px;}
.wv-template-actions{display:flex;gap:8px;white-space:nowrap;}
.wv-template-meta{display:grid;gap:10px;grid-template-columns:repeat(3,minmax(0,1fr));margin-bottom:14px;}
.wv-template-meta-card{background:#fff;border:1px solid #d9e5f2;border-radius:7px;min-height:64px;padding:10px 12px;}
.wv-template-meta-card .k{color:#64748b;display:block;font-size:10px;font-weight:800;letter-spacing:.03em;text-transform:uppercase;}
.wv-template-meta-card .v{color:#0f172a;display:block;font-size:13px;font-weight:800;margin-top:5px;word-break:break-word;}
.wv-template-status{border-radius:999px;display:inline-block;font-size:11px;font-weight:800;padding:5px 9px;}
.wv-template-status.published{background:#dcfce7;color:#166534;}
.wv-template-status.unpublished{background:#fee2e2;color:#991b1b;}
.wv-template-signatures{display:flex;gap:10px;grid-column:1 / -1;}
.wv-template-signature{align-items:center;background:#fff;border:1px solid #d9e5f2;border-radius:7px;display:flex;gap:10px;min-height:72px;padding:10px 12px;width:50%;}
.wv-template-signature strong{color:#0f172a;display:block;font-size:12px;}
.wv-template-signature span{color:#94a3b8;font-size:12px;font-weight:700;}
.wv-template-signature img{background:#fff;border:1px solid #e5edf5;border-radius:6px;max-height:48px;max-width:150px;padding:4px;}
.wv-template-preview-card{background:#fff;border:1px solid #cbdbea;border-radius:8px;overflow:hidden;}
.wv-template-preview-head{align-items:center;background:#eef5fb;border-bottom:1px solid #d9e5f2;display:flex;justify-content:space-between;padding:9px 12px;}
.wv-template-preview-head h4{color:#0f172a;font-size:13px;font-weight:800;margin:0;text-transform:uppercase;}
.wv-template-preview-note{color:#64748b;font-size:11px;font-weight:700;}
.wv-template-frame{background:#d7dee8;border:0;display:block;min-height:1120px;width:100%;}
.wv-template-body-fallback{background:#fff;padding:14px;}
@media(max-width:900px){
    .wv-template-topbar{align-items:flex-start;flex-direction:column;}
    .wv-template-meta{grid-template-columns:1fr;}
    .wv-template-signatures{flex-direction:column;}
    .wv-template-signature{width:100%;}
}
</style>
<div class="panel">
    <div class="panel-body">
        <div class="wv-template-shell">
            <?php
            $typeCode = isset($post['Template']['type']) ? $post['Template']['type'] : '';
            $typeLabel = isset($letterTypeArr[$typeCode]) ? $letterTypeArr[$typeCode] : $typeCode;
            $statusLabel = isset($post['Template']['status']) ? trim((string)$post['Template']['status']) : '';
            $statusClass = (strcasecmp($statusLabel, 'Published') === 0) ? 'published' : 'unpublished';
            ?>
            <div class="wv-template-topbar">
                <div>
                    <h2 class="wv-template-title"><?php echo h($post['Template']['name']);?></h2>
                    <div class="wv-template-subtitle"><?php echo h($typeLabel);?> <?php echo __('preview with sample data');?></div>
                </div>
                <div class="wv-template-actions">
                    <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'), array('controller'=>'Realestateletters','action'=>'templateedit',$post['Template']['id']), array('escape'=>false,'class'=>'btn btn-primary'));?>
                    <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print'), array('controller'=>'Realestateletters','action'=>'templateprint',$post['Template']['id']), array('escape'=>false,'class'=>'btn btn-success','target'=>'_blank'));?>
                    <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back'),array('controller'=>'Realestateletters','action'=>'templates'),array('escape'=>false,'class'=>'btn btn-default'));?>
                </div>
            </div>
            <div class="wv-template-meta">
                <div class="wv-template-meta-card">
                    <span class="k"><?php echo __('Template Type');?></span>
                    <span class="v"><?php echo h($typeLabel);?></span>
                </div>
                <div class="wv-template-meta-card">
                    <span class="k"><?php echo __('Template Name');?></span>
                    <span class="v"><?php echo h($post['Template']['name']);?></span>
                </div>
                <div class="wv-template-meta-card">
                    <span class="k"><?php echo __('Status');?></span>
                    <span class="v"><span class="wv-template-status <?php echo h($statusClass);?>"><?php echo h($statusLabel);?></span></span>
                </div>
                <div class="wv-template-signatures">
                    <?php
                    $sigItems = array(
                        __('CEO') => array(isset($post['Template']['ceo_signature']) ? $post['Template']['ceo_signature'] : '', isset($post['Template']['ceo_signature_dir']) ? $post['Template']['ceo_signature_dir'] : 'letter_signatures'),
                        __('Chairman') => array(isset($post['Template']['chairman_signature']) ? $post['Template']['chairman_signature'] : '', isset($post['Template']['chairman_signature_dir']) ? $post['Template']['chairman_signature_dir'] : 'letter_signatures')
                    );
                    foreach($sigItems as $sigLabel=>$sigData){
                        $sigFile = trim((string)$sigData[0]);
                        $sigDir = trim((string)$sigData[1]) !== '' ? trim((string)$sigData[1]) : 'letter_signatures';
                        echo '<div class="wv-template-signature"><div>';
                        if($sigFile !== ''){
                            echo $this->Html->image(trim($sigDir,'/').'/'.$sigFile);
                        }else{
                            echo '<span>'.__('Not uploaded').'</span>';
                        }
                        echo '</div><div><strong>'.h($sigLabel).' '.__('Signature').'</strong><span>'.($sigFile !== '' ? h($sigFile) : __('No file selected')).'</span></div></div>';
                    }
                    ?>
                </div>
            </div>
            <div class="wv-template-preview-card">
                <div class="wv-template-preview-head">
                    <h4><?php echo __('Template Preview');?></h4>
                    <span class="wv-template-preview-note"><?php echo __('Placeholders are replaced with sample data for preview only.');?></span>
                </div>
                <?php $previewDescription = isset($previewDescription) ? (string)$previewDescription : (isset($post['Template']['description']) ? (string)$post['Template']['description'] : '');?>
                <?php if(!empty($isFullHtmlTemplate)){ ?>
                    <iframe class="wv-template-frame" srcdoc="<?php echo h($previewDescription);?>"></iframe>
                <?php } else { ?>
                    <div class="wv-template-body-fallback">
                        <?php echo $previewDescription;?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
