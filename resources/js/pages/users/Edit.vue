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

interface UserData {
    id: number;
    name: string;
    email: string;
}

interface UserEditPageProps {
    user: UserData;
}

const props = defineProps<UserEditPageProps>();

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
        title: props.user.name,
        href: `/users/${props.user.id}`,
    },
    {
        title: 'Edit User',
        href: `/users/${props.user.id}/edit`,
    },
];

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(`/users/${props.user.id}`, {
        onSuccess: () => {
            // Form submission success will be handled by redirect from controller
        },
    });
};
</script>

<template>
    <Head :title="`Edit User: ${props.user.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex-1">
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                        Edit User
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Update user information
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

                            <!-- Password Section -->
                            <div class="border-t pt-6">
                                <h4 class="text-base font-medium text-gray-900 dark:text-white mb-4">
                                    Change Password (Optional)
                                </h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    Leave password fields empty to keep the current password.
                                </p>

                                <!-- New Password -->
                                <div class="grid gap-2 mb-4">
                                    <Label for="password" class="flex items-center gap-2">
                                        <Lock class="h-4 w-4" />
                                        New Password
                                    </Label>
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        class="mt-1 block w-full"
                                        autocomplete="new-password"
                                        placeholder="Enter new password (optional)"
                                    />
                                    <InputError :message="form.errors.password" />
                                </div>

                                <!-- Confirm New Password -->
                                <div class="grid gap-2">
                                    <Label for="password_confirmation" class="flex items-center gap-2">
                                        <Lock class="h-4 w-4" />
                                        Confirm New Password
                                    </Label>
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        class="mt-1 block w-full"
                                        autocomplete="new-password"
                                        placeholder="Confirm new password"
                                    />
                                    <InputError :message="form.errors.password_confirmation" />
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center gap-4 pt-4">
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="flex items-center gap-2"
                                >
                                    <Save class="h-4 w-4" />
                                    {{ form.processing ? 'Updating...' : 'Update User' }}
                                </Button>

                                <Link :href="`/users/${props.user.id}`">
                                    <Button variant="outline" type="button">
                                        Cancel
                                    </Button>
                                </Link>
                            </div>
                        </form>
                    </Card>
                </div>

                <!-- User Info -->
                <div class="md:col-span-1">
                    <Card class="p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Current User
                        </h3>

                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-full bg-blue-500 flex items-center justify-center">
                                <span class="text-lg font-bold text-white">
                                    {{ props.user.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white">
                                    {{ props.user.name }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ props.user.email }}
                                </p>
                            </div>
                        </div>

                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p class="mb-2">
                                <strong>User ID:</strong> #{{ props.user.id }}
                            </p>
                        </div>
                    </Card>

                    <Card class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Information
                        </h3>

                        <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                    Email Changes
                                </h4>
                                <p>
                                    Changing the email address will require the user to verify
                                    their new email address.
                                </p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                    Password Updates
                                </h4>
                                <p>
                                    If you change the password, the user will need to use
                                    the new password for their next login.
                                </p>
                            </div>

                            <div>
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">
                                    Account Safety
                                </h4>
                                <p>
                                    Changes to user accounts should be made carefully and
                                    with proper authorization.
                                </p>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
