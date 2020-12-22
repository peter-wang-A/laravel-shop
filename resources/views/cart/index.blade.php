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
        })
    </script>


@endsection
