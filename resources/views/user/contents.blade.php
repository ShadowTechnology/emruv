@if($page == 'faq')
	<link href="{{ url('/public/css/sitestyle.css') }}" rel="stylesheet">
	<style type="text/css">
		.accordion_2 .card-header h5 {
    		font-size: 35px;
    	}
    	.card-body p {
		    font-size: initial;
		}
	</style>
	
	<div role="tablist" class="add_bottom_45 accordion_2" id="payment">
		<h5 class="mb-0">Contact: {{$help_contact}}</h5>
		@if(!empty($contents) && count($contents)>0)

		@foreach($contents as $k=>$v)
		<div class="card">

			<div class="card-header" role="tab">
				<h5 class="mb-0">
					<a data-toggle="collapse" href="#collapse{{$v->id}}_payment" aria-expanded="true"><i class="indicator ti-minus"></i>{{$v->faq_question}}</a>
				</h5>
			</div>

			<div id="collapse{{$v->id}}_payment" class="collapse show" role="tabpanel" data-parent="#payment">
				<div class="card-body">
					<p>{{$v->faq_answer}}</p>
				</div>
			</div>
		</div>
		<!-- /card -->
		@endforeach
		@endif
	</div>
@else 
{!! $contents !!}
@endif