<?php

namespace App\Filament\Filters;

use Carbon\Carbon;
use Filament\Forms;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filtre "Période" réutilisable : choix rapide d'une année (remplit les bornes)
 * ou saisie manuelle de dates, appliqué sur une colonne de date donnée.
 */
class PeriodeFilter
{
    public static function make(string $colonne, string $label, string $model): Filter
    {
        return Filter::make("periode_{$colonne}")
            ->label($label)
            ->form([
                Forms\Components\Select::make('annee')
                    ->label('Année')
                    ->options(fn(): array => static::anneesDisponibles($model, $colonne))
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('date_debut', filled($state) ? "{$state}-01-01" : null);
                        $set('date_fin', filled($state) ? "{$state}-12-31" : null);
                    })
                    ->helperText('Choisir une année remplit les deux dates. Vous pouvez ensuite les affiner.'),

                Forms\Components\DatePicker::make('date_debut')
                    ->label('Du')
                    ->native(false)
                    ->displayFormat('d/m/Y'),

                Forms\Components\DatePicker::make('date_fin')
                    ->label('Au')
                    ->native(false)
                    ->displayFormat('d/m/Y'),
            ])
            ->query(function (Builder $query, array $data) use ($colonne): Builder {
                return $query
                    ->when($data['date_debut'] ?? null, fn(Builder $q, $date) => $q->whereDate($colonne, '>=', $date))
                    ->when($data['date_fin'] ?? null, fn(Builder $q, $date) => $q->whereDate($colonne, '<=', $date));
            })
            ->indicateUsing(function (array $data) use ($label): array {
                $debut = $data['date_debut'] ?? null;
                $fin = $data['date_fin'] ?? null;

                if (! $debut && ! $fin) {
                    return [];
                }

                return [
                    $label . ' : du ' . ($debut ? Carbon::parse($debut)->format('d/m/Y') : '…')
                        . ' au ' . ($fin ? Carbon::parse($fin)->format('d/m/Y') : '…'),
                ];
            });
    }

    /**
     * Années réellement présentes en base (min/max), sans charger les lignes.
     * Publique pour être réutilisée par les rapports qui construisent leur
     * propre filtre période (rapport consolidé des séquestres).
     */
    public static function anneesDisponibles(string $model, string $colonne): array
    {
        $min = $model::query()->min($colonne);
        $max = $model::query()->max($colonne);

        if (! $min || ! $max) {
            return [];
        }

        $anneeMax = (int) substr((string) $max, 0, 4);
        $anneeMin = (int) substr((string) $min, 0, 4);

        return collect(range($anneeMax, $anneeMin))
            ->mapWithKeys(fn(int $annee) => [$annee => (string) $annee])
            ->all();
    }
}
