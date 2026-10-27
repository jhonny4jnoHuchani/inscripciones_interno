<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Tag from 'primevue/tag';
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
});

// Referencias del DOM para calcular flechas SVG
const containerRef = ref(null);
const lineasSvg = ref([]);
const svgWidth = ref(2000);
const svgHeight = ref(1000);

// Estado de materias local para reactividad inmediata sin parpadeos
const materiasLocales = ref([...(props.carreraActiva?.materias || [])]);

// Sincronizar si cambian las props
watch(() => props.carreraActiva, (newVal) => {
    materiasLocales.value = [...(newVal?.materias || [])];
    nextTick(() => recalcularFlechas());
}, { deep: true });

// Materia actualmente seleccionada para resaltar flujo
const materiaSeleccionada = ref(null);

// Modo Enlace de Requisitos
const modoEnlace = ref(false);
const materiaOrigenEnlace = ref(null);

// Modal de Creación / Edición
const dialogVisible = ref(false);
const esEdicion = ref(false);
const materiaForm = ref({
    id: null,
    carrera_id: props.carreraActiva?.id,
    nombre: '',
    sigla: '',
    nivel: 1,
    nota_minima_aprobacion: 51,
});

// Niveles organizados en columnas
const totalNiveles = computed(() => props.carreraActiva?.total_niveles || 10);

const materiasPorNivel = computed(() => {
    const agrupadas = {};
    for (let i = 1; i <= totalNiveles.value; i++) {
        agrupadas[i] = [];
    }
    materiasLocales.value.forEach((materia) => {
        const nivel = materia.nivel || 1;
        if (!agrupadas[nivel]) {
            agrupadas[nivel] = [];
        }
        agrupadas[nivel].push(materia);
    });
    return agrupadas;
});

// IDs de requisitos de la materia seleccionada (hacia atrás)
const idsRequisitosSeleccionada = computed(() => {
    if (!materiaSeleccionada.value) return new Set();
    const reqs = new Set();
    const materia = materiasLocales.value.find(m => m.id === materiaSeleccionada.value.id);
    if (materia?.requisitos) {
        materia.requisitos.forEach(r => reqs.add(r.id));
    }
    return reqs;
});

// IDs de materias que tienen a la materia seleccionada como requisito (hacia adelante)
const idsDesbloqueadasSeleccionada = computed(() => {
    if (!materiaSeleccionada.value) return new Set();
    const desbloqueadas = new Set();
    materiasLocales.value.forEach(m => {
        if (m.requisitos?.some(r => r.id === materiaSeleccionada.value.id)) {
            desbloqueadas.add(m.id);
        }
    });
    return desbloqueadas;
});

// Calcular las coordenadas exactas de las flechas SVG entre tarjetas
const recalcularFlechas = () => {
    if (!containerRef.value) return;

    const container = containerRef.value;
    const containerRect = container.getBoundingClientRect();
    const scrollLeft = container.scrollLeft;
    const scrollTop = container.scrollTop;

    // Actualizar dimensiones del SVG para cubrir todo el scroll
    svgWidth.value = Math.max(container.scrollWidth, container.clientWidth);
    svgHeight.value = Math.max(container.scrollHeight, container.clientHeight);

    const lineas = [];

    materiasLocales.value.forEach((materia) => {
        if (!materia.requisitos || materia.requisitos.length === 0) return;

        const elDestino = document.getElementById(`card-materia-${materia.id}`);
        if (!elDestino) return;
        const rectDestino = elDestino.getBoundingClientRect();

        const xDestino = rectDestino.left - containerRect.left + scrollLeft;
        const yDestino = rectDestino.top + (rectDestino.height / 2) - containerRect.top + scrollTop;

        materia.requisitos.forEach((req) => {
            const elOrigen = document.getElementById(`card-materia-${req.id}`);
            if (!elOrigen) return;
            const rectOrigen = elOrigen.getBoundingClientRect();

            const xOrigen = rectOrigen.right - containerRect.left + scrollLeft;
            const yOrigen = rectOrigen.top + (rectOrigen.height / 2) - containerRect.top + scrollTop;

            // Curva de Bézier cúbica suave
            const dx = Math.max(30, (xDestino - xOrigen) / 2);
            const pathData = `M ${xOrigen} ${yOrigen} C ${xOrigen + dx} ${yOrigen}, ${xDestino - dx} ${yDestino}, ${xDestino} ${yDestino}`;

            // Determinar estado de resaltado
            let tipoResaltado = 'normal';
            if (materiaSeleccionada.value) {
                if (materia.id === materiaSeleccionada.value.id) {
                    tipoResaltado = 'requisito-previo'; // Viene de un requisito previo
                } else if (req.id === materiaSeleccionada.value.id) {
                    tipoResaltado = 'desbloqueada'; // Desbloquea a la materia
                } else {
                    tipoResaltado = 'atenuado';
                }
            }

            lineas.push({
                id: `${req.id}-${materia.id}`,
                origenId: req.id,
                destinoId: materia.id,
                d: pathData,
                tipoResaltado,
            });
        });
    });

    lineasSvg.value = lineas;
};

