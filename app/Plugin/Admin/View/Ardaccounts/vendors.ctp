<?php
$this->Js->JqueryEngine->jQueryObject = 'jQuery';
$this->Paginator->options(array('update' => '#resultDiv', 'evalScripts' => true));
$editHiddenId = !empty($this->request->data['Ardvendor']['id']) ? (int)$this->request->data['Ardvendor']['id'] : (!empty($editIdFromUrl) ? (int)$editIdFromUrl : 0);
$isEdit = ($editHiddenId > 0);
$basePrefix = trim((string)$this->request->base, '/');
if($basePrefix !== '')
{
    $basePrefix = '/' . $basePrefix;
}
$vendorsBaseUrl = $basePrefix . '/admin/Ardaccounts/vendors/' . (int)$currentProjectId;
$scheme = (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? (string)$_SERVER['HTTP_HOST'] : '';
$vendorsAbsoluteUrl = ($host !== '') ? ($scheme . '://' . $host . $vendorsBaseUrl) : $vendorsBaseUrl;
?>
<div id="resultDiv">
<div class="page-title"><div class="title-env"><h1 class="title"><?php echo __('Construction Vendors');?></h1></div></div>
<div class="panel"><?php echo $this->Session->flash();?>
    <div class="panel-heading">
        <div class="btn-group">
            <?php echo $this->Html->link('<span class="fa fa-home"></span>&nbsp;'.__('ARD Dashboard'), array('controller' => 'Ardaccounts', 'action' => 'dashboard', $currentProjectId), array('escape' => false, 'class' => 'btn btn-default'));?>
            <?php echo $this->Html->link('<span class="fa fa-cubes"></span>&nbsp;'.__('Stock'), array('controller' => 'Ardaccounts', 'action' => 'stock', $currentProjectId), array('escape' => false, 'class' => 'btn btn-warning'));?>
            <button type="button" class="btn btn-success" id="openVendorModal"><span class="fa fa-plus"></span> <?php echo $isEdit ? __('Edit Vendor') : __('Add Vendor');?></button>
        </div>
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <tr>
                    <th>#</th>
                    <th><?php echo __('Vendor Name');?></th>
                    <th><?php echo __('Phone');?></th>
                    <th><?php echo __('Address');?></th>
                    <th><?php echo __('Notes');?></th>
                    <th><?php echo __('Status');?></th>
                    <th><?php echo __('Action');?></th>
                </tr>
                <?php
                $pageParams = $this->Paginator->params();
                $limit = isset($pageParams['limit']) ? (int)$pageParams['limit'] : 1;
                $page = isset($pageParams['page']) ? (int)$pageParams['page'] : 1;
                $serialNo = 1 * $limit * ($page - 1) + 1;
                foreach((array)$rows as $row):
                    $id = (int)$row['Ardvendor']['id'];
                    $rowProjectId = (int)$row['Ardvendor']['project_id'];
                ?>
                <tr>
                    <td><?php echo $serialNo++;?></td>
                    <td><?php echo h($row['Ardvendor']['vendor_name']);?></td>
                    <td><?php echo h($row['Ardvendor']['phone']);?></td>
                    <td><?php echo h($row['Ardvendor']['address']);?></td>
                    <td><?php echo h($row['Ardvendor']['notes']);?></td>
                    <td><span class="label label-<?php echo (strtolower((string)$row['Ardvendor']['status']) === 'active' ? 'success' : 'danger');?>"><?php echo h(ucfirst((string)$row['Ardvendor']['status']));?></span></td>
                    <td>
                        <?php
                        $rowVendorsBaseUrl = $basePrefix . '/admin/Ardaccounts/vendors/' . $rowProjectId;
                        $rowVendorsAbsoluteUrl = ($host !== '') ? ($scheme . '://' . $host . $rowVendorsBaseUrl) : $rowVendorsBaseUrl;
                        $editUrl = $rowVendorsAbsoluteUrl . '?edit=' . $id;
                        echo '<a href="javascript:void(0);" class="btn btn-xs btn-info js-view-vendor"'
                            . ' data-vendor-name="' . h((string)$row['Ardvendor']['vendor_name'], true) . '"'
                            . ' data-phone="' . h((string)$row['Ardvendor']['phone'], true) . '"'
                            . ' data-address="' . h((string)$row['Ardvendor']['address'], true) . '"'
                            . ' data-notes="' . h((string)$row['Ardvendor']['notes'], true) . '"'
                            . ' data-status="' . h((string)$row['Ardvendor']['status'], true) . '"'
                            . '><span class="fa fa-eye"></span> '.__('View').'</a>';
                        echo ' ';
                        echo $this->Html->link(
                            '<span class="fa fa-book"></span> '.__('Ledger'),
                            array(
                                'controller' => 'Ardaccounts',
                                'action' => 'reports',
                                $rowProjectId,
                                '?' => array(
                                    'tab' => 'vendor_ledger',
                                    'preset' => 'lifetime',
                                    'start' => '',
                                    'end' => '',
                                    'project_id' => $rowProjectId,
                                    'vendor_id' => $id
                                )
                            ),
                            array('escape' => false, 'class' => 'btn btn-xs btn-primary')
                        );
                        echo ' ';
                        echo '<a href="'.h($editUrl).'" class="btn btn-xs btn-warning"><span class="fa fa-edit"></span> '.__('Edit').'</a>';
                        echo ' ';
                        echo $this->Html->link('<span class="fa fa-trash"></span> '.__('Delete'), array('controller' => 'Ardaccounts', 'action' => 'deletevendor', $id, $rowProjectId), array('escape' => false, 'class' => 'btn btn-xs btn-danger', 'onclick' => "return confirm('".__('Are you sure?')."');"));
                        ?>
                    </td>
                </tr>
                <?php endforeach;?>
            </table>
        </div>
        <?php echo $this->element('pagination');?>
    </div>
</div>

<div class="modal fade" id="vendorEntryModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo $isEdit ? __('Edit Vendor') : __('Add Vendor');?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->create('Ardvendor', array('url' => $vendorsAbsoluteUrl, 'class' => 'form-horizontal'));?>
                <?php if($userType != 'ADR'){echo $this->Form->hidden('project_id', array('value' => $agencyId));}?>
                <?php if($isEdit){echo $this->Form->hidden('id', array('value' => $editHiddenId));}?>
                <?php if($userType == 'ADR'){?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Agency');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('project_id', $projectName, array('empty' => __('Please Select'), 'class' => 'form-control', 'label' => false, 'div' => false));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('status', array('active' => __('Active'), 'inactive' => __('Inactive')), array('empty' => false, 'class' => 'form-control', 'label' => false, 'div' => false));?></div>
                </div>
                <?php } else { ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Status');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->select('status', array('active' => __('Active'), 'inactive' => __('Inactive')), array('empty' => false, 'class' => 'form-control', 'label' => false, 'div' => false));?></div>
                </div>
                <?php } ?>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Vendor Name');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('vendor_name', array('label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Vendor Name')));?></div>
                    <label class="col-sm-2 control-label"><?php echo __('Phone');?></label>
                    <div class="col-sm-4"><?php echo $this->Form->input('phone', array('label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Phone')));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Address');?></label>
                    <div class="col-sm-10"><?php echo $this->Form->input('address', array('type' => 'text', 'label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Address')));?></div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label"><?php echo __('Notes');?></label>
                    <div class="col-sm-10"><?php echo $this->Form->input('notes', array('label' => false, 'div' => false, 'class' => 'form-control', 'placeholder' => __('Notes')));?></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success"><span class="fa fa-save"></span> <?php echo ($isEdit ? __('Update Vendor') : __('Save Vendor'));?></button>
                <?php if($isEdit){echo '<a href="'.h($vendorsAbsoluteUrl).'" class="btn btn-danger"><span class="fa fa-close"></span> '.__('Cancel Edit').'</a>';}?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
                <?php echo $this->Form->end();?>
            </div>
        </div>
    </div>
