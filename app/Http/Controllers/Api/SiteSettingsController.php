<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FileUploadService;
use App\Models\SiteSetting;

class SiteSettingsController extends Controller
{
    protected $uploadService;

    /**
     * Inject the FileUploadService.
     *
     * @param FileUploadService $uploadService
     */
    public function __construct(FileUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }
    public function show()
    {
        // Assuming there's only one config record
        $config = SiteSetting::first();

        if (!$config) {
            return response()->json(['error' => 'Email configuration not found.'], 404);
        }

        return response()->json($config);
    }
    public function update(Request $request)
    {
        $q = SiteSetting::first();

        if (!$q) {
            return response()->json(['error' => 'Site configuration not found.'], 404);
        }
        // Create a validator instance for the email configuration update
        $validator = Validator::make($request->all(), [
            'site_name' => 'required|string',
            'short_info' => 'required|string',
            'contact_email' => 'required|string|email',
            'contact_phone' => 'required|string',
            'contact_address' => 'required|string',
            'logo' => 'nullable|file|mimes:png,jpg,jpeg',
            'footer_logo' => 'nullable|file|mimes:png,jpg,jpeg',
        ]);
        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }
        // Retrieve validated data
        $q->site_name = $request->site_name;
        $q->short_info = $request->short_info;
        $q->contact_email = $request->contact_email;
        $q->contact_phone = $request->contact_phone;
        $q->contact_address = $request->contact_address;
        if ($request->hasFile('logo')) {
            !empty($q->logo) && $this->uploadService->delete($q->logo);
            $result = $this->uploadService->upload($request->file('logo'), 'site_settings');
            $q->logo = $result['path'];
        }
        if ($request->hasFile('footer_logo')) {
            !empty($q->footer_logo) && $this->uploadService->delete($q->footer_logo);
            $result = $this->uploadService->upload($request->file('footer_logo'), 'site_settings');
            $q->footer_logo = $result['path'];
        }
        // Update the configuration
        $q->update();
        return response()->json($q);
    }
    public function updateSocialLinks(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'social_links' => 'required|array',
            'social_links.*.url' => 'nullable|url',
            'social_links.*.icon' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $config = SiteSetting::first();
        if (!$config) {
            return response()->json(['error' => 'Site configuration not found.'], 404);
        }

        $socialLinks = $request->input('social_links');

        // Remove any social link where the URL is empty (i.e., remove that link)
        foreach ($socialLinks as $platform => $data) {
            if (empty($data['url'])) {
                unset($socialLinks[$platform]);
            }
        }

        // Update the configuration with the new social links
        $config->social_links = $socialLinks;
        $config->save();

        return response()->json([
            'success' => true,
            'social_links' => $config->social_links
        ]);
    }
}
