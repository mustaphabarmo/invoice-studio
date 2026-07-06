<?php

namespace App\Enums;

enum PaymentPurpose: string
{
    case MembershipRenewal = 'membership_renewal';
    case PublicationPurchase = 'publication_purchase';
    case WalletDeposit = 'wallet_deposit';
}
