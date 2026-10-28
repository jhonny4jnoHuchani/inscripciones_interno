<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Card from 'primevue/card';

const props = defineProps({
    cursos: {
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

const cursoSeleccionado = ref(props.cursos[0] || null);
const periodoActivoId = ref(
    props.periodos.find(p => p.esta_habilitado)?.id || (props.periodos[0] ? props.periodos[0].id : null)
);

const periodoActivo = computed(() => {
    return props.periodos.find(p => p.id === periodoActivoId.value);
});

// Modelo local reactivo para notas
const notasForm = ref({});

const inicializarNotas = () => {
    if (!cursoSeleccionado.value || !periodoActivoId.value) return;

    notasForm.value = {};
    cursoSeleccionado.value.estudiantes.forEach(est => {
        const notaExistente = est.calificaciones?.[periodoActivoId.value];
        notasForm.value[est.inscripcion_id] = notaExistente !== undefined ? notaExistente : 0;
    });
};

// Inicializar cuando se selecciona un curso o periodo
inicializarNotas();

const seleccionarCurso = (curso) => {
    cursoSeleccionado.value = curso;
    inicializarNotas();
};

const cambiarPeriodo = (periodoId) => {
    periodoActivoId.value = periodoId;
    inicializarNotas();
};

const guardando = ref(false);

const guardarNotasCiclo = () => {
    if (!cursoSeleccionado.value || !periodoActivoId.value) return;

    const payload = {
        periodo_evaluacion_id: periodoActivoId.value,
        notas: Object.keys(notasForm.value).map(insId => ({
            inscripcion_id: parseInt(insId),
            nota: parseFloat(notasForm.value[insId] || 0),
        })),
    };

    guardando.value = true;
    router.post(route('mis-cursos.guardar-notas', cursoSeleccionado.value.id), payload, {
        preserveScroll: true,
        onSuccess: () => {
            guardando.value = false;
        },
        onError: () => {
            guardando.value = false;
        },
    });
};
</script>

<template>
    <Head title="Mis Cursos - Panel Docente" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="pi pi-book text-pink-500"></i>
                        Mis Cursos y Registro de Notas
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Carga docente, listas de alumnos y registro de notas por parciales según cronograma oficial.
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
            <!-- 1. SELECTOR DE CURSOS ASIGNADOS -->
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                    Cursos Dictados en Esta Gestión ({{ cursos.length }})
                </h3>

                <div v-if="cursos.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="c in cursos"
                        :key="c.id"
                        @click="seleccionarCurso(c)"
                        :class="[
                            'p-4 rounded-2xl border transition-all cursor-pointer shadow-sm flex flex-col justify-between',
                            cursoSeleccionado?.id === c.id
                                ? 'bg-white dark:bg-[#111827] border-pink-500 ring-2 ring-pink-500/30 shadow-lg'
                                : 'bg-white/80 dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 hover:border-slate-300'
                        ]"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 rounded text-[11px] font-black font-mono bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20">
                                    {{ c.materia_sigla }}
                                </span>
                                <span class="text-xs font-black px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    Paralelo {{ c.paralelo }}
                                </span>
                            </div>

                            <h4 class="font-bold text-sm text-slate-900 dark:text-white leading-snug">
                                {{ c.materia_nombre }}
                            </h4>
                            <p class="text-xs text-slate-400 mt-1">
                                {{ c.carrera }} - Semestre {{ c.nivel }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-xs">
                            <span class="text-slate-500">Inscritos:</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                {{ c.inscritos_count }} / {{ c.cupo_maximo }} alumnos
                            </span>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white dark:bg-[#111827] p-8 rounded-2xl text-center border border-dashed text-xs text-slate-400">
                    No tienes cursos asignados como docente para la gestión {{ gestionActual }}.
                </div>
            </div>

            <!-- 2. GESTOR DE CALIFICACIONES DEL CURSO SELECCIONADO -->
            <div v-if="cursoSeleccionado" class="bg-white dark:bg-[#111827] rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-6">
                <!-- CABECERA DEL CURSO Y CRONOGRAMA DE CICLOS -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-1 rounded-lg text-xs font-mono font-black bg-pink-500/10 text-pink-600 dark:text-pink-400">
                                {{ cursoSeleccionado.materia_sigla }} - Paralelo {{ cursoSeleccionado.paralelo }}
                            </span>
                            <h3 class="text-lg font-black text-slate-900 dark:text-white">
                                {{ cursoSeleccionado.materia_nombre }}
                            </h3>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Listado oficial de estudiantes matriculados ({{ cursoSeleccionado.estudiantes.length }} alumnos).
                        </p>
                    </div>

                    <!-- SELECTOR DE CICLO / PARCIAL -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-for="p in periodos"
                            :key="p.id"
                            @click="cambiarPeriodo(p.id)"
                            :class="[
                                'px-3 py-1.5 rounded-xl text-xs font-bold transition-all border flex items-center gap-1.5',
                                periodoActivoId === p.id
                                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-md'
                                    : 'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-100'
                            ]"
                        >
                            <span>{{ p.nombre }}</span>
                            <span
                                :class="[
                                    'w-2 h-2 rounded-full',
                                    p.esta_habilitado ? 'bg-emerald-400' : 'bg-rose-400'
                                ]"
                                :title="p.estado_texto"
                            ></span>
                        </button>
                    </div>
                </div>

                <!-- ALERTA DE ESTADO DEL PERIODO SELECCIONADO -->
                <div
                    v-if="periodoActivo"
                    :class="[
                        'p-4 rounded-xl border flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs',
                        periodoActivo.esta_habilitado
                            ? 'bg-emerald-50/70 dark:bg-emerald-950/30 border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200'
                            : 'bg-amber-50/70 dark:bg-amber-950/30 border-amber-200 dark:border-amber-800 text-amber-900 dark:text-amber-200'
                    ]"
                >
                    <div class="flex items-center gap-2.5">
                        <i :class="[periodoActivo.esta_habilitado ? 'pi pi-check-circle text-emerald-600' : 'pi pi-lock text-amber-600', 'text-base']"></i>
                        <div>
                            <span class="font-bold">Ciclo Seleccionado: {{ periodoActivo.nombre }} (Ponderación: {{ periodoActivo.ponderacion }}%)</span>
                            <div class="text-[11px] opacity-90 mt-0.5">
                                {{ periodoActivo.estado_texto }} | Plazo de Registro: Del {{ new Date(periodoActivo.fecha_inicio).toLocaleDateString() }} al {{ new Date(periodoActivo.fecha_fin).toLocaleDateString() }}
                            </div>
                        </div>
                    </div>

                    <div v-if="!periodoActivo.esta_habilitado" class="font-bold text-rose-600 dark:text-rose-400">
                        Edición Bloqueada Fuera de Fecha
                    </div>
                </div>

                <!-- TABLA DE ESTUDIANTES Y REGISTRO DE NOTAS -->
                <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/60 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <th class="py-3 px-4">Estudiante</th>
                                <th class="py-3 px-4">CI</th>
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
                                v-for="est in cursoSeleccionado.estudiantes"
                                :key="est.inscripcion_id"
                                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40"
                            >
                                <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ est.name }}
                                    <div class="text-[10px] font-normal text-slate-400">{{ est.email }}</div>
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold">{{ est.ci }}</td>

                                <!-- COLUMNAS DE PARCIALES -->
                                <td
                                    v-for="p in periodos"
                                    :key="p.id"
                                    class="py-3 px-4 text-center"
                                >
                                    <!-- Si es el periodo activo y está habilitado por fecha: Input para editar -->
                                    <div v-if="p.id === periodoActivoId && p.esta_habilitado" class="flex justify-center">
                                        <input
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.5"
                                            v-model.number="notasForm[est.inscripcion_id]"
                                            class="w-20 text-center font-bold text-xs rounded-lg border border-indigo-300 dark:border-indigo-700 bg-indigo-50/30 dark:bg-indigo-950/40 text-slate-900 dark:text-white py-1 focus:ring-2 focus:ring-indigo-500"
                                        />
                                    </div>
                                    <!-- De lo contrario: solo lectura -->
                                    <div v-else class="font-bold text-slate-600 dark:text-slate-400">
                                        {{ est.calificaciones?.[p.id] !== undefined ? `${est.calificaciones[p.id]}` : '—' }}
                                    </div>
                                </td>

                                <td class="py-3 px-4 text-center font-black text-slate-900 dark:text-white">
                                    {{ est.nota_final !== null ? est.nota_final : '—' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <Tag
                                        :severity="est.estado === 'Aprobado' ? 'success' : (est.estado === 'Reprobado' ? 'danger' : 'info')"
                                        :value="est.estado"
                                    />
                                </td>
                            </tr>

                            <tr v-if="cursoSeleccionado.estudiantes.length === 0">
                                <td :colspan="4 + periodos.length" class="text-center py-8 text-slate-400">
                                    No hay estudiantes inscritos en este paralelo aún.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- BOTÓN GUARDAR NOTAS (SOLO SI EL PERIODO ESTÁ HABILITADO) -->
                <div class="flex items-center justify-between pt-2">
                    <span class="text-xs text-slate-400">
                        * Al guardar las notas del parcial, el sistema recalcula la nota final ponderada automáticamente.
                    </span>

                    <Button
                        v-if="periodoActivo?.esta_habilitado"
                        icon="pi pi-save"
                        :label="`Guardar Notas de ${periodoActivo.nombre}`"
                        size="small"
                        severity="primary"
                        :loading="guardando"
                        @click="guardarNotasCiclo"
                        class="!bg-indigo-600 font-bold shadow-md"
                    />
                    <Button
                        v-else
                        icon="pi pi-lock"
                        label="Periodo Cerrado (No se pueden guardar notas)"
                        size="small"
                        disabled
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
