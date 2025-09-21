<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Search, Plus, Eye, Edit, Trash2, Users } from 'lucide-vue-next';
import dayjs from 'dayjs';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import DataTable from '@/components/DataTable.vue';
import Column from '@/components/Column.vue';
import Pagination from '@/components/Pagination.vue';
import { getNoTable } from '@/utils/table';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
    {
        title: 'Users',
        href: '/users',
    },
];

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

interface UsersPageProps {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: any[];
    };
    filters: {
        search: string;
        per_page: number;
    };
}

const props = defineProps<UsersPageProps>();

// Local reactive state for filters
const searchQuery = ref(props.filters.search || '');
const perPage = ref(props.filters.per_page || 10);

// Methods
const searchUsers = () => {
    router.get('/users', {
        search: searchQuery.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const deleteUser = (user: User) => {
    if (!confirm(`Are you sure you want to delete user "${user.name}"?`)) {
        return;
    }

    router.delete(`/users/${user.id}`, {
        onSuccess: () => {
            // Success handled by redirect from controller
        },
        onError: (errors) => {
            console.error('Error deleting user:', errors);
        }
    });
};

const changePage = (page: number) => {
    router.get('/users', {
        page,
        search: searchQuery.value,
        per_page: perPage.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

// Watchers for auto-search
let searchTimeout: ReturnType<typeof setTimeout>;

watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        searchUsers();
    }, 500);
});

watch(perPage, () => {
    searchUsers();
});
</script>

<template>
    <Head title="User Management" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">User Management</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage registered users and their accounts
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Link href="/users/create">
                        <Button class="inline-flex items-center gap-2">
                            <Plus class="h-4 w-4" />
                            Add New User
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Users</p>
                            <p class="text-2xl font-bold">{{ props.users.total }}</p>
                        </div>
                        <Users class="h-8 w-8 text-muted-foreground" />
                    </div>
                </div>
                <div class="rounded-lg border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Verified Users</p>
                            <p class="text-2xl font-bold">{{ props.users.data.filter(u => u.email_verified_at).length }}</p>
                        </div>
                        <Users class="h-8 w-8 text-green-600" />
                    </div>
                </div>
                <div class="rounded-lg border bg-card p-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Unverified Users</p>
                            <p class="text-2xl font-bold">{{ props.users.data.filter(u => !u.email_verified_at).length }}</p>
                        </div>
                        <Users class="h-8 w-8 text-orange-600" />
                    </div>
                </div>
            </div>

            <!-- Search -->
            <div class="relative">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search users by name or email..."
                    class="pl-10"
                />
            </div>

            <!-- Table -->
            <div class="w-full flex flex-col gap-4">
                <DataTable :value="props.users.data" scrollable tableStyle="min-width: 50rem">
                    <Column header="No">
                        <template #body="slotProps">
                            {{ getNoTable(slotProps.index, props.users.current_page, props.users.per_page) }}
                        </template>
                    </Column>
                    <Column field="name" header="Name">
                        <template #body="slotProps">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                        <span class="text-sm font-medium text-white">
                                            {{ slotProps.data.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ slotProps.data.name }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="email" header="Email"></Column>
                    <Column field="status" header="Status">
                        <template #body="slotProps">
                            <span
                                :class="[
                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                    slotProps.data.email_verified_at
                                        ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200'
                                        : 'bg-orange-100 text-orange-800 dark:bg-orange-800 dark:text-orange-200'
                                ]"
                            >
                                {{ slotProps.data.email_verified_at ? 'Verified' : 'Unverified' }}
                            </span>
                        </template>
                    </Column>
                    <Column field="created_at" header="Created">
                        <template #body="slotProps">
                            {{ dayjs(slotProps.data.created_at).format('DD/MM/YYYY, HH:mm') }}
                        </template>
                    </Column>
                    <Column class="text-right" header="Actions">
                        <template #body="slotProps">
                            <div class="flex items-center justify-end gap-2">
                                <Link :href="`/users/${slotProps.data.id}`">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        title="View Details"
                                    >
                                        <Eye class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <Link :href="`/users/${slotProps.data.id}/edit`">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        title="Edit User"
                                    >
                                        <Edit class="h-4 w-4" />
                                    </Button>
                                </Link>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    title="Delete User"
                                    class="text-red-600 hover:text-red-700"
                                    @click="deleteUser(slotProps.data)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </template>
                    </Column>
                </DataTable>

                <!-- Pagination Component -->
                <Pagination
                    v-if="props.users.total > 0"
                    :current-page="props.users.current_page"
                    :total-pages="props.users.last_page"
                    :total-items="props.users.total"
                    :items-per-page="props.users.per_page"
                    :show-info="true"
                    :show-items-per-page="false"
                    @page-change="changePage"
                />
            </div>
        </div>
    </AppLayout>
</template>
