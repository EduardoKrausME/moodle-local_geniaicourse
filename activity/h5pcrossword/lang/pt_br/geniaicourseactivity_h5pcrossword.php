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
 * @package geniaicourseactivity_h5pcrossword
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['h5pl10n_across'] = 'Horizontal';
$string['h5pl10n_down'] = 'Vertical';
$string['h5pl10n_checkAnswer'] = 'Verificar';
$string['h5pl10n_submitAnswer'] = 'Enviar';
$string['h5pl10n_tryAgain'] = 'Tentar novamente';
$string['h5pl10n_showSolution'] = 'Mostrar solução';
$string['h5pl10n_couldNotGenerateCrossword'] = 'Não foi possível gerar palavras cruzadas com as palavras fornecidas. Tente novamente com menos palavras ou com palavras que tenham mais letras em comum.';
$string['h5pl10n_couldNotGenerateCrosswordTooFewWords'] = 'Não foi possível gerar palavras cruzadas. São necessárias pelo menos duas palavras.';
$string['h5pl10n_probematicWords'] = 'Algumas palavras não puderam ser posicionadas. Palavras problemáticas: @words';
$string['h5pl10n_problematicWords'] = 'Algumas palavras não puderam ser posicionadas. Palavras problemáticas: @words';
$string['h5pl10n_extraClue'] = 'Dica extra';
$string['h5pl10n_closeWindow'] = 'Fechar janela';
$string['h5pa11y_crosswordGrid'] = 'Grade de palavras cruzadas. Use as setas para navegar e o teclado para inserir letras.';
$string['h5pa11y_column'] = 'Coluna';
$string['h5pa11y_row'] = 'Linha';
$string['h5pa11y_across'] = 'Horizontal';
$string['h5pa11y_down'] = 'Vertical';
$string['h5pa11y_empty'] = 'Vazio';
$string['h5pa11y_resultFor'] = 'Resultado para: @clue';
$string['h5pa11y_correct'] = 'Correto';
$string['h5pa11y_wrong'] = 'Incorreto';
$string['h5pa11y_point'] = 'ponto';
$string['h5pa11y_solutionFor'] = 'Para @clue, a solução é: @solution';
$string['h5pa11y_extraClueFor'] = 'Abrir dica extra para @clue';
$string['h5pa11y_letterSevenOfNine'] = 'Letra @position de @length';
$string['h5pa11y_lettersWord'] = 'Palavra de @length letras';
$string['h5pa11y_check'] = 'Verificar as letras. As respostas serão marcadas como corretas, incorretas ou não respondidas.';
$string['h5pa11y_submitAndcheck'] = 'Enviar a resposta e verificar as letras.';
$string['h5pa11y_showSolution'] = 'Mostrar a solução. As palavras cruzadas serão preenchidas com a solução correta.';
$string['h5pa11y_retry'] = 'Recomeçar a atividade. Limpar todas as respostas e tentar novamente.';
$string['h5pa11y_yourResult'] = 'Você obteve @score de @total pontos';


$string['privacy:metadata'] = 'O gerador cria atividades nativas do Moodle e não mantém informações próprias dos usuários.';

$string['description'] = 'Cria um H5P de palavras cruzadas nativo do Moodle com termos e pistas baseados na fonte.';
$string['h5pcontentcreatefailed'] = 'O Moodle não conseguiu montar o conteúdo H5P. {$a}';
$string['h5pexportmissing'] = 'O editor H5P não gerou o pacote de exportação da atividade.';
$string['h5pinvalidcontent'] = 'O conteúdo gerado não é suficiente para criar {$a}.';
$string['h5plibrarymissing'] = 'A biblioteca H5P necessária não está instalada ou habilitada no Moodle: {$a}';
$string['pluginname'] = 'H5P Palavras cruzadas';
