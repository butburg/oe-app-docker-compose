<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

class TestMailController extends Controller
{
        public function sendTestEmail()
    {
        Mail::to('recipient@example.com')->send(new TestMail());
        return 'Test email sent!';
    }
}
