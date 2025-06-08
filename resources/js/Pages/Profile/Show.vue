<script setup>
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import DashLayout from '@/Layouts/DashLayout.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
    user: Object,
});

import { ref } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'

const form = useForm({
    apk_file: null,
    name: 'CRESS V1.0'
})

function submit() {
    form.post(route('apk.upload'), {
        forceFormData: true
    })
}
</script>

<template>

    <Head title="Profile" />
    <DashLayout>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <UpdatePasswordForm class="mt-10 sm:mt-0" />

                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canManageTwoFactorAuthentication">
                    <TwoFactorAuthenticationForm :requires-confirmation="confirmsTwoFactorAuthentication"
                        class="mt-10 sm:mt-0" />

                    <SectionBorder />
                </div>

                <LogoutOtherBrowserSessionsForm :sessions="sessions" class="mt-10 sm:mt-0" />

                <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                    <SectionBorder />

                    <DeleteUserForm class="mt-10 sm:mt-0" />
                </template>

                <div v-if="user.role == 'admin'" class="max-w-md mx-auto p-6 mt-10 bg-white shadow rounded">
                    <h2 class="text-xl font-bold mb-4">Upload APK</h2>

                    <form @submit.prevent="submit">
                        <label for="name">App Name</label>
                        <input type="text" v-model="form.name" class="block rounded w-full mb-4">
                        <input type="file" @change="e => form.apk_file = e.target.files[0]" accept=".apk"
                            class="mb-4" />

                        <button type="submit" :disabled="form.processing"
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                            Upload
                        </button>

                        <p v-if="form.errors.apk_file" class="text-red-500 mt-2">
                            {{ form.errors.apk_file }}
                        </p>

                        <p v-if="$page.props.flash.success" class="text-green-600 mt-4">
                            {{ $page.props.flash.success }}
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </DashLayout>
</template>


