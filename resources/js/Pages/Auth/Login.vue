<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    carreras: {
        type: Array,
        default: () => [],
    },
});

// Pestaña principal: 'ingresar' o 'postular'
const tabPrincipal = ref('ingresar');

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'postular') {
        tabPrincipal.value = 'postular';
    }
});

// ==========================================
// FORMULARIO 1: INGRESAR AL SISTEMA
// ==========================================
const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
});

const submitLogin = () => {
    loginForm.post(route('login'), {
        onFinish: () => loginForm.reset('password'),
    });
};

const cargarCredencialDemo = (email, pass = 'password') => {
    loginForm.email = email;
    loginForm.password = pass;
};

// ==========================================
// FORMULARIO 2: POSTULAR AL EXAMEN DE ADMISIÓN
// ==========================================
const subTabPostular = ref('registro'); // 'registro' o 'consulta'

const postulacionForm = useForm({
    name: '',
    ci: '',
    email: '',
    carrera_id: props.carreras[0]?.id || null,
    modalidad_admision: 'Examen de Admisión',
});

const comprobanteVisible = ref(false);
const postulanteRegistrado = ref(null);

const submitPostulacion = () => {
    postulacionForm.post(route('admisiones.postulantes.store'), {
        onSuccess: () => {
            postulanteRegistrado.value = { ...postulacionForm.data() };
            comprobanteVisible.value = true;
            postulacionForm.reset();
        },
    });
};

// ==========================================
// CONSULTA DE ESTADO DE POSTULACIÓN POR CI
// ==========================================
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
            errorConsulta.value = response.data.mensaje;
        }
    } catch (err) {
        errorConsulta.value = 'Ocurrió un error al consultar el sistema. Verifique su conexión.';
    } finally {
        consultando.value = false;
    }
};
</script>

