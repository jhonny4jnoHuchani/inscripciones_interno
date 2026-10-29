<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import ProgressBar from 'primevue/progressbar';

const props = defineProps({
    postulantes: {
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
    cursosPrimerSemestre: {
        type: Array,
        default: () => [],
    },
    permisos: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => [],
    },
    metricas: {
        type: Object,
        default: () => ({
            total: 0,
            admitidos: 0,
            no_admitidos: 0,
            pendientes: 0,
            tasa_aprobacion: 0,
        }),
    },
    filtros: {
        type: Object,
        default: () => ({
            estado: '',
            buscar: '',
        }),
    },
});

const page = usePage();
const esAdmin = computed(() => {
    const rol = page.props.auth?.user?.role || (page.props.auth?.user?.roles && page.props.auth?.user?.roles[0]) || '';
    return rol === 'Administrador';
});

// Filtros reactivos
const busqueda = ref(props.filtros.buscar || '');
const filtroEstado = ref(props.filtros.estado || '');

const postulantesFiltrados = computed(() => {
    return props.postulantes.filter((p) => {
        const coincideEstado = !filtroEstado.value || p.estado_admision === filtroEstado.value;
        const texto = busqueda.value.toLowerCase().trim();
        const coincideTexto =
            !texto ||
            p.name.toLowerCase().includes(texto) ||
            (p.ci && p.ci.toLowerCase().includes(texto)) ||
            (p.email && p.email.toLowerCase().includes(texto));
        return coincideEstado && coincideTexto;
    });
});

// Modal 1: Registrar Postulante
const modalPostulanteVisible = ref(false);
const guardandoPostulante = ref(false);
const formPostulante = ref({
    name: '',
    ci: '',
    email: '',
    carrera_id: props.carreraActivaId || (props.carreras[0] ? props.carreras[0].id : null),
    modalidad_admision: 'Examen de Admisión',
    nota_admision: null,
});

const abrirModalPostulante = () => {
    formPostulante.value = {
        name: '',
        ci: '',
        email: '',
        carrera_id: props.carreraActivaId || (props.carreras[0] ? props.carreras[0].id : null),
        modalidad_admision: 'Examen de Admisión',
        nota_admision: null,
    };
    modalPostulanteVisible.value = true;
};

const guardarPostulante = () => {
    if (!formPostulante.value.name || !formPostulante.value.ci || !formPostulante.value.email) {
        alert('Por favor complete el nombre, CI y correo electrónico.');
        return;
    }
    guardandoPostulante.value = true;
    router.post(route('admisiones.postulantes.store'), formPostulante.value, {
        onSuccess: () => {
            modalPostulanteVisible.value = false;
        },
        onFinish: () => {
            guardandoPostulante.value = false;
        },
    });
};

// Modal 2: Calificar Examen de Admisión
const modalNotaVisible = ref(false);
const guardandoNota = ref(false);
const postulanteSeleccionado = ref(null);
const formNota = ref({
    nota_admision: null,
    modalidad_admision: 'Examen de Admisión',
});

const abrirModalNota = (postulante) => {
    postulanteSeleccionado.value = postulante;
    formNota.value = {
        nota_admision: postulante.nota_admision !== null ? Number(postulante.nota_admision) : null,
        modalidad_admision: postulante.modalidad_admision || 'Examen de Admisión',
    };
    modalNotaVisible.value = true;
};

const guardarNota = () => {
    if (formNota.value.nota_admision === null || formNota.value.nota_admision === undefined) {
        alert('Por favor ingrese la calificación obtenida (0 a 100).');
        return;
    }
    guardandoNota.value = true;
    router.post(
        route('admisiones.nota.store', postulanteSeleccionado.value.id),
        {
            nota_admision: formNota.value.nota_admision,
            modalidad_admision: formNota.value.modalidad_admision,
        },
        {
            onSuccess: () => {
                modalNotaVisible.value = false;
            },
            onFinish: () => {
                guardandoNota.value = false;
            },
        }
    );
};

// Modal 3: Inscripción Manual por Kardex
const modalKardexVisible = ref(false);
const guardandoKardex = ref(false);
const estudianteKardex = ref(null);
const cursosSeleccionados = ref([]);

