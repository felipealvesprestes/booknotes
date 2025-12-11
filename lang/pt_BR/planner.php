<?php

return [
    'title' => 'Roteiro de estudo',
    'heading' => 'Roteiro de estudo',
    'description' => 'Configure sua rotina de estudos semanal e aproveite cada modo de estudo.',

    'today_label' => 'Roteiro de hoje',
    'today_description' => 'Conclua as tarefas abaixo para fechar o dia com foco.',
    'today_empty_title' => 'Nenhuma tarefa gerada para hoje.',
    'today_empty_description' => 'Inclua disciplinas no planner para receber novas sessões automaticamente.',

    'upcoming_label' => 'Próximos dias',
    'upcoming_title' => 'O que vem depois',
    'upcoming_description' => 'Mantemos os próximos dias prontos para que você não precise montar calendários.',
    'upcoming_count' => '{1} :count tarefa agendada|[2,*] :count tarefas agendadas',
    'upcoming_empty_title' => 'Sem tarefas futuras ainda.',
    'upcoming_empty_description' => 'Assim que o plano for salvo iremos organizar os próximos dias para você.',

    'plan_label' => 'Configuração do plano',
    'plan_title' => 'Personalize seu ritmo',
    'plan_description' => 'Configure sua rotina de estudos semanal e aproveite cada modo de estudo.',

    'stats' => [
        'pending_today' => 'Pendentes',
        'completed_today' => 'Concluídas hoje',
        'disciplines' => 'Disciplinas no plano',
        'weekly_sessions' => 'Sessões semanais',
    ],

    'modes' => [
        'flashcards' => 'Flashcards',
        'true_false' => 'Verdadeiro ou falso',
        'fill_blank' => 'Completar lacuna',
        'multiple_choice' => 'Múltipla escolha',
        'simulated' => 'Simulado',
    ],

    'statuses' => [
        'pending' => 'Pendente',
        'completed' => 'Concluída',
        'cancelled' => 'Cancelada',
    ],

    'labels' => [
        'overdue' => 'Atrasada',
        'any_discipline' => 'Qualquer disciplina',
        'scheduled_for' => 'Agendada para :date',
        'mode_prefix' => 'Modo:',
        'days_suffix' => 'dias',
        'today_compact' => 'Hoje',
        'reset_warning' => 'Isso vai remover todas as rotinas e tarefas geradas. Deseja continuar?',
    ],

    'units' => [
        'cards' => '{1} :count card|[2,*] :count cards',
        'questions' => '{1} :count questão|[2,*] :count questões',
        'gaps' => '{1} :count lacuna|[2,*] :count lacunas',
        'exam' => '{1} :count simulado|[2,*] :count simulados',
        'items' => '{1} :count item|[2,*] :count itens',
    ],

    'form' => [
        'study_days' => 'Dias de estudo na semana',
        'disciplines' => 'Disciplinas do planner',
        'selected_count' => ':count selecionada(s)',
        'disciplines_hint' => 'Listamos apenas disciplinas que já possuem flashcards.',
        'empty_disciplines_title' => 'Você ainda não criou disciplinas.',
        'empty_disciplines_description' => 'Cadastre pelo menos uma disciplina para montar o planner.',
    ],

    'actions' => [
        'refresh' => 'Atualizar roteiro',
        'new_discipline' => 'Nova disciplina',
        'reset' => 'Apagar roteiro',
        'complete' => 'Concluir',
        'cancel' => 'Pular',
        'reopen' => 'Reabrir',
        'restore' => 'Restaurar',
        'start' => 'Iniciar estudo',
        'save_plan' => 'Salvar plano',
        'update_plan' => 'Atualizar plano',
    ],

    'messages' => [
        'plan_saved' => 'Planner atualizado com sucesso.',
        'tasks_refreshed' => 'Roteiro do dia recalculado.',
    ],

    'reminder' => [
        'title' => 'Você tem :count tarefa(s) para hoje.',
        'subtitle' => 'Conclua seu roteiro de estudo antes de continuar.',
        'cta' => 'Ver roteiro de hoje',
    ],

    'validation' => [
        'select_discipline' => 'Selecione pelo menos uma disciplina com flashcards.',
    ],
];
