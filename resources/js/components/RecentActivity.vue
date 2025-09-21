<script setup lang="ts">
interface ActivityItem {
    id: number;
    name: string;
    type: 'file' | 'user';
    action?: string;
    created_at: string;
    user?: string;
    is_public?: boolean;
    is_verified?: boolean;
}

interface RecentActivityProps {
    title: string;
    items: ActivityItem[];
    type: 'files' | 'users';
}

defineProps<RecentActivityProps>();

const truncateLabel = (text: string, length: number) => {
    if (text.length <= length) return text;
    return text.slice(0, length) + '...';
};
</script>

<template>
    <div class="rounded-lg border bg-card p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-foreground mb-4">{{ title }}</h3>

        <div v-if="items.length > 0" class="space-y-4">
            <div
                v-for="item in items"
                :key="item.id"
                class="flex items-center justify-between py-2 border-b border-border last:border-b-0"
            >
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-3">
                        <!-- Avatar/Icon -->
                        <div class="flex-shrink-0">
                            <div v-if="type === 'users'" class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                <span class="text-sm font-medium text-white">
                                    {{ item.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <div v-else class="h-8 w-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                                <svg class="h-4 w-4 text-gray-600 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-foreground truncate sm:break-words" :title="item.name">
                                {{ truncateLabel(item.name, 20) }}
                            </p>
                            <div class="flex items-center gap-2 mt-1 flex-wrap">
                                <p class="text-xs text-muted-foreground">
                                    {{ item.created_at }}
                                </p>
                                <div v-if="type === 'files'" class="flex items-center gap-1 flex-wrap">
                                    <span class="text-xs text-muted-foreground">by {{ item.user }}</span>
                                    <span
                                        :class="[
                                            'inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium',
                                            item.is_public
                                                ? 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200'
                                                : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200'
                                        ]"
                                    >
                                        {{ item.is_public ? 'Public' : 'Private' }}
                                    </span>
                                </div>
                                <div v-if="type === 'users'" class="flex items-center gap-1">
                                    <span
                                        :class="[
                                            'inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium',
                                            item.is_verified
                                                ? 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200'
                                                : 'bg-orange-100 text-orange-700 dark:bg-orange-800 dark:text-orange-200'
                                        ]"
                                    >
                                        {{ item.is_verified ? 'Verified' : 'Unverified' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-8">
            <p class="text-sm text-muted-foreground">No recent {{ type }} found</p>
        </div>
    </div>
</template>
