@props(['text'])
<span class="fhint">
    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span class="fhint-tip">{{ $text }}</span>
</span>
@once
<style>
    .fhint{position:relative;display:inline-flex;align-items:center;cursor:help;vertical-align:middle;margin-left:4px}
    .fhint-tip{
        position:absolute;left:0;top:calc(100% + 6px);
        width:240px;white-space:normal;background:#111827;color:#fff;font-size:11px;font-weight:500;line-height:1.4;
        padding:8px 10px;border-radius:8px;box-shadow:0 4px 14px rgba(0,0,0,.25);
        opacity:0;pointer-events:none;transition:opacity .12s ease;z-index:60;
    }
    .fhint:hover .fhint-tip{opacity:1}
</style>
@endonce
