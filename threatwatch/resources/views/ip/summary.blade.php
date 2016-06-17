@extends('template')

@section('title', 'IP Summary')

@section('script')
<script>
	(function($){
	 	$.fn.extend({
		    // Pass the options variable to the function
			percentcircle: function(options) {
				// Set the default values, use comma to separate the settings, example:
				var defaults = {
				        animate : true,
						diameter : 100,
						guage: 2,
						coverBg: '#fff',
						bgColor: '#efefef',
						fillColor: '#a94442',
						percentSize: '25px',
						percentWeight: 'normal'
					},
					styles = {
					    cirContainer : {
						    'margin': '10px auto',
						    'width':defaults.diameter,
							'height':defaults.diameter
						},
						cir : {
						    'position': 'relative',
						    'text-align': 'center',
						    'width': defaults.diameter,
						    'height': defaults.diameter,
						    'border-radius': '100%',
						    'background-color': defaults.bgColor,
						    'background-image' : 'linear-gradient(91deg, transparent 50%, '+defaults.bgColor+' 50%), linear-gradient(90deg, '+defaults.bgColor+' 50%, transparent 50%)'
						},
						cirCover: {
							'position': 'relative',
						    'top': defaults.guage,
						    'left': defaults.guage,
						    'text-align': 'center',
						    'width': defaults.diameter - (defaults.guage * 2),
						    'height': defaults.diameter - (defaults.guage * 2),
						    'border-radius': '100%',
						    'background-color': defaults.coverBg
						},
						percent: {
							'display':'block',
							'width': defaults.diameter,
						    'height': defaults.diameter,
						    'line-height': defaults.diameter + 'px',
						    'vertical-align': 'middle',
						    'font-size': defaults.percentSize,
						    'font-weight': defaults.percentWeight,
						    'color': defaults.fillColor
	                    }
					};

				var that = this,
						template = '<div><div class="ab"><div class="cir"><span class="perc">--percentage--</span></div></div></div>',
						options =  $.extend(defaults, options)

				function init(){
					that.each(function(){
						var $this = $(this),
						    //we need to check for a percent otherwise set to 0;
							perc = Math.round($this.data('percent')), //get the percentage from the element
							deg = perc * 3.6,
							stop = options.animate ? 0 : deg,
							$chart = $(template.replace('--percentage--',perc));
							//set all of the css properties forthe chart
							$chart.css(styles.cirContainer).find('.ab').css(styles.cir).find('.cir').css(styles.cirCover).find('.perc').css(styles.percent);

						$this.append($chart); //add the chart back to the target element
						setTimeout(function(){
							animateChart(deg,parseInt(stop),$chart.find('.ab')); //both values set to the same value to keep the function from looping and animating
						},250)
		    		});
				}

				var animateChart = function (stop,curr,$elm){
					var deg = curr;
					if(curr <= stop){
						if (deg>=180){
							$elm.css('background-image','linear-gradient(' + (90+deg) + 'deg, transparent 50%, '+options.fillColor+' 50%),linear-gradient(90deg, '+options.fillColor+' 50%, transparent 50%)');
						}else{
							$elm.css('background-image','linear-gradient(' + (deg-90) + 'deg, transparent 50%, '+options.bgColor+' 50%),linear-gradient(90deg, '+options.fillColor+' 50%, transparent 50%)');
						}
						curr ++;
						setTimeout(function(){
							animateChart(stop,curr,$elm);
						},1);
					}
				};

				init(); //kick off the goodness
			}
		});

	})(jQuery);
</script>
@stop

