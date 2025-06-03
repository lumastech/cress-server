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
            'apk_file' => 'required|file|mimes:apk,zip|max:51200', // 50MB max
        ]);

        $path = $request->file('apk_file')->store('apks', 'public');
        $data = [
            'name' => $request->name,
            'ref_id' => '',
            'file_path' => $path,
            'type' => 'apk',
            'status' => 'active'
        ];
        File::create($data);
        return redirect()->back()->with('success', 'APK uploaded successfully!');
    }


}
