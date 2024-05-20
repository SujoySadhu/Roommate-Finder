<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use App\Models\SavedJob;   
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobsController extends Controller
{
    //this method will show job page
    public function index (Request $request){

        $categories= Category :: where('status',1)->get();
        $jobTypes= JobType :: where('status',1)->get();

        $jobs=Job::where('status',1);


        //search using keywords
        if (!empty ($request->keyword)){
            $jobs = $jobs->where(function($query) use ($request){
                $query->orWhere('title','like','%'.$request->keyword.'%');
                $query->orWhere('keywords','like','%'.$request->keyword.'%');

            });
        }

        //search using location

        if(!empty($request->location)){
            $jobs = $jobs->where('location',$request->location);
        }

        //search using category

        if(!empty($request->category)){
            $jobs = $jobs->where('category_id',$request->category);
        }

        $jobTypeArray=[];

         //search using jobType

         if(!empty($request->jobType)){
            //1,2,3

            $jobTypeArray = explode(',',$request->jobType);


            $jobs = $jobs->whereIn('job_type_id', $jobTypeArray);
        }


         //search using experience

         if(!empty($request->experience)){
            $jobs = $jobs->where('experience',$request->experience);
        }

        $jobs = $jobs->with(['jobType','category']);
        if($request->sort=='0')
        {
            $jobs=$jobs->orderBy('created_at','ASC');

        }else{
            $jobs=$jobs->orderBy('created_at','DESC');
        }
        $jobs=$jobs->paginate(10);




        return view('front.jobs',[
            'categories'=> $categories,
            'jobTypes'=>$jobTypes,
            'jobs'=>$jobs,
            'jobTypeArray'=>$jobTypeArray
        ]);

    }

    //this method will show the job detail page
    public function detail($id){

        $job = Job::where([
            'id' => $id,
            'status' => 1

        ])->with(['jobType','category'])->first();
        
        if($job == null)
        {
            abort(404);
        }
        return view('front.jobDetail',['job' => $job]);
       
    }

    public function detail_chat(){
 
       
    }

    public function saveJob(Request $request)
    {
        $id = $request->id;
    
        $job = Job::find($id);
    
        if ($job == null) {
            return response()->json([
                'status' => false,
                'message' => 'Job not found',
            ]);
        }
    
        // Check if job is already saved
        $count = SavedJob::where([
            'job_id' => $id,
            'user_id' => Auth::user()->id
        ])->count();
    
        if ($count > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Job already saved',
            ]);
        }
    
        $savedJob = new SavedJob();
        $savedJob->job_id = $id;
        $savedJob->user_id = Auth::user()->id;
        $savedJob->save();
    
        return response()->json([
            'status' => true,
            'message' => 'Job saved successfully',
        ]);
    }
    
}