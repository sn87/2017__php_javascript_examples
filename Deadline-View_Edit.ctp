<script type="text/javascript" >
$(function() {
	$( ".datepicker_ui").datepicker({ changeMonth: true, changeYear:true, yearRange : 'c-20:c+20',dateFormat: "<?php echo Configure::read('DateFormatDtPick'); ?>" });
});

</script>
	
<div class="page form">
<h2><?php echo __('Edit %s', __('Deadline')); ?></h2>


<?php echo $this->Form->create('Deadline');?>
	<fieldset>
 		<legend><?php echo __('Edit %s', __('Deadline')); ?></legend>
	<?php
		$datepicker_format = Configure::read('DateFormatLabel');
		$empty = Configure::read('EmptyMsg');		
		echo $this->Form->input('id', array('type'=>'hidden'));
		echo $this->Form->input('case_file_id', array('type'=>'hidden'));
		echo $this->Form->input('case_file_id', array('empty'=>$empty, 'disabled'=>true));

		echo $this->Form->input('deadline', array('class'=>'datepicker_ui', 'type'=>'text', 'label'=>'Deadline '.$datepicker_format));    

		echo $this->Form->input('manual_int_deadline', array(
			'type'=>'checkbox', 'hiddenField' => false, 'label'=>'Deactivate auto calculation of Internal deadline'
		));

		echo $this->Form->input('deadline_type_id');
		echo $this->Form->input('deadline_reason_id', array('empty'=>$empty));				
		echo $this->Form->input('note');
						
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
</div>

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List %s', __('Deadlines')), array('action' => 'index'));?></li>
		<li><?php echo $this->Html->link(__('List %s', __('Case Files')), array('controller' => 'case_files', 'action' => 'index')); ?> </li>
	</ul>
</div>
