<template>
    <nav class="w-60 bg-primary-50 h-full overflow-y-auto fixed md:static z-50">
        <div class="navbar flex p-2 border-b border-primary-50">
            <img src="../../assets/images/logo.png" alt="sch" class="h-12">
        </div>
        <div class="nav p-4 text-secondary-800">
            <div class="flex mt-4 mb-2">
                <h1 class="font-bold flex-auto">Authorized Users</h1>
                <i class="fa fa-plus self-center"></i>
            </div>

            <Link href="/dashboard"
                class="flex py-1 px-2 rounded hover:bg-primary-200 bg-primary-100/50 my-1 transition-all">
            <div class="w-9">
                <i class="fa fa-chart-line text-xl mr-4 text-primary-900 drop-shadow-md"></i>
            </div>
            <span class="flex-auto">Dashboard</span>
            </Link>


            <div class="flex mt-4 mb-2">
                <h1 class="font-bold flex-auto">Administrator</h1>
                <i class="fa fa-plus self-center"></i>
            </div>

            <Link href="/users"
                class="flex py-1 px-2 rounded hover:bg-primary-200 bg-primary-100/50 my-1 transition-all">
            <div class="w-9">
                <i class="fa fa-users text-xl mr-4 text-primary-900 drop-shadow-md"></i>
            </div>
            <span class="flex-auto">Users</span>
            </Link>

            <Link href="/alerts"
                class="flex py-1 px-2 rounded hover:bg-primary-200 bg-primary-100/50 my-1 transition-all">
            <div class="w-9">
                <i class="fas fa-bell text-xl mr-4 text-primary-900 drop-shadow-md"></i>
            </div>
            <span class="flex-auto">Alerts</span>
            </Link>
            <Link href="/incidents"
                class="flex py-1 px-2 rounded hover:bg-primary-200 bg-primary-100/50 my-1 transition-all">
            <div class="w-9">
                <i class="fa fa-exclamation-circle text-xl mr-4 text-primary-900 drop-shadow-md"></i>
            </div>
            <span class="flex-auto">Incident Reports</span>
            </Link>
            <Link href="/health-centers"
                class="flex py-1 px-2 rounded hover:bg-primary-200 bg-primary-100/50 my-1 transition-all">
            <div class="w-9">
                <i class="fas fa-hospital text-xl mr-4 text-primary-900 drop-shadow-md"></i>
            </div>
            <span class="flex-auto">Health Centers</span>
            </Link>
            <Link href="/danger-zones"
                class="flex py-1 px-2 rounded hover:bg-primary-200 bg-primary-100/50 my-1 transition-all">
            <div class="w-9">
                <i class="fas fa-map-marked-alt text-xl mr-4 text-primary-900 drop-shadow-md"></i>
            </div>
            <span class="flex-auto">Danger Zones</span>
            </Link>
            <Link :href="route('profile.show')"
                class="flex py-1 px-2 rounded hover:bg-primary-200 bg-primary-100/50 my-1 transition-all">
            <div class="w-9">
                <i class="fa fa-cog text-xl mr-4 text-primary-900 drop-shadow-md"></i>
            </div>
            <span class="flex-auto">Settings</span>
            </Link>

            <div class="flex mt-4 mb-2">
                <h1 class="font-bold flex-auto">Website</h1>
                <i class="fa fa-plus self-center"></i>
            </div>

            <Link href="/" class="flex py-1 px-2 rounded hover:bg-primary-200 bg-primary-100/50 my-1 transition-all">
            <div class="w-9">
                <i class="fa fa-globe text-xl mr-4 text-primary-900 drop-shadow-md"></i>
            </div>
            <span class="flex-auto">Home</span>
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
