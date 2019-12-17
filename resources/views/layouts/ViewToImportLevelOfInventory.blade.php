<!DOCTYPE html>
<html>
<head>
    <title>import excel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
</head>
<body>
   
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
   
</body>
</html>