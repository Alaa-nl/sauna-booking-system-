<?php

namespace App\Services;

// Use the QRCode vendor package
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrCodeService
{
    /**
     * Generate a QR code data string from the provided data
     * 
     * @param string $data The data to encode in the QR code
     * @return string The data URI for the QR code image
     */
    public static function Create($data)
    {
        return (new QRCode)->render($data);
    }

    /**
     * Generate an HTML image tag with the QR code
     * 
     * @param string $data The data to encode in the QR code
     * @return string HTML image tag with the QR code
     */
    public static function CreateHtmlPreviewImage($data)
    {
        return '<img style="max-width: 500px;" src="' . self::Create($data) . '" alt="QR Code" />';
    }
    
    /**
     * Generate a QR code for a booking confirmation
     * 
     * @param array $booking The booking data
     * @return string The data URI for the QR code image
     */
    public static function CreateBookingQR($booking)
    {
        $bookingData = json_encode([
            'id' => $booking['id'],
            'guest' => $booking['guest_name'],
            'date' => $booking['date'],
            'time' => $booking['time'],
            'duration' => $booking['duration']
        ]);
        
        return self::Create($bookingData);
    }
}