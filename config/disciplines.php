<?php

// Canonical list shown in the UI.
// "ready" controls whether a discipline is selectable and included in digests.
return [
    'all' => [
        // Natural & Physical
        'math'         => ['label' => 'Mathematics',                         'ready' => true],
        'earth'        => ['label' => 'Earth & Environmental Sciences',      'ready' => true],

        // Life & Health
        'neuroscience' => ['label' => 'Neuroscience',                        'ready' => true],
        'agriculture'  => ['label' => 'Agriculture & Food Science',          'ready' => true],
        'psychology'   => ['label' => 'Psychology & Cognitive Science',      'ready' => true],
        'pharmacology' => ['label' => 'Pharmacology & Drug Discovery',       'ready' => true],

        // Eng/Computing
        'cs'           => ['label' => 'Computer Science & AI',               'ready' => true],
        'informatics'  => ['label' => 'Bioinformatics & Computational Biology','ready' => true],
        'engineering'  => ['label' => 'Engineering (General)',               'ready' => true],

        // Social/Humanities
        'economics'    => ['label' => 'Economics & Finance',                 'ready' => true],
        'linguistics'  => ['label' => 'Linguistics & Language Science',      'ready' => true],
        'law'          => ['label' => 'Law & Legal Studies',                 'ready' => false],
        'arts'         => ['label' => 'Arts & Cultural Studies',             'ready' => false],
        'education'    => ['label' => 'Education & Learning Sciences',       'ready' => true],
        'communication'=> ['label' => 'Communication & Media Studies',       'ready' => true],
    ],

    // Initial selection — keep only 'math' on to avoid it appearing unchecked,
    // and to ensure non-ready disciplines are excluded from generated digests.
    'enabled_by_default' => [
        'math',
    ],

    // Optional: map common typos/synonyms → canonical slugs
    'aliases' => [
        'neruoscience'     => 'neuroscience',
        'neuro'            => 'neuroscience',
        'informations'     => 'informatics',
        'info'             => 'informatics',
        'comp_sci'         => 'cs',
        'computer_science' => 'cs',
    ],
];
