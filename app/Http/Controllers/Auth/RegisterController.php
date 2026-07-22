<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Teams\CreateTeamAction;
use App\Events\Auth\UserRegistered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showForm(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request, CreateTeamAction $createTeam): RedirectResponse
    {
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => $request->password,    // cast handles hashing
            'is_active' => true,
        ]);

        if ($request->account_type === 'team') {
            // CreateTeamAction creates the Team, TeamMember(owner), and assigns team_owner role
            $createTeam->execute($user, ['name' => $request->team_name]);
        } else {
            $user->assignRole('individual');
        }

        // Fire event → queued WelcomeNotification and admin alert
        UserRegistered::dispatch($user);

        // Trigger email verification send
        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('app.dashboard');
    }
}
