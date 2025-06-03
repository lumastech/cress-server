<template>
    <div class="relative h-full w-full">
        <svg :width="width" :height="height" class="w-full h-full">
            <!-- X Axis -->
            <line x1="0" y1="100%" x2="100%" y2="100%" stroke="#E5E7EB" stroke-width="1" />

            <!-- Bars -->
            <rect v-for="(value, index) in normalizedData" :key="index" :x="`${(index / data.length) * 100}%`"
                :y="`${100 - (value * 100)}%`" :width="`${90 / data.length}%`" :height="`${value * 100}%`"
                fill="#10B981" rx="2" />

            <!-- Labels -->
            <text v-for="(label, index) in labels" :key="'label' + index" :x="`${((index + 0.5) / labels.length) * 100}%`"
                y="105%" text-anchor="middle" font-size="10" fill="#6B7280">
                {{ label }}
            </text>
        </svg>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    data: Array,
    labels: Array,
    width: {
        type: Number,
        default: 300
    },
    height: {
        type: Number,
        default: 200
    }
});

const maxValue = computed(() => Math.max(...props.data, 1));
const normalizedData = computed(() => props.data.map(val => val / maxValue.value));
</script>
