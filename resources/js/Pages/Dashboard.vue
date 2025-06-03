<template>

    <Head title="Dashboard" />
    <DashLayout>
        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <StatCard title="Total Users" :value="stats.totalUsers" icon="users" color="blue"
                    :trend="userGrowthTrend" />

                <StatCard title="Active Alerts" :value="stats.activeAlerts" icon="bell" color="red"
                    :trend="alertTrend" />

                <StatCard title="Incidents (24h)" :value="stats.incidentsToday" icon="triangle-exclamation"
                    color="yellow" />

                <StatCard title="System Uptime" :value="stats.systemUptime" icon="shield-halved" color="green" />
            </div>

            <!-- Charts Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">Alert Trends (7 days)</h3>
                        <div class="h-64">
                            <LineChart :data="alertTrends.map(t => t.count)"
                                :labels="alertTrends.map(t => new Date(t.date).toLocaleDateString('en', { month: 'short', day: 'numeric' }))" />
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-lg">
                        <h3 class="text-xl font-semibold text-gray-700 mb-4">User Growth (30 days)</h3>
                        <div class="h-64">
                            <BarChart :data="userGrowth.map(t => t.count)"
                                :labels="userGrowth.map(t => new Date(t.date).toLocaleDateString('en', { month: 'short', day: 'numeric' }))" />
                        </div>
                    </div>
                </div>

            <!-- Recent Activity -->
            <ActivityFeed :activities="recentActivities" />
        </div>
    </DashLayout>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import DashLayout from '@/Layouts/DashLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import BarChart from '@/Components/BarChart.vue';
import LineChart from '@/Components/LineChart.vue';
import ActivityFeed from '@/Components/ActivityFeed.vue';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    alertTrends: Array,
    userGrowth: Array,
    recentActivities: Array,
});

// Calculate trends
const alertTrend = computed(() => {
    if (props.alertTrends.length < 2) return 0;
    const current = props.alertTrends[props.alertTrends.length - 1].count;
    const previous = props.alertTrends[props.alertTrends.length - 2].count;
    return ((current - previous) / previous) * 100;
});

const userGrowthTrend = computed(() => {
    if (props.userGrowth.length < 2) return 0;
    const current = props.userGrowth[props.userGrowth.length - 1].count;
    const previous = props.userGrowth[props.userGrowth.length - 2].count;
    return ((current - previous) / previous) * 100;
});
</script>
