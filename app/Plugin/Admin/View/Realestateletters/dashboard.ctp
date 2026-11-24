<?php
$autoLetterTypeArr = isset($autoLetterTypeArr) ? (array)$autoLetterTypeArr : array();
$lettersProjectId = isset($lettersProjectId) ? (int)$lettersProjectId : 0;
?>

<style type="text/css">
.letters-dash-wrap {
    max-width: 100%;
}
.letters-hero {
    border: 1px solid #dbe4ef;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
    background: linear-gradient(135deg, #f9fbff 0%, #ffffff 70%);
}
.letters-hero-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}
.letters-hero-title {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
    color: #1b2c3d;
}
.letters-hero-sub {
    margin: 2px 0 0 0;
    color: #6b7d90;
    font-size: 13px;
}
.letters-stat-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(160px, 1fr));
    gap: 12px;
}
.letters-stat-card {
    border-radius: 10px;
    border: 1px solid #dce6f2;
    background: #fff;
    padding: 12px 14px;
}
.letters-stat-label {
    font-size: 12px;
    color: #607487;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: .4px;
    margin-bottom: 3px;
}
.letters-stat-value {
    font-size: 30px;
    font-weight: 800;
    color: #15293d;
    line-height: 1.15;
}
.letters-toolbox {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
    align-items: center;
}
.letters-filter-form {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.letters-filter-form .form-control {
    min-width: 170px;
}
.letters-type-card {
    border: 1px solid #d9e3ee;
    border-radius: 12px;
    margin-bottom: 14px;
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(17, 38, 64, 0.05);
    overflow: hidden;
}
.letters-type-head {
    padding: 12px 14px;
    border-bottom: 1px solid #e7eef6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
}
.letters-type-name {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #1e3349;
}
.letters-type-count {
    background: #eef4fb;
    color: #1b4d7c;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
    padding: 4px 10px;
    white-space: nowrap;
}
.letters-type-body {
    padding: 12px 14px 14px;
}
.letters-type-actions .btn {
    margin-right: 6px;
    margin-top: 6px;
    border-radius: 8px;
    font-weight: 700;
}
.letters-recent-panel {
    border: 1px solid #dce5f0;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 4px;
}
.letters-recent-head {
    background: #f6f9fc;
    border-bottom: 1px solid #dfe8f2;
    padding: 12px 14px;
    font-size: 18px;
    font-weight: 700;
    color: #1f3348;
}
.letters-recent-panel table {
    margin-bottom: 0;
}
.letters-recent-panel .table > thead > tr > th {
    background: #f9fbfd;
    color: #32506f;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .4px;
}
.letters-badge-auto {
    margin-left: 5px;
    background: #4f7fa8;
}
@media (max-width: 991px) {
    .letters-hero-head {
        flex-direction: column;
        align-items: flex-start;
    }
    .letters-toolbox {
        justify-content: flex-start;
    }
}
@media (max-width: 640px) {
    .letters-stat-grid {
        grid-template-columns: 1fr;
    }
    .letters-hero-title {
        font-size: 22px;
    }
}
</style>

<div class="letters-dash-wrap">
    <?php echo $this->Session->flash();?>

    <div class="letters-hero">
        <div class="letters-hero-head">
            <div>
                <h1 class="letters-hero-title"><?php echo __('Letters Dashboard');?></h1>
                <p class="letters-hero-sub"><?php echo __('Manage all letter types, templates, and recent issuance from one place.');?></p>
            </div>
            <div class="letters-toolbox">
                <?php if(!empty($lettersCanUseAgencyFilters)){ ?>
                    <?php echo $this->Form->create(false, array(
                        'type' => 'get',
                        'url' => array('controller' => 'Realestateletters', 'action' => 'dashboard'),
                        'class' => 'letters-filter-form'
                    ));?>
                    <?php echo $this->Form->select('project_id', (array)$lettersProjectName, array(
                        'empty' => __('All Agency'),
                        'value' => $lettersProjectId,
                        'label' => false,
                        'div' => false,
                        'class' => 'form-control'
                    ));?>
                    <button type="submit" class="btn btn-default"><span class="fa fa-filter"></span>&nbsp;<?php echo __('Load');?></button>
                    <?php echo $this->Form->end();?>
                <?php } ?>

                <?php
                echo $this->Html->link(
                    '<span class="fa fa-files-o"></span>&nbsp;'.__('Manage Templates'),
                    array('controller'=>'Realestateletters','action'=>'templates'),
                    array('escape'=>false,'class'=>'btn btn-info')
                );

                $trashUrl = array('controller' => 'Realestateletters', 'action' => 'trash');
                if ($lettersProjectId > 0) {
                    $trashUrl['?'] = array('project_id' => $lettersProjectId);
                }
                echo $this->Html->link(
                    '<span class="fa fa-trash"></span>&nbsp;'.__('Letters Trash'),
                    $trashUrl,
                    array('escape'=>false,'class'=>'btn btn-warning')
                );

                $lettersListUrl = array('controller' => 'Realestateletters', 'action' => 'index');
                if ($lettersProjectId > 0) {
                    $lettersListUrl['?'] = array('project_id' => $lettersProjectId);
                }
                echo $this->Html->link(
                    '<span class="fa fa-list"></span>&nbsp;'.__('Manage All Letters'),
                    $lettersListUrl,
                    array('escape'=>false,'class'=>'btn btn-primary')
                );
                ?>
            </div>
        </div>

        <div class="letters-stat-grid">
            <div class="letters-stat-card">
                <div class="letters-stat-label"><?php echo __('Total Letters');?></div>
                <div class="letters-stat-value"><?php echo (int)$totalLetters;?></div>
            </div>
            <div class="letters-stat-card">
                <div class="letters-stat-label"><?php echo __('Templates');?></div>
                <div class="letters-stat-value"><?php echo (int)$templateCount;?></div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php foreach($letterTypeArr as $typeCode => $typeLabel){ $count = isset($typeCounts[$typeCode]) ? (int)$typeCounts[$typeCode] : 0; ?>
            <div class="col-lg-4 col-md-6">
                <div class="letters-type-card">
                    <div class="letters-type-head">
                        <h4 class="letters-type-name"><?php echo h($typeLabel);?></h4>
                        <span class="letters-type-count"><?php echo __('Letters');?>: <?php echo $count;?></span>
                    </div>
                    <div class="letters-type-body">
                        <?php
                        $addUrl = array('controller' => 'Realestateletters', 'action' => 'add', $typeCode);
                        $manageUrl = array('controller' => 'Realestateletters', 'action' => 'index', $typeCode);
                        if ($lettersProjectId > 0) {
                            $addUrl['?'] = array('project_id' => $lettersProjectId);
                            $manageUrl['?'] = array('project_id' => $lettersProjectId);
                        }
                        ?>
                        <div class="letters-type-actions">
                            <?php
                            echo $this->Html->link('<span class="fa fa-plus"></span>&nbsp;'.__('Add'), $addUrl, array('escape'=>false,'class'=>'btn btn-success btn-sm'));
                            echo $this->Html->link('<span class="fa fa-list"></span>&nbsp;'.__('Manage'), $manageUrl, array('escape'=>false,'class'=>'btn btn-default btn-sm'));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="letters-recent-panel">
        <div class="letters-recent-head"><?php echo __('Recent Letters');?></div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><?php echo __('#');?></th>
                        <th><?php echo __('Ref No');?></th>
                        <th><?php echo __('Type');?></th>
                        <th><?php echo __('Client');?></th>
                        <th><?php echo __('Property');?></th>
                        <th><?php echo __('Membership');?></th>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($recentLetters)){ ?>
                        <?php $sn = 1; foreach((array)$recentLetters as $row):
                            $id = (int)$row['Realestateletter']['id'];
                            $typeCode = isset($row['Realestateletter']['letter_type']) ? $row['Realestateletter']['letter_type'] : '';
                            $typeLabel = isset($letterTypeArr[$typeCode]) ? $letterTypeArr[$typeCode] : $typeCode;
                        ?>
                            <tr>
                                <td><?php echo $sn++;?></td>
                                <td><?php echo h($row['Realestateletter']['reference_no']);?></td>
                                <td>
                                    <?php echo h($typeLabel);?>
                                    <?php if(isset($autoLetterTypeArr[$typeCode])){ ?>
                                        <span class="label label-info letters-badge-auto"><?php echo __('Auto');?></span>
                                    <?php } ?>
                                </td>
                                <td><?php echo h($row['Client']['name']);?></td>
                                <td><?php echo h($row['Property']['name']);?></td>
                                <td><?php echo h($row['Deal']['invoice_no']);?></td>
                                <td><?php echo !empty($row['Realestateletter']['letter_date']) ? $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,$row['Realestateletter']['letter_date']) : '';?></td>
                                <td>
                                    <?php echo $this->Html->link('<span class="fa fa-eye"></span>',array('controller'=>'Realestateletters','action'=>'view',$id),array('escape'=>false,'class'=>'btn btn-default btn-xs','title'=>__('View')));?>
                                    <?php echo $this->Html->link('<span class="fa fa-edit"></span>',array('controller'=>'Realestateletters','action'=>'edit',$id),array('escape'=>false,'class'=>'btn btn-primary btn-xs','title'=>__('Edit')));?>
                                    <?php echo $this->Html->link('<span class="fa fa-print"></span>',array('controller'=>'Realestateletters','action'=>'printletter',$id),array('escape'=>false,'class'=>'btn btn-success btn-xs','title'=>__('Print'),'target'=>'_blank'));?>
                                    <?php echo $this->Html->link('<span class="fa fa-file-pdf-o"></span>',array('controller'=>'Realestateletters','action'=>'exportletterpdf',$id),array('escape'=>false,'class'=>'btn btn-danger btn-xs','title'=>__('Export PDF'),'target'=>'_blank'));?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="8" class="text-center"><?php echo __('No letters found.');?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
