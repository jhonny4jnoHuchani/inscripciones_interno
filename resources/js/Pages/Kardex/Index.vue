<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Card from 'primevue/card';

const props = defineProps({
    estudiantes: {
        type: Array,
        default: () => [],
    },
    carreras: {
        type: Array,
        default: () => [],
    },
    carreraActivaId: {
        type: Number,
        default: null,
    },
    gestionActual: {
        type: String,
        default: '2026-I',
    },
    cursosDisponibles: {
        type: Array,
        default: () => [],
    },
    filtros: {
        type: Object,
        default: () => ({ buscar: '' }),
    },
});

const busqueda = ref(props.filtros.buscar || '');
const aplicarBusqueda = () => {
    router.get(route('kardex.index'), {
        carrera_id: props.carreraActivaId,
        buscar: busqueda.value,
    }, { preserveState: true, preserveScroll: true });
};

const cambiarCarrera = (id) => {
    router.get(route('kardex.index'), {
        carrera_id: id,
        buscar: busqueda.value,
    });
};

// Modal Ficha Kardex del Estudiante
const estudianteSeleccionado = ref(null);
const modalKardexVisible = ref(false);

const abrirKardex = (estudiante) => {
    estudianteSeleccionado.value = estudiante;
    modalKardexVisible.value = true;
};

// Materias actualmente cursando en la gestión activa
const materiasGestionActual = computed(() => {
    if (!estudianteSeleccionado.value) return [];
    return estudianteSeleccionado.value.inscripciones.filter(
        i => i.gestion === props.gestionActual
    );
});

// Historial académico pasado
const materiasHistorial = computed(() => {
    if (!estudianteSeleccionado.value) return [];
    return estudianteSeleccionado.value.inscripciones.filter(
        i => i.gestion !== props.gestionActual || i.estado !== 'En Curso'
    );
});

// Inscripción manual
const cursoAInscribirId = ref(null);
const inscribiendo = ref(false);

const inscribirCurso = () => {
    if (!cursoAInscribirId.value || !estudianteSeleccionado.value) return;

    inscribiendo.value = true;
    router.post(route('kardex.inscribir', estudianteSeleccionado.value.id), {
        curso_id: cursoAInscribirId.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            cursoAInscribirId.value = null;
            inscribiendo.value = false;
            // Refrescar estudiante seleccionado con los datos actualizados
            const actualizado = props.estudiantes.find(e => e.id === estudianteSeleccionado.value.id);
            if (actualizado) estudianteSeleccionado.value = actualizado;
        },
        onError: () => {
            inscribiendo.value = false;
        },
    });
};

const desinscribir = (inscripcionId) => {
    if (!estudianteSeleccionado.value) return;
    if (confirm('¿Estás seguro de retirar esta materia del estudiante?')) {
        router.delete(route('kardex.desinscribir', [estudianteSeleccionado.value.id, inscripcionId]), {
            preserveScroll: true,
            onSuccess: () => {
                const actualizado = props.estudiantes.find(e => e.id === estudianteSeleccionado.value.id);
                if (actualizado) estudianteSeleccionado.value = actualizado;
            },
        });
    }
};
</script>

