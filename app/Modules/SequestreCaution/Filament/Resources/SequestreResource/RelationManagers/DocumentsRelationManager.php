<?php

namespace App\Modules\SequestreCaution\Filament\Resources\SequestreResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';

    protected static ?string $title = 'Sous-dossiers documentaires';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('categorie')
                ->label('Sous-dossier')
                ->options([
                    'courrier' => '📨 Courrier',
                    'procedure' => '⚖️ Procédure',
                    'contrat' => '📄 Contrats',
                    'quittance' => '🧾 Quittances',
                ])
                ->required()
                ->native(false),

            Forms\Components\TextInput::make('libelle')
                ->label('Libellé du document')
                ->required()
                ->maxLength(255),

            Forms\Components\FileUpload::make('fichier_path')
                ->label('Fichier')
                ->disk('local')
                ->directory('sequestre-documents')
                ->visibility('private')
                ->required()
                ->downloadable()
                ->openable()
                ->maxSize(10240) // 10 Mo
                ->columnSpanFull(),

            Forms\Components\Textarea::make('description')
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('libelle')
            ->columns([
                Tables\Columns\TextColumn::make('categorie_label')
                    ->label('Sous-dossier')
                    ->badge()
                    ->color(fn($record) => match ($record->categorie) {
                        'courrier' => 'info',
                        'procedure' => 'warning',
                        'contrat' => 'success',
                        'quittance' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('libelle')
                    ->label('Document')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('deposePar.name')
                    ->label('Déposé par')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Déposé le')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('categorie')
                    ->options([
                        'courrier' => 'Courrier',
                        'procedure' => 'Procédure',
                        'contrat' => 'Contrats',
                        'quittance' => 'Quittances',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('➕ Ajouter un document')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['depose_par'] = auth()->id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('telecharger')
                    ->label('Télécharger')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn($record) => response()->download(
                        \Illuminate\Support\Facades\Storage::disk('local')->path($record->fichier_path),
                        $record->libelle
                    )),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('categorie');
    }
}
