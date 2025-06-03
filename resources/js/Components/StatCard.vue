<template>
    <div class="bg-white p-6 rounded-xl shadow hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 uppercase">{{ title }}</p>
                <p class="text-3xl font-bold" :class="`text-${color}-500`">{{ value }}</p>
                <p v-if="trend !== undefined" class="text-xs mt-1" :class="trend >= 0 ? 'text-green-500' : 'text-red-500'">
                    {{ Math.abs(trend).toFixed(1) }}% {{ trend >= 0 ? '↑' : '↓' }}
                </p>
            </div>
            <div :class="`bg-${color}-100 p-3 rounded-full`">
                <i :class="`fa-solid fa-${icon} w-6 h-6 text-${color}-500`"></i>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: String,
    color: {
        type: String,
        default: 'blue'
    },
    trend: Number
});
</script>