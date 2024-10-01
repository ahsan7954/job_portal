<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\category;
use App\Models\job;
use App\Models\jobType;

class HomeController extends Controller
{
    public function index()
    {
        $categories = category::where('status', 1)->orderBy('name', 'ASC')->take(8)->get();
        $newcategories = category::where('status', 1)->orderBy('name', 'ASC')->get();

        $featuredJobs = job::where('status', 1)->with('jobType')->where('isFeatured', 1)->orderBy('created_at', 'DESC')->take(6)->get();
        $latestJobs = job::where('status', 1)->with('jobType')->orderBy('created_at', 'DESC')->take(6)->get();
        return view('front.home', [
            'categories' => $categories,
            'featuredJobs' => $featuredJobs,
            'latestJobs' => $latestJobs,
            'newcategories' => $newcategories
        ]);
    }
}
