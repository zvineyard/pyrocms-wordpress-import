<section class="title">
	<!-- We'll use $this->method to switch between sample.create & sample.edit -->
	<h4><?php echo lang('wp:'.$this->method); ?></h4>
</section>

<section class="item">

	<?php echo form_open(base_url().'admin/'.$this->module_details['slug'].'/parse/'.$this->uri->segment(4)); ?>
		
		<p>It looks like your WordPress data has duplicate post titles. Please rename the titles you see below in your XML data.</p>      
        <ul>
		<?php
		foreach($items as $key => $item) {
			echo '<li>'.$item.'</li>';
		}
		?>
        </ul>
		<hr />
		<div class="buttons">
            <button type="submit" name="btnAction" value="save" class="btn orange"><span>Try Again</span></button>
			<a href="<?php echo base_url().'admin/'.$this->module_details['slug'];?>" class="btn gray cancel">Cancel</a>
		</div>
		
	<?php echo form_close(); ?>

</section>