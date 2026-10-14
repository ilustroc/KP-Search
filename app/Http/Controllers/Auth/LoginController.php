<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Services\Audit\AuditRecorder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AuditRecorder $auditRecorder): RedirectResponse
    {
        $credentials = [
            'email' => Str::lower($request->string('email')->toString()),
            'password' => $request->string('password')->toString(),
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            $auditRecorder->record($request, 'authentication.login', 'denied');

            throw ValidationException::withMessages([
                'email' => 'Las credenciales proporcionadas no son válidas o la cuenta está inactiva.',
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        $auditRecorder->record($request, 'authentication.login', 'success', user: $user);

        return redirect()->intended(route('search.index'));
    }

    public function destroy(Request $request, AuditRecorder $auditRecorder): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $auditRecorder->record($request, 'authentication.logout', 'success', user: $user);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
