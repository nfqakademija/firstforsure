<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.9
 * Time: 15.59
 */

namespace App\Models;


class OrderStatus
{
    public const ANSWERED = 'ANSWERED';
    public const CONFIRMED = 'CONFIRMED';
    public const VIEWED = 'VIEWED';
    public const SENT = 'SENT';
}