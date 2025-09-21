<script setup lang="ts">
import { computed } from 'vue';
import { ChevronLeft, ChevronRight, MoreHorizontal } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface PaginationProps {
    currentPage: number;
    totalPages: number;
    totalItems: number;
    itemsPerPage: number;
    showInfo?: boolean;
    showItemsPerPage?: boolean;
    itemsPerPageOptions?: number[];
    maxVisiblePages?: number;
    variant?: 'default' | 'minimal' | 'compact';
    size?: 'sm' | 'default' | 'lg';
}

interface PaginationEmits {
    (e: 'update:currentPage', page: number): void;
    (e: 'update:itemsPerPage', perPage: number): void;
    (e: 'pageChange', page: number): void;
}

const props = withDefaults(defineProps<PaginationProps>(), {
    showInfo: true,
    showItemsPerPage: false,
    itemsPerPageOptions: () => [10, 25, 50, 100],
    maxVisiblePages: 7,
    variant: 'default',
    size: 'default',
});

const emit = defineEmits<PaginationEmits>();

// Computed properties
const startItem = computed(() => {
    return ((props.currentPage - 1) * props.itemsPerPage) + 1;
});

const endItem = computed(() => {
    return Math.min(props.currentPage * props.itemsPerPage, props.totalItems);
});

const visiblePages = computed(() => {
    const pages: (number | string)[] = [];
    const { currentPage, totalPages, maxVisiblePages } = props;

    if (totalPages <= maxVisiblePages) {
        // Show all pages if total is less than max visible
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i);
        }
    } else {
        // Always show first page
        pages.push(1);

        const start = Math.max(2, currentPage - Math.floor((maxVisiblePages - 4) / 2));
        const end = Math.min(totalPages - 1, currentPage + Math.floor((maxVisiblePages - 4) / 2));

        // Add ellipsis after first page if needed
        if (start > 2) {
            pages.push('...');
        }

        // Add middle pages
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }

        // Add ellipsis before last page if needed
        if (end < totalPages - 1) {
            pages.push('...');
        }

        // Always show last page (if more than 1 page)
        if (totalPages > 1) {
            pages.push(totalPages);
        }
    }

    return pages;
});

const canGoPrevious = computed(() => props.currentPage > 1);
const canGoNext = computed(() => props.currentPage < props.totalPages);

// Methods
const goToPage = (page: number) => {
    if (page >= 1 && page <= props.totalPages && page !== props.currentPage) {
        emit('update:currentPage', page);
        emit('pageChange', page);
    }
};

const goToPrevious = () => {
    if (canGoPrevious.value) {
        goToPage(props.currentPage - 1);
    }
};

const goToNext = () => {
    if (canGoNext.value) {
        goToPage(props.currentPage + 1);
    }
};

const changeItemsPerPage = (perPage: number) => {
    emit('update:itemsPerPage', perPage);
    // Reset to first page when changing items per page
    if (props.currentPage > 1) {
        goToPage(1);
    }
};

// Size classes
</script>

<template>
    <div v-if="totalPages > 1 || showInfo" class="flex flex-col sm:flex-row items-center justify-between gap-4 py-4">
        <!-- Info Section -->
        <div v-if="showInfo" class="flex items-center gap-4">
            <div class="text-sm text-gray-700 dark:text-gray-300">
                <span v-if="totalItems > 0">
                    Show
                    <span class="font-medium">{{ startItem }}</span>
                    to
                    <span class="font-medium">{{ endItem }}</span>
                    of
                    <span class="font-medium">{{ totalItems }}</span>
                    results
                </span>
                <span v-else>No results found</span>
            </div>

            <!-- Items per page selector -->
            <div v-if="showItemsPerPage" class="flex items-center gap-2">
                <label class="text-sm text-gray-700 dark:text-gray-300">Show:</label>
                <select
                    :value="itemsPerPage"
                    @change="changeItemsPerPage(parseInt(($event.target as HTMLSelectElement).value))"
                    class="rounded-md border border-gray-300 px-2 py-1 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                    <option v-for="option in itemsPerPageOptions" :key="option" :value="option">
                        {{ option }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div v-if="totalPages > 1">
            <!-- Mobile pagination (minimal) -->
            <div v-if="variant === 'compact' || variant === 'minimal'" class="flex items-center gap-2">
                <Button
                    @click="goToPrevious"
                    :disabled="!canGoPrevious"
                    variant="outline"
                    :size="size"
                >
                    <ChevronLeft class="w-4 h-4" />
                    <span v-if="variant !== 'minimal'">Previous</span>
                </Button>

                <div v-if="variant !== 'minimal'" class="flex items-center gap-2 px-3 py-2">
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        {{ currentPage }} of {{ totalPages }}
                    </span>
                </div>

                <Button
                    @click="goToNext"
                    :disabled="!canGoNext"
                    variant="outline"
                    :size="size"
                >
                    <span v-if="variant !== 'minimal'">Next</span>
                    <ChevronRight class="w-4 h-4" />
                </Button>
            </div>

            <!-- Desktop pagination (full) -->
            <nav v-else class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <!-- Previous button -->
                <Button
                    @click="goToPrevious"
                    :disabled="!canGoPrevious"
                    variant="outline"
                    :size="size"
                    class="rounded-l-md rounded-r-none border-r-0"
                >
                    <ChevronLeft class="w-4 h-4" />
                    <span class="sr-only">Previous</span>
                </Button>

                <!-- Page numbers -->
                <template v-for="(page, index) in visiblePages" :key="index">
                    <!-- Ellipsis -->
                    <div
                        v-if="page === '...'"
                        class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 dark:text-gray-300 dark:ring-gray-600"
                    >
                        <MoreHorizontal class="w-4 h-4" />
                    </div>

                    <!-- Page number -->
                    <Button
                        v-else
                        @click="goToPage(page as number)"
                        :variant="currentPage === page ? 'default' : 'outline'"
                        :size="size"
                        class="rounded-none border-r-0 last:border-r relative"
                        :class="{
                            'z-10 bg-blue-600 text-white border-blue-600 hover:bg-blue-700 focus:bg-blue-700': currentPage === page,
                            'hover:bg-gray-50 dark:hover:bg-gray-700': currentPage !== page,
                        }"
                    >
                        {{ page }}
                    </Button>
                </template>

                <!-- Next button -->
                <Button
                    @click="goToNext"
                    :disabled="!canGoNext"
                    variant="outline"
                    :size="size"
                    class="rounded-r-md rounded-l-none"
                >
                    <ChevronRight class="w-4 h-4" />
                    <span class="sr-only">Next</span>
                </Button>
            </nav>
        </div>
    </div>
</template>
