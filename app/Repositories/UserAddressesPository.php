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
}
