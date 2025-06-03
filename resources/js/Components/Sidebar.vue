<template>
    <nav class="w-60 overflow-x-auto  ">
        <!-- Logo -->
        <div class="navbar flex p-4 border-b border-gray-100">
            <img src="../../assets/images/logo.png" alt="App Logo" class="h-10">
        </div>

        <!-- Menu Items -->
        <div class="nav p-4 text-gray-800 space-y-1">
            <!-- Dashboard Section -->
            <div class="flex mt-4 mb-2">
                <h1 class="font-bold flex-auto">User</h1>
            </div>

            <Link href="/dashboard" class="flex items-center py-2 px-3 rounded-md hover:bg-teal-50 transition-all">
            <i class="fas fa-chart-line text-lg w-6 text-teal-600"></i>
            <span class="ml-3">Dashboard</span>
            </Link>

            <!-- Administration Section -->
            <div class="flex mt-6 mb-2">
                <h1 class="font-bold flex-auto">Administration</h1>
            </div>

            <Link href="/users" class="flex items-center py-2 px-3 rounded-md hover:bg-teal-50 transition-all">
            <i class="fas fa-users text-lg w-6 text-teal-600"></i>
            <span class="ml-3">Users</span>
            </Link>

            <Link href="/alerts" class="flex items-center py-2 px-3 rounded-md hover:bg-teal-50 transition-all">
            <i class="fas fa-bell text-lg w-6 text-teal-600"></i>
            <span class="ml-3">Alerts</span>
            </Link>

            <Link href="/incidents" class="flex items-center py-2 px-3 rounded-md hover:bg-teal-50 transition-all">
            <i class="fas fa-bell text-lg w-6 text-teal-600"></i>
            <span class="ml-3">Incident Reports</span>
            </Link>

            <Link href="/health-centers" class="flex items-center py-2 px-3 rounded-md hover:bg-teal-50 transition-all">
            <i class="fas fa-hospital text-lg w-6 text-teal-600"></i>
            <span class="ml-3">Health Centers</span>
            </Link>

            <Link href="/danger-zones" class="flex items-center py-2 px-3 rounded-md hover:bg-teal-50 transition-all">
            <i class="fas fa-map-marked-alt text-lg w-6 text-teal-600"></i>
            <span class="ml-3">Danger Zones</span>
            </Link>

            <!-- Settings Section -->
            <div class="flex mt-6 mb-2">
                <h1 class="font-bold flex-auto">System</h1>
            </div>

            <Link :href="route('profile.show')"
                class="flex items-center py-2 px-3 rounded-md hover:bg-teal-50 transition-all">
            <i class="fas fa-cog text-lg w-6 text-teal-600"></i>
            <span class="ml-3">Settings</span>
            </Link>

            <!-- Settings Section -->
            <div class="flex mt-6 mb-2">
                <h1 class="font-bold flex-auto">Website</h1>
            </div>
            <Link href="/" class="flex items-center py-2 px-3 rounded-md hover:bg-teal-50 transition-all">
            <i class="fas fa-cog text-lg w-6 text-teal-600"></i>
            <span class="ml-3">Home</span>
            </Link>

        </div>
    </nav>
</template>

<script>
import { Link } from '@inertiajs/vue3';


export default {
    name: 'Sidebar',
    components: {
        Link
    },
    computed: {
    }
}
</script>
