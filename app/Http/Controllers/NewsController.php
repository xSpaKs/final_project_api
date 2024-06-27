<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\News;

class NewsController extends Controller
{
    public function news()
    {
        return News::all();
    }

    public function singleNews($id)
    {
        return News::where("id", $id)->first();
    }
}
