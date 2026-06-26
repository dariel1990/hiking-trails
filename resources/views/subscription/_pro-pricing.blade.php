{{-- Trial banner, plan selection, checkout CTA, and trust strip.
     Shared by the full /pro page and the upgrade modal. Expects $priceMonthly,
     $priceAnnual, $symbol, $currency, $trialDays, $stripeEnabled,
     and an ancestor x-data="{ plan: 'annual' }". --}}
@php $symbol ??= '$'; $currency ??= 'CAD'; @endphp
<div class="flex items-center gap-3 rounded-xl ring-1 ring-primary-300/25 bg-primary-500/10 px-5 py-4">
    <svg class="w-5 h-5 text-primary-200 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2zM12 14l2 2"/></svg>
    <p class="text-sm text-white/80">
        <span class="font-semibold text-white">Try Pro free for {{ $trialDays }} days.</span>
        Pick a plan below, cancel anytime.
    </p>
</div>

<div class="mt-5 grid sm:grid-cols-2 gap-4">
    <label :class="plan === 'monthly' ? 'ring-2 ring-primary-300 bg-white/10' : 'ring-1 ring-white/10 bg-white/5'"
           class="cursor-pointer rounded-2xl p-6 transition">
        <input type="radio" name="plan" value="monthly" x-model="plan" class="sr-only">
        <span class="block text-xs font-semibold uppercase tracking-wider text-white/50 mb-2">Monthly</span>
        <span class="text-3xl font-bold">{{ $symbol }}{{ $priceMonthly }}<span class="text-sm font-normal text-white/50"> {{ $currency }}/mo</span></span>
    </label>
    <label :class="plan === 'annual' ? 'ring-2 ring-primary-300 bg-white/10' : 'ring-1 ring-white/10 bg-white/5'"
           class="relative cursor-pointer rounded-2xl p-6 transition">
        <input type="radio" name="plan" value="annual" x-model="plan" class="sr-only">
        <span class="absolute -top-2.5 right-5 bg-accent-500 text-white text-[11px] font-semibold uppercase tracking-wide px-2.5 py-0.5 rounded-full">Best value</span>
        <span class="block text-xs font-semibold uppercase tracking-wider text-white/50 mb-2">Annual</span>
        <span class="text-3xl font-bold">{{ $symbol }}{{ $priceAnnual }}<span class="text-sm font-normal text-white/50"> {{ $currency }}/yr</span></span>
    </label>
</div>

<form method="POST" action="{{ route('pro.checkout') }}" class="mt-7">
    @csrf
    <input type="hidden" name="plan" :value="plan">
    <button type="submit" @if(!$stripeEnabled) disabled @endif
            class="w-full flex items-center justify-center gap-2 bg-accent-500 hover:bg-accent-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold text-lg px-6 py-4 rounded-full transition shadow-lg shadow-accent-500/20">
        @if($stripeEnabled)
            Subscribe now
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        @else
            Payments coming soon
        @endif
    </button>
    @unless($stripeEnabled)
        <p class="mt-3 text-center text-xs text-white/40">Checkout isn't live yet, payment setup is in progress.</p>
    @endunless
</form>

<div class="mt-6 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-xs text-white/45">
    <span class="inline-flex items-center gap-1.5">
        <svg class="w-4 h-4 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        Secure payment
    </span>
    <span class="inline-flex items-center gap-1.5">
        <svg class="w-4 h-4 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Cancel anytime
    </span>
    <span class="inline-flex items-center gap-1.5">
        <svg class="w-4 h-4 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11c0 1.657-1.343 3-3 3m3-3c0-1.657-1.343-3-3-3m3 3h6m-9 3a3 3 0 01-3-3m0 0a3 3 0 013-3m0 6v3m0-9V5m-6 7a9 9 0 1118 0 9 9 0 01-18 0z"/></svg>
        Your data is safe
    </span>
</div>

<p class="mt-4 text-center text-xs text-white/35">
    By starting your subscription you agree to our
    <a href="{{ route('terms') }}" target="_blank" class="underline hover:text-white/60 transition">Terms &amp; Conditions</a>,
    including the billing, auto-renewal, and refund policies.
</p>
