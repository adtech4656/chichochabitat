@php
    $categories = [
        ['name' => 'Buy House', 'icon' => '🏠', 'meta' => '890 homes'],
        ['name' => 'Rent House', 'icon' => '🔑', 'meta' => '420 rentals'],
        ['name' => 'Buy Land', 'icon' => '🌿', 'meta' => '260 plots'],
        ['name' => 'Rent Shop', 'icon' => '🏬', 'meta' => '174 spaces'],
        ['name' => 'Hotels', 'icon' => '🏨', 'meta' => '48 stays'],
        ['name' => 'Urgent Needs', 'icon' => '⚡', 'meta' => '12 new'],
        ['name' => 'Invest', 'icon' => '📈', 'meta' => 'Market insights'],
        ['name' => 'Agents', 'icon' => '🤝', 'meta' => 'Verified pros'],
    ];

    $featured = [
        ['title' => 'Mansard View Residence', 'location' => 'Cocody, Abidjan', 'price' => 'CFA 54,000,000', 'meta' => '3 bed • 2 bath • 180 m²', 'badge' => 'Verified'],
        ['title' => 'Sunset Creek Villa', 'location' => 'Yopougon, Abidjan', 'price' => 'CFA 38,500,000', 'meta' => '4 bed • 3 bath • 220 m²', 'badge' => 'New'],
        ['title' => 'Urban Heights Studio', 'location' => 'Plateau, Abidjan', 'price' => 'CFA 28,000,000', 'meta' => '2 bed • 1 bath • 85 m²', 'badge' => 'Hot'],
    ];

    $steps = [
        ['number' => '1', 'title' => 'Intent-first search', 'text' => 'Choose whether you want to buy, rent, or list before diving into filters.'],
        ['number' => '2', 'title' => 'Verified trust checks', 'text' => 'Every listing is screened for KYC, GPS, identity, and property verification.'],
        ['number' => '3', 'title' => 'Secure next steps', 'text' => 'Chat, save, negotiate, and accept offers with a clear, protected flow.'],
    ];

    $insights = [
        ['title' => 'Best-value homes in Abidjan', 'text' => 'Demand remains stable across Cocody and Riviera and early investors are gaining traction.', 'tag' => 'Market'],
        ['title' => 'Land registries, ADU, and IDUFCI', 'text' => 'Legal-ready documents reduce risk and improve conversion for land-heavy buyers.', 'tag' => 'Legal'],
        ['title' => 'Rent demand is rising near business hubs', 'text' => 'Commercial zones show a strong preference for verified short-term rental inventory.', 'tag' => 'Rental'],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'HabitatCI') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <header class="sticky top-0 z-50 border-b border-[#A5D6A7] bg-white/90 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#1B5E20] text-lg font-bold text-white">H</div>
                    <div>
                        <div class="text-lg font-semibold text-[#1B5E20]">HabitatCI</div>
                        <div class="text-[11px] font-medium uppercase tracking-[0.2em] text-[#757575]">Trust-first</div>
                    </div>
                </div>

                <nav class="hidden items-center gap-8 text-sm font-medium text-[#1B5E20] md:flex" aria-label="Main navigation">
                    <a href="#search" class="transition hover:text-[#66BB6A]">Buy</a>
                    <a href="#categories" class="transition hover:text-[#66BB6A]">Rent</a>
                    <a href="#featured" class="transition hover:text-[#66BB6A]">Featured</a>
                    <a href="#insights" class="transition hover:text-[#66BB6A]">Insights</a>
                    <a href="#agent" class="transition hover:text-[#66BB6A]">Agents</a>
                </nav>

                <div class="flex items-center gap-3">
                    <button type="button" class="hidden rounded-full border border-[#A5D6A7] bg-white px-4 py-2 text-sm font-semibold text-[#1B5E20] sm:inline-flex">FR / XOF</button>
                    <button type="button" class="inline-flex items-center justify-center rounded-full bg-[#66BB6A] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#5bb25f]">Get started</button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 pb-20 pt-8 sm:px-6 lg:px-8">
            <section class="py-6 lg:py-10">
                <div class="grid items-center gap-10 lg:grid-cols-[1.15fr_0.85fr]">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-[#A5D6A7] bg-[#F5F5F5] px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.18em] text-[#1B5E20]">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-[#66BB6A]"></span>
                            Verified marketplace
                        </div>

                        <h1 class="mt-6 max-w-xl text-4xl font-bold tracking-[-0.04em] text-[#1B5E20] md:text-5xl lg:text-6xl">
                            Trusted spaces to live, invest, and grow.
                        </h1>

                        <p class="mt-5 max-w-xl text-base leading-7 text-[#1B5E20]">
                            Buy, sell, rent, and list homes, land, shops, and hospitality spaces in Côte d’Ivoire with a trust-first flow built around verification, security, and local insight.
                        </p>

                        <div class="mt-8 flex flex-wrap gap-4">
                            <a href="#search" class="inline-flex items-center justify-center rounded-full bg-[#66BB6A] px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#5bb25f]">Find a property</a>
                            <a href="#agent" class="inline-flex items-center justify-center rounded-full border border-[#A5D6A7] bg-white px-6 py-3 text-sm font-semibold text-[#1B5E20] transition hover:bg-[#E8F5E9]">List a property</a>
                        </div>

                        <div class="mt-8 grid gap-4 sm:grid-cols-3">
                            <div class="rounded-2xl border border-[#A5D6A7] bg-white p-4 shadow-sm">
                                <div class="text-2xl font-bold text-[#1B5E20]">12.4k</div>
                                <div class="mt-1 text-sm text-[#757575]">Verified listings</div>
                            </div>
                            <div class="rounded-2xl border border-[#A5D6A7] bg-white p-4 shadow-sm">
                                <div class="text-2xl font-bold text-[#1B5E20]">98.7%</div>
                                <div class="mt-1 text-sm text-[#757575]">Approval rate</div>
                            </div>
                            <div class="rounded-2xl border border-[#A5D6A7] bg-white p-4 shadow-sm">
                                <div class="text-2xl font-bold text-[#1B5E20]">4.9/5</div>
                                <div class="mt-1 text-sm text-[#757575]">Customer trust</div>
                            </div>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="overflow-hidden rounded-[32px] border border-[#A5D6A7] bg-[#F5F5F5] p-4 shadow-[0_24px_60px_rgba(27,94,32,0.12)]">
                            <div class="relative overflow-hidden rounded-[28px] bg-[#1B5E20] p-4" style="background-image: linear-gradient(180deg, rgba(27,94,32,0.2), rgba(27,94,32,0.8)), url('https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=900&q=80'); background-size: cover; background-position: center;">
                                <div class="flex items-center justify-between text-[10px] font-semibold uppercase tracking-[0.22em] text-white/85">
                                    <span>Verified listing</span>
                                    <span class="rounded-full bg-[#66BB6A] px-2 py-1 text-[10px] font-semibold text-[#1B5E20]">Trusted</span>
                                </div>

                                <div class="mt-40 rounded-[24px] border border-white/20 bg-[#1B5E20]/65 p-4 backdrop-blur-sm">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="text-[10px] uppercase tracking-[0.2em] text-[#A5D6A7]">Abidjan</div>
                                            <div class="mt-2 text-2xl font-semibold text-white">Mansard View Residence</div>
                                        </div>
                                        <div class="rounded-full bg-[#66BB6A] px-2.5 py-1 text-xs font-semibold text-[#1B5E20]">4.9</div>
                                    </div>

                                    <div class="mt-4 grid grid-cols-3 gap-2 text-center text-[10px] text-white/80">
                                        <div class="rounded-xl bg-white/5 p-2">
                                            <div class="font-semibold text-white">3 BR</div>
                                        </div>
                                        <div class="rounded-xl bg-white/5 p-2">
                                            <div class="font-semibold text-white">2 BA</div>
                                        </div>
                                        <div class="rounded-xl bg-white/5 p-2">
                                            <div class="font-semibold text-white">180 m²</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4 flex items-center justify-between">
                                    <div>
                                        <div class="text-[10px] uppercase tracking-[0.2em] text-white/80">Starting</div>
                                        <div class="mt-1 text-3xl font-bold text-white">CFA 54M</div>
                                    </div>
                                    <button type="button" class="rounded-full bg-white px-4 py-2 text-xs font-semibold text-[#1B5E20]">View details</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="search" class="mt-10 rounded-[28px] border border-[#A5D6A7] bg-white p-5 shadow-sm lg:p-6">
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" class="rounded-full bg-[#66BB6A] px-4 py-2 text-sm font-semibold text-white">Find a property</button>
                    <button type="button" class="rounded-full border border-[#A5D6A7] bg-[#F5F5F5] px-4 py-2 text-sm font-semibold text-[#1B5E20]">List a property</button>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-5">
                    <label class="rounded-2xl border border-[#A5D6A7] bg-[#F5F5F5] p-3 text-sm text-[#1B5E20]">
                        <span class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.15em] text-[#757575]">Location</span>
                        <span class="block text-base font-medium">Abidjan</span>
                    </label>
                    <label class="rounded-2xl border border-[#A5D6A7] bg-[#F5F5F5] p-3 text-sm text-[#1B5E20]">
                        <span class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.15em] text-[#757575]">Type</span>
                        <span class="block text-base font-medium">House</span>
                    </label>
                    <label class="rounded-2xl border border-[#A5D6A7] bg-[#F5F5F5] p-3 text-sm text-[#1B5E20]">
                        <span class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.15em] text-[#757575]">Budget</span>
                        <span class="block text-base font-medium">CFA 20M – 80M</span>
                    </label>
                    <label class="rounded-2xl border border-[#A5D6A7] bg-[#F5F5F5] p-3 text-sm text-[#1B5E20]">
                        <span class="mb-2 block text-[11px] font-semibold uppercase tracking-[0.15em] text-[#757575]">Bedrooms</span>
                        <span class="block text-base font-medium">3+</span>
                    </label>
                    <button type="button" class="inline-flex items-center justify-center rounded-2xl bg-[#1B5E20] px-5 py-4 text-sm font-semibold text-white shadow-sm hover:bg-[#173f1b]">
                        Search now
                    </button>
                </div>
            </section>

            <section id="categories" class="mt-16">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.18em] text-[#757575]">Popular categories</div>
                        <h2 class="mt-2 text-3xl font-semibold text-[#1B5E20]">Browse with purpose</h2>
                    </div>
                    <a href="#" class="text-sm font-semibold text-[#66BB6A]">View all</a>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($categories as $category)
                        <div class="rounded-[24px] border border-[#A5D6A7] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#E8F5E9] text-2xl">{{ $category['icon'] }}</div>
                            <div class="mt-4 text-xl font-semibold text-[#1B5E20]">{{ $category['name'] }}</div>
                            <div class="mt-1 text-sm text-[#757575]">{{ $category['meta'] }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="featured" class="mt-16">
                <div class="mb-6 flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.18em] text-[#757575]">Featured listings</div>
                        <h2 class="mt-2 text-3xl font-semibold text-[#1B5E20]">Trusted homes worth a closer look</h2>
                    </div>
                    <a href="#" class="text-sm font-semibold text-[#66BB6A]">See all</a>
                </div>

                <div class="grid gap-6 lg:grid-cols-3">
                    @foreach ($featured as $property)
                        <article class="overflow-hidden rounded-[28px] border border-[#A5D6A7] bg-white shadow-sm">
                            <div class="h-56 bg-cover bg-center" style="background-image: linear-gradient(180deg, rgba(27,94,32,0.1), rgba(27,94,32,0.55)), url('https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=900&q=80');"></div>
                            <div class="p-5">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="rounded-full bg-[#E8F5E9] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#1B5E20]">{{ $property['badge'] }}</span>
                                    <span class="text-xs font-semibold text-[#757575]">✔ Verified</span>
                                </div>

                                <h3 class="mt-4 text-2xl font-semibold text-[#1B5E20]">{{ $property['title'] }}</h3>
                                <p class="mt-2 text-sm text-[#757575]">{{ $property['location'] }}</p>
                                <p class="mt-3 text-sm text-[#1B5E20]">{{ $property['meta'] }}</p>

                                <div class="mt-5 flex items-center justify-between gap-3 border-t border-[#A5D6A7] pt-4">
                                    <div>
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.16em] text-[#757575]">Price</div>
                                        <div class="mt-1 text-xl font-bold text-[#1B5E20]">{{ $property['price'] }}</div>
                                    </div>
                                    <button type="button" class="rounded-full bg-[#66BB6A] px-4 py-2 text-sm font-semibold text-white hover:bg-[#5bb25f]">View</button>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="mt-16 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                <div class="rounded-[28px] border border-[#A5D6A7] bg-white p-6 shadow-sm">
                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-[#757575]">How it works</div>
                    <h2 class="mt-2 text-3xl font-semibold text-[#1B5E20]">Simple, guided, and secure</h2>

                    <div class="mt-6 space-y-4">
                        @foreach ($steps as $step)
                            <div class="flex gap-4 rounded-2xl border border-[#A5D6A7] bg-[#F5F5F5] p-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[#1B5E20] text-lg font-bold text-white">{{ $step['number'] }}</div>
                                <div>
                                    <div class="text-lg font-semibold text-[#1B5E20]">{{ $step['title'] }}</div>
                                    <p class="mt-1 text-sm leading-6 text-[#757575]">{{ $step['text'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="insights" class="rounded-[28px] border border-[#A5D6A7] bg-[#F5F5F5] p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold uppercase tracking-[0.18em] text-[#757575]">Market insight</div>
                            <h2 class="mt-2 text-3xl font-semibold text-[#1B5E20]">What buyers need to know</h2>
                        </div>
                        <div class="rounded-full bg-[#E8F5E9] px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.14em] text-[#1B5E20]">Updated daily</div>
                    </div>

                    <div class="mt-6 space-y-4">
                        @foreach ($insights as $insight)
                            <article class="rounded-2xl border border-[#A5D6A7] bg-white p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="rounded-full bg-[#E8F5E9] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#1B5E20]">{{ $insight['tag'] }}</span>
                                    <span class="text-xs text-[#757575]">2 min read</span>
                                </div>
                                <h3 class="mt-3 text-xl font-semibold text-[#1B5E20]">{{ $insight['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[#757575]">{{ $insight['text'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="agent" class="mt-16">
                <div class="rounded-[32px] bg-gradient-to-r from-[#1B5E20] via-[#66BB6A] to-[#1B5E20] p-7 text-white lg:p-10">
                    <div class="grid items-center gap-8 lg:grid-cols-[1.15fr_0.85fr]">
                        <div>
                            <div class="text-sm font-semibold uppercase tracking-[0.18em] text-white/80">Become an agent</div>
                            <h2 class="mt-3 text-3xl font-semibold text-white md:text-4xl">List with confidence and grow your pipeline.</h2>
                            <p class="mt-4 max-w-xl text-base leading-7 text-white/90">
                                Save time with guided property upload, geolocation checks, KYC approval, and a verification workflow that keeps the marketplace safe.
                            </p>
                            <div class="mt-6 flex flex-wrap gap-4">
                                <button type="button" class="rounded-full bg-white px-5 py-3 text-sm font-semibold text-[#1B5E20]">Apply to list</button>
                                <button type="button" class="rounded-full border border-white/40 bg-white/10 px-5 py-3 text-sm font-semibold text-white">Book a demo</button>
                            </div>
                        </div>

                        <div class="rounded-[28px] border border-white/20 bg-white/10 p-5 backdrop-blur-sm">
                            <div class="rounded-[22px] bg-white p-4 text-[#1B5E20]">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-[#757575]">Agent onboarding</div>
                                        <div class="mt-1 text-2xl font-semibold">KYC ready</div>
                                    </div>
                                    <span class="rounded-full bg-[#E8F5E9] px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.14em] text-[#1B5E20]">1 of 4</span>
                                </div>

                                <div class="mt-5 space-y-3">
                                    <div class="flex items-center justify-between rounded-xl bg-[#F5F5F5] px-3 py-2.5">
                                        <span class="text-sm font-medium">Government ID</span>
                                        <span class="text-[#66BB6A]">✔</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl bg-[#F5F5F5] px-3 py-2.5">
                                        <span class="text-sm font-medium">Proof of address</span>
                                        <span class="text-[#66BB6A]">✔</span>
                                    </div>
                                    <div class="flex items-center justify-between rounded-xl bg-[#F5F5F5] px-3 py-2.5">
                                        <span class="text-sm font-medium">Liveness check</span>
                                        <span class="text-[#66BB6A]">✔</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-[#A5D6A7] bg-[#1B5E20] text-white">
            <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 lg:grid-cols-4 lg:px-8">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-lg font-bold text-[#1B5E20]">H</div>
                        <div>
                            <div class="text-lg font-semibold">HabitatCI</div>
                            <div class="text-[11px] uppercase tracking-[0.18em] text-white/70">Verified living</div>
                        </div>
                    </div>
                    <p class="mt-4 max-w-xs text-sm leading-6 text-white/80">
                        A trust-first property platform for secure buying, selling, and rental decisions across Côte d’Ivoire.
                    </p>
                </div>

                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-white/70">Browse</div>
                    <ul class="mt-4 space-y-2 text-sm text-white/80">
                        <li>Buy homes</li>
                        <li>Rent homes</li>
                        <li>Land & plots</li>
                        <li>Hotels</li>
                    </ul>
                </div>

                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-white/70">Company</div>
                    <ul class="mt-4 space-y-2 text-sm text-white/80">
                        <li>About</li>
                        <li>Trust & safety</li>
                        <li>Agents</li>
                        <li>Contact</li>
                    </ul>
                </div>

                <div>
                    <div class="text-sm font-semibold uppercase tracking-[0.18em] text-white/70">Regions</div>
                    <ul class="mt-4 space-y-2 text-sm text-white/80">
                        <li>Abidjan</li>
                        <li>Yamoussoukro</li>
                        <li>San Pedro</li>
                        <li>All regions</li>
                    </ul>
                </div>
            </div>
        </footer>
    </body>
</html>