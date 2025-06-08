<template>
    <DashLayout>

        <Head title="Incident Reports" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Incident Management</h1>
                    <p class="mt-1 text-sm text-gray-600">Track and manage all reported incidents</p>
                </div>
                <Link :href="route('incidents.create')"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i> Report Incident
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input v-model="filters.search" type="text" placeholder="Search incidents..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                            @input="debouncedSearch">
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select v-model="filters.status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                            @change="search">
                            <option :value="null">All Statuses</option>
                            <option v-for="option in statusOptions" :value="option" :key="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>

                    <!-- Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select v-model="filters.type"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                            @change="search">
                            <option :value="null">All Types</option>
                            <option v-for="option in typeOptions" :value="option" :key="option">
                                {{ option }}
                            </option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <div class="flex space-x-2">
                            <input v-model="filters.date_from" type="date"
                                class="w-1/2 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                            <input v-model="filters.date_to" type="date"
                                class="w-1/2 rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <StatCard title="Total Incidents" :value="stats?.total" icon="triangle-exclamation" color="gray" />
                <StatCard title="Reported" :value="stats.reported" icon="flag" color="yellow" />
                <StatCard title="Investigating" :value="stats.investigating" icon="magnifying-glass" color="blue" />
                <StatCard title="Resolved" :value="stats.resolved" icon="check-circle" color="green" />
            </div>

            <!-- Map and Table -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Map View -->
                <div class="lg:col-span-1 bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Incident Locations</h3>
                    </div>
                    <div class="h-96">
                        <GoogleMap :api-key="mapsApiKey" style="width: 100%; height: 100%" :center="mapCenter"
                            :zoom="12">
                            <Marker v-for="incident in incidents.data" :key="incident.id"
                                :options="{ position: { lat: parseFloat(incident.lat), lng: parseFloat(incident.lng) }, icon: getMarkerIcon(incident.status) }">
                                <InfoWindow>
                                    <div class="text-sm">
                                        <div class="font-medium">{{ incident.name }}</div>
                                        <div class="text-gray-600">{{ incident.type }}</div>
                                        <div class="text-xs mt-1">{{ incident.area }}</div>
                                    </div>
                                </InfoWindow>
                            </Marker>
                        </GoogleMap>
                    </div>
                </div>

                <!-- Table -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Incident
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type/Area
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Reported
                                    </th>
                                    <th scope="col"
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-if="incidents.data.length === 0">
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        No incidents found
                                    </td>
                                </tr>

                                <tr v-for="incident in incidents.data" :key="incident.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div
                                                class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                                <i :class="`fas fa-${getIncidentIcon(incident.type)} text-red-600`"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ incident.name }}</div>
                                                <div class="text-sm text-gray-500 truncate max-w-xs">{{ incident.details
                                                    }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 capitalize">{{ incident.type }}</div>
                                        <div class="text-sm text-gray-500">{{ incident.area }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select v-model="incident.status" @change="updateStatus(incident)"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                                            :class="getStatusClasses(incident.status)">
                                            <option v-for="option in statusOptions" :value="option" :key="option">
                                                {{ option }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ formatDate(incident.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link :href="route('incidents.show', incident.id)"
                                            class="text-red-600 hover:text-red-900 mr-3" title="View">
                                        <i class="fas fa-eye"></i>
                                        </Link>
                                        <Link :href="route('incidents.edit', incident.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-3" title="Edit">
                                        <i class="fas fa-edit"></i>
                                        </Link>
                                        <button @click="confirmDelete(incident)"
                                            class="text-gray-600 hover:text-gray-900" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 flex flex-col sm:flex-row items-center justify-between">
                        <div class="hidden sm:block text-sm text-gray-700 mb-3 sm:mb-0">
                            Showing <span class="font-medium">{{ incidents.from }}</span> to <span
                                class="font-medium">{{ incidents.to }}</span> of <span class="font-medium">{{
                                incidents.total }}</span> incidents
                        </div>
                        <div class="flex-1 flex justify-between sm:justify-end space-x-3">
                            <button @click="previousPage" :disabled="!incidents.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                Previous
                            </button>
                            <button @click="nextPage" :disabled="!incidents.next_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                                Next
                            </button>
                        </div>
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
            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                <DangerButton class="ms-3" @click="deleteIncident">Delete Incident</DangerButton>
            </template>
        </ConfirmationModal>
    </DashLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { GoogleMap, Marker, InfoWindow } from 'vue3-google-map';
import DashLayout from '@/Layouts/DashLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import StatCard from '@/Components/StatCard.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    incidents: Object,
    filters: Object,
    statusOptions: Array,
    typeOptions: Array,
    stats: Object
});

const filters = ref({
    search: props.filters.search || null,
    status: props.filters.status || null,
    type: props.filters.type || null,
    date_from: props.filters.date_from || null,
    date_to: props.filters.date_to || null,
});

const showDeleteModal = ref(false);
const incidentToDelete = ref(null);
const mapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

// Calculate map center based on incidents
const mapCenter = computed(() => {
    if (props.incidents.data.length === 0) {
        return { lat: 0, lng: 0 }; // Default center
    }

    const firstIncident = props.incidents.data[0];
    return {
        lat: parseFloat(firstIncident.lat),
        lng: parseFloat(firstIncident.lng)
    };
});

// Get appropriate icon for incident type
const getIncidentIcon = (type) => {
    const icons = {
        accident: 'car-crash',
        crime: 'handcuffs',
        natural_disaster: 'tornado',
        health_emergency: 'heart-pulse',
        other: 'triangle-exclamation'
    };
    return icons[type] || 'triangle-exclamation';
};

// Get marker icon based on status
const getMarkerIcon = (status) => {
    const color = {
        reported: 'red',
        investigating: 'orange',
        resolved: 'green',
        closed: 'gray'
    }[status] || 'red';

    return {
        url: `https://maps.google.com/mapfiles/ms/icons/${color}-dot.png`,
        scaledSize: { width: 32, height: 32 }
    };
};

// Get CSS classes for status
const getStatusClasses = (status) => {
    return {
        'bg-red-100 text-red-800': status === 'reported',
        'bg-yellow-100 text-yellow-800': status === 'investigating',
        'bg-blue-100 text-blue-800': status === 'resolved',
        'bg-gray-100 text-gray-800': status === 'closed'
    };
};

// Format date
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Debounced search
let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(search, 500);
};

const search = () => {
    router.get(route('incidents.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

// Pagination
const nextPage = () => {
    if (props.incidents.next_page_url) {
        router.get(props.incidents.next_page_url, {}, {
            preserveState: true,
        });
    }
};

const previousPage = () => {
    if (props.incidents.prev_page_url) {
        router.get(props.incidents.prev_page_url, {}, {
            preserveState: true,
        });
    }
};

// Status update
const updateStatus = (incident) => {
    router.post(route('incidents.update-status', incident.id), {
        status: incident.status
    }, {
        preserveScroll: true,
    });
};

// Delete confirmation
const confirmDelete = (incident) => {
    incidentToDelete.value = incident;
    showDeleteModal.value = true;
};

const deleteIncident = () => {
    router.delete(route('incidents.destroy', incidentToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

// Watch for filter changes
watch(() => filters.value, () => {
    search();
}, { deep: true });
</script>
