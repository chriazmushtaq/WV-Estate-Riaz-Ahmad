<?php
echo $this->Html->script('http://maps.google.com/maps/api/js?key='.$mapKey);
$photoUrl=$this->Html->url(array('controller'=>'img'));
$propertyName=$post['Property']['name'];
$agencyName=$post['Project']['name'];
$locationName=$post['Location']['name'];
$contractName=$post['Contract']['name'];
$propertyPrice=$currency.' '.$post['Property']['price'];
$propertyStatus=$post['Property']['status'];
$propertyBedroom=$post['Property']['bedroom'];
$propertyBathroom=$post['Property']['bathroom'];
$propertyArea=$post['Property']['area'].' '.$post['Unit']['name'];
$propertyView=$post['Property']['views'];
$propertyRating=$post['Property']['rating'];
$propertyDescription=$post['Property']['description'];
$propertyAgent=$post['User']['name'];
$feature1=array();
$floorPlan=array();

$url='nia.png';
foreach($post['PropertiesPhoto'] as $post1):
  if(empty($post1['photo']))continue;
  $url=$post1['dir'].'/'.$post1['photo'];
  if(!empty($post1['home']) && $post1['home']=='Yes')break;
  unset($post1);endforeach;
  
   foreach($feature as $fpost):
      $feature1[]=$fpost['Feature']['name'];
     unset($fpost);endforeach;
     $feature=implode(",",$feature1);
     
     foreach($post['PropertiesFloorplan'] as $fpost):$photo=$fpost['dir'].'_thumb/'.$fpost['photo'];
     $floorPlan[]=$this->Html->link($this->Html->image($photo),array('controller'=>'img','action'=>$photo=$fpost['dir'],$fpost['photo']),array('class'=>'image-popup','escape'=>false)).'&nbsp;&nbsp;';
     endforeach;unset($fpost);
     
     
   $propertyPhoto=$this->Html->image($url,array('class'=>'img-responsive','alt' => $propertyName,'height'=>'300'));  
    
     
$tempValue=eval('return "' . addslashes($template) . '";');
echo$tempValue;
?>       
<script type="text/javascript">
setTimeout(function(){if (typeof(window.print) != 'undefined') {
    window.print();
    window.close();
}}, 1500);
</script>
