<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaAdminService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = MediaAdminService::paginate(36, $request->get('q'));

        if ($request->get('picker')) {
            return view('admin.media.picker', compact('media'));
        }

        return view('admin.media.index', compact('media'));
    }

    public function api(Request $request)
    {
        $media = MediaAdminService::paginate(24, $request->get('q'));

        return response()->json([
            'data' => $media->getCollection()->map(fn ($item) => MediaAdminService::formatAttachment($item))->values(),
            'current_page' => $media->currentPage(),
            'last_page' => $media->lastPage(),
            'total' => $media->total(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,svg,pdf'],
        ]);

        $attachment = MediaAdminService::upload($request->file('file'));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'item' => MediaAdminService::formatAttachment($attachment),
            ]);
        }

        return back()->with('success', 'File uploaded.');
    }

    public function destroy(int $id)
    {
        MediaAdminService::destroy($id);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Media deleted.');
    }
}
