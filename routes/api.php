<?php

use NFSe\Http\NfseWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('callback', [NfseWebhookController::class, 'store'])->name('nfse.webhook.store');
