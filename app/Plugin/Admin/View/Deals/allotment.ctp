<?php echo $this->Html->css('print.css');?>
<style type="text/css">
.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
border-top: 0px solid #ddd;
}
.recipt_footer{position:relative;top: 25px;}
</style>
<table width="100%" class="table" style="max-height:960px;border-top: 0px">
    <tr>
        <td>
            <?php if(strlen($frontLogo)>0){?><?php echo $this->Html->image($frontLogo,array('alt'=>$siteName,'class'=>'','height'=>'100'));}?>  
        </td>
        <td colspan="4">
            <table>
            <tr><td><h2><?php echo $siteOrganization;?></h2></td></tr>
            <tr><td><p><?php echo $siteAddress;?></p></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="4" align="center"><strong><?php echo __('AGREED PAYMENT PLAN');?></strong></td>        
    </tr>
    <tr>
        <td valign="top" align="left"><strong><?php echo __('MEMBERSHIP NO');?></strong></td>
        <td valign="top" align="left"><?php echo h($post['Deal']['invoice_no']);?></td>
        <td align="left" valign="top"><strong><?php echo __('Booking Date');?></strong></td>
        <td align="left" valign="top"><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($post['Deal']['date']));?></td>
    </tr>
    <tr>
        <td align="left" valign="top"><strong><?php echo __('Client');?></strong></td>
        <td align="left" valign="top"><?php echo h($post['Client']['name']);?></td>
        <td align="left" valign="top"><strong><?php echo !empty($post['Client']['relation_type']) ? h($post['Client']['relation_type']).' '.__('Name') : __('Father / Guardian'); ?></strong></td>
        <td align="left" valign="top"><?php echo !empty($post['Client']['fathername']) ? h($post['Client']['fathername']) : '-'; ?></td>
    </tr>
    <tr>
        <td align="left" valign="top"><strong><?php echo __('Contact');?></strong></td>
        <td align="left" valign="top"><?php echo h($post['Client']['phone']);?></td>
        <td><strong><small class="text-danger"><?php echo __('C.N.I.C NO');?></small></strong></td>
        <td><?php echo h($post['Client']['pincode']);?></td>
    </tr>
    <tr>
        <td colspan="4" align="left" valign="top"><hr/></td>
    </tr>
    <tr>
        <td align="left" valign="top"><strong><?php echo __('Property');?></strong></td>
        <td align="left" valign="top" colspan="3"><?php echo h($post['Property']['name']);?></td>
    </tr>
    <tr>
        <td align="left" valign="top"><strong><?php echo __('Type');?></strong></td>
        <td align="left" valign="top"><?php echo h($post['Type']['name']);?>&nbsp;<strong><?php echo __('For');?></strong>&nbsp;<?php echo h($post['Contract']['name']);?></td>
        <td align="left" valign="top"><strong><?php echo __('Area');?></strong></td>
        <td align="left" valign="top"><?php echo $post['Property']['area'].' '.$post['Unit']['name'];?></td>
    </tr>
    <tr>
        <td align="left" valign="top"><strong><?php echo __('Amount');?></strong></td>
        <td valign="top" align="left"><?php echo $currency.$this->Number->format($post['Property']['price']);?></td>
        <td align="left" valign="top"><strong><?php echo __('Discount');?></strong></td>
        <td valign="top" align="left"><?php echo $currency.$this->Number->format($post['Deal']['discount']);?></td>
    </tr>
    <tr>
        <td align="left" valign="top"><strong><?php echo __('Total Amount');?></strong></td>
        <td colspan="3" valign="top" align="left"><strong><?php echo $currency.$this->Number->format($post['Deal']['total_amount']);?></strong></td>  
    </tr>
</table>
<?php if($plansPaymentArr)
    {
        ?>
        <table width="100%" class="table" border="1">
       <tr>
            <td colspan="4" align="left"><strong><?php echo __('PAYMENT DETAILS');?></strong></td>        
        </tr>
        <tr>
            <th align="left" valign="top"><?php echo __('#');?></th>
            <th align="left" valign="top"><?php echo __('Installment');?></th>
            <th align="left" valign="top"><?php echo __('Amount');?></th>
            <th align="left" valign="top"><?php echo __('Due Date');?></th>            
        </tr>
        <?php $serialNo=0;$totalAmount=0;
        foreach($plansPaymentArr as $value):$serialNo++;$totalAmount=$totalAmount+$value['PlansPayment']['amount'];?>
        <tr>
           <td align="left" valign="top"><?php echo $serialNo;?></td>
            <td align="left" valign="top"><?php echo h($value['PlansPayment']['name']);?></td>
            <td align="left" valign="top"><?php echo $currency.$this->Number->format($value['PlansPayment']['amount']);?></td>
            <td align="left" valign="top"><?php echo $this->Time->format($sysDay.$dateSep.$sysMonth.$dateSep.$sysYear,h($value['PlansPayment']['date']));?></td>            
        </tr>
        <?php endforeach;unset($value);?>
        <tr><td colspan="2" align="right"><strong><?php echo __('Total');?></strong></td><td colspan="2"><strong><?php echo $this->Number->format($totalAmount);?></strong></td></tr>
        </table>
        <?php }?>
<table width="100%" class="table">   
    <br>
    <tr><td colspan="4" valign="top" align="LEFT"><div class="recipt_footer"><strong><?php echo __('Signature');?></strong></div></td></tr>
    
    <tr><td colspan="2" valign="top" align="LEFT"><div class="recipt_footer"><p>
<?php echo __('Thanking You,');?><br>
<?php echo __('Yours faithfully,');?><br>
<strong><?php echo __('For M/s.').' '.$siteOrganization; ?></strong>
</p></div></td>
<br>
<td colspan="2" valign="top" align="right"><div class="recipt_footer"><strong><?php echo __('APPLICANT');?><br>
<?php echo h($post['Client']['name']);?></strong><?php if(!empty($post['Client']['fathername'])){ ?><br><small><?php echo !empty($post['Client']['relation_type']) && $post['Client']['relation_type']==='Husband' ? 'W/O' : 'S/O'; ?> <?php echo h($post['Client']['fathername']);?></small><?php } ?></div>
<br>
<?php echo h($post['Client']['address']);?><br>
</td>
</tr>
</table>
<script type="text/javascript">
setTimeout(function(){if (typeof(window.print) != 'undefined') {
    window.print();
}}, 300);
</script>
