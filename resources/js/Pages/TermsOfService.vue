<template>
    <Head title="Legal Information" />
    <div class="bg-gray-100 font-sans min-h-screen">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8 md:p-12">

                <!-- Header -->
                <div class="text-center mb-12">
                    <div class="inline-block p-2 rounded-lg">
                        <img src="../../assets/images/logo.png" alt="cress" class="w-12">
                    </div>
                    <h1 class="text-4xl font-bold text-gray-800 mt-4">Legal Information</h1>
                    <p class="text-gray-500 mt-2">Terms of Service & Privacy Policy for CRESS</p>
                </div>

                <!-- Tab Navigation -->
                <div class="mb-8 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button @click="activeTab = 'terms'" :class="[
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                            activeTab === 'terms' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]">
                            Terms of Service
                        </button>
                        <button @click="activeTab = 'privacy'" :class="[
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
                            activeTab === 'privacy' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        ]">
                            Privacy Policy
                        </button>
                    </nav>
                </div>

                <!-- Content Area -->
                <div class="prose max-w-none prose-blue">
                    <!-- Terms of Service Content -->
                    <div v-if="activeTab === 'terms'">
                        <h2>Terms of Service</h2>
                        <p><strong>Last Updated:</strong> {{ lastUpdated }}</p>

                        <p>Welcome to CRESS ("we," "us," or "our"). These Terms of Service ("Terms") govern your use of
                            the Community Rapid Emergency Support System (CRESS) mobile application and its related
                            services (collectively, the "Service"). By downloading, accessing, or using our Service, you
                            agree to be bound by these Terms.</p>

                        <h3>1. Description of Service</h3>
                        <p>CRESS is a personal safety application designed for users in Lusaka, Zambia. It provides
                            features including, but not limited to, rapid emergency alerts via tap or shake, real-time
                            GPS location sharing with designated contacts, a directory of nearby health centers, and a
                            community-sourced "Danger Zone" heatmap.</p>
                        <p><strong>IMPORTANT DISCLAIMER:</strong> The Service is intended to supplement, not replace,
                            traditional emergency services (such as dialing 999 or other local emergency numbers). We do
                            not guarantee a response from any emergency service provider. Your use of the Service is at
                            your own risk.</p>

                        <h3>2. User Accounts and Responsibilities</h3>
                        <ul>
                            <li>You must be at least 18 years of age to create an account.</li>
                            <li>You are responsible for maintaining the confidentiality of your account information,
                                including your password.</li>
                            <li>You are responsible for adding and maintaining accurate and up-to-date information for
                                your designated Emergency Contacts. You must obtain consent from your contacts before
                                adding them to the Service.</li>
                            <li>You agree to use the Service responsibly and only for its intended purpose of personal
                                safety and emergency communication.</li>
                        </ul>

                        <h3>3. User Conduct and Prohibitions</h3>
                        <p>You agree not to misuse the Service. Misuse includes, but is not limited to:</p>
                        <ul>
                            <li>Activating false alerts knowingly or maliciously.</li>
                            <li>Submitting false, misleading, or malicious incident reports for the Danger Zone feature.
                            </li>
                            <li>Attempting to interfere with the proper functioning of the Service.</li>
                            <li>Using the Service for any illegal or unauthorized purpose.</li>
                        </ul>
                        <p>We reserve the right to suspend or terminate your account for any violation of these terms.
                        </p>

                        <h3>4. Disclaimers of Warranties</h3>
                        <p>The Service is provided on an "as is" and "as available" basis without any warranties of any
                            kind, either express or implied. We do not warrant that:</p>
                        <ul>
                            <li>The Service will be uninterrupted, secure, or free from errors.</li>
                            <li>The GPS location data will be accurate at all times.</li>
                            <li>The Danger Zone heatmap will be a complete or perfectly accurate representation of all
                                potential risks.</li>
                            <li>An emergency alert will be successfully delivered or received by your emergency
                                contacts, as delivery depends on factors outside our control (e.g., network
                                connectivity, device settings).</li>
                        </ul>

                        <h3>5. Limitation of Liability</h3>
                        <p>To the fullest extent permitted by applicable law, CRESS and its developers shall not be
                            liable for any indirect, incidental, special, consequential, or punitive damages, or any
                            loss of life, injury, property damage, or loss of profits or revenues, whether incurred
                            directly or indirectly, resulting from (a) your access to, use of, or inability to use the
                            Service; (b) any failure of the Service to perform as expected; (c) any reliance on
                            information provided by the Service, including the Danger Zone feature. Your sole remedy for
                            dissatisfaction with the Service is to stop using it.</p>

                        <h3>6. Governing Law</h3>
                        <p>These Terms shall be governed by and construed in accordance with the laws of the Republic of
                            Zambia, without regard to its conflict of law principles.</p>
                    </div>

                    <!-- Privacy Policy Content -->
                    <div v-if="activeTab === 'privacy'">
                        <h2>Privacy Policy</h2>
                        <p><strong>Last Updated:</strong> {{ lastUpdated }}</p>

                        <p>Your privacy is critically important to us. This Privacy Policy outlines how we collect, use,
                            share, and protect your information when you use the CRESS Service.</p>

                        <h3>1. Information We Collect</h3>
                        <p>We collect the following types of information:</p>
                        <ul>
                            <li><strong>Personal Information:</strong> When you register, we collect your email address,
                                and optionally your name. We also store the contact information for your designated
                                Emergency Contacts (name, email, phone number).</li>
                            <li><strong>Location Data:</strong> We collect your precise real-time GPS location data ONLY
                                when you activate an emergency alert. We do not track your location during normal use of
                                the app. Your general location is used to show nearby health centers.</li>
                            <li><strong>Incident Report Data:</strong> When you report an incident for the Danger Zone
                                feature, we collect the location, time, and category of the report. This data is
                                collected and processed anonymously. We do not associate your personal account with the
                                public heatmap data.</li>
                            <li><strong>Usage Data:</strong> We may collect anonymous data about how you interact with
                                the Service (e.g., feature usage, crash reports) to help us improve the app.</li>
                        </ul>

                        <h3>2. How We Use Your Information</h3>
                        <p>We use the information we collect for the following purposes:</p>
                        <ul>
                            <li>To provide, maintain, and improve the CRESS Service.</li>
                            <li>To create and manage your account.</li>
                            <li>To send an emergency alert, including your name and real-time location, to your
                                designated Emergency Contacts when you activate the feature.</li>
                            <li>To anonymously aggregate incident reports to generate and display the Danger Zone
                                heatmap for the community.</li>
                            <li>To respond to your inquiries or provide user support.</li>
                            <li>For internal analytics to understand and improve service performance.</li>
                        </ul>

                        <h3>3. How We Share Your Information</h3>
                        <p>We do not sell your personal information. We only share your information in the following
                            limited circumstances:</p>
                        <ul>
                            <li><strong>With Your Emergency Contacts:</strong> This is the core function of the app.
                                When you trigger an alert, we share your name, location, and emergency message with the
                                contacts you have designated.</li>
                            <li><strong>For Legal Reasons:</strong> We may disclose your information if required to do
                                so by law or in response to valid requests by public authorities in Zambia.</li>
                            <li><strong>Service Providers:</strong> We may use third-party services (e.g., hosting
                                providers like Hostinger, push notification services like FCM) that process data on our
                                behalf. These providers are obligated not to disclose or use your information for any
                                other purpose.</li>
                        </ul>
                        <p>Data for the Danger Zone heatmap is only shared publicly in an aggregated and anonymized
                            form.</p>

                        <h3>4. Data Security</h3>
                        <p>We are committed to protecting your information. We implement a variety of security measures,
                            including HTTPS encryption for data transit and hashing for passwords, to maintain the
                            safety of your personal information. However, no method of transmission over the Internet or
                            method of electronic storage is 100% secure.</p>

                        <h3>5. Data Retention</h3>
                        <p>We retain your personal account information for as long as your account is active. Location
                            data associated with a specific alert is retained for a limited period for system integrity
                            and then deleted. Anonymized incident report data may be retained longer for trend analysis.
                        </p>

                        <h3>6. Your Rights</h3>
                        <p>In accordance with the Data Protection Act of Zambia, you have the right to access, correct,
                            or delete your personal information. You can manage most of this information directly within
                            the app's profile and settings sections. For other requests, please contact us at
                            lumastech@gmail.com.</p>

                        <h3>7. Contact Us</h3>
                        <p>If you have any questions about these Terms or the Privacy Policy, please contact us at
                            lumastech@gmail.com.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const activeTab = ref('terms'); // Default to Terms of Service
const lastUpdated = new Date().toLocaleDateString('en-GB', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
});

</script>
