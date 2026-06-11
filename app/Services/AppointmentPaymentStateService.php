<?php

namespace App\Services;

use App\Models\Source;
use Illuminate\Support\Facades\Schema;

class AppointmentPaymentStateService
{
    public const RULE_NO_PAYMENT_REQUIRED = 'no_payment_required';
    public const RULE_PAID = 'paid';
    public const RULE_PENDING = 'pending';

    public function resolve(
        ?int $sourceId,
        ?string $partnerClient,
        string $defaultPaymentStatus,
        string $defaultAppointmentStatus,
        $defaultPaymentDate = null
    ): array {
        $rule = $this->resolveRule($sourceId, $partnerClient);

        return match ($rule) {
            self::RULE_NO_PAYMENT_REQUIRED => [
                'rule' => $rule,
                'payment_status' => 'No Payment Required',
                'appointment_payment_status' => 'success',
                'payment_date' => null,
            ],
            self::RULE_PAID => [
                'rule' => $rule,
                'payment_status' => 'Authorized',
                'appointment_payment_status' => 'success',
                'payment_date' => now(),
            ],
            self::RULE_PENDING => [
                'rule' => $rule,
                'payment_status' => 'Pending',
                'appointment_payment_status' => 'initiated',
                'payment_date' => null,
            ],
            default => [
                'rule' => null,
                'payment_status' => $defaultPaymentStatus,
                'appointment_payment_status' => $defaultAppointmentStatus,
                'payment_date' => $defaultPaymentDate,
            ],
        };
    }

    public function ruleOptions(): array
    {
        return [
            self::RULE_NO_PAYMENT_REQUIRED => 'No Payment Required',
            self::RULE_PAID => 'Paid',
            self::RULE_PENDING => 'Payment Pending',
        ];
    }

    private function resolveRule(?int $sourceId, ?string $partnerClient): ?string
    {
        if ($sourceId && Schema::hasTable('sources')) {
            $sourceRule = Source::query()
                ->whereKey($sourceId)
                ->value('payment_rule');

            if (is_string($sourceRule) && isset($this->ruleOptions()[strtolower($sourceRule)])) {
                return strtolower($sourceRule);
            }
        }

        $clientRule = config('partner_api.payment_rules.' . strtolower((string) $partnerClient));

        if (is_string($clientRule) && isset($this->ruleOptions()[strtolower($clientRule)])) {
            return strtolower($clientRule);
        }

        return null;
    }
}
