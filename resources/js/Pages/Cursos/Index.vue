<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';
import axios from 'axios';

const props = defineProps({
    carreraActiva: {
        type: Object,
        required: true,
    },
    carreras: {
        type: Array,
        default: () => [],
    },
    gestionActual: {
        type: String,
        required: true,
    },
    gestionesDisponibles: {
        type: Array,
        default: () => [],
    },
    cursos: {
        type: Array,
        default: () => [],
    },
    docentes: {
        type: Array,
        default: () => [],
    },
});

// Filtros y estados
const filtroNivel = ref('todos');

// Modal de Creación / Edición de Curso (Paralelo)
const modalCursoVisible = ref(false);
const esEdicion = ref(false);
const formCurso = ref({
    id: null,
    materia_id: null,
    gestion: props.gestionActual,
    paralelo: 'A',
    cupo_maximo: 50,
    docente_id: null,
    fecha_inicio_calificacion: '',
    fecha_fin_calificacion: '',
});

// Modal de Asignación Rápida de Docente
const modalAsignarVisible = ref(false);
const cursoParaAsignar = ref(null);
const docenteSeleccionado = ref(null);

// Modal para Crear Nueva Gestión
const modalGestionVisible = ref(false);
const nuevaGestion = ref('');

// Materias agrupadas por nivel
const materiasPorNivel = computed(() => {
    const niveles = {};
    const totalNiveles = props.carreraActiva?.total_niveles || 9;

    for (let i = 1; i <= totalNiveles; i++) {
        niveles[i] = [];
    }

    (props.carreraActiva?.materias || []).forEach((materia) => {
        const nivel = materia.nivel || 1;
        if (!niveles[nivel]) niveles[nivel] = [];

        // Adjuntamos los paralelos abiertos para esta materia en la gestión actual
        const paralelosMateria = props.cursos.filter(c => c.materia_id === materia.id);
        niveles[nivel].push({
            ...materia,
            cursos: paralelosMateria,
        });
    });

    return niveles;
});

// Resumen Estadístico
const totalParalelos = computed(() => props.cursos.length);
const totalCupos = computed(() => props.cursos.reduce((acc, c) => acc + (c.cupo_maximo || 0), 0));
const totalInscritos = computed(() => props.cursos.reduce((acc, c) => acc + (c.inscripciones_count || 0), 0));
const cursosSinDocente = computed(() => props.cursos.filter(c => !c.docente_id).length);

// Navegación de filtros
const cambiarCarrera = (id) => {
    router.get(route('cursos.index', { carrera_id: id, gestion: props.gestionActual }));
};

const cambiarGestion = (gestion) => {
    router.get(route('cursos.index', { carrera_id: props.carreraActiva?.id, gestion }));
};

// Acciones con Modales (Sin vistas extras)
const abrirModalNuevoParalelo = (materia = null) => {
    esEdicion.value = false;
    formCurso.value = {
        id: null,
        materia_id: materia ? materia.id : (props.carreraActiva?.materias?.[0]?.id || null),
        gestion: props.gestionActual,
        paralelo: 'A',
        cupo_maximo: 50,
        docente_id: null,
        fecha_inicio_calificacion: '',
        fecha_fin_calificacion: '',
    };
    modalCursoVisible.value = true;
};

const abrirModalEditarParalelo = (curso) => {
    esEdicion.value = true;
    formCurso.value = {
        id: curso.id,
        materia_id: curso.materia_id,
        gestion: curso.gestion,
        paralelo: curso.paralelo,
        cupo_maximo: curso.cupo_maximo,
        docente_id: curso.docente_id,
        fecha_inicio_calificacion: curso.fecha_inicio_calificacion ? curso.fecha_inicio_calificacion.slice(0, 10) : '',
        fecha_fin_calificacion: curso.fecha_fin_calificacion ? curso.fecha_fin_calificacion.slice(0, 10) : '',
    };
    modalCursoVisible.value = true;
};

const guardarCurso = () => {
    if (esEdicion.value) {
        router.put(route('cursos.update', formCurso.value.id), formCurso.value, {
            onSuccess: () => {
                modalCursoVisible.value = false;
            },
        });
    } else {
        router.post(route('cursos.store'), formCurso.value, {
            onSuccess: () => {
                modalCursoVisible.value = false;
            },
        });
    }
};

