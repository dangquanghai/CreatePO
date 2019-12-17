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
   
    var ds = {!! json_encode($POs) !!};
    var dsDetail = {!! json_encode($POs) !!};
     $("#gridPO").kendoGrid({
                        dataSource: {
                            data: ds,
                            schema: {
                                model: {
                                    fields: {
                                        id: {type: "number" },
                                        title: {type: "string" },
                                        the_week:{type: "number" },
                                        the_year:{type: "number" }
                                    }
                                }
                            },
                            pageSize: 20
                        },
                        height: 350,
                        change: onChange,
                        selectable: "multiple cell",
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
                 
 
</script>
@endsection