<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('portal_portadas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('subtitulo')->nullable();
            $table->string('badge')->nullable();
            $table->string('imagen')->nullable();
            $table->string('link_url')->nullable();
            $table->string('link_texto')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('portal_publicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descripcion');
            $table->longText('contenido')->nullable();
            $table->string('imagen')->nullable();
            $table->enum('tipo', ['Noticia', 'Comunicado', 'Convocatoria'])->default('Noticia');
            $table->date('fecha')->nullable();
            $table->string('autor')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('portal_autoridades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('cargo');
            $table->string('grado_academico')->nullable();
            $table->string('foto')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('portal_institucion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('sigla');
            $table->string('facultad')->nullable();
            $table->longText('historia')->nullable();
            $table->text('mision')->nullable();
            $table->text('vision')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('facebook')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_institucion');
        Schema::dropIfExists('portal_autoridades');
        Schema::dropIfExists('portal_publicaciones');
        Schema::dropIfExists('portal_portadas');
    }
};