const eliminarParalelo = (curso) => {
    if (confirm(`¿Eliminar el Paralelo ${curso.paralelo} de ${curso.materia?.sigla}?`)) {
        router.delete(route('cursos.destroy', curso.id));
    }
};

// Asignación rápida de docente vía Modal / Ajax
const abrirModalAsignarDocente = (curso) => {
    cursoParaAsignar.value = curso;
    docenteSeleccionado.value = curso.docente_id;
    modalAsignarVisible.value = true;
};

const confirmarAsignacionDocente = async () => {
    if (!cursoParaAsignar.value) return;

    try {
        await axios.post(route('cursos.asignar-docente', cursoParaAsignar.value.id), {
            docente_id: docenteSeleccionado.value,
        });
        modalAsignarVisible.value = false;
        // Recargar silenciosamente los datos
        router.reload({ only: ['cursos'] });
    } catch (error) {
        console.error('Error al asignar docente:', error);
    }
};

// Crear nueva gestión semestral
const crearNuevaGestion = () => {
    if (!nuevaGestion.value.trim()) return;
    const gestionTexto = nuevaGestion.value.trim().toUpperCase();
    modalGestionVisible.value = false;
    nuevaGestion.value = '';
    cambiarGestion(gestionTexto);
};
</script>

<template>
    <Head :title="`Cursos y Paralelos - ${gestionActual}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded-lg text-sm border border-indigo-200 dark:border-indigo-800">
                            {{ carreraActiva?.codigo }}
                        </span>
                        <h2 class="text-xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                            Cursos y Asignación Docente
                        </h2>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Apertura de paralelos semestrales, control de cupos y designación de docentes titulares.
                    </p>
                </div>

                <!-- Selectores de Gestión y Carrera -->
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- Selector de Carrera -->
                    <select
                        :value="carreraActiva?.id"
                        @change="cambiarCarrera($event.target.value)"
                        class="text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-1.5 px-3 focus:ring-indigo-500"
                    >
                        <option v-for="c in carreras" :key="c.id" :value="c.id">
                            {{ c.codigo }} - {{ c.nombre }}
                        </option>
                    </select>

                    <!-- Selector de Gestión -->
                    <div class="flex items-center gap-1 bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-700 p-0.5">
                        <select
                            :value="gestionActual"
                            @change="cambiarGestion($event.target.value)"
                            class="text-xs border-0 bg-transparent text-gray-800 dark:text-gray-200 py-1 px-2.5 font-bold focus:ring-0"
                        >
                            <option v-for="g in gestionesDisponibles" :key="g" :value="g">
                                Semestre {{ g }}
                            </option>
                        </select>
                        <button
                            @click="modalGestionVisible = true"
                            class="p-1 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 rounded"
                            title="Aperturar nuevo semestre / gestión"
                        >
                            <i class="pi pi-plus text-xs"></i>
                        </button>
                    </div>

                    <!-- Botón Abrir Paralelo -->
                    <Button
                        icon="pi pi-plus"
                        label="Abrir Paralelo"
                        size="small"
                        severity="primary"
                        @click="abrirModalNuevoParalelo()"
                        class="!bg-gradient-to-r !from-pink-500 !to-rose-600 !border-0 font-bold shadow-md shadow-pink-500/25"
                    />
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- TARJETAS DE MÉTRICAS RÁPIDAS -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-indigo-50 dark:bg-indigo-950 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <i class="pi pi-book text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Paralelos Abiertos</div>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ totalParalelos }}</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <i class="pi pi-users text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Capacidad Total</div>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ totalCupos }} cupos</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="pi pi-user-check text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Estudiantes Inscritos</div>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ totalInscritos }}</div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-xs flex items-center gap-3">
                    <div :class="[
                        'w-10 h-10 rounded-lg flex items-center justify-center',
                        cursosSinDocente > 0 ? 'bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400' : 'bg-green-50 dark:bg-green-950 text-green-600 dark:text-green-400'
                    ]">
                        <i :class="cursosSinDocente > 0 ? 'pi pi-exclamation-triangle' : 'pi pi-check-circle'" class="text-lg"></i>
                    </div>
                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Paralelos sin Docente</div>
                        <div class="text-lg font-bold text-gray-800 dark:text-gray-100">
                            {{ cursosSinDocente }} {{ cursosSinDocente === 1 ? 'vacante' : 'vacantes' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTRO RÁPIDO DE NIVELES -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 border-b border-gray-200 dark:border-gray-700">
                <button
                    @click="filtroNivel = 'todos'"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors',
                        filtroNivel === 'todos'
                            ? 'bg-indigo-600 text-white shadow-xs'
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'
                    ]"
                >
                    Todos los Niveles
                </button>
                <button
                    v-for="nivel in (carreraActiva?.total_niveles || 9)"
                    :key="nivel"
                    @click="filtroNivel = nivel"
                    :class="[
                        'px-3 py-1.5 rounded-lg text-xs font-semibold whitespace-nowrap transition-colors',
                        filtroNivel === nivel
                            ? 'bg-indigo-600 text-white shadow-xs'
                            : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700'
                    ]"
                >
                    {{ nivel }}º Nivel
                </button>
            </div>

            <!-- LISTADO DE MATERIAS Y SUS PARALELOS -->
            <div class="space-y-6">
                <div
                    v-for="nivel in (carreraActiva?.total_niveles || 9)"
                    :key="nivel"
                    v-show="filtroNivel === 'todos' || filtroNivel === nivel"
                    class="space-y-4"
                >
                    <!-- Título de Nivel -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="text-base font-bold text-gray-800 dark:text-gray-100">
                                {{ nivel }}º Nivel / Semestre
                            </h3>
                            <span class="text-xs text-gray-400">
                                ({{ materiasPorNivel[nivel]?.length || 0 }} materias)
                            </span>
                        </div>
                    </div>

                    <!-- Cuadrícula de Materias con sus Paralelos -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div
                            v-for="materia in materiasPorNivel[nivel]"
                            :key="materia.id"
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-xs"
                        >
                            <!-- Cabecera de la Materia -->
                            <div class="flex items-start justify-between gap-3 pb-3 border-b border-gray-100 dark:border-gray-700">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold text-xs text-indigo-600 dark:text-indigo-400">
                                            {{ materia.sigla }}
                                        </span>
                                        <h4 class="font-bold text-sm text-gray-900 dark:text-gray-100">
                                            {{ materia.nombre }}
                                        </h4>
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-0.5">
                                        Nota mín. de aprobación: {{ materia.nota_minima_aprobacion }} pts.
                                    </div>
                                </div>
                                <Button
                                    icon="pi pi-plus"
                                    label="Paralelo"
                                    size="small"
                                    severity="secondary"
                                    outlined
                                    class="!text-xs !py-1 !px-2.5"
                                    @click="abrirModalNuevoParalelo(materia)"
                                />
                            </div>

                            <!-- Lista de Paralelos de la Materia -->
                            <div class="mt-3 space-y-2.5">
                                <div
                                    v-for="curso in materia.cursos"
                                    :key="curso.id"
                                    class="p-3 rounded-lg bg-gray-50/70 dark:bg-gray-900/40 border border-gray-200/80 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:border-indigo-300 dark:hover:border-indigo-600 transition-colors"
                                >
                                    <!-- Identificador de Paralelo y Docente -->
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-xs">
                                            {{ curso.paralelo }}
                                        </div>
                                        <div>
                                            <div class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5">
                                                <span>Paralelo {{ curso.paralelo }}</span>
                                                <Tag
                                                    :value="curso.docente ? 'Asignado' : 'Vacante'"
                                                    :severity="curso.docente ? 'success' : 'warning'"
                                                    class="!text-[9px] !px-1.5 !py-0"
                                                />
                                            </div>

                                            <!-- Docente asignado -->
                                            <div class="text-[11px] mt-0.5">
                                                <button
                                                    v-if="curso.docente"
                                                    @click="abrirModalAsignarDocente(curso)"
                                                    class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium flex items-center gap-1"
                                                    title="Clic para cambiar docente"
                                                >
                                                    <i class="pi pi-user text-[10px]"></i>
                                                    <span>{{ curso.docente.name }}</span>
                                                    <i class="pi pi-pencil text-[9px] opacity-60"></i>
                                                </button>
                                                <button
                                                    v-else
                                                    @click="abrirModalAsignarDocente(curso)"
                                                    class="text-amber-600 dark:text-amber-400 font-medium hover:underline flex items-center gap-1"
                                                >
                                                    <i class="pi pi-user-plus text-[10px]"></i>
                                                    <span>+ Asignar Docente Titular</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cupos y Barra de Ocupación -->
                                    <div class="sm:text-right shrink-0 min-w-[130px]">
                                        <div class="text-[11px] font-semibold text-gray-700 dark:text-gray-300">
                                            {{ curso.inscripciones_count }} / {{ curso.cupo_maximo }} estudiantes
                                        </div>
                                        <ProgressBar
                                            :value="Math.min(100, Math.round((curso.inscripciones_count / curso.cupo_maximo) * 100))"
                                            :showValue="false"
                                            class="!h-1.5 !mt-1"
                                        />
                                        <div class="text-[10px] text-gray-400 mt-0.5">
                                            {{ Math.max(0, curso.cupo_maximo - curso.inscripciones_count) }} cupos libres
                                        </div>
                                    </div>

                                    <!-- Acciones del Paralelo -->
                                    <div class="flex items-center gap-1 sm:border-l sm:border-gray-200 sm:dark:border-gray-700 sm:pl-2 shrink-0">
                                        <button
                                            @click="abrirModalEditarParalelo(curso)"
                                            class="p-1.5 rounded text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-200 dark:hover:bg-gray-700"
                                            title="Editar cupo o fechas"
                                        >
                                            <i class="pi pi-cog text-xs"></i>
                                        </button>
                                        <button
                                            @click="eliminarParalelo(curso)"
                                            class="p-1.5 rounded text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-200 dark:hover:bg-gray-700"
                                            title="Eliminar paralelo"
                                        >
                                            <i class="pi pi-trash text-xs"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Sin paralelos abiertos aún -->
                                <div
                                    v-if="!materia.cursos?.length"
                                    class="p-3 rounded-lg border border-dashed border-gray-200 dark:border-gray-700 text-center text-xs text-gray-400"
                                >
                                    <span>No hay paralelos habilitados para este semestre.</span>
                                    <button
                                        @click="abrirModalNuevoParalelo(materia)"
                                        class="ml-1 text-indigo-600 dark:text-indigo-400 font-semibold hover:underline"
                                    >
                                        + Habilitar Paralelo A
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL 1: ABRIR O EDITAR PARALELO                                  -->
        <!-- ================================================================= -->
        <Dialog
            v-model:visible="modalCursoVisible"
            modal
            :header="esEdicion ? 'Editar Paralelo de Curso' : 'Apertura de Nuevo Paralelo'"
            :style="{ width: '480px' }"
            class="p-fluid"
        >
            <form @submit.prevent="guardarCurso" class="space-y-4 pt-2">
                <!-- Materia -->
                <div v-if="!esEdicion">
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Materia *
                    </label>
                    <select
                        v-model="formCurso.materia_id"
                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-2 px-3 focus:ring-indigo-500"
                        required
                    >
                        <option v-for="m in carreraActiva?.materias" :key="m.id" :value="m.id">
                            {{ m.nivel }}º Nivel - {{ m.sigla }}: {{ m.nombre }}
                        </option>
                    </select>
                </div>

                <!-- Paralelo y Cupo Máximo -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Paralelo (Letra) *
                        </label>
                        <InputText
                            v-model="formCurso.paralelo"
                            placeholder="ej: A, B, C"
                            class="w-full uppercase font-bold"
                            maxlength="5"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Cupo Máximo *
                        </label>
                        <InputNumber
                            v-model="formCurso.cupo_maximo"
                            :min="1"
                            :max="500"
                            showButtons
                            class="w-full font-bold"
                            required
                        />
                    </div>
                </div>

                <!-- Asignar Docente -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Docente Titular (Opcional)
                    </label>
                    <select
                        v-model="formCurso.docente_id"
                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-2 px-3 focus:ring-indigo-500"
                    >
                        <option :value="null">-- Dejar vacante por ahora --</option>
                        <option v-for="d in docentes" :key="d.id" :value="d.id">
                            {{ d.name }} ({{ d.email }})
                        </option>
                    </select>
                    <small class="text-[11px] text-gray-400">
                        Un docente puede dictar en varios niveles distintos sin repetir el mismo paralelo.
                    </small>
                </div>

                <!-- Fechas de Calificación -->
                <div class="grid grid-cols-2 gap-3 pt-1 border-t border-gray-100 dark:border-gray-700">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Inicio Calificación
                        </label>
                        <input
                            type="date"
                            v-model="formCurso.fecha_inicio_calificacion"
                            class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-1.5 px-3"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Cierre Calificación
                        </label>
                        <input
                            type="date"
                            v-model="formCurso.fecha_fin_calificacion"
                            class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-1.5 px-3"
                        />
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <Button
                        label="Cancelar"
                        severity="secondary"
                        text
                        @click="modalCursoVisible = false"
                    />
                    <Button
                        :label="esEdicion ? 'Actualizar Paralelo' : 'Habilitar Paralelo'"
                        type="submit"
                        severity="primary"
                    />
                </div>
            </form>
        </Dialog>

        <!-- ================================================================= -->
        <!-- MODAL 2: ASIGNACIÓN RÁPIDA DE DOCENTE                              -->
        <!-- ================================================================= -->
        <Dialog
            v-model:visible="modalAsignarVisible"
            modal
            header="Asignar Docente Titular"
            :style="{ width: '420px' }"
            class="p-fluid"
        >
            <div class="space-y-4 pt-2">
                <div class="p-3 bg-gray-50 dark:bg-gray-900/60 rounded-lg border border-gray-200 dark:border-gray-700 text-xs">
                    <div class="text-gray-400">Materia y Paralelo:</div>
                    <div class="font-bold text-gray-800 dark:text-gray-100">
                        {{ cursoParaAsignar?.materia?.sigla }} - {{ cursoParaAsignar?.materia?.nombre }}
                    </div>
                    <div class="text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">
                        Paralelo {{ cursoParaAsignar?.paralelo }} (Gestión {{ cursoParaAsignar?.gestion }})
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                        Selecciona el Docente:
                    </label>
                    <select
                        v-model="docenteSeleccionado"
                        class="w-full text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-2 px-3 focus:ring-indigo-500"
                    >
                        <option :value="null">-- Dejar sin docente asignado --</option>
                        <option v-for="d in docentes" :key="d.id" :value="d.id">
                            {{ d.name }} ({{ d.email }})
                        </option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <Button
                        label="Cancelar"
                        severity="secondary"
                        text
                        @click="modalAsignarVisible = false"
                    />
                    <Button
                        label="Guardar Asignación"
                        severity="primary"
                        @click="confirmarAsignacionDocente"
                    />
                </div>
            </div>
        </Dialog>

        <!-- ================================================================= -->
        <!-- MODAL 3: APERTURA DE NUEVA GESTIÓN SEMESTRAL                      -->
        <!-- ================================================================= -->
        <Dialog
            v-model:visible="modalGestionVisible"
            modal
            header="Aperturar Nueva Gestión / Semestre"
            :style="{ width: '380px' }"
            class="p-fluid"
        >
            <form @submit.prevent="crearNuevaGestion" class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Código de la Gestión (Semestre) *
                    </label>
                    <InputText
                        v-model="nuevaGestion"
                        placeholder="ej: 2026-II o 2027-I"
                        class="w-full uppercase font-bold"
                        required
                    />
                    <small class="text-[11px] text-gray-500 dark:text-gray-400">
                        Al cambiar a una nueva gestión, los estudiantes que aprobaron en el semestre anterior podrán inscribirse a las materias que desbloquearon.
                    </small>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <Button
                        label="Cancelar"
                        severity="secondary"
                        text
                        @click="modalGestionVisible = false"
                    />
                    <Button
                        label="Abrir Semestre"
                        type="submit"
                        severity="primary"
                    />
                </div>
            </form>
        </Dialog>
    </AuthenticatedLayout>
</template>
