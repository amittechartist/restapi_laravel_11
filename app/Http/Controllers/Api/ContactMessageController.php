<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\ContactMessage;

class ContactMessageController extends Controller
{
    /**
     * Store a new contact message.
     *
     * Public endpoint.
     *
     * Expected JSON payload:
     * {
     *    "name": "John Doe",
     *    "email": "john@example.com",
     *    "subject": "Inquiry",
     *    "message": "I have a question..."
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string',
            'email'   => 'required|email',
            'subject' => 'nullable|string',
            'message' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $contact = ContactMessage::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Contact message submitted successfully.',
            'data'    => $contact
        ]);
    }

    /**
     * List all contact messages.
     *
     * Admin-only endpoint.
     */
    public function index()
    {
        $contacts = ContactMessage::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $contacts
        ]);
    }
}
