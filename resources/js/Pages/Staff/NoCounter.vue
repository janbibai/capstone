<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="No Counter Assigned" />
        
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl shadow-md p-12 text-center">
                    <div class="text-6xl mb-6">⚠️</div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-4">
                        No Counter Assigned
                    </h1>
                    <p class="text-gray-600 mb-6">
                        You are not currently assigned to a counter. Please contact your administrator.
                    </p>
                    <a href="/" class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700">
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>