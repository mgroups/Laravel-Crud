@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col"></div>
            <div class="col-6">

                @php $a = isset($MGVariableModel); @endphp

                <div class="card">
                    <div class="card-header h4 text-center">
                        MGCallWord Entry
                    </div>

                    <div class="card-body">

                        <div class="container">

                            <div class="card border-primary">
                                <div class="card-body">

                                    <form method="POST" @if($a) action="/MGKebabModel/{{$MGVariableModel->id}}" @else action="/MGKebabModel" @endif>
                                       {{-- <legend>Bill To</legend>
                                        <hr> --}}
                                        {{ csrf_field() }}

                                        @if($a) <input type="hidden" name="_method" value="PUT"> @endif

                                        @@MGInputs

                                        {{-- @@MGAdditionalInputs --}}

                                        <button type="submit" class="btn btn-outline-success btn-lg btn-block">SUBMIT</button>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <div class="col"></div>
        </div>
    </div>


@endsection

