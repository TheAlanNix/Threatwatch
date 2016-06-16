@extends('template')

@section('title', 'Domain Details')

@section('script')
<script src="https://code.highcharts.com/highcharts.js"></script>
@stop

@section('style')
.progress {
	position: relative;
}

.progress span {
	position: absolute;
	display: block;
	width: 100%;
	color: black;
}

table {
	table-layout: fixed;
}

tr td:first-child {
	width: 25%;
}

td {
	overflow: hidden;
	text-overflow: ellipsis;
}
@stop

@section('content')
<div class="page-header">
	<h1>Domain Details for <small>{{ $domain }}</small></h1>
</div>

@if (!empty(getSetting('OPENDNS_KEY')))
	<div class="col-xs-12">
		<h3>OpenDNS</h3>

		<div class="col-xs-12 col-md-6">
			<div class="col-xs-12 box-shadow buffer">
				<div class="col-xs-12">
					<h4>Domain Status</h4>

					<table class="table">
						<tr>
							<td>Status</td>
							<td>
								@if ($open_dns_domain_status_data->status == 1)
									BENIGN
								@elseif ($open_dns_domain_status_data->status == -1)
									MALICIOUS
								@else
									UNCLASSIFIED
								@endif
							</td>
						</tr>

						@if ($open_dns_domain_status_data->status == 1)
							<tr>
								<td>Content Categories</td>
								<td>
									<ul>
										@foreach ($open_dns_domain_status_data->content_categories as $category)
											<li>{{ $open_dns_categories->$category }}</li>
										@endforeach
									</ul>
								</td>
							</tr>
						@elseif	($open_dns_domain_status_data->status == -1)
							<tr>
								<td>Security Categories</td>
								<td>
									<ul>
										@foreach ($open_dns_domain_status_data->security_categories as $category)
											<li>{{ $open_dns_categories->$category }}</li>
										@endforeach
									</ul>
								</td>
							</tr>
						@endif

					</table>
				</div>
			</div>

			<div class="col-xs-12 box-shadow buffer">
				<div class="col-xs-12">
					<h4>WHOIS</h4>

					@if (!empty($open_dns_domain_whois_data))
						<table class="table">
							<tr>
								<td>Registrar</td>
								<td>{{ $open_dns_domain_whois_data->registrarName }}</td>
							</tr>
							<tr>
								<td>Organization</td>
								<td>{{ $open_dns_domain_whois_data->registrantOrganization }}</td>
							</tr>
							<tr>
								<td>Address</td>
								<td>
									{{ $open_dns_domain_whois_data->registrantStreet[0] }}<br>
									@if (isset($open_dns_domain_whois_data->registrantStreet[1]))
										{{ $open_dns_domain_whois_data->registrantStreet[1] }}<br>
									@endif
									{{ $open_dns_domain_whois_data->registrantCity }}, {{ $open_dns_domain_whois_data->registrantState }} {{ $open_dns_domain_whois_data->registrantPostalCode }}<br>
									{{ $open_dns_domain_whois_data->registrantCountry }}
								</td>
							</tr>
							<tr>
								<td>Email</td>
								<td><a href="mailto:{{ $open_dns_domain_whois_data->registrantEmail }}">{{ $open_dns_domain_whois_data->registrantEmail }}</a></td>
							</tr>
							<tr>
								<td>Telephone</td>
								<td>{{ $open_dns_domain_whois_data->registrantTelephone }}</td>
							</tr>
							<tr>
								<td>Name Servers</td>
								<td>
									<ul>
										@foreach ($open_dns_domain_whois_data->nameServers as $name_server)
											<li>{{ $name_server }}</li>
										@endforeach
									</ul>
								</td>
							</tr>
						</table>
					@else
						<div class="col-xs-12 center">No WHOIS data for this Domain</div>
					@endif

				</div>
			</div>
		</div>

		<div class="col-xs-12 col-md-6">
			<div class="col-xs-12 box-shadow buffer">
				<div class="col-xs-12">
					<h4>Security Profile</h4>

					<?php

						$dga_score		= null;
						$perplexity		= null;
						$securerank2	= null;
						$asn_score		= null;
						$prefix_score	= null;

						if (isset($open_dns_domain_security_data->dga_score))
							$dga_score = $open_dns_domain_security_data->dga_score * -1;

						if (isset($open_dns_domain_security_data->perplexity))
							$perplexity = $open_dns_domain_security_data->perplexity * 100;

						if (isset($open_dns_domain_security_data->securerank2))
							$securerank2 = (($open_dns_domain_security_data->securerank2 * -1) + 100) / 2;

						if (isset($open_dns_domain_security_data->asn_score))
							$asn_score = $open_dns_domain_security_data->asn_score * -1;

						if (isset($open_dns_domain_security_data->prefix_score))
							$prefix_score = $open_dns_domain_security_data->prefix_score * -1;

						$series_data = null;

						if (isset($open_dns_domain_security_data->geodiversity)) {
							foreach ($open_dns_domain_security_data->geodiversity as $country) {
								$series_data .= "{name: '" . $country[0] . "', y: " . ($country[1] * 100) ."},";
							}
						}

					?>

					<div class="col-xs-12">

						@if (!is_null($securerank2))
						<h5>SecureRank2</h5>
						<div>
							<span style="float: right"><small>Suspicious</small></span>
							<span><small>Benign</small></span>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{{ $securerank2 }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $securerank2 }}%">
								<span>{{ round($securerank2, 2) }}</span>
							</div>
						</div>
						@endif

						@if (!is_null($dga_score))
						<h5>Domain Generation Algorithm</h5>
						<div>
							<span style="float: right"><small>Suspicious</small></span>
							<span><small>Benign</small></span>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{{ $dga_score }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $dga_score }}%">
								<span>{{ round($dga_score, 2) }}</span>
							</div>
						</div>
						@endif

						@if (!is_null($asn_score))
						<h5>ASN Reputation</h5>
						<div>
							<span style="float: right"><small>Suspicious</small></span>
							<span><small>Benign</small></span>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{{ $asn_score }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $asn_score }}%">
								<span>{{ round($asn_score, 2) }}</span>
							</div>
						</div>
						@endif

						@if (!is_null($prefix_score))
						<h5>IP Prefix Reputation</h5>
						<div>
							<span style="float: right"><small>Suspicious</small></span>
							<span><small>Benign</small></span>
						</div>
						<div class="progress">
							<div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="{{ $prefix_score }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $prefix_score }}%">
								<span>{{ round($prefix_score, 2) }}</span>
							</div>
						</div>
						@endif
					</div>

					@if (isset($open_dns_domain_security_data->geodiversity))
					<div id="geodiversity" class="col-xs-12" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>

					<script>
						$(function () {
						    $('#geodiversity').highcharts({
						        chart: {
						            plotBackgroundColor: null,
						            plotBorderWidth: null,
						            plotShadow: false,
						            type: 'pie'
						        },
						        title: {
						            text: 'Client Distribution by Country',
						            style: { "font-family" : "Lato"}
						        },
						        tooltip: {
						            pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
						        },
						        plotOptions: {
						            pie: {
						                allowPointSelect: true,
						                cursor: 'pointer',
						                dataLabels: {
						                    enabled: true,
						                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
						                    style: {
						                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
						                    }
						                }
						            }
						        },
						        series: [{
						            name: 'Country',
						            colorByPoint: true,
						            data: [<?= $series_data ?>]
						        }]
						    });
						});
					</script>
					@endif

					<!--<pre>
						<div class="col-xs-12">
					    	{{ print_r($open_dns_domain_security_data) }}
						</div>
					</pre>-->

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

@stop
