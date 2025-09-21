<script setup lang="ts">
interface ChartDataItem {
    category: string;
    count: number;
    size: string;
    sizeBytes: number;
}

interface SimpleChartProps {
    title: string;
    data: ChartDataItem[];
}

defineProps<SimpleChartProps>();

const getBarWidth = (count: number, maxCount: number) => {
    if (maxCount === 0) return '0%';
    return `${(count / maxCount) * 100}%`;
};

const getMaxCount = (data: ChartDataItem[]) => {
    return Math.max(...data.map(item => item.count), 1);
};

const chartColors = [
    'bg-blue-500',
    'bg-green-500',
    'bg-orange-500',
    'bg-purple-500',
    'bg-red-500',
    'bg-yellow-500',
    'bg-indigo-500',
    'bg-pink-500',
];
</script>

<template>
    <div class="rounded-lg border bg-card p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-foreground mb-4">{{ title }}</h3>

        <div v-if="data.length > 0" class="space-y-4">
            <div
                v-for="(item, index) in data"
                :key="item.category"
                class="flex items-center gap-4"
            >
                <!-- Label -->
                <div class="w-20 flex-shrink-0">
                    <p class="text-sm font-medium text-foreground">{{ item.category }}</p>
                    <p class="text-xs text-muted-foreground">{{ item.count }} files</p>
                </div>

                <!-- Bar -->
                <div class="flex-1 relative">
                    <div class="w-full h-6 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                        <div
                            :class="[chartColors[index % chartColors.length]]"
                            :style="{ width: getBarWidth(item.count, getMaxCount(data)) }"
                            class="h-full rounded-full transition-all duration-300"
                        ></div>
                    </div>
                </div>

                <!-- Size -->
                <div class="w-16 flex-shrink-0 text-right">
                    <p class="text-sm text-muted-foreground">{{ item.size }}</p>
                </div>
            </div>
        </div>

        <div v-else class="text-center py-8">
            <p class="text-sm text-muted-foreground">No data available</p>
        </div>
    </div>
</template>
