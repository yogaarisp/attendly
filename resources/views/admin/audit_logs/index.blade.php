@extends('layouts.admin', ['title' => 'Audit Trail Logs'])

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-900 dark:text-white tracking-tight">Audit Trail & Activity Log</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Rekam jejak seluruh aktivitas user, login, presensi, ekspor data, dan manipulasi data sistem</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 shadow-sm dark:shadow-lg">
        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">User Pelaku</label>
                <select name="user_id" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua User</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ $userId == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->role }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Modul</label>
                <select name="module" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Modul</option>
                    @foreach ($modules as $m)
                        <option value="{{ $m }}" {{ $module == $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Aksi</label>
                <select name="action" class="w-full bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-brand-500">
                    <option value="">Semua Aksi</option>
                    @foreach ($actions as $a)
                        <option value="{{ $a }}" {{ $action == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 bg-brand-600 hover:bg-brand-500 text-white font-semibold rounded-xl text-xs flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filter</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm dark:shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-700 dark:text-slate-300">
                <thead class="bg-slate-50 dark:bg-slate-850 text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="px-5 py-3.5">Waktu Log</th>
                        <th class="px-4 py-3.5">User</th>
                        <th class="px-4 py-3.5">Modul</th>
                        <th class="px-4 py-3.5">Aksi</th>
                        <th class="px-4 py-3.5">IP Address</th>
                        <th class="px-4 py-3.5">Metadata Payload</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-850/50 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-slate-500 dark:text-slate-400 text-[11px] whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3.5 font-semibold text-slate-900 dark:text-white">
                                {{ $log->user->name ?? 'System / Anonymous' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-[10px] uppercase text-brand-600 dark:text-brand-400">
                                {{ $log->module }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 text-[10px] font-mono font-bold rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                {{ $log->ip_address }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-[10px] text-slate-500 dark:text-slate-400 max-w-xs truncate">
                                @if ($log->metadata)
                                    <code>{{ json_encode($log->metadata) }}</code>
                                @else
                                    <span class="text-slate-300 dark:text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">
                                Belum ada log aktivitas yang tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
