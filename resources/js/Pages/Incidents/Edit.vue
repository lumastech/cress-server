<template>
    <DashLayout>

        <Head :title="`Edit Incident - ${incident.name}`" />

        <div class="max-w-3xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Edit Incident Report</h1>
                        <p class="mt-1 text-sm text-gray-600">Update details for {{ incident.name }}</p>
                    </div>
                    <Link :href="route('incidents.index')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
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
                                <!-- Incident Name -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700">Incident Title
                                        *</label>
                                    <input v-model="form.name" type="text" id="name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.name }">
                                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                                </div>

                                <!-- Incident Type -->
                                <div>
                                    <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                                    <select v-model="form.type" id="type"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.type }">
                                        <option value="" disabled>Select type</option>
                                        <option v-for="option in typeOptions" :value="option" :key="option">
                                            {{ option.replace('_', ' ') }}
                                        </option>
                                    </select>
                                    <p v-if="errors.type" class="mt-1 text-sm text-red-600">{{ errors.type }}</p>
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                    <select v-model="form.status" id="status"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.status }">
                                        <option value="" disabled>Select status</option>
                                        <option v-for="option in statusOptions" :value="option" :key="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                    <p v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status }}</p>
                                </div>

                                <!-- Area/Location -->
                                <div class="md:col-span-2">
                                    <label for="area" class="block text-sm font-medium text-gray-700">Area/Location
                                        *</label>
                                    <input v-model="form.area" type="text" id="area"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                        :class="{ 'border-red-300': errors.area }">
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
                                                url: getStatusMarkerIcon(form.status),
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
                                                Click on the map or drag the marker to adjust location
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label for="lat"
                                                    class="block text-sm font-medium text-gray-700">Latitude *</label>
                                                <input v-model="form.lat" type="number" step="any" id="lat"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                    :class="{ 'border-red-300': errors.lat }">
                                                <p v-if="errors.lat" class="mt-1 text-sm text-red-600">{{ errors.lat }}
                                                </p>
                                            </div>
                                            <div>
                                                <label for="lng"
                                                    class="block text-sm font-medium text-gray-700">Longitude *</label>
                                                <input v-model="form.lng" type="number" step="any" id="lng"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                                                    :class="{ 'border-red-300': errors.lng }">
                                                <p v-if="errors.lng" class="mt-1 text-sm text-red-600">{{ errors.lng }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 p-3 rounded-md text-sm text-gray-600">
                                            <i class="fas fa-info-circle mr-1 text-red-600"></i>
                                            For precise location, you can search coordinates on
                                            <a href="https://www.latlong.net/" target="_blank"
                                                class="text-red-600 hover:underline">latlong.net</a>
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
                                    :class="{ 'border-red-300': errors.details }"></textarea>
                                <p v-if="errors.details" class="mt-1 text-sm text-red-600">{{ errors.details }}</p>
                            </div>
                        </div>

                        <!-- Evidence Section -->
                        <div v-if="incident.media && incident.media.length > 0">
                            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Current
                                Evidence</h2>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div v-for="media in incident.media" :key="media.id" class="relative group">
                                    <img :src="media.original_url" alt="Incident evidence"
                                        class="rounded-md h-24 w-full object-cover">
                                    <button @click.prevent="deleteMedia(media.id)"
                                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- New Evidence Section -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">Add New
                                Evidence</h2>
                            <div
                                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <div class="flex text-sm text-gray-600">
                                        <label for="file-upload"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-red-600 hover:text-red-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-red-500">
                                            <span>Upload additional files</span>
                                            <input id="file-upload" name="file-upload" type="file" class="sr-only"
                                                multiple @change="handleFileUpload">
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
                            Reset Changes
                        </button>
                        <button type="submit" :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-75">
                            <i v-if="form.processing" class="fas fa-circle-notch fa-spin mr-2"></i>
                            <i v-else class="fas fa-save mr-2"></i>
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </DashLayout>
</template>

<script setup>
import { reactive, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { GoogleMap, Marker } from 'vue3-google-map';
import DashLayout from '@/Layouts/DashLayout.vue';

const props = defineProps({
    incident: Object,
    errors: Object,
    typeOptions: Array,
    statusOptions: Array,
});

const mapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

const form = reactive({
    name: props.incident.name,
    type: props.incident.type,
    status: props.incident.status,
    area: props.incident.area,
    details: props.incident.details,
    lat: props.incident.lat,
    lng: props.incident.lng,
    media: [],
    processing: false,
});

const mapCenter = computed(() => ({
    lat: parseFloat(form.lat) || -15.434393,
    lng: parseFloat(form.lng) || 28.308563
}));

const getStatusMarkerIcon = (status) => {
    const color = {
        reported: 'red',
        investigating: 'orange',
        resolved: 'green',
        closed: 'gray'
    }[status] || 'red';

    return `https://maps.google.com/mapfiles/ms/icons/${color}-dot.png`;
};

const handleMapClick = (e) => {
    form.lat = e.latLng.lat();
    form.lng = e.latLng.lng();
};

const handleMarkerDrag = (e) => {
    form.lat = e.latLng.lat();
    form.lng = e.latLng.lng();
};

const handleFileUpload = (event) => {
    form.media = event.target.files;
};

const deleteMedia = (mediaId) => {
    if (confirm('Are you sure you want to delete this media?')) {
        router.delete(route('incidents.media.destroy', [props.incident.id, mediaId]), {
            preserveScroll: true,
        });
    }
};

const submit = () => {
    const formData = new FormData();

    // Append all form fields
    Object.keys(form).forEach(key => {
        if (key !== 'processing' && key !== 'media') {
            formData.append(key, form[key]);
        }
    });

    // Append files if any
    if (form.media && form.media.length > 0) {
        Array.from(form.media).forEach(file => {
            formData.append('media[]', file);
        });
    }

    router.post(route('incidents.update', props.incident.id), {
        _method: 'PUT',
        ...Object.fromEntries(formData)
    }, {
        onStart: () => form.processing = true,
        onFinish: () => form.processing = false,
        preserveScroll: true,
    });
};

const reset = () => {
    form.name = props.incident.name;
    form.type = props.incident.type;
    form.status = props.incident.status;
    form.area = props.incident.area;
    form.details = props.incident.details;
    form.lat = props.incident.lat;
    form.lng = props.incident.lng;
    form.media = [];
};
</script>
