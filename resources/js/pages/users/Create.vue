<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { User, Mail, Lock, Save } from 'lucide-vue-next';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card } from '@/components/ui/card';
import InputError from '@/components/InputError.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: 'Create User',
        href: '/users/create',
    },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/users', {
        onSuccess: () => {
            // Form submission success will be handled by redirect from controller
        },
    });
};
</script>

<template>
    <Head title="Create User" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Create New User
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Add a new user to the system
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Form -->
                <div class="md:col-span-2">
                    <Card class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            User Information
                        </h3>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Name -->
                            <div class="grid gap-2">
                                <Label for="name" class="flex items-center gap-2">
                                    <User class="h-4 w-4" />
                                    Full Name
                                </Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="name"
                                    placeholder="Enter full name"
                                />
                                <InputError :message="form.errors.name" />
                            </div>

                            <!-- Email -->
                            <div class="grid gap-2">
                                <Label for="email" class="flex items-center gap-2">
                                    <Mail class="h-4 w-4" />
                                    Email Address
                                </Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="email"
                                    placeholder="Enter email address"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <!-- Password -->
                            <div class="grid gap-2">
                                <Label for="password" class="flex items-center gap-2">
                                    <Lock class="h-4 w-4" />
                                    Password
                                </Label>
                                <Input
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Enter password"
                                />
                                <InputError :message="form.errors.password" />
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Password must be at least 8 characters long.
                                </p>
                            </div>

                            <!-- Confirm Password -->
                            <div class="grid gap-2">
                                <Label for="password_confirmation" class="flex items-center gap-2">
                                    <Lock class="h-4 w-4" />
                                    Confirm Password
                                </Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Confirm password"
                                />
                                <InputError :message="form.errors.password_confirmation" />
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center gap-4 pt-4">
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex items-center gap-2"
                                >
                                    <Save class="h-4 w-4" />
                                    {{ form.processing ? 'Creating...' : 'Create User' }}
                                </Button>

                                <Link href="/users">
                                    <Button variant="outline" type="button">
                                        Cancel
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </Card>
                </div>

                <!-- Help/Info -->
                <div class="md:col-span-1">
                    <Card class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Information
                        </h3>

                        <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                    Account Creation
                                </h4>
                                <p>
                                    A new user account will be created with the provided information.
                                    The user will be able to log in immediately with these credentials.
                                </p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                    Email Verification
                                </h4>
                                <p>
                                    The email address will need to be verified by the user.
                                    They will receive a verification email upon account creation.
                                </p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                    Password Requirements
                                </h4>
                                <ul class="list-disc list-inside space-y-1">
                                    <li>Minimum 8 characters</li>
                                    <li>Should include numbers and symbols</li>
                                    <li>Avoid common passwords</li>
                                </ul>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
