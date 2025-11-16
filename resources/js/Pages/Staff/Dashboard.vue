<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    counter: Object,
    currentQueue: Object,
    waitingQueues: Array,
    stats: Object
});

const showCompleteModal = ref(false);
const completeForm = useForm({
    notes: ''
});

const callNext = () => {
    router.post(route('staff.queue.call'), {}, {
        preserveScroll: true,
        onSuccess: () => {
            playNotificationSound();
        }
    });
};

const startServing = (id) => {
    router.post(route('staff.queue.serving', id), {}, {
        preserveScroll: true
    });
};

const completeQueue = () => {
    if (!props.currentQueue) return;
    
    completeForm.post(route('staff.queue.complete', props.currentQueue.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCompleteModal.value = false;
            completeForm.reset();
        }
    });
};

const markNoShow = (id) => {
    if (confirm('Mark this patient as no show?')) {
        router.post(route('staff.queue.noshow', id), {}, {
            preserveScroll: true
        });
    }
};

const updateCounterStatus = (status) => {
    router.post(route('staff.counter.status'), { status }, {
        preserveScroll: true
    });
};

const playNotificationSound = () => {
    // You can add audio notification here
    console.log('Queue called!');
};

const getPriorityColor = (priority) => {
    const colors = {
        emergency: 'bg-red-100 text-red-800',
        senior: 'bg-purple-100 text-purple-800',
        pwd: 'bg-blue-100 text-blue-800',
        pregnant: 'bg-pink-100 text-pink-800',
        normal: 'bg-gray-100 text-gray-800'
    };
    return colors[priority] || colors.normal;
};

const getPriorityLabel = (priority) => {
    const labels = {
        emergency: '🚨 Emergency',
        senior: '👴 Senior',
        pwd: '♿ PWD',
        pregnant: '🤰 Pregnant',
        normal: 'Regular'
    };
    return labels[priority] || priority;
};
</script>

<template>
    <Head title="Staff Queue Dashboard" />
    
    <div class="min-h-screen bg-gray-50">
        <!-- Top Bar -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-8 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ counter.name }}
                        </h1>
                        <p class="text-gray-600">{{ counter.service.name }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Counter Status</p>
                            <select 
                                :value="counter.status"
                                @change="updateCounterStatus($event.target.value)"
                                class="mt-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                            >
                                <option value="available">🟢 Available</option>
                                <option value="busy">🟡 Busy</option>
                                <option value="closed">🔴 Closed</option>
                            </select>
                        </div>
                        <a 
                            :href="route('dashboard')"
                            class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200"
                        >
                            ← Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-8 py-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Completed Today</p>
                            <p class="text-3xl font-bold text-green-600">{{ stats.completed }}</p>
                        </div>
                        <div class="text-4xl">✅</div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Currently Waiting</p>
                            <p class="text-3xl font-bold text-yellow-600">{{ stats.waiting }}</p>
                        </div>
                        <div class="text-4xl">⏳</div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Avg. Service Time</p>
                            <p class="text-3xl font-bold text-blue-600">
                                {{ Math.round(stats.average_time) }} min
                            </p>
                        </div>
                        <div class="text-4xl">⏱️</div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Current Patient -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">
                        Current Patient
                    </h2>

                    <div v-if="currentQueue" class="space-y-6">
                        <div class="bg-gradient-to-r from-indigo-500 to-blue-500 rounded-xl p-8 text-white text-center">
                            <p class="text-lg opacity-90 mb-2">Now Serving</p>
                            <div class="text-6xl font-bold tracking-wider mb-4">
                                {{ currentQueue.queue_number }}
                            </div>
                            <p class="text-xl">
                                {{ currentQueue.patient.first_name }} {{ currentQueue.patient.last_name }}
                            </p>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Priority:</span>
                                <span :class="getPriorityColor(currentQueue.priority)" class="px-3 py-1 rounded-full text-sm font-semibold">
                                    {{ getPriorityLabel(currentQueue.priority) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Wait Time:</span>
                                <span class="font-semibold">
                                    {{ Math.round((new Date() - new Date(currentQueue.created_at)) / 60000) }} min
                                </span>
                            </div>
                            <div v-if="currentQueue.serving_at" class="flex justify-between">
                                <span class="text-gray-600">Serving Since:</span>
                                <span class="font-semibold">
                                    {{ new Date(currentQueue.serving_at).toLocaleTimeString() }}
                                </span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3 pt-4">
                            <button
                                v-if="currentQueue.status === 'called'"
                                @click="startServing(currentQueue.id)"
                                class="w-full px-6 py-4 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors"
                            >
                                ▶️ Start Serving
                            </button>

                            <button
                                v-if="currentQueue.status === 'serving'"
                                @click="showCompleteModal = true"
                                class="w-full px-6 py-4 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition-colors"
                            >
                                ✓ Complete
                            </button>

                            <button
                                @click="markNoShow(currentQueue.id)"
                                class="w-full px-6 py-3 border-2 border-red-300 text-red-600 rounded-lg font-semibold hover:bg-red-50 transition-colors"
                            >
                                No Show
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center py-12">
                        <div class="text-6xl mb-4">👋</div>
                        <p class="text-gray-600 mb-6">No patient currently being served</p>
                        <button
                            @click="callNext"
                            :disabled="waitingQueues.length === 0"
                            class="px-8 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-lg"
                        >
                            📢 Call Next Patient
                        </button>
                    </div>
                </div>

                <!-- Waiting Queue -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">
                            Waiting Queue ({{ waitingQueues.length }})
                        </h2>
                        <button
                            v-if="!currentQueue"
                            @click="callNext"
                            :disabled="waitingQueues.length === 0"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            📢 Call Next
                        </button>
                    </div>

                    <div v-if="waitingQueues.length > 0" class="space-y-3 max-h-[600px] overflow-y-auto">
                        <div
                            v-for="queue in waitingQueues"
                            :key="queue.id"
                            class="border-2 border-gray-200 rounded-lg p-4 hover:border-indigo-300 transition-colors"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-2xl font-bold text-indigo-600">
                                            {{ queue.queue_number }}
                                        </span>
                                        <span :class="getPriorityColor(queue.priority)" class="px-2 py-1 rounded-full text-xs font-semibold">
                                            {{ getPriorityLabel(queue.priority) }}
                                        </span>
                                    </div>
                                    <p class="font-semibold text-gray-800">
                                        {{ queue.patient.first_name }} {{ queue.patient.last_name }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Waiting: {{ Math.round((new Date() - new Date(queue.created_at)) / 60000) }} min
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="text-center py-12">
                        <div class="text-6xl mb-4">🎉</div>
                        <p class="text-gray-600">No patients waiting</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Complete Modal -->
        <div v-if="showCompleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">
                    Complete Service
                </h3>
                
                <form @submit.prevent="completeQueue" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Notes (Optional)
                        </label>
                        <textarea
                            v-model="completeForm.notes"
                            rows="4"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            placeholder="Add any notes about the service..."
                        ></textarea>
                    </div>

                    <div class="flex gap-4">
                        <button
                            type="button"
                            @click="showCompleteModal = false"
                            class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="completeForm.processing"
                            class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            Complete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>