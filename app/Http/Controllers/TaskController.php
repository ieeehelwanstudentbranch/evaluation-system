<?php

namespace App\Http\Controllers;

use App\DeliverTask;
use App\Http\Resources\Task\CompleteTasks;
use App\Http\Resources\Task\PendingTasks;
use App\Http\Resources\Task\TaskCollection;
use App\Http\Resources\Task\TaskCollectionPanding;
use App\SendTask;
use App\Task;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');

    }

    public function createPage(){
        if ( JWTAuth::parseToken()->authenticate()->position == 'EX_com' || JWTAuth::parseToken()->authenticate()->position == 'highBoard') {
            $users = User::select('id', 'firstName', 'lastName', 'position')->get();
            return response($users);
        }else{
            return response()->json('Un Authenticated');
        }
    }

    public function store(Request $request){

        if ( JWTAuth::parseToken()->authenticate()->position == 'EX_com' || JWTAuth::parseToken()->authenticate()->position == 'highBoard') {
            $this->validate($request, [
                'title' => 'required |min:3 |max:100 ',
                'body' => 'required |min:3 |max:1000 ',
//                'deadline' => 'required |date',
                'files' => 'nullable| mimes:doc,pdf,docx,zip,txt,ppt,pptx,jpeg,jpg,svg,gif,ps,xls|max:10240000',
//                'to' => 'required',
            ]);

            foreach ($request->input('to') as $to){
                $task = new Task();
            $task->title = $request->input('title');
            $task->body_sent = $request->input('body');
            $task->deadline = $request->input('deadline');
            $task->from = JWTAuth::parseToken()->authenticate()->id;
            $task->to = $to;
//            upload files
            if ($request->hasfile('files')) {
                foreach ($request->file('files') as $file) {
                    $filenameWithExtention = $file->getClientOriginalName();
                    $fileName = pathinfo($filenameWithExtention, PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileNameStore = $fileName . '_' . time() . '.' . $extension;

                    $file_path = $file->move(base_path() . '/public/uploaded/tasks/', $fileNameStore);
                    $data[] = $file_path;
                }
                $task->taskFiles = json_encode($data);
            }
            $task->save();
        }
            return response()->json('success','task sent');

        }else{
            return response()->json('Un Authenticated');
        }

    }

//    pending tasks
    public function pendingTasks(){
        if (Task::all()->where('to', JWTAuth::parseToken()->authenticate()->id) || Task::all()->where('from', JWTAuth::parseToken()->authenticate()->id)){
        $tasks = Task::all();
        return new PendingTasks($tasks);
        }else{
            return response()->json('error','Un Authenticated');
        }
    }
    //    complete tasks
    public function completeTasks(){
        if (Task::all()->where('to', JWTAuth::parseToken()->authenticate()->id) || Task::all()->where('from', JWTAuth::parseToken()->authenticate()->id)){
        $tasksSent = Task::all();
        return new CompleteTasks([$tasksSent]);
        }else{
            return response()->json('error','Un Authenticated');
        }
    }
//    view task
    public function viewTask($id){
        if (Task::all()->where('to', JWTAuth::parseToken()->authenticate()->id) || Task::all()->where('from', JWTAuth::parseToken()->authenticate()->id)
        ||(Task::all()->where('to', JWTAuth::parseToken()->authenticate()->id))
        ){
        return new TaskCollectionPanding(Task::findOrFail($id));
        }else{
            return response()->json('error','Un Authenticated');
        }
    }

//    deliver task

    public function deliverTask(Request $request ,$id){
        $task = Task::findOrFail($id);
        if ($task->to == JWTAuth::parseToken()->authenticate()->id){
            $this->validate($request, [
                'body' => 'required |min:1|max:1000',
                'files' => 'nullable| mimes:doc,pdf,docx,zip,txt,ppt,pptx,jpeg,jpg,svg,gif,ps,xls|max:10240000',
            ]);
            //            upload files
            if ($request->hasfile('files')) {
                foreach ($request->file('files') as $file) {
                    $filenameWithExtention = $file->getClientOriginalName();
                    $fileName = pathinfo($filenameWithExtention, PATHINFO_FILENAME);
                    $extension = $file->getClientOriginalExtension();
                    $fileNameStore = $fileName . '_' . time() . '.' . $extension;

                    $file_path = $file->move(base_path() . '/public/uploaded/tasks/', $fileNameStore);
                    $data[] = $file_path;
                }
                $task->files_deliver = json_encode($data);
            }
            $task->body_deliver = $request->input('body');
            $task->update();
            return redirect()->back()->with('success','task sent');

        }else{
            return response()->json('error','Un Authenticated');
        }
    }

    public function acceptTask(Request $request , $id){
        $task = Task::findOrFail($id);

        if ($task->from == JWTAuth::parseToken()->authenticate()->id){
            $this->validate($request, [
                'rate' => 'required|numeric |min:1|max:100',
                'evaluation' => 'required |min:3 |max:1000',
            ]);

            $task->status = 'accepted';
            $task->rate = $request->input('rate');
            $task->evaluation = $request->input('evaluation');
            $task->update();

            return redirect()->back()->with('success','task accepted');
        }else{
            return response()->json('error','Un Authenticated');
            }
    }

    public function refuseTask($id){
        $task = Task::findOrFail($id);
        if ($task->from == JWTAuth::parseToken()->authenticate()->id){
            $task->status = 'pending';
            $task->update();
            return redirect()->back()->with('success','task refused');
        }else{
            return response()->json('error','Un Authenticated');
        }
    }

}
