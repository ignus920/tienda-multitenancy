@if(isset($buttons) && $buttons->count() > 0)
    <div class="flex flex-wrap gap-2">
        @foreach($buttons as $button)
            <a href="{{ $button->link }}" 
               target="_blank" 
               rel="noopener noreferrer"
               title="{{ $button->tittle }}"
               class="{{ $this->getDynamicButtonClasses($button->color) }} inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold uppercase tracking-widest transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2">
                {{ $button->tittle }}
            </a>
        @endforeach
    </div>
@endif
