<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Cielo;

use DateTime;

class CieloProductLink
{
    /** Link expiration date. */
    public DateTime|null $paymentExpirationDate = null;

    /** Payment max number of installments. */
    public int|null $paymentMaxInstallments = null;

    /** Product description. Max 512 characteres. */
    public string|array|null $productDescription = null;

    /** Product weight in grams. */
    public int|null $productWeight = null;

    /** Credit card payment descriptor. */
    public string|null $transactionSoftDescriptor = null;

    /** Payment max number of transactions. */
    public int|null $transactionsQuantity = null;

    public function __construct(
        /** Product name. */
        public string $productName,

        /** Product price. */
        public float $productPrice,

        /** Transaction type. */
        public CieloTransactionType $transactionType
    ) {
    }
}
