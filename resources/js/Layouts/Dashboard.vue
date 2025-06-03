<template>
    <div class="leading-normal tracking-normal" id="main-body">
        <div class="flex flex-wrap">
            <Sidebar />

            <div class="w-full bg-gray-100 pl-0 lg:pl-64 min-h-screen" :class="sideBarOpen ? 'overlay' : ''" id="main-content">
                <Navbar />

                <main>
                    <div class="p-6 bg-gray-100 mb-7 h-full">
                        <slot />
                    </div>
                </main>

                <Footer />
            </div>
        </div>
    </div>
</template>

<script>
// import { mapState } from 'vuex'

import Sidebar from '@/Components/Sidebar.vue'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'

export default {
  name: 'Dashboard',
  computed: {
    // ...mapState(['sideBarOpen'])
  },
  components: {
    Sidebar,
    Navbar,
    Footer
  }
}
</script>
