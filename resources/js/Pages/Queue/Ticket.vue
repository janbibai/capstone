<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    queue: Object,
    position: Number
});

const currentPosition = ref(props.position);
const currentStatus = ref(props.queue.status);

const fetchStatus = async () => {
    try {
        const response = await fetch(route('queue.status', props.queue.id));
        const data = await response.json();
        currentPosition.value = data.position;
        currentStatus.value = data.queue.status;
    } catch (error) {
        console.error('Failed to fetch status:', error);
    }
};

let interval;
onMounted(() => {
    interval = setInterval(fetchStatus, 5000); // Check every 5 seconds
});

onUnmounted(() => {
    if (interval) clearInterval(interval);
});

const print = () => {
    window.print();
};

const getStatusColor = (status) => {
    const colors = {
        waiting: 'bg-yellow-100 text-yellow-800 border-yellow-300',
        called: 'bg-blue-100 text-blue-800 border-blue-300',
        serving: 'bg-green-100 text-green-800 border-green-300',
        completed: 'bg-gray-100 text-gray-800 border-gray-300'
    };
    return colors[status] || colors.waiting;
};

const getStatusText = (status) => {
    const texts = {
        waiting: 'Waiting',
        called: 'Please Proceed to Counter',
        serving: 'Now Serving',
        completed: 'Completed'
    };
    return texts[status] || status;
};
</script>

<template>
    <Head title="Queue Ticket" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-4">
        <div class="max-w-2xl w-full">
            <!-- Success Animation -->
            <div class="text-center mb-8 animate-bounce">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-500 rounded-full text-white text-4xl">
                    ✓
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mt-4">
                    Registration Successful!
                </h1>
            </div>

            <!-- Ticket Card -->
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                <!-- Ticket Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 text-white p-8 text-center">
                    <p class="text-lg opacity-90 mb-2">Your Queue Number</p>
                    <div class="text-7xl font-bold tracking-wider">
                        {{ queue.queue_number }}
                    </div>
                    <p class="text-lg opacity-90 mt-4">{{ queue.service.name }}</p>
                </div>

                <!-- Ticket Body -->
                <div class="p-8 space-y-6">
                    <!-- Status Badge -->
                    <div class="text-center">
                        <span :class="getStatusColor(currentStatus)" class="inline-block px-6 py-3 rounded-full font-semibold text-lg border-2">
                            {{ getStatusText(currentStatus) }}
                        </span>
                    </div>

                    <!-- Position in Queue -->
                    <div v-if="currentStatus === 'waiting'" class="bg-indigo-50 rounded-xl p-6 text-center">
                        <p class="text-gray-600 mb-2">People ahead of you</p>
                        <p class="text-5xl font-bold text-indigo-600">
                            {{ currentPosition - 1 }}
                        </p>
                    </div>

                    <!-- Counter Information (when called) -->
                    <div v-if="currentStatus === 'called' && queue.counter" class="bg-blue-50 rounded-xl p-6 text-center border-2 border-blue-300">
                        <p class="text-gray-600 mb-2">Please proceed to</p>
                        <p class="text-4xl font-bold text-blue-600">
                            {{ queue.counter.name }}
                        </p>
                    </div>

                    <!-- Patient Info -->
                    <div class="border-t pt-6 space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Patient Name:</span>
                            <span class="font-semibold text-gray-800">
                                {{ queue.patient.first_name }} {{ queue.patient.last_name }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Department:</span>
                            <span class="font-semibold text-gray-800">
                                {{ queue.service.department.name }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Time Registered:</span>
                            <span class="font-semibold text-gray-800">
                                {{ new Date(queue.created_at).toLocaleTimeString() }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Estimated Time:</span>
                            <span class="font-semibold text-gray-800">
                                ~{{ queue.service.estimated_time }} minutes
                            </span>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="bg-yellow-50 rounded-xl p-6 border-l-4 border-yellow-400">
                        <h3 class="font-semibold text-gray-800 mb-2">📋 Important Reminders:</h3>
                        <ul class="text-sm text-gray-700 space-y-1">
                            <li>• Please stay near the waiting area</li>
                            <li>• Listen for your queue number announcement</li>
                            <li>• Check the display board for updates</li>
                            <li>• Bring this ticket when called</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button
                            @click="print"
                            class="flex-1 px-6 py-4 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors"
                        >
                            🖨️ Print Ticket
                        </button>
                        <a
                            :href="route('queue.kiosk')"
                            class="flex-1 px-6 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors text-center"
                        >
                            New Registration
                        </a>
                    </div>
                </div>
            </div>

            <!-- Auto-refresh indicator -->
            <div class="text-center mt-6 text-gray-600 text-sm">
                <span class="inline-flex items-center gap-2">
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    Auto-updating every 5 seconds
                </span>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            .bg-white {
                visibility: visible !important;
            }
            .bg-white * {
                visibility: visible !important;
            }
        }
    </style>
</template>