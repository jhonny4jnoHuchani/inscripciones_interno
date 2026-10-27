<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalPostulantes: 0,
            admitidos: 0,
            tasaAprobacion: 0,
            cursosActivos: 0,
            materiasTotal: 0,
            docentesTotal: 0,
            inscripcionesActivas: 0,
        }),
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- 1. ROW OF CREATIVE TIM SIGNATURE FLOATING STAT CARDS -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 pt-4">
                <!-- CARD 1: POSTULANTES Y ADMISIÓN (PINK) -->
                <div class="relative bg-white dark:bg-[#111827] rounded-2xl p-4 shadow-xl border border-slate-100 dark:border-slate-800 transition-all hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <!-- Floating Gradient Header Icon (Material Dashboard Signature) -->
                        <div class="-mt-8 w-16 h-16 rounded-2xl bg-gradient-to-tr from-pink-600 to-rose-400 flex items-center justify-center text-white shadow-lg shadow-pink-500/40">
                            <i class="pi pi-user-plus text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Postulantes</p>
                            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">
                                {{ stats.totalPostulantes }}
                            </h3>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-emerald-500 font-bold flex items-center gap-1">
                            <i class="pi pi-check text-[10px]"></i> {{ stats.admitidos }} Admitidos
                        </span>
                        <span class="text-slate-400 text-[11px]">Corte ≥ 61 pts</span>
                    </div>
                </div>

                <!-- CARD 2: MALLA Y MATERIAS (GREEN) -->
                <div class="relative bg-white dark:bg-[#111827] rounded-2xl p-4 shadow-xl border border-slate-100 dark:border-slate-800 transition-all hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <div class="-mt-8 w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/40">
                            <i class="pi pi-sitemap text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Materias</p>
                            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">
                                {{ stats.materiasTotal }}
                            </h3>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-emerald-500 font-bold">9 Niveles Activos</span>
                        <span class="text-slate-400 text-[11px]">Malla Curricular</span>
                    </div>
                </div>

                <!-- CARD 3: CURSOS Y PARALELOS (CYAN) -->
                <div class="relative bg-white dark:bg-[#111827] rounded-2xl p-4 shadow-xl border border-slate-100 dark:border-slate-800 transition-all hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <div class="-mt-8 w-16 h-16 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-400 flex items-center justify-center text-white shadow-lg shadow-cyan-500/40">
                            <i class="pi pi-calendar text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Paralelos 2026-I</p>
                            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">
                                {{ stats.cursosActivos }}
                            </h3>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-cyan-500 font-bold">Paralelos A, B, C</span>
                        <span class="text-slate-400 text-[11px]">Con cupos definidos</span>
                    </div>
                </div>

                <!-- CARD 4: DOCENTES ESPECIALISTAS (AMBER) -->
                <div class="relative bg-white dark:bg-[#111827] rounded-2xl p-4 shadow-xl border border-slate-100 dark:border-slate-800 transition-all hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <div class="-mt-8 w-16 h-16 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-400 flex items-center justify-center text-white shadow-lg shadow-amber-500/40">
                            <i class="pi pi-users text-2xl"></i>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Docentes</p>
                            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">
                                {{ stats.docentesTotal }}
                            </h3>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-amber-500 font-bold">100% Asignados</span>
                        <span class="text-slate-400 text-[11px]">Sin cruce de paralelos</span>
                    </div>
                </div>
            </div>

            <!-- 2. MAIN CENTER HERO CARD (ESTILO GLOBAL METRICS DE CREATIVE TIM) -->
            <div class="relative bg-white dark:bg-[#111827] rounded-3xl p-6 shadow-xl border border-slate-100 dark:border-slate-800 pt-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30">
                            <i class="pi pi-chart-pie text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800 dark:text-white">
                                Resumen General del Periodo Académico 2026-I
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Monitoreo centralizado de inscripciones, control de cupos y pre-requisitos curriculares.
                            </p>
                        </div>
                    </div>

                    <!-- Botones de Acción Rápida -->
                    <div class="flex flex-wrap items-center gap-2.5">
                        <Link
                            :href="route('malla-curricular.index')"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 flex items-center gap-2 transition-all shadow-xs"
                        >
                            <i class="pi pi-sitemap text-indigo-500"></i>
                            Ver Malla Curricular
                        </Link>
                        <Link
                            :href="route('cursos.index')"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-100 flex items-center gap-2 transition-all shadow-xs"
                        >
                            <i class="pi pi-calendar text-cyan-500"></i>
                            Cursos y Paralelos
                        </Link>
                        <Link
                            :href="route('admisiones.index')"
                            class="px-4 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-pink-500 to-rose-600 text-white flex items-center gap-2 shadow-lg shadow-pink-500/30 hover:shadow-pink-500/50 transition-all"
                        >
                            <i class="pi pi-user-plus"></i>
                            Admisiones y Kardex
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-6">
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-pink-100 dark:bg-pink-950/60 text-pink-600 dark:text-pink-300 flex items-center justify-center font-black text-lg">
                            {{ stats.tasaAprobacion }}%
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Tasa de Admisión Institucional</p>
                            <p class="text-[11px] text-slate-400">Postulantes con nota ≥ 61 puntos</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-300 flex items-center justify-center font-black text-lg">
                            {{ stats.inscripcionesActivas }}
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Inscripciones por Kardex</p>
                            <p class="text-[11px] text-slate-400">Materias asignadas en gestión activa</p>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-cyan-100 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-300 flex items-center justify-center font-black text-lg">
                            4
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-200">Carreras Integradas</p>
                            <p class="text-[11px] text-slate-400">Sistemas, Informática, Redes y Software</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. ROW OF 3 CREATIVE TIM FLOATING CHART CARDS (EXACT MATCH DE LA IMAGEN 2) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4">
                <!-- CHART 1: FLOATING PINK BAR CHART CARD (Website Views / Admisiones por Día) -->
                <div class="relative bg-white dark:bg-[#111827] rounded-3xl p-5 shadow-xl border border-slate-100 dark:border-slate-800 transition-all hover:-translate-y-1">
                    <div class="-mt-10 mx-2 h-44 rounded-2xl bg-gradient-to-tr from-pink-600 to-rose-400 shadow-lg shadow-pink-500/40 p-4 flex flex-col justify-between text-white">
                        <div class="flex items-center justify-between text-xs opacity-90">
                            <span class="font-bold uppercase tracking-wider">Exámenes de Admisión</span>
                            <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px]">Semana 4</span>
                        </div>
                        <!-- Mini SVG Bar Chart Graphic -->
                        <div class="flex items-end justify-between h-24 gap-1.5 pt-2">
                            <div class="w-full bg-white/30 rounded-t h-[35%]"></div>
                            <div class="w-full bg-white/40 rounded-t h-[55%]"></div>
                            <div class="w-full bg-white/50 rounded-t h-[40%]"></div>
                            <div class="w-full bg-white/70 rounded-t h-[75%]"></div>
                            <div class="w-full bg-white/90 rounded-t h-[95%]"></div>
                            <div class="w-full bg-white rounded-t h-[65%]"></div>
                            <div class="w-full bg-white/80 rounded-t h-[80%]"></div>
                        </div>
                        <div class="flex justify-between text-[9px] opacity-75 font-mono">
                            <span>L</span><span>M</span><span>M</span><span>J</span><span>V</span><span>S</span><span>D</span>
                        </div>
                    </div>
                    <div class="pt-4 px-2">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-white">Flujo de Postulantes</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Rendimiento en exámenes de ingreso</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center gap-1.5">
                            <i class="pi pi-clock text-xs"></i> Actualizado hace 2 horas
                        </div>
                    </div>
                </div>

                <!-- CHART 2: FLOATING GREEN LINE CHART CARD (Daily Sales / Ocupación de Cupos) -->
                <div class="relative bg-white dark:bg-[#111827] rounded-3xl p-5 shadow-xl border border-slate-100 dark:border-slate-800 transition-all hover:-translate-y-1">
                    <div class="-mt-10 mx-2 h-44 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 shadow-lg shadow-emerald-500/40 p-4 flex flex-col justify-between text-white">
                        <div class="flex items-center justify-between text-xs opacity-90">
                            <span class="font-bold uppercase tracking-wider">Demanda de Cupos</span>
                            <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px]">+18.5%</span>
                        </div>
                        <!-- Mini SVG Line Chart Graphic -->
                        <svg class="w-full h-24 overflow-visible" viewBox="0 0 100 40">
                            <polyline
                                fill="none"
                                stroke="#ffffff"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                points="0,32 18,28 36,15 54,22 72,8 90,14 100,5"
                            />
                            <circle cx="0" cy="32" r="2.5" fill="#ffffff" />
                            <circle cx="18" cy="28" r="2.5" fill="#ffffff" />
                            <circle cx="36" cy="15" r="2.5" fill="#ffffff" />
                            <circle cx="54" cy="22" r="2.5" fill="#ffffff" />
                            <circle cx="72" cy="8" r="2.5" fill="#ffffff" />
                            <circle cx="90" cy="14" r="2.5" fill="#ffffff" />
                            <circle cx="100" cy="5" r="2.5" fill="#ffffff" />
                        </svg>
                        <div class="flex justify-between text-[9px] opacity-75 font-mono">
                            <span>N1</span><span>N2</span><span>N3</span><span>N4</span><span>N5</span><span>N6</span><span>N8</span>
                        </div>
                    </div>
                    <div class="pt-4 px-2">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-white">Llenado de Paralelos</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Ocupación por nivel académico</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center gap-1.5">
                            <i class="pi pi-clock text-xs"></i> Actualizado recientemente
                        </div>
                    </div>
                </div>

                <!-- CHART 3: FLOATING CYAN/BLUE LINE CHART CARD (Completed Tasks / Aprobaciones) -->
                <div class="relative bg-white dark:bg-[#111827] rounded-3xl p-5 shadow-xl border border-slate-100 dark:border-slate-800 transition-all hover:-translate-y-1">
                    <div class="-mt-10 mx-2 h-44 rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-500 shadow-lg shadow-cyan-500/40 p-4 flex flex-col justify-between text-white">
                        <div class="flex items-center justify-between text-xs opacity-90">
                            <span class="font-bold uppercase tracking-wider">Índice de Aprobación</span>
                            <span class="px-2 py-0.5 rounded-full bg-white/20 text-[10px]">Gestión 2026-I</span>
                        </div>
                        <!-- Mini SVG Wave Line Chart Graphic -->
                        <svg class="w-full h-24 overflow-visible" viewBox="0 0 100 40">
                            <polyline
                                fill="none"
                                stroke="#ffffff"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                points="0,35 20,30 40,6 60,20 80,24 100,26"
                            />
                            <circle cx="0" cy="35" r="2.5" fill="#ffffff" />
                            <circle cx="20" cy="30" r="2.5" fill="#ffffff" />
                            <circle cx="40" cy="6" r="2.5" fill="#ffffff" />
                            <circle cx="60" cy="20" r="2.5" fill="#ffffff" />
                            <circle cx="80" cy="24" r="2.5" fill="#ffffff" />
                            <circle cx="100" cy="26" r="2.5" fill="#ffffff" />
                        </svg>
                        <div class="flex justify-between text-[9px] opacity-75 font-mono">
                            <span>12p</span><span>3p</span><span>6p</span><span>9p</span><span>12a</span><span>3a</span>
                        </div>
                    </div>
                    <div class="pt-4 px-2">
                        <h4 class="font-bold text-sm text-slate-800 dark:text-white">Cumplimiento Académico</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Materias habilitadas por pre-requisito</p>
                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 text-[11px] text-slate-400 flex items-center gap-1.5">
                            <i class="pi pi-check-circle text-xs text-emerald-500"></i> Metas semestrales alcanzadas
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
