<?php

namespace App\Modules\DecisionRecours\Filament\Pages;

use App\Models\NotificationPreference;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class PreferencesNotification extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static string $view = 'filament.pages.preferences-notification';

    protected static ?string $navigationLabel = 'Mes notifications';

    protected static ?string $title = 'Préférences de notification';

    protected static ?string $navigationGroup = 'Mon compte';

    protected static ?int $navigationSort = 99;

    // Visibilité : tous les utilisateurs connectés
    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public ?array $data = [];

    public function mount(): void
    {
        $preferences = auth()->user()->notificationPreference;

        if ($preferences) {
            $this->form->fill($preferences->toArray());
        } else {
            // Valeurs par défaut
            $this->form->fill([
                'email_enabled' => true,
                'push_enabled' => true,
                'sms_enabled' => false,
                'whatsapp_enabled' => false,
                'frequence' => 'quotidien',
                'heure_debut' => '08:00:00',
                'heure_fin' => '18:00:00',
                'recours_non_enregistres' => true,
                'recours_non_transmis' => true,
                'recours_urgents' => true,
                'resume_quotidien' => true,
                'heure_resume' => '08:00:00',
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Canaux de notification')
                    ->description('Choisissez comment vous souhaitez être notifié(e)')
                    ->schema([
                        Forms\Components\Toggle::make('email_enabled')
                            ->label('📧 Email')
                            ->helperText('Recevoir les notifications par email')
                            ->default(true)
                            ->live(),

                        Forms\Components\Toggle::make('push_enabled')
                            ->label('🔔 Notifications dans l\'application')
                            ->helperText('Recevoir les notifications directement dans le système')
                            ->default(true),

                        Forms\Components\Toggle::make('sms_enabled')
                            ->label('📱 SMS')
                            ->helperText('Recevoir les notifications par SMS (pour les urgences)')
                            ->live(),

                        Forms\Components\TextInput::make('phone_number')
                            ->label('Numéro de téléphone (SMS)')
                            ->tel()
                            ->placeholder('+237 6XX XX XX XX')
                            ->helperText('Format international : +237XXXXXXXXX')
                            ->visible(fn(Forms\Get $get) => $get('sms_enabled'))
                            ->required(fn(Forms\Get $get) => $get('sms_enabled')),

                        Forms\Components\Toggle::make('whatsapp_enabled')
                            ->label('💬 WhatsApp')
                            ->helperText('Recevoir les notifications par WhatsApp')
                            ->live(),

                        Forms\Components\TextInput::make('whatsapp_number')
                            ->label('Numéro WhatsApp')
                            ->tel()
                            ->placeholder('+237 6XX XX XX XX')
                            ->helperText('Format international : +237XXXXXXXXX')
                            ->visible(fn(Forms\Get $get) => $get('whatsapp_enabled'))
                            ->required(fn(Forms\Get $get) => $get('whatsapp_enabled')),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Fréquence et horaires')
                    ->description('Définissez quand recevoir les notifications')
                    ->schema([
                        Forms\Components\Select::make('frequence')
                            ->label('Fréquence des notifications')
                            ->options([
                                'quotidien' => 'Quotidien (tous les jours)',
                                'bi_quotidien' => 'Bi-quotidien (matin et après-midi)',
                                'hebdomadaire' => 'Hebdomadaire (résumé le lundi)',
                            ])
                            ->default('quotidien')
                            ->required()
                            ->helperText('À quelle fréquence souhaitez-vous recevoir les notifications ?'),

                        Forms\Components\TimePicker::make('heure_debut')
                            ->label('Heure de début')
                            ->default('08:00:00')
                            ->required()
                            ->helperText('Ne pas envoyer de notifications avant cette heure'),

                        Forms\Components\TimePicker::make('heure_fin')
                            ->label('Heure de fin')
                            ->default('18:00:00')
                            ->required()
                            ->helperText('Ne pas envoyer de notifications après cette heure'),

                        Forms\Components\Placeholder::make('note_horaires')
                            ->label('')
                            ->content('⚠️ Les notifications URGENTES seront toujours envoyées, même en dehors de ces horaires.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Types d\'alertes')
                    ->description('Choisissez les types d\'alertes que vous souhaitez recevoir')
                    ->schema([
                        Forms\Components\Toggle::make('recours_non_enregistres')
                            ->label('📋 Recours non enregistrés')
                            ->helperText('Recours déclarés mais non enregistrés depuis plus de 3 jours')
                            ->default(true),

                        Forms\Components\Toggle::make('recours_non_transmis')
                            ->label('📤 Recours non transmis')
                            ->helperText('Recours enregistrés mais non transmis à la CA depuis plus de 7 jours')
                            ->default(true),

                        Forms\Components\Toggle::make('recours_urgents')
                            ->label('🔴 Recours urgents')
                            ->helperText('Recours en attente depuis plus de 30 jours')
                            ->default(true),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('Résumé quotidien')
                    ->description('Recevoir un résumé quotidien des recours à traiter')
                    ->schema([
                        Forms\Components\Toggle::make('resume_quotidien')
                            ->label('📊 Activer le résumé quotidien')
                            ->helperText('Recevoir un email récapitulatif chaque jour')
                            ->default(true)
                            ->live(),

                        Forms\Components\TimePicker::make('heure_resume')
                            ->label('Heure du résumé')
                            ->default('08:00:00')
                            ->required(fn(Forms\Get $get) => $get('resume_quotidien'))
                            ->visible(fn(Forms\Get $get) => $get('resume_quotidien'))
                            ->helperText('À quelle heure souhaitez-vous recevoir le résumé ?'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $user = auth()->user();

        // Créer ou mettre à jour les préférences
        $user->notificationPreference()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        Notification::make()
            ->title('✅ Préférences enregistrées')
            ->success()
            ->body('Vos préférences de notification ont été mises à jour avec succès.')
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('💾 Enregistrer mes préférences')
                ->action('save')
                ->color('primary')
                ->icon('heroicon-o-check'),
        ];
    }
}