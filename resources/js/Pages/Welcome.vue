<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';

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
        }, 6000);
    }
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});

// Filtro de Comunicados y Noticias
const filtroTipo = ref('todos'); // 'todos', 'Convocatoria', 'Comunicado', 'Noticia'

const publicacionesFiltradas = ref([]);
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

                <!-- BOTONES DE ACCIÓN (POSTULAR / INGRESAR) -->
                <div class="flex items-center gap-2.5">
                    <button
                        @click="toggleTheme"
                        class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-pink-600 dark:hover:text-pink-400 transition-colors"
                        title="Modo Oscuro / Claro"
                    >
                        <i :class="isDark ? 'pi pi-sun text-amber-400' : 'pi pi-moon text-indigo-400'"></i>
                    </button>

                    <!-- BOTÓN POSTULACIÓN -->
                    <Link
                        href="/login?tab=postular"
                        class="px-3.5 py-2 rounded-xl text-xs font-black bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 transition-all shadow-xs flex items-center gap-1.5"
                    >
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-ping"></span>
                        <span>Postular</span>
                    </Link>

                    <!-- BOTÓN INGRESAR AL SISTEMA -->
                    <Link
                        href="/login"
                        class="px-4 py-2 rounded-xl text-xs font-black bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white shadow-md shadow-pink-500/25 transition-all flex items-center gap-1.5"
                    >
                        <i class="pi pi-sign-in text-xs"></i>
                        <span>Ingresar</span>
                    </Link>
                </div>
            </div>
        </header>

        <!-- ============================================================== -->
        <!-- 2. HERO SECTION / PORTADAS (INSPIRADO EN LA REFERENCIA)        -->
        <!-- ============================================================== -->
        <section id="inicio" class="relative overflow-hidden bg-gradient-to-b from-slate-900 via-[#111827] to-[#0b0f19] text-white py-20 lg:py-28">
            <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-pink-500/20 via-transparent to-transparent pointer-events-none"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="max-w-3xl space-y-6">
                    <!-- BADGE DE LA PORTADA ACTIVA -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-pink-300 text-xs font-bold uppercase tracking-wider">
                        <i class="pi pi-bolt text-xs text-amber-400"></i>
                        <span>{{ portadas[portadaActivaIndex]?.badge || 'Convocatoria Abierta' }}</span>
                    </div>

                    <!-- TÍTULO DINÁMICO DE LA PORTADA -->
                    <h2 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.1] bg-gradient-to-r from-white via-slate-100 to-pink-200 bg-clip-text text-transparent">
                        {{ portadas[portadaActivaIndex]?.titulo || 'Formando Líderes en Ciencia, Tecnología e Innovación' }}
                    </h2>

                    <!-- SUBTÍTULO -->
                    <p class="text-sm sm:text-base text-slate-300 leading-relaxed max-w-2xl">
                        {{ portadas[portadaActivaIndex]?.subtitulo || 'Desarrolla competencias científicas y profesionales de vanguardia en Ingeniería de Sistemas, Informática y Telecomunicaciones.' }}
                    </p>

                    <!-- BOTONES HERO -->
                    <div class="flex flex-wrap items-center gap-3 pt-4">
                        <Link
                            :href="portadas[portadaActivaIndex]?.link_url || '/login?tab=postular'"
                            class="px-6 py-3.5 rounded-2xl text-xs sm:text-sm font-black bg-gradient-to-r from-pink-500 to-rose-600 hover:from-pink-600 hover:to-rose-700 text-white shadow-xl shadow-pink-500/30 transition-all flex items-center gap-2 group"
                        >
                            <span>{{ portadas[portadaActivaIndex]?.link_texto || 'Postúlate al Examen de Admisión' }}</span>
                            <i class="pi pi-arrow-right text-xs group-hover:translate-x-1 transition-transform"></i>
                        </Link>

                        <a
                            href="#carreras"
                            class="px-6 py-3.5 rounded-2xl text-xs sm:text-sm font-bold bg-white/10 hover:bg-white/15 text-white backdrop-blur-md border border-white/20 transition-all flex items-center gap-2"
                        >
                            <i class="pi pi-sitemap text-xs text-pink-400"></i>
                            <span>Conoce las Carreras</span>
                        </a>
                    </div>
                </div>

                <!-- SELECTOR DE SLIDES (PUNTOS INDICADORES) -->
                <div v-if="portadas.length > 1" class="flex items-center gap-2 pt-12">
                    <button
                        v-for="(_, idx) in portadas"
                        :key="idx"
                        @click="portadaActivaIndex = idx"
                        :class="[
                            'h-2 rounded-full transition-all duration-300',
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
        <!-- 4. AUTORIDADES DE LA INSTITUCIÓN                               -->
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
                        <div class="w-20 h-20 mx-auto rounded-2xl bg-gradient-to-tr from-pink-500 to-rose-600 text-white font-black text-2xl flex items-center justify-center shadow-lg shadow-pink-500/25">
                            {{ a.nombre.charAt(a.nombre.indexOf(' ') + 1) || 'A' }}
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
        <!-- 5. COMUNICADOS OFICIALES Y PUBLICACIONES                       -->
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
                        :class="['px-3 py-1.5 rounded-xl transition-all', filtroTipo === 'todos' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500']"
                    >
                        Todos
                    </button>
                    <button
                        @click="actualizarFiltro('Convocatoria')"
                        :class="['px-3 py-1.5 rounded-xl transition-all', filtroTipo === 'Convocatoria' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500']"
                    >
                        Convocatorias
                    </button>
                    <button
                        @click="actualizarFiltro('Comunicado')"
                        :class="['px-3 py-1.5 rounded-xl transition-all', filtroTipo === 'Comunicado' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500']"
                    >
                        Comunicados
                    </button>
                    <button
                        @click="actualizarFiltro('Noticia')"
                        :class="['px-3 py-1.5 rounded-xl transition-all', filtroTipo === 'Noticia' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500']"
                    >
                        Noticias
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="pub in publicaciones.filter(p => filtroTipo === 'todos' || p.tipo === filtroTipo)"
                    :key="pub.id"
                    class="p-6 rounded-3xl bg-white dark:bg-[#111827] shadow-xl border border-slate-200/80 dark:border-slate-800 flex flex-col justify-between hover:border-pink-500/40 transition-all"
                >
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <Tag
                                :severity="pub.tipo === 'Convocatoria' ? 'danger' : (pub.tipo === 'Comunicado' ? 'info' : 'success')"
                                :value="pub.tipo"
                                rounded
                            />
                            <span class="text-xs text-slate-400 font-semibold">{{ pub.fecha || 'Reciente' }}</span>
                        </div>

                        <h4 class="font-bold text-sm text-slate-900 dark:text-white leading-snug">
                            {{ pub.titulo }}
                        </h4>

                        <p class="text-xs text-slate-500 dark:text-slate-400 line-clamp-3 leading-relaxed">
                            {{ pub.descripcion }}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-[11px] text-slate-400">{{ pub.autor || 'Dirección de Carrera' }}</span>
                        <Button
                            label="Leer Más"
                            icon="pi pi-arrow-right"
                            iconPos="right"
                            text
                            size="small"
                            severity="primary"
                            @click="verDetallePublicacion(pub)"
                            class="!text-xs font-bold"
                        />
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
                            <Link
                                :href="route('malla-curricular.index', { carrera_id: c.id })"
                                class="px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 dark:bg-slate-800 hover:bg-pink-500 hover:text-white transition-colors"
                            >
                                Ver Malla
                            </Link>
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
                        <div><Link href="/login?tab=postular" class="text-pink-400 hover:underline">Postulación a Examen 2026-I</Link></div>
                        <div><Link href="/login" class="text-slate-300 hover:underline">Ingreso al Sistema Docente / Estudiante</Link></div>
                        <div><a href="#carreras" class="text-slate-300 hover:underline">Planes de Estudio</a></div>
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

        <!-- MODAL DETALLE DE COMUNICADO O NOTICIA -->
        <Dialog
            v-model:visible="modalPublicacionVisible"
            :header="publicacionSeleccionada?.titulo || 'Detalle'"
            modal
            class="w-full max-w-xl"
        >
            <div v-if="publicacionSeleccionada" class="space-y-4 pt-2 text-xs">
                <div class="flex items-center justify-between pb-2 border-b">
                    <Tag :value="publicacionSeleccionada.tipo" severity="info" rounded />
                    <span class="text-slate-400 font-semibold">{{ publicacionSeleccionada.fecha }}</span>
                </div>

                <p class="text-slate-700 dark:text-slate-300 text-sm leading-relaxed">
                    {{ publicacionSeleccionada.contenido || publicacionSeleccionada.descripcion }}
                </p>

                <div class="text-[11px] text-slate-400">
                    Emitido por: <strong>{{ publicacionSeleccionada.autor || 'Dirección de Carrera' }}</strong>
                </div>
            </div>

            <template #footer>
                <Button label="Cerrar" text severity="secondary" @click="modalPublicacionVisible = false" />
            </template>
        </Dialog>
    </div>
</template>
