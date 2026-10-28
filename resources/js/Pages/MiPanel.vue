<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Tag from 'primevue/tag';
import Card from 'primevue/card';

const props = defineProps({
    estudiante: {
        type: Object,
        required: true,
    },
    materiasActuales: {
        type: Array,
        default: () => [],
    },
    materiasHistorial: {
        type: Array,
        default: () => [],
    },
    periodos: {
        type: Array,
        default: () => [],
    },
    gestionActual: {
        type: String,
        default: '2026-I',
    },
});
</script>

<template>
    <Head title="Mi Panel - Portal del Estudiante" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="pi pi-user text-pink-500"></i>
                        Portal del Estudiante
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Consulta de materias inscritas, notas parciales, docentes asignados y avance académico.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-pink-500/10 text-pink-600 dark:text-pink-400 font-bold text-xs border border-pink-500/20">
                        Gestión {{ gestionActual }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- TARJETA DE PERFIL Y RESUMEN ACADÉMICO -->
            <div class="bg-gradient-to-r from-pink-500 via-rose-500 to-amber-500 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2.5 py-0.5 rounded-lg bg-white/20 text-white font-mono font-bold text-xs">
                                {{ estudiante.codigo_carrera }}
                            </span>
                            <span class="text-xs font-semibold text-white/90">
                                {{ estudiante.carrera }} ({{ estudiante.total_niveles }} Semestres)
                            </span>
                        </div>
                        <h3 class="text-2xl font-black tracking-tight">
                            {{ estudiante.name }}
                        </h3>
                        <p class="text-xs text-white/80 mt-0.5">
                            CI: {{ estudiante.ci }} | Correo: {{ estudiante.email }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4 bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20">
                        <div class="text-center px-3 border-r border-white/20">
                            <div class="text-[10px] uppercase font-bold text-white/80">Aprobadas</div>
                            <div class="text-xl font-black">{{ estudiante.aprobadas_count }}</div>
                        </div>
                        <div class="text-center px-3 border-r border-white/20">
                            <div class="text-[10px] uppercase font-bold text-white/80">En Curso</div>
                            <div class="text-xl font-black">{{ materiasActuales.length }}</div>
                        </div>
                        <div class="text-center px-3">
                            <div class="text-[10px] uppercase font-bold text-white/80">Promedio</div>
                            <div class="text-xl font-black">{{ estudiante.promedio_general }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 1. MATERIAS INSCRITAS EN LA GESTIÓN ACTUAL CON SUS PARCIALES -->
            <div class="bg-white dark:bg-[#111827] rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="pi pi-calendar text-indigo-500"></i>
                        Materias Cursando Actualmente (Gestión {{ gestionActual }})
                    </h3>
                    <span class="text-xs text-slate-400 font-semibold">
                        {{ materiasActuales.length }} materias registradas
                    </span>
                </div>

                <div v-if="materiasActuales.length > 0" class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <th class="py-3 px-4">Sigla / Materia</th>
                                <th class="py-3 px-4">Paralelo</th>
                                <th class="py-3 px-4">Docente</th>
                                <th
                                    v-for="p in periodos"
                                    :key="p.id"
                                    class="py-3 px-4 text-center"
                                >
                                    {{ p.nombre }}
                                    <div class="text-[10px] font-normal lowercase text-slate-400">({{ p.ponderacion }}%)</div>
                                </th>
                                <th class="py-3 px-4 text-center">Nota Final</th>
                                <th class="py-3 px-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            <tr
                                v-for="m in materiasActuales"
                                :key="m.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40"
                            >
                                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                    <span class="font-mono text-pink-600 dark:text-pink-400 mr-1.5">[{{ m.materia_sigla }}]</span>
                                    {{ m.materia_nombre }}
                                    <div class="text-[10px] text-slate-400 font-normal">Semestre/Nivel {{ m.nivel }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-bold">Paralelo {{ m.paralelo }}</td>
                                <td class="py-3.5 px-4 text-slate-600 dark:text-slate-400">{{ m.docente }}</td>

                                <!-- NOTAS POR CADA CICLO -->
                                <td
                                    v-for="p in periodos"
                                    :key="p.id"
                                    class="py-3.5 px-4 text-center font-bold"
                                >
                                    <span v-if="m.calificaciones?.[p.id] !== undefined" class="text-slate-900 dark:text-white">
                                        {{ m.calificaciones[p.id] }}
                                    </span>
                                    <span v-else class="text-slate-400 font-normal italic text-[11px]">
                                        Pendiente
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-center font-black text-indigo-600 dark:text-indigo-400">
                                    {{ m.nota_final !== null ? `${m.nota_final}` : '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <Tag
                                        :severity="m.estado === 'Aprobado' ? 'success' : (m.estado === 'Reprobado' ? 'danger' : 'info')"
                                        :value="m.estado"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-8 text-xs text-slate-400 border border-dashed rounded-xl">
                    No tienes materias inscritas para esta gestión académica. Consulta con Kardex para tu inscripción.
                </div>
            </div>

            <!-- 2. HISTORIAL ACADÉMICO PREVIO -->
            <div class="bg-white dark:bg-[#111827] rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="pi pi-history text-slate-500"></i>
                        Historial Académico de Semestres Aprobados / Cursados
                    </h3>
                </div>

                <div v-if="materiasHistorial.length > 0" class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <th class="py-2.5 px-4">Gestión</th>
                                <th class="py-2.5 px-4">Sigla</th>
                                <th class="py-2.5 px-4">Materia</th>
                                <th class="py-2.5 px-4 text-center">Nivel</th>
                                <th class="py-2.5 px-4 text-center">Nota Final</th>
                                <th class="py-2.5 px-4 text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            <tr v-for="h in materiasHistorial" :key="h.id">
                                <td class="py-2.5 px-4 font-semibold text-slate-500">{{ h.gestion }}</td>
                                <td class="py-2.5 px-4 font-mono font-bold">{{ h.materia_sigla }}</td>
                                <td class="py-2.5 px-4 font-medium">{{ h.materia_nombre }}</td>
                                <td class="py-2.5 px-4 text-center">{{ h.nivel }}º</td>
                                <td class="py-2.5 px-4 text-center font-bold">{{ h.nota_final ?? 'S/N' }}</td>
                                <td class="py-2.5 px-4 text-center">
                                    <Tag
                                        :severity="h.estado === 'Aprobado' ? 'success' : (h.estado === 'Reprobado' ? 'danger' : 'info')"
                                        :value="h.estado"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-6 text-xs text-slate-400 border border-dashed rounded-xl">
                    Sin registros históricos anteriores. Eres un estudiante de primer ingreso.
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
