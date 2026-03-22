<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État de Décision - {{ $decision->numero_repertoire ?? 'N/A' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
        }

        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .header h2 {
            font-size: 14pt;
            font-weight: normal;
            color: #333;
        }

        .header .tribunal-info {
            margin-top: 10px;
            font-size: 11pt;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #f0f0f0;
            padding: 8px 10px;
            font-size: 12pt;
            font-weight: bold;
            border-left: 4px solid #2563eb;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 35%;
            padding: 5px 8px;
            font-weight: bold;
            background-color: #fafafa;
            border: 1px solid #ddd;
        }

        .info-value {
            display: table-cell;
            padding: 5px 8px;
            border: 1px solid #ddd;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }

        .badge-primary {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-info {
            background-color: #e0f2fe;
            color: #075985;
        }

        .badge-gray {
            background-color: #f3f4f6;
            color: #374151;
        }

        .parties-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .parties-table th {
            background-color: #f0f0f0;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 10pt;
        }

        .parties-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            font-size: 9pt;
        }

        .timeline {
            margin: 10px 0;
        }

        .timeline-item {
            display: flex;
            margin-bottom: 8px;
            padding-left: 15px;
            position: relative;
        }

        .timeline-item:before {
            content: "●";
            position: absolute;
            left: 0;
            color: #2563eb;
            font-size: 14pt;
        }

        .timeline-label {
            flex: 0 0 40%;
            font-weight: bold;
        }

        .timeline-value {
            flex: 1;
        }

        .decision-content {
            background-color: #fafafa;
            padding: 10px;
            border-left: 3px solid #2563eb;
            margin: 10px 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        .page-number:after {
            content: counter(page);
        }

        .alert {
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid;
            background-color: #fef3c7;
            border-color: #f59e0b;
        }

        .alert-danger {
            background-color: #fee2e2;
            border-color: #dc2626;
        }

        .alert-success {
            background-color: #dcfce7;
            border-color: #16a34a;
        }

        .membres-college {
            margin: 10px 0;
            padding: 10px;
            background-color: #fafafa;
            border: 1px solid #ddd;
        }

        .membre-item {
            padding: 5px 0;
            border-bottom: 1px dotted #ddd;
        }

        .membre-item:last-child {
            border-bottom: none;
        }

        .statut-workflow {
            text-align: center;
            padding: 15px;
            margin: 15px 0;
            background: linear-gradient(to right, #f0f0f0, #fafafa);
            border: 2px solid #2563eb;
            border-radius: 5px;
        }

        .statut-workflow .statut-label {
            font-size: 10pt;
            color: #666;
            margin-bottom: 5px;
        }

        .statut-workflow .statut-value {
            font-size: 16pt;
            font-weight: bold;
            color: #2563eb;
        }

        h3 {
            font-size: 11pt;
            margin: 10px 0 5px 0;
            color: #1f2937;
        }

        .two-columns {
            display: table;
            width: 100%;
        }

        .column {
            display: table-cell;
            width: 50%;
            padding: 0 10px;
        }

        .column:first-child {
            padding-left: 0;
        }

        .column:last-child {
            padding-right: 0;
        }
    </style>
</head>
<body>
    {{-- EN-TÊTE --}}
    <div class="header">
        <h1>République du Cameroun</h1>
        <h2>Paix - Travail - Patrie</h2>
        <div class="tribunal-info">
            <strong>{{ $decision->dossier->tribunal->nom ?? 'Tribunal' }}</strong><br>
            {{ $decision->dossier->section->libelle ?? '' }}
        </div>
    </div>

    {{-- TITRE DU DOCUMENT --}}
    <div style="text-align: center; margin: 20px 0; padding: 15px; background-color: #f0f0f0; border: 2px solid #2563eb;">
        <h1 style="font-size: 16pt; margin-bottom: 5px;">ÉTAT DE DÉCISION</h1>
        <div style="font-size: 12pt; color: #666;">
            N° {{ $decision->numero_repertoire ?? 'Non renseigné' }}
        </div>
    </div>

    {{-- STATUT ACTUEL --}}
    <div class="statut-workflow">
        <div class="statut-label">STATUT ACTUEL</div>
        <div class="statut-value">
            @switch($decision->statut)
                @case('brouillon') BROUILLON @break
                @case('validee') VALIDÉE @break
                @case('saisie') SAISIE @break
                @case('signee') SIGNÉE @break
                @case('enregistree') ENREGISTRÉE @break
                @case('archivee') ARCHIVÉE @break
                @default {{ strtoupper($decision->statut) }}
            @endswitch
        </div>
    </div>

    {{-- SECTION 1 : DOSSIER D'ENRÔLEMENT --}}
    <div class="section">
        <div class="section-title">📁 Dossier d'Enrôlement</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Numéro de dossier</div>
                <div class="info-value">
                    <span class="badge badge-primary">{{ $decision->dossier->numero_dossier }}</span>
                </div>
            </div>
            @if($decision->dossier->numero_dossier_personnalise)
            <div class="info-row">
                <div class="info-label">Ancien numéro</div>
                <div class="info-value">{{ $decision->dossier->numero_dossier_personnalise }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Tribunal</div>
                <div class="info-value">{{ $decision->dossier->tribunal->nom ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Section</div>
                <div class="info-value">
                    <span class="badge {{ $decision->dossier->section->type === 'repressive' ? 'badge-danger' : 'badge-info' }}">
                        {{ $decision->dossier->section->libelle ?? 'N/A' }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Matière</div>
                <div class="info-value">{{ $decision->dossier->matiere->designation ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Année judiciaire</div>
                <div class="info-value">{{ $decision->dossier->anneeJudiciaire->libelle ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date d'enrôlement</div>
                <div class="info-value">{{ $decision->dossier->date_enrolement?->format('d/m/Y') ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    {{-- SECTION 2 : PARTIES --}}
    <div class="section">
        <div class="section-title">👥 Parties au Litige</div>
        
        {{-- Parties requérantes --}}
        <h3>
            @if($decision->dossier->section->type === 'repressive')
                ⚖️ Ministère Public et Parties Civiles
            @else
                Demandeurs (Parties requérantes)
            @endif
        </h3>

        @if($decision->dossier->section->type === 'repressive')
            <div class="alert">
                <strong>⚖️ Ministère Public</strong> - Le Ministère Public est partie poursuivante d'office.
            </div>
        @endif

        @if($decision->dossier->demandeurs->count() > 0)
            <table class="parties-table">
                <thead>
                    <tr>
                        <th>Identité</th>
                        <th>Profession</th>
                        <th>Adresse</th>
                        <th>Avocat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($decision->dossier->demandeurs as $partie)
                    <tr>
                        <td><strong>{{ $partie->nom_complet }}</strong></td>
                        <td>{{ $partie->profession ?? '-' }}</td>
                        <td>{{ $partie->adresse ?? '-' }}</td>
                        <td>{{ $partie->avocat_nom ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Parties adverses --}}
        <h3 style="margin-top: 15px;">
            @if($decision->dossier->section->type === 'repressive')
                🔴 Prévenus (Personnes poursuivies)
            @else
                Défendeurs (Parties adverses)
            @endif
        </h3>

        @if($decision->dossier->defendeurs->count() > 0)
            <table class="parties-table">
                <thead>
                    <tr>
                        <th>Identité</th>
                        <th>Profession</th>
                        <th>Adresse</th>
                        <th>Avocat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($decision->dossier->defendeurs as $partie)
                    <tr>
                        <td><strong>{{ $partie->nom_complet }}</strong></td>
                        <td>{{ $partie->profession ?? '-' }}</td>
                        <td>{{ $partie->adresse ?? '-' }}</td>
                        <td>{{ $partie->avocat_nom ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Infractions --}}
        @if($decision->dossier->infractions->count() > 0)
            <h3 style="margin-top: 15px;">⚠️ Infractions / Objet du différend</h3>
            <ul style="margin-left: 20px;">
                @foreach($decision->dossier->infractions as $infraction)
                    <li>
                        <strong>{{ $infraction->libelle }}</strong> 
                        ({{ $infraction->code }})
                        @if($infraction->categorie)
                            - <span class="badge badge-danger">{{ $infraction->categorie }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    {{-- SECTION 3 : IDENTIFICATION DE LA DÉCISION --}}
    <div class="section">
        <div class="section-title">🔖 Identification de la Décision</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">N° Répertoire / Décision</div>
                <div class="info-value">
                    <span class="badge badge-primary">{{ $decision->numero_repertoire ?? 'Non renseigné' }}</span>
                </div>
            </div>
            @if($decision->numero_parquet)
            <div class="info-row">
                <div class="info-label">N° Parquet</div>
                <div class="info-value">{{ $decision->numero_parquet }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Nature de la décision</div>
                <div class="info-value">
                    <span class="badge badge-info">{{ $decision->natureDecision->libelle ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- SECTION 4 : DATES DU WORKFLOW --}}
    <div class="section">
        <div class="section-title">📅 Chronologie de Traitement</div>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-label">Date de décision :</div>
                <div class="timeline-value">
                    <strong>{{ $decision->date_decision?->format('d/m/Y') ?? 'Non renseignée' }}</strong>
                </div>
            </div>
            @if($decision->date_factum)
            <div class="timeline-item">
                <div class="timeline-label">Date du factum :</div>
                <div class="timeline-value">{{ $decision->date_factum->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($decision->date_premiere_audience)
            <div class="timeline-item">
                <div class="timeline-label">Date de 1ère audience :</div>
                <div class="timeline-value">{{ $decision->date_premiere_audience->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($decision->date_saisie)
            <div class="timeline-item">
                <div class="timeline-label">Date de saisie :</div>
                <div class="timeline-value">{{ $decision->date_saisie->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($decision->date_modification)
            <div class="timeline-item">
                <div class="timeline-label">Date de modification :</div>
                <div class="timeline-value">{{ $decision->date_modification->format('d/m/Y') }}</div>
            </div>
            @endif
            @if($decision->date_signature)
            <div class="timeline-item">
                <div class="timeline-label">Date de signature :</div>
                <div class="timeline-value"><strong>{{ $decision->date_signature->format('d/m/Y') }}</strong></div>
            </div>
            @endif
            @if($decision->date_enregistrement)
            <div class="timeline-item">
                <div class="timeline-label">Date d'enregistrement :</div>
                <div class="timeline-value"><strong>{{ $decision->date_enregistrement->format('d/m/Y') }}</strong></div>
            </div>
            @endif
        </div>
    </div>

    {{-- SECTION 5 : COMPOSITION DU TRIBUNAL --}}
    <div class="section">
        <div class="section-title">⚖️ Composition du Tribunal</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Mode de composition</div>
                <div class="info-value">
                    <span class="badge {{ $decision->mode_composition === 'juge_unique' ? 'badge-info' : 'badge-warning' }}">
                        {{ $decision->mode_composition === 'juge_unique' ? 'Juge unique' : 'Collège de juges (Collégialité)' }}
                    </span>
                </div>
            </div>

            @if($decision->mode_composition === 'juge_unique')
                <div class="info-row">
                    <div class="info-label">Juge</div>
                    <div class="info-value">
                        <strong>{{ $decision->jugeUnique->nom_complet ?? 'Non renseigné' }}</strong>
                    </div>
                </div>
            @else
                <div class="info-row">
                    <div class="info-label">Collège</div>
                    <div class="info-value">
                        <strong>{{ $decision->collegeJuge->designation ?? 'Non renseigné' }}</strong>
                    </div>
                </div>
            @endif

            @if($decision->greffierDecision)
            <div class="info-row">
                <div class="info-label">Greffier</div>
                <div class="info-value">{{ $decision->greffierDecision->nom_complet }}</div>
            </div>
            @endif
        </div>

        @if($decision->mode_composition === 'college' && $decision->collegeJuge && $decision->collegeJuge->membres->count() > 0)
            <h3>Membres du collège :</h3>
            <div class="membres-college">
                @foreach($decision->collegeJuge->membres as $membre)
                    <div class="membre-item">
                        <strong>{{ $membre->nom_complet }}</strong> - 
                        <span class="badge badge-info">
                            @switch($membre->pivot->qualite)
                                @case('president') Président @break
                                @case('juge_1') Juge 1 @break
                                @case('juge_2') Juge 2 @break
                                @case('assesseur_1') Assesseur 1 @break
                                @case('assesseur_2') Assesseur 2 @break
                                @case('juge_suppleant') Juge suppléant @break
                                @default {{ $membre->pivot->qualite }}
                            @endswitch
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- SECTION 6 : CONTENU DE LA DÉCISION --}}
    <div class="section">
        <div class="section-title">📄 Contenu de la Décision</div>
        
        @if($decision->resume)
            <h3>Résumé des faits :</h3>
            <div class="decision-content">{{ $decision->resume }}</div>
        @endif

        @if($decision->dispositif)
            <h3>Dispositif :</h3>
            <div class="decision-content">{{ $decision->dispositif }}</div>
        @endif
    </div>

    {{-- SECTION 7 : CONDAMNATIONS --}}
    @if($decision->montant_amende || $decision->montant_depens || $decision->duree_peine)
    <div class="section">
        <div class="section-title">💰 Condamnations</div>
        <div class="info-grid">
            @if($decision->montant_amende)
            <div class="info-row">
                <div class="info-label">Amende</div>
                <div class="info-value"><strong>{{ number_format($decision->montant_amende, 0, ',', ' ') }} FCFA</strong></div>
            </div>
            @endif
            @if($decision->montant_depens)
            <div class="info-row">
                <div class="info-label">Dépens</div>
                <div class="info-value"><strong>{{ number_format($decision->montant_depens, 0, ',', ' ') }} FCFA</strong></div>
            </div>
            @endif
            @if($decision->duree_peine)
            <div class="info-row">
                <div class="info-label">Peine privative de liberté</div>
                <div class="info-value"><span class="badge badge-danger">{{ $decision->duree_peine }}</span></div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- SECTION 8 : ENREGISTREMENT --}}
    @if(in_array($decision->statut, ['enregistree', 'archivee']))
    <div class="section">
        <div class="section-title">📋 Références d'Enregistrement</div>
        <div class="info-grid">
            @if($decision->numero_volume)
            <div class="info-row">
                <div class="info-label">N° Volume</div>
                <div class="info-value">{{ $decision->numero_volume }}</div>
            </div>
            @endif
            @if($decision->numero_folio)
            <div class="info-row">
                <div class="info-label">N° Folio</div>
                <div class="info-value">{{ $decision->numero_folio }}</div>
            </div>
            @endif
            @if($decision->numero_case_bd)
            <div class="info-row">
                <div class="info-label">N° Case BD</div>
                <div class="info-value">{{ $decision->numero_case_bd }}</div>
            </div>
            @endif
            @if($decision->numero_quittance)
            <div class="info-row">
                <div class="info-label">N° Quittance</div>
                <div class="info-value">{{ $decision->numero_quittance }}</div>
            </div>
            @endif
            @if($decision->montant_quittance)
            <div class="info-row">
                <div class="info-label">Montant Quittance</div>
                <div class="info-value"><strong>{{ number_format($decision->montant_quittance, 0, ',', ' ') }} FCFA</strong></div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- SECTION 9 : CERTIFICAT & GROSSE OU OPPOSITION --}}
    @if(in_array($decision->statut, ['enregistree', 'archivee']))
        @if($decision->a_opposition)
            {{-- Opposition --}}
            <div class="section">
                <div class="section-title" style="background-color: #fee2e2; color: #991b1b;">
                    ⚠️ Opposition Enregistrée
                </div>
                <div class="alert alert-danger">
                    Cette décision a fait l'objet d'une opposition. Le module Recours doit être activé.
                </div>
                <div class="info-grid">
                    @if($decision->lettre_opposition_reference)
                    <div class="info-row">
                        <div class="info-label">Référence de la lettre</div>
                        <div class="info-value">{{ $decision->lettre_opposition_reference }}</div>
                    </div>
                    @endif
                    @if($decision->lettre_opposition_date)
                    <div class="info-row">
                        <div class="info-label">Date</div>
                        <div class="info-value">{{ $decision->lettre_opposition_date->format('d/m/Y') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Certificat & Grosse --}}
            @if($decision->certificat_non_appel_reference || $decision->grosse_reference)
            <div class="section">
                <div class="section-title">✅ Certificat de Non-Appel & Grosse</div>
                
                <div class="two-columns">
                    <div class="column">
                        <h3>Certificat de non-appel</h3>
                        <div class="info-grid">
                            @if($decision->certificat_non_appel_reference)
                            <div class="info-row">
                                <div class="info-label">Référence</div>
                                <div class="info-value">{{ $decision->certificat_non_appel_reference }}</div>
                            </div>
                            @endif
                            @if($decision->certificat_non_appel_date)
                            <div class="info-row">
                                <div class="info-label">Date</div>
                                <div class="info-value">{{ $decision->certificat_non_appel_date->format('d/m/Y') }}</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <div class="column">
                        <h3>Grosse</h3>
                        <div class="info-grid">
                            @if($decision->grosse_reference)
                            <div class="info-row">
                                <div class="info-label">Référence</div>
                                <div class="info-value">{{ $decision->grosse_reference }}</div>
                            </div>
                            @endif
                            @if($decision->grosse_date)
                            <div class="info-row">
                                <div class="info-label">Date</div>
                                <div class="info-value">{{ $decision->grosse_date->format('d/m/Y') }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @endif
    @endif

    {{-- PIED DE PAGE --}}
    <div class="footer">
        <div>
            État généré le {{ $dateImpression->format('d/m/Y à H:i') }}
            | Page <span class="page-number"></span>
        </div>
        <div style="margin-top: 3px; font-size: 7pt; color: #999;">
            Document officiel - {{ $decision->dossier->tribunal->nom ?? 'Tribunal' }}
        </div>
    </div>
</body>
</html>