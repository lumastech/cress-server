<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;

class APKController extends Controller
{
    public function upload(Request $request)
    {
        // if user role is not admin, redirect to dashboard
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('dashboard')->with('error', 'You do not have permission to view activity logs.');
        }
        $request->validate([
            'name' => 'required|string|max:100',
            'apk_file' => 'required|file|mimes:apk,zip|max:101200', // 100MB max
        ]);

        // remove old APKs if needed
        $oldFiles = File::where('type', 'apk')->get();
        foreach ($oldFiles as $file) {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }

        // rename file extension from zip to apk if necessary
        if ($request->file('apk_file')->getClientOriginalExtension() === 'zip') {
            $newFileName = pathinfo($request->file('apk_file')->getClientOriginalName(), PATHINFO_FILENAME) . '.apk';
            $path = $request->file('apk_file')->storeAs('apks', $newFileName, 'public');
            $request->merge(['apk_file' => $newFileName]);
        }else{
            $path = $request->file('apk_file')->store('apks', 'public');
        }

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

    public function download()
    {
        $file = File::where('type', 'apk')->latest()->first();
        if (!$file) {
            return redirect()->back()->with('error', 'No APK file found.');
        }

        $filePath = storage_path('app/public/' . $file->file_path);
        if (!file_exists($filePath)) {
            return redirect()->back()->with('error', 'File not found.');
        }

        return response()->download($filePath, $file->name . '.apk');
    }


}
