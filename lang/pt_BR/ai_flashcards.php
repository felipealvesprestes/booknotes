<?php

return [
    'generated' => '{1} :count flashcard foi gerado com IA e adicionado à disciplina :discipline.|[2,*] :count flashcards foram gerados com IA e adicionados à disciplina :discipline.',
    'limit_remaining' => '{1} Você só pode gerar mais :count flashcard hoje.|[2,*] Você só pode gerar mais :count flashcards hoje.',
    'limit_reached' => 'Você atingiu o limite diário de flashcards com IA. Tente novamente amanhã.',
    'used_today' => '{1} Você já gerou :count flashcard com IA hoje.|[2,*] Você já gerou :count flashcards com IA hoje.',
    'remaining_today' => '{1} Ainda pode gerar :count flashcard hoje.|[2,*] Ainda pode gerar :count flashcards hoje.',
    'remaining_after' => '{1} restará :count flashcard hoje.|[2,*] restarão :count flashcards hoje.',
    'batch_size' => '{1} :count cartão neste lote.|[2,*] :count cartões neste lote.',
    'used_title' => 'Gerados hoje com IA',
    'remaining_title' => 'Ainda disponível hoje',
    'no_response' => 'A IA não retornou nenhum flashcard. Ajuste o tema e tente novamente.',
    'save_error' => 'Não foi possível salvar os flashcards gerados. Tente de novo.',
    'extra_note' => 'Nota extra: :extra',
    'errors' => [
        'missing_api' => 'A chave da API da OpenAI não está configurada. Peça suporte antes de gerar flashcards.',
        'unreachable' => 'Não conseguimos contatar o serviço de IA. Tente novamente em instantes.',
        'empty_response' => 'A resposta da IA veio vazia. Tente outro pedido.',
        'invalid_response' => 'Não conseguimos interpretar a resposta da IA. Tente novamente.',
        'no_flashcards' => 'A resposta da IA não continha flashcards válidos.',
    ],
];
