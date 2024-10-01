<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\jobApplication;
use Illuminate\Http\Request;

class jobApplicationController extends Controller
{
    public function index(){
        $applications = jobApplication::orderBy('created_at', 'DESC')
                                ->with('job', 'user', 'employer')
                                ->paginate(10);
        return view('admin.job-applications.list',[
            'applications' => $applications,
        ]);
    }
    public function destroy(Request $request){
        $id = $request->id;

        $jobApplication = jobApplication::find($id);
        if($jobApplication == null ){
            session()->flash('error', 'Job Applilcation is not found.');
            return response()->json([
                'status' => false
            ]);
        }
        $jobApplication->delete();
        session()->flash('success', 'Job Applilcation is deleted successfully.');
            return response()->json([
                'status' => true
            ]);

    }
}
