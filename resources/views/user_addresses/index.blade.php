@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card panel-default">
                <div class="card-header">收货地址列表
                    <a class="float-right" href="{{ route('user_addresses.create') }}">新增收货地址</a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>收货人</th>
                                <th>地址</th>
                                <th>邮编</th>
                                <th>电话</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($addresses as $item)
                                <tr>
                                    <td>{{ $item->contact_name }}</td>
                                    <td>{{ $item->full_address }}</td>
                                    <td>{{ $item->zip }}</td>
                                    <td>{{ $item->contact_phone }}</td>
                                    <td>
                                        <a class="btn btn-primary" href="{{ route('user_addresses.edit', $item->id) }}">修改
                                        </a>
                                        <button type="button" class="btn btn-danger btn-del-address"
                                            data-id="{{ $item->id }}">删除</button>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                    {{ $addresses->links() }}
                </div>
            </div>
        </div>

    </div>
@endsection

@section('scriptAfterJs')
    <script>
        $(document).ready(function() {
            $('.btn-del-address').click(function() {
                //获取data属性
                var id = $(this).data('id')
                swal({
                        title: "确定要删除吗？",
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    })
                    .then((willDelete) => {
                        if (!willDelete) {
                            return;
                        }
                        axios.post('/user_addresses/' + id + '/destory').then(res => {
                            if (res.data.code === 200) {
                                location.reload()
                            }
                        })

                    });
            })
        })

    </script>
@endsection
