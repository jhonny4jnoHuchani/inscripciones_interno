<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';

const props = defineProps({
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
});

const tabActivo = ref('institucion'); // 'institucion', 'publicaciones', 'autoridades'

// 1. Formulario Institución
const formInstitucion = ref({
    nombre: props.institucion?.nombre || '',
    sigla: props.institucion?.sigla || '',
    facultad: props.institucion?.facultad || '',
    historia: props.institucion?.historia || '',
    mision: props.institucion?.mision || '',
    vision: props.institucion?.vision || '',
    direccion: props.institucion?.direccion || '',
    telefono: props.institucion?.telefono || '',
    correo: props.institucion?.correo || '',
    whatsapp: props.institucion?.whatsapp || '',
});

const guardarInstitucion = () => {
    router.put(route('portal-web.institucion.update'), formInstitucion.value);
};

// 2. Modal Nueva Publicación / Comunicado
const modalPubVisible = ref(false);
const formPub = ref({
    titulo: '',
    tipo: 'Comunicado',
    descripcion: '',
    contenido: '',
    autor: 'Dirección de Carrera',
    fecha: new Date().toISOString().slice(0, 10),
});

const abrirModalPub = () => {
    formPub.value = {
        titulo: '',
        tipo: 'Comunicado',
        descripcion: '',
        contenido: '',
        autor: 'Dirección de Carrera',
        fecha: new Date().toISOString().slice(0, 10),
    };
    modalPubVisible.value = true;
};

const guardarPublicacion = () => {
    router.post(route('portal-web.publicaciones.store'), formPub.value, {
        onSuccess: () => {
            modalPubVisible.value = false;
        },
    });
};

const eliminarPublicacion = (pub) => {
    if (confirm(`¿Eliminar la publicación "${pub.titulo}"?`)) {
        router.delete(route('portal-web.publicaciones.destroy', pub.id));
    }
};

// 3. Modal Nueva Autoridad
const modalAutVisible = ref(false);
const formAut = ref({
    nombre: '',
    cargo: '',
    grado_academico: '',
    correo: '',
    telefono: '',
});

const abrirModalAut = () => {
    formAut.value = {
        nombre: '',
        cargo: '',
        grado_academico: '',
        correo: '',
        telefono: '',
    };
    modalAutVisible.value = true;
};

const guardarAutoridad = () => {
    router.post(route('portal-web.autoridades.store'), formAut.value, {
        onSuccess: () => {
            modalAutVisible.value = false;
        },
    });
};

const eliminarAutoridad = (aut) => {
    if (confirm(`¿Eliminar la autoridad "${aut.nombre}"?`)) {
        router.delete(route('portal-web.autoridades.destroy', aut.id));
    }
};
</script>

