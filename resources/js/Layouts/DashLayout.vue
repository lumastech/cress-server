<script setup>
import Navbar from '@/Components/Navbar.vue';
import Sidebar from '@/Components/Sidebar.vue';
import { ref, onMounted, onBeforeMount } from 'vue';
const navopen = ref(false);
if (window.innerWidth < 768) {
    navopen.value = false;
} else {
    navopen.value = true;
}

const closeNaveOnMobile = () => {
    if (window.innerWidth < 768) {
        navopen.value = !navopen.value;
    }
};


window.addEventListener('resize', () => {
    if (window.innerWidth < 768) {
        navopen.value = false;
    } else {
        navopen.value = true;
    }
});

onBeforeMount(() => {
    window.removeEventListener('resize', () => {
        if (window.innerWidth < 768) {
            navopen.value = false;
        } else {
            navopen.value = true;
        }
    });
});

</script>

<template>
    <div class="relative top-full">
        <div class="fixed md:flex h-full w-full bg-primary-50">
            <div @click="closeNaveOnMobile"
                class="overflow-x-auto absolute md:static md:bg-primary-50 bg-black/50 z-50 h-full md:shrink-0"
                :class="navopen ? 'translate-x-0 md:w-60 w-full md:transition-all md:duration-300 md:ease-in-out' : '-translate-x-full w-0 transition-all delay-300 duration-0 md:delay-0 md:duration-300 md:ease-in-out'">
                <Sidebar class="transition-all duration-300 ease-in-out"
                    :class="navopen ? 'translate-x-0 w-60' : '-translate-x-full w-0'" />
            </div>
            <main class="flex-auto">
                <div class="navbar p-2 h-16 border-primary-50 flex">
                    <button @click="navopen = !navopen"
                        class="inline mr-2 text-teal-700 h-full self-center px-2 hover:bg-primary-100 rounded transition">
                        <i class="fas fa-bars text-xl "></i>
                    </button>
                    <Navbar class="flex-auto" />
                </div>
                <div class="main h-full bg-white md:rounded-tl-2xl p-4 overflow-y-auto pb-24 text-secondary-500">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
