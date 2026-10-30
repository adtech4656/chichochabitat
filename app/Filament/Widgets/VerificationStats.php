<?php

namespace App\Filament\Widgets;

use App\Models\Property;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VerificationStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pending = Property::query()
            ->whereIn('status', [Property::STATUS_PENDING, Property::STATUS_IN_REVIEW])
            ->count();

        $approved = Property::query()->where('status', Property::STATUS_APPROVED)->count();

        $agents = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'agent')->where('guard_name', 'web'))
            ->count();

        // Platform revenue = the fees we recorded, which customers never see.
        $revenue = Property::query()
            ->where('status', Property::STATUS_APPROVED)
            ->sum('platform_fee');

        $approvalRate = $approved + $pending > 0
            ? round($approved / ($approved + $pending) * 100, 1)
            : 0;

        return [
            Stat::make('Awaiting review', (string) $pending)
                ->description($pending > 0 ? 'Needs a decision' : 'Queue is clear')
                ->descriptionIcon($pending > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pending > 0 ? 'warning' : 'success'),

            Stat::make('Live listings', (string) $approved)
                ->description($approvalRate.'% approval rate')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Verified agents', (string) $agents)
                ->description('KYC approved')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('primary'),

            Stat::make('Platform revenue', 'XOF '.number_format((int) $revenue))
                ->description('Recorded fees, customer-hidden')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('gray'),
        ];
    }
}
