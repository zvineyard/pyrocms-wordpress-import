<section class="title">
	<h4><?php echo lang('wp:item_list'); ?></h4>
</section>

<section class="item">
	<?php echo form_open('admin/wp/delete');?>
	
	<?php if (!empty($items)): ?>
	
		<table>
			<thead>
				<tr>
					<th><?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all'));?></th>
					<th><?php echo lang('wp:name'); ?></th>
					<th><?php echo lang('wp:slug'); ?></th>
					<th></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="5">
						<div class="inner"><?php $this->load->view('admin/partials/pagination'); ?></div>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach( $items as $item ): ?>
				<tr>
					<td><?php echo form_checkbox('action_to[]', $item->id); ?></td>
					<td><?php echo $item->name; ?></td>
					<td><a href="<?php echo rtrim(site_url(), '/').'/wp'; ?>">
						<?php echo rtrim(site_url(), '/').'/wp'; ?></a></td>
					<td class="actions">
						<?php echo
						anchor('sample', lang('wp:view'), 'class="button" target="_blank"').' '.
						anchor('admin/sample/edit/'.$item->id, lang('wp:edit'), 'class="button"').' '.
						anchor('admin/sample/delete/'.$item->id, 	lang('wp:delete'), array('class'=>'button')); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
		<div class="table_action_buttons">
			<?php $this->load->view('admin/partials/buttons', array('buttons' => array('delete'))); ?>
		</div>
		
	<?php else: ?>
		<div class="no_data"><?php echo lang('wp:no_items'); ?></div>
	<?php endif;?>
	
	<?php echo form_close(); ?>
</section>