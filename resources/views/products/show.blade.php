@extends('layouts.app')
@section('title', $product->title)


@section('content')
<div class="row">
    <div class="col-lg-10 offset-lg-1">
    <div class="card">
      <div class="card-body product-info">
        <div class="row">
          <div class="col-5">
            <img class="cover" src="{{ $product->image_url }}" alt="">
          </div>
          <div class="col-7">
            <div class="title">{{ $product->title }}</div>
            <div class="price"><label>价格</label><em>￥</em><span id="productPrice">{{ $product->price }}</span></div>
            <div class="sales_and_reviews">
              <div class="sold_count">累计销量 <span class="count">{{ $product->sold_count }}</span></div>
              <div class="review_count">累计评价 <span class="count">{{ $product->review_count }}</span></div>
              <div class="rating" title="评分 {{ $product->rating }}">评分 <span class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}</span></div>
            </div>
            <div class="skus">
              <template>
                <label>颜色:</label>
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  @foreach($product->skus as $sku)
                  @if($sku->sku_category == 1)
                    <label
                    class="btn sku-btn"
                    data-price="{{$sku->price}}"
                    data-stock="{{$sku->stock}}"
                    data-toggle="tooltip"
                    title="{{ $sku->description }}"
                    data-placement="bottom">
                      <input  type="radio" name="skus"  autocomplete="off" value="{{ $sku->id }}"> {{ $sku->title }}
                    </label>
                    @endif
                  @endforeach
                </div>
              </template>
              <template>
                <label>尺码:</label>
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  @foreach($product->skus as $sku)
                  @if($sku->sku_category == 2)
                    <label
                    class="btn sku-btn"
                    data-price="{{$sku->price}}"
                    data-stock="{{$sku->stock}}"
                    data-toggle="tooltip"
                    title="{{ $sku->description }}"
                    data-placement="bottom">
                      <input  type="radio" name="skus"  autocomplete="off" value="{{ $sku->id }}"> {{ $sku->title }}
                    </label>
                    @endif
                  @endforeach
                </div>
              </template>

            </div>
            <div class="cart_amount">
                <label>数量</label>
                <input type="text" class="form-control form-control-sm" value="1">
                <span>件</span>
                <span class="stock" id="productStok"></span>
            </div>
            <div class="buttons">
                @if($favored)
                <button class="btn btn-danger btn-disfavor" id="btn-disfavor">取消收藏</button>
              @else
                <button class="btn btn-success btn-favor" id="btn-favor">❤ 收藏</button>
              @endif
              <button class="btn btn-primary btn-add-to-cart">加入购物车</button>
            </div>
          </div>
        </div>
        <div class="product-detail">
          <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
              <a class="nav-link active" href="#product-detail-tab" aria-controls="product-detail-tab" role="tab" data-toggle="tab" aria-selected="true">商品详情</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#product-reviews-tab" aria-controls="product-reviews-tab" role="tab" data-toggle="tab" aria-selected="false">用户评价</a>
            </li>
          </ul>
          <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="product-detail-tab">
              {!! $product->discription !!}
            </div>
            <div role="tabpanel" class="tab-pane" id="product-reviews-tab">
            </div>
          </div>
        </div>
      </div>
    </div>
    </div>
    </div>
@endsection

@section('scriptAfterJs')
    <script>
        var product = {!! json_encode($product) !!}
        var productPrice = Number(product.price);
        var isFavor = Boolean({{$favored}})


        $(document).ready(function(){
            //选中默认属性
            var skuPrice = Number($('.sku-btn').data('price'));
            if(skuPrice === productPrice){
                $('.sku-btn').addClass('active');
               }
            //点击属性修改价格
            $('.sku-btn').click(function(event){
                $('[data-toggle="tooltip"]').tooltip({trigger: 'hover'});
                $('#productPrice').text($(this).data('price'));
                $('#productStok').text('库存：'+$(this).data('stock')+' 件');
            })

            //收藏
                $('#btn-favor').click(function(){
                    location.reload()
                        axios.post('{{route('products.favor',['product'=>$product->id])}}').then(res=>{
                            if(res.data.code==200){
                                swal(res.data.msg, '', 'success').then(()=>{
                                    location.reload()
                                });

                            }
                        }).catch(res=>{
                            // 如果返回码是 401 代表没登录
                            if (res.response && res.response.status === 500) {
                                location.href='/login'
                            } else if (res.response && (res.response.data.msg || res.response.data.message)) {
                                // 其他有 msg 或者 message 字段的情况，将 msg 提示给用户
                                swal(res.response.data.msg ? res.response.data.msg : res.response.data.message, '', 'error');
                            }  else {
                                // 其他情况应该是系统挂了
                                swal('系统错误', '', 'error');
                            }
                        })
                    })
                    //取消收藏
                    $('#btn-disfavor').click(function(){
                                axios.post('{{route('products.disfavor',['product'=>$product->id])}}').then(res=>{
                                    if(res.data.code==200){
                                        swal(res.data.msg, '', 'success').then(()=>{
                                            location.reload()
                                        });

                                    }
                                })
                            })
        })
    </script>
@endsection
