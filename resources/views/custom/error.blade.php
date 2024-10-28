@extends('layouts.admin_master')
@section('content')
<div>
        
    <div class="col-md-12" style="min-height: 250px;padding: 10%;font-size: larger;font-weight: bolder;text-align: center;">
        Oops! Something went wrong.<br> {{$error}} <br>
        <a class="bg-green btn btn-info" href="{{URL('/')}}/admin/home">Dashboard</a>
    </div>

</div>
@endsection