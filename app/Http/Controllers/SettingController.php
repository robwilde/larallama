<?php

namespace App\Http\Controllers;

use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show()
    {
        if (! Setting::exists()) {
            $setting = Setting::create([
                'steps' => [
                    'setup_secrets' => false,
                ],
                'meta_data' => [
                    'openai' => [
                        'models' => [],
                    ],
                ],
                'secrets' => [
                    'openai' => [
                        'api_key' => null,
                        'api_url' => 'https://api.openai.com/v1',
                    ],
                    'claude' => [
                        'api_key' => null,
                    ],
                    'groq' => [
                        'api_key' => null,
                        'api_url' => 'https://api.groq.com/openai/v1/',
                    ],
                    'ollama' => [
                        'api_key' => 'ollama',
                        'api_url' => 'http://localhost:11434/api/',
                    ],
                ],
            ]);
        } else {
            $setting = Setting::first();
        }

        return inertia('Settings/Show', [
            'setting' => new SettingResource($setting),
        ]);
    }

    protected function updateStep(Setting $setting): Setting
    {
        $steps = $setting->steps;
        $steps['setup_secrets'] = true;
        $setting->steps = $steps;
        $setting->save();

        return $setting;
    }

    public function updateClaude(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'api_key' => 'string|required',
        ]);

        $secrets = $setting->secrets;
        $secrets['claude'] = $validated;
        $setting->secrets = $secrets;
        $setting->save();
        $this->updateStep($setting);

        return back();
    }

    public function updateOllama(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'api_key' => 'string|required',
            'api_url' => 'string|required',
        ]);

        $secrets = $setting->secrets;
        $secrets['ollama'] = $validated;
        $setting->secrets = $secrets;
        $setting->save();
        $this->updateStep($setting);

        return back();
    }

    public function updateGroq(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'api_key' => 'string|required',
            'api_url' => 'string|required',
        ]);

        $secrets = $setting->secrets;
        $secrets['groq'] = $validated;
        $setting->secrets = $secrets;
        $setting->save();
        $this->updateStep($setting);

        return back();
    }

    public function updateOpenAi(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'api_key' => 'string|required',
            'api_url' => 'string|required',
        ]);

        $secrets = $setting->secrets;
        $secrets['openai'] = $validated;
        $setting->secrets = $secrets;
        $setting->save();
        $this->updateStep($setting);

        return back();
    }
}