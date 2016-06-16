@extends('template')

@section('title', 'Available Host Groups')

@section('script')
<script>
$(function() {
	$('#add-host-group-submit').click(function(e) {
		$('#add-host-group-form').submit();
	})
});

function updateAddHostGroup(host_group_id) {
	$('#parent_group_id').val(host_group_id);
}

$.fn.extend({
	treed: function (o) {

		var openedClass = 'glyphicon-minus-sign';
		var closedClass = 'glyphicon-plus-sign';

		if (typeof o != 'undefined') {
			if (typeof o.openedClass != 'undefined') {
				openedClass = o.openedClass;
			}
			if (typeof o.closedClass != 'undefined') {
				closedClass = o.closedClass;
			}
		};

		// Initialize each of the top levels
		var tree = $(this);

		tree.addClass("tree");
		tree.find('li').has("ul").each(function () {

			var branch = $(this); //li with children ul

			branch.prepend("<i class='indicator glyphicon " + closedClass + "'></i>");
			branch.addClass('branch');
			branch.on('click', function (e) {
				if (this == e.target) {
					var icon = $(this).children('i:first');
					icon.toggleClass(openedClass + " " + closedClass);
					$(this).children().children().toggle();
				}
			})

			branch.children().children().toggle();

		});

		// Fire event from the dynamically added icon
		tree.find('.branch .indicator').each(function() {
			$(this).on('click', function () {
				$(this).closest('li').click();
			});
		});
	}
});
</script>
@stop

@section('style')
.tree, .tree ul {
	margin:0;
	padding:0;
	list-style:none
}
.tree ul {
	margin-left:1em;
	position:relative
}
.tree ul ul {
	margin-left:.5em
}
.tree ul:before {
	content:"";
	display:block;
	width:0;
	position:absolute;
	top:0;
	bottom:0;
	left:0;
	border-left:1px solid
}
.tree li {
	margin:0;
	padding:0 1em;
	line-height:2em;
	color:#369;
	position:relative
}
.tree ul li:before {
	content:"";
	display:block;
	width:10px;
	height:0;
	border-top:1px solid;
	margin-top:-1px;
	position:absolute;
	top:1em;
	left:0
}
.tree ul li:last-child:before {
	background:#fff;
	height:auto;
	top:1em;
	bottom:0
}
.indicator {
	margin-right:5px;
}
.tree li a {
	text-decoration: none;
	color:#369;
}
.tree li button, .tree li button:active, .tree li button:focus {
	text-decoration: none;
	color:#369;
	border:none;
	background:transparent;
	margin:0px 0px 0px 0px;
	padding:0px 0px 0px 0px;
	outline: 0;
}
.pointer {
	cursor: pointer;
}
@stop

@section('content')

	<div class="page-header">
		<h1>Available Host Groups on <small>{{ getSetting('SMC_IP') }}</small></h1>
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
		<div class="col-xs-12">
			{!! $host_group_tree !!}

			<script>
				$('.tree').treed();
			</script>
		</div>
	@endif

<!-- Add Host Group Modal -->
<div class="modal fade add-host-group" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Add Host Group</h4>
			</div>
			<div class="modal-body">
				<form method="POST" action="/add-host-group" accept-charset="UTF-8" id="add-host-group-form" role="form" enctype="multipart/form-data">
					<input type="hidden" name="_token" value="{{ csrf_token() }}">
					<input type="hidden" name="host_ip_address" value="{{ $ip_address }}">
					<input type="hidden" name="parent_group_id" value="0" id="parent_group_id">
					<div class="form-group">
						<input class="form-control input-sm" placeholder="Host Group Name" name="host_group_name" type="text">
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary" id="add-host-group-submit">Add</button>
			</div>
		</div>
	</div>
</div>

@stop
