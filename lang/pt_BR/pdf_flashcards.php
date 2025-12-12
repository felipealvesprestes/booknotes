<?php

return [
    'title' => 'Flashcards a partir do PDF',
    'menu' => 'Flashcards via PDF',
    'description' => 'Envie um PDF, escolha a disciplina e deixe a IA criar os flashcards para você.',
    'errors' => [
        'parse_failed' => 'Não conseguimos ler este PDF. Envie um arquivo válido dentro dos limites.',
        'too_many_pages' => 'Este PDF tem :pages páginas. O limite é de :max páginas. <a href="https://smallpdf.com/pt/dividir-pdf" target="_blank" rel="noopener noreferrer" class="font-semibold underline underline-offset-2">Divida o PDF com Smallpdf</a> e tente novamente.',
        'empty_text' => 'Não foi possível extrair texto legível deste PDF.',
        'invalid_discipline' => 'Selecione uma disciplina válida para receber os flashcards.',
        'try_again' => 'Divida o PDF em partes de até :max páginas e tente novamente.',
        'too_large' => 'O PDF ultrapassa o limite de :size MB. Divida o arquivo com <a href="https://smallpdf.com/pt/dividir-pdf" target="_blank" rel="noopener noreferrer" class="font-semibold underline underline-offset-2">Smallpdf</a> e tente novamente.',
    ],
    'status' => [
        'generated' => '{1} :count flashcard criado para :discipline.|[2,*] :count flashcards criados para :discipline.',
        'pages_used' => '{1} :count página processada do PDF.|[2,*] :count páginas processadas do PDF.',
    ],
    'empty' => [
        'title' => 'Crie uma disciplina primeiro',
        'description' => 'Crie uma disciplina para receber os flashcards gerados do seu PDF.',
    ],
    'form' => [
        'title' => 'Importar e gerar flashcards',
        'subtitle' => 'Bloqueamos PDFs acima de :max páginas e descartamos o arquivo após o processamento.',
        'limit_badge' => 'Limite: :max páginas',
        'limit_help' => 'PDF muito grande? Use <a href="https://smallpdf.com/pt/dividir-pdf" target="_blank" rel="noopener noreferrer" class="font-semibold text-indigo-100 underline underline-offset-2 hover:text-white">Smallpdf</a> para dividir o arquivo antes de enviar.',
        'limit_help_label' => 'Ajuda sobre limite de páginas',
        'pdf_label' => 'Selecione um PDF',
        'pdf_placeholder' => 'Solte o PDF aqui ou clique para buscar',
        'pdf_hint' => 'O PDF é usado apenas para extrair texto e não é salvo.',
        'page_limit' => 'Até :max páginas (PDFs maiores precisam ser divididos)',
        'size_limit' => 'Máximo de :size MB',
        'discipline_label' => 'Disciplina',
        'quantity_label' => 'Quantidade de flashcards',
        'quantity_option' => '{1} :count flashcard|[2,*] :count flashcards',
        'limit_warning' => 'Limite diário atingido. Você precisa de pelo menos :min flashcards disponíveis para usar esta ação.',
        'helper' => 'Reduzimos o texto do PDF para cerca de :chars caracteres para manter a IA rápida e focada.',
        'submit' => 'Gerar flashcards',
        'steps_title' => 'Como funciona',
        'step_upload' => 'Envie um PDF dentro do limite de páginas.',
        'step_select' => 'Escolha a disciplina que receberá os flashcards.',
        'step_generate' => 'Defina a quantidade e gere com a IA.',
    ],
    'usage' => [
        'used' => 'Gerados hoje',
        'remaining' => 'Restantes',
        'total' => 'Limite diário',
    ],
    'helpers' => [
        'privacy' => 'Seu PDF permanece privado e é removido após o processamento.',
        'performance' => 'Enviamos apenas um trecho reduzido para manter a geração rápida.',
    ],
    'fields' => [
        'pdf' => 'arquivo PDF',
        'discipline' => 'disciplina',
        'quantity' => 'quantidade de flashcards',
    ],
];
