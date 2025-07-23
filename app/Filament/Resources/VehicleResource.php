<?php

namespace App\Filament\Resources;

use App\Enums\VehicleStatus;
use App\Filament\Resources\VehicleResource\Pages;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\VehicleStatusService;
use App\Traits\CountryOptions;
use App\Traits\IsMobile;
use App\Traits\PowerTrainOptions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Guava\FilamentIconPicker\Forms\IconPicker;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Livewire;

class VehicleResource extends Resource
{
    use CountryOptions;
    use PowerTrainOptions;
    use IsMobile;

    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'mdi-garage';

    public static function getNavigationLabel(): string
    {
        return __('My garage');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Vehicles');
    }

    public static function getModelLabel(): string
    {
        return __('Vehicle');
    }

    public static function form(Form $form): Form
    {
        $fuelTypes = trans('fuel_types');

        return $form
            ->schema([
                Placeholder::make('onboarding')
                    ->hiddenLabel()
                    ->hidden(fn () => User::isOnboarded())
                    ->content(__('To make use of the vehicle manager you need to create your first vehicle.')),
                Tabs::make('vehicle_tabs')
                    ->columnSpan(2)
                    ->tabs([
                        Tabs\Tab::make('data')
                            ->label(__('Data'))
                            ->icon('gmdi-directions-car-filled-r')
                            ->schema([
                                Fieldset::make('basic')
                                    ->label(__('Basic'))
                                    ->schema([
                                        Select::make('brand')
                                            ->label(__('Brand'))
                                            ->required()
                                            ->native((new self)->isMobile())
                                            ->searchable()
                                            ->options(config('vehicles.brands')),
                                        TextInput::make('model')
                                            ->label(__('Model'))
                                            ->required()
                                            ->maxLength(50),
                                        TextInput::make('version')
                                            ->label(__('Version'))
                                            ->required()
                                            ->maxLength(50),
                                        TextInput::make('engine')
                                            ->label(__('Engine'))
                                            ->maxLength(50),
                                        Select::make('powertrain')
                                            ->label(__('Powertrain'))
                                            ->native((new self)->isMobile())
                                            ->searchable()
                                            ->options((new self())->getPowerTrainOptions())
                                            ->reactive()
                                            ->required()
                                            ->afterStateUpdated(fn(callable $set, $state) => $set('powertrain', $state)),
                                        Select::make('fuel_types')
                                            ->required()
                                            ->label(__('Compatible fuel types'))
                                            ->native((new self)->isMobile())
                                            ->multiple()
                                            ->options($fuelTypes),
                                        TextInput::make('tank_capacity')
                                            ->numeric()
                                            ->required()
                                            ->label(__('Tank capacity'))
                                            ->suffix(fn ($get) => trans('powertrains')[$get('powertrain')]['unit_short'] ?? 'l'),
                                        FileUpload::make('image')
                                            ->disk('private')
                                            ->directory('vehicles')
                                            ->visibility('private')
                                            ->label(__('Image'))
                                            ->image()
                                            ->getUploadedFileNameForStorageUsing(
                                                fn (TemporaryUploadedFile $file, Vehicle $vehicle): string => (string) str($file->getClientOriginalExtension())
                                                    ->prepend($vehicle->id . '.'),
                                            ),
                                    ]),
                                Fieldset::make('ownership')
                                    ->label(__('Ownership'))
                                    ->schema([
                                        TextInput::make('mileage_start')
                                            ->label(__('Mileage on purchase'))
                                            ->suffix(' km')
                                            ->numeric()
                                            ->minValue(0),
                                        DatePicker::make('purchase_date')
                                            ->label(__('Purchase date'))
                                            ->native((new self)->isMobile())
                                            ->displayFormat('d-m-Y')
                                            ->maxDate(now()),
                                        DatePicker::make('construction_date')
                                            ->label(__('Construction date'))
                                            ->native((new self)->isMobile())
                                            ->required()
                                            ->displayFormat('d-m-Y')
                                            ->maxDate(now()),
                                        TextInput::make('purchase_price')
                                            ->label(__('Purchase price'))
                                            ->mask(RawJs::make('$money($input)'))
                                            ->stripCharacters(',')
                                            ->prefix('€')
                                            ->step(0.01),
                                        Select::make('country_registration')
                                            ->label(__('Country of registration'))
                                            ->searchable()
                                            ->native((new self)->isMobile())
                                            ->required()
                                            ->options((new self())->getCountryOptions())
                                            ->reactive()
                                            ->afterStateUpdated(fn(callable $set, $state) => $set('license_plate_prefix', $state))
                                            ->helperText(__('Is used for the license plate layout')),
                                        TextInput::make('license_plate')
                                            ->label(__('License plate'))
                                            ->required()
                                            ->prefix(fn(callable $get) => $countries[$get('license_plate_prefix')]['license_plate']['prefix'] ?? false),
                                        Section::make(__('Status'))
                                            ->icon('mdi-list-status')
                                            ->description(__('Default value: driveable. Click open to edit.'))
                                            ->collapsible()
                                            ->collapsed()
                                            ->schema([
                                                ToggleButtons::make('status')
                                                    ->label(__('Status'))
                                                    ->inline()
                                                    ->required()
                                                    ->options(VehicleStatus::class)
                                                    ->default(VehicleStatus::Drivable->value)
                                                    ->columns(2)
                                                    ->gridDirection('row'),
                                            ]),
                                    ]),
                                Fieldset::make('privacy')
                                    ->label(__('Privacy'))
                                    ->schema([
                                        Toggle::make('is_private')
                                            ->label(__('Private')),
                                    ]),
                            ]),
                        Tabs\Tab::make('specifications')
                            ->label(__('Specifications'))
                            ->icon('mdi-engine')
                            ->schema([
                                Repeater::make('specifications')
                                    ->hiddenLabel()
                                    ->defaultItems(0)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('Name')),
                                        TextInput::make('value')
                                            ->label(__('Value')),
                                        IconPicker::make('icon')
                                            ->label(__('Icon'))
                                            ->sets([
                                                'fontawesome-solid',
                                                'google-material-design-icons',
                                                'simple-icons',
                                                'blade-mdi',
                                            ])
                                            ->columns(3),
                                    ])
                                    ->columnSpan(2)
                                    ->columns(),
                            ]),
                        Tabs\Tab::make('notifications')
                            ->label(__('Notifications'))
                            ->icon('gmdi-notifications-active-r')
                            ->schema([
                                Section::make(__('Maintenance'))
                                    ->icon('mdi-car-wrench')
                                    ->collapsible()
                                    ->schema([
                                        Checkbox::make('notifications.maintenance.maintenance')
                                            ->label(__('Maintenance reminder'))
                                            ->default(true),
                                        Checkbox::make('notifications.maintenance.apk')
                                            ->label(__('MOT reminder'))
                                            ->default(true),
                                        Checkbox::make('notifications.maintenance.airco_check')
                                            ->label(__('Airco check reminder'))
                                            ->default(true),
                                        Checkbox::make('notifications.maintenance.liquids_check')
                                            ->label(__('Liquids check reminder'))
                                            ->default(true),
                                        Checkbox::make('notifications.maintenance.tire_pressure_check')
                                            ->label(__('Tire pressure check reminder'))
                                            ->default(true),
                                    ]),
                                Section::make(__('Reconditioning'))
                                    ->icon('mdi-car-wash')
                                    ->collapsible()
                                    ->schema([
                                        Checkbox::make('notifications.reconditioning.washing')
                                            ->label(__('Washing reminder'))
                                            ->default(true),
                                    ]),
                                Section::make(__('Refuelings'))
                                    ->icon('gmdi-local-gas-station-r')
                                    ->collapsible()
                                    ->schema([
                                        Checkbox::make('notifications.refueling.old_fuel')
                                            ->label(__('Outdated fuel (only unleaded 95 (E10))'))
                                            ->default(true),
                                        Checkbox::make('notifications.refueling.periodic_super_plus')
                                            ->label(__('1 in 4 times fill up with Super Plus'))
                                            ->default(true),
                                    ]),
                                Section::make(__('Insurances'))
                                    ->icon('mdi-shield-car')
                                    ->collapsible()
                                    ->schema([
                                        Checkbox::make('notifications.insurance.status')
                                            ->label(__('Insurance status reminder'))
                                            ->default(true),
                                    ]),
                                Section::make(__('Road taxes'))
                                    ->icon('mdi-highway')
                                    ->collapsible()
                                    ->schema([
                                        Checkbox::make('notifications.tax.period_reminder')
                                            ->label(__('Road tax period info'))
                                            ->default(true),
                                    ]),
                            ]),
                        Tabs\Tab::make('privacy')
                            ->label(__('Privacy'))
                            ->icon('gmdi-lock')
                            ->schema([
                                Placeholder::make('privacy_info')
                                    ->hiddenLabel()
                                    ->content(__('Share data from the following modules public to others:')),
                                Checkbox::make('privacy_settings.maintenances')
                                    ->label(__('Maintenance & repairs'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.reconditionings')
                                    ->label(__('Reconditioning & washing'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.accidents')
                                    ->label(__('Accidents & damage'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.products')
                                    ->label(__('Products & accessory'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.refuelings')
                                    ->label(__('Refuelings'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.insurances')
                                    ->label(__('Insurances'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.taxes')
                                    ->label(__('Road tax'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.parking')
                                    ->label(__('Parking'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.toll')
                                    ->label(__('Toll'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.fines')
                                    ->label(__('Fines'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.vignettes')
                                    ->label(__('Vignettes'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.environmental_stickers')
                                    ->label(__('Environmental stickers'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.services')
                                    ->label(__('Services'))
                                    ->default(false),
                                Checkbox::make('privacy_settings.ferries')
                                    ->label(__('Ferries'))
                                    ->default(false),
                            ]),
                        ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $brands = config('vehicles.brands');
        $powertrains = trans('powertrains');
        $powertrainsOptions = [];

        foreach ($powertrains as $key => $value) {
            $powertrainsOptions[$key] = $value['name'];
        }

        return $table
            ->description(__('This is an overview of the vehicles that are in your garage.'))
            ->columns([
                ImageColumn::make('image_url')
                    ->extraAttributes(['class' => 'mb-5'])
                    ->width('100%')
                    ->height('100%')
                    ->hidden(fn (Vehicle $vehicle) => ! Storage::disk('private')->exists('vehicles/' . $vehicle->id . '.jpg')),
                Tables\Columns\Layout\Split::make([
                    Stack::make([
                        TextColumn::make('brand')
                            ->sortable()
                            ->searchable()
                            ->label(__('Vehicle'))
                            ->icon(fn(Vehicle $vehicle) => 'si-' . str($brands[$vehicle->brand])->replace([' ', '-'], '')->lower()->ascii())
                            ->formatStateUsing(fn(Vehicle $vehicle) => $vehicle->full_name),
                        TextColumn::make('mileage_start')
                            ->sortable()
                            ->searchable()
                            ->icon('gmdi-route')
                            ->suffix(' km')
                            ->label(__('Mileage'))
                            ->tooltip(fn(Vehicle $vehicle) => __('Purchased with:') . ' ' . $vehicle->mileage_start . 'km')
                            ->formatStateUsing(fn(Vehicle $vehicle) => $vehicle->mileage_latest ?? $vehicle->mileage_start),
                        TextColumn::make('construction_date')
                            ->sortable()
                            ->searchable()
                            ->icon('fas-birthday-cake')
                            ->date()
                            ->tooltip(fn(Vehicle $vehicle) => __('Constructed on:') . ' ' . $vehicle->construction_date->format('d-m-Y'))
                            ->label(__('Construction date'))
                            ->suffix(' ' . __('years old'))
                            ->formatStateUsing(fn(Vehicle $vehicle) => $vehicle->construction_date->age),
                    ])
                        ->space(),
                    Stack::make([
                        TextColumn::make('license_plate')
                            ->sortable()
                            ->searchable()
                            ->formatStateUsing(function ($record) {
                                return Livewire::mount('license-plate', ['vehicleId' => $record->id]);
                            })
                            ->html()
                            ->label(__('License plate')),
                        TextColumn::make('status_badge')
                            ->sortable()
                            ->icon(function (Vehicle $vehicle, VehicleStatusService $statusService) {
                                $badge = $statusService->getBadge($vehicle);

                                return $badge['icon'] ?? null;
                            })
                            ->getStateUsing(function (Vehicle $vehicle, VehicleStatusService $statusService) {
                                $badge = $statusService->getBadge($vehicle);

                                return $badge['badgeText'] ?? null;
                            })
                            ->color(function (Vehicle $vehicle, VehicleStatusService $statusService) {
                                $badge = $statusService->getBadge($vehicle);

                                return $badge['filamentColor'] ?? null;
                            })
                            ->badge()
                            ->label(__('Status')),
                        TextColumn::make('status')
                            ->icon(fn(string $state) => VehicleStatus::from($state)->getIcon() ?? null)
                            ->formatStateUsing(fn(string $state) => VehicleStatus::from($state)->getLabel() ?? '')
                            ->badge()
                            ->default('drivable')
                            ->color('gray')
                            ->sortable()
                            ->label(__('Status')),
                    ])
                        ->space(),
                ]),
            ])
            ->defaultSort('purchase_date', 'desc')
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('purchase_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'view' => Pages\ViewVehicle::route('/{record}'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
