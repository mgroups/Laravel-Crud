@extends('layouts.app')

@section('content')

    <div class="container-fluid">
        @if(isset($MGVariableModels))



            <div class="table-responsive">

                <table class="table table-striped table-bordered table-hover table-condensed" style="margin-top: 50px;">
                    <thead>
                    <tr>
                        <th>#</th>
                        @@MGTableHeads
                        <th class="d-print-none">View &nbsp; <i class="fas fa-print"></i></th>
                        <th class="d-print-none">Edit &nbsp; <i class="fas fa-edit"></i> </th>
                        {{--<th class="d-print-none">Delete &nbsp; <i class="far fa-trash-alt"></i></th>--}}
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($MGVariableModels as $MGVariableModel)
                        <tr>
                            <td>{{ $loop->index + 1 }}</td>
                            @@MGTableData
                            <td class="d-print-none"> <a href="/MGKebabModel/{{ $MGVariableModel->id }}"><button class="btn btn-success">View &nbsp; <i class="fas fa-print"></i></button></a></td>
                            <td class="d-print-none"> <a href="/MGKebabModel/{{ $MGVariableModel->id }}/edit"><button class="btn btn-info">Edit &nbsp; <i class="fas fa-edit"></i></button></a></td>
                            {{-- <td class="d-print-none"> <a href="/MGKebabModel/{{ $MGVariableModel->id }}/edit"><button class="btn btn-info">Delete &nbsp; <i class="fas fa-trash-alt"></i></button></a></td> --}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>


            </div>

            @if($showPagination)

                <div class="d-flex justify-content-center">

                    {{ $MGVariableModels->links() }}

                </div>

            @endif

        @else

            <h4> No MGCallWord</h4>

        @endif

    </div>


@endsection
