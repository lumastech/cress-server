<template>
    <div class="bg-white p-6 rounded-xl shadow">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-700">Recent Activity</h3>
            <Link href="/admin/activity-log" class="text-sm text-blue-500 hover:underline">
                View All
            </Link>
        </div>
        
        <ul class="space-y-3">
            <li v-for="(activity, index) in activities" :key="index" class="flex items-start gap-3 p-2 hover:bg-gray-50 rounded">
                <div class="flex-shrink-0 mt-1" :class="`text-${getActivityColor(activity)}-500`">
                    <i :class="activity.icon || 'fa-solid fa-circle-info'"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-800">
                        <span class="font-medium">{{ activity.causer }}</span> {{ activity.description }}
                        <span v-if="activity.subject" class="font-medium">"{{ activity.subject }}"</span>
                    </p>
                    <p class="text-xs text-gray-400">{{ activity.time }}</p>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    activities: Array
});

const getActivityColor = (activity) => {
    if (activity.description.includes('created')) return 'green';
    if (activity.description.includes('deleted')) return 'red';
    return 'blue';
};
</script>