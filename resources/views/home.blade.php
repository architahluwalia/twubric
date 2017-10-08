@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Followers List</div>

                <div class="panel-body">
                    <ul>
                    @foreach ($followers['users'] as $user)
                    <li>
                        <a href = "/follower/{{$user['id']}}">{{$user['name']}}</a>
                    </li>
                    @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
