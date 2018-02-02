@extends('themes.default1.agent.layout.agent')

@section('Tools')
class="active"
@stop

@section('tools-bar')
active
@stop

@section('tools')
class="active"
@stop

@section('PageHeader')
<h1>{{trans('lang.tools')}}</h1>
@stop

<!-- content -->
@section('content')
<div class="box box-primary">
    <div class="box-header with-border">
        <h2 class="box-title">{!! trans('lang.canned_response') !!}</h2><a href="{{route('canned.create')}}" class="btn btn-primary pull-right">{!! trans('lang.create_canned_response') !!}</a></div>
    <div class="box-body table-responsive">
        <?php
        $Canneds = App\Model\helpdesk\Agent_panel\Canned::where('user_id', '=', Auth::user()->id)->paginate(20);
        ?>
        <!-- check whether success or not -->
        {{-- Success message --}}
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissable">
            <i class="fa  fa-check-circle"></i>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('success')}}
        </div>
        @endif
        {{-- failure message --}}
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fa fa-ban"></i>
            <b>{!! trans('lang.alert') !!}!</b>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('fails')}}
        </div>
        @endif
        <!-- Agent table -->
        <table class="table table-bordered table-hover" id="example1"  >
            <tr>
                <th width="100px">{{trans('lang.name')}}</th>
                <th width="100px">{{trans('lang.action')}}</th>
            </tr>
            @foreach($Canneds as $Canned)
            <tr>
                <td>{{$Canned->title }}</td>
                <td> 
                    {!! Form::open(['route'=>['canned.destroy', $Canned->id],'method'=>'DELETE']) !!}
                    <a data-toggle="modal" data-target="#view{!! $Canned->id !!}" href="#" class="btn btn-info btn-xs btn-flat">{!! trans('lang.view') !!}</a>
                    <a href="{!! URL::route('canned.edit',$Canned->id) !!}" class="btn btn-primary btn-xs btn-flat">{!! trans('lang.edit') !!}</a>
                    {!! Form::button('<i class="fa fa-trash" style="color:black;"> </i> '.trans('lang.delete'),
                    ['type' => 'submit',
                    'class'=> 'btn btn-warning btn-xs btn-flat',
                    'onclick'=>'return confirm("Are you sure?")'])
                    !!}
                    {!! Form::close() !!}
                </td>
            </tr>
            <!-- Surrender Modal -->
            <div class="modal fade" id="view{!! $Canned->id !!}">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">{!! trans('lang.surrender') !!}</h4>
                        </div>
                        <div class="modal-body">
                            <p><pre>{!! $Canned->message !!}</pre></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal" id="dismis6">{!! trans('lang.close') !!}</button>
                        </div>
                    </div><!-- /.modal-content -->
                </div><!-- /.modal-dialog -->
            </div><!-- /.modal -->
            @endforeach
        </table>
    </div>
</div>
<script>
    $(function() {
        $("#example1").DataTable();
        $('#example2').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": true
        });
    });
</script>
@stop
<!-- /content --> 