<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OptionWriter;
use App\Services\PostAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function index()
    {
        $acf = PostAdminService::getEditableOptionFields('cal_');

        $emails = [
            'admin_email' => DB::table('wp_options')->where('option_name', 'admin_email')->value('option_value'),
            'ca_admin_email' => DB::table('wp_options')->where('option_name', 'ca_admin_email')->value('option_value'),
        ];

        return view('admin.settings.index', [
            'acfScalars' => $acf['scalars'],
            'acfRepeaters' => $acf['repeaters'],
            'emails' => $emails,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'admin_email' => ['nullable', 'email'],
            'ca_admin_email' => ['nullable', 'email'],
            'acf_scalars' => ['nullable', 'array'],
            'acf_repeaters' => ['nullable', 'array'],
        ]);

        if (isset($data['admin_email'])) {
            DB::table('wp_options')->updateOrInsert(
                ['option_name' => 'admin_email'],
                ['option_value' => $data['admin_email'], 'autoload' => 'yes'],
            );
        }

        if (isset($data['ca_admin_email'])) {
            DB::table('wp_options')->updateOrInsert(
                ['option_name' => 'ca_admin_email'],
                ['option_value' => $data['ca_admin_email'], 'autoload' => 'yes'],
            );
        }

        if (! empty($data['acf_scalars'])) {
            foreach ($data['acf_scalars'] as $key => $value) {
                if (is_string($key) && str_starts_with($key, 'cal_')) {
                    OptionWriter::set($key, $value);
                }
            }
        }

        if (! empty($data['acf_repeaters'])) {
            foreach ($data['acf_repeaters'] as $key => $rows) {
                if (is_string($key) && str_starts_with($key, 'cal_')) {
                    OptionWriter::setRepeater($key, is_array($rows) ? $rows : []);
                }
            }
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings saved.');
    }
}