@section('style')
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
		<span style="float: right">
			@if (!empty(getSetting('SMC_IP')))
				<a class="btn btn-primary" href="/host-group-tree/?ip_address={{ $ip_address }}">Add to Host Group</a>
			@else
				A Stealthwatch SMC has not been configured.<br><a href="/config">Click here to add one.</a>
			@endif
		</span>
		<h1>IP Summary for <small>{{ $ip_address }}</small></h1>
	</div>

	@if (session('success'))
		 <div class="alert alert-success">{{ session('success') }}</div>
	@endif

	@if (count($errors) > 0)
	    <div class="alert alert-danger">
	        <ul>
	            @foreach ($errors->all() as $error)
	                <li>{{ $error }}</li>
	            @endforeach
	        </ul>
	    </div>
	@else
		@if (!empty(getSetting('SMC_IP')))
			<div class="col-xs-12">
				<h3>Host Groups</h3>

				<div class="col-xs-12 col-md-6">
					<div class="col-xs-12 box-shadow buffer">
						<div class="col-xs-12 buffer">
							@if (!empty($hostgroups))
								<ul>
									@foreach ($hostgroups as $hostgroup)
										<li>{{ $hostgroup }}</li>
									@endforeach
								</ul>
							@else
								<div class="center">IP not in FlowCollector cache</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		@endif

		<div class="col-xs-12">
			<h3>Blacklists</h3>

			<div class="col-xs-12 col-md-6">
				<div class="col-xs-12 box-shadow buffer">
					<div class="col-xs-12 buffer">
						<ul class="fa-ul">
							@foreach ($blacklists as $blacklist)
								@if (in_array($blacklist->id, $blacklisted))
									<li><i class="fa-li fa fa-check-circle" style="color: #d9534f"></i>{{ $blacklist->name }}</li>
								@else
									<li><i class="fa-li fa fa-times-circle" style="color: #5cb85c"></i>{{ $blacklist->name }}</li>
								@endif
							@endforeach
						</ul>
					</div>
				</div>
			</div>
		</div>

		@if (!empty(getSetting('OPENDNS_KEY')))
			<div class="col-xs-12">
				<h3>OpenDNS</h3>

				<div class="col-xs-12 col-md-6">
					<div class="col-xs-12 box-shadow buffer">
						<div class="col-xs-12">
							<h4>90 Day Domain History ({{ (!empty($open_dns_history_data)) ? $open_dns_history_data->features->rr_count : "0" }} Entries)</h4>

							<div class="col-xs-12">
								<?php $i = 0; ?>

								@if (!empty($open_dns_history_data->rrs))
									<ul>
										@foreach ($open_dns_history_data->rrs as $domain)
											<li><a href="/domain/{{ rtrim($domain->rr, '.') }}" title="Domain Details for {{ rtrim($domain->rr, '.') }}">{{ rtrim($domain->rr, '.') }}</a></li>

											<?php
												$i++;

												if ($i >= 25) {
													break;
												}
											?>
										@endforeach
									</ul>

									@if ($i >= 25)
										<div class="center"><a href="/ip-details/{{ $ip_address }}">See More</a></div>
									@endif

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
								<?php $i = 0; ?>

								@if (!empty($open_dns_malicious_data))
									<ul>
										@foreach ($open_dns_malicious_data as $domain)
											<li><a href="/domain/{{ $domain->name }}" title="Domain Details for {{ $domain->name }}">{{ $domain->name }}</a></li>

											<?php
												$i++;

												if ($i >= 25) {
													break;
												}
											?>
										@endforeach
									</ul>

									@if ($i >= 25)
										<div class="center"><a href="/ip-details/{{ $ip_address }}">See More</a></div>
									@endif

								@else
									<div class="center">No Malicious Domains for this IP</div>
								@endif
							</div>
						</div>
					</div>
				</div>
			</div>
		@endif

		@if (!empty(getSetting('OPENDNS_KEY')) && !empty($threatgrid_data->totalResults))
			<div class="col-xs-12">
				<h3>ThreatGRID ({{ $threatgrid_data->totalResults }} Results)</h3>

				<?php $i = 0 ?>

				@foreach ($threatgrid_data->samples as $sample)
					@if (!empty($sample->magicType))
						<div class="col-xs-12 col-md-6">
							<div class="col-xs-12 box-shadow buffer">
								<div class="col-xs-12 center">
									<h4>Threat Score</h4>
									<div class="circle-chart" data-percent="{{ $sample->threatScore }}"></div>
								</div>
								<div class="col-xs-12">
									<table class="table table-striped">
										<tr>
											<td>Threat Type</td>
											<td>{{ $sample->magicType }}</td>
										</tr>
										@if (count($sample->avresults) > 0)
											<tr>
												<td>AV Results</td>
												<td>
													<ul>
														@foreach ($sample->avresults as $avresult)
															<li>{{ $avresult->signature }} ({{ $avresult->product }})</li>
														@endforeach
													</ul>
												</td>
											</tr>
										@endif
										<tr>
											<td>Size</td>
											<td>{{ $sample->size }} Bytes</td>
										</tr>
										<tr>
											<td>SHA256 Hash</td>
											<td>{{ $sample->sha256 }}</td>
										</tr>
										<tr>
											<td>SHA1 Hash</td>
											<td>{{ $sample->sha1 }}</td>
										</tr>
										<tr>
											<td>MD5 Hash</td>
											<td>{{ $sample->md5 }}</td>
										</tr>
										<tr>
											<td>First Seen</td>
											<td>{{ date('M jS, Y', ($sample->firstSeen / 1000)) }}</td>
										</tr>
										<tr>
											<td>Last Seen</td>
											<td>{{ date('M jS, Y', ($sample->lastSeen / 1000)) }}</td>
										</tr>
									</table>
								</div>
							</div>
						</div>

						<?php
							$i++;

							if ($i % 2 == 0) {
								echo "<div class=\"clearfix\"></div>";
							}
						?>
					@endif
				@endforeach
			</div>

			<script>
				$('.circle-chart').percentcircle();
			</script>
		@endif
	@endif
@stop
