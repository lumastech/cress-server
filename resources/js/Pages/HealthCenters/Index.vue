<template>
    <DashLayout>
        <!-- breadcrumb -->

        <Head title="Health Centers" />
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
                    <a href="#" class="text-gray-600">Centers</a>
                </li>
            </ol>
        </nav>

        <!-- Header Section -->
        <div class="mb-8 container mx-auto">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <h1 class="text-2xl font-bold text-gray-800">Health Centers Management</h1>
                <Link :href="route('health-centers.create')"
                    class="inline-flex items-center px-4 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-700 focus:bg-teal-700 active:bg-teal-900 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <i class="fas fa-plus mr-2"></i> Add New Center
                </Link>
            </div>
            <p class="mt-1 text-sm text-gray-600">Manage all registered health facilities in the system</p>
        </div>

        <!-- Filters -->
        <div class="container mx-auto bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input v-model="filters.search" type="text" placeholder="Search by name, email or phone..."
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm"
                        @input="debouncedSearch">
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select v-model="filters.status"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm"
                        @change="search">
                        <option :value="null">All Statuses</option>
                        <option v-for="option in statusOptions" :value="option" :key="option">
                            {{ option }}
                        </option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select v-model="filters.type"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm"
                        @change="search">
                        <option :value="null">All Types</option>
                        <option v-for="option in typeOptions" :value="option" :key="option">
                            {{ option }}
                        </option>
                    </select>
                </div>

                <!-- Verification Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Verification</label>
                    <select v-model="filters.verified"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm"
                        @change="search">
                        <option :value="null">All</option>
                        <option value="true">Verified Only</option>
                        <option value="false">Unverified Only</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="container mx-auto bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Name
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contact
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Verification
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="centers.data.length === 0">
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No health centers found
                            </td>
                        </tr>

                        <tr v-for="center in centers.data" :key="center.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-10 w-10 rounded-full bg-teal-100 flex items-center justify-center">
                                        <i class="fas fa-hospital text-teal-600"></i>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ center.name }}</div>
                                        <div class="text-sm text-gray-500">{{ center.address }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ center.email }}</div>
                                <div class="text-sm text-gray-500">{{ center.phone }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ center.type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="{
                                    'bg-green-100 text-green-800': center.status === 'active',
                                    'bg-yellow-100 text-yellow-800': center.status === 'pending',
                                    'bg-red-100 text-red-800': center.status === 'inactive'
                                }">
                                    {{ center.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button @click="toggleVerification(center)"
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer"
                                    :class="{
                                        'bg-green-100 text-green-800': center.is_verified,
                                        'bg-gray-100 text-gray-800': !center.is_verified
                                    }">
                                    {{ center.is_verified ? 'Verified' : 'Unverified' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <Link :href="route('health-centers.show', center.id)"
                                    class="text-teal-600 hover:text-teal-900 mr-3" title="View">
                                <i class="fas fa-eye"></i>
                                </Link>
                                <Link v-if="$page.props.user.role == 'admin'" :href="route('health-centers.edit', center.id)"
                                    class="text-blue-600 hover:text-blue-900 mr-3" title="Edit">
                                <i class="fas fa-edit"></i>
                                </Link>
                                <button v-if="$page.props.user.role == 'admin'" @click="confirmDelete(center)" class="text-red-600 hover:text-red-900"
                                    title="Delete">
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
                    Showing <span class="font-medium">{{ centers.from }}</span> to <span class="font-medium">{{
                        centers.to }}</span> of <span class="font-medium">{{ centers.total }}</span> centers
                </div>
                <div class="flex-1 flex justify-between sm:justify-end space-x-3">
                    <button @click="previousPage" :disabled="!centers.prev_page_url"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <button @click="nextPage" :disabled="!centers.next_page_url"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false" @confirm="deleteCenter">
            <template #title>
                Delete Health Center
            </template>
            <template #content>
                Are you sure you want to delete this health center? This action cannot be undone.
            </template>
            <template #footer>
                <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                <DangerButton class="ms-3" @click="deleteCenter">Delete Center</DangerButton>
            </template>
        </ConfirmationModal>
    </DashLayout>

</template>

<script setup>
import DashLayout from '@/Layouts/DashLayout.vue';
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    centers: Object,
    filters: Object,
    statusOptions: Array,
    typeOptions: Array,
});

const filters = ref({
    search: props.filters.search || null,
    status: props.filters.status || null,
    type: props.filters.type || null,
    verified: props.filters.verified || null,
});

const showDeleteModal = ref(false);
const centerToDelete = ref(null);

// Debounced search
let searchTimeout = null;
const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(search, 500);
};

const search = () => {
    router.get(route('health-centers.index'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

// Pagination
const nextPage = () => {
    if (props.centers.next_page_url) {
        router.get(props.centers.next_page_url, {}, {
            preserveState: true,
        });
    }
};

const previousPage = () => {
    if (props.centers.prev_page_url) {
        router.get(props.centers.prev_page_url, {}, {
            preserveState: true,
        });
    }
};

// Verification toggle
const toggleVerification = (center) => {
    router.post(route('health-centers.toggle-verification', center.id), {}, {
        preserveState: true,
        onSuccess: () => {
            // Optional: Show toast notification
        },
    });
};

// Delete confirmation
const confirmDelete = (center) => {
    centerToDelete.value = center;
    showDeleteModal.value = true;
};

const deleteCenter = () => {
    router.delete(route('health-centers.destroy', centerToDelete.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            // Optional: Show toast notification
        },
    });
};

// Watch for filter changes
watch(() => filters.value, () => {
    search();
}, { deep: true });
</script>
