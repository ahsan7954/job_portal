<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\category;
use App\Models\job;
use App\Models\jobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    public function index()
    {
        $jobs = job::orderBy('created_at', 'DESC')->with('user', 'applications')->paginate(10);
        return view('admin.jobs.list', [
            'jobs' => $jobs
        ]);
    }
    public function edit($id)
    {
        $job = job::findOrFail($id);
        $categories = category::orderBy('name', 'ASC')->get();
        $jobTypes = jobType::orderBy('name', 'ASC')->get();
        return view('admin.jobs.edit', [
            'job' => $job,
            'categories' => $categories,
            'jobtypes' => $jobTypes
        ]);
    }
    public function update(Request $request, $id)
    {

        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:70'
        ];
        $validator = validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $job = job::find($id);
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->vacancy = $request->vacancy;
            $job->salary = $request->salary;
            $job->location = $request->location;
            $job->description = $request->description;
            $job->benefits = $request->benefits;
            $job->responsibility = $request->responsibility;
            $job->qualifications = $request->qualifications;
            $job->experience = $request->experience;
            $job->keywords = $request->keywords;
            $job->company_name = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website = $request->company_website;
            $job->status = $request->status;
            $job->isFeatured = (!empty($request->isFeatured)) ? $request->isFeatured : 0;
            $job->save();
            session()->flash('success', 'Job Updated Successfully');


            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function destroy(Request $request)
    {
        $id = $request->id;
        $job = job::find($id);
        if ($job == null) {
            session()->flash('error', 'Job not found.');
            return response()->json([
                'status' => false,
            ]);
        }
        $job->delete();
        session()->flash('success', 'Job deleted Successfully');

        return response()->json([
            'status' => true,
        ]);
    }
}