<template>
    <Head :title="tabPrincipal === 'ingresar' ? 'Ingreso al Sistema' : 'Portal de Admisiones y Postulación'" />

    <div class="min-h-screen bg-slate-100 dark:bg-[#070b13] flex flex-col justify-between selection:bg-pink-500 selection:text-white">
        <!-- TOPBAR MÍNIMO CON BOTÓN A PÁGINA WEB -->
        <nav class="w-full px-6 py-4 flex items-center justify-between">
            <Link
                href="/"
                class="flex items-center gap-2.5 text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-pink-600 dark:hover:text-pink-400 transition-colors"
            >
                <i class="pi pi-arrow-left text-xs"></i>
                <span>Volver a la Página Principal</span>
            </Link>

            <div class="flex items-center gap-2">
                <span class="text-[11px] font-semibold text-slate-400">Gestión Académica</span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-mono font-bold bg-pink-500/10 text-pink-600 dark:text-pink-400 border border-pink-500/20">
                    2026-I
                </span>
            </div>
        </nav>

        <!-- CONTENEDOR CENTRAL DUAL -->
        <div class="flex-1 flex items-center justify-center p-4 sm:p-6">
            <div class="w-full max-w-4xl bg-white dark:bg-[#111827] rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-800 overflow-hidden grid grid-cols-1 lg:grid-cols-12">
                <!-- COLUMNA IZQUIERDA: IDENTIDAD Y BIENVENIDA -->
                <div class="lg:col-span-5 bg-gradient-to-br from-[#111827] via-[#1f293d] to-pink-950 p-8 text-white flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute -right-20 -top-20 w-60 h-60 rounded-full bg-pink-500/15 blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-20 -bottom-20 w-60 h-60 rounded-full bg-indigo-500/15 blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-pink-500 to-rose-600 flex items-center justify-center shadow-lg shadow-pink-500/40">
                            <i class="pi pi-graduation-cap text-2xl text-white"></i>
                        </div>
                        <div>
                            <span class="text-[10px] font-black uppercase tracking-widest text-pink-400">
                                Sistema Académico Universitario
                            </span>
                            <h1 class="text-2xl font-black tracking-tight leading-snug mt-1">
                                Portal Central de Acceso y Admisiones
                            </h1>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Plataforma integral para la gestión curricular, seguimiento de cursos, registro de evaluaciones por parciales y admisión de nuevos aspirantes.
                        </p>
                    </div>

                    <!-- PÍLDORAS INFORMATIVAS -->
                    <div class="relative z-10 pt-6 space-y-2.5 border-t border-white/10 text-xs text-slate-300">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-check-circle text-emerald-400 text-xs"></i>
                            <span>Acceso seguro para Docentes y Estudiantes</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-calendar text-pink-400 text-xs"></i>
                            <span>Postulaciones abiertas para el Examen 2026-I</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-shield text-indigo-400 text-xs"></i>
                            <span>Validación automática de prerrequisitos</span>
                        </div>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: PESTAÑAS (1. INGRESAR / 2. POSTULAR) -->
                <div class="lg:col-span-7 p-6 sm:p-8 flex flex-col justify-between">
                    <div>
                        <!-- SELECTOR PRINCIPAL: INGRESAR VS POSTULAR -->
                        <div class="grid grid-cols-2 p-1 rounded-2xl bg-slate-100 dark:bg-slate-800/80 mb-6">
                            <button
                                type="button"
                                @click="tabPrincipal = 'ingresar'"
                                :class="[
                                    'py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2',
                                    tabPrincipal === 'ingresar'
                                        ? 'bg-white dark:bg-[#111827] text-slate-900 dark:text-white shadow-sm font-black'
                                        : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                                ]"
                            >
                                <i class="pi pi-sign-in text-xs text-pink-500"></i>
                                <span>Ingresar al Sistema</span>
                            </button>

                            <button
                                type="button"
                                @click="tabPrincipal = 'postular'"
                                :class="[
                                    'py-2.5 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-2',
                                    tabPrincipal === 'postular'
                                        ? 'bg-white dark:bg-[#111827] text-slate-900 dark:text-white shadow-sm font-black'
                                        : 'text-slate-500 hover:text-slate-800 dark:hover:text-slate-200'
                                ]"
                            >
                                <i class="pi pi-user-plus text-xs text-indigo-500"></i>
                                <span>Postular / Admisión</span>
                            </button>
                        </div>

                        <!-- ============================================== -->
                        <!-- VISTA 1: INGRESAR AL SISTEMA                   -->
                        <!-- ============================================== -->
                        <div v-if="tabPrincipal === 'ingresar'" class="space-y-5">
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-white">
                                    Iniciar Sesión
                                </h2>
                                <p class="text-xs text-slate-400">
                                    Introduce tus credenciales para acceder a tu panel.
                                </p>
                            </div>

                            <div v-if="status" class="p-3 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-semibold">
                                {{ status }}
                            </div>

                            <form @submit.prevent="submitLogin" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Correo Electrónico Institucional
                                    </label>
                                    <InputText
                                        v-model="loginForm.email"
                                        type="email"
                                        placeholder="usuario@academico.test"
                                        required
                                        autofocus
                                        class="w-full text-xs"
                                    />
                                    <span v-if="loginForm.errors.email" class="text-[11px] text-rose-500 font-semibold block mt-1">
                                        {{ loginForm.errors.email }}
                                    </span>
                                </div>

                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                            Contraseña
                                        </label>
                                        <span class="text-[11px] text-slate-400">Default: password</span>
                                    </div>
                                    <InputText
                                        v-model="loginForm.password"
                                        type="password"
                                        placeholder="••••••••"
                                        required
                                        class="w-full text-xs"
                                    />
                                    <span v-if="loginForm.errors.password" class="text-[11px] text-rose-500 font-semibold block mt-1">
                                        {{ loginForm.errors.password }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between pt-1">
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            v-model="loginForm.remember"
                                            class="rounded border-slate-300 text-pink-600 focus:ring-pink-500"
                                        />
                                        <span class="text-xs text-slate-600 dark:text-slate-400">Recordar sesión</span>
                                    </label>
                                </div>

                                <Button
                                    type="submit"
                                    label="Ingresar al Panel"
                                    icon="pi pi-sign-in"
                                    :loading="loginForm.processing"
                                    class="w-full !bg-gradient-to-r !from-pink-500 !to-rose-600 !border-0 font-bold shadow-lg shadow-pink-500/25 !text-xs py-2.5"
                                />
                            </form>

                            <!-- CUENTAS DEMO PARA PRUEBAS RÁPIDAS -->
                            <div class="pt-4 border-t border-slate-100 dark:border-slate-800">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-2">
                                    Accesos Rápidos de Prueba:
                                </span>
                                <div class="flex flex-wrap gap-1.5">
                                    <button
                                        type="button"
                                        @click="cargarCredencialDemo('admin@academico.test')"
                                        class="px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 hover:bg-pink-500 hover:text-white transition-colors"
                                    >
                                        Admin
                                    </button>
                                    <button
                                        type="button"
                                        @click="cargarCredencialDemo('director@academico.test')"
                                        class="px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 hover:bg-pink-500 hover:text-white transition-colors"
                                    >
                                        Director Sistemas
                                    </button>
                                    <button
                                        type="button"
                                        @click="cargarCredencialDemo('docente.calculo@academico.test')"
                                        class="px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 hover:bg-pink-500 hover:text-white transition-colors"
                                    >
                                        Docente
                                    </button>
                                    <button
                                        type="button"
                                        @click="cargarCredencialDemo('estudiante.nivel1@academico.test')"
                                        class="px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 hover:bg-pink-500 hover:text-white transition-colors"
                                    >
                                        Estudiante
                                    </button>
                                    <button
                                        type="button"
                                        @click="cargarCredencialDemo('kardex@academico.test')"
                                        class="px-2 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-800 hover:bg-pink-500 hover:text-white transition-colors"
                                    >
                                        Kardex
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- ============================================== -->
                        <!-- VISTA 2: PORTAL DE POSTULACIÓN / ADMISIONES    -->
                        <!-- ============================================== -->
                        <div v-else class="space-y-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-lg font-black text-slate-900 dark:text-white">
                                        Admisiones 2026-I
                                    </h2>
                                    <p class="text-xs text-slate-400">
                                        Postúlate al examen de suficiencia o consulta tu estado.
                                    </p>
                                </div>

                                <div class="flex gap-1 bg-slate-100 dark:bg-slate-800 p-1 rounded-xl">
                                    <button
                                        type="button"
                                        @click="subTabPostular = 'registro'"
                                        :class="[
                                            'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all',
                                            subTabPostular === 'registro' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500'
                                        ]"
                                    >
                                        Postularme
                                    </button>
                                    <button
                                        type="button"
                                        @click="subTabPostular = 'consulta'"
                                        :class="[
                                            'px-2.5 py-1 rounded-lg text-[11px] font-bold transition-all',
                                            subTabPostular === 'consulta' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-500'
                                        ]"
                                    >
                                        Consultar CI
                                    </button>
                                </div>
                            </div>

                            <!-- SUB-MODO A: REGISTRO DE NUEVA POSTULACIÓN -->
                            <div v-if="subTabPostular === 'registro'">
                                <form @submit.prevent="submitPostulacion" class="space-y-3.5">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                            Nombre Completo *
                                        </label>
                                        <InputText
                                            v-model="postulacionForm.name"
                                            placeholder="Nombres y Apellidos"
                                            required
                                            class="w-full text-xs"
                                        />
                                        <span v-if="postulacionForm.errors.name" class="text-[11px] text-rose-500 font-semibold block mt-0.5">
                                            {{ postulacionForm.errors.name }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                Cédula de Identidad (CI) *
                                            </label>
                                            <InputText
                                                v-model="postulacionForm.ci"
                                                placeholder="Ej. 10293847"
                                                required
                                                class="w-full text-xs font-mono"
                                            />
                                            <span v-if="postulacionForm.errors.ci" class="text-[11px] text-rose-500 font-semibold block mt-0.5">
                                                {{ postulacionForm.errors.ci }}
                                            </span>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                Correo Electrónico *
                                            </label>
                                            <InputText
                                                v-model="postulacionForm.email"
                                                type="email"
                                                placeholder="correo@ejemplo.com"
                                                required
                                                class="w-full text-xs"
                                            />
                                            <span v-if="postulacionForm.errors.email" class="text-[11px] text-rose-500 font-semibold block mt-0.5">
                                                {{ postulacionForm.errors.email }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                Carrera Deseada *
                                            </label>
                                            <select
                                                v-model="postulacionForm.carrera_id"
                                                class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2 font-medium"
                                                required
                                            >
                                                <option v-for="c in carreras" :key="c.id" :value="c.id">
                                                    {{ c.codigo }} - {{ c.nombre }}
                                                </option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                                Modalidad de Ingreso
                                            </label>
                                            <select
                                                v-model="postulacionForm.modalidad_admision"
                                                class="w-full text-xs rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-2 font-medium"
                                            >
                                                <option value="Examen de Admisión">Examen de Admisión</option>
                                                <option value="Excelencia Académica">Excelencia Académica</option>
                                                <option value="Traspaso">Traspaso Institucional</option>
                                            </select>
                                        </div>
                                    </div>

                                    <Button
                                        type="submit"
                                        label="Completar y Enviar Postulación"
                                        icon="pi pi-send"
                                        :loading="postulacionForm.processing"
                                        class="w-full !bg-gradient-to-r !from-indigo-600 !to-violet-600 !border-0 font-bold shadow-lg shadow-indigo-500/25 !text-xs py-2.5 mt-2"
                                    />
                                </form>
                            </div>

                            <!-- SUB-MODO B: CONSULTA DE ESTADO CON CI -->
                            <div v-else class="space-y-4">
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700">
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                                        Ingresa tu Cédula de Identidad (CI):
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <InputText
                                            v-model="ciConsulta"
                                            placeholder="Ej. 1029384"
                                            class="flex-1 text-xs font-mono"
                                            @keydown.enter="consultarEstado"
                                        />
                                        <Button
                                            label="Consultar"
                                            icon="pi pi-search"
                                            size="small"
                                            :loading="consultando"
                                            @click="consultarEstado"
                                            class="!bg-indigo-600 !text-xs font-bold"
                                        />
                                    </div>
                                </div>

                                <div v-if="errorConsulta" class="p-3 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900 text-rose-700 dark:text-rose-300 text-xs font-semibold">
                                    {{ errorConsulta }}
                                </div>

                                <div v-if="resultadoConsulta" class="p-4 rounded-xl border bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-700 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="font-bold text-sm text-slate-900 dark:text-white">
                                            {{ resultadoConsulta.nombre }}
                                        </h4>
                                        <Tag
                                            :severity="resultadoConsulta.estado_admision === 'Admitido' ? 'success' : (resultadoConsulta.estado_admision === 'No Admitido' ? 'danger' : 'info')"
                                            :value="resultadoConsulta.estado_admision"
                                            rounded
                                        />
                                    </div>

                                    <div class="text-xs space-y-1 text-slate-600 dark:text-slate-400">
                                        <div>Carrera: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ resultadoConsulta.carrera }}</span></div>
                                        <div>Modalidad: <span class="font-semibold text-slate-800 dark:text-slate-200">{{ resultadoConsulta.modalidad }}</span></div>
                                        <div>Nota de Examen: <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ resultadoConsulta.nota_admision !== null ? `${resultadoConsulta.nota_admision} / 100` : 'Pendiente de Calificación' }}</span></div>
                                    </div>

                                    <div v-if="resultadoConsulta.estado_admision === 'Admitido'" class="p-2.5 rounded-lg bg-emerald-50 text-emerald-800 text-[11px] font-semibold">
                                        🎉 ¡Felicidades! Has sido admitido a la carrera. Puedes iniciar sesión en la pestaña "Ingresar" con tu correo y contraseña asignada o solicitar tu inscripción en Kardex.
                                    </div>
                                    <div v-else-if="resultadoConsulta.estado_admision === 'Pendiente'" class="p-2.5 rounded-lg bg-blue-50 text-blue-800 text-[11px] font-semibold">
                                        ℹ️ Tu postulación está registrada. Preséntate en las aulas del Campus Central en la fecha programada con tu cédula de identidad.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 text-center text-[11px] text-slate-400">
                        © 2026 Facultad de Tecnología y Ciencias. Todos los derechos reservados.
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL COMPROBANTE DE POSTULACIÓN EXITOSA -->
        <Dialog
            v-model:visible="comprobanteVisible"
            header="¡Postulación Registrada Exitosamente!"
            modal
            class="w-full max-w-md"
        >
            <div v-if="postulanteRegistrado" class="space-y-4 pt-2 text-xs">
                <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-xl text-emerald-900 dark:text-emerald-200">
                    <p class="font-bold text-sm">Registro de Postulante Confirmado</p>
                    <p class="mt-1 text-[11px]">Conserva tu número de CI para verificar tus notas y estado de admisión tras la prueba.</p>
                </div>

                <div class="space-y-2 bg-slate-50 dark:bg-slate-800/60 p-3.5 rounded-xl border border-slate-200 dark:border-slate-700">
                    <div>Postulante: <strong class="text-slate-900 dark:text-white">{{ postulanteRegistrado.name }}</strong></div>
                    <div>CI: <strong class="font-mono text-indigo-600 dark:text-indigo-400">{{ postulanteRegistrado.ci }}</strong></div>
                    <div>Modalidad: <strong>{{ postulanteRegistrado.modalidad_admision }}</strong></div>
                    <div>Estado Inicial: <Tag severity="info" value="Pendiente" class="ml-1" /></div>
                </div>
            </div>

            <template #footer>
                <Button label="Entendido" severity="primary" @click="comprobanteVisible = false" />
            </template>
        </Dialog>
    </div>
</template>
