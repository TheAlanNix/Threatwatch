@extends('template')

@section('title', 'IP Details')

@section('content')

	<div class="page-header">
		<span style="float: right">
			@if (!empty(getSetting('SMC_IP')))
				<a class="btn btn-primary" href="/host-group-tree/?ip_address={{ $ip_address }}">Add to Host Group</a>
			@else
				A Stealthwatch SMC has not been configured.<br><a href="/config">Click here to add one.</a>
			@endif
		</span>
		<h1>IP Details for <small>{{ $ip_address }}</small></h1>
	</div>

	@if (count($errors) > 0)
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@else
		@if (!empty(getSetting('OPENDNS_KEY')))
			<div class="col-xs-12">
				<h3>OpenDNS</h3>

				<div class="col-xs-12 col-md-6">
					<div class="col-xs-12 box-shadow buffer">
						<div class="col-xs-12">
							<h4>90 Day Domain History ({{$open_dns_history_data->features->rr_count }} Entries)</h4>

							<div class="col-xs-12">
								@if (!empty($open_dns_history_data))
									<ul>
										@foreach ($open_dns_history_data->rrs as $domain)
											<li><a href="/domain/{{ rtrim($domain->rr, '.') }}" title="Domain details for {{ rtrim($domain->rr, '.') }}">{{ rtrim($domain->rr, '.') }}</a></li>
										@endforeach
									</ul>
								@else
									<div class="center">No Domain History for this IP</div>
								@endif
							</div>
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-md-6">
					<div class="col-xs-12 box-shadow buffer">
						<div class="col-xs-12">
							<h4>Malicious Domains ({{ count($open_dns_malicious_data) }} Entries)</h4>

							<div class="col-xs-12">
								@if (!empty($open_dns_malicious_data))
									<ul>
										@foreach ($open_dns_malicious_data as $domain)
											<li><a href="/domain/{{ $domain->name }}" title="Domain details for {{ $domain->name }}">{{ $domain->name }}</a></li>
										@endforeach
									</ul>
								@else
									<div class="center">No Malicious Domains for this IP</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		@else
			<div class="col-xs-12 center">
				<div class="col-xs-12">
					An OpenDNS Investigate API Key has not been provided.
				</div>
				<div class="col-xs-12">
					Please add an OpenDNS Investigate API Key on the <a href="/config">Configuration</a> page.
				</div>
			</div>
		@endif
	@endif
@stop
