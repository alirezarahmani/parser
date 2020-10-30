<?php

namespace App;

/**
 * Class Mapping
 * @package App
 */
class Mapping implements MappingInterface
{
    private $map = '
    {
        "userId": "wlygvmciosntssb", "eventName": "purchase",
        "eventTime": "2020-09-15T18:29:00-0800", "eventData": {
        "price": 5950000, "category": {
        "digital": "phone" },
        "detail": { "general": {
        "Dimensions": "8 × 77.5 × 163.3", "Weight": 183,
        "number of SIM card": 2
        }, "CPU": {
        "name": "Qualcomm SDM450 Snapdragon 450 (14 nm) Chipset", "detail": {
        "type": "64bit",
        "Mainframe frequency": 1.8, "GPU": "Adreno 506 GPU", "attrSample_A": {
        "attrSample_B": { "attrSample_C": {
        "attrSample_A": "2556" }
        } }
        } }
        } }
    }';


    public function toArray()
    {
        return json_decode($this->map, true);
    }
}