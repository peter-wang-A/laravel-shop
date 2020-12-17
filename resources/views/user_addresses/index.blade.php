@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card panel-default">
                <div class="card-header">收货地址列表</div>
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
                                        <button class="btn btn-primary">修改</button>
                                        <button class="btn btn-danger">删除</button>
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
