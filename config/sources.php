<?php

return [
    'list' => [
        // ============================
        // TIER 1: arXiv (rich Atom)
        // ============================
        [
            'key'         => 'arxiv_math_all',
            'label'       => 'arXiv — Mathematics (all)',
            'kind'        => 'primary',
            'disciplines' => ['math'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:math.*&sortBy=submittedDate&sortOrder=descending&max_results=25',
            'signal'      => 'latest preprints',
            'notes'       => 'Open, structured, abstracts available (Cornell).',
        ],

        // --- Popular subfields (arXiv categories) ---
        [
            'key' => 'arxiv_math_AG',
            'label' => 'arXiv — Algebraic Geometry (math.AG)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.AG&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_AT',
            'label' => 'arXiv — Algebraic Topology (math.AT)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.AT&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_CO',
            'label' => 'arXiv — Combinatorics (math.CO)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.CO&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_DG',
            'label' => 'arXiv — Differential Geometry (math.DG)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.DG&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_FA',
            'label' => 'arXiv — Functional Analysis (math.FA)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.FA&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_GT',
            'label' => 'arXiv — Geometric Topology (math.GT)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.GT&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_NT',
            'label' => 'arXiv — Number Theory (math.NT)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.NT&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_PR',
            'label' => 'arXiv — Probability (math.PR)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.PR&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_RA',
            'label' => 'arXiv — Rings and Algebras (math.RA)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.RA&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_RT',
            'label' => 'arXiv — Representation Theory (math.RT)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.RT&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_OC',
            'label' => 'arXiv — Optimization and Control (math.OC)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.OC&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_NA',
            'label' => 'arXiv — Numerical Analysis (math.NA)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.NA&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_AP',
            'label' => 'arXiv — Analysis of PDEs (math.AP)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.AP&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],
        [
            'key' => 'arxiv_math_ST',
            'label' => 'arXiv — Statistics Theory (math.ST)',
            'kind' => 'primary',
            'disciplines' => ['math'],
            'url' => 'http://export.arxiv.org/api/query?search_query=cat:math.ST&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal' => 'latest preprints',
            'notes' => 'Subfield feed.',
        ],

        // ============================
        // TIER 1b: bioRxiv / medRxiv (JSON)
        // ============================
        [
            'key'         => 'biorxiv_recent',
            'label'       => 'bioRxiv — Recent (quant/math adjacent)',
            'kind'        => 'json',
            'disciplines' => ['math'],
            'url'         => 'https://api.biorxiv.org/details/biorxiv/50',
            'signal'      => 'latest preprints',
            'notes'       => 'Structured JSON (title/abstract/DOI).',
        ],
        [
            'key'         => 'medrxiv_recent',
            'label'       => 'medRxiv — Recent (methods/stats)',
            'kind'        => 'json',
            'disciplines' => ['math'],
            'url'         => 'https://api.medrxiv.org/details/medrxiv/50',
            'signal'      => 'latest preprints',
            'notes'       => 'Structured JSON (title/abstract/DOI).',
        ],

        // ============================
        // COMPUTER SCIENCE & AI
        // ============================
        [
            'key'         => 'arxiv_cs_all',
            'label'       => 'arXiv — Computer Science (all)',
            'kind'        => 'primary',
            'disciplines' => ['cs'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.*&sortBy=submittedDate&sortOrder=descending&max_results=25',
            'signal'      => 'latest preprints',
            'notes'       => 'All CS categories (Cornell arXiv).',
        ],
        [
            'key'         => 'arxiv_cs_AI',
            'label'       => 'arXiv — Artificial Intelligence (cs.AI)',
            'kind'        => 'primary',
            'disciplines' => ['cs'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.AI&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Subfield feed.',
        ],
        [
            'key'         => 'arxiv_cs_LG',
            'label'       => 'arXiv — Machine Learning (cs.LG)',
            'kind'        => 'primary',
            'disciplines' => ['cs'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.LG&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Subfield feed.',
        ],
        [
            'key'         => 'arxiv_cs_CV',
            'label'       => 'arXiv — Computer Vision (cs.CV)',
            'kind'        => 'primary',
            'disciplines' => ['cs'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.CV&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Subfield feed.',
        ],
        [
            'key'         => 'arxiv_cs_SE',
            'label'       => 'arXiv — Software Engineering (cs.SE)',
            'kind'        => 'primary',
            'disciplines' => ['cs'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.SE&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Subfield feed.',
        ],
        [
            'key'         => 'arxiv_cs_CR',
            'label'       => 'arXiv — Cryptography & Security (cs.CR)',
            'kind'        => 'primary',
            'disciplines' => ['cs'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.CR&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Subfield feed.',
        ],
        [
            'key'         => 'arxiv_cs_DC',
            'label'       => 'arXiv — Distributed Computing (cs.DC)',
            'kind'        => 'primary',
            'disciplines' => ['cs'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.DC&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Subfield feed.',
        ],
        [
            'key'         => 'arxiv_cs_PL',
            'label'       => 'arXiv — Programming Languages (cs.PL)',
            'kind'        => 'primary',
            'disciplines' => ['cs'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.PL&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Subfield feed.',
        ],

        // ============================
        // NEUROSCIENCE
        // ============================
        [
            'key'         => 'arxiv_qbio_NC',
            'label'       => 'arXiv — Neurons and Cognition (q-bio.NC)',
            'kind'        => 'primary',
            'disciplines' => ['neuroscience', 'psychology'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:q-bio.NC&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Computational neuroscience (arXiv). Cross-listed: neuroscience + psychology.',
        ],
        [
            'key'         => 'europepmc_neuroscience',
            'label'       => 'Europe PMC — Neuroscience preprints',
            'kind'        => 'json',
            'disciplines' => ['neuroscience'],
            'url'         => 'https://www.ebi.ac.uk/europepmc/webservices/rest/search?query=SRC%3APPR+AND+TOPIC%3A%22neuroscience%22&resultType=core&pageSize=25&format=json&sort=FIRST_PDATE+desc',
            'signal'      => 'latest preprints',
            'notes'       => 'Aggregated preprints from bioRxiv, medRxiv, etc. via Europe PMC.',
        ],

        // ============================
        // EARTH & ENVIRONMENTAL SCIENCES
        // ============================
        [
            'key'         => 'arxiv_physics_ao',
            'label'       => 'arXiv — Atmospheric & Oceanic Physics (physics.ao-ph)',
            'kind'        => 'primary',
            'disciplines' => ['earth'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:physics.ao-ph&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Climate, weather, ocean dynamics (arXiv).',
        ],
        [
            'key'         => 'arxiv_physics_geo',
            'label'       => 'arXiv — Geophysics (physics.geo-ph)',
            'kind'        => 'primary',
            'disciplines' => ['earth'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:physics.geo-ph&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Seismology, geodesy, geomagnetism (arXiv).',
        ],
        [
            'key'         => 'arxiv_astro_EP',
            'label'       => 'arXiv — Earth & Planetary Astrophysics (astro-ph.EP)',
            'kind'        => 'primary',
            'disciplines' => ['earth'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:astro-ph.EP&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Planetary science, exoplanets (arXiv).',
        ],
        [
            'key'         => 'europepmc_earth',
            'label'       => 'Europe PMC — Earth science preprints',
            'kind'        => 'json',
            'disciplines' => ['earth'],
            'url'         => 'https://www.ebi.ac.uk/europepmc/webservices/rest/search?query=SRC%3APPR+AND+%28TOPIC%3A%22earth+science%22+OR+TOPIC%3A%22environmental+science%22+OR+TOPIC%3A%22climate%22%29&resultType=core&pageSize=25&format=json&sort=FIRST_PDATE+desc',
            'signal'      => 'latest preprints',
            'notes'       => 'Aggregated earth/environmental preprints via Europe PMC.',
        ],

        // ============================
        // ECONOMICS & FINANCE
        // ============================
        [
            'key'         => 'arxiv_econ_GN',
            'label'       => 'arXiv — General Economics (econ.GN)',
            'kind'        => 'primary',
            'disciplines' => ['economics'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:econ.GN&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Broad economics (arXiv).',
        ],
        [
            'key'         => 'arxiv_econ_EM',
            'label'       => 'arXiv — Econometrics (econ.EM)',
            'kind'        => 'primary',
            'disciplines' => ['economics'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:econ.EM&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Statistical methods in economics (arXiv).',
        ],
        [
            'key'         => 'arxiv_econ_TH',
            'label'       => 'arXiv — Theoretical Economics (econ.TH)',
            'kind'        => 'primary',
            'disciplines' => ['economics'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:econ.TH&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Game theory, mechanism design (arXiv).',
        ],
        [
            'key'         => 'arxiv_qfin_all',
            'label'       => 'arXiv — Quantitative Finance (all)',
            'kind'        => 'primary',
            'disciplines' => ['economics'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:q-fin.*&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Pricing, risk, portfolio theory (arXiv).',
        ],

        // ============================
        // PSYCHOLOGY & COGNITIVE SCIENCE
        // ============================
        // (also gets arxiv_qbio_NC via cross-listing above)
        [
            'key'         => 'psyarxiv_recent',
            'label'       => 'PsyArXiv — Recent preprints',
            'kind'        => 'json',
            'disciplines' => ['psychology'],
            'url'         => 'https://api.osf.io/v2/preprints/?filter[provider]=psyarxiv&sort=-date_created&page[size]=25',
            'signal'      => 'latest preprints',
            'notes'       => 'Psychology preprints via OSF Preprints API (JSON:API).',
        ],

        // ============================
        // PHARMACOLOGY & DRUG DISCOVERY
        // ============================
        [
            'key'         => 'arxiv_qbio_BM',
            'label'       => 'arXiv — Biomolecules (q-bio.BM)',
            'kind'        => 'primary',
            'disciplines' => ['pharmacology'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:q-bio.BM&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Protein structure, molecular dynamics (arXiv).',
        ],
        [
            'key'         => 'europepmc_pharmacology',
            'label'       => 'Europe PMC — Pharmacology preprints',
            'kind'        => 'json',
            'disciplines' => ['pharmacology'],
            'url'         => 'https://www.ebi.ac.uk/europepmc/webservices/rest/search?query=SRC%3APPR+AND+TOPIC%3A%22pharmacology%22&resultType=core&pageSize=25&format=json&sort=FIRST_PDATE+desc',
            'signal'      => 'latest preprints',
            'notes'       => 'Aggregated pharmacology preprints via Europe PMC.',
        ],

        // ============================
        // BIOINFORMATICS & COMPUTATIONAL BIOLOGY
        // ============================
        [
            'key'         => 'arxiv_qbio_QM',
            'label'       => 'arXiv — Quantitative Methods (q-bio.QM)',
            'kind'        => 'primary',
            'disciplines' => ['informatics'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:q-bio.QM&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Computational biology methods (arXiv).',
        ],
        [
            'key'         => 'arxiv_qbio_GN',
            'label'       => 'arXiv — Genomics (q-bio.GN)',
            'kind'        => 'primary',
            'disciplines' => ['informatics'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:q-bio.GN&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Genomics and sequencing (arXiv).',
        ],
        [
            'key'         => 'arxiv_qbio_MN',
            'label'       => 'arXiv — Molecular Networks (q-bio.MN)',
            'kind'        => 'primary',
            'disciplines' => ['informatics'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:q-bio.MN&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Gene regulatory networks, signaling (arXiv).',
        ],

        // ============================
        // ENGINEERING (GENERAL)
        // ============================
        [
            'key'         => 'arxiv_eess_SP',
            'label'       => 'arXiv — Signal Processing (eess.SP)',
            'kind'        => 'primary',
            'disciplines' => ['engineering'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:eess.SP&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'DSP, communications, radar (arXiv).',
        ],
        [
            'key'         => 'arxiv_eess_SY',
            'label'       => 'arXiv — Systems and Control (eess.SY)',
            'kind'        => 'primary',
            'disciplines' => ['engineering'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:eess.SY&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Control theory, automation (arXiv).',
        ],
        [
            'key'         => 'arxiv_cs_RO',
            'label'       => 'arXiv — Robotics (cs.RO)',
            'kind'        => 'primary',
            'disciplines' => ['engineering'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.RO&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Robotics, manipulation, planning (arXiv).',
        ],

        // ============================
        // LINGUISTICS & LANGUAGE SCIENCE
        // ============================
        [
            'key'         => 'arxiv_cs_CL',
            'label'       => 'arXiv — Computation and Language (cs.CL)',
            'kind'        => 'primary',
            'disciplines' => ['linguistics'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:cs.CL&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'NLP, computational linguistics (arXiv).',
        ],

        // ============================
        // EDUCATION & LEARNING SCIENCES
        // ============================
        [
            'key'         => 'edarxiv_recent',
            'label'       => 'EdArXiv — Recent preprints',
            'kind'        => 'json',
            'disciplines' => ['education'],
            'url'         => 'https://api.osf.io/v2/preprints/?filter[provider]=edarxiv&sort=-date_created&page[size]=25',
            'signal'      => 'latest preprints',
            'notes'       => 'Education research preprints via OSF Preprints API (JSON:API).',
        ],

        // ============================
        // COMMUNICATION & MEDIA STUDIES
        // ============================
        [
            'key'         => 'socarxiv_recent',
            'label'       => 'SocArXiv — Recent preprints',
            'kind'        => 'json',
            'disciplines' => ['communication'],
            'url'         => 'https://api.osf.io/v2/preprints/?filter[provider]=socarxiv&sort=-date_created&page[size]=25',
            'signal'      => 'latest preprints',
            'notes'       => 'Social science preprints via OSF Preprints API (JSON:API). Covers communication & media.',
        ],

        // ============================
        // AGRICULTURE & FOOD SCIENCE
        // ============================
        [
            'key'         => 'arxiv_qbio_PE',
            'label'       => 'arXiv — Populations and Evolution (q-bio.PE)',
            'kind'        => 'primary',
            'disciplines' => ['agriculture'],
            'url'         => 'http://export.arxiv.org/api/query?search_query=cat:q-bio.PE&sortBy=submittedDate&sortOrder=descending&max_results=15',
            'signal'      => 'latest preprints',
            'notes'       => 'Ecology, epidemiology, population dynamics (arXiv).',
        ],
        [
            'key'         => 'europepmc_agriculture',
            'label'       => 'Europe PMC — Agriculture preprints',
            'kind'        => 'json',
            'disciplines' => ['agriculture'],
            'url'         => 'https://www.ebi.ac.uk/europepmc/webservices/rest/search?query=SRC%3APPR+AND+%28TOPIC%3A%22agriculture%22+OR+TOPIC%3A%22food+science%22+OR+TOPIC%3A%22plant+biology%22%29&resultType=core&pageSize=25&format=json&sort=FIRST_PDATE+desc',
            'signal'      => 'latest preprints',
            'notes'       => 'Aggregated agriculture/food preprints via Europe PMC.',
        ],
    ],
];
