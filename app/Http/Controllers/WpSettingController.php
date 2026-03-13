<?php

namespace App\Http\Controllers;

use App\Models\WpSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class WpSettingController extends Controller
{
    /**
     * Display a listing of the authenticated user's WP settings.
     */
    public function index()
    {
        $settings = WpSetting::where('user_id', Auth::id())->get();

        return response()->json($settings);
    }

    /**
     * Store a newly created WP setting in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instance_id' => 'required|string|max:255|unique:wp_settings,instance_id',
            'instance_name' => 'required|string|max:255|unique:wp_settings,instance_name',
            'webhook_url' => 'required|url|max:255',
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $setting = WpSetting::create([
            'user_id' => Auth::id(),
            'instance_id' => $request->instance_id,
            'instance_name' => $request->instance_name,
            'webhook_url' => $request->webhook_url,
            'status' => $request->status,
        ]);

        return response()->json($setting, 201);
    }

    /**
     * Display the specified WP setting.
     */
    public function show(WpSetting $wpSetting)
    {
        $this->authorizeOwnership($wpSetting);

        return response()->json($wpSetting);
    }

    /**
     * Update the specified WP setting in storage.
     */
    public function update(Request $request, WpSetting $wpSetting)
    {
        $this->authorizeOwnership($wpSetting);

        $validator = Validator::make($request->all(), [
            'instance_id' => 'sometimes|required|string|max:255|unique:wp_settings,instance_id,' . $wpSetting->id,
            'instance_name' => 'sometimes|required|string|max:255|unique:wp_settings,instance_name,' . $wpSetting->id,
            'webhook_url' => 'sometimes|required|url|max:255',
            'status' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $wpSetting->update($request->only(['instance_id', 'instance_name', 'webhook_url']));

        return response()->json($wpSetting);
    }

    /**
     * Remove the specified WP setting from storage.
     */
    public function destroy(WpSetting $wpSetting)
    {
        $this->authorizeOwnership($wpSetting);
        $wpSetting->delete();

        return response()->json(['message' => 'Configuração WhatsApp removida com sucesso.']);
    }

    /**
     * Remove the specified WP setting from storage by instance name.
     */
    public function destroyByName(string $name)
    {
        $wpSetting = WpSetting::where('user_id', Auth::id())
            ->where('instance_name', $name)
            ->firstOrFail();

        $wpSetting->delete();

        return response()->json(['message' => 'Configuração WhatsApp removida com sucesso.']);
    }

    /**
     * Authorize that the authenticated user owns the WP setting.
     */
    protected function authorizeOwnership(WpSetting $wpSetting): void
    {
        if ($wpSetting->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }
}
