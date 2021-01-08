@extends('layouts.app')
@section('title', '商品列表')

@section('content')
    <div class="row" id='products'>
        <div class="col-lg-10 offset-lg-1">
            <div class="card">
                <div class="card-body">
                    {{--
                    <!-- 面包屑开始 --> --}}
                    <div class="col-auto category-breadcrumb">
                        {{--
                        <!-- 添加一个名为 全部 的链接，直接跳转到商品列表页 --> --}}
                        <a class="all-products" href="{{ route('products.index', ['category_id' => 'all']) }}">全部</a> >
                        {{--
                        <!-- 如果当前是通过类目筛选的 --> --}}
                        @if ($category && $category->is_directory)
                            {{--
                            <!-- 遍历这个类目的所有祖先类目，我们在模型的访问器中已经排好序，因此可以直接使用 --> --}}
                            @foreach ($category->ancestors as $ancestor)
                                {{--
                                <!-- 添加一个名为该祖先类目名的链接 --> --}}
                                <span class="category">
                                    <a
                                        href="{{ route('products.index', ['category_id' => $ancestor->id]) }}">{{ $ancestor->name }}</a>
                                </span>
                                <span>&gt;</span>
                            @endforeach
                            {{--
                            <!-- 最后展示出当前类目名称 --> --}}
                            <span class="category">{{ $category->name }}</span><span> ></span>
                            {{--
                            <!-- 当前类目的 ID，当用户调整排序方式时，可以保证 category_id 参数不丢失 --> --}}
                            <input type="hidden" name="category_id" value="{{ $category->id }}">
                        @endif


                    </div>
                    {{--
                    <!-- 面包屑结束 --> --}}

                    {{--
                    <!-- 筛选组件开始 --> --}}
                    <form action="{{ route('products.index') }}" class="search-form">
                        <div class="form-row">
                            <div class="col-md-9">
                                <div class="form-row">
                                    <div class="col-auto">
                                        <input type="text" class="form-control form-control-sm" name="search"
                                            placeholder="搜索">
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-primary btn-sm">搜索</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="order" class="form-control form-control-sm float-right">
                                    <option value="">排序方式</option>
                                    <option value="price_asc">价格从低到高</option>
                                    <option value="price_desc">价格从高到低</option>
                                    <option value="sold_count_desc">销量从高到低</option>
                                    <option value="sold_count_asc">销量从低到高</option>
                                    <option value="rating_desc">评价从高到低</option>
                                    <option value="rating_asc">评价从低到高</option>
                                </select>
                            </div>
                        </div>
                    </form>

                    {{--
                    <!-- 展示子类目开始 --> --}}
                    <div class="filters">
                        {{--
                        <!-- 如果当前是通过类目筛选，并且此类目是一个父类目 --> --}}
                        @if ($category && $category->is_directory)
                            <div class="row">
                                <div class="col-3 filter-key">子类目：</div>
                                <div class="col-9 filter-values">
                                    {{--
                                    <!-- 遍历直接子类目 --> --}}
                                    @foreach ($category->children as $child)
                                        <a
                                            href="{{ route('products.index', ['category_id' => $child->id]) }}">{{ $child->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        <!-- 分面搜索结果开始 -->
                        <!-- 遍历聚合的商品属性 -->
                        @foreach ($properties as $property)
                            <div class="row">
                                <!-- 输出属性名 -->
                                <div class="col-3 filter-key">{{ $property['key'] }}：</div>
                                <div class="col-9 filter-values">
                                    <!-- 遍历属性值列表 -->
                                    @foreach ($property['values'] as $value)
                                        <a
                                            href="javascript:appendFilterToQuery('{{ $property['key'] }}','{{ $value }}')">{{ $value }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        <!-- 分面搜索结果结束 -->
                    </div>
                </div>

                {{--
                <!-- 展示子类目结束 --> --}}
                <div class="row products-list">

                    <!-- 筛选组件结束 -->
                    <div class="row products-list">
                        @foreach ($products as $product)
                            <div class="col-3 product-item">
                                <div class="product-content">
                                    <div class="top">
                                        <a href="{{ route('products.show', ['product' => $product->id]) }}">
                                            <div class="img"><img src="{{ $product->image_url }}" alt=""></div>
                                            <div class="price"><b>￥</b>{{ $product->price }}</div>
                                            <div class="title">{{ $product->title }}</div>
                                        </a>
                                    </div>
                                    <div class="bottom">
                                        <div class="sold_count">销量 <span>{{ $product->sold_count }}笔</span></div>
                                        <div class="review_count">评价 <span>{{ $product->review_count }}</span></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                    </div>
                    <div class="float-right">{{ $products->onEachSide(1)->appends($filters)->links() }}</div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scriptAfterJs')
    <script>
        var filters = {!!json_encode($filters) !!};
        // var filters = {!!  json_encode($filters) !!};
        $(document).ready(function() {

            //把选中的值赋值给 input 框
            var search = $('.search-form input[name=search]').val(filters.search);
            var order = $('.search-form input[name=order]').val(filters.order);

            //选中下拉框自定提交,注意事件要绑定在 select 上
            $('.search-form select[name=order]').on('change', function() {
                console.log(order)
                $('.search-form').submit();
            });
        })

        // 定义一个函数，用于解析当前 Url 里的参数，并以 Key-Value 对象形式返回
        function parseSearch() {
            // 初始化一个空对象
            var searches = {}
            // location.search 会返回 Url 中 ? 以及后面的查询参数
            // substr(1) 将 ? 去除，然后以符号 & 分割成数组，然后遍历这个数组

            location.search.substr(1).split('&').forEach(function(str) {
                // 将字符串以符号 = 分割成数组
                var result = str.split('=')

                // 将数组的第一个值解码之后作为 Key，第二个值解码后作为 Value 放到之前初始化的对象中
                searches[decodeURIComponent(result[0])] = decodeURIComponent(result[1]);
            });
            return searches;
        }

        // 根据 Key-Value 对象构建查询参数
        function buildSearch(searches) {
            //初始化字符串
            var query = '?'
            //遍历 searches 对象
            _.forEach(searches, function(value, key) {
                query += encodeURIComponent(key) + '=' + encodeURIComponent(value) + '&';
            });

            // 去除最末尾的 & 符号
            return query.substr(0, query.length - 1);
        }

        //// 将新的 filter 追加到当前的 Url 中
        function appendFilterToQuery(name, value) {
            //解析 url 参数
            var searches = parseSearch();

            console.log(name, value)

            // 如果已经有了 filters 查询, // 则在已有的 filters 后追加
                      if (searches['filters']) {
                // 则在已有的 filters 后追加
                searches['filters'] += '|' + name + ':' + value;
            } else {
                // 否则初始化 filters
                searches['filters'] = name + ':' + value;
            }

            location.search = buildSearch(searches)

            console.log(searches)


            // console.log(buildSearch(searches))
        }

    </script>
@endsection
