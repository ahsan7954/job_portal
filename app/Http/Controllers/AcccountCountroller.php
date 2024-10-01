<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordEmail;
use App\Models\category;
use App\Models\job;
use App\Models\jobApplication;
use App\Models\jobType;
use App\Models\savedJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;



class AcccountCountroller extends Controller
{
    //This method use to show  user registration page
    public function registration(){
        return view('front.account.registration');
    }

    //This method use to save  user
    public function processregistration(Request $request){
        $validator = validator::make($request->all(),[
            "name"=>"required",
            "email"=>"required|email|unique:users,email",
            "password"=>"required|min:5|same:confirm_password",
            "confirm_password"=>"required"
        ]);
        if($validator->passes()){
            $user = new user;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();
            session()->flash('success','You Have Resisterd Successfully');
            return response()->json([
                'status' => true,
                'errors' => []
            ]);
        }else{
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    //This method use to show  user login page
    public function login(){
        return view('front.account.login');
    }
    // this method use to authenticate the login person
    public function authenticate(Request $request){
        $validator = validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required'
        ]);
        if($validator->passes()){
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                return redirect()->route('account.profile');
            } else {
                return redirect()->route('account.login')->with('error','Email/Password Is Incorrect');
            }
        } else {
            return redirect()->route('account.login')
            ->withErrors($validator)
            ->withInput($request->only('email'));
        }
    }

    public function profile(){
        $id = Auth::user()->id;
        $user = User::where('id',$id)->first();

        return view('front.account.profile',["user"=>$user]);
    }
    public function updateProfile(Request $request){
        $id = Auth::user()->id;
        $validator = validator::make($request->all(),[
            'name'=>'required|min:5|max:20',
            'email' => 'required|email|unique:users,email,'.$id.'id'
        ]);
        if ($validator->passes()) {
            $user = User::find($id);
            $user->name = $request->name;
            $user->email = $request->email;
            $user->designation = $request->designation;
            $user->mobile = $request->mobile;
            $user->save();
            session()->flash('success','Profile Updated Successfully');
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
    public function updateProfilePic(Request $request){
        $id = Auth::user()->id;
        $validator = validator::make($request->all(),[
            'image' => 'required|image'
       ]);
            if ($validator->passes()) {
            $image = $request->image;
            $ext = $image->getClientOriginalExtension();
            $imageName = $id.'-'.time().'.'.$ext;
            $image->move(public_path('profile_img/'),$imageName);
            // delete old profile
            File::delete(public_path('profile_img/'.Auth::user()->image));

            User::where('id',$id)->update(['image' => $imageName]);
            session()->flash('success','Profile Picture Update Successfully');
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

    public function logout(){
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function createJob(){
       $categories = category::orderBy('name','ASC')->where('status',1)->get();
       $jobtypes = jobType::orderBy('name','ASC')->where('status',1)->get();
        return view('front.account.job.create',[
            'categories' => $categories,
            'jobtypes' => $jobtypes
        ]);
    }

    public function saveJob(Request $request){

        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:70'
        ];
        $validator = validator::make($request->all(),$rules);

        if ($validator->passes()) {
            $job = new job();
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->user_id = Auth::user()->id;
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
            $job->save();
            session()->flash('success','Job Added Successfully');


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

    public function myJobs(){
        $jobs = job::where('user_id',Auth::user()->id)->with('jobType')->orderBy('created_at', 'DESC')->paginate(10);
        return view('front.account.job.my-jobs',[
            'jobs' => $jobs
        ]);
    }

    public function editjob( Request $request, $id){
        $categories = category::orderBy('name','ASC')->where('status',1)->get();
        $jobtypes = jobType::orderBy('name','ASC')->where('status',1)->get();
        $job = job::where([
            'user_id' => Auth::user()->id,
            'id' => $id
        ])->first();
        if ($job==null){
            abort(404);
        }
         return view('front.account.job.edit',[
             'categories' => $categories,
             'jobtypes' => $jobtypes,
             'job' => $job
         ]);
    }

    public function updatejob(Request $request, $id){

        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required|min:3|max:70'
        ];
        $validator = validator::make($request->all(),$rules);

        if ($validator->passes()) {
            $job = job::find($id);
            $job->title = $request->title;
            $job->category_id = $request->category;
            $job->job_type_id = $request->jobType;
            $job->user_id = Auth::user()->id;
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
            $job->save();
            session()->flash('success','Job Updated Successfully');


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

    public function deleteJob(Request $request){
        $job = job :: where([
            'user_id' => Auth::user()->id,
            'id' => $request->jobId
        ])->first();

        if ($job == null){
            session()->flash('error', 'Either job deleted or not found.');
            return response()->json([
                'status' => true
            ]);
        }
        job :: where('id', $request->jobId)->delete();
        session()->flash('success', 'job deleted successfully');
        return response()->json([
                'status' => true
            ]);
    }

    public function myJobApplications(){
        $jobApplications = jobApplication :: where('user_id',Auth::user()->id)
                                            ->with(['job','job.jobType','job.applications'])
                                            ->orderBy('created_at', 'DESC')
                                            ->paginate(10);
        // dd($jobs);
        return view('front.account.job.my-job-applications',[
            'jobApplications' => $jobApplications,
        ]);
    }

    public function removeJobs(Request $request ){
        $jobApplication = jobApplication :: where([
                                            'id' => $request->id,
                                            'user_id' => Auth::user()->id
                                            ])->first();
        if($jobApplication == null){
            session()->flash('error', 'Job application is not found');
            return response()->json([
               'status' => false
            ]);


        }
        jobApplication::find($request->id)->delete();
        session()->flash('success', 'Job application is removed successfully ');
        return response()->json([
           'status' => true
        ]);
}

public function savedJobs(){

    $savedJobs = savedJob::where([
        'user_id' => Auth::user()->id,
    ])->with(['job','job.jobType','job.applications'])->orderBy('created_at', 'DESC')->paginate(10);

    return view('front.account.job.saved-jobs',[
    'savedJobs' => $savedJobs,
]);
}

public function removeSavedJob(Request $request ){
    $savedJob = savedJob :: where([
                                        'id' => $request->id,
                                        'user_id' => Auth::user()->id
                                        ])->first();
    if($savedJob == null){
        session()->flash('error', 'Job is not found');
        return response()->json([
           'status' => false
        ]);


    }
    savedJob::find($request->id)->delete();
    session()->flash('success', 'Job is removed successfully ');
    return response()->json([
       'status' => true
    ]);
}

public function updatePassword(Request $request){

    $validator = validator::make($request->all(),[
        'old_password' => 'required',
        'new_password' => 'required|min:5',
        'confirm_password' => 'required|same:new_password',
    ]);


    if ($validator->fails()){
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ]);
    }

    if(Hash::check($request->old_password, Auth::user()->password) == false){
        session()->flash('error', 'Your old password is incorrect.');
        return response()->json([
            'status' => true,
        ]);
    }

    $user = User::find(Auth::user()->id);
    $user->password = Hash::make($request->new_password);
    $user->save();
    session()->flash('success', 'Your password is updated successfully.');
        return response()->json([
            'status' => true,
        ]);

    }
    public function forgotPassword(){
        return view('front.account.forgot-password');
    }


    public function processForgotPassword(Request $request){
        $validator = validator::make($request->all(),[
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()){
            return redirect()->route('account.forgotPassword')->withInput()->withErrors($validator);
        }

        $token = Str::random(60);
        \DB::table('password_reset_tokens')->where('email',$request->email)->delete();
        \DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now()
        ]);

        // send email
        $user = User::where('email',$request->email)->first();
        $mailDate = [
            'token' => $token,
            'user' => $user,
            'subject' => 'You have requested to change your password.'
        ];
        Mail :: to($request->email)->send(new ResetPasswordEmail($mailDate));
        return redirect()->route('account.forgotPassword')->with('success','Please check your inbox');
    }

    public function resetPassword($tokenString){

        $token = \DB::table('password_reset_tokens')->where('token',$tokenString)->first();

        if($token == null ){

        return redirect()->route('account.forgotPassword')->with('error','Invalid token.');

        }

        return view('front.account.reset-password',[
            'tokenString' => $tokenString
        ]);
    }

    public function processResetPassword(Request $request){

        $token = \DB::table('password_reset_tokens')->where('token',$request->token)->first();

        if($token == null ){

        return redirect()->route('account.forgotPassword')->with('error','Invalid token.');

        }

        $validator = validator::make($request->all(),[
            'new_password' => 'required|min:5',
            'confirm_password' => 'required|same:new_password'
        ]);

        if ($validator->fails()){
            return redirect()->route('account.resetPassword',$request->token)->withErrors($validator);
        }

        User::where('email',$token->email)->update([
            'password' => Hash::make($request->new_password)
        ]);

        return redirect()->route('account.login')->with('success','You have change your password successfully.');

    }

}