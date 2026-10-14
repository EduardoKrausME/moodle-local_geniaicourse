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
 * @package geniaicourseactivity_h5pdragdrop
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['h5p_scoreShow'] = 'Verificar';
$string['h5p_submit'] = 'Enviar';
$string['h5p_tryAgain'] = 'Tentar novamente';
$string['h5p_scoreExplanation'] = 'Respostas corretas somam pontos. Respostas incorretas podem reduzir a pontuação.';
$string['h5p_fullscreen'] = 'Tela cheia';
$string['h5p_exitFullscreen'] = 'Sair da tela cheia';
$string['h5p_grabbablePrefix'] = 'Item arrastável {num} de {total}.';
$string['h5p_grabbableSuffix'] = 'Colocado na área de destino {num}.';
$string['h5p_dropzonePrefix'] = 'Área de destino {num} de {total}.';
$string['h5p_noDropzone'] = 'Nenhuma área de destino.';
$string['h5p_tipLabel'] = 'Mostrar dica.';
$string['h5p_tipAvailable'] = 'Dica disponível';
$string['h5p_correctAnswer'] = 'Resposta correta';
$string['h5p_wrongAnswer'] = 'Resposta incorreta';
$string['h5p_feedbackHeader'] = 'Feedback';
$string['h5p_scoreBarLabel'] = 'Você obteve :num de :total pontos';
$string['h5p_scoreExplanationButtonLabel'] = 'Mostrar explicação da pontuação';
$string['h5p_a11yCheck'] = 'Verificar as respostas. Elas serão marcadas como corretas, incorretas ou não respondidas.';
$string['h5p_a11yRetry'] = 'Recomeçar a atividade. Limpar todas as respostas e tentar novamente.';
$string['h5p_defaultinstruction'] = 'Arraste cada item para o destino correto.';


$string['privacy:metadata'] = 'O gerador cria atividades nativas do Moodle e não mantém informações próprias dos usuários.';

$string['description'] = 'Cria uma atividade H5P nativa de arrastar e soltar para relacionar itens e destinos.';
$string['h5pcontentcreatefailed'] = 'O Moodle não conseguiu montar o conteúdo H5P. {$a}';
$string['h5pexportmissing'] = 'O editor H5P não gerou o pacote de exportação da atividade.';
$string['h5pinvalidcontent'] = 'O conteúdo gerado não é suficiente para criar {$a}.';
$string['h5plibrarymissing'] = 'A biblioteca H5P necessária não está instalada ou habilitada no Moodle: {$a}';
$string['pluginname'] = 'H5P Arrastar e soltar';
