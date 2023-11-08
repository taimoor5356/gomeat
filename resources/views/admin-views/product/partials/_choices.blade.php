@foreach($choice_options as $key=>$choice)
{{-- @dump($choice['options']) --}}
<div class="row">
    <div class="col-md-3">
        <input type="hidden" name="choice_no[]" value="{{$choice_no[$key]}}">
        <input type="text" class="form-control" name="choice[]" value="{{$choice['title']}}"
        placeholder="{{translate('messages.choice_title')}}" readonly>
    </div>
    {{-- @foreach ($choice['options'] as $option) --}}
            <div class="col-md-3">
                <input type="text" class="form-control call-update-sku" name="choice_options_{{$choice_no[$key]}}[]" data-role="tagsinput"
                    {{-- value="{{$option['type'].','}}"> --}}
                    value="@foreach($choice['options'] as $c) {{$c['type'].','}} @endforeach">
            </div>
    {{-- @endforeach --}}
        </div>
@endforeach