<template>
    <Head title="Gestión del Portal Web Institucional" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <i class="pi pi-globe text-pink-500"></i>
                        Portal Web Institucional
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Administración de contenidos públicos: historia, misión, comunicados, autoridades y portadas.
                    </p>
                </div>

                <a
                    href="/"
                    target="_blank"
                    class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:text-pink-600 transition-colors flex items-center gap-1.5 shadow-xs"
                >
                    <i class="pi pi-external-link text-xs"></i>
                    <span>Ver Página Pública</span>
                </a>
            </div>
        </template>

        <div class="py-6 px-4 sm:px-6 lg:px-8 space-y-6">
            <!-- PESTAÑAS DE CONFIGURACIÓN -->
            <div class="flex items-center gap-2 bg-white dark:bg-[#111827] p-2 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs font-bold">
                <button
                    @click="tabActivo = 'institucion'"
                    :class="['px-4 py-2 rounded-xl transition-all', tabActivo === 'institucion' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800']"
                >
                    <i class="pi pi-building mr-1"></i> Historia y Misión
                </button>
                <button
                    @click="tabActivo = 'publicaciones'"
                    :class="['px-4 py-2 rounded-xl transition-all', tabActivo === 'publicaciones' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800']"
                >
                    <i class="pi pi-megaphone mr-1"></i> Comunicados y Publicaciones ({{ publicaciones.length }})
                </button>
                <button
                    @click="tabActivo = 'autoridades'"
                    :class="['px-4 py-2 rounded-xl transition-all', tabActivo === 'autoridades' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800']"
                >
                    <i class="pi pi-users mr-1"></i> Autoridades ({{ autoridades.length }})
                </button>
            </div>

            <!-- TAB 1: INSTITUCIÓN, HISTORIA, MISIÓN Y VISIÓN -->
            <div v-if="tabActivo === 'institucion'" class="bg-white dark:bg-[#111827] rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-5">
                <div class="pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                        Datos Institucionales, Reseña y Misión
                    </h3>
                    <Button
                        label="Guardar Cambios"
                        icon="pi pi-check"
                        size="small"
                        severity="primary"
                        @click="guardarInstitucion"
                        class="!bg-pink-600 font-bold"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Nombre Institucional</label>
                        <InputText v-model="formInstitucion.nombre" class="w-full text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Sigla / Código</label>
                        <InputText v-model="formInstitucion.sigla" class="w-full text-xs font-mono" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Facultad / Área</label>
                        <InputText v-model="formInstitucion.facultad" class="w-full text-xs" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Reseña Histórica</label>
                    <textarea
                        v-model="formInstitucion.historia"
                        rows="4"
                        class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-3 leading-relaxed"
                    ></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Misión</label>
                        <textarea
                            v-model="formInstitucion.mision"
                            rows="4"
                            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-3 leading-relaxed"
                        ></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Visión</label>
                        <textarea
                            v-model="formInstitucion.vision"
                            rows="4"
                            class="w-full text-xs rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 p-3 leading-relaxed"
                        ></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Dirección del Campus</label>
                        <InputText v-model="formInstitucion.direccion" class="w-full text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Teléfono Central</label>
                        <InputText v-model="formInstitucion.telefono" class="w-full text-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1">Correo Electrónico</label>
                        <InputText v-model="formInstitucion.correo" class="w-full text-xs" />
                    </div>
                </div>
            </div>

            <!-- TAB 2: COMUNICADOS Y PUBLICACIONES -->
            <div v-else-if="tabActivo === 'publicaciones'" class="bg-white dark:bg-[#111827] rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                        Comunicados Oficiales y Noticias Públicas
                    </h3>
                    <Button
                        label="Nuevo Comunicado"
                        icon="pi pi-plus"
                        size="small"
                        severity="primary"
                        @click="abrirModalPub"
                        class="!bg-pink-600 font-bold"
                    />
                </div>

                <div class="divide-y divide-slate-100 dark:divide-slate-800 border rounded-2xl overflow-hidden">
                    <div
                        v-for="p in publicaciones"
                        :key="p.id"
                        class="p-4 flex items-center justify-between text-xs hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors"
                    >
                        <div class="space-y-1 pr-4">
                            <div class="flex items-center gap-2">
                                <Tag :value="p.tipo" :severity="p.tipo === 'Convocatoria' ? 'danger' : 'info'" rounded />
                                <span class="font-bold text-slate-900 dark:text-white text-sm">{{ p.titulo }}</span>
                            </div>
                            <p class="text-slate-500 line-clamp-1">{{ p.descripcion }}</p>
                            <span class="text-[11px] text-slate-400">Fecha: {{ p.fecha }} | Autor: {{ p.autor }}</span>
                        </div>

                        <Button
                            icon="pi pi-trash"
                            size="small"
                            severity="danger"
                            text
                            @click="eliminarPublicacion(p)"
                        />
                    </div>
                </div>
            </div>

            <!-- TAB 3: AUTORIDADES -->
            <div v-else class="bg-white dark:bg-[#111827] rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="font-bold text-sm text-slate-900 dark:text-white">
                        Cuerpo de Autoridades Institucionales
                    </h3>
                    <Button
                        label="Registrar Autoridad"
                        icon="pi pi-plus"
                        size="small"
                        severity="primary"
                        @click="abrirModalAut"
                        class="!bg-pink-600 font-bold"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="a in autoridades"
                        :key="a.id"
                        class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-between"
                    >
                        <div>
                            <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ a.nombre }}</h4>
                            <p class="text-[11px] text-pink-600 font-semibold">{{ a.cargo }}</p>
                            <span class="text-[10px] text-slate-400">{{ a.grado_academico }}</span>
                        </div>
                        <Button
                            icon="pi pi-trash"
                            size="small"
                            severity="danger"
                            text
                            @click="eliminarAutoridad(a)"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL NUEVO COMUNICADO -->
        <Dialog v-model:visible="modalPubVisible" header="Nuevo Comunicado o Noticia" modal class="w-full max-w-lg">
            <div class="space-y-4 pt-2 text-xs">
                <div>
                    <label class="block font-bold mb-1">Título</label>
                    <InputText v-model="formPub.titulo" placeholder="Título oficial" class="w-full" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Tipo</label>
                        <select v-model="formPub.tipo" class="w-full p-2 border rounded-lg text-xs bg-white dark:bg-slate-900">
                            <option value="Comunicado">Comunicado</option>
                            <option value="Convocatoria">Convocatoria</option>
                            <option value="Noticia">Noticia</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Autor / Unidad</label>
                        <InputText v-model="formPub.autor" class="w-full" />
                    </div>
                </div>
                <div>
                    <label class="block font-bold mb-1">Resumen Breve</label>
                    <InputText v-model="formPub.descripcion" class="w-full" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Contenido Detallado</label>
                    <textarea v-model="formPub.contenido" rows="3" class="w-full p-2 border rounded-lg text-xs bg-white dark:bg-slate-900"></textarea>
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="modalPubVisible = false" />
                <Button label="Publicar" severity="primary" @click="guardarPublicacion" />
            </template>
        </Dialog>

        <!-- MODAL NUEVA AUTORIDAD -->
        <Dialog v-model:visible="modalAutVisible" header="Registrar Autoridad" modal class="w-full max-w-md">
            <div class="space-y-4 pt-2 text-xs">
                <div>
                    <label class="block font-bold mb-1">Nombre Completo</label>
                    <InputText v-model="formAut.nombre" placeholder="Ing. Juan Pérez" class="w-full" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Cargo Institucional</label>
                    <InputText v-model="formAut.cargo" placeholder="Director de Carrera" class="w-full" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Grado Académico</label>
                    <InputText v-model="formAut.grado_academico" placeholder="M.Sc. en Computación" class="w-full" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Correo</label>
                        <InputText v-model="formAut.correo" class="w-full" />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Teléfono</label>
                        <InputText v-model="formAut.telefono" class="w-full" />
                    </div>
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="modalAutVisible = false" />
                <Button label="Registrar" severity="primary" @click="guardarAutoridad" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
