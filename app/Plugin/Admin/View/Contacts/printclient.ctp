<?php echo $this->Html->css('print.css');?>
<style type="text/css">
.table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
border-top: 0px solid #ddd;
}
</style>

<?php
if(!function_exists('wvResolveContactImage')){
    function wvResolveContactImage($filename, $customDir = ''){
        if(empty($filename)){
            return array('original' => '', 'thumb' => '');
        }
        $file = basename(trim((string)$filename));
        $dir = !empty($customDir) ? basename(trim((string)$customDir)) : 'contact';
        
        $candidates = array(
            'contact_thumb/' . $file,
            'contact/' . $file,
            $dir . '_thumb/' . $file,
            $dir . '/' . $file,
            'client_thumb/' . $file,
            'client/' . $file,
            'Uploads/clients/' . $file,
            'Uploads/images/' . $file
        );
        
        $original = '';
        $thumb = '';
        
        foreach(array_unique($candidates) as $candidate){
            $realPath = WWW_ROOT . 'img' . DS . str_replace('/', DS, $candidate);
            if(file_exists($realPath)){
                if($thumb === ''){
                    $thumb = $candidate;
                }
                if($original === '' && strpos($candidate, '_thumb') === false){
                    $original = '/img/' . $candidate;
                }
            }
        }
        
        if($thumb === ''){
            $thumb = 'contact_thumb/' . $file;
        }
        if($original === ''){
            $original = '/img/contact/' . $file;
        }
        
        return array('original' => $original, 'thumb' => $thumb);
    }
}

$profileImages = wvResolveContactImage(!empty($post['Contact']['photo']) ? $post['Contact']['photo'] : '', !empty($post['Contact']['dirt']) ? $post['Contact']['dirt'] : '');
$idFrontImages = wvResolveContactImage(!empty($post['Contact']['id_front']) ? $post['Contact']['id_front'] : '', !empty($post['Contact']['id_front_dir']) ? $post['Contact']['id_front_dir'] : '');
$idBackImages = wvResolveContactImage(!empty($post['Contact']['id_back']) ? $post['Contact']['id_back'] : '', !empty($post['Contact']['id_back_dir']) ? $post['Contact']['id_back_dir'] : '');
$sidePhotoImages = wvResolveContactImage(!empty($post['Contact']['side_photo']) ? $post['Contact']['side_photo'] : '', !empty($post['Contact']['side_photo_dir']) ? $post['Contact']['side_photo_dir'] : '');
?>

<table width="100%">
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
        <td colspan="4" align="center"><strong><?php echo $post['Category']['name'].__(' Details');?></strong></td>        
    </tr>
</table>

<table class="table table-bordered">
    <tr>
        <td><strong><small class="text-danger"><?php echo __('Name');?></small></strong></td>
        <td><?php echo h($post['Contact']['name']);?></td>
        <td><strong><small class="text-danger"><?php echo !empty($post['Contact']['relation_type']) ? h($post['Contact']['relation_type']).' '.__('Name') : __('Father / Guardian Name');?></small></strong></td>
        <td><?php echo !empty($post['Contact']['fathername']) ? h($post['Contact']['fathername']) : __('N/A');?></td>
    </tr>
    <tr>
        <td><strong><small class="text-danger"><?php echo __('Address');?></small></strong></td>
        <td><?php echo !empty($post['Contact']['address']) ? h($post['Contact']['address']) : __('N/A');?></td>
        <td><strong><small class="text-danger"><?php echo __('Location / Area');?></small></strong></td>
        <td><?php echo !empty($post['Contact']['address_hierarchy_path']) ? h($post['Contact']['address_hierarchy_path']) : (!empty($post['Contact']['district']) ? h($post['Contact']['district']).(!empty($post['Contact']['state']) ? ', '.h($post['Contact']['state']) : '') : __('N/A'));?></td>
    </tr>
    <tr>
        <td><strong><small class="text-danger"><?php echo __('Category');?></small></strong></td>
        <td><?php echo !empty($post['Category']['name']) ? h($post['Category']['name']) : __('N/A');?></td>
        <td><strong><small class="text-danger"><?php echo __('Gender');?></small></strong></td>
        <td><?php echo !empty($post['Contact']['gender']) ? h($post['Contact']['gender']) : __('N/A');?></td>
    </tr>
    <tr>
        <td><strong><small class="text-danger"><?php echo __('CNIC');?></small></strong></td>
        <td><?php echo !empty($post['Contact']['pincode']) ? h($post['Contact']['pincode']) : __('N/A');?></td>
        <td><strong><small class="text-danger"><?php echo __('Email');?></small></strong></td>
        <td><?php echo !empty($post['Contact']['email']) ? h($post['Contact']['email']) : __('N/A');?></td>
    </tr>
    <tr>
        <td><strong><small class="text-danger"><?php echo __('Cell phone');?></small></strong></td>
        <td><?php echo !empty($post['Contact']['mobile']) ? h($post['Contact']['mobile']) : __('N/A');?></td>
        <td><strong><small class="text-danger"><?php echo __('Mobile SMS');?></small></strong></td>
        <td><?php echo !empty($post['Contact']['phone']) ? h($post['Contact']['phone']) : __('N/A');?></td>
    </tr>
    <tr>
        <td><strong><small class="text-danger"><?php echo __('Profile Photo');?></small></strong></td>
        <td><?php if(!empty($post['Contact']['photo'])){ echo $this->Html->image($profileImages['thumb'],array('style'=>'max-height:90px;max-width:120px;')); } else { echo __('N/A'); }?></td>
        <td><strong><small class="text-danger"><?php echo __('ID Front (CNIC Front)');?></small></strong></td>
        <td><?php if(!empty($post['Contact']['id_front'])){ echo $this->Html->image($idFrontImages['thumb'],array('style'=>'max-height:90px;max-width:120px;')); } else { echo __('N/A'); }?></td>
    </tr>
    <tr>
        <td><strong><small class="text-danger"><?php echo __('ID Back (CNIC Back)');?></small></strong></td>
        <td><?php if(!empty($post['Contact']['id_back'])){ echo $this->Html->image($idBackImages['thumb'],array('style'=>'max-height:90px;max-width:120px;')); } else { echo __('N/A'); }?></td>
        <td><strong><small class="text-danger"><?php echo __('Gallery / Side Photo');?></small></strong></td>
        <td><?php if(!empty($post['Contact']['side_photo'])){ echo $this->Html->image($sidePhotoImages['thumb'],array('style'=>'max-height:90px;max-width:120px;')); } else { echo __('N/A'); }?></td>
    </tr>
</table>
<script type="text/javascript">
setTimeout(function(){if (typeof(window.print) != 'undefined') {
    window.print();
}}, 300);
</script>
