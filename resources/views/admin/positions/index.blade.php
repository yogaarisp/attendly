@extends('layouts.admin', ['title' => 'Master Jabatan / Posisi'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Jabatan & Posisi Karyawan</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Master data posisi kerja dan hubungannya dengan departemen</p>
        </div>
        <button type="button" onclick="openAddModal()" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-500 text-white text-xs font-bold rounded-xl shadow-lg shadow-brand-500/25 flex items-center justify-center gap-2 transition-all cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Tambah Jabatan</span>
        </button>
    </div>

    <!-- Filter by Department -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg transition-colors">
        <form method="GET" action="{{ route('admin.positions.index') }}" class="flex items-center gap-3">
            <div class="flex-1 sm:max-w-xs">
                <select name="department_id" onchange="this.form.submit()" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Departemen</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($departmentId)
                <a href="{{ route('admin.positions.index') }}" class="text-xs text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">Reset Filter</a>
            @endif
        </form>
    </div>

    <!-- Positions Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm dark:shadow-lg overflow-hidden transition-colors">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-850 text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Nama Jabatan</th>
                        <th class="px-4 py-3.5">Departemen Terkait</th>
                        <th class="px-4 py-3.5">Jumlah Karyawan</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse ($positions as $pos)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-850/50 transition-colors">
                            <td class="px-5 py-3.5 font-bold text-slate-900 dark:text-white">{{ $pos->name }}</td>
                            <td class="px-4 py-3.5 font-semibold text-brand-600 dark:text-brand-400">{{ $pos->department->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-slate-800 dark:text-slate-200">{{ $pos->employees_count }} Orang</td>
                            <td class="px-4 py-3.5">
                                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $pos->status === 'active' ? 'bg-emerald-500/20 text-emerald-700 dark:text-emerald-300' : 'bg-rose-500/20 text-rose-700 dark:text-rose-300' }}">
                                    {{ $pos->status === 'active' ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button 
                                        type="button" 
                                        onclick="openEditModal({{ json_encode($pos) }})"
                                        class="p-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white rounded-lg transition-colors cursor-pointer"
                                        title="Edit"
                                    >
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </button>
                                    @if ($pos->employees_count == 0)
                                        <form action="{{ route('admin.positions.destroy', $pos->id) }}" method="POST" onsubmit="return confirm('Hapus jabatan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 rounded-lg transition-colors cursor-pointer" title="Hapus">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500 dark:text-slate-400">
                                Belum ada data jabatan / posisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form (Add / Edit) -->
    <div id="pos-modal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 max-w-md w-full shadow-2xl transition-colors">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modal-title" class="text-sm font-bold text-slate-900 dark:text-white">Tambah Jabatan</h3>
                <button type="button" onclick="closeModal()" class="p-1 text-slate-400 hover:text-slate-700 dark:hover:text-white cursor-pointer">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <form id="pos-form" method="POST" action="{{ route('admin.positions.store') }}" class="space-y-4">
                @csrf
                <div id="method-container"></div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Departemen <span class="text-rose-500">*</span></label>
                    <select id="pos-dept" name="department_id" required class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Jabatan <span class="text-rose-500">*</span></label>
                    <input type="text" id="pos-name" name="name" required placeholder="Contoh: Senior Web Developer" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                    <select id="pos-status" name="status" class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                        <option value="active">Aktif</option>
                        <option value="inactive">Non-Aktif</option>
                    </select>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-500 text-white font-bold rounded-xl text-xs shadow-lg shadow-brand-500/25 transition-all cursor-pointer">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const modal = document.getElementById('pos-modal');
    const form = document.getElementById('pos-form');
    const modalTitle = document.getElementById('modal-title');
    const deptSelect = document.getElementById('pos-dept');
    const nameInput = document.getElementById('pos-name');
    const statusSelect = document.getElementById('pos-status');
    const methodContainer = document.getElementById('method-container');

    function openAddModal() {
        modalTitle.textContent = 'Tambah Jabatan Baru';
        form.action = "{{ route('admin.positions.store') }}";
        methodContainer.innerHTML = '';
        nameInput.value = '';
        statusSelect.value = 'active';
        modal.classList.remove('hidden');
    }

    function openEditModal(pos) {
        modalTitle.textContent = 'Edit Jabatan';
        form.action = `/admin/positions/${pos.id}`;
        methodContainer.innerHTML = '@method("PUT")';
        deptSelect.value = pos.department_id;
        nameInput.value = pos.name;
        statusSelect.value = pos.status;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        modal.classList.add('hidden');
    }
</script>
@endpush
