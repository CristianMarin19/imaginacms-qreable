<?php


namespace Modules\Qreable\Services;

use Illuminate\Support\Str;
use Modules\Qreable\Entities\Qr;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrService
{
    public function __construct()
    {

    }

    public function addQr($model, $redirect, $zone = null){
        $entityClass = get_class($model);
        $qr = new Qr(['code' => '']);
        $qr->save();
        if ($qr->qreables($entityClass)->get()->contains($model->id) === false) {
            $qr->qreables($entityClass)->attach($model, ['zone' => $zone, 'redirect' => $redirect]);
            $lastQreable = $qr->qreablesByZone($entityClass, $zone)->first();
            $qrCode = $this->generateQrCode(route('api.qreable.show',[$lastQreable->pivot->id]));
            $qr->update(['code'=>$qrCode]);
        }

    }

    public function generateQrCode($code){
        $hexPrimaryColor = str_ireplace('#','',setting('isite::brandPrimary'));
        $colors = str_split($hexPrimaryColor,2);
        foreach ($colors as &$color){
            $color = hexdec($color);
        }
        $qrCode = QrCode::format('png')->size(256)->color($colors[0],$colors[1],$colors[2])->generate($code);
        return 'data:image/png;base64,'.base64_encode($qrCode);
    }
}