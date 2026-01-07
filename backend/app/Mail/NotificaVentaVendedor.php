<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str; // <- para UUID
// use Illuminate\Contracts\Queue\ShouldQueue;

class NotificaVentaVendedor extends Mailable /* implements ShouldQueue */
{
    use Queueable, SerializesModels;

    public $cliente;
    public $plan;
    public $vendedorNombre;
    public $pdfContenido;

    public function __construct($cliente, $plan, $vendedorNombre, $pdfContenido = null)
    {
        $this->cliente        = $cliente;
        $this->plan           = $plan;
        $this->vendedorNombre = $vendedorNombre;
        $this->pdfContenido   = $pdfContenido;
    }

    public function build()
    {
        // Tomar la serie de contrato (o fallback)
        $contrato = $this->cliente->serie_contrato
            ?? $this->cliente->codigo_contrato
            ?? 'SIN-CONTRATO';

        $clienteN = trim(($this->cliente->nombres ?? '').' '.($this->cliente->apellidos ?? ''));

        // ✅ Asunto incluye el número de contrato
        $subject = "SOCNET - Venta {$contrato} · {$clienteN}";

        $mail = $this->from('notificaciones@seroficom.org', 'SEROFICOM')
            ->subject($subject)
            ->view('emails.venta_vendedor');

        // ✅ El adjunto también usa la serie de contrato
        if ($this->pdfContenido) {
            $mail->attachData($this->pdfContenido, "Contrato-{$contrato}.pdf", [
                'mime' => 'application/pdf',
            ]);
        }

        // (Opcional pero recomendado) forzar Message-ID único para que nunca se “hilen”
        if (method_exists($this, 'withSymfonyMessage')) {
            $this->withSymfonyMessage(function (\Symfony\Component\Mime\Email $message) use ($contrato) {
                $uniq = sprintf(
                    'venta-%s-%s@seroficom.org',
                    preg_replace('/[^A-Za-z0-9]/', '', (string) $contrato),
                    Str::uuid()
                );
                $headers = $message->getHeaders();
                if ($headers->has('Message-ID')) {
                    $headers->remove('Message-ID');
                }
                $headers->addIdHeader('Message-ID', $uniq);         // <- correcto para Symfony Mailer
                $headers->addTextHeader('X-Entity-Ref-ID', $uniq);  // extra informativa
            });
        }

        return $mail;
    }
}
