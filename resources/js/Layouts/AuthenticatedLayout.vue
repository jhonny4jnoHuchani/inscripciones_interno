<script setup>
import { ref, onMounted } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Avatar from 'primevue/avatar';

const showingNavigationDropdown = ref(false);
const isDark = ref(false);

const applyTheme = (dark) => {
    isDark.value = dark;
    if (dark) {
        document.documentElement.classList.add('app-dark', 'dark');
        localStorage.setItem('theme', 'dark');
    } else {
        document.documentElement.classList.remove('app-dark', 'dark');
        localStorage.setItem('theme', 'light');
    }
};

const toggleDarkMode = () => {
    applyTheme(!isDark.value);
};

onMounted(() => {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        applyTheme(true);
    } else if (savedTheme === 'light') {
        applyTheme(false);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        applyTheme(true);
    } else {
        applyTheme(false);
    }
});
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
            <nav
                class="border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-800 transition-colors duration-200"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('malla-curricular.index')"
                                    :active="route().current('malla-curricular.index')"
                                >
                                    Malla Curricular
                                </NavLink>
                                <NavLink
                                    :href="route('cursos.index')"
                                    :active="route().current('cursos.index')"
                                >
                                    Cursos y Paralelos
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center sm:gap-2">
                            <!-- PrimeVue Theme Toggle Button -->
                            <Button
                                :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
                                severity="secondary"
                                text
                                rounded
                                @click="toggleDarkMode"
                                :title="isDark ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'"
                                :aria-label="isDark ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'"
                                class="!p-2 text-gray-500 dark:text-gray-300 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                            />

                            <!-- Settings Dropdown -->
                            <div class="relative ms-2">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center gap-2 rounded-md border border-transparent bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium leading-4 text-gray-500 dark:text-gray-300 transition duration-150 ease-in-out hover:text-gray-700 dark:hover:text-white focus:outline-none"
                                            >
                                                <Avatar
                                                    :label="$page.props.auth.user.name.charAt(0).toUpperCase()"
                                                    shape="circle"
                                                    class="bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 text-xs font-bold"
                                                    style="width: 28px; height: 28px;"
                                                />
                                                <span>{{ $page.props.auth.user.name }}</span>

                                                <svg
                                                    class="-me-0.5 ms-1 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Perfil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Cerrar Sesión
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden gap-1">
                            <!-- PrimeVue Mobile Theme Toggle -->
                            <Button
                                :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
                                severity="secondary"
                                text
                                rounded
                                @click="toggleDarkMode"
                                class="!p-2 text-gray-500 dark:text-gray-300"
                            />

                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 dark:text-gray-300 transition duration-150 ease-in-out hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-500 focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('malla-curricular.index')"
                            :active="route().current('malla-curricular.index')"
                        >
                            Malla Curricular
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('cursos.index')"
                            :active="route().current('cursos.index')"
                        >
                            Cursos y Paralelos
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 dark:border-gray-700 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Cerrar Sesión
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white dark:bg-gray-800 shadow transition-colors duration-200"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
