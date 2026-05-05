<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Calendar - Smithers Events</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --color-primary: #10221B;
            --color-secondary: #1DC5CE;
            --color-text: #483E3E;
            --color-accent: #F29727;
            --color-gold: #DDAA6B;
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: var(--color-text);
            overflow-x: hidden;
        }

        .container {
            overflow: visible;
        }
        
        .event-card {
            transition: all 0.3s ease;
            border: 1px solid #e5e7eb;
            position: relative;
            overflow: visible;
            z-index: 1;
        }

        .event-card.dropdown-active {
            z-index: 50;
        }

        .event-card:hover {
            box-shadow: 0 20px 40px rgba(16, 34, 27, 0.15);
            border-color: var(--color-primary);
            z-index: 2; /* Slightly higher than others */
        }
        
        .category-badge {
            background-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0d1b15;
        }
        
        .btn-secondary {
            background-color: var(--color-secondary);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #18a5ad;
        }
        
        .calendar-btn-wrapper {
            position: relative;
            display: inline-block;
            z-index: 10001; /* Higher than any card */
        }

        .calendar-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            min-width: 220px;
            z-index: 10002; /* Highest */
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            pointer-events: none;
            white-space: nowrap;
            /* Break out of any parent clipping */
            isolation: isolate;
        }

        .calendar-dropdown.active {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto;
        }

        .calendar-dropdown a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--color-text);
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .calendar-dropdown a:hover {
            background-color: #f9fafb;
            color: var(--color-primary);
        }
        
        .calendar-dropdown a i {
            margin-right: 0.75rem;
            width: 20px;
        }
        
        .view-toggle a.active {
            background-color: var(--color-primary);
            color: white;
        }
        
        .calendar-day {
            min-height: 100px;
            transition: all 0.2s ease;
        }

        @media (max-width: 640px) {
            .calendar-day {
                min-height: 56px;
                padding: 4px !important;
            }

            .calendar-day .font-bold {
                font-size: 0.75rem;
            }
        }
        
        .calendar-day:hover {
            background-color: #f9fafb;
        }
        
        .event-dot {
            width: 8px;
            height: 8px;
            background: var(--color-primary);
            border-radius: 50%;
        }
        
        .today-badge {
            background-color: var(--color-accent);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 10000;
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: 0;
        }

        @media (min-width: 640px) {
            .modal.active {
                align-items: center;
                padding: 1rem;
            }
        }

        .modal-content {
            background: white;
            border-radius: 1rem 1rem 0 0;
            max-width: 800px;
            width: 100%;
            max-height: 92vh;
            overflow-y: auto;
            position: relative;
            animation: modalSlideUp 0.3s ease;
        }

        @media (min-width: 640px) {
            .modal-content {
                border-radius: 1rem;
                max-height: 90vh;
                animation: modalSlideIn 0.3s ease;
            }
        }

        @keyframes modalSlideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @keyframes modalSlideIn {
            from { opacity: 0; transform: translateY(-20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .modal-close {
            position: sticky;
            top: 0;
            right: 0;
            background: white;
            z-index: 10;
        }

        .event-card,
        .event-card > div,
        .container,
        .grid {
            overflow: visible !important;
        }

        /* ── Slideshow ─────────────────────────────── */
        .slideshow-wrap {
            position: relative;
            overflow: hidden;
            background: var(--color-primary);
            /* Mobile: aspect-ratio so full image shows */
            aspect-ratio: 4/3;
            height: auto;
        }

        @media (min-width: 640px) {
            .slideshow-wrap {
                aspect-ratio: unset;
                height: 340px;
            }
        }
        @media (min-width: 768px) { .slideshow-wrap { height: 440px; } }

        .slide {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.9s ease;
            z-index: 0;
        }

        .slide.active {
            opacity: 1;
            z-index: 1;
        }

        .slide img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
        }

        .slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to top,
                rgba(16, 34, 27, 0.80) 0%,
                rgba(16, 34, 27, 0.25) 45%,
                transparent 75%
            );
            z-index: 2;
        }

        .slide-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 3;
            padding: 1.25rem 1.5rem 2.25rem;
        }

        @media (min-width: 640px) {
            .slide-caption { padding: 2rem 3rem 3rem; }
        }

        .slideshow-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s;
            font-size: 0.85rem;
        }

        @media (min-width: 640px) {
            .slideshow-arrow { width: 50px; height: 50px; font-size: 1rem; }
        }

        .slideshow-arrow:hover {
            background: rgba(255, 255, 255, 0.32);
            transform: translateY(-50%) scale(1.08);
        }

        .slideshow-arrow.prev { left: 0.75rem; }
        .slideshow-arrow.next { right: 0.75rem; }

        .slideshow-dots {
            position: absolute;
            bottom: 0.85rem;
            right: 1.25rem;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .slideshow-dot {
            width: 7px;
            height: 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.45);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .slideshow-dot.active {
            background: white;
            width: 22px;
        }

        .slideshow-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: rgba(221, 170, 107, 0.85);
            z-index: 10;
            width: 0%;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- View Toggle & Filters -->
    <div class="bg-white shadow-sm sticky top-0 z-40 border-b border-gray-200">
        <div class="container mx-auto px-4 py-3">

            <!-- Row 1: Title + View Toggle (toggle hidden on mobile) -->
            <div class="flex items-center justify-between gap-3 mb-2">
                <div>
                    <h1 class="text-xl sm:text-3xl font-bold leading-tight" style="color: var(--color-primary);">Events Calendar</h1>
                    <p class="text-gray-500 text-xs sm:text-sm hidden sm:block">Discover events in Smithers, Telkwa & Bulkley Valley</p>
                </div>

                <!-- View Toggle — hidden on mobile -->
                <div class="view-toggle hidden sm:flex bg-gray-100 rounded-lg p-1 shrink-0">
                    <a href="{{ route('events.index', array_merge(request()->except('view'), ['view' => 'list'])) }}"
                       class="flex items-center gap-1.5 px-3 sm:px-5 py-2 sm:py-2.5 rounded-md transition font-medium text-sm {{ $view === 'list' ? 'active' : 'text-gray-600 hover:text-gray-900' }}">
                        <i class="fas fa-th-large"></i><span>List View</span>
                    </a>
                    <a href="{{ route('events.index', array_merge(request()->except('view'), ['view' => 'calendar'])) }}"
                       class="flex items-center gap-1.5 px-3 sm:px-5 py-2 sm:py-2.5 rounded-md transition font-medium text-sm {{ $view === 'calendar' ? 'active' : 'text-gray-600 hover:text-gray-900' }}">
                        <i class="fas fa-calendar-alt"></i><span>Calendar</span>
                    </a>
                </div>
            </div>

            <!-- Row 2: Category Filter -->
            <div class="flex items-center gap-2">
                <label class="text-gray-600 font-medium text-sm shrink-0 hidden sm:inline">Category:</label>
                <select onchange="window.location.href=this.value" class="min-w-0 flex-1 sm:flex-none px-3 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-opacity-50 focus:border-transparent">
                    <option value="{{ route('events.index', request()->except('category')) }}">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ route('events.index', array_merge(request()->except('category'), ['category' => $category])) }}"
                                {{ $categoryFilter === $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    @if($slides->isNotEmpty())
    <!-- Slideshow -->
    <div class="slideshow-wrap" id="slideshow">

        @foreach($slides as $i => $slide)
        <div class="slide {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}">
            <img src="{{ $slide['url'] }}" alt="{{ $slide['name'] }}" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
            <div class="slide-overlay"></div>
            <div class="slide-caption">
                <p class="text-white/60 text-xs sm:text-sm font-medium uppercase tracking-widest mb-1">Smithers Events</p>
                <h2 class="text-white text-xl sm:text-3xl md:text-4xl font-bold drop-shadow-lg">{{ $slide['name'] }}</h2>
            </div>
        </div>
        @endforeach

        <!-- Arrows -->
        <button class="slideshow-arrow prev" id="slidePrev" aria-label="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="slideshow-arrow next" id="slideNext" aria-label="Next">
            <i class="fas fa-chevron-right"></i>
        </button>

        <!-- Dots -->
        <div class="slideshow-dots" id="slideDots">
            @foreach($slides as $i => $slide)
            <div class="slideshow-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}"></div>
            @endforeach
        </div>

        <!-- Progress bar -->
        <div class="slideshow-progress" id="slideshowProgress"></div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-4">

        @if($view === 'list')
            <!-- List View -->
            <div class="mb-8">
                
                @if($upcomingEvents->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6" style="overflow: visible;">
                        @foreach($upcomingEvents as $event)
                            <div class="event-card bg-white rounded-xl flex flex-col">
                                <div class="p-4 sm:p-6 flex-1 flex flex-col" style="overflow: visible;">
                                    <!-- Category Badge -->
                                    @if($event->category)
                                        <span class="category-badge text-xs font-semibold px-3 py-1 rounded-full inline-block mb-3 w-fit">
                                            {{ $event->category }}
                                        </span>
                                    @endif
                                    
                                    <!-- Event Title -->
                                    <h3 class="text-sm sm:text-base font-bold mb-3 leading-snug line-clamp-3 flex-shrink-0" style="color: var(--color-accent);">
                                        <a href="#" onclick="openEventModal({{ $event->id }})" class="hover:opacity-80 transition">
                                            {{ $event->title }}
                                        </a>
                                    </h3>
                                    
                                    <!-- Event Date & Time -->
                                    <div class="space-y-2 mb-4">
                                        <div class="flex items-center text-gray-600">
                                            <i class="far fa-calendar mr-3 text-base" style="color: var(--color-primary);"></i>
                                            <span class="text-sm font-medium">{{ $event->formatted_date }}</span>
                                        </div>
                                        
                                        @if($event->event_time)
                                            <div class="flex items-center text-gray-600">
                                                <i class="far fa-clock mr-3 text-base" style="color: var(--color-primary);"></i>
                                                <span class="text-sm font-medium">
                                                    {{ $event->formatted_time }}
                                                    @if($event->end_time)
                                                        - {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}
                                                    @endif
                                                </span>
                                            </div>
                                        @endif
                                        
                                        <!-- Location -->
                                        @if($event->location)
                                            <div class="flex items-start text-gray-600">
                                                <i class="fas fa-map-marker-alt mr-3 text-base mt-0.5" style="color: var(--color-primary);"></i>
                                                <span class="text-sm font-medium line-clamp-2">{{ $event->location }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- <!-- Description -->
                                    @if($event->description)
                                        <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $event->description }}</p>
                                    @endif
                                     --}}
                                    <!-- Actions -->
                                    <div class="flex gap-2 mt-auto">
                                        <button onclick="openEventModal({{ $event->id }})" 
                                                class="flex-1 btn-primary text-center py-2.5 px-4 rounded-lg transition font-medium text-sm">
                                            View Details
                                        </button>
                                        <div class="calendar-btn-wrapper relative">
                                            <button onclick="toggleCalendarDropdown(event, {{ $event->id }})" 
                                                    class="btn-secondary py-2.5 px-4 rounded-lg transition font-medium text-sm whitespace-nowrap calendar-btn" 
                                                    id="calendar-btn-{{ $event->id }}"
                                                    title="Add to Calendar">
                                                <i class="fas fa-calendar-plus mr-2"></i>Add
                                            </button>
                                            <div id="calendar-dropdown-{{ $event->id }}" class="calendar-dropdown">
                                                <a href="https://calendar.google.com/calendar/render?action=TEMPLATE&text={{ urlencode($event->title) }}&dates={{ $event->event_date->format('Ymd') }}T{{ $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('His') : '000000' }}/{{ $event->end_date ? $event->end_date->format('Ymd') : $event->event_date->format('Ymd') }}T{{ $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('His') : '235959' }}&details={{ urlencode($event->description ?? '') }}&location={{ urlencode($event->location ?? '') }}" target="_blank" onclick="event.stopPropagation();">
                                                    <i class="fab fa-google"></i> Google Calendar
                                                </a>
                                                <a href="{{ route('events.calendar', $event) }}" onclick="event.stopPropagation();">
                                                    <i class="far fa-calendar"></i> iCal Calendar
                                                </a>
                                                <a href="https://outlook.live.com/calendar/0/deeplink/compose?subject={{ urlencode($event->title) }}&body={{ urlencode($event->description ?? '') }}&location={{ urlencode($event->location ?? '') }}&startdt={{ $event->event_date->format('Y-m-d') }}T{{ $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('H:i:s') : '00:00:00' }}&enddt={{ $event->end_date ? $event->end_date->format('Y-m-d') : $event->event_date->format('Y-m-d') }}T{{ $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('H:i:s') : '23:59:59' }}" target="_blank" onclick="event.stopPropagation();">
                                                    <i class="far fa-calendar-alt"></i> Outlook Calendar
                                                </a>
                                                <a href="https://calendar.yahoo.com/?v=60&title={{ urlencode($event->title) }}&st={{ $event->event_date->format('Ymd') }}T{{ $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('His') : '000000' }}&et={{ $event->end_date ? $event->end_date->format('Ymd') : $event->event_date->format('Ymd') }}T{{ $event->end_time ? \Carbon\Carbon::parse($event->end_time)->format('His') : '235959' }}&desc={{ urlencode($event->description ?? '') }}&in_loc={{ urlencode($event->location ?? '') }}" target="_blank" onclick="event.stopPropagation();">
                                                    <i class="fab fa-yahoo"></i> Yahoo Calendar
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-5">
                        {{ $upcomingEvents->links() }}
                    </div>
                @else
                    <div class="text-center py-16 bg-white rounded-xl">
                        <i class="fas fa-calendar-times text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg">No upcoming events found.</p>
                    </div>
                @endif
            </div>

        @else
            <!-- Calendar View -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 sm:p-8 overflow-x-auto">
                
                <!-- Calendar Header -->
                <div class="flex items-center justify-between mb-3">
                    <a href="{{ route('events.index', ['view' => 'calendar', 'month' => $prevMonth->month, 'year' => $prevMonth->year]) }}"
                       class="text-gray-600 hover:text-gray-900 transition p-2">
                        <i class="fas fa-chevron-left text-lg sm:text-2xl"></i>
                    </a>

                    <h2 class="text-lg sm:text-3xl font-bold" style="color: var(--color-primary);">
                        {{ $firstDayOfMonth->format('F Y') }}
                    </h2>

                    <a href="{{ route('events.index', ['view' => 'calendar', 'month' => $nextMonth->month, 'year' => $nextMonth->year]) }}"
                       class="text-gray-600 hover:text-gray-900 transition p-2">
                        <i class="fas fa-chevron-right text-lg sm:text-2xl"></i>
                    </a>
                </div>
                
                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-1 sm:gap-2" style="min-width: 280px;">
                    <!-- Day Headers -->
                    @foreach([['Sunday','Sun'], ['Monday','Mon'], ['Tuesday','Tue'], ['Wednesday','Wed'], ['Thursday','Thu'], ['Friday','Fri'], ['Saturday','Sat']] as [$full, $short])
                        <div class="text-center font-bold py-2 sm:py-3 text-xs sm:text-base" style="color: var(--color-secondary);">
                            <span class="hidden sm:inline">{{ $full }}</span>
                            <span class="sm:hidden">{{ $short }}</span>
                        </div>
                    @endforeach
                    
                    <!-- Empty cells before first day -->
                    @for($i = 0; $i < $startDayOfWeek; $i++)
                        <div class="calendar-day border border-gray-200 rounded-lg p-3 bg-gray-50"></div>
                    @endfor
                    
                    <!-- Days of month -->
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $currentDate = Carbon\Carbon::create($year, $month, $day);
                            $dayEvents = $calendarEvents->filter(function($event) use ($currentDate) {
                                return $event->event_date->isSameDay($currentDate);
                            });
                            $isToday = $currentDate->isToday();
                        @endphp
                        
                        <div class="calendar-day border border-gray-200 rounded-lg p-3 {{ $isToday ? 'ring-2' : 'bg-white' }}" style="{{ $isToday ? 'ring-color: var(--color-primary); background-color: #fff7ed;' : '' }}">
                            <div class="font-bold text-gray-800 mb-2 flex items-center justify-between">
                                <span>{{ $day }}</span>
                                @if($isToday)
                                    <span class="today-badge text-white text-xs px-2 py-0.5 rounded-full">Today</span>
                                @endif
                            </div>
                            
                            @foreach($dayEvents->take(3) as $event)
                                <div class="mb-1">
                                    <a href="#" onclick="openEventModal({{ $event->id }})"
                                       class="block text-xs rounded px-1 sm:px-2 py-1 sm:py-1.5 truncate transition hover:opacity-80"
                                       style="background-color: #fef3e2; color: var(--color-primary);"
                                       title="{{ $event->title }}">
                                        <span class="event-dot inline-block mr-0.5 sm:mr-1"></span>
                                        <span class="font-medium hidden sm:inline">{{ \Str::limit($event->title, 18) }}</span>
                                    </a>
                                </div>
                            @endforeach
                            
                            @if($dayEvents->count() > 3)
                                <div class="text-xs font-semibold mt-1" style="color: var(--color-primary);">
                                    +{{ $dayEvents->count() - 3 }} more
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
                
            </div>
        @endif

    </div>

    <div id="eventModal" class="modal">
        <div class="modal-content">
            <div class="modal-close flex items-center justify-between p-4 sm:p-6 border-b border-gray-200">
                <h2 class="text-lg sm:text-2xl font-bold" style="color: var(--color-primary);">Event Details</h2>
                <button onclick="closeEventModal()" class="text-gray-400 hover:text-gray-600 transition p-1">
                    <i class="fas fa-times text-xl sm:text-2xl"></i>
                </button>
            </div>
            <div id="modalBody" class="p-4 sm:p-8">
                <!-- Event details will be loaded here -->
                <div class="text-center py-8">
                    <i class="fas fa-spinner fa-spin text-4xl" style="color: var(--color-primary);"></i>
                </div>
            </div>
        </div>
    </div>

    <script>
        /* ── Slideshow ───────────────────────────────── */
        (function () {
            const wrap = document.getElementById('slideshow');
            if (!wrap) return;

            const slides   = wrap.querySelectorAll('.slide');
            const dots     = wrap.querySelectorAll('.slideshow-dot');
            const bar      = document.getElementById('slideshowProgress');
            const duration = 5000;
            let current    = 0;
            let timer      = null;
            let touchStartX = 0;

            function go(index) {
                slides[current].classList.remove('active');
                dots[current].classList.remove('active');
                current = (index + slides.length) % slides.length;
                slides[current].classList.add('active');
                dots[current].classList.add('active');
                restartProgress();
            }

            function startProgress() {
                bar.style.transition = 'none';
                bar.style.width = '0%';
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    bar.style.transition = `width ${duration}ms linear`;
                    bar.style.width = '100%';
                }));
            }

            function restartProgress() {
                clearInterval(timer);
                startProgress();
                timer = setInterval(() => go(current + 1), duration);
            }

            function pause() {
                clearInterval(timer);
                bar.style.transition = 'none';
                bar.style.width = '0%';
            }

            function resume() {
                restartProgress();
            }

            // Controls
            document.getElementById('slidePrev').addEventListener('click', () => go(current - 1));
            document.getElementById('slideNext').addEventListener('click', () => go(current + 1));
            dots.forEach(dot => dot.addEventListener('click', () => go(Number(dot.dataset.index))));
            wrap.addEventListener('mouseenter', pause);
            wrap.addEventListener('mouseleave', resume);

            // Touch / swipe
            wrap.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
            wrap.addEventListener('touchend', e => {
                const diff = touchStartX - e.changedTouches[0].screenX;
                if (Math.abs(diff) > 40) diff > 0 ? go(current + 1) : go(current - 1);
            }, { passive: true });

            // Start
            restartProgress();
        })();

        let closeTimeout;
        let currentOpenDropdown = null;

        // Toggle calendar dropdown
        function toggleCalendarDropdown(event, eventId) {
            event.stopPropagation();
            const dropdown = document.getElementById('calendar-dropdown-' + eventId);
            const card = dropdown.closest('.event-card'); // Get the parent card
            
            // Clear any pending close timeout
            if (closeTimeout) {
                clearTimeout(closeTimeout);
            }
            
            // Close all other dropdowns and remove dropdown-active from all cards
            document.querySelectorAll('.calendar-dropdown').forEach(d => {
                if (d.id !== 'calendar-dropdown-' + eventId) {
                    d.classList.remove('active');
                    const otherCard = d.closest('.event-card');
                    if (otherCard) {
                        otherCard.classList.remove('dropdown-active');
                    }
                }
            });
            
            // Toggle current dropdown
            const isActive = dropdown.classList.contains('active');
            
            if (!isActive) {
                dropdown.classList.add('active');
                card.classList.add('dropdown-active'); // Add high z-index to card
                currentOpenDropdown = eventId;
            } else {
                dropdown.classList.remove('active');
                card.classList.remove('dropdown-active'); // Remove high z-index from card
                currentOpenDropdown = null;
            }
        }
        
        // Keep dropdown open when mouse enters
        function keepDropdownOpen(eventId) {
            if (closeTimeout) {
                clearTimeout(closeTimeout);
            }
        }
        
        // Schedule dropdown close when mouse leaves
        function scheduleDropdownClose(eventId) {
            closeTimeout = setTimeout(() => {
                const dropdown = document.getElementById('calendar-dropdown-' + eventId);
                const card = dropdown.closest('.event-card');
                dropdown.classList.remove('active');
                card.classList.remove('dropdown-active'); // Remove high z-index when closing
                currentOpenDropdown = null;
            }, 300);
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.calendar-dropdown') && !event.target.closest('.calendar-btn')) {
                document.querySelectorAll('.calendar-dropdown').forEach(d => {
                    d.classList.remove('active');
                    const card = d.closest('.event-card');
                    if (card) {
                        card.classList.remove('dropdown-active'); // Remove high z-index from all cards
                    }
                });
                currentOpenDropdown = null;
            }
        });
        
        // Prevent card hover from closing dropdown
        document.querySelectorAll('.event-card').forEach(card => {
            card.addEventListener('mouseenter', function(e) {
                // Don't close dropdown if there's one open
                if (currentOpenDropdown && !this.querySelector('#calendar-dropdown-' + currentOpenDropdown)) {
                    // Only close if hovering different card
                }
            });
        });
        
        // Open event modal
        function openEventModal(eventId) {
            const modal = document.getElementById('eventModal');
            const modalBody = document.getElementById('modalBody');
            
            // Show modal with loading state
            modal.classList.add('active');
            modalBody.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-4xl" style="color: var(--color-primary);"></i></div>';
            
            // Fetch event details
            fetch(`/events/${eventId}/details`)
                .then(response => response.json())
                .then(data => {
                    modalBody.innerHTML = `
                        <div>
                            ${data.category ? `<span class="category-badge text-xs font-semibold px-3 py-1.5 rounded-full inline-block mb-3">${data.category}</span>` : ''}

                            <h2 class="text-xl sm:text-3xl font-bold mb-4 sm:mb-6 leading-snug" style="color: var(--color-accent);">${data.title}</h2>

                            <div class="grid sm:grid-cols-2 gap-3 sm:gap-6 mb-4 sm:mb-6">
                                <div class="space-y-3">
                                    <div class="flex items-center text-gray-700">
                                        <i class="far fa-calendar mr-3 text-base sm:text-xl shrink-0" style="color: var(--color-primary);"></i>
                                        <div>
                                            <div class="font-semibold text-xs text-gray-500 uppercase">Date</div>
                                            <div class="font-medium text-sm sm:text-base">${data.formatted_date}</div>
                                        </div>
                                    </div>

                                    ${data.event_time ? `
                                    <div class="flex items-center text-gray-700">
                                        <i class="far fa-clock mr-3 text-base sm:text-xl shrink-0" style="color: var(--color-primary);"></i>
                                        <div>
                                            <div class="font-semibold text-xs text-gray-500 uppercase">Time</div>
                                            <div class="font-medium text-sm sm:text-base">${data.formatted_time}${data.end_time ? ' - ' + data.end_time : ''}</div>
                                        </div>
                                    </div>` : ''}
                                </div>

                                <div class="space-y-3">
                                    ${data.location ? `
                                    <div class="flex items-start text-gray-700">
                                        <i class="fas fa-map-marker-alt mr-3 text-base sm:text-xl mt-0.5 shrink-0" style="color: var(--color-primary);"></i>
                                        <div>
                                            <div class="font-semibold text-xs text-gray-500 uppercase">Location</div>
                                            <div class="font-medium text-sm sm:text-base">${data.location}</div>
                                        </div>
                                    </div>` : ''}

                                    ${data.organizer ? `
                                    <div class="flex items-center text-gray-700">
                                        <i class="far fa-user mr-3 text-base sm:text-xl shrink-0" style="color: var(--color-primary);"></i>
                                        <div>
                                            <div class="font-semibold text-xs text-gray-500 uppercase">Organizer</div>
                                            <div class="font-medium text-sm sm:text-base">${data.organizer}</div>
                                        </div>
                                    </div>` : ''}
                                </div>
                            </div>

                            ${data.description ? `
                            <div class="mb-4 sm:mb-6">
                                <h3 class="text-base sm:text-lg font-bold mb-2 sm:mb-3" style="color: var(--color-secondary);">About This Event</h3>
                                <p class="text-gray-700 leading-relaxed text-sm sm:text-base">${data.description}</p>
                            </div>` : ''}

                            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4 sm:pt-6 border-t border-gray-200">
                                ${data.external_url ? `
                                <a href="${data.external_url}" target="_blank" class="flex-1 btn-primary text-center py-3 px-4 rounded-lg transition font-medium text-sm sm:text-base">
                                    <i class="fas fa-external-link-alt mr-2"></i>More Info
                                </a>` : ''}
                                <button onclick="showAddToCalendar(${eventId})" class="flex-1 btn-secondary text-center py-3 px-4 rounded-lg transition font-medium text-sm sm:text-base">
                                    <i class="fas fa-calendar-plus mr-2"></i>Add to Calendar
                                </button>
                            </div>

                            <div id="calendar-options-${eventId}" style="display: none;" class="mt-3 bg-gray-50 rounded-lg p-3 sm:p-4">
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="${data.google_calendar_url}" target="_blank" class="flex items-center justify-center py-2 px-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition text-sm">
                                        <i class="fab fa-google mr-2" style="color: var(--color-primary);"></i>Google
                                    </a>
                                    <a href="/events/${eventId}/calendar" class="flex items-center justify-center py-2 px-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition text-sm">
                                        <i class="far fa-calendar mr-2" style="color: var(--color-primary);"></i>iCal
                                    </a>
                                    <a href="${data.outlook_calendar_url}" target="_blank" class="flex items-center justify-center py-2 px-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition text-sm">
                                        <i class="far fa-calendar-alt mr-2" style="color: var(--color-primary);"></i>Outlook
                                    </a>
                                    <a href="${data.yahoo_calendar_url}" target="_blank" class="flex items-center justify-center py-2 px-3 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition text-sm">
                                        <i class="fab fa-yahoo mr-2" style="color: var(--color-primary);"></i>Yahoo
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                })
                .catch(error => {
                    modalBody.innerHTML = '<div class="text-center py-8 text-red-600">Error loading event details.</div>';
                });
        }
        
        // Close modal
        function closeEventModal() {
            document.getElementById('eventModal').classList.remove('active');
        }
        
        // Show calendar options in modal
        function showAddToCalendar(eventId) {
            const calendarOptions = document.getElementById('calendar-options-' + eventId);
            calendarOptions.style.display = calendarOptions.style.display === 'none' ? 'block' : 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('eventModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeEventModal();
            }
        });
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEventModal();
            }
        });
        
    </script>

</body>
</html>