<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
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

const page = usePage();
const currentUser = computed(() => page.props.auth?.user || {});
const esAdminODirector = computed(() => {
    const rol = currentUser.value.role || (currentUser.value.roles && currentUser.value.roles[0]) || '';
    return rol === 'Administrador' || rol === 'Director';
});

// Referencias del DOM para calcular flechas SVG
const containerRef = ref(null);
const lineasSvg = ref([]);
const svgWidth = ref(2400);
const svgHeight = ref(1200);

// Estado de materias local para reactividad inmediata sin parpadeos
const materiasLocales = ref([...(props.carreraActiva?.materias || [])]);

// Sincronizar si cambian las props
watch(() => props.carreraActiva, (newVal) => {
    materiasLocales.value = [...(newVal?.materias || [])];
    nextTick(() => recalcularFlechas());
}, { deep: true });

// Materia seleccionada (CLICK) y materia en hover (solo para iluminar flechas sin mover el layout)
const materiaSeleccionada = ref(null);
const materiaHoverId = ref(null);
const modoArbolCompleto = ref(false);

// Filtro de búsqueda y área
const busqueda = ref('');
const filtroArea = ref('todas');

// Materia activa para iluminación de flechas (hover prioritario sobre click para las líneas)
const idMateriaActiva = computed(() => materiaHoverId.value || materiaSeleccionada.value?.id || null);

// Modal de Creación / Edición de Materia
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

// Modal dedicado para Gestionar Pre-requisitos (Estricto nivel inferior)
const modalRequisitosVisible = ref(false);
const materiaParaRequisitos = ref(null);

// Niveles organizados en columnas (dinámico por carrera)
const totalNiveles = computed(() => props.carreraActiva?.total_niveles || 10);

// Mapeo inteligente de área académica según la sigla y nombre de la materia
const getAreaInfo = (materia) => {
    if (!materia) return { nombre: 'General', headerGradient: 'from-slate-600 to-gray-700', badgeBg: 'bg-slate-500/10 text-slate-500 border-slate-500/30', borderAccent: 'border-l-4 border-l-slate-400', icon: 'pi pi-book', glowColor: '#64748b' };
    const s = materia.sigla?.toUpperCase() || '';
    const n = materia.nombre?.toLowerCase() || '';

    if (s.startsWith('MAT') || s.startsWith('ALG') || n.includes('cálculo') || n.includes('álgebra') || n.includes('ecuacion') || n.includes('estadística') || n.includes('numérico')) {
        return {
            nombre: 'Matemáticas y Ciencias',
            badgeBg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30',
            headerGradient: 'from-emerald-500 to-teal-600',
            borderAccent: 'border-l-4 border-l-emerald-500',
            icon: 'pi pi-calculator',
            glowColor: '#10b981',
        };
    }
    if (s.startsWith('SIS-10') || s.startsWith('SIS-201') || s.startsWith('SIS-203') || s.startsWith('SIS-306') || s.startsWith('INF-10') || s.startsWith('INF-20') || n.includes('programación') || n.includes('estructura de datos')) {
        return {
            nombre: 'Programación y Algoritmos',
            badgeBg: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/30',
            headerGradient: 'from-indigo-600 to-violet-600',
            borderAccent: 'border-l-4 border-l-indigo-500',
            icon: 'pi pi-code',
            glowColor: '#6366f1',
        };
    }
    if (s.startsWith('FIS') || s.startsWith('SIS-202') || s.startsWith('SIS-502') || n.includes('física') || n.includes('arquitectura') || n.includes('robótica')) {
        return {
            nombre: 'Física y Hardware',
            badgeBg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/30',
            headerGradient: 'from-amber-500 to-orange-500',
            borderAccent: 'border-l-4 border-l-amber-500',
            icon: 'pi pi-bolt',
            glowColor: '#f59e0b',
        };
    }
    if (s.startsWith('TEL') || s.startsWith('SIS-403') || n.includes('redes') || n.includes('seguridad') || n.includes('comunicacion')) {
        return {
            nombre: 'Redes y Telecomunicaciones',
            badgeBg: 'bg-sky-500/10 text-sky-600 dark:text-sky-400 border-sky-500/30',
            headerGradient: 'from-sky-500 to-blue-600',
            borderAccent: 'border-l-4 border-l-sky-500',
            icon: 'pi pi-wifi',
            glowColor: '#0ea5e9',
        };
    }
    if (s.startsWith('SIS-301') || s.startsWith('SIS-304') || s.startsWith('SIS-302') || n.includes('base de datos') || n.includes('sistemas operativos') || n.includes('big data')) {
        return {
            nombre: 'Bases de Datos y SO',
            badgeBg: 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/30',
            headerGradient: 'from-cyan-500 to-teal-600',
            borderAccent: 'border-l-4 border-l-cyan-500',
            icon: 'pi pi-database',
            glowColor: '#06b6d4',
        };
    }
    if (s.startsWith('SIS-40') || s.startsWith('SIS-303') || s.startsWith('SIS-305') || s.startsWith('SIS-307') || n.includes('software') || n.includes('diseño de sistemas') || n.includes('gerencial') || n.includes('operativa')) {
        return {
            nombre: 'Ingeniería de Software',
            badgeBg: 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/30',
            headerGradient: 'from-purple-600 to-pink-600',
            borderAccent: 'border-l-4 border-l-purple-500',
            icon: 'pi pi-server',
            glowColor: '#a855f7',
        };
    }
    if (s.startsWith('SIS-5') || n.includes('taller') || n.includes('grado') || n.includes('inteligencia artificial') || n.includes('práctica')) {
        return {
            nombre: 'IA y Titulación',
            badgeBg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/30',
            headerGradient: 'from-rose-500 to-pink-600',
            borderAccent: 'border-l-4 border-l-rose-500',
            icon: 'pi pi-graduation-cap',
            glowColor: '#f43f5e',
        };
    }
    return {
        nombre: 'Formación Complementaria',
        badgeBg: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30',
        headerGradient: 'from-slate-600 to-gray-700',
        borderAccent: 'border-l-4 border-l-slate-400',
        icon: 'pi pi-book',
        glowColor: '#64748b',
    };
};

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

