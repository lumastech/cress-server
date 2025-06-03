<template>
    <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">{{ title }}</h3>
        <div class="h-64">
            <LineChart v-if="type === 'line'" :chart-data="formattedData" />
            <BarChart v-else :chart-data="formattedData" />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import LineChart from '@/Components/LineChart.vue';
import BarChart from '@/Components/BarChart.vue';

const props = defineProps({
    title: String,
    data: Array,
    type: {
        type: String,
        default: 'line'
    }
});

const formattedData = computed(() => ({
    labels: props.data.map(item => item.date),
    datasets: [{
        label: props.title ?? 'Tilte',
        data: props.data?.map(item => item.count),
        backgroundColor: props.type === 'line' ? '#3B82F6' : '#10B981',
        borderColor: '#3B82F6',
        tension: 0.1
    }]
}));
</script>
