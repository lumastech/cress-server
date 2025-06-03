<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
// import Navbar from '@/Components/Navbar.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <!-- MAIN SECTION -->
    <img id="background" class="absolute -left-20 top-0 max-w-[877px]"
        src="https://laravel.com/assets/img/welcome/background.svg" />
    <section class="fixed w-full h-full colousel-bgl">
        <div class="bg-secondary-900/80 w-full h-full overflow-y-auto">

            <Head title="Log in" />
            <header class="bg-secondary-500">
                <!-- <Navbar /> -->
            </header>
            <div class="px-4">
                <div class="max-w-sm mx-auto px-4 py-7 my-24 bg-secondary-100/10  shadow backdrop-blur rounded-md">
                    <div class="text-center">
                        <AuthenticationCardLogo class="block mx-auto w-16" />
                    </div>

                    <h1 class="text-2xl my-4 mt-7 text-white font-bold">Sign in</h1>

                    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="email" value="Email" class="text-white" />
                            <TextInput id="email" v-model="form.email" type="email"
                                class="mt-1 block w-full bg-secondary-500/10 backdrop-blur border-secondary-300"
                                required autofocus autocomplete="username" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password" value="Password" class="text-white" />
                            <TextInput id="password" v-model="form.password" type="password"
                                class="mt-1 block w-full bg-secondary-500/10 backdrop-blur border-secondary-300"
                                required autocomplete="current-password" />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="grid grid-cols-2 mt-4">
                            <label class="flex items-center">
                                <Checkbox v-model:checked="form.remember" name="remember" />
                                <span class="ml-2 text-sm text-secondary-100">Remember me</span>
                            </label>
                            <Link v-if="canResetPassword" :href="route('password.request')"
                                class="text-center block underline text-sm text-secondary-100 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Forgot your password?
                            </Link>
                        </div>

                        <div>
                            <button
                                class="rounded bg-primary-700/30 border border-secondary-500 hover:bg-primary-800 transition text-white uppercase font-bold px-4 py-2 block w-full mt-7 mb-2 backdrop-blur"
                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Log in</button>
                            <Link :href="route('register')"
                                class="text-center block underline text-sm text-secondary-100 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Not a member? Create account
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>



</template>

<style>
.colousel-bg {
    background-image: url("../../../assets/images/landing_cover.jpg");
    background-size: cover;
    background-position: 50%;
}
</style>
