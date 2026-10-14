@extends('sqlviewer.layout')

@section('title', 'Tables - ' . $database)

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $database }}</h1>
            <p class="text-sm text-gray-500">{{ $tables->count() }} tables</p>
        </div>

        <form method="GET" class="flex gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search table name..."
                   class="border rounded px-3 py-2 text-sm w-64">
            <button class="bg-slate-800 text-white px-4 py-2 rounded text-sm">Search</button>
            @if ($search !== '')
                <a href="{{ route('viewer.index') }}" class="px-3 py-2 text-sm text-gray-600">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-2 w-12">#</th>
                    <th class="px-4 py-2">Table</th>
                    <th class="px-4 py-2 text-right">Rows</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tables->values() as $i => $t)
                    <tr class="border-t hover:bg-blue-50">
                        <td class="px-4 py-2 text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('viewer.show', $t->table_name) }}"
                               class="text-blue-600 hover:underline">{{ $t->table_name }}</a>
                        </td>
                        <td class="px-4 py-2 text-right tabular-nums
                                   {{ $t->row_count == 0 ? 'text-gray-400' : '' }}">
                            {{ number_format($t->row_count) }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No tables found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection