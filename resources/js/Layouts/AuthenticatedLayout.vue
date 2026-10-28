<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const userRole = computed(() => {
    if (user.value.roles && user.value.roles.length > 0) {
        return typeof user.value.roles[0] === 'string'
            ? user.value.roles[0]
            : user.value.roles[0].name;
    }
    return 'Administrador';
});

// Sidebar Mobile Toggle
const sidebarAbierta = ref(false);
const toggleSidebar = () => {
    sidebarAbierta.value = !sidebarAbierta.value;
};

// Dark Mode Toggle
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

const todosLosMenus = [
    {
        name: 'Dashboard',
        route: 'dashboard',
        icon: 'pi pi-th-large',
        roles: ['Administrador', 'Director', 'Encargado de Kardex', 'Comisión Académica'],
    },
    {
        name: 'Malla Curricular',
        route: 'malla-curricular.index',
        icon: 'pi pi-sitemap',
        roles: ['Administrador', 'Director', 'Docente', 'Estudiante', 'Encargado de Kardex', 'Comisión Académica'],
    },
    {
        name: 'Cursos y Paralelos',
        route: 'cursos.index',
        icon: 'pi pi-calendar',
        roles: ['Administrador', 'Director', 'Encargado de Kardex'],
    },
    {
        name: 'Docentes',
        route: 'docentes.index',
        icon: 'pi pi-users',
        roles: ['Administrador', 'Director'],
    },
    {
        name: 'Admisiones',
        route: 'admisiones.index',
        icon: 'pi pi-user-plus',
        roles: ['Administrador', 'Director', 'Encargado de Kardex', 'Comisión Académica'],
    },
    {
        name: 'Kardex Académico',
        route: 'kardex.index',
        icon: 'pi pi-id-card',
        roles: ['Administrador', 'Director', 'Encargado de Kardex'],
    },
    {
        name: 'Config. Académica',
        route: 'configuracion-academica.index',
        icon: 'pi pi-sliders-h',
        roles: ['Administrador', 'Director'],
    },
    {
        name: 'Portal Web',
        route: 'portal-web.index',
        icon: 'pi pi-globe',
        roles: ['Administrador', 'Director'],
    },
    {
        name: 'Mis Cursos',
        route: 'mis-cursos.index',
        icon: 'pi pi-book',
        roles: ['Docente', 'Administrador'],
    },
    {
        name: 'Mi Panel',
        route: 'mi-panel.index',
        icon: 'pi pi-graduation-cap',
        roles: ['Estudiante', 'Administrador'],
    },
];

const menuItems = computed(() => {
    const rol = userRole.value;
    return todosLosMenus.filter(item => !item.roles || item.roles.includes(rol));
});

// Profile dropdown state
const dropdownPerfilAbierto = ref(false);
</script>

