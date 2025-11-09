@foreach($cookies->getCategories() as $category)
    <div class="divide-y divide-white/5">
        <div class="flex flex-col gap-2 px-6 py-4 text-sm">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-indigo-200">{{ $category->title }}</p>
            @if($category->description)
                <p class="text-sm text-indigo-100">{{ $category->description }}</p>
            @endif
        </div>

        <div class="divide-y divide-white/5">
            @foreach($category->getCookies() as $cookie)
                <div class="grid gap-4 px-6 py-4 text-sm text-indigo-100 md:grid-cols-[minmax(0,0.9fr)_minmax(0,1.6fr)_minmax(0,0.5fr)]">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-300">@lang('cookieConsent::cookies.cookie')</p>
                        <p class="mt-2 font-medium text-white">{{ $cookie->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-300">@lang('cookieConsent::cookies.purpose')</p>
                        @php
                            $defaultKey = 'cookieConsent::cookies.defaults.' . $cookie->name;
                            $defaultDescription = trans($defaultKey);
                            $fallback = trans('cookieConsent::cookies.defaults.consent');
                        @endphp
                        <p class="mt-2 leading-relaxed">
                            {{ $cookie->description ?? ($defaultDescription !== $defaultKey ? $defaultDescription : $fallback) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-indigo-300">@lang('cookieConsent::cookies.duration')</p>
                        <p class="mt-2 font-medium text-white">
                            {{ \Carbon\CarbonInterval::minutes($cookie->duration)->cascade() }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
