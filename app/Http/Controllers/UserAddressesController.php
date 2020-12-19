<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAddressRequest;
use App\Models\UserAddress;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use SebastianBergmann\Environment\Console;
use App\Repositories\UserAddressesPository;

class UserAddressesController extends Controller
{
    protected $repo;
    public function __construct(UserAddressesPository $repos)
    {
        $this->repo = $repos;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->orderBy('id', 'desc')->paginate(7);
        return view('user_addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $address = new class{};
        return view('user_addresses.create_and_edit', compact('address'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserAddressRequest $request)
    {
        $this->repo->addAddress($request);
        return response()->json(['code' => 200, 'data' => '保存成功', 'route' => route('user_addresses.index')]);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(UserAddress $user_address)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(UserAddress $user_address)
    {
        $this->authorize('own',$user_address);
        return view('user_addresses.create_and_edit', ['address' => $user_address]);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserAddress $user_address,Request $request)
    {
        $this->authorize('own',$user_address);
        $this->repo->updateAddress($request, $user_address->id);
        return response()->json(['code' => 200, 'data' => '修改成功', 'route' => route('user_addresses.index')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(UserAddress $user_address)
    {
        $this->authorize('own',$user_address);
        // dd($user_address);
        $user_address->delete();
        // return redirect()->route('user_addresses.index');
        return response()->json([
            'code' => 200,
            'data' => '删除成功'
        ]);
    }
}