// Eventos de selección e interactividad
const seleccionarMateria = (materia) => {
    if (modoEnlace.value && materiaOrigenEnlace.value) {
        if (materia.id === materiaOrigenEnlace.value.id) {
            cancelarModoEnlace();
            return;
        }
        conectarODesconectarRequisito(materiaOrigenEnlace.value.id, materia.id);
        return;
    }

    if (materiaSeleccionada.value?.id === materia.id) {
        materiaSeleccionada.value = null;
    } else {
        materiaSeleccionada.value = materia;
    }
    nextTick(() => recalcularFlechas());
};

const iniciarModoEnlace = (materia, event) => {
    event.stopPropagation();
    modoEnlace.value = true;
    materiaOrigenEnlace.value = materia;
    materiaSeleccionada.value = materia;
    nextTick(() => recalcularFlechas());
};

const cancelarModoEnlace = () => {
    modoEnlace.value = false;
    materiaOrigenEnlace.value = null;
};

// Conectar / Desconectar requisito en tiempo real con backend
const conectarODesconectarRequisito = async (materiaId, requisitoId) => {
    try {
        const response = await axios.post(route('malla-curricular.toggle-requisito'), {
            materia_id: materiaId,
            requisito_id: requisitoId,
        });

        if (response.data.success) {
            const index = materiasLocales.value.findIndex(m => m.id === materiaId);
            if (index !== -1) {
                materiasLocales.value[index] = response.data.materia;
                if (materiaSeleccionada.value?.id === materiaId) {
                    materiaSeleccionada.value = response.data.materia;
                }
            }
            nextTick(() => recalcularFlechas());
        }
    } catch (error) {
        console.error('Error al actualizar requisito:', error);
    } finally {
        cancelarModoEnlace();
    }
};

// Modales de Materia
const abrirModalNueva = (nivel = 1) => {
    esEdicion.value = false;
    materiaForm.value = {
        id: null,
        carrera_id: props.carreraActiva?.id,
        nombre: '',
        sigla: '',
        nivel: nivel,
        nota_minima_aprobacion: 51,
    };
    dialogVisible.value = true;
};

const abrirModalEditar = (materia, event) => {
    event.stopPropagation();
    esEdicion.value = true;
    materiaForm.value = {
        id: materia.id,
        carrera_id: materia.carrera_id,
        nombre: materia.nombre,
        sigla: materia.sigla,
        nivel: materia.nivel,
        nota_minima_aprobacion: materia.nota_minima_aprobacion,
    };
    dialogVisible.value = true;
};

const guardarMateria = () => {
    if (esEdicion.value) {
        router.put(route('malla-curricular.materias.update', materiaForm.value.id), materiaForm.value, {
            onSuccess: () => {
                dialogVisible.value = false;
            },
        });
    } else {
        router.post(route('malla-curricular.materias.store'), materiaForm.value, {
            onSuccess: () => {
                dialogVisible.value = false;
            },
        });
    }
};

const eliminarMateria = (materia, event) => {
    event.stopPropagation();
    if (confirm(`¿Estás seguro de eliminar "${materia.nombre}" (${materia.sigla}) de la malla curricular?`)) {
        router.delete(route('malla-curricular.materias.destroy', materia.id), {
            onSuccess: () => {
                materiaSeleccionada.value = null;
            },
        });
    }
};

const cambiarCarrera = (id) => {
    router.get(route('malla-curricular.index', { carrera_id: id }));
};

// Lifecycle
onMounted(() => {
    nextTick(() => {
        recalcularFlechas();
    });
    window.addEventListener('resize', recalcularFlechas);
});

onUnmounted(() => {
    window.removeEventListener('resize', recalcularFlechas);
});
</script>

