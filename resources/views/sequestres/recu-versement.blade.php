<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de Versement - {{ $numeroRecu }}</title>
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
            background-color: #dcfce7;
            border: 2px solid #16a34a;
        }

        .titre-document h1 { font-size: 15pt; color: #166534; }
        .titre-document .numero { font-size: 11pt; color: #166534; margin-top: 4px; }

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

        .montant-lettres {
            margin: 15px 0;
            padding: 12px;
            background-color: #fafafa;
            border-left: 4px solid #16a34a;
            font-style: italic;
        }

        .signatures {
            display: table;
            width: 100%;
            margin-top: 60px;
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
        <h1>REÇU DE VERSEMENT</h1>
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
            <div class="info-label">Date du versement</div>
            <div class="info-value">{{ $mouvement->date_mouvement->format('d/m/Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Versé par</div>
            <div class="info-value">
                <strong>{{ $mouvement->partieAdverse->nom_complet ?? $mouvement->operateur_beneficiaire }}</strong>
                @if($mouvement->partieAdverse?->numero_cni)
                    <br><small>CNI : {{ $mouvement->partieAdverse->numero_cni }}</small>
                @endif
                @if($mouvement->partieAdverse?->telephone)
                    <br><small>Tél : {{ $mouvement->partieAdverse->telephone }}</small>
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Motif</div>
            <div class="info-value">{{ $mouvement->motifMouvement->libelle ?? 'Non renseigné' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Montant versé</div>
            <div class="info-value"><strong>{{ number_format($mouvement->montant_mouvement, 0, ',', ' ') }} FCFA</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Taux de précompte appliqué</div>
            <div class="info-value">{{ number_format($mouvement->taux_applique * 100, 2) }} %</div>
        </div>
        <div class="info-row">
            <div class="info-label">Montant précompté</div>
            <div class="info-value">{{ number_format($mouvement->montant_precompte, 0, ',', ' ') }} FCFA</div>
        </div>
        <div class="info-row">
            <div class="info-label">Montant net crédité au séquestre</div>
            <div class="info-value"><strong>{{ number_format($mouvement->montant_net, 0, ',', ' ') }} FCFA</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Solde du séquestre après opération</div>
            <div class="info-value"><strong>{{ number_format($mouvement->solde_apres, 0, ',', ' ') }} FCFA</strong></div>
        </div>
    </div>

    <div class="montant-lettres">
        Arrêté le présent reçu à la somme de <strong>{{ ucfirst($montantEnLettres) }} francs CFA</strong>
        ({{ number_format($mouvement->montant_mouvement, 0, ',', ' ') }} FCFA).
    </div>

    <div class="signatures">
        <div class="signature-box">
            <div class="titre-signature">Le Versant</div>
            <div class="ligne-signature">Nom, date et signature</div>
        </div>
        <div class="signature-box">
            <div class="titre-signature">Pour le Greffe</div>
            <div class="ligne-signature">Nom, date et signature</div>
        </div>
    </div>

    <div class="footer">
        Document généré le {{ $dateImpression->format('d/m/Y à H:i') }} —
        Document officiel - {{ $mouvement->sequestre->dossier->tribunal->nom ?? 'Tribunal' }}
    </div>
</body>
</html>