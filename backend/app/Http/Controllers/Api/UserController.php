<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Tampilkan daftar seluruh user.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $perPage = max(1, min(100, (int) $request->query('per_page', 10)));

        $users = User::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'message' => 'Daftar user berhasil diambil',
            'data' => $users,
        ], 200);
    }

    /**
     * Tampilkan detail user berdasarkan ID.
     */
    public function show(string|int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'message' => 'Detail user berhasil diambil',
            'data' => $user,
        ], 200);
    }

    /**
     * Edit / update data user berdasarkan ID.
     */
    public function update(Request $request, string|int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'data' => $user->fresh(),
        ], 200);
    }

    /**
     * Hapus user berdasarkan ID.
     */
    public function destroy(string|int $id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        // Hapus semua token user terkait
        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus',
        ], 200);
    }

    /**
     * Edit / update data profil user yang sedang login.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $user->fresh(),
        ], 200);
    }

    /**
     * Hapus akun user yang sedang login.
     */
    public function destroyProfile(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Akun berhasil dihapus',
        ], 200);
    }
}
