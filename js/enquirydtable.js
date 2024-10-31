var dtable;
jQuery(document).ready(function ($) {
  dtable=$('#enquirytable').dataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url":ajax_url+'?action=enquiry_datatables',
        "data":function(d){d.filterp=$('#aforms').val()}
      },
      columnDefs: [
        { targets: -1, orderable: false },
        { targets: 1, orderable: false },
      ],
  });
});
function redrawdatatable(that){
  dtable.api().draw(false);
}
function deletethis(id){
  if (!confirm(deletewarn)){
    return false;
  }
  else{
    jQuery.ajax({
      type: 'POST',
      url: ajax_url,
      data: {"id":id,'action':'enquiry_delete'},
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
    data: {"id":id,'action':'enquiry_view'},
    success: function(data) {
      if(data.error==0){
        //console.log(data.data)
        jQuery( "#dialog" ).attr('title',data.data['name']);
        html='';
        jQuery.each(data.data,function(key,val){
          html+=key+": "+val+"<br>";
        })
        jQuery( "#dialogcontents" ).html(html);
        jQuery( "#dialog" ).dialog();
      }
    },
    dataType: 'json'
  });

}
