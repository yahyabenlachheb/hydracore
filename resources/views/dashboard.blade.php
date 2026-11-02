@extends('layouts.app')

@section('title', 'Dashboard')
@section('header', 'Dashboard')
@section('subheader', $brand->name . ' · ' . App\Http\Controllers\DashboardController::PERIODS[$period] . ' (' . $from->format('M j, H:i') . ' – ' . $to->format('M j, H:i') . ' UTC)')

@section('breadcrumbs')
    <span class="font-medium text-neutral-900 dark:text-white">Dashboard</span>
@endsection

@can('orders.create')
    @section('actions')
        <x-primary-link :href="route('orders.create')">New order</x-primary-link>
    @endsection
@endcan

@section('content')
    @php
        $card = 'rounded-md bg-white shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10';
        $cardHead = 'flex items-center justify-between gap-3 border-b border-neutral-200 px-5 py-3 dark:border-white/10';
        $cardTitle = 'text-sm font-semibold text-neutral-900 dark:text-white';
        $link = 'text-xs font-medium text-accent-600 hover:text-accent-500 dark:text-accent-400';
        $previousLabel = ['today' => 'the same time before', '7d' => 'previous 7 days', '30d' => 'previous 30 days', '90d' => 'previous 90 days'][$period];
        $money = fn (float $amount) => number_format($amount, $amount == floor($amount) ? 0 : 2);

        // label, value, note, current, previous (null: a snapshot with no comparison)
        $tiles = [];
        if (isset($revenue)) {
            $tiles[] = ['Revenue', $money($revenue) . ' ' . $currency, $otherCurrencies->isNotEmpty() ? '+ ' . $otherCurrencies->map(fn ($amount, $code) => $money((float) $amount) . ' ' . $code)->join(', ') : 'From paid orders', $revenue, $revenuePrevious];
            $tiles[] = ['Paid orders', number_format($paidOrders), $paidOrders ? number_format($revenue / $paidOrders, 2) . ' ' . $currency . ' average' : 'None in this period', $paidOrders, $paidOrdersPrevious];
        }
        if (isset($activeLines)) {
            $tiles[] = ['Active lines', number_format($activeLines), 'Right now', null, null];
            $tiles[] = ['Expiring soon', number_format($expiringCount), 'In the next 7 days', null, null];
        }
        if (isset($newCustomers)) {
            $tiles[] = ['New customers', number_format($newCustomers), 'Signed up on this brand', $newCustomers, $newCustomersPrevious];
        }
        if (isset($trialsStarted)) {
            $tiles[] = ['Trial conversion', $trialsStarted ? round($trialsConverted / $trialsStarted * 100, 1) . '%' : '—', number_format($trialsConverted) . ' of ' . number_format($trialsStarted) . ' ' . Str::plural('trial', $trialsStarted), null, null];
        }
        $hasRevenue = isset($chart) && collect($chart)->sum('revenue') > 0;
    @endphp

    {{-- One filter row; everything below follows it. --}}
    <div class="mb-6 flex flex-wrap items-center gap-2">
        <div class="inline-flex rounded-md bg-white p-0.5 shadow-sm ring-1 ring-neutral-200 dark:bg-neutral-900 dark:ring-white/10" role="group" aria-label="Period">
            @foreach (App\Http\Controllers\DashboardController::PERIODS as $value => $text)
                <a href="{{ route('dashboard', ['period' => $value]) }}" @class([
                    'rounded px-3 py-1.5 text-sm font-medium',
                    'bg-accent-600 text-white shadow-sm' => $period === $value,
                    'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-300 dark:hover:bg-white/10 dark:hover:text-white' => $period !== $value,
                ]) @if ($period === $value) aria-current="true" @endif>{{ $text }}</a>
            @endforeach
        </div>
    </div>

    {{-- Things waiting for someone --}}
    @php
        $waiting = array_filter([
            isset($pendingOrders) && $pendingOrders ? [$pendingOrders, Str::plural('order', $pendingOrders) . ' awaiting payment', route('orders.index', ['status' => 'pending'])] : null,
            isset($pendingTrials) && $pendingTrials ? [$pendingTrials, Str::plural('trial', $pendingTrials) . ' to review', route('trials.index', ['status' => 'pending'])] : null,
            isset($newContacts) && $newContacts ? [$newContacts, 'new ' . Str::plural('message', $newContacts), route('contacts.index', ['status' => 'new'])] : null,
        ]);
    @endphp
    @if ($waiting)
        <div class="mb-6 flex flex-wrap gap-2">
            @foreach ($waiting as [$count, $text, $url])
                <a href="{{ $url }}" class="inline-flex items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm text-amber-800 hover:bg-amber-100 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500/20">
                    <span class="font-semibold tabular-nums">{{ number_format($count) }}</span> {{ $text }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- KPI row --}}
    @if ($tiles)
        <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 2xl:grid-cols-6">
            @foreach ($tiles as [$label, $value, $note, $now, $before])
                @php
                    $change = $before > 0 ? ($now - $before) / $before * 100 : null;
                    $good = $change === null || abs($change) < 0.05 ? null : $change > 0;
                @endphp
                <div class="{{ $card }} px-5 py-4">
                    <p class="text-xs font-medium text-neutral-500 dark:text-neutral-400">{{ $label }}</p>
                    <p class="mt-1 text-2xl font-semibold text-neutral-900 dark:text-white">{{ $value }}</p>
                    <p class="mt-0.5 truncate text-xs text-neutral-500 dark:text-neutral-400" title="{{ $note }}">{{ $note }}</p>
                    @if ($now !== null)
                        <p @class([
                            'mt-2 flex items-center gap-1 text-xs font-medium',
                            'text-[#006300] dark:text-[#0ca30c]' => $good === true,
                            'text-red-700 dark:text-red-400' => $good === false,
                            'text-neutral-500 dark:text-neutral-400' => $good === null,
                        ])>
                            @if ($change === null)
                                {{ $now > 0 ? 'New' : 'No change' }} <span class="font-normal text-neutral-400">vs {{ $previousLabel }}</span>
                            @else
                                <span aria-hidden="true">{{ $change > 0.05 ? '▲' : ($change < -0.05 ? '▼' : '•') }}</span>
                                {{ $change > 0 ? '+' : '' }}{{ round($change, 1) }}%
                                <span class="font-normal text-neutral-400">vs {{ $previousLabel }}</span>
                            @endif
                        </p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        {{-- Revenue over time --}}
        @isset($chart)
            <section class="{{ $card }} viz-root xl:col-span-2">
                <div class="{{ $cardHead }}">
                    <h2 class="{{ $cardTitle }}">Revenue in {{ $currency }} <span class="font-normal text-neutral-500 dark:text-neutral-400">· {{ $period === 'today' ? 'per hour' : 'per day' }}</span></h2>
                    @if ($hasRevenue)
                        <button type="button" class="{{ $link }}" data-chart-toggle aria-pressed="false">Table</button>
                    @endif
                </div>
                @if ($hasRevenue)
                    <div class="relative px-3 pt-4 pb-2" data-chart='@json($chart)' data-currency="{{ $currency }}" tabindex="0" aria-label="Revenue chart. Use the arrow keys to read values; press Table for all values."></div>
                    <div class="hidden max-h-80 overflow-auto" data-chart-table>
                        <table class="min-w-full text-sm">
                            <thead class="sticky top-0 bg-neutral-50 text-left text-xs font-semibold text-neutral-500 dark:bg-neutral-800 dark:text-neutral-400">
                                <tr><th class="px-5 py-2">{{ $period === 'today' ? 'Hour' : 'Day' }}</th><th class="px-5 py-2 text-right">Revenue ({{ $currency }})</th></tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 tabular-nums dark:divide-white/10">
                                @foreach ($chart as $point)
                                    <tr><td class="px-5 py-1.5 text-neutral-600 dark:text-neutral-300">{{ $point['label'] }}</td><td class="px-5 py-1.5 text-right text-neutral-900 dark:text-white">{{ number_format($point['revenue'], 2) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex h-64 flex-col items-center justify-center px-6 text-center">
                        <p class="text-sm font-medium text-neutral-900 dark:text-white">No paid orders in this period</p>
                        <p class="mt-0.5 text-xs text-neutral-500 dark:text-neutral-400">Revenue shows up here once an order is paid.</p>
                    </div>
                @endif
            </section>

            {{-- Top plans --}}
            @php($maxAmount = max(0.01, (float) ($topPlans->max('amount') ?? 0)))
            <section class="{{ $card }} viz-root">
                <div class="{{ $cardHead }}"><h2 class="{{ $cardTitle }}">Best-selling plans</h2></div>
                @if ($topPlans->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">No sales in this period.</p>
                @else
                    <ol class="space-y-4 px-5 py-4">
                        @foreach ($topPlans as $plan)
                            <li>
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="min-w-0 truncate text-sm text-neutral-900 dark:text-white" title="{{ $plan->plan_name }}">{{ $plan->plan_name }}</span>
                                    <span class="shrink-0 text-sm font-semibold text-neutral-900 tabular-nums dark:text-white">{{ number_format((float) $plan->amount, 2) }} {{ $currency }}</span>
                                </div>
                                {{-- One series, one hue: bar length = revenue relative to the best plan --}}
                                <div class="mt-1.5 h-1.5 rounded-full bg-neutral-100 dark:bg-white/10">
                                    <div class="h-full rounded-full" style="width: {{ max(2, (float) $plan->amount / $maxAmount * 100) }}%; background: var(--series-1)"></div>
                                </div>
                                <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">{{ number_format($plan->sold) }} sold</p>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        @endisset

        {{-- Recent orders --}}
        @isset($recentOrders)
            <section class="{{ $card }} xl:col-span-2">
                <div class="{{ $cardHead }}">
                    <h2 class="{{ $cardTitle }}">Recent orders</h2>
                    <a href="{{ route('orders.index') }}" class="{{ $link }}">All orders →</a>
                </div>
                @if ($recentOrders->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">No orders yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <tbody class="divide-y divide-neutral-100 dark:divide-white/10">
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td class="px-5 py-2.5">
                                            <a href="{{ route('orders.show', $order) }}" class="font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400">{{ $order->customer->full_name }}</a>
                                            <p class="font-mono text-xs text-neutral-500 dark:text-neutral-400">{{ $order->reference_number }}</p>
                                        </td>
                                        <td class="px-5 py-2.5 whitespace-nowrap text-neutral-600 dark:text-neutral-300">{{ $order->plan_name }}</td>
                                        <td class="px-5 py-2.5"><x-status :status="$order->status" /></td>
                                        <td class="px-5 py-2.5 text-right text-neutral-900 dark:text-white"><x-money :amount="$order->total" :currency="$order->currency" /></td>
                                        <td class="px-5 py-2.5 text-right whitespace-nowrap text-xs text-neutral-500 dark:text-neutral-400">{{ $order->created_at?->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endisset

        {{-- Expiring soon: the people to chase for a renewal --}}
        @isset($expiring)
            <section class="{{ $card }}">
                <div class="{{ $cardHead }}">
                    <h2 class="{{ $cardTitle }}">Expiring in 7 days</h2>
                    <a href="{{ route('subscriptions.index', ['expiring' => 7]) }}" class="{{ $link }}">All →</a>
                </div>
                @if ($expiring->isEmpty())
                    <p class="px-5 py-10 text-center text-sm text-neutral-500 dark:text-neutral-400">Nothing expires this week.</p>
                @else
                    <ul class="divide-y divide-neutral-100 text-sm dark:divide-white/10">
                        @foreach ($expiring as $subscription)
                            <li class="flex items-center justify-between gap-3 px-5 py-2.5">
                                <div class="min-w-0">
                                    <a href="{{ route('subscriptions.show', $subscription) }}" class="block truncate font-medium text-neutral-900 hover:text-accent-600 dark:text-white dark:hover:text-accent-400">{{ $subscription->customer->full_name }}</a>
                                    <p class="truncate text-xs text-neutral-500 dark:text-neutral-400">{{ $subscription->plan->name }}</p>
                                </div>
                                <span class="shrink-0 text-xs text-neutral-600 dark:text-neutral-300" title="{{ $subscription->expires_at->format('M j, Y H:i') }} UTC">{{ $subscription->expires_at->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endisset
    </div>
@endsection

@push('styles')
    <style>
        /* Chart roles (validated: dataviz scripts/validate_palette.js, light on #ffffff, dark on #171717). */
        .viz-root {
            --series-1: #c2410c;
            --surface-1: #ffffff;
            --grid: #e5e5e5;
            --axis: #d4d4d4;
            --ink-muted: #737373;
        }
        .dark .viz-root {
            --series-1: #ea580c;
            --surface-1: #171717;
            --grid: rgba(255, 255, 255, 0.07);
            --axis: rgba(255, 255, 255, 0.16);
            --ink-muted: #a3a3a3;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Revenue chart: one smooth 2px line over a soft area, an end dot with a surface ring,
        // crosshair + tooltip on hover/arrow keys, and a table view. Redrawn on resize.
        (() => {
            const host = document.querySelector('[data-chart]');
            if (!host) return;
            const points = JSON.parse(host.dataset.chart);
            const currency = host.dataset.currency;
            const color = 'var(--series-1)';
            const ns = 'http://www.w3.org/2000/svg';
            const el = (name, attrs = {}) => { const n = document.createElementNS(ns, name); for (const k in attrs) n.setAttribute(k, attrs[k]); return n; };
            const compact = (n) => n >= 1e6 ? +(n / 1e6).toFixed(1) + 'M' : n >= 1e3 ? +(n / 1e3).toFixed(1) + 'K' : String(+n.toFixed(2));
            const niceMax = (v) => { if (v <= 4) return 4; const p = 10 ** Math.floor(Math.log10(v)); return [1, 2, 2.5, 5, 10].map((m) => m * p).find((m) => m >= v); };

            // Monotone cubic curve (Fritsch–Carlson): smooth, but never above or below its
            // neighbouring points, so it can't dip below zero or bulge past a real value.
            const smooth = (pts) => {
                const n = pts.length, f = (v) => v.toFixed(1);
                if (n < 3) return pts.map(([px, py], i) => `${i ? 'L' : 'M'}${f(px)},${f(py)}`).join('');
                const slope = [], t = [];
                for (let i = 0; i < n - 1; i++) slope.push((pts[i + 1][1] - pts[i][1]) / (pts[i + 1][0] - pts[i][0]));
                t[0] = slope[0];
                t[n - 1] = slope[n - 2];
                for (let i = 1; i < n - 1; i++) t[i] = slope[i - 1] * slope[i] <= 0 ? 0 : (slope[i - 1] + slope[i]) / 2;
                for (let i = 0; i < n - 1; i++) {
                    if (slope[i] === 0) { t[i] = t[i + 1] = 0; continue; }
                    const a = t[i] / slope[i], b = t[i + 1] / slope[i], s = a * a + b * b;
                    if (s > 9) { const k = 3 / Math.sqrt(s); t[i] = k * a * slope[i]; t[i + 1] = k * b * slope[i]; }
                }
                let d = `M${f(pts[0][0])},${f(pts[0][1])}`;
                for (let i = 0; i < n - 1; i++) {
                    const [x0, y0] = pts[i], [x1, y1] = pts[i + 1], h = (x1 - x0) / 3;
                    d += `C${f(x0 + h)},${f(y0 + t[i] * h)} ${f(x1 - h)},${f(y1 - t[i + 1] * h)} ${f(x1)},${f(y1)}`;
                }
                return d;
            };

            const tooltip = document.createElement('div');
            tooltip.className = 'pointer-events-none absolute z-10 hidden min-w-28 rounded-md bg-white px-3 py-2 text-xs shadow-lg ring-1 ring-neutral-200 dark:bg-neutral-800 dark:ring-white/10';
            let active = null;

            function draw() {
                if (!host.clientWidth) return; // Hidden while the table view is on.
                host.querySelector('svg')?.remove();
                const W = host.clientWidth - 24, H = 240, padL = 44, padR = 16, padT = 8, axisBand = 26;
                const plotW = Math.max(40, W - padL - padR), plotH = H - padT - axisBand;
                const max = niceMax(Math.max(...points.map((p) => p.revenue)));
                const x = (i) => padL + (points.length === 1 ? plotW / 2 : (i / (points.length - 1)) * plotW);
                const y = (v) => padT + plotH - (v / max) * plotH;
                const svg = el('svg', { width: W, height: H, role: 'img', 'aria-hidden': 'true' });

                // Recessive hairline grid + tick labels
                for (let i = 0; i <= 4; i++) {
                    const v = (max / 4) * i, yy = Math.round(y(v)) + 0.5;
                    svg.append(el('line', { x1: padL, x2: padL + plotW, y1: yy, y2: yy, stroke: i === 0 ? 'var(--axis)' : 'var(--grid)', 'stroke-width': 1 }));
                    const t = el('text', { x: padL - 8, y: yy + 4, 'text-anchor': 'end', 'font-size': 11, fill: 'var(--ink-muted)', style: 'font-variant-numeric: tabular-nums' });
                    t.textContent = compact(v);
                    svg.append(t);
                }
                // About six x labels, evenly spaced
                const every = Math.max(1, Math.ceil(points.length / Math.max(2, Math.floor(plotW / 90))));
                points.forEach((p, i) => {
                    if (i % every && i !== points.length - 1) return;
                    if (i !== points.length - 1 && points.length - 1 - i < every / 2) return;
                    const t = el('text', { x: x(i), y: H - 8, 'text-anchor': 'middle', 'font-size': 11, fill: 'var(--ink-muted)' });
                    t.textContent = p.label;
                    svg.append(t);
                });

                const defs = el('defs');
                const gradient = el('linearGradient', { id: 'area-revenue', x1: 0, y1: 0, x2: 0, y2: 1 });
                gradient.append(el('stop', { offset: '0%', style: `stop-color: ${color}; stop-opacity: .28` }));
                gradient.append(el('stop', { offset: '100%', style: `stop-color: ${color}; stop-opacity: 0` }));
                defs.append(gradient);
                svg.append(defs);

                const curve = smooth(points.map((p, i) => [x(i), y(p.revenue)]));
                const base = (padT + plotH).toFixed(1), last = points.length - 1;
                svg.append(el('path', { d: `${curve}L${x(last).toFixed(1)},${base}L${x(0).toFixed(1)},${base}Z`, fill: 'url(#area-revenue)' }));
                svg.append(el('path', { d: curve, fill: 'none', stroke: color, 'stroke-width': 2, 'stroke-linejoin': 'round', 'stroke-linecap': 'round' }));
                svg.append(el('circle', { cx: x(last), cy: y(points[last].revenue), r: 4, fill: color, stroke: 'var(--surface-1)', 'stroke-width': 2 }));

                // Hover layer: crosshair snaps to the nearest point
                const cross = el('line', { y1: padT, y2: padT + plotH, stroke: 'var(--axis)', 'stroke-width': 1, visibility: 'hidden' });
                const mark = el('circle', { r: 4, fill: color, stroke: 'var(--surface-1)', 'stroke-width': 2, visibility: 'hidden' });
                const hit = el('rect', { x: padL - 10, y: 0, width: plotW + 20, height: H - axisBand, fill: 'transparent' });
                svg.append(cross, mark, hit);

                const show = (i) => {
                    active = i;
                    const p = points[i], cx = x(i);
                    cross.setAttribute('x1', cx); cross.setAttribute('x2', cx); cross.setAttribute('visibility', 'visible');
                    mark.setAttribute('cx', cx); mark.setAttribute('cy', y(p.revenue)); mark.setAttribute('visibility', 'visible');
                    tooltip.replaceChildren();
                    const head = document.createElement('div');
                    head.className = 'mb-1 font-medium text-neutral-500 dark:text-neutral-400';
                    head.textContent = p.label;
                    const value = document.createElement('div');
                    value.className = 'font-semibold text-neutral-900 tabular-nums dark:text-white';
                    value.textContent = `${p.revenue.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} ${currency}`;
                    tooltip.append(head, value);
                    tooltip.classList.remove('hidden');
                    const left = cx + 12 + tooltip.offsetWidth > W ? cx - tooltip.offsetWidth - 12 : cx + 12;
                    tooltip.style.left = `${left + 12}px`;
                    tooltip.style.top = `${padT + 16}px`;
                };
                const hide = () => { active = null; cross.setAttribute('visibility', 'hidden'); mark.setAttribute('visibility', 'hidden'); tooltip.classList.add('hidden'); };
                const nearest = (clientX) => {
                    const px = clientX - svg.getBoundingClientRect().left;
                    return Math.max(0, Math.min(last, Math.round(((px - padL) / plotW) * last)));
                };
                hit.addEventListener('pointermove', (e) => show(nearest(e.clientX)));
                hit.addEventListener('pointerleave', hide);
                host.onkeydown = (e) => {
                    if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(e.key)) return;
                    e.preventDefault();
                    show(e.key === 'Home' ? 0 : e.key === 'End' ? last : Math.max(0, Math.min(last, (active ?? last) + (e.key === 'ArrowLeft' ? -1 : 1))));
                };
                host.onblur = hide;
                host.prepend(svg);
                host.append(tooltip);
            }

            draw();
            new ResizeObserver(() => draw()).observe(host);

            const toggle = document.querySelector('[data-chart-toggle]');
            toggle?.addEventListener('click', () => {
                const showTable = toggle.getAttribute('aria-pressed') !== 'true';
                toggle.setAttribute('aria-pressed', String(showTable));
                toggle.textContent = showTable ? 'Chart' : 'Table';
                host.classList.toggle('hidden', showTable);
                document.querySelector('[data-chart-table]').classList.toggle('hidden', !showTable);
            });
        })();
    </script>
@endpush
