<?php

namespace App\Services;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Generates a real, standards-compliant KHQR (Bakong) payment string.
 *
 * The output follows the EMVCo / NBC KHQR spec: a series of TLV
 * (tag-length-value) fields ending in a CRC-16 checksum. A string produced
 * here is a valid dynamic KHQR that a Cambodian banking app can scan.
 *
 * Configure the receiving account in config/services.php -> 'khqr'.
 * (Money only moves if account_id is a real Bakong account — for the demo
 * the "paid" step is still simulated via /payments/{id}/confirm.)
 */
class KhqrService
{
    private string $accountId;

    private string $merchantName;

    private string $merchantCity;

    private string $currency;   // ISO 4217 numeric: 840 = USD, 116 = KHR

    public function __construct()
    {
        $this->accountId = (string) config('services.khqr.account_id', 'chamber@aclb');
        $this->merchantName = (string) config('services.khqr.merchant_name', 'Chamber');
        $this->merchantCity = (string) config('services.khqr.merchant_city', 'Phnom Penh');
        $this->currency = (string) config('services.khqr.currency', '840');
    }

    /**
     * Build the KHQR payload string for a given amount + bill reference.
     */
    public function payload(float $amount, string $billRef): string
    {
        $isDynamic = $amount > 0;

        $p = $this->tlv('00', '01');                       // payload format indicator
        $p .= $this->tlv('01', $isDynamic ? '12' : '11');   // dynamic (12) vs static (11)
        $p .= $this->tlv('29', $this->tlv('00', $this->accountId)); // individual Bakong account
        $p .= $this->tlv('52', '5999');                     // merchant category code (misc retail)
        $p .= $this->tlv('53', $this->currency);            // transaction currency
        if ($isDynamic) {
            $p .= $this->tlv('54', number_format($amount, 2, '.', '')); // amount
        }
        $p .= $this->tlv('58', 'KH');                       // country code
        $p .= $this->tlv('59', $this->clip($this->merchantName, 25));
        $p .= $this->tlv('60', $this->clip($this->merchantCity, 15));
        $p .= $this->tlv('62', $this->tlv('01', $this->clip($billRef, 25))); // bill number

        if ($isDynamic) {
            // Required for any dynamic KHQR: creation + expiration timestamps
            // in milliseconds since epoch (13-digit strings).
            $createdAt = (string) (int) floor(microtime(true) * 1000);
            $expiresAt = (string) ((int) $createdAt + 15 * 60 * 1000); // 15-minute validity
            $p .= $this->tlv('99', $this->tlv('00', $createdAt).$this->tlv('01', $expiresAt));
        }

        $p .= '6304';                                       // CRC tag + length
        $p .= $this->crc16($p);                             // CRC value over everything above

        return $p;
    }

    /** MD5 of the payload — Bakong looks up a dynamic QR's status by this. */
    public function md5(string $payload): string
    {
        return md5($payload);
    }

    /** Render the payload as an SVG QR code (no image extension needed). */
    public function qrSvg(string $payload, int $size = 300): string
    {
        $writer = new Writer(new ImageRenderer(new RendererStyle($size), new SvgImageBackEnd));

        return $writer->writeString($payload);
    }

    /** The SVG QR as a data URI the frontend can drop straight into <img src>. */
    public function qrSvgDataUri(string $payload, int $size = 300): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->qrSvg($payload, $size));
    }

    // -------- helpers --------

    /** One TLV field: 2-char tag + 2-char length + value. */
    private function tlv(string $tag, string $value): string
    {
        return $tag.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
    }

    private function clip(string $value, int $max): string
    {
        return substr($value, 0, $max);
    }

    /** CRC-16/CCITT-FALSE (poly 0x1021, init 0xFFFF) as 4 upper-hex chars. */
    private function crc16(string $data): string
    {
        $crc = 0xFFFF;
        $len = strlen($data);

        for ($i = 0; $i < $len; $i++) {
            $crc ^= (ord($data[$i]) << 8);
            for ($j = 0; $j < 8; $j++) {
                $crc = ($crc & 0x8000)
                    ? (($crc << 1) ^ 0x1021) & 0xFFFF
                    : ($crc << 1) & 0xFFFF;
            }
        }

        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
