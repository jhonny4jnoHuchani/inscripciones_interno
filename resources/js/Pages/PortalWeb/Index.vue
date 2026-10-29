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

const tabActivo = ref('institucion'); // 'institucion', 'portadas', 'publicaciones', 'autoridades'

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

// 2. Modal Nueva Portada (Hero Slider)
const modalPortadaVisible = ref(false);
const formPortada = ref({
    titulo: '',
    subtitulo: '',
    badge: 'Convocatoria Abierta',
    link_url: '#modal-postular',
    link_texto: 'Postúlate al Examen Aquí',
    orden: 1,
    imagen: null,
});

const abrirModalPortada = () => {
    formPortada.value = {
        titulo: '',
        subtitulo: '',
        badge: 'Convocatoria Abierta',
        link_url: '#modal-postular',
        link_texto: 'Postúlate al Examen Aquí',
        orden: (props.portadas.length || 0) + 1,
        imagen: null,
    };
    modalPortadaVisible.value = true;
};

const onArchivoPortada = (e) => {
    formPortada.value.imagen = e.target.files[0] || null;
};

const guardarPortada = () => {
    const formData = new FormData();
    formData.append('titulo', formPortada.value.titulo);
    formData.append('subtitulo', formPortada.value.subtitulo || '');
    formData.append('badge', formPortada.value.badge || '');
    formData.append('link_url', formPortada.value.link_url || '');
    formData.append('link_texto', formPortada.value.link_texto || '');
    formData.append('orden', formPortada.value.orden || 1);
    if (formPortada.value.imagen) {
        formData.append('imagen', formPortada.value.imagen);
    }

    router.post(route('portal-web.portadas.store'), formData, {
        onSuccess: () => {
            modalPortadaVisible.value = false;
        },
    });
};

const eliminarPortada = (portada) => {
    if (confirm(`¿Eliminar la portada "${portada.titulo}"?`)) {
        router.delete(route('portal-web.portadas.destroy', portada.id));
    }
};

// 3. Modal Nueva Publicación / Comunicado
const modalPubVisible = ref(false);
const formPub = ref({
    titulo: '',
    tipo: 'Comunicado',
    descripcion: '',
    contenido: '',
    autor: 'Dirección de Carrera',
    fecha: new Date().toISOString().slice(0, 10),
    imagen: null,
});

const abrirModalPub = () => {
    formPub.value = {
        titulo: '',
        tipo: 'Comunicado',
        descripcion: '',
        contenido: '',
        autor: 'Dirección de Carrera',
        fecha: new Date().toISOString().slice(0, 10),
        imagen: null,
    };
    modalPubVisible.value = true;
};

const onArchivoPub = (e) => {
    formPub.value.imagen = e.target.files[0] || null;
};

