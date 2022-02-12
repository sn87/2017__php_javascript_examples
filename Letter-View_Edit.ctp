<script type="text/javascript" >
$( document ).ready(function() {
	$(".toggle_span").hide();
	$(".toggle_button").click(function() {
		  $( ".toggle_span" ).toggle();
		  if ($(".toggle_button").text().indexOf("Show") >= 0) {
		  	  $(".toggle_button").text($(".toggle_button").text().replace("Show", "Hide"));
		  }else if($(".toggle_button").text().indexOf("Hide") >= 0) {
			  $(".toggle_button").text($(".toggle_button").text().replace("Hide", "Show"));
		  }
	});
});

	
</script>
<div class="page form">
<h2><?php echo __('Edit %s', __('Letter')); ?></h2>

<?php echo $this->Form->create('Letter');?>
	<fieldset>
 		<legend><?php echo __('Edit %s', __('Letter')); ?></legend>
		<?php
		$empty = Configure::read('EmptyMsg');
		$cntTypes = 0;
		
		echo $this->Form->input('Letter.id', array('type'=>'hidden'));
		echo $this->Form->input('Letter.name');
		echo $this->Form->input('Letter.description');
		//echo $this->Form->input('Letter.template');
		?>
	</fieldset>
	<fieldset class="ptypes">
		<legend>Persontype of Addressee or Applicant to be selected</legend>
		<p>If Persontype (1. Option) is not available, Persontype (2. Option) will be used, etc.</p>  	
	    	<?php 
	    	for($i=5; $i<=9; $i++) {
	    		$j = $i-5;
	    		$nr = $j;
	    		$nr++;
	    		if (isset($letParamKeys['persontype'][$j])) {
	    			// load values from database to select fields
	    			$pKey = $letParamKeys['persontype'][$j];
	    			echo $this->Form->input('LetterParam.'.$pKey.'.id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$pKey.'.priority', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$pKey.'.letter_element_type_id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$pKey.'.persontype_id', array('label'=>'Persontype ('.$nr.'. Option)', 'empty'=>$empty, 'class'=>'confirm_sel'));
	    		}else{
	    			// create hidden and disabled select fields
	    			if (sizeof($freeKeys) > 0) {
	    				$k = array_shift($freeKeys);
		    			echo "<span class='hide_ptypes hide_x'>";
		    			echo $this->Form->input('LetterParam.'.$k.'.priority', array('type'=>'hidden', 'value'=> $nr));
		    			echo $this->Form->input('LetterParam.'.$k.'.letter_element_type_id', array('type'=>'hidden', 'value'=>23));
		    			echo $this->Form->input('LetterParam.'.$k.'.persontype_id', array('label'=>'Persontype ('.$nr.'. Option)', 'empty'=>$empty, 'class'=>'confirm_sel'));
		    			echo "</span>";
	    			}
	    		}
	    	}
	    	echo $this->Form->input('Show Additional Persontypes', array('type'=>'button', 'class'=>'add_x', 'div'=>array('class'=>'add button'), 'label'=>false));	    	
			?>
	</fieldset>	
	<fieldset class="comp_ptypes">
		<legend>Persontype of company addressees to be selected</legend>
		<p>Persontype of company, that will be used in co-Adresses. <br/>
		For company address without co-adressee please use persontype field above.</p>  	
			<?php 
	    	for($i=26; $i<=30; $i++) {
	    		$j = $i-26;
	    		$nr = $j;
	    		$nr++;
	    		if (isset($letParamKeys['comp_persontype'][$j])) {
	    			// load values from database to select fields
	    			$ccPKey = $letParamKeys['comp_persontype'][$j];
	    			echo $this->Form->input('LetterParam.'.$ccPKey.'.id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$ccPKey.'.priority', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$ccPKey.'.letter_element_type_id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$ccPKey.'.persontype_id', array('label'=>'Company Persontype ('.$nr.'. Option)', 'empty'=>$empty, 'class'=>'confirm_sel'));
	    		}else{
	    			// create hidden and disabled select fields
	    			if (sizeof($freeKeys) > 0) {
	    				$k = array_shift($freeKeys);
		    			echo "<span class='hide_comp_ptypes hide_x'>";
		    			echo $this->Form->input('LetterParam.'.$k.'.priority', array('type'=>'hidden', 'value'=> $nr));
		    			echo $this->Form->input('LetterParam.'.$k.'.letter_element_type_id', array('type'=>'hidden', 'value'=>52));
		    			echo $this->Form->input('LetterParam.'.$k.'.persontype_id', array('label'=>'Company Persontype ('.$nr.'. Option)', 'empty'=>$empty, 'class'=>'confirm_sel'));
		    			echo "</span>";
	    			}
	    		}
	    	}
	    	echo $this->Form->input('Show Additional Company Persontypes', array('type'=>'button', 'class'=>'add_x', 'div'=>array('class'=>'add button'), 'label'=>false));	    	
			?>
	</fieldset>	
	<fieldset class="cc_ptypes">
		<legend>Persontype of cc-addressees to be selected</legend>
		<p>For each selected persontypes all assigned persons emails will be added to the cc field</p>	
	    	<?php 
	    	for($i=16; $i<=20; $i++) {
	    		$j = $i-16;
	    		$nr = $j;
	    		$nr++;
	    		if (isset($letParamKeys['cc_persontype'][$j])) {
	    			// load values from database to select fields
	    			$ccPKey = $letParamKeys['cc_persontype'][$j];
	    			echo $this->Form->input('LetterParam.'.$ccPKey.'.id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$ccPKey.'.priority', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$ccPKey.'.letter_element_type_id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$ccPKey.'.persontype_id', array('label'=>'CC-Persontype ('.$nr.'. Option)', 'empty'=>$empty, 'class'=>'confirm_sel'));
	    		}else{
	    			// create hidden and disabled select fields
	    			if (sizeof($freeKeys) > 0) {
	    				$k = array_shift($freeKeys);
		    			echo "<span class='hide_cc_ptypes hide_x'>";
		    			echo $this->Form->input('LetterParam.'.$k.'.priority', array('type'=>'hidden', 'value'=> $nr));
		    			echo $this->Form->input('LetterParam.'.$k.'.letter_element_type_id', array('type'=>'hidden', 'value'=>44));
		    			echo $this->Form->input('LetterParam.'.$k.'.persontype_id', array('label'=>'CC-Persontype ('.$nr.'. Option)', 'empty'=>$empty, 'class'=>'confirm_sel'));
		    			echo "</span>";
	    			}
	    		}
	    	}
	    	echo $this->Form->input('Show Additional CC Persontypes', array('type'=>'button', 'class'=>'add_x', 'div'=>array('class'=>'add button'), 'label'=>false));	    	
			?>
	</fieldset>		
	<fieldset class="dlreasons">  
		<legend>Deadline Reasons of Main Deadline to be selected</legend>
		<p>If Deadline Reason (1. Option) is not available, Deadline Reason (2. Option) will be used, etc.</p>  	
	    	<?php 
	    	for($i=0; $i<=4; $i++) {
	    		$nr = $i;
	    		$nr++;
	    		if (isset($letParamKeys['deadline_reason'][$i])) {
	    			// load values from database to select fields
	    			$dKey = $letParamKeys['deadline_reason'][$i];
	    			echo $this->Form->input('LetterParam.'.$dKey.'.id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$dKey.'.priority', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$dKey.'.letter_element_type_id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$dKey.'.deadline_reason_id', array('label'=>'Deadline Reason ('.$nr.'. Option)', 'empty'=>$empty, 'class'=>'confirm_sel'));
	    		}else{
	    			// create hidden and disabled select fields
	    			if (sizeof($freeKeys) > 0) {
	    			$k = array_shift($freeKeys);
		    			echo "<span class='hide_x hide_dlreasons'>";
		    			echo $this->Form->input('LetterParam.'.$k.'.priority', array('type'=>'hidden', 'value'=> $nr));
		    			echo $this->Form->input('LetterParam.'.$k.'.letter_element_type_id', array('type'=>'hidden', 'value'=>24));
		    			echo $this->Form->input('LetterParam.'.$k.'.deadline_reason_id', array('class'=>'confirm_sel', 'label'=>'Deadline Reason ('.$nr.'. Option)', 'empty'=>$empty));
		    			echo "</span>";
	    			}
	    		}
	    	}

	    	echo $this->Form->input('Show Additional Deadline Reasons', array('type'=>'button','class'=>'add_x', 'div'=>array('class'=>'add button'), 'label'=>false));
	    	
			?>
	</fieldset>
	<fieldset class="dlrclasses">  
		<legend>Deadline Classes of Deadline Reason of Main Deadline to be selected</legend>
		<p>If Deadline Reason Class (1. Option) is not available, Deadline Reason Class (2. Option) will be used, etc.</p>  	
	    	<?php 
	    	for($i=21; $i<=25; $i++) {
	    		$j = $i-21;
	    		$nr = $j;
	    		$nr++;
	    		if (isset($letParamKeys['deadline_class'][$j])) {
	    			// load values from database to select fields
	    			$dcKey = $letParamKeys['deadline_class'][$j];
	    			echo $this->Form->input('LetterParam.'.$dcKey.'.id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$dcKey.'.priority', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$dcKey.'.letter_element_type_id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$dcKey.'.deadline_class_id', array('label'=>'Deadline Reason Class ('.$nr.'. Option)', 'empty'=>$empty, 'class'=>'confirm_sel'));
	    		}else{
	    			// create hidden and disabled select fields
	    			if (sizeof($freeKeys) > 0) {
	    			$k = array_shift($freeKeys);
		    			echo "<span class='hide_x hide_dlrclasses'>";
		    			echo $this->Form->input('LetterParam.'.$k.'.priority', array('type'=>'hidden', 'value'=> $nr));
		    			echo $this->Form->input('LetterParam.'.$k.'.letter_element_type_id', array('type'=>'hidden', 'value'=>51));
		    			echo $this->Form->input('LetterParam.'.$k.'.deadline_class_id', array('class'=>'confirm_sel', 'label'=>'Deadline Reason Class ('.$nr.'. Option)', 'empty'=>$empty));
		    			echo "</span>";
	    			}
	    		}
	    	}
			
	    	echo $this->Form->input('Show Additional Deadline Reason Classes', array('type'=>'button','class'=>'add_x', 'div'=>array('class'=>'add button'), 'label'=>false));
	    	
			?>
	</fieldset>
	<fieldset class="senddlrclasses">  
		<legend>Send Deadline Reasons of Main Deadline to be selected for sending date</legend>
		<p>If nothing is selcted, internal deadline of above selected Deadline Reason or Deadline Class will be used as sending date of email.<br/>
		If Deadline Reason (1. Option) is not available, Deadline Reason (2. Option) will be used, etc.</p> 	
	    	<?php 
	    	for($i=32; $i<=36; $i++) {
	    		$j = $i-32;
	    		$nr = $j;
	    		$nr++;
	    		if (isset($letParamKeys['send_deadline_reason'][$j])) {
	    			// load values from database to select fields
	    			$sdKey = $letParamKeys['send_deadline_reason'][$j];
	    			echo $this->Form->input('LetterParam.'.$sdKey.'.id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$sdKey.'.priority', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$sdKey.'.letter_element_type_id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$sdKey.'.deadline_reason_id', array('label'=>'Send Deadline Reason ('.$nr.'. Option)', 'empty'=>$empty, 'class'=>'confirm_sel'));
	    		}else{
	    			// create hidden and disabled select fields
	    			if (sizeof($freeKeys) > 0) {
	    			$k = array_shift($freeKeys);
		    			echo "<span class='hide_x hide_senddlrclasses'>";
		    			echo $this->Form->input('LetterParam.'.$k.'.priority', array('type'=>'hidden', 'value'=> $nr));
		    			echo $this->Form->input('LetterParam.'.$k.'.letter_element_type_id', array('type'=>'hidden', 'value'=>58));
		    			echo $this->Form->input('LetterParam.'.$k.'.deadline_reason_id', array('class'=>'confirm_sel', 'label'=>'Send Deadline Reason ('.$nr.'. Option)', 'empty'=>$empty));
		    			echo "</span>";
	    			}
	    		}
	    	}
			
	    	echo $this->Form->input('Show Additional Send Deadline Reasons', array('type'=>'button','class'=>'add_x', 'div'=>array('class'=>'add button'), 'label'=>false));
	    	
			?>
	</fieldset>	
	<fieldset>
		<legend>Custom Deadline</legend>
		<p>This field is for defining a custom deadline. You can define how many days and months before the main deadline the custom deadline will be</p>
			<?php 
			if (!empty($letParamKeys['cust_dl'])) {
				$cdKey = $letParamKeys['cust_dl'][0];								
				echo $this->Form->input('LetterParam.'.$cdKey.'.id', array('type'=>'hidden'));
				echo $this->Form->input('LetterParam.'.$cdKey.'.value_a', array('options'=>array_combine(range(1,24), range(1,24)), 'label'=>'Custom Deadline Months (Before Main Deadline)', 'empty'=>0));
				echo $this->Form->input('LetterParam.'.$cdKey.'.value_b', array('options'=>array_combine(range(1,31), range(1,31)), 'label'=>'Custom Deadline Days (Before Main Deadline)', 'empty'=>0)); 
				echo $this->Form->input('LetterParam.'.$cdKey.'.letter_element_type_id', array('type'=>'hidden', 'value'=>41));				
			}else{
				echo $this->Form->input('LetterParam.10.value_a', array('options'=>array_combine(range(1,24), range(1,24)), 'label'=>'Custom Deadline Months (Before Main Deadline)', 'empty'=>0));
				echo $this->Form->input('LetterParam.10.value_b', array('options'=>array_combine(range(1,31), range(1,31)), 'label'=>'Custom Deadline Days (Before Main Deadline)', 'empty'=>0));
				echo $this->Form->input('LetterParam.10.letter_element_type_id', array('type'=>'hidden', 'value'=>41));
			}
			?>		
	</fieldset>
	<fieldset>
		<legend>Letter Variables</legend>
		<p>Here you can define fields that can be filled for each letter instance seperatly<br />
		Each of these variables has to be defined in the template document too</p> 
		<span class="lv_wrap">
			<?php 
			if (!empty($letter['LetterVar'])) {
				$i = 0;
				foreach ($letter['LetterVar'] as $letterVar) {
					echo "<span>";
					echo $this->Form->input('LetterVar.'.$i.'.id', array('type'=>'hidden'));
					echo $this->Form->input('LetterVar.'.$i.'.name', array('label'=>'Letter Variable Name'));				
					echo '<div><a href="#" class="confirm_click remove_field">Remove Letter Variable Name</a></div>';
					$i++;
					echo "</span>";
				} 
				echo $this->Form->input('LetterVarCnt', array('type'=>'hidden', 'value'=>$i));
			}
			?>
		</span>
		<?php echo $this->Form->input('Add Letter Variable', array('type'=>'button', 'class'=>'add_lv', 'div'=>array('class'=>'add button'), 'label'=>false)); ?>
	</fieldset>
	
	<br/>
	<h2><?php echo __('Expert Options'); ?></h2>
	
	<fieldset>
  	<?php echo $this->Form->button('Show expert options', array('type'=>'button', 'class'=>'toggle_button')); ?>
	</fieldset>	
	
	<span class="toggle_span">
	<fieldset>
		<legend>Template and Language</legend>
		<?php 
		// With Select Letter
		echo $this->Form->input('Letter.is_mail', array('type'=>'hidden'));
		echo $this->Form->input('Letter.is_mail', array('type'=>'checkbox', 'hiddenField'=> false,'class'=>'mail_checkbox', 'label'=>'Mail', 'disabled'=>'disabled'));
		echo "<div class='input'>";
		echo "<label>Template</label>";
		if (isset($letter['Letter']['is_mail']) && $letter['Letter']['is_mail'] == 1) {
			echo $this->Form->input('Letter.template',array('options'=>$mailTmpls, 'label'=>false, 'div'=>false, 'id'=>'mail_tmpls', 'class'=>'confirm_sel'));
			echo $this->Form->input('Letter.template',array('options'=>$docTmpls, 'label'=>false, 'div'=>false, 'id'=>'doc_tmpls', 'class'=>'confirm_sel', 'style'=>'display:none;', 'disabled'=>'disabled'));
		}else{
			echo $this->Form->input('Letter.template',array('options'=>$docTmpls, 'label'=>false, 'div'=>false, 'id'=>'doc_tmpls', 'class'=>'confirm_sel'));
			echo $this->Form->input('Letter.template',array('options'=>$mailTmpls, 'label'=>false, 'div'=>false, 'id'=>'mail_tmpls', 'class'=>'confirm_sel', 'style'=>'display:none;', 'disabled'=>'disabled'));
		}
		echo "</div>";
		echo $this->Form->input('Letter.lang',array('options'=>array('en'=>'English', 'cn'=>'Chinese', 'de'=>'German'), 'label'=>'Language'));
		?>
	</fieldset>
	<fieldset>
		<legend>Automated Letter Elements</legend>
		<p>Here you can define fields that will be filled automatically</p>
		<?php  
		echo "<div class='rowboxes'>";
		foreach ($dynElemTypes as $elementType) {
			//var_dump($elementType);
			$typeAlias = $elementType['LetterElementType']['alias'];
			$checkOpts = array('type'=>'checkbox', 'class'=>'confirm_click input checkbox', 'hiddenField' => false, 'name' => 'data[LetterElementType][LetterElementType][]', 'label'=>$elementType['LetterElementType']['name'] , 'onChange'=>'showElem("'.$typeAlias.'")');
			if(in_array($elementType['LetterElementType']['id'],$selElemTypeIds)){
				$checkOpts['checked'] = true;
			}
			$checkOpts['value'] = $elementType['LetterElementType']['id'];
			echo $this->Form->input('LetterElementType.'.strval($cntTypes), $checkOpts);
			$cntTypes++;
		}
		echo "</div>";
		?>
	</fieldset>

	<fieldset>
		<legend>Deadline Reason Rule</legend>
		<p>You can select here what should happen, if a case file has several deadlines with the selected dealine reason ir deadline class above.<br/>
		Either the deadline closest to the present date, or farthest in the future can be selected<br/>
		The default is to take the deadline farthes in the future. This also applies is you set this value to empty</p>
			<?php
			$dlrROptions = array();
			if (!empty($elements['dlr_rule'])) {
				$dlrROptions = $elements['dlr_rule'];
				// unset, so that it does not occur in letter elements section below anymore
				unset($elements['dlr_rule']);
			}
			if (!empty($letParamKeys['dlr_rule'])) {				
				$dlrRuleKey = $letParamKeys['dlr_rule'][0];
				echo $this->Form->input('LetterParam.'.$dlrRuleKey.'.id', array('type'=>'hidden'));
				echo $this->Form->input('LetterParam.'.$dlrRuleKey.'.letter_element_id', array('options'=>$dlrROptions, 'label'=>'Deadline Reason Rule', 'empty'=>$empty));
				echo $this->Form->input('LetterParam.'.$dlrRuleKey.'.letter_element_type_id', array('type'=>'hidden', 'value'=>53));							
			}else{
				echo $this->Form->input('LetterParam.31.letter_element_id', array('options'=>$dlrROptions, 'label'=>'Deadline Reason Rule', 'empty'=>$empty));
				echo $this->Form->input('LetterParam.31.letter_element_type_id', array('type'=>'hidden', 'value'=>53));
			}
			?>
	</fieldset>	
	<fieldset class='attachments'>  
		<legend>Attached Letters</legend>
	    	<?php 
	    	for($i=11; $i<=15; $i++) {
	    		$j = $i-11;
	    		$nr = $j;
	    		$nr++;
	    		if (isset($letParamKeys['attached_letters'][$j])) {
	    			// load values from database to select fields
	    			$atKey = $letParamKeys['attached_letters'][$j];
	    			echo $this->Form->input('LetterParam.'.$atKey.'.id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$atKey.'.letter_element_type_id', array('type'=>'hidden'));
	    			echo $this->Form->input('LetterParam.'.$atKey.'.attachment_id', array('class'=>'confirm_sel', 'label'=>$nr.'. Attached Letter', 'empty'=>$empty, 'options'=>$letters));	    	
	    		}else{
	    			// create hidden and disabled select fields
	    			if (sizeof($freeKeys) > 0) {
	    			$k = array_shift($freeKeys);
		    			echo "<span class='hide_attachments hide_x'>";
		    			echo $this->Form->input('LetterParam.'.$k.'.letter_element_type_id', array('type'=>'hidden', 'value'=>42));
		    			echo $this->Form->input('LetterParam.'.$k.'.attachment_id', array('class'=>'confirm_sel', 'label'=>$nr.'. Attached Letter', 'empty'=>$empty, 'options'=>$letters));
		    			echo "</span>";
	    			}
	    		}
	    	}
	    	echo $this->Form->input('Show Additional Attachments', array('type'=>'button', 'class'=>'add_x', 'div'=>array('class'=>'add button'), 'label'=>false));
			?>
	</fieldset>
	<fieldset class="le_wrap">
		<legend>Static Letter Elements</legend>
		<p>These are static fields, that can be used in any letter or email</p>
		<?php 
		// button to show or hide checkboxes for showing or hiding letter elment types
		// checkboxes for showing or hiding element types
		echo "<div class='rowboxes'>";
		foreach ($statElemTypes as $elementType) {
			$typeAlias = $elementType['LetterElementType']['alias'];
			$checkOpts = array('type'=>'checkbox', 'hiddenField' => false, 'name' => 'data[LetterElementType][LetterElementType][]', 'class'=>'confirm_click input checkbox', 'label'=>$elementType['LetterElementType']['name'] , 'onChange'=>'showElem("'.$typeAlias.'")');
			if(in_array($elementType['LetterElementType']['id'],$selElemTypeIds)){
				$checkOpts['checked'] = true;
			}
			$checkOpts['value'] = $elementType['LetterElementType']['id'];
			echo $this->Form->input('LetterElementType.'.strval($cntTypes), $checkOpts);
			$cntTypes++;
		}
		echo "</div>";
		
		// LetterElements
		// Create letter element form fields
		if (!empty($statElemTypes)) {
			foreach ($statElemTypes as $elementType) {
				$typeAlias = $elementType['LetterElementType']['alias'];
				// If no options for element are in the database, disable field and notify user
				$options = null;
				$disabled = true;
				if (empty( $elements[$typeAlias])) {
					$emptyMsg = "(No values were entered in database for this item yet)";
				}else{
					$disabled = null;
					$options = $elements[$typeAlias];
					$emptyMsg = $empty;
				}		
					
				// selected assigned elements by checking if they have a value
				// if value is set, set it to the form element
				$selected = null;
				if (!empty($options) && !empty($selElementIds)){
					foreach ($options as $key => $value) {
						if (in_array($key,$selElementIds)) {
							$selected = $key;
						}
					}
				}
				$formOpts = array(
					'empty'=>$emptyMsg,
					'type' => 'select',
					'options' =>$options,
					'disabled' =>$disabled,
					'default' => $selected,
					'class'=>$typeAlias,
					'label'=>$elementType['LetterElementType']['name']
				); 
				// If element has value set show it, otherwise hide it
				if(in_array($elementType['LetterElementType']['id'],$selElemTypeIds)){//if ($selected != null) {						
					echo $this->Form->input('LetterElement.'.$typeAlias, $formOpts);
				}else{
					echo "<span class='hide_elems'>";
					echo $this->Form->input($typeAlias, $formOpts);
					echo "</span>";	
				}
			}
		}

	?>
	</fieldset>
	<fieldset>
		<legend>Availability for case file types</legend>
			<div class="rowboxes">
			    <?php
			     echo $this->Form->input('CaseFileType',
				     array(
				     		'div' => false,
				     		'label'=>false,
				     		'type'=>'select',
				     		'multiple' => 'checkbox',
				     		'options' => $caseFileTypes,
				     		'class'=>'confirm_click input checkbox',
				    )
			    );			    
			    ?>
		    </div>
	</fieldset>
	</span>

	
<?php echo $this->Form->end(__('Submit'));?>
</div>

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List %s', __('Letters')), array('action' => 'index'));?></li>
	</ul>
</div>
