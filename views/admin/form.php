<section class="title">
	<!-- We'll use $this->method to switch between sample.create & sample.edit -->
	<h4><?php echo lang('wp:'.$this->method); ?></h4>
</section>

<section class="item">

	<?php echo form_open_multipart($this->uri->uri_string().'/upload', 'class="uplaod"'); ?>
		
		<div class="form_inputs">
	
		<ul>
			<li>
            	<p>Please choose a WordPress XML export file to use for importing content.</p>
				<div class="input"><input type="file" name="userfile" size="20" /></div>
			</li>
		</ul>
		
		</div>
		
		<div class="buttons">
			<?php $this->load->view('admin/partials/buttons', array('buttons' => array('upload', 'cancel') )); ?>
		</div>
		
	<?php echo form_close(); ?>

</section>