@if($errors->any())
<div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 flex items-start gap-3 text-sm">
    <i class="fas fa-exclamation-circle flex-shrink-0 mt-0.5"></i>
    <div>
        @if($errors->count() === 1)
            {{ $errors->first() }}
        @else
            <p class="font-semibold mb-1.5">Por favor corrige los siguientes errores:</p>
            <ul class="space-y-0.5 list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endif
