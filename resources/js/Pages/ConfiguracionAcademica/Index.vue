<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
import Card from 'primevue/card';

const props = defineProps({
    carreras: {
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

// Modal de Periodo
const modalPeriodoVisible = ref(false);
const esEdicionPeriodo = ref(false);
const formPeriodo = ref({
    id: null,
    gestion: props.gestionActual,
    numero_parcial: 1,
    nombre: '',
    ponderacion: 33.33,
    fecha_inicio: '',
    fecha_fin: '',
    activo: true,
});

// Formateador de fechas para inputs datetime-local
const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const tzOffset = date.getTimezoneOffset() * 60000;
    const localISOTime = new Date(date.getTime() - tzOffset).toISOString().slice(0, 16);
    return localISOTime;
};

const abrirModalNuevoPeriodo = () => {
    esEdicionPeriodo.value = false;
    const siguienteNum = (props.periodos.length > 0)
        ? Math.max(...props.periodos.map(p => p.numero_parcial)) + 1
        : 1;

    formPeriodo.value = {
        id: null,
        gestion: props.gestionActual,
        numero_parcial: siguienteNum,
        nombre: `${siguienteNum}° Parcial (Ciclo ${siguienteNum})`,
        ponderacion: siguienteNum === 3 ? 40 : 30,
        fecha_inicio: new Date().toISOString().slice(0, 16),
        fecha_fin: new Date(Date.now() + 15 * 86400000).toISOString().slice(0, 16),
        activo: true,
    };
    modalPeriodoVisible.value = true;
};

const abrirModalEditarPeriodo = (periodo) => {
    esEdicionPeriodo.value = true;
    formPeriodo.value = {
        id: periodo.id,
        gestion: periodo.gestion,
        numero_parcial: periodo.numero_parcial,
        nombre: periodo.nombre,
        ponderacion: parseFloat(periodo.ponderacion),
        fecha_inicio: formatDateForInput(periodo.fecha_inicio),
        fecha_fin: formatDateForInput(periodo.fecha_fin),
        activo: periodo.activo,
    };
    modalPeriodoVisible.value = true;
};

const guardarPeriodo = () => {
    if (esEdicionPeriodo.value) {
        router.put(route('configuracion-academica.periodos.update', formPeriodo.value.id), formPeriodo.value, {
            onSuccess: () => {
                modalPeriodoVisible.value = false;
            },
        });
    } else {
        router.post(route('configuracion-academica.periodos.store'), formPeriodo.value, {
            onSuccess: () => {
                modalPeriodoVisible.value = false;
            },
        });
    }
};

const eliminarPeriodo = (periodo) => {
    if (confirm(`¿Estás seguro de eliminar el periodo "${periodo.nombre}"?`)) {
        router.delete(route('configuracion-academica.periodos.destroy', periodo.id));
    }
};

// Edición rápida de niveles de carrera
const formNiveles = ref({});
props.carreras.forEach(c => {
    formNiveles.value[c.id] = c.total_niveles;
});

const guardarNivelesCarrera = (carrera) => {
    const total = formNiveles.value[carrera.id];
    router.put(route('configuracion-academica.carreras.update-niveles', carrera.id), {
        total_niveles: total,
    });
};
</script>

<template>
    <Head title="Configuración Académica" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="pi pi-sliders-h text-pink-500"></i>
                        Configuración Académica
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Definición de semestres por carrera y cronograma de fechas para subida de notas por parciales.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-pink-500/10 text-pink-600 dark:text-pink-400 font-bold text-xs border border-pink-500/20">
                        Gestión Activa: {{ gestionActual }}
                    </span>
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- SECCIÓN 1: PERIODOS / CICLOS DE EVALUACIÓN CON FECHAS HABILITADAS -->
            <div class="bg-white dark:bg-[#111827] rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-800">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="pi pi-calendar-times text-indigo-500"></i>
                            Ciclos y Parciales de Calificación (Gestión {{ gestionActual }})
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Los docentes solo podrán registrar notas en el sistema durante las fechas autorizadas aquí para cada ciclo.
                        </p>
                    </div>
                    <Button
                        icon="pi pi-plus"
                        label="Nuevo Ciclo / Parcial"
                        size="small"
                        @click="abrirModalNuevoPeriodo"
                        class="!bg-gradient-to-r !from-indigo-600 !to-violet-600 !border-0 font-bold shadow-md"
                    />
                </div>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="p in periodos"
                        :key="p.id"
                        class="p-4 rounded-xl border border-slate-200 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/40 hover:border-indigo-500/50 transition-all flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                    Ciclo {{ p.numero_parcial }}
                                </span>
                                <Tag
                                    :severity="p.esta_habilitado ? 'success' : (new Date(p.fecha_inicio) > new Date() ? 'info' : 'danger')"
                                    :value="p.esta_habilitado ? 'Habilitado Hoy' : (new Date(p.fecha_inicio) > new Date() ? 'Próximo' : 'Cerrado')"
                                    rounded
                                />
                            </div>

                            <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100 mb-1">
                                {{ p.nombre }}
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mb-3 font-semibold">
                                Ponderación: <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ p.ponderacion }}%</span>
                            </p>

                            <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-900/60 p-3 rounded-lg border border-slate-100 dark:border-slate-800">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Apertura:</span>
                                    <span class="font-semibold">{{ new Date(p.fecha_inicio).toLocaleString() }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-400">Cierre:</span>
                                    <span class="font-semibold text-rose-500">{{ new Date(p.fecha_fin).toLocaleString() }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-200/80 dark:border-slate-700/50 flex items-center justify-end gap-2">
                            <Button
                                icon="pi pi-pencil"
                                label="Editar Fechas"
                                size="small"
                                text
                                severity="secondary"
                                @click="abrirModalEditarPeriodo(p)"
                            />
                            <Button
                                icon="pi pi-trash"
                                size="small"
                                text
                                severity="danger"
                                @click="eliminarPeriodo(p)"
                            />
                        </div>
                    </div>
                </div>

                <div v-if="periodos.length === 0" class="text-center py-8 text-slate-400 text-xs">
                    No se han configurado ciclos de evaluación para esta gestión. Haz clic en "Nuevo Ciclo / Parcial".
                </div>
            </div>

            <!-- SECCIÓN 2: CANTIDAD DE NIVELES / SEMESTRES POR CARRERA -->
            <div class="bg-white dark:bg-[#111827] rounded-2xl p-6 shadow-sm border border-slate-200 dark:border-slate-800">
                <div class="pb-4 border-b border-slate-100 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="pi pi-sitemap text-pink-500"></i>
                        Semestres y Niveles por Carrera
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Ajusta la cantidad total de semestres (niveles) que tiene la carrera (ej. 8, 9, 10 u 11 semestres). Esto ajusta automáticamente las columnas y la validación en la Malla Curricular.
                    </p>
                </div>

                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <div
                        v-for="c in carreras"
                        :key="c.id"
                        class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[11px] font-black bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                                    {{ c.codigo }}
                                </span>
                                <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100">
                                    {{ c.nombre }}
                                </h4>
                            </div>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Actualmente configurada con <span class="font-bold text-slate-700 dark:text-slate-300">{{ c.total_niveles }} semestres / niveles</span>.
                            </p>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-2">
                                <label class="text-xs text-slate-500 font-semibold">Total Semestres:</label>
                                <input
                                    type="number"
                                    min="1"
                                    max="14"
                                    v-model.number="formNiveles[c.id]"
                                    class="w-20 text-center text-xs font-bold rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 py-1.5 focus:ring-2 focus:ring-pink-500"
                                />
                            </div>
                            <Button
                                icon="pi pi-check"
                                label="Guardar"
                                size="small"
                                severity="primary"
                                @click="guardarNivelesCarrera(c)"
                                class="!bg-pink-600 hover:!bg-pink-700 !border-0 font-bold"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CREAR / EDITAR PERIODO -->
        <Dialog
            v-model:visible="modalPeriodoVisible"
            :header="esEdicionPeriodo ? 'Editar Ciclo / Parcial' : 'Nuevo Ciclo de Calificación'"
            modal
            class="w-full max-w-lg"
        >
            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Nombre del Ciclo</label>
                    <InputText
                        v-model="formPeriodo.nombre"
                        placeholder="Ej. 1er Parcial, 2do Parcial, Examen Final"
                        class="w-full"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Número de Parcial</label>
                        <InputNumber
                            v-model="formPeriodo.numero_parcial"
                            :min="1"
                            :max="10"
                            class="w-full"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Ponderación (%)</label>
                        <InputNumber
                            v-model="formPeriodo.ponderacion"
                            :min="0"
                            :max="100"
                            suffix="%"
                            class="w-full"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Fecha y Hora de Apertura</label>
                        <input
                            type="datetime-local"
                            v-model="formPeriodo.fecha_inicio"
                            class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Fecha y Hora de Cierre</label>
                        <input
                            type="datetime-local"
                            v-model="formPeriodo.fecha_fin"
                            class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2"
                        />
                    </div>
                </div>
            </div>

            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="modalPeriodoVisible = false" />
                <Button label="Guardar Ciclo" icon="pi pi-check" severity="primary" @click="guardarPeriodo" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
