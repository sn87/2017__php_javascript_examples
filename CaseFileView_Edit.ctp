<script type="text/javascript" >
$(function() {
	$( ".datepicker_ui").datepicker({ changeMonth: true, changeYear:true, yearRange : 'c-20:c+20',dateFormat: "<?php echo Configure::read('DateFormatDtPick'); ?>" });
});
	
<?php if ($pctFields == 0) { ?>


function startClock(){
		// Hide a special element when document loads
		document.getElementById("pct_span").style.display = "none";
		// Show a hide/show button when document loads
		//document.getElementById("pct_button").style.display = "none";
		document.getElementById("pct_button").style.display = "block";
	
		}
		if(window.addEventListener){
   		 	window.addEventListener('load',startClock,false); //W3C
		}
		else{
			window.attachEvent('onload',startClock); //IE
		}
	

<?php }else{ ?>

function startClock(){

		// Show a hide/show button when document loads		
		document.getElementById("pct_button").style.display = "block";
		document.getElementById("pct_button").innerHTML = "Hide and clear PCT fields";
	
		}
		if(window.addEventListener){
   		 	window.addEventListener('load',startClock,false); //W3C
		}
		else{
			window.attachEvent('onload',startClock); //IE
		}



<?php } ?>
 </script>






<div class="page form">
<!--h2><?php echo __('Edit %s', __('Case File')); ?></h2-->

