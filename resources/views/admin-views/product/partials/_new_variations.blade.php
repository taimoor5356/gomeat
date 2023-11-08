

<div class="__bg-F8F9FC-card view_new_option mb-2">
    <div>
        <div >
            <div class="d-flex align-items-center justify-content-between mb-3">
                <label class="form-check form--check">
                    <span class="form-check-label">variant# {{$key+1}}</span>
                </label>
                <div>
                    <button type="button" class="btn btn-danger btn-sm delete_input_button"
                        onclick="removeOption(this)" title="Delete">
                        <i class="tio-add-to-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="row g-2">
            <div class="col-xl-4 col-lg-6">
                <label for="">Name</label>
                <!-- <input required name="choice_options[{{ $key }}][title]" required class="form-control"
                    type="text" onkeyup="new_option_name(this.value,{{ $key }})"
                    value="{{ $item['title'] }}"> -->

                    <select name="attribute_id[]" id="choice_attributes"
                        class="form-control js-select2-custom" >
                            <option value="{{explode('_', $item['name'])[1]}}" selected>{{$item['title']}}</option>
                            @foreach (\App\Models\Attribute::orderBy('name')->get() as $attribute)
                            <option value="{{ $attribute['id'] }}">{{ $attribute['name'] }}</option>
                            @endforeach
                    </select>
                    
            </div>

            <div class="col-xl-4 col-lg-6">
                <div class="form-group">
                    <label class="input-label text-capitalize d-flex alig-items-center"><span
                            class="line--limit-1">Selcetion Type</span>
                    </label>
                    <div class="resturant-type-group ">
                        

                        <label class="form-check form--check mr-2 mr-md-4">
                            <input class="form-check-input" type="radio" value="0"
                                {{ $item['multiselect'] == 0 ? 'checked' : '' }} name="choice_options[{{ $key }}][multiselect]"
                                id="multiselect{{ $key }}" >
                            <span class="form-check-label">
                                Single Selection
                            </span>
                        </label>

                        <label class="form-check form--check mr-2 mr-md-4">
                            <input class="form-check-input" type="radio" value="1"
                                name="choice_options[{{ $key }}][multiselect]" id="multiselect{{ $key }}"
                                {{ $item['multiselect'] == 1 ? 'checked' : '' }}
                                >
                            <span class="form-check-label">
                                Multiple Selection
                            </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div id="option_price_{{ $key }}">
            <div class="bg-white border rounded p-3 pb-0 mt-3">
                    <div id="option_price_view_{{ $key }}">
                {{--@if (isset($item['options']))--}}
                    @foreach ($item['options'] as $key_value => $value)
                        <div class="row add_new_view_row_class mb-3 position-relative pt-3 pt-md-0">
                            <div class="col-md-4 col-sm-6">
                                <label for="">Option Name</label>
                                <input class="form-control" required type="text"
                                    name="choice_options[{{ $key }}][options][{{ $key_value }}][type]"
                                    value="{{ $value['type'] }}">
                            </div>
                            <div class="col-md-4 col-sm-6">
                                <label for="">Additional Price</label>
                                <input class="form-control" required type="number" min="0" step="0.01"
                                    name="choice_options[{{ $key }}][options][{{ $key_value }}][price]"
                                    value="{{ $value['price'] }}">
                                <input class="form-control" required type="hidden"
                                    name="choice_options[{{ $key }}][options][{{ $key_value }}][stock]"
                                    value=100000>
                            </div>
                            <div class="col-sm-2 max-sm-absolute">
                                <label class="d-none d-md-block">&nbsp;</label>
                                <div class="mt-1">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)"
                                        title="Delete">
                                        <i class="tio-add-to-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    @endforeach
                {{--@endif--}}
            </div>
                <div class="row mt-3 p-3 mr-1 d-flex" id="add_new_button_{{ $key }}">
                    <button type="button"
                        class="btn btn--primary btn-outline-primary"onclick="add_new_row_button({{ $key }})">Add New Option</button>
                </div>

            </div>




        </div>
    </div>
</div>
