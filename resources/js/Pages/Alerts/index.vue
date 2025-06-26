<template>
    <DashLayout>

        <Head title="Alerts" />
        <nav class="text-sm font-semibold mb-6" aria-label="Breadcrumb">
            <ol class="list-none p-0 inline-flex">
                <li class="flex items-center text-blue-500">
                    <a href="#" class="text-gray-700">Home</a>
                    <svg class="fill-current w-3 h-3 mx-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                        <path
                            d="M285.476 272.971L91.132 467.314c-9.373 9.373-24.569 9.373-33.941 0l-22.667-22.667c-9.357-9.357-9.375-24.522-.04-33.901L188.505 256 34.484 101.255c-9.335-9.379-9.317-24.544.04-33.901l22.667-22.667c9.373-9.373 24.569-9.373 33.941 0L285.475 239.03c9.373 9.372 9.373 24.568.001 33.941z" />
                    </svg>
                </li>
                <li class="flex items-center">
                    <a href="#" class="text-gray-600">Alerts</a>
                </li>
            </ol>
        </nav>

        <div class="bg-white rounded-lg shadow overflow-hidden container mx-auto">
            <!-- Table Header with Filters -->
            <div
                class="p-4  border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
                <h2 class="text-lg font-medium text-gray-800">Alerts Management</h2>

                <div class="max-w-2xl flex space-x-4 w-full sm:w-auto">
                    <!-- Status Filter -->
                    <select v-model="statusFilter"
                        class="block w-full sm:w-40 rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="active">Active</option>
                        <option value="resolved">Resolved</option>
                    </select>

                    <!-- Search Input -->
                    <input v-model="searchQuery" type="text" placeholder="Search alerts..."
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th v-for="header in headers" :key="header.key" scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                                :class="{ 'hover:text-teal-600': header.sortable }"
                                @click="header.sortable ? sortBy(header.key) : null">
                                <div class="flex items-center">
                                    {{ header.label }}
                                    <span v-if="sortColumn === header.key" class="ml-1">
                                        <i
                                            :class="sortDirection === 'asc' ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                                    </span>
                                </div>
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="alerts.loading">
                            <td :colspan="headers.length + 1" class="px-6 py-4 text-center text-gray-500">
                                <i class="fas fa-circle-notch fa-spin mr-2"></i> Loading alerts...
                            </td>
                        </tr>

                        <tr v-else-if="alerts.data.length === 0">
                            <td :colspan="headers.length + 1" class="px-6 py-4 text-center text-gray-500">
                                No alerts found
                            </td>
                        </tr>

                        <tr v-for="alert in alerts.data" :key="alert.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ alert.name }}</div>
                                <div class="text-sm text-gray-500">{{ alert.email }}</div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="{
                                'bg-yellow-100 text-yellow-800': alert.status === 'pending',
                                'bg-red-100 text-red-800': alert.status === 'active',
                                'bg-green-100 text-green-800': alert.status === 'resolved'
                            }">
                                    {{ alert.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(alert.initiated_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <a :href="`https://maps.google.com/?q=${alert.lat},${alert.lng}`" target="_blank"
                                    class="text-teal-600 hover:text-teal-900 hover:underline">
                                    View Map
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="editAlert(alert)" class="text-teal-600 hover:text-teal-900 mr-3">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button @click="confirmDelete(alert)" class="text-red-600 hover:text-red-900">
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
                    Showing <span class="font-medium">{{ alerts.from }}</span> to <span class="font-medium">{{ alerts.to
                        }}</span> of <span class="font-medium">{{ alerts.total }}</span> alerts
                </div>
                <div class="flex-1 flex justify-between sm:justify-end space-x-3">
                    <button @click="previousPage" :disabled="!alerts.prev_page_url"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <button @click="nextPage" :disabled="!alerts.next_page_url"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>

            <!-- Edit Alert Modal -->
            <Modal :show="showEditModal" @close="showEditModal = false">
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Alert</h2>

                    <form @submit.prevent="updateAlert">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <select v-model="editingAlert.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                                    <option value="pending">Pending</option>
                                    <option value="active">Active</option>
                                    <option value="resolved">Resolved</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Message</label>
                                <textarea v-model="editingAlert.message" rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"></textarea>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="showEditModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </Modal>

            <!-- Delete Confirmation Modal -->
            <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false" @confirm="deleteAlert">
                <template #title>
                    Delete Alert
                </template>
                <template #content>
                    Are you sure you want to delete this alert? This action cannot be undone.
                </template>
                <template #confirmButton>
                    Delete Alert
                </template>
            </ConfirmationModal>
        </div>
    </DashLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch, } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DashLayout from '@/Layouts/DashLayout.vue';

const props = defineProps({
    initialAlerts: {
        type: Object,
        default: () => ({ data: [], loading: true })
    },
    filters: {
        type: Object,
        default: () => ({ status: '', search: '' })
    }
});

const headers = [
    { label: 'User', key: 'name', sortable: true },
    { label: 'Status', key: 'status', sortable: true },
    { label: 'Initiated', key: 'initiated_at', sortable: true },
    { label: 'Location', key: 'lat', sortable: false }
];

const alerts = ref({ ...props.initialAlerts, loading: false });
const statusFilter = ref(props.filters.status);
const searchQuery = ref(props.filters.search);
const sortColumn = ref('initiated_at');
const sortDirection = ref('desc');
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingAlert = ref(null);
const alertToDelete = ref(null);

// Fetch alerts when filters/sorting change
const fetchAlerts = async () => {
    alerts.value.loading = true;
    try {
        router.get('alerts', {
            status: statusFilter.value,
            search: searchQuery.value,
            sort: sortColumn.value,
            direction: sortDirection.value,
            page: alerts.value.current_page
        }, {
            preserveState: true,
            replace: true,
            onSuccess: (response) => {
                alerts.value = response.props.alerts;
            },
            onError: (error) => {
                console.error('Error fetching alerts:', error);
            }
        });

    } finally {
        alerts.value.loading = false;
    }
};

// Debounced search
let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fetchAlerts, 500);
};

// Sorting
const sortBy = (column) => {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortColumn.value = column;
        sortDirection.value = 'asc';
    }
    fetchAlerts();
};

// Pagination
const nextPage = () => {
    if (alerts.value.next_page_url) {
        alerts.value.current_page++;
        fetchAlerts();
    }
};

const previousPage = () => {
    if (alerts.value.prev_page_url) {
        alerts.value.current_page--;
        fetchAlerts();
    }
};

// Alert actions
const editAlert = (alert) => {
    editingAlert.value = { ...alert };
    showEditModal.value = true;
};

const updateAlert = async () => {
    try {
        await router.put(route('alerts.update', editingAlert.value.id), {
            status: editingAlert.value.status,
            message: editingAlert.value.message
        });
        showEditModal.value = false;
        fetchAlerts();
    } catch (error) {
        console.error('Error updating alert:', error);
    }
};

const confirmDelete = (alert) => {
    alertToDelete.value = alert;
    showDeleteModal.value = true;
};

const deleteAlert = async () => {
    try {
        await router.delete(route('alerts.destroy', alertToDelete.value.id));
        showDeleteModal.value = false;
        fetchAlerts();
    } catch (error) {
        console.error('Error deleting alert:', error);
    }
};

// Format date for display
const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Watch for filter changes
watch([statusFilter, searchQuery], () => {
    alerts.value.current_page = 1;
    debouncedSearch();
});

// Initialize
onMounted(() => {
    if (alerts.value.data.length === 0) {
        fetchAlerts();
    }
});
</script>
