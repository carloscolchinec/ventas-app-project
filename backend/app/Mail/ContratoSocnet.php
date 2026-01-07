<?php


namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContratoSocnet extends Mailable
{
    use Queueable, SerializesModels;

    public $cliente;
    public $pdf;

    public function __construct($cliente, $pdf)
    {
        $this->cliente = $cliente;
        $this->pdf = $pdf;
    }

    public function build()
    {
        return $this->subject('¡Gracias por elegirnos! Detalles de su nuevo servicio SOCNET')
            ->view('emails.contrato_socnet')
            ->attachData($this->pdf, "Contrato-{$this->cliente->identificacion}.pdf", [
                'mime' => 'application/pdf',
            ])
            ->with([
                'cliente' => $this->cliente
            ]);
    }
}
