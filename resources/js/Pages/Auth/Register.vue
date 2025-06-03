<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
// import Navbar from '@/Components/Navbar.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <!-- MAIN SECTION -->
    <img id="background" class="absolute -left-20 top-0 max-w-[877px]"
        src="https://laravel.com/assets/img/welcome/background.svg" />
    <section class="fixed w-full h-full colousel-bwg">
        <div class="bg-secondary-900/80 w-full h-full text-secondary-100 overflow-y-auto">

            <Head title="Register" />
            <header class="bg-secondary-500">
                <!-- <Navbar /> -->
            </header>
            <div class="px-4">
                <div
                    class="max-w-sm mx-auto px-4 py-7 md:my-24 my-7 bg-secondary-100/10  shadow backdrop-blur rounded-md">
                    <AuthenticationCardLogo class="block mx-auto w-16" />

                    <h1 class="text-2xl my-4 mt-7 text-white font-bold">Sign up</h1>

                    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel for="name" value="Name" class="text-secondary-100" />
                            <TextInput id="name" v-model="form.name" type="text"
                                class="mt-1 block w-full bg-secondary-500/10 backdrop-blur border-secondary-300"
                                required autofocus autocomplete="name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="email" value="Email" class="text-secondary-100" />
                            <TextInput id="email" v-model="form.email" type="email"
                                class="mt-1 block w-full bg-secondary-500/10 backdrop-blur border-secondary-300"
                                required autocomplete="username" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password" value="Password" class="text-secondary-100" />
                            <TextInput id="password" v-model="form.password" type="password"
                                class="mt-1 block w-full bg-secondary-500/10 backdrop-blur border-secondary-300"
                                required autocomplete="new-password" />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div class="mt-4">
                            <InputLabel for="password_confirmation" value="Confirm Password"
                                class="text-secondary-100" />
                            <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                                class="mt-1 block w-full bg-secondary-500/10 backdrop-blur border-secondary-300"
                                required autocomplete="new-password" />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>

                        <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                            <InputLabel for="terms">
                                <div class="flex items-center">
                                    <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                                    <div class="ml-2">
                                        I agree to the <a target="_blank" :href="route('terms.show')"
                                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Terms
                                            of Service</a> and <a target="_blank" :href="route('policy.show')"
                                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Privacy
                                            Policy</a>
                                    </div>
                                </div>
                                <InputError class="mt-2" :message="form.errors.terms" />
                            </InputLabel>
                        </div>

                        <div>
                            <button
                                class="rounded bg-primary-700/30 border border-secondary-500 hover:bg-primary-800 transition text-white uppercase font-bold px-4 py-2 block w-full mt-7 mb-2 backdrop-blur"
                                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">Register</button>
                            <Link v-if="canResetPassword" :href="route('password.request')"
                                class="text-center block underline text-sm text-secondary-100 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Forgot your password?
                            </Link>
                            <Link :href="route('login')"
                                class="underline text-sm text-secondary-100 block text-center mt-4 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Already registered?
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>
