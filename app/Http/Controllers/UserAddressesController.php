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
        $userAddress = new UserAddress();
        return view('user_addresses.create_and_edit', compact('userAddress'));
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
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