<template>
    <Head :title="`Malla Curricular - ${carreraActiva?.nombre || 'Carrera'}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-1 bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 font-bold rounded-lg text-sm border border-indigo-200 dark:border-indigo-800">
                            {{ carreraActiva?.codigo }}
                        </span>
                        <h2 class="text-xl font-bold leading-tight text-gray-800 dark:text-gray-100">
                            Malla Curricular: {{ carreraActiva?.nombre }}
                        </h2>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Organización dinámica por niveles y mapa de pre-requisitos con flechas de flujo.
                    </p>
                </div>

                <!-- Selector de Carrera y Botón Rápido -->
                <div class="flex items-center gap-3">
                    <div v-if="carreras.length > 1" class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Carrera:</label>
                        <select
                            :value="carreraActiva?.id"
                            @change="cambiarCarrera($event.target.value)"
                            class="text-xs rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-200 py-1.5 px-3 focus:ring-indigo-500"
                        >
                            <option v-for="c in carreras" :key="c.id" :value="c.id">
                                {{ c.codigo }} - {{ c.nombre }}
                            </option>
                        </select>
                    </div>

                    <Button
                        icon="pi pi-plus"
                        label="Nueva Materia"
                        size="small"
                        severity="primary"
                        @click="abrirModalNueva(1)"
                    />
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <!-- Barra de Herramientas y Leyenda Interactiva -->
            <div class="mb-4 bg-white dark:bg-gray-800 rounded-xl p-3 shadow-xs border border-gray-200 dark:border-gray-700 flex flex-wrap items-center justify-between gap-3 text-xs">
                <!-- Leyenda de colores -->
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="font-semibold text-gray-700 dark:text-gray-300">Leyenda interactiva:</span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">Pre-requisitos previos (Aprobación obligatoria)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">Materias desbloqueadas a futuro</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-gray-600 dark:text-gray-400">Nota 51 / 61</span>
                    </div>
                </div>

                <!-- Estado del modo enlace -->
                <div v-if="modoEnlace" class="flex items-center gap-2 bg-indigo-50 dark:bg-indigo-950/80 px-3 py-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 animate-pulse">
                    <i class="pi pi-link"></i>
                    <span class="font-medium">
                        Selecciona el pre-requisito para <strong>{{ materiaOrigenEnlace?.sigla }}</strong>
                    </span>
                    <button @click="cancelarModoEnlace" class="ml-2 text-red-500 hover:text-red-700 font-bold">
                        Cancelar
                    </button>
                </div>
                <div v-else class="text-gray-400 dark:text-gray-500 text-[11px]">
                    <i class="pi pi-info-circle mr-1"></i> Haz clic en cualquier materia para iluminar su ruta de requisitos
                </div>
            </div>

            <!-- LIENZO PRINCIPAL CON SCROLL HORIZONTAL Y FLECHAS SVG -->
            <div
                ref="containerRef"
                @scroll="recalcularFlechas"
                class="relative overflow-x-auto overflow-y-hidden bg-slate-50 dark:bg-gray-900/60 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 min-h-[650px] shadow-inner select-none transition-colors"
            >
                <!-- CAPA SVG DE FLECHAS DINÁMICAS -->
                <svg
                    class="absolute inset-0 pointer-events-none z-10"
                    :style="{ width: `${svgWidth}px`, height: `${svgHeight}px` }"
                >
                    <defs>
                        <!-- Marcador de flecha normal -->
                        <marker id="arrow-normal" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
                            <path d="M 0 1 L 9 5 L 0 9 z" fill="#94a3b8" />
                        </marker>
                        <!-- Marcador de flecha requisito previo (Azul) -->
                        <marker id="arrow-previo" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                            <path d="M 0 1 L 9 5 L 0 9 z" fill="#3b82f6" />
                        </marker>
                        <!-- Marcador de flecha desbloqueada (Ámbar / Naranja) -->
                        <marker id="arrow-desbloqueada" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                            <path d="M 0 1 L 9 5 L 0 9 z" fill="#f59e0b" />
                        </marker>
                    </defs>

                    <!-- Renderizado de las rutas curvas Bézier -->
                    <g v-for="linea in lineasSvg" :key="linea.id">
                        <path
                            :d="linea.d"
                            fill="none"
                            :stroke="
                                linea.tipoResaltado === 'requisito-previo' ? '#3b82f6' :
                                linea.tipoResaltado === 'desbloqueada' ? '#f59e0b' :
                                '#cbd5e1'
                            "
                            :stroke-width="
                                linea.tipoResaltado === 'requisito-previo' || linea.tipoResaltado === 'desbloqueada' ? '3' : '1.5'
                            "
                            :stroke-dasharray="linea.tipoResaltado === 'desbloqueada' ? '4 3' : 'none'"
                            :opacity="linea.tipoResaltado === 'atenuado' ? '0.12' : '1'"
                            :marker-end="
                                linea.tipoResaltado === 'requisito-previo' ? 'url(#arrow-previo)' :
                                linea.tipoResaltado === 'desbloqueada' ? 'url(#arrow-desbloqueada)' :
                                'url(#arrow-normal)'
                            "
                            class="transition-all duration-300"
                        />
                    </g>
                </svg>

                <!-- GRID DE COLUMNAS POR NIVEL -->
                <div class="flex gap-6 relative z-20 items-start w-max min-w-full pb-8">
                    <div
                        v-for="nivel in totalNiveles"
                        :key="nivel"
                        class="w-64 shrink-0 flex flex-col bg-white/80 dark:bg-gray-800/80 backdrop-blur-xs rounded-xl border border-gray-200/90 dark:border-gray-700/80 shadow-xs"
                    >
                        <!-- Header de la Columna / Nivel -->
                        <div class="p-3 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between bg-gray-50/70 dark:bg-gray-800/90 rounded-t-xl">
                            <div>
                                <h3 class="font-bold text-sm text-gray-800 dark:text-gray-100">
                                    {{ nivel }}º Nivel
                                </h3>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ materiasPorNivel[nivel]?.length || 0 }} materias
                                </span>
                            </div>
                            <Button
                                icon="pi pi-plus"
                                size="small"
                                text
                                rounded
                                severity="secondary"
                                title="Agregar materia en este nivel"
                                @click="abrirModalNueva(nivel)"
                                class="!w-7 !h-7 !p-0"
                            />
                        </div>

                        <!-- Lista de Tarjetas de Materia del Nivel -->
                        <div class="p-3 flex flex-col gap-3 min-h-[480px]">
                            <div
                                v-for="materia in materiasPorNivel[nivel]"
                                :key="materia.id"
                                :id="`card-materia-${materia.id}`"
                                @click="seleccionarMateria(materia)"
                                :class="[
                                    'group relative rounded-xl p-3 border transition-all duration-200 cursor-pointer shadow-xs',
                                    // Resaltado de selección
                                    materiaSeleccionada?.id === materia.id
                                        ? 'bg-indigo-50/90 dark:bg-indigo-950/70 border-indigo-500 dark:border-indigo-400 ring-2 ring-indigo-400/50 shadow-md'
                                        : idsRequisitosSeleccionada.has(materia.id)
                                        ? 'bg-blue-50/90 dark:bg-blue-950/70 border-blue-500 dark:border-blue-400 ring-2 ring-blue-400/40 shadow-md'
                                        : idsDesbloqueadasSeleccionada.has(materia.id)
                                        ? 'bg-amber-50/90 dark:bg-amber-950/70 border-amber-500 dark:border-amber-400 ring-2 ring-amber-400/40 shadow-md'
                                        : materiaSeleccionada
                                        ? 'bg-white/60 dark:bg-gray-800/40 border-gray-200/60 dark:border-gray-700/40 opacity-40 hover:opacity-90'
                                        : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-md',
                                    // Modo enlace activo
                                    modoEnlace && materiaOrigenEnlace?.id === materia.id
                                        ? 'ring-2 ring-purple-500 animate-pulse'
                                        : ''
                                ]"
                            >
                                <!-- Cabecera de la Tarjeta: Sigla y Badges -->
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="font-extrabold text-xs tracking-wider text-indigo-700 dark:text-indigo-300">
                                        {{ materia.sigla }}
                                    </span>
                                    <div class="flex items-center gap-1">
                                        <Tag
                                            :value="`Mín ${materia.nota_minima_aprobacion}`"
                                            :severity="materia.nota_minima_aprobacion > 51 ? 'warning' : 'info'"
                                            class="!text-[10px] !px-1.5 !py-0.5"
                                        />
                                    </div>
                                </div>

                                <!-- Nombre de la Materia -->
                                <h4 class="font-semibold text-xs text-gray-900 dark:text-gray-100 line-clamp-2 mb-2 leading-snug">
                                    {{ materia.nombre }}
                                </h4>

                                <!-- Resumen de Requisitos y Acciones -->
                                <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-gray-700/60 text-[11px]">
                                    <span
                                        :class="[
                                            'flex items-center gap-1 font-medium',
                                            materia.requisitos?.length ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 dark:text-gray-500'
                                        ]"
                                        :title="materia.requisitos?.map(r => r.sigla).join(', ') || 'Sin pre-requisitos'"
                                    >
                                        <i class="pi pi-link text-[10px]"></i>
                                        <span>{{ materia.requisitos?.length || 0 }} req</span>
                                    </span>

                                    <!-- Botones de Acción -->
                                    <div class="flex items-center gap-1 opacity-80 group-hover:opacity-100 transition-opacity">
                                        <button
                                            @click="iniciarModoEnlace(materia, $event)"
                                            class="p-1 rounded text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            title="Enlazar o desenlazar pre-requisitos"
                                        >
                                            <i class="pi pi-sitemap text-[11px]"></i>
                                        </button>
                                        <button
                                            @click="abrirModalEditar(materia, $event)"
                                            class="p-1 rounded text-gray-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            title="Editar datos de materia"
                                        >
                                            <i class="pi pi-pencil text-[11px]"></i>
                                        </button>
                                        <button
                                            @click="eliminarMateria(materia, $event)"
                                            class="p-1 rounded text-gray-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            title="Eliminar materia"
                                        >
                                            <i class="pi pi-trash text-[11px]"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Etiqueta Flotante de Relación (cuando está seleccionada) -->
                                <div
                                    v-if="idsRequisitosSeleccionada.has(materia.id)"
                                    class="absolute -top-2.5 right-2 bg-blue-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow-xs"
                                >
                                    Pre-requisito
                                </div>
                                <div
                                    v-else-if="idsDesbloqueadasSeleccionada.has(materia.id)"
                                    class="absolute -top-2.5 right-2 bg-amber-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full shadow-xs"
                                >
                                    Desbloqueada
                                </div>
                            </div>

                            <!-- Estado vacío en la columna -->
                            <div
                                v-if="!materiasPorNivel[nivel]?.length"
                                class="flex-1 flex flex-col items-center justify-center border-2 border-dashed border-gray-200 dark:border-gray-700/60 rounded-xl p-4 text-center text-gray-400 dark:text-gray-500 text-xs"
                            >
                                <i class="pi pi-plus-circle text-lg mb-1 opacity-50"></i>
                                <span>Sin materias</span>
                                <button
                                    @click="abrirModalNueva(nivel)"
                                    class="mt-2 text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                                >
                                    + Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DIALOG / MODAL PARA CREAR O EDITAR MATERIA -->
        <Dialog
            v-model:visible="dialogVisible"
            modal
            :header="esEdicion ? 'Editar Materia' : 'Nueva Materia en Malla Curricular'"
            :style="{ width: '450px' }"
            class="p-fluid"
        >
            <form @submit.prevent="guardarMateria" class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Nombre de la Materia *
                    </label>
                    <InputText
                        v-model="materiaForm.nombre"
                        placeholder="ej: Algoritmos Avanzados"
                        class="w-full"
                        required
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Sigla / Código *
                        </label>
                        <InputText
                            v-model="materiaForm.sigla"
                            placeholder="ej: SIS-302"
                            class="w-full uppercase"
                            required
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                            Nivel / Semestre *
                        </label>
                        <InputNumber
                            v-model="materiaForm.nivel"
                            :min="1"
                            :max="totalNiveles"
                            showButtons
                            class="w-full"
                        />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                        Nota Mínima de Aprobación *
                    </label>
                    <InputNumber
                        v-model="materiaForm.nota_minima_aprobacion"
                        :min="1"
                        :max="100"
                        showButtons
                        class="w-full"
                    />
                    <small class="text-[11px] text-gray-500 dark:text-gray-400">
                        Por defecto 51 puntos (o 61 para materias especiales).
                    </small>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 dark:border-gray-700">
                    <Button
                        label="Cancelar"
                        severity="secondary"
                        text
                        @click="dialogVisible = false"
                    />
                    <Button
                        :label="esEdicion ? 'Guardar Cambios' : 'Crear Materia'"
                        type="submit"
                        severity="primary"
                    />
                </div>
            </form>
        </Dialog>
    </AuthenticatedLayout>
</template>
