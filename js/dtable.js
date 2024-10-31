var dtable;
jQuery(document).ready(function ($) {
  dtable=$('#apiformtable').dataTable({
      "processing": true,
      "serverSide": true,
      "ajax": ajax_url+'?action=apiforms_datatables',
      columnDefs: [
        { targets: -1, orderable: false },
        { targets: 1, orderable: false },
      ],
  });
});

function deletethis(id){
  if (!confirm(deletewarn)){
    return false;
  }
  else{
    jQuery.ajax({
      type: 'POST',
      url: ajax_url,
      data: {"id":id,'action':'apiforms_delete'},
      success: function(data) {
        if(data.error==0){
          dtable.api().draw(false);
        }
      },
      dataType: 'json'
    });
  }
}

function viewthis(id){
  jQuery.ajax({
    type: 'POST',
    url: ajax_url,
    data: {"id":id,'action':'apiforms_view'},
    success: function(data) {
      if(data.error==0){
        jQuery( "#dialog" ).attr('title',data.data['formname']);
        html=data.data['formpreview'];
        jQuery( "#dialogcontents" ).html(html);
        jQuery( "#dialog" ).dialog();
        jQuery('.datepicker').removeClass('hasDatepicker').next().remove();
        jQuery('.datepicker').datepicker({
            showOn: 'both',
            buttonImage: plugin_url+"images/calender.png",
            buttonText : '<i class="dashicons-calendar-alt"></i>',
        });
      }
    },
    dataType: 'json'
  });
}
