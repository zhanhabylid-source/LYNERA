<x-backend-layout>
    <section class="space-y-5">
        <div>
            <h2 class="text-2xl font-semibold text-stone-900">Audit Log Backend</h2>
            <p class="mt-1 text-sm text-stone-600">Seluruh aktivitas sensitif super admin terekam di sini.</p>
        </div>

        <form method="GET" action="{{ route('backend.audit-logs.index') }}" class="grid gap-2 rounded-2xl border border-stone-200 bg-white p-4 sm:grid-cols-3">
            <input type="text" name="action" value="{{ $action }}" placeholder="Filter action" class="rounded-xl border border-stone-300 px-3 py-2 text-sm">
            <input type="text" name="target_type" value="{{ $targetType }}" placeholder="Filter target type" class="rounded-xl border border-stone-300 px-3 py-2 text-sm">
            <button type="submit" class="rounded-xl bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black">Filter</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200">
                    <thead class="bg-stone-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-stone-500">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-stone-500">Actor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-stone-500">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-stone-500">Target</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-stone-500">Meta</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse($logs as $log)
                            <tr class="align-top">
                                <td class="px-4 py-3 text-sm text-stone-700">{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                                <td class="px-4 py-3 text-sm text-stone-700">{{ $log->actor?->email ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-stone-900">{{ $log->action }}</td>
                                <td class="px-4 py-3 text-sm text-stone-700">
                                    <div>{{ $log->target_type ?? '-' }}</div>
                                    <div class="text-xs text-stone-500">{{ $log->target_label ?? '-' }} @if($log->target_id) (#{{ $log->target_id }}) @endif</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-stone-600">
                                    @if(!empty($log->meta))
                                        <pre class="whitespace-pre-wrap break-words">{{ json_encode($log->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-stone-500">Belum ada audit log.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $logs->links() }}
            </div>
        </div>
    </section>
</x-backend-layout>

