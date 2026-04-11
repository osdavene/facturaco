@props(['messages'])

@if ($messages)
    @foreach ((array) $messages as $message)
    <p class="text-red-400 text-xs mt-1.5 flex items-center gap-1">
        <i class="fas fa-exclamation-circle text-[10px]"></i> {{ $message }}
    </p>
    @endforeach
@endif
