@extends('layouts.app')
@section('title', '购物车')

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-header">我的购物车</div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select-all"></th>
                                <th>商品信息</th>
                                <th>单价</th>
                                <th>数量</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody class="product_list">
                            @foreach ($cartItems as $item)
                                <tr data-id="{{ $item->productSku->id }}">
                                    <td>
                                        <input type="checkbox" name="select" value="{{ $item->productSku->id }}"
                                            {{ $item->productSku->product->on_sale ? 'checked' : 'disabled' }}>
                                    </td>
                                    <td class="product_info">
                                        <div class="preview">
                                            <a target="_blank"
                                                href="{{ route('products.show', [$item->productSku->product_id]) }}">
                                                <img src="{{ $item->productSku->product->image_url }}">
                                            </a>
                                        </div>
                                        <div @if (!$item->productSku->product->on_sale)
                                            class="not_on_sale"
                            @endif>
                            <span class="product_title">
                                <a target="_blank"
                                    href="{{ route('products.show', [$item->productSku->product_id]) }}">{{ $item->productSku->product->title }}</a>
                            </span>
                            <span class="sku_title">{{ $item->productSku->title }}</span>
                            @if (!$item->productSku->product->on_sale)
                                <span class="warning">该商品已下架</span>
                            @endif
                </div>
                </td>
                <td><span class="price">￥{{ $item->productSku->price }}</span></td>
                <td>
                    <input type="text" class="form-control form-control-sm amount" @if (!$item->productSku->product->on_sale) disabled @endif name="amount"
                    value="{{ $item->amount }}">
                </td>
                <td>
                    <button class="btn btn-sm btn-danger btn-remove" id="cart-btn-remove">移除</button>
                </td>
                </tr>
                @endforeach
                </tbody>
                </table>
                <!-- 开始 -->
                <div>
                    <form class="form-horizontal" role="form" id="order-form">
                        <div class="form-group row">
                            <label class="col-form-label col-sm-3 text-md-right">选择收货地址</label>
                            <div class="col-sm-9 col-md-7">
                                <select class="form-control" name="address">
                                    @foreach ($addresses as $address)
                                        <option value="{{ $address->id }}">{{ $address->full_address }}
                                            {{ $address->contact_name }} {{ $address->contact_phone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-form-label col-sm-3 text-md-right">备注</label>
                            <div class="col-sm-9 col-md-7">
                                <textarea name="remark" class="form-control" rows="3" id="form-control"></textarea>
                            </div>
                        </div>

                             <!-- 优惠码开始 -->
                            <div class="form-group row">
                                <label class="col-form-label col-sm-3 text-md-right">优惠码</label>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control" name="coupon_code">
                                    <span class="form-text text-muted" id="coupon_desc"></span>
                                </div>
                                <div class="col-sm-3">
                                    <button type="button" class="btn btn-success" id="btn-check-coupon">检查</button>
                                    <button type="button" class="btn btn-danger" style="display: none;" id="btn-cancel-coupon">取消</button>
                                </div>
                            </div>
                            <!-- 优惠码结束 -->

                        <div class="form-group">
                            <div class="offset-sm-3 col-sm-3">
                                <button type="button" class="btn btn-primary btn-create-order"
                                    id="btn-create-order">提交订单</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- 结束 -->

            </div>
        </div>
    </div>
    </div>
@endsection

@section('scriptAfterJs')
    <script>
        $(document).ready(function() {
            //删除购物车商品
            $('#cart-btn-remove').click(function() {
                var skuId = $(this).closest('tr').data('id');
                console.log(skuId)
                swal({
                        title: '忍心要删除吗~',
                        icon: 'warning',
                        buttons: ['取消',
                            '确定'
                        ],
                        dangerMode: true
                    })
                    .then(willDelete => {
                        // 用户点击 确定 按钮，willDelete 的值就会是 true，否则为 false
                        if (!willDelete) {
                            return;
                        }
                        axios.delete('/cart/' + skuId).then(res => {
                            location.reload()
                        })
                    })
            })

            //全选/取消全选
            $('#select-all').change(function() {
                //prop 知道标签的状态
                var checked = $(this).prop('checked');
                // 获取所有 name=select 并且不带有 disabled 属性的勾选框
                // 对于已经下架的商品我们不希望对应的勾选框会被选中，因此我们需要加上 :not([disabled]) 这个条件
                $('input[name=select][type=checkbox]:not([disabled])').each(function(index, val) {
                    $(this).prop('checked', checked);
                })
            })


            //提交订单
            //监听提交按钮
            $('#btn-create-order').click(function() {
                //构建请求参数，将地址和备注到里面
                var req = {
                    address_id: $('#order-form').find('select[name=address]').val(),
                    items: [],
                    remrk: $('#form-control').val(),
                    coupon_code: $('input[name=coupon_code]').val(), // 从优惠码输入框中获取优惠码
                }

                // 遍历 <table> 标签内所有带有 data-id 属性的 <tr> 标签，也就是每一个购物车中的商品 SKU
                $('table tr[data-id]').each(function() {
                    var checkbox = $(this).find('input[name=select][type=checkbox]');

                    //去掉属性是 disabled 的
                    if (checkbox.prop('disabled') && !checkbox.prop('check')) {
                        return;
                    }

                    //获取当前数量输入框
                    var $input = $(this).find('input[name=amount]').val();
                    //如果输入框为0或不是一个数字，则跳过
                    if ($input <= 0 && isNaN($input)) {
                        return;
                    }

                    req.items.push({
                        'sku_id': checkbox.val(),
                        'amount': $input
                    })
                })
                axios.post('{{ route('orders.store') }}', req).then(res => {
                    console.log(res)
                    swal('订单提交成功', '', 'success');
                    location.href='{{route('orders.index')}}'
                }).catch(error=>{
                    if (error.response.status === 422) {
                        // http 状态码为 422 代表用户输入校验失败
                        var html = '<div>';
                        _.each(error.response.data.errors, function (errors) {
                        _.each(errors, function (error) {
                            html += error+'<br>';
                        })
                        });
                        html += '</div>';
                        swal({content: $(html)[0], icon: 'error'})
                    } else {
                        // 其他情况应该是系统挂了
                        swal('系统错误', '', 'error');
                    }
                })
            })

            //检查优惠券
            $('#btn-check-coupon').click(function(){
                //获取输入框的值
               var value = $('input[name=coupon_code]').val()
               if(!value){
                swal('请输入优惠券码','','warning')
                return
               }

               axios.get('/coupon_codes/'+encodeURIComponent(value)).then(res=>{
                   console.log(res)
                   if(res.status === 200){
                    $('#coupon_desc').text(res.data.description)
                    $('input[name=coupon_code]').prop('readonly', true); // 禁用输入框
                    $('#btn-cancel-coupon').show(); // 显示 取消 按钮
                    $('#btn-check-coupon').hide(); // 隐藏 检查 按钮
                   }
               }).catch(err=>{
                   // 如果返回码是 404，说明优惠券不存在
                   console.log(err.response.status)
                   if(err.response.status == 404){
                       swal('优惠码不存在','','error')
                   }
                   else if(err.response.status == 403){
                       swal(err.response.data.msg,'','error')
                   }else{
                       //其他错误
                       swal('系统内部错误','','error')
                   }
               })
            })
        })

    </script>


@endsection
