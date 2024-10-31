jQuery(document).ready(function ($) {
  $("#apiform input").on( "keyup", function(){
    $('#fmsg').html('');
  } );

  $('input[name="taxcode"]').keyup(function() {
    var start = this.selectionStart;
    var end = this.selectionEnd;
    this.value = this.value.toUpperCase();
    this.setSelectionRange(start, end);
 });

  $('#apiformsubmit').click(function(){
    if(!validateForm($("#apiform"))){
      return false;
    }
    $('#submitloader').removeClass('hide');
    $('#apiformsubmit').attr("disabled", true);
    fdata=$("#apiform").serialize();
    jQuery.ajax({
      type: 'POST',
      url: ajax_url,
      data: fdata,
      success: function(data) {
        if(data.error==0){
          $('#submitloader').addClass('hide');
          $('#apiform')[0].reset();
          $('#apiformsubmit').attr("disabled", false);
          if(data.afterpost==2){
            $('#fmsg').html(data.msg);
            setTimeout(function(){ $('#fmsg').html('') }, 30000);
          }
          else{
            window.location.href = data.redirect;
          }
        }
        else{
          $('#submitloader').addClass('hide');
          $('#apiformsubmit').attr("disabled", false);
          $('#fmsg').html(data.msg);
        }
      },
      dataType: 'json'
    });
  });
  jQuery('.datepicker').removeClass('hasDatepicker').next().remove();
  jQuery('.datepicker').datepicker({
      showOn: 'both',
      buttonImage: plugin_url+"images/calender.png",
      buttonText : '<i class="dashicons-calendar-alt"></i>',
  });
});

function validateField(field) {
	var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
 //console.log(field, field.form, field[0].value);

	if (field[0].hasAttribute('required') && (field.attr('type')=="text" || field.attr('type')=="number" || field.attr('type')=="password") && field.val() == "" ) {
		// add an "invalid" class to the field:
		field.addClass('invalid');
		return false;
	}
  else if (field[0].name=='_cpassword' && field[0].value != document.getElementsByName("_password")[0].value ) {
		// add an "invalid" class to the field:
		field.addClass('invalid');
		return false;
	}
	else if (field[0].hasAttribute('required') && field.attr('type')=="email" && (field.val() == "" || !(re.test(String(field.val()).toLowerCase())) )) {
		// add an "invalid" class to the field:
		field.addClass('invalid');
		return false;
	}
  else if (field[0].hasAttribute('required') && field.attr('type')=="checkbox" && (field[0].checked != true) ) {
		// add an "invalid" class to the field:
		field.addClass('invalid');
    //console.log(field[0].checked, field, field[0], );
		return false;
	}
  else if (field[0].hasAttribute('required') && field[0].tagName=="SELECT" &&  (field[0].selectedIndex == -1 || field[0].options[field[0].selectedIndex].value == -1 || field[0].options[field[0].selectedIndex].value == '') ) {
		// add an "invalid" class to the field:
		field.addClass('invalid');
    //console.log(field[0].checked, field, field[0], );
		return false;
	}

  else if (field[0].hasAttribute('required') && field[0].tagName=="TEXTAREA" && field[0].value == "" ) {
		// add an "invalid" class to the field:
		field.addClass('invalid');
    //console.log(field[0].checked, field, field[0], );
		return false;
	}

  else{
		return true;
	}
}

function validateForm(form) {

	jQuery( "input:text, input[type=email], input[type=number], input[type=password], input[type=checkbox], select, textarea" ).change(function() {
	  if(validateField(jQuery(this))){
			jQuery(this).removeClass('invalid');
		}
	});

  // This function deals with validation of the form fields
  var x, y, y2, i, valid = true;
  y = form[0].getElementsByTagName("input");
  // A loop that checks every input field in the current tab:
  for (i = 0; i < y.length; i++) {
    // If a field is empty...
    if (!validateField(jQuery(y[i]))) {
      // and set the current valid status to false:
      valid = false;
    }
  }

  y2 = form[0].getElementsByTagName("select");
  // A loop that checks every input field in the current tab:
  for (i = 0; i < y2.length; i++) {
    // If a field is empty...
    if (!validateField(jQuery(y2[i]))) {
      // and set the current valid status to false:
      valid = false;
    }
  }

  y3 = form[0].getElementsByTagName("textarea");
  // A loop that checks every input field in the current tab:
  for (i = 0; i < y3.length; i++) {
    // If a field is empty...
    if (!validateField(jQuery(y3[i]))) {
      // and set the current valid status to false:
      valid = false;
    }
  }
  var userjsv=userjs();
  //console.log(userjsv);
  if(typeof userjsv !== 'undefined' && !userjsv){
    //console.log(typeof userjsv);
    valid = false;
  }
  // If the valid status is true, mark the step as finished and valid:
  return valid; // return the valid status
 //return false;
}
