<x-mail::message>
    # Hola {{ $user->name }}

    Has cambiado tu correo electrónico. Por favor verifica la nueva dierccion usando el siguiente boton:

    <x-mail::button :url="{{ route('verify', $user->verification_token) }}">
        Confirmar mi cuenta
    </x-mail::button>

    Gracias,<br>
    {{ config('app.name') }}
</x-mail::message>
