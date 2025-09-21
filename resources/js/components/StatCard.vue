<script setup lang="ts">
interface StatCardProps {
    title: string;
    value: string | number;
    subtitle?: string;
    icon?: any;
    trend?: {
        value: number;
        isPositive: boolean;
        label: string;
    };
    color?: 'blue' | 'green' | 'orange' | 'red' | 'purple' | 'gray';
}

withDefaults(defineProps<StatCardProps>(), {
    color: 'blue',
});

const colorClasses = {
    blue: 'text-blue-600 bg-blue-100 dark:text-blue-400 dark:bg-blue-900/20',
    green: 'text-green-600 bg-green-100 dark:text-green-400 dark:bg-green-900/20',
    orange: 'text-orange-600 bg-orange-100 dark:text-orange-400 dark:bg-orange-900/20',
    red: 'text-red-600 bg-red-100 dark:text-red-400 dark:bg-red-900/20',
    purple: 'text-purple-600 bg-purple-100 dark:text-purple-400 dark:bg-purple-900/20',
    gray: 'text-gray-600 bg-gray-100 dark:text-gray-400 dark:bg-gray-900/20',
};
</script>

<template>
    <div class="rounded-lg border bg-card p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium text-muted-foreground">{{ title }}</p>
                <p class="text-2xl font-bold text-foreground">{{ value }}</p>
                <p v-if="subtitle" class="text-sm text-muted-foreground mt-1">{{ subtitle }}</p>

                <!-- Trend indicator -->
                <div v-if="trend" class="flex items-center mt-2">
                    <span
                        :class="[
                            'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                            trend.isPositive ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200'
                        ]"
                    >
                        {{ trend.isPositive ? '+' : '' }}{{ trend.value }}%
                    </span>
                    <span class="ml-2 text-sm text-muted-foreground">{{ trend.label }}</span>
                </div>
            </div>

            <!-- Icon -->
            <div v-if="icon" :class="['flex h-12 w-12 items-center justify-center rounded-full', colorClasses[color]]">
                <component :is="icon" class="h-6 w-6" />
            </div>
        </div>
    </div>
</template>
