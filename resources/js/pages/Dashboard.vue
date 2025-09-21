<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Users, Files, HardDrive, Eye, EyeOff, UserCheck, UserX, Plus, TrendingUp } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import StatCard from '@/components/StatCard.vue';
import RecentActivity from '@/components/RecentActivity.vue';
import SimpleChart from '@/components/SimpleChart.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface DashboardStats {
    totalUsers: number;
    totalFiles: number;
    totalFileSize: string;
    totalFileSizeBytes: number;
    publicFiles: number;
    privateFiles: number;
    usersThisMonth: number;
    filesThisMonth: number;
    verifiedUsers: number;
    unverifiedUsers: number;
}

interface FileItem {
    id: number;
    name: string;
    original_name: string;
    type: string;
    size: string;
    is_public: boolean;
    created_at: string;
    user: string;
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    is_verified: boolean;
    created_at: string;
}

interface FileTypeStats {
    category: string;
    count: number;
    size: string;
    sizeBytes: number;
}

interface MonthlyUpload {
    month: string;
    count: number;
}

interface DashboardProps {
    stats: DashboardStats;
    recentFiles: FileItem[];
    recentUsers: UserItem[];
    fileTypeStats: FileTypeStats[];
    monthlyFileUploads: MonthlyUpload[];
}

const props = defineProps<DashboardProps>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Dashboard</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Overview of your application statistics and recent activity
                    </p>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto">
                    <Link href="/files/create" class="w-full md:w-auto">
                        <Button class="w-full md:w-auto">
                            <Plus class="h-4 w-4 mr-2" />
                            Upload Files
                        </Button>
                    </Link>
                </div>
            </div>

            <!-- Key Statistics -->
            <div class="grid gap-4 md:grid-cols-2">
                <SimpleChart
                    title="File Type Distribution"
                    :data="props.fileTypeStats"
                />

                <div class="grid gap-4 md:grid-cols-2">
                    <StatCard
                        title="Total Users"
                        :value="props.stats.totalUsers"
                        :icon="Users"
                        color="blue"
                    />
                    <StatCard
                        title="Total Files"
                        :value="props.stats.totalFiles"
                        :icon="Files"
                        color="green"
                    />
                    <StatCard
                        title="Storage Used"
                        :value="props.stats.totalFileSize"
                        :icon="HardDrive"
                        color="purple"
                    />
                    <StatCard
                        title="Public Files"
                        :value="props.stats.publicFiles"
                        :icon="Eye"
                        color="orange"
                    />
                </div>
            </div>

            <!-- Secondary Statistics -->
            <div class="grid gap-4 md:grid-cols-2">
                <div class="grid gap-4 md:grid-cols-2">
                    <StatCard
                        title="Private Files"
                        :value="props.stats.privateFiles"
                        :icon="EyeOff"
                        color="gray"
                    />
                    <StatCard
                        title="New Users"
                        :value="props.stats.usersThisMonth"
                        subtitle="This month"
                        :icon="TrendingUp"
                        color="blue"
                    />
                    <StatCard
                        title="New Files"
                        :value="props.stats.filesThisMonth"
                        subtitle="This month"
                        :icon="TrendingUp"
                        color="green"
                    />
                    <StatCard
                        title="Verified Users"
                        :value="props.stats.verifiedUsers"
                        :icon="UserCheck"
                        color="green"
                    />
                </div>
                <div class="grid gap-4 md:grid-rows-2">
                    <StatCard
                        title="Unverified"
                        :value="props.stats.unverifiedUsers"
                        :icon="UserX"
                        color="orange"
                    />
                    <StatCard
                        title="Verification Rate"
                        :value="`${props.stats.totalUsers > 0 ? Math.round((props.stats.verifiedUsers / props.stats.totalUsers) * 100) : 0}%`"
                        :icon="UserCheck"
                        color="blue"
                    />
                </div>
            </div>

            <!-- Charts and Recent Activity -->
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="grid grid-rows-2 gap-6">
                    <!-- Recent Users -->
                    <RecentActivity
                        title="Recent Users"
                        :items="props.recentUsers.map(user => ({
                            id: user.id,
                            name: user.name,
                            type: 'user' as const,
                            created_at: user.created_at,
                            is_verified: user.is_verified
                        }))"
                        type="users"
                    />

                    <!-- Monthly Upload Trend -->
                    <div class="rounded-lg border bg-card p-6 shadow-sm">
                        <h3 class="text-lg font-semibold text-foreground mb-4">Monthly Upload Trend</h3>
                        <div class="space-y-3">
                            <div
                                v-for="month in props.monthlyFileUploads"
                                :key="month.month"
                                class="flex items-center justify-between"
                            >
                                <span class="text-sm text-muted-foreground">{{ month.month }}</span>
                                <span class="text-sm font-medium">{{ month.count }} files</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Files -->
                <RecentActivity
                    title="Recent Files"
                    :items="props.recentFiles.map(file => ({
                        id: file.id,
                        name: file.original_name,
                        type: 'file' as const,
                        created_at: file.created_at,
                        user: file.user,
                        is_public: file.is_public
                    }))"
                    type="files"
                />
            </div>

            <!-- Quick Actions -->
            <div class="rounded-lg border bg-card p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-foreground mb-4">Quick Actions</h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Link href="/files">
                        <Button variant="outline" class="w-full justify-start">
                            <Files class="h-4 w-4 mr-2" />
                            Manage Files
                        </Button>
                    </Link>
                    <Link href="/users">
                        <Button variant="outline" class="w-full justify-start">
                            <Users class="h-4 w-4 mr-2" />
                            Manage Users
                        </Button>
                    </Link>
                    <Link href="/files/create">
                        <Button variant="outline" class="w-full justify-start">
                            <Plus class="h-4 w-4 mr-2" />
                            Upload File
                        </Button>
                    </Link>
                    <Link href="/profile">
                        <Button variant="outline" class="w-full justify-start">
                            <UserCheck class="h-4 w-4 mr-2" />
                            Profile Settings
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
