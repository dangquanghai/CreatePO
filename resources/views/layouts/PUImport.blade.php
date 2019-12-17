@extends('inc.master')

@section('content')
<div class="container">
        <div class="card bg-light mt-3">
            <div class="card-header">
               
            </div>
            <div class="card-body">
                <form action="{{ route('puImport') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" id="file" class="form-control">
                    <br>
                    <button class="btn btn-success">Import Sales LOI</button>
                   
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
<script>
     
    $("#menu").kendoMenu();
   
</script>
@endsection