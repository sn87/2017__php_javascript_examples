function divHideClearShow(name, button, divId1, divId2) {
	
	var div = document.getElementById(divId1);
	var div2 = null;
	var conf = false;
	if (divId2 != null) {
		var div2 = document.getElementById(divId2);
	}
	var button = document.getElementById(button);

	

		
		
	if (div.style.display == "block") 
	{
		// Confirm clearing of all Inputs and Selects
		conf = confirm("Are you sure you want to clear all "+name+" fields?");
		if (conf == true) {			
			button.innerHTML = "Show "+name+" fields";
			// Hide div
			div.style.display = "none";
			// Clear all Inputs and Selects
			var childInputs = div.getElementsByTagName('input');
			var childSelects = div.getElementsByTagName('select');

			for (var i = 0; i < childInputs.length; i++) {			
				childInputs[i].value = '';
			}
			
			if (childSelects.length > 0) {
				for (var i = 0; i < childSelects.length; i++) {			
					childSelects[i].selectedIndex = 0;	
				}				
			}
		}

		
	}
	else if (div.style.display == "none")
	{
		// Show div
		div.style.display = "block";
		button.innerHTML = "Hide and clear "+name+" fields";
	}
	
	
	if (div2 != null) {

		if (div2.style.display == "block") 
		{
			
			if (conf == true) {			
				// Hide div
				div2.style.display = "none";	
				// Clear all Inputs and Selects
				var childInputs2 = div2.getElementsByTagName('input');
				var childSelects2 = div2.getElementsByTagName('select');
				
				for (var i = 0; i < childInputs2.length; i++) {			
					childInputs2[i].value = '';
				}
				
				if (childSelects2.length > 0) {
					for (var i = 0; i < childSelects2.length; i++) {			
						childSelects2[i].selectedIndex = 0;	
					}				
				}
			}
	
			
		}
		else if (div2.style.display == "none")
		{
			// Show div
			div2.style.display = "block";			
		}

	}
} 

function showElem(elemType) {
	var disabled = $('.'+elemType).attr('disabled');
	$id = $('.'+elemType).attr('id');
	if (disabled === 'disabled') {
		$('.'+elemType).show();		
		$("label[for='"+$id+"']").show();
		$('.'+elemType).closest('label').show();
		$('.'+elemType).closest('div').show();
		$('.'+elemType).attr('disabled',false);
	}else{ 
		$('.'+elemType).hide();
		$("label[for='"+$id+"']").hide();
		$('.'+elemType).closest('label').hide();
		$('.'+elemType).closest('div').hide();
		$('.'+elemType).attr('disabled',true);
	}
}






