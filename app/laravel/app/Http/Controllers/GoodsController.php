<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Goods;

class GoodsController extends Controller
{
    function goodsList($id=null) {
        return $id ? Goods::find($id) : Goods::all();
    }

    function search($name) {
        return Goods::where('name', 'like',  '%'.$name.'%')->get();
    }
}
