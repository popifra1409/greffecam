<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>État des comptes bancaires</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 7pt;
            line-height: 1.35;
            color: #000;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #000;
        }

        .header h1 { font-size: 13pt; font-weight: bold; text-transform: uppercase; }
        .header h2 { font-size: 10pt; font-weight: normal; color: #333; }

        .titre-document {
            text-align: center;
            margin: 10px 0;
            padding: 8px;
            background-color: #f0f0f0;
            border: 1px solid #2563eb;
        }

        .titre-document h1 { font-size: 12pt; }
        .periode { font-size: 8pt; color: #444; margin-top: 4px; }

        table.etat {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.etat th {
            background-color: #e5e7eb;
            padding: 4px 3px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #9ca3af;
            font-size: 6.5pt;
        }

        table.etat td {
            padding: 3px;
            border: 1px solid #d1d5db;
            font-size: 6.5pt;
            vertical-align: top;
        }

        table.etat tfoot td {
            font-weight: bold;
            background-color: #f3f4f6;
            font-size: 7pt;
        }

        .sous-titre { font-size: 6pt; color: #555; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .montant-positif { color: #166534; }
        .montant-negatif { color: #991b1b; }
        .a-decaisser { color: #991b1b; }
        .a-renflouer { color: #b45309; }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 6.5pt;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 4px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>République du Cameroun</h1>
        <h2>Paix - Travail - Patrie</h2>
    </div>

    <div class="titre-document">
        <h1>ÉTAT DES COMPTES BANCAIRES DES SÉQUESTRES</h1>
        <div class="periode">
            Situation arrêtée au {{ \Carbon\Carbon::parse($dateArrete)->format('d/m/Y') }}
            — Intérêts : {{ number_format($tauxInteret * 100, 2, ',', ' ') }} % l'an
            — TVA sur intérêts : {{ number_format($tauxTva * 100, 2, ',', ' ') }} %
        </div>
    </div>

    <table class="etat">
        <thead>
            <tr>
                <th>Intitulé du compte</th>
                <th>N° du compte</th>
                <th>Date d'ouverture</th>
                <th class="text-right">Total entrées</th>
                <th class="text-right">Total sorties</th>
                <th class="text-right">Solde à la banque</th>
                <th class="text-right">Solde au séquestre</th>
                <th class="text-center">Statut du compte</th>
                <th class="text-center">État du compte</th>
                <th class="text-right">À renflouer / décaisser</th>
                <th class="text-right">Intérêts du solde</th>
                <th class="text-right">TVA sur intérêts</th>
                <th class="text-right">Solde banque + intérêts</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lignes as $ligne)
            <tr>
                <td>
                    {{ $ligne['intitule'] }}
                    <div class="sous-titre">{{ $ligne['numero_dossier'] }}</div>
                </td>
                <td>{{ $ligne['numero_compte'] ?: '— (à ouvrir)' }}</td>
                <td>{{ $ligne['date_ouverture_compte'] ? $ligne['date_ouverture_compte']->format('d/m/Y') : '—' }}</td>
                <td class="text-right montant-positif">{{ number_format($ligne['total_entrees'], 0, ',', ' ') }}</td>
                <td class="text-right montant-negatif">{{ number_format($ligne['total_sorties'], 0, ',', ' ') }}</td>
                <td class="text-right">
                    {{ $ligne['solde_banque'] === null ? 'Non renseigné' : number_format($ligne['solde_banque'], 0, ',', ' ') }}
                </td>
                <td class="text-right">{{ number_format($ligne['solde_sequestre'], 0, ',', ' ') }}</td>
                <td class="text-center">{{ $ligne['statut_compte'] }}</td>
                <td class="text-center {{ $ligne['etat_compte'] === 'À décaisser' ? 'a-decaisser' : ($ligne['etat_compte'] === 'À renflouer' ? 'a-renflouer' : '') }}">
                    {{ $ligne['etat_compte'] }}
                </td>
                <td class="text-right">{{ number_format($ligne['montant_ecart'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($ligne['interet'], 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($ligne['tva'], 0, ',', ' ') }}</td>
                <td class="text-right">
                    {{ $ligne['solde_banque_interet'] === null ? '—' : number_format($ligne['solde_banque_interet'], 0, ',', ' ') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="13" class="text-center">Aucun séquestre pour cette situation.</td>
            </tr>
            @endforelse
        </tbody>
        @if($lignes->isNotEmpty())
        <tfoot>
            <tr>
                <td colspan="3" class="text-right">TOTAUX GÉNÉRAUX :</td>
                <td class="text-right">{{ number_format($lignes->sum('total_entrees'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($lignes->sum('total_sorties'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($lignes->whereNotNull('solde_banque')->sum('solde_banque'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($lignes->sum('solde_sequestre'), 0, ',', ' ') }}</td>
                <td></td>
                <td></td>
                <td class="text-right">{{ number_format($lignes->sum('montant_ecart'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($lignes->sum('interet'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($lignes->sum('tva'), 0, ',', ' ') }}</td>
                <td class="text-right">{{ number_format($lignes->whereNotNull('solde_banque_interet')->sum('solde_banque_interet'), 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">
        État généré le {{ $dateImpression->format('d/m/Y à H:i') }} —
        {{ $lignes->count() }} compte(s) — Les soldes bancaires non renseignés correspondent aux comptes sans relevé à la date d'arrêté.
    </div>
</body>
</html>