const materiasPrimerSemestreAgrupadas = computed(() => {
    // Agrupar cursos por materia
    const mapa = {};
    props.cursosPrimerSemestre.forEach((c) => {
        if (!mapa[c.materia_id]) {
            mapa[c.materia_id] = {
                materia_id: c.materia_id,
                materia_nombre: c.materia_nombre,
                materia_sigla: c.materia_sigla,
                paralelos: [],
            };
        }
        mapa[c.materia_id].paralelos.push(c);
    });
    return Object.values(mapa);
});

const abrirModalKardex = (postulante) => {
    if (postulante.estado_admision !== 'Admitido') {
        alert('Este postulante no ha sido admitido aún. Se requiere nota mínima de 61 o Excelencia Académica.');
        return;
    }
    estudianteKardex.value = postulante;
    cursosSeleccionados.value = [];
    modalKardexVisible.value = true;
};

const guardarInscripcionKardex = () => {
    if (cursosSeleccionados.value.length === 0) {
        alert('Seleccione al menos un curso o paralelo para inscribir.');
        return;
    }
    guardandoKardex.value = true;
    router.post(
        route('admisiones.inscribir-manual', estudianteKardex.value.id),
        {
            curso_ids: cursosSeleccionados.value,
        },
        {
            onSuccess: () => {
                modalKardexVisible.value = false;
            },
            onFinish: () => {
                guardandoKardex.value = false;
            },
        }
    );
};

const eliminarInscripcion = (inscripcionId) => {
    if (!confirm('¿Confirma que desea desinscribir esta materia para el estudiante?')) return;
    router.delete(route('admisiones.desinscribir', [estudianteKardex.value.id, inscripcionId]), {
        preserveScroll: true,
        onSuccess: () => {
            // Actualizar lista local de inscripciones
            if (estudianteKardex.value) {
                estudianteKardex.value.inscripciones = estudianteKardex.value.inscripciones.filter(
                    (i) => i.id !== inscripcionId
                );
            }
        },
    });
};

// Modal 4: Gestión de Roles y Permisos Dinámicos con Campo Descripción
const modalRolesPermisosVisible = ref(false);
const tabActivoRoles = ref('roles'); // 'roles' o 'permisos'
const guardandoRol = ref(false);
const guardandoPermiso = ref(false);

const formNuevoRol = ref({
    name: '',
    permissions: [],
});

const formNuevoPermiso = ref({
    name: '',
    descripcion: '',
});

const abrirModalRolesPermisos = () => {
    modalRolesPermisosVisible.value = true;
};

const guardarNuevoRol = () => {
    if (!formNuevoRol.value.name) {
        alert('Ingrese el nombre del rol.');
        return;
    }
    guardandoRol.value = true;
    router.post(route('roles.store'), formNuevoRol.value, {
        onSuccess: () => {
            formNuevoRol.value = { name: '', permissions: [] };
        },
        onFinish: () => {
            guardandoRol.value = false;
        },
    });
};

const guardarNuevoPermiso = () => {
    if (!formNuevoPermiso.value.name || !formNuevoPermiso.value.descripcion) {
        alert('Ingrese tanto el nombre del permiso como su descripción explicativa.');
        return;
    }
    guardandoPermiso.value = true;
    router.post(route('permisos.store'), formNuevoPermiso.value, {
        onSuccess: () => {
            formNuevoPermiso.value = { name: '', descripcion: '' };
        },
        onFinish: () => {
            guardandoPermiso.value = false;
        },
    });
};
</script>

