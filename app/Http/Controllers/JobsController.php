<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use Illuminate\Http\Request;
use App\Models\category;
use App\Models\job;
use App\Models\jobApplication;
use App\Models\jobType;
use App\Models\savedJob;
use Illuminate\Queue\Jobs\JobName;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class JobsController extends Controller
{
    //This method used to display jobs page
    public function index(Request $request){
        $categories = category :: where('status', 1)->get();
        $jobTypes = jobType :: where('status', 1)->get();
        $jobs = job :: where('status', 1);

        // search using keywords
        if(!empty($request->keyword)){
            $jobs = $jobs->where(function($query)use($request){
                $query->orWhere('title','like','%'.$request->keyword.'%');
                $query->orWhere('keywords','like','%'.$request->keyword.'%');
            });
        }
        // search using location
        if(!empty($request->location)){
            $jobs = $jobs->where('location',$request->location);
        }
         // search using category
         if(!empty($request->category)){
            $jobs = $jobs->where('category_id',$request->category);
        }
        // search using jobtype
        $jobTypeArray = [];
        if(!empty($request->jobType)){
            $jobTypeArray = explode(',' , $request->jobType );
            $jobs = $jobs->whereIn('job_type_id',$jobTypeArray );
        }
         // search using experience
         if(!empty($request->experience)){
            $jobs = $jobs->where('experience',$request->experience);
        }

        $jobs = $jobs->with(['jobType','category']);
        if($request->sort == '0'){
            $jobs = $jobs->orderBy('created_at', 'ASC');

        }else{
            $jobs = $jobs->orderBy('created_at', 'DESC');
        }

        $jobs = $jobs->paginate(12);

        return view('front.jobs',[
            'categories' => $categories,
            'jobTypes' => $jobTypes,
            'jobs' => $jobs,
            'jobTypeArray' => $jobTypeArray
        ]);

    }
    // this method will show jobs details page
    public function detail($id){
        $job = job :: where([
                        'id' => $id,
                        'status' => 1
                    ])->with(['jobType', 'category'])->first();
        if($job == null){
            abort(404);
        }

        $countJob = 0;

        if(Auth::user()){
            $countJob = savedJob::where([
                'user_id' => Auth::user()->id,
                'job_id' => $id
                ])->count();
            }
        // job applicants
           $applications = jobApplication::where('job_id', $id)->with('user')->get();
        //    dd($applications);

        return view('front.jobDetail',[
                    'job' => $job,
                    'countJob' => $countJob,
                    'applications' => $applications
                ]);
    }

    public function applyJob(Request $request){
        $id = $request->id;
        // dd($id);
        $job = job::where('id',$id)->first();
        // if job not found in db
        if($job == null){
            $message = "Job does not exist.";
            session()->flash('error', $message);
            return response()->json([
                'status' => false,
                'message' => $message
            ]);
        }
        // you can not apply on your own job
        $employer_id = $job->user_id;
        // dd($employer_id);
        if($employer_id == Auth::user()->id){
        $message = "You can not apply on your own job.";
            session()->flash('error',$message);
            return response()->json([
                'status' => false,
                'message' => $message
            ]);
        }
        // you can not apply for a job twise
        $jobApplicationCount = jobApplication :: where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();
        if($jobApplicationCount > 0){
            $message = "You already apply on this job.";
            session()->flash('error',$message);
            return response()->json([
                'status' => false,
                'message' => $message
            ]);
        }

        $application = new jobApplication();
        $application->job_id = $id;
        $application->user_id = Auth::user()->id;
        $application->employer_id =  $employer_id;
        $application->applied_date =  now();
        $application->save();

        // send email to the employer
        $employer = User::where('id',$employer_id)->first();
        $mailData = [
            'employer' => $employer,
            'user' => Auth::user(),
            'job' => $job
        ];
        Mail::to($employer->email)->send(new JobNotificationEmail($mailData));

        $message = "You have successfully applied.";
        session()->flash('success',$message);
            return response()->json([
                'status' => true,
                'message' => $message
            ]);

   }
   public function saveJob(Request $request){

        $id = $request->id;
        $job = job::find($id);
        if ($job == null){
            $message = "Job not found.";
            session()->flash('error', $message);
            return response()->json([
                'status' => false,
                'message' => $message
            ]);
        }

        // check if the user already save the job
        $countJob = savedJob::where([
            'user_id' => Auth::user()->id,
            'job_id' => $id
        ])->count();
        if ( $countJob > 0 ){
            $message = "You already save this job.";
            session()->flash('error', $message);
            return response()->json([
                'status' => false,
                'message' => $message

            ]);
        }


        $savedJob = new savedJob();
        $savedJob->job_id = $id;
        $savedJob->user_id = Auth::user()->id;
        $savedJob->save();
        $message = "You saved this job successfully.";
        session()->flash('success', $message);
            return response()->json([
                'status' => true,
                'message' => $message

            ]);
   }
}