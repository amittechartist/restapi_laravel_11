<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailConfig;
use App\Mail\DynamicEmail;

class EmailSenderController extends Controller
{
    /**
     * Send an email using the dynamic configuration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        // Validate the incoming request
        $data = $request->validate([
            'to'      => 'required|email',
            'subject' => 'required|string',
            'body'    => 'required|string',
        ]);

        // Load the email configuration from the database
        $config = EmailConfig::first();
        if (!$config) {
            return response()->json(['error' => 'Email configuration not found.'], 404);
        }

        // Dynamically update the mail configuration for this request
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport'  => $config->mail_driver,
            'mail.mailers.smtp.host'       => $config->host,
            'mail.mailers.smtp.port'       => $config->port,
            'mail.mailers.smtp.username'   => $config->username,
            'mail.mailers.smtp.password'   => $config->password,
            'mail.mailers.smtp.encryption' => $config->encryption,
            'mail.from.address'            => $config->from_address,
            'mail.from.name'               => $config->from_name,
        ]);
        app()->forgetInstance('mailer');
        Mail::clearResolvedInstances();
        // Prepare email data
        $emailData = [
            'subject' => $data['subject'],
            'body'    => $data['body'],
        ];

        try {
            // Send the email using a Mailable
            Mail::mailer('smtp')->to($data['to'])->send(new DynamicEmail($emailData));
            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully!'
            ], 200);
        } catch (\Exception $e) {
            // Catch any errors that occur while sending
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
