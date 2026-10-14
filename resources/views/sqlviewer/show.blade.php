@extends('sqlviewer.layout')

@section('title', $table)

@section('content')
    <a href="{{ route('viewer.index') }}" class="text-sm text-blue-600 hover:underline">&larr; All tables</a>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-2 mb-4">
        <div>
            <h1 class="text-2xl font-bold">{{ $table }}</h1>
            <p class="text-sm text-gray-500">
                {{ number_format($rows->total()) }} rows
                @if ($search !== '') matching "{{ $search }}" @endif
                &middot; {{ $visible->count() }} columns
                @if ($hidden > 0)
                    &middot; <span title="Image / binary columns are hidden">{{ $hidden }} hidden</span>
                @endif
            </p>
        </div>

        <form method="GET" class="flex gap-2">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">
            <input type="text" name="q" value="{{ $search }}" placeholder="Search all columns..."
                   class="border rounded px-3 py-2 text-sm w-64">
            <button class="bg-slate-800 text-white px-4 py-2 rounded text-sm">Search</button>
            @if ($search !== '')
                <a href="{{ route('viewer.show', $table) }}" class="px-3 py-2 text-sm text-gray-600">Clear</a>
            @endif
        </form>
    </div>

    <div class="bg-white rounded shadow overflow-auto max-h-[70vh]">
        <table class="text-sm whitespace-nowrap">
            <thead class="bg-gray-50 text-left text-gray-600 sticky top-0">
                <tr>
                    @foreach ($visible as $col)
                        @php
                            $name = $col->COLUMN_NAME;
                            $nextDir = ($sort === $name && $dir === 'asc') ? 'desc' : 'asc';
                        @endphp
                        <th class="px-3 py-2 border-b" title="{{ $col->DATA_TYPE }}">
                            <a href="{{ request()->fullUrlWithQuery(['sort' => $name, 'dir' => $nextDir, 'page' => 1]) }}"
                               class="hover:text-blue-600">
                                {{ $name }}
                                @if ($sort === $name)
                                    <span>{{ $dir === 'asc' ? '▲' : '▼' }}</span>
                                @endif
                            </a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr class="border-t hover:bg-blue-50">
                        @foreach ($visible as $col)
                            @php $value = $row->{$col->COLUMN_NAME}; @endphp
                            <td class="px-3 py-1.5 max-w-xs truncate" title="{{ $value }}">
                                @if (is_null($value))
                                    <span class="text-gray-300">NULL</span>
                                @else
                                    {{ $value }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $visible->count() }}" class="px-4 py-6 text-center text-gray-500">
                            No rows found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rows->links() }}
    </div>
@endsection