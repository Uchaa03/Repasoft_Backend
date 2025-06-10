<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class RepairStatusChanged extends Mailable
{
    public $repair;
    public $oldStatus;

    public function __construct($repair, $oldStatus)
    {
        $this->repair = $repair;
        $this->oldStatus = $oldStatus;
    }

    public function build()
    {
        $subject = 'Actualización de estado de tu reparación';
        $message = 'El estado de tu reparación ha cambiado de ' . $this->oldStatus . ' a ' . $this->repair->status . '.';

        if ($this->oldStatus === 'created') {
            $subject = 'Nueva reparación registrada';
            $message = 'Se ha registrado una nueva reparación para ti. El estado actual es: ' . $this->repair->status . '.';
        } elseif ($this->repair->status === 'in_progress') {
            $message = 'Tu reparación está en progreso.';
        } elseif ($this->repair->status === 'completed') {
            $message = '¡Tu reparación ha sido completada!';
        } elseif ($this->repair->status === 'cancelled') {
            $message = 'Tu reparación ha sido cancelada.';
        }

        return $this->subject($subject)
            ->view('emails.repair-status-changed-html')
            ->with([
                'repair' => $this->repair,
                'oldStatus' => $this->oldStatus,
                'notificationMessage' => $message, // <-- Cambia aquí
            ]);
    }

}

