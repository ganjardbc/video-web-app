<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="flex min-h-screen flex-col items-center bg-white text-gray-500 dark:bg-black px-4">
        <header class="sticky top-0 z-10 w-full h-[72px] flex items-center justify-center bg-white dark:border-b-gray-800 dark:bg-black">
            <div class="container h-full flex items-center justify-between">
                <div class="flex items-center justify-center">
                    <Link
                        href="/"
                        class="inline-block"
                    >
                        <div
                            class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground"
                        >
                            <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
                        </div>
                    </Link>
                </div>
                <nav class="flex items-center justify-end gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="inline-block rounded-sm border border-gray-200 px-5 py-1.5 text-sm leading-normal text-gray-500 hover:border-gray-300 dark:border-gray-500 dark:text-gray-100 dark:hover:border-gray-600"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-gray-500 hover:border-gray-200 dark:text-gray-100 dark:hover:border-gray-500"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="register()"
                            class="inline-block rounded-sm border border-gray-200 px-5 py-1.5 text-sm leading-normal text-gray-500 hover:border-gray-300 dark:border-gray-500 dark:text-gray-100 dark:hover:border-gray-600"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </div>
        </header>

        <main class="container min-h-[calc(100vh-125px)] xl:max-w-2/3 py-4 flex flex-col items-center gap-8">
            <slot />
        </main>

        <footer>
            <div class="w-full py-4 text-center text-xs lg:text-sm text-gray-500 dark:text-gray-400">
                &copy; 2024 Video Upload App. All rights reserved.
            </div>
        </footer>
    </div>
</template>
