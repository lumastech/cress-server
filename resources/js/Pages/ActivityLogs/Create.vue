<template>
    <DashLayout>

        <Head title="Report New Incident" />

        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Report New Incident</h1>
                        <p class="mt-1 text-sm text-gray-600">Provide details about the incident you're reporting</p>
                    </div>
                    <Link :href="route('incidents.index')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-arrow-left mr-2"></i> Cancel
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
                                <!-- Incident Name -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Incident Title
                                        *</label>
                                    <input v-model="form.name" type="text" id="name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.name }"
                                        placeholder="Brief description of the incident">
                                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                                </div>

                                <!-- Incident Type -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Incident Type
                                        *</label>
                                    <select v-model="form.type" id="type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.type }">
                                        <option value="" disabled>Select incident type</option>
                                        <option v-for="option in typeOptions" :value="option" :key="option">
                                            {{ option.replace('_', ' ') }}
                                        </option>
                                    </select>
                                    <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
                                </div>

                                <!-- Area/Location -->
                                <div>
                                    <label for="area" class="block text-sm font-medium text-gray-700">Area/Location
                                        *</label>
                                    <input v-model="form.area" type="text" id="area"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.area }"
                                        placeholder="Neighborhood, landmark, or address">
                                    <p v-if="errors.area" class="mt-1 text-sm text-red-600">{{ errors.area }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Location Mapping Section -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Location on
                                Map</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Map -->
                                <div class="h-64 md:h-full">
                                    <GoogleMap :api-key="mapsApiKey" style="width: 100%; height: 100%"
                                        :center="mapCenter" :zoom="15" @click="handleMapClick">
                                        <Marker v-if="form.lat && form.lng" :options="{
                                            position: { lat: parseFloat(form.lat), lng: parseFloat(form.lng) },
                                            draggable: true,
                                            icon: {
                                                url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                                                scaledSize: { width: 32, height: 32 }
                                            }
                                        }" @dragend="handleMarkerDrag" />
                                    </GoogleMap>
                                </div>

                                <!-- Coordinates -->
                                <div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Pin Location</label>
                                            <p class="mt-1 text-sm text-gray-500">
                                                Click on the map or drag the marker to set the exact location
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="lat"
                                                    class="block text-sm font-medium text-gray-700">Latitude *</label>
                                                <input v-model="form.lat" type="number" step="any" id="lat"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                    :class="{ 'border-red-300': errors.lat }" readonly>
                                                <p v-if="errors.lat" class="mt-1 text-sm text-red-600">{{ errors.lat }}
                                                </p>
                                            </div>
                                            <div>
                                                <label for="lng"
                                                    class="block text-sm font-medium text-gray-700">Longitude *</label>
                                                <input v-model="form.lng" type="number" step="any" id="lng"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                    :class="{ 'border-red-300': errors.lng }" readonly>
                                                <p v-if="errors.lng" class="mt-1 text-sm text-red-600">{{ errors.lng }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 p-3 rounded-md text-sm text-gray-600">
                                            <i class="fas fa-info-circle mr-1 text-red-600"></i>
                                            For precise location, you can search coordinates on
                                            <a href="https://www.latlong.net/" target="_blank"
                                                class="text-red-600 hover:underline">latlong.net</a>
                                            and paste them here
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Section -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Incident
                                Details</h2>
                            <div>
                                <label for="details" class="block text-sm font-medium text-gray-700">Description
                                    *</label>
                                <textarea v-model="form.details" id="details" rows="4"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                    :class="{ 'border-red-300': errors.details }"
                                    placeholder="Provide detailed information about what happened..."></textarea>
                                <p v-if="errors.details" class="mt-1 text-sm text-red-600">{{ errors.details }}</p>
                            </div>
                        </div>

                        <!-- Evidence Section -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Evidence
                                (Optional)</h2>
                            <div
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file-upload"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                            <span>Upload photos or videos</span>
                                            <input id="file-upload" name="file-upload" type="file" class="sr-only"
                                                multiple>
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PNG, JPG, MP4 up to 10MB
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Footer -->
                    <div class="px-6 py-3 bg-gray-50 text-right border-t border-gray-200">
                        <button type="button" @click="reset"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 mr-3">
                            Reset Form
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-75">
                            <i v-if="form.processing" class="fas fa-circle-notch fa-spin mr-2"></i>
                            <i v-else class="fas fa-triangle-exclamation mr-2"></i>
                            {{ form.processing ? 'Submitting...' : 'Report Incident' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashLayout>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { GoogleMap, Marker } from 'vue3-google-map';
import DashLayout from '@/Layouts/DashLayout.vue';

const props = defineProps({
    errors: Object,
    typeOptions: Array,
});

const mapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

// Default center - you might want to get this from user's current location
const defaultCenter = { lat: -1.2921, lng: 36.8219 }; // Default to Nairobi coordinates

const form = reactive({
    name: '',
    type: '',
    area: '',
    details: '',
    lat: '',
    lng: '',
    processing: false,
});

const mapCenter = computed(() => {
    return form.lat && form.lng
        ? { lat: parseFloat(form.lat), lng: parseFloat(form.lng) }
        : defaultCenter;
});

const handleMapClick = (e) => {
    form.lat = e.latLng.lat();
    form.lng = e.latLng.lng();
};

const handleMarkerDrag = (e) => {
    form.lat = e.latLng.lat();
    form.lng = e.latLng.lng();
};

const submit = () => {
    router.post(route('incidents.store'), form, {
        onStart: () => form.processing = true,
        onFinish: () => form.processing = false,
        preserveScroll: true,
    });
};

const reset = () => {
    form.name = '';
    form.type = '';
    form.area = '';
    form.details = '';
    form.lat = '';
    form.lng = '';
};
</script>
