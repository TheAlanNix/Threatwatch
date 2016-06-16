@extends('template')

@section('title', 'ThreatWatch Configuration')

@section('style')
#opendns_key {
	width: 100%;
}
@stop

@section('content')
<div class="page-header">
	<h1>Configuration</h1>
</div>

<div class="col-xs-4">
	<h3>StealthWatch</h3>

	@if (count($errors) > 0)
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@endif

	@if (session('SMC_ERROR'))
		 <div class="alert alert-danger">{{ session('SMC_ERROR') }}</div>
	@endif

	@if (!empty(getSetting('SMC_IP')))
		@if ($smc_status == 200)
			<div class="alert alert-success">Successfully Connected to Stealthwatch.</div>
		@elseif ($smc_status == 401 || $smc_status == 403)
			<div class="alert alert-danger">Access Denied. Please verify your Stealthwatch credentials.</div>
		@else
			<div class="alert alert-warning">SMC Connection Failure. Status code: {{ $smc_status }}</div>
		@endif
	@endif

	<form method="post" action="/config/stealthwatch">
		<input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="form-group">
			<input class="form-control input-sm" type="text" name="ip_address" placeholder="IP Address" value="{{ getSetting('SMC_IP') }}">
		</div>
		<div class="form-group">
			<input class="form-control input-sm" type="text" name="username" placeholder="Username" value="{{ getSetting('SMC_USER') }}">
		</div>
		<div class="form-group">
			<input class="form-control input-sm" type="password" name="password" placeholder="Password" value="{{ getSetting('SMC_PASS') }}">
		</div>
		<div class="form-group">
			<input class="form-control input-sm" type="text" name="domain" placeholder="Domain ID" value="{{ getSetting('SMC_DOMAIN') }}">
		</div>
		<div class="center">
			<input class="btn btn-sm btn-primary" type="submit" value="Save">
		</div>
	</form>
</div>

<div class="col-xs-4">
	<h3>OpenDNS</h3>

	@if (!empty(getSetting('OPENDNS_KEY')))
		@if ($opendns_status == 200 || $opendns_status == 204)
			<div class="alert alert-success">Successfully Connected to OpenDNS.</div>
		@elseif ($opendns_status == 401 || $opendns_status == 403)
			<div class="alert alert-danger">Access Denied. Please verify your API Key.</div>
		@else
			<div class="alert alert-warning">API Connection Failure. Status code: {{ $opendns_status }}</div>
		@endif
	@endif

	<form action="/config/opendns" method="post">
	    <input type="hidden" name="_method" value="POST">
	    <input type="hidden" name="_token" value="{{ csrf_token() }}">
		<div class="form-group">
			<input class="form-control input-sm" type="text" name="opendns_key" placeholder="OpenDNS API Key" value="{{ getSetting('OPENDNS_KEY') }}">
		</div>
		<div class="center">
			<input class="btn btn-sm btn-primary" type="submit" value="Save">
		</div>
	</form>

</div>

<div class="col-xs-12">
	<h3>Blacklists</h3>

	<table class="table">
		<tr>
			<th>Name</th>
			<th>URL</th>
			<th>RegEx</th>
			<th>Action</th>
		</tr>

		@foreach ($blacklists as $blacklist)
			<tr>
				<td>{{ $blacklist->name }}</td>
				<td>{{ $blacklist->url }}</td>
				<td>{{ $blacklist->regex }}</td>
				<td><a class="btn btn-sm btn-danger" href="/config/delete-blacklist?id={{ $blacklist->id }}">Delete</a></td>
			</tr>
		@endforeach

		<tr>
			<form method="post" action="/config/blacklist">
				<input type="hidden" name="_method" value="POST">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<td><input class="form-control input-sm" type="text" name="name"></td>
				<td><input class="form-control input-sm" type="text" name="url"></td>
				<td><input class="form-control input-sm" type="text" name="regex"></td>
				<td><input class="btn btn-sm btn-success" type="submit" value="Save"></td>
			</form>
		</tr>
	</table>
</div>

@stop
