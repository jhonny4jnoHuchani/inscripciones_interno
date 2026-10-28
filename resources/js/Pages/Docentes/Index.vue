<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Card from 'primevue/card';

const props = defineProps({
    docentes: {
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
    filtros: {
        type: Object,
        default: () => ({ buscar: '' }),
    },
});

const busqueda = ref(props.filtros.buscar || '');
const aplicarBusqueda = () => {
    router.get(route('docentes.index'), {
        carrera_id: props.carreraActivaId,
        buscar: busqueda.value,
    }, { preserveState: true, preserveScroll: true });
};

const cambiarCarrera = (id) => {
    router.get(route('docentes.index'), {
        carrera_id: id || undefined,
        buscar: busqueda.value,
    });
};

// Modal Crear / Editar Docente
const modalDocenteVisible = ref(false);
const esEdicionDocente = ref(false);
const formDocente = ref({
    id: null,
    name: '',
    ci: '',
    email: '',
    carrera_id: props.carreraActivaId || (props.carreras[0] ? props.carreras[0].id : null),
    password: '',
});

const abrirModalNuevoDocente = () => {
    esEdicionDocente.value = false;
    formDocente.value = {
        id: null,
        name: '',
        ci: '',
        email: '',
        carrera_id: props.carreraActivaId || (props.carreras[0] ? props.carreras[0].id : null),
        password: '',
    };
    modalDocenteVisible.value = true;
};

const abrirModalEditarDocente = (docente) => {
    esEdicionDocente.value = true;
    formDocente.value = {
        id: docente.id,
        name: docente.name,
        ci: docente.ci,
        email: docente.email,
        carrera_id: docente.carrera_id,
        password: '',
    };
    modalDocenteVisible.value = true;
};

const guardarDocente = () => {
    if (esEdicionDocente.value) {
        router.put(route('docentes.update', formDocente.value.id), formDocente.value, {
            onSuccess: () => {
                modalDocenteVisible.value = false;
            },
        });
    } else {
        router.post(route('docentes.store'), formDocente.value, {
            onSuccess: () => {
                modalDocenteVisible.value = false;
            },
        });
    }
};

const eliminarDocente = (docente) => {
    if (confirm(`¿Estás seguro de eliminar al docente "${docente.name}"?`)) {
        router.delete(route('docentes.destroy', docente.id));
    }
};

// Modal Resumen de Cursos del Docente
const modalCursosVisible = ref(false);
const docenteSeleccionado = ref(null);

const verCursosDocente = (docente) => {
    docenteSeleccionado.value = docente;
    modalCursosVisible.value = true;
};
</script>

<template>
    <Head title="Gestión de Docentes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="pi pi-users text-pink-500"></i>
                        Gestión de Docentes
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Administración del cuerpo docente y seguimiento de materias/paralelos asignados.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-500 uppercase">Carrera:</label>
                        <select
                            :value="carreraActivaId || ''"
                            @change="cambiarCarrera($event.target.value)"
                            class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 py-1.5 px-3 font-semibold shadow-xs"
                        >
                            <option value="">Todas las Carreras</option>
                            <option v-for="c in carreras" :key="c.id" :value="c.id">
                                {{ c.codigo }} - {{ c.nombre }}
                            </option>
                        </select>
                    </div>

                    <Button
                        icon="pi pi-plus"
                        label="Nuevo Docente"
                        size="small"
                        severity="primary"
                        @click="abrirModalNuevoDocente"
                        class="!bg-gradient-to-r !from-pink-500 !to-rose-600 !border-0 font-bold shadow-md"
                    />
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- BUSCADOR -->
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
                <Button
                    label="Buscar"
                    icon="pi pi-search"
                    size="small"
                    severity="secondary"
                    @click="aplicarBusqueda"
                />
            </div>

            <!-- LISTADO DE DOCENTES -->
            <div class="bg-white dark:bg-[#111827] rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="pi pi-briefcase text-indigo-500"></i>
                        Docentes Registrados ({{ docentes.length }})
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200 dark:border-slate-800">
                                <th class="py-3 px-4">Docente</th>
                                <th class="py-3 px-4">CI</th>
                                <th class="py-3 px-4">Carrera Principal</th>
                                <th class="py-3 px-4 text-center">Cursos Gestión ({{ gestionActual }})</th>
                                <th class="py-3 px-4 text-center">Alumnos Atendidos</th>
                                <th class="py-3 px-4 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
                            <tr
                                v-for="d in docentes"
                                :key="d.id"
                                class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors"
                            >
                                <td class="py-3.5 px-4 font-bold text-slate-900 dark:text-white">
                                    {{ d.name }}
                                    <div class="text-[11px] font-normal text-slate-400">{{ d.email }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold">{{ d.ci || 'S/N' }}</td>
                                <td class="py-3.5 px-4">{{ d.carrera || 'Facultad General' }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                        {{ d.cursos_gestion_count }} paralelos
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center font-bold">
                                    {{ d.total_estudiantes }} estudiantes
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1">
                                    <Button
                                        icon="pi pi-list"
                                        label="Materias"
                                        size="small"
                                        severity="info"
                                        text
                                        @click="verCursosDocente(d)"
                                        class="!text-xs font-bold"
                                    />
                                    <Button
                                        icon="pi pi-pencil"
                                        size="small"
                                        severity="secondary"
                                        text
                                        @click="abrirModalEditarDocente(d)"
                                    />
                                    <Button
                                        icon="pi pi-trash"
                                        size="small"
                                        severity="danger"
                                        text
                                        @click="eliminarDocente(d)"
                                    />
                                </td>
                            </tr>
                            <tr v-if="docentes.length === 0">
                                <td colspan="6" class="py-8 text-center text-slate-400">
                                    No se encontraron docentes registrados con los filtros seleccionados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL RESUMEN DE MATERIAS ASIGNADAS AL DOCENTE -->
        <Dialog
            v-model:visible="modalCursosVisible"
            :header="`Materias Asignadas: ${docenteSeleccionado?.name || ''}`"
            modal
            class="w-full max-w-2xl"
        >
            <div v-if="docenteSeleccionado" class="space-y-4 pt-2">
                <div class="flex items-center justify-between text-xs text-slate-500 pb-2 border-b">
                    <div>CI: <span class="font-bold text-slate-800 dark:text-slate-200">{{ docenteSeleccionado.ci }}</span></div>
                    <div>Carrera: <span class="font-bold text-slate-800 dark:text-slate-200">{{ docenteSeleccionado.carrera }}</span></div>
                    <div>Total Cursos Históricos: <span class="font-bold text-slate-800 dark:text-slate-200">{{ docenteSeleccionado.cursos.length }}</span></div>
                </div>

                <div v-if="docenteSeleccionado.cursos.length > 0" class="divide-y divide-slate-100 dark:divide-slate-800 border rounded-xl overflow-hidden">
                    <div
                        v-for="c in docenteSeleccionado.cursos"
                        :key="c.id"
                        class="p-3.5 bg-white dark:bg-slate-900/60 flex items-center justify-between text-xs"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20 font-mono">
                                    {{ c.materia_sigla }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ c.materia_nombre }}</span>
                                <span class="text-slate-400 font-bold">Paralelo {{ c.paralelo }}</span>
                            </div>
                            <div class="text-[11px] text-slate-400 mt-1">
                                Gestión: <span class="font-semibold text-slate-600 dark:text-slate-300">{{ c.gestion }}</span> | Semestre/Nivel: {{ c.nivel }}
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                {{ c.inscritos_count }} / {{ c.cupo_maximo }} inscritos
                            </span>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-6 text-xs text-slate-400 border border-dashed rounded-xl">
                    Este docente aún no tiene materias o paralelos asignados en el sistema.
                </div>
            </div>

            <template #footer>
                <Button label="Cerrar" text severity="secondary" @click="modalCursosVisible = false" />
            </template>
        </Dialog>

        <!-- MODAL CREAR / EDITAR DOCENTE -->
        <Dialog
            v-model:visible="modalDocenteVisible"
            :header="esEdicionDocente ? 'Editar Docente' : 'Registrar Nuevo Docente'"
            modal
            class="w-full max-w-md"
        >
            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Nombre Completo y Título</label>
                    <InputText
                        v-model="formDocente.name"
                        placeholder="Ej. Ing. Juan Pérez Ramos"
                        class="w-full"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Cédula de Identidad (CI)</label>
                        <InputText
                            v-model="formDocente.ci"
                            placeholder="Ej. 1029384"
                            class="w-full"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Carrera Principal</label>
                        <select
                            v-model="formDocente.carrera_id"
                            class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2"
                        >
                            <option v-for="c in carreras" :key="c.id" :value="c.id">
                                {{ c.codigo }} - {{ c.nombre }}
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Correo Electrónico Institucional</label>
                    <InputText
                        v-model="formDocente.email"
                        placeholder="docente@academico.test"
                        type="email"
                        class="w-full"
                    />
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">
                        Contraseña {{ esEdicionDocente ? '(Opcional, dejar vacío para mantener)' : '(Por defecto: password)' }}
                    </label>
                    <InputText
                        v-model="formDocente.password"
                        placeholder="••••••••"
                        type="password"
                        class="w-full"
                    />
                </div>
            </div>

            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="modalDocenteVisible = false" />
                <Button label="Guardar Docente" icon="pi pi-check" severity="primary" @click="guardarDocente" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
