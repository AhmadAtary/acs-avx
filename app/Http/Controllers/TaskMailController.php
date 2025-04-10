<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class TaskMailController extends Controller
{
    public function send(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'username'    => 'required|string',
            'device_id'   => 'required|string',
            'email'       => 'required|email',       // Recipient email
            'user_email'  => 'required|email',       // User's email
            'subject'     => 'required|string',
            'description' => 'required|string',
        ]);

        // Store the new task
        $task = new Task();
        $task->username = $validated['username'];
        $task->device_id = $validated['device_id'];
        $task->subject = $validated['subject'];
        $task->description = $validated['description'];
        $task->user_email = $validated['user_email'];
        $task->email = $validated['email'];
        $task->save();  // Save the task to the database

        // Send the email
        $validated['task_id'] = $task->id;  // Pass the task ID

        Mail::send('Mails.send-task', $validated, function ($message) use ($validated) {
            $message->to($validated['email'])        // Recipient email
                    ->subject($validated['subject']);
        });

        return back()->with('success', 'Task email sent successfully.');
    }
}
