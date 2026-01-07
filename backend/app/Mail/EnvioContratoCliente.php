<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EnvioContratoCliente extends Mailable
{
    use Queueable, SerializesModels;

    public $cliente;
    public $rutaPdf;

    public function __construct($cliente, $rutaPdf)
    {
        $this->cliente = $cliente;
        $this->rutaPdf = $rutaPdf;
    }

    public function build()
    {
        return $this->subject('Contrato de Servicio')
                    ->view('emails.contrato')
                    ->attach($this->rutaPdf, [
                        'as' => 'Contrato_Cliente.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