<template>
    <Head title="Admisiones y Kardex Manual" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="pi pi-user-plus text-indigo-600 dark:text-indigo-400"></i>
                        Admisiones, Postulantes y Kardex Manual
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Control de admisión por examen (mínimo 61 pts) y habilitación de inscripción al 1er semestre.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        v-if="esAdmin"
                        label="Roles y Permisos"
                        icon="pi pi-shield"
                        severity="secondary"
                        outlined
                        size="small"
                        @click="abrirModalRolesPermisos"
                        class="shadow-sm"
                    />
                    <Button
                        label="Nuevo Postulante"
                        icon="pi pi-plus"
                        severity="primary"
                        size="small"
                        @click="abrirModalPostulante"
                        class="shadow-md !bg-gradient-to-r !from-pink-500 !to-rose-600 !border-0 font-bold"
                    />
                </div>
            </div>
        </template>

        <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- Flash Message Alerts -->
            <div
                v-if="page.props.flash?.success"
                class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 flex items-center justify-between"
            >
                <div class="flex items-center gap-2">
                    <i class="pi pi-check-circle text-emerald-600 dark:text-emerald-400 text-lg"></i>
                    <span class="text-sm font-medium">{{ page.props.flash.success }}</span>
                </div>
            </div>

            <div
                v-if="page.props.flash?.warning"
                class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 flex items-center justify-between"
            >
                <div class="flex items-center gap-2">
                    <i class="pi pi-exclamation-triangle text-amber-600 dark:text-amber-400 text-lg"></i>
                    <span class="text-sm font-medium">{{ page.props.flash.warning }}</span>
                </div>
            </div>

            <div
                v-if="page.props.errors?.error"
                class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 flex items-center justify-between"
            >
                <div class="flex items-center gap-2">
                    <i class="pi pi-times-circle text-rose-600 dark:text-rose-400 text-lg"></i>
                    <span class="text-sm font-medium">{{ page.props.errors.error }}</span>
                </div>
            </div>

            <!-- Métricas Principales de Admisión -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Postulantes</p>
                        <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ metricas.total }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                        <i class="pi pi-users text-xl"></i>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-emerald-200/80 dark:border-emerald-800/60 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Admitidos (≥ 61)</p>
                        <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1">{{ metricas.admitidos }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <i class="pi pi-check-circle text-xl"></i>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-rose-200/80 dark:border-rose-800/60 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase tracking-wider">No Admitidos (&lt; 61)</p>
                        <p class="text-2xl font-black text-rose-700 dark:text-rose-300 mt-1">{{ metricas.no_admitidos }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <i class="pi pi-times-circle text-xl"></i>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-amber-200/80 dark:border-amber-800/60 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Pendientes</p>
                        <p class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-1">{{ metricas.pendientes }}</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="pi pi-clock text-xl"></i>
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-indigo-200/80 dark:border-indigo-800/60 shadow-sm flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Tasa Aprobación</p>
                        <span class="text-xs font-bold text-gray-500">{{ metricas.tasa_aprobacion }}%</span>
                    </div>
                    <div class="mt-2">
                        <ProgressBar :value="metricas.tasa_aprobacion" :showValue="false" class="h-2 rounded-full" />
                    </div>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-2">Corte aprobatorio: 61 puntos</p>
                </div>
            </div>

            <!-- Filtros y Barra de Búsqueda -->
            <div class="p-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Filtrar:</span>
                    <button
                        type="button"
                        @click="filtroEstado = ''"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all',
                            filtroEstado === ''
                                ? 'bg-indigo-600 text-white shadow-sm'
                                : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                        ]"
                    >
                        Todos ({{ postulantes.length }})
                    </button>
                    <button
                        type="button"
                        @click="filtroEstado = 'Admitido'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all',
                            filtroEstado === 'Admitido'
                                ? 'bg-emerald-600 text-white shadow-sm'
                                : 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100'
                        ]"
                    >
                        Admitidos (≥ 61)
                    </button>
                    <button
                        type="button"
                        @click="filtroEstado = 'No Admitido'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all',
                            filtroEstado === 'No Admitido'
                                ? 'bg-rose-600 text-white shadow-sm'
                                : 'bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 hover:bg-rose-100'
                        ]"
                    >
                        No Admitidos (&lt; 61)
                    </button>
                    <button
                        type="button"
                        @click="filtroEstado = 'Pendiente'"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-semibold transition-all',
                            filtroEstado === 'Pendiente'
                                ? 'bg-amber-600 text-white shadow-sm'
                                : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100'
                        ]"
                    >
                        Pendientes
                    </button>
                </div>

                <div class="w-full sm:w-72">
                    <span class="p-input-icon-left w-full">
                        <InputText
                            v-model="busqueda"
                            placeholder="Buscar por Nombre, CI o Email..."
                            class="w-full text-xs !py-2"
                        />
                    </span>
                </div>
            </div>

            <!-- Tabla de Postulantes y Estudiantes -->
            <div class="rounded-2xl bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/75 dark:bg-gray-800/80 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                <th class="py-3.5 px-4">Postulante / Estudiante</th>
                                <th class="py-3.5 px-4">CI</th>
                                <th class="py-3.5 px-4">Modalidad</th>
                                <th class="py-3.5 px-4 text-center">Nota Examen</th>
                                <th class="py-3.5 px-4 text-center">Estado Admisión</th>
                                <th class="py-3.5 px-4 text-center">Materias 1er Semestre</th>
                                <th class="py-3.5 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-sm">
                            <tr
                                v-for="p in postulantesFiltrados"
                                :key="p.id"
                                class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition-colors"
                            >
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ p.name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ p.email }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-gray-600 dark:text-gray-300">
                                    {{ p.ci || 'Sin CI' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <i class="pi pi-bookmark text-[10px] text-gray-400"></i>
                                        {{ p.modalidad_admision }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span
                                        v-if="p.nota_admision !== null"
                                        :class="[
                                            'font-mono font-bold text-sm px-2.5 py-0.5 rounded-full inline-block',
                                            p.nota_admision >= 61
                                                ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'
                                        ]"
                                    >
                                        {{ p.nota_admision }} / 100
                                    </span>
                                    <span v-else class="text-xs text-gray-400 italic font-mono">
                                        Sin Calificar
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <Tag
                                        v-if="p.estado_admision === 'Admitido'"
                                        value="Admitido"
                                        severity="success"
                                        icon="pi pi-check"
                                    />
                                    <Tag
                                        v-else-if="p.estado_admision === 'No Admitido'"
                                        value="No Admitido"
                                        severity="danger"
                                        icon="pi pi-times"
                                    />
                                    <Tag
                                        v-else
                                        value="Pendiente"
                                        severity="warn"
                                        icon="pi pi-clock"
                                    />
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span
                                        v-if="p.materias_inscritas_count > 0"
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300"
                                    >
                                        <i class="pi pi-book text-[10px]"></i>
                                        {{ p.materias_inscritas_count }} materia(s)
                                    </span>
                                    <span v-else class="text-xs text-gray-400 italic">
                                        Ninguna
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Botón Calificar Examen -->
                                        <Button
                                            icon="pi pi-file-edit"
                                            severity="secondary"
                                            text
                                            rounded
                                            size="small"
                                            @click="abrirModalNota(p)"
                                            title="Asentar o Modificar Nota de Examen (Corte ≥ 61)"
                                        />

                                        <!-- Botón Inscripción Manual Kardex -->
                                        <Button
                                            icon="pi pi-graduation-cap"
                                            :severity="p.estado_admision === 'Admitido' ? 'primary' : 'secondary'"
                                            text
                                            rounded
                                            size="small"
                                            :disabled="p.estado_admision !== 'Admitido'"
                                            @click="abrirModalKardex(p)"
                                            :title="
                                                p.estado_admision === 'Admitido'
                                                    ? 'Inscribir materias manualmente por Kardex (1er Semestre)'
                                                    : 'Requiere estar Admitido (nota ≥ 61) para inscribir materias'
                                            "
                                        />
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="postulantesFiltrados.length === 0">
                                <td colspan="7" class="py-12 text-center text-gray-400 dark:text-gray-500 text-sm">
                                    <i class="pi pi-search text-3xl mb-2 text-gray-300 dark:text-gray-600 block"></i>
                                    No se encontraron postulantes con los filtros seleccionados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL 1: REGISTRAR NUEVO POSTULANTE -->
        <Dialog
            v-model:visible="modalPostulanteVisible"
            modal
            header="Registrar Nuevo Postulante"
            :style="{ width: '90vw', maxWidth: '540px' }"
        >
            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Nombre Completo *</label>
                    <InputText v-model="formPostulante.name" placeholder="Ej: Carlos Gómez Mamani" class="w-full text-sm" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Cédula de Identidad (CI) *</label>
                        <InputText v-model="formPostulante.ci" placeholder="Ej: 9876543" class="w-full text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Carrera *</label>
                        <select
                            v-model="formPostulante.carrera_id"
                            class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white"
                        >
                            <option v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Correo Electrónico *</label>
                    <InputText v-model="formPostulante.email" type="email" placeholder="ejemplo@academico.test" class="w-full text-sm" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Modalidad de Admisión</label>
                        <select
                            v-model="formPostulante.modalidad_admision"
                            class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-gray-900 dark:text-white"
                        >
                            <option value="Examen de Admisión">Examen de Admisión</option>
                            <option value="Excelencia Académica">Excelencia Académica</option>
                            <option value="Traspaso">Traspaso</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div v-if="formPostulante.modalidad_admision !== 'Excelencia Académica'">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">Nota Admisión (Opcional)</label>
                        <InputNumber
                            v-model="formPostulante.nota_admision"
                            :min="0"
                            :max="100"
                            placeholder="Ej: 75.00"
                            class="w-full text-sm"
                        />
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 text-indigo-900 dark:text-indigo-200 text-xs space-y-1">
                    <p class="font-semibold flex items-center gap-1.5">
                        <i class="pi pi-info-circle"></i> Regla de Admisión
                    </p>
                    <p>
                        Si la nota es ≥ 61 o la modalidad es Excelencia Académica, el postulante será formalmente Admitido y obtendrá el rol de Estudiante para poder inscribirse a materias.
                    </p>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Cancelar" severity="secondary" text @click="modalPostulanteVisible = false" />
                    <Button label="Registrar Postulante" icon="pi pi-check" :loading="guardandoPostulante" @click="guardarPostulante" />
                </div>
            </template>
        </Dialog>

        <!-- MODAL 2: ASENTAR NOTA DEL EXAMEN DE ADMISIÓN (REGLA ≥ 61) -->
        <Dialog
            v-model:visible="modalNotaVisible"
            modal
            header="Calificar Examen de Admisión"
            :style="{ width: '90vw', maxWidth: '480px' }"
        >
            <div v-if="postulanteSeleccionado" class="space-y-4 pt-2">
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-750 border border-gray-200 dark:border-gray-700">
                    <p class="font-bold text-gray-900 dark:text-white text-base">{{ postulanteSeleccionado.name }}</p>
                    <p class="text-xs text-gray-500 font-mono mt-0.5">CI: {{ postulanteSeleccionado.ci }} | Modalidad: {{ formNota.modalidad_admision }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-1">
                        Calificación del Examen (Escala 0 a 100) *
                    </label>
                    <InputNumber
                        v-model="formNota.nota_admision"
                        :min="0"
                        :max="100"
                        :minFractionDigits="0"
                        :maxFractionDigits="2"
                        placeholder="Ej: 65.50"
                        class="w-full text-base font-mono font-bold"
                    />
                </div>

                <!-- Live Preview de Admisión -->
                <div
                    v-if="formNota.nota_admision !== null && formNota.nota_admision !== undefined"
                    :class="[
                        'p-4 rounded-xl border text-xs transition-all',
                        formNota.nota_admision >= 61 || formNota.modalidad_admision === 'Excelencia Académica'
                            ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-300 dark:border-emerald-700 text-emerald-900 dark:text-emerald-200'
                            : 'bg-rose-50 dark:bg-rose-950/40 border-rose-300 dark:border-rose-700 text-rose-900 dark:text-rose-200'
                    ]"
                >
                    <div class="flex items-start gap-2.5">
                        <i
                            :class="[
                                'text-lg mt-0.5',
                                formNota.nota_admision >= 61 || formNota.modalidad_admision === 'Excelencia Académica'
                                    ? 'pi pi-check-circle text-emerald-600 dark:text-emerald-400'
                                    : 'pi pi-times-circle text-rose-600 dark:text-rose-400'
                            ]"
                        ></i>
                        <div>
                            <p class="font-bold text-sm">
                                {{
                                    formNota.nota_admision >= 61 || formNota.modalidad_admision === 'Excelencia Académica'
                                        ? 'RESULTADO: ADMITIDO'
                                        : 'RESULTADO: NO ADMITIDO'
                                }}
                            </p>
                            <p class="mt-1">
                                {{
                                    formNota.nota_admision >= 61 || formNota.modalidad_admision === 'Excelencia Académica'
                                        ? 'Supera la nota mínima aprobatoria institucional (≥ 61 pts). El postulante será admitido formalmente, se le asignará el rol de Estudiante y quedará habilitado para inscribirse en 1er semestre.'
                                        : 'La calificación es menor a 61 puntos. El postulante no cumple el requisito y NO se le permitirá la toma de materias.'
                                }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2 pt-2">
                    <Button label="Cancelar" severity="secondary" text @click="modalNotaVisible = false" />
                    <Button label="Asentar Calificación" icon="pi pi-check" :loading="guardandoNota" @click="guardarNota" />
                </div>
            </template>
        </Dialog>

        <!-- MODAL 3: INSCRIPCIÓN MANUAL POR KARDEX (1ER SEMESTRE Y PARALELOS) -->
        <Dialog
            v-model:visible="modalKardexVisible"
            modal
            header="Inscripción Manual por Kardex"
            :style="{ width: '92vw', maxWidth: '780px' }"
        >
            <div v-if="estudianteKardex" class="space-y-5 pt-2">
                <!-- Estudiante Header -->
                <div class="p-4 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <span class="text-xs uppercase font-bold text-indigo-600 dark:text-indigo-400">Estudiante Admitido</span>
                        <h4 class="text-lg font-bold text-gray-900 dark:text-white">{{ estudianteKardex.name }}</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-mono">CI: {{ estudianteKardex.ci }} | Nota Admisión: {{ estudianteKardex.nota_admision }}/100</p>
                    </div>
                    <Tag value="Admitido para Inscripción" severity="success" icon="pi pi-verified" class="self-start sm:self-auto" />
                </div>

                <!-- Materias ya inscritas -->
                <div v-if="estudianteKardex.inscripciones && estudianteKardex.inscripciones.length > 0">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2">Materias Actualmente Asignadas</h5>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div
                            v-for="ins in estudianteKardex.inscripciones"
                            :key="ins.id"
                            class="p-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-between"
                        >
                            <div>
                                <span class="font-bold text-xs text-gray-800 dark:text-gray-200">{{ ins.sigla }} - {{ ins.materia }}</span>
                                <div class="text-[11px] text-gray-500">Paralelo {{ ins.paralelo }} | {{ ins.tipo_registro }}</div>
                            </div>
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                rounded
                                size="small"
                                @click="eliminarInscripcion(ins.id)"
                                title="Desinscribir materia"
                            />
                        </div>
                    </div>
                </div>

                <!-- Selección de Paralelos para 1er Semestre -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Apertura de Paralelos - 1er Semestre (Gestión {{ gestionActual }})
                        </h5>
                        <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">Seleccione los paralelos deseados</span>
                    </div>

                    <div class="space-y-3 max-h-72 overflow-y-auto pr-1">
                        <div
                            v-for="grupo in materiasPrimerSemestreAgrupadas"
                            :key="grupo.materia_id"
                            class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/60"
                        >
                            <div class="font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded bg-indigo-100 dark:bg-indigo-900/60 text-indigo-700 dark:text-indigo-300 font-mono text-xs">
                                    {{ grupo.materia_sigla }}
                                </span>
                                {{ grupo.materia_nombre }}
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <label
                                    v-for="par in grupo.paralelos"
                                    :key="par.id"
                                    :class="[
                                        'p-2.5 rounded-lg border text-xs cursor-pointer transition-all flex flex-col justify-between',
                                        cursosSeleccionados.includes(par.id)
                                            ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-950/60 font-semibold text-indigo-900 dark:text-indigo-200'
                                            : par.cupos_disponibles === 0
                                            ? 'border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-900 opacity-50 cursor-not-allowed'
                                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:border-gray-300'
                                    ]"
                                >
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="checkbox"
                                                :value="par.id"
                                                v-model="cursosSeleccionados"
                                                :disabled="par.cupos_disponibles === 0"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <span class="font-bold text-sm">Paralelo {{ par.paralelo }}</span>
                                        </div>
                                        <span
                                            :class="[
                                                'text-[10px] px-1.5 py-0.5 rounded font-mono',
                                                par.cupos_disponibles > 5
                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                                    : par.cupos_disponibles > 0
                                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                                                    : 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'
                                            ]"
                                        >
                                            {{ par.cupos_disponibles }} cupos
                                        </span>
                                    </div>
                                    <div class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
                                        Docente: {{ par.docente }}
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-between items-center w-full pt-2">
                    <span class="text-xs text-gray-500">
                        {{ cursosSeleccionados.length }} materia(s) seleccionada(s)
                    </span>
                    <div class="flex gap-2">
                        <Button label="Cerrar" severity="secondary" text @click="modalKardexVisible = false" />
                        <Button
                            label="Completar Inscripción Manual"
                            icon="pi pi-check"
                            :loading="guardandoKardex"
                            :disabled="cursosSeleccionados.length === 0"
                            @click="guardarInscripcionKardex"
                        />
                    </div>
                </div>
            </template>
        </Dialog>

        <!-- MODAL 4: ROLES Y PERMISOS DINÁMICOS CON CAMPO DESCRIPCIÓN -->
        <Dialog
            v-model:visible="modalRolesPermisosVisible"
            modal
            header="Roles y Permisos Dinámicos"
            :style="{ width: '92vw', maxWidth: '820px' }"
        >
            <div class="space-y-4 pt-2">
                <!-- Selector de Pestañas -->
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <button
                        type="button"
                        @click="tabActivoRoles = 'roles'"
                        :class="[
                            'py-2 px-4 text-xs font-bold border-b-2 transition-colors',
                            tabActivoRoles === 'roles'
                                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        Roles del Sistema ({{ roles.length }})
                    </button>
                    <button
                        type="button"
                        @click="tabActivoRoles = 'permisos'"
                        :class="[
                            'py-2 px-4 text-xs font-bold border-b-2 transition-colors',
                            tabActivoRoles === 'permisos'
                                ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400'
                                : 'border-transparent text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        Permisos y Descripciones ({{ permisos.length }})
                    </button>
                </div>

                <!-- TAB 1: ROLES -->
                <div v-if="tabActivoRoles === 'roles'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-h-64 overflow-y-auto pr-1">
                        <div
                            v-for="r in roles"
                            :key="r.id"
                            class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-sm text-gray-900 dark:text-white">{{ r.name }}</span>
                                <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-mono">
                                    {{ r.permissions_count }} permisos
                                </span>
                            </div>
                            <div class="mt-2 flex flex-wrap gap-1">
                                <span
                                    v-for="p in r.permissions"
                                    :key="p"
                                    class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-mono"
                                >
                                    {{ p }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario Crear Nuevo Rol -->
                    <div class="p-3.5 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40 space-y-3">
                        <h5 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Crear Nuevo Rol Dinámico
                        </h5>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div class="sm:col-span-2">
                                <InputText
                                    v-model="formNuevoRol.name"
                                    placeholder="Nombre del nuevo Rol (Ej: Encargado de Admisiones)"
                                    class="w-full text-xs"
                                />
                            </div>
                            <Button
                                label="Guardar Rol"
                                icon="pi pi-plus"
                                size="small"
                                :loading="guardandoRol"
                                @click="guardarNuevoRol"
                            />
                        </div>

                        <div>
                            <p class="text-[11px] font-semibold text-gray-500 uppercase mb-1.5">Asignar Permisos Iniciales:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 max-h-36 overflow-y-auto">
                                <label
                                    v-for="perm in permisos"
                                    :key="perm.id"
                                    class="flex items-start gap-2 p-1.5 rounded hover:bg-white dark:hover:bg-gray-700 text-xs cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :value="perm.name"
                                        v-model="formNuevoRol.permissions"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 mt-0.5"
                                    />
                                    <div>
                                        <div class="font-mono font-semibold">{{ perm.name }}</div>
                                        <div class="text-[11px] text-gray-500">{{ perm.descripcion }}</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: PERMISOS CON DESCRIPCIÓN -->
                <div v-if="tabActivoRoles === 'permisos'" class="space-y-4">
                    <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                        <div
                            v-for="perm in permisos"
                            :key="perm.id"
                            class="p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2"
                        >
                            <div>
                                <span class="font-mono font-bold text-xs text-indigo-600 dark:text-indigo-400">
                                    {{ perm.name }}
                                </span>
                                <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">
                                    {{ perm.descripcion || 'Sin descripción asignada.' }}
                                </p>
                            </div>
                            <Tag value="Activo" severity="info" class="self-start sm:self-auto" />
                        </div>
                    </div>

                    <!-- Formulario Crear Nuevo Permiso con Descripción -->
                    <div class="p-3.5 rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40 space-y-3">
                        <h5 class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Registrar Nuevo Permiso con Descripción Explicativa
                        </h5>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase mb-1">Nombre del Permiso (Código)</label>
                                <InputText
                                    v-model="formNuevoPermiso.name"
                                    placeholder="Ej: autorizar_convalidaciones"
                                    class="w-full text-xs font-mono"
                                />
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-600 dark:text-gray-400 uppercase mb-1">Descripción Explicativa</label>
                                <InputText
                                    v-model="formNuevoPermiso.descripcion"
                                    placeholder="Breve detalle de lo que permite este permiso..."
                                    class="w-full text-xs"
                                />
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <Button
                                label="Registrar Permiso"
                                icon="pi pi-check"
                                size="small"
                                :loading="guardandoPermiso"
                                @click="guardarNuevoPermiso"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end pt-2">
                    <Button label="Cerrar" severity="secondary" text @click="modalRolesPermisosVisible = false" />
                </div>
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
