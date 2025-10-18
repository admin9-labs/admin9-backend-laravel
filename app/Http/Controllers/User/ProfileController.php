<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function show()
    {
        $user = user();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'email_verified' => $user->email_verified,
            'role' => $user->role()->value,
            'avatar' => $user->avatar,
            'is_password_set' => $user->is_password_set,
        ]);
    }

    /**
     * Update user profile.
     */
    public function update(): JsonResponse
    {
        $user = user();
        $data = $this->validate(request(), [
            'name' => 'required|string|min:2|max:32|unique:users,name,'.$user->id,
        ]);
        $user->name = $data['name'];
        $user->save();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
        ]);
    }
}