<template>
    <div class="min-h-screen bg-slate-100 dark:bg-[#0b0f19] text-slate-800 dark:text-slate-100 flex transition-colors duration-200">
        <!-- BACKDROP PARA MÓVILES -->
        <div
            v-if="sidebarAbierta"
            @click="sidebarAbierta = false"
            class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 lg:hidden"
        ></div>

        <!-- SIDEBAR ESTILO CREATIVE TIM (MATERIAL DASHBOARD PRO) -->
        <aside
            :class="[
                'fixed lg:sticky top-0 h-screen z-50 w-64 bg-[#111827] dark:bg-[#0d121f] text-white flex flex-col justify-between border-r border-slate-800/80 shadow-2xl transition-transform duration-300 ease-in-out',
                sidebarAbierta ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
                <!-- BRAND / LOGO HEADER -->
                <div class="flex items-center gap-3 px-2">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-pink-500 via-rose-500 to-amber-400 flex items-center justify-center shadow-lg shadow-pink-500/30">
                        <i class="pi pi-graduation-cap text-xl text-white font-bold"></i>
                    </div>
                    <div>
                        <h1 class="text-sm font-black tracking-wider uppercase bg-gradient-to-r from-white via-slate-200 to-pink-200 bg-clip-text text-transparent">
                            Creative Academic
                        </h1>
                        <p class="text-[10px] text-slate-400 font-semibold tracking-widest uppercase">
                            Sistema de Gestión
                        </p>
                    </div>
                </div>

                <div class="h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>

                <!-- USER PROFILE WIDGET (Estilo Tania Andrew en Material Dashboard) -->
                <div class="px-2 py-3 rounded-2xl bg-white/5 border border-white/5 flex items-center gap-3">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-pink-500 to-rose-600 flex items-center justify-center font-bold text-sm text-white shadow-md">
                            {{ user.name ? user.name.charAt(0) : 'U' }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-3 h-3 rounded-full bg-emerald-500 border-2 border-[#111827]"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white truncate">
                            {{ user.name }}
                        </p>
                        <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-semibold bg-pink-500/20 text-pink-300 border border-pink-500/30 uppercase tracking-wider">
                            {{ userRole }}
                        </span>
                    </div>
                </div>

                <!-- NAVIGATION MENU -->
                <nav class="space-y-1.5">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 mb-2">
                        Módulos Académicos
                    </p>

                    <Link
                        v-for="item in menuItems"
                        :key="item.name"
                        :href="route(item.route)"
                        :class="[
                            'flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold transition-all duration-200 group',
                            route().current(item.route)
                                ? 'bg-gradient-to-r from-pink-500 via-rose-500 to-pink-600 text-white shadow-lg shadow-pink-500/35 font-bold'
                                : 'text-slate-300 hover:text-white hover:bg-white/10'
                        ]"
                    >
                        <div
                            :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center transition-colors',
                                route().current(item.route)
                                    ? 'bg-white/20 text-white'
                                    : 'bg-white/5 text-slate-400 group-hover:text-white group-hover:bg-white/15'
                            ]"
                        >
                            <i :class="[item.icon, 'text-sm']"></i>
                        </div>
                        <span>{{ item.name }}</span>
                    </Link>
                </nav>
            </div>

            <!-- FOOTER SIDEBAR -->
            <div class="p-4 border-t border-slate-800/80 bg-black/20">
                <div class="flex items-center justify-between">
                    <button
                        @click="toggleDarkMode"
                        type="button"
                        class="p-2 rounded-lg bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white transition-colors text-xs flex items-center gap-2"
                        :title="isDark ? 'Cambiar a Modo Claro' : 'Cambiar a Modo Oscuro'"
                    >
                        <i :class="isDark ? 'pi pi-sun text-amber-400' : 'pi pi-moon text-indigo-300'"></i>
                        <span class="text-[11px]">{{ isDark ? 'Claro' : 'Oscuro' }}</span>
                    </button>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="p-2 rounded-lg bg-rose-500/20 hover:bg-rose-500 text-rose-300 hover:text-white transition-all text-xs flex items-center gap-1.5 font-semibold"
                        title="Cerrar Sesión"
                    >
                        <i class="pi pi-power-off text-xs"></i>
                        <span class="text-[11px]">Salir</span>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- MAIN WRAPPER -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- TOP NAVBAR (Material Dashboard Floating Glassmorphism Style) -->
            <header class="sticky top-0 z-30 px-4 sm:px-6 lg:px-8 py-3 bg-white/80 dark:bg-[#111827]/80 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 transition-colors">
                <div class="flex items-center justify-between gap-4">
                    <!-- Left: Hamburger (mobile) + Breadcrumbs -->
                    <div class="flex items-center gap-3">
                        <button
                            @click="toggleSidebar"
                            type="button"
                            class="lg:hidden p-2 rounded-lg text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800"
                        >
                            <i class="pi pi-bars text-lg"></i>
                        </button>

                        <div>
                            <div class="flex items-center gap-1.5 text-[11px] text-slate-400 dark:text-slate-500 font-medium">
                                <span>Páginas</span>
                                <span>/</span>
                                <span class="text-slate-700 dark:text-slate-300 font-semibold capitalize">
                                    {{ route().current()?.split('.')[0] || 'Dashboard' }}
                                </span>
                            </div>
                            <h2 class="text-base font-black text-slate-900 dark:text-white tracking-tight">
                                <slot name="header">
                                    {{ route().current()?.split('.')[0]?.toUpperCase() || 'DASHBOARD' }}
                                </slot>
                            </h2>
                        </div>
                    </div>

                    <!-- Right: Search Bar + Action Icons -->
                    <div class="flex items-center gap-3">
                        <div class="hidden sm:block relative w-56">
                            <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input
                                type="text"
                                placeholder="Buscar en el sistema..."
                                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/90 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-pink-500 transition-all shadow-xs"
                            />
                        </div>

                        <!-- Notification Bell con Badge Rojo (Como en Creative Tim) -->
                        <div class="relative">
                            <button
                                type="button"
                                class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition-colors relative"
                                title="Notificaciones del Sistema"
                            >
                                <i class="pi pi-bell text-sm"></i>
                                <span class="absolute top-1 right-1 w-2.5 h-2.5 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-900 animate-pulse"></span>
                            </button>
                        </div>

                        <!-- Theme Toggle Quick Button -->
                        <button
                            @click="toggleDarkMode"
                            type="button"
                            class="w-9 h-9 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center transition-colors"
                        >
                            <i :class="isDark ? 'pi pi-sun text-amber-400 text-sm' : 'pi pi-moon text-indigo-400 text-sm'"></i>
                        </button>

                        <!-- User Avatar Quick Dropdown -->
                        <div class="relative">
                            <button
                                @click="dropdownPerfilAbierto = !dropdownPerfilAbierto"
                                type="button"
                                class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                            >
                                <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-pink-500 to-rose-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                                    {{ user.name ? user.name.charAt(0) : 'U' }}
                                </div>
                            </button>

                            <!-- Dropdown Menu -->
                            <div
                                v-if="dropdownPerfilAbierto"
                                @click="dropdownPerfilAbierto = false"
                                class="absolute right-0 mt-2 w-48 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-xl py-1 z-50 text-xs"
                            >
                                <div class="px-4 py-2 border-b border-slate-100 dark:border-slate-700">
                                    <p class="font-bold text-slate-900 dark:text-white truncate">{{ user.name }}</p>
                                    <p class="text-[11px] text-slate-500 truncate">{{ user.email }}</p>
                                </div>
                                <Link
                                    :href="route('profile.edit')"
                                    class="block px-4 py-2 text-slate-700 dark:text-slate-300 hover:bg-pink-50 dark:hover:bg-pink-950/40 hover:text-pink-600"
                                >
                                    <i class="pi pi-user mr-2 text-xs"></i> Mi Perfil
                                </Link>
                                <Link
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="w-full text-left block px-4 py-2 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40"
                                >
                                    <i class="pi pi-power-off mr-2 text-xs"></i> Cerrar Sesión
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- MAIN CONTENT -->
            <main class="flex-1">
                <slot />
            </main>
        </div>
    </div>
</template>
