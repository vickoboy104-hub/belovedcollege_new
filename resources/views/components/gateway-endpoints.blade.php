@props(['callback', 'webhook'])

<div class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs">
    <div class="font-bold text-slate-800">Provider dashboard URLs</div>
    <div class="mt-2 grid gap-2">
        <label class="block">
            <span class="font-semibold text-slate-500">Callback / redirect URL</span>
            <input value="{{ $callback }}" readonly class="theme-input mt-1 w-full font-mono text-[11px]" onclick="this.select()">
        </label>
        <label class="block">
            <span class="font-semibold text-slate-500">Webhook URL</span>
            <input value="{{ $webhook }}" readonly class="theme-input mt-1 w-full font-mono text-[11px]" onclick="this.select()">
        </label>
    </div>
</div>