<?php $datepicker_format="(yyyy-mm-dd)"; ?>
<?php echo $this->Form->create('CaseFile');?>
	<fieldset>
 		<legend><?php echo __('Edit %s', __('Case File')); ?></legend>
	
		<?php	
		// load message for empty select-fields from app/config/configs.php
		$empty = Configure::read('EmptyMsg');
		
		// Set view option for pct-fields
		// Show them if at least one of them is not empty
		$pctDisplay = 'none';
		if ($pctFields == 1) $pctDisplay = 'block';
		
		echo $this->Form->input('id', array('type'=>'hidden'));			
		echo $this->Form->input('CaseFile.id', array('type'=>'hidden'));		
		echo $this->Form->input('CaseFile.kind_id', array('empty'=>$empty, 'disabled'=>true));
		echo $this->Form->input('CaseFile.patent_office_id', array('empty'=>$empty, 'disabled'=>true));		
		echo $this->Form->input('CaseFile.filenumber',  array('disabled'=>true));			
		
		if ($params['show']['title']) {			
					
		echo $this->Form->input('CaseFile.title_trans.eng', array(
				'empty'=>'Choose an option',
				'label'=>'Title of invention (EN)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['title']['eng']) ? $this->Form->data['CaseFile']['title']['eng'] : ""
				));
		
		echo $this->Form->input('CaseFile.title_trans.chi', array(
				'empty'=>'Choose an option',
				'label'=>'Title of invention (CN)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['title']['chi']) ? $this->Form->data['CaseFile']['title']['chi'] : ""
				));
		echo $this->Form->input('CaseFile.title_trans.deu', array(
				'empty'=>'Choose an option',
				'label'=>'Title of invention (DE)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['title']['deu']) ? $this->Form->data['CaseFile']['title']['deu'] : ""
		));		
		echo $this->Form->input('CaseFile.title_trans.fra', array(
				'empty'=>'Choose an option',
				'label'=>'Title of invention (FR)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['title']['fra']) ? $this->Form->data['CaseFile']['title']['fra'] : ""
				));
		echo $this->Form->input('CaseFile.title_trans.esp', array(
				'empty'=>'Choose an option',
				'label'=>'Title of invention (ES)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['title']['esp']) ? $this->Form->data['CaseFile']['title']['esp'] : ""
				));
		echo $this->Form->input('CaseFile.title_trans.ita', array(
				'empty'=>'Choose an option',
				'label'=>'Title of invention (IT)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['title']['ita']) ? $this->Form->data['CaseFile']['title']['ita'] : ""
				));
		}
			

		if ($params['show']['representation']) {
		echo $this->Form->input('CaseFile.representation',array(
				'label' => 'Representation (EN)',
				'style'=>'text-transform:uppercase',				
				'value'=>isset($this->Form->data['CaseFile']['representation']['eng']) ? $this->Form->data['CaseFile']['representation']['eng'] : ""
				));
		echo $this->Form->input('CaseFile.representation_trans.chi',array(
				'label' => 'Representation (CN)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['representation']['chi']) ? $this->Form->data['CaseFile']['representation']['chi'] : ""
				));
		echo $this->Form->input('CaseFile.representation_trans.deu',array(
				'label' => 'Representation (DE)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['representation']['deu']) ? $this->Form->data['CaseFile']['representation']['deu'] : ""
				));
		echo $this->Form->input('CaseFile.representation_trans.fra',array(
				'label' => 'Representation (FR)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['representation']['fra']) ? $this->Form->data['CaseFile']['representation']['fra'] : ""
		));
		echo $this->Form->input('CaseFile.representation_trans.esp',array(
				'label' => 'Representation (ES)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['representation']['esp']) ? $this->Form->data['CaseFile']['representation']['esp'] : ""
		));
		echo $this->Form->input('CaseFile.representation_trans.ita',array(
				'label' => 'Representation (IT)',
				'style'=>'text-transform:uppercase',
				'value'=>isset($this->Form->data['CaseFile']['representation']['ita']) ? $this->Form->data['CaseFile']['representation']['ita'] : ""
		));
		}

		$number = intval($params['show']['priority_count']);
		for($i=0; $i<($number); $i++) { //load value from controller, which increases. show as many priortity as needed
				$nr = $i;
				$nr++;
				echo $this->Form->input('CaseFilePriority.'.$i.'.id', array('type'=>'hidden'));		
				echo $this->Form->input('CaseFilePriority.'.$i.'.date', array('type'=>'text', 'class'=>'datepicker_ui', 'empty'=>$empty, 'dateFormat'=>'DMY', 'label'=>'Priority Date '.($nr).' '.$datepicker_format));
				echo $this->Form->input('CaseFilePriority.'.$i.'.number', array('label'=>'Priority Number '.($nr)));
				echo $this->Form->input('CaseFilePriority.'.$i.'.country_id', array('empty'=>$empty, 'label'=>'Priority Country '.($nr)));
				echo $this->Form->button('Delete Priority '.($nr), array('type'=>'submit', 'name'=>'del_priority', 'value'=>$i, 'onclick'=>"return confirm('Are you sure you want to delete Priority ".h(($nr))."?')"));
				echo '<br/>';
				echo '<br/>';
		}	
		
		if ($params['show']['priority']) {
			echo $this->Form->button('Add Priority', array('type'=>'submit', 'name'=>'add_priority', 'value'=>'add_priority', 'onclick'=>"return confirm('Are you sure you want to add another Priority?')"));
			echo '<br/>';
			echo '<br/>';
		}		
		
		if ($params['show']['pct_application_date']==TRUE) {
		echo $this->Form->input('CaseFile.pct_application_date', array(
			'label'=>'(PCT-)Application Date',
			'empty' => $empty,
			'class' => 'datepicker_ui',
			'type' => 'text'
			));
		}
		if ($params['show']['application_number']==TRUE) {
		echo $this->Form->input('CaseFile.application_number');
		}
		
		
				//PCT START
		if ($params['show']['pct'] == TRUE) {
			echo $this->Form->button('Show PCT Fields', array('type'=>'button', 'name'=>'show_pct', 'value'=>'show_pct', 'style'=>'display:none;', 'id'=>'pct_button', 'onclick'=>'divHideClearShow(\'PCT\', \'pct_button\',\'pct_span\',null)'));
			echo '<br/>';
			echo '<br/>';
		}
		echo "<span id='pct_span' style='display:".$pctDisplay.";'>";
			if ($params['show']['nat_reg_entering_date']==TRUE) {
				echo $this->Form->input('CaseFile.nat_reg_entering_date', array(
						'type'=>'text',
						'class'=>'datepicker_ui',
						'label'=>'National/Regional Entering Date '.$datepicker_format,
						'empty' => $empty));
			}
			if ($params['show']['pct_application_number']==TRUE) {
			echo $this->Form->input('CaseFile.pct_application_number', array(
				'label'=>'PCT Application Number'));
			}
			if ($params['show']['pct_publication_number']==TRUE) {
			echo $this->Form->input('CaseFile.pct_publication_number', array(
				'label'=>'PCT Publication Number'));
			}
			if ($params['show']['pct_publication_date']==TRUE) {
			echo $this->Form->input('CaseFile.pct_publication_date', array(
				'type'=>'text',
				'class'=>'datepicker_ui',
				'label'=>'PCT Publication Date '.$datepicker_format,
				'empty' => $empty));
			}
			if ($params['show']['pct_receiving_office']==TRUE) {
			echo $this->Form->input('CaseFile.pct_receiving_office', array(
				'label'=>'PCT Receiving Office',
				'type' => 'select',
				'options' => $patentOffices, 			    
   				'empty' =>$empty));
			}
			if ($params['show']['pct_international_search_authority']==TRUE) {
			echo $this->Form->input('CaseFile.pct_international_search_authority', array(
				'label'=>'PCT International Search Authority',
				'type' => 'select',
				'options' => $patentOffices,
				'empty' =>$empty));
			}
			if ($params['show']['pct_international_examination_authority']==TRUE) {
			echo $this->Form->input('CaseFile.pct_international_examination_authority', array(
				'label'=>'PCT International Examination Authority',
				'type' => 'select',
				'options' => $patentOffices,
				'empty' =>$empty));					
			}
			if ($params['show']['pct_date_int_search_rep']==TRUE) {
			echo $this->Form->input('CaseFile.pct_date_int_search_rep', array(
				'type'=>'text',
				'class'=>'datepicker_ui',
				'label'=>'PCT Date International Search Report '.$datepicker_format,
				'empty' => $empty));
			}
			if ($params['show']['pct_date_written_opinion_int_search_rep']==TRUE) {
			echo $this->Form->input('CaseFile.pct_date_written_opinion_int_search_rep', array(
				'type'=>'text',
				'class'=>'datepicker_ui',
				'label'=>'PCT Date Written Opinion of International Search Report '.$datepicker_format,
				'empty' => $empty));
			}
			if ($params['show']['pct_date_int_pre_exam_report']==TRUE) {
			echo $this->Form->input('CaseFile.pct_date_int_pre_exam_report', array(
				'type'=>'text',
				'class'=>'datepicker_ui',
				'label'=>'PCT Date International Preliminary Examination Report '.$datepicker_format,
				'empty' => $empty));
			}
		echo "</span>";
		// PCT END		
		
		
		if ($params['show']['grant_reg_number']==TRUE) {
		echo $this->Form->input('CaseFile.grant_reg_number', array(
			'label'=>'Grant/Registration Number'));
		}
		if ($params['show']['grant_reg_date']==TRUE) {
		echo $this->Form->input('CaseFile.grant_reg_date', array(
			'type'=>'text',
			'class'=>'datepicker_ui',
			'label'=>'Grant/Registration Date '.$datepicker_format,
			'empty' => $empty));
		}
		if ($params['show']['grant_reg_pub_date']==TRUE) {
		echo $this->Form->input('CaseFile.grant_reg_pub_date', array(
			'type'=>'text',
			'class'=>'datepicker_ui',
			'label'=>'Grant/Registration Publication Date '.$datepicker_format,
			'empty' => $empty));
		}
		if ($params['show']['electr_filling_number']==TRUE) {
		echo $this->Form->input('CaseFile.electr_filling_number', array(
			'label'=>'Electronic Filling Number'));
		}
		if ($params['show']['publication_number']==TRUE) {
		echo $this->Form->input('CaseFile.publication_number');
		}
		if ($params['show']['publication_date_app_trans']==TRUE) {
		echo $this->Form->input('CaseFile.publication_date_app_trans', array(
			'type'=>'text',
			'class'=>'datepicker_ui',
			'label'=>'Publication Date of Application/Translation of Application '.$datepicker_format,
			'empty' => $empty));
		}
		if ($params['show']['publication_requested']==TRUE) {
		echo $this->Form->input('CaseFile.publication_requested');
		}
		if ($params['show']['classes_for_products_services']==TRUE) {
		echo $this->Form->input('CaseFile.classes_for_products_services', array(
			'label'=>'Classes for products and services'));
		}
		if ($params['show']['main_class_for_products_services']==TRUE) {
		echo $this->Form->input('CaseFile.main_class_for_products_services', array(
			'label'=>'Main Class for products and services'));
		}
		if ($params['show']['class']==TRUE) {
			echo $this->Form->input('CaseFile.class', array(
					'label'=>'Class'));
		}

		if ($params['show']['status_id']==TRUE) {
		echo $this->Form->input('CaseFile.status_id', array(
			'empty' => $empty));
		}

		if ($params['show']['note']==TRUE) {
		echo "<div class=\"input text\">";
		echo $this->Form->label('CaseFile.note', 'Notes');
		echo $this->Form->textarea('CaseFile.note');
		echo "</div>";
		}


	?>
	</fieldset>
