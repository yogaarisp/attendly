@extends('layouts.admin', ['title' => 'Manajemen Karyawan'])

@section('content')
<div class="space-y-6">

    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Daftar Karyawan</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Kelola data seluruh personil, akun, penempatan cabang, dan status</p>
        </div>
        <a href="{{ route('admin.employees.create') }}" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-brand-500/25 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah Karyawan</span>
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg transition-colors">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Cari nama, NIK, email..."
                    class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-brand-500"
                >
            </div>
            <div>
                <select name="department_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Departemen</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="branch_id" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Cabang</option>
                    @foreach ($branches as $br)
                        <option value="{{ $br->id }}" {{ $branchId == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filter</span>
                </button>
                @if($search || $departmentId || $branchId)
                    <a href="{{ route('admin.employees.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 rounded-xl text-xs flex items-center justify-center transition-colors">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Employee Table Card -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm dark:shadow-lg overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-850 text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Karyawan</th>
                        <th class="px-4 py-3.5">Kontak</th>
                        <th class="px-4 py-3.5">Departemen & Posisi</th>
                        <th class="px-4 py-3.5">Cabang</th>
                        <th class="px-4 py-3.5">Tgl Gabung</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($employees as $emp)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-850/50 transition-colors">
                            <td class="px-5 py-3.5 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-brand-500/20 text-brand-600 dark:text-brand-300 font-bold text-xs flex items-center justify-center shrink-0 border border-brand-500/30">
                                    {{ strtoupper(substr($emp->full_name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $emp->full_name }}</span>
                                    <span class="text-[10px] text-slate-500 font-mono">{{ $emp->employee_code }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="text-slate-800 dark:text-slate-300 block font-mono">{{ $emp->email }}</span>
                                <span class="text-[10px] text-slate-500 font-mono">{{ $emp->phone ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-semibold text-slate-900 dark:text-white block">{{ $emp->department->name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $emp->position->name ?? '-' }}</span>
                            </td>
                            <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-300">{{ $emp->branch->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-500 dark:text-slate-400">{{ $emp->join_date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $emp->status === 'active' ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' : 'bg-rose-500/20 text-rose-700 dark:text-rose-300' }}">
                                    {{ $emp->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.employees.edit', $emp->id) }}"
                                        class="p-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors"
                                        title="Edit Data">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </a>
                                    {{-- Toggle aktif/nonaktif --}}
                                    <button type="button"
                                        onclick="confirmToggle({{ $emp->id }}, '{{ addslashes($emp->full_name) }}', '{{ $emp->status }}')"
                                        class="p-1.5 {{ $emp->status === 'active' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500/20' }} rounded-lg transition-colors cursor-pointer"
                                        title="{{ $emp->status === 'active' ? 'Nonaktifkan' : 'Aktifkan Kembali' }}">
                                        <i data-lucide="{{ $emp->status === 'active' ? 'user-x' : 'user-check' }}" class="w-3.5 h-3.5"></i>
                                    </button>
                                    {{-- Hapus permanen --}}
                                    <button type="button"
                                        onclick="confirmDelete({{ $emp->id }}, '{{ addslashes($emp->full_name) }}', '{{ $emp->employee_code }}')"
                                        class="p-1.5 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 rounded-lg transition-colors cursor-pointer"
                                        title="Hapus Permanen">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">
                                Tidak ada data karyawan yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $employees->links() }}
        </div>
    </div>

</div>

{{-- ── Modal Konfirmasi Toggle Status ────────────────────────────────── --}}
<div id="modal-toggle" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 w-full max-w-sm shadow-2xl space-y-4">
        <div class="flex items-start gap-3">
            <div id="modal-icon-wrap" class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0">
                <i id="modal-icon" data-lucide="user-x" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 id="modal-title" class="text-sm font-bold text-slate-900 dark:text-white"></h3>
                <p id="modal-desc" class="text-xs text-slate-500 dark:text-slate-400 mt-1"></p>
            </div>
        </div>
        <div class="flex gap-2 pt-1">
            <button type="button" onclick="closeToggleModal()"
                class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-xs transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" id="modal-confirm-btn" onclick="submitToggle()"
                class="flex-1 py-2.5 font-bold rounded-xl text-xs text-white transition-colors cursor-pointer">
                Konfirmasi
            </button>
        </div>
    </div>
</div>

{{-- ── Modal Konfirmasi Hapus Permanen ──────────────────────────────── --}}
<div id="modal-delete" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-500/30 rounded-2xl p-6 w-full max-w-sm shadow-2xl space-y-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-500/15 text-rose-500 flex items-center justify-center shrink-0">
                <i data-lucide="trash-2" class="w-5 h-5"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Hapus Permanen?</h3>
                <p id="delete-desc" class="text-xs text-slate-500 dark:text-slate-400 mt-1"></p>
            </div>
        </div>
        {{-- Warning box --}}
        <div class="p-3 bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/30 rounded-xl text-xs text-rose-700 dark:text-rose-300 space-y-1">
            <p class="font-bold flex items-center gap-1.5"><i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i> Tindakan ini tidak dapat dibatalkan!</p>
            <p>Seluruh data karyawan termasuk akun login dan riwayat absensi terkait akan ikut terhapus.</p>
        </div>
        <div class="flex gap-2 pt-1">
            <button type="button" onclick="closeDeleteModal()"
                class="flex-1 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold rounded-xl text-xs transition-colors cursor-pointer">
                Batal
            </button>
            <button type="button" onclick="submitDelete()"
                class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-500 font-bold rounded-xl text-xs text-white transition-colors cursor-pointer">
                Ya, Hapus Permanen
            </button>
        </div>
    </div>
</div>

{{-- Hidden forms --}}
<form id="form-toggle" method="POST" class="hidden">
    @csrf
    @method('PATCH')
</form>
<form id="form-delete" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('scripts')
<script>
    let toggleUrl = '';
    let deleteUrl  = '';

    // ── Toggle Aktif / Nonaktif ──────────────────────────────────────
    function confirmToggle(id, name, currentStatus) {
        const isActive = currentStatus === 'active';
        toggleUrl = `/admin/employees/${id}/toggle-status`;

        const iconWrap = document.getElementById('modal-icon-wrap');
        const icon     = document.getElementById('modal-icon');
        const title    = document.getElementById('modal-title');
        const desc     = document.getElementById('modal-desc');
        const btn      = document.getElementById('modal-confirm-btn');

        if (isActive) {
            iconWrap.className = 'w-10 h-10 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center shrink-0';
            icon.setAttribute('data-lucide', 'user-x');
            title.textContent = `Nonaktifkan ${name}?`;
            desc.textContent  = 'Karyawan tidak akan bisa login dan melakukan absensi. Bisa diaktifkan kembali kapan saja.';
            btn.className     = 'flex-1 py-2.5 bg-amber-600 hover:bg-amber-500 font-bold rounded-xl text-xs text-white transition-colors cursor-pointer';
            btn.textContent   = 'Ya, Nonaktifkan';
        } else {
            iconWrap.className = 'w-10 h-10 rounded-xl bg-emerald-500/15 text-emerald-500 flex items-center justify-center shrink-0';
            icon.setAttribute('data-lucide', 'user-check');
            title.textContent = `Aktifkan kembali ${name}?`;
            desc.textContent  = 'Karyawan akan bisa login dan melakukan absensi kembali.';
            btn.className     = 'flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 font-bold rounded-xl text-xs text-white transition-colors cursor-pointer';
            btn.textContent   = 'Ya, Aktifkan';
        }

        lucide.createIcons();
        document.getElementById('modal-toggle').classList.remove('hidden');
    }

    function closeToggleModal() {
        document.getElementById('modal-toggle').classList.add('hidden');
    }

    function submitToggle() {
        const form = document.getElementById('form-toggle');
        form.action = toggleUrl;
        form.submit();
    }

    document.getElementById('modal-toggle').addEventListener('click', function(e) {
        if (e.target === this) closeToggleModal();
    });

    // ── Hapus Permanen ───────────────────────────────────────────────
    function confirmDelete(id, name, code) {
        deleteUrl = `/admin/employees/${id}`;
        document.getElementById('delete-desc').textContent =
            `Karyawan "${name}" (${code}) akan dihapus secara permanen dari sistem.`;
        lucide.createIcons();
        document.getElementById('modal-delete').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('modal-delete').classList.add('hidden');
    }

    function submitDelete() {
        const form = document.getElementById('form-delete');
        form.action = deleteUrl;
        form.submit();
    }

    document.getElementById('modal-delete').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>
@endpush
