<?php

namespace App\Modules\DecisionRecours\Filament\Resources\RecoursResource\Pages;

use App\Modules\DecisionRecours\Filament\Resources\RecoursResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class ViewRecours extends ViewRecord
{
    protected static string $resource = RecoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),

            Actions\Action::make('voir_decision')
                ->label('Voir la décision attaquée')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(fn($record) => $record->decision_id
                    ? \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('view', ['record' => $record->decision_id])
                    : null)
                ->visible(fn($record) => $record->decision_id),

            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informations du recours')
                    ->schema([
                        Infolists\Components\TextEntry::make('numero_recours')
                            ->label('Numéro du recours')
                            ->badge()
                            ->color('primary')
                            ->size('lg')
                            ->weight('bold')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('decision.numero_repertoire')
                            ->label('Décision attaquée')
                            ->badge()
                            ->color('danger')
                            ->size('lg')
                            ->url(fn($record) => $record->decision_id
                                ? \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl('view', ['record' => $record->decision_id])
                                : null)
                            ->icon('heroicon-o-arrow-top-right-on-square'),

                        Infolists\Components\TextEntry::make('type_recours')
                            ->label('Type de recours')
                            ->badge()
                            ->size('lg')
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'appel' => '⚖️ Appel',
                                'opposition' => '⚠️ Opposition',
                                'tierce_opposition' => '👥 Tierce opposition',
                                'retractation' => '🔄 Rétractation',
                                'revision' => '🔍 Révision',
                                'pourvoi_cassation' => '⚖️ Pourvoi en cassation',
                                default => $state,
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'appel' => 'danger',
                                'opposition' => 'warning',
                                'pourvoi_cassation' => 'info',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('date_recours')
                            ->label('Date du recours')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('warning'),

                        Infolists\Components\TextEntry::make('reference_lettre')
                            ->label('Référence de la lettre')
                            ->badge()
                            ->copyable()
                            ->placeholder('Non renseignée'),

                        Infolists\Components\TextEntry::make('fichier_lettre')
                            ->label('Lettre de déclaration')
                            ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                            ->openUrlInNewTab()
                            ->badge()
                            ->icon('heroicon-o-document-arrow-down')
                            ->placeholder('Pas de fichier'),
                    ])->columns(3),

                Infolists\Components\Section::make('Dates de traitement')
                    ->schema([
                        Infolists\Components\TextEntry::make('date_enregistrement')
                            ->label('Date d\'enregistrement')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('info')
                            ->placeholder('Non enregistré'),

                        Infolists\Components\TextEntry::make('date_transmission_cour_appel')
                            ->label('Date de transmission à la Cour d\'Appel')
                            ->date('d/m/Y')
                            ->badge()
                            ->color('success')
                            ->placeholder('Non transmis'),
                    ])->columns(2)
                    ->collapsible(),

                Infolists\Components\Section::make('Documents de mise en état')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('documents_mise_en_etat')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('type')
                                    ->label('Type de document')
                                    ->badge()
                                    ->color('primary')
                                    ->formatStateUsing(fn(string $state): string => match ($state) {
                                        'pv_reception' => '📋 PV de réception',
                                        'pv_notification_appelant' => '📬 PV notification appelant',
                                        'pv_notification_intime' => '📬 PV notification intimé',
                                        'memoire_appelant' => '📝 Mémoire appelant',
                                        'memoire_intime' => '📝 Mémoire intimé',
                                        'pieces_justificatives' => '📎 Pièces justificatives',
                                        'ordonnance_cloture' => '⚖️ Ordonnance de clôture',
                                        'autre' => '📄 Autre',
                                        default => $state,
                                    }),

                                Infolists\Components\TextEntry::make('date')
                                    ->label('Date')
                                    ->date('d/m/Y')
                                    ->badge(),

                                Infolists\Components\TextEntry::make('reference')
                                    ->label('Référence')
                                    ->badge()
                                    ->placeholder('Sans référence'),

                                Infolists\Components\TextEntry::make('fichier')
                                    ->label('Fichier')
                                    ->url(fn($state) => $state ? asset('storage/' . $state) : null, true)
                                    ->openUrlInNewTab()
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-document-arrow-down'),
                            ])
                            ->columns(4)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn($record) => !empty($record->documents_mise_en_etat))
                    ->collapsible()
                    ->collapsed(fn($record) => count($record->documents_mise_en_etat ?? []) > 3),

                Infolists\Components\Section::make('Informations sur la décision attaquée')
                    ->schema([
                        Infolists\Components\TextEntry::make('decision.dossier.numero_dossier')
                            ->label('Numéro du dossier')
                            ->badge()
                            ->color('gray'),

                        Infolists\Components\TextEntry::make('decision.tribunal.nom')
                            ->label('Tribunal')
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('decision.date_decision')
                            ->label('Date de décision')
                            ->date('d/m/Y')
                            ->badge(),

                        Infolists\Components\TextEntry::make('decision.statut')
                            ->label('Statut de la décision')
                            ->badge()
                            ->formatStateUsing(fn(string $state): string => match ($state) {
                                'brouillon' => 'Brouillon',
                                'validee' => 'Validée',
                                'saisie' => 'Saisie',
                                'signee' => 'Signée',
                                'enregistree' => 'Enregistrée',
                                'archivee' => 'Archivée',
                                default => $state,
                            })
                            ->color(fn(string $state): string => match ($state) {
                                'archivee' => 'secondary',
                                'enregistree' => 'success',
                                'signee' => 'primary',
                                default => 'warning',
                            }),
                    ])->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Infolists\Components\Section::make('Métadonnées')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y à H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y à H:i'),
                    ])->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}