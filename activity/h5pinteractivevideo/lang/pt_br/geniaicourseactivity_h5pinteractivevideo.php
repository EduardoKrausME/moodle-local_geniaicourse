<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * GeniAI Course activity subplugin.
 *
 * @package geniaicourseactivity_h5pinteractivevideo
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['description'] = 'Cria um H5P Vídeo Interativo nativo do Moodle a partir de um vídeo enviado ou URL de vídeo compatível informada explicitamente.';
$string['h5pcontentcreatefailed'] = 'O Moodle não conseguiu montar o conteúdo H5P. {$a}';
$string['h5pexportmissing'] = 'O editor H5P não gerou o pacote de exportação da atividade.';
$string['h5pinvalidcontent'] = 'O conteúdo gerado não é suficiente para criar {$a}.';
$string['h5plibrarymissing'] = 'A biblioteca H5P necessária não está instalada ou habilitada no Moodle: {$a}';
$string['h5pvideomissing'] = 'Vídeo Interativo exige um arquivo MP4/WebM/OGV/M4V enviado, uma URL do YouTube ou uma URL direta para um arquivo de vídeo compatível.';
$string['pluginname'] = 'H5P Vídeo Interativo';
$string['videowarning'] = 'O vídeo é armazenado para o Interactive Video. Nenhuma transcrição é extraída automaticamente.';
