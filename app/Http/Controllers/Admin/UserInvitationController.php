<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\UserInvitationNotification;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class UserInvitationController extends Controller
{
    public function __construct(protected AuditLogger $audit)
    {
        $this->middleware(['can:users']);
    }

    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        $invitations = UserInvitation::with('role')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereNull('accepted_at')
            ->latest()
            ->paginate(10);

        return view('admin.pages.users.invite', compact('roles', 'invitations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['nullable', 'exists:roles,id'],
        ]);

        $tenantId = auth()->user()->tenant_id;

        if (User::where('tenant_id', $tenantId)->where('email', $data['email'])->exists()) {
            return back()->withInput()->with('messageDanger', 'Já existe um usuário com este e-mail nesta organização.');
        }

        $invitation = UserInvitation::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'email' => $data['email'],
            ],
            [
                'name' => $data['name'],
                'role_id' => $data['role_id'] ?? null,
                'invited_by' => auth()->id(),
                'token' => UserInvitation::generateToken(),
                'accepted_at' => null,
                'expires_at' => now()->addDays(7),
            ]
        );

        Notification::route('mail', $invitation->email)
            ->notify(new UserInvitationNotification($invitation));

        $this->audit->log('user.invited', $invitation, [
            'email' => $invitation->email,
            'role_id' => $invitation->role_id,
            'accept_url' => route('invitations.accept.show', $invitation->token),
        ]);

        return redirect()
            ->route('users.invite.create')
            ->with('messageSuccess', 'Convite enviado para ' . $invitation->email);
    }

    public function destroy(int $id): RedirectResponse
    {
        $invitation = UserInvitation::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $this->audit->log('user.invitation_cancelled', $invitation, [
            'email' => $invitation->email,
        ]);

        $invitation->delete();

        return back()->with('messageSuccess', 'Convite cancelado.');
    }
}
