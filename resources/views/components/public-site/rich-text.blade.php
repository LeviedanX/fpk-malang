@props(['text' => null])

@php($paragraphs = collect(preg_split('/\r\n\r\n|\n\n/', (string) $text))->map(fn ($p) => trim($p))->filter()->values())

@foreach ($paragraphs as $paragraph)
    <p class="mb-4 leading-relaxed last:mb-0">{!! nl2br(e($paragraph)) !!}</p>
@endforeach
