<div class="container">
    <div class="panel panel-custom mrg">
	<div class="panel-heading"><?php echo __('Property');?> <?php echo __('Details');?><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button></div>            
        <div class="panel-body"><?php echo $this->Session->flash();?>
            <div class="table-responsive">
			<table class="table table-bordered">
			    <tr>
				<td colspan="2"><strong><big class="text-danger"><?php echo __('Office ID');?></big></strong>: <strong><big class="text-info"><?php echo h(!empty($post['Property']['uniq_id']) ? $post['Property']['uniq_id'] : '#'.$post['Property']['id']);?></big></strong></td>				
			    </tr>
			    <tr>
				<td><strong><small class="text-danger"><?php echo __('Project');?></small></strong>: <?php echo h(!empty($post['Project']['name']) ? $post['Project']['name'] : __('Not Specified'));?></td>
				<td><strong><small class="text-danger"><?php echo __("Property's Agent");?></small></strong>: <?php echo h(!empty($post['User']['name']) ? $post['User']['name'] : __('White Villas'));?></td>
			    </tr>
			    <tr>
				<td><strong><small class="text-danger"><?php echo __('Title');?></small></strong>: <?php echo h($post['Property']['name']);?></td>				
				<td><strong><small class="text-danger"><?php echo __('Type');?></small></strong>: <?php echo h(!empty($post['Type']['name']) ? $post['Type']['name'] : __('Not Specified'));?></td>
			    </tr>
			    <tr>
				<td><strong><small class="text-danger"><?php echo __('Latitude');?></small></strong>: <?php if(!empty($post['Property']['latitude'])) echo h($post['Property']['latitude']); else echo __('Not specified');?></td>				
				<td><strong><small class="text-danger"><?php echo __('Longitude');?></small></strong>: <?php if(!empty($post['Property']['longitude'])) echo h($post['Property']['longitude']); else echo __('Not specified');?></td>
			    </tr>
			    <tr>
				<td><strong><small class="text-danger"><?php echo __('Price');?></small></strong>: <?php echo h($post['Property']['price']);?></td>
				<td><strong><small class="text-danger"><?php echo __('Area');?></small></strong>: <?php echo h($post['Property']['area'].' '.(!empty($post['Unit']['name']) ? $post['Unit']['name'] : ''));?></td>
			    </tr>
			    <tr>
				<td><strong><small class="text-danger"><?php echo __('Location');?></small></strong>: <?php echo h(!empty($post['Location']['name']) ? $post['Location']['name'] : '');?></td>
				<td><strong><small class="text-danger"><?php echo __('Address');?></small></strong>: <?php echo h($post['Property']['address']);?></td>
			    </tr>
			    <?php if(!empty($post['Property']['address_hierarchy_path'])){ ?>
			    <tr>
				<td colspan="2"><strong><small class="text-danger"><?php echo __('Address Hierarchy');?></small></strong>: <?php echo h($post['Property']['address_hierarchy_path']);?></td>
			    </tr>
			    <?php } ?>
			    <tr>
				<td><strong><small class="text-danger"><?php echo __('Number of floors');?></small></strong>: <?php if(!empty($post['Property']['floor'])) echo h($post['Property']['floor']); else echo __('Not specified');?></td>
				<td><strong><small class="text-danger"><?php echo __('Status');?></small></strong>: <?php echo h($post['Property']['status']);?></td>
			    </tr>
			    <tr>
				<td><strong><small class="text-danger"><?php echo __('Number of bedrooms');?></small></strong>: <?php if(!empty($post['Property']['bedroom'])) echo h($post['Property']['bedroom']); else echo __('Not specified');?></td>
				<td><strong><small class="text-danger"><?php echo __('Number of bathrooms');?></small></strong>: <?php if(!empty($post['Property']['bathroom'])) echo h($post['Property']['bathroom']); else echo __('Not specified');?></td>
			    </tr>
			    <tr>
				<td><strong><small class="text-danger"><?php echo __('Available For');?></small></strong>: <?php echo h(!empty($post['Contract']['name']) ? $post['Contract']['name'] : '');?></td>
				<td><strong><small class="text-danger"><?php echo __('Embed Video');?></small></strong>: <?php echo h($post['Property']['video']);?></td>
			    </tr>
			    <tr>
				<td><strong><small class="text-danger"><?php echo __('Allow user rating');?></small></strong>: <?php if(!empty($post['Property']['allow_rating'])) echo __("Yes"); else echo __('No');?></td>
				<td><strong><small class="text-danger"><?php echo __('Allow user comment');?></small></strong>: <?php if(!empty($post['Property']['allow_comment'])) echo __("Yes"); else echo __('No');?></td>
			    </tr>
			    <tr>
				<td colspan="2"><strong><small class="text-danger"><?php echo __('Description');?></small></strong>: <?php echo $post['Property']['description'];?></td>
			    </tr>
			</table>
		    </div>
        </div>
    </div>
</div>
