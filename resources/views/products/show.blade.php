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
            <div class="title">{{ $product->long_title }}</div>


             <!-- 众筹商品模块开始 -->
            @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)
            <div class="crowdfunding-info">
            <div class="have-text">已筹到</div>
            <div class="total-amount"><span class="symbol">￥</span>{{ $product->crowdfunding->total_amount }}</div>
            <!-- 这里使用了 Bootstrap 的进度条组件 -->
            <div class="progress">
                <div class="progress-bar progress-bar-success progress-bar-striped"
                role="progressbar"
                aria-valuenow="{{ $product->crowdfunding->percent }}"
                aria-valuemin="0"
                aria-valuemax="100"
                style="min-width: 1em; width: {{ min($product->crowdfunding->percent, 100) }}%">
                </div>
            </div>
            <div class="progress-info">
                <span class="current-progress">当前进度：{{ $product->crowdfunding->percent }}%</span>
                <span class="float-right user-count">{{ $product->crowdfunding->user_count }}名支持者</span>
            </div>
            <!-- 如果众筹状态是众筹中，则输出提示语 -->
            @if ($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
            <div>此项目必须在
                <span class="text-red">{{ $product->crowdfunding->end_at->format('Y-m-d H:i:s') }}</span>
                前得到
                <span class="text-red">￥{{ $product->crowdfunding->target_amount }}</span>
                的支持才可成功，
                <!-- Carbon 对象的 diffForHumans() 方法可以计算出与当前时间的相对时间，更人性化 -->
                筹款将在<span class="text-red">{{ $product->crowdfunding->end_at->diffForHumans(now()) }}</span>结束！
            </div>
            @endif
            </div>
            @else
                 <!-- 原普通商品模块开始 -->
            <div class="price"><label>价格</label><em>￥</em><span id="productPrice">{{ $product->price }}</span></div>
            <div class="sales_and_reviews">
              <div class="sold_count">累计销量 <span class="count">{{ $product->sold_count }}</span></div>
              <div class="review_count">累计评价 <span class="count">{{ $product->review_count }}</span></div>
              <div class="rating" title="评分 {{ $product->rating }}">评分 <span class="count">{{ str_repeat('★', floor($product->rating)) }}{{ str_repeat('☆', 5 - floor($product->rating)) }}</span></div>
            <!-- 原普通商品模块结束 -->
            @endif
            </div>


            <div class="skus">
              <template>
                <label>颜色:</label>
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  @foreach($product->skus as $sku)
                    <label
                    class="btn sku-btn"
                    data-price="{{$sku->price}}"
                    data-stock="{{$sku->stock}}"
                    data-toggle="tooltip"
                    title="{{ $sku->description }}"
                    data-placement="bottom">
                      <input  type="radio" name="skus"  autocomplete="off" value="{{ $sku->id }}"> {{ $sku->title }}
                    </label>
                  @endforeach
                </div>
              </template>
            </div>
            <div class="cart_amount">
                <label>数量</label>
                <input type="text" id="amount" class="form-control form-control-sm" value="1">
                <span>件</span>
                <span class="stock" id="productStok"></span>
            </div>

            <div class="buttons">
                @if($favored)
                <button class="btn btn-danger btn-disfavor" id="btn-disfavor">取消收藏</button>
              @else
                <button class="btn btn-success btn-favor" id="btn-favor">❤ 收藏</button>
              @endif

                <!-- 众筹商品下单按钮开始 -->
                @if($product->type === \App\Models\Product::TYPE_CROWDFUNDING)
                @if(Auth::check())
                    @if($product->crowdfunding->status === \App\Models\CrowdfundingProduct::STATUS_FUNDING)
                    <button class="btn btn-primary btn-crowdfunding">参与众筹</button>
                    @else
                    <button class="btn btn-primary disabled">
                        {{ \App\Models\CrowdfundingProduct::$statusMap[$product->crowdfunding->status] }}
                    </button>
                    @endif
                @else
                    <a class="btn btn-primary" href="{{ route('login') }}">请先登录</a>
                @endif
            @else
              <button class="btn btn-primary btn-add-to-cart" id='btn-add-to-cart'>加入购物车</button>
              @endif
            <!-- 众筹商品下单按钮结束 -->
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
                 <!-- 产品属性开始 -->
          <div class="properties-list">
            <div class="properties-list-title">产品参数：</div>
            <ul class="properties-list-body">
              @foreach($product->group_properties as $name=>$values)
                <li>{{ $name }}：{{ join(' ',$values) }}</li>
              @endforeach
            </ul>
          </div>
          <!-- 产品属性结束 -->
          <div class="product-description">
              {!! $product->discription !!}
            </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="product-reviews-tab">
                 <!-- 评论列表开始 -->
                 <table class="table table-bordered table-striped">
                    <thead>
                    <tr>
                    <td>用户</td>
                    <td>商品</td>
                    <td>评分</td>
                    <td>评价</td>
                    <td>时间</td>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($reviews as $review)
                    <tr>
                        <td>{{ $review->order->user->name }}</td>
                        <td>{{ $review->productSku->title }}</td>
                        <td>{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                        <td>{{ $review->review }}</td>
                        <td>{{ $review->reviewed_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                <!-- 评论列表结束 -->
            </div>
          </div>
        </div>
          <!-- 猜你喜欢开始 -->
            @if(count($similar) > 0)
            <div class="similar-products">
            <div class="title">猜你喜欢</div>
            <div class="row products-list">
                <!-- 这里不能使用 $product 作为 foreach 出来的变量，否则会覆盖掉当前页面的 $product 变量 -->
                @foreach($similar as $p)
                <div class="col-3 product-item">
                    <div class="product-content">
                    <div class="top">
                        <div class="img">
                        <a href="{{ route('products.show', ['product' => $p->id]) }}">
                            <img src="{{ $p->image_url }}" alt="">
                        </a>
                        </div>
                        <div class="price"><b>￥</b>{{ $p->price }}</div>
                        <div class="title">
                        <a href="{{ route('products.show', ['product' => $p->id]) }}">{{ $p->title }}</a>
                        </div>
                    </div>
                    </div>
                </div>
                @endforeach
            </div>
            </div>
        @endif
        <!-- 猜你喜欢结束 -->
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

                    //购物车
                    $('#btn-add-to-cart').click(function(){
                            axios.post('{{route('cart.add')}}',{
                                sku_id:$('label.active input[name=skus]').val(),
                                amount:$('#amount').val(),
                            }).then(res=>{
                                if(res.data.code==200){
                                    swal('加入成功','success')
                                };
                            }).catch(error=>{
                                if(error.response.status ===401){
                                    //http 状态码 401 代表未登录
                                    location.href='/login'
                                    // http 状态码为 422 代表用户输入校验失败
                                }else if(error.response.status === 422){
                                    console.log(error)
                                    var html = '<div>';
                                        _.each(error.response.data.errors, function (errors) {
                                        _.each(errors, function (error) {
                                            html += error+'<br>';
                                        })
                                        });
                                        html += '</div>';
                                        swal({content: $(html)[0], icon: 'error'})
                                }else{
                                     // 其他情况应该是系统挂了
                                     swal('系统错误', '', 'error');
                                };
                            })
                    })

                    //参与众筹点击事件
                    $(".btn-crowdfunding").click(function(){
                        // 判断是否选中 SKU;
                        var check_sku =$('label.active input[name=skus]').val()
                        if(! check_sku ){
                            swal('请选择商品','','warning');
                            return;
                        }
                    // 把用户的收货地址以 JSON 的形式放入页面，赋值给 addresses 变量
                    addresses  = {!! json_encode(Auth::check() ? Auth::user()->addresses:[]) !!}

                    // 使用 jQuery 动态创建一个表单
                    var $form = $('<form></form>');
                    // 表单中添加一个收货地址的下拉框
                    $form.append(`<div class="form-group row">
                        <label class="col-form-label col-sm-3">选择地址</label>
                        <div class="col-sm-9">
                            <select class="custom-select" name="address_id"></select>
                        </div>
                        </div>`);
                        //循环每个收货地址,把当前收货地址添加到收货地址下拉框选项中
                        addresses.forEach(function(address){
                            $form.find('select[name=address_id]').append(`
                                <option value=${address.id}>${address.full_address} ${address.contact_name}${address.contact_phone}</option>
                            `)
                        });
                       // 在表单中添加一个名为 购买数量 的输入框
                        $form.append('<div class="form-group row">' +
                            '<label class="col-form-label col-sm-3">购买数量</label>' +
                            '<div class="col-sm-9"><input class="form-control" name="amount">' +
                            '</div></div>');

                        // 调用 SweetAlert 弹框
                        swal({
                            text:'参与众筹',
                            content:$form[0],//弹框的内容就是刚刚创建的表单
                            buttons:['取消','确定']
                        }).then(ret=>{
                            // 如果用户没有点确定按钮，则什么也不做
                            if (!ret) {
                            return;
                            }
                            //构建请求参数
                            var req = {
                                address_id:$form.find('select[name=address_id]').val(),
                                amount:$form.find('input[name=amount]').val(),
                                sku_id:check_sku
                            }

                            axios.post('{{route('crowdfunding_orders.store')}}',req).then(res=>{
                               // 订单创建成功，跳转到订单详情页
                                    swal('订单提交成功', '', 'success')
                                    .then(() => {
                                        console.log(res)
                                        return
                                        location.href = '/orders/' + res.data.id;
                                    }).catch(error=>{

                                        // 输入参数校验失败，展示失败原因
                                    if (error.response.status === 422) {
                                    var html = '<div>';
                                    _.each(error.response.data.errors, function (errors) {
                                        _.each(errors, function (error) {
                                        html += error+'<br>';
                                        })
                                    });
                                    html += '</div>';
                                    swal({content: $(html)[0], icon: 'error'})
                                    } else if (error.response.status === 403) {
                                    swal(error.response.data.msg, '', 'error');
                                    } else {
                                    swal('系统错误', '', 'error');
                                    }

                                    });
                                })
                        })

                    });
        })
    </script>
@endsection
