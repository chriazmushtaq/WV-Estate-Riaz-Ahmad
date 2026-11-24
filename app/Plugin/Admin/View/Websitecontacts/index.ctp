<style>
.wv-admin-web{background:#f6f8fb;padding:16px;}
.wv-web-head{background:linear-gradient(135deg,#07152a,#102746 62%,#ff0a46 155%);border-radius:8px;color:#fff;margin-bottom:16px;padding:18px 20px;}
.wv-web-head h3{color:#fff;font-size:22px;font-weight:800;margin:0 0 5px;}
.wv-web-head p{color:#cbd5e1;margin:0;}
.wv-web-card{background:#fff;border:1px solid #dfe7f1;border-radius:8px;box-shadow:0 10px 24px rgba(15,23,42,.06);margin-bottom:18px;overflow:hidden;}
.wv-web-card-title{align-items:center;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;padding:12px 14px;}
.wv-web-card-title h4{color:#07152a;font-size:16px;font-weight:800;margin:0;}
.wv-web-table{margin:0;}
.wv-web-table th{background:#f8fafc;color:#64748b;font-size:11px;text-transform:uppercase;}
.wv-web-table td{font-size:12px;vertical-align:middle!important;}
.wv-status{border-radius:999px;color:#fff;display:inline-block;font-size:10px;font-weight:800;padding:4px 9px;text-transform:uppercase;}
.wv-status-new{background:#ff0a46;}
.wv-status-read{background:#0b4d36;}
.wv-message-cell{max-width:360px;white-space:normal;}
.wv-muted{color:#64748b;}
.wv-action-row{display:flex;flex-wrap:wrap;gap:5px;min-width:245px;}
.wv-reply-modal .modal-header{background:#07152a;color:#fff;}
.wv-reply-modal .modal-title{color:#fff;font-weight:800;}
.wv-reply-target{background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;color:#334155;font-size:12px;margin-bottom:10px;padding:8px 10px;}
.wv-reply-modal textarea{min-height:150px;resize:vertical;}
</style>
<div class="wv-admin-web">
    <div class="wv-web-head">
        <h3><?php echo __('Website Inquiries');?></h3>
        <p><?php echo __('Manage website contact messages and property inquiries submitted from the public website.');?></p>
    </div>
    <?php echo $this->Session->flash();?>
    <div class="wv-web-card">
        <div class="wv-web-card-title">
            <h4><?php echo __('Website Contact Messages');?></h4>
            <span class="wv-muted"><?php echo __('Saved from Contact Us page');?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped wv-web-table">
                <thead>
                    <tr>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('Name');?></th>
                        <th><?php echo __('Phone');?></th>
                        <th><?php echo __('Email');?></th>
                        <th><?php echo __('Subject');?></th>
                        <th><?php echo __('Message');?></th>
                        <th><?php echo __('Status');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($contacts)){ ?>
                    <tr><td colspan="8" class="text-center"><?php echo __('No website contact messages found.');?></td></tr>
                    <?php } else { foreach($contacts as $row){ $status=strtolower((string)$row['WebsiteContact']['status']); ?>
                    <?php $contactId = (int)$row['WebsiteContact']['id']; ?>
                    <tr>
                        <td><?php echo !empty($row['WebsiteContact']['created']) ? h(date('d-M-Y H:i',strtotime($row['WebsiteContact']['created']))) : '-';?></td>
                        <td><strong><?php echo h($row['WebsiteContact']['name']);?></strong></td>
                        <td><?php if(!empty($row['WebsiteContact']['phone'])){ ?><a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$row['WebsiteContact']['phone']));?>"><?php echo h($row['WebsiteContact']['phone']);?></a><?php } ?></td>
                        <td><?php echo h($row['WebsiteContact']['email']);?></td>
                        <td><?php echo h($row['WebsiteContact']['subject']);?></td>
                        <td class="wv-message-cell"><?php echo nl2br(h($row['WebsiteContact']['message']));?></td>
                        <td><span class="wv-status <?php echo $status==='read' ? 'wv-status-read' : 'wv-status-new';?>"><?php echo h($row['WebsiteContact']['status']);?></span></td>
                        <td>
                            <div class="wv-action-row">
                                <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#ReplyWebsiteContact<?php echo $contactId;?>"><i class="fa fa-reply"></i> <?php echo __('Reply');?></button>
                                <?php echo $this->Html->link(__('Mark Read'),array('action'=>'mark',$contactId,'Read'),array('class'=>'btn btn-xs btn-success'));?>
                                <?php echo $this->Html->link(__('New'),array('action'=>'mark',$contactId,'New'),array('class'=>'btn btn-xs btn-default'));?>
                                <?php if(!empty($isSuperAdmin)){ ?>
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i> '.__('Edit'),array('action'=>'edit',$contactId),array('class'=>'btn btn-xs btn-warning','escape'=>false));?>
                                    <?php echo $this->Form->postLink('<i class="fa fa-trash"></i> '.__('Delete'),array('action'=>'delete',$contactId),array('class'=>'btn btn-xs btn-danger','escape'=>false,'confirm'=>__('Are you sure you want to delete this website contact?')));?>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php }} ?>
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <?php echo $this->element('pagination',array('IsSearch'=>'No','IsDropdown'=>'No'));?>
        </div>
    </div>
    <?php if(!empty($contacts)){ foreach($contacts as $row){ ?>
    <?php
    $contactId = (int)$row['WebsiteContact']['id'];
    $contactName = trim((string)$row['WebsiteContact']['name']);
    $replySiteName = !empty($siteName) ? $siteName : 'White Villas';
    $defaultReply = "Dear ".($contactName !== '' ? $contactName : 'Client').",\n\nThank you for contacting ".$replySiteName.". We have received your message and our team will contact you soon.\n\nRegards,\n".$replySiteName;
    ?>
    <div class="modal fade wv-reply-modal" id="ReplyWebsiteContact<?php echo $contactId;?>" tabindex="-1" role="dialog" aria-labelledby="ReplyWebsiteContactLabel<?php echo $contactId;?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="<?php echo $this->Html->url(array('action'=>'reply',$contactId));?>">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo __('Close');?>"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="ReplyWebsiteContactLabel<?php echo $contactId;?>"><?php echo __('Reply Website Contact');?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="wv-reply-target">
                            <strong><?php echo h($contactName !== '' ? $contactName : __('No Name'));?></strong><br>
                            <?php echo __('Email');?>: <?php echo h(!empty($row['WebsiteContact']['email']) ? $row['WebsiteContact']['email'] : '-');?> |
                            <?php echo __('Phone');?>: <?php echo h(!empty($row['WebsiteContact']['phone']) ? $row['WebsiteContact']['phone'] : '-');?>
                        </div>
                        <div class="form-group">
                            <label><?php echo __('Reply By');?></label>
                            <select name="data[WebsiteContactReply][method]" class="form-control" required>
                                <option value="email"><?php echo __('Email');?></option>
                                <option value="sms"><?php echo __('SMS');?></option>
                                <option value="whatsapp"><?php echo __('WhatsApp');?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?php echo __('Custom Message');?></label>
                            <textarea name="data[WebsiteContactReply][message]" class="form-control" required><?php echo h($defaultReply);?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?php echo __('Send / Open Reply');?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php }} ?>
    <div class="wv-web-card">
        <div class="wv-web-card-title">
            <h4><?php echo __('Website Property Inquiries');?></h4>
            <span class="wv-muted"><?php echo __('Latest Web Inquiry leads');?></span>
        </div>
        <div class="table-responsive">
            <table class="table table-striped wv-web-table">
                <thead>
                    <tr>
                        <th><?php echo __('Date');?></th>
                        <th><?php echo __('Client');?></th>
                        <th><?php echo __('Mobile');?></th>
                        <th><?php echo __('Email');?></th>
                        <th><?php echo __('Property');?></th>
                        <th><?php echo __('Remarks');?></th>
                        <th><?php echo __('Action');?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($propertyInquiries)){ ?>
                    <tr><td colspan="7" class="text-center"><?php echo __('No website property inquiries found.');?></td></tr>
                    <?php } else { foreach($propertyInquiries as $lead){ ?>
                    <?php
                    $leadId = (int)$lead['Lead']['id'];
                    $inquiryDateValue = '';
                    if(!empty($propertyInquiryDateField) && !empty($lead['Lead'][$propertyInquiryDateField]))
                    {
                        $inquiryDateValue = $lead['Lead'][$propertyInquiryDateField];
                    }
                    $propertyName = '';
                    if(!empty($lead['Property']['uniq_id']) || !empty($lead['Property']['name']))
                    {
                        $propertyName = trim((!empty($lead['Property']['uniq_id']) ? $lead['Property']['uniq_id'].' - ' : '').(!empty($lead['Property']['name']) ? $lead['Property']['name'] : ''));
                    }
                    ?>
                    <tr>
                        <td><?php echo h($inquiryDateValue);?></td>
                        <td><strong><?php echo h(!empty($lead['Lead']['lead_name']) ? $lead['Lead']['lead_name'] : '');?></strong></td>
                        <td><?php if(!empty($lead['Lead']['lead_mobile'])){ ?><a href="tel:<?php echo h(preg_replace('/[^0-9\+]/','',$lead['Lead']['lead_mobile']));?>"><?php echo h($lead['Lead']['lead_mobile']);?></a><?php } ?></td>
                        <td><?php echo h(!empty($lead['Lead']['lead_email']) ? $lead['Lead']['lead_email'] : '');?></td>
                        <td><?php echo h($propertyName);?></td>
                        <td class="wv-message-cell"><?php echo nl2br(h(!empty($lead['Lead']['remarks']) ? $lead['Lead']['remarks'] : ''));?></td>
                        <td>
                            <div class="wv-action-row">
                                <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#ReplyWebsiteLead<?php echo $leadId;?>"><i class="fa fa-reply"></i> <?php echo __('Reply');?></button>
                                <?php if(!empty($isSuperAdmin)){ ?>
                                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i> '.__('Edit'),array('controller'=>'Leads','action'=>'edit',$leadId),array('class'=>'btn btn-xs btn-warning','escape'=>false));?>
                                    <?php echo $this->Form->postLink('<i class="fa fa-trash"></i> '.__('Delete'),array('action'=>'deletelead',$leadId),array('class'=>'btn btn-xs btn-danger','escape'=>false,'confirm'=>__('Are you sure you want to delete this property inquiry?')));?>
                                <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <?php }} ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if(!empty($propertyInquiries)){ foreach($propertyInquiries as $lead){ ?>
    <?php
    $leadId = (int)$lead['Lead']['id'];
    $leadName = !empty($lead['Lead']['lead_name']) ? trim((string)$lead['Lead']['lead_name']) : '';
    $replySiteName = !empty($siteName) ? $siteName : 'White Villas';
    $leadDefaultReply = "Dear ".($leadName !== '' ? $leadName : 'Client').",\n\nThank you for your property inquiry. Our team has received your request and will contact you soon with details.\n\nRegards,\n".$replySiteName;
    ?>
    <div class="modal fade wv-reply-modal" id="ReplyWebsiteLead<?php echo $leadId;?>" tabindex="-1" role="dialog" aria-labelledby="ReplyWebsiteLeadLabel<?php echo $leadId;?>">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="post" action="<?php echo $this->Html->url(array('action'=>'leadreply',$leadId));?>">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo __('Close');?>"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="ReplyWebsiteLeadLabel<?php echo $leadId;?>"><?php echo __('Reply Property Inquiry');?></h4>
                    </div>
                    <div class="modal-body">
                        <div class="wv-reply-target">
                            <strong><?php echo h($leadName !== '' ? $leadName : __('No Name'));?></strong><br>
                            <?php echo __('Email');?>: <?php echo h(!empty($lead['Lead']['lead_email']) ? $lead['Lead']['lead_email'] : '-');?> |
                            <?php echo __('Mobile');?>: <?php echo h(!empty($lead['Lead']['lead_mobile']) ? $lead['Lead']['lead_mobile'] : '-');?>
                        </div>
                        <div class="form-group">
                            <label><?php echo __('Reply By');?></label>
                            <select name="data[WebsiteContactReply][method]" class="form-control" required>
                                <option value="email"><?php echo __('Email');?></option>
                                <option value="sms"><?php echo __('SMS');?></option>
                                <option value="whatsapp"><?php echo __('WhatsApp');?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?php echo __('Custom Message');?></label>
                            <textarea name="data[WebsiteContactReply][message]" class="form-control" required><?php echo h($leadDefaultReply);?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __('Cancel');?></button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> <?php echo __('Send / Open Reply');?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php }} ?>
</div>
