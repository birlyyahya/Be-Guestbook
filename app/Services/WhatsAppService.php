<?php

namespace App\Services;

use App\Models\Events;
use Illuminate\Support\Facades\Http;
use Vinkla\Hashids\Facades\Hashids;

class WhatsAppService
{
    /**
     * Create a new class instance.
     */
    public static function sendMessage($guests, Events $event)
    {
        $token = env('FONNTE_TOKEN');
        $encryptedCode = Hashids::encode($guests->id);

        $curl = curl_init();

        $message = "🎉 *Undangan Spesial untuk Anda!* 🎉\n\n"
            . "Halo *{$guests->name}*, 👋\n\n"
            . "Kami dengan senang hati mengundang Anda untuk menghadiri acara berikut:\n\n"
            . "✨ *{$event->name}*\n"
            . "📅 Tanggal: *" . date('d F Y', strtotime($event->start_date)) . " - " . date('d F Y', strtotime($event->end_date)) . "*\n"
            . "🕓 Waktu: *" . ($event->start_time ?? '-') . " s/d " . ($event->end_time ?? '-') . "*\n"
            . "📍 Lokasi: *{$event->location}*\n\n"
            . "Mohon kesediaan Anda untuk *mengonfirmasi kehadiran* melalui tautan berikut:\n"
            . "🔗 " . url("api/guests/confirm/{$encryptedCode}") . "\n\n"
            . "Setelah konfirmasi, Anda akan menerima *QR Code* undangan digital yang bisa digunakan untuk absensi pada hari acara. 🎟️\n\n"
            . "_Terima kasih atas perhatian Anda dan kami tunggu kehadiran Anda!_\n\n"
            . "*Salam hangat,*\n";


        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $guests->phone,
                'message' => $message,
                'countryCode' => '62', //optional
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' .  $token //change TOKEN to your actual token
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
}
