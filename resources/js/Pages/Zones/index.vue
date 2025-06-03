<template>
    <DashLayout>

        <Head title="Danger Zones" />

        <div class="flex flex-col h-full">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-800">Danger Zones</h1>
                <p class="mt-1 text-sm text-gray-600">Visualize high-risk areas based on recent activity</p>
            </div>

            <!-- Controls -->
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <select v-model="timeRange"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm">
                            <option value="24h">Last 24 Hours</option>
                            <option value="7d">Last 7 Days</option>
                            <option value="30d">Last 30 Days</option>
                            <option value="all">All Time</option>
                        </select>
                    </div>

                    <!-- Data Layers -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heatmap Layers</label>
                        <div class="flex space-x-4">
                            <label class="inline-flex items-center">
                                <input v-model="layers.alerts" type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                                <span class="ml-2 text-sm text-gray-700">Alerts</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input v-model="layers.incidents" type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700">Incidents</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input v-model="layers.combined" type="checkbox"
                                    class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                                <span class="ml-2 text-sm text-gray-700">Combined</span>
                            </label>
                        </div>
                    </div>

                    <!-- Intensity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Heatmap Intensity</label>
                        <input v-model="intensity" type="range" min="1" max="10"
                            class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>
                </div>
            </div>

            <!-- Map Container -->
            <div class="flex-1 bg-white rounded-lg shadow overflow-hidden">
                <div class="h-full w-full relative">
                    <!-- Loading State -->
                    <div v-if="loading"
                        class="absolute inset-0 bg-white bg-opacity-70 flex items-center justify-center z-10">
                        <div class="text-center">
                            <i class="fas fa-circle-notch fa-spin text-3xl text-red-600 mb-2"></i>
                            <p class="text-sm text-gray-600">Loading danger zone data...</p>
                        </div>
                    </div>

                    <!-- Google Map -->
                    <GoogleMap :api-key="mapsApiKey" style="width: 100%; height: 100%" :center="mapCenter" :zoom="12"
                        @loaded="onMapLoaded">
                        <!-- Heatmap Layer -->
                        <HeatmapLayer v-if="heatmapData.length > 0" :data="heatmapData" :options="heatmapOptions" />

                        <!-- Cluster Markers for Individual Points -->
                        <MarkerCluster>
                            <Marker v-for="(alert, index) in filteredAlerts" v-if="layers.alerts && showPoints"
                                :key="'alert-' + index" :options="{
                                    position: { lat: parseFloat(alert.lat), lng: parseFloat(alert.lng) },
                                    icon: {
                                        url: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png',
                                        scaledSize: { width: 24, height: 24 }
                                    }
                                }" />
                            <Marker v-for="(incident, index) in filteredIncidents" v-if="layers.incidents && showPoints"
                                :key="'incident-' + index" :options="{
                                    position: { lat: parseFloat(incident.lat), lng: parseFloat(incident.lng) },
                                    icon: {
                                        url: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png',
                                        scaledSize: { width: 24, height: 24 }
                                    }
                                }" />
                        </MarkerCluster>

                        <!-- Legend -->
                        <div class="absolute bottom-4 left-4 bg-white p-3 rounded-md shadow-md z-10">
                            <div class="flex items-center mb-2" v-if="layers.alerts">
                                <div class="w-4 h-4 bg-red-500 rounded-full mr-2"></div>
                                <span class="text-xs">Alerts</span>
                            </div>
                            <div class="flex items-center mb-2" v-if="layers.incidents">
                                <div class="w-4 h-4 bg-blue-500 rounded-full mr-2"></div>
                                <span class="text-xs">Incidents</span>
                            </div>
                            <div class="flex items-center" v-if="layers.combined">
                                <div class="w-4 h-4 bg-purple-500 rounded-full mr-2"></div>
                                <span class="text-xs">Combined Hotspots</span>
                            </div>
                        </div>
                    </GoogleMap>
                </div>
            </div>

            <!-- Toggle Points Visibility -->
            <div class="mt-4 flex justify-end">
                <button @click="showPoints = !showPoints"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    {{ showPoints ? 'Hide Individual Points' : 'Show Individual Points' }}
                </button>
            </div>
        </div>
    </DashLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import { GoogleMap, Marker, HeatmapLayer, MarkerCluster } from 'vue3-google-map';
