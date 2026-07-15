@props(['text' => null, 'class' => ''])

@php($items = collect(preg_split('/\r\n|\r|\n/', (string) $text))->map(fn ($line) => trim($line))->filter()->values())

@if ($items->isNotEmpty())
    <ul {{ $attributes->merge(['class' => 'space-y-2.5 ' . $class]) }}>
        @foreach ($items as $item)
            <li class="flex gap-3">
                <svg class="mt-1 h-4 w-4 flex-none text-gold-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>{{ $item }}</span>
            </li>
        @endforeach
    </ul>
@endif
