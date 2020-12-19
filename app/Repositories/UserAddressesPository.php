<?php
namespace App\Repositories;

class  UserAddressesPository{

    public function addAddress($request){
       return $request->user()->addresses()->create($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));
    }

    public function updateAddress($request,$id){
       return $request->user()->addresses()->where('id',$id)->update($request->only([
            'province',
            'city',
            'district',
            'address',
            'zip',
            'contact_name',
            'contact_phone',
        ]));
    }
}