// Función para obtener el árbol genealógico completo (recursivo)
const getAncestrosRecursivos = (materiaId, visitados = new Set()) => {
    if (visitados.has(materiaId)) return visitados;
    visitados.add(materiaId);
    const m = materiasLocales.value.find(mat => mat.id === materiaId);
    if (m?.requisitos) {
        m.requisitos.forEach(r => getAncestrosRecursivos(r.id, visitados));
    }
    return visitados;
};

const getDescendientesRecursivos = (materiaId, visitados = new Set()) => {
    if (visitados.has(materiaId)) return visitados;
    visitados.add(materiaId);
    materiasLocales.value.forEach(m => {
        if (m.requisitos?.some(r => r.id === materiaId)) {
            getDescendientesRecursivos(m.id, visitados);
        }
    });
    return visitados;
};

// IDs de requisitos de la materia activa (hacia atrás)
const idsRequisitosActiva = computed(() => {
    if (!idMateriaActiva.value) return new Set();
    if (modoArbolCompleto.value) {
        const full = getAncestrosRecursivos(idMateriaActiva.value);
        full.delete(idMateriaActiva.value);
        return full;
    }
    const reqs = new Set();
    const materia = materiasLocales.value.find(m => m.id === idMateriaActiva.value);
    if (materia?.requisitos) {
        materia.requisitos.forEach(r => reqs.add(r.id));
    }
    return reqs;
});

// IDs de materias que tienen a la materia activa como requisito (hacia adelante)
const idsDesbloqueadasActiva = computed(() => {
    if (!idMateriaActiva.value) return new Set();
    if (modoArbolCompleto.value) {
        const full = getDescendientesRecursivos(idMateriaActiva.value);
        full.delete(idMateriaActiva.value);
        return full;
    }
    const desbloqueadas = new Set();
    materiasLocales.value.forEach(m => {
        if (m.requisitos?.some(r => r.id === idMateriaActiva.value)) {
            desbloqueadas.add(m.id);
        }
    });
    return desbloqueadas;
});

// Lista de objetos Materia para el inspector
const listaRequisitosSeleccionada = computed(() => {
    if (!materiaSeleccionada.value) return [];
    const ids = idsRequisitosActiva.value;
    return materiasLocales.value.filter(m => ids.has(m.id));
});

const listaDesbloqueadasSeleccionada = computed(() => {
    if (!materiaSeleccionada.value) return [];
    const ids = idsDesbloqueadasActiva.value;
    return materiasLocales.value.filter(m => ids.has(m.id));
});

