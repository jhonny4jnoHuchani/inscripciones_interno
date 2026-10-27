<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import Card from 'primevue/card';
</script>

<template>
    <Head title="Mi Panel de Estudiante" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Mi Panel
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <Card class="shadow-sm border border-gray-100 dark:border-gray-700">
                    <template #title>
                        <span class="text-gray-900 dark:text-gray-100 font-bold">Portal del Estudiante</span>
                    </template>
                    <template #subtitle>
                        <span class="text-gray-500 dark:text-gray-400">Módulo exclusivo para Estudiantes</span>
                    </template>
                    <template #content>
                        <p class="m-0 text-gray-700 dark:text-gray-300">
                            Bienvenido a tu panel de estudiante. Desde aquí podrás revisar tus materias inscritas, horarios y calificaciones actuales.
                        </p>
                    </template>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
