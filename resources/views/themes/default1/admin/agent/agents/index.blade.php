@extends('themes.default1.layouts.admin')

@section('Staffs')
class="active"
@stop

@section('staffs-bar')
active
@stop

@section('staffs')
class="active"
@stop


@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')

@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">

</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')

	
<div class="box box-primary">
<div class="box-header">
	<h2 class="box-title">{{Lang::get('lang.agents')}}</h2><a href="{{route('agents.create')}}" class="btn btn-primary pull-right">{{Lang::get('lang.create_agent')}}</a></div>

<div class="box-body table-responsive no-padding">

<!-- check whether success or not -->

@if(Session::has('success'))
    <div class="alert alert-success alert-dismissable">
        <i class="fa  fa-check-circle"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('success')}}
    </div>
    @endif
    <!-- failure message -->
    @if(Session::has('fails'))
    <div class="alert alert-danger alert-dismissable">
        <i class="fa fa-ban"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {{Session::get('fails')}}
    </div>
    @endif
    		<!-- Agent table -->
				<table class="table table-hover" style="overflow:hidden;">
					<tr>
							<th width="100px">{{Lang::get('lang.name')}}</th>
							<th width="100px">{{Lang::get('lang.user_name')}}</th>
							<th width="100px">{{Lang::get('lang.role')}}</th>
							<th width="100px">{{Lang::get('lang.status')}}</th>
							<th width="100px">{{Lang::get('lang.group')}}</th>
							<th width="100px">{{Lang::get('lang.department')}}</th>
							<th width="100px">{{Lang::get('lang.created')}}</th>
							{{-- <th width="100px">{{Lang::get('lang.lastlogin')}}</th> --}}
							<th width="100px">{{Lang::get('lang.action')}}</th>
						</tr>
						@foreach($user as $use)
						@if($use->role == 'admin' || $use->role == 'agent')
						<tr>
							<td><a href="{{route('agents.edit', $use->id)}}"> {!! $use->first_name !!} {!! " ". $use->last_name !!}</a></td>
							<td><a href="{{route('agents.edit', $use->id)}}"> {!! $use->user_name !!}</td>
							<?php 
							if($use->role == 'admin')
							{
							echo '<td><button class="btn btn-success btn-xs">'.$use->role.'</button></td>';	
							}
							elseif ($use->role == 'agent') {
							echo '<td><button class="btn btn-primary btn-xs">'.$use->role.'</button></td>';		
							}
							?>
							<td>
								@if($use->account_type=='1')
								<span style="color:green">{{'Active'}}</span>
								@else
								<span style="color:red">{{'Inactive'}}</span>
								@endif
							<td>{{$use->assign_group }}</td>
							<td>{{$use->primary_dpt }}</td>
							<td>{{$use->created_at}}</td>
							{{-- <td>{{$use->Lastlogin_at}}</td> --}}
							<td>
							{!! Form::open(['route'=>['agents.destroy', $use->id],'method'=>'DELETE']) !!}
							<a href="{{route('agents.edit', $use->id)}}" class="btn btn-info btn-xs btn-flat"><i class="fa fa-edit" style="color:black;"> </i> Edit</a>
								<!-- To pop up a confirm Message -->
									{!! Form::button('<i class="fa fa-trash" style="color:black;"> </i> Delete',
					            		['type' => 'submit',
					            		'class'=> 'btn btn-warning btn-xs btn-flat',
					            		'onclick'=>'return confirm("Are you sure?")'])
					            	!!}
							{!! Form::close() !!}
							</td>
						</tr>
						@endif
						@endforeach
				</table>
		</div>
		<div class="box-footer"></div>
</div>
@section('FooterInclude')
@stop
@stop
<!-- /content -->