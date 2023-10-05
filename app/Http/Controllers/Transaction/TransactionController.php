<?php

namespace App\Http\Controllers\Transaction;

use App\Models\Transaction;
use App\Http\Controllers\ApiController;

class TransactionController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->showAll(Transaction::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        return $this->showOne($transaction);
    }
}
