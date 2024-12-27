<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendTestEmail()
    {
        $testDetails = [
            'subject' => 'Test Email',
            'body' => 'This is a test email sent from Laravel.'
        ];

        Mail::raw($testDetails['body'], function ($message) use ($testDetails) {
            $message->to('walidvl5546@gmail.com') // Replace with your email
                    ->subject($testDetails['subject']);
        });

        return response()->json(['success' => true, 'message' => 'Test email sent successfully!']);
    }
}

