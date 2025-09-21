<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { LoaderCircle, Upload, AlertCircle } from 'lucide-vue-next';

interface FileUploadProps {
    isLoading?: boolean;
    uploadErrors?: string[];
}

interface FileUploadEmits {
    (e: 'filesSelected', files: FileList | File[]): void;
    (e: 'clearErrors'): void;
}

withDefaults(defineProps<FileUploadProps>(), {
    isLoading: false,
    uploadErrors: () => []
});

const emit = defineEmits<FileUploadEmits>();

const isDragOver = ref(false);

// Drag and drop handlers
const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = true;
};

const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragOver.value = false;

    if (event.dataTransfer?.files) {
        emit('filesSelected', event.dataTransfer.files);
    }
};

const handleFiles = (event: Event) => {
    const input = event.target as HTMLInputElement;

    if (!input.files || input.files.length > 10) {
        alert('You can upload a maximum of 10 files at a time.');
        return;
    }

    if (input.files) {
        emit('filesSelected', input.files);
    }
};

// Keyboard shortcuts
const handleKeydown = (event: KeyboardEvent) => {
    // Ctrl/Cmd + O to open file dialog
    if ((event.ctrlKey || event.metaKey) && event.key === 'o') {
        event.preventDefault();
        document.getElementById('file-upload-input')?.click();
    }

    // Escape key to cancel drag
    if (event.key === 'Escape' && isDragOver.value) {
        isDragOver.value = false;
    }
};

// Lifecycle hooks
onMounted(() => {
    document.addEventListener('keydown', handleKeydown);

    // Prevent default drag behavior on document
    document.addEventListener('dragover', (e) => e.preventDefault());
    document.addEventListener('drop', (e) => e.preventDefault());
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
    document.removeEventListener('dragover', (e) => e.preventDefault());
    document.removeEventListener('drop', (e) => e.preventDefault());
});
</script>

<template>
    <div class="w-full flex flex-col items-center gap-4">
        <!-- Error Messages -->
        <div v-if="uploadErrors.length > 0" class="mb-6 w-full max-w-2xl">
            <div
                v-for="error in uploadErrors"
                :key="error"
                class="mb-2 flex items-center gap-2 rounded-md bg-red-50 p-3 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
            >
                <AlertCircle class="h-4 w-4" />
                {{ error }}
            </div>
        </div>

        <!-- Drag & Drop Upload Area -->
        <div
            class="w-full max-w-[520px]"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
        >
            <div
                class="rounded-lg border-2 border-dashed p-8 text-center transition-colors"
                :class="[
                    isDragOver
                        ? 'border-blue-400 bg-blue-50 dark:border-blue-500 dark:bg-gray-900'
                        : 'border-gray-300 dark:border-gray-600'
                ]"
            >
                <Upload class="mx-auto mb-4 h-12 w-12 text-gray-400" />
                <p class="mb-4 text-lg font-medium text-gray-700 dark:text-gray-200">
                    {{ isDragOver ? 'Drop files here' : 'Drag and drop files here' }}
                </p>
                <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
                    or click to browse (Max 50MB per file and Max 10 files)
                    <br>
                    <span class="text-xs">Keyboard shortcuts: Ctrl/Cmd+O to upload</span>
                </p>

                <input
                    id="file-upload-input"
                    type="file"
                    accept="video/*,image/*"
                    multiple
                    max="10"
                    class="hidden"
                    @change="handleFiles"
                />

                <label
                    for="file-upload-input"
                    class="
                        inline-flex items-center justify-center h-[52px] w-full md:w-[320px] rounded-full cursor-pointer
                        bg-black text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2
                        dark:bg-white dark:text-gray-600 hover:dark:bg-gray-200 hover:dark:text-black focus:dark:outline-none focus:dark:ring-2 focus:dark:ring-gray-500 focus:dark:ring-offset-2
                        disabled:opacity-50 disabled:cursor-not-allowed
                        transition-colors font-medium"
                    :class="{ 'opacity-50 cursor-not-allowed': isLoading }"
                >
                    <LoaderCircle
                        v-if="isLoading"
                        class="h-4 w-4 animate-spin mr-2"
                    />
                    <span class="text-sm lg:text-md">
                        {{ isLoading ? 'Processing...' : 'Upload Images or Videos' }}
                    </span>
                </label>
            </div>
        </div>
    </div>
</template>
