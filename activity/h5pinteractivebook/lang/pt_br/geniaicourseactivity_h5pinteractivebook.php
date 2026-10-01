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
 * @package geniaicourseactivity_h5pinteractivebook
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['description'] = 'Cria um H5P Interactive Book e reaproveita os subplugins H5P selecionados como páginas do livro. Marque o livro junto com um ou vários tipos H5P para colocá-los dentro dele; marque somente o livro para usar automaticamente os tipos recomendados pela análise.';
$string['h5pbookchildfailed'] = 'Não foi possível adicionar {$a->name} ao Interactive Book: {$a->error}';
$string['h5pbookembedunsupported'] = 'O H5P Interactive Book não consegue incorporar {$a} com as bibliotecas H5P.Column instaladas neste Moodle.';
$string['h5pbookiframewarning'] = 'Estes tipos H5P foram incorporados ao livro por um iframe interno porque a biblioteca H5P.Column instalada não os aceita diretamente como filhos: {$a}. A pontuação própria dessas interações não é agregada ao resumo do Interactive Book.';
$string['h5pbookpagedefault'] = 'Página do livro {$a}';
$string['h5pcontentcreatefailed'] = 'O Moodle não conseguiu montar o conteúdo H5P. {$a}';
$string['h5pexportmissing'] = 'O editor H5P não gerou o pacote de exportação da atividade.';
$string['h5pinvalidcontent'] = 'O conteúdo gerado não é suficiente para criar {$a}.';
$string['h5plibrarymissing'] = 'A biblioteca H5P necessária não está instalada ou habilitada no Moodle: {$a}';
$string['pluginname'] = 'H5P Interactive Book';
