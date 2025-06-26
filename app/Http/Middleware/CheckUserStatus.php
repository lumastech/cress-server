<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CheckUserStatus
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && ($user->status !== 'active' && $user->status !== 'pending'  && $user->role !== 'admin')) {
            $request->session()->flush();

            // Optional: redirect with message based on status
            $message = match ($user->status) {
                'suspended' => 'Your account is suspended. Please contact support.',
                'rejected'  => 'Your account application was rejected.',
                'pending'  => 'Your account application is pending approval.',
                'banned'    => 'Your account has been banned.',
                default     => 'Your account is not active. kindly contact support.',
            };

            return redirect()->route('login')->withErrors([
                'email' => $message,
            ]);
        }

        // If the user is active or pending, redirect to profile show if current route is not profile.show
        if ($request->route()->getName() !== 'profile.show' && $request->route()->getName() !== 'logout' && $user && $user->status === 'pending') {
            return redirect()->route('profile.show')->with('message', 'Please complete your profile by filling in all required fields.');
        }


        return $next($request);
    }
}

