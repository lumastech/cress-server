<template>
    <DashLayout>

        <Head :title="center.name" />

        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">{{ center.name }}</h1>
                        <div class="flex items-center mt-2 space-x-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-green-100 text-green-800': center.status === 'active',
                                    'bg-yellow-100 text-yellow-800': center.status === 'pending',
                                    'bg-red-100 text-red-800': center.status === 'inactive'
                                }">
                                {{ center.status }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                :class="{
                                    'bg-teal-100 text-teal-800': center.is_verified,
                                    'bg-gray-100 text-gray-800': !center.is_verified
                                }">
                                {{ center.is_verified ? 'Verified' : 'Unverified' }}
                            </span>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ center.type }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <Link :href="route('health-centers.index')"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                        <i class="fas fa-arrow-left mr-2"></i> Back
                        </Link>
                        <Link v-if="$page.props.user.role == 'admin'" :href="route('health-centers.edit', center.id)"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                        <i class="fas fa-edit mr-2"></i> Edit
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <!-- Details Section -->
                <div class="px-6 py-5 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Center Details</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Contact Info -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Contact Information</h3>
                            <div class="space-y-1">
                                <div class="flex items-center">
                                    <i class="fas fa-envelope text-gray-400 w-5 mr-2"></i>
                                    <a :href="`mailto:${center.email}`"
                                        class="text-sm text-gray-600 hover:text-teal-600 hover:underline">
                                        {{ center.email }}
                                    </a>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-phone text-gray-400 w-5 mr-2"></i>
                                    <a :href="`tel:${center.phone}`"
                                        class="text-sm text-gray-600 hover:text-teal-600 hover:underline">
                                        {{ center.phone }}
                                    </a>
                                </div>
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-gray-400 w-5 mr-2 mt-0.5"></i>
                                    <span class="text-sm text-gray-600">{{ center.address }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Location Map -->
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Location</h3>
                            <div class="h-48 rounded-md overflow-hidden border border-gray-200">
                                <GoogleMap :api-key="mapsApiKey" style="width: 100%; height: 100%" :center="mapCenter"
                                    :zoom="15">
                                    <Marker :options="{ position: mapCenter }">
                                        <InfoWindow>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ center.name }}
                                            </div>
                                        </InfoWindow>
                                    </Marker>
                                </GoogleMap>
                            </div>
                            <div class="mt-2 text-right">
                                <a :href="`https://maps.google.com/?q=${center.lat},${center.lng}`" target="_blank"
                                    class="inline-flex items-center text-xs text-gray-500 hover:text-teal-600">
                                    <i class="fas fa-external-link-alt mr-1"></i>
                                    Open in Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Section -->
                <div class="px-6 py-5 border-b border-gray-200" v-if="center.description">
                    <h2 class="text-lg font-medium text-gray-900 mb-2">Description</h2>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ center.description }}</p>
                </div>

                <!-- Meta Information -->
                <div class="px-6 py-4 bg-gray-50 flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Created by {{ center.user?.name || 'system' }} on {{ formatDate(center.created_at) }}
                    </div>
                    <div v-if="$page.props.user.role == 'admin'" class="flex space-x-3">
                        <button @click="toggleVerification" class="text-sm font-medium" :class="{
                            'text-teal-600 hover:text-teal-900': center.is_verified,
                            'text-gray-600 hover:text-gray-900': !center.is_verified
                        }">
                            <i class="fas mr-1" :class="center.is_verified ? 'fa-check-circle' : 'fa-times-circle'"></i>
                            Mark as {{ center.is_verified ? 'Unverified' : 'Verified' }}
                        </button>
                        <button @click="confirmDelete" class="text-sm font-medium text-red-600 hover:text-red-900">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false" @confirm="deleteCenter">
            <template #title>
                Delete Health Center
            </template>
            <template #content>
                Are you sure you want to delete {{ center.name }}? This action cannot be undone.
            </template>
            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                <DangerButton class="ms-3" @click="deleteCenter">Delete Center</DangerButton>
            </template>
        </ConfirmationModal>
    </DashLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { GoogleMap, Marker, InfoWindow } from 'vue3-google-map';
import DashLayout from '@/Layouts/DashLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    center: Object,
});

const showDeleteModal = ref(false);
const mapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY; // Make sure to set this in your .env file

const mapCenter = computed(() => ({
    lat: parseFloat(props.center.lat),
    lng: parseFloat(props.center.lng)
}));

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const toggleVerification = () => {
    router.post(route('health-centers.toggle-verification', props.center.id), {}, {
        preserveScroll: true,
    });
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteCenter = () => {
    router.delete(route('health-centers.destroy', props.center.id), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('health-centers.index'));
        },
    });
};
</script>
