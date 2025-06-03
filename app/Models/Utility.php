<?php
namespace App\Models;
use Illuminate\Http\Request;
use Jenssegers\Agent\Facades\Agent;
class Utility{
    public function countVisitors(Request $request) {
        try {
            if (!Visitor::where('ip', $request->ip())->first()) {
                $visitor = new Visitor();

                $visitor->ip = $request->ip();
                $visitor->device_os = Agent::platform();
                $visitor->device_os_version = Agent::version(Agent::platform());
                $visitor->device_type = Agent::isDesktop()? 'desktop': 'mobile';
                $visitor->browser = Agent::browser();
                $visitor->browser_version = Agent::version(Agent::browser());
                $visitor->save();
            }
        } catch (\Throwable $th) {
            // return $th;
        }
    }
}