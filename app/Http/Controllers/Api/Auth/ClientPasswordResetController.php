<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

/**
 * Recuperação de senha do cidadão: o app pede o link (API) e a troca acontece na página web.
 */
class ClientPasswordResetController extends Controller
{
    public function sendLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        Password::broker('clients')->sendResetLink($request->only('email'));

        // Mesma resposta exista ou não a conta, para não revelar e-mails cadastrados
        return response()->json([
            'message' => 'Se o e-mail estiver cadastrado, você receberá um link para criar uma nova senha.',
        ]);
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.passwords.client-reset', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-zA-Z]/', 'regex:/[0-9]/', 'confirmed'],
        ], [
            'password.min' => 'A senha deve conter no mínimo 8 caracteres.',
            'password.regex' => 'A senha deve conter pelo menos uma letra e um número.',
            'password.confirmed' => 'A confirmação da senha não confere.',
        ]);

        $status = Password::broker('clients')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Client $client, string $password) {
                $client->forceFill(['password' => Hash::make($password)])->save();
                // Encerra as sessões abertas no app com a senha antiga
                $client->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Link inválido ou expirado. Peça um novo pelo app.']);
        }

        return view('auth.passwords.client-reset-done');
    }
}
