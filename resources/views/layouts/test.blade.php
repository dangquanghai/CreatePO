@extends('inc.master')
@section('content')
 <br><br> <br><br> 
 <div id="example">
    <a id="mylink"></a>
    <div id="grid"></div>
    <div id="wnd">
      <div id="wrapped-grid"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $("#menu").kendoMenu();
    $(document).ready(function() {
    var ds = {!! json_encode($POs) !!};
            $("#grid").kendoGrid({
            dataSource: {
            data:ds,
              schema: {
                model: {
                  fields: {
                    id: { type: "number" },
                    title:  { type: "string" },
                    the_week: { type: "number" },
                    the_year: { type: "number" }
                  }
                }
              },
              pageSize: 20,
              serverPaging: true,
              serverFiltering: true,
              serverSorting: true
            },
            height: 550,
            filterable: true,
            sortable: true,
            pageable: true,
            columns: [{
              field:"id",
              filterable: false
            },
                      "title",
                      {
                        field: "the_week",
                        title: "the_week"
                      }, {
                        field: "the_year",
                        title: "the_year"
                      } 
                     ]
          });

          var wnd = $("#wnd").kendoWindow({
            height: 400,
            width: 600,
            visible: false
          }).data("kendoWindow");

          var wrappedGrid = $("#wrapped-grid").kendoGrid({
            dataSource: {
              data: products,
              pageSize: 5
            },
            height: 200,
            scrollable: true,
            columns: [
              "sku",
              { field: "title", title: "title", width: "130px" },
              { field: "quantity", title: "quantity:", width: "130px" }
            ]
          }).data("kendoGrid");

          //apply the activate event, which is thrown only after the animation is played out
          wnd.one("activate", function() {
            wrappedGrid.resize();
          });

          $('table').on('click', function(e){
          	if(e.target.nodeName == 'TD'){
            	wnd.title(e.target.innerText);
              e.preventDefault();
              $.ajaxSetup({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                  }
              });
               jQuery.ajax({
                  url: "{{ url('/GetPODetail/post') }}",
                  method: 'post',
                  data: {
                     name: jQuery('#name').val(),
                     type: jQuery('#type').val(),
                     price: jQuery('#price').val()
                  },
                  success: function(result){
                     jQuery('.alert').show();
                     jQuery('.alert').html(result.success);
                  }});
               });



              var products = {!! json_encode($PODetails) !!};
              wnd.open();
            }
          })
        });
</script>
@endsection