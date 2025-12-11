<?php

return [
    'title' => 'Flashcards from PDF',
    'menu' => 'PDF flashcards',
    'description' => 'Upload a PDF, choose the discipline, and let AI create flashcards without storing your file.',
    'errors' => [
        'parse_failed' => 'We could not read this PDF. Please upload a valid file under the limits.',
        'too_many_pages' => 'This PDF has :pages pages. The limit is :max pages.',
        'empty_text' => 'We could not extract readable text from this PDF.',
        'invalid_discipline' => 'Select a valid discipline to receive the flashcards.',
        'try_again' => 'Check the file and try again.',
    ],
    'status' => [
        'generated' => '{1} :count flashcard created for :discipline.|[2,*] :count flashcards created for :discipline.',
        'pages_used' => '{1} :count page processed from the PDF.|[2,*] :count pages processed from the PDF.',
    ],
    'empty' => [
        'title' => 'Add a discipline first',
        'description' => 'Create a discipline to receive the flashcards generated from your PDF.',
    ],
    'form' => [
        'title' => 'Import and generate flashcards',
        'subtitle' => 'We block PDFs above :max pages and discard the file right after processing.',
        'limit_badge' => 'Limit: :max pages',
        'pdf_label' => 'Select a PDF',
        'pdf_placeholder' => 'Drop your PDF here or click to browse',
        'pdf_hint' => 'The PDF is only used to extract text and is not stored.',
        'page_limit' => 'Up to :max pages',
        'size_limit' => 'Max :size MB',
        'discipline_label' => 'Discipline',
        'quantity_label' => 'Flashcards to generate',
        'quantity_option' => '{1} :count flashcard|[2,*] :count flashcards',
        'limit_warning' => 'Daily limit reached. You need at least :min available flashcards to run this request.',
        'helper' => 'We trim the PDF text to about :chars characters to keep the AI fast and focused.',
        'submit' => 'Generate flashcards',
        'steps_title' => 'How it works',
        'step_upload' => 'Upload a PDF with up to the allowed pages.',
        'step_select' => 'Choose the discipline that will receive the flashcards.',
        'step_generate' => 'Pick the quantity and generate with AI.',
    ],
    'usage' => [
        'used' => 'Generated today',
        'remaining' => 'Remaining',
        'total' => 'Daily limit',
    ],
    'helpers' => [
        'privacy' => 'Your PDF stays private and is removed after processing.',
        'performance' => 'We only send a lean chunk of the text so generation stays fast.',
    ],
    'fields' => [
        'pdf' => 'PDF file',
        'discipline' => 'discipline',
        'quantity' => 'flashcard quantity',
    ],
];
