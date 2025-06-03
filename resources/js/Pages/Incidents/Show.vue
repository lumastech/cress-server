<template>
    <DashLayout>

        <Head :title="incident.name" />

        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ incident.name }}</h1>
                        <div class="flex items-center mt-2 space-x-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="statusClasses(incident.status)">
                                {{ incident.status }}
                            </span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                                {{ incident.type }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Reported {{ formatDate(incident.created_at) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <Link :href="route('incidents.index')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                        </Link>
                        <Link :href="route('incidents.edit', incident.id)"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-edit mr-2"></i> Edit
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Details Section -->
                <div class="p-6 space-y-6">
                    <!-- Location and Map -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Location Details -->
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Location Details</h2>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-gray-400 mt-0.5 mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Area</p>
                                        <p class="text-sm text-gray-600">{{ incident.area }}</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <i class="fas fa-map-pin text-gray-400 mt-0.5 mr-3"></i>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Coordinates</p>
                                        <p class="text-sm text-gray-600">
                                            {{ incident.lat }}, {{ incident.lng }}
                                        </p>
                                    </div>
                                </div>
                                <a :href="`https://maps.google.com/?q=${incident.lat},${incident.lng}`" target="_blank"
                                    class="inline-flex items-center text-sm text-red-600 hover:text-red-800">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    View on Google Maps
                                </a>
                            </div>
                        </div>

                        <!-- Map -->
                        <div class="h-64 md:h-full rounded-md overflow-hidden border border-gray-200">
                            <GoogleMap :api-key="mapsApiKey" style="width: 100%; height: 100%"
                                :center="{ lat: parseFloat(incident.lat), lng: parseFloat(incident.lng) }" :zoom="15">
                                <Marker :options="{
                                    position: { lat: parseFloat(incident.lat), lng: parseFloat(incident.lng) },
                                    icon: {
                                        url: statusMarkerIcon(incident.status),
                                        scaledSize: { width: 32, height: 32 }
                                    }
                                }">
                                    <InfoWindow>
                                        <div class="text-sm">
                                            <div class="font-medium">{{ incident.name }}</div>
                                            <div class="text-gray-600 capitalize">{{ incident.type }}</div>
                                        </div>
                                    </InfoWindow>
                                </Marker>
                            </GoogleMap>
                        </div>
                    </div>

                    <!-- Incident Details -->
                    <div>
                        <h2 class="text-lg font-medium text-gray-900 mb-2">Incident Details</h2>
                        <div class="prose prose-sm max-w-none text-gray-600 whitespace-pre-line">
                            {{ incident.details }}
                        </div>
                    </div>

                    <!-- Status Management -->
                    <div class="border-t border-gray-200 pt-4">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Status Management</h2>
                        <div class="flex flex-wrap gap-3">
                            <button v-for="status in statusOptions" :key="status" @click="updateStatus(status)"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-colors" :class="status === incident.status
                                    ? 'bg-red-100 text-red-800 border border-red-300'
                                    : 'bg-gray-100 text-gray-800 hover:bg-gray-200'">
                                {{ status }}
                            </button>
                        </div>
                    </div>

                    <!-- Evidence -->
                    <div v-if="incident.media && incident.media.length > 0">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Evidence</h2>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div v-for="media in incident.media" :key="media.id"
                                class="relative group rounded-md overflow-hidden border border-gray-200">
                                <a :href="media.original_url" target="_blank"
                                    class="block h-40 bg-gray-100 flex items-center justify-center">
                                    <img v-if="isImage(media.mime_type)" :src="media.original_url"
                                        :alt="`Incident evidence ${media.id}`" class="h-full w-full object-cover">
                                    <div v-else class="text-center p-4">
                                        <i class="fas fa-file text-4xl text-gray-400 mb-2"></i>
                                        <p class="text-xs text-gray-500 truncate">{{ media.file_name }}</p>
                                    </div>
                                </a>
                                <button @click="deleteMedia(media.id)"
                                    class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Reporter Info -->
                    <div class="border-t border-gray-200 pt-4">
                        <h2 class="text-lg font-medium text-gray-900 mb-2">Reporter Information</h2>
                        <div class="flex items-center space-x-4">
                            <div
                                class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-user text-gray-500"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ incident.user?.name || 'Anonymous' }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Reported {{ formatTimeAgo(incident.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Last updated {{ formatTimeAgo(incident.updated_at) }}
                    </div>
                    <div class="flex space-x-3">
                        <button @click="confirmDelete" class="text-sm font-medium text-red-600 hover:text-red-900">
                            <i class="fas fa-trash mr-1"></i> Delete Incident
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false" @confirm="deleteIncident">
            <template #title>
                Delete Incident Report
            </template>
            <template #content>
                Are you sure you want to delete this incident report? This action cannot be undone.
            </template>
            <template #confirmButton>
                Delete Incident
            </template>
        </ConfirmationModal>
    </DashLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { GoogleMap, Marker, InfoWindow } from 'vue3-google-map';
import DashLayout from '@/Layouts/DashLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

const props = defineProps({
    incident: Object,
    statusOptions: Array,
});

const showDeleteModal = ref(false);
const mapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

const statusClasses = (status) => {
    return {
        'bg-red-100 text-red-800': status === 'reported',
        'bg-yellow-100 text-yellow-800': status === 'investigating',
        'bg-blue-100 text-blue-800': status === 'resolved',
        'bg-gray-100 text-gray-800': status === 'closed'
    };
};

const statusMarkerIcon = (status) => {
    const color = {
        reported: 'red',
        investigating: 'orange',
        resolved: 'green',
        closed: 'gray'
    }[status] || 'red';

    return `https://maps.google.com/mapfiles/ms/icons/${color}-dot.png`;
};

const isImage = (mimeType) => {
    return mimeType.startsWith('image/');
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatTimeAgo = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    return `${Math.floor(diffInSeconds / 86400)} days ago`;
};

const updateStatus = (status) => {
    router.patch(route('incidents.update-status', props.incident.id), { status }, {
        preserveScroll: true,
    });
};

const deleteMedia = (mediaId) => {
    if (confirm('Are you sure you want to delete this evidence?')) {
        router.delete(route('incidents.media.destroy', [props.incident.id, mediaId]), {
            preserveScroll: true,
        });
    }
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteIncident = () => {
    router.delete(route('incidents.destroy', props.incident.id), {
        onSuccess: () => {
            router.visit(route('incidents.index'));
        },
    });
};
</script>
