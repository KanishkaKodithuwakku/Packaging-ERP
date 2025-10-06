<x-layouts.app>
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Activity Log</h1>
                    
                    <!-- Filter Options -->
                    <div class="mb-6 flex flex-wrap gap-4">
                        <select class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <option>All Activities</option>
                            <option>Login/Logout</option>
                            <option>Data Changes</option>
                            <option>System Actions</option>
                        </select>
                        <input type="date" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        <input type="date" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Activity List -->
                    <div class="space-y-4">
                        <!-- Today's Activities -->
                        <div class="border-l-4 border-blue-500 pl-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Today</h3>
                            <div class="space-y-3">
                                <div class="flex items-center space-x-3 p-3 bg-green-50 rounded-lg">
                                    <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Logged in successfully</p>
                                        <p class="text-xs text-gray-500">IP: 192.168.1.100 • {{ now()->format('H:i') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-3 bg-blue-50 rounded-lg">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Accessed Dashboard</p>
                                        <p class="text-xs text-gray-500">{{ now()->subMinutes(15)->format('H:i') }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-3 bg-yellow-50 rounded-lg">
                                    <div class="w-2 h-2 bg-yellow-500 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Viewed Quotations</p>
                                        <p class="text-xs text-gray-500">{{ now()->subMinutes(30)->format('H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Yesterday's Activities -->
                        <div class="border-l-4 border-gray-300 pl-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">Yesterday</h3>
                            <div class="space-y-3">
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Created new quotation QT-003</p>
                                        <p class="text-xs text-gray-500">Yesterday at 14:30</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Updated customer order CO-001</p>
                                        <p class="text-xs text-gray-500">Yesterday at 11:15</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Logged out</p>
                                        <p class="text-xs text-gray-500">Yesterday at 17:45</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- This Week's Activities -->
                        <div class="border-l-4 border-gray-300 pl-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-3">This Week</h3>
                            <div class="space-y-3">
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Password changed</p>
                                        <p class="text-xs text-gray-500">3 days ago</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Profile updated</p>
                                        <p class="text-xs text-gray-500">5 days ago</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Load More Button -->
                    <div class="mt-6 text-center">
                        <button class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-md hover:bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Load More Activities
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