import DashLayout from '@/Layouts/DashLayout.vue';

const props = defineProps({
    initialAlerts: Array,
    initialIncidents: Array,
    defaultCenter: Object,
});

const mapsApiKey = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

// State
const loading = ref(false);
const timeRange = ref('7d');
const intensity = ref(5);
const showPoints = ref(true);
const mapCenter = ref(props.defaultCenter || { lat: 0, lng: 0 });
const mapReady = ref(false);

// Layer visibility
const layers = ref({
    alerts: true,
    incidents: false,
    combined: true
});

// Data
const alerts = ref(props.initialAlerts);
const incidents = ref(props.initialIncidents);

// Filter data based on time range
const filteredAlerts = computed(() => {
    const cutoff = getCutoffDate(timeRange.value);
    return alerts.value.filter(alert => new Date(alert.created_at) >= cutoff);
});

const filteredIncidents = computed(() => {
    const cutoff = getCutoffDate(timeRange.value);
    return incidents.value.filter(incident => new Date(incident.created_at) >= cutoff);
});

// Heatmap data
const heatmapData = computed(() => {
    const data = [];

    if (layers.value.alerts) {
        filteredAlerts.value.forEach(alert => {
            data.push({
                location: new google.maps.LatLng(parseFloat(alert.lat), parseFloat(alert.lng)),
                weight: 1
            });
        });
    }

    if (layers.value.incidents) {
        filteredIncidents.value.forEach(incident => {
            data.push({
                location: new google.maps.LatLng(parseFloat(incident.lat), parseFloat(incident.lng)),
                weight: 2 // Incidents might be weighted heavier than alerts
            });
        });
    }

    return data;
});

// Heatmap options
const heatmapOptions = computed(() => ({
    radius: intensity.value * 5,
    opacity: 0.6,
    gradient: [
        'rgba(0, 255, 255, 0)',
        'rgba(0, 255, 255, 1)',
        'rgba(0, 191, 255, 1)',
        'rgba(0, 127, 255, 1)',
        'rgba(0, 63, 255, 1)',
        'rgba(0, 0, 255, 1)',
        'rgba(0, 0, 223, 1)',
        'rgba(0, 0, 191, 1)',
        'rgba(0, 0, 159, 1)',
        'rgba(0, 0, 127, 1)',
        'rgba(63, 0, 91, 1)',
        'rgba(127, 0, 63, 1)',
        'rgba(191, 0, 31, 1)',
        'rgba(255, 0, 0, 1)'
    ]
}));

// Helper functions
function getCutoffDate(range) {
    const now = new Date();
    switch (range) {
        case '24h': return new Date(now.setDate(now.getDate() - 1));
        case '7d': return new Date(now.setDate(now.getDate() - 7));
        case '30d': return new Date(now.setDate(now.getDate() - 30));
        default: return new Date(0); // All time
    }
}

function onMapLoaded(map) {
    mapReady.value = true;
    // Auto-center map based on data if needed
    if (heatmapData.value.length > 0) {
        const bounds = new google.maps.LatLngBounds();
        heatmapData.value.forEach(point => bounds.extend(point.location));
        map.fitBounds(bounds);
    }
}

// Watch for changes to refresh data
watch([timeRange, layers], async () => {
    loading.value = true;
    try {
        // In a real app, you might fetch filtered data from the server
        // const response = await fetchData(timeRange.value);
        // alerts.value = response.alerts;
        // incidents.value = response.incidents;
    } finally {
        loading.value = false;
    }
}, { deep: true });
</script>
