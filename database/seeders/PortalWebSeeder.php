<?php

namespace Database\Seeders;

use App\Models\PortalAutoridad;
use App\Models\PortalInstitucion;
use App\Models\PortalPortada;
use App\Models\PortalPublicacion;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PortalWebSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Institución
        PortalInstitucion::updateOrCreate(
            ['sigla' => 'FAC-TEC'],
            [
                'nombre' => 'Facultad de Ciencia y Tecnología Universitaria',
                'facultad' => 'Área de Ingeniería, Desarrollo Tecnológico y Ciencias Exactas',
                'historia' => 'Nuestra institución académica nació con el firme propósito de democratizar la educación superior de excelencia y formar profesionales de alta competitividad científica, tecnológica y con profundo compromiso social. A lo largo de los años nos hemos consolidado como un referente regional y nacional en investigación aplicada, innovación en software, redes inteligentes y automatización industrial.',
                'mision' => 'Formar profesionales idóneos de excelencia académica, científica, ética y tecnológica con liderazgo e innovación, capaces de proponer soluciones pertinentes a las demandas del desarrollo productivo y social sostenible.',
                'vision' => 'Ser una institución universitaria líder y acreditada a nivel nacional e internacional, reconocida por su excelencia académica, la rigurosidad de sus investigaciones y el impacto transformador de sus graduados en la sociedad.',
                'direccion' => 'Av. Sucre A s/n, Zona Villa Esperanza, Campus Universitario Central',
                'telefono' => '+591 2 2844177',
                'correo' => 'contacto@academico.test',
                'whatsapp' => '+591 71556677',
                'facebook' => 'https://facebook.com/FacultadTecnologia',
            ]
        );

        // 2. Portadas (Hero Slider con imágenes en storage)
        $portadas = [
            [
                'titulo' => 'Convocatoria Oficial: Examen de Admisión 2026-I',
                'subtitulo' => 'Inicia tu formación profesional en Ingeniería de Sistemas, Informática y Telecomunicaciones. Postulaciones abiertas para el nuevo periodo.',
                'badge' => 'Admisiones 2026-I Abiertas',
                'imagen' => 'portal/portadas/banner-admision.svg',
                'link_url' => '#modal-postular',
                'link_texto' => 'Postúlate al Examen Aquí',
                'orden' => 1,
                'activo' => true,
            ],
            [
                'titulo' => 'Liderazgo en Ciencia, Algoritmos y Transformación Digital',
                'subtitulo' => 'Laboratorios de última generación, mallas curriculares actualizadas y cuerpo docente con alta especialización de posgrado.',
                'badge' => 'Excelencia Académica',
                'imagen' => 'portal/portadas/banner-tecnologia.svg',
                'link_url' => '#carreras',
                'link_texto' => 'Ver Carreras y Mallas',
                'orden' => 2,
                'activo' => true,
            ],
            [
                'titulo' => 'Proceso de Inscripción y Matrícula Semestral 2026',
                'subtitulo' => 'Estudiantes regulares y docentes: el sistema de gestión académica se encuentra habilitado para control de notas y seguimiento curricular.',
                'badge' => 'Gestión Académica en Línea',
                'imagen' => 'portal/portadas/banner-academico.svg',
                'link_url' => '#modal-ingresar',
                'link_texto' => 'Ingresar al Sistema',
                'orden' => 3,
                'activo' => true,
            ],
        ];

        foreach ($portadas as $p) {
            PortalPortada::updateOrCreate(['titulo' => $p['titulo']], $p);
        }

        // 3. Autoridades (con fotos de muestra en storage)
        $autoridades = [
            [
                'nombre' => 'Ing. Carlos Mendoza Alarcón',
                'cargo' => 'Director de Carrera - Ingeniería de Sistemas',
                'grado_academico' => 'M.Sc. en Seguridad Informática y Redes',
                'foto' => 'portal/autoridades/director-sistemas.svg',
                'telefono' => '71550001',
                'correo' => 'director@academico.test',
                'orden' => 1,
                'activo' => true,
            ],
            [
                'nombre' => 'M.Sc. Patricia Zambrana Quispe',
                'cargo' => 'Directora de Carrera - Ciencias de la Computación e Informática',
                'grado_academico' => 'Doctora en Inteligencia Artificial y Machine Learning',
                'foto' => 'portal/autoridades/directora-informatica.svg',
                'telefono' => '71550002',
                'correo' => 'director.informatica@academico.test',
                'orden' => 2,
                'activo' => true,
            ],
            [
                'nombre' => 'Lic. Elena Arze Miranda',
                'cargo' => 'Presidenta de la Comisión Académica y Evaluación Curricular',
                'grado_academico' => 'Especialista en Acreditación Universitaria',
                'foto' => 'portal/autoridades/presidenta-comision.svg',
                'telefono' => '71550003',
                'correo' => 'comision@academico.test',
                'orden' => 3,
                'activo' => true,
            ],
            [
                'nombre' => 'Lic. Gonzalo Valenzuela Torrez',
                'cargo' => 'Encargado de Kardex Académico Central y Admisiones',
                'grado_academico' => 'Lic. en Administración Educativa',
                'foto' => 'portal/autoridades/kardex-central.svg',
                'telefono' => '71550004',
                'correo' => 'kardex@academico.test',
                'orden' => 4,
                'activo' => true,
            ],
        ];

        foreach ($autoridades as $a) {
            PortalAutoridad::updateOrCreate(['nombre' => $a['nombre']], $a);
        }

        // 4. Publicaciones y Comunicados (con imágenes ilustrativas en storage)
        $publicaciones = [
            [
                'titulo' => 'Convocatoria Oficial al Examen de Admisión Periodo Académico 2026-I',
                'tipo' => 'Convocatoria',
                'descripcion' => 'Se invita a todos los bachilleres y aspirantes a postularse al examen de suficiencia académica para el ingreso a las carreras universitarias.',
                'contenido' => 'La Dirección de Carrera y la Unidad de Kardex comunican que el registro de postulantes se encuentra disponible en línea. El examen presencial se llevará a cabo en las aulas del Campus Central.',
                'imagen' => 'portal/publicaciones/convocatoria-admision.svg',
                'fecha' => Carbon::now()->subDays(3),
                'autor' => 'Dirección de Admisiones',
                'activo' => true,
            ],
            [
                'titulo' => 'Cronograma de Registro y Subida de Calificaciones del 1er Parcial',
                'tipo' => 'Comunicado',
                'descripcion' => 'Comunicado dirigido a todos los docentes titulares para el cumplimiento del plazo habilitado en el sistema académico.',
                'contenido' => 'De acuerdo a la Resolución Académica vigente, las fechas de habilitación para el asentamiento de notas del Primer Ciclo concluyen según el calendario establecido en el módulo de Configuración Académica.',
                'imagen' => 'portal/publicaciones/cronograma-notas.svg',
                'fecha' => Carbon::now()->subDays(1),
                'autor' => 'Kardex Académico',
                'activo' => true,
            ],
            [
                'titulo' => 'Exitosa Participación Estudiantil en la Feria Anual de Ciencia y Robótica',
                'tipo' => 'Noticia',
                'descripcion' => 'Nuestros estudiantes de semestres avanzados obtuvieron los primeros lugares con proyectos de visión artificial e Internet de las Cosas (IoT).',
                'contenido' => 'Más de 40 proyectos innovadores fueron presentados en los laboratorios de especialidad, demostrando la alta capacidad inventiva y técnica de la comunidad estudiantil.',
                'imagen' => 'portal/publicaciones/feria-robotica.svg',
                'fecha' => Carbon::now()->subDays(7),
                'autor' => 'Comisión de Investigación',
                'activo' => true,
            ],
            [
                'titulo' => 'Publicación de Malla Curricular Actualizada con Prerrequisitos por Semestre',
                'tipo' => 'Comunicado',
                'descripcion' => 'Los planes de estudio de las tres carreras han sido organizados con rigurosidad académica para optimizar el avance estudiantil.',
                'contenido' => 'Cada estudiante puede verificar a través de su portal institucional las materias aprobadas y los requisitos obligatorios de semestres anteriores necesarios para su inscripción.',
                'imagen' => 'portal/publicaciones/malla-curricular.svg',
                'fecha' => Carbon::now()->subDays(10),
                'autor' => 'Comisión Académica',
                'activo' => true,
            ],
        ];

        foreach ($publicaciones as $pub) {
            PortalPublicacion::updateOrCreate(['titulo' => $pub['titulo']], $pub);
        }
    }
}