$(document).ready(function() {
	
	// select field with confirm onChange
	var prevValSel = "";
	$('.confirm_sel')
		.click(function(e){
			prevValSel = $(this).val();
		})
		.change(function(e){
			if (!confirm("Are you sure you want to change this value?\nThis might change the content of all letter instances and emails based on this letter template")) {
	            $(this).val(prevValSel);
	            return false;
			}
		});

	// checkbox / click with confirm onChange
	$('.confirm_click')
		.click(function(e){
			if (!confirm("Are you sure you want to change this value?\nThis might change the content of all letter instances and emails based on this letter template")) {
				e.preventDefault();
	            return false;
			}
		});
	
	
	
	
	/* ========== LETTERS ========== */
	
	$('.hide_x :input, .hide_elems :input').attr('disabled',true); 	
	$('.hide_x, .hide_elems').hide(); 
	$('.toggle_le').hide();

	$('.change_le').click(function(e){
		e.preventDefault();
		$('.toggle_le').toggle();
	});

	$('.mail_checkbox').change(function(e){
		e.preventDefault();
		if($(this).prop('checked') == true) {
			$('#mail_tmpls').attr('disabled',false).show();
			$('#doc_tmpls').attr('disabled',true).hide();
		}else{
			$('#mail_tmpls').attr('disabled',true).hide();
			$('#doc_tmpls').attr('disabled',false).show();
		}
	});

		
	$('.add_x').click(function(e){
		name = $(this).parents('fieldset').attr('class');
		var name = ".hide_"+name;
		var name_inputs = name+" :input";
		e.preventDefault();
		var disabled = $(name_inputs).first().attr('disabled');
		if (disabled === 'disabled') {
			$(name_inputs).attr('disabled',false); 
			$(name).show();
			
		}else{ 
			$(name_inputs).attr('disabled',true);
			$(name).hide();			 				
		}
		text = $(this).text();
		if (text.contains('Show')) {
			$(this).text(text.replace('Show','Hide'));
		}else if (text.contains('Hide')) {
			$(this).text(text.replace('Hide','Show'));
		}
	});	
	
    var maxFields	= 100; // Maximum allowed fields
    var wrapperLv	= $(".lv_wrap"); // Wrapper element: Deadline Reasons
    var addLv  		= $(".add_lv"); // Add Deadline Reason button ID
	var y 			= 0;
	if ($("#LetterLetterVarCnt").val() !== undefined && $("#LetterLetterVarCnt").val().length > 0) { y = $("#LetterLetterVarCnt").val()-1; }
    
	// On add Deadline Reason button click
    $(addLv).click(function(e){
        e.preventDefault();
        if(y < maxFields){
            // Field number increment
        	y++;
            $(wrapperLv).append(
            		'<span>'+
	            		'<div class="input text">'+
	            			'<label for="LetterVar'+y+'Name">Letter Variable Name</label>'+
	            			'<input name="data[LetterVar][][name]" maxlength="255"  id="LetterVar'+y+'Name" type="text">'+
	            		'</div>'+      
						'<div>'+
							'<a href="#" class="confirm_click remove_field">Remove Letter Variable Name</a>'+
	            		'</div>'+
            		'</span>'
            );
            
        }
    });

    
  	// user click on remove text
	$(wrapperLv).on("click",".remove_field", function(e){ 
		e.preventDefault(); $(this).parent('div').parent('span').remove();
    });
	

	

	// if there is a page refresh, set letter select to the value, when the page loaded for the first time
	if ($(".hidden_letter_sel").val()) {
		$('.letter_sel').val($(".hidden_letter_sel").val());
	}else if ($(".letter_sel option:first").val()) {
		$('.letter_sel').val($(".letter_sel option:first").val());
	}
	// show letter vars input fields according to selected letter
	var prevVal = "";
	$('.letter_sel')
		.click(function(){
			prevVal = $('.letter_sel').val();
		})
		.change(function(e){
			if (confirm("Changing template will delete all letter variables in this form")) {
				var id = $('.letter_sel').val();
				$.ajax( window.location.origin+"/juracake/letter_instances/get_letter_vars/"+id ) // IMPORTANT: without /juracake on server !!!
					.done(function(response) {							
						$('.letter_vars').empty();
						var varNames = $.parseJSON(response);
						if (varNames.length === 0 ) {
							$('.letter_vars').append('<p>No Letter Variables defined for this letter.</p>');
						}else{
							i = 0;
							$.each( varNames, function( key3, val3 ) {
								$.each( val3, function( key2, val2 ) {
									console.log(key2+""+val2);
									normName = "";
									upperCase = "";
									letterId = "";
									parentId = "";
									name = "";									
									$.each( val2, function( key1, val1 ) {
										console.log(key1+""+val1);
										// convert underscored words to camel cased, then make makel cased to normal words (beginning capital letter)
										if (key1 == "name") {
											name = val1;
											normName = val1.replace(/_([a-zA-Z0-9])/g, function (g) { return g[1].toUpperCase(); }).replace(/([A-Z0-9])/g, ' $1').replace(/^./, function(str){ return str.toUpperCase(); });
											// remove whitespaces from normal words (beginning capital letter)
											upperCase = normName.replace(/ /g, '');
										}
										if (key1 == "letter_id") {
											letter_id = val1;
										}
										if (key1 == "id") {
											parent_id = val1;
										}
									});
									$('.letter_vars').append(
											'<input name="data[LetterVar]['+i+'][name]" value="'+name+'" id="LetterVar'+i+'Name" type="hidden">'+
											'<input name="data[LetterVar]['+i+'][letter_id]" value="'+letter_id+'" id="LetterVar'+i+'LetterId" type="hidden">'+
											'<input name="data[LetterVar]['+i+'][parent_id]" value="'+parent_id+'" id="LetterVar'+i+'ParentId" type="hidden">'+
							            	'<div class="input text">'+
							        			'<label for="LetterVar'+i+'Value">'+normName+'</label>'+
							        			'<textarea name="data[LetterVar]['+i+'][value]" cols="30" rows="6" id="LetterVar'+i+'Value"></textarea>'+
							        		'</div>'
								        );
									i++;
								});

							});
						}
					})
					.fail(function() {
						
					});
			}else{
	            $(this).val(prevVal);
	            return false;
			}
		});
		

	//* ========= END OF LETERS ========= */
	
	
	//* ========= CASE FILES ============ */

	// show letter vars input fields according to selected letter
	var prevVal = "";
	$('.change_fields')
		.click(function(){
			prevVal = $('.change_fields').val();
		})
		.change(function(e){
			if (confirm("Changing this value might cause values of some fields to be lost")) {
				var pOfficeId = $('.poffice').val();
				var kindId = $('.kind').val();
				var filenumber = $('.filenumber').val();
				// reset changes made in case of errors
				$('.errormsg').hide();
				$('.errormsg').children("p").text("");
				if ($('.poffice').parent("div").hasClass("required error")){
					$('.poffice').parent("div").removeClass("required error");
				}
				if ($('.kind').parent("div").hasClass("required error")){
					$('.kind').parent("div").removeClass("required error");
				}
				// reset hidden input flag for save validation
				$("[name='data[CaseFile][validfile]']").val(1);

				$.ajax( window.location.origin+"/juracake/case_files/get_fields_filenumber/"+pOfficeId+"/"+kindId+"/"+filenumber ) // IMPORTANT: without /juracake on server !!!
					.done(function(response) {				
						var varReturn = $.parseJSON(response);
						if (varReturn['errors']) {
							var text = "";
							$.each(varReturn['errors'], function( key0, val0 ) {
								if (text !== "") text +=  "<br/>"; 
								text += val0;
								
							});
							$('.errormsg').show();
							$('.errormsg').children("p").text(text);
							$('.poffice').parent("div").addClass("required error");
							$('.kind').parent("div").addClass("required error");
							$("[name='data[CaseFile][validfile]']").val(0);
						}else{
							if (varReturn['filenumber']) {
								$('.filenumber').val(varReturn['filenumber']);
							}
							if (typeof varReturn['fields'] !== 'undefined' && varReturn['fields'].length > 0) {
								$.each(varReturn['fields'], function( key1, val1 ) {									
									if (val1 === 'priority' || val1 === 'pct') {										
										$("."+val1).show().children().show();
									}else{										
										$("[name='data[CaseFile]["+val1+"]']").parent("div").show().children().show();
									}
								});
							}
							if (typeof varReturn['unusedFields'] !== 'undefined' && varReturn['unusedFields'].length > 0) { 
								$.each(varReturn['unusedFields'], function( key2, val2 ) {
									if (val2 === 'priority' || val2 === 'pct') {								
										$("."+val2).hide().children().hide();
										$("."+val2).find(":input").val("");
									}else{										
										$("[name='data[CaseFile]["+val2+"]']").val("");
										$("[name='data[CaseFile]["+val2+"]']").parent("div").hide().children().hide();
									}
								});
							}
							
						}

					})
					.fail(function() {
						
					});
			}else{
	            $(this).val(prevVal);
	            return false;
			}
		});

});





