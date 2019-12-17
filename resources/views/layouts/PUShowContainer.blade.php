@extends('inc.master')

@section('content')
 <br><br> <br><br> 
     <div class="container">
        <div class="row">
            <div class="col-sm-4" style="background-color:white;">
            <form method ="post" action ="{{route('ShowUseContainer')}}">
                    @csrf
                    <table style="width:100%">
                        <tr>
                            <td>
                            From Year:
                            </td>
                            <td>
                                <input type="number"  class="form-control" name="FromYear" value="{{old('FromYear')}}" required > 
                            </td>
                            <td>
                            From Week:
                            </td>
                            <td>
                                <input type="number"  class="form-control" name="FromWeek" value="{{old('FromWeek')}}" required > 
                            </td>
                        </tr>
                        <tr>
                           
                        </tr>
                        <tr>
                        <td>
                            To Year:
                        </td>
                        <td>
                            <input type="number"    class="form-control"  name="ToYear"  value="{{old('ToYear')}}" required >
                        </td>

                        <td>
                            To Week:
                        </td>
                        <td>
                            <input type="number"   class="form-control"  name="ToWeek"  value="{{old('ToWeek')}}" required >
                        </td>

                        </tr>
                        <tr>
                           
                        </tr>
                
                        <tr>
                            <td>
                            </td>
                            <td>
                            <button class ="btn btn-primary " type="submit">View </button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
            <div class="col-sm-8" style="background-color:white;">
                <div id="treelist"></div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
    $("#menu").kendoMenu();
</script>
@endsection