<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';
</script>

<template>
    <Head title="Mis Cursos" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Mis Cursos
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card class="shadow-sm border border-gray-100 dark:border-gray-700">
                    <template #title>
                        <span class="text-gray-900 dark:text-gray-100 font-bold">Gestión de Cursos</span>
                    </template>
                    <template #subtitle>
                        <span class="text-gray-500 dark:text-gray-400">Módulo exclusivo para Docentes</span>
                    </template>
                    <template #content>
                        <p class="m-0 text-gray-700 dark:text-gray-300">
                            Bienvenido al panel docente. Aquí podrá gestionar los cursos asignados, listas de estudiantes y registro de notas.
                        </p>
                    </template>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
