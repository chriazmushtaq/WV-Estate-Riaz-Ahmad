<div class="container">
    <div class="panel panel-custom mrg">
        <div class="panel-heading"><?php echo __('SMS / WhatsApp');?> <?php echo __('Template');?><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button></div>
        <div class="panel-body"><?php echo $this->Session->flash();?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <tr>
                        <td><strong><small class="text-danger"><?php echo __('Name');?></small></strong></td>
                        <td><?php echo h($post['Smstemplate']['name']);?></td>
                    </tr>
                    <tr>
                        <td><strong><small class="text-danger"><?php echo __('Type Code');?></small></strong></td>
                        <td><?php echo h(!empty($post['Smstemplate']['type']) ? $post['Smstemplate']['type'] : __('N/A'));?></td>
                    </tr>
                    <tr>
                        <td><strong><small class="text-danger"><?php echo __('Template');?></small></strong></td>
                        <td><pre style="white-space:pre-wrap;margin:0;"><?php echo h($post['Smstemplate']['description']);?></pre></td>
                    </tr>
                    <tr>
                        <td><strong><small class="text-danger"><?php echo __('Preview');?></small></strong></td>
                        <td class="js-template-preview-scope">
                            <textarea class="js-template-preview-source" style="display:none;"><?php echo h($post['Smstemplate']['description']);?></textarea>
                            <?php echo $this->element('message_template_live_preview', array('previewMode' => 'auto', 'defaultMode' => 'sms', 'initialType' => !empty($post['Smstemplate']['type']) ? $post['Smstemplate']['type'] : ''));?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
