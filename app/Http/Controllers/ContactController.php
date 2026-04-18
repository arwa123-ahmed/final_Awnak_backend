<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactFormMail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // ✅ $validated مش $data
        Mail::to('aounak6@gmail.com')->send(new ContactFormMail($validated));

        return response()->json(['message' => 'Email sent successfully'], 200);
    }
}