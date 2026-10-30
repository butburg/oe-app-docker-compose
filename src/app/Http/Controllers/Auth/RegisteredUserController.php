<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Session key that ties the "check your email" page to its pending registration.
     */
    private const SESSION_KEY = 'pending_registration_id';

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * No account is created yet, only a pending registration and a confirmation mail.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $this->lowercaseEmail($request);
        $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:' . User::class],
            // No unique rule on the email: an existing account must not be revealed here
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', Rules\Password::defaults()], // Removed 'confirmed' rule
            'privacy_policy' => 'accepted',
        ]);
        PendingRegistration::ensureCanSendTo($request->email);

        PendingRegistration::deleteExpired();
        // A new registration for the same email replaces the old one, its link stops working
        PendingRegistration::where('email', $request->email)->delete();

        $pending = new PendingRegistration([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $pending->sendLink();

        $request->session()->put(self::SESSION_KEY, $pending->id);

        return redirect()->route('register.pending');
    }

    /**
     * Show the "check your email" page for the registration of this session.
     */
    public function pending(Request $request): View|RedirectResponse
    {
        $pending = $this->currentPending($request);
        if (!$pending) {
            return $this->registerAgain();
        }

        return view('auth.register-pending', ['email' => $pending->email]);
    }

    /**
     * Send the confirmation mail again, with a new link.
     */
    public function resend(Request $request): RedirectResponse
    {
        $pending = $this->currentPending($request);
        if (!$pending) {
            return $this->registerAgain();
        }

        PendingRegistration::ensureCanSendTo($pending->email);
        $pending->sendLink();

        return back()->with('status', 'link-sent');
    }

    /**
     * Correct the email address and send the link to the new one.
     */
    public function updateEmail(Request $request): RedirectResponse
    {
        $pending = $this->currentPending($request);
        if (!$pending) {
            return $this->registerAgain();
        }

        $this->lowercaseEmail($request);
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);
        PendingRegistration::ensureCanSendTo($validated['email']);

        PendingRegistration::where('email', $validated['email'])->whereKeyNot($pending->id)->delete();

        $pending->email = $validated['email'];
        $pending->sendLink();

        return back()->with('status', 'link-sent');
    }

    /**
     * Abort the registration: the pending data and its link are discarded.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $this->currentPending($request)?->delete();
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('register')->with('status', 'Your registration was cancelled.');
    }

    /**
     * The link in the mail only shows a button. Mail scanners open links on their own,
     * a GET that created the account would use up the link before the user clicks it.
     */
    public function showConfirm(string $token): View
    {
        $pending = PendingRegistration::findByToken($token);
        if (!$pending) {
            return view('auth.register-link-invalid');
        }

        return view('auth.register-confirm', ['pending' => $pending, 'token' => $token]);
    }

    /**
     * Create the account from the button on the link page. Works in any tab or device,
     * since everything needed is stored with the token, not in the session.
     */
    public function confirm(Request $request, string $token): View|RedirectResponse
    {
        $pending = PendingRegistration::findByToken($token);
        if (!$pending) {
            return view('auth.register-link-invalid');
        }

        // Someone else may have taken the name or email since the form was sent
        if (User::where('email', $pending->email)->orWhere('name', $pending->name)->exists()) {
            $pending->delete();
            return view('auth.register-link-invalid', [
                'reason' => 'The username or email is already taken by now. Please register again.',
            ]);
        }

        $user = DB::transaction(function () use ($pending) {
            $user = new User([
                'name' => $pending->name,
                'email' => $pending->email,
            ]);
            // Already hashed, so set it without the fillable and cast round trip
            $user->forceFill([
                'password' => $pending->password,
                'email_verified_at' => now(),
            ])->save();

            $pending->delete();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget(self::SESSION_KEY);
        session()->flash('notif.success', 'Welcome, ' . $user->name . '! Your account is ready to grow.');

        return redirect(route('dashboard', absolute: false));
    }

    /**
     * Emails are stored lowercase, so the input is lowercased instead of rejected.
     */
    private function lowercaseEmail(Request $request): void
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => Str::lower($request->input('email'))]);
        }
    }

    /**
     * The session no longer knows the pending registration, usually because it expired.
     */
    private function registerAgain(): RedirectResponse
    {
        return redirect()->route('register')
            ->with('status', 'Your registration session has expired. Please register again.');
    }

    private function currentPending(Request $request): ?PendingRegistration
    {
        $id = $request->session()->get(self::SESSION_KEY);

        return $id ? PendingRegistration::find($id) : null;
    }
}
