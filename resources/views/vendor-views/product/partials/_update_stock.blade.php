<div class="card-header">
    <h4>Stock</h4>
    <input name="product_id" value="{{$product['id']}}" style="display: none">
</div>
<div class="card-body">
    <div class="form-group">
        <div class="row">
            <div class="col-md-12 mt-2 mb-2">
                <div class="variant_combination" id="variant_combination">
                    @include('vendor-views.product.partials._edit-combinations',['combinations'=>json_decode($product['variations'],true),'stock'=>config('module.'.$product->module->module_type)['stock']])
                </div>
                <div class="col-md-12" id="quantity">
                    <label
                        class="control-label"></label>
                        <label class="input-label" for="total_stock">{{translate('messages.total_stock')}}</label>                                
                        <input type="number" class="form-control" name="current_stock" value="{{$product->stock}}" id="quantity" {{count(json_decode($product['variations'],true)) > 0 ? 'readonly' : ""}}>
                </div>
            </div>
            
        </div>
    </div>
    <br>
</div>