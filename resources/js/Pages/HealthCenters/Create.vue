<template>
    <DashLayout>

        <Head title="Add New Health Center" />

        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Add New Health Center</h1>
                        <p class="mt-1 text-sm text-gray-600">Register a new healthcare facility in the system</p>
                    </div>
                    <Link :href="route('health-centers.index')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                    <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <form @submit.prevent="submit">
                    <div class="p-6 space-y-6">
                        <!-- Basic Information Section -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Basic
                                Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Center Name
                                        *</label>
                                    <input v-model="form.name" type="text" id="name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.name }">
                                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                                </div>

                                <!-- Type -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                                    <select v-model="form.type" id="type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.type }">
                                        <option value="" disabled>Select type</option>
                                        <option v-for="option in typeOptions" :value="option" :key="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                    <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email *</label>
                                    <input v-model="form.email" type="email" id="email"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.email }">
                                    <p v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email }}</p>
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone *</label>
                                    <input v-model="form.phone" type="tel" id="phone"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.phone }">
                                    <p v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Location Information Section -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Location
                                Information</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Address -->
                                <div class="md:col-span-2">
                                    <label for="address" class="block text-sm font-medium text-gray-700">Full Address
                                        *</label>
                                    <textarea v-model="form.address" id="address" rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.address }"></textarea>
                                    <p v-if="errors.address" class="mt-1 text-sm text-red-600">{{ errors.address }}</p>
                                </div>

                                <!-- Map Picker -->
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Location
                                        Coordinates</label>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="lat" class="sr-only">Latitude</label>
                                            <input v-model="form.lat" type="number" step="any" id="lat"
                                                placeholder="Latitude"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                                :class="{ 'border-red-300': errors.lat }">
                                            <p v-if="errors.lat" class="mt-1 text-sm text-red-600">{{ errors.lat }}</p>
                                        </div>
                                        <div>
                                            <label for="lng" class="sr-only">Longitude</label>
                                            <input v-model="form.lng" type="number" step="any" id="lng"
                                                placeholder="Longitude"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                                :class="{ 'border-red-300': errors.lng }">
                                            <p v-if="errors.lng" class="mt-1 text-sm text-red-600">{{ errors.lng }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-2 bg-gray-100 rounded-md p-3 text-sm text-gray-600">
                                        <i class="fas fa-info-circle mr-1 text-teal-600"></i>
                                        Click <a href="https://www.latlong.net/" target="_blank"
                                            class="text-teal-600 hover:underline">here</a> to find coordinates for an
                                        address
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Additional
                                Information</h2>
                            <div class="grid grid-cols-1 gap-6">
                                <!-- Description -->
                                <div>
                                    <label for="description"
                                        class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea v-model="form.description" id="description" rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.description }"></textarea>
                                    <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description
                                        }}</p>
                                </div>

                                <!-- Verification -->
                                <div>
                                    <div class="flex items-center">
                                        <input v-model="form.is_verified" id="is_verified" type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                                        <label for="is_verified" class="ml-2 block text-sm text-gray-700">
                                            Mark as verified
                                        </label>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Verified centers appear more prominently in
                                        search results</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Footer -->
                    <div class="px-6 py-3 bg-gray-50 text-right border-t border-gray-200">
                        <button type="button" @click="reset"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 mr-3">
                            Reset
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-75">
                            <i v-if="form.processing" class="fas fa-circle-notch fa-spin mr-2"></i>
                            <i v-else class="fas fa-save mr-2"></i>
                            {{ form.processing ? 'Saving...' : 'Save Health Center' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import DashLayout from '@/Layouts/DashLayout.vue';

const props = defineProps({
    errors: Object,
    typeOptions: Array,
});

const form = reactive({
    name: '',
    email: '',
    phone: '',
    type: '',
    lat: '',
    lng: '',
    address: '',
    description: '',
    is_verified: false,
    processing: false,
});

const submit = () => {
    router.post(route('health-centers.store'), form, {
        onStart: () => form.processing = true,
        onFinish: () => form.processing = false,
        preserveScroll: true,
    });
};

const reset = () => {
    form.name = '';
    form.email = '';
    form.phone = '';
    form.type = '';
    form.lat = '';
    form.lng = '';
    form.address = '';
    form.description = '';
    form.is_verified = false;
};
</script>
