<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Menggunakan model User
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Import Rule untuk validasi unique saat update

class EmployeeAccountController extends Controller
{
    /**
     * Menampilkan halaman pengelolaan akun pegawai.
     * (Dipindahkan dari HomeController)
     */
    public function showEmployeeAccounts()
    {
        // Mengambil semua user dengan role 'admin' atau 'manager' dari database
        // Anda mungkin ingin memfilter ini agar tidak menampilkan akun manajer lain jika hanya admin yang boleh mengelola admin.
        $employeeAccounts = User::whereIn('role', ['admin', 'manager'])->get();

        // Daftar peran yang tersedia untuk dipilih saat menambah/mengedit
        $roles = ['admin', 'manager']; 

        return view('dashboard.employee_accounts', [
            'employeeAccounts' => $employeeAccounts,
            'roles' => $roles,
        ]);
    }

    /**
     * Menyimpan akun pegawai baru.
     * (Dipindahkan dari HomeController)
     */
    public function storeEmployeeAccount(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' akan mencari password_confirmation
            'role' => 'required|in:admin,manager', // Hanya izinkan role 'admin' atau 'manager'
            'phone_number' => 'nullable|string|max:20',
        ], [
            'full_name.required' => 'Nama Lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata Sandi wajib diisi.',
            'password.min' => 'Kata Sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi Kata Sandi tidak cocok.',
            'role.required' => 'Peran Pegawai wajib dipilih.',
            'role.in' => 'Peran Pegawai yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Buat user baru
            $user = User::create([
                'full_name' => $request->full_name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role, 
                'phone_number' => $request->phone_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akun pegawai berhasil ditambahkan!',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('Error storing employee account: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan akun: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengambil detail akun pegawai untuk diedit.
     * Digunakan oleh AJAX.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    /**
     * Memperbarui akun pegawai yang ada.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed', // Password opsional saat update
            'role' => 'required|in:admin,manager',
            'phone_number' => 'nullable|string|max:20',
        ], [
            'full_name.required' => 'Nama Lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.min' => 'Kata Sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi Kata Sandi tidak cocok.',
            'role.required' => 'Peran Pegawai wajib dipilih.',
            'role.in' => 'Peran Pegawai yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user->full_name = $request->full_name;
            $user->username = $request->username;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->phone_number = $request->phone_number;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Akun pegawai berhasil diperbarui!',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating employee account: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui akun: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus akun pegawai.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        try {
            // Pastikan user tidak menghapus dirinya sendiri jika Anda tidak ingin itu terjadi
            // if (Auth::id() == $user->id) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Anda tidak dapat menghapus akun Anda sendiri.'
            //     ], 403);
            // }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Akun pegawai berhasil dihapus!'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting employee account: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus akun: ' . $e->getMessage()
            ], 500);
        }
    }
}
