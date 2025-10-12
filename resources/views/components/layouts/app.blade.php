<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Packaging ERP') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom Scrollbar Styling -->
    <style>
        /* Custom scrollbar for the sidebar navigation */
        .sidebar-scroll::-webkit-scrollbar {
            width: 8px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-track {
            background: #374151; /* gray-700 - matches sidebar background */
            border-radius: 4px;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #4B5563; /* gray-600 - slightly lighter than background */
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }
        
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #6B7280; /* gray-500 - lighter on hover */
        }
        
        /* Firefox scrollbar styling */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: #4B5563 #374151;
        }
        
        /* Smooth scrolling */
        .sidebar-scroll {
            scroll-behavior: smooth;
        }
        
        /* Hide scrollbar arrows on webkit browsers */
        .sidebar-scroll::-webkit-scrollbar-button {
            display: none;
        }
    </style>
    
    @livewireStyles
</head>

<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex" x-data="{ sidebarOpen: true }">
        <!-- Sidebar Navigation -->
        <div class="bg-gray-800 text-white shadow-lg fixed h-full z-50 transition-all duration-300 flex flex-col"
            :class="sidebarOpen ? 'w-64' : 'w-16'">
            <div class="p-6 flex-shrink-0">
                <img src="{{ asset('src/images/logo/auth-logo.png') }}" alt="Logo"
                    class="h-full w-full mx-auto transition-opacity duration-300"
                    :class="sidebarOpen ? 'opacity-100' : 'opacity-0'" />
            </div>

            <nav class="mt-6 flex-1 overflow-y-auto sidebar-scroll" x-show="sidebarOpen">
                <div class="px-3 space-y-1 pb-4">
                    <a wire:navigate href="{{ route('dashboard') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                        </svg>
                        Dashboard
                    </a>

                    <!-- Test Menu with Submenu -->
                    <div x-data="{ 
                        testMenuOpen: false,
                        item1Visible: false,
                        item2Visible: false,
                        item3Visible: false,
                        item4Visible: false,
                        toggleMenu() {
                            if (this.testMenuOpen) {
                                // Closing - hide items one by one with delays
                                this.item4Visible = false;
                                setTimeout(() => this.item3Visible = false, 50);
                                setTimeout(() => this.item2Visible = false, 100);
                                setTimeout(() => this.item1Visible = false, 150);
                                setTimeout(() => this.testMenuOpen = false, 200);
                            } else {
                                // Opening - show menu first, then items one by one with same timing
                                this.testMenuOpen = true;
                                setTimeout(() => this.item1Visible = true, 50);
                                setTimeout(() => this.item2Visible = true, 100);
                                setTimeout(() => this.item3Visible = true, 150);
                                setTimeout(() => this.item4Visible = true, 200);
                            }
                        }
                    }" class="space-y-1">
                        <button @click="toggleMenu()"
                            class="group flex items-center justify-between w-full px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200">
                            <div class="flex items-center">
                                <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                                    </path>
                                </svg>
                                Test Menu
                            </div>
                            <svg class="h-4 w-4 transition-transform duration-300 ease-in-out"
                                :class="testMenuOpen ? 'rotate-180' : 'rotate-0'" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Submenu Items -->
                        <div x-show="testMenuOpen" class="ml-6 space-y-1">
                            <a href="#"
                                class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-800 ease-out"
                                x-show="item1Visible"
                                x-transition:enter="transition ease-out duration-800"
                                x-transition:enter-start="opacity-0 transform translate-x-4"
                                x-transition:enter-end="opacity-100 transform translate-x-0"
                                x-transition:leave="transition ease-in duration-800"
                                x-transition:leave-start="opacity-100 transform translate-x-0"
                                x-transition:leave-end="opacity-0 transform translate-x-4">
                                <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Test Item 1
                            </a>
                            <a href="#"
                                class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-800 ease-out"
                                x-show="item2Visible"
                                x-transition:enter="transition ease-out duration-800"
                                x-transition:enter-start="opacity-0 transform translate-x-4"
                                x-transition:enter-end="opacity-100 transform translate-x-0"
                                x-transition:leave="transition ease-in duration-800"
                                x-transition:leave-start="opacity-100 transform translate-x-0"
                                x-transition:leave-end="opacity-0 transform translate-x-4">
                                <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Test Item 2
                            </a>
                            <a href="#"
                                class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-800 ease-out"
                                x-show="item3Visible"
                                x-transition:enter="transition ease-out duration-800"
                                x-transition:enter-start="opacity-0 transform translate-x-4"
                                x-transition:enter-end="opacity-100 transform translate-x-0"
                                x-transition:leave="transition ease-in duration-800"
                                x-transition:leave-start="opacity-100 transform translate-x-0"
                                x-transition:leave-end="opacity-0 transform translate-x-4">
                                <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Test Item 3
                            </a>
                            <a href="#"
                                class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-700 hover:text-white transition-all duration-800 ease-out"
                                x-show="item4Visible"
                                x-transition:enter="transition ease-out duration-800"
                                x-transition:enter-start="opacity-0 transform translate-x-4"
                                x-transition:enter-end="opacity-100 transform translate-x-0"
                                x-transition:leave="transition ease-in duration-800"
                                x-transition:leave-start="opacity-100 transform translate-x-0"
                                x-transition:leave-end="opacity-0 transform translate-x-4">
                                <svg class="mr-3 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Test Item 4
                            </a>
                        </div>
                    </div>

                    <a wire:navigate href="{{ route('quotations') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('quotations') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Quotations
                    </a>

                    <a wire:navigate href="{{ route('customer-orders') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('customer-orders') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Customer Orders
                    </a>

                    <a wire:navigate href="{{ route('job-orders') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('job-orders') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                            </path>
                        </svg>
                        Job Orders
                    </a>

                    <a wire:navigate href="{{ route('supplier-orders') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('supplier-orders') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Supplier Orders
                    </a>

                    <a wire:navigate href="{{ route('grns') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('grns') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                        GRNs
                    </a>

                    <a wire:navigate href="{{ route('material-requests') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('material-requests') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Material Requests
                    </a>

                    <a wire:navigate href="{{ route('delivery-notes') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('delivery-notes') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Delivery Notes
                    </a>

                    <div class="border-t border-gray-700 my-4"></div>

                    <!-- Accounting Section -->
                    <div class="px-3 mb-2">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Accounting</h3>
                    </div>

                    <a wire:navigate href="{{ route('accounting.chart-of-accounts') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.chart-of-accounts') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Chart of Accounts
                    </a>

                    <a wire:navigate href="{{ route('accounting.entries') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.entries') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Entries
                    </a>

                    <a wire:navigate href="{{ route('accounting.journal-entries') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.journal-entries') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Journal Entries
                    </a>

                    <a wire:navigate href="{{ route('accounting.currencies') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.currencies') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Currencies
                    </a>

                    <a wire:navigate href="{{ route('accounting.exchange-rates') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.exchange-rates') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                        Exchange Rates
                    </a>

                    <!-- Reports Section -->
                    <div class="px-3 mb-2 mt-4">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Reports</h3>
                    </div>

                    <a wire:navigate href="{{ route('accounting.reports.balance-sheet') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.reports.balance-sheet') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                        </svg>
                        Balance Sheet
                    </a>

                    <a wire:navigate href="{{ route('accounting.entry-types') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.entry-types') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Entry Types
                    </a>

                    <div class="border-t border-gray-700 my-4"></div>

                    <!-- Role Management Section -->
                    <div class="px-3 mb-2">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Administration</h3>
                    </div>

                    <a wire:navigate href="{{ route('role-management') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('role-management') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z">
                            </path>
                        </svg>
                        Role Management
                    </a>

                    <a wire:navigate href="{{ route('user-management') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('user-management') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                        User Management
                    </a>

                    <a wire:navigate href="{{ route('permission-management') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('permission-management') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0117 9z">
                            </path>
                        </svg>
                        Permission Management
                    </a>

                    <div class="border-t border-gray-700 my-4"></div>

                    <!-- UOM Management Section -->
                    <div class="px-3 mb-2">
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">UOM Management</h3>
                    </div>

                    <a wire:navigate href="{{ route('uom-dashboard') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('uom-dashboard') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                        </svg>
                        UOM Dashboard
                    </a>

                    <a wire:navigate href="{{ route('uom-management') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('uom-management') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z">
                            </path>
                        </svg>
                        UOM Management
                    </a>

                    <a wire:navigate href="{{ route('uom-conversion-management') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('uom-conversion-management') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        UOM Conversions
                    </a>

                    <a wire:navigate href="{{ route('uom-conversion-profile-management') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('uom-conversion-profile-management') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                            </path>
                        </svg>
                        Conversion Profiles
                    </a>

                    <a wire:navigate href="{{ route('uom-conversion-examples') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('uom-conversion-examples') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                        Conversion Examples
                    </a>

                    <div class="border-t border-gray-700 my-4"></div>

                    <a wire:navigate href="{{ route('inventory-dashboard') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('inventory-dashboard') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                        Inventory Dashboard
                    </a>

                    <a wire:navigate href="{{ route('inventory-transactions') }}"
                        class="group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('inventory-transactions') ? 'bg-gray-700 text-white' : '' }}">
                        <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                        Inventory Transactions
                    </a>

                </div>
            </nav>

            <!-- Collapsed Navigation (Icons Only) -->
            <nav class="mt-6 flex-1 overflow-y-auto sidebar-scroll" x-show="!sidebarOpen">
                <div class="px-2 space-y-1 pb-4">
                    <a wire:navigate href="{{ route('dashboard') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-gray-700 text-white' : '' }}"
                        title="Dashboard">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                        </svg>
                    </a>
                    <a href="#"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200"
                        title="Test Menu">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('quotations') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('quotations') ? 'bg-gray-700 text-white' : '' }}"
                        title="Quotations">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('customer-orders') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('customer-orders') ? 'bg-gray-700 text-white' : '' }}"
                        title="Customer Orders">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('job-orders') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('job-orders') ? 'bg-gray-700 text-white' : '' }}"
                        title="Job Orders">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                            </path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('supplier-orders') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('supplier-orders') ? 'bg-gray-700 text-white' : '' }}"
                        title="Supplier Orders">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('grns') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('grns') ? 'bg-gray-700 text-white' : '' }}"
                        title="GRNs">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01">
                            </path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('material-requests') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('material-requests') ? 'bg-gray-700 text-white' : '' }}"
                        title="Material Requests">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('delivery-notes') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('delivery-notes') ? 'bg-gray-700 text-white' : '' }}"
                        title="Delivery Notes">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('inventory-dashboard') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('inventory-dashboard') ? 'bg-gray-700 text-white' : '' }}"
                        title="Inventory Dashboard">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('inventory-transactions') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('inventory-transactions') ? 'bg-gray-700 text-white' : '' }}"
                        title="Inventory Transactions">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                    </a>
                    
                    <!-- Accounting Icons (Collapsed) -->
                    <a wire:navigate href="{{ route('accounting.chart-of-accounts') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.chart-of-accounts') ? 'bg-gray-700 text-white' : '' }}"
                        title="Chart of Accounts">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </a>
                    
                    <a wire:navigate href="{{ route('accounting.entries') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.entries') ? 'bg-gray-700 text-white' : '' }}"
                        title="Entries">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('accounting.journal-entries') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.journal-entries') ? 'bg-gray-700 text-white' : '' }}"
                        title="Journal Entries">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('accounting.currencies') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.currencies') ? 'bg-gray-700 text-white' : '' }}"
                        title="Currencies">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('accounting.exchange-rates') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.exchange-rates') ? 'bg-gray-700 text-white' : '' }}"
                        title="Exchange Rates">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                    </a>
                    
                    <!-- Reports Icons (Collapsed) -->
                    <a wire:navigate href="{{ route('accounting.reports.balance-sheet') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.reports.balance-sheet') ? 'bg-gray-700 text-white' : '' }}"
                        title="Balance Sheet">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
                        </svg>
                    </a>
                    <a wire:navigate href="{{ route('accounting.entry-types') }}"
                        class="group flex items-center justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 {{ request()->routeIs('accounting.entry-types') ? 'bg-gray-700 text-white' : '' }}"
                        title="Entry Types">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </a>
                </div>


            </nav>

        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-16'">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center">
                        <!-- Sidebar Toggle Button -->
                        <button @click="sidebarOpen = !sidebarOpen"
                            class="mr-4 p-1.5 bg-gray-100 text-gray-600 hover:text-gray-500 hover:bg-indigo-100 rounded-md transition-all duration-200">
                            <!-- Folding Icon (when sidebar is open) -->
                            <svg x-show="sidebarOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                class="h-5 w-5 transition-opacity duration-200" aria-hidden="true" fill="#878d96">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M157.3 413.2c-6.6 0-13.1-2.5-18.2-7.5L7.5 274c-10-10-10-26.3 0-36.3L139.2 106c10-10 26.3-10 36.3 0s10 26.3 0 36.3L62 255.9l113.5 113.5c10 10 10 26.3 0 36.3-5 5-11.6 7.5-18.2 7.5">
                                </path>
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M290.1 285H73.5c-14.2 0-25.7-11.5-25.7-25.7s11.5-25.7 25.7-25.7h216.7c14.2 0 25.7 11.5 25.7 25.7-.1 14.2-11.6 25.7-25.8 25.7m195.2 0h-94.4c-14.2 0-25.7-11.5-25.7-25.7s11.5-25.7 25.7-25.7h94.4c14.2 0 25.7 11.5 25.7 25.7S499.5 285 485.3 285m0 125.1H342c-14.2 0-25.7-11.5-25.7-25.7s11.5-25.7 25.7-25.7h143.3c14.2 0 25.7 11.5 25.7 25.7s-11.5 25.7-25.7 25.7m0-250.1H342c-14.2 0-25.7-11.5-25.7-25.7s11.5-25.7 25.7-25.7h143.3c14.2 0 25.7 11.5 25.7 25.7S499.5 160 485.3 160">
                                </path>
                            </svg>
                            <!-- Unfolding Icon (when sidebar is closed) -->
                            <svg x-show="!sidebarOpen" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"
                                class="h-5 w-5 transition-opacity duration-200" aria-hidden="true" fill="#878d96">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M353.7 98.5c6.6 0 13.1 2.5 18.2 7.5l131.7 131.7c10 10 10 26.3 0 36.3L371.8 405.7c-10 10-26.3 10-36.3 0s-10-26.3 0-36.3L449 255.9 335.5 142.4c-10-10-10-26.3 0-36.3 5-5.1 11.6-7.6 18.2-7.6">
                                </path>
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M220.9 226.8h216.7c14.2 0 25.7 11.5 25.7 25.7s-11.5 25.7-25.7 25.7H220.9c-14.2 0-25.7-11.5-25.7-25.7s11.5-25.7 25.7-25.7m-195.2 0h94.4c14.2 0 25.7 11.5 25.7 25.7s-11.5 25.7-25.7 25.7H25.7C11.5 278.1 0 266.6 0 252.5s11.5-25.7 25.7-25.7m0-125.1H169c14.2 0 25.7 11.5 25.7 25.7S183.2 153 169 153H25.7C11.5 153 0 141.5 0 127.3s11.5-25.6 25.7-25.6m0 250H169c14.2 0 25.7 11.5 25.7 25.7s-11.5 25.7-25.7 25.7H25.7C11.5 403.1 0 391.6 0 377.4s11.5-25.7 25.7-25.7">
                                </path>
                            </svg>
                        </button>

                        <h2 class="text-2xl font-semibold text-gray-800">
                            @if (request()->routeIs('dashboard'))
                                Dashboard
                            @elseif(request()->routeIs('quotations'))
                                Quotations
                            @elseif(request()->routeIs('customer-orders'))
                                Customer Orders
                            @elseif(request()->routeIs('job-orders'))
                                Job Orders
                            @elseif(request()->routeIs('supplier-orders'))
                                Supplier Orders
                            @elseif(request()->routeIs('grns'))
                                Goods Receipt Notes
                            @elseif(request()->routeIs('material-requests'))
                                Material Requests
                            @elseif(request()->routeIs('delivery-notes'))
                                Delivery Notes
                            @elseif(request()->routeIs('inventory-dashboard'))
                                Inventory Dashboard
                            @elseif(request()->routeIs('inventory-transactions'))
                                Inventory Transactions
                            @elseif(request()->routeIs('accounting.chart-of-accounts'))
                                Chart of Accounts
                            @elseif(request()->routeIs('accounting.entries'))
                                Entries
                            @elseif(request()->routeIs('accounting.journal-entries'))
                                Journal Entries
                            @elseif(request()->routeIs('accounting.currencies'))
                                Currencies
                            @elseif(request()->routeIs('accounting.exchange-rates'))
                                Exchange Rates
                            @elseif(request()->routeIs('accounting.entry-types'))
                                Entry Types
                            @else
                                Packaging ERP
                            @endif
                        </h2>
                    </div>

                    <!-- User Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                            class="flex items-center space-x-2 text-sm text-gray-600 hover:text-gray-900 focus:outline-none">
                            <span class="font-medium">{{ auth()->user()?->name ?? 'Guest' }}</span>
                            <span class="text-gray-500">({{ auth()->user()?->getRoleNames()->first() ?? 'No Role' }})</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" x-transition
                            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border">
                            <a wire:navigate href="{{ route('profile') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile
                            </a>
                            <a wire:navigate href="{{ route('profile') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Settings
                            </a>
                            <div class="border-t border-gray-100"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                        </path>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                @if (session()->has('message'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('message') }}
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>

</html>