// Calcular las coordenadas exactas de las flechas SVG entre tarjetas
const recalcularFlechas = () => {
    if (!containerRef.value) return;

    const container = containerRef.value;
    const containerRect = container.getBoundingClientRect();
    const scrollLeft = container.scrollLeft;
    const scrollTop = container.scrollTop;

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

            const dx = Math.max(40, (xDestino - xOrigen) / 2);
            const pathData = `M ${xOrigen} ${yOrigen} C ${xOrigen + dx} ${yOrigen}, ${xDestino - dx} ${yDestino}, ${xDestino} ${yDestino}`;

            let tipoResaltado = 'normal';
            if (idMateriaActiva.value) {
                const esReqDirecto = (materia.id === idMateriaActiva.value);
                const esDesbloqueoDirecto = (req.id === idMateriaActiva.value);

                if (esReqDirecto || (modoArbolCompleto.value && idsRequisitosActiva.value.has(req.id) && idsRequisitosActiva.value.has(materia.id))) {
                    tipoResaltado = 'requisito-previo';
                } else if (esDesbloqueoDirecto || (modoArbolCompleto.value && idsDesbloqueadasActiva.value.has(req.id) && idsDesbloqueadasActiva.value.has(materia.id))) {
                    tipoResaltado = 'desbloqueada';
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

// Eventos de selección con CLICK (fijo, sin mover layout)
const seleccionarMateria = (materia) => {
    if (materiaSeleccionada.value?.id === materia.id) {
        materiaSeleccionada.value = null;
    } else {
        materiaSeleccionada.value = materia;
    }
    nextTick(() => recalcularFlechas());
};

// Hover suave (solo ilumina líneas, NUNCA abre banners en la parte superior)
const entrarHover = (materia) => {
    materiaHoverId.value = materia.id;
    // Solo actualizamos clases de las flechas sin alterar altura del layout
    recalcularFlechas();
};

const salirHover = () => {
    materiaHoverId.value = null;
    recalcularFlechas();
};

const enfocarMateria = (materiaId) => {
    const m = materiasLocales.value.find(item => item.id === materiaId);
    if (m) {
        materiaSeleccionada.value = m;
        const el = document.getElementById(`card-materia-${materiaId}`);
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'center', inline: 'center' });
        }
        nextTick(() => recalcularFlechas());
    }
};

// MODAL DE GESTIÓN DE PRE-REQUISITOS (ESTRICTO NIVEL INFERIOR)
const abrirModalRequisitos = (materia, event) => {
    event?.stopPropagation();
    materiaParaRequisitos.value = materia;
    modalRequisitosVisible.value = true;
};

// Candidatos válidos: solo materias de la misma carrera que pertenezcan a niveles inferiores
const candidatosRequisitos = computed(() => {
    if (!materiaParaRequisitos.value) return [];
    const nivelActual = materiaParaRequisitos.value.nivel;

    // Solo materias con nivel estrictamente inferior (< nivelActual)
    return materiasLocales.value
        .filter(m => m.nivel < nivelActual)
        .sort((a, b) => a.nivel - b.nivel || a.sigla.localeCompare(b.sigla));
});

const esRequisitoVinculado = (candidatoId) => {
    if (!materiaParaRequisitos.value?.requisitos) return false;
    return materiaParaRequisitos.value.requisitos.some(r => r.id === candidatoId);
};

