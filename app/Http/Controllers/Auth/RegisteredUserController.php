<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'fname' => ['required', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
    
        $user = User::create([
            'fname' => $request->input('fname'),
            'lname' => $request->input('lname'),
            'name' => $request->input('first_name') . ' ' . $request->input('last_name'),
            'address' => $request->address,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'admin', // Set the user_type to 'admin'
        ]);

        $user->sendEmailVerificationNotification();
    
        // Log the user registration as an activity
        activity('created')
            ->causedBy($user) // Set the user who caused the activity
            ->performedOn($user) // Set the user as the subject of the activity
            ->withProperties(['type' => 'registration']) // Add a property to specify the type
            ->log('User Registration');
    
        Auth::login($user);
    
        return redirect(RouteServiceProvider::HOME);
    }
    
}