</div>
</div>

<div class="modal fade" id="vendorViewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo __('Vendor Details');?></h4>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" style="margin-bottom:0;">
                    <tr><th style="width:35%;"><?php echo __('Vendor Name');?></th><td id="VendorViewName">-</td></tr>
                    <tr><th><?php echo __('Phone');?></th><td id="VendorViewPhone">-</td></tr>
                    <tr><th><?php echo __('Address');?></th><td id="VendorViewAddress">-</td></tr>
                    <tr><th><?php echo __('Notes');?></th><td id="VendorViewNotes">-</td></tr>
                    <tr><th><?php echo __('Status');?></th><td id="VendorViewStatus">-</td></tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Close');?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(function($){
    var $vendorModal = $('#vendorEntryModal');
    var $vendorViewModal = $('#vendorViewModal');
    function nvl(text){
        var value = $.trim(String(text || ''));
        return value === '' ? '-' : value;
    }
    $('#openVendorModal').on('click', function(e){
        e.preventDefault();
        $vendorModal.modal('show');
    });
    $(document).on('click', '.js-view-vendor', function(e){
        e.preventDefault();
        var $btn = $(this);
        $('#VendorViewName').text(nvl($btn.attr('data-vendor-name')));
        $('#VendorViewPhone').text(nvl($btn.attr('data-phone')));
        $('#VendorViewAddress').text(nvl($btn.attr('data-address')));
        $('#VendorViewNotes').text(nvl($btn.attr('data-notes')));
        var statusRaw = nvl($btn.attr('data-status'));
        if(statusRaw !== '-'){
            statusRaw = statusRaw.charAt(0).toUpperCase() + statusRaw.slice(1).toLowerCase();
        }
        $('#VendorViewStatus').text(statusRaw);
        $vendorViewModal.modal('show');
    });
    <?php if($isEdit){?>
    $vendorModal.modal('show');
    <?php }?>
});
</script>
