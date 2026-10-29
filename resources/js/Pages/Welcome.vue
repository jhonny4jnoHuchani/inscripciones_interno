<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    institucion: {
        type: Object,
        default: () => ({}),
    },
    portadas: {
        type: Array,
        default: () => [],
    },
    autoridades: {
        type: Array,
        default: () => [],
    },
    publicaciones: {
        type: Array,
        default: () => [],
    },
    carreras: {
        type: Array,
        default: () => [],
    },
});

// Slider de Portadas
const portadaActivaIndex = ref(0);
let intervalId = null;

onMounted(() => {
    if (props.portadas.length > 1) {
        intervalId = setInterval(() => {
            portadaActivaIndex.value = (portadaActivaIndex.value + 1) % props.portadas.length;
        }, 7000);
    }
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

// Filtro de Comunicados y Noticias
const filtroTipo = ref('todos'); // 'todos', 'Convocatoria', 'Comunicado', 'Noticia'

const actualizarFiltro = (tipo) => {
    filtroTipo.value = tipo;
};

// Modal de Detalle de Publicación
const modalPublicacionVisible = ref(false);
const publicacionSeleccionada = ref(null);

const verDetallePublicacion = (pub) => {
    publicacionSeleccionada.value = pub;
    modalPublicacionVisible.value = true;
};

// Dark mode toggle
const isDark = ref(false);
const toggleTheme = () => {
    isDark.value = !isDark.value;
    if (isDark.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

// ==============================================================
// MODAL 1: INGRESAR AL SISTEMA
// ==============================================================
const modalIngresarVisible = ref(false);

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const abrirModalIngresar = () => {
    modalPostularVisible.value = false;
    modalIngresarVisible.value = true;
};

const submitLogin = () => {
    loginForm.post(route('login'), {
        onFinish: () => loginForm.reset('password'),
    });
};

const cargarCredencialDemo = (email, pass = 'password') => {
    loginForm.email = email;
    loginForm.password = pass;
};

// ==============================================================
// MODAL 2: POSTULAR AL EXAMEN DE ADMISIÓN Y CONSULTA
// ==============================================================
const modalPostularVisible = ref(false);
const subTabPostular = ref('registro'); // 'registro' | 'consulta'

const postulacionForm = useForm({
    name: '',
    ci: '',
    email: '',
    carrera_id: props.carreras[0]?.id || null,
    modalidad_admision: 'Examen de Admisión',
});

const comprobanteVisible = ref(false);
const postulanteRegistrado = ref(null);

const abrirModalPostular = (tab = 'registro') => {
    modalIngresarVisible.value = false;
    subTabPostular.value = tab;
    modalPostularVisible.value = true;
};

const submitPostulacion = () => {
    postulacionForm.post(route('admisiones.postulantes.store'), {
        onSuccess: () => {
            postulanteRegistrado.value = { ...postulacionForm.data() };
            comprobanteVisible.value = true;
            postulacionForm.reset();
        },
    });
};

// Consulta de estado de postulación por CI
const ciConsulta = ref('');
const consultando = ref(false);
const resultadoConsulta = ref(null);
const errorConsulta = ref('');

const consultarEstado = async () => {
    if (!ciConsulta.value.trim()) return;

    consultando.value = true;
    errorConsulta.value = '';
    resultadoConsulta.value = null;

    try {
        const response = await axios.post(route('consulta.postulacion'), {
            ci: ciConsulta.value.trim(),
        });

        if (response.data.encontrado) {
            resultadoConsulta.value = response.data;
        } else {
            errorConsulta.value = response.data.mensaje || 'No se encontró ninguna postulación con ese C.I.';
        }
    } catch {
        errorConsulta.value = 'Ocurrió un error al consultar el sistema. Intenta nuevamente.';
    } finally {
        consultando.value = false;
    }
};

// Manejo de clicks en botones de la portada
const manejarClickPortada = (portada) => {
    const url = portada?.link_url || '';
    if (url.includes('postular') || url === '#modal-postular') {
        abrirModalPostular('registro');
    } else if (url.includes('login') || url === '#modal-ingresar') {
        abrirModalIngresar();
    } else if (url.startsWith('#')) {
        const el = document.querySelector(url);
        if (el) el.scrollIntoView({ behavior: 'smooth' });
    } else if (url) {
        window.location.href = url;
    } else {
        abrirModalPostular('registro');
    }
};
</script>

<template>
    <Head :title="`${institucion?.nombre || 'Facultad de Tecnología'} - Portal Oficial`" />

    <div class="min-h-screen bg-slate-50 dark:bg-[#070b13] text-slate-800 dark:text-slate-100 selection:bg-pink-500 selection:text-white transition-colors duration-200">
        <!-- ============================================================== -->
        <!-- 1. HEADER INSTITUCIONAL Y NAVBAR GLASSMORPHISM                -->
        <!-- ============================================================== -->
        <header class="sticky top-0 z-50 bg-white/90 dark:bg-[#111827]/90 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800/80 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
                <!-- LOGO E IDENTIDAD -->
                <a href="#inicio" class="flex items-center gap-3 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-pink-500 via-rose-500 to-amber-400 flex items-center justify-center shadow-lg shadow-pink-500/25 group-hover:scale-105 transition-transform">
                        <i class="pi pi-graduation-cap text-2xl text-white"></i>
                    </div>
                    <div>
                        <span class="text-xs font-black uppercase tracking-wider bg-gradient-to-r from-pink-600 to-rose-600 dark:from-pink-400 dark:to-rose-400 bg-clip-text text-transparent block">
                            {{ institucion?.sigla || 'FAC-TEC' }}
                        </span>
                        <h1 class="text-sm font-black text-slate-900 dark:text-white tracking-tight leading-tight">
                            {{ institucion?.nombre || 'Facultad de Ciencia y Tecnología' }}
                        </h1>
                    </div>
                </a>

                <!-- NAVEGACIÓN PRINCIPAL -->
                <nav class="hidden lg:flex items-center gap-6 text-xs font-bold text-slate-600 dark:text-slate-300">
                    <a href="#inicio" class="hover:text-pink-600 dark:hover:text-pink-400 transition-colors">Inicio</a>
                    <a href="#nosotros" class="hover:text-pink-600 dark:hover:text-pink-400 transition-colors">Historia & Misión</a>
                    <a href="#autoridades" class="hover:text-pink-600 dark:hover:text-pink-400 transition-colors">Autoridades</a>
                    <a href="#comunicados" class="hover:text-pink-600 dark:hover:text-pink-400 transition-colors">Comunicados</a>
                    <a href="#carreras" class="hover:text-pink-600 dark:hover:text-pink-400 transition-colors">Carreras</a>
                    <a href="#contacto" class="hover:text-pink-600 dark:hover:text-pink-400 transition-colors">Contacto</a>
                </nav>

                <!-- BOTONES DE ACCIÓN (POSTULAR / INGRESAR) CON MODALES -->
                <div class="flex items-center gap-2.5">
                    <button
                        @click="toggleTheme"
                        class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-pink-600 dark:hover:text-pink-400 transition-colors cursor-pointer"
                        title="Modo Oscuro / Claro"
                    >
                        <i :class="isDark ? 'pi pi-sun text-amber-400' : 'pi pi-moon text-indigo-400'"></i>
                    </button>

                    <!-- BOTÓN POSTULACIÓN (ABRE MODAL) -->
                    <button
                        type="button"
                        @click="abrirModalPostular('registro')"
                        class="px-3.5 py-2 rounded-xl text-xs font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-all shadow-xs flex items-center gap-1.5 cursor-pointer"
                    >
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
                        <span>Postular</span>
                    </button>

                    <!-- BOTÓN INGRESAR AL SISTEMA (ABRE MODAL) -->
                    <button
                        type="button"
                        @click="abrirModalIngresar"
                        class="px-4 py-2 rounded-xl text-xs font-black bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white shadow-md shadow-pink-500/25 transition-all flex items-center gap-1.5 cursor-pointer"
                    >
                        <i class="pi pi-sign-in text-xs"></i>
                        <span>Ingresar</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- ============================================================== -->
        <!-- 2. HERO SECTION / PORTADAS CON IMÁGENES EN STORAGE             -->
        <!-- ============================================================== -->
        <section id="inicio" class="relative overflow-hidden bg-gradient-to-b from-slate-900 via-[#111827] to-[#0b0f19] text-white py-16 lg:py-24">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-pink-500/20 via-transparent to-transparent pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
                    <!-- CONTENIDO DE TEXTO -->
                    <div class="lg:col-span-7 space-y-6">
                        <!-- BADGE DE LA PORTADA ACTIVA -->
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-pink-300 text-xs font-bold uppercase tracking-wider">
                            <i class="pi pi-bolt text-xs text-amber-400"></i>
                            <span>{{ portadas[portadaActivaIndex]?.badge || 'Convocatoria Abierta' }}</span>
                        </div>

                        <!-- TÍTULO DINÁMICO DE LA PORTADA -->
                        <h2 class="text-3xl sm:text-5xl lg:text-5xl font-black tracking-tight leading-[1.1] bg-gradient-to-r from-white via-slate-100 to-pink-200 bg-clip-text text-transparent">
                            {{ portadas[portadaActivaIndex]?.titulo || 'Formando Líderes en Ciencia, Tecnología e Innovación' }}
                        </h2>

                        <!-- SUBTÍTULO -->
                        <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl">
                            {{ portadas[portadaActivaIndex]?.subtitulo || 'Desarrolla competencias científicas y profesionales de vanguardia en Ingeniería de Sistemas, Informática y Telecomunicaciones.' }}
                        </p>

                        <!-- BOTONES HERO (POSTULAR / CONOCE CARRERAS) -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <button
                                type="button"
                                @click="manejarClickPortada(portadas[portadaActivaIndex])"
                                class="px-6 py-3.5 rounded-2xl text-xs sm:text-sm font-black bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white shadow-xl shadow-pink-500/30 transition-all flex items-center gap-2 group cursor-pointer"
                            >
                                <span>{{ portadas[portadaActivaIndex]?.link_texto || 'Postúlate al Examen de Admisión' }}</span>
                                <i class="pi pi-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                            </button>

                            <button
                                type="button"
                                @click="abrirModalPostular('consulta')"
                                class="px-5 py-3.5 rounded-2xl text-xs sm:text-sm font-bold bg-white/10 hover:bg-white/15 text-white backdrop-blur-md border border-white/20 transition-all flex items-center gap-2 cursor-pointer"
                            >
                                <i class="pi pi-search text-xs text-pink-400"></i>
                                <span>Consultar Estado C.I.</span>
                            </button>
                        </div>
                    </div>

                    <!-- ILUSTRACIÓN / IMAGEN DE PORTADA DESDE STORAGE (ENLACE SIMBÓLICO) -->
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="relative w-full max-w-md rounded-3xl overflow-hidden border border-white/15 bg-white/5 backdrop-blur-xl shadow-2xl p-3 group">
                            <div class="aspect-video w-full rounded-2xl overflow-hidden bg-slate-950/60 flex items-center justify-center">
                                <img
                                    v-if="portadas[portadaActivaIndex]?.imagen_url"
                                    :src="portadas[portadaActivaIndex].imagen_url"
                                    :alt="portadas[portadaActivaIndex]?.titulo"
                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                />
                                <div v-else class="text-center p-6 space-y-2">
                                    <i class="pi pi-image text-4xl text-pink-400/40"></i>
                                    <p class="text-xs text-slate-400">Portal Académico 2026</p>
                                </div>
                            </div>

                            <div class="p-3 flex items-center justify-between text-xs text-slate-300">
                                <span class="font-bold flex items-center gap-1.5">
                                    <i class="pi pi-verified text-pink-400"></i>
                                    Gestión Académica Oficial
                                </span>
                                <span class="font-mono text-[11px] text-pink-400">2026-I</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SELECTOR DE SLIDES (PUNTOS INDICADORES) -->
                <div v-if="portadas.length > 1" class="flex items-center gap-2 pt-8">
                    <button
                        v-for="(_, idx) in portadas"
                        :key="idx"
                        @click="portadaActivaIndex = idx"
                        :class="[
                            'h-2 rounded-full transition-all duration-300 cursor-pointer',
                            portadaActivaIndex === idx ? 'w-8 bg-pink-500' : 'w-2 bg-white/30 hover:bg-white/50'
                        ]"
                        :title="`Ver portada ${idx + 1}`"
                    ></button>
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- 3. SOBRE NOSOTROS: HISTORIA, MISIÓN Y VISIÓN                   -->
        <!-- ============================================================== -->
        <section id="nosotros" class="py-16 sm:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="text-xs font-black uppercase tracking-widest text-pink-600 dark:text-pink-400">
                    Trayectoria y Compromiso
                </span>
                <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                    Historia, Misión y Visión Institucional
                </h3>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Conoce nuestros pilares académicos y el compromiso formativo que impulsa a nuestra comunidad.
                </p>
            </div>

            <!-- TARJETA HISTORIA -->
            <div class="p-6 sm:p-10 rounded-3xl bg-white dark:bg-[#111827] shadow-xl border border-slate-200/80 dark:border-slate-800 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-8 space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-xl bg-pink-500/10 text-pink-600 dark:text-pink-400 font-bold text-xs">
                        <i class="pi pi-history"></i> Reseña Histórica
                    </div>
                    <h4 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white leading-snug">
                        Comprometidos con el desarrollo científico y la transformación productiva
                    </h4>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ institucion?.historia || 'Nuestra institución académica nació con el firme propósito de democratizar la educación superior de excelencia y formar profesionales de alta competitividad científica, tecnológica y con profundo compromiso social. A lo largo de los años nos hemos consolidado como un referente regional y nacional en investigación aplicada, innovación en software, redes inteligentes y automatización industrial.' }}
                    </p>
                </div>

                <div class="lg:col-span-4 bg-gradient-to-tr from-pink-500/10 via-rose-500/10 to-amber-500/10 dark:from-pink-950/40 dark:to-slate-800/60 p-6 rounded-2xl border border-pink-500/20 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-pink-500 text-white flex items-center justify-center font-black">
                            <i class="pi pi-verified"></i>
                        </div>
                        <div>
                            <div class="text-base font-black text-slate-900 dark:text-white">Acreditación</div>
                            <div class="text-xs text-slate-500">Comisión de Calidad Curricular</div>
                        </div>
                    </div>
                    <div class="space-y-2 text-xs text-slate-600 dark:text-slate-300">
                        <div>✓ Mallas actualizadas con correlatividad estricta.</div>
                        <div>✓ Laboratorios equipados para software y hardware.</div>
                        <div>✓ Cuerpo docente con maestría y doctorado.</div>
                    </div>
                </div>
            </div>

            <!-- TARJETAS GEMELAS: MISIÓN Y VISIÓN -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- MISIÓN -->
                <div class="p-8 rounded-3xl bg-white dark:bg-[#111827] shadow-xl border border-slate-200/80 dark:border-slate-800 relative overflow-hidden group hover:border-indigo-500/50 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mb-4 text-xl">
                        <i class="pi pi-compass"></i>
                    </div>
                    <h4 class="text-lg font-black text-slate-900 dark:text-white mb-2">
                        Nuestra Misión
                    </h4>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ institucion?.mision || 'Formar profesionales idóneos de excelencia académica, científica, ética y tecnológica con liderazgo e innovación, capaces de proponer soluciones pertinentes a las demandas del desarrollo productivo y social sostenible.' }}
                    </p>
                </div>

                <!-- VISIÓN -->
                <div class="p-8 rounded-3xl bg-white dark:bg-[#111827] shadow-xl border border-slate-200/80 dark:border-slate-800 relative overflow-hidden group hover:border-pink-500/50 transition-all">
                    <div class="w-12 h-12 rounded-2xl bg-pink-500/10 text-pink-600 dark:text-pink-400 flex items-center justify-center mb-4 text-xl">
                        <i class="pi pi-eye"></i>
                    </div>
                    <h4 class="text-lg font-black text-slate-900 dark:text-white mb-2">
                        Nuestra Visión
                    </h4>
                    <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                        {{ institucion?.vision || 'Ser una institución universitaria líder y acreditada a nivel nacional e internacional, reconocida por su excelencia académica, la rigurosidad de sus investigaciones y el impacto transformador de sus graduados en la sociedad.' }}
                    </p>
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- 4. AUTORIDADES DE LA INSTITUCIÓN CON FOTOS EN STORAGE         -->
        <!-- ============================================================== -->
        <section id="autoridades" class="py-16 sm:py-24 bg-slate-100/70 dark:bg-[#0c101d] border-y border-slate-200/80 dark:border-slate-800/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
                        Liderazgo Académico
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        Autoridades de la Carrera y Gestión
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Cuerpo directivo comprometido con la excelencia académica y la administración transparente.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div
                        v-for="a in autoridades"
                        :key="a.id"
                        class="p-6 rounded-3xl bg-white dark:bg-[#111827] shadow-lg border border-slate-200/80 dark:border-slate-800 text-center space-y-4 hover:-translate-y-1 transition-all"
                    >
                        <!-- FOTO DESDE STORAGE CON FALLBACK DE INICIALES -->
                        <div class="relative w-24 h-24 mx-auto rounded-2xl overflow-hidden shadow-lg shadow-pink-500/20 border border-slate-200 dark:border-slate-700 bg-slate-900 flex items-center justify-center">
                            <img
                                v-if="a.foto_url"
                                :src="a.foto_url"
                                :alt="a.nombre"
                                class="w-full h-full object-cover"
                            />
                            <div
                                v-else
                                class="w-full h-full bg-gradient-to-tr from-pink-500 to-rose-600 text-white font-black text-2xl flex items-center justify-center"
                            >
                                {{ a.nombre.charAt(a.nombre.indexOf(' ') + 1) || 'A' }}
                            </div>
                        </div>

                        <div>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white leading-tight">
                                {{ a.nombre }}
                            </h4>
                            <p class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold mt-1">
                                {{ a.cargo }}
                            </p>
                            <span v-if="a.grado_academico" class="text-[11px] text-slate-400 block mt-0.5">
                                {{ a.grado_academico }}
                            </span>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 space-y-1">
                            <div v-if="a.correo" class="truncate">{{ a.correo }}</div>
                            <div v-if="a.telefono" class="font-mono text-[11px]">Telf: {{ a.telefono }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- 5. COMUNICADOS OFICIALES Y PUBLICACIONES CON IMÁGENES STORAGE  -->
        <!-- ============================================================== -->
        <section id="comunicados" class="py-16 sm:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-pink-600 dark:text-pink-400">
                        Información en Tiempo Real
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        Comunicados, Avisos y Publicaciones
                    </h3>
                </div>

                <div class="flex items-center gap-1.5 bg-white dark:bg-slate-900 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs font-bold">
                    <button
                        @click="actualizarFiltro('todos')"
                        :class="['px-3 py-1.5 rounded-xl transition-all cursor-pointer', filtroTipo === 'todos' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500']"
                    >
                        Todos
                    </button>
                    <button
                        @click="actualizarFiltro('Convocatoria')"
                        :class="['px-3 py-1.5 rounded-xl transition-all cursor-pointer', filtroTipo === 'Convocatoria' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500']"
                    >
                        Convocatorias
                    </button>
                    <button
                        @click="actualizarFiltro('Comunicado')"
                        :class="['px-3 py-1.5 rounded-xl transition-all cursor-pointer', filtroTipo === 'Comunicado' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500']"
                    >
                        Comunicados
                    </button>
                    <button
                        @click="actualizarFiltro('Noticia')"
                        :class="['px-3 py-1.5 rounded-xl transition-all cursor-pointer', filtroTipo === 'Noticia' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500']"
                    >
                        Noticias
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="pub in publicaciones.filter(p => filtroTipo === 'todos' || p.tipo === filtroTipo)"
                    :key="pub.id"
                    class="rounded-3xl bg-white dark:bg-[#111827] shadow-xl border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between overflow-hidden hover:border-pink-500/40 transition-all group"
                >
                    <!-- IMAGEN DE LA NOTICIA EN STORAGE -->
                    <div class="w-full h-44 overflow-hidden bg-slate-900 relative">
                        <img
                            v-if="pub.imagen_url"
                            :src="pub.imagen_url"
                            :alt="pub.titulo"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        />
                        <div v-else class="w-full h-full bg-gradient-to-tr from-slate-800 to-indigo-950 flex items-center justify-center text-slate-500">
                            <i class="pi pi-megaphone text-3xl opacity-40"></i>
                        </div>

                        <div class="absolute top-3 left-3">
                            <Tag
                                :severity="pub.tipo === 'Convocatoria' ? 'danger' : (pub.tipo === 'Comunicado' ? 'info' : 'success')"
                                :value="pub.tipo"
                                rounded
                            />
                        </div>
                    </div>

                    <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                        <div class="space-y-2">
                            <span class="text-[11px] text-slate-400 font-semibold block">{{ pub.fecha || 'Reciente' }}</span>
                            <h4 class="font-bold text-sm text-slate-900 dark:text-white leading-snug line-clamp-2">
                                {{ pub.titulo }}
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-3 leading-relaxed">
                                {{ pub.descripcion }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <span class="text-[11px] text-slate-400">{{ pub.autor || 'Dirección de Carrera' }}</span>
                            <Button
                                label="Leer Más"
                                icon="pi pi-arrow-right"
                                iconPos="right"
                                text
                                size="small"
                                severity="primary"
                                @click="verDetallePublicacion(pub)"
                                class="!text-xs font-bold cursor-pointer"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- 6. OFERTA ACADÉMICA / CARRERAS Y MALLAS                        -->
        <!-- ============================================================== -->
        <section id="carreras" class="py-16 sm:py-24 bg-gradient-to-b from-slate-100/70 to-slate-200/40 dark:from-[#0b0f19] dark:to-[#070b13] border-t border-slate-200/80 dark:border-slate-800/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="text-center max-w-2xl mx-auto space-y-2">
                    <span class="text-xs font-black uppercase tracking-widest text-pink-600 dark:text-pink-400">
                        Formación Profesional
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                        Programas Académicos Universitarios
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                        Mallas curriculares organizadas con prerrequisitos formativos para garantizar tu titulación.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="c in carreras"
                        :key="c.id"
                        class="p-6 rounded-3xl bg-white dark:bg-[#111827] shadow-xl border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between"
                    >
                        <div class="space-y-3">
                            <span class="px-3 py-1 rounded-xl text-xs font-mono font-black bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20">
                                {{ c.codigo }}
                            </span>
                            <h4 class="text-lg font-black text-slate-900 dark:text-white">
                                {{ c.nombre }}
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Plan de estudios estructurado en {{ c.total_niveles || 10 }} semestres académicos con menciones de especialidad.
                            </p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-400">{{ c.materias_count || 0 }} materias en malla</span>
                            <button
                                type="button"
                                @click="abrirModalPostular('registro')"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-pink-500 text-white hover:bg-pink-600 transition-colors cursor-pointer"
                            >
                                Postular
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- 7. FOOTER INSTITUCIONAL                                        -->
        <!-- ============================================================== -->
        <footer id="contacto" class="bg-[#111827] text-white pt-16 pb-12 border-t border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="space-y-3 md:col-span-2">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-pink-500 text-white flex items-center justify-center font-bold">
                                <i class="pi pi-graduation-cap"></i>
                            </div>
                            <span class="font-black text-sm uppercase tracking-wider text-white">
                                {{ institucion?.nombre || 'Facultad de Ciencia y Tecnología' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-400 max-w-md leading-relaxed">
                            Formando ingenieros y científicos computacionales líderes con excelencia ética, rigurosidad académica y compromiso con el desarrollo social.
                        </p>
                    </div>

                    <div class="space-y-2 text-xs">
                        <h5 class="font-black text-slate-200 uppercase tracking-widest text-[11px]">Contacto Central</h5>
                        <div class="text-slate-400">{{ institucion?.direccion || 'Av. Sucre s/n, Campus Universitario' }}</div>
                        <div class="text-slate-400 font-mono">Telf: {{ institucion?.telefono || '+591 2 2844177' }}</div>
                        <div class="text-slate-400">Email: {{ institucion?.correo || 'contacto@academico.test' }}</div>
                    </div>

                    <div class="space-y-2 text-xs">
                        <h5 class="font-black text-slate-200 uppercase tracking-widest text-[11px]">Accesos Rápidos</h5>
                        <div>
                            <button type="button" @click="abrirModalPostular('registro')" class="text-pink-400 hover:underline cursor-pointer">
                                Postulación a Examen 2026-I
                            </button>
                        </div>
                        <div>
                            <button type="button" @click="abrirModalIngresar" class="text-slate-300 hover:underline cursor-pointer">
                                Ingreso al Sistema Docente / Estudiante
                            </button>
                        </div>
                        <div>
                            <button type="button" @click="abrirModalPostular('consulta')" class="text-slate-300 hover:underline cursor-pointer">
                                Consultar Estado de Admisión por C.I.
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between text-xs text-slate-500 gap-4">
                    <div>© 2026 {{ institucion?.nombre || 'Facultad Universitaria' }}. Todos los derechos reservados.</div>
                    <div class="flex items-center gap-4">
                        <span>Gestión Académica Oficial</span>
                        <span>Campus Central</span>
                    </div>
                </div>
            </div>
        </footer>

        <!-- ============================================================== -->
        <!-- MODAL 1: INGRESAR AL SISTEMA (LOGIN EN MODAL)                  -->
        <!-- ============================================================== -->
        <Dialog
            v-model:visible="modalIngresarVisible"
            modal
            :closable="true"
            class="w-full max-w-md mx-4"
            :pt="{
                root: { class: 'rounded-3xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-2xl' },
                header: { class: 'p-0' },
                content: { class: 'p-0' }
            }"
        >
            <div class="bg-gradient-to-tr from-pink-600 via-rose-600 to-amber-500 p-6 text-white relative">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                        <i class="pi pi-lock text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black tracking-tight">Ingresar al Sistema Académico</h3>
                        <p class="text-xs text-pink-100">Acceso para Directores, Docentes y Estudiantes</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-[#111827] space-y-5">
                <!-- ACCESOS RÁPIDOS DEMO -->
                <div class="space-y-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block">Cuentas de Prueba (1 Click):</span>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button
                            type="button"
                            @click="cargarCredencialDemo('director@academico.test')"
                            class="px-2.5 py-1.5 rounded-xl border border-pink-500/20 bg-pink-500/5 hover:bg-pink-500/15 text-pink-600 dark:text-pink-400 font-bold transition-all text-left flex items-center gap-1.5 cursor-pointer"
                        >
                            <i class="pi pi-briefcase text-xs"></i>
                            <span>Director</span>
                        </button>
                        <button
                            type="button"
                            @click="cargarCredencialDemo('docente1@academico.test')"
                            class="px-2.5 py-1.5 rounded-xl border border-indigo-500/20 bg-indigo-500/5 hover:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 font-bold transition-all text-left flex items-center gap-1.5 cursor-pointer"
                        >
                            <i class="pi pi-user text-xs"></i>
                            <span>Docente</span>
                        </button>
                        <button
                            type="button"
                            @click="cargarCredencialDemo('estudiante1@academico.test')"
                            class="px-2.5 py-1.5 rounded-xl border border-emerald-500/20 bg-emerald-500/5 hover:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-bold transition-all text-left flex items-center gap-1.5 cursor-pointer"
                        >
                            <i class="pi pi-book text-xs"></i>
                            <span>Estudiante</span>
                        </button>
                        <button
                            type="button"
                            @click="cargarCredencialDemo('admin@academico.test')"
                            class="px-2.5 py-1.5 rounded-xl border border-amber-500/20 bg-amber-500/5 hover:bg-amber-500/15 text-amber-600 dark:text-amber-400 font-bold transition-all text-left flex items-center gap-1.5 cursor-pointer"
                        >
                            <i class="pi pi-shield text-xs"></i>
                            <span>Administrador</span>
                        </button>
                    </div>
                </div>

                <!-- FORMULARIO DE LOGIN -->
                <form @submit.prevent="submitLogin" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Correo Electrónico</label>
                        <InputText
                            v-model="loginForm.email"
                            type="email"
                            class="w-full text-xs"
                            placeholder="tu.correo@academico.test"
                            required
                        />
                        <span v-if="loginForm.errors.email" class="text-[11px] text-rose-500 font-medium">
                            {{ loginForm.errors.email }}
                        </span>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Contraseña</label>
                        <InputText
                            v-model="loginForm.password"
                            type="password"
                            class="w-full text-xs"
                            placeholder="••••••••"
                            required
                        />
                        <span v-if="loginForm.errors.password" class="text-[11px] text-rose-500 font-medium">
                            {{ loginForm.errors.password }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between text-xs">
                        <label class="flex items-center gap-2 cursor-pointer text-slate-600 dark:text-slate-400">
                            <input type="checkbox" v-model="loginForm.remember" class="rounded text-pink-600 focus:ring-pink-500" />
                            <span>Recordarme</span>
                        </label>
                    </div>

                    <Button
                        type="submit"
                        label="Iniciar Sesión"
                        icon="pi pi-sign-in"
                        :loading="loginForm.processing"
                        class="w-full !rounded-xl !bg-gradient-to-r !from-pink-500 !to-rose-600 !border-0 text-white font-black !py-2.5 shadow-md shadow-pink-500/25 cursor-pointer"
                    />
                </form>

                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500">
                    ¿Eres postulante nuevo?
                    <button
                        type="button"
                        @click="abrirModalPostular('registro')"
                        class="text-pink-600 dark:text-pink-400 font-bold hover:underline ml-1 cursor-pointer"
                    >
                        Postúlate al examen aquí
                    </button>
                </div>
            </div>
        </Dialog>

        <!-- ============================================================== -->
        <!-- MODAL 2: POSTULACIÓN AL EXAMEN Y CONSULTA POR CI               -->
        <!-- ============================================================== -->
        <Dialog
            v-model:visible="modalPostularVisible"
            modal
            :closable="true"
            class="w-full max-w-lg mx-4"
            :pt="{
                root: { class: 'rounded-3xl border border-slate-200/80 dark:border-slate-800 overflow-hidden shadow-2xl' },
                header: { class: 'p-0' },
                content: { class: 'p-0' }
            }"
        >
            <div class="bg-gradient-to-tr from-indigo-600 via-indigo-700 to-purple-800 p-6 text-white relative">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center">
                        <i class="pi pi-file-edit text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black tracking-tight">Admisiones Periodo 2026-I</h3>
                        <p class="text-xs text-indigo-100">Registro de Postulantes y Consulta Oficial por C.I.</p>
                    </div>
                </div>

                <!-- TABS DE POSTULAR -->
                <div class="flex items-center gap-2 mt-4 bg-black/20 p-1 rounded-xl text-xs font-bold">
                    <button
                        type="button"
                        @click="subTabPostular = 'registro'"
                        :class="['flex-1 py-1.5 rounded-lg text-center transition-all cursor-pointer', subTabPostular === 'registro' ? 'bg-white text-indigo-900 shadow-xs' : 'text-indigo-200 hover:text-white']"
                    >
                        Nueva Postulación
                    </button>
                    <button
                        type="button"
                        @click="subTabPostular = 'consulta'"
                        :class="['flex-1 py-1.5 rounded-lg text-center transition-all cursor-pointer', subTabPostular === 'consulta' ? 'bg-white text-indigo-900 shadow-xs' : 'text-indigo-200 hover:text-white']"
                    >
                        Consultar Estado (C.I.)
                    </button>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-[#111827]">
                <!-- TAB 1: FORMULARIO DE POSTULACIÓN -->
                <div v-if="subTabPostular === 'registro'" class="space-y-4">
                    <div v-if="comprobanteVisible && postulanteRegistrado" class="p-5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 space-y-3">
                        <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-400 font-black text-sm">
                            <i class="pi pi-check-circle text-lg"></i>
                            <span>¡Postulación Registrada con Éxito!</span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            Tu registro al Examen de Admisión 2026-I se encuentra guardado. Tu C.I. es tu clave de seguimiento.
                        </p>
                        <div class="p-3 bg-white dark:bg-slate-900 rounded-xl space-y-1 text-xs">
                            <div><strong>Aspirante:</strong> {{ postulanteRegistrado.name }}</div>
                            <div><strong>C.I.:</strong> {{ postulanteRegistrado.ci }}</div>
                            <div><strong>Modalidad:</strong> {{ postulanteRegistrado.modalidad_admision }}</div>
                        </div>
                        <div class="flex gap-2 pt-2">
                            <Button
                                label="Consultar Estado"
                                icon="pi pi-search"
                                size="small"
                                severity="success"
                                @click="subTabPostular = 'consulta'; ciConsulta = postulanteRegistrado.ci; consultarEstado();"
                                class="!text-xs font-bold cursor-pointer"
                            />
                            <Button
                                label="Cerrar"
                                text
                                size="small"
                                severity="secondary"
                                @click="comprobanteVisible = false; modalPostularVisible = false;"
                                class="!text-xs cursor-pointer"
                            />
                        </div>
                    </div>

                    <form v-else @submit.prevent="submitPostulacion" class="space-y-3.5">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Nombre Completo</label>
                            <InputText
                                v-model="postulacionForm.name"
                                class="w-full text-xs"
                                placeholder="Ej: Juan Carlos Pérez"
                                required
                            />
                            <span v-if="postulacionForm.errors.name" class="text-[11px] text-rose-500">
                                {{ postulacionForm.errors.name }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Cédula de Identidad (C.I.)</label>
                                <InputText
                                    v-model="postulacionForm.ci"
                                    class="w-full text-xs font-mono"
                                    placeholder="Ej: 8945123"
                                    required
                                />
                                <span v-if="postulacionForm.errors.ci" class="text-[11px] text-rose-500">
                                    {{ postulacionForm.errors.ci }}
                                </span>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Correo Electrónico</label>
                                <InputText
                                    v-model="postulacionForm.email"
                                    type="email"
                                    class="w-full text-xs"
                                    placeholder="aspirante@test.com"
                                    required
                                />
                                <span v-if="postulacionForm.errors.email" class="text-[11px] text-rose-500">
                                    {{ postulacionForm.errors.email }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Carrera a Postular</label>
                                <select
                                    v-model="postulacionForm.carrera_id"
                                    class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs py-2 px-3 focus:ring-indigo-500"
                                    required
                                >
                                    <option v-for="c in carreras" :key="c.id" :value="c.id">
                                        {{ c.codigo }} - {{ c.nombre }}
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Modalidad</label>
                                <select
                                    v-model="postulacionForm.modalidad_admision"
                                    class="w-full rounded-xl border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs py-2 px-3 focus:ring-indigo-500"
                                >
                                    <option value="Examen de Admisión">Examen de Admisión</option>
                                    <option value="Excelencia Académica">Excelencia Académica</option>
                                    <option value="Traspaso">Traspaso</option>
                                </select>
                            </div>
                        </div>

                        <Button
                            type="submit"
                            label="Registrar Postulación al Examen"
                            icon="pi pi-send"
                            :loading="postulacionForm.processing"
                            class="w-full !rounded-xl !bg-indigo-600 hover:!bg-indigo-700 !border-0 text-white font-black !py-2.5 shadow-md shadow-indigo-600/25 cursor-pointer mt-2"
                        />
                    </form>
                </div>

                <!-- TAB 2: CONSULTA POR CI -->
                <div v-else class="space-y-4">
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">
                            Ingresa tu Número de Cédula de Identidad (C.I.):
                        </label>
                        <div class="flex gap-2">
                            <InputText
                                v-model="ciConsulta"
                                @keyup.enter="consultarEstado"
                                class="flex-1 text-xs font-mono"
                                placeholder="Ej: 8945123"
                            />
                            <Button
                                label="Consultar"
                                icon="pi pi-search"
                                :loading="consultando"
                                @click="consultarEstado"
                                class="!rounded-xl !bg-indigo-600 !border-0 text-white font-bold cursor-pointer"
                            />
                        </div>
                    </div>

                    <!-- ERROR EN CONSULTA -->
                    <div v-if="errorConsulta" class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-2">
                        <i class="pi pi-exclamation-circle text-base"></i>
                        <span>{{ errorConsulta }}</span>
                    </div>

                    <!-- RESULTADO ENCONTRADO -->
                    <div v-if="resultadoConsulta" class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-slate-200 dark:border-slate-800">
                            <span class="text-xs text-slate-500">Resultado Oficial</span>
                            <Tag
                                :severity="resultadoConsulta.estado_admision === 'Admitido' ? 'success' : (resultadoConsulta.estado_admision === 'No Admitido' ? 'danger' : 'warn')"
                                :value="resultadoConsulta.estado_admision"
                                rounded
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Postulante:</span>
                                <strong class="text-slate-800 dark:text-slate-200">{{ resultadoConsulta.nombre }}</strong>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">C.I.:</span>
                                <span class="font-mono text-slate-800 dark:text-slate-200">{{ resultadoConsulta.ci }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Carrera:</span>
                                <span class="text-slate-800 dark:text-slate-200">{{ resultadoConsulta.carrera }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Nota de Examen:</span>
                                <strong :class="resultadoConsulta.nota_admision >= 61 ? 'text-emerald-500' : 'text-amber-500'">
                                    {{ resultadoConsulta.nota_admision !== null ? `${resultadoConsulta.nota_admision} / 100` : 'En espera de calificación' }}
                                </strong>
                            </div>
                        </div>

                        <div v-if="resultadoConsulta.estado_admision === 'Admitido'" class="p-2.5 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-xs font-semibold">
                            ¡Felicidades! Has sido formalmente admitido. Ya puedes ingresar al sistema institucional con tu usuario y contraseña (tu C.I.).
                        </div>
                    </div>
                </div>

                <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 text-center text-xs text-slate-500">
                    ¿Ya eres estudiante o docente del sistema?
                    <button
                        type="button"
                        @click="abrirModalIngresar"
                        class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline ml-1 cursor-pointer"
                    >
                        Ingresar al Sistema
                    </button>
                </div>
            </div>
        </Dialog>

        <!-- ============================================================== -->
        <!-- MODAL DETALLE DE COMUNICADO O NOTICIA                          -->
        <!-- ============================================================== -->
        <Dialog
            v-model:visible="modalPublicacionVisible"
            :header="publicacionSeleccionada?.titulo || 'Detalle'"
            modal
            class="w-full max-w-xl mx-4"
        >
            <div v-if="publicacionSeleccionada" class="space-y-4 pt-2 text-xs">
                <!-- IMAGEN COMPLETA DE LA NOTICIA EN STORAGE -->
                <div v-if="publicacionSeleccionada.imagen_url" class="w-full h-56 rounded-2xl overflow-hidden bg-slate-900">
                    <img
                        :src="publicacionSeleccionada.imagen_url"
                        :alt="publicacionSeleccionada.titulo"
                        class="w-full h-full object-cover"
                    />
                </div>

                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <Tag :value="publicacionSeleccionada.tipo" severity="info" rounded />
                    <span class="text-slate-400 font-semibold">{{ publicacionSeleccionada.fecha }}</span>
                </div>

                <p class="text-slate-700 dark:text-slate-300 text-sm leading-relaxed whitespace-pre-line">
                    {{ publicacionSeleccionada.contenido || publicacionSeleccionada.descripcion }}
                </p>

                <div class="text-[11px] text-slate-400">
                    Emitido por: <strong>{{ publicacionSeleccionada.autor || 'Dirección de Carrera' }}</strong>
                </div>
            </div>

            <template #footer>
                <Button label="Cerrar" text severity="secondary" @click="modalPublicacionVisible = false" class="cursor-pointer" />
            </template>
        </Dialog>
    </div>
</template>
