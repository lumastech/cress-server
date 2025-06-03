<template>
    <div class="relative h-full w-full">
        <svg :width="width" :height="height" class="w-full h-full">
            <!-- X Axis -->
            <line x1="0" y1="100%" x2="100%" y2="100%" stroke="#E5E7EB" stroke-width="1" />

            <!-- Y Axis -->
            <line x1="0" y1="0" x2="0" y2="100%" stroke="#E5E7EB" stroke-width="1" />

            <!-- Grid Lines -->
            <template v-for="(_, index) in 5" :key="'h' + index">
                <line x1="0" :y1="`${100 - (index * 25)}%`" x2="100%" :y2="`${100 - (index * 25)}%`" stroke="#F3F4F6"
                    stroke-width="1" />
            </template>

            <!-- Data Line -->
            <polyline fill="none" stroke="#3B82F6" stroke-width="2" :points="linePoints" />

            <!-- Data Points -->
            <circle v-for="(point, index) in normalizedData" :key="index"
                :cx="`${(index / (normalizedData.length - 1)) * 100}%`" :cy="`${100 - (point * 100)}%`" r="4"
                fill="#3B82F6" />

            <!-- Labels -->
            <text v-for="(label, index) in labels" :key="'label' + index" :x="`${(index / (labels.length - 1)) * 100}%`"
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

const linePoints = computed(() => {
    return props.data.map((val, index) => {
        const x = (index / (props.data.length - 1)) * 100;
        const y = 100 - ((val / maxValue.value) * 100);
        return `${x}%,${y}%`;
    }).join(' ');
});
</script>