const alternarRequisito = async (requisito) => {
    if (!materiaParaRequisitos.value) return;

    try {
        const response = await axios.post(route('malla-curricular.toggle-requisito'), {
            materia_id: materiaParaRequisitos.value.id,
            requisito_id: requisito.id,
        });

        if (response.data.success) {
            const index = materiasLocales.value.findIndex(m => m.id === materiaParaRequisitos.value.id);
            if (index !== -1) {
                materiasLocales.value[index] = response.data.materia;
                materiaParaRequisitos.value = response.data.materia;
                if (materiaSeleccionada.value?.id === materiaParaRequisitos.value.id) {
                    materiaSeleccionada.value = response.data.materia;
                }
            }
            nextTick(() => recalcularFlechas());
        }
    } catch (error) {
        alert(error.response?.data?.message || 'Error al actualizar el pre-requisito.');
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

const getEtapaNivel = (nivel) => {
    if (nivel <= 2) return { nombre: 'Ciclo Básico / Fundamentos' };
    if (nivel <= 5) return { nombre: 'Ciclo Formativo Central' };
    if (nivel <= 7) return { nombre: 'Ciclo de Especialidad' };
    return { nombre: 'Graduación y Titulación' };
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
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 rounded-xl bg-pink-500/20 text-pink-600 dark:text-pink-400 font-mono font-black text-xs border border-pink-500/30">
                        {{ carreraActiva?.codigo }}
                    </span>
                    <div>
                        <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
                            Malla Curricular: {{ carreraActiva?.nombre }}
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ totalNiveles }} semestres configurados. Grafo estable de pre-requisitos con trazado vectorial dinámico.
                        </p>
                    </div>
                </div>

                <!-- SELECTOR DE CARRERA Y ACCIONES -->
                <div class="flex flex-wrap items-center gap-3">
                    <div v-if="carreras.length > 1" class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider">Carrera:</label>
                        <select
                            :value="carreraActiva?.id"
                            @change="cambiarCarrera($event.target.value)"
                            class="text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 py-1.5 px-3 focus:ring-2 focus:ring-pink-500 font-semibold shadow-xs"
                        >
                            <option v-for="c in carreras" :key="c.id" :value="c.id">
                                {{ c.codigo }} - {{ c.nombre }} ({{ c.total_niveles }} semestres)
                            </option>
                        </select>
                    </div>

                    <Button
                        v-if="esAdminODirector"
                        icon="pi pi-plus"
                        label="Nueva Materia"
                        size="small"
                        severity="primary"
                        @click="abrirModalNueva(1)"
                        class="shadow-md !bg-gradient-to-r !from-pink-500 !to-rose-600 !border-0 font-bold"
                    />
                </div>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-4">
            <!-- 1. HUD INSPECTOR FIJO (SOLO SE ACTIVA AL HACER CLICK EN UNA MATERIA, NUNCA CON HOVER PARA EVITAR SALTOS) -->
            <div
                v-if="materiaSeleccionada"
                class="p-4 rounded-2xl bg-white dark:bg-[#111827] border-2 border-pink-500/50 dark:border-pink-500/40 shadow-2xl transition-all duration-200 flex flex-col lg:flex-row lg:items-center justify-between gap-4"
            >
                <div class="flex items-center gap-3.5">
                    <div
                        :class="[
                            'w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg bg-gradient-to-tr font-black',
                            getAreaInfo(materiaSeleccionada).headerGradient
                        ]"
                    >
                        <i :class="[getAreaInfo(materiaSeleccionada).icon, 'text-xl']"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-md font-mono font-bold text-xs bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white">
                                {{ materiaSeleccionada.sigla }}
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-pink-500/10 text-pink-600 dark:text-pink-400">
                                {{ materiaSeleccionada.nivel }}º Semestre
                            </span>
                        </div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white mt-0.5">
                            {{ materiaSeleccionada.nombre }}
                        </h3>
                    </div>
                </div>

                <!-- Requisitos y Desbloqueos en Pills Interactivos -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1 lg:justify-center">
                    <!-- Requisitos Previos -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider flex items-center gap-1">
                            <i class="pi pi-arrow-left text-[9px]"></i> Requisitos Previos ({{ listaRequisitosSeleccionada.length }}):
                        </span>
                        <div class="flex flex-wrap gap-1">
                            <button
                                v-for="req in listaRequisitosSeleccionada"
                                :key="req.id"
                                @click="enfocarMateria(req.id)"
                                class="px-2 py-1 rounded-lg text-xs font-mono font-bold bg-blue-50 dark:bg-blue-950/70 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 hover:bg-blue-100 transition-colors cursor-pointer"
                                :title="req.nombre"
                            >
                                {{ req.sigla }} ({{ req.nivel }}º)
                            </button>
                            <span v-if="listaRequisitosSeleccionada.length === 0" class="text-xs text-slate-400 italic">
                                Ninguno (Materia inicial de nivel)
                            </span>
                        </div>
                    </div>

                    <div class="hidden sm:block h-8 w-px bg-slate-200 dark:bg-slate-700"></div>

                    <!-- Materias Desbloqueadas a Futuro -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1">
                            Desbloquea a futuro ({{ listaDesbloqueadasSeleccionada.length }}): <i class="pi pi-arrow-right text-[9px]"></i>
                        </span>
                        <div class="flex flex-wrap gap-1">
                            <button
                                v-for="des in listaDesbloqueadasSeleccionada"
                                :key="des.id"
                                @click="enfocarMateria(des.id)"
                                class="px-2 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 transition-colors cursor-pointer"
                                :title="des.nombre"
                            >
                                {{ des.sigla }} ({{ des.nivel }}º)
                            </button>
                            <span v-if="listaDesbloqueadasSeleccionada.length === 0" class="text-xs text-slate-400 italic">
                                Último nivel / Terminal
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Botón gestionar requisitos + cerrar -->
                <div class="flex items-center gap-2 self-end lg:self-center">
                    <Button
                        v-if="esAdminODirector"
                        label="Gestionar Requisitos"
                        icon="pi pi-link"
                        size="small"
                        severity="info"
                        @click="abrirModalRequisitos(materiaSeleccionada)"
                        class="!text-xs font-bold"
                    />
                    <Button
                        icon="pi pi-times"
                        severity="secondary"
                        text
                        rounded
                        size="small"
                        @click="materiaSeleccionada = null; nextTick(() => recalcularFlechas())"
                        title="Cerrar inspección"
                    />
                </div>
            </div>

            <!-- 2. BARRA DE HERRAMIENTAS Y LEYENDA CLARA -->
            <div class="bg-white dark:bg-[#111827] rounded-2xl p-3.5 shadow-md border border-slate-200/80 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4 text-xs">
                <!-- Leyenda de colores -->
                <div class="flex items-center gap-4 flex-wrap">
                    <span class="font-black text-slate-700 dark:text-slate-200 uppercase tracking-wider text-[11px]">
                        Flujo de Relaciones:
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 rounded-full bg-blue-500 ring-4 ring-blue-500/20"></span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">Requisito Obligatorio (⬅️ Entrante)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 ring-4 ring-emerald-500/20"></span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">Materia Desbloqueada (➡️ Saliente)</span>
                    </div>
                </div>

                <div class="text-slate-500 dark:text-slate-400 text-[11px] flex items-center gap-1.5">
                    <i class="pi pi-info-circle text-pink-500"></i>
                    Haz clic en cualquier materia para inspeccionar sus relaciones o en el icono <i class="pi pi-link mx-0.5"></i> para configurar requisitos de niveles inferiores.
                </div>
            </div>

            <!-- 3. LIENZO PRINCIPAL CON SCROLL HORIZONTAL Y FLECHAS SVG -->
            <div
                ref="containerRef"
                @scroll="recalcularFlechas"
                class="relative overflow-x-auto overflow-y-hidden bg-slate-100/70 dark:bg-[#070b13] rounded-3xl border border-slate-200 dark:border-slate-800 p-6 min-h-[720px] shadow-2xl select-none custom-scrollbar transition-colors"
            >
                <!-- CAPA SVG DE FLECHAS DINÁMICAS -->
                <svg
                    class="absolute inset-0 pointer-events-none z-10"
                    :style="{ width: `${svgWidth}px`, height: `${svgHeight}px` }"
                >
                    <defs>
                        <marker id="arrow-normal" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="7" markerHeight="7" orient="auto-start-reverse">
                            <path d="M 0 1.5 L 8 5 L 0 8.5 z" fill="#64748b" />
                        </marker>
                        <marker id="arrow-previo" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="9" markerHeight="9" orient="auto-start-reverse">
                            <path d="M 0 1 L 9 5 L 0 9 z" fill="#3b82f6" />
                        </marker>
                        <marker id="arrow-desbloqueada" viewBox="0 0 10 10" refX="9" refY="5" markerWidth="9" markerHeight="9" orient="auto-start-reverse">
                            <path d="M 0 1 L 9 5 L 0 9 z" fill="#10b981" />
                        </marker>
                    </defs>

                    <g v-for="linea in lineasSvg" :key="linea.id">
                        <path
                            :d="linea.d"
                            fill="none"
                            :stroke="
                                linea.tipoResaltado === 'requisito-previo' ? '#3b82f6' :
                                linea.tipoResaltado === 'desbloqueada' ? '#10b981' :
                                '#64748b'
                            "
                            :stroke-width="
                                linea.tipoResaltado === 'requisito-previo' || linea.tipoResaltado === 'desbloqueada' ? '3.5' : '1.8'
                            "
                            :stroke-dasharray="
                                linea.tipoResaltado === 'requisito-previo' || linea.tipoResaltado === 'desbloqueada' ? '8 4' : 'none'
                            "
                            :opacity="
                                linea.tipoResaltado === 'atenuado' ? '0.04' :
                                linea.tipoResaltado === 'normal' ? '0.45' : '1'
                            "
                            :marker-end="
                                linea.tipoResaltado === 'requisito-previo' ? 'url(#arrow-previo)' :
                                linea.tipoResaltado === 'desbloqueada' ? 'url(#arrow-desbloqueada)' :
                                'url(#arrow-normal)'
                            "
                            :class="[
                                'transition-all duration-300',
                                (linea.tipoResaltado === 'requisito-previo' || linea.tipoResaltado === 'desbloqueada') ? 'animate-flow-dash' : ''
                            ]"
                        />
                    </g>
                </svg>

                <!-- GRID DE COLUMNAS POR NIVEL -->
                <div class="flex gap-6 relative z-20 items-start w-max min-w-full pb-8">
                    <div
                        v-for="nivel in totalNiveles"
                        :key="nivel"
                        class="w-72 shrink-0 flex flex-col bg-white/90 dark:bg-[#111827]/90 backdrop-blur-md rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xl"
                    >
                        <!-- Header de la Columna / Nivel con Gradiente -->
                        <div class="p-3.5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between rounded-t-2xl bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/80 dark:to-slate-800/40">
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="font-black text-sm text-slate-800 dark:text-white">
                                        {{ nivel }}º Semestre
                                    </h3>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full font-bold bg-pink-500/10 text-pink-600 dark:text-pink-400">
                                        {{ materiasPorNivel[nivel]?.length || 0 }}
                                    </span>
                                </div>
                                <span class="text-[10px] text-slate-400 font-medium block mt-0.5">
                                    {{ getEtapaNivel(nivel).nombre }}
                                </span>
                            </div>
                                <Button
                                    v-if="esAdminODirector"
                                    icon="pi pi-plus"
                                    size="small"
                                    text
                                    rounded
                                    severity="secondary"
                                    title="Agregar materia en este nivel"
                                    @click="abrirModalNueva(nivel)"
                                    class="!w-8 !h-8 !p-0 hover:!bg-pink-500 hover:!text-white transition-colors"
                                />
                        </div>

                        <!-- Lista de Tarjetas de Materia del Nivel -->
                        <div class="p-3 space-y-3.5 flex-1 min-h-[480px]">
                            <div
                                v-for="materia in materiasPorNivel[nivel]"
                                :key="materia.id"
                                :id="`card-materia-${materia.id}`"
                                @click="seleccionarMateria(materia)"
                                @mouseenter="entrarHover(materia)"
                                @mouseleave="salirHover"
                                :class="[
                                    'group relative rounded-2xl p-3.5 transition-all duration-200 cursor-pointer shadow-md select-none border',
                                    getAreaInfo(materia).borderAccent,

                                    // Estado Seleccionada / Hover
                                    materiaSeleccionada?.id === materia.id
                                        ? 'ring-3 ring-pink-500 bg-white dark:bg-slate-800 shadow-2xl scale-[1.03] z-30'
                                        : idsRequisitosActiva.has(materia.id)
                                        ? 'ring-2 ring-blue-500 bg-blue-50/90 dark:bg-blue-950/60 shadow-xl scale-[1.01] z-20'
                                        : idsDesbloqueadasActiva.has(materia.id)
                                        ? 'ring-2 ring-emerald-500 bg-emerald-50/90 dark:bg-emerald-950/60 shadow-xl scale-[1.01] z-20'
                                        : idMateriaActiva
                                        ? 'opacity-40 bg-white/70 dark:bg-slate-900/60 border-slate-200/60 dark:border-slate-800'
                                        : 'bg-white dark:bg-slate-900/90 hover:shadow-xl hover:-translate-y-0.5 border-slate-200/80 dark:border-slate-800'
                                ]"
                            >
                                <!-- Conector Puerto Izquierdo (Requisitos Entrantes) -->
                                <div
                                    :class="[
                                        'absolute -left-2 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full border-2 border-white dark:border-slate-900 flex items-center justify-center transition-colors',
                                        idsRequisitosActiva.has(materia.id)
                                            ? 'bg-blue-500 shadow-md shadow-blue-500/50'
                                            : 'bg-slate-300 dark:bg-slate-700'
                                    ]"
                                    title="Punto de conexión de requisitos"
                                >
                                    <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                </div>

                                <!-- Conector Puerto Derecho (Desbloqueos Salientes) -->
                                <div
                                    :class="[
                                        'absolute -right-2 top-1/2 -translate-y-1/2 w-4 h-4 rounded-full border-2 border-white dark:border-slate-900 flex items-center justify-center transition-colors',
                                        idsDesbloqueadasActiva.has(materia.id)
                                            ? 'bg-emerald-500 shadow-md shadow-emerald-500/50'
                                            : 'bg-slate-300 dark:bg-slate-700'
                                    ]"
                                    title="Punto de conexión de desbloqueo"
                                >
                                    <div class="w-1.5 h-1.5 rounded-full bg-white"></div>
                                </div>

                                <!-- Header de Tarjeta: Sigla, Área y Nota Mínima -->
                                <div class="flex items-center justify-between gap-1 mb-2">
                                    <span class="font-mono font-black text-xs px-2 py-0.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700">
                                        {{ materia.sigla }}
                                    </span>
                                    <span :class="['text-[10px] font-bold px-2 py-0.5 rounded-full border', getAreaInfo(materia).badgeBg]">
                                        <i :class="[getAreaInfo(materia).icon, 'text-[9px] mr-1']"></i>
                                        {{ getAreaInfo(materia).nombre.split(' ')[0] }}
                                    </span>
                                </div>

                                <!-- Nombre de la Materia -->
                                <h4 class="font-bold text-xs text-slate-900 dark:text-white leading-tight line-clamp-2">
                                    {{ materia.nombre }}
                                </h4>

                                <!-- Footer de Tarjeta: Badges y Acciones Rápidas -->
                                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px]">
                                    <div class="flex items-center gap-1.5 font-mono">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                                            {{ materia.requisitos?.length || 0 }} req
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                            Min {{ materia.nota_minima_aprobacion }}
                                        </span>
                                    </div>

                                    <!-- Botones de Acción (Solo Admin / Director) -->
                                    <div v-if="esAdminODirector" class="flex items-center gap-1 opacity-90 group-hover:opacity-100">
                                        <button
                                            @click="abrirModalRequisitos(materia, $event)"
                                            class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-indigo-500 hover:text-white text-slate-500 dark:text-slate-400 flex items-center justify-center transition-colors"
                                            title="Gestionar Pre-requisitos de esta materia"
                                        >
                                            <i class="pi pi-link text-[10px]"></i>
                                        </button>
                                        <button
                                            @click="abrirModalEditar(materia, $event)"
                                            class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 flex items-center justify-center transition-colors"
                                            title="Editar materia"
                                        >
                                            <i class="pi pi-pencil text-[10px]"></i>
                                        </button>
                                        <button
                                            @click="eliminarMateria(materia, $event)"
                                            class="w-6 h-6 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-rose-500 hover:text-white text-slate-500 dark:text-slate-400 flex items-center justify-center transition-colors"
                                            title="Eliminar materia"
                                        >
                                            <i class="pi pi-trash text-[10px]"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Botón agregar al pie de columna (Solo Admin / Director) -->
                            <button
                                v-if="esAdminODirector"
                                @click="abrirModalNueva(nivel)"
                                class="w-full py-2.5 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 hover:border-pink-500 dark:hover:border-pink-500 hover:bg-pink-50/30 text-slate-400 hover:text-pink-600 dark:hover:text-pink-400 text-xs font-bold transition-all flex items-center justify-center gap-1.5"
                            >
                                <i class="pi pi-plus text-[10px]"></i>
                                <span>Agregar Materia</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL DEDICADO: GESTIÓN DE PRE-REQUISITOS (ESTRICTO NIVEL INFERIOR) -->
        <Dialog
            v-model:visible="modalRequisitosVisible"
            modal
            :header="`Configurar Pre-requisitos: [${materiaParaRequisitos?.sigla}] ${materiaParaRequisitos?.nombre}`"
            class="w-full max-w-xl"
        >
            <div v-if="materiaParaRequisitos" class="space-y-4 pt-2">
                <div class="p-3 bg-blue-50/60 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 rounded-xl text-xs text-blue-900 dark:text-blue-200">
                    <p class="font-bold flex items-center gap-1.5">
                        <i class="pi pi-shield"></i>
                        Regla de Pre-requisito: Solo Niveles Inferiores
                    </p>
                    <p class="text-[11px] text-blue-700 dark:text-blue-300 mt-1">
                        Esta materia pertenece al <strong>{{ materiaParaRequisitos.nivel }}º Semestre</strong>. Por norma curricular, solo puedes vincular materias pertenecientes a semestres anteriores (Niveles 1 a {{ materiaParaRequisitos.nivel - 1 }}). Las materias del mismo nivel o niveles superiores quedan automáticamente excluidas para evitar ciclos y errores.
                    </p>
                </div>

                <div v-if="materiaParaRequisitos.nivel === 1" class="text-center py-6 text-xs text-slate-400 border border-dashed rounded-xl">
                    Esta materia pertenece al <strong>1º Semestre (Nivel Inicial)</strong>, por lo que no requiere ningún pre-requisito universitario.
                </div>

                <div v-else-if="candidatosRequisitos.length > 0" class="divide-y divide-slate-100 dark:divide-slate-800 border rounded-xl max-h-96 overflow-y-auto">
                    <div
                        v-for="cand in candidatosRequisitos"
                        :key="cand.id"
                        class="p-3 flex items-center justify-between hover:bg-slate-50/80 dark:hover:bg-slate-800/40 text-xs transition-colors"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-slate-200 dark:bg-slate-800 font-mono">
                                    {{ cand.sigla }}
                                </span>
                                <span class="font-bold text-slate-800 dark:text-slate-100">{{ cand.nombre }}</span>
                            </div>
                            <span class="text-[11px] text-slate-400">
                                Pertenece al {{ cand.nivel }}º Semestre (Nivel Inferior Válido)
                            </span>
                        </div>

                        <Button
                            :icon="esRequisitoVinculado(cand.id) ? 'pi pi-check' : 'pi pi-plus'"
                            :label="esRequisitoVinculado(cand.id) ? 'Vinculado' : 'Asignar'"
                            size="small"
                            :severity="esRequisitoVinculado(cand.id) ? 'success' : 'secondary'"
                            @click="alternarRequisito(cand)"
                            class="!text-xs font-bold"
                        />
                    </div>
                </div>

                <div v-else class="text-center py-6 text-xs text-slate-400 border border-dashed rounded-xl">
                    No hay materias registradas en niveles inferiores a este semestre.
                </div>
            </div>

            <template #footer>
                <Button label="Listo" severity="primary" @click="modalRequisitosVisible = false" />
            </template>
        </Dialog>

        <!-- MODAL DE CREACIÓN / EDICIÓN DE MATERIA -->
        <Dialog
            v-model:visible="dialogVisible"
            modal
            :header="esEdicion ? 'Editar Materia Curricular' : 'Registrar Nueva Materia'"
            class="w-full max-w-md"
        >
            <div class="space-y-4 pt-2">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nombre de la Materia *</label>
                    <InputText v-model="materiaForm.nombre" placeholder="Ej: Inteligencia Artificial" class="w-full text-xs" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Sigla / Código *</label>
                        <InputText v-model="materiaForm.sigla" placeholder="Ej: SIS-501" class="w-full text-xs font-mono uppercase" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Semestre / Nivel *</label>
                        <InputNumber v-model="materiaForm.nivel" :min="1" :max="totalNiveles" class="w-full text-xs" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1">Nota Mínima de Aprobación *</label>
                    <InputNumber v-model="materiaForm.nota_minima_aprobacion" :min="1" :max="100" class="w-full text-xs" />
                    <p class="text-[11px] text-slate-400 mt-1">Generalmente 51 puntos.</p>
                </div>
            </div>

            <template #footer>
                <Button label="Cancelar" severity="secondary" text @click="dialogVisible = false" />
                <Button
                    :label="esEdicion ? 'Actualizar Materia' : 'Crear Materia'"
                    icon="pi pi-check"
                    @click="guardarMateria"
                    class="!bg-gradient-to-r !from-pink-500 !to-rose-600 !border-0 font-bold"
                />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
