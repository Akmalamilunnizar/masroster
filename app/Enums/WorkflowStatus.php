<?php

namespace App\Enums;

enum WorkflowStatus: string
{
    case DRAFT = 'Draft';
    case MENUNGGU_PEMBAYARAN = 'Menunggu Pembayaran';
    case PAID = 'Paid';
}
