<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserInvitation;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AcceptInvitationController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invitation = UserInvitation::where('token', $token)->first();

        if (!$invitation || $invitation->isAccepted() || $invitation->isExpired()) {
            return redirect()->route('login')->with('messageDanger', 'Convite inválido ou expirado.');
        }

        return view('auth.accept-invitation', compact('invitation'));
    }

    public function store(Request $request, string $token, AuditLogger $audit): RedirectResponse
    {
        $invitation = UserInvitation::where('token', $token)->first();

        if (!$invitation || $invitation->isAccepted() || $invitation->isExpired()) {
            return redirect()->route('login')->with('messageDanger', 'Convite inválido ou expirado.');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $invitation->name,
            'email' => $invitation->email,
            'password' => Hash::make($data['password']),
            'tenant_id' => $invitation->tenant_id,
        ]);

        if ($invitation->role_id) {
            $user->roles()->attach($invitation->role_id);
        }

        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        $audit->log('user.invitation_accepted', $user, [
            'email' => $user->email,
            'invitation_id' => $invitation->id,
            'tenant_id' => $invitation->tenant_id,
        ]);

        return redirect()->route('admin.index')->with('messageSuccess', 'Bem-vindo(a)! Conta criada com sucesso.');
    }
}