const guardarPublicacion = () => {
    const formData = new FormData();
    formData.append('titulo', formPub.value.titulo);
    formData.append('tipo', formPub.value.tipo);
    formData.append('descripcion', formPub.value.descripcion);
    formData.append('contenido', formPub.value.contenido || '');
    formData.append('autor', formPub.value.autor || '');
    formData.append('fecha', formPub.value.fecha);
    if (formPub.value.imagen) {
        formData.append('imagen', formPub.value.imagen);
    }

    router.post(route('portal-web.publicaciones.store'), formData, {
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

// 4. Modal Nueva Autoridad
const modalAutVisible = ref(false);
const formAut = ref({
    nombre: '',
    cargo: '',
    grado_academico: '',
    correo: '',
    telefono: '',
    foto: null,
});

const abrirModalAut = () => {
    formAut.value = {
        nombre: '',
        cargo: '',
        grado_academico: '',
        correo: '',
        telefono: '',
        foto: null,
    };
    modalAutVisible.value = true;
};

const onArchivoAut = (e) => {
    formAut.value.foto = e.target.files[0] || null;
};

const guardarAutoridad = () => {
    const formData = new FormData();
    formData.append('nombre', formAut.value.nombre);
    formData.append('cargo', formAut.value.cargo);
    formData.append('grado_academico', formAut.value.grado_academico || '');
    formData.append('correo', formAut.value.correo || '');
    formData.append('telefono', formAut.value.telefono || '');
    if (formAut.value.foto) {
        formData.append('foto', formAut.value.foto);
    }

    router.post(route('portal-web.autoridades.store'), formData, {
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
                        Administración de contenidos públicos: historia, misión, portadas hero, comunicados y autoridades.
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
            <div class="flex items-center gap-2 bg-white dark:bg-[#111827] p-2 rounded-2xl border border-slate-200 dark:border-slate-800 text-xs font-bold overflow-x-auto">
                <button
                    @click="tabActivo = 'institucion'"
                    :class="['px-4 py-2 rounded-xl transition-all cursor-pointer whitespace-nowrap', tabActivo === 'institucion' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white']"
                >
                    <i class="pi pi-building mr-1"></i> Historia y Misión
                </button>
                <button
                    @click="tabActivo = 'portadas'"
                    :class="['px-4 py-2 rounded-xl transition-all cursor-pointer whitespace-nowrap', tabActivo === 'portadas' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white']"
                >
                    <i class="pi pi-images mr-1"></i> Portadas Hero ({{ portadas.length }})
                </button>
                <button
                    @click="tabActivo = 'publicaciones'"
                    :class="['px-4 py-2 rounded-xl transition-all cursor-pointer whitespace-nowrap', tabActivo === 'publicaciones' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white']"
                >
                    <i class="pi pi-megaphone mr-1"></i> Comunicados ({{ publicaciones.length }})
                </button>
                <button
                    @click="tabActivo = 'autoridades'"
                    :class="['px-4 py-2 rounded-xl transition-all cursor-pointer whitespace-nowrap', tabActivo === 'autoridades' ? 'bg-pink-500 text-white shadow-xs' : 'text-slate-500 hover:text-slate-800 dark:hover:text-white']"
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
                        class="!bg-pink-600 font-bold cursor-pointer"
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

            <!-- TAB 2: PORTADAS (HERO SLIDER) -->
            <div v-if="tabActivo === 'portadas'" class="bg-white dark:bg-[#111827] rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-5">
                <div class="pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Portadas del Hero Slider</h3>
                        <p class="text-xs text-slate-400">Diapositivas dinámicas mostradas en la parte superior del portal público</p>
                    </div>
                    <Button
                        label="Nueva Portada"
                        icon="pi pi-plus"
                        size="small"
                        severity="primary"
                        @click="abrirModalPortada"
                        class="!bg-pink-600 font-bold cursor-pointer"
                    />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div
                        v-for="p in portadas"
                        :key="p.id"
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 overflow-hidden flex flex-col justify-between"
                    >
                        <div class="w-full h-36 bg-slate-900 overflow-hidden relative">
                            <img
                                v-if="p.imagen_url"
                                :src="p.imagen_url"
                                :alt="p.titulo"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-500">
                                <i class="pi pi-image text-3xl"></i>
                            </div>
                            <span class="absolute top-2 left-2 px-2.5 py-1 rounded-lg bg-black/60 text-pink-300 text-[10px] font-bold">
                                {{ p.badge || 'Slide ' + p.orden }}
                            </span>
                        </div>

                        <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-xs text-slate-900 dark:text-white line-clamp-1">{{ p.titulo }}</h4>
                                <p class="text-[11px] text-slate-500 line-clamp-2 mt-1">{{ p.subtitulo }}</p>
                            </div>
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                <span class="text-[11px] font-mono text-slate-400">Orden: {{ p.orden }}</span>
                                <Button
                                    icon="pi pi-trash"
                                    size="small"
                                    severity="danger"
                                    text
                                    @click="eliminarPortada(p)"
                                    class="cursor-pointer"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: COMUNICADOS Y NOTICIAS -->
            <div v-if="tabActivo === 'publicaciones'" class="bg-white dark:bg-[#111827] rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-5">
                <div class="pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Avisos, Convocatorias y Noticias</h3>
                        <p class="text-xs text-slate-400">Publicaciones visibles en la sección de noticias del portal</p>
                    </div>
                    <Button
                        label="Nuevo Comunicado"
                        icon="pi pi-plus"
                        size="small"
                        severity="primary"
                        @click="abrirModalPub"
                        class="!bg-pink-600 font-bold cursor-pointer"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="pub in publicaciones"
                        :key="pub.id"
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 overflow-hidden flex flex-col justify-between"
                    >
                        <div class="w-full h-32 bg-slate-900 overflow-hidden relative">
                            <img
                                v-if="pub.imagen_url"
                                :src="pub.imagen_url"
                                :alt="pub.titulo"
                                class="w-full h-full object-cover"
                            />
                            <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                                <i class="pi pi-megaphone text-2xl"></i>
                            </div>
                            <Tag
                                :severity="pub.tipo === 'Convocatoria' ? 'danger' : (pub.tipo === 'Comunicado' ? 'info' : 'success')"
                                :value="pub.tipo"
                                rounded
                                class="!absolute top-2 left-2 text-[10px]"
                            />
                        </div>

                        <div class="p-4 space-y-2 flex-1 flex flex-col justify-between">
                            <div>
                                <h4 class="font-bold text-xs text-slate-900 dark:text-white line-clamp-1">{{ pub.titulo }}</h4>
                                <p class="text-[11px] text-slate-500 line-clamp-2 mt-1">{{ pub.descripcion }}</p>
                            </div>
                            <div class="pt-2 border-t border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                <span class="text-[10px] text-slate-400">{{ pub.autor }}</span>
                                <Button
                                    icon="pi pi-trash"
                                    size="small"
                                    severity="danger"
                                    text
                                    @click="eliminarPublicacion(pub)"
                                    class="cursor-pointer"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: AUTORIDADES -->
            <div v-if="tabActivo === 'autoridades'" class="bg-white dark:bg-[#111827] rounded-3xl p-6 shadow-sm border border-slate-200 dark:border-slate-800 space-y-5">
                <div class="pb-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 dark:text-white">Cuerpo de Autoridades</h3>
                        <p class="text-xs text-slate-400">Directores y comisiones académicas institucionales</p>
                    </div>
                    <Button
                        label="Registrar Autoridad"
                        icon="pi pi-plus"
                        size="small"
                        severity="primary"
                        @click="abrirModalAut"
                        class="!bg-pink-600 font-bold cursor-pointer"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="a in autoridades"
                        :key="a.id"
                        class="p-4 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/40 flex items-center justify-between gap-3"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-xl bg-slate-900 overflow-hidden flex items-center justify-center border border-slate-300 dark:border-slate-700 shrink-0">
                                <img
                                    v-if="a.foto_url"
                                    :src="a.foto_url"
                                    :alt="a.nombre"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-white font-black text-sm">
                                    {{ a.nombre.charAt(a.nombre.indexOf(' ') + 1) || 'A' }}
                                </span>
                            </div>
                            <div>
                                <h4 class="font-bold text-xs text-slate-900 dark:text-white line-clamp-1">{{ a.nombre }}</h4>
                                <p class="text-[11px] text-pink-600 font-semibold line-clamp-1">{{ a.cargo }}</p>
                                <span class="text-[10px] text-slate-400">{{ a.grado_academico }}</span>
                            </div>
                        </div>
                        <Button
                            icon="pi pi-trash"
                            size="small"
                            severity="danger"
                            text
                            @click="eliminarAutoridad(a)"
                            class="cursor-pointer shrink-0"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL NUEVA PORTADA (HERO SLIDER) -->
        <Dialog v-model:visible="modalPortadaVisible" header="Nueva Portada para Hero Slider" modal class="w-full max-w-lg mx-4">
            <div class="space-y-4 pt-2 text-xs">
                <div>
                    <label class="block font-bold mb-1">Título de la Portada</label>
                    <InputText v-model="formPortada.titulo" placeholder="Ej: Convocatoria Admisión 2026-I" class="w-full text-xs" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Subtítulo Descriptivo</label>
                    <InputText v-model="formPortada.subtitulo" placeholder="Texto explicativo para la portada" class="w-full text-xs" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Badge Superior</label>
                        <InputText v-model="formPortada.badge" placeholder="Ej: Admisiones Abiertas" class="w-full text-xs" />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Texto del Botón</label>
                        <InputText v-model="formPortada.link_texto" placeholder="Ej: Postúlate Aquí" class="w-full text-xs" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Enlace / Destino</label>
                        <InputText v-model="formPortada.link_url" placeholder="#modal-postular o #carreras" class="w-full text-xs" />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Orden de Presentación</label>
                        <InputText v-model.number="formPortada.orden" type="number" class="w-full text-xs" />
                    </div>
                </div>
                <div>
                    <label class="block font-bold mb-1">Imagen de Portada (Almacenada en Storage)</label>
                    <input
                        type="file"
                        accept="image/*"
                        @change="onArchivoPortada"
                        class="w-full text-xs p-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900"
                    />
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="modalPortadaVisible = false" class="cursor-pointer" />
                <Button label="Guardar Portada" severity="primary" @click="guardarPortada" class="cursor-pointer" />
            </template>
        </Dialog>

        <!-- MODAL NUEVO COMUNICADO -->
        <Dialog v-model:visible="modalPubVisible" header="Nuevo Comunicado o Noticia" modal class="w-full max-w-lg mx-4">
            <div class="space-y-4 pt-2 text-xs">
                <div>
                    <label class="block font-bold mb-1">Título</label>
                    <InputText v-model="formPub.titulo" placeholder="Título oficial" class="w-full text-xs" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Tipo</label>
                        <select v-model="formPub.tipo" class="w-full p-2 border rounded-xl text-xs bg-white dark:bg-slate-900">
                            <option value="Comunicado">Comunicado</option>
                            <option value="Convocatoria">Convocatoria</option>
                            <option value="Noticia">Noticia</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Autor / Unidad</label>
                        <InputText v-model="formPub.autor" class="w-full text-xs" />
                    </div>
                </div>
                <div>
                    <label class="block font-bold mb-1">Resumen Breve</label>
                    <InputText v-model="formPub.descripcion" class="w-full text-xs" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Contenido Detallado</label>
                    <textarea v-model="formPub.contenido" rows="3" class="w-full p-2 border rounded-xl text-xs bg-white dark:bg-slate-900"></textarea>
                </div>
                <div>
                    <label class="block font-bold mb-1">Imagen Ilustrativa (Storage)</label>
                    <input
                        type="file"
                        accept="image/*"
                        @change="onArchivoPub"
                        class="w-full text-xs p-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900"
                    />
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="modalPubVisible = false" class="cursor-pointer" />
                <Button label="Publicar" severity="primary" @click="guardarPublicacion" class="cursor-pointer" />
            </template>
        </Dialog>

        <!-- MODAL NUEVA AUTORIDAD -->
        <Dialog v-model:visible="modalAutVisible" header="Registrar Autoridad" modal class="w-full max-w-md mx-4">
            <div class="space-y-4 pt-2 text-xs">
                <div>
                    <label class="block font-bold mb-1">Nombre Completo</label>
                    <InputText v-model="formAut.nombre" placeholder="Ing. Juan Pérez" class="w-full text-xs" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Cargo Institucional</label>
                    <InputText v-model="formAut.cargo" placeholder="Director de Carrera" class="w-full text-xs" />
                </div>
                <div>
                    <label class="block font-bold mb-1">Grado Académico</label>
                    <InputText v-model="formAut.grado_academico" placeholder="M.Sc. en Computación" class="w-full text-xs" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold mb-1">Correo</label>
                        <InputText v-model="formAut.correo" class="w-full text-xs" />
                    </div>
                    <div>
                        <label class="block font-bold mb-1">Teléfono</label>
                        <InputText v-model="formAut.telefono" class="w-full text-xs" />
                    </div>
                </div>
                <div>
                    <label class="block font-bold mb-1">Fotografía (Storage)</label>
                    <input
                        type="file"
                        accept="image/*"
                        @change="onArchivoAut"
                        class="w-full text-xs p-2 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900"
                    />
                </div>
            </div>
            <template #footer>
                <Button label="Cancelar" text severity="secondary" @click="modalAutVisible = false" class="cursor-pointer" />
                <Button label="Registrar" severity="primary" @click="guardarAutoridad" class="cursor-pointer" />
            </template>
        </Dialog>
    </AuthenticatedLayout>
</template>
