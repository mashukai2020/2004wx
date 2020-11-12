<?php

namespace App\Http\Controllers\Red;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class RedController extends Controller
{
    //
    public function test(){
        $key="ma";
        $res = Redis::set($key,time());
        print_r($res);
    }
    public function mysq(){
        $res= \DB::table('admin')->get()->toarray();
        print_r($res);
    }
}
