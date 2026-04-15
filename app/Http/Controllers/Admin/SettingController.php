<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SettingController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.view', only: ['index']),
            new Middleware('permission:settings.edit', only: ['update']),
        ];
    }

    public function index()
    {
        $settings = [
            'qimera_api_url' => Setting::get('qimera_api_url', 'https://factura.grupoqimera.co/api'),
            'qimera_api_token' => Setting::get('qimera_api_token', ''),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
			'qimera_api_url' => 'required|url',
			'qimera_api_token' => 'nullable|string',
		]);

        foreach ($data as $key => $value) {
            Setting::set($key, $value, 'qimera');
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Configuración actualizada.');
    }
}