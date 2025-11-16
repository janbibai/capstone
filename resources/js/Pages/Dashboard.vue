<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    stats: Object,
    queuesByService: Object,
    recentQueues: Array,
    serviceStats: Array
});

const getStatusColor = (status) => {
    const colors = {
        waiting: 'bg-yellow-100 text-yellow-800',
        called: 'bg-blue-100 text-blue-800',
        serving: 'bg-purple-100 text-purple-800',
        completed: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800',
        no_show: 'bg-gray-100 text-gray-800'
    };
    return colors[status] || colors.waiting;
};

const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit'
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Dashboard" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Page Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600 mt-2">Welcome back! Here's today's overview</p>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Patients Today</p>
                                <p class="text-3xl font-bold text-indigo-600 mt-2">
                                    {{ stats.total_patients }}
                                </p>
                            </div>
                            <div class="text-5xl">👥</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Completed</p>
                                <p class="text-3xl font-bold text-green-600 mt-2">
                                    {{ stats.completed }}
                                </p>
                            </div>
                            <div class="text-5xl">✅</div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            {{ stats.total_patients > 0 ? Math.round((stats.completed / stats.total_patients) * 100) : 0 }}% completion rate
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Currently Waiting</p>
                                <p class="text-3xl font-bold text-yellow-600 mt-2">
                                    {{ stats.waiting }}
                                </p>
                            </div>
                            <div class="text-5xl">⏳</div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Avg. Wait Time</p>
                                <p class="text-3xl font-bold text-blue-600 mt-2">
                                    {{ Math.round(stats.average_wait_time) }}
                                </p>
                                <p class="text-sm text-gray-500">minutes</p>
                            </div>
                            <div class="text-5xl">⏱️</div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Actions</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <Link
                            :href="route('queue.kiosk')"
                            class="flex flex-col items-center gap-2 p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors"
                        >
                            <span class="text-3xl">🖥️</span>
                            <span class="font-semibold text-gray-700">Open Kiosk</span>
                        </Link>

                        <Link
                            :href="route('staff.queue')"
                            class="flex flex-col items-center gap-2 p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors"
                        >
                            <span class="text-3xl">👨‍⚕️</span>
                            <span class="font-semibold text-gray-700">Staff Dashboard</span>
                        </Link>

                        <Link
                            :href="route('display.board')"
                            target="_blank"
                            class="flex flex-col items-center gap-2 p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors"
                        >
                            <span class="text-3xl">📺</span>
                            <span class="font-semibold text-gray-700">Display Board</span>
                        </Link>

                        <a
                            href="#"
                            class="flex flex-col items-center gap-2 p-4 border-2 border-gray-200 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors"
                        >
                            <span class="text-3xl">📊</span>
                            <span class="font-semibold text-gray-700">Reports</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Service Statistics -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Service Statistics (Today)</h2>
                        <div class="space-y-4">
                            <div
                                v-for="service in serviceStats"
                                :key="service.id"
                                class="border-2 border-gray-200 rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl font-bold text-indigo-600">
                                            {{ service.code }}
                                        </span>
                                        <span class="font-semibold text-gray-800">
                                            {{ service.name }}
                                        </span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <p class="text-sm text-gray-600">Total</p>
                                        <p class="text-2xl font-bold text-gray-800">
                                            {{ service.today_count }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Completed</p>
                                        <p class="text-2xl font-bold text-green-600">
                                            {{ service.completed_count }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-3 bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-green-500 h-2 rounded-full transition-all"
                                        :style="{ width: service.today_count > 0 ? `${(service.completed_count / service.today_count) * 100}%` : '0%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Queue Activity -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Recent Queue Activity</h2>
                        <div class="space-y-3 max-h-[600px] overflow-y-auto">
                            <div
                                v-for="queue in recentQueues"
                                :key="queue.id"
                                class="border-2 border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors"
                            >
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-xl font-bold text-indigo-600">
                                                {{ queue.queue_number }}
                                            </span>
                                            <span :class="getStatusColor(queue.status)" class="px-2 py-1 rounded-full text-xs font-semibold">
                                                {{ queue.status }}
                                            </span>
                                        </div>
                                        <p class="font-semibold text-gray-800">
                                            {{ queue.patient.first_name }} {{ queue.patient.last_name }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            {{ queue.service.name }}
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1">
                                            {{ formatTime(queue.created_at) }}
                                            <span v-if="queue.counter"> • {{ queue.counter.name }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Management Links (if admin) -->
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 rounded-xl shadow-md p-6 mt-8 text-white">
                    <h2 class="text-xl font-bold mb-4">System Management</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a
                            href="#"
                            class="flex flex-col items-center gap-2 p-4 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-colors"
                        >
                            <span class="text-3xl">🏢</span>
                            <span class="font-semibold">Departments</span>
                        </a>
                        <a
                            href="#"
                            class="flex flex-col items-center gap-2 p-4 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-colors"
                        >
                            <span class="text-3xl">⚕️</span>
                            <span class="font-semibold">Services</span>
                        </a>
                        <a
                            href="#"
                            class="flex flex-col items-center gap-2 p-4 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-colors"
                        >
                            <span class="text-3xl">🪟</span>
                            <span class="font-semibold">Counters</span>
                        </a>
                        <a
                            href="#"
                            class="flex flex-col items-center gap-2 p-4 bg-white bg-opacity-20 rounded-lg hover:bg-opacity-30 transition-colors"
                        >
                            <span class="text-3xl">👨‍💼</span>
                            <span class="font-semibold">Staff</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>