<?php

namespace App\Http\Controllers;

use App\Order;
use App\Mail\OrderShipped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * Ship the given order.
     *
     * @param  Request  $request
     * @param  int  $orderId
     * @return Response
     */
    public function ship(Request $request)
    {
        Mail::send('emails.orders.shipped', [], function ($message) {
            $message->from('example@example.com', 'Example');
            
            $message->to('example@example.com');
        });
    }
}