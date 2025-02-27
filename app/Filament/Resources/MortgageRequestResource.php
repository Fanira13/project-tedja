<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MortgageRequestResource\Pages;
use App\Models\MortgageRequest;
use App\Models\User;
use App\Filament\Resources\MortgageRequestResource\RelationManagers\InstallmentsRelationManager;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MortgageRequestResource extends Resource
{
    protected static ?string $model = MortgageRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Transactions';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Product and Price')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                               

                                Forms\Components\Select::make('house_id')
                                    ->label('House')
                                    ->options(\App\Models\House::query()->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $house = \App\Models\House::find($state);
                                        if ($house) {
                                            $set('house_price', $house->price ?? 0);
                                        }
                                    }),

                                
                                Forms\Components\Select::make('interest_id')
                                    ->label('Annual Interest in %')
                                    ->options(function (callable $get) {
                                        $houseId = $get('house_id');
                                        if ($houseId) {
                                            return \App\Models\Interest::where('house_id', $houseId)
                                                ->pluck('interest', 'id');
                                        }
                                        return [];
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $interest = \App\Models\Interest::find($state);
                                        if ($interest) {
                                            $set('bank_name', $interest->bank?->name ?? 'Unknown Bank');
                                            $set('interest', $interest->interest);
                                            $set('duration', $interest->duration);
                                        }
                                    }),

  
                                Forms\Components\TextInput::make('bank_name')
                                    ->label('Bank Name')
                                    ->readOnly()
                                    ->default('Unknown Bank'),

                                
                                Forms\Components\TextInput::make('duration')
                                    ->label('Duration in Years')
                                    ->required()
                                    ->readOnly()
                                    ->numeric()
                                    ->suffix(' Years'),

                               
                                Forms\Components\TextInput::make('interest')
                                    ->label('Interest Rate')
                                    ->required()
                                    ->readOnly()
                                    ->numeric()
                                    ->suffix('%'),

                               
                                Forms\Components\TextInput::make('house_price')
                                    ->label('House Price')
                                    ->required()
                                    ->readOnly()
                                    ->numeric()
                                    ->prefix('IDR'),

                                Forms\Components\Select::make('dp_percentage')
                                    ->label('Down Payment (%)')
                                    ->options([
                                        5 => '5%',
                                        10 => '10%',
                                        15 => '15%',
                                        20 => '20%',
                                        25 => '25%',
                                        30 => '30%',
                                    ])
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                        $housePrice = $get('house_price') ?? 0;
                                        $dpAmount = ($state / 100) * $housePrice;
                                        $loanAmount = max($housePrice - $dpAmount, 0);

                                        $set('dp_total_amount', round($dpAmount));
                                        $set('loan_total_amount', round($loanAmount));

                                        $durationYears = $get('duration') ?? 0;
                                        $interestRate = $get('interest') ?? 0;

                                        if ($durationYears > 0 && $loanAmount > 0 && $interestRate > 0) {
                                            $totalPayments =  $durationYears * 12;
                                            $monthlyInterestRate =  $interestRate / 100 / 12;

                                            $numerator = $loanAmount *  $monthlyInterestRate * pow(1 +  $monthlyInterestRate,   $totalPayments);
                                            $denominator = pow(1 + $monthlyInterestRate,  $totalPayments) - 1;
                                            $monthlyPayments = $denominator > 0 ? $numerator / $denominator : 0;

                                            $set('monthly_amount', round($monthlyPayments));

                                            $loanInterestTotalAmount =  $monthlyPayments  * $totalPayments;
                                            $set('loan_interest_total_amount', round($loanInterestTotalAmount));
                                        } else {
                                            $set('monthly_amount', 0);
                                            $set('loan_interest_total_amount', 0);
                                        }
                                    }),

                                Forms\Components\TextInput::make('dp_total_amount')
                                    ->label('Down Payment Amount')
                                    ->readOnly()
                                    ->numeric()
                                    ->prefix('IDR'),

                                Forms\Components\TextInput::make('loan_total_amount')
                                    ->label('Loan Amount')
                                    ->readOnly()
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR'),

                                Forms\Components\TextInput::make('monthly_amount')
                                    ->label('Monthly Payment')
                                    ->readOnly()
                                    ->required()
                                    ->numeric()
                                    ->prefix('IDR'),

                                Forms\Components\TextInput::make('loan_interest_total_amount')
                                    ->label('Total Payment Amount')
                                    ->readOnly()
                                    ->numeric()
                                    ->prefix('IDR'),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Customer Information')
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->relationship('customer', 'email')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    $user = User::find($state);
                                    if ($user) {
                                        $set('name', $user->name);
                                        $set('email', $user->email);
                                    }
                                })
                                ->afterStateHydrated(function (callable $set, $state) {
                                    $userId = $state;
                                    if ($userId) {
                                        $user = User::find($userId);
                                        if ($user) {
                                            $set('name', $user->name);
                                            $set('email', $user->email);
                                        }
                                    }
                                }),

                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->readOnly()
                                ->maxLength(255),

                            Forms\Components\TextInput::make('email')
                                ->required()
                                ->readOnly()
                                ->maxLength(255),
                        ]),

                    Forms\Components\Wizard\Step::make('Bank Approval')
                        ->schema([
                            Forms\Components\FileUpload::make('documents')
                                ->acceptedFileTypes(['application/pdf'])
                                ->required(),

                            Forms\Components\Select::make('status')
                                ->label('Approval Status')
                                ->options([
                                    'Waiting for Bank' => 'Waiting for Bank',
                                    'Approved' => 'Approved',
                                    'Rejected' => 'Rejected',
                                ])
                                ->required(),
                        ]),
                ])
                ->columnSpan('full')
                ->columns(1)
                ->skippable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('house.thumbnail'),
                Tables\Columns\TextColumn::make('customer.name')
                ->searchable(),
                Tables\Columns\TextColumn::make('house.name'),
                Tables\Columns\TextColumn::make('status'),
                
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (MortgageRequest $record) => asset('storage/' . $record->documents))
                ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            InstallmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMortgageRequests::route('/'),
            'create' => Pages\CreateMortgageRequest::route('/create'),
            'edit' => Pages\EditMortgageRequest::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}