<template>
    <Head title="Kardex Académico" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="pi pi-id-card text-pink-500"></i>
                        Kardex Académico
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Historial escolar de estudiantes regulares, materias inscritas y gestión manual de inscripciones.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div v-if="carreras.length > 1" class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-500 uppercase">Carrera:</label>
                        <select
                            :value="carreraActivaId"
                            @change="cambiarCarrera($event.target.value)"
                            class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 py-1.5 px-3 font-semibold shadow-xs"
                        >
                            <option v-for="c in carreras" :key="c.id" :value="c.id">
                                {{ c.codigo }} - {{ c.nombre }}
                            </option>
                        </select>
                    </div>

                    <span class="px-3 py-1.5 rounded-xl bg-pink-500/10 text-pink-600 dark:text-pink-400 font-bold text-xs border border-pink-500/20">
                        Gestión {{ gestionActual }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- BUSCADOR Y FILTROS -->
            <div class="bg-white dark:bg-[#111827] rounded-2xl p-4 shadow-sm border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="relative flex-1 max-w-md">
                    <i class="pi pi-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <InputText
                        v-model="busqueda"
                        placeholder="Buscar por Nombre, CI o Correo..."
                        class="w-full !pl-8 !text-xs"
                        @keydown.enter="aplicarBusqueda"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        label="Buscar"
                        icon="pi pi-search"
                        size="small"
                        severity="secondary"
                        @click="aplicarBusqueda"
                    />
                </div>
            </div>

            <!-- TABLA DE ESTUDIANTES REGULARES -->
            <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="pi pi-users text-indigo-500"></i>
                        Estudiantes Registrados ({{ estudiantes.length }})
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <th class="py-3 px-4">Estudiante</th>
                                <th class="py-3 px-4">CI</th>
                                <th class="py-3 px-4">Carrera</th>
                                <th class="py-3 px-4 text-center">Materias Aprobadas</th>
                                <th class="py-3 px-4 text-center">En Curso ({{ gestionActual }})</th>
                                <th class="py-3 px-4 text-center">Promedio General</th>
                                <th class="py-3 px-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            <tr
                                v-for="est in estudiantes"
                                :key="est.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ est.name }}
                                    <div class="text-[11px] font-normal text-slate-400">{{ est.email }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold">{{ est.ci }}</td>
                                <td class="py-3.5 px-4">{{ est.carrera || 'Sin Asignar' }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                        {{ est.aprobadas_count }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[11px] font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                        {{ est.en_curso_count }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    {{ est.promedio_general > 0 ? `${est.promedio_general} / 100` : 'S/N' }}
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <Button
                                        icon="pi pi-file-edit"
                                        label="Ver Kardex"
                                        size="small"
                                        severity="info"
                                        @click="abrirKardex(est)"
                                        class="!text-xs font-bold"
                                    />
                                </td>
                            </tr>
                            <tr v-if="estudiantes.length === 0">
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    No se encontraron estudiantes registrados en esta carrera.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL DETALLE DE KARDEX E INSCRIPCIÓN MANUAL -->
        <Dialog
            v-model:visible="modalKardexVisible"
            :header="`Kardex Académico: ${estudianteSeleccionado?.name || ''}`"
            modal
            class="w-full max-w-4xl"
        >
            <div v-if="estudianteSeleccionado" class="space-y-6 pt-2">
                <!-- RESUMEN DEL ESTUDIANTE -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 dark:bg-slate-800/60 p-4 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold">CI:</span>
                        <div class="font-bold text-xs">{{ estudianteSeleccionado.ci }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Carrera:</span>
                        <div class="font-bold text-xs">{{ estudianteSeleccionado.carrera }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Aprobadas:</span>
                        <div class="font-bold text-xs text-emerald-600">{{ estudianteSeleccionado.aprobadas_count }} materias</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold">Promedio:</span>
                        <div class="font-black text-xs text-indigo-600">{{ estudianteSeleccionado.promedio_general }} / 100</div>
                    </div>
                </div>

                <!-- SECCIÓN 1: MATERIAS EN CURSO (GESTIÓN ACTUAL) -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="pi pi-calendar text-indigo-500"></i>
                            Materias Inscritas (Gestión {{ gestionActual }})
                        </h4>
                    </div>

                    <div v-if="materiasGestionActual.length > 0" class="divide-y divide-slate-100 dark:divide-slate-800 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                        <div
                            v-for="m in materiasGestionActual"
                            :key="m.id"
                            class="p-3 bg-white dark:bg-slate-900/60 flex items-center justify-between text-xs"
                        >
                            <div>
                                <span class="font-mono font-bold text-pink-600 dark:text-pink-400 mr-2">[{{ m.sigla }}]</span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ m.materia }}</span>
                                <span class="ml-2 text-slate-400">(Paralelo {{ m.paralelo }})</span>
                                <Tag severity="info" value="En Curso" class="ml-2 !text-[10px]" />
                            </div>
                            <Button
                                icon="pi pi-times"
                                label="Retirar"
                                severity="danger"
                                text
                                size="small"
                                @click="desinscribir(m.id)"
                            />
                        </div>
                    </div>
                    <div v-else class="text-xs text-slate-400 p-4 border border-dashed rounded-xl text-center">
                        El estudiante no tiene materias inscritas para la gestión activa {{ gestionActual }}.
                    </div>
                </div>

                <!-- SECCIÓN 2: INSCRIBIR NUEVA MATERIA MANUALMENTE -->
                <div class="bg-indigo-50/50 dark:bg-indigo-950/20 p-4 rounded-xl border border-indigo-200 dark:border-indigo-900/40">
                    <h4 class="font-bold text-xs text-indigo-950 dark:text-indigo-200 mb-2 flex items-center gap-2">
                        <i class="pi pi-plus-circle text-indigo-600"></i>
                        Inscripción Manual de Materia (Kardex)
                    </h4>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">
                        El sistema validará automáticamente que el estudiante cumpla todos los pre-requisitos y que el paralelo cuente con cupos disponibles.
                    </p>

                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <select
                            v-model="cursoAInscribirId"
                            class="flex-1 text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2.5"
                        >
                            <option :value="null">-- Seleccionar Materia y Paralelo --</option>
                            <option
                                v-for="c in cursosDisponibles"
                                :key="c.id"
                                :value="c.id"
                                :disabled="c.cupos_disponibles <= 0"
                            >
                                [Nivel {{ c.nivel }}] {{ c.materia_sigla }} - {{ c.materia_nombre }} (Paralelo {{ c.paralelo }}) - Cupos: {{ c.cupos_disponibles }}/{{ c.cupo_maximo }} - Docente: {{ c.docente }}
                            </option>
                        </select>
                        <Button
                            label="Inscribir"
                            icon="pi pi-check"
                            size="small"
                            severity="primary"
                            :disabled="!cursoAInscribirId || inscribiendo"
                            :loading="inscribiendo"
                            @click="inscribirCurso"
                            class="!bg-indigo-600 font-bold"
                        />
                    </div>
                </div>

                <!-- SECCIÓN 3: HISTORIAL ACADÉMICO HISTÓRICO -->
                <div>
                    <h4 class="font-bold text-sm text-slate-900 dark:text-white mb-2 flex items-center gap-2">
                        <i class="pi pi-history text-slate-500"></i>
                        Historial Académico Previo
                    </h4>

                    <div v-if="materiasHistorial.length > 0" class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 font-bold uppercase">
                                <tr>
                                    <th class="p-2.5">Gestión</th>
                                    <th class="p-2.5">Sigla</th>
                                    <th class="p-2.5">Materia</th>
                                    <th class="p-2.5 text-center">Nota Final</th>
                                    <th class="p-2.5 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="h in materiasHistorial" :key="h.id">
                                    <td class="p-2.5 font-semibold text-slate-500">{{ h.gestion }}</td>
                                    <td class="p-2.5 font-mono font-bold">{{ h.sigla }}</td>
                                    <td class="p-2.5 font-medium">{{ h.materia }}</td>
                                    <td class="p-2.5 text-center font-bold">{{ h.nota_final ?? 'S/N' }}</td>
                                    <td class="p-2.5 text-center">
                                        <Tag
                                            :severity="h.estado === 'Aprobado' ? 'success' : (h.estado === 'Reprobado' ? 'danger' : 'info')"
                                            :value="h.estado"
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-xs text-slate-400 p-4 border border-dashed rounded-xl text-center">
                        Sin historial previo registrado.
                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="Cerrar" text severity="secondary" @click="modalKardexVisible = false" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
