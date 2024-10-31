jQuery(document).ready(function ($) {

  $('input[name="afterpst"]').change(function(){
    var selchi=$(this).val();
    $('.spthn').addClass('hide');
    $('#spthn'+selchi).removeClass('hide');
  });

  $('input[name="tandc"]').change(function(){
    var selchi=$(this).val();
    $('.tandcsettings').addClass('hide');
    $('#tandcsettings'+selchi).removeClass('hide');
  });

  $('input[name="contacttp"]').change(function(){
    var selchi=$(this).val();
    $('.creatapiuser').addClass('hide');
    $('#creatapiuser'+selchi).removeClass('hide');
    $('.appconnect').addClass('hide');
    $('#appconnect'+selchi).removeClass('hide');
    if(selchi==2){
      $('input[name="creatapiuser"]').prop('checked',false).change();
      $('input[name="appconnect"]').prop('checked',false).change();

    }
  });

  $('input[name="creatapiuser"]').change(function(){
    if($(this).is(":checked")){
      addUserPassword();
    }
    else{
      removeUserPassword();
    }
  });
  $('input[name="creatwpuser"]').change(function(){
    if($(this).is(":checked")){
      addUserPassword();
    }
    else{
      removeUserPassword();
    }
  });
  $('input[name="appconnect"]').change(function(){
    if($(this).is(":checked")){
      $('#smsdisclaimer1').removeClass('hide');
    }
    else{
        $('#smsdisclaimer1').addClass('hide');
    }
  });

  function removeUserPassword(){
    if($('input[name="creatapiuser"]').is(":checked") || $('input[name="creatwpuser"]').is(":checked")){
      return 1;
    }
    else{
      $('._cpasswordContainer').parent().remove();
      $('._passwordContainer').parent().remove();
      getPreview();
    }
  }
  function addUserPassword() {
      var field = "_password";
      var name = form_builder_vars.password; ;
      var type = "password";
      var required = 1;
      var standard = 0;
      var soption = 0;
      var html = '<div class="all_div _passwordContainer"><div class="row li_row"><div class="col-md-12"><div class="pull-left form_output" data-name="' + name + '" data-layout="12" data-key="' + field + '" data-type="' + type + '" data-required="' + required + '" data-standard="' + standard + '"  data-soption=\'' + soption + '\'>'+name+'</div><button type="button"  class="btn btn-primary btn-sm  pull-right mrr-5 tglbtn"  title="'+form_builder_vars.showoption+'"><i class="fa fa-ellipsis-h"></i></button><div class="clearfix"></div><div class="toggleble">'+form_builder_vars.label+' <input type="text" name="lbl" class="inptlbl form-control" value="'+name+'"><span>'+form_builder_vars.required+'</span> <input type="checkbox" value="1" name="'+name+'_req" class="requiredtick" '+(required==1?"checked=\"true\" ":"")+' disabled="true" ><div class="ltsep">'+form_builder_vars.layout+' <span class="lticon active" data-span="12">1</i></span> <span class="lticon"  data-span="6">1/2</span>  <span class="lticon"  data-span="4">1/3</span>  <span class="lticon"  data-span="3">1/4</span> <span class="lticon"  data-span="8">2/3</span> <span class="lticon"  data-span="9">3/4</span> </div><div class="clearfix"></div></div></div></div></div>';
      if($('._passwordContainer').length==0){
        var genUsernameField = $('<div>').addClass('li_' + field + ' form_builder_field').html(html);
        $(".form_builder_area").append(genUsernameField);
      }


       field = "_cpassword";
       name = form_builder_vars.cpassword; ;
       type = "password";
       required = 1;
       standard = 0;
       soption = 0;
       html = '<div class="all_div _cpasswordContainer"><div class="row li_row"><div class="col-md-12"><div class="pull-left form_output" data-name="' + name + '" data-layout="12" data-key="' + field + '" data-type="' + type + '" data-required="' + required + '" data-standard="' + standard + '"  data-soption=\'' + soption + '\'>'+name+'</div><button type="button"  class="btn btn-primary btn-sm  pull-right mrr-5 tglbtn"  title="'+form_builder_vars.showoption+'"><i class="fa fa-ellipsis-h"></i></button><div class="clearfix"></div><div class="toggleble">'+form_builder_vars.label+' <input type="text" name="lbl" class="inptlbl form-control" value="'+name+'"><span>'+form_builder_vars.required+'</span> <input type="checkbox" value="1" name="'+name+'_req" class="requiredtick" '+(required==1?"checked=\"true\" ":"")+' disabled="true" ><div class="ltsep">'+form_builder_vars.layout+' <span class="lticon active" data-span="12">1</i></span> <span class="lticon"  data-span="6">1/2</span>  <span class="lticon"  data-span="4">1/3</span>  <span class="lticon"  data-span="3">1/4</span> <span class="lticon"  data-span="8">2/3</span> <span class="lticon"  data-span="9">3/4</span> </div><div class="clearfix"></div></div></div></div></div>';
       if($('._cpasswordContainer').length==0){
        var genPassField = $('<div>').addClass('li_' + field + ' form_builder_field').html(html);
        $(".form_builder_area").append(genPassField);
      }

      getPreview();

  }

  $(".form_bal_formfield").draggable({
      helper: function () {
          return getFormFieldHTML(this);
      },
      connectToSortable: ".form_builder_area"
  });


    $(".form_builder_area").sortable({
        cursor: 'move',
        placeholder: 'placeholder',
        start: function (e, ui) {
            ui.placeholder.height(ui.helper.outerHeight());
        },
        stop: function (ev, ui) {
            getPreview();
        }
    });
    $(".form_builder_area").disableSelection();
    getPreview();
    var el = $('.form_builder_area .form_output');
    $('.toggleble').css('display','none')
    el.each(function () {
        var data_name = $(this).attr('data-name');
        $(this).parent().find('.inptlbl').val(data_name);
        if($(this).parent().find('.requiredtick').length==0){
          var required=$(this).attr('data-required');
          var type=$(this).attr('data-type');
          var newrequired='<span>'+form_builder_vars.required+'</span> <input type="checkbox" value="1" name="'+data_name+'_req" class="requiredtick" '+(required==1?"checked=\"true\" ":"")+' '+(required==1 || type=="checkbox" || type=="radio"?" disabled=\"true\"":"")+' > ';
          $(newrequired).insertAfter($(this).parent().find('.inptlbl'));
        }
        if($(this).attr('data-required')==1){
          $(this).parent().find('.requiredtick').prop("checked", true);
        }
    });
    function unescapeUnicode( str ) {
        return str.replace( /\\u([a-fA-F0-9]{4})/g, function(g, m1) {
             return String.fromCharCode(parseInt(m1, 16));
        });
    }
    function getFormFieldHTML(that) {
      var el = $('.form_builder_area .form_output');
      var found=false;
      el.each(function () {
        if($(that).data('key')==$(this).attr('data-key')){
          found=true;
        }
      });
      if(found){
        return $('<div>');
      }
        //console.log();
        var field = $(that).data('key');
        var name = $(that).data('name');
        var type = $(that).data('type');
        var required = $(that).data('required');
        var standard = $(that).data('standard');
        var soption = unescapeUnicode($(that).attr('data-soption'));
        var html = '<div class="all_div"><div class="row li_row"><div class="col-md-12"><div class="pull-left form_output" data-name="' + name + '" data-layout="12" data-key="' + field + '" data-type="' + type + '" data-required="' + required + '" data-standard="' + standard + '"  data-soption=\'' + soption + '\'>'+name+'</div><button type="button" class="btn btn-primary btn-sm remove_bal_field pull-right" data-field="' + field + '" title="'+form_builder_vars.remove+'"><i class="fa fa-times"></i></button><button type="button"  class="btn btn-primary btn-sm  pull-right mrr-5 tglbtn"  title="'+form_builder_vars.showoption+'"><i class="fa fa-ellipsis-h"></i></button><div class="clearfix"></div><div class="toggleble">'+form_builder_vars.label+' <input type="text" name="lbl" class="inptlbl form-control" value="'+name+'"><span>'+form_builder_vars.required+'</span> <input type="checkbox" value="1" name="'+name+'_req" class="requiredtick" '+(required==1?"checked=\"true\" ":"")+' '+(required==1 || type=="checkbox" || type=="radio"?" disabled=\"true\"":"")+' ><div class="ltsep">'+form_builder_vars.layout+' <span class="lticon active" data-span="12">1</i></span> <span class="lticon"  data-span="6">1/2</span>  <span class="lticon"  data-span="4">1/3</span>  <span class="lticon"  data-span="3">1/4</span> <span class="lticon"  data-span="8">2/3</span> <span class="lticon"  data-span="9">3/4</span> </div><div class="clearfix"></div></div></div></div></div>';
        return $('<div>').addClass('li_' + field + ' form_builder_field').html(html);
    }

    $(document).on('click', '.lticon', function (e) {
        var spanval = $(this).attr('data-span');
        $(this).parent().parent().parent().find('.form_output').attr('data-layout',spanval);
        $(this).parent().find('.lticon').removeClass('active');
        $(this).addClass('active');
        getPreview();
    });
    $(document).on('click', '.addtoinclude', function (e) {
        var targele = $(this).parent().parent();
        var generet = getFormFieldHTML(targele);
        $(".form_builder_area").append(generet);
        getPreview();
    });

    $(document).on('click', '.requiredtick', function (e) {
        var requiredchecked = $(this).is(":checked");
        if(requiredchecked){
          $(this).parent().parent().parent().find('.form_output').attr('data-required',1);
        }
        else{
          $(this).parent().parent().parent().find('.form_output').attr('data-required',0);
        }

        getPreview();
    });


    $(document).on('click', '.remove_bal_field', function (e) {
        e.preventDefault();
        var field = $(this).attr('data-field');
        $(this).closest('.li_' + field).hide('400', function () {
            $(this).remove();
            getPreview();
        });
    });

    $(document).on('keyup', '.inptlbl', function () {
        var inptlbl_val = $(this).val();
        $(this).parent().parent().find('.form_output').attr('data-name',inptlbl_val);
        getPreview();
    });

    $(document).on('click', '.tglbtn', function () {
      jQuery(this).closest('.form_builder_field').css('height', 'auto');
      jQuery(this).parent().find('.toggleble').slideToggle();
    });

    function getPreview(plain_html = '') {
        $('li.form_bal_formfield').css('border-color','#ccc');
        var el = $('.form_builder_area .form_output');
        var html = '';
        //console.log(el);
        el.each(function () {
            var data_type = $(this).attr('data-type');
            //var field = $(this).attr('data-field');
            var label = $(this).find('.form_input_label').val();
            label=(label==undefined?$(this).attr('data-name'):label);
            var name = $(this).find('.form_input_name').val();
            name=(name==undefined?$(this).attr('data-key'):label);
            var placeholder = $(this).find('.form_input_placeholder').val();
            placeholder=(placeholder==undefined?'':placeholder);
            var layout = $(this).attr('data-layout')
            var checkbox = $(this).find('.form-check-input');
            var required = '';
            if (checkbox.is(':checked')) {
                required = 'required';
            }
            else{
              required = ($(this).attr('data-required')==1?'required':'');
            }
            $('li#li_'+name).css('border-color','#f00');
            if (data_type === 'text' || data_type === 'number' || data_type === 'email' || data_type === 'password' ) {
                html += '<div class="form-group col-md-'+layout+'"><label class="control-label">' + label +(required==''?'':'*')+'</label><input type="'+data_type+'" name="' + name + '" placeholder="' + placeholder + '" class="form-control" ' + required + '/></div>';
            }
            if (data_type === 'textarea') {
                html += '<div class="form-group col-md-'+layout+'"><label class="control-label">' + label +(required==''?'':'*')+'</label><textarea rows="5" name="' + name + '" placeholder="' + placeholder + '" class="form-control" ' + required + '/></div>';
            }

            if (data_type === 'date') {

                html += '<div class="form-group col-md-'+layout+'"><label class="control-label">' + label +(required==''?'':'*')+'</label><input type="text" name="' + name + '" class="form-control datepicker" ' + required + '/></div>';
            }
            if (data_type === 'button') {
                var btn_class = $(this).find('.form_input_button_class').val();
                var btn_value = $(this).find('.form_input_button_value').val();
                html += '<button name="' + name + '" type="submit" class="' + btn_class + '">' + btn_value + '</button>';
            }
            if (data_type === 'select' || data_type === 'multiselect') {
                var option_html = '';
                $(this).find('select option').each(function () {
                    var option = $(this).html();
                    var value = $(this).val();
                    option_html += '<option value="' + value + '">' + option + '</option>';
                });
                var options=JSON.parse($(this).attr('data-soption'));
                $.each(options, function(idx, obj) {
                  option_html += '<option value="' + obj.id + '">' + obj.name + '</option>';
                });

                html += '<div class="form-group col-md-'+layout+'"><label class="control-label">' + label +(required==''?'':'*')+'</label><select class="form-control" name="' + name + ''+(data_type=='multiselect'?'[]':'')+'" ' + required + ' '+(data_type=='multiselect'?'MULTIPLE':'')+'>' + option_html + '</select></div>';
            }
            if (data_type === 'radio') {
                var option_html = '';
                $(this).find('.mt-radio').each(function () {
                    var option = $(this).find('p').html();
                    var value = $(this).find('input[type=radio]').val();
                    option_html += '<div class="form-check"><label class="form-check-label"><input type="radio" class="form-check-input" name="' + name + '" value="' + value + '">' + option + '</label></div>';
                });

                html += '<div class="form-group  col-md-'+layout+'"><label class="control-label">' + label +(required==''?'':'*')+'</label>' + option_html + '</div>';
            }
            if (data_type === 'checkbox') {
                var option_html = '';
                $(this).find('.mt-checkbox').each(function () {
                    var option = $(this).find('p').html();
                    var value = $(this).find('input[type=checkbox]').val();
                    option_html += '<div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="' + name + '[]" value="' + value + '">' + option + '</label></div>';
                });

                var options=JSON.parse($(this).attr('data-soption'));
                if(options==undefined || options.length==0){
                  option_html += '<div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="' + name + '" value="1"></label></div>';
                }
                else{
                  $.each(options, function(idx, obj) {
                    option_html += '<div class="form-check"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="' + name + '[]" value="' + obj.id + '">' + obj.name + '</label></div>';

                  });
                }

                html += '<div class="form-group col-md-'+layout+'"><label class="control-label">' + label + '</label>' + option_html + '</div>';
            }
        });
        if (html.length) {
            $('.export_html').removeClass('hide');
        } else {
            $('.export_html').addClass('hide');
        }
        $('.preview').html(html).show();

    $('.form_builder_area').css('height','auto');
    if($('.nav').height()>=$('.form_builder_area').height()){
      $('.form_builder_area').height($('.nav').height());
    }

    $('.bal_builder').height($('.form_builder_area').height());

      $('.datepicker').datepicker({
          showOn: 'both',
          buttonImage: plugin_url+"images/calender.png",
          buttonText : '<i class="dashicons-calendar-alt"></i>',
      });

    }
    $(document).on('click', '.export_html', function () {
      var el = $('.form_builder_area .form_output');
      var found=0;
      var allFields=[];
      el.each(function () {
        allFields.push($(this).attr('data-key'));
        if($(this).attr('data-required')==1){
          found++;
        }
      });
      //console.log(allFields);
      //console.log(requiredsFields);
      //console.log(requiredsFields.every(function(val) { return allFields.indexOf(val) >= 0; }));
      if($('#formnamef').val()==''){
        alert(form_builder_vars.enter_form);
        return false;
      }

      if($('input[name="afterpst"]:checked').val()==1 && $('#thankurl').val()==''){
        alert(form_builder_vars.thanks_url_error);
        return false;
      }

      if($('input[name="afterpst"]:checked').val()==2 && $('#thanksmsg').val()==''){
        alert(form_builder_vars.thanks_msg_error);
        return false;
      }

      if($('input[name="tandc"]:checked').val()==1 && $('#tandc_label').val()==''){
        alert(form_builder_vars.tandc_label_error);
        return false;
      }

      if($('input[name="tandc"]:checked').val()==1 && $('#tandc_url').val()==''){
        alert(form_builder_vars.tandc_url_error);
        return false;
      }

      if(requiredsFields.every(function(val) { return allFields.indexOf(val) >= 0; })===false){
        alert(form_builder_vars.star_mark);
        return false;
      }

      $('#formname').val($('#formnamef').val());
      $('#formpreview').val($('.preview').html());
      $('#formelement').val($('.form_builder_area').html().trim());
      $('#saveform').submit();
    });

    if($('.nav').height()>=$('.form_builder_area').height()){
      $('.form_builder_area').height($('.nav').height());
    }
    else{
      $('.form_builder_area').css('height','auto');
    }
    $('.bal_builder').height($('.form_builder_area').height());

});
