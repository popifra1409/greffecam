<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Décharge de Retrait - {{ $numeroRecu }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #000;
        }

        .header h1 { font-size: 16pt; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 12pt; font-weight: normal; color: #333; }
        .header .tribunal-info { margin-top: 8px; font-size: 10pt; }

        .titre-document {
            text-align: center;
            margin: 20px 0;
            padding: 12px;
            background-color: #fee2e2;
            border: 2px solid #dc2626;
        }

        .titre-document h1 { font-size: 15pt; color: #991b1b; }
        .titre-document .numero { font-size: 11pt; color: #991b1b; margin-top: 4px; }

        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .info-row { display: table-row; }

        .info-label {
            display: table-cell;
            width: 40%;
            padding: 6px 10px;
            font-weight: bold;
            background-color: #fafafa;
            border: 1px solid #ddd;
        }

        .info-value {
            display: table-cell;
            padding: 6px 10px;
            border: 1px solid #ddd;
        }

        .alert-procuration {
            margin: 15px 0;
            padding: 12px;
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
        }

        .mention-legale {
            margin: 20px 0;
            padding: 15px;
            background-color: #fafafa;
            border: 1px solid #ddd;
            line-height: 1.8;
        }

        .montant-lettres {
            margin: 15px 0;
            padding: 12px;
            background-color: #fafafa;
            border-left: 4px solid #dc2626;
            font-style: italic;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 50px;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 20px;
        }

        .signature-box .titre-signature {
            font-weight: bold;
            margin-bottom: 50px;
        }

        .signature-box .ligne-signature {
            border-top: 1px solid #000;
            padding-top: 5px;
            font-size: 9pt;
            color: #666;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>République du Cameroun</h1>
        <h2>Paix - Travail - Patrie</h2>
        <div class="tribunal-info">
            <strong>{{ $mouvement->sequestre->dossier->tribunal->nom ?? 'Tribunal' }}</strong>
        </div>
    </div>

    <div class="titre-document">
        <h1>DÉCHARGE DE RETRAIT</h1>
        <div class="numero">N° {{ $numeroRecu }}</div>
    </div>

    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Dossier Séquestre</div>
            <div class="info-value">{{ $mouvement->sequestre->numero_dossier_sequestre }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Intitulé</div>
            <div class="info-value">{{ $mouvement->sequestre->intitule }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Date du retrait</div>
            <div class="info-value">{{ $mouvement->date_mouvement->format('d/m/Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">
                {{ $mouvement->sequestre_partie_tierce_id ? 'Partie Tierce (bénéficiaire)' : 'Bénéficiaire légal (ayant droit)' }}
            </div>
            <div class="info-value">
                @if($mouvement->sequestre_partie_tierce_id)
                    <strong>{{ $mouvement->partieTierce->nom_complet ?? 'Non renseigné' }}</strong>
                    <br><small>Type : {{ $mouvement->partieTierce->type_label ?? '-' }}</small>
                    @if($mouvement->partieTierce?->reference)
                        <br><small>Réf : {{ $mouvement->partieTierce->reference }}</small>
                    @endif
                    @if($mouvement->partieTierce?->telephone)
                        <br><small>Tél : {{ $mouvement->partieTierce->telephone }}</small>
                    @endif
                @else
                    <strong>{{ $mouvement->ayantDroit->nom_complet ?? 'Non renseigné' }}</strong>
                    @if($mouvement->ayantDroit?->numero_cni)
                        <br><small>CNI : {{ $mouvement->ayantDroit->numero_cni }}</small>
                    @endif
                    @if($mouvement->ayantDroit?->telephone)
                        <br><small>Tél : {{ $mouvement->ayantDroit->telephone }}</small>
                    @endif
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Motif</div>
            <div class="info-value">{{ $mouvement->motifMouvement->libelle ?? 'Non renseigné' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Montant retiré</div>
            <div class="info-value"><strong>{{ number_format($mouvement->montant_mouvement, 0, ',', ' ') }} FCFA</strong></div>
        </div>
        {{-- <div class="info-row">
            <div class="info-label">Solde du séquestre après opération</div>
            <div class="info-value"><strong>{{ number_format($mouvement->solde_apres, 0, ',', ' ') }} FCFA</strong></div>
        </div> --}}
    </div>

    @if($mouvement->est_procuration)
    <div class="alert-procuration">
        <strong>⚠️ Retrait effectué par procuration</strong><br>
        Mandataire : <strong>{{ $mouvement->mandataire_nom }}</strong><br>
        Référence de la procuration : {{ $mouvement->mandataire_reference_procuration ?? 'Non renseignée' }}
    </div>
    @endif

    <div class="montant-lettres">
        Somme retirée : <strong>{{ ucfirst($montantEnLettres) }} francs CFA</strong>
        ({{ number_format($mouvement->montant_mouvement, 0, ',', ' ') }} FCFA).
    </div>

    <div class="mention-legale">
        @if($mouvement->sequestre_partie_tierce_id)
            Je soussigné(e) <strong>{{ $mouvement->partieTierce->nom_complet ?? 'le prestataire susnommé' }}</strong>
            ({{ $mouvement->partieTierce->type_label ?? 'Partie Tierce' }}), reconnais avoir reçu du Greffier en Chef
            du {{ $mouvement->sequestre->dossier->tribunal->nom ?? 'Tribunal' }} la somme de
            <strong>{{ number_format($mouvement->montant_mouvement, 0, ',', ' ') }} FCFA</strong>
            ({{ $montantEnLettres }} francs CFA), au titre de « {{ $mouvement->motifMouvement->libelle ?? $mouvement->operateur_beneficiaire }} »,
            pour le compte du séquestre {{ $mouvement->sequestre->numero_dossier_sequestre }}.
        @elseif($mouvement->est_procuration)
            Je soussigné(e) <strong>{{ $mouvement->mandataire_nom }}</strong>, agissant en qualité de mandataire de
            <strong>{{ $mouvement->ayantDroit->nom_complet ?? 'l\'ayant droit susnommé' }}</strong> en vertu de la
            procuration référencée ci-dessus, reconnais avoir reçu du Greffier en Chef du {{ $mouvement->sequestre->dossier->tribunal->nom ?? 'Tribunal' }} la somme de
            <strong>{{ number_format($mouvement->montant_mouvement, 0, ',', ' ') }} FCFA</strong>
            ({{ $montantEnLettres }} francs CFA), pour le compte du séquestre {{ $mouvement->sequestre->numero_dossier_sequestre }}.
        @else
            Je soussigné(e) <strong>{{ $mouvement->ayantDroit->nom_complet ?? 'le bénéficiaire susnommé' }}</strong>,
            reconnais avoir reçu personnellement du Greffe la somme de
            <strong>{{ number_format($mouvement->montant_mouvement, 0, ',', ' ') }} FCFA</strong>
            ({{ $montantEnLettres }} francs CFA), au titre de « {{ $mouvement->motifMouvement->libelle ?? $mouvement->operateur_beneficiaire }} »,
            au titre du séquestre {{ $mouvement->sequestre->numero_dossier_sequestre }}.
        @endif
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="titre-signature">
                @if($mouvement->sequestre_partie_tierce_id)
                    Le Prestataire
                @else
                    {{ $mouvement->est_procuration ? 'Le Mandataire' : 'Le Bénéficiaire' }}
                @endif
            </div>
            <div class="ligne-signature">Nom, date et signature (précédée de la mention « Bon pour reçu »)</div>
        </div>
        <div class="signature-box">
            <div class="titre-signature">Pour le Greffe en Chef</div>
            <div class="ligne-signature">Nom, date et signature</div>
        </div>
    </div>

    <div class="footer">
        Document généré le {{ $dateImpression->format('d/m/Y à H:i') }} —
        Document officiel - {{ $mouvement->sequestre->dossier->tribunal->nom ?? 'Tribunal' }}
    </div>
</body>
</html>