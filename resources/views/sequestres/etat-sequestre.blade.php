<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>État de Séquestre - {{ $sequestre->numero_dossier_sequestre ?? 'N/A' }}</title>
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

        .badge-primary { background-color: #dbeafe; color: #1e40af; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger  { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info    { background-color: #e0f2fe; color: #075985; }
        .badge-gray    { background-color: #f3f4f6; color: #374151; }

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

        .parties-table tfoot td {
            font-weight: bold;
            background-color: #fafafa;
        }

        .montant-positif { color: #166534; font-weight: bold; }
        .montant-negatif { color: #991b1b; font-weight: bold; }

        .solde-global {
            text-align: center;
            padding: 15px;
            margin: 15px 0;
            background: linear-gradient(to right, #f0f0f0, #fafafa);
            border: 2px solid #2563eb;
            border-radius: 5px;
        }

        .solde-global .solde-label {
            font-size: 10pt;
            color: #666;
            margin-bottom: 5px;
        }

        .solde-global .solde-value {
            font-size: 20pt;
            font-weight: bold;
            color: #2563eb;
        }

        .solde-global.solde-negatif .solde-value {
            color: #991b1b;
        }

        .documents-liste {
            margin: 10px 0;
            padding: 10px;
            background-color: #fafafa;
            border: 1px solid #ddd;
        }

        .document-item {
            padding: 5px 0;
            border-bottom: 1px dotted #ddd;
        }

        .document-item:last-child {
            border-bottom: none;
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

        h3 {
            font-size: 11pt;
            margin: 10px 0 5px 0;
            color: #1f2937;
        }
    </style>
</head>
<body>
    {{-- EN-TÊTE --}}
    <div class="header">
        <h1>République du Cameroun</h1>
        <h2>Paix - Travail - Patrie</h2>
        <div class="tribunal-info">
            <strong>{{ $sequestre->dossier->tribunal->nom ?? 'Tribunal' }}</strong>
        </div>
    </div>

    {{-- TITRE DU DOCUMENT --}}
    <div style="text-align: center; margin: 20px 0; padding: 15px; background-color: #f0f0f0; border: 2px solid #2563eb;">
        <h1 style="font-size: 16pt; margin-bottom: 5px;">ÉTAT DE SÉQUESTRE</h1>
        <div style="font-size: 12pt; color: #666;">
            N° {{ $sequestre->numero_dossier_sequestre ?? 'Non renseigné' }}
        </div>
    </div>

    {{-- STATUT ACTUEL --}}
    <div class="solde-global {{ $sequestre->solde_actuel < 0 ? 'solde-negatif' : '' }}">
        <div class="solde-label">SOLDE ACTUEL</div>
        <div class="solde-value">{{ number_format($sequestre->solde_actuel, 0, ',', ' ') }} FCFA</div>
    </div>

    {{-- SECTION 1 : IDENTIFICATION DU SÉQUESTRE --}}
    <div class="section">
        <div class="section-title">🔒 Identification du Séquestre</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">N° Dossier Séquestre</div>
                <div class="info-value">
                    <span class="badge badge-primary">{{ $sequestre->numero_dossier_sequestre ?? 'Non renseigné' }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Intitulé</div>
                <div class="info-value"><strong>{{ $sequestre->intitule }}</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nature du séquestre</div>
                <div class="info-value">
                    <span class="badge badge-info">{{ $sequestre->natureSequestre->libelle ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Statut</div>
                <div class="info-value">
                    <span class="badge badge-{{ $sequestre->statutSequestre->couleur ?? 'gray' }}">
                        {{ $sequestre->statutSequestre->libelle ?? 'N/A' }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Date d'ouverture</div>
                <div class="info-value">{{ $sequestre->date_ouverture?->format('d/m/Y') ?? 'Non renseignée' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Taux de précompte</div>
                <div class="info-value"><strong>{{ $sequestre->taux_pourcentage }}</strong></div>
            </div>
            @if($sequestre->representant)
            <div class="info-row">
                <div class="info-label">Représentant de la famille</div>
                <div class="info-value">{{ $sequestre->representant->nom_complet }}</div>
            </div>
            @endif
        </div>
    </div>

    {{-- SECTION 2 : ORIGINE JUDICIAIRE --}}
    <div class="section">
        <div class="section-title">⚖️ Origine Judiciaire</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">N° Dossier d'enrôlement</div>
                <div class="info-value">
                    <span class="badge badge-gray">{{ $sequestre->numero_dossier ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">N° Décision</div>
                <div class="info-value">
                    <span class="badge badge-primary">{{ $sequestre->numero_decision ?? 'N/A' }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Type de décision</div>
                <div class="info-value">{{ $sequestre->type_decision_label ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Nature de la décision</div>
                <div class="info-value">{{ $sequestre->nature_decision_label ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date de la décision</div>
                <div class="info-value">{{ $sequestre->date_decision?->format('d/m/Y') ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    {{-- SECTION 3 : AYANTS DROIT --}}
    @if($sequestre->ayantsDroit->count() > 0)
    <div class="section">
        <div class="section-title">👥 Ayants Droit (Bénéficiaires)</div>
        <table class="parties-table">
            <thead>
                <tr>
                    <th>Nom complet</th>
                    <th>N° CNI</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sequestre->ayantsDroit as $ayantDroit)
                <tr>
                    <td><strong>{{ $ayantDroit->nom_complet }}</strong></td>
                    <td>{{ $ayantDroit->numero_cni ?? '-' }}</td>
                    <td>{{ $ayantDroit->telephone ?? '-' }}</td>
                    <td>{{ $ayantDroit->adresse ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- SECTION 3bis : RÉPARTITION PAR AYANT DROIT (ce que chacun a déjà perçu) --}}
    @if($repartitionAyantsDroit->count() > 0)
    <div class="section">
        <div class="section-title">📊 Répartition des Montants Perçus par Ayant Droit</div>
        <table class="parties-table">
            <thead>
                <tr>
                    <th>Ayant droit</th>
                    <th>Nb. retraits</th>
                    <th>Dont procuration</th>
                    <th>Total perçu</th>
                </tr>
            </thead>
            <tbody>
                @foreach($repartitionAyantsDroit as $ligne)
                <tr>
                    <td><strong>{{ $ligne['ayant_droit']?->nom_complet ?? 'Non attribué (historique)' }}</strong></td>
                    <td>{{ $ligne['nombre_retraits'] }}</td>
                    <td>{{ $ligne['dont_procuration'] }}</td>
                    <td class="montant-negatif">{{ number_format($ligne['total_percu'], 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="text-align: right;">TOTAL DÉJÀ PERÇU :</td>
                    <td>{{ number_format($repartitionAyantsDroit->sum('total_percu'), 0, ',', ' ') }} FCFA</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- SECTION 4 : PARTIES ADVERSES --}}
    @if($sequestre->partiesAdverses->count() > 0)
    <div class="section">
        <div class="section-title">🏠 Parties Adverses (Payeurs)</div>
        <table class="parties-table">
            <thead>
                <tr>
                    <th>Nom complet</th>
                    <th>N° CNI</th>
                    <th>Téléphone</th>
                    <th>Adresse</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sequestre->partiesAdverses as $partieAdverse)
                <tr>
                    <td><strong>{{ $partieAdverse->nom_complet }}</strong></td>
                    <td>{{ $partieAdverse->numero_cni ?? '-' }}</td>
                    <td>{{ $partieAdverse->telephone ?? '-' }}</td>
                    <td>{{ $partieAdverse->adresse ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- SECTION 5 : RÉSUMÉ FINANCIER --}}
    <div class="section">
        <div class="section-title">💰 Résumé Financier</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Total des entrées</div>
                <div class="info-value montant-positif">{{ number_format($sequestre->total_entrees, 0, ',', ' ') }} FCFA</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total des sorties</div>
                <div class="info-value montant-negatif">{{ number_format($sequestre->total_sorties, 0, ',', ' ') }} FCFA</div>
            </div>
            <div class="info-row">
                <div class="info-label">Total Montant Séquestre</div>
                <div class="info-value"><strong>{{ number_format($sequestre->montant_sequestre_total, 0, ',', ' ') }} FCFA</strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Nombre de mouvements</div>
                <div class="info-value">{{ $sequestre->mouvements->count() }}</div>
            </div>
        </div>
    </div>

    {{-- SECTION 6 : HISTORIQUE DES MOUVEMENTS --}}
    @if($sequestre->mouvements->count() > 0)
    <div class="section" style="page-break-before: always;">
        <div class="section-title">📊 Historique des Mouvements</div>
        <table class="parties-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Opérateur / Bénéficiaire</th>
                    <th>Motif</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Montant Séquestre</th>
                    <th>Solde</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sequestre->mouvements as $mouvement)
                <tr>
                    <td>{{ $mouvement->date_mouvement?->format('d/m/Y') }}</td>
                    <td>{{ $mouvement->operateur_beneficiaire }}</td>
                    <td>{{ $mouvement->motifMouvement?->libelle ?? '-' }}</td>
                    <td>
                        <span class="badge {{ $mouvement->type_mouvement === 'versement' ? 'badge-success' : 'badge-danger' }}">
                            {{ $mouvement->type_label }}
                        </span>
                    </td>
                    <td>{{ number_format($mouvement->montant_mouvement, 0, ',', ' ') }}</td>
                    <td>{{ number_format($mouvement->montant_precompte, 0, ',', ' ') }}</td>
                    <td><strong>{{ number_format($mouvement->solde_apres, 0, ',', ' ') }}</strong></td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right;">SOLDE FINAL :</td>
                    <td>{{ number_format($sequestre->solde_actuel, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    {{-- SECTION 7 : SOUS-DOSSIERS DOCUMENTAIRES --}}
    @if($sequestre->documents->count() > 0)
    <div class="section">
        <div class="section-title">📁 Sous-dossiers Documentaires</div>

        @foreach(['courrier' => '📨 Courrier', 'procedure' => '⚖️ Procédure', 'contrat' => '📄 Contrats', 'quittance' => '🧾 Quittances'] as $categorie => $libelleCategorie)
            @php $documentsCategorie = $sequestre->documents->where('categorie', $categorie); @endphp
            @if($documentsCategorie->count() > 0)
                <h3>{{ $libelleCategorie }} ({{ $documentsCategorie->count() }})</h3>
                <div class="documents-liste">
                    @foreach($documentsCategorie as $document)
                        <div class="document-item">
                            <strong>{{ $document->libelle }}</strong>
                            @if($document->description)
                                <br><small style="color: #666;">{{ $document->description }}</small>
                            @endif
                            <br><small style="color: #999;">Déposé le {{ $document->created_at->format('d/m/Y') }}</small>
                        </div>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- SECTION 8 : OBSERVATIONS --}}
    @if($sequestre->observations)
    <div class="section">
        <div class="section-title">📝 Observations</div>
        <div style="background-color: #fafafa; padding: 10px; border-left: 3px solid #2563eb;">
            {{ $sequestre->observations }}
        </div>
    </div>
    @endif

    {{-- PIED DE PAGE --}}
    <div class="footer">
        <div>
            État généré le {{ $dateImpression->format('d/m/Y à H:i') }}
            | Page <span class="page-number"></span>
        </div>
        <div style="margin-top: 3px; font-size: 7pt; color: #999;">
            Document officiel - {{ $sequestre->dossier->tribunal->nom ?? 'Tribunal' }}
        </div>
    </div>
</body>
</html>