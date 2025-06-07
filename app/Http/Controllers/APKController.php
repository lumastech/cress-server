<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class APKController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'apk_file' => 'required|file|mimes:apk|max:101200', // 100MB max
        ]);

        $path = $request->file('apk_file')->store('apks', 'public');
        $data = [
            'name' => $request->name,
            'ref_id' => '1',
            'file_path' => $path,
            'type' => 'apk',
            'status' => 'active'
        ];
        File::create($data);
        return redirect()->back()->with('success', 'APK uploaded successfully!');
    }

    public function download($id)
    {
        $file = File::findOrFail($id);
        if ($file->type !== 'apk') {
            return redirect()->back()->with('error', 'Invalid file type.');
        }

        $filePath = storage_path('app/public/' . $file->file_path);
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath, $file->name . '.apk');
    }


}
