@extends('layouts.app')
@section('title', '新增收货地址')

@section('content')
    <create-and-edit :addresses="{{ json_encode($address) }}"></create-and-edit>
@endsection
