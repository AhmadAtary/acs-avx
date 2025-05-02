<?php

namespace App\Helpers;

class DeviceHelper
{
    /**
     * Extracts RF values and signal status from the device data.
     *
     * @param array $deviceData The processed device data.
     * @return array An array containing 'rfValues' and 'signalStatus'.
     */
    public static function getRfValues($deviceData)
    {
        $rfValues = [];
        $signalStatus = [];

        // Extract RF values and signal status based on the device data structure
        if (isset($deviceData['InternetGatewayDevice']['X_Web']['MobileNetwork'])) {
            $mobileNetwork = $deviceData['InternetGatewayDevice']['X_Web']['MobileNetwork'];

            if (isset($mobileNetwork['RFValues'])) {
                $rfValues = $mobileNetwork['RFValues'];
            }

            if (isset($mobileNetwork['SignalStatus'])) {
                $signalStatus = $mobileNetwork['SignalStatus'];
            }
        }

        return [
            'rfValues' => $rfValues,
            'signalStatus' => $signalStatus,
        ];
    }
}