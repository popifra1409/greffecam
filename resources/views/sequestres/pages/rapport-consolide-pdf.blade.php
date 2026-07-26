<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport Consolidé des Séquestres</title>
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
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 3px solid #000;
        }

        .header h1 { font-size: 15pt; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 11pt; font-weight: normal; color: #333; }

        .titre-document {
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background-color: #f0f0f0;
            border: 2px solid #2563eb;
        }

        .titre-document h1 { font-size: 13pt; }
        .periode { font-size: 9pt; color: #666; margin-top: 5px; }

        table.rapport {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table.rapport th {
            background-color: #f0f0f0;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
            font-size: 8pt;
        }

        table.rapport td {
            padding: 5px 6px;
            border: 1px solid #ddd;
            font-size: 8pt;
        }

        table.rapport tfoot td {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        .text-right { text-align: right; }
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
        <h1>RAPPORT CONSOLIDÉ DES SÉQUESTRES</h1>
        <div class="periode">
            @if($dateDebut || $dateFin)
                Période : {{ $dateDebut ? \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') : '...' }}
                au {{ $dateFin ? \Carbon\Carbon::parse($dateFin)->format('d/m/Y') : '...' }}
            @else
                Toutes périodes confondues
            @endif
        </div>
    </div>

    <table class="rapport">
        <thead>
            <tr>
                <th>N° Dossier Séquestre</th>
                <th>Intitulé</th>
                <th>Nature</th>
                <th>Statut</th>
                <th class="text-right">Entrées (période)</th>
                <th class="text-right">Sorties (période)</th>
                <th class="text-right">Précompte (période)</th>
                <th class="text-right">Solde actuel</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sequestres as $sequestre)
            <tr>
                <td>{{ $sequestre->numero_dossier_sequestre }}</td>
                <td>{{ $sequestre->intitule }}</td>
                <td>{{ $sequestre->natureSequestre?->libelle ?? '-' }}</td>
                <td>{{ $sequestre->statutSequestre?->libelle ?? '-' }}</td>
                <td class="text-right montant-positif">{{ number_format($sequestre->total_entrees_periode, 0, ',', ' ') }}</td>
                <td class="text-right montant-negatif">{{ number_format($sequestre->total_sorties_periode, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($sequestre->total_precompte_periode, 0, ',', ' ') }}</td>
                <td class="text-right"><strong>{{ number_format($sequestre->solde_courant ?? 0, 0, ',', ' ') }}</strong></td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right">TOTAUX GÉNÉRAUX :</td>
                <td class="text-right">{{ number_format($totalEntrees, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totalSorties, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totalPrecompte, 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($totalSolde, 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Rapport généré le {{ $dateImpression->format('d/m/Y à H:i') }} —
        {{ $sequestres->count() }} séquestre(s) inclus dans ce rapport
    </div>
</body>
</html>