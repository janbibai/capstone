<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    services: Array
});

const step = ref(1);
const selectedService = ref(null);

const form = useForm({
    first_name: '',
    last_name: '',
    middle_name: '',
    phone: '',
    service_id: null,
    priority: 'normal'
});

const selectService = (service) => {
    selectedService.value = service;
    form.service_id = service.id;
    step.value = 2;
};

const submit = () => {
    form.post(route('queue.register'));
};

const reset = () => {
    form.reset();
    step.value = 1;
    selectedService.value = null;
};
</script>

<template>
    <Head title="Patient Registration Kiosk" />
    
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100">
        <!-- Header -->
        <div class="bg-white shadow-md">
            <div class="max-w-7xl mx-auto px-8 py-6">
                <h1 class="text-4xl font-bold text-indigo-900">
                    🏥 Public Health Clinic
                </h1>
                <p class="text-gray-600 mt-2">Welcome! Please register for your queue number</p>
            </div>
        </div>

        <div class="max-w-6xl mx-auto px-8 py-12">
            <!-- Step 1: Service Selection -->
            <div v-if="step === 1" class="space-y-6">
                <h2 class="text-3xl font-bold text-gray-800 text-center mb-8">
                    Select Service
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <button
                        v-for="service in services"
                        :key="service.id"
                        @click="selectService(service)"
                        class="bg-white p-8 rounded-xl shadow-lg hover:shadow-2xl transform hover:scale-105 transition-all duration-200 text-left border-2 border-transparent hover:border-indigo-500"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-5xl font-bold text-indigo-600">
                                {{ service.code }}
                            </div>
                            <div class="text-sm text-gray-500">
                                ~{{ service.estimated_time }} min
                            </div>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">
                            {{ service.name }}
                        </h3>
                        <p class="text-gray-600 text-sm">
                            {{ service.department.name }}
                        </p>
                        <p class="text-gray-500 text-sm mt-2">
                            {{ service.description }}
                        </p>
                    </button>
                </div>
            </div>

            <!-- Step 2: Patient Information -->
            <div v-if="step === 2" class="max-w-2xl mx-auto">
                <div class="bg-white rounded-xl shadow-xl p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            Patient Information
                        </h2>
                        <button 
                            @click="reset" 
                            class="text-gray-500 hover:text-gray-700"
                        >
                            ← Back
                        </button>
                    </div>

                    <div class="bg-indigo-50 p-4 rounded-lg mb-6">
                        <p class="text-sm text-gray-600">Selected Service:</p>
                        <p class="text-lg font-semibold text-indigo-900">
                            {{ selectedService.name }}
                        </p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                First Name *
                            </label>
                            <input
                                v-model="form.first_name"
                                type="text"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                                placeholder="Juan"
                            />
                            <div v-if="form.errors.first_name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.first_name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Last Name *
                            </label>
                            <input
                                v-model="form.last_name"
                                type="text"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                                placeholder="Dela Cruz"
                            />
                            <div v-if="form.errors.last_name" class="text-red-500 text-sm mt-1">
                                {{ form.errors.last_name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Middle Name (Optional)
                            </label>
                            <input
                                v-model="form.middle_name"
                                type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                                placeholder="Santos"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Contact Number (Optional)
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                                placeholder="09XX XXX XXXX"
                            />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Priority Status *
                            </label>
                            <select
                                v-model="form.priority"
                                required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
                            >
                                <option value="normal">Regular</option>
                                <option value="senior">Senior Citizen</option>
                                <option value="pwd">Person with Disability (PWD)</option>
                                <option value="pregnant">Pregnant</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>

                        <div class="flex gap-4 pt-4">
                            <button
                                type="button"
                                @click="reset"
                                class="flex-1 px-6 py-4 border-2 border-gray-300 text-gray-700 rounded-lg font-semibold hover:bg-gray-50 transition-colors"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="flex-1 px-6 py-4 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors text-lg"
                            >
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Get Queue Number</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>