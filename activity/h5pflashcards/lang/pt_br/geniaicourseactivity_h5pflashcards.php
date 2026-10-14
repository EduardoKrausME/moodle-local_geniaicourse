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
 * @package geniaicourseactivity_h5pflashcards
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['h5p_progressText'] = 'Cartão @card de @total';
$string['h5p_next'] = 'Próximo';
$string['h5p_previous'] = 'Anterior';
$string['h5p_checkAnswerText'] = 'Verificar';
$string['h5p_defaultAnswerText'] = 'Sua resposta';
$string['h5p_correctAnswerText'] = 'Correto';
$string['h5p_incorrectAnswerText'] = 'Incorreto';
$string['h5p_showSolutionText'] = 'Resposta(s) correta(s)';
$string['h5p_results'] = 'Resultados';
$string['h5p_cardsHeader'] = 'Cartões';
$string['h5p_scoreHeader'] = 'Pontuação';
$string['h5p_ofCorrect'] = '@score de @total corretos';
$string['h5p_showResults'] = 'Mostrar resultados';
$string['h5p_answerShortText'] = 'R:';
$string['h5p_retry'] = 'Tentar novamente';
$string['h5p_cardAnnouncement'] = 'Resposta incorreta. A resposta correta era @answer';
$string['h5p_correctAnswerAnnouncement'] = '@answer está correto.';
$string['h5p_pageAnnouncement'] = 'Página @current de @total';
$string['h5p_defaultinstruction'] = 'Revise os cartões e digite a resposta correta.';


$string['privacy:metadata'] = 'O gerador cria atividades nativas do Moodle e não mantém informações próprias dos usuários.';

$string['description'] = 'Cria uma atividade H5P nativa de flashcards para prática de recuperação e memorização.';
$string['h5pcontentcreatefailed'] = 'O Moodle não conseguiu montar o conteúdo H5P. {$a}';
$string['h5pexportmissing'] = 'O editor H5P não gerou o pacote de exportação da atividade.';
$string['h5pinvalidcontent'] = 'O conteúdo gerado não é suficiente para criar {$a}.';
$string['h5plibrarymissing'] = 'A biblioteca H5P necessária não está instalada ou habilitada no Moodle: {$a}';
$string['pluginname'] = 'H5P Flashcards';
