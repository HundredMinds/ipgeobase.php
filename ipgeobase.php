<?php
/*
    Copyright 2013, Vladislav Ross
    Contributed by Timur Baymbetov, Kirill Dlussky.

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/
class IPGeoBase
{
    private $fhandleCIDR, $fhandleCities, $fSizeCIDR, $fsizeCities, $outputEncoding;

    const DEFAULT_FILE_ENCODING = 'windows-1251';
    const DEFAULT_OUTPUT_ENCODING = 'UTF-8';

    public function __construct($CIDRFile = null, $CitiesFile = null, $outputEncoding = self::DEFAULT_OUTPUT_ENCODING)
    {
        $CIDRFile = $CIDRFile ?: dirname(__FILE__) . '/cidr_optim.txt';
        $CitiesFile = $CitiesFile ?: dirname(__FILE__) . '/cities.txt';

        $this->outputEncoding = $outputEncoding;

        if (!file_exists($CIDRFile) ||
            !($this->fhandleCIDR = fopen($CIDRFile, 'r'))) {
            throw new \RuntimeException("Cannot access CIDR file: $CIDRFile");
        }
        if (!file_exists($CitiesFile) ||
            !($this->fhandleCities = fopen($CitiesFile, 'r'))) {
            throw new \RuntimeException("Cannot access cities file: $CitiesFile");
        }

        $this->fSizeCIDR = filesize($CIDRFile);
        $this->fsizeCities = filesize($CitiesFile);
    }

    private function getCityByIdx($idx)
    {
        rewind($this->fhandleCities);

        while (!feof($this->fhandleCities)) {
            $str = fgets($this->fhandleCities);
            $arRecord = explode("\t", trim($str));
            if ($arRecord[0] == $idx) {
                $outputConverter = function ($string) {
                    return iconv(self::DEFAULT_FILE_ENCODING, $this->outputEncoding, $string);
                };

                return array_map($outputConverter, array(
                    'city' => $arRecord[1],
                    'region' => $arRecord[2],
                    'district' => $arRecord[3]
                )) + array(
                    'lat' => $arRecord[4],
                    'lng' => $arRecord[5]
                );
            }
        }

        return false;
    }

    public function getRecord($ip)
    {
        $ip = sprintf('%u', ip2long($ip));

        rewind($this->fhandleCIDR);
        $rad = floor($this->fSizeCIDR / 2);
        $pos = $rad;
        while (fseek($this->fhandleCIDR, $pos, SEEK_SET) != -1) {

            if ($rad) {
                fgets($this->fhandleCIDR);
            } else {
                rewind($this->fhandleCIDR);
            }

            $str = fgets($this->fhandleCIDR);

            if (!$str) {
                return false;
            }

            $arRecord = explode("\t", trim($str));

            $rad = floor($rad / 2);
            if (!$rad && ($ip < $arRecord[0] || $ip > $arRecord[1])) {
                return false;
            }

            if ($ip < $arRecord[0]) {
                $pos -= $rad;
            } elseif ($ip > $arRecord[1]) {
                $pos += $rad;
            } else {
                $result = array('range' => $arRecord[2], 'cc' => $arRecord[3]);

                if ($arRecord[4] != '-' && $cityResult = $this->getCityByIdx($arRecord[4])) {
                    $result += $cityResult;
                }

                return $result;
            }
        }
        return false;
    }
}

