<?php
namespace App\Http\Controllers;
use Intervention\Image\ImageManager;


use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use  Illuminate\Support\Facades\Hash;
use  Illuminate\Support\Facades\Auth;
use  Illuminate\Support\Facades\File;
use  Illuminate\Support\Facades\Validator;
use App\Rules\StrongPassword;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Facades\Image;


class AccountController extends Controller
{
    //this method will show user registration page
    public function registration()
    {
        return view('front.account.registration');

    }


  

    public function processRegistration(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword],
        ]);
    
        if ($validator->passes()) {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
    
            $user->save();
    
            session()->flash('success', 'You have registered successfully.');
    
            return response()->json([
                'status' => true,
                'errors' => [],
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ]);
        }
    }
    
    
    public function login()
    {
        return view('front.account.login');

    }
    public function authenticate(Request $request){
        $validator=Validator::make($request->all(),[
            'email'=>'required|email',
            'password'=>'required'
        ]);
        if($validator->passes()){
            if(Auth::attempt(['email'=>$request->email,'password'=>$request->password])){
                return redirect()->route('account.profile');

            }else{
                return redirect()->route('account.login')->with('error','Either Email/Password is incorrect');
            }

        }else{
            return redirect()->route('account.login')->withErrors($validator)->withInput($request->only('email'));

        }
    }
    public function profile(){
       $id= Auth::user()->id;
       $user=User::where('id',$id)->first();
       //dd($user);
        return view('front.account.profile',[
            'user'=>$user
        ]);
    }

    public function updateProfile(Request $request){
        $id= Auth::user()->id;
        $validator=Validator::make($request->all(),[
            'name'=>'required|min:5|max:20',
            'email'=>'required|email|unique:users,email,'.$id.',id'
        ]);


        if($validator->passes()){

            $user=User::find($id);
            $user->name=$request->name;
            $user->email=$request->email;
            $user->mobile=$request->mobile;
            $user->designation=$request->designation;
            
            $user->save();
            session()->flash('success','Profile updated successfully.');


            return response()->json([
                'status'=>true,
                'errors'=>[]

            ]);



        }
        else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()

            ]

            );
        }
        
       
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function updateProfilePic(Request $request){
        //dd($request->all());
        $id= Auth::user()->id;
        $validator=Validator::make($request->all(),[
            'image'=>'required|image'
           
        ]);
        if($validator->passes()){
            $image=$request->image;
            $ext=$image->getClientOriginalExtension();
            $imageName=$id.'-'.time().'.'. $ext;
            $image->move(public_path('/profile_pic/'),$imageName);

            //create small thrumbnail
            $sourcePath=public_path('/profile_pic/'.$imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath);

// crop the best fitting 5:3 (600x360) ratio and resize to 600x360 pixel
             $image->cover(150, 150);
             $image->toPng()->save(public_path('/profile_pic/thumb/'.$imageName));
             //deleting old pic
             File::delete(public_path('/profile_pic/thumb/'.Auth::user()->image));
             File::delete(public_path('/profile_pic/'.Auth::user()->image));


            User::where('id',$id)->update(['image'=>$imageName]);

            session()->flash('success','Profile picture updated successfully.');
            return response()->json([
                'status'=>true,
                'errors'=>[]

            ]

            );


           



        }
        else{
            return response()->json([
                'status'=>false,
                'errors'=>$validator->errors()

            ]

            );
        }
        

       
    }
    public function createJob(){
       $categories= Category :: orderBy('name','ASC')->where('status',1)->get();
       $jobTypes= JobType :: orderBy('name','ASC')->where('status',1)->get();
        return view('front.account.job.create',[
          'categories'=> $categories,
          'jobTypes'=>$jobTypes
        ]);
    }
   
    public function saveJob(Request $request)
    {
        $rules = [
            'title' => 'required|min:5|max:200',
            'category' => 'required',
            'jobType' => 'required',
            'vacancy' => 'required|integer',
            'location' => 'required|max:50',
            'description' => 'required',
            'company_name' => 'required',
            'image' => 'required|image' // Adding validation rule for image
        ];
    
        $validator = Validator::make($request->all(), $rules);
    
        if ($validator->passes()) {
            $job = new Job();
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
    
            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('job_images'), $imageName);
    
                $sourcePath=public_path('/job_images/'.$imageName);
            $manager = new ImageManager(Driver::class);
            $image = $manager->read($sourcePath);

// crop the best fitting 5:3 (600x360) ratio and resize to 600x360 pixel
             $image->cover(150, 150);
             $image->toPng()->save(public_path('/job_images/thumb/'.$imageName));

             
                $job->image = $imageName;
    
            $job->save();
    
            session()->flash('success', 'Job added successfully');
    
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
}
    
    
    

    
    
 public function myjobs()
 {   
    $jobs=Job::where('user_id',Auth::user()->id)->with('jobType')->orderBy('created_at','DESC')->paginate(10);
    
    return view('front.account.job.my-jobs' ,[
        'jobs'=>$jobs
    ]);
 }
 public function editJob(Request $request,$id){
    
    $categories= Category :: orderBy('name','ASC')->where('status',1)->get();
       $jobTypes= JobType :: orderBy('name','ASC')->where('status',1)->get();

       $job=Job::where([
        'user_id'=> Auth::user()->id,
        'id'=> $id

       ])->first();
       //dd($job);

       if($job==null)
       {
           abort(404);
       }

    return view('front.account.job.edit',[
        'categories'=> $categories,
          'jobTypes'=>$jobTypes,
          'job'=>$job,

    ]);
 }

 public function updateJob(Request $request,$id)
 {
     $rules = [
         'title' => 'required|min:5|max:200',
         'category' => 'required',
         'jobType' => 'required',
         'vacancy' => 'required|integer',
         'location' => 'required|max:50',
         'description' => 'required',
         'company_name' => 'required',
     ];
 
     $validator = Validator::make($request->all(), $rules);
 
     if ($validator->passes()) {
         $job =Job::find($id);
         $job->title= $request->title;
        // $categoryId = (int) $category->id;
         $job->category_id= $request->category;
         $job->job_type_id= $request->jobType;
         $job->user_id= Auth::user()->id;
         $job->vacancy= $request->vacancy;
         $job->salary= $request->salary;
         $job->location= $request->location;
         $job->description= $request->description;
         $job->benefits= $request->benefits;
         $job->responsibility= $request->responsibility;
         $job->qualifications= $request->qualifications;
         $job->experience= $request->experience;
         $job->keywords= $request->keywords;
         $job->company_name= $request->company_name;
         $job->company_location= $request->company_location;
         $job->company_website= $request->company_website;
         $job->save();
         
         session()->flash('success','job updated Successfully');
 
         
         // Process the job creation
         return response()->json([
             'status' => true,
             'errors' => []
         ]);
     } else {
         return response()->json([
             'status' => false,
             'errors' => $validator->errors(), // Convert errors to array for easier handling in JavaScript
         ]);
     }
 }
 public function deleteJob(Request $request){

    $job= Job::where([
         'user_id'=> Auth::user()->id,
         'id'=>$request->jobId,
 
     ])->first();
 
 
 
        if($job==null)
        {
            session()->flash('error','Either job is deleted or not found.');
            return response()->json([
             'status'=>true
            ]);
        }
 
        Job::where('id',$request->jobId)->delete();
 
        session()->flash('success','Car deleted successfully.');
            return response()->json([
             'status'=>true
            ]);
 
  }
  public function savedJobs(){
   $savedJobs = SavedJob::where('user_id', Auth::user()->id)->with('job','job.jobType')->orderBy('created_at', 'DESC')->
    paginate(10);
    return view('front.account.job.saved-jobs', [
        'savedJobs' => $savedJobs
    ]);
     

  }
  public function removeSavedJob(Request $request) {
    // dd($request->id);

    $savedJob = SavedJob::where([
        'user_id' => Auth::user()->id,
        'id' => $request->id,
    ])->first();

    if ($savedJob == null) {
        session()->flash('error', 'Either job is deleted or not found.');
        return response()->json([
            'status' => false,
        ]);
    }

    $savedJob->delete();
    session()->flash('success', 'Job removed from saved list.');
    return response()->json([
        'status' => true,
    ]);
}

public function updatePassword(Request $request){
    $validator = Validator::make($request->all(),[
        'old_password' => 'required',
        'new_password' => ['required', 'string', 'min:8', 'confirmed', new StrongPassword],
        'password_confirmation' => 'required|same:new_password',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ]);
    }

    if (Hash::check($request->old_password, Auth::user()->password) == false){
        session()->flash('error','Your old password is incorrect.');
        return response()->json([
            'status' => true                
        ]);
    }


    $user = User::find(Auth::user()->id);
    $user->password = Hash::make($request->new_password);  
    $user->save();

    session()->flash('success','Password updated successfully.');
    return response()->json([
        'status' => true                
    ]);

}


  

  

    
}