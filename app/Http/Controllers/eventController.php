<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentData;
use App\Models\Appointments;
use App\Models\Committees;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\automaticMail;
use App\Mail\recruitmentAutoMail;

class eventController extends Controller
{
    public function recruitment(Request $request)
    {
        // dd($request->all(),isset($request->interview_time_b));
        $validition = Validator::make($request->all(), [
            'studentName' => ['required', 'string', 'max:255'],
            'studentEmail' => ['required', 'string', 'email', 'max:255', 'unique:events,email'],
            'studentPhone' => ['required', 'numeric', 'digits:11', 'unique:events,phone'],
            'studentCollege' => ['required', 'string', 'max:255'],
            'studentYear' => ['required', 'numeric'],
            'interview_time_a' => ['required', 'string', 'max:255'],
            // 'interview_time_id' => ['nullable', 'exists:appointments,id'],
            'studentCommitteeA' => ['required', 'string', 'max:255','exists:committees,name'],
            //'workshop_name' => ['required', 'string', 'max:255','exists:committees,name'],
            'studentCommitteeB' => ['nullable', 'string', 'max:255','exists:committees,name'],
        ]);
        // dd($validition->errors()->messages());
        if($validition->fails()){
            return redirect()->back()->withErrors($validition->errors()->messages());
        }

        $member=new Event();

        if($request->studentCommitteeA == $request->studentCommitteeB)
        {
            return redirect()->back()->with(['fail'=>"Don't Choose the Same Committee Twice!"]);
        }
        $member->name=$request->studentName;
        $member->email=$request->studentEmail;
        $member->phone=$request->studentPhone;
        $member->college=$request->studentCollege;
        $member->studentYear=$request->studentYear;
        $member->committee_A=$request->studentCommitteeA;
        $member->interview_time_a=$request->interview_time_a;
        if(isset($request->interview_time_b)){
            $member->committee_B=$request->studentCommitteeB;
            $member->interview_time_b=$request->interview_time_b;
        }
        $member->save();
        $data=$request->all();

        if($request->interview_time_a_id !=null){
            $interview_a = Appointments::find($request->interview_time_a_id);
            if($interview_a->numberOfSeats > 0){
            $interview_a->numberOfSeats--;
            $interview_a->save();
            }
            if(isset($request->interview_time_b)){
                $interview_b = Appointments::find($request->interview_time_b_id);
                if($interview_b->numberOfSeats > 0){
                $interview_b->numberOfSeats--;
                $interview_b->save();
                }
            }
           //  Mail::to($request['studentEmail'])->send(new recruitmentAutoMail($request));
            return redirect()->back()->withSuccess('Registration Successfully!');

            }else{
                return redirect()->back()->withSuccess('you in waitiong ');
            }
         
    }

    public function getAllMembers($key=null)
    {
        $member=new Event();
        
        if($key!=null){
            $collection=$member->where('committee_A','like',$key)->orWhere('committee_B','like',$key)->get();
            // dd($collection);
        }else{
        $collection=$member->get();
        }
        return view('Committees.EventMembers')->with('collection',$collection);
    }
  
    public function getAllCommittees(){

        $committees = new Committees();
        $committees=$committees->get();
        return view('Committees.home')->with('committees',$committees);
     }
    public function getAppointments(Request $request )
    {
        // dd($request->name);
        $committee_name=$request->name;
        $committee_id = Committees::where('name','like',$committee_name)->where('name','>','0')->first()->id;
        // dd($committee_id);
        $appointments= DB::table('appointments')->where('committee_id',$committee_id)->get();
        return $appointments;

    }

    public function registrationView(){
        $committees = new Committees();
        $committees = $committees->get();
        return view('event.EventRegisteration')->with('committees',$committees);
    }

    public function deleteMember($id)
    {
        $member= Event::findOrFail($id);
        $member->delete();
        
        return redirect()->route('EventMembers');
    }
}

