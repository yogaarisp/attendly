@extends('layouts.admin', ['title' => 'Tambah Karyawan Baru'])

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Tambah Karyawan Baru</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Buat akun login dan profil data induk karyawan</p>
        </div>
        <a href="{{ route('admin.employees.index') }}" class="px-3.5 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 bg-rose-500/10 border border-rose-500/30 rounded-2xl text-rose-600 dark:text-rose-400 text-xs">
            <p class="font-bold mb-1">Terdapat kesalahan input:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm dark:shadow-xl transition-colors">
        <form method="POST" action="{{ route('admin.employees.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                
                <!-- Employee Code / NIK -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">NIK / Kode Karyawan <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="employee_code" 
                        value="{{ old('employee_code', 'EMP-'.date('Y').'-'.rand(100, 999)) }}" 
                        required
                        class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 font-mono"
                    >
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="full_name" 
                        value="{{ old('full_name') }}" 
                        required
                        placeholder="Contoh: Muhammad Rizky"
                        class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500"
                    >
                </div>

                <!-- Email (Login) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Email Akun (Login) <span class="text-rose-500">*</span></label>
                    <input 
                        type="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required
                        placeholder="karyawan@perusahaan.com"
                        class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 font-mono"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Password Login <span class="text-rose-500">*</span></label>
                    <input 
                        type="password" 
                        name="password" 
                        value="password" 
                        required
                        class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 font-mono"
                    >
                </div>

                <!-- Phone Number -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nomor Telepon / WhatsApp</label>
                    <input 
                        type="text" 
                        name="phone" 
                        value="{{ old('phone') }}" 
                        placeholder="081234567890"
                        class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-brand-500 font-mono"
                    >
                </div>

                <!-- Gender -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="gender" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <!-- Department -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Departemen <span class="text-rose-500">*</span></label>
                    <select id="department_select" name="department_id" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="">Pilih Departemen</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Position -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Posisi / Jabatan <span class="text-rose-500">*</span></label>
                    <select id="position_select" name="position_id" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="">Pilih Jabatan</option>
                        @foreach ($positions as $pos)
                            <option value="{{ $pos->id }}" data-dept="{{ $pos->department_id }}" {{ old('position_id') == $pos->id ? 'selected' : '' }}>
                                {{ $pos->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Branch -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Penempatan Kantor Cabang <span class="text-rose-500">*</span></label>
                    <select name="branch_id" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        @foreach ($branches as $br)
                            <option value="{{ $br->id }}" {{ old('branch_id') == $br->id ? 'selected' : '' }}>{{ $br->name }} ({{ $br->timezone }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Join Date -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tanggal Bergabung <span class="text-rose-500">*</span></label>
                    <input 
                        type="date" 
                        name="join_date" 
                        value="{{ old('join_date', date('Y-m-d')) }}" 
                        required
                        class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500 font-mono"
                    >
                </div>

                <!-- Status -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Status Karyawan <span class="text-rose-500">*</span></label>
                    <select name="status" required class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Aktif (Dapat Melakukan Absensi)</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Non-Aktif (Akses Diblokir)</option>
                    </select>
                </div>

            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-3">
                <a href="{{ route('admin.employees.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-xs transition-colors">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-brand-500/25 transition-all cursor-pointer">
                    Simpan Data Karyawan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection

@push('scripts')
<script>
    // Filter positions dynamically when department changes
    const deptSelect = document.getElementById('department_select');
    const posSelect = document.getElementById('position_select');

    deptSelect.addEventListener('change', function() {
        const deptId = this.value;
        const options = posSelect.querySelectorAll('option');

        options.forEach(opt => {
            if (!opt.value) return; // Keep placeholder
            if (!deptId || opt.getAttribute('data-dept') === deptId) {
                opt.style.display = '';
            } else {
                opt.style.display = 'none';
            }
        });

        // Reset if selected option is hidden
        const selected = posSelect.selectedOptions[0];
        if (selected && selected.style.display === 'none') {
            posSelect.value = '';
        }
    });
</script>
@endpush
