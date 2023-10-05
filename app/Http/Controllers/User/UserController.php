<?php

namespace App\Http\Controllers\User;

use App\Mail\UserCreated;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Mail;

class UserController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->showAll(User::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required'],
            'email' => ['email', 'required', 'unique:users'],
            'password' => ['required', 'min:6', 'confirmed']
        ]);

        $fields = $request->all();
        $fields['password'] = Hash::make($request->password);
        $fields['verifed'] = User::USUARIO_NO_VERIFICADO;
        $fields['verification_token'] = User::generarVerificationToken();
        $fields['admin'] = User::USUARIO_REGULAR;

        $user = User::create($fields);

        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->validate($request, [
            'email' => ['email', 'unique:users,email,' . $user->id],
            'password' => ['min:6', 'confirmed'],
            'admin' => 'in:' . User::USUARIO_ADMINISTRADOR . ',' . User::USUARIO_REGULAR
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email') && $user->email != $request->email) {
            $user->verified = User::USUARIO_NO_VERIFICADO;
            $user->verification_token = User::generarVerificationToken();
            $user->email = $request->email;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('admin')) {
            if (!$user->esVerificado()) {
                $message = 'Unicamente los usuarios verificados pueden cambiar su valor de administrador';
                return $this->errorResponse($message, 409);
            }

            $user->admin = $request->admin;
        }

        if (!$user->isDirty()) {
            $message = 'Se debe especificar al menos un valor diferente para actualizar';
            return $this->errorResponse($message, 422);
        }

        $user->save();

        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        return $this->showOne($user);
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::USUARIO_VERIFICADO;
        $user->verification_token = null;
        $user->save();

        return $this->showMessage('La cuenta ha sido verificada');
    }

    public function resend(User $user)
    {
        if ($user->esVerificado()) {
            return $this->errorResponse('Este usuario ya ha sido verificado', 409);
        }

        retry(5, function () use ($user) {
            Mail::to($user)->send(new UserCreated($user));
        }, 100);

        return $this->showMessage('El correo de verificacion se ha reenviado');
    }
}
