<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $titre }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 3px solid #000;
        }

        .header h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 10pt; font-weight: normal; color: #333; }

        .titre-document {
            text-align: center;
            margin: 12px 0;
            padding: 10px;
            background-color: #f0f0f0;
            border: 2px solid #2563eb;
        }

        .titre-document h1 { font-size: 12pt; }
        .titre-document .entite { font-size: 10pt; font-weight: bold; margin-top: 4px; }
        .periode { font-size: 9pt; color: #555; margin-top: 4px; }

        .identite {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 8.5pt;
        }

        .identite td { padding: 3px 6px; vertical-align: top; }
        .identite td.label { font-weight: bold; width: 22%; color: #333; }

        table.recap {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 12px;
            font-size: 8.5pt;
        }

        table.recap td {
            border: 1px solid #d1d5db;
            padding: 4px 6px;
            background-color: #f9fafb;
        }

        table.recap td.label { font-weight: bold; width: 30%; color: #333; }

        table.mouvements {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        table.mouvements th {
            background-color: #f0f0f0;
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 7.5pt;
        }

        table.mouvements td {
            padding: 4px;
            border: 1px solid #ddd;
            font-size: 7.5pt;
            vertical-align: top;
        }

        table.mouvements tfoot td {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        table.mouvements tr.ligne-groupe td {
            background-color: #e5edff;
            font-weight: bold;
            font-size: 8pt;
            border-color: #c7d2fe;
        }

        table.mouvements tr.ligne-sous-total td {
            background-color: #fafafa;
            font-style: italic;
            font-weight: bold;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .montant-positif { color: #166534; }
        .montant-negatif { color: #991b1b; }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 7pt;
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
    </div>

    <div class="titre-document">
        <h1>{{ $titre }}</h1>
        @if($entite)
            <div class="entite">{{ $entite }}</div>
        @endif
        <div class="periode">
            @if($dateDebut || $dateFin)
                Période : {{ $dateDebut ? \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') : '...' }}
                au {{ $dateFin ? \Carbon\Carbon::parse($dateFin)->format('d/m/Y') : '...' }}
            @else
                Toutes périodes confondues
            @endif
        </div>
    </div>

    <table class="identite">
        <tr>
            <td class="label">Séquestre</td>
            <td>{{ $sequestre->numero_dossier_sequestre }} — {{ $sequestre->intitule }}</td>
        </tr>
        <tr>
            <td class="label">Dossier / Tribunal</td>
            <td>
                {{ $sequestre->dossier?->numero_dossier ?? '—' }}
                @if($sequestre->dossier?->tribunal)
                    — {{ $sequestre->dossier->tribunal->nom }}
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Décision</td>
            <td>{{ $sequestre->decision?->numero_repertoire ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Solde actuel au séquestre</td>
            <td><strong>{{ number_format((float) $sequestre->solde_actuel, 0, ',', ' ') }} FCFA</strong></td>
        </tr>
    </table>

    @if(!empty($recap))
        <table class="recap">
            @foreach($recap as $ligne)
                <tr>
                    <td class="label">{{ $ligne['label'] }}</td>
                    <td>{{ $ligne['valeur'] }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <table class="mouvements">
        <thead>
            <tr>
                <th>Date</th>
                <th>Motif</th>
                <th>Opérateur / Bénéficiaire</th>
                <th>Procuration</th>
                <th class="text-right">Entrées</th>
                <th class="text-right">Sorties</th>
                <th class="text-right">Montant séquestre</th>
                <th class="text-right">Solde après</th>
            </tr>
        </thead>
        <tbody>
            @forelse($groupes as $groupe)
                <tr class="ligne-groupe">
                    <td colspan="8">
                        <strong>{{ $groupe['type'] }}</strong>
                        — {{ $groupe['lignes']->count() }} mouvement(s)
                        @if($groupe['entrees'] > 0)
                            · Entrées : {{ number_format($groupe['entrees'], 0, ',', ' ') }} FCFA
                        @endif
                        @if($groupe['sorties'] > 0)
                            · Sorties : {{ number_format($groupe['sorties'], 0, ',', ' ') }} FCFA
                        @endif
                    </td>
                </tr>
                @foreach($groupe['lignes'] as $mouvement)
                    <tr>
                        <td>{{ $mouvement->date_mouvement?->format('d/m/Y') }}</td>
                        <td>{{ $mouvement->motifMouvement?->libelle ?? '—' }}</td>
                        <td>{{ $mouvement->nom_affichage }}</td>
                        <td>
                            @if($mouvement->est_procuration)
                                Oui{{ $mouvement->mandataire_reference_procuration ? ' (' . $mouvement->mandataire_reference_procuration . ')' : '' }}
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-right montant-positif">
                            {{ $mouvement->type_mouvement === 'versement' ? number_format((float) $mouvement->montant_mouvement, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="text-right montant-negatif">
                            {{ $mouvement->type_mouvement === 'retrait' ? number_format((float) $mouvement->montant_mouvement, 0, ',', ' ') : '—' }}
                        </td>
                        <td class="text-right">{{ number_format((float) $mouvement->montant_precompte, 0, ',', ' ') }}</td>
                        <td class="text-right"><strong>{{ number_format((float) $mouvement->solde_apres, 0, ',', ' ') }}</strong></td>
                    </tr>
                @endforeach
                <tr class="ligne-sous-total">
                    <td colspan="4" class="text-right">Sous-total {{ $groupe['type'] }}</td>
                    <td class="text-right montant-positif">{{ $groupe['entrees'] > 0 ? number_format($groupe['entrees'], 0, ',', ' ') : '—' }}</td>
                    <td class="text-right montant-negatif">{{ $groupe['sorties'] > 0 ? number_format($groupe['sorties'], 0, ',', ' ') : '—' }}</td>
                    <td class="text-right">{{ number_format($groupe['precompte'], 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">Aucun mouvement ne correspond à cet état.</td>
                </tr>
            @endforelse
        </tbody>
        @if($mouvements->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">TOTAUX :</td>
                    <td class="text-right">{{ number_format($totalEntrees, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($totalSorties, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ number_format($totalPrecompte, 0, ',', ' ') }}</td>
                    <td class="text-right">{{ $soldeFinal !== null ? number_format((float) $soldeFinal, 0, ',', ' ') : '—' }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        État généré le {{ $dateImpression->format('d/m/Y à H:i') }} —
        {{ $mouvements->count() }} mouvement(s)
    </div>
</body>
</html>
