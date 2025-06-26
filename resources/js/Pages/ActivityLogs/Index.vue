<template>
    <DashLayout title="Activity Logs">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Activity Logs
                </h2>
                <div class="flex space-x-2">
                    <select v-model="filters.event"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                        <option value="">All Events</option>
                        <option value="created">Created</option>
                        <option value="updated">Updated</option>
                        <option value="deleted">Deleted</option>
                    </select>

                    <input v-model="filters.search" type="text" placeholder="Search..."
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm">
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="grid grid-cols-6 gap-4">
                <div class="md:col-span-4 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col"
                                            class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Event
                                        </th>
                                        <th scope="col"
                                            class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th scope="col"
                                            class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Subject
                                        </th>
                                        <th scope="col"
                                            class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Causer
                                        </th>
                                        <th scope="col"
                                            class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th scope="col"
                                            class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Details
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="log in logs.data" :key="log.id">
                                        <td class="px-1 py-1 whitespace-nowrap">
                                            <span :class="badgeClass(log.event)">
                                                {{ log.event }}
                                            </span>
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ log.description }}</div>
                                            <div class="text-sm text-gray-500">{{ log.log_name }}</div>
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap">
                                            <div v-if="log.subject" class="text-sm text-gray-900">
                                                {{ log.subject_type }} #{{ log.subject_id }}
                                            </div>
                                            <div v-else class="text-sm text-gray-500">
                                                N/A
                                            </div>
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap">
                                            <div v-if="log.causer" class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <img class="h-10 w-10 rounded-full"
                                                        :src="log.causer.profile_photo_url" alt="">
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ log.causer.name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">{{ log.causer.email }}</div>
                                                </div>
                                            </div>
                                            <div v-else class="text-sm text-gray-500">
                                                System
                                            </div>
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(log.created_at) }}
                                        </td>
                                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                                            <button @click="toggleDetails(log.id)"
                                                class="text-indigo-600 hover:text-indigo-900">
                                                {{ showDetails[log.id] ? 'Hide' : 'Show' }}
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="logs.data.length === 0">
                                        <td colspan="6" class="px-2 py-2 text-center text-sm text-gray-500">
                                            No activity logs found
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <Pagination :links="logs.links" class="mt-4" />
                    </div>
                </div>
                <div class="md:col-span-2">
                    <!-- Details Modal -->
                    <div v-for="log in logs.data" :key="'details-' + log.id" v-show="showDetails[log.id]"
                        class="mt-4 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Technical Info</h3>
                            <div class="mt-1 text-xs space-y-1">
                                <p><span class="font-medium">IP:</span> {{ log.ip_address }}</p>
                                <p><span class="font-medium">User Agent:</span> {{ log.user_agent }}</p>
                                <p><span class="font-medium">Batch UUID:</span> {{ log.batch_uuid || 'N/A' }}
                                </p>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Properties</h3>
                            <pre class="mt-1 text-xs p-2 bg-white rounded overflow-x-auto">{{
                                prettyPrint(log.properties) }}</pre>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </DashLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import DashLayout from '@/Layouts/DashLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    logs: Object,
    filters: Object,
});

const showDetails = ref({});

const filters = ref({
    search: props.filters.search || '',
    event: props.filters.event || '',
});

watch(filters, (value) => {
    router.get('/activity-logs', {
        search: value.search,
        event: value.event,
    }, {
        preserveState: true,
        replace: true,
    });
}, { deep: true });

const badgeClass = (event) => {
    const classes = {
        'px-2 py-2 text-xs rounded-full font-medium': true,
        'bg-green-100 text-green-800': event === 'created',
        'bg-blue-100 text-blue-800': event === 'updated',
        'bg-red-100 text-red-800': event === 'deleted',
        'bg-gray-100 text-gray-800': !['created', 'updated', 'deleted'].includes(event),
    };
    return classes;
};

const formatDate = (date) => {
    return new Date(date).toLocaleString();
};

const prettyPrint = (obj) => {
    return JSON.stringify(obj, null, 2);
};

const toggleDetails = (id) => {
    showDetails.value = {
        ...showDetails.value,
        [id]: !showDetails.value[id]
    };
};
</script>
