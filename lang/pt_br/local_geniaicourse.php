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
 * GeniAI Course Builder.
 *
 * @package local_geniaicourse
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['activity'] = 'Atividade';
$string['analysiserror'] = 'A análise da IA falhou: {$a}';
$string['analyze'] = 'Analisar fontes';
$string['analyzing'] = 'Analisando...';
$string['backtocourse'] = 'Voltar ao curso';
$string['confidence'] = 'Confiança';
$string['create'] = 'Criar atividades selecionadas';
$string['created'] = 'Criado';
$string['createdtitle'] = 'Atividades criadas';
$string['creating'] = 'Criando...';
$string['emptyairesponse'] = 'O provedor de IA retornou uma resposta vazia.';
$string['extractionerror'] = 'Erro de extração: {$a}';
$string['failed'] = 'Falhou';
$string['fileinstruction'] = 'O que deve ser feito com este arquivo?';
$string['fileinstructionplaceholder'] = 'Ex.: Use apenas este print na página inicial';
$string['files'] = 'Arquivos';
$string['files_help'] = 'Os formatos de arquivo aceitos dependem dos subplugins de atividade instalados.';
$string['geniaicourse:use'] = 'Usar o construtor de cursos GeniAI';
$string['h5pembedfailed'] = 'Não foi possível incorporar a mídia ao pacote H5P gerado.';
$string['imagewarning'] = 'Os pixels da imagem não são enviados ao AI Bridge por este plugin. Os analisadores usam o nome do arquivo, tipo MIME e a instrução do professor.';
$string['instruction'] = 'Instrução';
$string['invalidjsonresponse'] = 'O provedor de IA retornou um JSON inválido.';
$string['invalidplugin'] = 'Tipo de atividade GeniAI desconhecido: {$a}';
$string['legacyofficewarning'] = 'Formato Office binário legado: a extração de texto é aproximada. Recomenda-se DOCX/XLSX/PPTX.';
$string['maxextractchars'] = 'Máximo de caracteres extraídos por fonte';
$string['maxextractchars_desc'] = 'Quantidade máxima de texto da fonte enviada para cada analisador de atividade.';
$string['maxuploadexceeded'] = 'O arquivo {$a->filename} excede o tamanho máximo permitido de {$a->limit}.';
$string['mcp_banner_badge'] = 'INTEGRAÇÃO COM IA';
$string['mcp_banner_benefit_control'] = 'Confirmação obrigatória para alterações';
$string['mcp_banner_benefit_discovery'] = 'Descoberta automática de ferramentas';
$string['mcp_banner_benefit_security'] = 'Permissões do Moodle respeitadas';
$string['mcp_banner_cta'] = 'Conhecer o Moodle MCP';
$string['mcp_banner_description'] = 'Instale o local_mcp para disponibilizar os recursos do GeniAI Course Builder ao ChatGPT, respeitando as permissões do Moodle.';
$string['mcp_banner_dismiss'] = 'Não mostrar novamente';
$string['mcp_banner_title'] = 'Use o GeniAI Course Builder com o ChatGPT';
$string['mcpalreadyanalysed'] = 'Esta fonte já possui análise salva; utilize force para substituí-la.';
$string['mcpanalysesource'] = 'Analisar uma fonte do projeto';
$string['mcpanalysesourcedesc'] = 'Após prévia e confirmação, solicita a todos os geradores instalados a análise de uma fonte pelo local_ai_bridge. Pode consumir créditos de IA, sem criar atividades Moodle.';
$string['mcpcreateproject'] = 'Criar projeto GeniAI para curso';
$string['mcpcreateprojectdesc'] = 'Cria um projeto do professor a partir de texto e o salva para adição de fontes e análise, sem chamadas à IA.';
$string['mcpfilelimit'] = 'O arquivo ultrapassa o limite de envio do MCP ou do curso Moodle.';
$string['mcpgenerators'] = 'Listar geradores de atividades do curso';
$string['mcpgeneratorsdesc'] = 'Lista os geradores GeniAI instalados e verifica se o professor conectado pode criar as atividades correspondentes.';
$string['mcpgetanalysis'] = 'Consultar análise salva de um gerador';
$string['mcpgetanalysisdesc'] = 'Lê um trecho limitado do plano salvo do gerador de um projeto do professor conectado, sem IA nem alterações.';
$string['mcpgetproject'] = 'Consultar meu projeto GeniAI';
$string['mcpgetprojectdesc'] = 'Consulta estado, metadados das fontes, resumos dos geradores e hash de concorrência, com paginação e acesso restrito ao dono.';
$string['mcpinvalidinstruction'] = 'A instrução do arquivo excede o limite.';
$string['mcpinvalidprompt'] = 'Informe um texto de projeto não vazio com até 16000 caracteres.';
$string['mcpinvalidselection'] = 'Selecione combinações válidas e distintas de fonte, gerador e seção deste projeto.';
$string['mcplistprojects'] = 'Listar meus projetos GeniAI';
$string['mcplistprojectsdesc'] = 'Lista somente os projetos existentes do professor conectado neste curso, com paginação.';
$string['mcppublish'] = 'Publicar atividades GeniAI selecionadas';
$string['mcppublishbusy'] = 'Já existe uma publicação em andamento para este projeto.';
$string['mcppublishdesc'] = 'Confirma fontes e geradores selecionados para publicar atividades Moodle ou questões nativas. A publicação não admite repetição e pode criar vários registros.';
$string['mcpstaleproject'] = 'O projeto mudou desde a consulta. Consulte-o novamente para obter o hash atualizado.';
$string['mcpuploadsource'] = 'Enviar arquivo-fonte ao projeto GeniAI';
$string['mcpuploadsourcedesc'] = 'Após confirmação, adiciona um arquivo Base64 ao projeto usando os serviços de arquivos e extração do Moodle. Limite de 8 MiB decodificados; não busca URLs nem chama IA.';
$string['missinganalysis'] = 'O tipo de atividade selecionado não possui uma análise salva para esta fonte.';
$string['multipleselectionhint'] = 'Você pode selecionar um ou vários tipos de atividade para esta fonte. Subplugins consumidores podem combinar tipos selecionados em uma única atividade gerada.';
$string['navtitle'] = 'Criar com IA';
$string['newanalysis'] = 'Iniciar outra análise';
$string['noextractorwarning'] = 'Não há extrator de texto disponível para este tipo de arquivo.';
$string['nosources'] = 'Digite um prompt ou envie pelo menos um arquivo.';
$string['notsuggested'] = 'Não sugerido';
$string['pdffallbackwarning'] = 'O pdftotext não estava disponível ou não retornou texto; foi usada a extração interna de PDF, que pode ser incompleta.';
$string['pdfscannedwarning'] = 'O PDF pode ser digitalizado ou composto apenas por imagens.';
$string['pdftotextpath'] = 'Executável pdftotext';
$string['pdftotextpath_desc'] = 'Caminho do pdftotext. Se não estiver disponível, é usado um extrator interno de PDF em modo de melhor esforço.';
$string['pluginname'] = 'GeniAI Course Builder';
$string['preview'] = 'Prévia extraída';
$string['privacy:metadata:aibridge'] = 'O material-fonte é enviado pelo local_ai_bridge ao provedor de IA selecionado pela rota configurada para que os subplugins de atividade instalados possam analisá-lo.';
$string['privacy:metadata:aibridge:filename'] = 'O nome do arquivo enviado, o tipo MIME e a extensão são enviados para análise.';
$string['privacy:metadata:aibridge:instruction'] = 'A instrução do professor associada a cada fonte é enviada para análise.';
$string['privacy:metadata:aibridge:prompt'] = 'O prompt geral do professor é enviado para análise.';
$string['privacy:metadata:aibridge:sourcecontent'] = 'O texto extraído do arquivo enviado ou do texto colado é enviado para análise.';
$string['privacy:metadata:files'] = 'Os arquivos de origem são armazenados temporariamente no contexto do usuário.';
$string['privacy:metadata:project'] = 'Armazena projetos de criação de curso com IA feitos pelo usuário.';
$string['privacy:metadata:project:courseid'] = 'Curso onde o projeto está sendo criado.';
$string['privacy:metadata:project:prompt'] = 'Prompt informado pelo usuário.';
$string['privacy:metadata:project:userid'] = 'Usuário que criou o projeto.';
$string['privacy:metadata:source'] = 'Armazena arquivos enviados, instruções e texto extraído das fontes.';
$string['privacy:metadata:source:analysisjson'] = 'Análise de IA devolvida pelos subplugins instalados.';
$string['privacy:metadata:source:extractedtext'] = 'Texto extraído da fonte enviada.';
$string['privacy:metadata:source:instruction'] = 'Instrução específica informada para o arquivo.';
$string['projectalreadycreated'] = 'Esta análise já foi processada. Inicie uma nova análise para criar outro conjunto de atividades.';
$string['prompt'] = 'Prompt / texto colado';
$string['prompt_help'] = 'Descreva o que deve ser criado. Você também pode colar o próprio conteúdo de origem aqui.';
$string['reason'] = 'Motivo';
$string['reviewsubtitle'] = 'Selecione uma ou várias atividades Moodle para cada fonte. Deixe todas desmarcadas para ignorar uma fonte.';
$string['reviewtitle'] = 'Revise as sugestões da IA';
$string['section'] = 'Seção do curso';
$string['skip'] = 'Não criar nada';
$string['source'] = 'Fonte';
$string['subplugintype_geniaicourseactivity'] = 'Atividade do GeniAI Course';
$string['subplugintype_geniaicourseactivity_plural'] = 'Atividades do GeniAI Course';
$string['subtitle'] = 'Descreva o que deseja e anexe os arquivos de origem. Cada subplugin de atividade instalado analisa cada fonte antes de qualquer criação.';
$string['suggested'] = 'Sugerido';
$string['suggestedtitle'] = 'Título sugerido';
$string['summary'] = 'Resumo';
$string['taskcleanup'] = 'Limpar projetos antigos do GeniAI Course Builder';
$string['title'] = 'Criar conteúdo do curso com IA';
$string['truncatedwarning'] = 'O texto extraído foi limitado a {$a} caracteres para análise pela IA.';
$string['unsupportedfile'] = 'Tipo de arquivo não suportado: {$a}';
$string['uploaderror'] = 'Não foi possível enviar {$a}.';
