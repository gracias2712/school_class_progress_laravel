<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    private function checkAccess() {
        if (Auth::user()->role_id != 1) abort(403, 'Akses Ditolak! Hanya untuk Waka.');
    }

    private function getRoleData($role_id, $viewName, $title) {
        $this->checkAccess();
        
        $users = User::select('users.*', 'roles.role_name', 'classes.class_name')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('classes', 'users.class_id', '=', 'classes.id')
            ->where('users.role_id', $role_id)
            ->whereNotNull('users.username')
            ->orderBy('users.fullname', 'asc')
            ->get();

        $roles = DB::table('roles')->where('id', $role_id)->get(); 
        $classes = DB::table('classes')->orderBy('class_name', 'asc')->get();

        return view('master.users.'.$viewName, compact('users', 'roles', 'classes', 'title', 'role_id'));
    }

    // Arahkan Guru, Wali Kelas, dan BK ke view yang sama (guru.blade.php)
    public function indexGuru() { return $this->getRoleData(2, 'guru', 'Guru'); }
    public function indexWaliKelas() { return $this->getRoleData(3, 'guru', 'Wali Kelas'); }
    public function indexBK() { return $this->getRoleData(4, 'guru', 'Guru BK'); }
    
    // Arahkan Siswa ke view spesifik (siswa.blade.php)
    public function indexSiswa() { return $this->getRoleData(5, 'siswa', 'Siswa'); }

    public function indexOrangTua() {
        $this->checkAccess();

        $role_id = 6;
        $title = 'Orang Tua';

        $users = User::query()
            ->where('role_id', $role_id)
            ->whereNotNull('username')
            ->orderBy('fullname', 'asc')
            ->get();

        $studentsById = User::query()
            ->where('role_id', 5)
            ->whereIn('id', $users->pluck('student_id')->merge($users->pluck('parent_id'))->filter()->unique())
            ->get()
            ->keyBy('id');

        $linkedStudentIds = collect();
        foreach ($users as $guardian) {
            $studentId = $guardian->student_id ?: $guardian->parent_id;
            $linkedStudent = $studentsById->get($studentId);
            $guardian->setRelation('students', $linkedStudent ? collect([$linkedStudent]) : collect());
            if ($linkedStudent) {
                $linkedStudentIds->push($linkedStudent->id);
            }
        }

        $students = User::query()
            ->where('role_id', 5)
            ->orderBy('fullname', 'asc')
            ->get()
            ->whereNotIn('id', $linkedStudentIds->unique())
            ->values();

        return view('master.users.orangtua', compact('users', 'students', 'title', 'role_id'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();

        $rules = [
            'role_id'         => 'required|exists:roles,id',
            'user_identifier' => 'required_unless:role_id,5|string|max:100',
            'fullname'        => 'required_if:role_id,5|string|max:100',
            'nisn_nip'        => 'required_if:role_id,6|nullable|string|max:50',
            'class_id'        => 'nullable|exists:classes,id',
            'subject'         => 'nullable|string|max:100',
            'email'           => ['nullable', 'email', 'max:100', Rule::unique('users', 'email')],
            'student_id' => [
                'required_if:role_id,6',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role_id', 5)),
                Rule::unique('users', 'student_id')->where(fn ($query) => $query->where('role_id', 6)),
                Rule::unique('users', 'parent_id')->where(fn ($query) => $query->where('role_id', 6)),
            ],
        ];

        if ($request->role_id != 5 && $request->role_id != 6) {
            $rules['username'] = 'required|string|max:50|unique:users,username';
        }

        $request->validate($rules);

        $username = strtolower($request->username ?? '');
        $password = 'password';

        if ($request->role_id == 5) {
            $username = strtolower($request->nisn_nip);
        } elseif ($request->role_id == 6) {
            $usernameBase = 'ortu_'.strtolower($request->nisn_nip ?: 'wali');
            $username = $usernameBase;
            $suffix = 1;
            while (User::query()->where('username', $username)->exists()) {
                $username = $usernameBase.'_'.$suffix;
                $suffix++;
            }
        }

        $dataToSave = [
            'role_id'    => $request->role_id,
            'fullname'   => strtoupper($request->role_id == 5 ? $request->fullname : $request->user_identifier),
            'nisn_nip'   => $request->nisn_nip,
            'class_id'   => $request->class_id,
            'subject'    => strtoupper($request->subject),
            'email'      => $request->email ?? ($username.'@ukkscp.com'),
            'username'   => $username,
            'student_id' => $request->role_id == 6 ? $request->student_id : null,
            'parent_id'  => $request->role_id == 6 ? $request->student_id : null,
            'password'   => Hash::make($password),
        ];

        User::create($dataToSave);

        return redirect()->back()->with('success', 'Akun Pengguna berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $this->checkAccess();
        $user = User::findOrFail($id);

        $request->validate([
            'fullname' => 'required|string|max:255',
            'email'    => 'nullable|email|unique:users,email,' . $id,
            'nisn_nip' => 'nullable|string|max:50',
            'subject'  => 'nullable|string|max:100'
        ]);

        $user->update([
            'fullname' => strtoupper($request->fullname),
            'email'    => $request->email,
            'nisn_nip' => $request->nisn_nip,
            'subject'  => strtoupper($request->subject)
        ]);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $this->checkAccess();
        if (Auth::id() == $id) return redirect()->back()->with('error', 'Gagal! Anda sedang menggunakannya.');
        
        User::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Akun berhasil dihapus!');
    }

    // FUNGSI IMPORT DATA PEGAWAI (DINAMIS SESUAI ROLE: GURU MAPEL, WALAS, BK)
    public function importPegawai(Request $request)
    {
        $request->validate([
            'file'    => 'required|mimes:xlsx,xls,csv',
            'role_id' => 'required'
        ]);

        $roleId = $request->role_id;

        if ($roleId == 3) {
            // Role ID 3 = Wali Kelas
            Excel::import(new \App\Imports\WaliKelasImport, $request->file('file'));
        } elseif ($roleId == 4) {
            // Role ID 4 = Guru BK
            Excel::import(new \App\Imports\GuruBkImport, $request->file('file'));
        } else {
            // Role ID 2 = Guru Mapel
            Excel::import(new \App\Imports\GuruImport($roleId), $request->file('file'));
        }

        return redirect()->back()->with('success', 'Data Pegawai berhasil diimport!');
    }

    // FUNGSI IMPORT DATA SISWA
    public function importSiswa(Request $request)
    {
        $request->validate([
            'file'    => 'required|mimes:xlsx,xls,csv',
            'role_id' => 'required'
        ]);

        Excel::import(new \App\Imports\SiswaImport($request->role_id), $request->file('file'));

        return redirect()->back()->with('success', 'Data Siswa berhasil diimport!');
    }
}