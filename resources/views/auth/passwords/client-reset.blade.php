@extends('adminlte::auth.passwords.reset')

@section('password_reset_url', 'cidadao/redefinir-senha')

@section('auth_header', 'Crie uma nova senha para o app')

@push('js')
<script>
    // E-mail vem do link; preenche quando não há old() de um envio anterior
    (function () {
        var field = document.querySelector('input[name="email"]');
        if (field && !field.value) field.value = @json($email ?? '');
    })();
</script>
@endpush
