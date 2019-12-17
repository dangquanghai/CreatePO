@extends('inc.master')
@section('content')
 <br><br> <br><br> 
     <div class="container">
        <div class="col-sm-8" style="background-color:white;">
            <div id="gridPO"></div>
        </div>
        <a id="mylink"></a>
        <div id="window">
            <div id="gridPODetail"></div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    $("#menu").kendoMenu();
   
    var SelectPOID;
    var ds = {!! json_encode($POs) !!};
     $("#gridPO").kendoGrid({
                        dataSource: {
                            data: ds,
                            schema: {
                                model: {
                                    fields: {
                                        id: { type: "number" },
                                        title: { type: "string" },
                                        the_week: { type: "number" },
                                        the_year: { type: "number" }
                                    }
                                }
                            },
                            pageSize: 20
                        },
                        /*
                        height: 550,
                        scrollable: true,
                        sortable: true,
                        filterable: true,
                        change: onChange,
                        */
                        height: 350,
                        change: onChange,
                        //dataBound: onDataBound,
                        //dataBinding: onDataBinding,
                        selectable: "multiple cell",
                        
                        sortable: true,
                        //filterable: true,
                       // groupable: true,
                       // sort: onSorting,
                       // filter: onFiltering,
                       // group: onGrouping,
                       // page: onPaging,
                       // groupExpand: onGroupExpand,
                       // groupCollapse: onGroupCollapse,

                        
                        pageable: {
                            input: true,
                            numeric: false
                        },
                        columns: [
                            { field: "id", title: "id", width: "130px" },
                            { field: "title", title: "vendor", width: "130px" },
                            { field: "the_week", width: "130px" },
                            { field: "the_year", width: "130px" },
                          
                        ]
                    });
                 

                    function onChange(arg) {
                    var a = document.getElementById('mylink'); 
                    SelectPOID = $.map(this.select(), function(item) {
                        return $(item).text();
                    });
                    
                    a.href = "/ShowPODetail/" + SelectPOID[0];
                    window.location=document.getElementById('mylink').href;
                    document.getElementById('mylink').click();
                   
                  }

  
</script>
@endsection