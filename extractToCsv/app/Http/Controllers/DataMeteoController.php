<?php

namespace App\Http\Controllers;

use DateInterval;
use DatePeriod;
use DateTime;
use KubAT\PhpSimple\HtmlDomParser;
use App\Http\Requests;

class DataMeteoController extends Controller
{
    public function getFileContent()
    {
        $period = new DatePeriod(
            new DateTime('2011-11-01'),
            new DateInterval('P1D'),
            new DateTime('2014-03-01')
        );

        foreach ($period as $key => $value) {
            $data = $value->format('Ymd');
            $dataAfisare=$value->format('d-m-Y');
            $dataMonth = $value->format('m');
            $dataYear = $value->format('Y');

            //CURL este o bibliotecă PHP care am  importat-o pt a  descarca date prin HTTP.
            // initiem o sesiune  curl
            $ch = curl_init();

            //Cu curl_setopt setam o optiune la sesiunea curl.
            // Optiunea CURLOPT_URL este pentru a specifica url-ul ce urmeaza vizitat.
            curl_setopt($ch, CURLOPT_URL, "https://www.timeanddate.com/scripts/cityajax.php?n=uk/london&mode=historic&hd={$data}&month={$dataMonth}&year={$dataYear}");

            //facem o cerere de tip post, 1-true
            curl_setopt($ch, CURLOPT_POST, 1);

            //CURLOPT_RETURNTRANSFER-returneaza si nu afiseaza direct ce se afla pe pagina
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            //Executam sesiunea cURL $ch si afisam in browser rezultatul
            $server_output = curl_exec($ch);


            //HtmlDomParser returneaza continutul paginii HTML
            $dom = HtmlDomParser::str_get_html($server_output);

            //clasa->primaPozitie->copilul de pe pozitia 1
            $tabelParcurgere = $dom->find("tbody");


            $toateRanduri = [];
            $array = [
                0 => 'Time',
                1 => 'Temp',
                2 => 'Weather',
                3 => 'Wind',
                4 => 'Humidity',
                5 => 'Barometer'
            ];
            $toateRanduri [] = $array;
            $tabel = $tabelParcurgere[0]->childNodes();

            if(($tabel[0]->childNodes(0)->text()) !== "No data available for the given date. Try selecting a different day."){

                foreach ($tabel as $row){

                    if (strlen($row->childNodes(2)->text()) == 10) {

                        $toateRanduri [] = [
                            0 => $row->childNodes(0)->text(),
                            1 => substr_replace($row->childNodes(2)->text(), '', 1, 7),
                            2 => $row->childNodes(3)->text(),
                            3 => $row->childNodes(4)->text(),
                            4 => $row->childNodes(6)->text(),
                            5 => $row->childNodes(7)->text()
                        ];
                    }

                    elseif(strlen($row->childNodes(2)->text()) == 11)
                    {
                        $toateRanduri [] = [
                            0 => $row->childNodes(0)->text(),
                            1 => substr_replace($row->childNodes(2)->text(), '', 2, 7),
                            2 => $row->childNodes(3)->text(),
                            3 => $row->childNodes(4)->text(),
                            4 => $row->childNodes(6)->text(),
                            5 => $row->childNodes(7)->text()
                        ];
                    }
                }
                //  crearea si salvarea datelor extrase intr-un fisier de tip csv
                $fp = fopen("Prognoza meteo Londra {$dataAfisare}.csv", 'w');
                foreach ($toateRanduri as $fields) {
                    fputcsv($fp, $fields);
                }
                fclose($fp);
            }else { continue; }
        }
        echo "Fisierele pentru anul 2011 au fost exportate cu succes";
    }
}


