<?php

namespace App\Modules\SequestreCaution\Filament\Resources;

use App\Modules\SequestreCaution\Filament\Resources\NatureSequestreResource\Pages;
use App\Models\NatureSequestre;
use App\Traits\HasResourcePermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NatureSequestreResource extends Resource
{
    use HasResourcePermissions;

    protected static ?string $model = NatureSequestre::class;
    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Référentiels';
    protected static ?string $modelLabel = 'Nature de séquestre';
    protected static ?string $pluralModelLabel = 'Natures de séquestre';

    protected static function getViewPermission(): string
    {
        return 'view_referentiels';
    }
    protected static function getCreatePermission(): string
    {
        return 'manage_referentiels';
    }
    protected static function getEditPermission(): string
    {
        return 'manage_referentiels';
    }
    protected static function getDeletePermission(): string
    {
        return 'manage_referentiels';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations générales')
                ->schema([
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('libelle')
                        ->label('Libellé')
                        ->required(),

                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull(),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Actif')
                        ->default(true),
                ])->columns(2),

            Forms\Components\Section::make('Terminologie personnalisée')
                ->description('Adapte le vocabulaire utilisé dans les états et écrans selon le contexte (ex: "Locataires" pour un séquestre de loyer, "Héritiers" pour une succession).')
                ->schema([
                    Forms\Components\TextInput::make('terme_ayants_droit')
                        ->label('Terme "Ayants droit" pour cette nature')
                        ->placeholder('Ex: Héritiers, Pupille, Bénéficiaires...')
                        ->helperText('Laissez vide pour utiliser "Ayants droit" par défaut'),

                    Forms\Components\TextInput::make('terme_parties_adverses')
                        ->label('Terme "Parties adverses" pour cette nature')
                        ->placeholder('Ex: Locataires (versants), Débiteurs, Contributeurs...')
                        ->helperText('Laissez vide pour utiliser "Parties adverses (payeurs)" par défaut'),

                    Forms\Components\TextInput::make('terme_partie_tierce')
                        ->label('Terme "Partie Tierce" pour cette nature')
                        ->placeholder('Ex: Prestataires, Notaires & Prestataires...')
                        ->helperText('Laissez vide pour utiliser "Partie Tierce (Huissier, Avocat, Services)" par défaut'),
                ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('libelle')
                    ->label('Libellé')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('terme_ayants_droit')
                    ->label('Terme "Ayants droit"')
                    ->placeholder('Ayants droit (défaut)')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('terme_parties_adverses')
                    ->label('Terme "Parties adverses"')
                    ->placeholder('Parties adverses (défaut)')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('terme_partie_tierce')
                    ->label('Terme "Partie Tierce"')
                    ->placeholder('Partie Tierce (défaut)')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sequestres_count')
                    ->label('Séquestres')
                    ->counts('sequestres')
                    ->badge(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNatureSequestres::route('/'),
            'create' => Pages\CreateNatureSequestre::route('/create'),
            'edit' => Pages\EditNatureSequestre::route('/{record}/edit'),
        ];
    }
}
