<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\Pages;

use App\Modules\SequestreCaution\Filament\Resources\SequestreResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewSequestre extends ViewRecord
{
    protected static string $resource = SequestreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('apercu_pdf')
                ->label('Aperçu PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn() => route('sequestres.etat.pdf', ['sequestre' => $this->record->id]))
                ->openUrlInNewTab(),

            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            // ================================================================
            // EN-TÊTE : Identification rapide
            // ================================================================
            Infolists\Components\Section::make()
                ->schema([
                    Infolists\Components\TextEntry::make('numero_dossier_sequestre')
                        ->label('N° Dossier Séquestre')
                        ->badge()
                        ->color('primary')
                        ->size('lg')
                        ->weight('bold')
                        ->icon('heroicon-o-lock-closed')
                        ->copyable(),

                    Infolists\Components\TextEntry::make('intitule')
                        ->label('')
                        ->size('lg')
                        ->weight('bold')
                        ->color('gray'),

                    Infolists\Components\TextEntry::make('statutSequestre.libelle')
                        ->label('Statut')
                        ->badge()
                        ->size('lg')
                        ->color(fn($record) => $record->statutSequestre?->couleur ?? 'gray'),
                ])
                ->columns(3),

            // ================================================================
            // RÉSUMÉ FINANCIER : mis en avant, c'est l'info la plus consultée
            // ================================================================
            Infolists\Components\Section::make('💰 Situation financière')
                ->schema([
                    Infolists\Components\TextEntry::make('solde_actuel')
                        ->label('Solde actuel')
                        ->money('XAF')
                        ->size('xl')
                        ->weight('bold')
                        ->color(fn($state) => $state >= 0 ? 'success' : 'danger'),

                    Infolists\Components\TextEntry::make('total_entrees')
                        ->label('Total des entrées')
                        ->money('XAF')
                        ->color('success')
                        ->icon('heroicon-o-arrow-down-circle'),

                    Infolists\Components\TextEntry::make('total_sorties')
                        ->label('Total des sorties')
                        ->money('XAF')
                        ->color('danger')
                        ->icon('heroicon-o-arrow-up-circle'),

                    Infolists\Components\TextEntry::make('montant_sequestre_total')
                        ->label('Total précompté')
                        ->money('XAF')
                        ->color('warning')
                        ->icon('heroicon-o-receipt-percent'),

                    Infolists\Components\TextEntry::make('taux_pourcentage')
                        ->label('Taux de précompte appliqué')
                        ->badge()
                        ->color('info'),

                    Infolists\Components\TextEntry::make('mouvements_count')
                        ->label('Nombre de mouvements')
                        ->getStateUsing(fn($record) => $record->mouvements()->count())
                        ->badge()
                        ->color('gray'),
                ])
                ->columns(3),

            // ================================================================
            // ORIGINE JUDICIAIRE
            // ================================================================
            Infolists\Components\Section::make('⚖️ Origine judiciaire')
                ->schema([
                    Infolists\Components\TextEntry::make('numero_dossier')
                        ->label('N° Dossier d\'enrôlement')
                        ->badge()
                        ->color('gray')
                        ->url(fn($record) => $record->dossier_id
                            ? \App\Modules\DecisionRecours\Filament\Resources\DossierResource::getUrl(
                                'view',
                                ['record' => $record->dossier_id],
                                panel: 'decision-recours'
                            )
                            : null)
                        ->openUrlInNewTab(),

                    Infolists\Components\TextEntry::make('numero_decision')
                        ->label('N° Décision')
                        ->badge()
                        ->color('primary')
                        ->url(fn($record) => $record->decision_id
                            ? \App\Modules\DecisionRecours\Filament\Resources\DecisionResource::getUrl(
                                'view',
                                ['record' => $record->decision_id],
                                panel: 'decision-recours'
                            )
                            : null)
                        ->openUrlInNewTab(),

                    Infolists\Components\TextEntry::make('type_decision_label')
                        ->label('Type de décision')
                        ->badge()
                        ->color('info'),

                    Infolists\Components\TextEntry::make('nature_decision_label')
                        ->label('Nature de la décision')
                        ->badge()
                        ->color('warning'),

                    Infolists\Components\TextEntry::make('date_decision')
                        ->label('Date de la décision')
                        ->date('d/m/Y'),

                    Infolists\Components\TextEntry::make('dossier.tribunal.nom')
                        ->label('Tribunal')
                        ->badge()
                        ->color('gray'),
                ])
                ->columns(3)
                ->collapsible(),

            // ================================================================
            // CARACTÉRISTIQUES DU SÉQUESTRE
            // ================================================================
            Infolists\Components\Section::make('📋 Caractéristiques')
                ->schema([
                    Infolists\Components\TextEntry::make('natureSequestre.libelle')
                        ->label('Nature du séquestre')
                        ->badge()
                        ->color('info'),

                    Infolists\Components\TextEntry::make('date_ouverture')
                        ->label('Date d\'ouverture')
                        ->date('d/m/Y')
                        ->icon('heroicon-o-calendar'),

                    Infolists\Components\TextEntry::make('representant.nom_complet')
                        ->label('Représentant de la famille')
                        ->placeholder('Non renseigné')
                        ->icon('heroicon-o-user'),

                    Infolists\Components\TextEntry::make('observations')
                        ->label('Observations')
                        ->placeholder('Aucune observation')
                        ->columnSpanFull(),
                ])
                ->columns(3)
                ->collapsible(),

            // ================================================================
            // AYANTS DROIT (bénéficiaires)
            // ================================================================
            Infolists\Components\Section::make('👥 Ayants droit (bénéficiaires)')
                ->description('Personnes qui perçoivent l\'argent du séquestre')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('ayantsDroit')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('nom_complet')
                                ->label('Nom complet')
                                ->weight('bold')
                                ->icon('heroicon-o-identification'),

                            Infolists\Components\TextEntry::make('numero_cni')
                                ->label('N° CNI')
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('telephone')
                                ->label('Téléphone')
                                ->placeholder('—')
                                ->icon('heroicon-o-phone')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('adresse')
                                ->label('Adresse')
                                ->placeholder('—')
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(fn($record) => $record->ayantsDroit->isEmpty()),

            // ================================================================
            // PARTIES ADVERSES (payeurs)
            // ================================================================
            Infolists\Components\Section::make('🏠 Parties adverses (payeurs)')
                ->description('Locataires ou autres parties qui versent l\'argent au séquestre')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('partiesAdverses')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('nom_complet')
                                ->label('Nom complet')
                                ->weight('bold')
                                ->icon('heroicon-o-identification'),

                            Infolists\Components\TextEntry::make('numero_cni')
                                ->label('N° CNI')
                                ->placeholder('—'),

                            Infolists\Components\TextEntry::make('telephone')
                                ->label('Téléphone')
                                ->placeholder('—')
                                ->icon('heroicon-o-phone')
                                ->copyable(),

                            Infolists\Components\TextEntry::make('adresse')
                                ->label('Adresse')
                                ->placeholder('—')
                                ->columnSpanFull(),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
                ])
                ->collapsible()
                ->collapsed(fn($record) => $record->partiesAdverses->isEmpty()),

            // ================================================================
            // SOUS-DOSSIERS DOCUMENTAIRES : compteurs par catégorie
            // ================================================================
            Infolists\Components\Section::make('📁 Sous-dossiers documentaires')
                ->schema([
                    Infolists\Components\TextEntry::make('doc_courrier')
                        ->label('📨 Courrier')
                        ->getStateUsing(fn($record) => $record->documents()->where('categorie', 'courrier')->count() . ' document(s)')
                        ->badge()
                        ->color('info'),

                    Infolists\Components\TextEntry::make('doc_procedure')
                        ->label('⚖️ Procédure')
                        ->getStateUsing(fn($record) => $record->documents()->where('categorie', 'procedure')->count() . ' document(s)')
                        ->badge()
                        ->color('warning'),

                    Infolists\Components\TextEntry::make('doc_contrat')
                        ->label('📄 Contrats')
                        ->getStateUsing(fn($record) => $record->documents()->where('categorie', 'contrat')->count() . ' document(s)')
                        ->badge()
                        ->color('success'),

                    Infolists\Components\TextEntry::make('doc_quittance')
                        ->label('🧾 Quittances')
                        ->getStateUsing(fn($record) => $record->documents()->where('categorie', 'quittance')->count() . ' document(s)')
                        ->badge()
                        ->color('primary'),
                ])
                ->columns(4)
                ->collapsible(),

            // ================================================================
            // MÉTADONNÉES
            // ================================================================
            Infolists\Components\Section::make('Métadonnées')
                ->schema([
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Créé le')
                        ->dateTime('d/m/Y à H:i'),

                    Infolists\Components\TextEntry::make('updated_at')
                        ->label('Modifié le')
                        ->dateTime('d/m/Y à H:i'),
                ])
                ->columns(2)
                ->collapsible()
                ->collapsed(),
        ]);
    }
}
