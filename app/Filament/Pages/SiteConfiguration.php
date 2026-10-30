<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\Settings as SettingsReader;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;

/**
 * The control panel for everything a visitor can read.
 *
 * Values are stored in `site_settings` and read through App\Support\Settings,
 * so an edit here changes the public site immediately after the cache flush.
 */
class SiteConfiguration extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $title = 'Site settings';

    protected static ?int $navigationSort = 6;

    protected static string $view = 'filament.pages.site-configuration';

    /** @var array<string, mixed> */
    public array $data = [];

    /** Which language is being edited. Driven by the header language switch. */
    public string $locale = 'en';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasPermission('content.view') ?? false;
    }

    public static function canEditSettings(): bool
    {
        return auth()->user()?->hasPermission('content.edit') ?? false;
    }

    public function mount(): void
    {
        $requested = (string) request()->query('locale', $this->locale);
        $this->locale = SettingsReader::normalize($requested);

        $stored = SiteSetting::query()
            ->where('type', '!=', 'secret')
            ->where('locale', $this->locale)
            ->pluck('value', 'key')
            ->all();

        $this->form->fill([
            // Only this language's own rows are hydrated; the fallback language
            // is shown as a placeholder so nothing is silently overwritten.
            ...$stored,
            // Secrets are never hydrated into the form; we only report presence.
            'secret_map' => SiteSetting::hasSecret('maps.api_key'),
            'secret_kyc' => SiteSetting::hasSecret('kyc.api_key'),
            'secret_sms' => SiteSetting::hasSecret('sms.api_key'),
            'secret_payments' => SiteSetting::hasSecret('payments.api_secret'),
        ]);
    }

    /** English values, used as placeholder text while editing another language. */
    protected function fallbackValues(): array
    {
        if ($this->locale === 'en') {
            return [];
        }

        return SiteSetting::query()
            ->where('type', '!=', 'secret')
            ->where('locale', 'en')
            ->pluck('value', 'key')
            ->all();
    }

    /** @return array<int, Action> */
    protected function getHeaderActions(): array
    {
        $actions = [];

        foreach (['en' => 'English', 'fr' => 'Français'] as $code => $label) {
            $actions[] = Action::make('locale_'.$code)
                ->label($label)
                ->color($this->locale === $code ? 'primary' : 'gray')
                ->link()
                ->url(fn (): string => static::getUrl(['locale' => $code]));
        }

        return $actions;
    }

    public function form(Form $form): Form
    {
        $disabled = ! self::canEditSettings();
        $fallback = $this->fallbackValues();

        $text = fn (string $key, string $label, ?string $hint = null) => Forms\Components\TextInput::make($key)
            ->label($label)
            ->helperText($hint)
            ->placeholder($fallback[$key] ?? null)
            ->disabled($disabled);

        return $form
            ->statePath('data')
            ->schema([
                Tabs::make('Settings')->tabs([
                    Tabs\Tab::make('General')->schema([
                        $text('site.name', 'Site name'),
                        $text('site.tagline', 'Tagline'),
                    ])->columns(2),

                    Tabs\Tab::make('Homepage')->schema([
                        $text('hero.eyebrow', 'Hero eyebrow'),
                        $text('hero.title_line1', 'Headline — first line'),
                        $text('hero.title_line2', 'Headline — second line'),
                        Forms\Components\Textarea::make('hero.subtitle')
                            ->label('Hero paragraph')
                            ->rows(3)
                            ->placeholder($fallback['hero.subtitle'] ?? null)
                            ->columnSpanFull()
                            ->disabled($disabled),
                    ])->columns(2),

                    Tabs\Tab::make('Counters')->schema([
                        $text('stats.listings', 'Verified listings'),
                        $text('stats.agents', 'Checked agents'),
                        $text('stats.approval', 'Approval rate'),
                        $text('stats.response', 'Average response'),
                    ])->columns(2)
                        ->description('Live counts from the database are merged with these labels on the homepage.'),

                    Tabs\Tab::make('Footer & contact')->schema([
                        Forms\Components\Textarea::make('footer.tagline')
                            ->label('Footer description')
                            ->rows(3)
                            ->placeholder($fallback['footer.tagline'] ?? null)
                            ->columnSpanFull()
                            ->disabled($disabled),
                        $text('contact.email', 'Contact email'),
                        $text('contact.phone', 'Contact phone'),
                        $text('contact.address', 'Head office'),
                    ])->columns(2),

                    Tabs\Tab::make('Legal identity')->schema([
                        $text('legal.company_name', 'Registered company name'),
                        $text('legal.registration', 'Registration number'),
                        $text('legal.updated_at', 'Legal documents updated'),
                    ])->columns(2),

                    Tabs\Tab::make('Integrations')
                        ->visible(fn () => auth()->user()?->hasPermission('settings.view') ?? false)
                        ->schema([
                            Forms\Components\Placeholder::make('integration_notice')
                                ->hiddenLabel()
                                ->content('Keys are encrypted at rest with AES-256 and never rendered back into this form. Enter a new value to replace an existing key; leave blank to keep it.')
                                ->columnSpanFull(),

                            Forms\Components\TextInput::make('maps.api_key')
                                ->label('Mapbox token')
                                ->password()
                                ->revealable(false)
                                ->placeholder(fn () => $this->data['secret_map'] ? '•••••••• saved' : 'Not configured')
                                ->disabled(! (auth()->user()?->hasPermission('settings.edit') ?? false)),

                            Forms\Components\TextInput::make('kyc.api_key')
                                ->label('KYC provider key')
                                ->password()
                                ->placeholder(fn () => $this->data['secret_kyc'] ? '•••••••• saved' : 'Not configured')
                                ->disabled(! (auth()->user()?->hasPermission('settings.edit') ?? false)),

                            Forms\Components\TextInput::make('sms.api_key')
                                ->label('SMS provider key')
                                ->password()
                                ->placeholder(fn () => $this->data['secret_sms'] ? '•••••••• saved' : 'Not configured')
                                ->disabled(! (auth()->user()?->hasPermission('settings.edit') ?? false)),

                            Forms\Components\TextInput::make('payments.api_secret')
                                ->label('Payments secret key')
                                ->password()
                                ->placeholder(fn () => $this->data['secret_payments'] ? '•••••••• saved' : 'Not configured')
                                ->disabled(! (auth()->user()?->hasPermission('settings.edit') ?? false)),
                        ])->columns(2),
                ]),
            ]);
    }

    public function save(): void
    {
        abort_unless(self::canEditSettings(), 403);

        $data = $this->form->getState();

        $secrets = [
            'maps.api_key' => 'Mapbox token',
            'kyc.api_key' => 'KYC provider key',
            'sms.api_key' => 'SMS provider key',
            'payments.api_secret' => 'Payments secret key',
        ];

        foreach ($secrets as $key => $label) {
            $value = $data[$key] ?? null;

            if (filled($value)) {
                SiteSetting::putSecret($key, $label, $value);
            }

            unset($data[$key]);
        }

        $secretFlags = ['secret_map', 'secret_kyc', 'secret_sms', 'secret_payments'];

        foreach ($secretFlags as $flag) {
            unset($data[$flag]);
        }

        foreach ($data as $key => $value) {
            $template = SiteSetting::query()->where('key', $key)->first();

            if (! $template) {
                continue;
            }

            // Writes land on the language currently being edited, creating the
            // translated row on first save instead of overwriting the original.
            SiteSetting::updateOrCreate(
                ['key' => $key, 'locale' => $this->locale],
                [
                    'group' => $template->group,
                    'type' => $template->type,
                    'label' => $template->label,
                    'value' => $value,
                ]
            );
        }

        SettingsReader::flush();

        Notification::make()->title('Settings saved')->success()->send();
    }
}
