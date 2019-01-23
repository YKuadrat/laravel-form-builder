@php
if (!is_array($attributes)) $attributes = [];
$isDataRequestByAjax = is_array($options) ? false : true;
$url = $options;

// SET DEFAULT CLASS
$attributes['elOptions']['class'] = 'select2 form-control';

// SET DEFAULT ID
$attributes['elOptions']['id'] = $attributes['elOptions']['id'] ?? 'select2-' . $name;

// SET DEFAULT FOR FORMATTED SELECT2 DATA FORMAT
$attributes['text'] = $attributes['text'] ?? 'obj.name';
$attributes['key'] = isset($attributes['key']) ? "obj.".$attributes['key'] : 'obj.id';

// CALLING SETUP DEFAULT CONFIG
$config = FormBuilderHelper::setupDefaultConfig($name, $attributes, true);
$config['pluginOptions'] = $attributes['pluginOptions'] ?? [];
$config['ajaxParams'] = $attributes['ajaxParams'] ?? [];

// FORMATTING TEXT BY TEMPLATE 
// if (is_array($config['text'])) {
// 	$text = null;
// 	foreach ($config['text']['field'] as $field) {
// 		$text = str_replace("<<$field>>", "'+ obj.$field +'", $text ?? $config['text']['template']);
// 	}
// str_replace_array('<<field>>', $config['text']['field'], $config['text']['template']); // Laravel str helper method 
// 	$config['text'] = "'" . $text . "'";
// }
@endphp

<div class="form-group {{ !$errors->has($name) ?: 'has-danger' }}">
	@if ($config['useLabel'])
	<div class="row">
		<div class="{{ $config['labelContainerClass'] }}">
			<label class="col-form-label">
				{!! $config['labelText'] !!}
			</label>
		</div>
		<div class="{{ $config['inputContainerClass'] }}">
	@endif

			<select name="{{ isset($config['pluginOptions']['multiple']) && $config['pluginOptions']['multiple'] ? $name . '[]' : $name }}" <?= $config['htmlOptions'] ?>>
				@if (!$isDataRequestByAjax)
					<option></option>
					@foreach ($options as $key => $option)
		                <option value="{{ $key }}">{{ $option }}</option>
					@endforeach
				@endif
            </select>

            <div class="error-container">
	            @if($errors->has($name))
	            <div class="form-control-feedback">{{ $errors->first($name) }}</div>
				@endif
            </div>

            {!! @$config['info'] !!}

	@if ($config['useLabel'])
		</div>
	</div>
	@endif
</div>

@section('fb-select2-resource')
<link rel="stylesheet" type="text/css" href="{{ asset('vendor/form-builder/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('vendor/form-builder/plugins/select2/select2.min.js') }}">
@endsection

@push('additional-js')
<script type="text/javascript">
	$(document).ready(function() {
		var select2Options_{{$name}} = Object.assign({
				placeholder: "{{ $config['elOptions']['placeholder'] }}",
		    	allowClear: true,//
			}, {!! json_encode($config['pluginOptions']) !!}),
			select2val_{{$name}} = {!! !is_array($value) ? json_encode([$value]) : json_encode($value) !!}



		// IF THE SELECT2 IS REQUEST DATA BY AJAX
		@if ($isDataRequestByAjax)
		select2Options_{{$name}}.ajax = {
			url: "{{ $url }}",
			data: function(params) {
				var data = {
					q: params.term
				}
				@foreach ($config['ajaxParams'] as $key => $val)
					data.{{$key}} = {!! $val !!}
				@endforeach
				return data
			},
			processResults: function (data) {
				var result = {},
					isPaginate = data.hasOwnProperty('data'),
					isSimplePaginate = !data.hasOwnProperty('last_page');

	                result.results = $.map(isPaginate ? data.data : data, function (obj) {                                
	                    return {id: {{ $config['key'] }}, text: {!! $config['text'] !!} }
	                })

	                if (isPaginate) {
	                	result.pagination = {
		                	more: isSimplePaginate ? data.next_page_url !== null : data.current_page < data.last_page
		                }
	                }

				return result;
			}
		}
		@endif



		// FOR SELECT2 DROPDOWNPARENT
		@if (isset($config['pluginOptions']['dropdownParent']))
		    select2Options_{{$name}}.dropdownParent = $('<?= $config['pluginOptions']["dropdownParent"] ?>')
		@endif

	    $('#{{ $config['elOptions']['id'] }}').select2(select2Options_{{$name}}).val(select2val_{{$name}}).trigger('change');
	})
</script>
@endpush