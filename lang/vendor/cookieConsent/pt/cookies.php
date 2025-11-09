<?php
return [
    'title' => 'Controlamos cookies juntos',
    'intro' => 'O ' . config('app.name') . ' usa cookies para garantir uma sessão segura e adaptar os recursos às suas preferências. Nada é ativado sem o seu consentimento.',
    'link' => 'Consulte a nossa <a href=":url">Política de Cookies</a> para ver os detalhes técnicos.',

    'essentials' => 'Somente essenciais',
    'all' => 'Aceitar todos',
    'customize' => 'Personalizar',
    'manage' => 'Gerenciar cookies',
    'details' => [
        'more' => 'Mais detalhes',
        'less' => 'Menos detalhes',
    ],
    'save' => 'Salvar configurações',
    'cookie' => 'Cookie',
    'purpose' => 'Propósito',
    'duration' => 'Duração',
    'year' => 'Ano|Anos',
    'day' => 'Dia|Dias',
    'hour' => 'Hora|Horas',
    'minute' => 'Minuto|Minutos',

    'categories' => [
        'essentials' => [
            'title' => 'Essenciais',
            'description' => 'Garantem login, segurança e lembram o consentimento. Sem eles a plataforma não consegue funcionar de forma estável.',
        ],
        'analytics' => [
            'title' => 'Analíticos',
            'description' => 'Quando autorizados, monitoram de forma anônima como o produto é utilizado para priorizarmos melhorias reais.',
        ],
        'optional' => [
            'title' => 'Experiência',
            'description' => 'Personalizam a plataforma com preferências visuais e fluxos extras. São totalmente opcionais.',
        ],
    ],

    'defaults' => [
        'consent' => 'Utilizado para armazenar as preferências de consentimento de cookies do usuário.',
        'session' => 'Utilizado para identificar a sessão de navegação do usuário.',
        'csrf' => 'Utilizado para proteger tanto o usuário como o nosso sítio contra ataques de falsificação de solicitações entre sítios.',
        '_ga' => 'Principal cookie utilizado pelo Google Analytics, permite que um serviço distingua um visitante do outro.',
        '_ga_ID' => 'Utilizado pelo Google Analytics para persistir o estado da sessão.',
        '_gid' => 'Utilizado pelo Google Analytics para identificar o usuário.',
        '_gat' => 'Utilizado pelo Google Analytics para limitar a taxa de requisições.',
    ]
];
