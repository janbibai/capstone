<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    services: Array
});

const currentQueues = ref([]);
const waitingCounts = ref({});
const counters = ref([]);
const currentTime = ref(new Date());
const lastUpdated = ref(null);

const fetchData = async () => {
    try {
        const response = await fetch(route('display.data'));
        const data = await response.json();
        
        currentQueues.value = data.currentQueues;
        waitingCounts.value = data.waitingCounts;
        counters.value = data.counters;
        lastUpdated.value = new Date(data.timestamp);
    } catch (error) {
        console.error('Failed to fetch display data:', error);
    }
};

const updateTime = () => {
    currentTime.value = new Date();
};

let dataInterval;
let timeInterval;

onMounted(() => {
    fetchData();
    dataInterval = setInterval(fetchData, 3000); // Refresh every 3 seconds
    timeInterval = setInterval(updateTime, 1000); // Update time every second
});

onUnmounted(() => {
    if (dataInterval) clearInterval(dataInterval);
    if (timeInterval) clearInterval(timeInterval);
});

const formatTime = (date) => {
    return date.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        second: '2-digit'
    });
};

const getCounterStatus = (counter) => {
    const currentQueue = currentQueues.value.find(q => q.counter_id === counter.id && q.status === 'serving');
    if (currentQueue) {
        return {
            status: 'serving',
            queue: currentQueue
        };
    }
    
    const calledQueue = currentQueues.value.find(q => q.counter_id === counter.id && q.status === 'called');
    if (calledQueue) {
        return {
            status: 'called',
            queue: calledQueue
        };
    }
    
    return {
        status: counter.status,
        queue: null
    };
};

const getStatusColor = (status) => {
    const colors = {
        serving: 'bg-green-500',
        called: 'bg-blue-500 animate-pulse',
        available: 'bg-gray-300',
        busy: 'bg-yellow-500',
        closed: 'bg-red-500'
    };
    return colors[status] || colors.closed;
};
</script>

<template>
    <Head title="Queue Display Board" />
    
    <div class="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-indigo-900 text-white">
        <!-- Header -->
        <div class="bg-black bg-opacity-50 backdrop-blur-sm">
            <div class="max-w-7xl mx-auto px-8 py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-4xl font-bold">🏥 Public Health Clinic</h1>
                        <p class="text-xl text-gray-300 mt-2">Queue Display Board</p>
                    </div>
                    <div class="text-right">
                        <div class="text-5xl font-bold font-mono">
                            {{ formatTime(currentTime) }}
                        </div>
                        <p class="text-gray-300 mt-1">
                            {{ currentTime.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-8 py-8">
            <!-- Now Serving / Recently Called -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
                    <span class="text-yellow-400">📢</span>
                    Now Serving
                </h2>
                
                <div v-if="currentQueues.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="queue in currentQueues.slice(0, 6)"
                        :key="queue.id"
                        class="bg-white bg-opacity-10 backdrop-blur-md rounded-2xl p-8 border-2"
                        :class="queue.status === 'called' ? 'border-blue-400 animate-pulse' : 'border-green-400'"
                    >
                        <div class="text-center">
                            <div class="text-sm text-gray-300 mb-2">
                                {{ queue.status === 'called' ? 'PLEASE PROCEED TO' : 'NOW SERVING' }}
                            </div>
                            <div class="text-7xl font-bold text-yellow-400 mb-4 tracking-wider">
                                {{ queue.queue_number }}
                            </div>
                            <div class="text-3xl font-bold mb-2">
                                {{ queue.counter.name }}
                            </div>
                            <div class="text-lg text-gray-300">
                                {{ queue.service.name }}
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16">
                    <div class="text-8xl mb-6">⏸️</div>
                    <p class="text-3xl text-gray-300">No active queue at the moment</p>
                </div>
            </div>

            <!-- Counter Status Grid -->
            <div class="mb-8">
                <h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
                    <span class="text-blue-400">🪟</span>
                    Counter Status
                </h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div
                        v-for="counter in counters"
                        :key="counter.id"
                        class="bg-white bg-opacity-10 backdrop-blur-md rounded-xl p-6"
                    >
                        <div class="text-center">
                            <div :class="getStatusColor(getCounterStatus(counter).status)" 
                                 class="w-4 h-4 rounded-full mx-auto mb-3">
                            </div>
                            <div class="text-2xl font-bold mb-2">
                                {{ counter.name }}
                            </div>
                            <div class="text-sm text-gray-300 mb-2">
                                {{ counter.service.name }}
                            </div>
                            <div v-if="getCounterStatus(counter).queue" class="text-3xl font-bold text-yellow-400 mt-3">
                                {{ getCounterStatus(counter).queue.queue_number }}
                            </div>
                            <div v-else class="text-lg text-gray-400 mt-3">
                                {{ counter.status === 'closed' ? 'Closed' : counter.status === 'available' ? 'Available' : 'Busy' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Waiting Queue Summary -->
            <div>
                <h2 class="text-3xl font-bold mb-6 flex items-center gap-3">
                    <span class="text-orange-400">⏳</span>
                    Waiting Queue
                </h2>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                    <div
                        v-for="service in services"
                        :key="service.id"
                        class="bg-white bg-opacity-10 backdrop-blur-md rounded-xl p-6 text-center"
                    >
                        <div class="text-5xl font-bold text-indigo-300 mb-2">
                            {{ service.code }}
                        </div>
                        <div class="text-sm text-gray-300 mb-3">
                            {{ service.name }}
                        </div>
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-2xl">⏳</span>
                            <span class="text-4xl font-bold text-yellow-400">
                                {{ waitingCounts[service.id] || 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="fixed bottom-0 left-0 right-0 bg-black bg-opacity-50 backdrop-blur-sm py-4">
            <div class="max-w-7xl mx-auto px-8">
                <div class="flex items-center justify-between text-sm text-gray-400">
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <span>Live Updates</span>
                    </div>
                    <div v-if="lastUpdated">
                        Last updated: {{ formatTime(lastUpdated) }}
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-green-500"></span> Serving
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500"></span> Called
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-gray-300"></span> Available
                        </span>
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500"></span> Closed
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>