<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\EmailConfig;

class EmailConfigController extends Controller
{
    /**
     * Update the email configuration.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        // Assuming there's only one config record
        $config = EmailConfig::first();

        if (!$config) {
            return response()->json(['error' => 'Email configuration not found.'], 404);
        }

        return response()->json($config);
    }
    public function update(Request $request)
    {
        $config = EmailConfig::first();

        if (!$config) {
            return response()->json(['error' => 'Email configuration not found.'], 404);
        }

        // Create a validator instance for the email configuration update
        $validator = Validator::make($request->all(), [
            'mail_driver'   => 'required|string',
            'host'          => 'required|string',
            'port'          => 'required|integer',
            'username'      => 'required|string',
            'password'      => 'nullable|string', // Only update if provided
            'encryption'    => 'nullable|string',
            'from_address'  => 'required|email',
            'from_name'     => 'required|string',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        // Retrieve validated data
        $data = $validator->validated();

        // If password is not provided, remove it from the validated data to avoid overwriting it with null.
        if (!isset($data['password'])) {
            unset($data['password']);
        }

        // Update the configuration
        $config->update($data);

        return response()->json($config);
    }
}
