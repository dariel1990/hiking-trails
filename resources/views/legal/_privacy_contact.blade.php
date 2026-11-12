<div class="rounded-xl bg-emerald-50 border border-emerald-100 p-6 space-y-2 text-gray-700">
    <p class="font-semibold text-emerald-800">{{ setting('site_name') }}</p>
    <p class="text-sm">{{ setting('contact_address') }}</p>
    @if (setting('main_site_url'))
        <a href="{{ setting('main_site_url') }}" target="_blank"
            class="text-sm text-emerald-700 hover:underline block">{{ parse_url(setting('main_site_url'), PHP_URL_HOST) }}</a>
    @endif
</div>
