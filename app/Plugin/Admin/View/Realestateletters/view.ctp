<div class="page-title">
    <div class="title-env">
        <h1 class="title"><?php echo __('View Real Estate Letter');?></h1>
    </div>
</div>
<?php $letterStatusArr = isset($letterStatusArr) ? (array)$letterStatusArr : array('Published' => __('Issued'), 'Unpublished' => __('Cancle')); ?>
<div class="panel">
    <div class="panel-body">
        <div class="btn-group" style="margin-bottom:12px;">
            <?php echo $this->Html->link('<span class="fa fa-edit"></span>&nbsp;'.__('Edit'),array('controller'=>'Realestateletters','action'=>'edit',$post['Realestateletter']['id']),array('escape'=>false,'class'=>'btn btn-primary'));?>
            <?php echo $this->Html->link('<span class="fa fa-print"></span>&nbsp;'.__('Print'),array('controller'=>'Realestateletters','action'=>'printletter',$post['Realestateletter']['id']),array('escape'=>false,'class'=>'btn btn-success','target'=>'_blank'));?>
            <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>&nbsp;'.__('Export PDF'),array('controller'=>'Realestateletters','action'=>'exportletterpdf',$post['Realestateletter']['id']),array('escape'=>false,'class'=>'btn btn-danger','target'=>'_blank'));?>
            <?php echo $this->Html->link('<span class="fa fa-arrow-left"></span>&nbsp;'.__('Back'),array('controller'=>'Realestateletters','action'=>'index'),array('escape'=>false,'class'=>'btn btn-default'));?>
        </div>
        <table class="table table-bordered">
            <tr>
                <th width="25%"><?php echo __('Reference No');?></th>
                <td><?php echo h($post['Realestateletter']['reference_no']);?></td>
            </tr>
            <tr>
                <th><?php echo __('Title');?></th>
                <td><?php echo h($post['Realestateletter']['title']);?></td>
            </tr>
            <tr>
                <th><?php echo __('Type');?></th>
                <td><?php echo h($letterTypeLabel);?></td>
            </tr>
            <tr>
                <th><?php echo __('Date');?></th>
                <td><?php echo !empty($post['Realestateletter']['letter_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$post['Realestateletter']['letter_date']) : '';?></td>
            </tr>
            <tr>
                <th><?php echo __('Client');?></th>
                <td><?php echo h($post['Client']['name']);?></td>
            </tr>
            <tr>
                <th><?php echo __('Father / Guardian');?></th>
                <td>
                    <?php
                    $fatherName = !empty($post['Client']['fathername']) ? trim((string)$post['Client']['fathername']) : '';
                    $relationType = !empty($post['Client']['relation_type']) ? trim((string)$post['Client']['relation_type']) : '';
                    $gender = !empty($post['Client']['gender']) ? trim((string)$post['Client']['gender']) : '';
                    $clientName = !empty($post['Client']['name']) ? trim((string)$post['Client']['name']) : '';
                    if ($fatherName === '' && preg_match('/^(.*?)\s*(?:(S\s*\/\s*O|D\s*\/\s*O|W\s*\/\s*O|SON\s+OF|DAUGHTER\s+OF|WIFE\s+OF))\s+(.+)$/i', $clientName, $pm)) {
                        $fatherName = trim($pm[3]);
                        $relWord = strtoupper(str_replace(' ', '', $pm[2]));
                        if (strpos($relWord, 'W') !== false) {
                            $relationType = 'Husband';
                        } elseif (strpos($relWord, 'D') !== false) {
                            $relationType = 'Father';
                        } else {
                            $relationType = 'Father';
                        }
                    }
                    if ($fatherName !== '') {
                        $prefix = 'S/O';
                        $relLower = strtolower($relationType);
                        $genderLower = strtolower($gender);
                        if ($relLower === 'husband' || strpos($relLower, 'wife') !== false || strpos($relLower, 'w/o') !== false) {
                            $prefix = 'W/O';
                        } elseif ($genderLower === 'female' || $genderLower === 'f' || $relLower === 'daughter' || strpos($relLower, 'd/o') !== false) {
                            $prefix = 'D/O';
                        }
                        echo '<strong>' . h($fatherName) . '</strong>';
                        echo ' <span class="badge" style="background:#e0f2fe;color:#0369a1;font-size:10px;padding:2px 6px;">' . h($prefix) . '</span>';
                        if ($relationType !== '') {
                            echo ' <small class="text-muted">(' . h($relationType) . ')</small>';
                        }
                    } else {
                        echo '<span class="text-muted">-</span>';
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th><?php echo __('Property');?></th>
                <td><?php echo h($post['Property']['name']);?></td>
            </tr>
            <tr>
                <th><?php echo __('Membership');?></th>
                <td><?php echo h($post['Deal']['invoice_no']);?></td>
            </tr>
            <tr>
                <th><?php echo __('Status');?></th>
                <?php $statusText = isset($post['Realestateletter']['status']) ? trim((string)$post['Realestateletter']['status']) : 'Published'; ?>
                <td><?php echo h(isset($letterStatusArr[$statusText]) ? $letterStatusArr[$statusText] : $statusText);?></td>
            </tr>
        </table>
        <hr>
        <h4><?php echo __('Letter Body');?></h4>
        <?php if(!empty($isFullHtmlTemplate)){ ?>
            <iframe src="<?php echo $this->Html->url(array('controller'=>'Realestateletters','action'=>'printletter',$post['Realestateletter']['id']));?>" style="width:100%;min-height:1050px;border:1px solid #d9d9d9;background:#fff;"></iframe>
        <?php } else { ?>
            <div class="well" style="background:#fff;">
                <?php echo $renderedDescription;?>
            </div>
        <?php } ?>
    </div>
</div>