<div class="submit">
<span class="submit_cancel">

	<?php echo $this->Form->submit('Submit', array('name' => 'cf_submit', 'div'=>'false'));?>
	<?php echo $this->Form->submit('Cancel', array('name' => 'cf_cancel', 'class'=>'cancel_btn', 'div'=>'false'));?>
</span>
</div>
</div>

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List %s', __('Case Files')), array('action' => 'unlock', $id));?></li>
	</ul>
	<br/>

</div>
	<p id="countdowntext">Time left until Case File can be edited by other users again:
	<br/>
		<span id="countdown"></span>
	</p>
	
<script type="text/javascript" >
<?php if ($editTimeSec > 0) {?>
$('#countdown').timeTo(<?php echo $editTimeSec;?>, 
						{fontSize: 16, fontFamiliy: "'lucida grande',verdana,helvetica,arial,sans-serif"}, 
						function(){  
							$('#countdowntext').css("color", "red"); 
							$('#countdowntext').html('Time has run out. <br/>But you can still save the Case File, if it has not been saved by another user in the meantime');
						} 
					);
<?php }else{?>
$('#countdowntext').css("color", "red"); 
$('#countdowntext').html('Time has run out. <br/>But you can still save the Case File, if it has not been saved by another user in the meantime');
<?php }?>
</script>
