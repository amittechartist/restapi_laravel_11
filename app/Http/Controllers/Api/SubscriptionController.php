<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Subscription;

class SubscriptionController extends Controller
{
    /**
     * Store a new subscription.
     *
     * Public endpoint.
     *
     * Expected JSON payload:
     * {
     *    "email": "subscriber@example.com",
     *    "name": "Subscriber Name"  // optional
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:subscriptions,email',
            'name'  => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $subscription = Subscription::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Subscription successful.',
            'data'    => $subscription
        ]);
    }

    /**
     * List all subscriptions.
     *
     * Admin-only endpoint.
     */
    public function index()
    {
        $subscriptions = Subscription::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $subscriptions
        ]);
    }
}
