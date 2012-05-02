<section class="title">
	<!-- We'll use $this->method to switch between sample.create & sample.edit -->
	<h4><?php echo lang('wp:'.$this->method); ?></h4>
</section>

<section class="item">

	<?php echo form_open($this->uri->uri_string().'/update_duplicates'); ?>
		
		<p>It looks like your WordPress data has duplicate post titles. Please rename the titles you see below.</p>      
        <?php
		foreach($items as $key => $item) {
		
			$pattern = '/'.$item.'/';
			$replacement = $item.'-'.rand(1,999);
			echo preg_replace($pattern, $replacement, $string);
			
			echo form_label('Duplicate Value ', $key."_dup");
			$data = array(
				'name' => $key."_dup",
				'id' => $key."_dup",
				'value' => $item,
				'style' => 'width:300px;'
			);
			echo form_input($data);
		}
		?>
		
		<div class="buttons">
			<?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'cancel') )); ?>
		</div>
		
	<?php echo form_close(); ?>

</section>